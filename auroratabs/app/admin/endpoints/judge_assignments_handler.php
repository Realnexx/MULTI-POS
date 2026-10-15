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
    case 'list_judges':
        $query = "SELECT u.id, u.first_name, u.last_name, u.avatar_url FROM users u 
                  JOIN roles r ON u.role_id = r.id 
                  WHERE r.name = 'judge' AND u.is_deleted = 0 
                  ORDER BY u.last_name ASC";
        $result = $conn->query($query);
        $judges = [];
        while ($row = $result->fetch_assoc()) {
            $judges[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $judges]);
        break;

    case 'list_competitions':
        $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 ORDER BY title ASC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'list_assignments':
        $query = "SELECT ja.*, u.first_name, u.last_name, u.avatar_url, c.title AS competition_title 
                  FROM judge_assignments ja 
                  JOIN users u ON ja.judge_id = u.id 
                  JOIN competitions c ON ja.competition_id = c.id 
                  WHERE u.is_deleted = 0 AND c.is_deleted = 0
                  ORDER BY ja.assigned_at DESC";
        $result = $conn->query($query);
        $assignments = [];
        while ($row = $result->fetch_assoc()) {
            $assignments[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $assignments]);
        break;

    case 'assign_bulk':
        $judge_ids = $_POST['judge_ids'] ?? [];
        $competition_id = intval($_POST['competition_id'] ?? 0);
        $role = sanitize($_POST['role'] ?? 'member');

        if (empty($judge_ids) || $competition_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Please select judges and a target competition."]));
        }

        if (!is_array($judge_ids)) {
            $judge_ids = explode(',', $judge_ids);
        }

        // Fetch Competition Title
        $comp_title = "ID: $competition_id";
        $get_comp = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
        $get_comp->bind_param("i", $competition_id);
        if ($get_comp->execute()) {
            $comp_res = $get_comp->get_result()->fetch_assoc();
            if ($comp_res) $comp_title = $comp_res['title'];
        }

        // Fetch Judge Names
        $names = [];
        $placeholders = implode(',', array_fill(0, count($judge_ids), '?'));
        $types = str_repeat('i', count($judge_ids));
        $get_names = $conn->prepare("SELECT first_name, last_name FROM users WHERE id IN ($placeholders)");
        $get_names->bind_param($types, ...$judge_ids);
        if ($get_names->execute()) {
            $name_res = $get_names->get_result();
            while ($row = $name_res->fetch_assoc()) {
                $names[] = $row['first_name'] . " " . $row['last_name'];
            }
        }
        $names_list = implode(', ', $names);

        $success_count = 0;
        $error_count = 0;

        foreach ($judge_ids as $judge_id) {
            $judge_id = intval($judge_id);
            // Check if already assigned
            $check = $conn->prepare("SELECT id FROM judge_assignments WHERE judge_id = ? AND competition_id = ?");
            $check->bind_param("ii", $judge_id, $competition_id);
            $check->execute();
            if ($check->get_result()->num_rows > 0) {
                // Update role if already assigned
                $stmt = $conn->prepare("UPDATE judge_assignments SET role = ? WHERE judge_id = ? AND competition_id = ?");
                $stmt->bind_param("sii", $role, $judge_id, $competition_id);
            } else {
                // Insert new assignment
                $stmt = $conn->prepare("INSERT INTO judge_assignments (judge_id, competition_id, role) VALUES (?, ?, ?)");
                $stmt->bind_param("iis", $judge_id, $competition_id, $role);
            }

            if ($stmt->execute()) {
                $success_count++;
            } else {
                $error_count++;
                $last_error = $stmt->error;
            }
        }

        if ($success_count > 0) {
            log_activity($conn, $current_user_id, 'Assigned', 'Judges', "Bulk assigned $success_count judges ($names_list) to competition: $comp_title as $role");
            echo json_encode(["status" => "success", "message" => "$success_count judges assigned successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to assign judges."]);
        }
        break;

    case 'unassign':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch Judge Name and Competition Title
        $desc = "ID: $id";
        $get_info = $conn->prepare("SELECT u.first_name, u.last_name, c.title 
                                   FROM judge_assignments ja
                                   JOIN users u ON ja.judge_id = u.id
                                   JOIN competitions c ON ja.competition_id = c.id
                                   WHERE ja.id = ?");
        $get_info->bind_param("i", $id);
        if ($get_info->execute()) {
            $res = $get_info->get_result()->fetch_assoc();
            if ($res) {
                $desc = "Judge: " . $res['first_name'] . " " . $res['last_name'] . " from Competition: " . $res['title'];
            }
        }

        $stmt = $conn->prepare("DELETE FROM judge_assignments WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Unassigned', 'Judges', "Removed assignment: $desc");
            echo json_encode(["status" => "success", "message" => "Judge unassigned successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to unassign judge."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
