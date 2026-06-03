<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Website.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch']);
    exit;
}

if (empty($_SESSION['user']) || !User::has_permission($_SESSION['user']['id'], 'manage_cms')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'mark_read':
        $message_id = (int)($_POST['message_id'] ?? 0);
        if ($message_id > 0) {
            $success = Website::mark_message_read($message_id);
            echo json_encode(['success' => $success, 'message' => $success ? 'Message marked as read' : 'Failed to update message']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid message ID']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
