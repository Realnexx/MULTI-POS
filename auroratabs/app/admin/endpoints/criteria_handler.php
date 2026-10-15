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
        $cat_id = intval($_POST['category_id'] ?? 0);
        
        $where = ["c.is_deleted = 0"];
        $params = [];
        $types = "";

        if ($comp_id > 0) {
            $where[] = "cat.competition_id = ?";
            $params[] = $comp_id;
            $types .= "i";
        }
        if ($cat_id > 0) {
            $where[] = "c.category_id = ?";
            $params[] = $cat_id;
            $types .= "i";
        }

        $where_clause = implode(" AND ", $where);
        $query = "SELECT c.*, cat.name as category_name, comp.title as competition_title
                  FROM criteria c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  LEFT JOIN competitions comp ON cat.competition_id = comp.id
                  WHERE $where_clause 
                  ORDER BY comp.title ASC, cat.display_order ASC, c.display_order ASC";
        
        $stmt = $conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $criteria = [];
        while ($row = $result->fetch_assoc()) {
            $criteria[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $criteria]);
        break;

    case 'list_categories':
        $query = "SELECT cat.id, cat.name, comp.title as competition_title 
                  FROM categories cat 
                  JOIN competitions comp ON cat.competition_id = comp.id
                  WHERE cat.is_deleted = 0 
                  ORDER BY comp.title ASC, cat.name ASC";
        $result = $conn->query($query);
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $row['display_name'] = $row['name'] . " (" . $row['competition_title'] . ")";
            $categories[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $categories]);
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

    case 'get_categories_by_comp':
        $comp_id = intval($_POST['competition_id'] ?? 0);
        if ($comp_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Competition ID."]));
        
        $stmt = $conn->prepare("SELECT id, name FROM categories WHERE competition_id = ? AND is_deleted = 0 ORDER BY display_order ASC");
        $stmt->bind_param("i", $comp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $categories = [];
        while ($row = $result->fetch_assoc()) {
            $categories[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $categories]);
        break;

    case 'get_by_category':
        $cat_id = intval($_POST['category_id'] ?? 0);
        if ($cat_id <= 0) die(json_encode(["status" => "error", "message" => "Invalid Category ID."]));

        $stmt = $conn->prepare("SELECT id, name, weight_percent FROM criteria WHERE category_id = ? AND is_deleted = 0 ORDER BY display_order ASC");
        $stmt->bind_param("i", $cat_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $criteria = [];
        while ($row = $result->fetch_assoc()) {
            $criteria[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $criteria]);
        break;

    case 'update_weights':
        $weights = $_POST['weights'] ?? []; // Array of {id: X, weight: Y}
        if (empty($weights)) die(json_encode(["status" => "error", "message" => "No weights provided."]));

        $conn->begin_transaction();
        try {
            $stmt = $conn->prepare("UPDATE criteria SET weight_percent = ? WHERE id = ?");
            foreach ($weights as $w) {
                $id = intval($w['id']);
                $val = floatval($w['weight']);
                $stmt->bind_param("di", $val, $id);
                $stmt->execute();
            }
            $conn->commit();
            log_activity($conn, $current_user_id, 'Updated Weights', 'Criteria', "Batch updated criteria weights for category.");
            echo json_encode(["status" => "success", "message" => "Weights updated successfully."]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Failed to update weights: " . $e->getMessage()]);
        }
        break;

    case 'create':
        $name = sanitize($_POST['name'] ?? '');
        $weight_percent = floatval($_POST['weight'] ?? 0);
        $display_order = intval($_POST['display_order'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $is_required = isset($_POST['is_required']) && $_POST['is_required'] === 'true' ? 1 : 0;

        if (empty($name) || $category_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Name and Category are required."]));
        }

        // Check for unique display order in the same category
        $order_check = $conn->prepare("SELECT id FROM criteria WHERE category_id = ? AND display_order = ? AND is_deleted = 0");
        $order_check->bind_param("ii", $category_id, $display_order);
        $order_check->execute();
        if ($order_check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Display order $display_order is already taken in this category."]));
        }

        $stmt = $conn->prepare("INSERT INTO criteria (name, weight_percent, display_order, category_id, is_required) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sdisi", $name, $weight_percent, $display_order, $category_id, $is_required);
        
        if ($stmt->execute()) {
            $crit_id = $conn->insert_id;
            // Fetch category name for detailed logging
            $cat_name = "ID: $category_id";
            $cat_q = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $cat_q->bind_param("i", $category_id);
            if ($cat_q->execute()) {
                $cat_res = $cat_q->get_result()->fetch_assoc();
                if ($cat_res) $cat_name = $cat_res['name'];
            }

            log_activity($conn, $current_user_id, 'Created', 'Criteria', "Added criterion: $name ($weight_percent%) in category: $cat_name (ID: $crit_id)");
            echo json_encode(["status" => "success", "message" => "Criterion created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create criterion: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $weight_percent = floatval($_POST['weight'] ?? 0);
        $display_order = intval($_POST['display_order'] ?? 0);
        $category_id = intval($_POST['category_id'] ?? 0);
        $is_required = isset($_POST['is_required']) && $_POST['is_required'] === 'true' ? 1 : 0;

        if ($id <= 0 || empty($name) || $category_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Valid ID, name, and category are required."]));
        }

        // Check for unique display order in the same category (excluding this record)
        $order_check = $conn->prepare("SELECT id FROM criteria WHERE category_id = ? AND display_order = ? AND id != ? AND is_deleted = 0");
        $order_check->bind_param("iii", $category_id, $display_order, $id);
        $order_check->execute();
        if ($order_check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Display order $display_order is already taken in this category."]));
        }

        $stmt = $conn->prepare("UPDATE criteria SET name = ?, weight_percent = ?, display_order = ?, category_id = ?, is_required = ? WHERE id = ?");
        $stmt->bind_param("sdisii", $name, $weight_percent, $display_order, $category_id, $is_required, $id);

        if ($stmt->execute()) {
            // Fetch category name for detailed logging
            $cat_name = "ID: $category_id";
            $cat_q = $conn->prepare("SELECT name FROM categories WHERE id = ?");
            $cat_q->bind_param("i", $category_id);
            if ($cat_q->execute()) {
                $cat_res = $cat_q->get_result()->fetch_assoc();
                if ($cat_res) $cat_name = $cat_res['name'];
            }

            log_activity($conn, $current_user_id, 'Updated', 'Criteria', "Updated criterion: $name ($weight_percent%) in category: $cat_name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Criterion updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update criterion: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Get name before delete for logging
        $check = $conn->prepare("SELECT name FROM criteria WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();
        $name = $res ? $res['name'] : "ID $id";

        $stmt = $conn->prepare("UPDATE criteria SET is_deleted = 1, deleted_at = NOW(), deleted_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $current_user_id, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Criteria', "Soft-deleted criterion: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Criterion moved to archive."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete criterion."]);
        }
        break;

    case 'list_deleted':
        $query = "SELECT c.*, cat.name as category_name, u.username as deleted_by_name 
                  FROM criteria c 
                  LEFT JOIN categories cat ON c.category_id = cat.id 
                  LEFT JOIN users u ON c.deleted_by = u.id
                  WHERE c.is_deleted = 1 
                  ORDER BY c.deleted_at DESC";
        $result = $conn->query($query);
        $criteria = [];
        while ($row = $result->fetch_assoc()) {
            $criteria[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $criteria]);
        break;

    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Get name before restore for logging
        $check = $conn->prepare("SELECT name FROM criteria WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();
        $name = $res ? $res['name'] : "ID $id";

        $stmt = $conn->prepare("UPDATE criteria SET is_deleted = 0, deleted_at = NULL, deleted_by = NULL WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Restored', 'Criteria', "Restored criterion: $name (ID: $id) from archive");
            echo json_encode(["status" => "success", "message" => "Criterion restored successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore criterion."]);
        }
        break;

    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Get name before delete for logging
        $check = $conn->prepare("SELECT name FROM criteria WHERE id = ?");
        $check->bind_param("i", $id);
        $check->execute();
        $res = $check->get_result()->fetch_assoc();
        $name = $res ? $res['name'] : "ID $id";

        $stmt = $conn->prepare("DELETE FROM criteria WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Purged', 'Criteria', "Permanently deleted criterion: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Criterion purged permanently."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to purge criterion."]);
        }
        break;

    case 'empty_trash':
        $stmt = $conn->prepare("DELETE FROM criteria WHERE is_deleted = 1");
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Emptied Trash', 'Criteria', "Emptied criteria archive");
            echo json_encode(["status" => "success", "message" => "Archive emptied successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to empty archive."]);
        }
        break;

    case 'toggle_required':
        $id = intval($_POST['id'] ?? 0);
        $required = intval($_POST['required'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $new_val = ($required == 1) ? 0 : 1;
        $stmt = $conn->prepare("UPDATE criteria SET is_required = ? WHERE id = ?");
        $stmt->bind_param("ii", $new_val, $id);

        if ($stmt->execute()) {
            // Fetch criterion name for the log
            $crit_name = "ID: $id";
            $crit_q = $conn->prepare("SELECT name FROM criteria WHERE id = ?");
            $crit_q->bind_param("i", $id);
            if ($crit_q->execute()) {
                $crit_res = $crit_q->get_result()->fetch_assoc();
                if ($crit_res) $crit_name = $crit_res['name'];
            }

            $msg = $new_val ? "Criterion is now required." : "Criterion is no longer required.";
            $action_text = $new_val ? "marked as required" : "marked as not required";
            log_activity($conn, $current_user_id, 'Updated', 'Criteria', "Toggled requirement for criterion: $crit_name ($action_text)");
            echo json_encode(["status" => "success", "message" => $msg]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update requirement."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
