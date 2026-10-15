<?php
require_once 'app/auth/config/db_connection.php';
$result = $conn->query("DESCRIBE categories");
while($row = $result->fetch_assoc()) {
    print_r($row);
}
?>
