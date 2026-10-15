<?php
require_once 'config/db.php';
$sql = "ALTER TABLE products ADD COLUMN is_discountable TINYINT(1) DEFAULT 1 AFTER is_ingredient";
if ($conn->query($sql)) {
    echo "SUCCESS";
} else {
    echo "ERROR: " . $conn->error;
}
?>