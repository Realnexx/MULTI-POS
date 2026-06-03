<?php
require_once 'config/db.php';
global $conn;

$check = $conn->query("SHOW COLUMNS FROM users LIKE 'deleted_at'");
if ($check && $check->num_rows == 0) {
    echo "Adding 'deleted_at' column to 'users' table...\n";
    $result = $conn->query("ALTER TABLE users ADD COLUMN deleted_at DATETIME DEFAULT NULL");
    if ($result) {
        echo "Successfully added 'deleted_at' column.\n";
    } else {
        echo "Error: " . $conn->error . "\n";
    }
} else {
    echo "Column 'deleted_at' already exists.\n";
}
