<?php
require_once 'app/auth/config/db_connection.php';
$cand_id = 6;

$out = "";
function addLog($msg) {
    global $out;
    $out .= $msg . "\n";
}

// 1. Get Categories and Weights
$cats = [];
$res = $conn->query("SELECT id, name, weight_percent FROM categories WHERE competition_id = 3 AND is_deleted = 0");
while($row = $res->fetch_assoc()) {
    $cats[$row['id']] = $row;
}

addLog("--- Categories and Weights ---");
foreach($cats as $id => $cat) {
    addLog("ID: $id | Name: {$cat['name']} | Weight: {$cat['weight_percent']}%");
}

// 2. Get Criteria for each category
$criteria = [];
$res = $conn->query("SELECT id, name, category_id, weight_percent FROM criteria WHERE category_id IN (" . implode(',', array_keys($cats)) . ") AND is_deleted = 0");
while($row = $res->fetch_assoc()) {
    $criteria[$row['id']] = $row;
}

// 3. Get All Scores for Candidate 5
addLog("\n--- Individual Scores ---");
$query = "SELECT s.id, s.score_value, s.criteria_id, ja.judge_id 
          FROM scores s 
          JOIN judge_assignments ja ON s.assignment_id = ja.id 
          WHERE s.candidate_id = $cand_id";
$res = $conn->query($query);
$judge_cat_scores = [];

while($row = $res->fetch_assoc()) {
    $crit_id = $row['criteria_id'];
    if(!isset($criteria[$crit_id])) continue;
    $cat_id = $criteria[$crit_id]['category_id'];
    $j_id = $row['judge_id'];
    $val = floatval($row['score_value']);
    
    addLog("ScoreID: {$row['id']} | Judge: $j_id | Cat: {$cats[$cat_id]['name']} | Crit: {$criteria[$crit_id]['name']} | Value: $val");
    
    if(!isset($judge_cat_scores[$j_id])) $judge_cat_scores[$j_id] = [];
    if(!isset($judge_cat_scores[$j_id][$cat_id])) $judge_cat_scores[$j_id][$cat_id] = 0;
    $judge_cat_scores[$j_id][$cat_id] += $val;
}

addLog("\n--- Judge Category Totals ---");
foreach($judge_cat_scores as $j_id => $j_cats) {
    foreach($j_cats as $cat_id => $total) {
        addLog("Judge: $j_id | Category: {$cats[$cat_id]['name']} | Total: $total");
    }
}

// 4. Group by Category to see averages
addLog("\n--- Final Calculation Breakdown ---");
$overall_total = 0;
foreach($cats as $cat_id => $cat) {
    $cat_name = $cat['name'];
    $cat_weight = $cat['weight_percent'];
    
    $j_totals = [];
    foreach($judge_cat_scores as $j_id => $j_cats) {
        if(isset($j_cats[$cat_id])) {
            $j_totals[] = $j_cats[$cat_id];
        }
    }
    
    $j_count = count($j_totals);
    $avg = $j_count > 0 ? array_sum($j_totals) / $j_count : 0;
    $contribution = $avg * ($cat_weight / 100);
    $overall_total += $contribution;
    
    addLog("$cat_name (Weight: $cat_weight%):");
    addLog("  Judge Score Sums: " . implode(', ', $j_totals) . " (Count: $j_count)");
    addLog("  Average: $avg");
    addLog("  Contribution: " . number_format($contribution, 2));
}

addLog("\nFINAL TOTAL: " . number_format($overall_total, 2) . "%");

file_put_contents('tmp_exhaustive_breakdown.txt', $out);
echo "Breakdown written to tmp_exhaustive_breakdown.txt\n";
