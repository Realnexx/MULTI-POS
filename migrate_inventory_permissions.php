<?php
require_once __DIR__ . '/config/db.php';

$new_permissions = [
    ['add_product', 'Add new products to the catalog', 'Inventory'],
    ['edit_product', 'Edit existing product details', 'Inventory'],
    ['delete_product', 'Remove products from the catalog', 'Inventory'],
    ['manage_inventory', 'Adjust stock levels and view logs', 'Inventory'],
    ['print_products', 'Export or print product lists', 'Inventory'],
    ['view_product', 'View product list and details', 'Inventory']
];

echo "Starting permission migration...\n";

$stmt = $conn->prepare("INSERT IGNORE INTO permissions (name, description, module) VALUES (?, ?, ?)");
foreach ($new_permissions as $perm) {
    $stmt->bind_param("sss", $perm[0], $perm[1], $perm[2]);
    if ($stmt->execute()) {
        echo "Ensured permission: {$perm[0]}\n";
    } else {
        echo "Error adding permission {$perm[0]}: " . $conn->error . "\n";
    }
}
$stmt->close();

// Assign all new permissions to the Admin role
$admin_role_query = $conn->query("SELECT id FROM roles WHERE name = 'Admin'");
if ($admin_role_query && $admin_role_query->num_rows > 0) {
    $admin_role_id = $admin_role_query->fetch_assoc()['id'];
    
    $perm_names = array_column($new_permissions, 0);
    $placeholders = implode(',', array_fill(0, count($perm_names), '?'));
    $types = str_repeat('s', count($perm_names));
    
    $sel_stmt = $conn->prepare("SELECT id FROM permissions WHERE name IN ($placeholders)");
    $sel_stmt->bind_param($types, ...$perm_names);
    $sel_stmt->execute();
    $res = $sel_stmt->get_result();
    
    $assign_stmt = $conn->prepare("INSERT IGNORE INTO role_permissions (role_id, permission_id) VALUES (?, ?)");
    while ($row = $res->fetch_assoc()) {
        $assign_stmt->bind_param("ii", $admin_role_id, $row['id']);
        $assign_stmt->execute();
    }
    $assign_stmt->close();
    $sel_stmt->close();
    echo "Assigned granular permissions to Admin role.\n";
} else {
    echo "Admin role not found.\n";
}

echo "Migration completed.\n";
