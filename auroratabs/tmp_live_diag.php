<?php
session_start();
header("Content-Type: application/json");
require 'app/auth/config/db_connection.php';
require 'app/auth/helpers/security_helper.php';
require 'app/auth/helpers/formula_engine.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$comp_id = 3;

$comp_query = "SELECT title FROM competitions WHERE id = ?";
$stmt = $conn->prepare($comp_query);
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$comp = $stmt->get_result()->fetch_assoc();
if (!$comp) die("Competition not found");

$form_query = "SELECT f.expression 
               FROM formulas f
               JOIN competitions c ON c.formula_id = f.id
               WHERE c.id = ? AND f.is_active = 1";
$stmt = $conn->prepare($form_query);
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$formula_res = $stmt->get_result()->fetch_assoc();
$formula_expression = $formula_res ? $formula_res['expression'] : null;

$cat_query = "SELECT id, name, weight_percent FROM categories WHERE competition_id = ? AND is_deleted = 0";
$stmt = $conn->prepare($cat_query);
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$cand_query = "SELECT id, CONCAT(first_name, ' ', last_name) as name, candidate_number as number FROM candidates WHERE competition_id = ? AND is_deleted = 0 ORDER BY candidate_number ASC";
$stmt = $conn->prepare($cand_query);
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo "Comp: {$comp['title']}, Categories: " . count($categories) . ", Candidates: " . count($candidates) . "\n";

$results = [];
foreach ($candidates as $cand) {
    echo "Processing Cand {$cand['id']}...\n";
    $cand_id = $cand['id'];
    $total_score = 0;
    
    foreach ($categories as $cat) {
        $cat_id = $cat['id'];
        $weight = floatval($cat['weight_percent']);

        // Check locks
        $l_query = "SELECT is_locked FROM judge_locks WHERE competition_id = ? AND category_id = ? AND candidate_id IS NULL";
        $l_stmt = $conn->prepare($l_query);
        $l_stmt->bind_param("ii", $comp_id, $cat_id);
        $l_stmt->execute();
        $is_locked = $l_stmt->get_result()->fetch_assoc();
        
        $j_query = "SELECT SUM(s.score_value) as judge_total 
                    FROM scores s
                    JOIN judge_assignments ja ON s.assignment_id = ja.id
                    JOIN criteria cr ON s.criteria_id = cr.id
                    WHERE s.candidate_id = ? AND cr.category_id = ?
                    GROUP BY ja.judge_id";
        $stmt = $conn->prepare($j_query);
        $stmt->bind_param("ii", $cand_id, $cat_id);
        $stmt->execute();
        $j_res = $stmt->get_result();
        
        $judge_scores = [];
        while($row = $j_res->fetch_assoc()) {
            $judge_scores[] = floatval($row['judge_total']);
        }
        
        $cat_score = 0;
        if ($formula_expression) {
            $sum_scores = array_sum($judge_scores);
            $j_count = count($judge_scores);
            $avg_score = $j_count > 0 ? $sum_scores / $j_count : 0;
            
            $engine = new FormulaEngine($formula_expression);
            $context = [
                'category_weight' => $weight,
                'judge_count' => $j_count,
                'criteria_score' => $sum_scores,
                'category_score' => $avg_score,
                'average_score' => $avg_score,
                'total_score' => $avg_score * ($weight / 100)
            ];
            $cat_score = $engine->evaluate($judge_scores, $context);
        }
        $total_score += $cat_score;
    }
    
    $results[] = [
        'id' => $cand['id'],
        'name' => $cand['name'],
        'total_score' => $total_score
    ];
}

print_r($results);
?>
