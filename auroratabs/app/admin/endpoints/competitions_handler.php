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
        $query = "SELECT c.*, u.username as lead_admin_name, f.name as formula_name 
                  FROM competitions c
                  LEFT JOIN users u ON c.lead_admin_id = u.id
                  LEFT JOIN formulas f ON c.formula_id = f.id
                  WHERE c.is_deleted = 0
                  ORDER BY c.created_at DESC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'get':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $stmt = $conn->prepare("SELECT * FROM competitions WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            echo json_encode(["status" => "success", "data" => $result]);
        } else {
            echo json_encode(["status" => "error", "message" => "Competition not found."]);
        }
        break;

    case 'list_deleted':
        $query = "SELECT c.*, u.username as deleted_by_name 
                  FROM competitions c
                  LEFT JOIN users u ON c.deleted_by = u.id
                  WHERE c.is_deleted = 1
                  ORDER BY c.deleted_at DESC";
        $result = $conn->query($query);
        $competitions = [];
        while ($row = $result->fetch_assoc()) {
            $competitions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $competitions]);
        break;

    case 'create':
        $title = sanitize($_POST['title'] ?? '');
        $type = sanitize($_POST['type'] ?? ''); // This is our hard-coded category
        $description = sanitize($_POST['description'] ?? '');
        $start_date = $_POST['start_date'] ?? null;
        $start_time = $_POST['start_time'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $end_time = $_POST['end_time'] ?? null;
        $venue = sanitize($_POST['venue'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $lead_admin_id = intval($_POST['lead_admin_id'] ?? 0);
        $formula_id = intval($_POST['formula_id'] ?? 0);
        $plan_type = $_POST['plan_type'] ?? 'starter';
        $enable_audience_voting = isset($_POST['enable_audience_voting']) ? 1 : 0;
        $unlock_limit = isset($_POST['unlock_limit']) ? intval($_POST['unlock_limit']) : 0;
        
        // Auto-generated/Derived fields
        $organization_id = 1; // Default for now
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        $created_by = $current_user_id;

        if (empty($title) || empty($type)) {
            die(json_encode(["status" => "error", "message" => "Competition title and category are required."]));
        }

        $query = "INSERT INTO competitions (
                    organization_id, created_by, lead_admin_id, title, slug, type, 
                    plan_type, description, venue, status, start_date, start_time, 
                    end_date, end_time, enable_audience_voting, formula_id, unlock_limit
                  ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiisssssssssssiii", 
            $organization_id, $created_by, $lead_admin_id, $title, $slug, $type, 
            $plan_type, $description, $venue, $status, $start_date, $start_time, 
            $end_date, $end_time, $enable_audience_voting, $formula_id, $unlock_limit
        );

        if ($stmt->execute()) {
            $comp_id = $conn->insert_id;
            log_activity($conn, $current_user_id, 'Created', 'Competitions', "Created new competition: $title (ID: $comp_id)");
            echo json_encode(["status" => "success", "message" => "Competition created successfully.", "id" => $comp_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create competition: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $title = sanitize($_POST['title'] ?? '');
        $type = sanitize($_POST['type'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $start_date = $_POST['start_date'] ?? null;
        $start_time = $_POST['start_time'] ?? null;
        $end_date = $_POST['end_date'] ?? null;
        $end_time = $_POST['end_time'] ?? null;
        $venue = sanitize($_POST['venue'] ?? '');
        $status = $_POST['status'] ?? 'draft';
        $lead_admin_id = intval($_POST['lead_admin_id'] ?? 0);
        $formula_id = intval($_POST['formula_id'] ?? 0);
        $plan_type = $_POST['plan_type'] ?? 'starter';
        $enable_audience_voting = isset($_POST['enable_audience_voting']) ? 1 : 0;
        $unlock_limit = isset($_POST['unlock_limit']) ? intval($_POST['unlock_limit']) : 0;

        if ($id <= 0 || empty($title) || empty($type)) {
            die(json_encode(["status" => "error", "message" => "ID, title, and category are required."]));
        }

        $query = "UPDATE competitions SET 
                    title = ?, type = ?, description = ?, venue = ?, status = ?, 
                    start_date = ?, start_time = ?, end_date = ?, end_time = ?, 
                    lead_admin_id = ?, formula_id = ?, plan_type = ?, 
                    enable_audience_voting = ?, unlock_limit = ?
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssssssisiii", 
            $title, $type, $description, $venue, $status, 
            $start_date, $start_time, $end_date, $end_time, 
            $lead_admin_id, $formula_id, $plan_type, 
            $enable_audience_voting, $unlock_limit, $id
        );

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Updated', 'Competitions', "Updated competition: $title (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Competition updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update competition: " . $conn->error]);
        }
        break;

    case 'toggle_status':
        $id = intval($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');

        if ($id <= 0 || empty($status)) {
            die(json_encode(["status" => "error", "message" => "Invalid ID or status."]));
        }

        $stmt = $conn->prepare("UPDATE competitions SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Updated Status', 'Competitions', "Updated competition (ID: $id) status to $status");
            echo json_encode(["status" => "success", "message" => "Status updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update status."]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        }

        // Fetch title for logging
        $comp_title = "Unknown";
        $title_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
        $title_q->bind_param("i", $id);
        if ($title_q->execute()) {
            $title_res = $title_q->get_result()->fetch_assoc();
            if ($title_res) $comp_title = $title_res['title'];
        }

        $stmt = $conn->prepare("UPDATE competitions SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $current_user_id, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Competitions', "Soft-deleted competition: $comp_title (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Competition deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete competition."]);
        }
        break;

    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch title for logging
        $comp_title = "Unknown";
        $title_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
        $title_q->bind_param("i", $id);
        if ($title_q->execute()) {
            $title_res = $title_q->get_result()->fetch_assoc();
            if ($title_res) $comp_title = $title_res['title'];
        }

        $stmt = $conn->prepare("UPDATE competitions SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Restored', 'Competitions', "Restored competition: $comp_title (ID: $id) from trash");
            echo json_encode(["status" => "success", "message" => "Competition restored successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore competition."]);
        }
        break;

    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        
        // Fetch title for logging
        $comp_title = "Unknown";
        $title_q = $conn->prepare("SELECT title FROM competitions WHERE id = ?");
        $title_q->bind_param("i", $id);
        if ($title_q->execute()) {
            $title_res = $title_q->get_result()->fetch_assoc();
            if ($title_res) $comp_title = $title_res['title'];
        }

        $stmt = $conn->prepare("DELETE FROM competitions WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Purged', 'Competitions', "Permanently deleted competition: $comp_title (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Competition purged permanently."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to purge competition."]);
        }
        break;

    case 'empty_trash':
        $stmt = $conn->prepare("DELETE FROM competitions WHERE is_deleted = 1");
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Emptied Trash', 'Competitions', "Emptied competition trash bin");
            echo json_encode(["status" => "success", "message" => "Trash emptied successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to empty trash."]);
        }
        break;

    case 'get_dependencies':
        $dependencies = [];

        // Admins
        $res_admins = $conn->query("SELECT u.id, u.username, r.name as role_name 
                                    FROM users u 
                                    JOIN roles r ON u.role_id = r.id 
                                    WHERE u.is_deleted = 0 
                                    ORDER BY u.username ASC");
        while ($row = $res_admins->fetch_assoc()) {
            $dependencies['admins'][] = $row;
        }

        // Formulas
        $res_formulas = $conn->query("SELECT id, name FROM formulas WHERE is_active = 1 ORDER BY name ASC");
        while ($row = $res_formulas->fetch_assoc()) {
            $dependencies['formulas'][] = $row;
        }

        echo json_encode(["status" => "success", "data" => $dependencies]);
        break;

    case 'upload_logo':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid competition ID."]));

        if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            die(json_encode(["status" => "error", "message" => "No file uploaded or upload error."]));
        }

        $file = $_FILES['logo'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file['type'], $allowedTypes)) {
            die(json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, GIF, and WEBP are allowed."]));
        }

        $uploadDir = '../../uploads/competitions/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'comp_' . $id . '_' . time() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            $dbPath = '../uploads/competitions/' . $filename; // Relative path for DB

            // Delete old file if exists
            $stmt = $conn->prepare("SELECT banner_url FROM competitions WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $old_path = $stmt->get_result()->fetch_assoc()['banner_url'] ?? '';
            
            if ($old_path && file_exists('../../' . ltrim($old_path, '../'))) {
                @unlink('../../' . ltrim($old_path, '../'));
            }

            $stmt = $conn->prepare("UPDATE competitions SET banner_url = ? WHERE id = ?");
            $stmt->bind_param("si", $dbPath, $id);
            if ($stmt->execute()) {
                log_activity($conn, $current_user_id, 'Updated Logo', 'Competitions', "Updated logo for competition ID $id");
                echo json_encode(["status" => "success", "message" => "Logo updated successfully.", "path" => $dbPath]);
            } else {
                echo json_encode(["status" => "error", "message" => "Database update failed."]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
