<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Website.php';
require_once __DIR__ . '/../models/ActivityLog.php';

header('Content-Type: application/json');
$action = $_POST['action'] ?? '';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('cms_action', $action);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}







switch ($action) {
    case 'mark_read':
        $message_id = (int)($_POST['message_id'] ?? 0);
        if ($message_id > 0) {
            $success = Website::mark_message_read($message_id);
            if ($success) {
                ActivityLog::log('Message Marked Read', "Marked contact message ID {$message_id} as read");
            }
            echo json_encode(['success' => $success, 'message' => $success ? 'Message marked as read' : 'Failed to update message']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
