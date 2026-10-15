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

switch ($action) {
    case 'get_judge_data':
        // 1. Explicit Role Check
        $role_query = "SELECT r.name FROM users u JOIN roles r ON u.role_id = r.id WHERE u.id = ? AND u.is_deleted = 0";
        $role_stmt = $conn->prepare($role_query);
        $role_stmt->bind_param("i", $current_user_id);
        $role_stmt->execute();
        $role_res = $role_stmt->get_result()->fetch_assoc();

        if (!$role_res || strtolower($role_res['name']) !== 'judge') {
            die(json_encode(["status" => "error", "type" => "unauthorized", "message" => "Access Denied. This portal is only accessible to users with the 'Judge' role."]));
        }

        // 2. Get Assigned Competition
        $query = "SELECT comp.id as competition_id, comp.title as competition_title, comp.unlock_limit, ja.id as assignment_id
                  FROM judge_assignments ja
                  JOIN competitions comp ON ja.competition_id = comp.id
                  WHERE ja.judge_id = ? AND comp.is_deleted = 0 AND comp.status = 'active'
                  LIMIT 1";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $current_user_id);
        $stmt->execute();
        $assignment = $stmt->get_result()->fetch_assoc();

        if (!$assignment) {
            die(json_encode(["status" => "error", "message" => "No assigned competition found for your account."]));
        }

        $comp_id = $assignment['competition_id'];

        // Get Categories with Lock Status for this specific judge
        $cat_query = "SELECT c.id, c.name, c.level, c.type, 
                             (SELECT jl.is_locked FROM judge_locks jl 
                              WHERE jl.judge_id = ? AND jl.competition_id = ? AND jl.category_id = c.id AND jl.candidate_id IS NULL
                              ORDER BY jl.id DESC LIMIT 1) as is_locked,
                             COALESCE((SELECT jl.unlock_count FROM judge_locks jl 
                              WHERE jl.judge_id = ? AND jl.competition_id = ? AND jl.category_id = c.id AND jl.candidate_id IS NULL
                              ORDER BY jl.id DESC LIMIT 1), 0) as unlock_count
                      FROM categories c 
                      WHERE c.competition_id = ? AND c.is_deleted = 0 
                      ORDER BY c.display_order ASC";
        $cat_stmt = $conn->prepare($cat_query);
        if (!$cat_stmt) {
            die(json_encode(["status" => "error", "message" => "DB Error (cat prepare): " . $conn->error]));
        }
        $cat_stmt->bind_param("iiiii", $current_user_id, $comp_id, $current_user_id, $comp_id, $comp_id);
        if (!$cat_stmt->execute()) {
            die(json_encode(["status" => "error", "message" => "DB Error (cat execute): " . $cat_stmt->error]));
        }
        $cat_res = $cat_stmt->get_result();
        if (!$cat_res) {
            die(json_encode(["status" => "error", "message" => "DB Error (cat result): " . $cat_stmt->error]));
        }
        $categories = $cat_res->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "status" => "success",
            "data" => [
                "competition" => $assignment,
                "categories" => $categories
            ]
        ]);
        break;

    case 'get_category_details':
        $cat_id = intval($_POST['category_id'] ?? 0);
        $comp_id = intval($_POST['competition_id'] ?? 0);

        if ($cat_id <= 0 || $comp_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        }

        // Verify judge is actually assigned to this exact competition and that it is active
        $verify_q = "SELECT ja.id FROM judge_assignments ja
                     JOIN competitions comp ON ja.competition_id = comp.id
                     WHERE ja.judge_id = ? AND comp.id = ? AND comp.is_deleted = 0 AND comp.status = 'active'";
        $verify_stmt = $conn->prepare($verify_q);
        $verify_stmt->bind_param("ii", $current_user_id, $comp_id);
        $verify_stmt->execute();
        if ($verify_stmt->get_result()->num_rows === 0) {
            die(json_encode(["status" => "error", "message" => "Unauthorized access or competition is no longer active."]));
        }

        // Get Criteria
        $crit_query = "SELECT id, name, weight_percent FROM criteria WHERE category_id = ? AND is_deleted = 0 ORDER BY display_order ASC";
        $crit_stmt = $conn->prepare($crit_query);
        $crit_stmt->bind_param("i", $cat_id);
        $crit_stmt->execute();
        $criteria = $crit_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get Candidates
        $cand_query = "SELECT id, candidate_number, first_name, last_name, hometown, photo_url, gender, department FROM candidates WHERE competition_id = ? AND is_deleted = 0 ORDER BY candidate_number ASC";
        $cand_stmt = $conn->prepare($cand_query);
        $cand_stmt->bind_param("i", $comp_id);
        $cand_stmt->execute();
        $candidates = $cand_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Get Existing Scores for this Judge and Category
        $scores_query = "SELECT s.candidate_id, s.criteria_id, s.score_value 
                        FROM scores s
                        JOIN judge_assignments ja ON s.assignment_id = ja.id
                        JOIN criteria crit ON s.criteria_id = crit.id
                        WHERE ja.judge_id = ? AND crit.category_id = ?";
        $scores_stmt = $conn->prepare($scores_query);
        $scores_stmt->bind_param("ii", $current_user_id, $cat_id);
        $scores_stmt->execute();
        $scores = $scores_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        // Check for locks
        // 1. Overall Category Lock
        $cat_lock_q = "SELECT is_locked, unlock_count FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL";
        $cat_lock_stmt = $conn->prepare($cat_lock_q);
        $cat_lock_stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);
        $cat_lock_stmt->execute();
        $cat_lock_res = $cat_lock_stmt->get_result()->fetch_assoc();
        $is_locked = $cat_lock_res['is_locked'] ?? 0;
        $unlock_count = $cat_lock_res['unlock_count'] ?? 0;

        // 2. Individual Candidate Locks
        $cand_locks_q = "SELECT candidate_id, is_locked FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NOT NULL";
        $cand_locks_stmt = $conn->prepare($cand_locks_q);
        $cand_locks_stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);
        $cand_locks_stmt->execute();
        $cand_locks = $cand_locks_stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "status" => "success",
            "data" => [
                "criteria" => $criteria,
                "candidates" => $candidates,
                "scores" => $scores,
                "is_locked" => $is_locked,
                "unlock_count" => $unlock_count,
                "candidate_locks" => $cand_locks
            ]
        ]);
        break;

    case 'save_score':
        $assignment_id = intval($_POST['assignment_id'] ?? 0);
        $candidate_id = intval($_POST['candidate_id'] ?? 0);
        $criteria_id = intval($_POST['criteria_id'] ?? 0);
        $score_value = floatval($_POST['score_value'] ?? 0);
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);

        // Security check: Verify assignment belongs to user
        $check_q = "SELECT id FROM judge_assignments WHERE id = ? AND judge_id = ?";
        $check_stmt = $conn->prepare($check_q);
        $check_stmt->bind_param("ii", $assignment_id, $current_user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows === 0) {
            die(json_encode(["status" => "error", "message" => "Unauthorized access."]));
        }

        // Check if locked
        // Case A: Category-wide lock
        $lock_q = "SELECT id FROM judge_locks 
                   WHERE judge_id = ? 
                     AND competition_id = ? 
                     AND category_id = ? 
                     AND candidate_id IS NULL 
                     AND is_locked = 1";
        $lock_stmt = $conn->prepare($lock_q);
        $lock_stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);
        $lock_stmt->execute();
        if ($lock_stmt->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Scoring is already locked for this entire category."]));
        }

        // Case B: Candidate-specific lock
        $cand_lock_q = "SELECT id FROM judge_locks 
                        WHERE judge_id = ? 
                          AND competition_id = ? 
                          AND category_id = ? 
                          AND candidate_id = ? 
                          AND is_locked = 1";
        $cand_lock_stmt = $conn->prepare($cand_lock_q);
        $cand_lock_stmt->bind_param("iiii", $current_user_id, $comp_id, $cat_id, $candidate_id);
        $cand_lock_stmt->execute();
        if ($cand_lock_stmt->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Scoring is locked for this specific candidate."]));
        }

        // Save or update score
        $upsert_q = "INSERT INTO scores (assignment_id, candidate_id, criteria_id, score_value) 
                    VALUES (?, ?, ?, ?) 
                    ON DUPLICATE KEY UPDATE score_value = VALUES(score_value)";
        $stmt = $conn->prepare($upsert_q);
        $stmt->bind_param("iiid", $assignment_id, $candidate_id, $criteria_id, $score_value);

        if ($stmt->execute()) {
            // Detailed Logging
            $meta_q = "SELECT 
                        cand.first_name, cand.last_name, cand.candidate_number,
                        cat.name as category_name,
                        crit.name as criteria_name,
                        u.first_name as j_first, u.last_name as j_last
                       FROM candidates cand
                       CROSS JOIN categories cat ON cat.id = ?
                       CROSS JOIN criteria crit ON crit.id = ?
                       CROSS JOIN users u ON u.id = ?
                       WHERE cand.id = ?";
            $meta_stmt = $conn->prepare($meta_q);
            $meta_stmt->bind_param("iiii", $cat_id, $criteria_id, $current_user_id, $candidate_id);
            $meta_stmt->execute();
            $meta = $meta_stmt->get_result()->fetch_assoc();

            if ($meta) {
                $details = "Judge {$meta['j_first']} {$meta['j_last']} scored {$score_value} for Candidate #{$meta['candidate_number']} ({$meta['first_name']} {$meta['last_name']}) in {$meta['category_name']} - {$meta['criteria_name']}";
                log_activity($conn, $current_user_id, 'Scoring', 'Judging', $details);
            }

            echo json_encode(["status" => "success", "message" => "Score saved."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save score."]);
        }
        break;

    case 'finalize':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);

        if ($comp_id <= 0 || $cat_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        }

        // Verify if all required scores are present
        // 1. Count criteria for this category
        $crit_q = "SELECT COUNT(*) as cnt FROM criteria WHERE category_id = ?";
        $crit_stmt = $conn->prepare($crit_q);
        $crit_stmt->bind_param("i", $cat_id);
        $crit_stmt->execute();
        $crit_count = $crit_stmt->get_result()->fetch_assoc()['cnt'];

        // 2. Count active candidates for this competition
        $cand_q = "SELECT COUNT(*) as cnt FROM candidates WHERE competition_id = ? AND is_deleted = 0";
        $cand_stmt = $conn->prepare($cand_q);
        $cand_stmt->bind_param("i", $comp_id);
        $cand_stmt->execute();
        $cand_count = $cand_stmt->get_result()->fetch_assoc()['cnt'];

        $required_scores = $crit_count * $cand_count;

        if ($required_scores > 0) {
            // Get judge assignment ID
            $assign_q = "SELECT id FROM judge_assignments WHERE judge_id = ? AND competition_id = ?";
            $assign_stmt = $conn->prepare($assign_q);
            $assign_stmt->bind_param("ii", $current_user_id, $comp_id);
            $assign_stmt->execute();
            $assign_res = $assign_stmt->get_result()->fetch_assoc();
            $assignment_id = $assign_res['id'] ?? 0;

            // 3. Count scored entries by this judge for these candidates in this category
            $scores_q = "SELECT COUNT(*) as cnt FROM scores s 
                         JOIN criteria cr ON s.criteria_id = cr.id 
                         JOIN candidates c ON s.candidate_id = c.id 
                         WHERE cr.category_id = ? 
                           AND c.competition_id = ? 
                           AND s.assignment_id = ? 
                           AND s.score_value IS NOT NULL AND s.score_value != ''";
            $scores_stmt = $conn->prepare($scores_q);
            $scores_stmt->bind_param("iii", $cat_id, $comp_id, $assignment_id);
            $scores_stmt->execute();
            $scored_count = $scores_stmt->get_result()->fetch_assoc()['cnt'];

            if ($scored_count < $required_scores) {
                die(json_encode(["status" => "error", "message" => "Incomplete scores. You must score all candidates before finalizing. ($scored_count/$required_scores completed)"]));
            }
        }

        // Lock the category for this judge
        $check_q = "SELECT id FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL LIMIT 1";
        $check_stmt = $conn->prepare($check_q);
        $check_stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $lock_q = "UPDATE judge_locks SET is_locked = 1, locked_at = CURRENT_TIMESTAMP WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL";
        } else {
            $lock_q = "INSERT INTO judge_locks (judge_id, competition_id, category_id, is_locked) VALUES (?, ?, ?, 1)";
        }
        $stmt = $conn->prepare($lock_q);
        $stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);

        if ($stmt->execute()) {
            // Detailed Logging
            $meta_q = "SELECT 
                        comp.title as competition_title,
                        cat.name as category_name,
                        u.first_name as j_first, u.last_name as j_last
                       FROM competitions comp
                       JOIN categories cat ON cat.id = ?
                       JOIN users u ON u.id = ?
                       WHERE comp.id = ?";
            $meta_stmt = $conn->prepare($meta_q);
            $meta_stmt->bind_param("iii", $cat_id, $current_user_id, $comp_id);
            $meta_stmt->execute();
            $meta = $meta_stmt->get_result()->fetch_assoc();

            $log_details = "Judge finalized scores for Category ID: $cat_id in Competition ID: $comp_id";
            if ($meta) {
                $log_details = "Judge {$meta['j_first']} {$meta['j_last']} finalized and locked results for category '{$meta['category_name']}' in '{$meta['competition_title']}'";
            }

            log_activity($conn, $current_user_id, 'Finalized Scores', 'Judging', $log_details);
            echo json_encode(["status" => "success", "message" => "Scores finalized and locked successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to finalize scores."]);
        }
        break;

    case 'unlock_self':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);

        if ($comp_id <= 0 || $cat_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        }

        // 1. Check if the judge HAS reached the limit for this competition
        $limit_q = "SELECT unlock_limit FROM competitions WHERE id = ?";
        $l_stmt = $conn->prepare($limit_q);
        $l_stmt->bind_param("i", $comp_id);
        $l_stmt->execute();
        $limit_res = $l_stmt->get_result()->fetch_assoc();
        $unlock_limit = $limit_res ? intval($limit_res['unlock_limit']) : 3;

        $count_q = "SELECT unlock_count FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL";
        $c_stmt = $conn->prepare($count_q);
        $c_stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);
        $c_stmt->execute();
        $count_res = $c_stmt->get_result()->fetch_assoc();
        $current_unlock_count = $count_res ? intval($count_res['unlock_count']) : 0;

        if ($current_unlock_count >= $unlock_limit) {
            die(json_encode([
                "status" => "error", 
                "message" => "You have reached the maximum unlock limit ({$unlock_limit}) for this category. Please contact an administrator for assistance."
            ]));
        }

        $unlock_q = "UPDATE judge_locks 
                     SET is_locked = 0, unlock_count = unlock_count + 1 
                     WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL";
        $stmt = $conn->prepare($unlock_q);
        $stmt->bind_param("iii", $current_user_id, $comp_id, $cat_id);
        
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            // Detailed Logging
            $meta_q = "SELECT 
                        comp.title as competition_title,
                        cat.name as category_name,
                        u.first_name as j_first, u.last_name as j_last
                       FROM competitions comp
                       JOIN categories cat ON cat.id = ?
                       JOIN users u ON u.id = ?
                       WHERE comp.id = ?";
            $meta_stmt = $conn->prepare($meta_q);
            $meta_stmt->bind_param("iii", $cat_id, $current_user_id, $comp_id);
            $meta_stmt->execute();
            $meta = $meta_stmt->get_result()->fetch_assoc();

            $log_details = "Judge unlocked their own scores for Category ID: $cat_id";
            if ($meta) {
                $log_details = "Judge {$meta['j_first']} {$meta['j_last']} unlocked their finalized results for category '{$meta['category_name']}' in '{$meta['competition_title']}'";
            }

            log_activity($conn, $current_user_id, 'Self-Unlocked Category', 'Judging', $log_details);
            echo json_encode(["status" => "success", "message" => "Category unlocked successfully. You can now resume scoring."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to unlock scores or already unlocked."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
