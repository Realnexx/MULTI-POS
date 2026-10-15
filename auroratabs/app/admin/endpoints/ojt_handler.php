<?php
session_start();
ob_start(); 

// Enable Error Reporting for Debugging
ini_set('display_errors', 0); // Don't display to output, breaks JSON
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/ojt_handler_errors.log'); // Log to file next to script
error_reporting(E_ALL);

// Shutdown function to handle fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== NULL && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        // Log the fatal error
        $log_msg = "[" . date('Y-m-d H:i:s') . "] FATAL ERROR: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line'] . "\n";
        file_put_contents(__DIR__ . '/ojt_handler_errors.log', $log_msg, FILE_APPEND);

        // Clear buffer and send JSON error
        if (ob_get_length()) ob_clean();
        header('Content-Type: application/json');
        echo json_encode([
            "status" => "error",
            "message" => "Fatal PHP Error: " . $error['message'] . " in " . $error['file'] . " on line " . $error['line']
        ]);
        exit;
    }
});


require_once '../../auth/helpers/security_helper.php';

header('Content-Type: application/json');

// Simple auth check
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Unauthorized access"]);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_students':
        // Connect to remote DB only when needed
        ob_start(); 
        require_once("../../auth/config/ojt_db.php");
        $connection_error = ob_get_clean();

        if (!isset($conn_ojt) || $conn_ojt->connect_error) {
             echo json_encode(["status" => "error", "message" => "Remote Database Unavailable: " . ($conn_ojt->connect_error ?? "Connection failed") . " " . $connection_error]);
             exit;
        }

        $query = "SELECT s.id, CONCAT(s.lname, ', ', s.fname, ' ', s.mname ) as fullname, s.sex, e.syear, e.course, e.major, e.status 
                  FROM students s
                  LEFT JOIN enrolment e ON s.id = e.studID
                  WHERE e.status = 'confirm' 
                    AND e.course = 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY' 
                    AND e.level = '4' 
                    AND e.semester = '2nd'
                    AND e.syear = '2025'
                  ORDER BY s.lname ASC";
        
        $result = $conn_ojt->query($query);
        $data = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Formatting for the modern UI
                $status_class = 'bg-light-secondary text-secondary';
                if ($row['status'] == 'confirm') $status_class = 'bg-light-success text-success';
                else if ($row['status'] == 'temporary') $status_class = 'bg-light-warning text-warning';

                $data[] = [
                    'id' => $row['id'],
                    'fullname' => mb_convert_case($row['fullname'], MB_CASE_TITLE, "UTF-8"),
                    'course' => $row['course'] ?? 'No Course',
                    'major' => $row['major'] ?? '',
                    'sex' => ucfirst($row['sex']),
                    'status' => ucfirst($row['status'] ?? 'None'),
                    'status_class' => $status_class
                ];
            }
            $json = json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);
            
            if ($json === false) {
                echo json_encode([
                    "status" => "error", 
                    "message" => "JSON Encoding Error: " . json_last_error_msg()
                ]);
            } else {
                echo $json;
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Query Error: " . $conn_ojt->error]);
        }
        break;

    case 'export_students':
        // Connect to remote DB only when needed
        ob_start(); 
        require_once("../../auth/config/ojt_db.php");
        $connection_error = ob_get_clean();

        if (!isset($conn_ojt) || $conn_ojt->connect_error) {
             echo json_encode(["status" => "error", "message" => "Remote Database Unavailable: " . ($conn_ojt->connect_error ?? "Connection failed") . " " . $connection_error]);
             exit;
        }

        require_once("../../auth/config/db_connection.php"); // Connect to local Aurora DB ($conn)
        
        // Fetch the same filtered list as get_students
        $query = "SELECT s.id, s.fname, s.mname, s.lname, s.sex, e.course, e.status 
                  FROM students s
                  LEFT JOIN enrolment e ON s.id = e.studID
                  WHERE e.status = 'confirm' 
                    AND e.course = 'BACHELOR OF SCIENCE IN INFORMATION TECHNOLOGY' 
                    AND e.level = '4' 
                    AND e.semester = '2nd'
                    AND e.syear = '2025'";
        
        $result = $conn_ojt->query($query);
        $exported_count = 0;
        $error_count = 0;

        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $student_id = $conn->real_escape_string($row['id']);
                $fname = $conn->real_escape_string($row['fname']);
                $mname = $conn->real_escape_string($row['mname']);
                $lname = $conn->real_escape_string($row['lname']);
                $sex = $conn->real_escape_string($row['sex']);
                $course = $conn->real_escape_string($row['course']);
                
                // Status mapping (ECR 'confirm' -> Local 'Active')
                $status = ($row['status'] == 'confirm') ? 'Active' : 'Inactive';

                // UPSERT into local ojt_students table
                $insert_sql = "INSERT INTO ojt_students (student_id, fname, mname, lname, course, sex, status) 
                               VALUES ('$student_id', '$fname', '$mname', '$lname', '$course', '$sex', '$status')
                               ON DUPLICATE KEY UPDATE 
                               fname = VALUES(fname), 
                               mname = VALUES(mname), 
                               lname = VALUES(lname), 
                               course = VALUES(course), 
                               sex = VALUES(sex), 
                               status = VALUES(status)";
                
                if ($conn->query($insert_sql)) {
                    $exported_count++;
                } else {
                    $error_count++;
                }
            }
            echo json_encode([
                "status" => "success", 
                "message" => "Export complete. $exported_count students synced. Errors: $error_count"
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "ECR Database Error: " . $conn_ojt->error]);
        }
        break;

    case 'get_local_students':
        ob_start();
        require_once("../../auth/config/db_connection.php"); 
        ob_clean(); // Clear any connection messages
        
        $sex = $_POST['sex'] ?? 'all';
        $where_sex = "";
        if ($sex !== 'all') {
            $s_sex = sanitize($sex);
            $where_sex = " WHERE sex = '$s_sex'";
        }
        
        $query = "SELECT student_id, fname, mname, lname, course, sex, status FROM ojt_students $where_sex ORDER BY lname ASC";
        $result = $conn->query($query);
        $data = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $mi = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
                $fullname = $row['lname'] . ', ' . $row['fname'] . ' ' . $mi;
                
                $status_class = 'bg-light-secondary text-secondary';
                if ($row['status'] == 'Active') $status_class = 'bg-light-success text-success';
                else if ($row['status'] == 'Completed') $status_class = 'bg-light-primary text-primary';
                else if ($row['status'] == 'Dropped') $status_class = 'bg-light-danger text-danger';

                $data[] = [
                    'id' => $row['student_id'],
                    'student_id' => $row['student_id'],
                    'fname' => mb_convert_case($row['fname'], MB_CASE_TITLE, "UTF-8"),
                    'mname' => mb_convert_case($row['mname'], MB_CASE_TITLE, "UTF-8"),
                    'lname' => mb_convert_case($row['lname'], MB_CASE_TITLE, "UTF-8"),
                    'fullname' => mb_convert_case($fullname, MB_CASE_TITLE, "UTF-8"),
                    'course' => $row['course'],
                    'sex' => ucfirst($row['sex']),
                    'status' => ucfirst($row['status']),
                    'status_class' => $status_class
                ];
            }
            echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
        } else {
            echo json_encode(["status" => "error", "message" => "Local Database Error: " . $conn->error]);
        }
        break;

    case 'get_student':
        require_once("../../auth/config/db_connection.php"); 
        require_once '../../auth/helpers/security_helper.php';
        $student_id = sanitize($_POST['student_id'] ?? '');
        
        $query = "SELECT * FROM ojt_students WHERE student_id = '$student_id'";
        $result = $conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            echo json_encode(["status" => "success", "data" => $row]);
        } else {
            echo json_encode(["status" => "error", "message" => "Student not found"]);
        }
        break;

    case 'update_student':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        $student_id = sanitize($_POST['student_id'] ?? '');
        $fname = sanitize($_POST['fname'] ?? '');
        $mname = sanitize($_POST['mname'] ?? '');
        $lname = sanitize($_POST['lname'] ?? '');
        $course = sanitize($_POST['course'] ?? '');
        $sex = sanitize($_POST['sex'] ?? '');
        $status = sanitize($_POST['status'] ?? 'Active');

        $query = "UPDATE ojt_students SET 
                  fname = '$fname', mname = '$mname', lname = '$lname', 
                  course = '$course', sex = '$sex', status = '$status' 
                  WHERE student_id = '$student_id'";
        
        if ($conn->query($query)) {
            echo json_encode(["status" => "success", "message" => "Student updated successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Update failed: " . $conn->error]);
        }
        break;

    case 'delete_student':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        $student_id = sanitize($_POST['student_id'] ?? '');
        
        $query = "DELETE FROM ojt_students WHERE student_id = '$student_id'";
        if ($conn->query($query)) {
            echo json_encode(["status" => "success", "message" => "Student deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Delete failed: " . $conn->error]);
        }
        break;

    case 'get_attendance_record':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        $student_id = sanitize($_POST['student_id'] ?? '');
        $date = sanitize($_POST['date'] ?? '');
        
        $query = "SELECT check_in, break_out, break_in, check_out FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$date'";
        $result = $conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            // Format to H:i for input fields
            $format = function($dt) {
                return $dt ? date('H:i', strtotime($dt)) : '';
            };
            echo json_encode([
                "status" => "success",
                "data" => [
                    "check_in" => $format($row['check_in']),
                    "break_out" => $format($row['break_out']),
                    "break_in" => $format($row['break_in']),
                    "check_out" => $format($row['check_out'])
                ]
            ]);
        } else {
            echo json_encode(["status" => "success", "data" => null]); // No record yet
        }
        break;

    case 'record_attendance_multi':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        
        $student_id = sanitize($_POST['student_id'] ?? '');
        $manual_date = sanitize($_POST['manual_date'] ?? date('Y-m-d'));
        
        $fields = ['check_in', 'break_out', 'break_in', 'check_out'];
        $updates = [];
        $has_data = false;

        foreach ($fields as $field) {
            if (!empty($_POST[$field])) {
                $time = sanitize($_POST[$field]);
                $full_datetime = $manual_date . ' ' . $time . ':00';
                $updates[$field] = $full_datetime;
                $has_data = true;
            }
        }

        if (!$has_data) {
            echo json_encode(["status" => "error", "message" => "No time data provided."]);
            exit;
        }

        // Check if record exists for this date
        $check_sql = "SELECT id FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$manual_date'";
        $res_check = $conn->query($check_sql);
        
        if ($res_check && $res_check->num_rows > 0) {
            // Update existing record
            $update_parts = [];
            foreach ($updates as $f => $v) {
                $update_parts[] = "$f = '$v'";
            }
            $query = "UPDATE ojt_attendance SET " . implode(", ", $update_parts) . " WHERE studID = '$student_id' AND timestamp = '$manual_date'";
        } else {
            // Insert new record
            $cols = array_keys($updates);
            $vals = array_values($updates);
            $query = "INSERT INTO ojt_attendance (studID, timestamp, " . implode(", ", $cols) . ") 
                      VALUES ('$student_id', '$manual_date', '" . implode("', '", $vals) . "')";
        }

        if ($conn->query($query)) {
            calculateAttendanceStats($student_id, $manual_date, $conn);
            
            // Re-fetch for response message
            $res = $conn->query("SELECT total_hours, status FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$manual_date'");
            $row = $res->fetch_assoc();
            
            echo json_encode(["status" => "success", "message" => "Attendance saved for " . date('M d', strtotime($manual_date)) . ". Total: " . $row['total_hours'] . " hrs | Status: " . $row['status']]);
        } else {
            echo json_encode(["status" => "error", "message" => "Save failed: " . $conn->error]);
        }
        break;

    case 'record_attendance':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        $student_id = sanitize($_POST['student_id'] ?? '');
        $type = sanitize($_POST['type'] ?? ''); // check_in, break_out, break_in, check_out
        $manual_date = sanitize($_POST['manual_date'] ?? date('Y-m-d'));
        $manual_time = sanitize($_POST['manual_time'] ?? '');
        
        // Final timestamp for recording
        if (!empty($manual_time)) {
            $full_datetime = $manual_date . ' ' . $manual_time . ':00';
        } else {
            $full_datetime = $manual_date . ' ' . date('H:i:s');
        }
        
        // Check if slot is already filled
        $check_sql = "SELECT $type FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$manual_date'";
        $result = $conn->query($check_sql);
        
        if ($result && $row = $result->fetch_assoc()) {
            if (!empty($row[$type])) {
                $readable_type = ucwords(str_replace('_', ' ', $type));
                $existing_time = date('h:i A', strtotime($row[$type]));
                echo json_encode(["status" => "error", "message" => "$readable_type is already recorded for this date ($existing_time)."]);
                exit;
            }
            $query = "UPDATE ojt_attendance SET $type = '$full_datetime' WHERE studID = '$student_id' AND timestamp = '$manual_date'";
        } else {
            $query = "INSERT INTO ojt_attendance (studID, $type, timestamp) VALUES ('$student_id', '$full_datetime', '$manual_date')";
        }

        if ($conn->query($query)) {
            calculateAttendanceStats($student_id, $manual_date, $conn);
            
            // Re-fetch for response message
            $res = $conn->query("SELECT total_hours, status FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$manual_date'");
            $row = $res->fetch_assoc();

            echo json_encode([
                "status" => "success", 
                "message" => "Logged at " . date('h:i A', strtotime($full_datetime)) . " (" . date('M d', strtotime($manual_date)) . "). Total: " . $row['total_hours'] . " hrs | Status: " . $row['status']
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Log failed: " . $conn->error]);
        }
        break;

    case 'get_attendance_logs':
        require_once("../../auth/config/db_connection.php"); 
        
        $filter = $_POST['filter'] ?? 'all';
        $sex = $_POST['sex'] ?? 'all';
        $custom_date = $_POST['custom_date'] ?? '';
        $custom_month = $_POST['custom_month'] ?? '';
        $where_clause = "";
        
        if ($filter == 'today') {
            $where_clause = " AND a.timestamp = CURDATE()";
        } elseif ($filter == 'week') {
            $where_clause = " AND a.timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        } elseif ($filter == 'month') {
            $where_clause = " AND MONTH(a.timestamp) = MONTH(CURDATE()) AND YEAR(a.timestamp) = YEAR(CURDATE())";
        } elseif ($filter == 'date' && !empty($custom_date)) {
            $s_date = sanitize($custom_date);
            $where_clause = " AND a.timestamp = '$s_date'";
        } elseif ($filter == 'custom_month' && !empty($custom_month)) {
            $s_month = sanitize($custom_month); // Format: YYYY-MM
            $where_clause = " AND a.timestamp LIKE '$s_month%'";
        }

        if ($sex !== 'all') {
            $s_sex = sanitize($sex);
            $where_clause .= " AND s.sex = '$s_sex'";
        }

        $query = "SELECT a.*, s.fname, s.mname, s.lname, s.course, s.sex 
                  FROM ojt_attendance a 
                  JOIN ojt_students s ON a.studID = s.student_id 
                  WHERE 1=1 $where_clause
                  ORDER BY a.timestamp DESC, s.lname ASC";
        
        $result = $conn->query($query);
        $data = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $mi = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
                $fullname = mb_convert_case($row['lname'] . ', ' . $row['fname'] . ' ' . $mi, MB_CASE_TITLE, "UTF-8");
                
                // Status Classes
                $status_class = 'bg-light-success text-success';
                if ($row['status'] == 'Late') $status_class = 'bg-light-warning text-warning';
                else if ($row['status'] == 'Half-day') $status_class = 'bg-light-info text-info';
                else if ($row['status'] == 'Absent') $status_class = 'bg-light-danger text-danger';

                $data[] = [
                    'id' => $row['id'],
                    'student_id' => $row['studID'],
                    'fullname' => $fullname,
                    'course' => $row['course'],
                    'date' => date('M d, Y', strtotime($row['timestamp'])),
                    'check_in' => $row['check_in'] ? date('h:i A', strtotime($row['check_in'])) : '--:--',
                    'break_out' => $row['break_out'] ? date('h:i A', strtotime($row['break_out'])) : '--:--',
                    'break_in' => $row['break_in'] ? date('h:i A', strtotime($row['break_in'])) : '--:--',
                    'check_out' => $row['check_out'] ? date('h:i A', strtotime($row['check_out'])) : '--:--',
                    'total_hours' => number_format($row['total_hours'], 2),
                    'status' => $row['status'],
                    'status_class' => $status_class
                ];
            }
            echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["status" => "error", "message" => "Logs Error: " . $conn->error]);
        }
        break;

    case 'get_hours_summary':
        require_once("../../auth/config/db_connection.php"); 
        
        $goal = 486; // Total OJT hours required
        $filter = $_POST['filter'] ?? 'all';
        $sex = $_POST['sex'] ?? 'all';
        $custom_date = $_POST['custom_date'] ?? '';
        $custom_month = $_POST['custom_month'] ?? '';
        
        // Determine Reference Date (Default to today)
        $ref_date = date('Y-m-d');
        if ($filter == 'date' && !empty($custom_date)) {
            $ref_date = sanitize($custom_date);
        } elseif ($filter == 'custom_month' && !empty($custom_month)) {
            $ref_date = sanitize($custom_month) . "-01"; // Start of selected month
        }

        $where_sex = "";
        if ($sex !== 'all') {
            $s_sex = sanitize($sex);
            $where_sex = " WHERE s.sex = '$s_sex'";
        }

        $query = "SELECT s.student_id, s.fname, s.mname, s.lname, s.course, s.sex,
                    COALESCE((SELECT SUM(total_hours) FROM ojt_attendance WHERE studID = s.student_id), 0) as total_cumulative,
                    COALESCE((SELECT SUM(total_hours) FROM ojt_attendance WHERE studID = s.student_id AND timestamp = '$ref_date'), 0) as total_today,
                    COALESCE((SELECT SUM(total_hours) FROM ojt_attendance WHERE studID = s.student_id AND timestamp >= DATE_SUB('$ref_date', INTERVAL 7 DAY) AND timestamp <= '$ref_date'), 0) as total_week,
                    COALESCE((SELECT SUM(total_hours) FROM ojt_attendance WHERE studID = s.student_id AND MONTH(timestamp) = MONTH('$ref_date') AND YEAR(timestamp) = YEAR('$ref_date')), 0) as total_month
                  FROM ojt_students s 
                  $where_sex
                  ORDER BY s.lname ASC";
        
        $result = $conn->query($query);
        $data = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $mi = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
                $fullname = mb_convert_case($row['lname'] . ', ' . $row['fname'] . ' ' . $mi, MB_CASE_TITLE, "UTF-8");
                
                $cumulative = (float)$row['total_cumulative'];
                $remaining = max(0, $goal - $cumulative);
                $progress = ($cumulative > 0) ? round(($cumulative / $goal) * 100, 2) : 0;
                if ($progress > 100) $progress = 100;

                $data[] = [
                    'student_id' => $row['student_id'],
                    'fullname' => $fullname,
                    'course' => $row['course'],
                    'today' => number_format($row['total_today'], 2),
                    'week' => number_format($row['total_week'], 2),
                    'month' => number_format($row['total_month'], 2),
                    'cumulative' => number_format($cumulative, 2),
                    'remaining' => number_format($remaining, 2),
                    'progress' => $progress,
                    'goal' => $goal
                ];
            }
            echo json_encode(["status" => "success", "data" => $data], JSON_UNESCAPED_UNICODE);
        } else {
            echo json_encode(["status" => "error", "message" => "Summary Error: " . $conn->error]);
        }
        break;

    case 'save_monthly_attendance':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        
        $student_id = sanitize($_POST['student_id'] ?? '');
        $attendance_data = json_decode($_POST['attendance_data'] ?? '[]', true);
        
        if (empty($student_id) || empty($attendance_data)) {
            echo json_encode(["status" => "error", "message" => "Missing data."]);
            exit;
        }

        $success_count = 0;
        foreach ($attendance_data as $record) {
            $date = sanitize($record['date']);
            $updates = [];
            $has_data = false;
            
            $fields = ['check_in', 'break_out', 'break_in', 'check_out'];
            foreach ($fields as $field) {
                if (!empty($record[$field])) {
                    $time = sanitize($record[$field]);
                    $updates[$field] = $date . ' ' . $time . ':00';
                    $has_data = true;
                } else {
                    $updates[$field] = null;
                }
            }

            if ($has_data) {
                // Check exists
                $check_sql = "SELECT id FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$date'";
                $res_check = $conn->query($check_sql);
                
                if ($res_check && $res_check->num_rows > 0) {
                    $update_parts = [];
                    foreach ($updates as $f => $v) {
                        $val = ($v === null) ? "NULL" : "'$v'";
                        $update_parts[] = "$f = $val";
                    }
                    $query = "UPDATE ojt_attendance SET " . implode(", ", $update_parts) . " WHERE studID = '$student_id' AND timestamp = '$date'";
                } else {
                    $cols = ['studID', 'timestamp'];
                    $vals = ["'$student_id'", "'$date'"];
                    foreach ($updates as $f => $v) {
                        if ($v !== null) {
                            $cols[] = $f;
                            $vals[] = "'$v'";
                        }
                    }
                    $query = "INSERT INTO ojt_attendance (" . implode(", ", $cols) . ") VALUES (" . implode(", ", $vals) . ")";
                }

                if ($conn->query($query)) {
                    calculateAttendanceStats($student_id, $date, $conn);
                    $success_count++;
                }
            } else {
                // If all fields empty, optionally delete the record if it exists
                $conn->query("DELETE FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$date'");
            }
        }

        echo json_encode(["status" => "success", "message" => "Successfully processed $success_count records."]);
        break;

    case 'get_monthly_attendance':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        $student_id = sanitize($_POST['student_id'] ?? '');
        $month = sanitize($_POST['month'] ?? ''); // YYYY-MM
        
        if (empty($student_id) || empty($month)) {
            echo json_encode(["status" => "error", "message" => "Missing student ID or month."]);
            exit;
        }

        $query = "SELECT * FROM ojt_attendance WHERE studID = '$student_id' AND timestamp LIKE '$month%'";
        $result = $conn->query($query);
        $data = [];
        
        while ($result && $row = $result->fetch_assoc()) {
            $format = function($dt) {
                return $dt ? date('H:i', strtotime($dt)) : '';
            };
            $data[$row['timestamp']] = [
                "check_in" => $format($row['check_in']),
                "break_out" => $format($row['break_out']),
                "break_in" => $format($row['break_in']),
                "check_out" => $format($row['check_out']),
                "total_hours" => $row['total_hours'],
                "status" => $row['status']
            ];
        }
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'save_manual_monthly_hours':
        require_once("../../auth/config/db_connection.php");
        require_once '../../auth/helpers/security_helper.php';
        
        $student_id = sanitize($_POST['student_id'] ?? '');
        $month = sanitize($_POST['month'] ?? ''); // YYYY-MM
        $total_hours = floatval($_POST['total_hours'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');
        
        if (empty($student_id) || empty($month)) {
            echo json_encode(["status" => "error", "message" => "Missing student ID or month."]);
            exit;
        }

        // Calculate last day of the month
        $last_day = date("Y-m-t", strtotime($month . "-01"));

        // Check if record exists
        $check_sql = "SELECT id FROM ojt_attendance WHERE studID = '$student_id' AND timestamp = '$last_day'";
        $res_check = $conn->query($check_sql);
        
        if ($res_check && $res_check->num_rows > 0) {
            $query = "UPDATE ojt_attendance SET 
                      total_hours = '$total_hours', 
                      status = 'Manual', 
                      description = '$description' 
                      WHERE studID = '$student_id' AND timestamp = '$last_day'";
        } else {
            $query = "INSERT INTO ojt_attendance (studID, timestamp, total_hours, status, description) 
                      VALUES ('$student_id', '$last_day', '$total_hours', 'Manual', '$description')";
        }

        if ($conn->query($query)) {
            echo json_encode(["status" => "success", "message" => "Manual hours saved successfully for " . date('M Y', strtotime($month))]);
        } else {
            echo json_encode(["status" => "error", "message" => "Database Error: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action: " . $action]);
        break;
}

/**
 * Reusable attendance calculation logic
 */
function calculateAttendanceStats($studID, $date, $conn) {
    $fetch_sql = "SELECT check_in, break_out, break_in, check_out, status FROM ojt_attendance WHERE studID = '$studID' AND timestamp = '$date'";
    $res = $conn->query($fetch_sql);
    if ($res && $row = $res->fetch_assoc()) {
        // If manually set, do not overwrite with auto-calculation
        if ($row['status'] === 'Manual') return;

        $total_seconds = 0;
        
        // Normalization Constants
        $am_start = strtotime($date . ' 08:00:00');
        $am_end   = strtotime($date . ' 12:00:00');
        $pm_start = strtotime($date . ' 13:00:00');
        $pm_end   = strtotime($date . ' 17:00:00');

        // AM Session or Full Day if no breaks
        if (!empty($row['check_in'])) {
            $start = max($am_start, strtotime($row['check_in']));
            
            if (!empty($row['break_out'])) {
                $end = min($am_end, strtotime($row['break_out']));
                $total_seconds += max(0, $end - $start);
            } elseif (!empty($row['check_out']) && empty($row['break_in'])) {
                $end = min($pm_end, strtotime($row['check_out']));
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
        
        // Status Logic
        $status = 'Present';
        if (!empty($row['check_in'])) {
            $actual_in = date('H:i:s', strtotime($row['check_in']));
            if ($actual_in > '08:15:59') $status = 'Late';
        }
        if ($status !== 'Late' && !empty($row['break_in'])) {
            $actual_pm_in = date('H:i:s', strtotime($row['break_in']));
            if ($actual_pm_in > '13:15:59') $status = 'Late';
        }
        if ($total_hours > 0 && $total_hours <= 5.0) {
            $status = 'Half-day';
        }
        
        $conn->query("UPDATE ojt_attendance SET total_hours = '$total_hours', status = '$status' WHERE studID = '$studID' AND timestamp = '$date'");
    }
}
?>
