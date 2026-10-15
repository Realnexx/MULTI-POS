<?php
require_once 'config/db.php';

$queries = [
    "ALTER TABLE products ADD COLUMN sku VARCHAR(50) DEFAULT NULL AFTER name",
    "ALTER TABLE products ADD COLUMN barcode VARCHAR(50) DEFAULT NULL AFTER sku",
    "ALTER TABLE products ADD COLUMN status ENUM('active', 'inactive') DEFAULT 'active' AFTER image_url",
    "CREATE INDEX idx_products_sku ON products(sku)",
    "CREATE INDEX idx_products_barcode ON products(barcode)"
];

foreach ($queries as $q) {
    if ($conn->query($q)) {
        echo "Executed: $q\n";
    } else {
        echo "Error executing $q: " . $conn->error . "\n";
    }
}
