<?php
require_once 'app/auth/config/db_connection.php';
$result = $conn->query("SELECT id, first_name, last_name, photo_url FROM candidates LIMIT 10");
echo "<pre>";
while ($row = $result->fetch_assoc()) {
    echo "ID: {$row['id']} | {$row['first_name']} {$row['last_name']} | photo_url: '{$row['photo_url']}'\n";
}
echo "</pre>";
