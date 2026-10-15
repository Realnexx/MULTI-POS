<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 5;
$query = "SELECT comp.id, comp.title, (SELECT COUNT(*) FROM judge_assignments ja WHERE ja.competition_id = comp.id) as judge_count 
          FROM candidates c 
          JOIN competitions comp ON c.competition_id = comp.id 
          WHERE c.id = $cand_id";
$res = $conn->query($query);
$row = $res->fetch_assoc();
echo "Competition: " . $row['title'] . " (ID: " . $row['id'] . ")\n";
echo "Judge Count (Assigned): " . $row['judge_count'] . "\n";

$query2 = "SELECT COUNT(DISTINCT ja.judge_id) as voting_judges 
           FROM scores s 
           JOIN judge_assignments ja ON s.assignment_id = ja.id 
           WHERE s.candidate_id = $cand_id";
$res2 = $conn->query($query2);
$row2 = $res2->fetch_assoc();
echo "Judges who voted for this candidate: " . $row2['voting_judges'] . "\n";
