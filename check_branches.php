<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config/db.php';
$res = $conn->query("SELECT * FROM branches");
if (!$res) {
    die("Query failed: " . $conn->error);
}
while($row = $res->fetch_assoc()) {
    echo $row['id'] . ": " . $row['name'] . "\n";
}
echo "Done\n";
