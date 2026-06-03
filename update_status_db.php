<?php
require 'config/db.php';
global $conn;

// Try to add status column
$result = $conn->query("ALTER TABLE users ADD COLUMN status ENUM('online', 'offline') DEFAULT 'offline' AFTER password");
if (!$result) {
    echo "Add column error: " . $conn->error . "\n";
} else {
    echo "Added status column\n";
}
