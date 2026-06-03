<?php
require 'config/db.php';
global $conn;

// Create activity_logs table
$sql = "CREATE TABLE IF NOT EXISTS activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;";

$result = $conn->query($sql);
if (!$result) {
    echo "Error: " . $conn->error . "\n";
} else {
    echo "activity_logs table created or already exists.\n";
}
