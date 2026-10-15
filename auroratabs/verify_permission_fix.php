<?php
session_start();
require_once 'app/auth/config/db_connection.php';
require_once 'app/auth/helpers/security_helper.php';

echo "<h2>✅ Permission Fix Verification</h2>";
echo "<hr>";

// Check session
echo "<h3>Session Status:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? '<span style="color:red">NOT SET</span>') . "<br>";
echo "Role ID: " . ($_SESSION['role_id'] ?? '<span style="color:red">NOT SET</span>') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? '<span style="color:red">NOT SET</span>') . "<br>";

if (!isset($_SESSION['role_id'])) {
    echo "<p style='color:red; font-weight:bold'>⚠️ You are not logged in! Please login first.</p>";
    echo "<a href='app/auth/login.php'>Go to Login</a>";
    exit;
}

echo "<hr>";

// Test with connection parameter (NEW WAY - RECOMMENDED)
echo "<h3>✅ Testing with Connection Parameter (NEW - Recommended):</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Module</th><th>Result</th><th>Status</th></tr>";

$test_modules = [
    'Dashboard',
    'Manage Students',
    'Student Attendance',
    'Hours Summary',
    'OJT Student Registry'
];

foreach ($test_modules as $module) {
    $result = has_permission($module, 'view', $conn);
    $status = $result ? '✓ ALLOWED' : '✗ DENIED';
    $color = $result ? 'green' : 'red';
    echo "<tr>";
    echo "<td><strong>{$module}</strong></td>";
    echo "<td>" . ($result ? 'TRUE' : 'FALSE') . "</td>";
    echo "<td style='color: {$color}; font-weight: bold;'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";

// Test without connection parameter (OLD WAY - using global)
echo "<h3>⚠️ Testing without Connection Parameter (OLD - May fail):</h3>";
echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Module</th><th>Result</th><th>Status</th></tr>";

foreach ($test_modules as $module) {
    $result = has_permission($module, 'view'); // No $conn parameter
    $status = $result ? '✓ ALLOWED' : '✗ DENIED';
    $color = $result ? 'green' : 'red';
    echo "<tr>";
    echo "<td><strong>{$module}</strong></td>";
    echo "<td>" . ($result ? 'TRUE' : 'FALSE') . "</td>";
    echo "<td style='color: {$color}; font-weight: bold;'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>Summary:</h3>";
echo "<p>✅ The fix has been applied! The sidebar now passes the <code>\$conn</code> parameter explicitly.</p>";
echo "<p>📝 <strong>Recommendation:</strong> Always pass the <code>\$conn</code> parameter when calling <code>has_permission()</code> to ensure reliable database access.</p>";
echo "<p>🔗 <strong>Next Step:</strong> Refresh your admin pages to see the sidebar links appear correctly!</p>";
?>
