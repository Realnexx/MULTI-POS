<?php
require_once 'config/db.php';

$cols = [
    "inventory_type" => "ENUM('stocked', 'non-stock', 'ingredient-based', 'unlimited') DEFAULT 'stocked'",
    "json_attributes" => "JSON DEFAULT NULL",
    "unit_type" => "VARCHAR(50) DEFAULT 'pc'"
];

foreach ($cols as $col => $def) {
    echo "Adding $col... ";
    $res = $conn->query("ALTER TABLE products ADD COLUMN $col $def");
    if ($res) {
        echo "SUCCESS\n";
    } else {
        echo "ERROR: " . $conn->error . "\n";
    }
}
?>