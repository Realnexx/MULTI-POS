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
        $result = $conn->query("SELECT * FROM modules ORDER BY name ASC");
        $modules = [];
        while ($row = $result->fetch_assoc()) {
            $modules[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $modules]);
        break;

    case 'create':
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if (empty($name)) {
            die(json_encode(["status" => "error", "message" => "Module name is required."]));
        }

        $stmt = $conn->prepare("INSERT INTO modules (name, slug, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $description);
        
        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Created', 'Modules', "Added new module: $name");
            echo json_encode(["status" => "success", "message" => "Module created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create module: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));

        if ($id <= 0 || empty($name)) {
            die(json_encode(["status" => "error", "message" => "Valid ID and name are required."]));
        }

        $stmt = $conn->prepare("UPDATE modules SET name = ?, slug = ?, description = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $slug, $description, $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Updated', 'Modules', "Updated module: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Module updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update module: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        }

        // Fetch name for log BEFORE deletion
        $module_name = "ID: $id";
        $mod_q = $conn->prepare("SELECT name FROM modules WHERE id = ?");
        $mod_q->bind_param("i", $id);
        if ($mod_q->execute()) {
            $mod_res = $mod_q->get_result()->fetch_assoc();
            if ($mod_res) $module_name = $mod_res['name'];
        }

        $stmt = $conn->prepare("DELETE FROM modules WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Modules', "Deleted module: $module_name");
            echo json_encode(["status" => "success", "message" => "Module deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete module: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
