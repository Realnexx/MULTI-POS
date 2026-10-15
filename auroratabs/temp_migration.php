<?php
require_once 'app/auth/config/db_connection.php';

$queries = [
    "ALTER TABLE users ADD COLUMN username VARCHAR(100) AFTER status",
    "ALTER TABLE users ADD COLUMN password_hint VARCHAR(255) AFTER username"
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
