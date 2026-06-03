<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch']);
    exit;
}

if (empty($_SESSION['user']) || !User::has_permission($_SESSION['user']['id'], 'manage_roles')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'get_permissions':
        $role_id = (int)($_POST['role_id'] ?? 0);
        if ($role_id > 0) {
            $perms = Role::get_role_permissions($role_id);
            echo json_encode(['success' => true, 'permissions' => $perms]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid role ID']);
        }
        break;

    case 'add_permission':
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $module = $_POST['module'] ?? '';
        
        if (!empty($name) && !empty($module)) {
            try {
                $success = Role::add_permission($name, $desc, $module);
                $msg = $success ? 'Permission added successfully' : 'Failed to add permission';
                
                if (!$success && $conn->errno === 1062) {
                    $msg = "Error: A permission token named '$name' already exists.";
                }

                echo json_encode(['success' => $success, 'message' => $msg]);
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
                $msg = (strpos($errMsg, 'Duplicate entry') !== false) 
                    ? "Error: Permission token '$name' already exists." 
                    : "System Error: Unable to process request due to a database conflict.";
                echo json_encode(['success' => false, 'message' => $msg]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Permission name and module are required']);
        }
        break;

    case 'get_modules':
        $modules = Role::get_unique_modules();
        echo json_encode(['success' => true, 'modules' => array_column($modules, 'module')]);
        break;

    case 'update_permission':
        $id = (int)($_POST['id'] ?? 0);
        $name = $_POST['name'] ?? '';
        $desc = $_POST['description'] ?? '';
        $module = $_POST['module'] ?? '';
        
        if ($id > 0 && !empty($name) && !empty($module)) {
            try {
                $success = Role::update_permission($id, $name, $desc, $module);
                $msg = $success ? 'Permission updated' : 'Update failed';

                if (!$success && $conn->errno === 1062) {
                    $msg = "Error: The token '$name' is already in use.";
                }

                echo json_encode(['success' => $success, 'message' => $msg]);
            } catch (Exception $e) {
                $errMsg = $e->getMessage();
                $msg = (strpos($errMsg, 'Duplicate entry') !== false) 
                    ? "Error: Token '$name' already exists elsewhere." 
                    : "System Error: " . $errMsg;
                echo json_encode(['success' => false, 'message' => $msg]);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        }
        break;

    case 'delete_permission':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            $success = Role::delete_permission($id);
            echo json_encode(['success' => $success, 'message' => $success ? 'Permission deleted' : 'Deletion failed']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid ID']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
