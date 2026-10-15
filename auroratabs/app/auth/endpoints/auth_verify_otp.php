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

require_once '../helpers/security_helper.php';
$otp_input = sanitize($data['otp'] ?? '');
$user_id = $_SESSION['reg_user_id'] ?? '';

if (empty($otp_input)) {
    die(json_encode(["status" => "error", "message" => "OTP is required."]));
}

// 1. Verify OTP against Database
$stmt = $conn->prepare("SELECT otp_code, otp_expires_at FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$db_res = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$db_res || $db_res['otp_code'] !== $otp_input) {
    die(json_encode(["status" => "error", "message" => "Invalid verification code."]));
}

// 2. Check Expiration
$expires_at = strtotime($db_res['otp_expires_at']);
if (time() > $expires_at) {
    die(json_encode(["status" => "error", "message" => "Verification code has expired (3-minute limit)."]));
}

// 3. Update User Status and Clear OTP
$stmt = $conn->prepare("UPDATE users SET status = 'active', otp_code = NULL, otp_expires_at = NULL WHERE id = ?");
$stmt->bind_param("i", $user_id);

if ($stmt->execute()) {
    // Fetch user info for JWT
    $user_stmt = $conn->prepare("SELECT id, email, first_name, last_name, role_id FROM users WHERE id = ?");
    $user_stmt->bind_param("i", $user_id);
    $user_stmt->execute();
    $user = $user_stmt->get_result()->fetch_assoc();

    // Prepare JWT Payload
    $payload = [
        'iss' => 'http://localhost/auroratabs',
        'aud' => 'http://localhost/auroratabs',
        'iat' => time(),
        'exp' => time() + (60 * 60 * 2), // 2 hours
        'data' => [
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role_id' => $user['role_id'],
            'name' => $user['first_name'] . ' ' . $user['last_name']
        ]
    ];

    $token = JWT::encode($payload);

    // Set PHP session
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['email'] = $user['email'];
    $_SESSION['token'] = $token;

    // Clear registration session data
    unset($_SESSION['reg_otp']);
    unset($_SESSION['reg_user_id']);
    unset($_SESSION['reg_email']);

    $response = [
        "status" => "success",
        "message" => "Verification successful. Account activated.",
        "token" => $token,
        "redirect" => ($user['role_id'] == 2) ? "../admin/index.php" : "../audience/index.php"
    ];
    
    // For organizers, return competition info for payment
    if ($user['role_id'] == 2 && isset($_SESSION['reg_comp_id'])) {
        $response['comp_id'] = $_SESSION['reg_comp_id'];
        $response['plan'] = $_SESSION['reg_plan'] ?? 'starter';
    }
    
    echo json_encode($response);
} else {
    echo json_encode(["status" => "error", "message" => "Failed to activate account."]);
}

$stmt->close();
$conn->close();
?>
