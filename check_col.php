<?php
require_once 'config/db.php';
$result = $conn->query("SHOW COLUMNS FROM products LIKE 'is_discountable'");
if ($result->num_rows > 0) {
    echo "EXISTS";
} else {
    echo "MISSING";
}
?>