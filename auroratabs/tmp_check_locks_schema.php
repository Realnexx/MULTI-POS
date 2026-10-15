<?php
require 'app/auth/config/db_connection.php';
$res = $conn->query("DESCRIBE judge_locks");
while($row = $res->fetch_assoc()) {
    echo $row['Field'] . " - " . $row['Type'] . "\n";
}
