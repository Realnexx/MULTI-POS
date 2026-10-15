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

switch ($action) {
    case 'list':
        $query = "SELECT r.*, 
                         (SELECT COUNT(*) FROM role_permissions rp WHERE rp.role_id = r.id AND (can_view=1 OR can_manage=1 OR can_delete=1 OR can_export=1)) as perm_count 
                  FROM roles r ORDER BY name ASC";
        $result = $conn->query($query);
        $roles = [];
        while ($row = $result->fetch_assoc()) {
            $roles[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $roles]);
        break;

    case 'create':
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        if (empty($name)) {
            die(json_encode(["status" => "error", "message" => "Role title is required."]));
        }

        $stmt = $conn->prepare("INSERT INTO roles (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        
        if ($stmt->execute()) {
            log_activity($conn, $_SESSION['user_id'] ?? 0, 'Created', 'Roles', "Added new role: $name");
            echo json_encode(["status" => "success", "message" => "Role created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create role: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $name = sanitize($_POST['name'] ?? '');
        $description = sanitize($_POST['description'] ?? '');

        if ($id <= 0 || empty($name)) {
            die(json_encode(["status" => "error", "message" => "Valid ID and title are required."]));
        }

        $stmt = $conn->prepare("UPDATE roles SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);

        if ($stmt->execute()) {
            log_activity($conn, $_SESSION['user_id'] ?? 0, 'Updated', 'Roles', "Updated role: $name (ID: $id)");
            echo json_encode(["status" => "success", "message" => "Role updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update role: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);

        if ($id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid ID."]));
        }

        // Check if role is in use? For now, we allow deletion as per user request for CRUD.
        // Usually, 'admin' role shouldn't be deleted.
        $role_check_stmt = $conn->prepare("SELECT name FROM roles WHERE id = ?");
        $role_check_stmt->bind_param("i", $id);
        $role_check_stmt->execute();
        $role_res = $role_check_stmt->get_result()->fetch_assoc();
        
        if ($role_res && strtolower($role_res['name']) === 'admin') {
            die(json_encode(["status" => "error", "message" => "The Admin role cannot be deleted."]));
        }

        $stmt = $conn->prepare("DELETE FROM roles WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $_SESSION['user_id'] ?? 0, 'Deleted', 'Roles', "Deleted role ID: $id (" . ($role_res['name'] ?? 'Unknown') . ")");
            echo json_encode(["status" => "success", "message" => "Role deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete role: " . $conn->error]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
