<?php
$title = "Dashboard Overview";

$stats = $stats ?? [
    'sales_today' => 0.00,
    'orders_today' => 0,
    'avg_order_value' => 0.00,
    'total_products' => 0,
    'low_stock_count' => 0,
    'total_users' => 0,
    'sales_chart_labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'sales_chart_data' => [0, 0, 0, 0, 0, 0, 0]
];
$recent_sales = $recent_sales ?? [];
$low_stock = $low_stock ?? [];
$top_selling = $top_selling ?? [];
$all_branches = $all_branches ?? [];
$selected_branch = $selected_branch ?? 'all';

require_once __DIR__ . '/../../models/ActivityLog.php';
$recent_activities = [];
if (class_exists('ActivityLog') && method_exists('ActivityLog', 'getDTLogs')) {
    try {
        $recent_activities = ActivityLog::getDTLogs(0, 5, [], 'created_at', 'DESC');
    } catch (Exception $e) {
    }
}

$current_branch = $selected_branch ?? 'all';
$display_branch = ($current_branch === 'all' || !$current_branch) ? 'All Branches' : htmlspecialchars($current_branch);
$display_date = empty($_GET['date_range']) ? 'Today' : htmlspecialchars($_GET['date_range']);

$completed_orders = 0;
$pending_orders = 0;
foreach ($recent_sales as $sale) {
    strtolower($sale['status'] ?? '') === 'completed' ? $completed_orders++ : $pending_orders++;
}
if ($completed_orders === 0 && $pending_orders === 0 && $stats['orders_today'] > 0) {
    $completed_orders = $stats['orders_today'];
}

$user_first = htmlspecialchars(explode(' ', $_SESSION['user']['name'] ?? 'Admin')[0]);

ob_start();
?>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
    /* ================================================================
   AURORA POS DASHBOARD — ENHANCED UI STYLES
   ================================================================ */

    /* Page Header */
    .dash-page-header {
        margin-top: 0.25rem;
        margin-bottom: 1.5rem;
    }

    .dash-page-title {
        font-size: 1.65rem;
        font-weight: 800;
        color: #2a3547;
        letter-spacing: -0.5px;
        margin-bottom: 4px;
    }

    .dash-page-subtitle {
        font-size: 0.88rem;
        color: #7c8fac;
        margin-bottom: 0;
    }

    .dash-badge-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eef2ff;
        color: #5d87ff;
        border-radius: 50px;
        padding: 5px 14px;
        font-size: 0.78rem;
        font-weight: 700;
        margin-bottom: 8px;
    }

    /* Filter Controls (Clean Solid Style, No Glassmorphism) */
    .filter-card {
        border-radius: 18px;
        border: 1px solid #e8edf5;
        background: #ffffff;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
    }

    .filter-label {
        font-size: 0.75rem;
        font-weight: 700;
        color: #5a6a85;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .filter-label iconify-icon {
        font-size: 0.95rem;
        color: #5d87ff;
    }

    .form-select-solid,
    .form-control-solid {
        border-radius: 12px !important;
        border: 1px solid #dfe5ef !important;
        background-color: #f8faff !important;
        color: #2a3547 !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        padding: 0.55rem 0.85rem !important;
        transition: all 0.2s ease !important;
    }

    .form-select-solid:focus,
    .form-control-solid:focus {
        border-color: #5d87ff !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.12) !important;
        outline: none !important;
    }

    .btn-clear-filter {
        background: #f0f4fb;
        border: 1px solid #dfe5ef;
        color: #5a6a85;
        border-radius: 12px;
        font-weight: 700;
        font-size: 0.84rem;
        padding: 0.55rem 1rem;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        text-decoration: none;
    }

    .btn-clear-filter:hover {
        background: #e2e8f0;
        color: #2a3547;
    }

    /* Metric KPI Cards (Clean Solid Style) */
    .kpi-card {
        border-radius: 18px;
        border: 1px solid #e8edf5;
        background: #ffffff;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.03);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        position: relative;
        overflow: hidden;
    }

    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
    }

    .kpi-glow {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 3px;
    }

    .kpi-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.1);
    }

    .kpi-icon-wrap {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        flex-shrink: 0;
    }

    .kpi-value {
        font-size: 1.75rem;
        font-weight: 800;
        letter-spacing: -1px;
        line-height: 1;
    }

    .kpi-label {
        font-size: 0.78rem;
        font-weight: 600;
        color: #7c8fac;
        text-transform: uppercase;
        letter-spacing: 0.6px;
    }

    .kpi-trend {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 3px 10px;
        border-radius: 50px;
    }

    .kpi-trend.up {
        background: #e8fdf5;
        color: #13deb9;
    }

    .kpi-trend.down {
        background: #fef2f2;
        color: #f87171;
    }

    .kpi-trend.neutral {
        background: #f0f4ff;
        color: #5d87ff;
    }

    /* Main content cards */
    .aurora-card {
        border-radius: 18px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        box-shadow: 0 2px 16px rgba(0, 0, 0, 0.05);
        background: #ffffff;
        transition: box-shadow 0.2s ease;
    }

    .aurora-card:hover {
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
    }

    .aurora-card-header {
        padding: 22px 24px 0 24px;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .aurora-card-title {
        font-size: 0.95rem;
        font-weight: 800;
        color: #2a3547;
        letter-spacing: -0.2px;
        margin-bottom: 2px;
    }

    .aurora-card-subtitle {
        font-size: 0.78rem;
        color: #7c8fac;
    }

    .aurora-card-body {
        padding: 18px 24px 22px 24px;
    }

    /* Timeline widget enhanced */
    .dash-timeline {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .dash-timeline-item {
        display: flex;
        gap: 12px;
        align-items: flex-start;
        padding-bottom: 18px;
        position: relative;
    }

    .dash-timeline-item:not(:last-child)::before {
        content: '';
        position: absolute;
        left: 16px;
        top: 28px;
        width: 2px;
        bottom: 0;
        background: linear-gradient(to bottom, #e5eaef, transparent);
    }

    .dash-tl-dot {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 700;
    }

    .dash-tl-dot.success {
        background: rgba(19, 222, 185, 0.12);
        color: #13deb9;
    }

    .dash-tl-dot.warning {
        background: rgba(255, 174, 31, 0.12);
        color: #ffae1f;
    }

    .dash-tl-dot.danger {
        background: rgba(250, 84, 84, 0.12);
        color: #fa5454;
    }

    .dash-tl-dot.primary {
        background: rgba(93, 135, 255, 0.12);
        color: #5d87ff;
    }

    .dash-tl-content h6 {
        font-size: 0.82rem;
        font-weight: 700;
        color: #2a3547;
        margin-bottom: 2px;
    }

    .dash-tl-content p {
        font-size: 0.76rem;
        color: #7c8fac;
        margin-bottom: 0;
    }

    .dash-tl-time {
        font-size: 0.72rem;
        color: #b0b7c3;
        white-space: nowrap;
        padding-top: 5px;
    }

    /* Transactions table */
    .dash-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }

    .dash-table thead th {
        font-size: 0.7rem;
        font-weight: 700;
        color: #9eaac7;
        text-transform: uppercase;
        letter-spacing: 0.8px;
        padding: 10px 14px;
        border-bottom: 1px solid #f0f3fa;
        white-space: nowrap;
    }

    .dash-table tbody td {
        padding: 13px 14px;
        border-bottom: 1px solid #f8f9fc;
        font-size: 0.83rem;
        vertical-align: middle;
    }

    .dash-table tbody tr:last-child td {
        border-bottom: none;
    }

    .dash-table tbody tr:hover td {
        background: #fafbff;
    }

    .ticket-id {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: linear-gradient(135deg, #5d87ff22, #3eb7ff22);
        color: #5d87ff;
        font-weight: 800;
        font-size: 0.75rem;
        flex-shrink: 0;
    }

    /* Status badges */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 12px;
        border-radius: 50px;
        font-size: 0.74rem;
        font-weight: 700;
    }

    .status-pill.completed {
        background: #e6fbf7;
        color: #0fb68c;
    }

    .status-pill.pending {
        background: #fff8e6;
        color: #c98800;
    }

    .status-pill.cancelled {
        background: #fef2f2;
        color: #e53e3e;
    }

    .status-pill::before {
        content: '';
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
        display: inline-block;
    }

    /* Top products rank cards */
    .rank-card {
        display: flex;
        align-items: center;
        gap: 14px;
        background: #f8faff;
        border-radius: 14px;
        padding: 14px 16px;
        transition: all 0.2s ease;
    }

    .rank-card:hover {
        background: #eef2ff;
        transform: translateX(4px);
    }

    .rank-badge {
        width: 34px;
        height: 34px;
        border-radius: 10px;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 900;
        font-size: 0.85rem;
    }

    .rank-badge.gold {
        background: linear-gradient(135deg, #ffd700, #ffae1f);
        color: #7a4800;
    }

    .rank-badge.silver {
        background: linear-gradient(135deg, #d4d8e2, #b0b7c9);
        color: #3a3f52;
    }

    .rank-badge.bronze {
        background: linear-gradient(135deg, #d4845a, #b0622a);
        color: #fff;
    }

    .rank-badge.other {
        background: #eef2ff;
        color: #5d87ff;
    }

    /* Low stock cards */
    .stock-alert-card {
        display: flex;
        align-items: center;
        gap: 14px;
        border-radius: 14px;
        padding: 14px 16px;
        background: #fff9f9;
        border: 1px solid #fde8e8;
        transition: all 0.2s;
    }

    .stock-alert-card:hover {
        background: #fff2f2;
    }

    /* Flatpickr overrides */
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange {
        background: #5d87ff !important;
        border-color: #5d87ff !important;
    }

    /* Responsive spacing */
    .row.g-aurora {
        --bs-gutter-x: 20px;
        --bs-gutter-y: 20px;
    }
</style>


<div class="dash-wrapper pb-2">

    <!-- ============ CLEAN PAGE HEADER (No Glassmorphism) ============ -->
    <div
        class="dash-page-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-4">
        <div>
            <div class="dash-badge-pill">
                <iconify-icon icon="solar:shop-2-bold"></iconify-icon>
                <span>Store Overview &middot; Real-Time Snapshot</span>
            </div>
            <h2 class="dash-page-title">Good
                <?= (date('H') < 12) ? 'Morning' : ((date('H') < 17) ? 'Afternoon' : 'Evening') ?>, <?= $user_first ?>
                👋</h2>
            <p class="dash-page-subtitle">Here's your store's performance snapshot and operations summary for today.</p>
        </div>

        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span
                class="badge bg-light-primary text-primary fw-semibold px-3 py-2 rounded-pill fs-2 d-inline-flex align-items-center gap-1">
                <iconify-icon icon="solar:shop-2-bold" class="fs-4"></iconify-icon>
                <?= $display_branch ?>
            </span>
            <span
                class="badge bg-light-info text-info fw-semibold px-3 py-2 rounded-pill fs-2 d-inline-flex align-items-center gap-1">
                <iconify-icon icon="solar:calendar-line-duotone" class="fs-4"></iconify-icon>
                <?= $display_date ?>
            </span>
            <?php if (User::has_permission($_SESSION['user']['id'] ?? 0, 'access_pos')): ?>
                <a href="<?= url('?route=pos') ?>"
                    class="btn btn-primary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-2 shadow-sm ms-md-2"
                    title="Launch POS Terminal">
                    <iconify-icon icon="solar:calculator-minimalistic-line-duotone" class="fs-5"></iconify-icon>
                    <span>Open POS</span>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ KPI CARDS ROW ============ -->
    <div class="row g-aurora mb-4">

        <!-- Revenue Today -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card card p-0">
                <div class="kpi-glow" style="background:linear-gradient(90deg,#5d87ff,#3eb7ff);"></div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon-wrap" style="background:rgba(93,135,255,0.12);">
                            <iconify-icon icon="solar:wallet-money-bold-duotone"
                                style="color:#5d87ff;font-size:1.5rem;"></iconify-icon>
                        </div>
                        <span class="kpi-trend neutral"><i class="ti ti-trending-up"></i> Today</span>
                    </div>
                    <div class="kpi-value text-dark mb-1">₱<?= number_format($stats['sales_today'], 2) ?></div>
                    <div class="kpi-label">Gross Revenue</div>
                    <div class="mt-2 pt-2 border-top" style="font-size:0.75rem;color:#9eaac7;">
                        Avg <strong class="text-dark">₱<?= number_format($stats['avg_order_value'], 2) ?></strong> /
                        ticket
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Today -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card card p-0">
                <div class="kpi-glow" style="background:linear-gradient(90deg,#13deb9,#3eb7ff);"></div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon-wrap" style="background:rgba(19,222,185,0.12);">
                            <iconify-icon icon="solar:bag-5-bold-duotone"
                                style="color:#13deb9;font-size:1.5rem;"></iconify-icon>
                        </div>
                        <span class="kpi-trend up"><i class="ti ti-check"></i> Done</span>
                    </div>
                    <div class="kpi-value text-dark mb-1"><?= number_format($stats['orders_today']) ?></div>
                    <div class="kpi-label">Orders Today</div>
                    <div class="mt-2 pt-2 border-top" style="font-size:0.75rem;color:#9eaac7;">
                        <span class="text-success fw-bold"><?= $completed_orders ?></span> completed &middot; <span
                            class="text-warning fw-bold"><?= $pending_orders ?></span> pending
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Products -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card card p-0">
                <div class="kpi-glow" style="background:linear-gradient(90deg,#ffae1f,#ffd700);"></div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon-wrap" style="background:rgba(255,174,31,0.12);">
                            <iconify-icon icon="solar:box-bold-duotone"
                                style="color:#ffae1f;font-size:1.5rem;"></iconify-icon>
                        </div>
                        <span class="kpi-trend neutral"><i class="ti ti-package"></i> SKUs</span>
                    </div>
                    <div class="kpi-value text-dark mb-1"><?= number_format($stats['total_products'] ?? 0) ?></div>
                    <div class="kpi-label">Active Products</div>
                    <div class="mt-2 pt-2 border-top" style="font-size:0.75rem;color:#9eaac7;">
                        <span
                            class="text-danger fw-bold"><?= (int) ($stats['low_stock_count'] ?? count($low_stock)) ?></span>
                        below reorder point
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members -->
        <div class="col-6 col-lg-3">
            <div class="kpi-card card p-0">
                <div class="kpi-glow" style="background:linear-gradient(90deg,#fa5454,#ffae1f);"></div>
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="kpi-icon-wrap" style="background:rgba(250,84,84,0.10);">
                            <iconify-icon icon="solar:users-group-rounded-bold-duotone"
                                style="color:#fa5454;font-size:1.5rem;"></iconify-icon>
                        </div>
                        <span class="kpi-trend neutral"><i class="ti ti-users"></i> Staff</span>
                    </div>
                    <div class="kpi-value text-dark mb-1"><?= number_format($stats['total_users'] ?? 0) ?></div>
                    <div class="kpi-label">Team Members</div>
                    <div class="mt-2 pt-2 border-top" style="font-size:0.75rem;color:#9eaac7;">
                        Across <strong class="text-dark"><?= count($all_branches) ?: 1 ?></strong>
                        branch<?= count($all_branches) !== 1 ? 'es' : '' ?>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============ FILTER CONTROLS (Clean Solid Style, No Glassmorphism) ============ -->
    <div class="filter-card p-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:filter-bold-duotone" class="text-primary fs-5"></iconify-icon>
                <h6 class="mb-0 fw-bold text-dark">Filter Dashboard</h6>
            </div>
            <span class="text-muted small">Live analytics update on apply</span>
        </div>

        <form method="GET" action="<?= url('?route=dashboard') ?>" id="dashboard-filter-form">
            <input type="hidden" name="route" value="dashboard">
            <div class="row g-3 align-items-end">
                <?php if (!empty($all_branches)): ?>
                    <div class="col-12 col-sm-6 col-lg-4">
                        <label class="filter-label">
                            <iconify-icon icon="solar:shop-2-line-duotone"></iconify-icon>
                            <span>Branch</span>
                        </label>
                        <select name="branch" id="branch-select" class="form-select form-select-solid"
                            onchange="this.form.submit()">
                            <option value="all" <?= ($current_branch === 'all') ? 'selected' : '' ?>>All Branches</option>
                            <?php foreach ($all_branches as $b): ?>
                                <option value="<?= htmlspecialchars($b['name']) ?>" <?= ($current_branch === $b['name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="branch" value="all">
                <?php endif; ?>

                <div class="col-12 col-sm-6 col-lg-5">
                    <label class="filter-label">
                        <iconify-icon icon="solar:calendar-line-duotone"></iconify-icon>
                        <span>Date Range</span>
                    </label>
                    <input type="text" name="date_range" id="date-range-picker" class="form-control form-control-solid"
                        placeholder="Select date period (e.g. Sep 1 to Sep 7)..."
                        value="<?= htmlspecialchars($_GET['date_range'] ?? '') ?>" readonly>
                </div>

                <div class="col-12 col-sm-6 col-lg-3 d-flex gap-2">
                    <button type="submit"
                        class="btn btn-primary rounded-pill px-3 py-2 fw-semibold w-100 d-flex align-items-center justify-content-center gap-1 shadow-sm">
                        <iconify-icon icon="solar:check-read-linear" class="fs-4"></iconify-icon>
                        <span>Apply</span>
                    </button>
                    <?php if (!empty($_GET['date_range']) || ($current_branch !== 'all' && !empty($all_branches))): ?>
                        <a href="<?= url('?route=dashboard') ?>"
                            class="btn-clear-filter px-3 py-2 w-100 d-flex align-items-center justify-content-center gap-1 text-decoration-none"
                            title="Reset all filters">
                            <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
                            <span>Reset</span>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>

    <!-- ============ CHART ROW ============ -->
    <div class="row g-aurora mb-4">

        <!-- Sales Trend Chart (8 cols) -->
        <div class="col-lg-8">
            <div class="aurora-card h-100">
                <div class="aurora-card-header">
                    <div>
                        <div class="aurora-card-title">Sales &amp; Revenue Trends</div>
                        <div class="aurora-card-subtitle">Daily gross revenue — last 7 operational dates</div>
                    </div>
                    <div class="dropdown">
                        <button data-bs-toggle="dropdown"
                            class="btn btn-sm btn-light rounded-circle p-2 border-0 shadow-none"
                            style="width:32px;height:32px;">
                            <i class="ti ti-dots-vertical" style="font-size:0.9rem;"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3"
                            style="font-size:0.83rem;">
                            <li><a class="dropdown-item py-2" href="javascript:void(0)" onclick="location.reload()"><i
                                        class="ti ti-refresh me-2"></i>Refresh</a></li>
                            <li><?php if(User::has_permission($_SESSION['user']['id'],'view_reports')): ?><a class="dropdown-item py-2" href="<?= url('?route=admin/reports') ?>"><i
                                        class="ti ti-chart-bar me-2"></i>Full Reports</a><?php endif; ?></li>
                        </ul>
                    </div>
                </div>
                <div class="aurora-card-body pt-2">
                    <div id="sales-trend-chart" style="min-height:300px;"></div>
                </div>
            </div>
        </div>

        <!-- Right stack (4 cols) -->
        <div class="col-lg-4 d-flex flex-column gap-aurora" style="gap:20px;">

            <!-- Order Status Donut -->
            <div class="aurora-card flex-fill">
                <div class="aurora-card-header">
                    <div>
                        <div class="aurora-card-title">Order Status</div>
                        <div class="aurora-card-subtitle">Today's order breakdown</div>
                    </div>
                </div>
                <div class="aurora-card-body pt-2">
                    <div class="d-flex align-items-center gap-3">
                        <div id="order-donut-chart" style="min-width:130px;"></div>
                        <div class="d-flex flex-column gap-2" style="font-size:0.8rem;">
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    style="width:10px;height:10px;background:#13deb9;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                                <span class="text-muted">Completed</span>
                                <strong class="ms-auto"><?= $completed_orders ?></strong>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span
                                    style="width:10px;height:10px;background:#ffae1f;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                                <span class="text-muted">Pending</span>
                                <strong class="ms-auto"><?= $pending_orders ?></strong>
                            </div>
                            <div class="d-flex align-items-center gap-2 pt-1 border-top mt-1">
                                <span
                                    style="width:10px;height:10px;background:#5d87ff;border-radius:50%;display:inline-block;flex-shrink:0;"></span>
                                <span class="text-muted fw-semibold">Total</span>
                                <strong class="ms-auto text-primary"><?= $stats['orders_today'] ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Revenue Sparkline -->
            <div class="aurora-card flex-fill">
                <div class="aurora-card-header pb-0">
                    <div>
                        <div class="aurora-card-title">Revenue Trend</div>
                        <div class="aurora-card-subtitle">7-day rolling sparkline</div>
                    </div>
                    <div class="text-end">
                        <div class="fw-800 text-primary" style="font-size:1.1rem;font-weight:800;">
                            ₱<?= number_format($stats['sales_today']) ?></div>
                        <div style="font-size:0.72rem;color:#9eaac7;">Today</div>
                    </div>
                </div>
                <div id="revenue-sparkline-chart" style="margin:0 -4px;"></div>
            </div>

        </div>
    </div>

    <!-- ============ ACTIVITY + TRANSACTIONS ============ -->
    <div class="row g-aurora mb-4">

        <!-- Activity Timeline -->
        <div class="col-lg-4">
            <div class="aurora-card h-100">
                <div class="aurora-card-header">
                    <div>
                        <div class="aurora-card-title">System Activity</div>
                        <div class="aurora-card-subtitle">Recent actions &amp; events</div>
                    </div>
                    <?php if(User::has_permission($_SESSION['user']['id'],'view_activity_logs')): ?><a href="<?= url('?route=admin/activity_logs') ?>"
                        class="text-primary fw-semibold text-decoration-none" style="font-size:0.8rem;">View All</a><?php endif; ?>
                </div>
                <div class="aurora-card-body">
                    <div>
                        <ul class="dash-timeline">
                            <?php if (empty($recent_activities)): ?>
                                <li class="text-center py-4 text-muted" style="font-size:0.82rem;">
                                    <iconify-icon icon="solar:bell-sleep-line-duotone"
                                        style="font-size:2rem;display:block;margin-bottom:8px;color:#b0b7c3;"></iconify-icon>
                                    No recent activity yet.
                                </li>
                            <?php else: ?>
                                <?php foreach ($recent_activities as $act):
                                    $act_action = strtolower($act['action'] ?? '');
                                    if (stripos($act_action, 'delete') !== false || stripos($act_action, 'cancel') !== false)
                                        $dot = 'danger';
                                    elseif (stripos($act_action, 'update') !== false || stripos($act_action, 'edit') !== false)
                                        $dot = 'warning';
                                    elseif (stripos($act_action, 'create') !== false || stripos($act_action, 'sale') !== false || stripos($act_action, 'checkout') !== false || stripos($act_action, 'add') !== false)
                                        $dot = 'success';
                                    else
                                        $dot = 'primary';
                                    $icon_map = ['success' => 'solar:check-circle-bold-duotone', 'danger' => 'solar:trash-bin-trash-bold-duotone', 'warning' => 'solar:pen-new-square-bold-duotone', 'primary' => 'solar:info-circle-bold-duotone'];
                                    ?>
                                    <li class="dash-timeline-item">
                                        <div class="dash-tl-dot <?= $dot ?>">
                                            <iconify-icon icon="<?= $icon_map[$dot] ?>"></iconify-icon>
                                        </div>
                                        <div class="dash-tl-content flex-fill">
                                            <h6><?= htmlspecialchars($act['action']) ?></h6>
                                            <p><?= htmlspecialchars(mb_strimwidth($act['description'] ?? 'Activity logged', 0, 55, '…')) ?>
                                            </p>
                                            <span class="dash-tl-time">by
                                                <?= htmlspecialchars($act['user_name'] ?? 'System') ?></span>
                                        </div>
                                        <div class="dash-tl-time ms-2"><?= date('h:i A', strtotime($act['created_at'])) ?></div>
                                    </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transactions Table -->
        <div class="col-lg-8">
            <div class="aurora-card h-100">
                <div class="aurora-card-header">
                    <div>
                        <div class="aurora-card-title">Recent Transactions</div>
                        <div class="aurora-card-subtitle">Latest processed sales tickets across branches</div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <?php if(User::has_permission($_SESSION['user']['id'],'access_pos')): ?><a href="<?= url('?route=pos/history') ?>"
                            class="btn btn-sm btn-light-primary text-primary rounded-pill px-3"
                            style="font-size:0.78rem;font-weight:600;">
                            <i class="ti ti-history me-1"></i>Full History
                        </a><?php endif; ?>
                    </div>
                </div>
                <div class="aurora-card-body pt-2">
                    <div>
                        <table class="dash-table">
                            <thead>
                                <tr>
                                    <th>Ticket</th>
                                    <th>Cashier &amp; Branch</th>
                                    <th>Payment</th>
                                    <th>Status</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($recent_sales)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-5" style="font-size:0.82rem;color:#9eaac7;">No
                                            transactions recorded for this period.</td>
                                    </tr>
                                <?php else:
                                    foreach (array_slice($recent_sales, 0, 7) as $s):
                                        $stat = strtolower($s['status'] ?? 'completed');
                                        if ($stat === 'completed') {
                                            $pill_class = 'completed';
                                        } elseif ($stat === 'cancelled' || $stat === 'refunded') {
                                            $pill_class = 'cancelled';
                                        } else {
                                            $pill_class = 'pending';
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="ticket-id">#<?= substr((string) $s['id'], -3) ?></div>
                                                    <div>
                                                        <div style="font-weight:700;color:#2a3547;font-size:0.82rem;">Order
                                                            #<?= $s['id'] ?></div>
                                                        <div style="font-size:0.72rem;color:#9eaac7;">
                                                            <?= date('M d, h:i A', strtotime($s['created_at'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="font-weight:600;font-size:0.82rem;color:#2a3547;">
                                                    <?= htmlspecialchars($s['cashier_name'] ?? 'Staff') ?>
                                                </div>
                                                <div style="font-size:0.72rem;color:#9eaac7;">
                                                    <?= htmlspecialchars($s['branch_name'] ?? 'Main') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span
                                                    style="background:#f0f4ff;color:#5d87ff;border-radius:8px;padding:3px 10px;font-size:0.74rem;font-weight:600;">
                                                    <?= htmlspecialchars($s['payment_method'] ?? 'Cash') ?>
                                                </span>
                                            </td>
                                            <td><span
                                                    class="status-pill <?= $pill_class ?>"><?= ucfirst($s['status'] ?? 'Completed') ?></span>
                                            </td>
                                            <td class="text-end">
                                                <div style="font-weight:800;color:#5d87ff;font-size:0.9rem;">
                                                    ₱<?= number_format($s['final_amount'], 2) ?></div>
                                            </td>
                                        </tr>
                                    <?php endforeach; endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============ TOP PRODUCTS + LOW STOCK ============ -->
    <div class="row g-aurora mb-2">

        <!-- Top Selling Products -->
        <div class="col-lg-6">
            <div class="aurora-card h-100">
                <div class="aurora-card-header">
                    <div>
                        <div class="aurora-card-title">🏆 Top Selling Items</div>
                        <div class="aurora-card-subtitle">Ranked by sales volume this period</div>
                    </div>
                    <?php if(User::has_permission($_SESSION['user']['id'],'view_product')): ?><a href="<?= url('?route=inventory/products') ?>"
                        class="text-primary fw-semibold text-decoration-none" style="font-size:0.8rem;">Manage</a><?php endif; ?>
                </div>
                <div class="aurora-card-body">
                    <?php if (empty($top_selling)): ?>
                        <div class="text-center py-4 text-muted" style="font-size:0.82rem;">No product sales recorded yet.
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column" style="gap:10px;">
                            <?php foreach ($top_selling as $idx => $prod):
                                $badges = ['gold', 'silver', 'bronze'];
                                $badge = $badges[$idx] ?? 'other';
                                $rank_labels = ['🥇', '🥈', '🥉'];
                                ?>
                                <div class="rank-card">
                                    <div class="rank-badge <?= $badge ?>"><?= $rank_labels[$idx] ?? ($idx + 1) ?></div>
                                    <div class="flex-fill">
                                        <div style="font-weight:700;font-size:0.85rem;color:#2a3547;">
                                            <?= htmlspecialchars($prod['name']) ?>
                                        </div>
                                        <div style="font-size:0.74rem;color:#9eaac7;"><?= (int) ($prod['total_qty'] ?? 0) ?>
                                            orders completed</div>
                                    </div>
                                    <div class="text-end">
                                        <div style="font-weight:800;font-size:0.9rem;color:#13deb9;">
                                            ₱<?= number_format($prod['total_revenue'] ?? 0, 2) ?></div>
                                        <div style="font-size:0.7rem;color:#b0b7c3;">revenue</div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Low Stock Alerts -->
        <div class="col-lg-6">
            <div class="aurora-card h-100">
                <div class="aurora-card-header">
                    <div>
                        <div class="aurora-card-title">⚠️ Low Stock Alerts</div>
                        <div class="aurora-card-subtitle">Items below reorder threshold</div>
                    </div>
                    <span class="kpi-trend <?= count($low_stock) > 0 ? 'down' : 'up' ?>">
                        <?= count($low_stock) ?> Alert<?= count($low_stock) !== 1 ? 's' : '' ?>
                    </span>
                </div>
                <div class="aurora-card-body">
                    <?php if (empty($low_stock)): ?>
                        <div class="text-center py-5" style="font-size:0.82rem;color:#9eaac7;">
                            <iconify-icon icon="solar:shield-check-bold-duotone"
                                style="font-size:2.5rem;display:block;margin-bottom:10px;color:#13deb9;"></iconify-icon>
                            All products have sufficient stock!
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column" style="gap:10px;">
                            <?php foreach (array_slice($low_stock, 0, 4) as $item): ?>
                                <div class="stock-alert-card">
                                    <div
                                        style="width:38px;height:38px;background:rgba(250,84,84,0.12);border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                                        <iconify-icon icon="solar:danger-bold-duotone"
                                            style="color:#fa5454;font-size:1.2rem;"></iconify-icon>
                                    </div>
                                    <div class="flex-fill">
                                        <div style="font-weight:700;font-size:0.85rem;color:#2a3547;">
                                            <?= htmlspecialchars($item['name']) ?>
                                        </div>
                                        <div style="font-size:0.74rem;color:#fa5454;font-weight:600;">
                                            <?= (int) $item['stock'] ?> left &middot; min
                                            <?= (int) ($item['low_stock_threshold'] ?? 10) ?>
                                        </div>
                                    </div>
                                    <?php if(User::has_permission($_SESSION['user']['id'],'view_product')): ?><a href="<?= url('?route=inventory/products') ?>" class="btn btn-sm rounded-pill px-3"
                                        style="background:#fa545415;color:#fa5454;font-size:0.75rem;font-weight:700;border:none;">
                                        Restock
                                    </a><?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

</div>

<!-- ============ APEXCHARTS INIT ============ -->
<script>
    $(function () {
        const chartLabels = <?= json_encode($stats['sales_chart_labels'] ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) ?>;
        const chartData = <?= json_encode($stats['sales_chart_data'] ?? [0, 0, 0, 0, 0, 0, 0]) ?>;

        // 1. Sales Trend – Bar Chart
        new ApexCharts(document.querySelector('#sales-trend-chart'), {
            series: [{ name: 'Revenue (₱)', data: chartData }],
            chart: { type: 'bar', height: 300, toolbar: { show: false }, fontFamily: "'Plus Jakarta Sans', sans-serif" },
            colors: ['#5d87ff'],
            plotOptions: { bar: { columnWidth: '44%', borderRadius: 7, endingShape: 'rounded' } },
            dataLabels: { enabled: false },
            grid: { borderColor: '#f0f3fa', strokeDashArray: 4, padding: { left: 4, right: 4 } },
            xaxis: {
                categories: chartLabels,
                axisBorder: { show: false }, axisTicks: { show: false },
                labels: { style: { colors: '#9eaac7', fontSize: '12px', fontFamily: 'inherit' } }
            },
            yaxis: {
                labels: {
                    style: { colors: '#9eaac7', fontSize: '12px', fontFamily: 'inherit' },
                    formatter: v => '₱' + Number(v).toLocaleString()
                }
            },
            tooltip: { theme: 'dark', y: { formatter: v => '₱' + Number(v).toLocaleString(undefined, { minimumFractionDigits: 2 }) } }
        }).render();

        // 2. Order Status – Donut
        const comp = <?= (int) $completed_orders ?>;
        const pend = <?= (int) $pending_orders ?>;
        const total = comp + pend;
        new ApexCharts(document.querySelector('#order-donut-chart'), {
            series: total > 0 ? [comp, pend] : [1],
            labels: total > 0 ? ['Completed', 'Pending'] : ['No Data'],
            chart: { type: 'donut', height: 140, fontFamily: "'Plus Jakarta Sans', sans-serif" },
            colors: total > 0 ? ['#13deb9', '#ffae1f'] : ['#e5eaef'],
            plotOptions: { pie: { donut: { size: '72%', labels: { show: false } } } },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { show: false },
            tooltip: { theme: 'dark' }
        }).render();

        // 3. Revenue – Sparkline Area
        new ApexCharts(document.querySelector('#revenue-sparkline-chart'), {
            series: [{ name: 'Revenue', data: chartData.length ? chartData : [0, 0, 0, 0, 0, 0, 0] }],
            chart: { type: 'area', height: 80, sparkline: { enabled: true }, fontFamily: "'Plus Jakarta Sans', sans-serif" },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: {
                colors: ['#5d87ff'], type: 'gradient',
                gradient: { shadeIntensity: 0, inverseColors: false, opacityFrom: 0.3, opacityTo: 0.02, stops: [0, 100] }
            },
            colors: ['#5d87ff'],
            tooltip: { theme: 'dark', x: { show: false }, y: { formatter: v => '₱' + Number(v).toLocaleString() } }
        }).render();

        // 4. Flatpickr date range
        if (typeof flatpickr !== 'undefined') {
            flatpickr('#date-range-picker', {
                mode: 'range',
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: 'M j, Y',
                onClose: function (dates, str) {
                    if (str) document.getElementById('dashboard-filter-form').submit();
                }
            });
        }
    });
</script>


<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>