<?php
    session_start();
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $filter = $_GET['filter'] ?? 'all';
    $sex = $_GET['sex'] ?? 'all';
    $custom_date = $_GET['custom_date'] ?? '';
    $custom_month = $_GET['custom_month'] ?? '';
    
    // Determine Reference Date
    $ref_date = date('Y-m-d');
    if ($filter == 'date' && !empty($custom_date)) {
        $ref_date = sanitize($custom_date);
    } elseif ($filter == 'custom_month' && !empty($custom_month)) {
        $ref_date = sanitize($custom_month) . "-01";
    }

    $where_clause = "WHERE 1=1";
    $filename_part = "All_Logs";

    if ($filter === 'today') {
        $where_clause .= " AND a.timestamp = CURDATE()";
        $filename_part = "Today_" . date('Y-m-d');
    } elseif ($filter === 'week') {
        $where_clause .= " AND a.timestamp >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
        $filename_part = "This_Week";
    } elseif ($filter === 'month') {
        $where_clause .= " AND MONTH(a.timestamp) = MONTH(CURDATE()) AND YEAR(a.timestamp) = YEAR(CURDATE())";
        $filename_part = date('F_Y');
    } elseif ($filter === 'date') {
        $where_clause .= " AND a.timestamp = '$ref_date'";
        $filename_part = date('Y-m-d', strtotime($ref_date));
    } elseif ($filter === 'custom_month') {
        $where_clause .= " AND MONTH(a.timestamp) = MONTH('$ref_date') AND YEAR(a.timestamp) = YEAR('$ref_date')";
        $filename_part = date('F_Y', strtotime($ref_date));
    }

    if ($sex !== 'all') {
        $s_sex = sanitize($sex);
        $where_clause .= " AND s.sex = '$s_sex'";
        $filename_part .= "_" . $sex;
    }

    $query = "SELECT a.*, s.fname, s.mname, s.lname, s.student_id, s.course, s.sex 
              FROM ojt_attendance a 
              JOIN ojt_students s ON a.studID = s.student_id 
              $where_clause 
              ORDER BY a.timestamp DESC, s.lname ASC";
    
    $result = $conn->query($query);

    // Header for Excel Download (CSV format)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=OJT_Attendance_' . $filename_part . '.csv');

    $output = fopen('php://output', 'w');

    // Add Byte Order Mark for Excel UTF-8 support
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // CSV Headers
    fputcsv($output, ['OJT ATTENDANCE LOGS REPORT']);
    fputcsv($output, ['Generated on: ' . date('F d, Y h:i A')]);
    fputcsv($output, []); // Spacer
    fputcsv($output, ['Date', 'Student Name', 'Course', 'Gender', 'Morning In', 'Morning Out', 'Afternoon In', 'Afternoon Out', 'Total Hours', 'Status']);

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $mi = !empty($row['mname']) ? strtoupper(substr($row['mname'], 0, 1)) . '.' : '';
            $fullname = mb_convert_case($row['lname'] . ', ' . $row['fname'] . ' ' . $mi, MB_CASE_TITLE, "UTF-8");
            
            $formatTime = function($time) {
                if ($time === '--:--' || empty($time)) return '--:--';
                return date('h:i A', strtotime($time));
            };

            fputcsv($output, [
                date('M d, Y', strtotime($row['timestamp'])),
                $fullname,
                $row['course'],
                $row['sex'],
                $formatTime($row['check_in']),
                $formatTime($row['break_out']),
                $formatTime($row['break_in']),
                $formatTime($row['check_out']),
                $row['total_hours'] . ' hrs',
                ucfirst($row['status'])
            ]);
        }
    } else {
        fputcsv($output, ['No attendance logs found with the current filters.']);
    }

    fclose($output);
    exit();
?>
