<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 5;
$query = "SELECT cat.name as cat_name, cr.name as cr_name, cr.weight_percent as weight, s.score_value 
          FROM criteria cr 
          JOIN categories cat ON cr.category_id = cat.id 
          LEFT JOIN scores s ON s.criteria_id = cr.id AND s.candidate_id = $cand_id
          WHERE cat.competition_id = (SELECT competition_id FROM candidates WHERE id = $cand_id)
          ORDER BY cat.display_order, cr.display_order";
$res = $conn->query($query);
while($row = $res->fetch_assoc()) {
    echo $row['cat_name'] . " | " . $row['cr_name'] . " (Weight: " . $row['weight'] . "%): " . ($row['score_value'] ?? 'N/A') . "\n";
}
