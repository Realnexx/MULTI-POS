<?php
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Branch.php';
require_once __DIR__ . '/../../models/Sale.php';
require_once __DIR__ . '/../../models/Expense.php';
$logs = $logs ?? [];
$products = $products ?? [];
$branches = $branches ?? Branch::for_current_user();
$sale_stats = $sale_stats ?? ['revenue' => 0, 'discounts' => 0, 'count' => 0];
$sale_stats_today = $sale_stats_today ?? ['revenue' => 0, 'orders' => 0];
$top_selling = $top_selling ?? [];
$recent_sales = $recent_sales ?? [];
$expenses = $expenses ?? [];
$expense_stats = $expense_stats ?? ['total_this_month' => 0, 'count_today' => 0, 'highest_category' => 'N/A'];
$expense_categories = $expense_categories ?? [];
$inventory_stats = $inventory_stats ?? ['total_items' => 0, 'low_stock_count' => 0, 'total_valuation' => 0, 'total_revenue' => 0];
$low_stock_products = $low_stock_products ?? [];

$title = "Reports & Analytics";
ob_start();
?>

<link rel="stylesheet" href="<?= url('assets/css/inventory-reports.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/inventory-reports.css') ?>">
<section class="inventory-reports" aria-labelledby="reports-page-title">
<header class="reports-heading">
    <div class="reports-heading-icon" aria-hidden="true"><span class="material-symbols-outlined">monitoring</span></div>
    <div>
        <div class="reports-heading-title">
            <h1 id="reports-page-title">Reports & Analytics</h1>
            <span class="reports-label">Business overview</span>
        </div>
        <p>Sales, stock movements and expenses in one place.</p>
    </div>
</header>

<div class="reports-mobile-filterbar">
    <button type="button" class="btn reports-filter-trigger" data-bs-toggle="modal" data-bs-target="#reportsFiltersModal"><span class="material-symbols-outlined" aria-hidden="true">tune</span> Filters</button>
    <span>Branch and date range</span>
</div>
<div id="reportsFilterDesktopSlot"></div>
<!-- Filter Bar -->
<form method="GET" class="reports-filters" id="reportFilterForm">
    <input type="hidden" name="route" value="inventory/logs">
    <input type="hidden" name="tab" id="filterActiveTab" value="<?= htmlspecialchars($_GET['tab'] ?? 'dashboard') ?>">
    <div class="reports-filter-inner">
        <span class="material-symbols-outlined reports-filter-icon" aria-hidden="true">filter_alt</span>

        <select aria-label="Filter by branch" name="branch_id" class="form-select border-0 bg-light bg-opacity-50 shadow-none" style="width: auto; font-size: 0.78rem; padding: 6px 28px 6px 12px;">
            <?php if (User::has_all_branch_access((int)($_SESSION['user']['id'] ?? 0))): ?>
                <option value="">All Branches</option>
            <?php endif; ?>
            <?php foreach ($branches as $b): ?>
                <option value="<?= $b['id'] ?>" <?= ($filter_branch_id ?? null) == $b['id'] ? 'selected' : '' ?>><?= htmlspecialchars($b['name']) ?></option>
            <?php endforeach; ?>
        </select>

        <div class="d-flex gap-1 reports-presets" aria-label="Date presets">
            <?php
            $presets = [
                'today' => 'Today',
                'week' => 'This Week',
                'month' => 'This Month',
                'year' => 'This Year',
            ];
            $active_preset = $_GET['preset'] ?? '';
            foreach ($presets as $val => $label):
                $preset_url = '?route=inventory/logs';
                if ($filter_branch_id) $preset_url .= '&branch_id=' . $filter_branch_id;
                if ($val === 'today') {
                    $from = date('Y-m-d');
                    $to = date('Y-m-d');
                } elseif ($val === 'week') {
                    $from = date('Y-m-d', strtotime('monday this week'));
                    $to = date('Y-m-d');
                } elseif ($val === 'month') {
                    $from = date('Y-m-01');
                    $to = date('Y-m-d');
                } elseif ($val === 'year') {
                    $from = date('Y-01-01');
                    $to = date('Y-m-d');
                }
                $active_tab = $_GET['tab'] ?? 'dashboard';
                $preset_url .= '&date_from=' . $from . '&date_to=' . $to . '&preset=' . $val . '&tab=' . $active_tab;
            ?>
                <a href="<?= $preset_url ?>"
                   class="btn btn-sm rounded-pill fw-semibold px-3 <?= $active_preset === $val ? 'btn-primary text-white' : 'btn-light text-muted' ?>"
                   style="font-size: 0.72rem; <?= $active_preset === $val ? 'background: #5d87ff; border-color: #5d87ff;' : '' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="reports-date-range">
            <input aria-label="Start date" type="date" name="date_from" value="<?= htmlspecialchars($_GET['date_from'] ?? '') ?>"
                   class="form-control border-0 bg-light bg-opacity-50 shadow-none" style="width: 140px; font-size: 0.78rem; padding: 6px 10px;">
            <span class="text-muted" style="font-size: 0.72rem;">to</span>
            <input aria-label="End date" type="date" name="date_to" value="<?= htmlspecialchars($_GET['date_to'] ?? '') ?>"
                   class="form-control border-0 bg-light bg-opacity-50 shadow-none" style="width: 140px; font-size: 0.78rem; padding: 6px 10px;">
        </div>

        <button type="submit" class="btn btn-sm fw-bold px-3 rounded-pill text-white border-0 reports-apply"
                style="background: #5d87ff; font-size: 0.72rem;">Apply</button>

        <?php if ($filter_branch_id || $filter_date_from || $filter_date_to): ?>
            <a href="?route=inventory/logs&tab=<?= htmlspecialchars($_GET['tab'] ?? 'dashboard') ?>" class="btn btn-sm btn-light rounded-pill px-3 fw-semibold" style="font-size: 0.72rem;">Clear</a>
        <?php endif; ?>
    </div>
</form>

<!-- Tab Navigation -->
<ul class="nav report-tab gap-2 mb-4 pb-2 border-bottom" id="reportTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="dashboard-tab" data-bs-toggle="tab" data-bs-target="#dashboard" type="button" role="tab">
            <span class="material-symbols-outlined me-1" style="font-size: 16px; vertical-align: middle;">dashboard</span>
            Dashboard
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button" role="tab">
            <span class="material-symbols-outlined me-1" style="font-size: 16px; vertical-align: middle;">point_of_sale</span>
            Sales Report
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="inventory-tab" data-bs-toggle="tab" data-bs-target="#inventory" type="button" role="tab">
            <span class="material-symbols-outlined me-1" style="font-size: 16px; vertical-align: middle;">inventory</span>
            Inventory Report
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="expenses-tab" data-bs-toggle="tab" data-bs-target="#expenses" type="button" role="tab">
            <span class="material-symbols-outlined me-1" style="font-size: 16px; vertical-align: middle;">receipt_long</span>
            Expenses Report
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="revenue-tab" data-bs-toggle="tab" data-bs-target="#revenue" type="button" role="tab">
            <span class="material-symbols-outlined me-1" style="font-size: 16px; vertical-align: middle;">trending_up</span>
            Revenue Report
        </button>
    </li>
</ul>

<div class="tab-content" id="reportTabsContent">

<!-- ===================== DASHBOARD TAB ===================== -->
<div class="tab-pane fade show active" id="dashboard" role="tabpanel">
    <div class="reports-print-toolbar"><a class="btn btn-sm btn-light report-print-link" data-report-tab="dashboard" href="<?= url('?route=inventory/logs/print&tab=dashboard' . (!empty($filter_branch_id) ? '&branch_id=' . (int)$filter_branch_id : '') . '&date_from=' . rawurlencode($filter_date_from ?? '') . '&date_to=' . rawurlencode($filter_date_to ?? '')) ?>" target="_blank" rel="noopener"><span class="material-symbols-outlined" aria-hidden="true">print</span> Print Overview</a></div>
    <div class="row g-2 mb-3 reports-stat-row">
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white">
                <div class="text-muted small text-uppercase fw-bold mb-1"><span class="material-symbols-outlined me-2" aria-hidden="true">payments</span>Today's Revenue</div>
                <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($sale_stats_today['revenue'] ?? 0, 2) ?></h3>
                <div class="mt-2"><span class="badge bg-success-subtle text-success rounded-pill fs-8"><?= $sale_stats_today['orders'] ?? 0 ?> orders</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white">
                <div class="text-muted small text-uppercase fw-bold mb-1"><span class="material-symbols-outlined me-2" aria-hidden="true">monitoring</span>Total Revenue</div>
                <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($sale_stats['revenue'] ?? 0, 2) ?></h3>
                <div class="mt-2"><span class="badge bg-primary-subtle text-primary rounded-pill fs-8"><?= $sale_stats['count'] ?? 0 ?> transactions</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white">
                <div class="text-muted small text-uppercase fw-bold mb-1"><span class="material-symbols-outlined me-2" aria-hidden="true">inventory_2</span>Inventory Valuation</div>
                <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($inventory_stats['total_valuation'], 2) ?></h3>
                <div class="mt-2"><span class="badge bg-warning-subtle text-warning rounded-pill fs-8"><?= $inventory_stats['low_stock_count'] ?> low stock</span></div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white">
                <div class="text-muted small text-uppercase fw-bold mb-1"><span class="material-symbols-outlined me-2" aria-hidden="true">receipt_long</span>Expenses (Selected Period)</div>
                <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($expense_stats['total_this_month'], 2) ?></h3>
                <div class="mt-2"><span class="badge bg-danger-subtle text-danger rounded-pill fs-8">Top: <?= htmlspecialchars($expense_stats['highest_category']) ?></span></div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm border border-light">
                <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">trending_up</span> Top Selling Products</h6>
                </div>
                <div class="card-body p-0 tabular-data">
                    <div class="report-table-scroll"><table class="table table-hover mb-0 report-table">
                        <thead><tr><th class="ps-4">#</th><th>Product</th><th class="text-end pe-4">Qty Sold</th></tr></thead>
                        <tbody>
                            <?php if (empty($top_selling)): ?>
                                <tr><td colspan="3" class="text-center text-muted py-4">No sales data yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($top_selling as $i => $ts): ?>
                                    <tr>
                                        <td class="ps-4 text-muted fw-bold"><?= $i + 1 ?></td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($ts['name']) ?></td>
                                        <td class="text-end pe-4 fw-bold" style="color: #5d87ff;"><?= (int) $ts['total_qty'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 rounded-4 shadow-sm border border-light">
                <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">warning</span> Low Stock Alerts</h6>
                </div>
                <div class="card-body p-0 tabular-data">
                    <div class="report-table-scroll"><table class="table table-hover mb-0 report-table">
                        <thead><tr><th class="ps-4">Product</th><th class="text-center">Stock</th><th class="text-center">Threshold</th><th class="text-end pe-4">Status</th></tr></thead>
                        <tbody>
                            <?php if (empty($low_stock_products)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">All products are well-stocked.</td></tr>
                            <?php else: ?>
                                <?php foreach ($low_stock_products as $lp): ?>
                                    <tr>
                                        <td class="ps-4 fw-semibold text-dark"><?= htmlspecialchars($lp['name']) ?></td>
                                        <td class="text-center fw-bold text-danger"><?= (int) $lp['stock'] ?></td>
                                        <td class="text-center text-muted"><?= (int) $lp['low_stock_threshold'] ?></td>
                                        <td class="text-end pe-4"><span class="badge bg-danger-subtle text-danger rounded-pill fs-8">Low Stock</span></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-2">
        <div class="col-12">
            <div class="card border-0 rounded-4 shadow-sm border border-light">
                <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">leaderboard</span> <?= $filter_branch_id !== null ? 'Branch Sales Rank' : 'Top Performing Branches' ?></h6>
                </div>
                <div class="card-body p-0">
                    <p class="small text-muted px-4 pt-3 mb-2">Ranked by completed sales revenue after discounts in the selected date range, across all active branches. Equal revenue shares a rank.</p>
                    <div class="report-table-scroll"><table class="table table-hover mb-0 report-table">
                        <thead><tr><th class="ps-4">Rank</th><th>Branch</th><th class="text-center">Orders</th><th class="text-end pe-4">Revenue</th></tr></thead>
                        <tbody>
                            <?php if (empty($top_branches)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No sales data yet.</td></tr>
                            <?php else: ?>
                                <?php
                                $rank_colors = ['#5d87ff', '#e85d3a', '#f09c3e'];
                                $rank_icons = ['emoji_events', 'military_tech', 'star'];
                                ?>
                                <?php foreach ($top_branches as $i => $tb): ?>
                                    <?php
                                    $is_top3 = $i < 3;
                                    $bg = $is_top3 ? 'bg-light' : '';
                                    ?>
                                    <tr class="reports-branch-rank <?= $bg ?>">
                                        <td class="ps-4" style="width: 60px;">
                                            <?php $branchRank = (int)($tb['rank'] ?? $i + 1); $rankTone = $branchRank === 1 ? 'gold' : ($branchRank === 2 ? 'silver' : ($branchRank === 3 ? 'bronze' : 'standard')); ?>
                                            <span class="reports-rank-badge reports-rank-<?= $rankTone ?>"><span class="material-symbols-outlined" aria-hidden="true"><?= $branchRank === 1 ? 'emoji_events' : ($branchRank <= 3 ? 'military_tech' : 'leaderboard') ?></span><span>#<?= $branchRank ?></span></span>
                                        </td>
                                        <td class="fw-semibold text-dark"><?= htmlspecialchars($tb['name'] ?? 'Unassigned') ?></td>
                                        <td class="text-center fw-bold"><?= (int) ($tb['total_orders'] ?? 0) ?></td>
                                        <td class="text-end pe-4 fw-bold" style="color: #5d87ff;">₱<?= number_format((float) ($tb['total_revenue'] ?? 0), 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#dashboard .stat-card { border:1px solid #e1eaf0; border-radius:18px; box-shadow:0 5px 20px rgba(25,62,82,.045); height:100%; }
#dashboard .stat-card::before,#dashboard .stat-card::after { display:none; }
#dashboard .stat-card>.text-uppercase { display:flex; align-items:center; font-size:.72rem; }
#dashboard .stat-card .material-symbols-outlined { color:#008aa5; background:#eaf8fb; padding:8px; border-radius:12px; font-size:22px; }
#dashboard .stat-card h3 { overflow-wrap:anywhere; }
</style>
<!-- ===================== SALES REPORT TAB ===================== -->
<div class="tab-pane fade" id="sales" role="tabpanel">
    <div class="reports-print-toolbar"><a class="btn btn-sm btn-light report-print-link" data-report-tab="sales" href="<?= url('?route=inventory/logs/print&tab=sales' . (!empty($filter_branch_id) ? '&branch_id=' . (int)$filter_branch_id : '') . '&date_from=' . rawurlencode($filter_date_from ?? '') . '&date_to=' . rawurlencode($filter_date_to ?? '')) ?>" target="_blank" rel="noopener"><span class="material-symbols-outlined" aria-hidden="true">print</span> Print Sales</a></div>
    <div class="row g-2 mb-3 reports-stat-row">
        <div class="col-6 col-xl-4">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0288d1;">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Total Orders</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font"><?= $sale_stats['count'] ?? 0 ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <span class="material-symbols-outlined">trending_up</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Total Revenue</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($sale_stats['revenue'] ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef2f2; color: #dc2626;">
                    <span class="material-symbols-outlined">money_off</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Total Discounts</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($sale_stats['discounts'] ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm border border-light">
        <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">receipt_long</span> Transactions</h6>
            <input type="text" id="salesDtSearch" aria-label="Search transactions" class="form-control form-control-sm border-0 bg-light shadow-none rounded-pill px-3" style="width: 200px; font-size: 0.75rem;" placeholder="Search transactions...">
        </div>
        <div class="card-body p-0">
            <div class="report-table-scroll"><table class="table table-hover mb-0 report-table w-100" id="salesReportTable">
                <thead><tr><th class="ps-4">#</th><th>Cashier</th><th>Products</th><th>Qty</th><th>Total</th><th>Discount</th><th>Final</th><th>Payment</th><th class="text-end pe-4">Date</th></tr></thead>
            </table></div>
        </div>
    </div>
</div>

<!-- ===================== INVENTORY REPORT TAB ===================== -->
<div class="tab-pane fade" id="inventory" role="tabpanel">
    <div class="reports-print-toolbar"><a class="btn btn-sm btn-light report-print-link" data-report-tab="inventory" href="<?= url('?route=inventory/logs/print&tab=inventory' . (!empty($filter_branch_id) ? '&branch_id=' . (int)$filter_branch_id : '') . '&date_from=' . rawurlencode($filter_date_from ?? '') . '&date_to=' . rawurlencode($filter_date_to ?? '')) ?>" target="_blank" rel="noopener"><span class="material-symbols-outlined" aria-hidden="true">print</span> Print Stock Movements</a></div>
    <div class="row g-2 mb-3 reports-stat-row">
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0288d1;">
                    <span class="material-symbols-outlined">inventory_2</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Total Products</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font"><?= $inventory_stats['total_items'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef2f2; color: #dc2626;">
                    <span class="material-symbols-outlined">warning</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Low Stock Items</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font" style="<?= $inventory_stats['low_stock_count'] > 0 ? 'color: #dc2626;' : '' ?>"><?= $inventory_stats['low_stock_count'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <span class="material-symbols-outlined">payments</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Cost Valuation</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($inventory_stats['total_valuation'], 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #f3e8ff; color: #9333ea;">
                    <span class="material-symbols-outlined">trending_up</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Potential Revenue</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font" style="color: #5d87ff;">₱<?= number_format($inventory_stats['total_revenue'], 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm border border-light">
        <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">history</span> Inventory Audit Log</h6>
            <div class="d-flex gap-2 align-items-center reports-header-actions">
                <input type="text" id="inventoryDtSearch" aria-label="Search inventory movements" class="form-control form-control-sm border-0 bg-light shadow-none rounded-pill px-3" style="width: 200px; font-size: 0.75rem;" placeholder="Search logs...">
                <?php if(User::has_permission($_SESSION['user']['id'],'view_product')): ?><a href="<?= url('?route=inventory/products') ?>" class="btn btn-sm btn-light rounded-pill px-3 fw-medium">View Products</a><?php endif; ?>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="report-table-scroll"><table class="table table-hover mb-0 report-table w-100" id="inventoryLogsTable">
                <thead><tr><th class="ps-4">Product</th><th>Type</th><th>Qty</th><th>Staff</th><th>Remarks</th><th class="text-end pe-4">Date</th></tr></thead>
            </table></div>
        </div>
    </div>
</div>

<!-- ===================== EXPENSES REPORT TAB ===================== -->
<div class="tab-pane fade" id="expenses" role="tabpanel">
    <div class="reports-print-toolbar"><a class="btn btn-sm btn-light report-print-link" data-report-tab="expenses" href="<?= url('?route=inventory/logs/print&tab=expenses' . (!empty($filter_branch_id) ? '&branch_id=' . (int)$filter_branch_id : '') . '&date_from=' . rawurlencode($filter_date_from ?? '') . '&date_to=' . rawurlencode($filter_date_to ?? '')) ?>" target="_blank" rel="noopener"><span class="material-symbols-outlined" aria-hidden="true">print</span> Print Expenses</a></div>
    <div class="row g-2 mb-3 reports-stat-row">
        <div class="col-6 col-xl-4">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef2f2; color: #dc2626;">
                    <span class="material-symbols-outlined">receipt_long</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">This Month</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($expense_stats['total_this_month'], 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef3c7; color: #d97706;">
                    <span class="material-symbols-outlined">today</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Today's Count</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font"><?= $expense_stats['count_today'] ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-4">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #f3e8ff; color: #9333ea;">
                    <span class="material-symbols-outlined">category</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Top Category</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font"><?= htmlspecialchars($expense_stats['highest_category']) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm border border-light">
        <div class="card-header bg-transparent border-bottom px-4 py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">receipt_long</span> All Expenses</h6>
            <input type="text" id="expensesDtSearch" aria-label="Search expenses" class="form-control form-control-sm border-0 bg-light shadow-none rounded-pill px-3" style="width: 200px; font-size: 0.75rem;" placeholder="Search expenses...">
        </div>
        <div class="card-body p-0">
            <div class="report-table-scroll"><table class="table table-hover mb-0 report-table w-100" id="expensesReportTable">
                <thead><tr><th class="ps-4">Date</th><th>Category</th><th>Description</th><th>Branch</th><th class="text-end pe-4">Amount</th></tr></thead>
            </table></div>
        </div>
    </div>
</div>

<!-- ===================== REVENUE REPORT TAB ===================== -->
<div class="tab-pane fade" id="revenue" role="tabpanel">
    <div class="reports-print-toolbar"><a class="btn btn-sm btn-light report-print-link" data-report-tab="revenue" href="<?= url('?route=inventory/logs/print&tab=revenue' . (!empty($filter_branch_id) ? '&branch_id=' . (int)$filter_branch_id : '') . '&date_from=' . rawurlencode($filter_date_from ?? '') . '&date_to=' . rawurlencode($filter_date_to ?? '')) ?>" target="_blank" rel="noopener"><span class="material-symbols-outlined" aria-hidden="true">print</span> Print Revenue</a></div>
    <div class="row g-2 mb-3 reports-stat-row">
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #dcfce7; color: #16a34a;">
                    <span class="material-symbols-outlined">trending_up</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Gross Revenue</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format(($sale_stats['revenue'] ?? 0) + ($sale_stats['discounts'] ?? 0), 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #fef2f2; color: #dc2626;">
                    <span class="material-symbols-outlined">money_off</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Total Discounts</div>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">- ₱<?= number_format($sale_stats['discounts'] ?? 0, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #d1fae5; color: #059669;">
                    <span class="material-symbols-outlined">account_balance</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Net Revenue</div>
                    <?php $net_revenue = (float) ($sale_stats['revenue'] ?? 0); ?>
                    <h3 class="fw-bold mb-0 outfit-font" style="color: #059669;">₱<?= number_format($net_revenue, 2) ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-xl-3">
            <div class="stat-card p-3 bg-white d-flex align-items-center gap-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px; background: #e0f2fe; color: #0288d1;">
                    <span class="material-symbols-outlined">shopping_cart_checkout</span>
                </div>
                <div>
                    <div class="text-muted small text-uppercase fw-bold mb-0">Avg Order Value</div>
                    <?php $avg = ($sale_stats['count'] ?? 0) > 0 ? ($sale_stats['revenue'] ?? 0) / ($sale_stats['count'] ?? 1) : 0; ?>
                    <h3 class="fw-bold text-dark mb-0 outfit-font">₱<?= number_format($avg, 2) ?></h3>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 rounded-4 shadow-sm border border-light">
        <div class="card-header bg-transparent border-bottom px-4 py-3">
            <h6 class="fw-bold text-dark mb-0"><span class="material-symbols-outlined me-1" style="font-size: 16px; color: #5d87ff;">analytics</span> Revenue Summary</h6>
        </div>
        <div class="card-body p-4">
            <div class="revenue-statement" aria-label="Revenue statement">
                <div class="statement-heading"><span>Revenue statement</span><small>Selected reporting period · Philippine peso (PHP)</small></div>
                <div class="statement-line statement-section"><span>Sales revenue</span><span>Amount</span></div>
                <div class="statement-line"><span>Gross sales</span><strong>₱<?= number_format(($sale_stats['revenue'] ?? 0) + ($sale_stats['discounts'] ?? 0), 2) ?></strong></div>
                <div class="statement-line statement-deduction"><span>Less: Sales discounts</span><span>(₱<?= number_format($sale_stats['discounts'] ?? 0, 2) ?>)</span></div>
                <div class="statement-line statement-subtotal"><span>Net sales revenue</span><strong>₱<?= number_format($net_revenue, 2) ?></strong></div>
                <div class="statement-line statement-deduction"><span>Less: Recorded expenses</span><span>(₱<?= number_format($expense_stats['total_this_month'] ?? 0, 2) ?>)</span></div>
                <?php $net_profit = $net_revenue - ($expense_stats['total_this_month'] ?? 0); ?>
                <div class="statement-line statement-total"><span>Revenue less expenses</span><strong><?= $net_profit < 0 ? '(₱' : '₱' ?><?= number_format(abs($net_profit), 2) ?><?= $net_profit < 0 ? ')' : '' ?></strong></div>
                <p class="statement-note">This balance excludes cost of goods sold and unrecorded costs; it is not net profit.</p>
                <div class="statement-metrics"><div><span>Completed transactions</span><strong><?= number_format($sale_stats['count'] ?? 0) ?></strong></div><div><span>Average order value</span><strong>₱<?= number_format($avg, 2) ?></strong></div></div>
            </div>
        </div>
    </div>
</div>

</div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    function escapeLog(value) { return $('<div>').text(value == null ? '' : value).html(); }
    const branchId = <?= json_encode($filter_branch_id) ?>;
    const dateFrom = <?= json_encode($filter_date_from) ?>;
    const dateTo = <?= json_encode($filter_date_to) ?>;
    const csrfToken = '<?= csrf_token() ?>';

    document.querySelectorAll('.report-print-link').forEach(function (link) {
        link.addEventListener('click', function () {
            const searchIds = {sales: 'salesDtSearch', inventory: 'inventoryDtSearch', expenses: 'expensesDtSearch'};
            const input = document.getElementById(searchIds[link.dataset.reportTab]);
            const target = new URL(link.href, location.href);
            if (input && input.value.trim()) target.searchParams.set('search', input.value.trim());
            else target.searchParams.delete('search');
            link.href = target.toString();
        });
    });

    // Activate the correct tab from URL param
    const activeTab = <?= json_encode(in_array($_GET['tab'] ?? '', ['dashboard','sales','inventory','expenses','revenue'], true) ? $_GET['tab'] : 'dashboard', JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const tabBtn = document.querySelector('#reportTabs .nav-link[data-bs-target="#' + activeTab + '"]');
    if (tabBtn) {
        bootstrap.Tab.getOrCreateInstance(tabBtn).show();
    }

    // Update hidden input when tab changes (to preserve across filter submits)
    document.querySelectorAll('#reportTabs .nav-link').forEach(function (link) {
        link.addEventListener('shown.bs.tab', function (e) {
            const targetId = e.target.getAttribute('data-bs-target').replace('#', '');
            document.getElementById('filterActiveTab').value = targetId;
        });
    });

    // Initialize sales DataTable only when the sales tab is first shown
    let salesTableInit = false;
    document.querySelector('#sales-tab').addEventListener('shown.bs.tab', function () {
        if (salesTableInit) return;
        salesTableInit = true;

        const salesTable = $('#salesReportTable').DataTable({
            autoWidth: false,
            dom: 'rt<"reports-table-footer"lip>',
            processing: true,
            serverSide: true,
            pageLength: 10,
            ordering: true,
            order: [[0, 'desc']],
            ajax: {
                url: 'ajax/inventory_action.php',
                type: 'POST',
                data: function (d) {
                    d.action = 'sales_dt';
                    d.csrf_token = csrfToken;
                    if (branchId) d.branch_id = branchId;
                    if (dateFrom) d.date_from = dateFrom;
                    if (dateTo) d.date_to = dateTo;
                    return d;
                },
                dataSrc: function (json) {
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'id',
                    render: function (data) {
                        return '<span class="ps-4 text-muted fw-bold">#' + String(data).padStart(6, '0') + '</span>';
                    }
                },
                {
                    data: 'cashier_name',
                    render: function (data) {
                        return '<span class="fw-semibold text-dark">' + escapeLog((data || 'System')) + '</span>';
                    }
                },
                {
                    data: 'products',
                    render: function (data) {
                        return data ? '<span class="text-dark small">' + escapeLog(data) + '</span>' : '<span class="text-muted small">-</span>';
                    }
                },
                {
                    data: 'total_qty',
                    className: 'text-center',
                    render: function (data) {
                        return '<span class="fw-semibold">' + (data || 0) + '</span>';
                    }
                },
                {
                    data: 'total_amount',
                    render: function (data) {
                        return '₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 });
                    }
                },
                {
                    data: 'discount_amount',
                    render: function (data) {
                        return '<span class="text-danger">- ₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</span>';
                    }
                },
                {
                    data: 'final_amount',
                    render: function (data) {
                        return '<span class="fw-bold" style="color: #5d87ff;">₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</span>';
                    }
                },
                {
                    data: 'payment_method',
                    render: function (data) {
                        return '<span class="badge bg-light text-dark border rounded-pill fs-8">' + escapeLog((data || 'N/A')) + '</span>';
                    }
                },
                {
                    data: 'created_at',
                    className: 'text-end pe-4',
                    render: function (data) {
                        const d = new Date(data);
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
                            ' <span class="text-muted">' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + '</span>';
                    }
                }
            ],
            language: {
                lengthMenu: "Show _MENU_ logs",
                info: "Showing _START_ to _END_ of _TOTAL_ logs",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: `
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined fs-1 text-muted opacity-50">receipt_long</span>
                        </div>
                        <p class="text-muted fw-semibold mb-1" style="font-size:0.9rem;">No Transactions Found</p>
                        <p class="text-muted small mb-0">Sales records will appear here once transactions are made.</p>
                    </div>`,
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Custom search
        $('#salesDtSearch').on('keyup', function () {
            salesTable.search(this.value).draw();
        });

        // Re-draw on filter form submit without page reload
        $('#reportFilterForm').on('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const params = new URLSearchParams(formData);
            window.location.search = params.toString();
        });
    });

    // If sales tab is already active on load (from URL param), init it
    if (activeTab === 'sales') {
        document.querySelector('#sales-tab').dispatchEvent(new Event('shown.bs.tab'));
    }

    // Initialize inventory DataTable only when the inventory tab is first shown
    let inventoryTableInit = false;
    document.querySelector('#inventory-tab').addEventListener('shown.bs.tab', function () {
        if (inventoryTableInit) return;
        inventoryTableInit = true;

        const inventoryTable = $('#inventoryLogsTable').DataTable({
            autoWidth: false,
            dom: 'rt<"reports-table-footer"lip>',
            processing: true,
            serverSide: true,
            pageLength: 10,
            ordering: true,
            order: [[5, 'desc']],
            ajax: {
                url: 'ajax/inventory_action.php',
                type: 'POST',
                data: function (d) {
                    d.action = 'logs_dt';
                    d.csrf_token = csrfToken;
                    if (branchId) d.branch_id = branchId;
                    if (dateFrom) d.date_from = dateFrom;
                    if (dateTo) d.date_to = dateTo;
                    return d;
                },
                dataSrc: function (json) {
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'product_name',
                    render: function (data) {
                        return '<span class="ps-4 fw-semibold text-dark">' + escapeLog((data || 'Deleted')) + '</span>';
                    }
                },
                {
                    data: 'action',
                    render: function (data) {
                        if (data === 'stock_in' || data === 'initial') {
                            return '<span class="badge bg-success-subtle text-success rounded-pill fs-8">IN</span>';
                        }
                        return '<span class="badge bg-danger-subtle text-danger rounded-pill fs-8">OUT</span>';
                    }
                },
                {
                    data: null,
                    className: 'fw-bold',
                    render: function (data) {
                        const isIn = (data.action === 'stock_in' || data.action === 'initial');
                        const symbol = isIn ? '+' : '-';
                        const cls = isIn ? 'text-success' : 'text-danger';
                        return '<span class="' + cls + '">' + symbol + (data.quantity || 0) + '</span>';
                    }
                },
                {
                    data: 'user_name',
                    render: function (data) {
                        return '<span class="text-muted">' + escapeLog((data || 'System')) + '</span>';
                    }
                },
                {
                    data: 'remarks',
                    render: function (data) {
                        return '<span class="text-muted small">' + escapeLog((data || '')) + '</span>';
                    }
                },
                {
                    data: 'created_at',
                    className: 'text-end pe-4 text-muted',
                    render: function (data) {
                        const d = new Date(data);
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) +
                            ' <span class="text-muted">' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }) + '</span>';
                    }
                }
            ],
            language: {
                lengthMenu: "Show _MENU_ logs",
                info: "Showing _START_ to _END_ of _TOTAL_ logs",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: `
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined fs-1 text-muted opacity-50">history</span>
                        </div>
                        <p class="text-muted fw-semibold mb-1" style="font-size:0.9rem;">No Inventory Logs Found</p>
                        <p class="text-muted small mb-0">Inventory movements will appear here once stock adjustments are made.</p>
                    </div>`,
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Custom search
        $('#inventoryDtSearch').on('keyup', function () {
            inventoryTable.search(this.value).draw();
        });
    });

    // If inventory tab is already active on load, init it
    if (activeTab === 'inventory') {
        document.querySelector('#inventory-tab').dispatchEvent(new Event('shown.bs.tab'));
    }

    // Initialize expenses DataTable only when the expenses tab is first shown
    let expensesTableInit = false;
    document.querySelector('#expenses-tab').addEventListener('shown.bs.tab', function () {
        if (expensesTableInit) return;
        expensesTableInit = true;

        const expensesTable = $('#expensesReportTable').DataTable({
            autoWidth: false,
            dom: 'rt<"reports-table-footer"lip>',
            processing: true,
            serverSide: true,
            pageLength: 10,
            ordering: true,
            order: [[0, 'desc']],
            ajax: {
                url: 'ajax/inventory_action.php',
                type: 'POST',
                data: function (d) {
                    d.action = 'expenses_dt';
                    d.csrf_token = csrfToken;
                    if (branchId) d.branch_id = branchId;
                    if (dateFrom) d.date_from = dateFrom;
                    if (dateTo) d.date_to = dateTo;
                    return d;
                },
                dataSrc: function (json) {
                    return json.data;
                }
            },
            columns: [
                {
                    data: 'expense_date',
                    className: 'ps-4 text-muted',
                    render: function (data) {
                        const d = new Date(data);
                        return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                    }
                },
                {
                    data: 'category',
                    render: function (data) {
                        return '<span class="badge bg-light text-dark border rounded-pill fs-8">' + escapeLog(data) + '</span>';
                    }
                },
                {
                    data: 'description',
                    render: function (data) {
                        return '<span class="text-dark small">' + escapeLog((data || '')) + '</span>';
                    }
                },
                {
                    data: 'branch_name',
                    render: function (data) {
                        return '<span class="text-muted">' + (data || 'Main') + '</span>';
                    }
                },
                {
                    data: 'amount',
                    className: 'text-end pe-4',
                    render: function (data) {
                        return '<span class="fw-bold text-danger">₱' + parseFloat(data).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</span>';
                    }
                }
            ],
            language: {
                lengthMenu: "Show _MENU_ expenses",
                info: "Showing _START_ to _END_ of _TOTAL_ expenses",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                },
                emptyTable: `
                    <div class="text-center py-5">
                        <div class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 80px; height: 80px;">
                            <span class="material-symbols-outlined fs-1 text-muted opacity-50">receipt_long</span>
                        </div>
                        <p class="text-muted fw-semibold mb-1" style="font-size:0.9rem;">No Expenses Found</p>
                        <p class="text-muted small mb-0">Expense records will appear here once expenses are added.</p>
                    </div>`,
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
            }
        });

        // Custom search
        $('#expensesDtSearch').on('keyup', function () {
            expensesTable.search(this.value).draw();
        });
    });

    // If expenses tab is already active on load, init it
    if (activeTab === 'expenses') {
        document.querySelector('#expenses-tab').dispatchEvent(new Event('shown.bs.tab'));
    }
});
</script>

<div class="modal fade reports-filters-modal" id="reportsFiltersModal" tabindex="-1" aria-labelledby="reportsFiltersTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title" id="reportsFiltersTitle"><span class="material-symbols-outlined" aria-hidden="true">tune</span> Report filters</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close filters"></button></div>
    <div class="modal-body" id="reportsFilterMobileSlot"></div>
    <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" form="reportFilterForm" class="btn btn-primary"><span class="material-symbols-outlined" aria-hidden="true">check</span> Apply filters</button></div>
  </div></div>
</div>
<script defer src="<?= url('assets/js/inventory-report-cards.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/inventory-report-cards.js') ?>"></script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
