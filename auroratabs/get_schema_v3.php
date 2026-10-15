<?php
require_once 'app/auth/config/db_connection.php';
$result = $conn->query("DESCRIBE categories");
$schema = [];
while($row = $result->fetch_assoc()) {
    $schema[] = $row;
}
file_put_contents('schema_output.json', json_encode($schema, JSON_PRETTY_PRINT));
?>
