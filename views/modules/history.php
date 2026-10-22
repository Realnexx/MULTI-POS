<?php
// Prevent direct access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$title = "POS Transaction History";

// Fallbacks for data passed from controller
$today_stats = $today_stats ?? ['revenue' => 0, 'orders' => 0, 'refund_rate' => 0];
$filters = $filters ?? ['search' => '', 'date_from' => '', 'date_to' => '', 'payment_status' => 'All', 'channels' => [], 'locations' => []];
$active_branches = $active_branches ?? [];

$selected_location = !empty($filters['locations'][0]) ? $filters['locations'][0] : '';
$selected_status = $filters['payment_status'] ?? 'All';
$selected_channel = !empty($filters['channels'][0]) ? $filters['channels'][0] : '';

ob_start();
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">

<style>
/* ================================================================
   AURORA POS — TRANSACTION HISTORY CLEAN MODERN STYLES (No Glassmorphism)
   ================================================================ */

/* Page Header */
.his-page-header {
    margin-top: 0.25rem;
    margin-bottom: 1.5rem;
}
.his-page-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #2a3547;
    letter-spacing: -0.5px;
    margin-bottom: 4px;
}
.his-page-subtitle {
    font-size: 0.88rem;
    color: #7c8fac;
    margin-bottom: 0;
}
.his-badge-pill {
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

/* KPI Stat Cards */
.his-kpi-card {
    border-radius: 18px;
    border: 1px solid #e8edf5;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
    background: #ffffff;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.his-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}
.his-kpi-glow {
    height: 4px;
    width: 100%;
}
.his-kpi-icon {
    width: 48px;
    height: 48px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.his-kpi-value {
    font-size: 1.85rem;
    font-weight: 800;
    letter-spacing: -1px;
    line-height: 1;
    color: #2a3547;
}
.his-kpi-label {
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

/* Table Design */
.aurora-table {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
}
.aurora-table thead th {
    font-size: 0.68rem !important;
    font-weight: 700 !important;
    color: #9eaac7 !important;
    text-transform: uppercase !important;
    letter-spacing: 0.8px !important;
    padding: 14px 16px !important;
    border-bottom: 2px solid #f0f4fb !important;
    white-space: nowrap !important;
    background: #fafbff;
}
.aurora-table tbody td {
    padding: 13px 16px !important;
    border-bottom: 1px solid #f8f9fc !important;
    font-size: 0.84rem !important;
    vertical-align: middle !important;
}
.aurora-table tbody tr:hover td {
    background: #fafbff !important;
}
.aurora-table tbody tr {
    cursor: pointer;
    transition: background 0.15s ease;
}

/* Order ID Badge */
.his-id-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 3px 10px;
    border-radius: 8px;
    font-weight: 800;
    font-size: 0.75rem;
    background: #f0f4ff;
    color: #5d87ff;
}

/* Action Buttons inside Table */
.action-btn-view {
    background: rgba(93, 135, 255, 0.1) !important;
    border: 1px solid rgba(93, 135, 255, 0.25) !important;
    color: #5d87ff !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 5px 12px !important;
    font-size: 0.76rem !important;
    transition: all 0.2s ease !important;
}
.action-btn-view:hover {
    background: #5d87ff !important;
    color: #ffffff !important;
    border-color: #5d87ff !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(93, 135, 255, 0.25) !important;
}

.action-btn-print {
    background: rgba(19, 222, 185, 0.1) !important;
    border: 1px solid rgba(19, 222, 185, 0.3) !important;
    color: #0e9f83 !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 5px 12px !important;
    font-size: 0.76rem !important;
    transition: all 0.2s ease !important;
}
.action-btn-print:hover {
    background: #13deb9 !important;
    color: #ffffff !important;
    border-color: #13deb9 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(19, 222, 185, 0.25) !important;
}

.action-btn-refund {
    background: rgba(250, 84, 84, 0.1) !important;
    border: 1px solid rgba(250, 84, 84, 0.25) !important;
    color: #fa5454 !important;
    font-weight: 700 !important;
    border-radius: 8px !important;
    padding: 5px 12px !important;
    font-size: 0.76rem !important;
    transition: all 0.2s ease !important;
}
.action-btn-refund:hover {
    background: #fa5454 !important;
    color: #ffffff !important;
    border-color: #fa5454 !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(250, 84, 84, 0.25) !important;
}

.action-btn-cancel {
    background: rgba(255, 174, 31, 0.1) !important;
    border: 1px solid rgba(255, 174, 31, 0.25) !important;
    color: #ffae1f !important;
    border-radius: 8px !important;
    transition: all 0.2s ease !important;
}
.action-btn-cancel:hover {
    background: #ffae1f !important;
    color: #ffffff !important;
    border-color: #ffae1f !important;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(255, 174, 31, 0.25) !important;
}

/* DataTables Pagination & Length */
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

/* Custom Table Search Input */
.his-search-input {
    border-radius: 50px;
    border: 1px solid #dfe5ef;
    background: #f8faff;
    padding: 8px 16px 8px 38px;
    font-size: 0.84rem;
    color: #2a3547;
    width: 260px;
    outline: none;
    transition: all 0.2s ease;
}
.his-search-input:focus {
    border-color: #5d87ff;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.1);
}

/* Print Only Section */
@media print {
    aside, header, footer, .d-print-none, .dataTables_length, .dataTables_info, .dataTables_paginate {
        display: none !important;
    }
    .print-only { display: block !important; }
    table { width: 100% !important; border-collapse: collapse !important; }
    th, td { border: 1px solid #ddd !important; padding: 8px !important; font-size: 12px !important; }
}

/* ================================================================
   THERMAL RECEIPT STYLES (for modal AJAX content)
   ================================================================ */
.thermal-ticket {
    background: #fff;
    border-radius: 4px;
    font-family: 'Courier New', Courier, monospace;
    font-size: 13px;
    color: #1a1a1a;
    max-width: 100%;
    padding: 0 4px 8px;
}

/* Serrated edges */
.serrated-edge {
    width: 100%;
    height: 16px;
    background-image: radial-gradient(circle at 8px 0, #f8faff 8px, transparent 8px);
    background-size: 16px 16px;
    background-repeat: repeat-x;
}
.serrated-top { background-position: 0 0; margin-bottom: 4px; }
.serrated-bottom {
    background-image: radial-gradient(circle at 8px 16px, #f8faff 8px, transparent 8px);
    background-position: 0 0;
    margin-top: 4px;
}

/* Header */
.receipt-header { text-align: center; padding: 8px 16px 4px; }
.store-badge-circle {
    width: 48px; height: 48px;
    background: linear-gradient(135deg, #5d87ff, #13deb9);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 8px;
}
.badge-letter { color: #fff; font-size: 20px; font-weight: 900; font-family: 'Outfit', sans-serif; }
.receipt-brand { font-family: 'Outfit', sans-serif; font-weight: 900; color: #1a1a1a; margin: 0; font-size: 18px !important; }
.receipt-contact { font-size: 10px; color: #6b7280; }
.receipt-contact p { margin: 2px 0; display: flex; align-items: center; justify-content: center; gap: 4px; }
.receipt-contact .material-symbols-outlined { font-size: 12px; }
.header-divider { display: flex; align-items: center; gap: 8px; margin: 12px 16px 0; }
.divider-line { flex: 1; height: 1px; border-top: 1px dashed #d1d5db; }
.divider-text { font-size: 9px; font-weight: 700; letter-spacing: 2px; color: #9ca3af; white-space: nowrap; }

/* Metadata rows */
.receipt-metadata { padding: 12px 16px 0; }
.meta-row { display: flex; justify-content: space-between; align-items: center; padding: 4px 0; font-size: 11.5px; border-bottom: 1px dotted #f0f0f0; }
.meta-label { color: #6b7280; font-weight: 500; }
.meta-value { font-weight: 700; color: #1a1a1a; display: flex; align-items: center; gap: 3px; }
.meta-value .material-symbols-outlined { font-size: 12px; color: #5d87ff; }
.meta-value.highlight { color: #5d87ff; font-size: 13px; }

/* Items */
.receipt-items { padding: 10px 16px 0; }
.items-header { display: flex; justify-content: space-between; font-size: 9px; font-weight: 700; letter-spacing: 1.5px; color: #9ca3af; text-transform: uppercase; padding-bottom: 6px; border-bottom: 2px solid #1a1a1a; margin-bottom: 6px; }
.item-row { padding: 5px 0; border-bottom: 1px dotted #e5e7eb; }
.item-main-row { display: flex; align-items: center; gap: 4px; }
.item-name { font-weight: 700; font-size: 12px; color: #1a1a1a; flex: 1; }
.dot-leader { flex: 1; border-bottom: 1px dotted #d1d5db; min-width: 20px; }
.item-subtotal { font-weight: 800; font-size: 12.5px; color: #1a1a1a; white-space: nowrap; }
.item-sub-row { font-size: 10px; color: #6b7280; margin-top: 2px; padding-left: 2px; }
.item-modifier-row { display: flex; justify-content: space-between; font-size: 10px; color: #9ca3af; padding-left: 12px; }
.item-modifier-price { color: #ef4444; font-weight: 600; }

/* Summary */
.receipt-summary { padding: 10px 16px 0; }
.summary-row { display: flex; justify-content: space-between; font-size: 11.5px; padding: 3px 0; }
.summary-label { color: #6b7280; }
.summary-value { font-weight: 700; color: #1a1a1a; }
.summary-value.discount { color: #ef4444; }
.total-due-row { margin-top: 6px; padding-top: 8px; border-top: 2px solid #1a1a1a; }
.total-due-row .summary-label { font-size: 13px; font-weight: 900; color: #1a1a1a; }
.total-due-row .total-amount { font-size: 18px; font-weight: 900; color: #5d87ff; }

/* Payment */
.receipt-payment { padding: 10px 16px 0; }
.payment-row { display: flex; justify-content: space-between; align-items: center; font-size: 11.5px; padding: 3px 0; }
.payment-label { color: #6b7280; }
.payment-value { font-weight: 700; color: #1a1a1a; display: flex; align-items: center; gap: 4px; }
.payment-value .material-symbols-outlined { font-size: 13px; }
.change-banner {
    display: flex; justify-content: space-between; align-items: center;
    margin-top: 10px; padding: 10px 14px; border-radius: 10px;
    background: #e8fdf5;
}
.change-banner.theme-gcash { background: #eff6ff; }
.change-banner.theme-card { background: #f5f3ff; }
.change-label { font-size: 11px; font-weight: 700; color: #065f46; display: flex; align-items: center; gap: 4px; }
.change-banner.theme-gcash .change-label { color: #1e40af; }
.change-banner.theme-card .change-label { color: #6d28d9; }
.change-label .material-symbols-outlined { font-size: 14px; }
.change-amount { font-size: 16px; font-weight: 900; color: #059669; }
.change-banner.theme-gcash .change-amount { color: #2563eb; }
.change-banner.theme-card .change-amount { color: #7c3aed; }

/* Barcode */
.receipt-barcode { text-align: center; padding: 14px 16px 4px; }
.barcode-lines { display: flex; align-items: stretch; justify-content: center; gap: 1px; height: 36px; margin-bottom: 4px; }
.barcode-lines > div { background: #1a1a1a; border-radius: 1px; }
.barcode-lines .line.thin { width: 1px; }
.barcode-lines .line.medium { width: 2px; }
.barcode-lines .line.thick { width: 3px; }
.barcode-lines .line.extra-thick { width: 4px; }
.barcode-lines .space { background: transparent; }
.barcode-lines .space.thin { width: 2px; }
.barcode-lines .space.medium { width: 3px; }
.barcode-lines .space.thick { width: 5px; }
.barcode-text { font-family: 'Courier New', monospace; font-size: 9px; letter-spacing: 2px; color: #6b7280; }

/* Footer */
.receipt-footer { text-align: center; padding: 4px 16px 10px; }
.footer-seal { display: flex; align-items: center; gap: 8px; }
.seal-line { flex: 1; height: 1px; background: #e5e7eb; }
.footer-seal .material-symbols-outlined { font-size: 18px; color: #5d87ff; }
.footer-thanks { font-family: 'Outfit', sans-serif; font-size: 10px !important; font-weight: 900; letter-spacing: 3px; color: #1a1a1a; margin: 8px 0 4px; }
.footer-tagline { font-size: 9px; color: #9ca3af; font-style: italic; }
.receipt-policy { border-top: 1px dashed #e5e7eb; margin-top: 10px; padding-top: 8px; }
.receipt-policy p { font-size: 8px; color: #9ca3af; letter-spacing: 1px; text-transform: uppercase; margin: 0; }


/* Match Reports and Dashboard surfaces. */
.his-kpi-glow { display:none; }
.his-kpi-card { border:1px solid #e1eaf0; background:linear-gradient(145deg,#fff,#f8fbfd); box-shadow:0 5px 20px rgba(25,62,82,.045); }
.his-kpi-label { color:#62758b; }
.his-wrapper .filter-applied { border-color:#008aa5 !important; background-color:#eaf8fb !important; color:#006b80 !important; box-shadow:0 0 0 1px rgba(0,138,165,.12); }
.his-wrapper .page-item.active .page-link,
.his-wrapper .dataTables_paginate .paginate_button.current { background:#008aa5 !important; border-color:#008aa5 !important; color:#fff !important; box-shadow:0 3px 9px rgba(0,138,165,.2); font-weight:700; }
.his-wrapper .page-link { min-width:40px; min-height:40px; display:inline-flex; align-items:center; justify-content:center; }
.his-wrapper a:focus-visible,.his-wrapper button:focus-visible { outline:3px solid #64cfe0; outline-offset:3px; }
@media(max-width:575px) { .his-wrapper .filter-card,.his-wrapper .aurora-card { padding:16px !important; } .his-kpi-value { font-size:1.65rem; } .his-page-header>div:last-child { flex-wrap:wrap; } }
@media(prefers-reduced-motion:reduce) { .his-kpi-card,.his-wrapper .form-select-solid,.his-wrapper .form-control-solid { transition:none !important; } .his-kpi-card:hover { transform:none; } }
</style>

<!-- Print Header (Hidden on screen) -->
<div class="d-none print-only mb-4 text-center">
    <h3 class="fw-bold text-dark mb-1">POS Sales Transactions Report</h3>
    <p class="small text-muted">Generated by <?= htmlspecialchars($_SESSION['user']['name'] ?? 'System') ?> on <?= date('M d, Y h:i A') ?></p>
</div>

<div class="his-wrapper pb-3">

    <!-- ============ CLEAN PAGE HEADER (No Glassmorphism) ============ -->
    <div class="his-page-header d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3">
        <div>
            <div class="his-badge-pill">
                <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                POS Ledger &amp; Audit Trail
            </div>
            <h2 class="his-page-title">Transaction History</h2>
            <p class="his-page-subtitle">Real-time order logs, cashier settlements, receipt lookups, and transaction governance.</p>
        </div>

        <!-- Quick Header Actions -->
        <div class="d-flex align-items-center gap-2">
            <?php
            $export_qs = array_merge($_GET, ['export' => 'csv']);
            $export_url = url('?' . http_build_query($export_qs));
            ?>
            <a href="<?= $export_url ?>" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-bold d-flex align-items-center gap-2" id="his-export-btn" title="Download CSV">
                <iconify-icon icon="solar:cloud-download-bold-duotone" class="fs-5"></iconify-icon>
                <span>Export CSV</span>
            </a>

            <a href="<?= url('?route=admin/reports/print&type=sales') ?>" target="_blank" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2 shadow-sm" id="print-daily-btn" title="Print Sales Report">
                <iconify-icon icon="solar:printer-bold-duotone" class="fs-5"></iconify-icon>
                <span>Print Report</span>
            </a>
        </div>
    </div>

    <!-- ============ ROW 1: KPI STATS CARDS ============ -->
    <div class="row g-4 mb-4">

        <!-- Card 1: Revenue -->
        <div class="col-12 col-md-4">
            <div class="his-kpi-card">
                <div class="his-kpi-glow" style="background:linear-gradient(90deg, #5d87ff, #13deb9);"></div>
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="his-kpi-icon" style="background:rgba(93,135,255,0.1);">
                            <iconify-icon icon="solar:wallet-money-bold-duotone" style="color:#5d87ff;font-size:1.55rem;"></iconify-icon>
                        </div>
                        <span style="background:#e8fdf5;color:#0fb68c;border-radius:50px;padding:3px 12px;font-size:0.72rem;font-weight:700;">Live</span>
                    </div>
                    <div class="his-kpi-label mb-1">Total Revenue</div>
                    <div class="his-kpi-value" id="stat-revenue">₱<?= number_format($today_stats['revenue'], 2) ?></div>
                </div>
            </div>
        </div>

        <!-- Card 2: Total Orders -->
        <div class="col-12 col-md-4">
            <div class="his-kpi-card">
                <div class="his-kpi-glow" style="background:linear-gradient(90deg, #13deb9, #5d87ff);"></div>
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="his-kpi-icon" style="background:rgba(19,222,185,0.1);">
                            <iconify-icon icon="solar:bag-check-bold-duotone" style="color:#13deb9;font-size:1.55rem;"></iconify-icon>
                        </div>
                        <span style="background:#f0f4ff;color:#5d87ff;border-radius:50px;padding:3px 12px;font-size:0.72rem;font-weight:700;">Count</span>
                    </div>
                    <div class="his-kpi-label mb-1">Total Orders</div>
                    <div class="his-kpi-value" id="stat-orders"><?= number_format($today_stats['orders']) ?></div>
                </div>
            </div>
        </div>

        <!-- Card 3: Refund Rate -->
        <div class="col-12 col-md-4">
            <div class="his-kpi-card">
                <div class="his-kpi-glow" style="background:linear-gradient(90deg, #fa5454, #ffae1f);"></div>
                <div class="p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="his-kpi-icon" style="background:rgba(250,84,84,0.1);">
                            <iconify-icon icon="solar:restart-circle-bold-duotone" style="color:#fa5454;font-size:1.55rem;"></iconify-icon>
                        </div>
                        <span style="background:#fef2f2;color:#e53e3e;border-radius:50px;padding:3px 12px;font-size:0.72rem;font-weight:700;">Deductions</span>
                    </div>
                    <div class="his-kpi-label mb-1">Refund &amp; Cancel Rate</div>
                    <div class="his-kpi-value" id="stat-refund-rate"><?= $today_stats['refund_rate'] ?>%</div>
                </div>
            </div>
        </div>

    </div>

    <!-- ============ ROW 2: FILTERS (BELOW STATS CARD AS REQUESTED) ============ -->
    <div class="filter-card p-4 mb-4 d-print-none">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:filter-bold-duotone" class="text-primary fs-5"></iconify-icon>
                <h6 class="fw-bold text-dark mb-0" style="font-size:0.95rem;">Filter Transactions</h6>
            </div>
            <span class="text-muted small">Live updates instantly on change</span>
        </div>

        <form id="filter-form" action="<?= url() ?>" method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="route" value="pos/history">

            <!-- Branch Filter -->
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="filter-label">
                    <iconify-icon icon="solar:shop-2-line-duotone"></iconify-icon>
                    <span>Branch</span>
                </label>
                <select name="locations[]" id="locationFilter" class="form-select form-select-solid">
                    <?php if(User::has_all_branch_access($_SESSION['user']['id'])): ?><option value="">All Branches</option><?php endif; ?>
                    <?php foreach ($active_branches as $b): ?>
                        <option value="<?= htmlspecialchars($b['name']) ?>" <?= ($selected_location === $b['name']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Payment Status Filter -->
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="filter-label">
                    <iconify-icon icon="solar:card-line-duotone"></iconify-icon>
                    <span>Payment Status</span>
                </label>
                <select name="payment_status" id="paymentStatusFilter" class="form-select form-select-solid">
                    <option value="All" <?= ($selected_status === 'All') ? 'selected' : '' ?>>All Statuses</option>
                    <option value="Paid" <?= ($selected_status === 'Paid') ? 'selected' : '' ?>>Paid</option>
                    <option value="Pending" <?= ($selected_status === 'Pending') ? 'selected' : '' ?>>Pending</option>
                    <option value="Refunded" <?= ($selected_status === 'Refunded') ? 'selected' : '' ?>>Refunded</option>
                </select>
            </div>

            <!-- Sales Channel Filter -->
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="filter-label">
                    <iconify-icon icon="solar:screencast-2-line-duotone"></iconify-icon>
                    <span>Channel</span>
                </label>
                <select name="channels[]" id="channelFilter" class="form-select form-select-solid">
                    <option value="">All Channels</option>
                    <option value="POS Terminal" <?= ($selected_channel === 'POS Terminal') ? 'selected' : '' ?>>POS Terminal</option>
                    <option value="Online Store" <?= ($selected_channel === 'Online Store') ? 'selected' : '' ?>>Online Store</option>
                </select>
            </div>

            <!-- Date From -->
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="filter-label">
                    <iconify-icon icon="solar:calendar-date-bold-duotone"></iconify-icon>
                    <span>Date From</span>
                </label>
                <input type="date" name="date_from" id="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="form-control form-control-solid">
            </div>

            <!-- Date To -->
            <div class="col-12 col-sm-6 col-lg-2">
                <label class="filter-label">
                    <iconify-icon icon="solar:calendar-mark-bold-duotone"></iconify-icon>
                    <span>Date To</span>
                </label>
                <input type="date" name="date_to" id="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="form-control form-control-solid">
            </div>

            <!-- Reset / Clear Button -->
            <div class="col-12 col-sm-6 col-lg-2 d-flex">
                <a href="<?= url('?route=pos/history') ?>" id="btn-clear-all" class="btn-clear-filter w-100" title="Reset all filters">
                    <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
                    <span>Clear Filters</span>
                </a>
            </div>

        </form>
    </div>

    <!-- ============ ROW 3: TRANSACTIONS TABLE CARD ============ -->
    <div class="aurora-card p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h5 class="fw-bold text-dark mb-1" style="font-size:1.05rem;">Sales Ledger</h5>
                <p class="text-muted small mb-0 d-print-none">Review past sales, verify payments, and inspect receipt records.</p>
            </div>
            <div class="d-flex align-items-center gap-2 d-print-none position-relative">
                <iconify-icon icon="solar:magnifer-line-duotone" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#9eaac7;font-size:1.05rem;pointer-events:none;"></iconify-icon>
                <input type="text" id="custom-search" class="his-search-input" placeholder="Search order, cashier, customer..." autocomplete="off">
            </div>
        </div>

        <div class="table-responsive">
            <table id="transactions-table" class="table aurora-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date &amp; Time</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th>Channel</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th class="text-end d-print-none">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ============ RECEIPT MODAL ============ -->
<div class="modal fade" id="historyReceiptModal" tabindex="-1" aria-labelledby="historyReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 520px;">
        <div class="modal-content border-0 overflow-hidden" style="border-radius:24px;box-shadow:0 20px 60px rgba(0,0,0,0.15);">

            <!-- Accent Bar -->
            <div style="height:5px;background:linear-gradient(90deg,#5d87ff,#13deb9);"></div>

            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-3">
                <div class="d-flex align-items-center gap-3">
                    <div style="width:42px;height:42px;background:#eef2ff;border-radius:12px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <i class="fa-solid fa-receipt" style="font-size:1.1rem;color:#5d87ff;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-800 text-dark mb-0" id="historyReceiptModalLabel" style="font-size:1.05rem;font-family:'Outfit',sans-serif;">Transaction Receipt</h5>
                        <div class="text-muted" style="font-size:0.75rem;" id="receipt-modal-subtitle">Loading details...</div>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 py-3" id="historyReceiptModalBody" style="background:#f8faff;min-height:200px;">
                <div class="text-center py-5" id="receipt-loading-state">
                    <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;border-width:3px;">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-3 small fw-semibold">Fetching receipt data...</p>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-0 px-4 py-3" style="background:#fff;">
                <button type="button" class="btn fw-semibold px-4 py-2 rounded-pill"
                    style="background:#f0f4fb;color:#5a6a85;border:1px solid #e2e8f0;font-size:0.85rem;"
                    data-bs-dismiss="modal">
                    <i class="ti ti-x me-1"></i> Close
                </button>
                <button type="button" class="btn btn-primary fw-semibold px-4 py-2 rounded-pill ms-2 d-flex align-items-center gap-2"
                    style="font-size:0.85rem;box-shadow:0 4px 14px rgba(93,135,255,0.3);"
                    id="modalPrintBtn"
                    title="Open Print Center (Normal, Thermal 80mm / 58mm)">
                    <iconify-icon icon="solar:printer-bold-duotone" style="font-size:1.1rem;"></iconify-icon>
                    Print Receipt (Normal / Thermal)
                </button>
            </div>

        </div>
    </div>
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {

    // ================================================================
    // DATATABLES INITIALIZATION
    // ================================================================
    const table = $('#transactions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "?route=pos/history/data",
            type: "GET",
            data: function(d) {
                d.date_from = $('#date_from').val();
                d.date_to = $('#date_to').val();
                d.payment_status = $('#paymentStatusFilter').val() || 'All';

                const channels = $('#channelFilter').val();
                if (channels) {
                    d.channels = [channels];
                }

                const locations = $('#locationFilter').val();
                if (locations) {
                    d.locations = [locations];
                }
            }
        },
        columns: [
            {
                data: 0,
                className: "py-3 px-3 fw-bold text-primary",
                render: function(d) {
                    return '<span class="his-id-badge">' + d + '</span>';
                }
            },
            { data: 1, className: "py-3 text-muted small" },
            { data: 2, className: "py-3 text-dark fw-semibold" },
            { data: 3, className: "py-3 text-muted" },
            { data: 4, className: "py-3" },
            {
                data: 5,
                className: "py-3 fw-bold text-dark",
                render: function(d) {
                    return '<strong style="color:#2a3547;font-size:0.88rem;">' + d + '</strong>';
                }
            },
            { data: 6, className: "py-3" },
            { data: 7, className: "py-3" },
            {
                data: 8,
                className: "py-3 px-3 text-end d-print-none",
                orderable: false
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        ordering: true,
        order: [[0, 'desc']],
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search records...",
            lengthMenu: "Show _MENU_ entries",
            info: "Showing _START_ to _END_ of _TOTAL_ transactions",
            paginate: {
                previous: '<iconify-icon icon="solar:alt-arrow-left-bold"></iconify-icon>',
                next: '<iconify-icon icon="solar:alt-arrow-right-bold"></iconify-icon>'
            }
        },
        drawCallback: function() {
            $('.dataTables_paginate').addClass('mt-3 d-flex justify-content-end align-items-center gap-1');
        }
    });

    // Custom instant search
    const customSearch = $('#custom-search');
    if (customSearch.length) {
        customSearch.on('input', function() {
            table.search(this.value).draw();
            updateFilteredStats(); updateExportLink();
        });
        customSearch.on('keypress', function(e) {
            if (e.key === 'Enter') e.preventDefault();
        });
    }

    // Update Export and Print URLs
    function updateExportLink() {
        const date_from = $('#date_from').val();
        const date_to = $('#date_to').val();
        const payment_status = $('#paymentStatusFilter').val();
        const search = customSearch.val() || '';
        const channels = $('#channelFilter').val();
        const locations = $('#locationFilter').val();

        const commonParams = {
            date_from: date_from,
            date_to: date_to,
            payment_status: payment_status,
            search: search
        };

        const csvParams = new URLSearchParams({
            route: 'pos/history',
            export: 'csv',
            ...commonParams
        });
        if (channels) csvParams.append('channels[]', channels);
        if (locations) csvParams.append('locations[]', locations);
        $('#his-export-btn').attr('href', '<?= url('?') ?>' + csvParams.toString());

        const printParams = new URLSearchParams({
            route: 'admin/reports/print',
            type: 'sales',
            ...commonParams
        });
        if (channels) printParams.append('channels[]', channels);
        if (locations) printParams.append('locations[]', locations);
        $('#print-daily-btn').attr('href', '<?= url('?') ?>' + printParams.toString());
    }

    // Refresh KPI Stats via AJAX
    let historyStatsRequest = null;
    function updateFilteredStats() {
        if (historyStatsRequest) historyStatsRequest.abort();
        const params = {
            date_from: $('#date_from').val(),
            date_to: $('#date_to').val(),
            payment_status: $('#paymentStatusFilter').val() || 'All',
            search: $('#custom-search').val() || ''
        };
        const channels = $('#channelFilter').val();
        if (channels) params['channels[]'] = channels;
        const locations = $('#locationFilter').val();
        if (locations) params['locations[]'] = locations;

        historyStatsRequest = $.get('?route=pos/history/stats', params, function(stats) {
            if (stats) {
                $('#stat-revenue').text('₱' + Number(stats.revenue || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
                $('#stat-orders').text(Number(stats.orders || 0).toLocaleString());
                $('#stat-refund-rate').text((stats.refund_rate || 0) + '%');
            }
        }, 'json');
    }

    function highlightFilters() {
        $('#locationFilter, #paymentStatusFilter, #channelFilter, #date_from, #date_to').each(function() {
            const value = this.value;
            this.classList.toggle('filter-applied', Boolean(value && value !== 'All'));
        });
    }
    highlightFilters();
    function reloadAll() {
        highlightFilters();
        table.draw();
        updateFilteredStats();
        updateExportLink();
    }

    // Live filter change triggers
    $('#locationFilter, #paymentStatusFilter, #channelFilter, #date_from, #date_to').on('change', function() {
        reloadAll();
    });

    // Reset filter click
    $('#btn-clear-all').on('click', function(e) {
        e.preventDefault();
        const branchSelect = document.getElementById('locationFilter');
        if (branchSelect) branchSelect.selectedIndex = 0;
        $('#paymentStatusFilter').val('All');
        $('#channelFilter').val('');
        $('#date_from').val('');
        $('#date_to').val('');
        customSearch.val('');
        table.search('');
        reloadAll();
    });

    table.on('draw', function() {
        updateExportLink();
        $('[data-bs-toggle="tooltip"]').each(function() {
            const inst = bootstrap.Tooltip.getInstance(this);
            if (inst) inst.dispose();
            new bootstrap.Tooltip(this, { boundary: document.body, trigger: 'hover' });
        });
    });

    // ================================================================
    // RECEIPT MODAL & ACTIONS
    // ================================================================
    const receiptModalEl = document.getElementById('historyReceiptModal');
    let receiptBsModal = null;
    if (receiptModalEl) {
        receiptBsModal = new bootstrap.Modal(receiptModalEl);
    }

    let currentHistoryReceiptSaleId = null;

    function openHistoryReceiptModal(saleId) {
        currentHistoryReceiptSaleId = saleId;
        $('#receipt-modal-subtitle').text('Loading details...');
        $('#historyReceiptModalBody').html(`
            <div class="text-center py-5" id="receipt-loading-state">
                <div class="spinner-border text-primary" role="status" style="width:2.5rem;height:2.5rem;border-width:3px;">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="text-muted mt-3 small fw-semibold">Fetching receipt data...</p>
            </div>
        `);
        if (receiptBsModal) receiptBsModal.show();

        $.get('?route=pos/receipt&id=' + saleId + '&ajax=1', function(html) {
            $('#historyReceiptModalBody').html(html);
            $('#receipt-modal-subtitle').text('Receipt #' + String(saleId).padStart(6, '0'));
        }).fail(function() {
            $('#receipt-modal-subtitle').text('Failed to load');
            $('#historyReceiptModalBody').html(`
                <div class="text-center py-5 text-danger">
                    <iconify-icon icon="solar:danger-triangle-bold-duotone" style="font-size:2.5rem;"></iconify-icon>
                    <p class="fw-bold mt-2 mb-1">Failed to Load</p>
                    <p class="text-muted small">Could not retrieve receipt data from server.</p>
                </div>
            `);
        });
    }

    // Modal print button: redirect to dedicated print center with format selections
    $('#modalPrintBtn').on('click', function() {
        if (currentHistoryReceiptSaleId) {
            window.location.href = '?route=pos/receipt&id=' + currentHistoryReceiptSaleId;
        }
    });

    // Row click opens receipt modal
    $('#transactions-table tbody').on('click', 'tr', function(e) {
        if ($(e.target).closest('.action-cell, .action-btn, button, a').length > 0) return;
        const saleId = $(this).attr('data-id');
        if (saleId) openHistoryReceiptModal(saleId);
    });

    // View Receipt button click
    $('#transactions-table tbody').on('click', '.view-receipt-btn', function(e) {
        e.stopPropagation();
        const saleId = $(this).attr('data-id');
        if (saleId) openHistoryReceiptModal(saleId);
    });

    // Status updates (Refund / Cancel) with SweetAlert2
    function updateTransactionStatus(id, newStatus, actionName, successColor) {
        Swal.fire({
            title: 'Are you sure?',
            text: `Do you want to ${actionName} this transaction (#HH-${String(id).padStart(6, '0')})?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: successColor,
            cancelButtonColor: '#6c757d',
            confirmButtonText: `Yes, ${actionName}!`,
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Processing...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.post('?route=pos/history/update_status', {
                    id: id,
                    status: newStatus
                }, function(response) {
                    if (response && response.success) {
                        Swal.fire(
                            'Updated!',
                            `The transaction has been successfully ${newStatus.toLowerCase()}.`,
                            'success'
                        ).then(() => {
                            table.draw(false);
                            updateFilteredStats();
                        });
                    } else {
                        Swal.fire(
                            'Error!',
                            response.error || 'Failed to update transaction status.',
                            'error'
                        );
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error!', 'Server error encountered while updating transaction status.', 'error');
                });
            }
        });
    }

    $('#transactions-table tbody').on('click', '.refund-btn', function(e) {
        e.stopPropagation();
        const id = $(this).attr('data-id');
        updateTransactionStatus(id, 'Refunded', 'refund', '#fa5454');
    });

    $('#transactions-table tbody').on('click', '.cancel-btn', function(e) {
        e.stopPropagation();
        const id = $(this).attr('data-id');
        updateTransactionStatus(id, 'Cancelled', 'cancel', '#ffae1f');
    });

});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<script defer src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script defer src="<?= url('assets/js/dashboard-motion.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/dashboard-motion.js') ?>"></script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
