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
    case 'save':
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $expression = $_POST['expression'] ?? '';
        $components = $_POST['components'] ?? ''; // Expecting JSON string
        $is_active = intval($_POST['is_active'] ?? 1);
        $is_public = intval($_POST['is_public'] ?? 0);

        if (empty($name) || empty($expression)) {
            die(json_encode(["status" => "error", "message" => "Formula name and expression are required."]));
        }

        if ($id > 0) {
            // Update
            $stmt = $conn->prepare("UPDATE formulas SET name = ?, expression = ?, components = ?, is_active = ?, is_public = ? WHERE id = ?");
            $stmt->bind_param("sssiii", $name, $expression, $components, $is_active, $is_public, $id);
            $action_text = "Updated";
        } else {
            // Create
            $stmt = $conn->prepare("INSERT INTO formulas (name, expression, components, is_active, is_public) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $name, $expression, $components, $is_active, $is_public);
            $action_text = "Created";
        }

        if ($stmt->execute()) {
            $formula_id = ($id > 0) ? $id : $conn->insert_id;
            log_activity($conn, $current_user_id, $action_text, 'Formulas', "$action_text formula: $name");
            echo json_encode(["status" => "success", "message" => "Formula $action_text successfully.", "id" => $formula_id]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to save formula: " . $conn->error]);
        }
        break;

    case 'list':
        $query = "SELECT id, name, is_active, is_public, created_at FROM formulas WHERE is_active >= 0 ORDER BY name ASC";
        $result = $conn->query($query);
        $formulas = [];
        while ($row = $result->fetch_assoc()) {
            $formulas[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $formulas]);
        break;

    case 'get':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $stmt = $conn->prepare("SELECT * FROM formulas WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            echo json_encode(["status" => "success", "data" => $row]);
        } else {
            echo json_encode(["status" => "error", "message" => "Formula not found."]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch name for log
        $name_q = $conn->prepare("SELECT name FROM formulas WHERE id = ?");
        $name_q->bind_param("i", $id);
        $name_q->execute();
        $name_res = $name_q->get_result()->fetch_assoc();
        $formula_name = $name_res ? $name_res['name'] : "ID: $id";

        $stmt = $conn->prepare("DELETE FROM formulas WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Formulas', "Deleted formula: $formula_name");
            echo json_encode(["status" => "success", "message" => "Formula deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete formula."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
