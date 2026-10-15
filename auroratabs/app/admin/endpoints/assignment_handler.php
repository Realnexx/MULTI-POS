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
    case 'list_all_candidates':
        // Fetch all candidates with current competition
        $query = "SELECT c.id, c.first_name, c.last_name, c.photo_url, c.competition_id, comp.title AS competition_title 
                  FROM candidates c 
                  LEFT JOIN competitions comp ON c.competition_id = comp.id 
                  WHERE c.is_deleted = 0 
                  ORDER BY c.last_name ASC, c.first_name ASC";
        $result = $conn->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $candidates]);
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

    case 'assign_bulk':
        $candidate_ids = $_POST['candidate_ids'] ?? [];
        $competition_id = intval($_POST['competition_id'] ?? 0);

        if (empty($candidate_ids) || $competition_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Please select candidates and a target competition."]));
        }

        // Convert to array if it's a comma-separated string from AJAX
        if (!is_array($candidate_ids)) {
            $candidate_ids = explode(',', $candidate_ids);
        }

        // Fetch Competition Title
        $comp_title = "ID: $competition_id";
        $get_comp = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
        $get_comp->bind_param("i", $competition_id);
        if ($get_comp->execute()) {
            $comp_res = $get_comp->get_result()->fetch_assoc();
            if ($comp_res) $comp_title = $comp_res['title'];
        }

        // Fetch Candidate Names
        $names = [];
        $placeholders = implode(',', array_fill(0, count($candidate_ids), '?'));
        $types = str_repeat('i', count($candidate_ids));
        $get_names = $conn->prepare("SELECT first_name, last_name FROM candidates WHERE id IN ($placeholders)");
        $get_names->bind_param($types, ...$candidate_ids);
        if ($get_names->execute()) {
            $name_res = $get_names->get_result();
            while ($row = $name_res->fetch_assoc()) {
                $names[] = $row['first_name'] . " " . $row['last_name'];
            }
        }
        $names_list = implode(', ', $names);

        $stmt = $conn->prepare("UPDATE candidates SET competition_id = ? WHERE id IN ($placeholders)");
        $params = array_merge([$competition_id], $candidate_ids);
        $stmt->bind_param("i" . $types, ...$params);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Assigned', 'Candidates', "Bulk assigned " . count($candidate_ids) . " candidates ($names_list) to competition: $comp_title");
            echo json_encode(["status" => "success", "message" => "Candidates assigned successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to assign candidates."]);
        }
        break;

    case 'unassign':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch Candidate Name
        $cand_name = "ID: $id";
        $get_name = $conn->prepare("SELECT first_name, last_name FROM candidates WHERE id = ?");
        $get_name->bind_param("i", $id);
        if ($get_name->execute()) {
            $res = $get_name->get_result()->fetch_assoc();
            if ($res) $cand_name = $res['first_name'] . " " . $res['last_name'];
        }

        $stmt = $conn->prepare("UPDATE candidates SET competition_id = 1 WHERE id = ?"); // Defaulting to 1 or whatever base competition exists
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Unassigned', 'Candidates', "Removed candidate: $cand_name (ID: $id) from competition");
            echo json_encode(["status" => "success", "message" => "Candidate unassigned successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to unassign candidate."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
