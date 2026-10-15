<?php
require_once 'app/auth/helpers/email_helper.php';

echo "Testing OTP Send via Google Apps Script (Custom doPost)...\n";
$email = "test@example.com";
$otp = "654321";
$username = "Test User";

$res = sendVerificationEmail($email, $otp, $username);

echo "Response Body:\n" . ($res['message'] ?? 'No message') . "\n";
print_r($res);
?>
