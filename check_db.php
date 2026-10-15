<?php
require_once 'config/db.php';
$result = $conn->query("DESCRIBE products");
$columns = $result->fetch_all(MYSQLI_ASSOC);
echo json_encode($columns, JSON_PRETTY_PRINT);
?>