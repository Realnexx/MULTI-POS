<?php
include 'app/auth/config/db_connection.php';
$result = $conn->query('DESCRIBE votes');
$out = fopen('c:\xampp\htdocs\auroratabs\tmp_votes_schema.txt', 'w');
while($row = $result->fetch_assoc()) {
    fwrite($out, print_r($row, true));
}
fclose($out);
?>
