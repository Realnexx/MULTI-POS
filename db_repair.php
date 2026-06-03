<?php
require_once __DIR__ . '/config/db.php';
header('Content-Type: text/plain');

$q1 = "CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date DATE DEFAULT NULL,
    status ENUM('active', 'depleted', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;";

if ($conn->query($q1)) {
    echo "SUCCESS: Table product_batches created.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}

$q2 = "ALTER TABLE inventory_logs ADD COLUMN IF NOT EXISTS batch_id INT DEFAULT NULL AFTER product_id;";
if ($conn->query($q2)) {
    echo "SUCCESS: batch_id column added.\n";
} else {
    echo "ERROR: " . $conn->error . "\n";
}
