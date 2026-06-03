<?php
$title = "Advanced Analytics & System Audits";

// Ensure required variables are defined
if (!isset($is_admin)) {
    $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
}
if (!isset($branches)) {
    require_once __DIR__ . '/../../models/Branch.php';
    $branches = $is_admin ? (Branch::get_all_active() ?: []) : [];
}

ob_start();
?>

<style>
    .custom-tooltip-trigger {
        position: relative;
    }
    .custom-tooltip {
        position: absolute;
        bottom: 125%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        background: #1e1b4b;
        color: white;
        padding: 8px 14px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        white-space: nowrap;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        pointer-events: none;
        z-index: 1000;
    }
    .custom-tooltip::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        border-width: 6px;
        border-style: solid;
        border-color: #1e1b4b transparent transparent transparent;
    }
    .custom-tooltip-trigger:hover .custom-tooltip {
        opacity: 1;
        visibility: visible;
        transform: translateX(-50%) translateY(0);
    }
    .fs-xs { font-size: 0.75rem; }
    .cursor-pointer { cursor: pointer; }
    .hover-scale:hover { transform: scale(1.05); }
    input[type="date"]::-webkit-calendar-picker-indicator {
        filter: invert(1);
        cursor: pointer;
    }
    .animate-in {
        animation: animate-in 0.3s ease-out;
    }
    @keyframes animate-in {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div class="reports-header border-0 rounded-4 p-5 mb-5 shadow-sm" style="background: #4f46e5; background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div class="d-inline-block px-3 py-1 rounded-pill bg-white bg-opacity-20 text-dark small fw-bold mb-3 shadow-sm">System Report</div>
            <h2 class="h2 fw-extrabold text-white mb-2" style="letter-spacing: -0.02em;">Operational Analytics</h2>
            <p class="text-white text-opacity-70 lead mb-0 fs-6">High-fidelity data orchestration and administrative audit logs.</p>
        </div>

        <div class="d-flex flex-column align-items-end gap-2">
            <!-- Consolidated Action Control Bar -->
            <div class="p-2 rounded-4 bg-white bg-opacity-10 border border-white border-opacity-20 shadow-lg d-flex align-items-center gap-1" style="backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);">
                
                <!-- Data Scope Filter -->
                <?php if ($is_admin): ?>
                <div class="d-flex align-items-center px-3 py-1 border-end border-white border-opacity-10">
                    <div class="me-3">
                        <label class="d-block text-white text-opacity-50 fs-xs fw-bold text-uppercase tracking-widest mb-0" style="font-size: 9px;">Data Scope</label>
                        <select id="branchFilter" class="form-select bg-transparent text-white border-0 p-0 fw-bold shadow-none cursor-pointer" style="font-size: 14px; min-width: 150px;">
                            <option value="all" class="text-dark">Global Enterprise</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id'] ?>" class="text-dark"><?= htmlspecialchars($branch['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <span class="material-symbols-outlined text-white text-opacity-25 fs-5">database</span>
                </div>
                <?php endif; ?>

                <!-- Temporal Insight Filter -->
                <div class="d-flex align-items-center px-3 py-1 border-end border-white border-opacity-10">
                    <div class="me-3">
                        <label class="d-block text-white text-opacity-50 fs-xs fw-bold text-uppercase tracking-widest mb-0" style="font-size: 9px;">Temporal Insight</label>
                        <select id="datePreset" class="form-select bg-transparent text-white border-0 p-0 fw-bold shadow-none cursor-pointer" style="font-size: 14px; min-width: 140px;">
                            <option value="today" class="text-dark">Today's Focus</option>
                            <option value="yesterday" class="text-dark">Yesterday</option>
                            <option value="last7" class="text-dark">Trailing 7 Days</option>
                            <option value="last30" class="text-dark">Last 30 Days</option>
                            <option value="this_month" class="text-dark">Monthly Audit</option>
                            <option value="custom" class="text-dark">Custom Range</option>
                        </select>
                    </div>
                    <span class="material-symbols-outlined text-white text-opacity-25 fs-5">calendar_month</span>
                </div>

                <!-- Print Action -->
                <div class="ps-2 pe-1">
                    <button onclick="printInsights()" 
                            class="btn btn-white rounded-3 p-2 shadow-sm transition-all hover-scale d-flex align-items-center justify-content-center custom-tooltip-trigger" 
                            style="width: 44px; height: 44px; background: #fff;"
                            data-tooltip="Generate Print Report">
                        <span class="material-symbols-outlined text-primary fs-4" style="font-variation-settings: 'FILL' 1;">print</span>
                        <!-- Custom Tooltip Element -->
                        <div class="custom-tooltip">Print Report</div>
                    </button>
                </div>
            </div>

            <!-- Custom Date Range Picker (Conditional) -->
            <div id="customDateRange" class="d-none animate-in">
                <div class="p-2 rounded-4 bg-dark bg-opacity-25 border border-white border-opacity-10 shadow-lg d-flex align-items-center gap-3 px-4" style="backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-white text-opacity-50 fs-6">start</span>
                        <input type="date" id="startDate" class="bg-transparent text-white border-0 fw-bold shadow-none cursor-pointer p-1" style="font-size: 13px; outline: none; color-scheme: dark;">
                    </div>
                    <span class="text-white text-opacity-30 fw-bold">/</span>
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined text-white text-opacity-50 fs-6">event_upcoming</span>
                        <input type="date" id="endDate" class="bg-transparent text-white border-0 fw-bold shadow-none cursor-pointer p-1" style="font-size: 13px; outline: none; color-scheme: dark;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Dynamic Summary Metrics -->
<div class="row gy-4 mb-5" id="stats-container">
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden bg-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-3 bg-primary-subtle rounded-4 text-primary">
                        <span class="material-symbols-outlined display-6">payments</span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1">Live Feed</span>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase tracking-widest fw-bold small mb-1">Total Gross Revenue</h6>
                <h2 class="fw-extrabold outfit-font mb-0 text-dark" id="stat-revenue">₱0.00</h2>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden bg-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-3 bg-danger-subtle rounded-4 text-danger">
                        <span class="material-symbols-outlined display-6">sell</span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1">-8.4% Est.</span>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase tracking-widest fw-bold small mb-1">Marketing Discounts</h6>
                <h2 class="fw-extrabold outfit-font mb-0 text-dark" id="stat-discounts">₱0.00</h2>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden bg-white">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="p-3 bg-success-subtle rounded-4 text-success">
                        <span class="material-symbols-outlined display-6">receipt_long</span>
                    </div>
                    <div class="text-end">
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">Batch Validated</span>
                    </div>
                </div>
                <h6 class="text-muted text-uppercase tracking-widest fw-bold small mb-1">Validated Transactions</h6>
                <h2 class="fw-extrabold outfit-font mb-0 text-dark" id="stat-count">0</h2>
            </div>
        </div>
    </div>
</div>

<!-- Navigation Interface -->
<div class="card border-0 rounded-4 shadow-sm mb-5 bg-white overflow-hidden">
    <div class="card-header bg-white border-0 p-4 pb-0">
        <ul class="nav nav-tabs border-0 gap-4" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active border-0 border-bottom border-3 border-transparent rounded-0 px-0 py-3 fw-bold outfit-font text-muted transition-all hov-primary" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales-pane" type="button" role="tab">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">point_of_sale</span>
                        Sales Distribution
                    </div>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link border-0 border-bottom border-3 border-transparent rounded-0 px-0 py-3 fw-bold outfit-font text-muted transition-all hov-primary" id="logs-tab" data-bs-toggle="tab" data-bs-target="#logs-pane" type="button" role="tab">
                    <div class="d-flex align-items-center gap-2">
                        <span class="material-symbols-outlined">inventory_2</span>
                        Inventory Governance
                    </div>
                </button>
            </li>
        </ul>
    </div>
    <div class="card-body p-4">
        <div class="tab-content" id="reportTabsContent">
            <!-- Sales Pane -->
            <div class="tab-pane fade show active" id="sales-pane" role="tabpanel">
                <div class="table-responsive">
                    <table id="salesTable" class="table table-hover align-middle mb-0 w-100 border-0">
                        <thead class="bg-light text-uppercase fs-xs tracking-tighter text-muted fw-bold">
                            <tr>
                                <th class="py-3 px-4 rounded-start-3">ID</th>
                                <th class="py-3">Staff Operator</th>
                                <th class="py-3 text-end">Gross</th>
                                <th class="py-3 text-end">Discount</th>
                                <th class="py-3 text-end">Net Final</th>
                                <th class="py-3">Modality</th>
                                <th class="py-3">Lifecycle</th>
                                <th class="py-3 px-4 rounded-end-3 text-end">Timestamp</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <!-- DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Inventory Logs Pane -->
            <div class="tab-pane fade" id="logs-pane" role="tabpanel">
                <div class="table-responsive">
                    <table id="logsTable" class="table table-hover align-middle mb-0 w-100 border-0">
                        <thead class="bg-light text-uppercase fs-xs tracking-tighter text-muted fw-bold">
                            <tr>
                                <th class="py-3 px-4 rounded-start-3">Entry</th>
                                <th class="py-3">Asset Identity</th>
                                <th class="py-3">Operator</th>
                                <th class="py-3">Mutation</th>
                                <th class="py-3 text-center">Qty</th>
                                <th class="py-3">Audit Remarks</th>
                                <th class="py-3 px-4 rounded-end-3 text-end">Execution Date</th>
                            </tr>
                        </thead>
                        <tbody class="border-top-0">
                            <!-- DataTables AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .hov-primary:hover,
    .hov-primary.active {
        color: var(--bs-primary) !important;
        border-bottom-color: var(--bs-primary) !important;
    }

    .fs-xs {
        font-size: 0.7rem;
    }

    .tracking-tighter {
        letter-spacing: 0.05rem;
    }

    .fw-extrabold {
        font-weight: 800;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: var(--bs-primary) !important;
        border-color: var(--bs-primary) !important;
        color: white !important;
        border-radius: 50px;
    }

    .dataTables_wrapper .dataTables_filter input {
        border-radius: 50px;
        padding: 0.5rem 1.5rem;
        border: 1px solid #eee;
        background: #fcfcfc;
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 50px;
        padding-left: 1rem;
        border: 1px solid #eee;
    }

    .table-hover tbody tr:hover {
        background-color: #f8f9fa !important;
        transform: scale(1.002);
        transition: all 0.2s ease;
    }
</style>

<script>
    $(document).ready(function() {
        // Print Handler
        window.printInsights = function() {
            const branchId = $('#branchFilter').val() || 'all';
            const start = $('#startDate').val();
            const end = $('#endDate').val();
            const activeTab = $('#reportTabs .nav-link.active').attr('id');
            const type = activeTab === 'sales-tab' ? 'sales' : 'inventory';
            
            const url = `?route=admin/reports/print&type=${type}&branch_id=${branchId}&start_date=${start}&end_date=${end}`;
            window.open(url, '_blank');
        };

        // Initialize Statistics
        fetchStats();

        // DataTables: Sales Distribution
        const salesTable = $('#salesTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/report_action.php?action=get_dt_sales',
                type: 'POST',
                data: function(d) {
                    d.branch_id = $('#branchFilter').val() || 'all';
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                }
            },
            columns: [{
                    data: 'id',
                    render: function(data) {
                        return `<span class="fw-bold text-dark">#${data.toString().padStart(6, '0')}</span>`;
                    }
                },
                {
                    data: 'cashier_name'
                },
                {
                    data: 'total_amount',
                    className: 'text-end',
                    render: function(data) {
                        return `₱${parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2})}`;
                    }
                },
                {
                    data: 'discount_amount',
                    className: 'text-end',
                    render: function(data) {
                        return `<span class="text-danger">₱${parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>`;
                    }
                },
                {
                    data: 'final_amount',
                    className: 'text-end',
                    render: function(data) {
                        return `<span class="fw-extrabold text-primary">₱${parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2})}</span>`;
                    }
                },
                {
                    data: 'payment_method',
                    render: function(data) {
                        return `<span class="badge border bg-light text-dark px-3 py-1 rounded-pill">${data}</span>`;
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        let cls = 'success';
                        if (data === 'Refunded') cls = 'warning';
                        if (data === 'Cancelled') cls = 'danger';
                        return `<span class="badge bg-${cls}-subtle text-${cls} rounded-pill px-3 py-1 fw-bold">${data}</span>`;
                    }
                },
                {
                    data: 'created_at',
                    className: 'text-end small text-muted',
                    render: function(data) {
                        return new Date(data).toLocaleString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    }
                }
            ],
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Query reports...",
                paginate: {
                    previous: '<span class="material-symbols-outlined">chevron_left</span>',
                    next: '<span class="material-symbols-outlined">chevron_right</span>'
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate').addClass('mt-3 d-flex justify-content-end');
            }
        });

        // DataTables: Inventory Governance
        const logsTable = $('#logsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: 'ajax/report_action.php?action=get_dt_inventory_logs',
                type: 'POST',
                data: function(d) {
                    d.branch_id = $('#branchFilter').val() || 'all';
                    d.start_date = $('#startDate').val();
                    d.end_date = $('#endDate').val();
                }
            },
            columns: [{
                    data: 'id',
                    render: function(data) {
                        return `<span class="text-muted fw-bold">ID-${data}</span>`;
                    }
                },
                {
                    data: 'product_name',
                    className: 'fw-bold outfit-font text-dark'
                },
                {
                    data: 'user_name',
                    className: 'text-muted'
                },
                {
                    data: 'action',
                    render: function(data) {
                        if (data === 'stock_in' || data === 'initial') {
                            return `<span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i> Inflow</span>`;
                        }
                        return `<span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold"><i class="fa-solid fa-arrow-trend-down me-1"></i> Outflow</span>`;
                    }
                },
                {
                    data: null,
                    className: 'text-center fw-bold',
                    render: function(data) {
                        let symbol = (data.action === 'stock_in' || data.action === 'initial') ? '+' : '-';
                        let cls = (data.action === 'stock_in' || data.action === 'initial') ? 'text-success' : 'text-danger';
                        return `<span class="${cls}">${symbol}${data.quantity}</span>`;
                    }
                },
                {
                    data: 'remarks',
                    className: 'text-muted small'
                },
                {
                    data: 'created_at',
                    className: 'text-end small text-muted',
                    render: function(data) {
                        return new Date(data).toLocaleString('en-US', {
                            month: 'short',
                            day: 'numeric',
                            year: 'numeric',
                            hour: 'numeric',
                            minute: '2-digit',
                            hour12: true
                        });
                    }
                }
            ],
            order: [
                [0, 'desc']
            ],
            pageLength: 10,
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Filter assets...",
                paginate: {
                    previous: '<span class="material-symbols-outlined">chevron_left</span>',
                    next: '<span class="material-symbols-outlined">chevron_right</span>'
                }
            }
        });

        // Filter handling
        $('#branchFilter, #startDate, #endDate').on('change', function() {
            fetchStats();
            salesTable.ajax.reload();
            logsTable.ajax.reload();
        });

        $('#datePreset').on('change', function() {
            const val = $(this).val();
            const startInput = $('#startDate');
            const endInput = $('#endDate');
            const customDiv = $('#customDateRange');

            let start = '',
                end = '';
            const today = new Date();
            const formatDate = (date) => date.toISOString().split('T')[0];

            if (val === 'custom') {
                customDiv.removeClass('d-none');
                return;
            } else {
                customDiv.addClass('d-none');
            }

            switch (val) {
                case 'today':
                    start = end = formatDate(today);
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    start = end = formatDate(yesterday);
                    break;
                case 'last7':
                    const l7 = new Date(today);
                    l7.setDate(l7.getDate() - 6);
                    start = formatDate(l7);
                    end = formatDate(today);
                    break;
                case 'last30':
                    const l30 = new Date(today);
                    l30.setDate(l30.getDate() - 29);
                    start = formatDate(l30);
                    end = formatDate(today);
                    break;
                case 'this_month':
                    start = formatDate(new Date(today.getFullYear(), today.getMonth(), 1));
                    end = formatDate(today);
                    break;
            }

            startInput.val(start);
            endInput.val(end);
            startInput.trigger('change');
        });

        // Initialize with 'Today'
        $('#datePreset').val('today').trigger('change');

        // Refresh logs on tab switch to ensure correct scaling
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            $.fn.dataTable.tables({
                visible: true,
                api: true
            }).columns.adjust();
        });

        function fetchStats() {
            const branchId = $('#branchFilter').val() || 'all';
            const start = $('#startDate').val();
            const end = $('#endDate').val();
            $.getJSON('ajax/report_action.php', {
                action: 'get_summary_stats',
                branch_id: branchId,
                start_date: start,
                end_date: end
            }, function(data) {
                $('#stat-revenue').text('₱' + parseFloat(data.revenue || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#stat-discounts').text('₱' + parseFloat(data.discounts || 0).toLocaleString(undefined, {
                    minimumFractionDigits: 2
                }));
                $('#stat-count').text(parseInt(data.count || 0).toLocaleString());
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>