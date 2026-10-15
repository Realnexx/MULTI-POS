<?php
session_start();
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';
require_once '../../auth/helpers/log_helper.php';

if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}
header('Content-Type: application/json');
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$current_user_id = $_SESSION['user_id'] ?? null;

if ($action === 'get_settings') {
    $stmt = $conn->prepare("SELECT setting_key, setting_value FROM settings WHERE `group` = 'security'");
    $stmt->execute();
    $result = $stmt->get_result();
    
    $settings = [];
    while ($row = $result->fetch_assoc()) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
    
    echo json_encode(['status' => 'success', 'data' => $settings]);
    exit();
}

if ($action === 'update_settings') {
    $conn->begin_transaction();
    try {
        $settings = $_POST['settings'] ?? [];
        
        $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, `group`) 
                                VALUES (?, ?, 'security') 
                                ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        
        foreach ($settings as $key => $value) {
            // Basic sanitization
            $key = preg_replace('/[^a-zA-Z0-9_]/', '', $key);
            $stmt->bind_param("ss", $key, $value);
            $stmt->execute();
        }
        
        $conn->commit();
        log_activity($conn, $current_user_id, 'Updated', 'Security Settings', 'Modified system security policies');
        
        echo json_encode(['status' => 'success', 'message' => 'Security policies applied successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => 'Failed to update settings: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
