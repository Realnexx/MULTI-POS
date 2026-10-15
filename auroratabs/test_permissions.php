<?php
session_start();
require_once 'app/auth/config/db_connection.php';
require_once 'app/auth/helpers/security_helper.php';

echo "<h2>Permission Debug Test</h2>";
echo "<hr>";

// Check session
echo "<h3>Session Data:</h3>";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "<br>";
echo "Role ID: " . ($_SESSION['role_id'] ?? 'NOT SET') . "<br>";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "<br>";
echo "<hr>";

// Check all modules in database
echo "<h3>All Modules in Database:</h3>";
$modules_query = "SELECT * FROM modules ORDER BY name";
$modules_result = $conn->query($modules_query);
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Slug</th><th>Description</th></tr>";
while ($module = $modules_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$module['id']}</td>";
    echo "<td>{$module['name']}</td>";
    echo "<td>{$module['slug']}</td>";
    echo "<td>{$module['description']}</td>";
    echo "</tr>";
}
echo "</table>";
echo "<hr>";

// Check permissions for current role
if (isset($_SESSION['role_id'])) {
    echo "<h3>Permissions for Role ID: {$_SESSION['role_id']}</h3>";
    $perm_query = "SELECT m.name, rp.can_view, rp.can_manage, rp.can_delete, rp.can_export
                   FROM role_permissions rp
                   JOIN modules m ON rp.module_id = m.id
                   WHERE rp.role_id = ?
                   ORDER BY m.name";
    $stmt = $conn->prepare($perm_query);
    $stmt->bind_param("i", $_SESSION['role_id']);
    $stmt->execute();
    $perm_result = $stmt->get_result();
    
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Module Name</th><th>Can View</th><th>Can Manage</th><th>Can Delete</th><th>Can Export</th></tr>";
    while ($perm = $perm_result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$perm['name']}</td>";
        echo "<td>" . ($perm['can_view'] ? 'YES' : 'NO') . "</td>";
        echo "<td>" . ($perm['can_manage'] ? 'YES' : 'NO') . "</td>";
        echo "<td>" . ($perm['can_delete'] ? 'YES' : 'NO') . "</td>";
        echo "<td>" . ($perm['can_export'] ? 'YES' : 'NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<hr>";
}

// Test specific permission checks
echo "<h3>Testing hasPermission() Function:</h3>";
$test_modules = ['Dashboard', 'Manage Students', 'Student Attendance', 'Hours Summary', 'OJT Student Registry'];
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Module</th><th>Can View?</th><th>Result</th></tr>";
foreach ($test_modules as $module) {
    $result = hasPermission($module, 'view');
    echo "<tr>";
    echo "<td>{$module}</td>";
    echo "<td>" . ($result ? 'YES' : 'NO') . "</td>";
    echo "<td style='color: " . ($result ? 'green' : 'red') . "'>" . ($result ? '✓ ALLOWED' : '✗ DENIED') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>
