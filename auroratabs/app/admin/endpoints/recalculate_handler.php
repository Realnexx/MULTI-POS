<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';
require_once '../../auth/helpers/log_helper.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$action = sanitize($_POST['action'] ?? '');
$current_user_id = $_SESSION['user_id'] ?? 0;

if ($current_user_id == 0) {
    die(json_encode(["status" => "error", "message" => "Unauthorized"]));
}

require_once '../../auth/helpers/formula_engine.php';

switch ($action) {
    case 'get_competitions':
        $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY id DESC";
        $result = $conn->query($query);
        $data = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'get_history':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $where = "";
        if($comp_id > 0) {
            $where = "WHERE rh.competition_id = $comp_id";
        }
        
        $query = "SELECT rh.*, c.title as competition_title, 
                  CONCAT(u.first_name, ' ', u.last_name) as triggered_by_name
                  FROM recalculation_history rh
                  JOIN competitions c ON rh.competition_id = c.id
                  LEFT JOIN users u ON rh.triggered_by = u.id
                  $where
                  ORDER BY rh.created_at DESC LIMIT 50";
        
        $result = $conn->query($query);
        $data = [];
        if($result) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'recalculate':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        if ($comp_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid Competition ID"]));
        }

        $conn->begin_transaction();
        try {
            // 0. Get Competition Title & formula Expression
            $comp_query = "SELECT c.title, f.expression as formula_expression 
                           FROM competitions c
                           LEFT JOIN formulas f ON c.formula_id = f.id
                           WHERE c.id = ? AND (f.is_active = 1 OR f.is_active IS NULL)";
            $c_stmt = $conn->prepare($comp_query);
            $c_stmt->bind_param("i", $comp_id);
            $c_stmt->execute();
            $comp_row = $c_stmt->get_result()->fetch_assoc();
            $comp_title = $comp_row['title'] ?? 'Unknown Competition';
            $formula_expression = $comp_row['formula_expression'] ?? null;

            if (!$formula_expression) {
                // Fallback: try finding any active formula as a generic default
                $f_fb_query = "SELECT expression FROM formulas WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
                $stmt = $conn->prepare($f_fb_query);
                $stmt->execute();
                $fb_res = $stmt->get_result()->fetch_assoc();
                $formula_expression = $fb_res ? $fb_res['expression'] : null;
                $stmt->close();
            }

            // 1. Get Categories and Weights
            $cat_query = "SELECT id, weight_percent FROM categories WHERE competition_id = ? AND is_deleted = 0";
            $stmt = $conn->prepare($cat_query);
            $stmt->bind_param("i", $comp_id);
            $stmt->execute();
            $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            // 2. Get Candidates
            $cand_query = "SELECT id FROM candidates WHERE competition_id = ? AND is_deleted = 0";
            $stmt = $conn->prepare($cand_query);
            $stmt->bind_param("i", $comp_id);
            $stmt->execute();
            $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

            $final_scores = [];

            // 3. For each candidate, calculate score
            foreach ($candidates as $cand) {
                $cand_id = $cand['id'];
                $total_weighted_score = 0;
                $details_log = [];

                foreach ($categories as $cat) {
                    $cat_id = $cat['id']; 
                    $weight = floatval($cat['weight_percent']);
                    
                    // Get all judges' total scores for this candidate in this category
                    // Judge Total = Sum of criteria scores
                    $score_query = "SELECT 
                                        ja.judge_id, 
                                        SUM(s.score_value) as judge_total 
                                    FROM scores s
                                    JOIN judge_assignments ja ON s.assignment_id = ja.id
                                    JOIN criteria cr ON s.criteria_id = cr.id
                                    WHERE s.candidate_id = ? AND cr.category_id = ?
                                    GROUP BY ja.judge_id";
                                    
                    $s_stmt = $conn->prepare($score_query);
                    $s_stmt->bind_param("ii", $cand_id, $cat_id);
                    $s_stmt->execute();
                    $judge_scores = $s_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

                    if (count($judge_scores) > 0) {
                        $sum_judge_scores = 0;
                        $j_scores_arr = [];
                        foreach ($judge_scores as $js) {
                            $j_scores_arr[] = floatval($js['judge_total']);
                            $sum_judge_scores += floatval($js['judge_total']);
                        }
                        $avg_score = $sum_judge_scores / count($judge_scores);
                    } else {
                        $avg_score = 0;
                        $j_scores_arr = [];
                        $sum_judge_scores = 0;
                    }

                    if ($formula_expression) {
                        $j_count = count($j_scores_arr);
                        $engine = new FormulaEngine($formula_expression);
                        $context = [
                            'category_weight' => $weight,
                            'judge_count' => $j_count,
                            'criteria_score' => $sum_judge_scores,
                            'criteria_weighted' => $sum_judge_scores * ($weight / 100),
                            'category_score' => $avg_score,
                            'category_weighted' => $avg_score * ($weight / 100),
                            'total_category_score' => $avg_score,
                            'total_criteria_score' => $sum_judge_scores,
                            'average_score' => $avg_score,
                            'total_score' => $avg_score * ($weight / 100)
                        ];
                        $cat_score = $engine->evaluate($j_scores_arr, $context);
                    } else {
                        $cat_score = $avg_score * ($weight / 100);
                    }
                    
                    $total_weighted_score += $cat_score;
                }

                $final_scores[] = [
                    'candidate_id' => $cand_id,
                    'score' => $total_weighted_score
                ];
            }

            // 4. Sort and Rank
            usort($final_scores, function($a, $b) {
                return $b['score'] <=> $a['score'];
            });

            // 5. Clear old results
            $del_stmt = $conn->prepare("DELETE FROM final_scores WHERE competition_id = ?");
            $del_stmt->bind_param("i", $comp_id);
            $del_stmt->execute();

            // 6. Insert new results
            $ins_stmt = $conn->prepare("INSERT INTO final_scores (competition_id, candidate_id, score, rank) VALUES (?, ?, ?, ?)");
            $rank = 1;
            $prev_score = -1;
            $actual_rank = 1;
            $epsilon = 0.0001;
            
            foreach ($final_scores as $index => $fs) {
                // Handle ties with precision
                if ($prev_score != -1 && abs($fs['score'] - $prev_score) > $epsilon) {
                    $rank = $actual_rank;
                }
                
                $ins_stmt->bind_param("iidi", $comp_id, $fs['candidate_id'], $fs['score'], $rank);
                $ins_stmt->execute();
                
                $prev_score = $fs['score'];
                $actual_rank++;
            }

            // 7. Log History
            $log_stmt = $conn->prepare("INSERT INTO recalculation_history (competition_id, triggered_by, status, details) VALUES (?, ?, 'success', ?)");
            $cand_count = count($final_scores);
            $details = "Recalculated scores for $cand_count candidates in $comp_title (ID: $comp_id)";
            $log_stmt->bind_param("iis", $comp_id, $current_user_id, $details);
            $log_stmt->execute();

            $conn->commit();
            
            // System Log
            $sys_log_details = "Manual recalculation for Competition ID: $comp_id - $comp_title. Processed $cand_count candidates.";
            log_activity($conn, $current_user_id, 'Recalculate Results', 'System', $sys_log_details);

            echo json_encode(["status" => "success", "message" => "Results recalculated successfully."]);

        } catch (Exception $e) {
            $conn->rollback();
            
            // Log Failure
            try {
                $log_stmt = $conn->prepare("INSERT INTO recalculation_history (competition_id, triggered_by, status, details) VALUES (?, ?, 'failed', ?)");
                $error_msg = $e->getMessage();
                $log_stmt->bind_param("iis", $comp_id, $current_user_id, $error_msg);
                $log_stmt->execute();
            } catch (Exception $logEx) {
                 // Ignore logging error if DB is totally broken
            }
            
            echo json_encode(["status" => "error", "message" => "Calculation failed: " . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
