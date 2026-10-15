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
    case 'list':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $where = "";
        if ($comp_id > 0) $where = " AND comp.id = $comp_id ";

        $query = "SELECT 
                    u.id AS judge_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS judge_name,
                    'Judge' AS judge_role,
                    u.avatar_url AS judge_avatar,
                    comp.id AS competition_id,
                    comp.title AS competition_title,
                    cand.id AS candidate_id,
                    cand.candidate_number,
                    CONCAT(cand.last_name, ', ', cand.first_name) AS candidate_name,
                    cat.name AS category_name,
                    COALESCE(SUM(s.score_value), 0) AS total_score,
                    CASE 
                        WHEN jl.is_locked = 1 THEN 'Submitted'
                        ELSE 'Pending'
                    END AS status,
                    jl.locked_at AS time_submitted,
                    cat.id as category_id
                  FROM judge_assignments ja
                  INNER JOIN users u ON ja.judge_id = u.id
                  INNER JOIN competitions comp ON ja.competition_id = comp.id
                  INNER JOIN candidates cand ON cand.competition_id = comp.id AND cand.is_deleted = 0
                  INNER JOIN categories cat ON cat.competition_id = comp.id AND cat.is_deleted = 0
                  LEFT JOIN criteria crit ON crit.category_id = cat.id AND crit.is_deleted = 0
                  LEFT JOIN scores s ON s.assignment_id = ja.id AND s.candidate_id = cand.id AND s.criteria_id = crit.id
                  LEFT JOIN judge_locks jl ON jl.judge_id = u.id 
                    AND jl.competition_id = comp.id 
                    AND jl.category_id = cat.id 
                    AND (jl.candidate_id = cand.id OR jl.candidate_id IS NULL)
                  WHERE 1=1 $where
                  GROUP BY u.id, comp.id, cand.id, cat.id
                  ORDER BY jl.locked_at DESC, cand.candidate_number ASC";

        $result = $conn->query($query);
        if (!$result) die(json_encode(["status" => "error", "message" => "Database error: " . $conn->error]));

        $submissions = [];
        while ($row = $result->fetch_assoc()) {
            $submissions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $submissions]);
        break;

    case 'unlock':
        $judge_id = intval($_POST['judge_id'] ?? 0);
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);
        $cand_id = intval($_POST['candidate_id'] ?? 0);

        if ($judge_id <= 0 || $comp_id <= 0 || $cat_id <= 0 || $cand_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid Judge, Competition, Category, or Candidate ID."]));
        }

        // Fetch names for logging
        $j_name = "Judge ID: $judge_id";
        $name_q = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) as name FROM users WHERE id = ?");
        $name_q->bind_param("i", $judge_id);
        if ($name_q->execute()) {
            $res = $name_q->get_result()->fetch_assoc();
            if ($res) $j_name = $res['name'];
        }

        // To support granular unlock, we check if there's a category-wide lock.
        // If there is, we might want to "convert" it to individual locks for ALL OTHER candidates
        // to keep them locked while unlocking this one.
        
        // Check for category-wide lock (candidate_id IS NULL)
        $check_q = "SELECT id FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL";
        $stmt = $conn->prepare($check_q);
        $stmt->bind_param("iii", $judge_id, $comp_id, $cat_id);
        $stmt->execute();
        $cat_lock = $stmt->get_result()->fetch_assoc();

        if ($cat_lock) {
            // "Break" the category lock: 
            // 1. Delete the category-wide lock
            $conn->query("DELETE FROM judge_locks WHERE id = " . $cat_lock['id']);
            
            // 2. Insert individual locks for all OTHER candidates in this competition
            $cands_q = "SELECT id FROM candidates WHERE competition_id = ? AND is_deleted = 0 AND id != ?";
            $c_stmt = $conn->prepare($cands_q);
            $c_stmt->bind_param("ii", $comp_id, $cand_id);
            $c_stmt->execute();
            $other_cands = $c_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            
            foreach ($other_cands as $oc) {
                $check = $conn->prepare("SELECT id FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id = ? LIMIT 1");
                $check->bind_param("iiii", $judge_id, $comp_id, $cat_id, $oc['id']);
                $check->execute();
                if ($check->get_result()->num_rows > 0) {
                    $ins = $conn->prepare("UPDATE judge_locks SET is_locked = 1, locked_at = NOW() WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id = ?");
                } else {
                    $ins = $conn->prepare("INSERT INTO judge_locks (judge_id, competition_id, category_id, candidate_id, is_locked, locked_at) VALUES (?, ?, ?, ?, 1, NOW())");
                }
                $ins->bind_param("iiii", $judge_id, $comp_id, $cat_id, $oc['id']);
                $ins->execute();
            }
        } else {
            // Just delete the specific candidate lock if it exists
            $stmt = $conn->prepare("DELETE FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id = ?");
            $stmt->bind_param("iiii", $judge_id, $comp_id, $cat_id, $cand_id);
            $stmt->execute();
        }

        // Detailed Logging Metadata
        $meta_q = "SELECT 
                    u.first_name as j_first, u.last_name as j_last,
                    cat.name as category_name,
                    comp.title as competition_title,
                    cand.first_name as c_first, cand.last_name as c_last, cand.candidate_number
                   FROM users u
                   CROSS JOIN categories cat ON cat.id = ?
                   CROSS JOIN competitions comp ON comp.id = ?
                   CROSS JOIN candidates cand ON cand.id = ?
                   WHERE u.id = ?";
        $meta_stmt = $conn->prepare($meta_q);
        $meta_stmt->bind_param("iiii", $cat_id, $comp_id, $cand_id, $judge_id);
        $meta_stmt->execute();
        $meta = $meta_stmt->get_result()->fetch_assoc();

        $log_msg = "Unlocked scores for Judge ID: $judge_id for Candidate ID: $cand_id in Category ID: $cat_id";
        if ($meta) {
            $log_msg = "Admin unlocked scores for Candidate #{$meta['candidate_number']} ({$meta['c_first']} {$meta['c_last']}) in category '{$meta['category_name']}' for Judge {$meta['j_first']} {$meta['j_last']}";
        }

        log_activity($conn, $current_user_id, 'Unlocked', 'Judges', $log_msg);
        echo json_encode(["status" => "success", "message" => "Candidate submission unlocked successfully."]);
        break;

    case 'unlock_category':
        $judge_id = intval($_POST['judge_id'] ?? 0);
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);

        if ($judge_id <= 0 || $comp_id <= 0 || $cat_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Missing IDs."]));
        }

        $stmt = $conn->prepare("DELETE FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ?");
        $stmt->bind_param("iii", $judge_id, $comp_id, $cat_id);

        if ($stmt->execute()) {
            // Detailed Logging Metadata
            $meta_q = "SELECT 
                        u.first_name as j_first, u.last_name as j_last,
                        cat.name as category_name
                       FROM users u
                       CROSS JOIN categories cat ON cat.id = ?
                       WHERE u.id = ?";
            $meta_stmt = $conn->prepare($meta_q);
            $meta_stmt->bind_param("ii", $cat_id, $judge_id);
            $meta_stmt->execute();
            $meta = $meta_stmt->get_result()->fetch_assoc();

            $log_msg = "Unlocked category ID: $cat_id for judge ID: $judge_id";
            if ($meta) {
                $log_msg = "Admin unlocked entire category '{$meta['category_name']}' for Judge {$meta['j_first']} {$meta['j_last']}";
            }

            log_activity($conn, $current_user_id, 'Unlocked Category', 'Judges', $log_msg);
            echo json_encode(["status" => "success", "message" => "Category unlocked successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to unlock category."]);
        }
        break;

    case 'clear_all':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        if ($comp_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Competition ID."]));

        $stmt = $conn->prepare("DELETE FROM judge_locks WHERE competition_id = ?");
        $stmt->bind_param("i", $comp_id);

        if ($stmt->execute()) {
            $comp_name = "Competition ID: $comp_id";
            $name_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
            $name_q->bind_param("i", $comp_id);
            if ($name_q->execute()) {
                $res = $name_q->get_result()->fetch_assoc();
                if ($res) $comp_name = $res['title'];
            }

            log_activity($conn, $current_user_id, 'Cleared Locks', 'Judges', "Admin cleared ALL score locks for competition: $comp_name");
            echo json_encode(["status" => "success", "message" => "All submissions for this competition have been unlocked."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to clear locks."]);
        }
        break;

    case 'view_details':
        $judge_id = intval($_POST['judge_id'] ?? 0);
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);
        $cand_id = intval($_POST['candidate_id'] ?? 0);

        if ($judge_id <= 0 || $comp_id <= 0 || $cat_id <= 0 || $cand_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Missing required IDs."]));
        }

        $query = "SELECT crit.name as criteria_name, crit.weight_percent, s.score_value 
                  FROM criteria crit
                  INNER JOIN judge_assignments ja ON ja.competition_id = crit.competition_id
                  LEFT JOIN scores s ON s.criteria_id = crit.id 
                    AND s.assignment_id = ja.id 
                    AND s.candidate_id = ?
                  WHERE ja.judge_id = ? 
                    AND crit.competition_id = ? 
                    AND crit.category_id = ?
                    AND crit.is_deleted = 0
                  ORDER BY crit.display_order ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiii", $cand_id, $judge_id, $comp_id, $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $details = [];
        while ($row = $result->fetch_assoc()) {
            $details[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $details]);
        break;

    case 'get_competitions':
        $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY title ASC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'list_locks':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $where = "";
        if ($comp_id > 0) $where = " AND comp.id = $comp_id ";

        $query = "SELECT 
                    u.id AS judge_id,
                    CONCAT(u.first_name, ' ', u.last_name) AS judge_name,
                    u.avatar_url AS judge_avatar,
                    u.username as judge_user,
                    comp.id AS competition_id,
                    comp.title AS competition_title,
                    cat.id AS category_id,
                    cat.name AS category_name,
                    (SELECT COUNT(*) FROM judge_locks jl 
                     WHERE jl.judge_id = u.id AND jl.competition_id = comp.id AND jl.category_id = cat.id AND jl.candidate_id IS NULL AND jl.is_locked = 1) AS is_category_locked,
                    (SELECT COUNT(*) FROM judge_locks jl 
                     WHERE jl.judge_id = u.id AND jl.competition_id = comp.id AND jl.category_id = cat.id AND jl.candidate_id IS NOT NULL AND jl.is_locked = 1) AS locked_candidates_count,
                    (SELECT COUNT(*) FROM candidates cand WHERE cand.competition_id = comp.id AND cand.is_deleted = 0) AS total_candidates_count,
                    (SELECT COALESCE(MAX(jl.unlock_count), 0) FROM judge_locks jl 
                     WHERE jl.judge_id = u.id AND jl.competition_id = comp.id AND jl.category_id = cat.id AND jl.candidate_id IS NULL) AS unlock_count,
                    (SELECT MAX(locked_at) FROM judge_locks jl 
                     WHERE jl.judge_id = u.id AND jl.competition_id = comp.id AND jl.category_id = cat.id) AS last_updated
                  FROM judge_assignments ja
                  INNER JOIN users u ON ja.judge_id = u.id
                  INNER JOIN competitions comp ON ja.competition_id = comp.id
                  INNER JOIN categories cat ON cat.competition_id = comp.id AND cat.is_deleted = 0
                  WHERE comp.is_deleted = 0 $where
                  ORDER BY comp.title ASC, u.first_name ASC, cat.display_order ASC";

        $result = $conn->query($query);
        if (!$result) die(json_encode(["status" => "error", "message" => "Database error: " . $conn->error]));

        $locks = [];
        while ($row = $result->fetch_assoc()) {
            $locks[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $locks]);
        break;

    case 'lock':
        $judge_id = intval($_POST['judge_id'] ?? 0);
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $cat_id = intval($_POST['category_id'] ?? 0);

        if ($judge_id <= 0 || $comp_id <= 0 || $cat_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Missing IDs."]));
        }

        $check_q = "SELECT id FROM judge_locks WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL LIMIT 1";
        $check_stmt = $conn->prepare($check_q);
        $check_stmt->bind_param("iii", $judge_id, $comp_id, $cat_id);
        $check_stmt->execute();
        
        if ($check_stmt->get_result()->num_rows > 0) {
            $stmt = $conn->prepare("UPDATE judge_locks SET is_locked = 1, locked_at = NOW() WHERE judge_id = ? AND competition_id = ? AND category_id = ? AND candidate_id IS NULL");
        } else {
            $stmt = $conn->prepare("INSERT INTO judge_locks (judge_id, competition_id, category_id, is_locked, locked_at) VALUES (?, ?, ?, 1, NOW())");
        }
        $stmt->bind_param("iii", $judge_id, $comp_id, $cat_id);

        if ($stmt->execute()) {
            // Detailed Logging Metadata
            $meta_q = "SELECT 
                        u.first_name as j_first, u.last_name as j_last,
                        cat.name as category_name
                       FROM users u
                       CROSS JOIN categories cat ON cat.id = ?
                       WHERE u.id = ?";
            $meta_stmt = $conn->prepare($meta_q);
            $meta_stmt->bind_param("ii", $cat_id, $judge_id);
            $meta_stmt->execute();
            $meta = $meta_stmt->get_result()->fetch_assoc();

            $log_msg = "Manually locked category ID: $cat_id for judge ID: $judge_id";
            if ($meta) {
                $log_msg = "Admin manually locked category '{$meta['category_name']}' for Judge {$meta['j_first']} {$meta['j_last']}";
            }

            log_activity($conn, $current_user_id, 'Locked', 'Judges', $log_msg);
            echo json_encode(["status" => "success", "message" => "Category locked successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to lock category."]);
        }
        break;

    case 'unlock_all_comp':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        if ($comp_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Competition ID."]));

        $stmt = $conn->prepare("DELETE FROM judge_locks WHERE competition_id = ?");
        $stmt->bind_param("i", $comp_id);

        if ($stmt->execute()) {
            $comp_name = "Competition ID: $comp_id";
            $name_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
            $name_q->bind_param("i", $comp_id);
            if ($name_q->execute()) {
                $res = $name_q->get_result()->fetch_assoc();
                if ($res) $comp_name = $res['title'];
            }

            log_activity($conn, $current_user_id, 'Unlocked All', 'Judges', "Admin unlocked ALL judges and categories for competition: $comp_name");
            echo json_encode(["status" => "success", "message" => "All judges unlocked for this competition."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to unlock judges."]);
        }
        break;

    case 'lock_all_comp':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        if ($comp_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Competition ID."]));

        // First UPDATE existing ones
        $upd_query = "UPDATE judge_locks jl 
                      INNER JOIN judge_assignments ja ON jl.judge_id = ja.judge_id AND jl.competition_id = ja.competition_id
                      INNER JOIN categories cat ON jl.category_id = cat.id AND cat.competition_id = ja.competition_id
                      SET jl.is_locked = 1, jl.locked_at = NOW()
                      WHERE ja.competition_id = ? AND cat.is_deleted = 0 AND jl.candidate_id IS NULL";
        $upd = $conn->prepare($upd_query);
        $upd->bind_param("i", $comp_id);
        $upd->execute();
        
        // Then INSERT missing ones
        $ins_query = "INSERT INTO judge_locks (judge_id, competition_id, category_id, is_locked, locked_at)
                      SELECT ja.judge_id, ja.competition_id, cat.id, 1, NOW()
                      FROM judge_assignments ja
                      INNER JOIN categories cat ON cat.competition_id = ja.competition_id
                      WHERE ja.competition_id = ? AND cat.is_deleted = 0
                      AND NOT EXISTS (
                          SELECT 1 FROM judge_locks jl 
                          WHERE jl.judge_id = ja.judge_id AND jl.competition_id = ja.competition_id 
                          AND jl.category_id = cat.id AND jl.candidate_id IS NULL
                      )";
        $stmt = $conn->prepare($ins_query);
        $stmt->bind_param("i", $comp_id);

        if ($stmt->execute()) {
            $comp_name = "Competition ID: $comp_id";
            $name_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
            $name_q->bind_param("i", $comp_id);
            if ($name_q->execute()) {
                $res = $name_q->get_result()->fetch_assoc();
                if ($res) $comp_name = $res['title'];
            }

            log_activity($conn, $current_user_id, 'Locked All', 'Judges', "Admin locked ALL judges and categories for competition: $comp_name");
            echo json_encode(["status" => "success", "message" => "All judges and categories locked for this competition."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to lock all judges."]);
        }
        break;

    case 'get_unlock_limit':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        if ($comp_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Competition ID."]));

        $stmt = $conn->prepare("SELECT unlock_limit FROM competitions WHERE id = ?");
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        echo json_encode(["status" => "success", "unlock_limit" => $res ? intval($res['unlock_limit']) : 3]);
        break;

    case 'update_unlock_limit':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        $limit = intval($_POST['unlock_limit'] ?? 3);
        
        if ($comp_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Competition ID."]));

        $stmt = $conn->prepare("UPDATE competitions SET unlock_limit = ? WHERE id = ?");
        $stmt->bind_param("ii", $limit, $comp_id);

        if ($stmt->execute()) {
            $comp_name = "Competition ID: $comp_id";
            $name_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
            $name_q->bind_param("i", $comp_id);
            if ($name_q->execute()) {
                $res = $name_q->get_result()->fetch_assoc();
                if ($res) $comp_name = $res['title'];
            }

            log_activity($conn, $current_user_id, 'Updated Limit', 'Judges', "Admin updated judge unlock limit to $limit for competition: $comp_name");
            echo json_encode(["status" => "success", "message" => "Unlock limit updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update unlock limit."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
