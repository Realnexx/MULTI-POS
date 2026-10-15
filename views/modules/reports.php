<?php
$title = "Performance Analytics";

if (!isset($is_admin)) {
  $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
}
if (!isset($branches)) {
  require_once __DIR__ . '/../../models/Branch.php';
  $branches = $is_admin ? (Branch::get_all_active() ?: []) : [];
}

ob_start();
?>

<!-- Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<style>
  /* ================================================================
   AURORA REPORTS — CLEAN MODERN STYLES (No Glassmorphism)
   ================================================================ */

  /* Page Header */
  .rpt-page-header {
    margin-top: 0.25rem;
    margin-bottom: 1.5rem;
  }
  .rpt-page-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #2a3547;
    letter-spacing: -0.5px;
    margin-bottom: 4px;
  }
  .rpt-page-subtitle {
    font-size: 0.88rem;
    color: #7c8fac;
    margin-bottom: 0;
  }
  .rpt-badge-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #eef2ff;
    color: #5d87ff;
    border-radius: 50px;
    padding: 4px 12px;
    font-size: 0.75rem;
    font-weight: 700;
    margin-bottom: 8px;
  }

  /* Aurora Solid Cards */
  .aurora-card {
    border-radius: 18px;
    border: 1px solid #e8edf5;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
    background: #ffffff;
    transition: box-shadow 0.2s ease;
  }
  .aurora-card:hover {
    box-shadow: 0 6px 22px rgba(0, 0, 0, 0.06);
  }

  /* KPI stat cards */
  .rpt-kpi-card {
    border-radius: 18px;
    border: 1px solid #e8edf5;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    overflow: hidden;
    background: #ffffff;
  }
  .rpt-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
  }
  .rpt-kpi-glow {
    height: 4px;
    width: 100%;
  }
  .rpt-kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  .rpt-kpi-value {
    font-size: 1.85rem;
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1;
    color: #2a3547;
  }
  .rpt-kpi-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #9eaac7;
    text-transform: uppercase;
    letter-spacing: 0.7px;
  }

  /* Filter Controls (Clean Solid Style) */
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

  /* Clear Button */
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
    height: 100%;
  }
  .btn-clear-filter:hover {
    background: #e4ebf7;
    color: #2a3547;
    border-color: #ccd7ea;
  }
  .btn-clear-filter iconify-icon {
    font-size: 1.05rem;
    transition: transform 0.3s ease;
  }
  .btn-clear-filter:hover iconify-icon {
    transform: rotate(-180deg);
  }

  /* Tab Bar Segmented Control */
  .rpt-tabs-header {
    padding: 16px 22px;
    border-bottom: 1px solid #f0f4fb;
    background: #fafbff;
  }
  .rpt-tabs-bar {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #eef2f7;
    border-radius: 12px;
    padding: 4px;
    border: 1px solid #e2e8f0;
  }
  .rpt-tab-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 7px 16px;
    border-radius: 8px;
    border: none;
    background: transparent;
    color: #64748b;
    font-weight: 600;
    font-size: 0.84rem;
    cursor: pointer;
    transition: all 0.2s ease;
    user-select: none;
  }
  .rpt-tab-btn iconify-icon {
    font-size: 1.15rem;
    color: #64748b;
    transition: color 0.2s ease;
  }
  .rpt-tab-btn:hover:not(.active) {
    color: #1e293b;
    background: rgba(255, 255, 255, 0.7);
  }
  .rpt-tab-btn:hover:not(.active) iconify-icon {
    color: #5d87ff;
  }
  .rpt-tab-btn.active {
    background: #ffffff;
    color: #5d87ff;
    font-weight: 700;
    box-shadow: 0 2px 8px rgba(93, 135, 255, 0.14), 0 1px 2px rgba(0, 0, 0, 0.04);
  }
  .rpt-tab-btn.active iconify-icon {
    color: #5d87ff;
  }

  /* DataTable overrides — Aurora palette */
  .dataTables_wrapper .dataTables_paginate .paginate_button.current,
  .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: linear-gradient(135deg, #5d87ff, #3eb7ff) !important;
    border-color: #5d87ff !important;
    color: #fff !important;
    border-radius: 8px !important;
    box-shadow: 0 4px 12px rgba(93, 135, 255, 0.3) !important;
  }
  .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: rgba(93, 135, 255, 0.08) !important;
    border-color: transparent !important;
    color: #5d87ff !important;
    border-radius: 8px !important;
  }
  .dataTables_wrapper .dataTables_filter input {
    border-radius: 50px !important;
    padding: 0.4rem 1.2rem !important;
    border: 1px solid #dfe5ef !important;
    background: #f8faff !important;
    font-size: 0.82rem !important;
    transition: all 0.2s !important;
  }
  .dataTables_wrapper .dataTables_filter input:focus {
    border-color: #5d87ff !important;
    box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.1) !important;
    outline: none !important;
  }
  .dataTables_wrapper .dataTables_length select,
  select.form-select-sm,
  .dataTables_length select {
    border-radius: 8px !important;
    border: 1px solid #dfe5ef !important;
    font-size: 0.82rem !important;
    padding: 0.3rem 2rem 0.3rem 0.8rem !important;
    color: #2a3547 !important;
    background-color: #ffffff !important;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%232a3547' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e") !important;
    background-repeat: no-repeat !important;
    background-position: right 0.6rem center !important;
    background-size: 14px !important;
    font-weight: 600 !important;
    -webkit-appearance: none !important;
    appearance: none !important;
    min-width: 70px;
  }
  .dataTables_wrapper .dataTables_length select option,
  .dataTables_length select option {
    color: #2a3547 !important;
    background: #ffffff !important;
  }
  .dataTables_wrapper .dataTables_info {
    font-size: 0.78rem;
    color: #9eaac7;
  }

  /* Report table */
  .rpt-table {
    width: 100% !important;
    border-collapse: separate;
    border-spacing: 0;
  }
  .rpt-table thead th {
    font-size: 0.68rem;
    font-weight: 700;
    color: #9eaac7;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    padding: 12px 10px;
    border-bottom: 2px solid #f0f4fb;
    white-space: nowrap;
    background: #fafbff;
  }
  .rpt-table tbody td {
    padding: 11px 10px;
    border-bottom: 1px solid #f8f9fc;
    font-size: 0.82rem;
    vertical-align: middle;
  }
  .rpt-table tbody tr:last-child td {
    border-bottom: none;
  }
  .rpt-table tbody tr:hover td {
    background: #fafbff;
  }

  /* Specific column adjustments for salesTable to eliminate horizontal scroll */
  #salesTable {
    table-layout: fixed;
    width: 100% !important;
  }
  #salesTable th:nth-child(1) { width: 85px; }   /* Invoice */
  #salesTable th:nth-child(2) { width: 125px; }  /* Cashier */
  #salesTable th:nth-child(3) { width: auto; }   /* Items Purchased (flexible) */
  #salesTable th:nth-child(4) { width: 85px; }   /* Gross */
  #salesTable th:nth-child(5) { width: 85px; }   /* Discount */
  #salesTable th:nth-child(6) { width: 95px; }   /* Net Pay */
  #salesTable th:nth-child(7) { width: 80px; }   /* Method */
  #salesTable th:nth-child(8) { width: 90px; }   /* Status */
  #salesTable th:nth-child(9) { width: 110px; }  /* Timestamp */

  /* Remove horizontal scrollbar on sales pane for desktop */
  #sales-pane .table-responsive {
    overflow-x: hidden;
  }
  @media (max-width: 991px) {
    #sales-pane .table-responsive {
      overflow-x: auto;
    }
  }

  .rpt-id-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 8px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 0.74rem;
    background: #f0f4ff;
    color: #5d87ff;
  }
  .rpt-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 3px 10px;
    border-radius: 50px;
    font-size: 0.73rem;
    font-weight: 700;
    white-space: nowrap;
  }
  .rpt-pill::before {
    content: '';
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: currentColor;
    display: inline-block;
  }
  .rpt-pill.success {
    background: #e6fbf7;
    color: #0fb68c;
  }
  .rpt-pill.warning {
    background: #fff8e6;
    color: #c98800;
  }
  .rpt-pill.danger {
    background: #fef2f2;
    color: #e53e3e;
  }
  .rpt-pill.inflow {
    background: rgba(19, 222, 185, 0.1);
    color: #0fb68c;
  }
  .rpt-pill.outflow {
    background: rgba(250, 84, 84, 0.1);
    color: #fa5454;
  }
  .rpt-pay-badge {
    background: #f0f4ff;
    color: #5d87ff;
    border-radius: 8px;
    padding: 3px 8px;
    font-size: 0.74rem;
    font-weight: 600;
    white-space: nowrap;
  }
</style>

<div class="rpt-wrapper pb-3">

  <!-- ============ CLEAN PAGE HEADER (No Glassmorphism) ============ -->
  <div class="rpt-page-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
    <div>
      <div class="rpt-badge-pill">
        <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
        Analytics &amp; Reporting
      </div>
      <h2 class="rpt-page-title">Performance Analytics</h2>
      <p class="rpt-page-subtitle">Real-time sales insights, inventory governance, and operational audit trails.</p>
    </div>

    <!-- Quick Actions -->
    <div>
      <button onclick="printInsights()" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" title="Print Current Report">
        <iconify-icon icon="solar:printer-bold-duotone" class="fs-5"></iconify-icon>
        <span>Print Report</span>
      </button>
    </div>
  </div>

  <!-- ============ ROW 1: KPI STATS CARDS ============ -->
  <div class="row g-4 mb-4" id="stats-container">

    <div class="col-md-4">
      <div class="rpt-kpi-card">
        <div class="rpt-kpi-glow" style="background:linear-gradient(90deg,#5d87ff,#13deb9);"></div>
        <div class="p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="rpt-kpi-icon" style="background:rgba(93,135,255,0.1);">
              <iconify-icon icon="solar:wallet-money-bold-duotone" style="color:#5d87ff;font-size:1.55rem;"></iconify-icon>
            </div>
            <span style="background:#e8fdf5;color:#0fb68c;border-radius:50px;padding:3px 12px;font-size:0.72rem;font-weight:700;">Live</span>
          </div>
          <div class="rpt-kpi-label mb-1">Total Revenue</div>
          <div class="rpt-kpi-value" id="stat-revenue">₱0.00</div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="rpt-kpi-card">
        <div class="rpt-kpi-glow" style="background:linear-gradient(90deg,#fa5454,#ffae1f);"></div>
        <div class="p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="rpt-kpi-icon" style="background:rgba(250,84,84,0.1);">
              <iconify-icon icon="solar:tag-bold-duotone" style="color:#fa5454;font-size:1.55rem;"></iconify-icon>
            </div>
            <span style="background:#fef2f2;color:#e53e3e;border-radius:50px;padding:3px 12px;font-size:0.72rem;font-weight:700;">Deductions</span>
          </div>
          <div class="rpt-kpi-label mb-1">Discounts Given</div>
          <div class="rpt-kpi-value" id="stat-discounts">₱0.00</div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="rpt-kpi-card">
        <div class="rpt-kpi-glow" style="background:linear-gradient(90deg,#13deb9,#5d87ff);"></div>
        <div class="p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="rpt-kpi-icon" style="background:rgba(19,222,185,0.1);">
              <iconify-icon icon="solar:bill-list-bold-duotone" style="color:#13deb9;font-size:1.55rem;"></iconify-icon>
            </div>
            <span style="background:#e6fbf7;color:#0fb68c;border-radius:50px;padding:3px 12px;font-size:0.72rem;font-weight:700;">Validated</span>
          </div>
          <div class="rpt-kpi-label mb-1">Transactions</div>
          <div class="rpt-kpi-value" id="stat-count">0</div>
        </div>
      </div>
    </div>

  </div>

  <!-- ============ ROW 2: FILTERS (BELOW STATS CARD) ============ -->
  <div class="filter-card p-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3">
      <div class="d-flex align-items-center gap-2">
        <iconify-icon icon="solar:filter-bold-duotone" class="text-primary fs-5"></iconify-icon>
        <h6 class="fw-bold text-dark mb-0" style="font-size:0.95rem;">Filter Analytics</h6>
      </div>
      <span class="text-muted small">Live updates instantly on change</span>
    </div>

    <div class="row g-3 align-items-end">

      <?php if ($is_admin && !empty($branches)): ?>
      <!-- Branch Filter -->
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="filter-label">
          <iconify-icon icon="solar:shop-2-line-duotone"></iconify-icon>
          <span>Branch</span>
        </label>
        <select id="branchFilter" class="form-select form-select-solid">
          <option value="all">All Branches</option>
          <?php foreach ($branches as $b): ?>
            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php else: ?>
        <input type="hidden" id="branchFilter" value="all">
      <?php endif; ?>

      <!-- Period Filter -->
      <div class="col-12 col-sm-6 col-lg-3">
        <label class="filter-label">
          <iconify-icon icon="solar:calendar-line-duotone"></iconify-icon>
          <span>Period</span>
        </label>
        <select id="datePreset" class="form-select form-select-solid">
          <option value="today" selected>Today</option>
          <option value="yesterday">Yesterday</option>
          <option value="last7">Last 7 Days</option>
          <option value="last30">Last 30 Days</option>
          <option value="this_month">This Month</option>
          <option value="custom">Custom Range…</option>
        </select>
      </div>

      <!-- Start Date -->
      <div class="col-12 col-sm-6 col-lg-2" id="startDateCol">
        <label class="filter-label">
          <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
          <span>Date From</span>
        </label>
        <input type="date" id="startDate" class="form-control form-control-solid">
      </div>

      <!-- End Date -->
      <div class="col-12 col-sm-6 col-lg-2" id="endDateCol">
        <label class="filter-label">
          <iconify-icon icon="solar:calendar-mark-bold-duotone"></iconify-icon>
          <span>Date To</span>
        </label>
        <input type="date" id="endDate" class="form-control form-control-solid">
      </div>

      <!-- Reset Filter Button -->
      <div class="col-12 col-sm-6 col-lg-2 d-flex">
        <button type="button" id="btn-reset-filters" class="btn-clear-filter w-100" title="Reset all filters">
          <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
          <span>Reset Filters</span>
        </button>
      </div>

    </div>
  </div>

  <!-- ============ ROW 3: ANALYTICS LEDGER & TABLES ============ -->
  <div class="aurora-card mb-4">
    <div class="rpt-tabs-header d-flex align-items-center justify-content-between flex-wrap gap-3">
      <div class="rpt-tabs-bar" id="reportTabs" role="tablist">
        <button class="rpt-tab-btn active" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-pane"
          type="button" role="tab" aria-controls="sales-pane" aria-selected="true">
          <iconify-icon icon="solar:pie-chart-bold-duotone"></iconify-icon>
          <span>Sales Distribution</span>
        </button>
        <button class="rpt-tab-btn" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane"
          type="button" role="tab" aria-controls="logs-pane" aria-selected="false">
          <iconify-icon icon="solar:clipboard-list-bold-duotone"></iconify-icon>
          <span>Inventory Logs</span>
        </button>
      </div>
      <div class="d-flex align-items-center gap-2 text-muted small">
        <span class="d-inline-block rounded-circle bg-success" style="width:7px;height:7px;"></span>
        <span>Real-time synced across terminal</span>
      </div>
    </div>

    <div class="tab-content p-4">

      <!-- Tab: Sales -->
      <div class="tab-pane fade show active" id="sales-pane" role="tabpanel">
        <div class="table-responsive">
          <table id="salesTable" class="rpt-table">
            <thead>
              <tr>
                <th>Invoice</th>
                <th>Cashier</th>
                <th>Items Purchased</th>
                <th class="text-end">Gross</th>
                <th class="text-end">Discount</th>
                <th class="text-end">Net Pay</th>
                <th>Method</th>
                <th>Status</th>
                <th class="text-end">Timestamp</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

      <!-- Tab: Inventory Logs -->
      <div class="tab-pane fade" id="logs-pane" role="tabpanel">
        <div class="table-responsive">
          <table id="logsTable" class="rpt-table">
            <thead>
              <tr>
                <th>Log ID</th>
                <th>Product</th>
                <th>Performed By</th>
                <th>Action</th>
                <th class="text-center">Quantity</th>
                <th>Remarks</th>
                <th class="text-end">Timestamp</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>

    </div>
  </div>

</div>

<script>
  $(document).ready(function () {

    // Tab switch — sync active class & adjust columns
    $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
      document.querySelectorAll('.rpt-tab-btn').forEach(function (b) { b.classList.remove('active'); });
      e.target.classList.add('active');
      $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
    });

    // Print Handler
    window.printInsights = function () {
      var branchId = $('#branchFilter').val() || 'all';
      var start = $('#startDate').val();
      var end = $('#endDate').val();
      var activeTab = document.querySelector('.rpt-tab-btn.active');
      var type = (activeTab && activeTab.id === 'logs-tab') ? 'inventory' : 'sales';
      var url = '?route=admin/reports/print&type=' + type + '&branch_id=' + branchId + '&start_date=' + start + '&end_date=' + end;
      window.open(url, '_blank');
    };

    // Stats fetch
    function fetchStats() {
      var branchId = $('#branchFilter').val() || 'all';
      var start = $('#startDate').val();
      var end = $('#endDate').val();
      $.getJSON('ajax/report_action.php', { action: 'get_summary_stats', branch_id: branchId, start_date: start, end_date: end }, function (data) {
        $('#stat-revenue').text('₱' + parseFloat(data.revenue || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }));
        $('#stat-discounts').text('₱' + parseFloat(data.discounts || 0).toLocaleString(undefined, { minimumFractionDigits: 2 }));
        $('#stat-count').text(parseInt(data.count || 0).toLocaleString());
      });
    }

    // DataTable: Sales
    var salesTable = $('#salesTable').DataTable({
      processing: true,
      serverSide: true,
      autoWidth: false,
      ajax: {
        url: 'ajax/report_action.php?action=get_dt_sales',
        type: 'POST',
        data: function (d) {
          d.branch_id = $('#branchFilter').val() || 'all';
          d.start_date = $('#startDate').val();
          d.end_date = $('#endDate').val();
        }
      },
      columns: [
        {
          data: 'id',
          render: function (d) {
            return '<span class="rpt-id-badge">#' + d.toString().padStart(6, '0') + '</span>';
          }
        },
        {
          data: 'cashier_name',
          render: function (d) {
            return '<span class="text-truncate d-inline-block fw-semibold text-dark" style="max-width:115px;" title="' + $('<div>').text(d).html() + '">' + d + '</span>';
          }
        },
        {
          data: 'products',
          render: function (d) {
            return d ? '<span class="text-truncate d-inline-block text-muted" style="max-width:100%;font-size:0.77rem;" title="' + $('<div>').text(d).html() + '">' + d + '</span>' : '<span class="text-muted fst-italic">—</span>';
          }
        },
        {
          data: 'total_amount',
          className: 'text-end',
          render: function (d) {
            return '₱' + parseFloat(d).toLocaleString(undefined, { minimumFractionDigits: 2 });
          }
        },
        {
          data: 'discount_amount',
          className: 'text-end',
          render: function (d) {
            return '<span style="color:#fa5454;font-weight:600;">₱' + parseFloat(d).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</span>';
          }
        },
        {
          data: 'final_amount',
          className: 'text-end',
          render: function (d) {
            return '<strong style="color:#5d87ff;font-size:0.88rem;">₱' + parseFloat(d).toLocaleString(undefined, { minimumFractionDigits: 2 }) + '</strong>';
          }
        },
        {
          data: 'payment_method',
          render: function (d) {
            return '<span class="rpt-pay-badge">' + d + '</span>';
          }
        },
        {
          data: 'status',
          render: function (d) {
            var cls = d === 'Refunded' ? 'warning' : (d === 'Cancelled' ? 'danger' : 'success');
            return '<span class="rpt-pill ' + cls + '">' + d + '</span>';
          }
        },
        {
          data: 'created_at',
          className: 'text-end',
          render: function (d) {
            var dt = new Date(d);
            var dateStr = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var timeStr = dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            return '<div style="font-size:0.75rem;line-height:1.25;"><span class="text-dark fw-semibold">' + dateStr + '</span><br><span class="text-muted" style="font-size:0.68rem;">' + timeStr + '</span></div>';
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      language: {
        search: "_INPUT_", searchPlaceholder: "Search sales…",
        paginate: { previous: '<iconify-icon icon="solar:alt-arrow-left-bold"></iconify-icon>', next: '<iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>' }
      },
      drawCallback: function () { $('.dataTables_paginate').addClass('mt-3 d-flex justify-content-end align-items-center gap-1'); }
    });

    // DataTable: Inventory Logs
    var logsTable = $('#logsTable').DataTable({
      processing: true,
      serverSide: true,
      autoWidth: false,
      ajax: {
        url: 'ajax/report_action.php?action=get_dt_inventory_logs',
        type: 'POST',
        data: function (d) {
          d.branch_id = $('#branchFilter').val() || 'all';
          d.start_date = $('#startDate').val();
          d.end_date = $('#endDate').val();
        }
      },
      columns: [
        { data: 'id', render: function (d) { return '<span class="rpt-id-badge">ID-' + d + '</span>'; } },
        { data: 'product_name', render: function (d) { return '<span style="font-weight:700;color:#2a3547;">' + d + '</span>'; } },
        { data: 'user_name', className: 'text-muted' },
        {
          data: 'action', render: function (d) {
            var isIn = (d === 'stock_in' || d === 'initial');
            return isIn
              ? '<span class="rpt-pill inflow"><iconify-icon icon="solar:arrow-up-bold"></iconify-icon> Inflow</span>'
              : '<span class="rpt-pill outflow"><iconify-icon icon="solar:arrow-down-bold"></iconify-icon> Outflow</span>';
          }
        },
        {
          data: null, className: 'text-center', render: function (d) {
            var isIn = (d.action === 'stock_in' || d.action === 'initial');
            return '<strong style="color:' + (isIn ? '#13deb9' : '#fa5454') + '">' + (isIn ? '+' : '-') + d.quantity + '</strong>';
          }
        },
        { data: 'remarks', className: 'text-muted', render: function (d) { return d ? '<span style="font-size:0.78rem;">' + d + '</span>' : '<span class="text-muted fst-italic">—</span>'; } },
        {
          data: 'created_at', className: 'text-end', render: function (d) {
            var dt = new Date(d);
            var dateStr = dt.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            var timeStr = dt.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            return '<div style="font-size:0.75rem;line-height:1.25;"><span class="text-dark fw-semibold">' + dateStr + '</span><br><span class="text-muted" style="font-size:0.68rem;">' + timeStr + '</span></div>';
          }
        }
      ],
      order: [[0, 'desc']],
      pageLength: 10,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      language: {
        search: "_INPUT_", searchPlaceholder: "Filter logs…",
        paginate: { previous: '<iconify-icon icon="solar:alt-arrow-left-bold"></iconify-icon>', next: '<iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>' }
      }
    });

    // Preset handler
    function applyDatePreset(val) {
      var today = new Date();
      var fmt = function (d) { return d.toISOString().split('T')[0]; };
      var start = '', end = '';

      if (val === 'today') { start = end = fmt(today); }
      else if (val === 'yesterday') { var y = new Date(today); y.setDate(y.getDate() - 1); start = end = fmt(y); }
      else if (val === 'last7') { var l7 = new Date(today); l7.setDate(l7.getDate() - 6); start = fmt(l7); end = fmt(today); }
      else if (val === 'last30') { var l30 = new Date(today); l30.setDate(l30.getDate() - 29); start = fmt(l30); end = fmt(today); }
      else if (val === 'this_month') { start = fmt(new Date(today.getFullYear(), today.getMonth(), 1)); end = fmt(today); }
      else if (val === 'custom') {
        return;
      }

      $('#startDate').val(start);
      $('#endDate').val(end);
      reloadTables();
    }

    function reloadTables() {
      fetchStats();
      if (typeof salesTable !== 'undefined') salesTable.ajax.reload();
      if (typeof logsTable !== 'undefined') logsTable.ajax.reload();
    }

    // Filter change listeners
    $('#branchFilter').on('change', function () { reloadTables(); });
    $('#datePreset').on('change', function () { applyDatePreset(this.value); });
    $('#startDate, #endDate').on('change', function () {
      $('#datePreset').val('custom');
      reloadTables();
    });

    // Reset button
    $('#btn-reset-filters').on('click', function () {
      $('#branchFilter').val('all');
      $('#datePreset').val('today');
      applyDatePreset('today');
    });

    // Init with Today
    applyDatePreset('today');
  });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>