<?php
require_once 'config/db.php';
// Check product_recipes
$r = $conn->query("DESCRIBE product_recipes");
while ($row = $r->fetch_assoc()) {
    echo $row['Field'] . " (" . $row['Type'] . ")\n";
}
?>