<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 5;
$query = "SELECT s.id, s.score_value, cr.name as criteria_name, cat.name as category_name, ja.judge_id 
          FROM scores s 
          JOIN criteria cr ON s.criteria_id = cr.id 
          JOIN categories cat ON cr.category_id = cat.id 
          JOIN judge_assignments ja ON s.assignment_id = ja.id
          WHERE s.candidate_id = $cand_id";
$res = $conn->query($query);
while($row = $res->fetch_assoc()) {
    echo "Score ID: " . $row['id'] . " | Judge: " . $row['judge_id'] . " | Cat: " . $row['category_name'] . " | Crit: " . $row['criteria_name'] . " -> Value: " . $row['score_value'] . "\n";
}
