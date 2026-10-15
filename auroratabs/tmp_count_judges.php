<?php
require_once 'app/auth/config/db_connection.php';
$query = "SELECT COUNT(DISTINCT ja.judge_id) as j_count 
          FROM scores s 
          JOIN judge_assignments ja ON s.assignment_id = ja.id 
          JOIN criteria cr ON s.criteria_id = cr.id 
          JOIN categories cat ON cr.category_id = cat.id 
          WHERE cat.competition_id = 3";
$res = $conn->query($query);
$row = $res->fetch_assoc();
echo "Judges who voted in Comp 3: " . $row['j_count'] . "\n";

$query2 = "SELECT DISTINCT ja.judge_id FROM judge_assignments ja WHERE ja.competition_id = 3";
$res2 = $conn->query($query2);
echo "Judges assigned to Comp 3: ";
while($row2 = $res2->fetch_assoc()) echo $row2['judge_id'] . " ";
echo "\n";
