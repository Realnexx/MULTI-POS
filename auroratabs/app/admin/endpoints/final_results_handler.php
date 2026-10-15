<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

// disable error reporting for production output, but log errors
// disable error reporting for production output, but log errors
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$action = sanitize($_POST['action'] ?? '');
$comp_id = intval($_POST['competition_id'] ?? 0);

require_once '../../auth/helpers/formula_engine.php';

switch ($action) {
    case 'get_competitions':
        $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY id DESC";
        $result = $conn->query($query);
        $data = [];
        if($result) {
            $data = $result->fetch_all(MYSQLI_ASSOC);
        }
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'get_final_results':
        if ($comp_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid Competition ID"]);
            exit;
        }

        // 1. Get Competition Info & Active Formula
        // Get Competition Info
        $comp_query = "SELECT title, venue, description, DATE_FORMAT(created_at, '%M %d, %Y') as date FROM competitions WHERE id = ?";
        $stmt = $conn->prepare($comp_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $comp_res = $stmt->get_result();
        $comp = $comp_res->fetch_assoc();
        $stmt->close();
        
        if (!$comp) {
            echo json_encode(["status" => "error", "message" => "Competition not found"]);
            exit;
        }

        // Get Active Formula (prefer competition-linked formula, fallback to first active for this comp if exists)
        $form_query = "SELECT f.expression 
                       FROM formulas f
                       JOIN competitions c ON c.formula_id = f.id
                       WHERE c.id = ? AND f.is_active = 1";
        $stmt = $conn->prepare($form_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $formula_res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        $formula_expression = $formula_res ? $formula_res['expression'] : null;

        if (!$formula_expression) {
            // Fallback: try finding any active formula as a generic default
            $f_fb_query = "SELECT expression FROM formulas WHERE is_active = 1 ORDER BY id DESC LIMIT 1";
            $stmt = $conn->prepare($f_fb_query);
            $stmt->execute();
            $fb_res = $stmt->get_result()->fetch_assoc();
            $formula_expression = $fb_res ? $fb_res['expression'] : null;
            $stmt->close();
        }

        // Get Judges
        $judge_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, 'Judge' as role
                        FROM users u
                        JOIN judge_assignments ja ON u.id = ja.judge_id
                        WHERE ja.competition_id = ?
                        GROUP BY u.id";
        $stmt = $conn->prepare($judge_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $judges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get Tabulators
        $tab_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, 'Tabulator' as role
                      FROM users u
                      JOIN tabulator_assignments ta ON u.id = ta.user_id
                      WHERE ta.competition_id = ?
                      GROUP BY u.id";
        $stmt = $conn->prepare($tab_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $tabulators = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get Organizers
        $org_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name, 'Chairman' as role
                      FROM users u
                      JOIN organizer_assignments oa ON u.id = oa.user_id
                      WHERE oa.competition_id = ?
                      GROUP BY u.id";
        $stmt = $conn->prepare($org_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $organizers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // 2. GetData: Candidates, Categories, Scores
        // Get Categories
        $cat_query = "SELECT id, name, weight_percent FROM categories WHERE competition_id = ? AND is_deleted = 0";
        $stmt = $conn->prepare($cat_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $categories = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get Candidates
        $cand_query = "SELECT id, CONCAT(first_name, ' ', last_name) as name, candidate_number as number, photo_url as avatar, gender, department FROM candidates WHERE competition_id = ? AND is_deleted = 0 ORDER BY candidate_number ASC";
        $stmt = $conn->prepare($cand_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        $results = [];

        // 3. Calculation Loop
        foreach ($candidates as $cand) {
            $cand_id = $cand['id'];
            $total_score = 0;
            
            foreach ($categories as $cat) {
                $cat_id = $cat['id'];
                $weight = floatval($cat['weight_percent']);

                // Get Judge Scores Aggregated
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
                        'criteria_weighted' => $sum_scores * ($weight / 100),
                        'category_score' => $avg_score,
                        'category_weighted' => $avg_score * ($weight / 100),
                        'total_category_score' => $avg_score,
                        'total_criteria_score' => $sum_scores,
                        'average_score' => $avg_score,
                        'total_score' => $avg_score * ($weight / 100)
                    ];
                    $cat_score = $engine->evaluate($judge_scores, $context);
                } else {
                    if (count($judge_scores) > 0) {
                        $avg = array_sum($judge_scores) / count($judge_scores);
                        $cat_score = $avg * ($weight / 100);
                    }
                }
                
                // Ensure score is a valid number (not NAN or INF) before adding to total
                if (is_nan($cat_score) || is_infinite($cat_score)) {
                    $cat_score = 0;
                }
                
                $total_score += $cat_score;
            }

            $results[] = [
                'candidate_id' => $cand['id'],
                'name' => $cand['name'],
                'number' => $cand['number'],
                'avatar' => $cand['avatar'] ? (strpos($cand['avatar'], 'uploads/') === 0 ? '../' . $cand['avatar'] : $cand['avatar']) : null,
                'gender' => $cand['gender'],
                'department' => $cand['department'],
                'score' => $total_score
            ];
        }

        // 4. Sort and Rank
        usort($results, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // We no longer calculate ranks or remarks here on the backend, 
        // because the frontend might need to split the rankings by gender 
        // (creating separate sets of Champions, 1st Runner Ups, etc.).
        // We only sort by score desc and provide the formatted score.
        foreach ($results as &$res) {
            $res['score_formatted'] = number_format($res['score'], 2) . '%';
        }

        echo json_encode([
            "status" => "success",
            "competition_title" => $comp['title'],
            "competition_description" => $comp['description'] ?? "Official Results",
            "competition_venue" => $comp['venue'] ?? "Ubay Cultural Center",
            "competition_date" => $comp['date'],
            "data" => $results,
            "judges" => $judges,
            "tabulators" => $tabulators,
            "organizers" => $organizers
        ]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
