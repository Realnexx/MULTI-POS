<?php
require_once 'app/auth/config/db_connection.php';

$res = $conn->query("DESCRIBE users");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

$conn->close();
?>
