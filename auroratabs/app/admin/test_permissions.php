<?php
session_start();
require_once("../auth/config/db_connection.php");
require_once '../auth/helpers/security_helper.php';

echo "<h2>Permission System Debug</h2>";
echo "<hr>";

// Check session
echo "<h3>1. Session Information</h3>";
echo "<ul>";
echo "<li><b>User ID:</b> " . ($_SESSION['user_id'] ?? 'NOT SET') . "</li>";
echo "<li><b>Role ID:</b> " . ($_SESSION['role_id'] ?? 'NOT SET') . "</li>";
echo "<li><b>Username:</b> " . ($_SESSION['username'] ?? 'NOT SET') . "</li>";
echo "</ul>";

if (!isset($_SESSION['role_id'])) {
    echo "<p style='color:red'><b>ERROR:</b> role_id is not set in session!</p>";
    exit;
}

$role_id = $_SESSION['role_id'];

// Check role details
echo "<h3>2. Role Details</h3>";
$role_query = $conn->prepare("SELECT * FROM roles WHERE id = ?");
$role_query->bind_param("i", $role_id);
$role_query->execute();
$role_result = $role_query->get_result();

if ($role_result->num_rows > 0) {
    $role = $role_result->fetch_assoc();
    echo "<ul>";
    echo "<li><b>Role Name:</b> {$role['name']}</li>";
    echo "<li><b>Description:</b> {$role['description']}</li>";
    echo "</ul>";
} else {
    echo "<p style='color:red'>Role not found in database!</p>";
}

// Check OJT modules
echo "<h3>3. OJT Modules in Database</h3>";
$modules_query = $conn->query("SELECT * FROM modules WHERE name LIKE '%OJT%' OR name LIKE '%Student%' OR name LIKE '%Hours%' OR name LIKE '%Manage%'");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Module Name</th><th>Slug</th></tr>";
while ($mod = $modules_query->fetch_assoc()) {
    echo "<tr><td>{$mod['id']}</td><td>{$mod['name']}</td><td>{$mod['slug']}</td></tr>";
}
echo "</table>";

// Check permissions for current role
echo "<h3>4. Your Role's Permissions</h3>";
$perm_query = $conn->prepare("
    SELECT m.name, rp.can_view, rp.can_manage, rp.can_delete, rp.can_export 
    FROM role_permissions rp 
    JOIN modules m ON rp.module_id = m.id 
    WHERE rp.role_id = ? AND (m.name LIKE '%OJT%' OR m.name LIKE '%Student%' OR m.name LIKE '%Hours%' OR m.name LIKE '%Manage%')
");
$perm_query->bind_param("i", $role_id);
$perm_query->execute();
$perm_result = $perm_query->get_result();

echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Module</th><th>View</th><th>Manage</th><th>Delete</th><th>Export</th></tr>";
while ($perm = $perm_result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$perm['name']}</td>";
    echo "<td>" . ($perm['can_view'] ? '✓' : '✗') . "</td>";
    echo "<td>" . ($perm['can_manage'] ? '✓' : '✗') . "</td>";
    echo "<td>" . ($perm['can_delete'] ? '✓' : '✗') . "</td>";
    echo "<td>" . ($perm['can_export'] ? '✓' : '✗') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Test has_permission function
echo "<h3>5. Testing has_permission() Function</h3>";
$test_modules = ['Manage Students', 'Student Attendance', 'Hours Summary', 'OJT Student Registry'];
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Module</th><th>View</th><th>Manage</th><th>Delete</th><th>Export</th></tr>";
foreach ($test_modules as $mod) {
    echo "<tr>";
    echo "<td><b>$mod</b></td>";
    echo "<td>" . (has_permission($mod, 'view') ? '✓ YES' : '✗ NO') . "</td>";
    echo "<td>" . (has_permission($mod, 'manage') ? '✓ YES' : '✗ NO') . "</td>";
    echo "<td>" . (has_permission($mod, 'delete') ? '✓ YES' : '✗ NO') . "</td>";
    echo "<td>" . (has_permission($mod, 'export') ? '✓ YES' : '✗ NO') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>
