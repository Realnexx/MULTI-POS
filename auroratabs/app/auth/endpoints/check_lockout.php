<?php
date_default_timezone_set('Asia/Manila');
header("Content-Type: application/json");
require_once '../config/db_connection.php';

$ip_address = $_SERVER['REMOTE_ADDR'];

require_once '../helpers/security_helper.php';
$username = sanitize($_GET['username'] ?? '');

try {
    // Check if IP OR Username is currently locked out
    $sql = "SELECT attempts, lockout_until FROM login_attempts WHERE ip_address = ?";
    $params = ["s", $ip_address];
    
    if (!empty($username)) {
        $sql .= " OR username_attempted = ?";
        $params[0] .= "s";
        $params[] = $username;
    }
    
    $sql .= " ORDER BY lockout_until DESC LIMIT 1"; // Get the one with a lockout (if any)
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(...$params);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $lockout_until = $row['lockout_until'];
        
        if ($lockout_until) {
            $lockout_time = strtotime($lockout_until);
            $current_time = time();
            
            if ($current_time < $lockout_time) {
                $remaining = $lockout_time - $current_time;
                echo json_encode([
                    "status" => "locked", 
                    "remaining_time" => $remaining,
                    "message" => "Too many failed attempts. Please try again in {$remaining} seconds."
                ]);
                exit;
            } else {
                // Lockout expired, but we don't reset attempts here yet.
                // We reset only on successful login or if we want to reset counter after expiration.
                // Strategy: If time expired, we allow them to try.
                // If they fail again, we check if we should escalate.
            }
        }
    }

    echo json_encode(["status" => "allowed"]);

} catch (Exception $e) {
    echo json_encode(["status" => "error", "message" => $e->getMessage()]);
}

$conn->close();
?>
