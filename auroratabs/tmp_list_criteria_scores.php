<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 5;
$query = "SELECT cat.name as category_name, cr.name as criteria_name, cr.max_score, s.score_value 
          FROM scores s 
          JOIN criteria cr ON s.criteria_id = cr.id 
          JOIN categories cat ON cr.category_id = cat.id
          WHERE s.candidate_id = $cand_id
          ORDER BY cat.id, cr.id";
$res = $conn->query($query);
while($row = $res->fetch_assoc()) {
    echo $row['category_name'] . " | " . $row['criteria_name'] . " (Max: " . $row['max_score'] . "): " . $row['score_value'] . "\n";
}
echo "\n--- Summary by Category ---\n";
$query2 = "SELECT cat.name, SUM(s.score_value) as total 
           FROM scores s 
           JOIN criteria cr ON s.criteria_id = cr.id 
           JOIN categories cat ON cr.category_id = cat.id
           WHERE s.candidate_id = $cand_id
           GROUP BY cat.id";
$res2 = $conn->query($query2);
while($row = $res2->fetch_assoc()) {
    echo $row['name'] . ": " . $row['total'] . "\n";
}
