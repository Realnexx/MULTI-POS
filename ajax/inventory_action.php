<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';

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

if (empty($_SESSION['user']) || !User::has_permission($_SESSION['user']['id'], 'manage_inventory')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'quick_restock':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $remarks = $_POST['remarks'] ?? 'Quick restock via AJAX';
        $user_id = $_SESSION['user']['id'];

        if ($product_id > 0 && $quantity > 0) {
            $success = Product::adjust_stock($product_id, $quantity, 'stock_in', $remarks, $user_id);
            echo json_encode(['success' => $success, 'message' => $success ? 'Stock updated successfully' : 'Failed to update stock']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    case 'adjust_stock':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $quantity = (int) ($_POST['quantity'] ?? 0);
        $adj_type = $_POST['adjustment_type'] ?? 'stock_in';
        $remarks = $_POST['remarks'] ?? 'Manual adjustment via AJAX';
        $user_id = $_SESSION['user']['id'];
        $batch_number = $_POST['batch_number'] ?? null;
        $cost_price = isset($_POST['cost_price']) ? (float) $_POST['cost_price'] : null;
        $branch_id = !empty($_POST['branch_id']) ? (int) $_POST['branch_id'] : null;

        if ($product_id > 0 && $quantity > 0 && in_array($adj_type, ['stock_in', 'stock_out'])) {
            $success = Product::adjust_stock($product_id, $user_id, $adj_type, $quantity, $remarks, $batch_number, $cost_price, $branch_id);
            echo json_encode(['success' => $success, 'message' => $success ? 'Stock adjusted successfully' : 'Failed to adjust stock']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    case 'get_next_batch':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            $next_batch = Product::get_next_batch_number($product_id);
            echo json_encode(['success' => true, 'batch_number' => $next_batch]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'logs_datatable':
        $draw = (int) ($_POST['draw'] ?? 0);
        $start = (int) ($_POST['start'] ?? 0);
        $length = (int) ($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $order_col = (int) ($_POST['order'][0]['column'] ?? 0);
        $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

        $branch_id = !empty($_POST['branch_id']) ? (int) $_POST['branch_id'] : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

        $logs = Product::get_dt_inventory_logs($start, $length, $search, $order_col, $order_dir, $branch_id, $start_date, $end_date);
        $recordsTotal = Product::get_total_inventory_logs_count($branch_id, $start_date, $end_date);
        $recordsFiltered = Product::count_dt_inventory_logs_filtered($search, $branch_id, $start_date, $end_date);

        // Stats for current filtered view (or total if no filter)
        $total_in = 0;
        $total_out = 0;

        // This is a bit expensive if we query all, but for stats we usually want the totals of the filtered set
        // However, for simplicity and performance, we'll return the stats for the whole system or the branch if specified
        $product_id = !empty($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        $all_logs = Product::get_inventory_logs_stats($branch_id, $start_date, $end_date, $product_id);
        foreach ($all_logs as $l) {
            $is_in = ($l['action'] === 'stock_in' || $l['action'] === 'initial' || $l['action'] === 'Purchase');
            if ($is_in)
                $total_in += $l['quantity'];
            else
                $total_out += $l['quantity'];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $logs,
            "stats" => [
                "total_in" => $total_in,
                "total_out" => $total_out,
                "net" => $total_in - $total_out,
                "count" => $recordsFiltered
            ]
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}
