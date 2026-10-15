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
        $query = "SELECT c.*, comp.title AS competition_title 
                  FROM candidates c 
                  LEFT JOIN competitions comp ON c.competition_id = comp.id 
                  WHERE c.is_deleted = 0 
                  ORDER BY comp.title ASC, c.candidate_number ASC";
        $result = $conn->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $candidates]);
        break;

    case 'list_competitions':
        $query = "SELECT id, title FROM competitions WHERE is_deleted = 0 AND status = 'active' ORDER BY title ASC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'get_next_number':
        $competition_id = intval($_POST['competition_id'] ?? 0);
        $gender = sanitize($_POST['gender'] ?? '');
        
        if ($competition_id <= 0 || empty($gender)) {
            die(json_encode(["status" => "error", "message" => "Missing parameters for auto-numbering."]));
        }
        
        $query = "SELECT MAX(candidate_number) as max_num FROM candidates WHERE competition_id = ? AND gender = ? AND is_deleted = 0";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $competition_id, $gender);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        
        $next_number = ($res && !is_null($res['max_num'])) ? intval($res['max_num']) + 1 : 1;
        
        echo json_encode(["status" => "success", "data" => ["next_number" => $next_number]]);
        break;

    case 'create':
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $middle_name = sanitize($_POST['middle_name'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $height = sanitize($_POST['height'] ?? '');
        $hometown = sanitize($_POST['hometown'] ?? '');
        $competition_id = intval($_POST['competition_id'] ?? 0);
        $candidate_number = intval($_POST['candidate_number'] ?? 0);
        $department = sanitize($_POST['department'] ?? '');
        $biography = sanitize($_POST['biography'] ?? '');
        $existing_photo_url = sanitize($_POST['existing_photo_url'] ?? '');
        
        $photo_url = $existing_photo_url;
        if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/candidates/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = uniqid('cand_') . '.' . $file_ext;
                if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $upload_dir . $new_filename)) {
                    $photo_url = 'uploads/candidates/' . $new_filename;
                }
            }
        }

        if (empty($first_name) || empty($last_name) || empty($gender) || $age <= 0 || empty($height) || $competition_id <= 0 || $candidate_number <= 0 || empty($department)) {
            die(json_encode(["status" => "error", "message" => "Please fill in all required fields marked with an asterisk (*)."]));
        }

        // Check for unique candidate number in the same competition and gender
        $check = $conn->prepare("SELECT id FROM candidates WHERE competition_id = ? AND gender = ? AND candidate_number = ? AND is_deleted = 0");
        $check->bind_param("isi", $competition_id, $gender, $candidate_number);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Candidate number $candidate_number is already taken in this competition."]));
        }

        $stmt = $conn->prepare("INSERT INTO candidates (competition_id, candidate_number, first_name, last_name, middle_name, gender, age, height, hometown, department, biography, photo_url) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iissssssssss", $competition_id, $candidate_number, $first_name, $last_name, $middle_name, $gender, $age, $height, $hometown, $department, $biography, $photo_url);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Created', 'Candidates', "Added candidate: $first_name $last_name (#$candidate_number)");
            echo json_encode(["status" => "success", "message" => "Candidate created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create candidate: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $middle_name = sanitize($_POST['middle_name'] ?? '');
        $gender = sanitize($_POST['gender'] ?? '');
        $age = intval($_POST['age'] ?? 0);
        $height = sanitize($_POST['height'] ?? '');
        $hometown = sanitize($_POST['hometown'] ?? '');
        $competition_id = intval($_POST['competition_id'] ?? 0);
        $candidate_number = intval($_POST['candidate_number'] ?? 0);
        $department = sanitize($_POST['department'] ?? '');
        $biography = sanitize($_POST['biography'] ?? '');
        $existing_photo_url = sanitize($_POST['existing_photo_url'] ?? '');

        $photo_url = $existing_photo_url;
        if (isset($_FILES['photo_file']) && $_FILES['photo_file']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../../uploads/candidates/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = strtolower(pathinfo($_FILES['photo_file']['name'], PATHINFO_EXTENSION));
            $allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
            
            if (in_array($file_ext, $allowed_exts)) {
                $new_filename = uniqid('cand_') . '.' . $file_ext;
                if (move_uploaded_file($_FILES['photo_file']['tmp_name'], $upload_dir . $new_filename)) {
                    $photo_url = 'uploads/candidates/' . $new_filename;
                }
            }
        }

        if ($id <= 0 || empty($first_name) || empty($last_name) || empty($gender) || $age <= 0 || empty($height) || $competition_id <= 0 || $candidate_number <= 0 || empty($department)) {
            die(json_encode(["status" => "error", "message" => "Please fill in all required fields marked with an asterisk (*)."]));
        }

        // Check for unique candidate number in the same competition (excluding self)
        $check = $conn->prepare("SELECT id FROM candidates WHERE competition_id = ? AND gender = ? AND candidate_number = ? AND id != ? AND is_deleted = 0");
        $check->bind_param("isii", $competition_id, $gender, $candidate_number, $id);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Candidate number $candidate_number is already taken in this competition."]));
        }

        $stmt = $conn->prepare("UPDATE candidates SET competition_id = ?, candidate_number = ?, first_name = ?, last_name = ?, middle_name = ?, gender = ?, age = ?, height = ?, hometown = ?, department = ?, biography = ?, photo_url = ? WHERE id = ?");
        $stmt->bind_param("iissssssssssi", $competition_id, $candidate_number, $first_name, $last_name, $middle_name, $gender, $age, $height, $hometown, $department, $biography, $photo_url, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Updated', 'Candidates', "Updated candidate: $first_name $last_name (#$candidate_number)");
            echo json_encode(["status" => "success", "message" => "Candidate updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update candidate: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $name = "ID: $id";
        $get_name = $conn->prepare("SELECT first_name, last_name FROM candidates WHERE id = ?");
        $get_name->bind_param("i", $id);
        if ($get_name->execute()) {
            $res = $get_name->get_result()->fetch_assoc();
            if ($res) $name = $res['first_name'] . " " . $res['last_name'];
        }

        $stmt = $conn->prepare("UPDATE candidates SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $current_user_id, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Candidates', "Soft-deleted candidate: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Candidate moved to trash."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete candidate."]);
        }
        break;

    case 'list_deleted':
        $query = "SELECT c.*, comp.title AS competition_title, u.first_name as deleter_fname, u.last_name as deleter_lname 
                  FROM candidates c 
                  LEFT JOIN competitions comp ON c.competition_id = comp.id 
                  LEFT JOIN users u ON c.deleted_by = u.id
                  WHERE c.is_deleted = 1 
                  ORDER BY c.deleted_at DESC";
        $result = $conn->query($query);
        $candidates = [];
        while ($row = $result->fetch_assoc()) {
            $candidates[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $candidates]);
        break;

    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $name = "ID: $id";
        $get_name = $conn->prepare("SELECT first_name, last_name FROM candidates WHERE id = ?");
        $get_name->bind_param("i", $id);
        if ($get_name->execute()) {
            $res = $get_name->get_result()->fetch_assoc();
            if ($res) $name = $res['first_name'] . " " . $res['last_name'];
        }

        $stmt = $conn->prepare("UPDATE candidates SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Restored', 'Candidates', "Restored candidate: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Candidate restored successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore candidate."]);
        }
        break;

    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $name = "ID: $id";
        $get_name = $conn->prepare("SELECT first_name, last_name FROM candidates WHERE id = ?");
        $get_name->bind_param("i", $id);
        if ($get_name->execute()) {
            $res = $get_name->get_result()->fetch_assoc();
            if ($res) $name = $res['first_name'] . " " . $res['last_name'];
        }

        $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Purged', 'Candidates', "Permanently deleted candidate: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Candidate purged permanently."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to purge candidate."]);
        }
        break;

    case 'empty_trash':
        $stmt = $conn->prepare("DELETE FROM candidates WHERE is_deleted = 1");
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Emptied Trash', 'Candidates', "Emptied candidates trash bin");
            echo json_encode(["status" => "success", "message" => "Trash emptied successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to empty trash."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid Action"]);
        break;
}

$conn->close();
?>
