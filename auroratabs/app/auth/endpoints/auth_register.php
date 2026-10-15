<?php
session_start();
header("Content-Type: application/json");
require_once '../config/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["status" => "error", "message" => "Invalid Request Method"]));
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

require_once '../helpers/security_helper.php';

// Common Fields
$first_name = sanitize($data['fname'] ?? '');
$middle_name = sanitize($data['mname'] ?? '');
$last_name = sanitize($data['lname'] ?? '');
$email = sanitize($data['email'] ?? '');
$password = $data['password'] ?? ''; // Don't sanitize password (could mangle characters)
$mobile = sanitize($data['mobile'] ?? '');
$role_id = intval($data['role_id'] ?? 5); 
$username = sanitize($data['username'] ?? '');
$passwordHint = sanitize($data['passwordHint'] ?? '');

if (empty($first_name) || empty($last_name) || empty($email) || empty($password)) {
    die(json_encode(["status" => "error", "message" => "Required fields are missing."]));
}

// 1. Check if email already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
$check_stmt->bind_param("s", $email);
$check_stmt->execute();
$check_res = $check_stmt->get_result();
if ($check_res->num_rows > 0) {
    die(json_encode(["status" => "error", "message" => "Email already registered."]));
}
$check_stmt->close();

// 1.1. Check if username already exists
$check_stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
$check_stmt->bind_param("s", $username);
$check_stmt->execute();
$check_res = $check_stmt->get_result();
if ($check_res->num_rows > 0) {
    die(json_encode(["status" => "error", "message" => "Username already registered."]));
}
$check_stmt->close();

// 2. Hash Password
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// Start Transaction
$conn->begin_transaction();

try {
    // 3. Create User
    $stmt = $conn->prepare("INSERT INTO users (email, password_hash, first_name, last_name, middle_name, mobile, role_id, status, username, password_hint) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?)");
    $stmt->bind_param("ssssssiss", $email, $password_hash, $first_name, $last_name, $middle_name, $mobile, $role_id, $username, $passwordHint);
    $stmt->execute();
    $user_id = $stmt->insert_id;
    $stmt->close();

    // 4. Handle Organizer Specific Data (role_id = 2)
    if ($role_id == 2) {
        $org_name = $conn->real_escape_string($data['orgName'] ?? '');
        $event_name = $conn->real_escape_string($data['eventName'] ?? '');
        $event_type = $conn->real_escape_string($data['eventType'] ?? '');
        $start_date = $conn->real_escape_string($data['startDate'] ?? '');
        $end_date = $conn->real_escape_string($data['endDate'] ?? null);
        $location = $conn->real_escape_string($data['eventLocation'] ?? '');
        $plan = $conn->real_escape_string($data['selectedPlan'] ?? 'starter');

        // Create Organization
        $org_stmt = $conn->prepare("INSERT INTO organizations (owner_id, name, email, phone) VALUES (?, ?, ?, ?)");
        $org_stmt->bind_param("isss", $user_id, $org_name, $email, $mobile);
        $org_stmt->execute();
        $org_id = $org_stmt->insert_id;
        $org_stmt->close();

        // Create Competition
        $comp_stmt = $conn->prepare("INSERT INTO competitions (organization_id, created_by, title, type, plan_type, venue, start_date, end_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $comp_stmt->bind_param("iissssss", $org_id, $user_id, $event_name, $event_type, $plan, $location, $start_date, $end_date);
        $comp_stmt->execute();
        $comp_id = $comp_stmt->insert_id;
        $comp_stmt->close();
        
        // Store competition ID and plan in session for payment processing
        $_SESSION['reg_comp_id'] = $comp_id;
        $_SESSION['reg_plan'] = $plan;
    }

    // 5. Generate OTP (6-digits)
    $otp = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
    $expires_at = date('Y-m-d H:i:s', strtotime('+3 minutes'));

    // Update DB with OTP
    $otp_stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $otp_stmt->bind_param("ssi", $otp, $expires_at, $user_id);
    $otp_stmt->execute();
    $otp_stmt->close();

    $_SESSION['reg_otp'] = $otp;
    $_SESSION['reg_user_id'] = $user_id;
    $_SESSION['reg_email'] = $email;

    // 6. Send Verification Email
    require_once '../helpers/email_helper.php';
    $email_result = sendVerificationEmail($email, $otp, $first_name . ' ' . $last_name);

    $conn->commit();
    
    $response = [
        "status" => "success", 
        "message" => "Registration successful. Please check your email for the verification code.",
        "otp_debug" => $otp // REMOVE IN PRODUCTION
    ];

    if ($email_result['status'] === 'error') {
        $response['email_status'] = "warning";
        $response['email_message'] = "Note: Automatic email delivery failed. " . $email_result['message'];
    }

    echo json_encode($response);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["status" => "error", "message" => "Registration failed: " . $e->getMessage()]);
}

$conn->close();
?>
