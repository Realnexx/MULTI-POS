<?php
require_once __DIR__ . '/config/db.php';

echo "DEBUG: Starting Batch Management migration...\n";

// 1. Create product_batches table
$sql = "CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date DATE DEFAULT NULL,
    status ENUM('active', 'depleted', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

if ($conn->query($sql)) {
    echo "DEBUG: Table 'product_batches' created/exists.\n";
} else {
    echo "DEBUG ERROR: " . $conn->error . "\n";
}

// 2. Add batch_id to inventory_logs
$sql = "ALTER TABLE inventory_logs ADD COLUMN IF NOT EXISTS batch_id INT DEFAULT NULL AFTER product_id;";
if ($conn->query($sql)) {
    echo "DEBUG: Column 'batch_id' processed.\n";
} else {
    echo "DEBUG ERROR ALTER: " . $conn->error . "\n";
}
echo "DEBUG DONE.\n";
