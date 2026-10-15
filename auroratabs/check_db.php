<?php
// Simple CLI script to check database
require_once 'app/auth/config/db_connection.php';

echo "=== CHECKING MODULES ===\n";
$result = $conn->query("SELECT * FROM modules ORDER BY name");
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']}, Name: {$row['name']}, Slug: {$row['slug']}\n";
}

echo "\n=== CHECKING ROLE PERMISSIONS (Role ID 1) ===\n";
$stmt = $conn->prepare("SELECT m.name, rp.can_view, rp.can_manage, rp.can_delete, rp.can_export
                        FROM role_permissions rp
                        JOIN modules m ON rp.module_id = m.id
                        WHERE rp.role_id = 1
                        ORDER BY m.name");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    echo "Module: {$row['name']} | View: {$row['can_view']} | Manage: {$row['can_manage']} | Delete: {$row['can_delete']} | Export: {$row['can_export']}\n";
}

echo "\n=== CHECKING IF 'Dashboard' MODULE EXISTS ===\n";
$stmt = $conn->prepare("SELECT * FROM modules WHERE name = 'Dashboard'");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Dashboard module found! ID: {$row['id']}\n";
    
    // Check if permissions exist for this module
    $stmt2 = $conn->prepare("SELECT * FROM role_permissions WHERE module_id = ? AND role_id = 1");
    $stmt2->bind_param("i", $row['id']);
    $stmt2->execute();
    $perm_result = $stmt2->get_result();
    if ($perm_result->num_rows > 0) {
        $perm = $perm_result->fetch_assoc();
        echo "Permissions found for Dashboard: View={$perm['can_view']}, Manage={$perm['can_manage']}, Delete={$perm['can_delete']}, Export={$perm['can_export']}\n";
    } else {
        echo "NO PERMISSIONS SET FOR DASHBOARD MODULE!\n";
    }
} else {
    echo "Dashboard module NOT FOUND in database!\n";
}
?>
