<?php
require 'app/auth/config/db_connection.php';
$res = $conn->query("DESCRIBE competitions");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . "\n";
}
echo "\nTABLES:\n";
$res = $conn->query("SHOW TABLES");
while($row = $res->fetch_array()) {
    echo $row[0] . "\n";
}
