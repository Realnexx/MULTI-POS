<?php
$title = "System Activity Logs";

// Defensive initialization for server-side stability
$total_logs = $total_logs ?? 0;
$users = $users ?? [];

ob_start();
?>
<section id="activity-logs-page">

<!-- Tailwind CSS CDN (matching POS history) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        important: '#activity-logs-page',
        corePlugins: {
            preflight: false,
        },
        theme: {
            extend: {
                colors: {
                    primary: '#b60e3d',
                    secondary: '#8429c8',
                    error: '#ba1a1a',
                }
            }
        }
    }
</script>

<style>
    .stats-card {
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        backdrop-filter: blur(8px);
        background: rgba(255, 255, 255, 0.8) !important;
        border: 1px solid rgba(255, 255, 255, 0.3) !important;
    }
    .stats-card:hover {
        transform: translateY(-8px) scale(1.01);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.06) !important;
        background: rgba(255, 255, 255, 0.95) !important;
    }
    .card-filter {
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.03) !important;
        background: rgba(255, 255, 255, 0.7) !important;
        backdrop-filter: blur(10px);
    }
    .card-filter:focus-within {
        border-color: rgba(132, 41, 200, 0.2) !important;
        box-shadow: 0 10px 30px rgba(132, 41, 200, 0.04) !important;
    }
    .smaller { font-size: 0.75rem; }
    .btn-outline-primary {
        border-color: #b60e3d !important;
        color: #b60e3d !important;
    }
    .btn-outline-primary:hover {
        background-color: #b60e3d !important;
        color: #fff !important;
    }
    .text-primary { color: #b60e3d !important; }
    .text-secondary { color: #8429c8 !important; }
    
    .dataTables_wrapper .dataTables_info {
        padding-top: 1.5rem !important;
        font-size: 13px !important;
        color: #666 !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1.5rem !important;
    }

    /* Base Pagination Overrides (Targeting Bootstrap integrated DataTables) */
    #activity-table_wrapper .pagination {
        gap: 8px !important;
        margin-top: 1.5rem !important;
    }

    #activity-table_wrapper .page-item {
        margin: 0 !important;
        border: none !important;
    }

    #activity-table_wrapper .page-link {
        border-radius: 12px !important;
        border: 1px solid #e5e7eb !important;
        background: #ffffff !important;
        color: #374151 !important;
        padding: 8px 16px !important;
        font-weight: 600 !important;
        font-size: 14px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05) !important;
    }

    /* Page numbers optimization */
    #activity-table_wrapper .page-item:not(.next):not(.previous) .page-link {
        min-width: 42px !important;
        text-align: center !important;
        padding: 8px 8px !important;
    }

    /* Active State (The Red Look) */
    #activity-table_wrapper .page-item.active .page-link {
        background-color: #b60e3d !important;
        border-color: #b60e3d !important;
        color: #ffffff !important;
        box-shadow: 0 4px 12px rgba(182, 14, 61, 0.25) !important;
    }

    /* Hover State */
    #activity-table_wrapper .page-link:hover {
        background-color: #f9fafb !important;
        color: #111827 !important;
        border-color: #d1d5db !important;
        z-index: 2;
    }

    /* Disabled State */
    #activity-table_wrapper .page-item.disabled .page-link {
        opacity: 0.5 !important;
        color: #9ca3af !important;
        background-color: #ffffff !important;
        border-color: #f3f4f6 !important;
        box-shadow: none !important;
    }

    /* Remove focus shadow */
    .page-link:focus {
        box-shadow: none !important;
    }

    .dataTables_filter input {
        border-radius: 10px;
        border: 1px solid #eee;
        padding: 8px 12px;
    }
</style>

<div class="container-fluid px-4 py-4" style="background: radial-gradient(circle at top right, rgba(182, 14, 61, 0.03), transparent 300px), radial-gradient(circle at bottom left, rgba(132, 41, 200, 0.03), transparent 300px);">
    
    <!-- Header -->
    <div class="d-flex align-items-center justify-content-between mb-4" data-aos="fade-down" data-aos-duration="600">
        <div>
            <h4 class="fw-bold outfit-font text-dark mb-1">System Activity Logs</h4>
            <p class="text-muted small mb-0">Complete audit trail of administrative and user-level events.</p>
        </div>
        <div class="d-flex gap-2 d-print-none">
            <button type="button" onclick="printAudit()" class="btn btn-outline-primary rounded-pill px-4 fw-semibold d-flex align-items-center gap-2 transition-all hover:scale-105 active:scale-95 shadow-sm">
                <i class="fa-solid fa-print"></i> Print Audit
            </button>
        </div>
    </div>

    <!-- Row 1: Stats Summary Cards (Matching History Design) -->
    <div class="row g-3 mb-4 d-print-none">
        <!-- Card 1: Total Actions -->
        <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light stats-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Total Logs</p>
                        <h3 class="fw-bold outfit-font text-primary mb-0"><?= number_format($total_logs) ?></h3>
                    </div>
                    <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 shadow-sm">
                        <span class="material-symbols-outlined" style="font-size: 28px;">clinical_notes</span>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-bolt"></i> Lifetime recorded actions</p>
            </div>
        </div>

        <!-- Card 2: Active Admins -->
        <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light stats-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">System Users</p>
                        <h3 class="fw-bold outfit-font text-secondary mb-0"><?= count($users) ?></h3>
                    </div>
                    <div class="p-3 rounded-4 shadow-sm" style="color: #8429c8; background-color: rgba(132, 41, 200, 0.1);">
                        <span class="material-symbols-outlined" style="font-size: 28px;">shield_person</span>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-user-shield"></i> Authorized personnel</p>
            </div>
        </div>

        <!-- Card 3: Security Score (Mock/Placeholder for design) -->
        <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light stats-card h-100">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <p class="text-muted small fw-bold text-uppercase mb-1">Audit Coverage</p>
                        <h3 class="fw-bold outfit-font text-success mb-0">100%</h3>
                    </div>
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-4 shadow-sm">
                        <span class="material-symbols-outlined" style="font-size: 28px;">verified_user</span>
                    </div>
                </div>
                <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-check-double"></i> All modules tracked</p>
            </div>
        </div>
    </div>

    <!-- Row 2: Orchestration Bar (Filters) -->
    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light mb-4 card-filter d-print-none" data-aos="fade-in" data-aos-delay="400">
        <h6 class="fw-bold outfit-font text-secondary mb-3"><i class="fa-solid fa-sliders me-2"></i>Orchestration Bar</h6>
        <form id="filter-form" class="row g-3 align-items-end">
            <!-- User Filter -->
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted fw-semibold">Performed By</label>
                <select name="user_id" id="filter-user" class="form-select rounded-3 border-light-subtle shadow-none">
                    <option value="">All Users</option>
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Action Keywords -->
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted fw-semibold">Action Type</label>
                <input type="text" name="action" id="filter-action" placeholder="LOGIN, DELETE..." class="form-control rounded-3 border-light-subtle shadow-none"/>
            </div>

            <!-- Date From -->
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted fw-semibold">Date From</label>
                <input type="date" name="date_from" id="filter-date-from" class="form-control rounded-3 border-light-subtle shadow-none"/>
            </div>

            <!-- Date To -->
            <div class="col-12 col-md-2">
                <label class="form-label small text-muted fw-semibold">Date To</label>
                <input type="date" name="date_to" id="filter-date-to" class="form-control rounded-3 border-light-subtle shadow-none"/>
            </div>

            <!-- Actions -->
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="button" id="btn-apply-filter" class="btn btn-primary rounded-3 w-100 fw-semibold shadow-sm transition-all hover:scale-105">
                    Apply
                </button>
                <button type="button" id="btn-reset-filter" class="btn btn-outline-secondary rounded-3 w-100 fw-semibold d-flex align-items-center justify-content-center">
                    Clear
                </button>
            </div>
        </form>
    </div>

    <!-- Row 3: Audit Trail Table -->
    <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light mb-4" data-aos="fade-up" data-aos-delay="500">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h5 class="fw-bold outfit-font text-dark mb-1">Administrative Audit Trail</h5>
                <p class="text-muted small mb-0">Review system events, secure login records, and modification history.</p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center d-print-none">
                <!-- Real-time Search Box (Matching History) -->
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="custom-search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search logs, IPs, actions..." />
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle w-100" id="activity-table">
                <thead>
                    <tr class="text-muted small text-uppercase fw-bold">
                        <th class="py-3 px-4">User</th>
                        <th class="py-3">Action</th>
                        <th class="py-3">Description</th>
                        <th class="py-3">IP Address</th>
                        <th class="py-3 px-4">Timestamp</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <!-- DataTables will populate this -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Initialize DataTable with same UX/UI patterns as POS history
        const table = $('#activity-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= url('?route=admin/activity_logs/data') ?>',
                data: function(d) {
                    d.user_id = $('#filter-user').val();
                    d.action = $('#filter-action').val();
                    d.date_from = $('#filter-date-from').val();
                    d.date_to = $('#filter-date-to').val();
                }
            },
            pageLength: 10,
            ordering: true,
            order: [[4, 'desc']], 
            columns: [
                { orderable: true },
                { orderable: true },
                { orderable: true },
                { orderable: true },
                { orderable: true }
            ],
            language: {
                lengthMenu: "Show _MENU_ logs",
                info: "Showing _START_ to _END_ of _TOTAL_ logs",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            }
        });

        // Use custom search trigger for consistent design
        $('#custom-search').on('keyup', function() {
            table.search(this.value).draw();
        });

        // Apply filters
        $('#btn-apply-filter').on('click', function() {
            table.ajax.reload();
        });

        // Reset all filters
        $('#btn-reset-filter').on('click', function() {
            $('#filter-form')[0].reset();
            $('#custom-search').val('');
            table.search('').ajax.reload();
        });

        // Print Audit sync with filters
        function openPrintReport() {
            const userId = $('#filter-user').val();
            const action = $('#filter-action').val();
            const dateFrom = $('#filter-date-from').val();
            const dateTo = $('#filter-date-to').val();
            
            const params = new URLSearchParams({
                user_id: userId,
                action: action,
                date_from: dateFrom,
                date_to: dateTo
            });
            
            window.open('<?= url('?route=admin/activity_logs/print') ?>&' + params.toString(), '_blank');
        }

        // Global button hook
        window.printAudit = openPrintReport;
    });
</script>


</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
