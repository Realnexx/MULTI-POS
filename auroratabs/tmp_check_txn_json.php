<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("DESCRIBE transactions");
$schema = [];
while($row = $res->fetch_assoc()) $schema[] = $row;
echo json_encode($schema, JSON_PRETTY_PRINT);
?>
