<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("SELECT payment_gateway, COUNT(*) as count FROM transactions GROUP BY payment_gateway");
$data = [];
while($row = $res->fetch_assoc()) $data[] = $row;
echo json_encode($data, JSON_PRETTY_PRINT);
?>
