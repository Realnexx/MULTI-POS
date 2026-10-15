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
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.is_deleted = 0 
                  ORDER BY u.id DESC";
        $result = $conn->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password_hash']); // Security: remove hash from list
            $users[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $users]);
        break;

    case 'list_judges':
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.is_deleted = 0 AND r.name = 'judge' 
                  ORDER BY u.id DESC";
        $result = $conn->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password_hash']);
            $users[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $users]);
        break;

    case 'list_deleted':
        $query = "SELECT u.*, r.name as role_name 
                  FROM users u 
                  LEFT JOIN roles r ON u.role_id = r.id 
                  WHERE u.is_deleted = 1 
                  ORDER BY u.id DESC";
        $result = $conn->query($query);
        $users = [];
        while ($row = $result->fetch_assoc()) {
            unset($row['password_hash']);
            $users[] = $row;
        }
        echo json_encode(["status" => "success", "data" => $users]);
        break;

    case 'create':
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $role_id = intval($_POST['role_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? 'active');

        if (empty($first_name) || empty($last_name) || empty($username) || empty($password) || empty($role_id)) {
            die(json_encode(["status" => "error", "message" => "All required fields must be filled."]));
        }

        // Check if username exists
        $check = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Username already exists."]));
        }

           // Check if email exists
        $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check->bind_param("s", $email);
        $check->execute();
        if ($check->get_result()->num_rows > 0) {
            die(json_encode(["status" => "error", "message" => "Email already exists."]));
        }

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("INSERT INTO users (first_name, last_name, email, username, password_hash, role_id, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssis", $first_name, $last_name, $email, $username, $password_hash, $role_id, $status);
        
        if ($stmt->execute()) {
            // Fetch role name for detailed logging
            $role_name = "ID: $role_id";
            $role_q = $conn->prepare("SELECT name FROM roles WHERE id = ?");
            $role_q->bind_param("i", $role_id);
            if ($role_q->execute()) {
                $role_res = $role_q->get_result()->fetch_assoc();
                if ($role_res) $role_name = $role_res['name'];
            }

            log_activity($conn, $current_user_id, 'Created', 'Users', "Added new user: $username ($role_name)");
            echo json_encode(["status" => "success", "message" => "User created successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to create user: " . $conn->error]);
        }
        break;

    case 'update':
        $id = intval($_POST['id'] ?? 0);
        $first_name = sanitize($_POST['first_name'] ?? '');
        $last_name = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $username = sanitize($_POST['username'] ?? '');
        $role_id = intval($_POST['role_id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');

        if ($id <= 0 || empty($first_name) || empty($last_name) || empty($username) || empty($role_id)) {
            die(json_encode(["status" => "error", "message" => "Invalid input data."]));
        }

        $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, username = ?, role_id = ?, status = ? WHERE id = ?");
        $stmt->bind_param("ssssisi", $first_name, $last_name, $email, $username, $role_id, $status, $id);

        if ($stmt->execute()) {
            // Fetch role name for detailed logging
            $role_name = "ID: $role_id";
            $role_q = $conn->prepare("SELECT name FROM roles WHERE id = ?");
            $role_q->bind_param("i", $role_id);
            if ($role_q->execute()) {
                $role_res = $role_q->get_result()->fetch_assoc();
                if ($role_res) $role_name = $role_res['name'];
            }

            log_activity($conn, $current_user_id, 'Updated', 'Users', "Updated user profile: $username (Role: $role_name)");
            echo json_encode(["status" => "success", "message" => "User updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update user: " . $conn->error]);
        }
        break;

    case 'delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $stmt = $conn->prepare("UPDATE users SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Fetch username for logging
            $target_username = "ID: $id";
            $user_q = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $user_q->bind_param("i", $id);
            if ($user_q->execute()) {
                $user_res = $user_q->get_result()->fetch_assoc();
                if ($user_res) $target_username = $user_res['username'];
            }

            log_activity($conn, $current_user_id, 'Deleted', 'Users', "Soft-deleted user: $target_username");
            echo json_encode(["status" => "success", "message" => "User deleted successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete user."]);
        }
        break;

    case 'restore':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        $stmt = $conn->prepare("UPDATE users SET is_deleted = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Fetch username for logging
            $target_username = "ID: $id";
            $user_q = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $user_q->bind_param("i", $id);
            if ($user_q->execute()) {
                $user_res = $user_q->get_result()->fetch_assoc();
                if ($user_res) $target_username = $user_res['username'];
            }

            log_activity($conn, $current_user_id, 'Restored', 'Users', "Restored user: $target_username from archive");
            echo json_encode(["status" => "success", "message" => "User restored successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to restore user."]);
        }
        break;

    case 'permanent_delete':
        $id = intval($_POST['id'] ?? 0);
        if ($id <= 0) die(json_encode(["status" => "error", "message" => "Invalid ID."]));

        // Fetch username for logging before deletion
        $target_username = "ID: $id";
        $user_q = $conn->prepare("SELECT username FROM users WHERE id = ?");
        $user_q->bind_param("i", $id);
        if ($user_q->execute()) {
            $user_res = $user_q->get_result()->fetch_assoc();
            if ($user_res) $target_username = $user_res['username'];
        }

        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            log_activity($conn, $current_user_id, 'Deleted', 'Users', "Permanently deleted user: $target_username from archive");
            echo json_encode(["status" => "success", "message" => "User permanently deleted."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to delete user permanently."]);
        }
        break;

    case 'toggle_status':
        $id = intval($_POST['id'] ?? 0);
        $status = sanitize($_POST['status'] ?? '');
        if ($id <= 0 || empty($status)) die(json_encode(["status" => "error", "message" => "Invalid data."]));

        $new_status = ($status === 'active') ? 'suspended' : 'active';
        $stmt = $conn->prepare("UPDATE users SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $new_status, $id);

        if ($stmt->execute()) {
            // Fetch username for logging
            $target_username = "ID: $id";
            $user_q = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $user_q->bind_param("i", $id);
            if ($user_q->execute()) {
                $user_res = $user_q->get_result()->fetch_assoc();
                if ($user_res) $target_username = $user_res['username'];
            }

            log_activity($conn, $current_user_id, 'Updated', 'Users', "Toggled status for user $target_username to $new_status");
            echo json_encode(["status" => "success", "message" => "Status updated to " . $new_status]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update status."]);
        }
        break;

    case 'reset_password':
        $id = intval($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        if ($id <= 0 || empty($password)) die(json_encode(["status" => "error", "message" => "Invalid data."]));

        $password_hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $stmt->bind_param("si", $password_hash, $id);

        if ($stmt->execute()) {
            // Fetch username for logging
            $target_username = "ID: $id";
            $user_q = $conn->prepare("SELECT username FROM users WHERE id = ?");
            $user_q->bind_param("i", $id);
            if ($user_q->execute()) {
                $user_res = $user_q->get_result()->fetch_assoc();
                if ($user_res) $target_username = $user_res['username'];
            }

            log_activity($conn, $current_user_id, 'Updated', 'Users', "Reset password for user: $target_username");
            echo json_encode(["status" => "success", "message" => "Password reset successful."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to reset password."]);
        }
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
