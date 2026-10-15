<?php
include 'app/auth/config/db_connection.php';
$result = $conn->query('SELECT * FROM vote_packages');
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
