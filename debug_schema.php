<?php
require_once 'config/db.php';
$res = $conn->query("DESCRIBE products");
$cols = [];
while ($row = $res->fetch_assoc()) {
    $cols[] = $row;
}
file_put_contents('db_schema_debug.json', json_encode($cols, JSON_PRETTY_PRINT));
echo "DONE";
?>