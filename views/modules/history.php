<?php
// Prevent direct access
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$title = "POS Transaction History";

// Deterministic customer names mapping
$mock_names = ["Angelo Reyes", "Maria Clara", "Jose Rizal", "Sofia Loren", "Juan Luna", "Gabriela Silang", "Andres Bonifacio", "Emilio Aguinaldo", "Apolinario Mabini", "Melchora Aquino"];

// Fallbacks for data passed from controller
$today_stats = $today_stats ?? ['revenue' => 0, 'orders' => 0, 'refund_rate' => 0];
$filters = $filters ?? ['search' => '', 'date_from' => '', 'date_to' => '', 'payment_status' => 'All', 'channels' => [], 'locations' => []];
$active_branches = $active_branches ?? [];

ob_start();
?>

<!-- SweetAlert2 (Loaded for premium confirmation alerts) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Tailwind CSS CDN (with Preflight disabled to prevent conflicts with Bootstrap) -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
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

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<!-- AOS Library (Animate On Scroll) -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
    /* Premium Extra Small Buttons for actions */
    .btn-xs {
        padding: 5px 12px !important;
        font-size: 11px !important;
        line-height: 1.2 !important;
        border-radius: 20px !important;
        font-weight: 600 !important;
        box-shadow: none !important;
    }

    /* Premium Action Buttons Styling - Fully Unique Designs */
    .action-btn-view {
        background: rgba(132, 41, 200, 0.07) !important;
        border: 1px solid rgba(132, 41, 200, 0.15) !important;
        color: #8429c8 !important;
        font-weight: 700 !important;
        font-family: 'Outfit', sans-serif !important;
        letter-spacing: 0.3px !important;
        padding: 5px 12px !important;
        text-decoration: none !important;
        border-radius: 20px !important;
        box-shadow: none !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
        font-size: 11px !important;
    }

    .action-btn-view:hover {
        background: #8429c8 !important;
        color: #ffffff !important;
        border-color: #8429c8 !important;
        text-decoration: none !important;
        transform: translateY(-1.5px) !important;
        box-shadow: 0 4px 12px rgba(132, 41, 200, 0.25) !important;
    }

    .action-btn-refund {
        background: linear-gradient(135deg, #e11d48, #be123c) !important;
        color: #ffffff !important;
        border: none !important;
        border-radius: 20px !important;
        padding: 6px 14px !important;
        font-weight: 700 !important;
        font-size: 11px !important;
        letter-spacing: 0.2px !important;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .action-btn-refund:hover {
        background: linear-gradient(135deg, #be123c, #9f1239) !important;
        color: #ffffff !important;
        transform: translateY(-1.5px) !important;
        box-shadow: 0 4px 12px rgba(225, 29, 72, 0.3) !important;
    }

    .action-btn-cancel {
        background-color: rgba(217, 119, 6, 0.08) !important;
        color: #d97706 !important;
        border: 1px solid rgba(217, 119, 6, 0.25) !important;
        border-radius: 50% !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }

    .action-btn-cancel:hover {
        background-color: #d97706 !important;
        color: #ffffff !important;
        border-color: #d97706 !important;
        transform: rotate(90deg) scale(1.05) !important;
        box-shadow: 0 4px 10px rgba(217, 119, 6, 0.3) !important;
    }

    /* Premium Custom Dark Tooltip Styling */
    .tooltip .tooltip-inner {
        background-color: #1e1e24 !important;
        color: #ffffff !important;
        font-size: 11px !important;
        font-weight: 500 !important;
        padding: 6px 12px !important;
        border-radius: 8px !important;
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.18) !important;
        border: 1px solid rgba(255, 255, 255, 0.08) !important;
        letter-spacing: 0.2px !important;
    }

    .tooltip .tooltip-arrow::before {
        border-top-color: #1e1e24 !important;
        border-bottom-color: #1e1e24 !important;
    }

    /* Premium Thermal Ticket Styling for Modal Receipt */
    .thermal-ticket {
        background-color: #fdfdfa !important;
        background-image: repeating-linear-gradient(45deg, rgba(182, 14, 61, 0.01) 0px, rgba(182, 14, 61, 0.01) 1px, transparent 1px, transparent 4px) !important;
        border: 1px solid #e5e4dc !important;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.04) !important;
        border-radius: 20px;
        font-family: 'Inter', sans-serif;
        position: relative;
        color: #2b2d2f;
        padding: 32px 24px;
        margin: 8px 0;
        box-sizing: border-box;
        overflow: hidden;
    }

    .serrated-edge {
        height: 6px;
        background-image: radial-gradient(circle at 50% 0, transparent 45%, #e5e4dc 50%, transparent 55%);
        background-size: 12px 6px;
        background-repeat: repeat-x;
        width: 100%;
        position: absolute;
        left: 0;
        opacity: 0.5;
    }

    .serrated-top {
        top: 0;
    }

    .serrated-bottom {
        bottom: 0;
        transform: scaleY(-1);
    }

    .stamp-paid {
        position: absolute;
        right: 20px;
        top: 110px;
        pointer-events: none;
        transform: rotate(-12deg);
        border: 4px double rgba(182, 14, 61, 0.2);
        color: rgba(182, 14, 61, 0.4);
        font-family: 'Montserrat', sans-serif;
        padding: 5px 15px;
        border-radius: 8px;
        text-align: center;
        font-weight: 900;
        font-size: 11px;
        letter-spacing: 0.1em;
        z-index: 5;
    }

    .store-badge-circle {
        width: 54px;
        height: 54px;
        background: linear-gradient(135deg, #b60e3d, #ff4d6d);
        border-radius: 50%;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        box-shadow: 0 8px 16px rgba(182, 14, 61, 0.2);
        border: 4px solid white;
    }

    .badge-letter {
        font-family: 'Montserrat', sans-serif;
        font-weight: 900;
        font-size: 24px;
    }

    .header-divider {
        display: flex;
        align-items: center;
        gap: 12px;
        opacity: 0.4;
    }

    .divider-line {
        height: 1px;
        flex: 1;
        background: repeating-linear-gradient(to right, #b60e3d 0, #b60e3d 4px, transparent 4px, transparent 8px);
    }

    .divider-text {
        font-size: 10px;
        font-weight: 800;
        letter-spacing: 0.2em;
        color: #b60e3d;
        white-space: nowrap;
    }

    .receipt-brand {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        color: #191c1d;
        margin: 0;
        letter-spacing: -0.01em;
    }

    .receipt-contact {
        font-size: 10.5px;
        color: #5a4042;
    }

    .receipt-contact p {
        margin: 2px 0;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
    }

    .footer-seal {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 15px;
        opacity: 0.3;
    }

    .seal-line {
        height: 1px;
        width: 40px;
        background: #2b2d2f;
    }

    .receipt-metadata {
        background-color: rgba(245, 244, 237, 0.4);
        border: 1px solid #e8e7df;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #444648;
    }

    .meta-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .meta-row:last-child {
        margin-bottom: 0;
    }

    .meta-label {
        opacity: 0.8;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.05em;
    }

    .meta-value {
        font-weight: 700;
        color: #191c1d;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .meta-value.highlight {
        color: #b60e3d;
        font-family: monospace;
        font-size: 14px;
        letter-spacing: 0.05em;
    }

    .meta-value .material-symbols-outlined {
        font-size: 14px;
        color: #b60e3d;
    }

    .receipt-items {
        margin-bottom: 20px;
    }

    .items-header {
        display: flex;
        justify-content: space-between;
        font-size: 10px;
        font-weight: 800;
        color: #5a4042;
        opacity: 0.7;
        text-transform: uppercase;
        letter-spacing: 0.1em;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8e7df;
        margin-bottom: 14px;
    }

    .items-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .item-row {
        display: flex;
        flex-direction: column;
        font-size: 12px;
        padding-bottom: 12px;
        border-bottom: 1px dashed rgba(226, 190, 192, 0.3);
    }

    .item-row:last-child {
        border-bottom: none;
        padding-bottom: 0;
    }

    .item-main-row {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
    }

    .item-name {
        font-weight: 700;
        color: #191c1d;
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 70%;
    }

    .dot-leader {
        border-bottom: 2px dotted #dfded4;
        flex-grow: 1;
        margin: 0 6px;
        position: relative;
        top: -4px;
    }

    .item-subtotal {
        font-weight: 800;
        color: #191c1d;
        font-size: 14px;
    }

    .item-sub-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 4px;
        font-size: 11px;
        color: #5a4042;
        opacity: 0.8;
    }

    .item-qty-price {
        font-weight: 600;
        color: #b60e3d;
        background-color: rgba(182, 14, 61, 0.05);
        padding: 2px 8px;
        border-radius: 4px;
    }

    .receipt-summary {
        background-color: #fcfbf7;
        border: 1px solid #e8e7df;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #444648;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .summary-label {
        opacity: 0.8;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.05em;
    }

    .summary-value {
        font-weight: 700;
        color: #191c1d;
    }

    .summary-value.discount {
        color: #ba1a1a;
        background-color: rgba(250, 26, 26, 0.05);
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 10px;
    }

    .total-due-row {
        border-top: 1px dashed #e8e7df;
        padding-top: 14px;
        margin-top: 12px;
        margin-bottom: 0;
    }

    .total-due-row .summary-label {
        font-size: 12px;
        font-weight: 800;
    }

    .total-due-row .total-amount {
        color: #b60e3d;
        font-size: 20px;
        font-weight: 900;
        letter-spacing: 0.02em;
    }

    .receipt-payment {
        background-color: #fcfbf7;
        border: 1px solid #e8e7df;
        border-radius: 16px;
        padding: 16px;
        margin-bottom: 20px;
        font-size: 12px;
        color: #444648;
    }

    .payment-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
    }

    .payment-row:last-of-type {
        margin-bottom: 0;
    }

    .payment-label {
        opacity: 0.8;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 10px;
        letter-spacing: 0.05em;
    }

    .payment-value {
        font-weight: 800;
        color: #191c1d;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .payment-value .material-symbols-outlined {
        font-size: 16px;
        color: #b60e3d;
    }

    .change-banner {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border-radius: 12px;
        padding: 12px 16px;
        border: 1px solid;
        margin-top: 12px;
    }

    .change-banner.theme-cash {
        background-color: rgba(16, 185, 129, 0.05);
        border-color: rgba(16, 185, 129, 0.2);
        color: #065f46;
    }

    .change-banner.theme-gcash {
        background-color: rgba(59, 130, 246, 0.05);
        border-color: rgba(59, 130, 246, 0.2);
        color: #1e40af;
    }

    .change-banner.theme-card {
        background-color: rgba(147, 51, 234, 0.05);
        border-color: rgba(147, 51, 234, 0.2);
        color: #6b21a8;
    }

    .change-label {
        font-weight: 800;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .change-label .material-symbols-outlined {
        font-size: 16px;
        font-weight: 700;
    }

    .change-amount {
        font-weight: 900;
        font-size: 18px;
    }

    .receipt-barcode {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        margin: 24px 0;
        padding: 10px;
        background-color: #ffffff;
        border: 1px solid rgba(232, 231, 223, 0.4);
        border-radius: 16px;
    }

    .barcode-lines {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 1.5px;
        opacity: 0.8;
        height: 32px;
        padding: 0 16px;
    }

    .barcode-lines .line {
        height: 100%;
        background-color: #191c1d;
    }

    .barcode-lines .line.thin {
        width: 1px;
    }

    .barcode-lines .line.medium {
        width: 2px;
    }

    .barcode-lines .line.thick {
        width: 3px;
    }

    .barcode-lines .line.extra-thick {
        width: 4px;
    }

    .barcode-lines .space {
        height: 100%;
        background-color: transparent;
    }

    .barcode-lines .space.thin {
        width: 1px;
    }

    .barcode-lines .space.medium {
        width: 2px;
    }

    .barcode-lines .space.thick {
        width: 3px;
    }

    .barcode-text {
        font-family: monospace;
        font-size: 9px;
        letter-spacing: 6px;
        color: #5a4042;
        font-weight: 700;
        margin-top: 6px;
        opacity: 0.8;
        text-transform: uppercase;
    }

    .receipt-footer {
        text-align: center;
        color: #5a4042;
        opacity: 0.85;
        margin-top: 16px;
    }

    .footer-stars {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        margin-bottom: 6px;
    }

    .footer-stars .star {
        font-size: 12px;
        color: #b60e3d;
    }

    .footer-thanks {
        font-family: 'Montserrat', sans-serif;
        font-weight: 800;
        font-size: 12px;
        color: #191c1d;
        margin: 0;
        letter-spacing: 0.05em;
        text-transform: uppercase;
    }

    .footer-tagline {
        font-size: 10px;
        font-weight: 600;
        margin: 0;
        opacity: 0.75;
    }

    /* Premium DataTables Styling overrides to match Stacey Halu Halu */
    .dataTables_wrapper {
        padding: 0 !important;
        font-family: 'Inter', sans-serif !important;
    }

    .dataTables_wrapper .dataTables_length {
        margin-bottom: 1.5rem !important;
        font-size: 13px !important;
        color: #555 !important;
    }

    .dataTables_wrapper .dataTables_filter {
        display: none !important;
        /* Hide default search bar, we use the custom one */
    }

    .dataTables_wrapper .dataTables_length select {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        padding: 4px 24px 4px 8px !important;
        margin: 0 4px !important;
        background-color: white !important;
        outline: none !important;
    }

    .dataTables_wrapper .dataTables_info {
        padding-top: 1.5rem !important;
        font-size: 13px !important;
        color: #666 !important;
    }

    .dataTables_wrapper .dataTables_paginate {
        padding-top: 1.5rem !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        border-radius: 8px !important;
        border: 1px solid #dee2e6 !important;
        background: white !important;
        color: #333 !important;
        padding: 5px 12px !important;
        margin: 0 2px !important;
        transition: all 0.2s !important;
        font-weight: 500 !important;
        cursor: pointer !important;
        display: inline-block !important;
        text-decoration: none !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.current,
    .dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
        background: #b60e3d !important;
        color: white !important;
        border-color: #b60e3d !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
        background: #e21c50 !important;
        color: white !important;
        border-color: #e21c50 !important;
    }

    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:hover,
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled:active {
        opacity: 0.5 !important;
        color: #888 !important;
        background: #f8f9fa !important;
        border-color: #dee2e6 !important;
        cursor: not-allowed !important;
    }

    table.dataTable {
        border-collapse: collapse !important;
        width: 100% !important;
        margin: 12px 0 !important;
        border: none !important;
    }

    table.dataTable thead th {
        background-color: #f8f9fa !important;
        color: #2b2d42 !important;
        font-family: 'Outfit', sans-serif !important;
        font-weight: 600 !important;
        text-transform: uppercase !important;
        font-size: 11px !important;
        letter-spacing: 0.05em !important;
        border-bottom: 2px solid #eee !important;
        padding: 12px 16px !important;
    }

    table.dataTable tbody tr {
        background-color: transparent !important;
        cursor: pointer !important;
    }

    table.dataTable tbody tr:hover {
        background-color: #fcf8f9 !important;
    }

    table.dataTable tbody td {
        border-bottom: 1px solid #eee !important;
        padding: 14px 16px !important;
        font-size: 14px !important;
        color: #444 !important;
    }

    /* Stats card transitions and Glassmorphism */
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

    /* Print Styles */
    @media print {

        #wrapper,
        #admin-topbar,
        .navbar,
        aside,
        footer,
        .d-print-none,
        .dataTables_length,
        .dataTables_info,
        .dataTables_paginate,
        .card-filter {
            display: none !important;
        }

        #page-content-wrapper {
            margin: 0 !important;
            padding: 0 !important;
        }

        main {
            margin: 0 !important;
            padding: 0 !important;
        }

        .print-only {
            display: block !important;
        }

        table {
            width: 100% !important;
            border-collapse: collapse !important;
        }

        th,
        td {
            border: 1px solid #ddd !important;
            padding: 8px !important;
            font-size: 12px !important;
        }
    }
</style>

<!-- Print Header (Hidden on screen) -->
<div class="hidden print-only mb-4 text-center">
    <h3 class="fw-bold text-primary mb-1">Stacey Halu Halu</h3>
    <h5 class="text-secondary mb-1">Daily Sales Transactions Report</h5>
    <p class="small text-muted">Generated by <?= htmlspecialchars($_SESSION['user']['name'] ?? 'System') ?> on <?= date('M d, Y h:i A') ?></p>
</div>

<!-- Row 1: Stats Summary Cards -->
<div class="row g-3 mb-4 d-print-none">
    <!-- Card 1: Daily Revenue -->
    <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light stats-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Daily Revenue</p>
                    <h3 class="fw-bold outfit-font text-primary mb-0">₱<?= number_format($today_stats['revenue'], 2) ?></h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 shadow-sm">
                    <span class="material-symbols-outlined" style="font-size: 28px;">payments</span>
                </div>
            </div>
            <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-clock-rotate-left"></i> Today's total sales</p>
        </div>
    </div>

    <!-- Card 2: Total Orders -->
    <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="200">
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light stats-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Total Orders</p>
                    <h3 class="fw-bold outfit-font text-secondary mb-0"><?= $today_stats['orders'] ?></h3>
                </div>
                <div class="p-3 rounded-4 shadow-sm" style="color: #8429c8; background-color: rgba(132, 41, 200, 0.1);">
                    <span class="material-symbols-outlined" style="font-size: 28px;">shopping_basket</span>
                </div>
            </div>
            <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-circle-check"></i> Completed & pending orders</p>
        </div>
    </div>

    <!-- Card 3: Refund Rate -->
    <div class="col-12 col-md-4" data-aos="fade-up" data-aos-delay="300">
        <div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light stats-card h-100">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <p class="text-muted small fw-bold text-uppercase mb-1">Refund Rate</p>
                    <h3 class="fw-bold outfit-font text-danger mb-0"><?= $today_stats['refund_rate'] ?>%</h3>
                </div>
                <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-4 shadow-sm">
                    <span class="material-symbols-outlined" style="font-size: 28px;">undo</span>
                </div>
            </div>
            <p class="text-muted small mt-3 mb-0"><i class="fa-solid fa-ban"></i> Today's cancelled / refunded</p>
        </div>
    </div>
</div>

<!-- Row 2: Advanced Filters (Horizontal Row below Stats Cards) -->
<div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light mb-4 card-filter d-print-none" data-aos="fade-in" data-aos-delay="400">
    <h6 class="fw-bold outfit-font text-secondary mb-3"><i class="fa-solid fa-sliders me-2"></i>Advanced Filters</h6>
    <form id="filter-form" action="<?= url() ?>" method="GET" class="row g-3 align-items-end">
        <input type="hidden" name="route" value="pos/history" />

        <!-- Date From -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted fw-semibold">Date From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>" class="form-control rounded-3 border-light-subtle shadow-none" />
        </div>

        <!-- Date To -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted fw-semibold">Date To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>" class="form-control rounded-3 border-light-subtle shadow-none" />
        </div>

        <!-- Sales Channel -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted fw-semibold">Sales Channel</label>
            <select name="channels[]" class="form-select rounded-3 border-light-subtle shadow-none">
                <option value="">All Channels</option>
                <option value="POS Terminal" <?= in_array('POS Terminal', $filters['channels']) ? 'selected' : '' ?>>POS Terminal</option>
                <option value="Online Store" <?= in_array('Online Store', $filters['channels']) ? 'selected' : '' ?>>Online Store</option>
            </select>
        </div>

        <!-- Payment Status -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted fw-semibold">Payment Status</label>
            <select name="payment_status" class="form-select rounded-3 border-light-subtle shadow-none">
                <option value="All" <?= $filters['payment_status'] === 'All' ? 'selected' : '' ?>>All Statuses</option>
                <option value="Paid" <?= $filters['payment_status'] === 'Paid' ? 'selected' : '' ?>>Paid</option>
                <option value="Pending" <?= $filters['payment_status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                <option value="Refunded" <?= $filters['payment_status'] === 'Refunded' ? 'selected' : '' ?>>Refunded</option>
            </select>
        </div>

        <!-- Store Location -->
        <div class="col-12 col-md-2">
            <label class="form-label small text-muted fw-semibold">Store Location</label>
            <select name="locations[]" class="form-select rounded-3 border-light-subtle shadow-none">
                <option value="">All Locations</option>
                <?php foreach ($active_branches as $branch): ?>
                    <?php
                    $selected = in_array($branch['name'], $filters['locations']) ? 'selected' : '';
                    ?>
                    <option value="<?= htmlspecialchars($branch['name']) ?>" <?= $selected ?>><?= htmlspecialchars($branch['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Action Buttons -->
        <div class="col-12 col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary rounded-3 w-100 fw-semibold">
                Apply
            </button>
            <a href="<?= url('?route=pos/history') ?>" class="btn btn-outline-secondary rounded-3 w-100 fw-semibold text-decoration-none d-flex align-items-center justify-content-center">
                Clear
            </a>
        </div>
    </form>
</div>

<!-- Row 3: Transactions Table -->
<div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light mb-4" data-aos="fade-up" data-aos-delay="500">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h5 class="fw-bold outfit-font text-dark mb-1">Order History</h5>
            <p class="text-muted small mb-0 d-print-none">Review past sales, re-print customer receipts, and verify payment records.</p>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center d-print-none">
            <!-- Real-time Search Box -->
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-search"></i></span>
                <input type="text" id="custom-search" class="form-control border-start-0 ps-0 shadow-none" placeholder="Search cashier, order ID..." />
            </div>

            <?php
            $export_qs = array_merge($_GET, ['export' => 'csv']);
            $export_url = url('?' . http_build_query($export_qs));
            ?>
            <!-- Export CSV -->
            <a href="<?= $export_url ?>" class="btn btn-outline-primary rounded-pill px-3 fw-semibold text-decoration-none d-flex align-items-center gap-2">
                <i class="fa-solid fa-download"></i> Export CSV
            </a>
            <!-- Print Daily -->
            <a href="<?= url('?route=admin/reports/print&type=sales') ?>" target="_blank" id="print-daily-btn" class="btn btn-outline-primary rounded-pill px-4 fw-semibold d-flex align-items-center gap-2 transition-all hover:scale-105 active:scale-95 shadow-sm">
                <i class="fa-solid fa-print"></i> Print
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table id="transactions-table" class="table table-hover align-middle">
            <thead>
                <tr>
                    <th class="py-3 px-4">Order ID</th>
                    <th class="py-3">Date &amp; Time</th>
                    <th class="py-3">Cashier</th>
                    <th class="py-3">Customer</th>
                    <th class="py-3">Channel</th>
                    <th class="py-3">Amount</th>
                    <th class="py-3">Payment</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 px-4 text-end d-print-none">Action</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal" id="historyReceiptModal" tabindex="-1" aria-labelledby="historyReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
        <div class="modal-content border-0 rounded-4 overflow-hidden shadow-lg">
            <div class="modal-header border-0 bg-light py-3 px-4 d-flex align-items-center justify-content-between">
                <h5 class="modal-title fw-bold text-dark outfit-font" id="historyReceiptModalLabel">
                    <i class="fa-solid fa-receipt text-primary me-2"></i>Transaction Receipt
                </h5>
                <button type="button" class="btn-close shadow-none" data-dismiss="receipt-modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" id="historyReceiptModalBody" style="background-color: #f1f3f5;">
                <!-- Content loaded dynamically via AJAX -->
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="text-muted mt-2 small">Loading receipt details...</p>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light py-3 px-4 d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold w-100" data-dismiss="receipt-modal">Close</button>
                <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold w-100 d-flex align-items-center justify-content-center gap-2" id="modalPrintBtn">
                    <i class="fa-solid fa-print"></i> Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out-back',
            once: true
        });

        // Dynamically load DataTables JS after global jQuery has loaded at the bottom of admin.php
        const dtScript = document.createElement('script');
        dtScript.src = 'https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js';
        dtScript.onload = function() {
            // DataTables is loaded and ready
            initializeDataTable();
        };
        document.head.appendChild(dtScript);

        function initializeDataTable() {
            const table = $('#transactions-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "?route=pos/history/data",
                    type: "GET",
                    data: function(d) {
                        // Send advanced filters along with the request
                        d.date_from = $('input[name="date_from"]').val();
                        d.date_to = $('input[name="date_to"]').val();
                        d.payment_status = $('select[name="payment_status"]').val();

                        const channels = $('select[name="channels[]"]').val();
                        if (channels) {
                            d.channels = [channels];
                        }

                        const locations = $('select[name="locations[]"]').val();
                        if (locations) {
                            d.locations = [locations];
                        }
                    }
                },
                columns: [{
                        data: 0,
                        className: "py-3 px-4 fw-bold text-primary"
                    },
                    {
                        data: 1,
                        className: "py-3 text-muted small"
                    },
                    {
                        data: 2,
                        className: "py-3 text-muted"
                    },
                    {
                        data: 3,
                        className: "py-3 fw-medium text-dark"
                    },
                    {
                        data: 4,
                        className: "py-3"
                    },
                    {
                        data: 5,
                        className: "py-3 fw-bold text-dark"
                    },
                    {
                        data: 6,
                        className: "py-3"
                    },
                    {
                        data: 7,
                        className: "py-3"
                    },
                    {
                        data: 8,
                        className: "py-3 px-4 text-end d-print-none",
                        orderable: false
                    }
                ],
                pageLength: 10,
                ordering: true,
                order: [
                    [0, 'desc']
                ], // Sort by order ID desc by default
                language: {
                    lengthMenu: "Show _MENU_ transactions",
                    info: "Showing _START_ to _END_ of _TOTAL_ transactions",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    }
                }
            });

            // Search logic: filter DataTable when typing in custom search input
            const customSearch = $('#custom-search');
            if (customSearch.length) {
                customSearch.on('input', function() {
                    table.search(this.value).draw();
                });

                // Prevent enter key from submitting form
                customSearch.on('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            }

            // Dynamically update the export CSV and Print Report URLs based on current filters
            function updateExportLink() {
                const date_from = $('input[name="date_from"]').val();
                const date_to = $('input[name="date_to"]').val();
                const payment_status = $('select[name="payment_status"]').val();
                const search = customSearch.val() || '';
                const channels = $('select[name="channels[]"]').val();
                const locations = $('select[name="locations[]"]').val();

                const commonParams = {
                    date_from: date_from,
                    date_to: date_to,
                    payment_status: payment_status,
                    search: search
                };

                // Update CSV Export
                const csvParams = new URLSearchParams({
                    route: 'pos/history',
                    export: 'csv',
                    ...commonParams
                });

                if (channels) csvParams.append('channels[]', channels);
                if (locations) csvParams.append('locations[]', locations);
                $('a[href*="export=csv"]').attr('href', '<?= url('?') ?>' + csvParams.toString());

                // Update Print Report
                const printParams = new URLSearchParams({
                    route: 'admin/reports/print',
                    type: 'sales',
                    ...commonParams
                });
                if (channels) printParams.append('channels[]', channels);
                if (locations) printParams.append('locations[]', locations);
                $('#print-daily-btn').attr('href', '<?= url('?') ?>' + printParams.toString());
            }

            // Draw the table when filters change without reloading the page
            $('select[name="payment_status"], select[name="channels[]"], select[name="locations[]"], input[name="date_from"], input[name="date_to"]').on('change', function() {
                table.draw();
            });

            // Prevent form submission and trigger redraw instead
            $('#filter-form').on('submit', function(e) {
                e.preventDefault();
                table.draw();
            });

            // Update CSV link and initialize custom tooltips whenever DataTable draws
            table.on('draw', function() {
                updateExportLink();

                // Dispose existing tooltips to avoid memory leaks
                $('[data-bs-toggle="tooltip"]').each(function() {
                    const inst = bootstrap.Tooltip.getInstance(this);
                    if (inst) inst.dispose();
                });

                // Initialize Bootstrap tooltips on newly rendered rows
                $('[data-bs-toggle="tooltip"]').each(function() {
                    new bootstrap.Tooltip(this, {
                        boundary: document.body,
                        trigger: 'hover'
                    });
                });
            });

            // Function to show/hide the receipt modal manually to avoid Bootstrap backdrop and transitionend bugs
            function showHistoryReceiptModal() {
                const $modal = $('#historyReceiptModal');
                $modal.addClass('show').css({
                    'display': 'block',
                    'padding-right': '0px'
                }).attr('aria-hidden', 'false').attr('aria-modal', 'true').attr('role', 'dialog');

                // Add backdrop if it doesn't exist
                if ($('.modal-backdrop').length === 0) {
                    $('<div class="modal-backdrop show"></div>').appendTo('body');
                }
                $('body').addClass('modal-open').css('overflow', 'hidden');
            }

            function hideHistoryReceiptModal() {
                const $modal = $('#historyReceiptModal');
                $modal.removeClass('show').css('display', 'none').attr('aria-hidden', 'true').removeAttr('aria-modal').removeAttr('role');

                // Remove backdrop and restore body scrolling
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open').css('overflow', '');
            }

            // Bind click handlers to dismiss elements
            $('#historyReceiptModal').on('click', '[data-dismiss="receipt-modal"], .btn-close', function(e) {
                e.preventDefault();
                hideHistoryReceiptModal();
            });

            // Close modal when clicking outside the modal content (backdrop click)
            $('#historyReceiptModal').on('click', function(e) {
                if ($(e.target).is('#historyReceiptModal')) {
                    hideHistoryReceiptModal();
                }
            });

            // Close modal on Escape key press
            $(document).on('keydown', function(e) {
                if (e.key === 'Escape' && $('#historyReceiptModal').hasClass('show')) {
                    hideHistoryReceiptModal();
                }
            });

            // Function to open the receipt modal and load content via AJAX
            function openHistoryReceiptModal(saleId) {
                // Reset body to loader state
                $('#historyReceiptModalBody').html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted mt-2 small">Loading receipt details...</p>
                    </div>
                `);

                // Show modal manually
                showHistoryReceiptModal();

                // Fetch receipt layout with AJAX body_only mode
                $.get('?route=pos/receipt&id=' + saleId + '&ajax=1', function(html) {
                    $('#historyReceiptModalBody').html(html);
                }).fail(function() {
                    $('#historyReceiptModalBody').html(`
                        <div class="text-center py-5 text-danger">
                            <i class="fa-solid fa-triangle-exclamation fs-2 mb-2"></i>
                            <p class="fw-bold mb-1">Failed to Load</p>
                            <p class="text-muted small">Could not retrieve receipt data from server.</p>
                        </div>
                    `);
                });
            }

            // Print Receipt action inside modal
            $('#modalPrintBtn').on('click', function() {
                printModalReceipt();
            });

            // Prevent row redirection when clicking buttons inside action cell
            $('#transactions-table tbody').on('click', '.action-cell, .action-btn', function(e) {
                e.stopPropagation();
            });

            // Delegate click handler for row view modal
            $('#transactions-table tbody').on('click', 'tr', function(e) {
                // Ignore click if it originated from a button, link or cell containing actions
                if ($(e.target).closest('.action-cell, .action-btn').length > 0) {
                    return;
                }
                const saleId = $(this).attr('data-id');
                if (saleId) {
                    openHistoryReceiptModal(saleId);
                }
            });

            // View Receipt button click event
            $('#transactions-table tbody').on('click', '.view-receipt-btn', function(e) {
                e.stopPropagation();
                const saleId = $(this).attr('data-id');
                if (saleId) {
                    openHistoryReceiptModal(saleId);
                }
            });

            // AJAX handler for status updates (Refund / Cancel)
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
                            didOpen: () => {
                                Swal.showLoading();
                            }
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
                                    // Dispose tooltips before redrawing to prevent ghost/stuck tooltips
                                    $('[data-bs-toggle="tooltip"]').each(function() {
                                        const inst = bootstrap.Tooltip.getInstance(this);
                                        if (inst) inst.dispose();
                                    });
                                    // Redraw table in-place (false preserves pagination page)
                                    table.draw(false);
                                });
                            } else {
                                Swal.fire(
                                    'Error!',
                                    response.error || 'Failed to update transaction status.',
                                    'error'
                                );
                            }
                        }, 'json').fail(function() {
                            Swal.fire(
                                'Error!',
                                'Server error encountered while updating transaction status.',
                                'error'
                            );
                        });
                    }
                });
            }

            // Refund Button click event
            $('#transactions-table tbody').on('click', '.refund-btn', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                updateTransactionStatus(id, 'Refunded', 'refund', '#dc3545');
            });

            // Cancel Button click event
            $('#transactions-table tbody').on('click', '.cancel-btn', function(e) {
                e.stopPropagation();
                const id = $(this).attr('data-id');
                updateTransactionStatus(id, 'Cancelled', 'cancel', '#ffc107');
            });
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>