<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 5;
$query = "SELECT s.score_value, cr.name as cr_name, cat.name as cat_name, comp.title as comp_title 
          FROM scores s 
          JOIN criteria cr ON s.criteria_id = cr.id 
          JOIN categories cat ON cr.category_id = cat.id 
          JOIN competitions comp ON cat.competition_id = comp.id
          WHERE s.candidate_id = $cand_id";
$res = $conn->query($query);
while($row = $res->fetch_assoc()) {
    echo "Comp: " . $row['comp_title'] . " | Cat: " . $row['cat_name'] . " | Crit: " . $row['cr_name'] . " -> Value: " . $row['score_value'] . "\n";
}
