<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../middleware/rbac.php';

header('Content-Type: application/json');

$action = $_GET['action'] ?? '';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('report_action', $action);

// Shared Branch Filter Logic
$access = User::access_context((int)$_SESSION['user']['id']);
$is_admin = $access['global'];
$branch_id_req = $_REQUEST['branch_id'] ?? 'all';
$branch_id = ($is_admin && $branch_id_req !== 'all') ? (int)$branch_id_req : ($is_admin ? null : ($access['branch_id'] ?? 0));

// Date Range Filtering
$start_date = $_REQUEST['start_date'] ?? null;
$end_date = $_REQUEST['end_date'] ?? null;

if ($action === 'get_dt_sales') {
    require_permission('view_reports');

    $draw = max(0, intval($_POST['draw'] ?? 0));
    $start = max(0, intval($_POST['start'] ?? 0));
    $length = max(1, min(100, intval($_POST['length'] ?? 10)));
    $search = $_POST['search']['value'] ?? '';
    $order_col = intval($_POST['order'][0]['column'] ?? 0);
    $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

    $sales = Sale::get_dt_sales($start, $length, $search, $order_col, $order_dir, $branch_id, $start_date, $end_date);
    $total_records = Sale::get_total_sales_count($branch_id, $start_date, $end_date);
    $filtered_records = Sale::count_dt_sales_filtered($search, $branch_id, $start_date, $end_date);

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $total_records,
        "recordsFiltered" => $filtered_records,
        "data" => $sales
    ]);
    exit;
}

if ($action === 'get_dt_inventory_logs') {
    require_permission('view_reports');

    $draw = max(0, intval($_POST['draw'] ?? 0));
    $start = max(0, intval($_POST['start'] ?? 0));
    $length = max(1, min(100, intval($_POST['length'] ?? 10)));
    $search = $_POST['search']['value'] ?? '';
    $order_col = intval($_POST['order'][0]['column'] ?? 0);
    $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

    // Product's shared log method uses a different column order.
    $order_col = [-1, 0, 3, 1, 2, 4, 5][$order_col] ?? -1;
    $logs = Product::get_dt_inventory_logs($start, $length, $search, $order_col, $order_dir, $branch_id, $start_date, $end_date);
    $total_records = Product::get_total_inventory_logs_count($branch_id, $start_date, $end_date);
    $filtered_records = Product::count_dt_inventory_logs_filtered($search, $branch_id, $start_date, $end_date);

    echo json_encode([
        "draw" => $draw,
        "recordsTotal" => $total_records,
        "recordsFiltered" => $filtered_records,
        "data" => $logs
    ]);
    exit;
}

if ($action === 'get_summary_stats') {
    require_permission('view_reports');
    $stats = Sale::get_summary_stats($branch_id, $start_date, $end_date);
    echo json_encode($stats);
    exit;
}
