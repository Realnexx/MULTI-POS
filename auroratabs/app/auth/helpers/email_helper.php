<?php
/**
 * Email Helper for AuroraTabs using Google Apps Script Bridge
 * 
 * This helper sends emails by making an HTTP POST request to a deployed
 * Google Apps Script Web App using cURL for better redirect handling.
 */

function sendVerificationEmail($email, $otp, $username = "User") {
    // Current Web App URL
    $gas_url = 'https://script.google.com/macros/s/AKfycbx_Crrx5oNzhygP5jFsyaYtCBNilMjyDqlplpVlIbnXZQRQuBKTPk60I7oRog4ScMg/exec';

    if (empty($gas_url) || $gas_url === 'YOUR_GOOGLE_APPS_SCRIPT_URL_HERE') {
        return ["status" => "error", "message" => "Google Apps Script URL not configured in email_helper.php"];
    }

    $data = [
        "email" => $email,
        "otp" => $otp,
        "username" => $username
    ];

    $json_data = json_encode($data);
    $ch = curl_init($gas_url);
    
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // CRITICAL for GAS
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    // Removed CURLOPT_POSTREDIR to allow redirect to convert back to GET for the result page
    // Optional: if dealing with SSL issues on local dev
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 

    $result = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($result === false) {
        return ["status" => "error", "message" => "cURL Error: " . $error];
    }

    if ($http_code !== 200) {
        return ["status" => "error", "message" => "HTTP Error Code: " . $http_code . ". Raw result: " . substr($result, 0, 100)];
    }

    $response = json_decode($result, true);
    
    // Debugging: If decoding fails, the result might be HTML (e.g., Google's error or login page)
    if (json_last_error() !== JSON_ERROR_NONE) {
        return [
            "status" => "error", 
            "message" => "Invalid JSON from GAS. Status: " . $http_code . ". Raw: " . htmlspecialchars(substr($result, 0, 150))
        ];
    }

    if (isset($response['status']) && $response['status'] === 'success') {
        return ["status" => "success", "message" => "OTP sent successfully via Google Apps Script."];
    } else {
        return [
            "status" => "error", 
            "message" => $response['message'] ?? "GAS Script Error (Check Script Editor Logs). Raw: " . $result
        ];
    }
}
?>
