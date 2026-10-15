<?php
session_start();
require_once("../auth/config/db_connection.php");
require_once '../auth/helpers/security_helper.php';

echo "<h2>✅ New Permission System Test</h2>";
echo "<hr>";

// Check session
echo "<h3>Session Information</h3>";
echo "<ul>";
echo "<li><b>User ID:</b> " . ($_SESSION['user_id'] ?? '<span style="color:red">NOT SET</span>') . "</li>";
echo "<li><b>Role ID:</b> " . ($_SESSION['role_id'] ?? '<span style="color:red">NOT SET</span>') . "</li>";
echo "<li><b>Username:</b> " . ($_SESSION['username'] ?? '<span style="color:red">NOT SET</span>') . "</li>";
echo "<li><b>Permissions Loaded:</b> " . (isset($_SESSION['permissions']) ? '<span style="color:green">YES (' . count($_SESSION['permissions']) . ' modules)</span>' : '<span style="color:red">NO</span>') . "</li>";
echo "</ul>";

if (!isset($_SESSION['permissions'])) {
    echo "<div style='background:#fff3cd; padding:15px; border-left:4px solid #ffc107; margin:20px 0;'>";
    echo "<b>⚠️ Permissions Not Loaded</b><br>";
    echo "Please log out and log back in to load permissions into your session.";
    echo "</div>";
} else {
    // Show cached permissions
    echo "<h3>Cached Permissions in Session</h3>";
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f8f9fa;'><th>Module</th><th>View</th><th>Manage</th><th>Delete</th><th>Export</th></tr>";
    
    foreach ($_SESSION['permissions'] as $module => $perms) {
        echo "<tr>";
        echo "<td><b>$module</b></td>";
        echo "<td style='text-align:center;'>" . ($perms['can_view'] ? '✅' : '❌') . "</td>";
        echo "<td style='text-align:center;'>" . ($perms['can_manage'] ? '✅' : '❌') . "</td>";
        echo "<td style='text-align:center;'>" . ($perms['can_delete'] ? '✅' : '❌') . "</td>";
        echo "<td style='text-align:center;'>" . ($perms['can_export'] ? '✅' : '❌') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Test hasPermission function
    echo "<h3>Testing hasPermission() Function</h3>";
    $test_modules = ['Manage Students', 'Student Attendance', 'Hours Summary', 'OJT Student Registry'];
    echo "<table border='1' cellpadding='8' style='border-collapse:collapse; width:100%;'>";
    echo "<tr style='background:#f8f9fa;'><th>Module</th><th>View</th><th>Manage</th><th>Delete</th><th>Export</th></tr>";
    
    foreach ($test_modules as $mod) {
        echo "<tr>";
        echo "<td><b>$mod</b></td>";
        $view = hasPermission($mod, 'view');
        $manage = hasPermission($mod, 'manage');
        $delete = hasPermission($mod, 'delete');
        $export = hasPermission($mod, 'export');
        
        echo "<td style='text-align:center; background:" . ($view ? '#d4edda' : '#f8d7da') . ";'>" . ($view ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td style='text-align:center; background:" . ($manage ? '#d4edda' : '#f8d7da') . ";'>" . ($manage ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td style='text-align:center; background:" . ($delete ? '#d4edda' : '#f8d7da') . ";'>" . ($delete ? '✅ YES' : '❌ NO') . "</td>";
        echo "<td style='text-align:center; background:" . ($export ? '#d4edda' : '#f8d7da') . ";'>" . ($export ? '✅ YES' : '❌ NO') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div style='background:#d1ecf1; padding:15px; border-left:4px solid #0c5460; margin:20px 0;'>";
    echo "<b>ℹ️ How It Works:</b><br>";
    echo "• Permissions are loaded <b>once during login</b> and cached in <code>\$_SESSION['permissions']</code><br>";
    echo "• The <code>hasPermission()</code> function checks the cached data (no database queries!)<br>";
    echo "• Much faster than querying the database on every page load<br>";
    echo "• To refresh permissions, simply log out and log back in";
    echo "</div>";
}
?>
