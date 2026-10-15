<?php
ob_start();

// Enable Error Reporting for Debugging (Log to file)
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/tracker_errors.log');
error_reporting(E_ALL);

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Fatal PHP Error: " . $error['message']
        ]);
        exit;
    }
});

require_once '../auth/helpers/security_helper.php';
require_once "../auth/config/db_connection.php";
// Clear any output from includes
if (ob_get_length()) ob_clean();

header('Content-Type: application/json');

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_student_info':
        $student_id = sanitize($_POST['student_id'] ?? '');
        $query = "SELECT fname, lname FROM ojt_students WHERE student_id = '$student_id'";
        $result = $conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            echo json_encode([
                "status" => "success", 
                "data" => [
                    "fullname" => $row['fname'] . ' ' . $row['lname']
                ]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Student ID not found."]);
        }
        break;

    case 'record_attendance':
        $student_id = sanitize($_POST['student_id'] ?? '');
        $type = sanitize($_POST['type'] ?? ''); // check_in, break_out, break_in, check_out
        $manual_date = date('Y-m-d'); // Always use server date for kiosk
        $manual_time = date('H:i');   // Always use server time for kiosk
        
        if (empty($student_id)) {
            echo json_encode(["status" => "error", "message" => "Student ID is required."]);
            exit;
        }

        // Verify Student Exists
        $check_stud = $conn->query("SELECT id FROM ojt_students WHERE student_id = '$student_id'");
        if ($check_stud->num_rows === 0) {
            echo json_encode(["status" => "error", "message" => "Student ID not found."]);
            exit;
        }

        // Final timestamp for recording
        $full_datetime = $manual_date . ' ' . date('H:i:s');
        
        // Check if slot is already filled
        $check_sql = "SELECT $type FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$manual_date'";
        $result = $conn->query($check_sql);
        
        if ($result && $row = $result->fetch_assoc()) {
            if (!empty($row[$type])) {
                $readable_type = ucwords(str_replace('_', ' ', $type));
                $existing_time = date('h:i A', strtotime($row[$type]));
                echo json_encode(["status" => "error", "message" => "$readable_type is already recorded today at $existing_time."]);
                exit;
            }
            $query = "UPDATE ojt_attendance SET $type = '$full_datetime' WHERE studID = '$student_id' AND timestamp = '$manual_date'";
        } else {
            $query = "INSERT INTO ojt_attendance (studID, $type, timestamp) VALUES ('$student_id', '$full_datetime', '$manual_date')";
        }

        if ($conn->query($query)) {
            // --- AUTOMATIC HOURS CALCULATION ---
            $fetch_sql = "SELECT check_in, break_out, break_in, check_out FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$manual_date'";
            $res = $conn->query($fetch_sql);
            if ($res && $row = $res->fetch_assoc()) {
                $total_seconds = 0;
                
                // Normalization Constants
                $am_start = strtotime($manual_date . ' 08:00:00');
                $am_end   = strtotime($manual_date . ' 12:00:00');
                $pm_start = strtotime($manual_date . ' 13:00:00');
                $pm_end   = strtotime($manual_date . ' 17:00:00');

                // AM Session or Full Day if no breaks
                if (!empty($row['check_in'])) {
                    // Normalize start time (if before 8am, use 8am)
                    $start = max($am_start, strtotime($row['check_in']));
                    
                    if (!empty($row['break_out'])) {
                        // Normalize end time (if after 12pm, use 12pm)
                        $end = min($am_end, strtotime($row['break_out']));
                        $total_seconds += max(0, $end - $start);
                    } elseif (!empty($row['check_out']) && empty($row['break_in'])) {
                        // Simple In-Out without recorded breaks
                        $end = min($pm_end, strtotime($row['check_out']));
                        
                        // Subtract lunch break if it covers the 12-1 period
                        $raw_start = strtotime($row['check_in']);
                        $raw_end = strtotime($row['check_out']);
                        
                        $duration = max(0, $end - $start);
                        if ($raw_start <= $am_end && $raw_end >= $pm_start) {
                            $duration -= 3600; // Deduct 1 hour lunch
                        }
                        $total_seconds += max(0, $duration);
                    }
                }
                
                // PM Session
                if (!empty($row['break_in']) && !empty($row['check_out'])) {
                    $start = max($pm_start, strtotime($row['break_in']));
                    $end = min($pm_end, strtotime($row['check_out']));
                    $total_seconds += max(0, $end - $start);
                }
                
                // Final calculation with 8-hour cap
                $total_hours = round($total_seconds / 3600, 2);
                if ($total_hours > 8.0) $total_hours = 8.00;
                
                // --- AUTOMATIC STATUS CALCULATION ---
                $status = 'Present';
                
                // Late Detection based on ACTUAL times (not normalized)
                if (!empty($row['check_in'])) {
                    $actual_in = date('H:i:s', strtotime($row['check_in']));
                    if ($actual_in > '08:15:59') $status = 'Late';
                }
                
                if ($status !== 'Late' && !empty($row['break_in'])) {
                    $actual_pm_in = date('H:i:s', strtotime($row['break_in']));
                    if ($actual_pm_in > '13:15:59') $status = 'Late';
                }
                
                // Half-day Logic (based on normalized hours)
                if ($total_hours > 0 && $total_hours <= 5.0) {
                    $status = 'Half-day';
                }
                
                $conn->query("UPDATE ojt_attendance SET total_hours = '$total_hours', status = '$status' WHERE studID = '$student_id' AND timestamp = '$manual_date'");
                
                echo json_encode([
                    "status" => "success", 
                    "message" => "Successfully timed " . strtoupper(str_replace('_', ' ', $type)) . " at " . date('h:i A') . "."
                ]);
            } else {
                 echo json_encode(["status" => "error", "message" => "Error calculating hours."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Log failed: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action: " . $action]);
        break;
}
?>
