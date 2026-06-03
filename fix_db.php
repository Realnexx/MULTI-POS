<?php
require_once 'config/db.php';
global $conn;

echo "Checking for 'image' column in 'users' table...\n";
$check = $conn->query("SHOW COLUMNS FROM users LIKE 'image'");
if ($check && $check->num_rows == 0) {
    echo "Column 'image' missing. Adding it...\n";
    $result = $conn->query("ALTER TABLE users ADD COLUMN image VARCHAR(255) DEFAULT NULL AFTER password");
    if ($result) {
        echo "Successfully added 'image' column.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
} else {
    echo "Column 'image' already exists or there was an error checking it.\n";
}

echo "Checking for 'avatar_url' column (used in update_profile)...\n";
$check_avatar = $conn->query("SHOW COLUMNS FROM users LIKE 'avatar_url'");
if ($check_avatar && $check_avatar->num_rows == 0) {
    echo "Column 'avatar_url' missing. Adding it...\n";
    $result = $conn->query("ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) DEFAULT NULL");
    if ($result) {
        echo "Successfully added 'avatar_url' column.\n";
    } else {
        echo "Error adding column: " . $conn->error . "\n";
    }
}

// Also check for other columns used in update_profile but potentially missing
$cols = [
    'firstname' => "VARCHAR(100)",
    'middlename' => "VARCHAR(100)",
    'lastname' => "VARCHAR(100)",
    'extname' => "VARCHAR(10)",
    'birthdate' => "DATE",
    'bio' => "TEXT",
    'layers' => "VARCHAR(255)",
    'address' => "TEXT",
    'purok' => "VARCHAR(100)",
    'barangay' => "VARCHAR(100)",
    'town' => "VARCHAR(100)",
    'city' => "VARCHAR(100)",
    'province' => "VARCHAR(100)",
    'country' => "VARCHAR(100)",
    'zip' => "VARCHAR(20)",
    'store' => "VARCHAR(100)",
    'status' => "ENUM('online', 'offline') DEFAULT 'offline'"
];

foreach ($cols as $col => $type) {
    $c = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
    if ($c && $c->num_rows == 0) {
        echo "Adding column '$col'...\n";
        $conn->query("ALTER TABLE users ADD COLUMN $col $type");
    }
}

echo "Done.\n";
