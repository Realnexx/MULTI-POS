<?php
session_start();

// Simulate the exact scenario from index.php
if (!isset($_SESSION['user_id'])) {
    echo "<h2 style='color:red;'>❌ NOT LOGGED IN</h2>";
    echo "<p>Please <a href='../auth/login.php'>login first</a> to test permissions.</p>";
    exit;
}

require_once '../auth/config/db_connection.php';
require_once '../auth/helpers/security_helper.php';

echo "<h2>✅ Session Active - Testing Sidebar Permissions</h2>";
echo "<hr>";

echo "<h3>Session Data:</h3>";
echo "<pre>";
echo "User ID: {$_SESSION['user_id']}\n";
echo "Role ID: {$_SESSION['role_id']}\n";
echo "Username: {$_SESSION['username']}\n";
echo "</pre>";
echo "<hr>";

echo "<h3>Testing Exact Sidebar Code:</h3>";
echo "<pre>";
echo "// This is the exact code from sidebar.php\n\n";

// Execute the exact same code as sidebar
$dashboard_view = has_permission('Dashboard', 'view', $conn);
$can_view_manage_students = has_permission('Manage Students', 'view', $conn);
$can_view_student_attendance = has_permission('Student Attendance', 'view', $conn);
$can_view_hours_summary = has_permission('Hours Summary', 'view', $conn);
$can_view_ojt_student_registry = has_permission('OJT Student Registry', 'view', $conn);

echo "\$dashboard_view = " . ($dashboard_view ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "\$can_view_manage_students = " . ($can_view_manage_students ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "\$can_view_student_attendance = " . ($can_view_student_attendance ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "\$can_view_hours_summary = " . ($can_view_hours_summary ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "\$can_view_ojt_student_registry = " . ($can_view_ojt_student_registry ? 'TRUE ✅' : 'FALSE ❌') . "\n";
echo "</pre>";
echo "<hr>";

echo "<h3>Sidebar Link Visibility:</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Section</th><th>Variable</th><th>Will Show?</th></tr>";

$tests = [
    ['Dashboard', '$dashboard_view', $dashboard_view],
    ['Managed Students', '$can_view_manage_students', $can_view_manage_students],
    ['Student Attendance', '$can_view_student_attendance', $can_view_student_attendance],
    ['Hours Summary', '$can_view_hours_summary', $can_view_hours_summary],
    ['OJT Student Registry', '$can_view_ojt_student_registry', $can_view_ojt_student_registry]
];

foreach ($tests as $test) {
    $color = $test[2] ? 'green' : 'red';
    $status = $test[2] ? '✅ YES - Link will show' : '❌ NO - Link will be hidden';
    echo "<tr>";
    echo "<td><strong>{$test[0]}</strong></td>";
    echo "<td><code>{$test[1]}</code></td>";
    echo "<td style='color: {$color}; font-weight: bold;'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>✅ Next Step:</h3>";
echo "<p>If all values above show TRUE ✅, then refresh your <strong>index.php</strong> page and the sidebar links should now appear!</p>";
echo "<p><a href='index.php' style='font-size: 18px; font-weight: bold;'>→ Go to Dashboard (index.php)</a></p>";
?>
