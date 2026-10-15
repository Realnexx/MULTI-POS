<?php
session_start();
header("Content-Type: application/json");
require_once '../config/db_connection.php';
require_once '../helpers/jwt_utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$data = json_decode(file_get_contents("php://input"), true);
// Fallback to POST if JSON body is empty (for standard form submission compatibility)
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

$username = $conn->real_escape_string($data['username'] ?? '');
$password = $data['password'] ?? '';
$role_id = intval($data['role'] ?? 0);

if (empty($username) || empty($password) || empty($role_id)) {
    die(json_encode(["status" => "error", "message" => "All fields are required."]));
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

if ($attempt_record) {
    if ($attempt_record['lockout_until']) {
        $lockout_time = strtotime($attempt_record['lockout_until']);
        if ($current_time < $lockout_time) {
             $remaining = $lockout_time - $current_time;
             die(json_encode([
                 "status" => "locked", 
                 "remaining_time" => $remaining,
                 "message" => "System locked. Please wait {$remaining} seconds."
             ]));
        } else {
             // Lockout expired.
             // We don't reset attempts immediately to 0 here to keep track of repeated offenses?
             // Simplest: If expired, let them try. If fail, extend duration.
        }
    }
}

// 1. Verify credentials and role
// Using prepared statement for security
$stmt = $conn->prepare("SELECT id, username, password_hash, first_name, last_name, role_id, status, is_deleted FROM users WHERE username = ? AND role_id = ? LIMIT 1");
$stmt->bind_param("si", $username, $role_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();

    // Check if account is deleted
    if ($user['is_deleted'] == 1) {
         die(json_encode(["status" => "error", "message" => "This account has been deactivated."]));
    }

    // Check Status
    if ($user['status'] === 'pending') {
        die(json_encode(["status" => "pending", "message" => "Your account is not yet verified. Please check your email for the verification code."]));
    }
    if ($user['status'] === 'suspended') {
        die(json_encode(["status" => "error", "message" => "Your account is suspended. Please call the administrator."]));
    }
    if ($user['status'] !== 'active') {
        die(json_encode(["status" => "error", "message" => "Account status: " . $user['status'] . ". Please contact support."]));
    }

    // Verify Password (assuming bcrypt)
    if (password_verify($password, $user['password_hash'])) {
        
        // Prepare Payload for JWT
        $payload = [
            'iss' => 'http://localhost/auroratabs', // Issuer
            'aud' => 'http://localhost/auroratabs', // Audience
            'iat' => time(), // Issued at
            'exp' => time() + (60 * 60 * 2), // Expiration time (2 hours)
            'data' => [
                'user_id' => $user['id'],
                'username' => $username,
                'role_id' => $user['role_id'],
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ]
        ];

        $token = JWT::encode($payload);

        // Also set PHP session for legacy/server-side checks if needed
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['username'] = $username;
        $_SESSION['token'] = $token;

        // Load permissions into session for fast access
        require_once '../helpers/security_helper.php';
        $_SESSION['permissions'] = loadRolePermissions($conn, $user['role_id']);

        // Reset Login Attempts on Success
        if ($attempt_record) {
            $conn->query("UPDATE login_attempts SET attempts = 0, lockout_until = NULL WHERE ip_address = '$ip_address'");
        }

        echo json_encode([
            "status" => "success",
            "message" => "Login Successful",
            "token" => $token,
            "redirect" => "../admin/index.php" // Redirect to admin dashboard
        ]);
    } else {
        // Handle Failed Attempt
        $attempts = handleFailedAttempt($conn, $ip_address, $username, $attempt_record);
        
        // Calculate attempts left based on modulo 3
        // If attempts = 1, mod = 1, left = 2
        // If attempts = 2, mod = 2, left = 1
        // If attempts = 3, mod = 0, left = 0 (Locked)
        // If attempts = 4, mod = 1, left = 2 (New cycle)
        $mod = $attempts % 3;
        $attempts_left = ($mod == 0) ? 0 : (3 - $mod);

        $msg = "Invalid Password.";
        if ($attempts_left > 0) {
            $msg .= " " . $attempts_left . " attempts remaining.";
        } else {
             $msg .= " System locked.";
        }
        echo json_encode(["status" => "error", "message" => $msg]);
    }
} else {
    // Handle Failed Attempt (User Not Found)
    $attempts = handleFailedAttempt($conn, $ip_address, $username, $attempt_record);
    $mod = $attempts % 3;
    $attempts_left = ($mod == 0) ? 0 : (3 - $mod);

    $msg = "User not found with this role.";
    if ($attempts_left > 0) {
        $msg .= " " . $attempts_left . " attempts remaining.";
    } else {
         $msg .= " System locked.";
    }
    echo json_encode(["status" => "error", "message" => $msg]);
}

function handleFailedAttempt($conn, $ip, $username, $record) {
    $current_time = date('Y-m-d H:i:s');
    $new_attempts = 0;
    
    if ($record) {
        $new_attempts = $record['attempts'] + 1;
        $lockout_until = NULL;

        // Check if we hit the limit (every 3 attempts)
        // 3rd attempt -> 30s
        // 6th attempt -> 60s
        // 9th attempt -> 90s
        if ($new_attempts > 0 && $new_attempts % 3 == 0) {
            $cycle = $new_attempts / 3;
            $penalty_seconds = 30 * $cycle;
            $lockout_timestamp = time() + $penalty_seconds;
            $lockout_until = date('Y-m-d H:i:s', $lockout_timestamp);
        }

        $stmt = $conn->prepare("UPDATE login_attempts SET attempts = ?, last_attempt_at = ?, lockout_until = ?, username_attempted = ? WHERE ip_address = ?");
        $stmt->bind_param("issss", $new_attempts, $current_time, $lockout_until, $username, $ip);
        $stmt->execute();
    } else {
        // First failed attempt
        $new_attempts = 1;
        $stmt = $conn->prepare("INSERT INTO login_attempts (ip_address, username_attempted, attempts, last_attempt_at) VALUES (?, ?, 1, ?)");
        $stmt->bind_param("sss", $ip, $username, $current_time);
        $stmt->execute();
    }
    return $new_attempts;
}

$stmt->close();
$conn->close();
?>
