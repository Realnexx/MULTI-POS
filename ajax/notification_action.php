<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/StockNotification.php';

header('Content-Type: application/json');

$action = $_REQUEST['action'] ?? 'get_alerts';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('notification_action', $action);
$branch_id = !empty($_SESSION['user']['branch_id']) ? (int)$_SESSION['user']['branch_id'] : (!empty($_REQUEST['branch_id']) ? (int)$_REQUEST['branch_id'] : null);

if (User::has_all_branch_access($_SESSION['user']['id'])) {
    $branch_id=!empty($_REQUEST['branch_id'])?(int)$_REQUEST['branch_id']:null;
} else {
    $branch_id=!empty($_SESSION['user']['branch_id'])?(int)$_SESSION['user']['branch_id']:null;
}

switch ($action) {
    case 'get_alerts':
        $filter_type = !empty($_REQUEST['type']) && in_array($_REQUEST['type'], ['out_of_stock', 'low_stock', 'overstock']) ? $_REQUEST['type'] : null;
        
        // Sync alerts automatically
        StockNotification::sync_stock_alerts($branch_id);
        
        $counts = StockNotification::get_alert_counts($branch_id);
        $alerts = StockNotification::get_active_alerts(35, $branch_id, $filter_type);
        
        echo json_encode([
            'success' => true,
            'counts' => $counts,
            'alerts' => $alerts
        ]);
        break;

    case 'mark_read':
        $id = (int)($_POST['id'] ?? 0);
        if ($id > 0) {
            StockNotification::mark_as_read($id, $branch_id);
            $counts = StockNotification::get_alert_counts($branch_id);
            echo json_encode(['success' => true, 'counts' => $counts]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid notification ID']);
        }
        break;

    case 'mark_all_read':
        StockNotification::mark_all_as_read($branch_id);
        $counts = StockNotification::get_alert_counts($branch_id);
        echo json_encode(['success' => true, 'counts' => $counts]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
