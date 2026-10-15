<?php
session_start();
require_once '../auth/config/db_connection.php';
require_once '../auth/helpers/security_helper.php';

echo "<h2>🔍 Live Permission Debugging</h2>";
echo "<hr>";

// Check session
echo "<h3>1. Session Check:</h3>";
echo "<pre>";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? '❌ NOT SET') . "\n";
echo "Role ID: " . ($_SESSION['role_id'] ?? '❌ NOT SET') . "\n";
echo "Username: " . ($_SESSION['username'] ?? '❌ NOT SET') . "\n";
echo "</pre>";

if (!isset($_SESSION['role_id'])) {
    echo "<p style='color:red; font-weight:bold'>⚠️ PROBLEM FOUND: You are not logged in or session is lost!</p>";
    echo "<p>Please <a href='../auth/login.php'>login here</a> and try again.</p>";
    exit;
}

echo "<hr>";

// Check database connection
echo "<h3>2. Database Connection:</h3>";
echo "<pre>";
echo "Connection exists: " . (isset($conn) ? '✅ YES' : '❌ NO') . "\n";
if (isset($conn)) {
    echo "Connection active: " . ($conn->ping() ? '✅ YES' : '❌ NO') . "\n";
    echo "Database: " . $conn->get_server_info() . "\n";
}
echo "</pre>";
echo "<hr>";

// Test the exact code from sidebar
echo "<h3>3. Testing Exact Sidebar Code:</h3>";
echo "<pre>";
echo "Executing: \$dashboard_view = has_permission('Dashboard', 'view', \$conn);\n\n";
$dashboard_view = has_permission('Dashboard', 'view', $conn);
echo "Result: " . ($dashboard_view ? '✅ TRUE' : '❌ FALSE') . "\n";
echo "Type: " . gettype($dashboard_view) . "\n";
echo "Value: " . var_export($dashboard_view, true) . "\n";
echo "</pre>";
echo "<hr>";

// Manual database query
echo "<h3>4. Manual Database Query:</h3>";
$role_id = $_SESSION['role_id'];
$module_name = 'Dashboard';
$query = "SELECT m.id, m.name, rp.can_view, rp.can_manage, rp.can_delete, rp.can_export
          FROM role_permissions rp
          JOIN modules m ON rp.module_id = m.id
          WHERE rp.role_id = ? AND m.name = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("is", $role_id, $module_name);
$stmt->execute();
$result = $stmt->get_result();

echo "<pre>";
if ($row = $result->fetch_assoc()) {
    echo "✅ Permission record found:\n";
    echo "Module ID: {$row['id']}\n";
    echo "Module Name: {$row['name']}\n";
    echo "Can View: {$row['can_view']} (" . ($row['can_view'] == 1 ? '✅ YES' : '❌ NO') . ")\n";
    echo "Can Manage: {$row['can_manage']}\n";
    echo "Can Delete: {$row['can_delete']}\n";
    echo "Can Export: {$row['can_export']}\n";
} else {
    echo "❌ NO PERMISSION RECORD FOUND!\n";
    echo "Role ID: {$role_id}\n";
    echo "Module: {$module_name}\n";
}
echo "</pre>";
$stmt->close();
echo "<hr>";

// Test all OJT permissions
echo "<h3>5. All Permission Tests:</h3>";
$test_modules = [
    'Dashboard',
    'Manage Students',
    'Student Attendance',
    'Hours Summary',
    'OJT Student Registry'
];

echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
echo "<tr style='background: #f0f0f0;'><th>Module</th><th>has_permission() Result</th><th>Status</th></tr>";
foreach ($test_modules as $module) {
    $result = has_permission($module, 'view', $conn);
    $status = $result ? '✅ PASS' : '❌ FAIL';
    $color = $result ? 'green' : 'red';
    echo "<tr>";
    echo "<td><strong>{$module}</strong></td>";
    echo "<td>" . ($result ? 'TRUE' : 'FALSE') . "</td>";
    echo "<td style='color: {$color}; font-weight: bold;'>{$status}</td>";
    echo "</tr>";
}
echo "</table>";

echo "<hr>";
echo "<h3>6. Sidebar Simulation:</h3>";
echo "<pre>";
echo "If this were the sidebar:\n\n";
echo "<?php if (\$dashboard_view): ?>\n";
echo "  This means dashboard_view = " . ($dashboard_view ? 'TRUE' : 'FALSE') . "\n";
echo "  So the content would be: " . ($dashboard_view ? '✅ SHOWN' : '❌ HIDDEN') . "\n";
echo "<?php endif; ?>\n";
echo "</pre>";
?>
