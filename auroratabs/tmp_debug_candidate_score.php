<?php
require_once 'app/auth/config/db_connection.php';
require_once 'app/auth/helpers/formula_engine.php';

$logFile = 'tmp_candidate_score_detailed.txt';
$log = "";

function writeLog($msg) {
    global $log;
    $log .= $msg . "\n";
}

// Specifically Target Candidate 5 ("Candidate 1 Test")
$query = "SELECT c.id as candidate_id, c.first_name, c.last_name, c.competition_id, comp.title as competition_title, comp.formula_id 
          FROM candidates c 
          JOIN competitions comp ON c.competition_id = comp.id
          WHERE c.id = 5
          LIMIT 1";
$res = $conn->query($query);
$candidate = $res->fetch_assoc();

if (!$candidate) {
    writeLog("Candidate 5 not found");
    file_put_contents($logFile, $log);
    die();
}

writeLog("Found Candidate: " . $candidate['first_name'] . " " . $candidate['last_name'] . " (ID: " . $candidate['candidate_id'] . ") in Competition: " . $candidate['competition_title']);

$comp_id = $candidate['competition_id'];
$cand_id = $candidate['candidate_id'];

// Get Active Formula
$form_query = "SELECT f.name, f.expression 
               FROM formulas f
               JOIN competitions c ON c.formula_id = f.id
               WHERE c.id = ? AND f.is_active = 1";
$stmt = $conn->prepare($form_query);
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$formula = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$formula) {
    writeLog("No specific formula for competition, checking global active...");
    $formula = $conn->query("SELECT name, expression FROM formulas WHERE is_active = 1 ORDER BY id DESC LIMIT 1")->fetch_assoc();
}

if ($formula) {
    writeLog("Using Formula: " . $formula['name'] . " -> " . $formula['expression']);
} else {
    writeLog("No formula found!");
}

// Get Categories
$cat_query = "SELECT id, name, weight_percent FROM categories WHERE competition_id = ? AND is_deleted = 0";
$stmt = $conn->prepare($cat_query);
$stmt->bind_param("i", $comp_id);
$stmt->execute();
$categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

writeLog("\n--- Calculation Breakdown ---");
$total_score = 0;

foreach ($categories as $cat) {
    $cat_id = $cat['id'];
    $weight = floatval($cat['weight_percent']);

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
    $stmt->close();

    $sum_scores = array_sum($judge_scores);
    $j_count = count($judge_scores);
    $avg_score = $j_count > 0 ? $sum_scores / $j_count : 0;
    
    $cat_score = 0;
    if ($formula) {
        $engine = new FormulaEngine($formula['expression']);
        $context = [
            'category_weight' => $weight,
            'judge_count' => $j_count,
            'criteria_score' => $sum_scores,
            'criteria_weighted' => $sum_scores * ($weight / 100),
            'category_score' => $avg_score,
            'category_weighted' => $avg_score * ($weight / 100),
            'total_category_score' => $avg_score,
            'total_criteria_score' => $sum_scores,
            'average_score' => $avg_score,
            'total_score' => $avg_score * ($weight / 100)
        ];
        $cat_score = $engine->evaluate($judge_scores, $context);
        writeLog("  Context used: " . json_encode($context));
    } else {
        $cat_score = $avg_score * ($weight / 100);
    }
    
    writeLog("Category: " . $cat['name'] . " (Weight: $weight%)");
    writeLog("  Judge Totals (SUM of criteria per judge): " . implode(", ", $judge_scores) . " (Count: $j_count)");
    writeLog("  AVG of Judge Totals: $avg_score");
    writeLog("  Category Contribution to Total (Cat Final Score): $cat_score");
    
    $total_score += $cat_score;
}

writeLog("\nTotal Overall Score (Sum of Category scores): $total_score");
writeLog("Formatted: " . number_format($total_score, 2) . "%");

file_put_contents($logFile, $log);
echo "Log written to $logFile\n";
