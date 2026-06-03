<?php
require_once __DIR__ . '/config/db.php';

echo "Starting Batch Management migration...\n";

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
    echo "- Table 'product_batches' created successfully.\n";
} else {
    die("Error creating table: " . $conn->error . "\n");
}

// 2. Add batch_id to inventory_logs for traceability
$sql = "ALTER TABLE inventory_logs ADD COLUMN batch_id INT DEFAULT NULL AFTER product_id;";
if ($conn->query($sql)) {
    echo "- Column 'batch_id' added to 'inventory_logs'.\n";
} else {
    echo "- Column 'batch_id' might already exist or error: " . $conn->error . "\n";
}

// 3. Initialize batches for existing stock (Migration)
echo "Initializing batches for existing stock...\n";
$res = $conn->query("SELECT id, stock, cost_price FROM products WHERE stock > 0");
while ($row = $res->fetch_assoc()) {
    $pid = $row['id'];
    $qty = $row['stock'];
    $cost = $row['cost_price'];
    $batch_no = "B-" . date('Ymd') . "-" . str_pad($pid, 3, '0', STR_PAD_LEFT);
    
    $stmt = $conn->prepare("INSERT INTO product_batches (product_id, batch_number, quantity, cost_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isid", $pid, $batch_no, $qty, $cost);
    $stmt->execute();
    $batch_id = $stmt->insert_id;
    $stmt->close();
    
    echo "  * Created initial batch '$batch_no' for product #$pid\n";
}

echo "Migration completed successfully!\n";
unlink(__FILE__);
