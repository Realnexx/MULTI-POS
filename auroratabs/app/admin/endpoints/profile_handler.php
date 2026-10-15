<?php
session_start();
header("Content-Type: application/json");
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';
require_once '../../auth/helpers/log_helper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid Request Method']);
    exit();
}

$action = sanitize($_POST['action'] ?? '');
$user_id = $_SESSION['user_id'];

switch ($action) {
    case 'get_profile':
        // Fetch User Details
        $stmt = $conn->prepare("SELECT u.first_name, u.middle_name, u.last_name, u.email, u.mobile, u.username, u.role_id, u.status, u.avatar_url, r.name as role_name 
                                FROM users u 
                                LEFT JOIN roles r ON u.role_id = r.id 
                                WHERE u.id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$user) {
            echo json_encode(["status" => "error", "message" => "User not found."]);
            exit();
        }

        // Fetch Recent Activity
        $log_stmt = $conn->prepare("SELECT action, details, created_at FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
        $log_stmt->bind_param("i", $user_id);
        $log_stmt->execute();
        $logs_result = $log_stmt->get_result();
        $logs = [];
        while ($row = $logs_result->fetch_assoc()) {
            $logs[] = $row;
        }
        $log_stmt->close();

        echo json_encode([
            "status" => "success", 
            "data" => [
                "user" => $user,
                "logs" => $logs
            ]
        ]);
        break;

    case 'update_avatar':
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(["status" => "error", "message" => "No file uploaded or upload error."]);
            exit();
        }

        $file = $_FILES['avatar'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $maxSize = 5 * 1024 * 1024; // 5MB

        // Validate Type
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);
        if (!in_array($mime, $allowedTypes)) {
            echo json_encode(["status" => "error", "message" => "Invalid file type. Only JPG, PNG, GIF, WEBP allowed."]);
            exit();
        }

        // Validate Size
        if ($file['size'] > $maxSize) {
            echo json_encode(["status" => "error", "message" => "File too large. Max 5MB."]);
            exit();
        }

        // Generate Filename
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = 'user_' . $user_id . '_' . time() . '.' . $ext;
        $uploadDir = '../src/assets/images/profile/';
        
        // Ensure dir exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $targetPath = $uploadDir . $filename;
        $dbPath = 'src/assets/images/profile/' . $filename; // Relative for DB

        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            // Update DB
            $stmt = $conn->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
            $stmt->bind_param("si", $dbPath, $user_id);
            if ($stmt->execute()) {
                log_activity($conn, $user_id, 'Updated', 'Profile', 'User updated their profile picture');
                echo json_encode(["status" => "success", "message" => "Avatar updated successfully.", "url" => $dbPath]);
            } else {
                 echo json_encode(["status" => "error", "message" => "Database update failed."]);
            }
            $stmt->close();
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to move uploaded file."]);
        }
        break;

    case 'update_details':
        $first_name = sanitize($_POST['fname'] ?? '');
        $middle_name = sanitize($_POST['mname'] ?? '');
        $last_name = sanitize($_POST['lname'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $mobile = sanitize($_POST['mobile'] ?? '');
        $username = sanitize($_POST['username'] ?? '');

        if (empty($first_name) || empty($last_name) || empty($email)) {
            echo json_encode(["status" => "error", "message" => "First Name, Last Name, and Email are required."]);
            exit();
        }

        // Check if email taken by another user
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            echo json_encode(["status" => "error", "message" => "Email already acts for another user."]);
            exit();
        }
        $stmt->close();

        // Check if username taken by another user
        if (!empty($username)) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->bind_param("si", $username, $user_id);
            $stmt->execute();
            if ($stmt->get_result()->num_rows > 0) {
                echo json_encode(["status" => "error", "message" => "Username already taken."]);
                exit();
            }
            $stmt->close();
        }

        // Update User
        $update_sql = "UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, email = ?, mobile = ?, username = ? WHERE id = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("ssssssi", $first_name, $middle_name, $last_name, $email, $mobile, $username, $user_id);

        if ($stmt->execute()) {
            log_activity($conn, $user_id, 'Updated', 'Profile', 'User updated their profile details');
            echo json_encode(["status" => "success", "message" => "Profile updated successfully."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update profile: " . $conn->error]);
        }
        $stmt->close();
        break;

    case 'update_password':
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            echo json_encode(["status" => "error", "message" => "All password fields are required."]);
            exit();
        }

        if ($new_password !== $confirm_password) {
            echo json_encode(["status" => "error", "message" => "New passwords do not match."]);
            exit();
        }

        if (strlen($new_password) < 8) {
            echo json_encode(["status" => "error", "message" => "New password must be at least 8 characters long."]);
            exit();
        }

        // Verify current password
        $stmt = $conn->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows === 1) {
            $row = $res->fetch_assoc();
            if (!password_verify($current_password, $row['password_hash'])) {
                echo json_encode(["status" => "error", "message" => "Incorrect current password."]);
                exit();
            }
        } else {
             echo json_encode(["status" => "error", "message" => "User not found."]);
             exit();
        }
        $stmt->close();

        // Update password
        $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $update_stmt->bind_param("si", $new_hash, $user_id);

        if ($update_stmt->execute()) {
            log_activity($conn, $user_id, 'Updated', 'Security', 'User changed their password');
            echo json_encode(["status" => "success", "message" => "Password updated successfully. Please re-login."]);
        } else {
            echo json_encode(["status" => "error", "message" => "Failed to update password."]);
        }
        $update_stmt->close();
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action."]);
        break;
}

$conn->close();
?>
