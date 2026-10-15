<?php
require_once 'app/auth/config/db_connection.php';

$queries = [
    "ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) AFTER password_hint",
    "ALTER TABLE users ADD COLUMN otp_expires_at TIMESTAMP NULL AFTER otp_code"
];

foreach ($queries as $sql) {
    if ($conn->query($sql)) {
        echo "Successfully executed: $sql\n";
    } else {
        echo "Error executing $sql: " . $conn->error . "\n";
    }
}

$conn->close();
?>
