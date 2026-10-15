<?php
require_once 'app/auth/config/db_connection.php';

$res = $conn->query("SELECT * FROM settings WHERE `group` IN ('system', 'email')");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

$conn->close();
?>
