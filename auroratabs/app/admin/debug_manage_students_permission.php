<?php
session_start();
require_once("../auth/config/db_connection.php");
require_once '../auth/helpers/security_helper.php';

echo "<h2>🔍 Permission Debug for Manage Students</h2>";
echo "<hr>";

// Check what's in session
echo "<h3>1. Session Permissions Cache</h3>";
if (isset($_SESSION['permissions'])) {
    echo "<p><b>Permissions are cached in session</b></p>";
    
    if (isset($_SESSION['permissions']['Manage Students'])) {
        echo "<table border='1' cellpadding='8'>";
        echo "<tr><th>Permission</th><th>Value</th></tr>";
        foreach ($_SESSION['permissions']['Manage Students'] as $perm => $value) {
            $color = $value ? 'green' : 'red';
            echo "<tr><td>$perm</td><td style='color:$color; font-weight:bold;'>" . ($value ? 'YES (1)' : 'NO (0)') . "</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color:red;'>Manage Students module not found in session cache!</p>";
    }
} else {
    echo "<p style='color:red;'>No permissions loaded in session!</p>";
}

// Check what's in database
echo "<h3>2. Database Permissions (Current)</h3>";
$role_id = $_SESSION['role_id'] ?? 0;
echo "<p>Your Role ID: <b>$role_id</b></p>";

$query = "SELECT m.name, rp.can_view, rp.can_manage, rp.can_delete, rp.can_export
          FROM role_permissions rp
          JOIN modules m ON rp.module_id = m.id
          WHERE rp.role_id = ? AND m.name = 'Manage Students'";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $role_id);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo "<table border='1' cellpadding='8'>";
    echo "<tr><th>Permission</th><th>Value</th></tr>";
    echo "<tr><td>can_view</td><td style='color:" . ($row['can_view'] ? 'green' : 'red') . "; font-weight:bold;'>" . ($row['can_view'] ? 'YES (1)' : 'NO (0)') . "</td></tr>";
    echo "<tr><td>can_manage</td><td style='color:" . ($row['can_manage'] ? 'green' : 'red') . "; font-weight:bold;'>" . ($row['can_manage'] ? 'YES (1)' : 'NO (0)') . "</td></tr>";
    echo "<tr><td>can_delete</td><td style='color:" . ($row['can_delete'] ? 'green' : 'red') . "; font-weight:bold;'>" . ($row['can_delete'] ? 'YES (1)' : 'NO (0)') . "</td></tr>";
    echo "<tr><td>can_export</td><td style='color:" . ($row['can_export'] ? 'green' : 'red') . "; font-weight:bold;'>" . ($row['can_export'] ? 'YES (1)' : 'NO (0)') . "</td></tr>";
    echo "</table>";
} else {
    echo "<p style='color:red;'>No permissions found in database for Manage Students!</p>";
}

// Check what hasPermission returns
echo "<h3>3. hasPermission() Function Results</h3>";
echo "<table border='1' cellpadding='8'>";
echo "<tr><th>Check</th><th>Result</th></tr>";
echo "<tr><td>hasPermission('Manage Students', 'view')</td><td style='color:" . (hasPermission('Manage Students', 'view') ? 'green' : 'red') . "; font-weight:bold;'>" . (hasPermission('Manage Students', 'view') ? 'TRUE' : 'FALSE') . "</td></tr>";
echo "<tr><td>hasPermission('Manage Students', 'manage')</td><td style='color:" . (hasPermission('Manage Students', 'manage') ? 'green' : 'red') . "; font-weight:bold;'>" . (hasPermission('Manage Students', 'manage') ? 'TRUE' : 'FALSE') . "</td></tr>";
echo "<tr><td>hasPermission('Manage Students', 'delete')</td><td style='color:" . (hasPermission('Manage Students', 'delete') ? 'green' : 'red') . "; font-weight:bold;'>" . (hasPermission('Manage Students', 'delete') ? 'TRUE' : 'FALSE') . "</td></tr>";
echo "<tr><td>hasPermission('Manage Students', 'export')</td><td style='color:" . (hasPermission('Manage Students', 'export') ? 'green' : 'red') . "; font-weight:bold;'>" . (hasPermission('Manage Students', 'export') ? 'TRUE' : 'FALSE') . "</td></tr>";
echo "</table>";

echo "<hr>";
echo "<h3>⚠️ Important Notes:</h3>";
echo "<ul>";
echo "<li>If <b>Session Cache</b> and <b>Database</b> don't match → You need to <b>log out and log back in</b></li>";
echo "<li>The <code>hasPermission()</code> function uses the <b>Session Cache</b>, not the database</li>";
echo "<li>To refresh permissions: <b>Log out → Log back in</b></li>";
echo "</ul>";

echo "<div style='background:#fff3cd; padding:15px; border-left:4px solid #ffc107; margin:20px 0;'>";
echo "<b>🔄 To Apply Permission Changes:</b><br>";
echo "1. Go to your Roles & Permissions page<br>";
echo "2. Update the permissions for 'Manage Students'<br>";
echo "3. <b>Log out completely</b><br>";
echo "4. <b>Log back in</b><br>";
echo "5. Permissions will now be refreshed!";
echo "</div>";
?>
