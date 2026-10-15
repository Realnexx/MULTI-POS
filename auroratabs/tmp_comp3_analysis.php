<?php
require_once 'app/auth/config/db_connection.php';

$out = "";
function addLog($msg) {
    global $out;
    echo $msg . "\n";
    $out .= $msg . "\n";
}

$comp_id = 3;

// 1. All categories
$cats = [];
$res = $conn->query("SELECT id, name, weight_percent FROM categories WHERE competition_id = $comp_id AND is_deleted = 0 ORDER BY display_order");
while($row = $res->fetch_assoc()) {
    $cats[$row['id']] = $row;
}

addLog("--- Categories ---");
foreach($cats as $cat) {
    addLog("ID: {$cat['id']} | Name: {$cat['name']} | Weight: {$cat['weight_percent']}%");
}

// 2. All criteria
addLog("\n--- Criteria ---");
$res = $conn->query("SELECT id, category_id, name, weight_percent FROM criteria WHERE category_id IN (" . implode(',', array_keys($cats)) . ") AND is_deleted = 0 ORDER BY category_id, display_order");
while($row = $res->fetch_assoc()) {
    addLog("Cat: {$cats[$row['category_id']]['name']} | CritID: {$row['id']} | Name: {$row['name']} | Weight: {$row['weight_percent']}%");
}

// 3. All Candidates and their overall scores
addLog("\n--- Candidates Results ---");
$res = $conn->query("SELECT id, first_name, last_name, candidate_number FROM candidates WHERE competition_id = $comp_id AND is_deleted = 0");
while($cand = $res->fetch_assoc()) {
    $cand_id = $cand['id'];
    $total = 0;
    
    foreach($cats as $cat_id => $cat) {
        $j_totals = [];
        $scores_q = $conn->query("SELECT SUM(s.score_value) as j_total 
                                  FROM scores s 
                                  JOIN judge_assignments ja ON s.assignment_id = ja.id 
                                  JOIN criteria cr ON s.criteria_id = cr.id 
                                  WHERE s.candidate_id = $cand_id AND cr.category_id = $cat_id 
                                  GROUP BY ja.judge_id");
        while($s = $scores_q->fetch_assoc()) $j_totals[] = $s['j_total'];
        
        $avg = count($j_totals) > 0 ? array_sum($j_totals) / count($j_totals) : 0;
        $total += $avg * ($cat['weight_percent'] / 100);
    }
    addLog("#{$cand['candidate_number']} {$cand['first_name']} {$cand['last_name']} (ID: $cand_id): " . number_format($total, 2) . "%");
}

file_put_contents('tmp_comp3_analysis.txt', $out);
