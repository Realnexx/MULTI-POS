<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 5;
$query = "SELECT DISTINCT cat.id, cat.name, cat.weight_percent 
          FROM scores s 
          JOIN criteria cr ON s.criteria_id = cr.id 
          JOIN categories cat ON cr.category_id = cat.id 
          WHERE s.candidate_id = $cand_id";
$res = $conn->query($query);
while($row = $res->fetch_assoc()) {
    echo "Cat ID: " . $row['id'] . " | Name: " . $row['name'] . " | Weight: " . $row['weight_percent'] . "%\n";
}
echo "\n--- ANY OTHER CATEGORIES FOR COMP 3? ---\n";
$res2 = $conn->query("SELECT id, name, weight_percent FROM categories WHERE competition_id = 3 AND is_deleted = 0");
while($row = $res2->fetch_assoc()) {
    echo "  Cat ID: " . $row['id'] . " | Name: " . $row['name'] . " | Weight: " . $row['weight_percent'] . "%\n";
}
