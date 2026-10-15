<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("DESCRIBE transactions");
while($row = $res->fetch_assoc()) print_r($row);
$res = $conn->query("SELECT * FROM transactions LIMIT 1");
print_r($res->fetch_assoc());
?>
