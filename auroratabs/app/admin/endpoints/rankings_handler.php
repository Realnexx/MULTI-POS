<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

// disable error reporting for production output, but log errors
error_reporting(0);
ini_set('display_errors', 0);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$action = sanitize($_POST['action'] ?? '');
$comp_id = intval($_POST['competition_id'] ?? 0);
$cat_id = intval($_POST['category_id'] ?? 0);

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

    case 'get_categories':
        if ($comp_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Invalid Competition ID"]);
            exit;
        }
        $query = "SELECT id, name FROM categories WHERE competition_id = ? AND is_deleted = 0 ORDER BY display_order ASC, name ASC";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $data = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        echo json_encode(["status" => "success", "data" => $data]);
        break;

    case 'get_rankings':
        if ($comp_id <= 0 || $cat_id <= 0) {
            echo json_encode(["status" => "error", "message" => "Competiton and Category IDs are required"]);
            exit;
        }

        // 0. Get Competition & Category Info
        $info_query = "SELECT c.title as comp_title, c.venue, DATE_FORMAT(c.start_date, '%M %d, %Y') as comp_date,
                              cat.name as cat_name
                       FROM competitions c
                       JOIN categories cat ON c.id = cat.competition_id
                       WHERE c.id = ? AND cat.id = ?";
        $stmt = $conn->prepare($info_query);
        $stmt->bind_param("ii", $comp_id, $cat_id);
        $stmt->execute();
        $info_res = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        // 1. Get Judges assigned to this competition
        $judge_query = "SELECT u.id, CONCAT(u.first_name, ' ', u.last_name) as name
                        FROM users u
                        JOIN judge_assignments ja ON u.id = ja.judge_id
                        WHERE ja.competition_id = ?
                        GROUP BY u.id
                        ORDER BY u.id ASC";
        $stmt = $conn->prepare($judge_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $judges = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // 2. Get Candidates
        $cand_query = "SELECT id, CONCAT(first_name, ' ', last_name) as name, candidate_number as number 
                       FROM candidates 
                       WHERE competition_id = ? AND is_deleted = 0 
                       ORDER BY candidate_number ASC";
        $stmt = $conn->prepare($cand_query);
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $candidates = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // 3. Get Lock status
        $lock_query = "SELECT is_locked FROM judge_locks WHERE competition_id = ? AND category_id = ? LIMIT 1";
        $stmt = $conn->prepare($lock_query);
        $stmt->bind_param("ii", $comp_id, $cat_id);
        $stmt->execute();
        $lock_res = $stmt->get_result()->fetch_assoc();
        $is_verified = $lock_res ? (bool)$lock_res['is_locked'] : false;
        $stmt->close();

        $results = [];

        // 4. Fetch scores for each candidate
        foreach ($candidates as $cand) {
            $cand_id = $cand['id'];
            $judge_scores = [];
            $total_raw = 0;
            $judge_count = 0;

            foreach ($judges as $judge) {
                $judge_id = $judge['id'];
                
                // Get SUM of scores for this judge, candidate, and category
                $score_query = "SELECT SUM(s.score_value) as total 
                                FROM scores s
                                JOIN judge_assignments ja ON s.assignment_id = ja.id
                                JOIN criteria cr ON s.criteria_id = cr.id
                                WHERE s.candidate_id = ? 
                                AND cr.category_id = ? 
                                AND ja.judge_id = ?";
                $stmt = $conn->prepare($score_query);
                $stmt->bind_param("iii", $cand_id, $cat_id, $judge_id);
                $stmt->execute();
                $score_val = $stmt->get_result()->fetch_assoc()['total'];
                $stmt->close();

                $val = $score_val !== null ? floatval($score_val) : 0;
                $judge_scores[] = $val;
                $total_raw += $val;
                if($score_val !== null) $judge_count++;
            }

            $average = $judge_count > 0 ? $total_raw / $judge_count : 0;

            $results[] = [
                'id' => $cand_id,
                'name' => $cand['name'],
                'number' => $cand['number'],
                'judge_scores' => $judge_scores,
                'average' => $average,
                'average_formatted' => number_format($average, 2) . '%'
            ];
        }

        // 5. Sort and Assign Rank
        usort($results, function($a, $b) {
            return $b['average'] <=> $a['average'];
        });

        $rank = 1;
        $actual_rank = 1;
        $prev_score = -1;
        foreach ($results as &$res) {
            if (abs($res['average'] - $prev_score) > 0.0001) {
                $rank = $actual_rank;
            }
            $res['rank'] = $rank;
            $prev_score = $res['average'];
            $actual_rank++;
        }

        echo json_encode([
            "status" => "success",
            "is_verified" => $is_verified,
            "competition_title" => $info_res['comp_title'] ?? 'N/A',
            "competition_venue" => $info_res['venue'] ?? 'N/A',
            "competition_date" => $info_res['comp_date'] ?? 'N/A',
            "category_name" => $info_res['cat_name'] ?? 'N/A',
            "judges" => $judges,
            "data" => $results
        ]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
