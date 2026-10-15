<?php
require_once 'app/auth/config/db_connection.php';
require_once 'app/auth/helpers/formula_engine.php';

$comp_id = 3; // Matahum sa Union
$res = $conn->query("SELECT id, first_name, last_name, candidate_number FROM candidates WHERE competition_id = $comp_id AND is_deleted = 0");

echo "Results for Competition 3:\n";

while($cand = $res->fetch_assoc()) {
    $cand_id = $cand['id'];
    $total = 0;
    
    $cats = $conn->query("SELECT id, name, weight_percent FROM categories WHERE competition_id = $comp_id");
    while($cat = $cats->fetch_assoc()) {
        $cat_id = $cat['id'];
        $weight = $cat['weight_percent'];
        
        $scores_q = $conn->query("SELECT SUM(s.score_value) as j_total 
                                  FROM scores s 
                                  JOIN judge_assignments ja ON s.assignment_id = ja.id 
                                  JOIN criteria cr ON s.criteria_id = cr.id 
                                  WHERE s.candidate_id = $cand_id AND cr.category_id = $cat_id 
                                  GROUP BY ja.judge_id");
        $j_scores = [];
        while($s = $scores_q->fetch_assoc()) $j_scores[] = $s['j_total'];
        
        $avg = count($j_scores) > 0 ? array_sum($j_scores) / count($j_scores) : 0;
        $cat_score = $avg * ($weight / 100);
        $total += $cat_score;
    }
    
    echo "#" . $cand['candidate_number'] . " " . $cand['first_name'] . " " . $cand['last_name'] . " (ID: $cand_id): " . number_format($total, 2) . "%\n";
}
