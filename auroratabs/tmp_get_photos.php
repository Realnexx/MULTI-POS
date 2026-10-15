<?php
include 'app/auth/config/db_connection.php';
$result = $conn->query('SELECT first_name, last_name, photo_url FROM candidates WHERE photo_url IS NOT NULL LIMIT 20');
$out = fopen('c:\xampp\htdocs\auroratabs\tmp_photo_paths.txt', 'w');
while($row = $result->fetch_assoc()) {
    fwrite($out, "Candidate: " . $row['first_name'] . " " . $row['last_name'] . " - Photo: " . $row['photo_url'] . "\n");
}
fclose($out);
?>
