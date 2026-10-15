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
$is_admin = User::has_all_branch_access($_SESSION['user']['id']);
$branch_id_req = $_REQUEST['branch_id'] ?? 'all';
$branch_id = ($is_admin && $branch_id_req !== 'all') ? (int)$branch_id_req : ($is_admin ? null : ($_SESSION['user']['branch_id'] ?? null));

// Date Range Filtering
$start_date = $_REQUEST['start_date'] ?? null;
$end_date = $_REQUEST['end_date'] ?? null;

if ($action === 'get_dt_sales') {
    require_permission('view_reports');

    $draw = intval($_POST['draw']);
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
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

    $draw = intval($_POST['draw']);
    $start = intval($_POST['start']);
    $length = intval($_POST['length']);
    $search = $_POST['search']['value'] ?? '';
    $order_col = intval($_POST['order'][0]['column'] ?? 0);
    $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

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
