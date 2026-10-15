<?php
// Test the hasPermission function directly
session_start();
require_once 'app/auth/config/db_connection.php';
require_once 'app/auth/helpers/security_helper.php';

echo "=== SESSION DATA ===\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n";
echo "Role ID: " . ($_SESSION['role_id'] ?? 'NOT SET') . "\n";
echo "Username: " . ($_SESSION['username'] ?? 'NOT SET') . "\n\n";

echo "=== DATABASE CONNECTION ===\n";
echo "Connection object exists: " . (isset($conn) ? 'YES' : 'NO') . "\n";
if (isset($conn)) {
    echo "Connection status: " . ($conn->ping() ? 'ACTIVE' : 'INACTIVE') . "\n";
}
echo "\n";

echo "=== TESTING hasPermission() ===\n";

// Test Dashboard permission
echo "Testing: hasPermission('Dashboard', 'view')\n";
$result = hasPermission('Dashboard', 'view');
echo "Result: " . ($result ? 'TRUE' : 'FALSE') . "\n\n";

// Manually test the query
if (isset($_SESSION['role_id'])) {
    echo "=== MANUAL QUERY TEST ===\n";
    $module_name = 'Dashboard';
    $query = "SELECT rp.can_view
              FROM role_permissions rp
              JOIN modules m ON rp.module_id = m.id
              WHERE rp.role_id = ? AND m.name = ?";
    
    $stmt = $conn->prepare($query);
    if ($stmt) {
        $stmt->bind_param("is", $_SESSION['role_id'], $module_name);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo "Query returned row: can_view = {$row['can_view']}\n";
            echo "Permission check: " . ((int)$row['can_view'] === 1 ? 'TRUE' : 'FALSE') . "\n";
        } else {
            echo "Query returned NO ROWS\n";
        }
        $stmt->close();
    } else {
        echo "Failed to prepare statement: " . $conn->error . "\n";
    }
}
?>
