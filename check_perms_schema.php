<?php
require_once 'config/db.php';
$result = $conn->query("DESCRIBE permissions");
if ($result) {
    while($row = $result->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Error: " . $conn->error;
}
