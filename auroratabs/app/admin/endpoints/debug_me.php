<?php
require_once '../../auth/config/db_connection.php';

$sql = "ALTER TABLE competitions ADD COLUMN venue VARCHAR(255) DEFAULT 'Ubay Cultural Center' AFTER title";
$sql2 = "ALTER TABLE competitions ADD COLUMN description TEXT AFTER venue";

if ($conn->query($sql) === TRUE) {
    echo "Venue column added successfully\n";
} else {
    echo "Error adding venue: " . $conn->error . "\n";
}

if ($conn->query($sql2) === TRUE) {
    echo "Description column added successfully\n";
} else {
    echo "Error adding description: " . $conn->error . "\n";
}
?>
