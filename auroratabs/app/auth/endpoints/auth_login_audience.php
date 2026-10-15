<?php
session_start();
header("Content-Type: application/json");
require_once '../config/db_connection.php';
require_once '../helpers/jwt_utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

$username = $conn->real_escape_string($data['username'] ?? '');
$password = $data['password'] ?? '';
$role_id = 5; // Audience

if (empty($username) || empty($password)) {
    die(json_encode(["status" => "error", "message" => "Username and password are required."]));
}

$ip_address = $_SERVER['REMOTE_ADDR'];
date_default_timezone_set('Asia/Manila');
$current_time = time();

// 0. Check Rate Limiting
$stmt = $conn->prepare("SELECT id, attempts, lockout_until FROM login_attempts WHERE ip_address = ?");
$stmt->bind_param("s", $ip_address);
$stmt->execute();
$res_limit = $stmt->get_result();
$attempt_record = $res_limit->fetch_assoc();

if ($attempt_record && $attempt_record['lockout_until']) {
    $lockout_time = strtotime($attempt_record['lockout_until']);
    if ($current_time < $lockout_time) {
        $remaining = $lockout_time - $current_time;
        die(json_encode([
            "status" => "locked", 
            "remaining_time" => $remaining,
            "message" => "System locked. Please wait {$remaining} seconds."
        ]));
    }
}

// 1. Verify credentials and role
$stmt = $conn->prepare("SELECT id, username, email, password_hash, first_name, last_name, role_id, status, is_deleted FROM users WHERE username = ? AND role_id = ? LIMIT 1");
$stmt->bind_param("si", $username, $role_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    if ($user['is_deleted'] == 1) {
         die(json_encode(["status" => "error", "message" => "This account has been deactivated."]));
    }

    if ($user['status'] === 'pending') {
        die(json_encode([
            "status" => "pending", 
            "message" => "Your account is not yet verified. Please check your email for the verification code.",
            "user_id" => $user['id'],
            "email" => $user['email']
        ]));
    }

    if ($user['status'] === 'suspended') {
        die(json_encode(["status" => "error", "message" => "Your account is suspended. Please call the administrator."]));
    }

    if ($user['status'] !== 'active') {
        die(json_encode(["status" => "error", "message" => "Account status: " . $user['status'] . "."]));
    }

    if (password_verify($password, $user['password_hash'])) {
        $payload = [
            'iss' => 'http://localhost/auroratabs',
            'aud' => 'http://localhost/auroratabs',
            'iat' => time(),
            'exp' => time() + (60 * 60 * 2),
            'data' => [
                'user_id' => $user['id'],
                'username' => $username,
                'role_id' => $user['role_id'],
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ]
        ];

        $token = JWT::encode($payload);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['username'] = $username;
        $_SESSION['token'] = $token;

        if ($attempt_record) {
            $conn->query("UPDATE login_attempts SET attempts = 0, lockout_until = NULL WHERE ip_address = '$ip_address'");
        }

        echo json_encode([
            "status" => "success",
            "message" => "Login Successful",
            "token" => $token,
            "redirect" => "../admin/live-results.php" // Redirect to audience dashboard
        ]);
    } else {
        $attempts = handleFailedAttempt($conn, $ip_address, $username, $attempt_record);
        $mod = $attempts % 3;
        $attempts_left = ($mod == 0) ? 0 : (3 - $mod);
        $msg = "Invalid credentials. " . ($attempts_left > 0 ? "$attempts_left attempts remaining." : "System locked.");
        echo json_encode(["status" => "error", "message" => $msg]);
    }
} else {
    $attempts = handleFailedAttempt($conn, $ip_address, $username, $attempt_record);
    $mod = $attempts % 3;
    $attempts_left = ($mod == 0) ? 0 : (3 - $mod);
    $msg = "Invalid credentials. " . ($attempts_left > 0 ? "$attempts_left attempts remaining." : "System locked.");
    echo json_encode(["status" => "error", "message" => $msg]);
}

function handleFailedAttempt($conn, $ip, $username, $record) {
    $current_time = date('Y-m-d H:i:s');
    $new_attempts = ($record ? $record['attempts'] : 0) + 1;
    $lockout_until = NULL;

    if ($new_attempts > 0 && $new_attempts % 3 == 0) {
        $cycle = $new_attempts / 3;
        $lockout_until = date('Y-m-d H:i:s', time() + (30 * $cycle));
    }

    if ($record) {
        $stmt = $conn->prepare("UPDATE login_attempts SET attempts = ?, last_attempt_at = ?, lockout_until = ?, username_attempted = ? WHERE ip_address = ?");
        $stmt->bind_param("issss", $new_attempts, $current_time, $lockout_until, $username, $ip);
    } else {
        $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username_attempted, attempts, last_attempt_at, lockout_until) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("ssiss", $ip, $username, $new_attempts, $current_time, $lockout_until);
    }
    $stmt->execute();
    return $new_attempts;
}

$conn->close();
?>
