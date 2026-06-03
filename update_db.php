<?php
require 'config/db.php';
global $conn;

// Try to add username column
$result = $conn->query("ALTER TABLE users ADD COLUMN username VARCHAR(50) UNIQUE AFTER name");
if (!$result) {
    echo "Add column error: " . $conn->error . "\n";
} else {
    echo "Added username column\n";
}

// Try to update existing users
$result2 = $conn->query("UPDATE users SET username = SUBSTRING_INDEX(email, '@', 1) WHERE username IS NULL");
if (!$result2) {
    echo "Update error: " . $conn->error . "\n";
} else {
    echo "Updated existing users\n";
}
