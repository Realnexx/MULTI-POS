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
        
        $where = "WHERE cat.is_deleted = 0";
        $params = [];
        $types = "";

        if ($comp_id > 0) {
            $where .= " AND cat.competition_id = ?";
            $params[] = $comp_id;
            $types .= "i";
        }

        $query = "SELECT cat.*, c.title as competition_title 
                  FROM categories cat
                  LEFT JOIN competitions c ON cat.competition_id = c.id
                  $where
                  ORDER BY cat.display_order ASC, cat.name ASC";
        
        $stmt = $conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $categories]);
        break;

    case 'get':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $stmt = $conn->prepare("SELECT * FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();

        if ($result) {
            echo json_encode(["status" => "success", "data" => $result]);
        } else {
            echo json_encode(["status" => "error", "message" => "Category not found."]);
        }
        break;

    case 'create':
        $competition_id = intval($_POST['competition_id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $type = sanitize($_POST['type'] ?? 'major');
        $level = intval($_POST['level'] ?? 1);
        $display_order = intval($_POST['display_order'] ?? 0);
        $weight_percent = floatval($_POST['weight_percent'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');

        if ($competition_id <= 0 || empty($name)) {
            die(json_encode(["status" => "error", "message" => "Competition and Category Name are required."]));
        }

        // Check if display order already exists for this competition
        $order_check = $conn->prepare("SELECT id FROM categories WHERE competition_id = ? AND display_order = ? AND is_deleted = 0");
        $order_check->bind_param("ii", $competition_id, $display_order);
        $order_check->execute();
        if ($order_check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "The display order $display_order is already taken for this competition."]));
        }

        $query = "INSERT INTO categories (competition_id, name, type, level, display_order, weight_percent, description) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issiiis", $competition_id, $name, $type, $level, $display_order, $weight_percent, $description);

        if ($stmt->execute()) {
            $cat_id = $conn->insert_id;
            log_activity($conn, $current_user_id, 'Created', 'Categories', "Created new category: $name (ID: $cat_id)");
            echo json_encode(["status" => "success", "message" => "Category created successfully.", "id" => $cat_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create category: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $competition_id = intval($_POST['competition_id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $type = sanitize($_POST['type'] ?? 'major');
        $level = intval($_POST['level'] ?? 1);
        $display_order = intval($_POST['display_order'] ?? 0);
        $weight_percent = floatval($_POST['weight_percent'] ?? 0);
        $description = sanitize($_POST['description'] ?? '');

        if ($id <= 0 || $competition_id <= 0 || empty($name)) {
            die(json_encode(["status" => "error", "message" => "ID, Competition, and Category Name are required."]));
        }

        // Check if display order already exists for this competition (excluding this record)
        $order_check = $conn->prepare("SELECT id FROM categories WHERE competition_id = ? AND display_order = ? AND id != ? AND is_deleted = 0");
        $order_check->bind_param("iii", $competition_id, $display_order, $id);
        $order_check->execute();
        if ($order_check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "The display order $display_order is already taken for this competition."]));
        }

        $query = "UPDATE categories SET 
                    competition_id = ?, name = ?, type = ?, level = ?, 
                    display_order = ?, weight_percent = ?, description = ?
                  WHERE id = ?";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issiiisi", $competition_id, $name, $type, $level, $display_order, $weight_percent, $description, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Updated', 'Categories', "Updated category: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Category updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update category: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch name for logging
        $cat_name = "Unknown";
        $name_q = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $name_q->bind_param("i", $id);
        if ($name_q->execute()) {
            $name_res = $name_q->get_result()->fetch_assoc();
            if ($name_res) $cat_name = $name_res['name'];
        }

        $stmt = $conn->prepare("UPDATE categories SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $current_user_id, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Categories', "Soft-deleted category: $cat_name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Category deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete category."]);
        }
        break;

    case 'list_deleted':
        $query = "SELECT cat.*, c.title as competition_title, u.username as deleted_by_name 
                  FROM categories cat
                  LEFT JOIN competitions c ON cat.competition_id = c.id
                  LEFT JOIN users u ON cat.deleted_by = u.id
                  WHERE cat.is_deleted = 1
                  ORDER BY cat.deleted_at DESC";
        $result = $conn->query($query);
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $categories]);
        break;

    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch name for logging
        $cat_name = "Unknown";
        $name_q = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $name_q->bind_param("i", $id);
        if ($name_q->execute()) {
            $name_res = $name_q->get_result()->fetch_assoc();
            if ($name_res) $cat_name = $name_res['name'];
        }

        $stmt = $conn->prepare("UPDATE categories SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Restored', 'Categories', "Restored category: $cat_name (ID: $id) from trash");
            echo json_encode(["status" => "success", "message" => "Category restored successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore category."]);
        }
        break;

    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch name for logging
        $cat_name = "Unknown";
        $name_q = $conn->prepare("SELECT name FROM categories WHERE id = ?");
        $name_q->bind_param("i", $id);
        if ($name_q->execute()) {
            $name_res = $name_q->get_result()->fetch_assoc();
            if ($name_res) $cat_name = $name_res['name'];
        }

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Purged', 'Categories', "Permanently deleted category: $cat_name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Category purged permanently."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to purge category."]);
        }
        break;

    case 'empty_trash':
        $stmt = $conn->prepare("DELETE FROM categories WHERE is_deleted = 1");
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Emptied Trash', 'Categories', "Emptied category trash bin");
            echo json_encode(["status" => "success", "message" => "Trash emptied successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to empty trash."]);
        }
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

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
