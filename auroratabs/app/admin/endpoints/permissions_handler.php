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
    case 'get_role_permissions':
        $role_id = intval($_POST['role_id'] ?? 0);
        if ($role_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid Role ID."]));
        }

        // Fetch all modules and join with permissions for this role
        // Using LEFT JOIN so even modules without specific permission entries show up
        $query = "SELECT m.id as module_id, m.name as module_name, 
                         COALESCE(rp.can_view, 0) as can_view, 
                         COALESCE(rp.can_manage, 0) as can_manage, 
                         COALESCE(rp.can_delete, 0) as can_delete, 
                         COALESCE(rp.can_export, 0) as can_export
                  FROM modules m
                  LEFT JOIN role_permissions rp ON m.id = rp.module_id AND rp.role_id = ?
                  ORDER BY m.name ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $role_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $permissions = [];
        while ($row = $result->fetch_assoc()) {
            $permissions[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $permissions]);
        break;

    case 'save_permissions':
        $role_id = intval($_POST['role_id'] ?? 0);
        $perms_data = $_POST['permissions'] ?? [];

        if ($role_id <= 0) {
            die(json_encode(["status" => "error", "message" => "Invalid Role ID."]));
        }

        $conn->begin_transaction();

        try {
            // Clear existing permissions for this role
            $stmt = $conn->prepare("DELETE FROM role_permissions WHERE role_id = ?");
            $stmt->bind_param("i", $role_id);
            $stmt->execute();

            // Insert new permissions
            if (!empty($perms_data)) {
                $stmt = $conn->prepare("INSERT INTO role_permissions (role_id, module_id, can_view, can_manage, can_delete, can_export) VALUES (?, ?, ?, ?, ?, ?)");
                
                foreach ($perms_data as $p) {
                    $mid = intval($p['module_id']);
                    $view = intval($p['can_view']);
                    $manage = intval($p['can_manage']);
                    $delete = intval($p['can_delete']);
                    $export = intval($p['can_export']);
                    
                    $stmt->bind_param("iiiiii", $role_id, $mid, $view, $manage, $delete, $export);
                    $stmt->execute();
                }
            }

            $conn->commit();
            
            // Fetch role name for detailed logging
            $role_name = "ID: $role_id";
            $role_q = $conn->prepare("SELECT name FROM roles WHERE id = ?");
            $role_q->bind_param("i", $role_id);
            if ($role_q->execute()) {
                $role_res = $role_q->get_result()->fetch_assoc();
                if ($role_res) $role_name = $role_res['name'];
            }

            log_activity($conn, $_SESSION['user_id'] ?? 0, 'Updated', 'Permissions', "Updated permissions for role: $role_name");
            echo json_encode(["status" => "success", "message" => "Permissions updated successfully."]);
        } catch (Exception $e) {
            $conn->rollback();
            echo json_encode(["status" => "error", "message" => "Failed to save permissions: " . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
