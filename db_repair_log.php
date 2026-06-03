<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once __DIR__ . '/config/db.php';

$output = "REPAIR LOG - " . date('Y-m-d H:i:s') . "\n";

// Table 1
$res1 = $conn->query("CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date DATE DEFAULT NULL,
    status ENUM('active', 'depleted', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$output .= "Table product_batches: " . ($res1 ? "OK" : "ERROR: " . $conn->error) . "\n";

// Column
$res2 = $conn->query("ALTER TABLE inventory_logs ADD COLUMN batch_id INT DEFAULT NULL AFTER product_id");
$output .= "Column batch_id: " . ($res2 ? "OK" : "ERROR/ALREADY EXISTS: " . $conn->error) . "\n";

file_put_contents('repair_log.txt', $output);
echo "DONE. Check repair_log.txt";
