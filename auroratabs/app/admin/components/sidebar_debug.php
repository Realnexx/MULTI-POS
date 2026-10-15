<?php
// Add debug logging to sidebar
$debug_log = "C:/xampp/htdocs/auroratabs/sidebar_debug.txt";

// Log session status
$log_content = "=== SIDEBAR DEBUG LOG ===\n";
$log_content .= "Time: " . date('Y-m-d H:i:s') . "\n";
$log_content .= "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'NOT ACTIVE') . "\n";
$log_content .= "Session ID: " . (session_status() === PHP_SESSION_ACTIVE ? session_id() : 'N/A') . "\n";
$log_content .= "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
$log_content .= "Role ID: " . ($_SESSION['role_id'] ?? 'NOT SET') . "\n";
$log_content .= "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n";

require_once '../auth/helpers/security_helper.php';
require_once '../auth/config/db_connection.php';

$log_content .= "DB Connection exists: " . (isset($conn) ? 'YES' : 'NO') . "\n";
if (isset($conn)) {
    $log_content .= "DB Connection active: " . ($conn->ping() ? 'YES' : 'NO') . "\n";
}

// Pass $conn explicitly to avoid scope issues
$dashboard_view = has_permission('Dashboard', 'view', $conn);
$log_content .= "\nPermission Check Results:\n";
$log_content .= "dashboard_view = " . ($dashboard_view ? 'TRUE' : 'FALSE') . "\n";

$can_view_manage_students = has_permission('Manage Students', 'view', $conn);
$log_content .= "can_view_manage_students = " . ($can_view_manage_students ? 'TRUE' : 'FALSE') . "\n";

$can_view_student_attendance = has_permission('Student Attendance', 'view', $conn);
$log_content .= "can_view_student_attendance = " . ($can_view_student_attendance ? 'TRUE' : 'FALSE') . "\n";

$can_view_hours_summary = has_permission('Hours Summary', 'view', $conn);
$log_content .= "can_view_hours_summary = " . ($can_view_hours_summary ? 'TRUE' : 'FALSE') . "\n";

$can_view_ojt_student_registry = has_permission('OJT Student Registry', 'view', $conn);
$log_content .= "can_view_ojt_student_registry = " . ($can_view_ojt_student_registry ? 'TRUE' : 'FALSE') . "\n";

$log_content .= "\n=========================\n\n";

file_put_contents($debug_log, $log_content, FILE_APPEND);
?>
