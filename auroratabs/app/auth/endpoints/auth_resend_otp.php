<?php
header("Content-Type: application/json");
require_once '../config/db_connection.php';
require_once '../helpers/email_helper.php';
require_once '../helpers/security_helper.php';

session_start();

// Get the stored email and user_id from session
// These are set in auth_register.php when the initial registration succeeds
// 1. Get from Request (for standalone) or Session (for registration)
$email = $_POST['email'] ?? ($_SESSION['reg_email'] ?? null);
$user_id = $_POST['user_id'] ?? ($_SESSION['reg_user_id'] ?? null);

if (!$email || !$user_id) {
    echo json_encode([
        "status" => "error",
        "message" => "Session expired or invalid. Please restart the registration process."
    ]);
    exit;
}

// Generate new OTP
$otp = sprintf("%06d", mt_rand(1, 999999));
$expires_at = date('Y-m-d H:i:s', strtotime('+3 minutes'));

try {
    // Update database
    $stmt = $conn->prepare("UPDATE users SET otp_code = ?, otp_expires_at = ? WHERE id = ?");
    $stmt->bind_param("ssi", $otp, $expires_at, $user_id);
    
    if ($stmt->execute()) {
        // Fetch user's full name for the email
        $name_stmt = $conn->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $name_stmt->bind_param("i", $user_id);
        $name_stmt->execute();
        $result = $name_stmt->get_result();
        $user = $result->fetch_assoc();
        $full_name = $user ? ($user['first_name'] . ' ' . $user['last_name']) : "User";
        
        // Send email
        $email_result = sendVerificationEmail($email, $otp, $full_name);
        
        if ($email_result['status'] === 'success') {
            echo json_encode([
                "status" => "success",
                "message" => "A new verification code has been sent to your email."
            ]);
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Failed to send email: " . $email_result['message']
            ]);
        }
    } else {
        throw new Exception("Failed to update OTP in database.");
    }
} catch (Exception $e) {
    echo json_encode([
        "status" => "error",
        "message" => "An error occurred: " . $e->getMessage()
    ]);
}
?>
