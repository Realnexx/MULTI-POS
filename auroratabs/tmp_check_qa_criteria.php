<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("SELECT id FROM categories WHERE competition_id = 3 AND name LIKE '%Question%'");
$cat = $res->fetch_assoc();
$cat_id = $cat['id'];
echo "Q&A Category ID: $cat_id\n";

$res2 = $conn->query("SELECT id, name, weight_percent FROM criteria WHERE category_id = $cat_id AND is_deleted = 0");
while($row = $res2->fetch_assoc()) {
    echo "  Criteria ID: " . $row['id'] . " | Name: " . $row['name'] . " | Weight: " . $row['weight_percent'] . "%\n";
}

$res3 = $conn->query("SELECT id, name, weight_percent FROM categories WHERE competition_id = 3 AND is_deleted = 0");
echo "\nCategories in Comp 3:\n";
while($row = $res3->fetch_assoc()) {
    echo "  Cat ID: " . $row['id'] . " | Name: " . $row['name'] . " | Weight: " . $row['weight_percent'] . "%\n";
}
