<?php
if (!isset($sale)) {
    require_once __DIR__ . '/../../models/Sale.php';
    require_once __DIR__ . '/../../models/Setting.php';
    $sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $sale = Sale::find_sale_by_id($sale_id);
    
    if (!$sale) {
        die("Transaction not found. Please access this page through the Sales History module.");
    }
}

// Fetch store settings
$general_settings = Setting::get_by_category('general');
$store_name = $general_settings['store_name'] ?? Setting::get('store_name', 'Stacey Halo Halo');
$store_address = $general_settings['store_address'] ?? Setting::get('store_address', '123 Sweet Tooth Blvd, Sugar City');
$store_phone = $general_settings['store_phone'] ?? Setting::get('store_phone', '+63 912 345 6789');
$store_email = $general_settings['store_email'] ?? Setting::get('store_email', 'contact@staceyhalu.com');
$store_logo = $general_settings['store_logo'] ?? Setting::get('store_logo', '');

// Pre-calculate receipt data
$receipt_number = str_pad($sale['id'], 6, '0', STR_PAD_LEFT);
$barcode_value = 'SHH-' . $receipt_number;
$formatted_date = date('M d, Y h:i A', strtotime($sale['created_at']));
$short_date = date('m/d/y h:iA', strtotime($sale['created_at']));
$subtotal_val = (float)($sale['total_amount'] ?? 0);
$discount_val = (float)($sale['discount_amount'] ?? 0);
$final_val = (float)($sale['final_amount'] ?? 0);
$tendered_val = (float)($sale['amount_tendered'] ?? 0);
$change_val = (float)($sale['change_amount'] ?? 0);

// Default format from query parameter ('normal', 'thermal80', 'thermal58')
$initial_format = isset($_GET['format']) ? strtolower(trim($_GET['format'])) : 'thermal80';
if (!in_array($initial_format, ['normal', 'thermal80', 'thermal58'])) {
    $initial_format = 'thermal80';
}
$autoprint = isset($_GET['autoprint']) && $_GET['autoprint'] == '1';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Receipt #<?= $receipt_number ?> - <?= htmlspecialchars($store_name) ?></title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@700;800;900&family=Outfit:wght@500;600;700;800&family=Roboto+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" rel="stylesheet" />

    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- JsBarcode CDN for Crisp Barcodes -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>

    <style>
        :root {
            --primary: #5d87ff;
            --primary-dark: #4570ea;
            --primary-light: #ecf2ff;
            --secondary: #49beff;
            --success: #13deb9;
            --dark: #2a3547;
            --light-bg: #f4f6f9;
            --border-color: #e5eaef;
        }

        *, *::before, *::after {
            box-sizing: border-box !important;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg);
            color: #2a3547;
            margin: 0;
            padding: 0;
            min-height: 100vh;
        }

        /* Top Navigation Header */
        .print-hub-navbar {
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
            padding: 12px 24px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.04);
        }

        /* Format Switcher Pills */
        .format-switcher-container {
            background: #eef2f8;
            padding: 4px;
            border-radius: 12px;
            display: inline-flex;
            gap: 4px;
        }

        .format-btn {
            border: none;
            background: transparent;
            padding: 7px 18px;
            border-radius: 9px;
            font-size: 0.84rem;
            font-weight: 600;
            color: #5a6a85;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .format-btn:hover:not(.active) {
            color: var(--primary);
            background: rgba(255, 255, 255, 0.5);
        }

        .format-btn.active {
            background: #ffffff;
            color: var(--primary);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        }

        .format-btn .badge-pill {
            font-size: 0.68rem;
            padding: 2px 6px;
            border-radius: 6px;
            background: #eef2ff;
            color: var(--primary);
        }

        /* Workspace Canvas */
        .preview-workspace {
            padding: 28px 20px 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: calc(100vh - 75px);
            background: radial-gradient(#dfe5ef 1px, transparent 1px);
            background-size: 20px 20px;
            background-color: #f4f6f9;
        }

        /* Thermal Printing Helper Tip */
        .print-tip-bar {
            max-width: 600px;
            background: #ffffff;
            border: 1px solid #dbeafe;
            border-left: 4px solid #3b82f6;
            border-radius: 8px;
            padding: 10px 16px;
            margin-bottom: 24px;
            font-size: 0.81rem;
            color: #1e3a8a;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.06);
        }

        /* Paper Containers */
        .receipt-paper {
            background: #ffffff;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08), 0 5px 15px rgba(0, 0, 0, 0.04);
            transition: transform 0.2s ease, width 0.2s ease;
            position: relative;
        }

        /* ================================================================
           FORMAT 1: NORMAL A4 / FULL PAGE INVOICE
           ================================================================ */
        .normal-a4-layout {
            width: 100%;
            max-width: 820px;
            border-radius: 16px;
            padding: 48px;
            margin: 0 auto;
            border: 1px solid #e2e8f0;
        }

        .normal-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding-bottom: 24px;
            border-bottom: 2px solid #0f172a;
            margin-bottom: 24px;
        }

        .store-logo-img {
            max-height: 65px;
            width: auto;
            object-fit: contain;
            margin-bottom: 8px;
        }

        .brand-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 1.65rem;
            color: #0f172a;
            letter-spacing: -0.02em;
            margin: 0;
        }

        .invoice-title-tag {
            font-family: 'Montserrat', sans-serif;
            font-size: 1.45rem;
            font-weight: 900;
            color: #0f172a;
            text-align: right;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .invoice-meta-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 28px;
        }

        .meta-col label {
            display: block;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            margin-bottom: 3px;
        }

        .meta-col span {
            font-size: 0.9rem;
            font-weight: 700;
            color: #0f172a;
        }

        .normal-table {
            width: 100%;
            margin-bottom: 24px;
            border-collapse: separate;
            border-spacing: 0;
        }

        .normal-table th {
            background: #f1f5f9;
            color: #334155;
            font-size: 0.75rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 12px 16px;
            border-bottom: 2px solid #cbd5e1;
        }

        .normal-table td {
            padding: 14px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 0.88rem;
            color: #1e293b;
        }

        .normal-table tr:last-child td {
            border-bottom: 2px solid #cbd5e1;
        }

        .normal-summary-box {
            width: 340px;
            margin-left: auto;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 22px;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 0.88rem;
            color: #475569;
        }

        .summary-line.grand-total {
            margin-top: 12px;
            padding-top: 12px;
            border-top: 2px dashed #cbd5e1;
            font-size: 1.2rem;
            font-weight: 800;
            color: #0f172a;
        }

        .summary-line.grand-total .amount {
            color: var(--primary);
            font-family: 'Outfit', sans-serif;
            font-size: 1.35rem;
        }

        /* ================================================================
           FORMAT 2: THERMAL 80MM (FAILSAFE ANTI-CUT ARCHITECTURE)
           ================================================================ */
        .thermal-80-layout {
            width: 68mm;
            max-width: 258px;
            padding: 14px 8px;
            border-radius: 8px;
            margin: 0 auto;
            font-family: 'Roboto Mono', 'Courier New', monospace;
            font-size: 10.5px;
            line-height: 1.4;
            color: #000000;
            border: 1px solid #e2e8f0;
            box-sizing: border-box !important;
            word-break: break-word !important;
        }

        /* Serrated preview edges */
        .serrated-edge {
            height: 6px;
            background-size: 10px 10px;
            background-repeat: repeat-x;
            width: 100%;
            position: absolute;
            left: 0;
            z-index: 5;
        }
        .serrated-top {
            top: 0;
            background-image: linear-gradient(135deg, transparent 33.33%, #ffffff 33.33%, #ffffff 66.67%, transparent 66.67%), linear-gradient(225deg, transparent 33.33%, #ffffff 33.33%, #ffffff 66.67%, transparent 66.67%);
        }
        .serrated-bottom {
            bottom: 0;
            background-image: linear-gradient(315deg, transparent 33.33%, #ffffff 33.33%, #ffffff 66.67%, transparent 66.67%), linear-gradient(45deg, transparent 33.33%, #ffffff 33.33%, #ffffff 66.67%, transparent 66.67%);
        }

        .thermal-divider {
            border-top: 1px dashed #000000;
            margin: 5px 0;
            width: 100%;
        }

        .thermal-double-divider {
            border-top: 2px dashed #000000;
            margin: 5px 0;
            width: 100%;
        }

        /* Robust Table Framework for Thermal Printing (Never Clips Right Content) */
        .thermal-table-wrap {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .thermal-table-wrap td,
        .thermal-table-wrap th {
            padding: 1.5px 0;
            vertical-align: top;
        }

        .thermal-table-wrap td:last-child,
        .thermal-table-wrap th:last-child {
            padding-right: 3px !important;
        }

        /* ================================================================
           FORMAT 3: THERMAL 58MM (FAILSAFE ANTI-CUT MINI ROLL)
           ================================================================ */
        .thermal-58-layout {
            width: 48mm;
            max-width: 182px;
            padding: 12px 6px;
            border-radius: 6px;
            margin: 0 auto;
            font-family: 'Roboto Mono', 'Courier New', monospace;
            font-size: 9px;
            line-height: 1.4;
            color: #000000;
            border: 1px solid #e2e8f0;
            box-sizing: border-box !important;
            word-break: break-word !important;
        }

        .thermal-58-layout .thermal-table-wrap td,
        .thermal-58-layout .thermal-table-wrap th {
            padding: 1.5px 0;
        }

        /* Barcode container */
        .barcode-wrap {
            text-align: center;
            margin: 8px auto 4px;
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }
        .barcode-wrap svg {
            max-width: 85%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        /* Toast notification */
        .toast-bubble {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background: #1e293b;
            color: #ffffff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0;
            transform: translateY(15px);
            transition: all 0.3s ease;
            pointer-events: none;
        }
        .toast-bubble.show {
            opacity: 1;
            transform: translateY(0);
        }

        /* Utility classes */
        .font-mono { font-family: 'Roboto Mono', 'Courier New', monospace !important; }
        .font-outfit { font-family: 'Outfit', sans-serif !important; }

        /* ================================================================
           PRINT MEDIA QUERIES (100% RELIABLE NO-CUT PRINTING)
           ================================================================ */
        @media print {
            body, html {
                background: #ffffff !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                overflow: visible !important;
            }

            .d-print-none,
            .print-hub-navbar,
            .print-tip-bar,
            .toast-bubble,
            .serrated-edge {
                display: none !important;
            }

            .preview-workspace {
                padding: 0 !important;
                background: none !important;
                display: block !important;
                min-height: auto !important;
                margin: 0 !important;
                width: 100% !important;
            }

            .receipt-paper {
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
            }

            /* Normal A4 Layout */
            body.print-format-normal .format-view-thermal80,
            body.print-format-normal .format-view-thermal58 {
                display: none !important;
            }
            body.print-format-normal .format-view-normal {
                display: block !important;
                padding: 0 !important;
                max-width: 100% !important;
                margin: 0 auto !important;
            }

            /* Thermal 80mm Print Layout
               - Left-aligned (thermal printers feed from left edge)
               - 64mm content = safe within 72mm print head even with browser default margins
               - font-size in px for consistent 1:1 print rendering
            */
            body.print-format-thermal80 .format-view-normal,
            body.print-format-thermal80 .format-view-thermal58 {
                display: none !important;
            }
            body.print-format-thermal80 .format-view-thermal80 {
                display: block !important;
                width: 58mm !important;
                max-width: 58mm !important;
                padding: 2mm 5mm 2mm 1.5mm !important;
                margin: 0 !important;
                margin-left: 0 !important;
                margin-right: 6mm !important;
                box-sizing: border-box !important;
                overflow: visible !important;
                font-size: 10.5px !important;
                line-height: 1.4 !important;
            }

            /* Thermal 58mm Print Layout
               - Left-aligned always
               - 40mm with 4mm right safety margin prevents any clipping
            */
            body.print-format-thermal58 .format-view-normal,
            body.print-format-thermal58 .format-view-thermal80 {
                display: none !important;
            }
            body.print-format-thermal58 .format-view-thermal58 {
                display: block !important;
                width: 40mm !important;
                max-width: 40mm !important;
                padding: 1.5mm 4mm 1.5mm 1mm !important;
                margin: 0 !important;
                margin-left: 0 !important;
                margin-right: 4mm !important;
                box-sizing: border-box !important;
                overflow: visible !important;
                font-size: 9px !important;
                line-height: 1.4 !important;
            }
        }
    </style>
    
    <!-- Dynamic Print Page Size Override Tag -->
    <style id="dynamic-print-page-style">
        @media print {
            @page {
                size: <?= $initial_format === 'normal' ? 'portrait' : 'auto' ?>;
                margin: <?= $initial_format === 'normal' ? '12mm' : '0mm' ?> !important;
            }
        }
    </style>
    <!-- Dynamic Print Width Override Tag -->
    <style id="thermal-custom-width-style"></style>
<link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">
</head>

<body class="print-format-<?= htmlspecialchars($initial_format) ?>">

    <!-- ================================================================
         TOP NAVIGATION BAR (ACTIONS, FORMAT SWITCHER, PREVIEW CONTROLS)
         ================================================================ -->
    <header class="print-hub-navbar d-print-none">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            
            <!-- Left: Back Navigation & Meta Pill -->
            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('?route=pos/history') ?>" class="btn btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-2" style="font-size:0.83rem;">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Back to History</span>
                </a>
                
                <div class="d-none d-md-flex align-items-center gap-2 border-start ps-3">
                    <span class="badge bg-primary-subtle text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 fw-bold font-mono" style="font-size:0.82rem;">
                        #HH-<?= $receipt_number ?>
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.75rem;">
                        <i class="fa-solid fa-check-circle me-1"></i> <?= htmlspecialchars($sale['status'] ?? 'Completed') ?>
                    </span>
                    <span class="text-muted small d-none d-lg-inline">
                        <i class="fa-regular fa-clock me-1"></i> <?= $formatted_date ?>
                    </span>
                </div>
            </div>

            <!-- Center: Format Selector -->
            <div class="d-flex align-items-center">
                <div class="format-switcher-container" role="tablist">
                    <button type="button" class="format-btn <?= $initial_format === 'normal' ? 'active' : '' ?>" data-format="normal" onclick="switchPrintFormat('normal')">
                        <i class="fa-regular fa-file-lines"></i>
                        <span>Normal Print</span>
                        <span class="badge-pill">A4 / Invoice</span>
                    </button>
                    
                    <button type="button" class="format-btn <?= $initial_format === 'thermal80' ? 'active' : '' ?>" data-format="thermal80" onclick="switchPrintFormat('thermal80')">
                        <i class="fa-solid fa-receipt"></i>
                        <span>Thermal 80mm</span>
                        <span class="badge-pill">Standard POS</span>
                    </button>

                    <button type="button" class="format-btn <?= $initial_format === 'thermal58' ? 'active' : '' ?>" data-format="thermal58" onclick="switchPrintFormat('thermal58')">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                        <span>Thermal 58mm</span>
                        <span class="badge-pill">Mini POS</span>
                    </button>
                </div>
            </div>

            <!-- Right: Action Buttons & Print Trigger -->
            <div class="d-flex align-items-center gap-2">
                <!-- Dropdown: Options / Toggle Elements -->
                <div class="dropdown">
                    <button class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size:0.83rem;color:#475569;">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Options</span>
                        <i class="fa-solid fa-chevron-down ms-1" style="font-size:0.65rem;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 rounded-3 p-3" style="min-width: 270px; font-size: 0.85rem;">
                        <li class="fw-bold text-dark px-2 mb-2 pb-1 border-bottom d-flex align-items-center justify-content-between">
                            <span>Anti-Cut Width Tuning</span>
                            <span class="badge bg-success-subtle text-success small" style="font-size:0.65rem;">Active</span>
                        </li>
                        <li class="px-2 py-1 mb-2">
                            <label class="form-label text-muted small mb-1 fw-semibold">Thermal Paper Buffer:</label>
                            <select class="form-select form-select-sm" id="thermalWidthSelect" onchange="adjustThermalWidth(this.value)">
                                <option value="safe" selected>Ultra-Safe Anti-Cut (60mm / 42mm)</option>
                                <option value="narrow">Extra Narrow Buffer (56mm / 38mm)</option>
                                <option value="standard">Standard Width (66mm / 46mm)</option>
                                <option value="full">Full Width (70mm / 48mm)</option>
                            </select>
                            <div class="form-text text-muted" style="font-size:0.7rem;line-height:1.2;margin-top:4px;">
                                Prevents right side numbers and borders from being clipped on thermal roll paper.
                            </div>
                        </li>
                        <li class="px-2 py-1 mb-2">
                            <label class="form-label text-muted small mb-1 fw-semibold">Paper Feed Alignment:</label>
                            <select class="form-select form-select-sm" id="thermalAlignSelect" onchange="adjustThermalAlignment(this.value)">
                                <option value="center" selected>Centered on Roll (Default)</option>
                                <option value="left">Left-Edge Aligned</option>
                            </select>
                        </li>
                        <li class="fw-bold text-dark px-2 mb-2 pb-1 border-bottom">Display Elements</li>
                        <li class="px-2 py-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="toggle-logo" checked onchange="toggleReceiptElement('.store-logo-box', this.checked)">
                                <label class="form-check-label" for="toggle-logo">Store Logo / Badge</label>
                            </div>
                        </li>
                        <li class="px-2 py-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="toggle-barcode" checked onchange="toggleReceiptElement('.barcode-wrap', this.checked)">
                                <label class="form-check-label" for="toggle-barcode">Scannable Barcode</label>
                            </div>
                        </li>
                        <li class="px-2 py-1">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="toggle-copy-type" onchange="toggleMerchantCopy(this.checked)">
                                <label class="form-check-label" for="toggle-copy-type">Merchant Copy Tag</label>
                            </div>
                        </li>
                    </ul>
                </div>

                <!-- Copy Plain Text -->
                <button type="button" onclick="copyReceiptPlainText()" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5" style="font-size:0.83rem;color:#475569;" title="Copy plain text receipt for messaging or records">
                    <i class="fa-regular fa-clipboard"></i>
                    <span class="d-none d-sm-inline">Copy Text</span>
                </button>

                <!-- Primary Print Now Button -->
                <button type="button" onclick="executePrint()" class="btn btn-primary rounded-pill px-4 py-1.5 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" style="font-size:0.85rem;background:linear-gradient(135deg,#5d87ff,#4570ea);">
                    <i class="fa-solid fa-print"></i>
                    <span>Print Now</span>
                    <span class="badge bg-white bg-opacity-25 rounded-pill px-2 py-0.5 fw-semibold d-none d-lg-inline" style="font-size:0.68rem;">Ctrl+P</span>
                </button>

                <!-- New Sale Quick Link -->
                <a href="<?= url('?route=pos') ?>" class="btn btn-light border rounded-pill px-3 py-1.5 fw-semibold d-none d-xl-inline-flex align-items-center gap-1.5" style="font-size:0.83rem;color:#475569;" title="Create new POS transaction">
                    <i class="fa-solid fa-cash-register text-primary"></i>
                    <span>New Sale</span>
                </a>
            </div>

        </div>
    </header>


    <!-- ================================================================
         RECEIPT PREVIEW STAGE
         ================================================================ -->
    <main class="preview-workspace">

        <!-- Thermal Printing Helper Banner -->
        <div class="print-tip-bar d-print-none">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info fs-6 text-primary"></i>
                <div>
                    <strong>Thermal Print Anti-Cut Active:</strong> In the browser print dialog (Ctrl+P), set <strong>Margins: None</strong> and select your thermal roll paper size.
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white small ms-2" onclick="this.closest('.print-tip-bar').remove()" aria-label="Close"></button>
        </div>

        <!-- ============================================================
             1. FORMAT: NORMAL A4 / FULL INVOICE VIEW
             ============================================================ -->
        <div class="receipt-paper normal-a4-layout format-view-normal <?= $initial_format !== 'normal' ? 'd-none' : '' ?>">
            
            <!-- Normal Header -->
            <div class="normal-header">
                <div>
                    <div class="store-logo-box mb-2">
                        <?php if (!empty($store_logo)): ?>
                            <img src="<?= htmlspecialchars($store_logo) ?>" alt="<?= htmlspecialchars($store_name) ?>" class="store-logo-img">
                        <?php else: ?>
                            <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-3 shadow-sm" style="width:48px;height:48px;font-weight:800;font-size:1.3rem;">
                                <?= strtoupper(substr($store_name, 0, 1)) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h1 class="brand-title"><?= htmlspecialchars($store_name) ?></h1>
                    <div class="text-muted small mt-1">
                        <div><?= htmlspecialchars($store_address) ?></div>
                        <div>Tel: <?= htmlspecialchars($store_phone) ?> &bull; <?= htmlspecialchars($store_email) ?></div>
                    </div>
                </div>

                <div class="text-end">
                    <div class="invoice-title-tag">Sales Invoice</div>
                    <div class="fw-bold text-dark fs-6 mt-1 font-mono">INV-#<?= $receipt_number ?></div>
                    <div class="mt-2">
                        <span class="badge bg-success-subtle text-success border border-success border-opacity-30 rounded-pill px-3 py-1 fw-bold" style="font-size:0.75rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> OFFICIAL RECEIPT
                        </span>
                    </div>
                    <div class="merchant-copy-tag text-muted fw-bold small text-uppercase mt-1" style="display:none;font-size:0.7rem;letter-spacing:0.05em;">
                        [ Merchant Copy ]
                    </div>
                </div>
            </div>

            <!-- Invoice Metadata Grid -->
            <div class="invoice-meta-grid">
                <div class="meta-col">
                    <label>Transaction Date</label>
                    <span><?= $formatted_date ?></span>
                </div>
                <div class="meta-col">
                    <label>Branch / Station</label>
                    <span><?= htmlspecialchars($sale['branch_name'] ?? 'Main Branch') ?></span>
                </div>
                <div class="meta-col">
                    <label>Cashier / Staff</label>
                    <span><?= htmlspecialchars($sale['cashier_name'] ?? 'Authorized Staff') ?></span>
                </div>
                <div class="meta-col">
                    <label>Order Type / Channel</label>
                    <span><?= htmlspecialchars($sale['order_type'] ?? 'Dine-in') ?></span>
                </div>
            </div>

            <!-- Itemized Table -->
            <table class="normal-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 55%;">Item & Customizations</th>
                        <th class="text-end" style="width: 15%;">Unit Price</th>
                        <th class="text-center" style="width: 10%;">Qty</th>
                        <th class="text-end" style="width: 15%;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $item_idx = 1;
                    foreach ($sale['items'] as $item): 
                    ?>
                        <tr>
                            <td class="text-muted fw-semibold"><?= $item_idx++ ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($item['product_name']) ?></div>
                                <?php if (!empty($item['modifiers'])): ?>
                                    <div class="mt-1" style="padding-left: 10px;">
                                        <?php foreach ($item['modifiers'] as $mod): ?>
                                            <div class="text-muted small">
                                                <i class="fa-solid fa-angle-right text-primary me-1" style="font-size: 0.65rem;"></i>
                                                <?= htmlspecialchars($mod['modifier_name']) ?>
                                                <?php if ((float)($mod['additional_price'] ?? 0) > 0): ?>
                                                    <span class="text-dark fw-semibold">(+₱<?= number_format((float)$mod['additional_price'], 2) ?>)</span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end font-mono">₱<?= number_format($item['price'], 2) ?></td>
                            <td class="text-center fw-bold font-mono"><?= $item['quantity'] ?></td>
                            <td class="text-end fw-bold text-dark font-mono">₱<?= number_format($item['subtotal'], 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- Financial Summary & Signatures -->
            <div class="row align-items-end mt-4">
                <div class="col-6">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="small fw-bold text-uppercase text-muted mb-1" style="font-size:0.7rem;">Payment Information</div>
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Method:</span>
                            <span class="fw-bold text-dark"><?= htmlspecialchars($sale['payment_method']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small mb-1">
                            <span>Amount Tendered:</span>
                            <span class="fw-bold text-dark font-mono">₱<?= number_format($tendered_val, 2) ?></span>
                        </div>
                        <div class="d-flex justify-content-between text-muted small">
                            <span>Change Paid:</span>
                            <span class="fw-bold text-success font-mono">₱<?= number_format($change_val, 2) ?></span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 text-muted small">
                        <p class="mb-0 fw-semibold text-dark" style="font-size:0.78rem;">Notice & Policy:</p>
                        <p class="mb-0 text-muted" style="font-size:0.74rem;">Thank you for your visit! Please keep this receipt. No exchange or returns without an official receipt within 24 hours.</p>
                    </div>
                </div>

                <div class="col-6">
                    <div class="normal-summary-box">
                        <div class="summary-line">
                            <span>Subtotal Amount:</span>
                            <span class="font-mono">₱<?= number_format($subtotal_val, 2) ?></span>
                        </div>
                        <div class="summary-line">
                            <span>Discounts Applied:</span>
                            <span class="font-mono text-danger">- ₱<?= number_format($discount_val, 2) ?></span>
                        </div>
                        <div class="summary-line grand-total">
                            <span>Total Due:</span>
                            <span class="amount font-mono">₱<?= number_format($final_val, 2) ?></span>
                        </div>
                    </div>

                    <!-- Barcode Display -->
                    <div class="barcode-wrap mt-4 text-end">
                        <svg class="barcode-svg" id="barcode-normal"></svg>
                        <div class="small text-muted font-mono" style="font-size:0.7rem;letter-spacing:0.1em;">*<?= $barcode_value ?>*</div>
                    </div>
                </div>
            </div>

            <!-- Footer Signatures -->
            <div class="row mt-5 pt-4 border-top">
                <div class="col-6 text-center">
                    <div class="mx-auto border-bottom border-dark border-opacity-25 mb-1" style="max-width: 220px; height: 35px;"></div>
                    <span class="text-muted small text-uppercase" style="font-size:0.68rem;letter-spacing:0.05em;">Cashier Signature</span>
                </div>
                <div class="col-6 text-center">
                    <div class="mx-auto border-bottom border-dark border-opacity-25 mb-1" style="max-width: 220px; height: 35px;"></div>
                    <span class="text-muted small text-uppercase" style="font-size:0.68rem;letter-spacing:0.05em;">Customer Signature</span>
                </div>
            </div>

        </div>


        <!-- ============================================================
             2. FORMAT: THERMAL 80MM (FAILSAFE ANTI-CUT ARCHITECTURE)
             ============================================================ -->
        <div class="receipt-paper thermal-80-layout format-view-thermal80 <?= $initial_format !== 'thermal80' ? 'd-none' : '' ?>">
            <div class="serrated-edge serrated-top"></div>
            
            <!-- Store Header -->
            <div class="text-center pt-1 mb-1">
                <div class="store-logo-box mb-1">
                    <?php if (!empty($store_logo)): ?>
                        <img src="<?= htmlspecialchars($store_logo) ?>" alt="<?= htmlspecialchars($store_name) ?>" style="max-height: 32px; width: auto;">
                    <?php else: ?>
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle" style="width:26px;height:26px;font-weight:900;font-size:11px;">
                            <?= strtoupper(substr($store_name, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fw-bold text-uppercase" style="font-size: 13px; letter-spacing: 0.02em;"><?= htmlspecialchars($store_name) ?></div>
                <div style="font-size: 10px; line-height: 1.35;" class="mt-1">
                    <?= htmlspecialchars($store_address) ?><br>
                    Tel: <?= htmlspecialchars($store_phone) ?>
                </div>
                <div class="fw-bold mt-1 text-uppercase" style="font-size: 10px; letter-spacing: 0.06em;">*** OFFICIAL RECEIPT ***</div>
                <div class="merchant-copy-tag fw-bold text-uppercase mt-1" style="display:none;font-size:9px;">[ Merchant Copy ]</div>
            </div>

            <div class="thermal-divider"></div>

            <!-- Receipt Metadata (HTML Table to Guarantee Fixed Alignment) -->
            <table class="thermal-table-wrap" style="font-size: 10.5px; line-height: 1.4;">
                <tr>
                    <td style="color: #333; width: 42%;">RCPT:</td>
                    <td style="text-align: right; font-weight: 700; white-space: nowrap; width: 58%;">#<?= $receipt_number ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">DATE:</td>
                    <td style="text-align: right; white-space: nowrap;"><?= $short_date ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">STAFF:</td>
                    <td style="text-align: right; white-space: nowrap;"><?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">BRANCH:</td>
                    <td style="text-align: right; white-space: nowrap;"><?= htmlspecialchars($sale['branch_name'] ?? 'Main') ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">TYPE:</td>
                    <td style="text-align: right; font-weight: 700; text-transform: uppercase; white-space: nowrap;"><?= htmlspecialchars($sale['order_type'] ?? 'Dine-in') ?></td>
                </tr>
            </table>

            <div class="thermal-double-divider"></div>

            <!-- Items Table (Table-Layout Fixed Prevents Right-Edge Cutoff) -->
            <table class="thermal-table-wrap mb-1" style="font-size: 10.5px;">
                <thead>
                    <tr style="border-bottom: 1px dashed #000; text-transform: uppercase; font-size: 9.5px;">
                        <th style="text-align: left; padding: 2px 0 4px; font-weight: 700;">ITEM / QTY</th>
                        <th style="text-align: right; padding: 2px 0 4px; font-weight: 700; width: 78px; white-space: nowrap;">AMT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale['items'] as $item): ?>
                        <tr>
                            <td style="padding: 3px 4px 2px 0; word-break: break-word;">
                                <div style="font-weight: 700; line-height: 1.25;"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div style="font-size: 9px; color: #444; margin-top: 1px;">
                                    <?= $item['quantity'] ?> x &#x20B1;<?= number_format($item['price'], 2) ?>
                                </div>
                                <?php if (!empty($item['modifiers'])): ?>
                                    <?php foreach ($item['modifiers'] as $mod): ?>
                                        <div style="font-size: 8.5px; color: #555; padding-left: 6px; line-height: 1.2;">
                                            + <?= htmlspecialchars($mod['modifier_name']) ?>
                                            <?php if ((float)($mod['additional_price'] ?? 0) > 0): ?>
                                                (&#x20B1;<?= number_format((float)$mod['additional_price'], 2) ?>)
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; vertical-align: top; padding: 3px 0 2px; font-weight: 700; font-family: 'Roboto Mono', monospace; font-size: 10px; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">
                                &#x20B1;<?= number_format($item['subtotal'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="thermal-divider"></div>

            <!-- Totals (HTML Table Guaranteeing Visible Subtotals and Totals) -->
            <table class="thermal-table-wrap" style="font-size: 10px; line-height: 1.45;">
                <tr>
                    <td style="color: #333;">Subtotal:</td>
                    <td style="text-align: right; font-family: 'Roboto Mono', monospace; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($subtotal_val, 2) ?></td>
                </tr>
                <?php if ($discount_val > 0): ?>
                    <tr>
                        <td style="color: #b60e3d;">Discount:</td>
                        <td style="text-align: right; font-family: 'Roboto Mono', monospace; color: #b60e3d; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">-&#x20B1;<?= number_format($discount_val, 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="border-top: 1px dashed #000; border-bottom: 1px dashed #000;">
                    <td style="font-weight: 800; font-size: 11.5px; padding: 3px 0;">TOTAL DUE:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 12px; font-family: 'Roboto Mono', monospace; padding: 3px 0; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($final_val, 2) ?></td>
                </tr>
                <tr>
                    <td style="padding-top: 3px; color: #333;">PAID (<?= htmlspecialchars($sale['payment_method']) ?>):</td>
                    <td style="padding-top: 3px; text-align: right; font-family: 'Roboto Mono', monospace; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($tendered_val, 2) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; font-size: 10.5px;">CHANGE:</td>
                    <td style="text-align: right; font-weight: 700; font-size: 11px; font-family: 'Roboto Mono', monospace; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($change_val, 2) ?></td>
                </tr>
            </table>

            <div class="thermal-double-divider"></div>

            <!-- Barcode -->
            <div class="barcode-wrap">
                <svg class="barcode-svg" id="barcode-thermal80"></svg>
                <div style="font-size: 9px; letter-spacing: 0.07em; margin-top: 2px;">*<?= $barcode_value ?>*</div>
            </div>

            <!-- Footer Message -->
            <div class="text-center mt-2 pt-1" style="font-size: 10px; line-height: 1.4;">
                <div class="fw-bold text-uppercase">Thank You For Your Visit!</div>
                <div>Please come again soon</div>
                <div class="mt-1" style="font-size: 9px; opacity: 0.75;">No return or refund without receipt</div>
                <div class="mt-1 font-mono" style="font-size: 9px;">- - - - - - - - - - - &#x2702;</div>
            </div>

            <div class="serrated-edge serrated-bottom"></div>
        </div>


        <!-- ============================================================
             3. FORMAT: THERMAL 58MM (FAILSAFE ANTI-CUT MINI POS ROLL)
             ============================================================ -->
        <div class="receipt-paper thermal-58-layout format-view-thermal58 <?= $initial_format !== 'thermal58' ? 'd-none' : '' ?>">
            <div class="serrated-edge serrated-top"></div>
            
            <!-- Store Header -->
            <div class="text-center pt-1 mb-1">
                <div class="store-logo-box mb-1">
                    <?php if (!empty($store_logo)): ?>
                        <img src="<?= htmlspecialchars($store_logo) ?>" alt="<?= htmlspecialchars($store_name) ?>" style="max-height: 24px; width: auto;">
                    <?php else: ?>
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded-circle" style="width:20px;height:20px;font-weight:900;font-size:9px;">
                            <?= strtoupper(substr($store_name, 0, 1)) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="fw-bold text-uppercase" style="font-size: 11px; letter-spacing: 0.02em;"><?= htmlspecialchars($store_name) ?></div>
                <div style="font-size: 8.5px; line-height: 1.3;" class="mt-0.5">
                    <?= htmlspecialchars($store_address) ?><br>
                    Tel: <?= htmlspecialchars($store_phone) ?>
                </div>
                <div class="fw-bold mt-1 text-uppercase" style="font-size: 9px;">* OFFICIAL RECEIPT *</div>
                <div class="merchant-copy-tag fw-bold text-uppercase mt-0.5" style="display:none;font-size:8.5px;">[ Merchant Copy ]</div>
            </div>

            <div class="thermal-divider" style="margin: 4px 0;"></div>

            <!-- Receipt Metadata (Table for Clean Columns) -->
            <table class="thermal-table-wrap" style="font-size: 9px; line-height: 1.4;">
                <tr>
                    <td style="color: #333; width: 40%;">RCPT:</td>
                    <td style="text-align: right; font-weight: 700; white-space: nowrap; width: 60%;">#<?= $receipt_number ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">DATE:</td>
                    <td style="text-align: right; white-space: nowrap;"><?= $short_date ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">STAFF:</td>
                    <td style="text-align: right; white-space: nowrap;"><?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?></td>
                </tr>
                <tr>
                    <td style="color: #333;">TYPE:</td>
                    <td style="text-align: right; font-weight: 700; white-space: nowrap;"><?= htmlspecialchars($sale['order_type'] ?? 'Dine-in') ?></td>
                </tr>
            </table>

            <div class="thermal-double-divider" style="margin: 4px 0;"></div>

            <!-- Items List (Table Format Prevents Overflow) -->
            <table class="thermal-table-wrap mb-1" style="font-size: 9px;">
                <thead>
                    <tr style="border-bottom: 1px dashed #000; text-transform: uppercase; font-size: 8.5px;">
                        <th style="text-align: left; padding: 2px 0 3px; font-weight: 700;">ITEM / QTY</th>
                        <th style="text-align: right; padding: 2px 0 3px; font-weight: 700; width: 62px; white-space: nowrap;">AMT</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sale['items'] as $item): ?>
                        <tr>
                            <td style="padding: 3px 3px 1px 0; word-break: break-word;">
                                <div style="font-weight: 700; line-height: 1.2;"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div style="font-size: 8px; color: #444;">
                                    <?= $item['quantity'] ?>x &#x20B1;<?= number_format($item['price'], 2) ?>
                                </div>
                                <?php if (!empty($item['modifiers'])): ?>
                                    <?php foreach ($item['modifiers'] as $mod): ?>
                                        <div style="font-size: 7.5px; color: #555; padding-left: 4px;">
                                            +<?= htmlspecialchars($mod['modifier_name']) ?>
                                            <?php if ((float)($mod['additional_price'] ?? 0) > 0): ?>
                                                (&#x20B1;<?= number_format((float)$mod['additional_price'], 2) ?>)
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: right; vertical-align: top; padding: 3px 0 1px; font-weight: 700; font-family: 'Roboto Mono', monospace; font-size: 8.5px; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">
                                &#x20B1;<?= number_format($item['subtotal'], 2) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <div class="thermal-divider" style="margin: 4px 0;"></div>

            <!-- Totals (Table Format for 58mm) -->
            <table class="thermal-table-wrap" style="font-size: 8.5px; line-height: 1.4;">
                <tr>
                    <td style="color: #333;">Subtotal:</td>
                    <td style="text-align: right; font-family: 'Roboto Mono', monospace; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($subtotal_val, 2) ?></td>
                </tr>
                <?php if ($discount_val > 0): ?>
                    <tr>
                        <td style="color: #b60e3d;">Discount:</td>
                        <td style="text-align: right; font-family: 'Roboto Mono', monospace; color: #b60e3d; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">-&#x20B1;<?= number_format($discount_val, 2) ?></td>
                    </tr>
                <?php endif; ?>
                <tr style="border-top: 1px dashed #000; border-bottom: 1px dashed #000;">
                    <td style="font-weight: 800; font-size: 10px; padding: 2px 0;">TOTAL:</td>
                    <td style="text-align: right; font-weight: 800; font-size: 10.5px; font-family: 'Roboto Mono', monospace; padding: 2px 0; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($final_val, 2) ?></td>
                </tr>
                <tr>
                    <td style="padding-top: 2px; color: #333;">PAID(<?= htmlspecialchars($sale['payment_method']) ?>):</td>
                    <td style="padding-top: 2px; text-align: right; font-family: 'Roboto Mono', monospace; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($tendered_val, 2) ?></td>
                </tr>
                <tr>
                    <td style="font-weight: 700; font-size: 9px;">CHANGE:</td>
                    <td style="text-align: right; font-weight: 700; font-size: 9.5px; font-family: 'Roboto Mono', monospace; letter-spacing: -0.3px; font-variant-numeric: tabular-nums; white-space: nowrap;">&#x20B1;<?= number_format($change_val, 2) ?></td>
                </tr>
            </table>

            <div class="thermal-double-divider" style="margin: 4px 0;"></div>

            <!-- Barcode -->
            <div class="barcode-wrap" style="margin: 4px 0 2px;">
                <svg class="barcode-svg" id="barcode-thermal58"></svg>
                <div style="font-size: 8px; letter-spacing: 0.05em; margin-top: 1px;">*<?= $barcode_value ?>*</div>
            </div>

            <!-- Footer Message -->
            <div class="text-center mt-1" style="font-size: 9px; line-height: 1.3;">
                <div class="fw-bold">THANK YOU!</div>
                <div>Come again soon</div>
                <div class="mt-1 font-mono" style="font-size: 8px;">- - - - - - - &#x2702;</div>
            </div>

            <div class="serrated-edge serrated-bottom"></div>
        </div>

    </main>

    <!-- Floating Toast Notification -->
    <div id="toastNotification" class="toast-bubble">
        <i class="fa-solid fa-circle-check text-success"></i>
        <span id="toastMessage">Action completed successfully</span>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Global receipt information for plain text copying
        const receiptData = {
            storeName: <?= json_encode($store_name) ?>,
            storeAddress: <?= json_encode($store_address) ?>,
            storePhone: <?= json_encode($store_phone) ?>,
            receiptNumber: <?= json_encode($receipt_number) ?>,
            date: <?= json_encode($formatted_date) ?>,
            cashier: <?= json_encode($sale['cashier_name'] ?? 'Staff') ?>,
            branch: <?= json_encode($sale['branch_name'] ?? 'Main Branch') ?>,
            orderType: <?= json_encode($sale['order_type'] ?? 'Dine-in') ?>,
            items: <?= json_encode($sale['items']) ?>,
            subtotal: <?= json_encode(number_format($subtotal_val, 2)) ?>,
            discount: <?= json_encode(number_format($discount_val, 2)) ?>,
            total: <?= json_encode(number_format($final_val, 2)) ?>,
            paymentMethod: <?= json_encode($sale['payment_method']) ?>,
            tendered: <?= json_encode(number_format($tendered_val, 2)) ?>,
            change: <?= json_encode(number_format($change_val, 2)) ?>
        };

        let currentActiveFormat = '<?= $initial_format ?>';

        // Render Barcodes with JsBarcode with fail-safe compact dimensions
        function renderAllBarcodes() {
            const barcodeVal = <?= json_encode($barcode_value) ?>;
            try {
                if (typeof JsBarcode !== 'undefined') {
                    // Normal A4 Barcode
                    if (document.getElementById('barcode-normal')) {
                        JsBarcode('#barcode-normal', barcodeVal, {
                            format: 'CODE128',
                            width: 1.5,
                            height: 38,
                            displayValue: false,
                            margin: 0
                        });
                    }

                    // Thermal 80mm Barcode (width 0.95 to fit safely within 60mm)
                    if (document.getElementById('barcode-thermal80')) {
                        JsBarcode('#barcode-thermal80', barcodeVal, {
                            format: 'CODE128',
                            width: 0.95,
                            height: 26,
                            displayValue: false,
                            margin: 0
                        });
                    }

                    // Thermal 58mm Barcode (width 0.72 to fit safely within 42mm)
                    if (document.getElementById('barcode-thermal58')) {
                        JsBarcode('#barcode-thermal58', barcodeVal, {
                            format: 'CODE128',
                            width: 0.72,
                            height: 20,
                            displayValue: false,
                            margin: 0
                        });
                    }
                }
            } catch (err) {
                console.error('Barcode rendering error:', err);
            }
        }

        // Switch Print Format Function
        function switchPrintFormat(format) {
            currentActiveFormat = format;

            // Update switcher buttons
            document.querySelectorAll('.format-btn').forEach(btn => {
                if (btn.getAttribute('data-format') === format) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            // Update body print class
            document.body.className = document.body.className.replace(/\bprint-format-[^\s]+\b/, 'print-format-' + format);

            // Toggle preview visibility
            document.querySelectorAll('.receipt-paper').forEach(el => el.classList.add('d-none'));
            const activeEl = document.querySelector('.format-view-' + format);
            if (activeEl) {
                activeEl.classList.remove('d-none');
            }

            // Update Dynamic @page Print Size
            const dynamicStyle = document.getElementById('dynamic-print-page-style');
            if (dynamicStyle) {
                let pageSize = 'auto';
                let pageMargin = '0mm';
                if (format === 'normal') {
                    pageSize = 'portrait';
                    pageMargin = '12mm';
                }
                dynamicStyle.innerHTML = `
                    @media print {
                        @page {
                            size: ${pageSize};
                            margin: ${pageMargin} !important;
                        }
                    }
                `;
            }

            // Save preference to localStorage
            try {
                localStorage.setItem('pos_receipt_preferred_format', format);
            } catch(e) {}

            // Update URL without reload
            const newUrl = new URL(window.location.href);
            newUrl.searchParams.set('format', format);
            window.history.replaceState({}, '', newUrl.toString());

            showToast(`Switched to ${format === 'normal' ? 'Normal (A4)' : (format === 'thermal58' ? 'Thermal 58mm' : 'Thermal 80mm')} format`);
        }

        // Anti-Cut Width Mode Adjustment Function
        function adjustThermalWidth(mode) {
            const el80 = document.querySelector('.thermal-80-layout');
            const el58 = document.querySelector('.thermal-58-layout');
            
            let w80 = '60mm';
            let w58 = '42mm';
            let printW80 = '60mm';
            let printW58 = '42mm';

            if (mode === 'narrow') {
                w80 = '56mm';
                w58 = '38mm';
                printW80 = '56mm';
                printW58 = '38mm';
            } else if (mode === 'standard') {
                w80 = '66mm';
                w58 = '46mm';
                printW80 = '66mm';
                printW58 = '46mm';
            } else if (mode === 'full') {
                w80 = '70mm';
                w58 = '48mm';
                printW80 = '70mm';
                printW58 = '48mm';
            }

            if (el80) el80.style.width = w80;
            if (el58) el58.style.width = w58;

            // Inject print rule override
            let overrideStyle = document.getElementById('thermal-custom-width-style');
            if (!overrideStyle) {
                overrideStyle = document.createElement('style');
                overrideStyle.id = 'thermal-custom-width-style';
                document.head.appendChild(overrideStyle);
            }
            overrideStyle.innerHTML = `
                @media print {
                    body.print-format-thermal80 .format-view-thermal80 {
                        width: ${printW80} !important;
                        max-width: ${printW80} !important;
                    }
                    body.print-format-thermal58 .format-view-thermal58 {
                        width: ${printW58} !important;
                        max-width: ${printW58} !important;
                    }
                }
            `;

            try {
                localStorage.setItem('pos_receipt_width_mode', mode);
            } catch(e) {}

            showToast(`Thermal buffer set to: ${mode}`);
        }

        // Paper Feed Alignment Adjustment
        function adjustThermalAlignment(alignment) {
            if (alignment === 'left') {
                document.body.classList.add('print-align-left');
                const el80 = document.querySelector('.thermal-80-layout');
                const el58 = document.querySelector('.thermal-58-layout');
                if (el80) { el80.style.marginLeft = '0'; el80.style.marginRight = 'auto'; }
                if (el58) { el58.style.marginLeft = '0'; el58.style.marginRight = 'auto'; }
            } else {
                document.body.classList.remove('print-align-left');
                const el80 = document.querySelector('.thermal-80-layout');
                const el58 = document.querySelector('.thermal-58-layout');
                if (el80) { el80.style.marginLeft = 'auto'; el80.style.marginRight = 'auto'; }
                if (el58) { el58.style.marginLeft = 'auto'; el58.style.marginRight = 'auto'; }
            }

            try {
                localStorage.setItem('pos_receipt_alignment', alignment);
            } catch(e) {}

            showToast(`Receipt alignment set to: ${alignment}`);
        }

        // Execute Print Trigger
        function executePrint() {
            window.print();
        }

        // Toggle Receipt Elements (Logo, Barcode)
        function toggleReceiptElement(selector, isVisible) {
            document.querySelectorAll(selector).forEach(el => {
                el.style.display = isVisible ? '' : 'none';
            });
        }

        // Toggle Merchant Copy Tag
        function toggleMerchantCopy(isMerchant) {
            document.querySelectorAll('.merchant-copy-tag').forEach(el => {
                el.style.display = isMerchant ? 'block' : 'none';
            });
            showToast(isMerchant ? 'Merchant copy tag enabled' : 'Customer copy tag enabled');
        }

        // Generate Plain Text Receipt and Copy to Clipboard
        function copyReceiptPlainText() {
            let text = '========================================\n';
            text += `         ${receiptData.storeName.toUpperCase()}\n`;
            text += `  ${receiptData.storeAddress}\n`;
            text += `  Tel: ${receiptData.storePhone}\n`;
            text += '========================================\n';
            text += `Receipt No : #${receiptData.receiptNumber}\n`;
            text += `Date/Time  : ${receiptData.date}\n`;
            text += `Cashier    : ${receiptData.cashier}\n`;
            text += `Branch     : ${receiptData.branch}\n`;
            text += `Order Type : ${receiptData.orderType}\n`;
            text += '----------------------------------------\n';
            text += 'ITEM DESCRIPTION       QTY       AMOUNT\n';
            text += '----------------------------------------\n';

            receiptData.items.forEach(item => {
                const name = item.product_name.padEnd(20, ' ').substring(0, 20);
                const qty = String(item.quantity).padStart(4, ' ');
                const sub = ('₱' + parseFloat(item.subtotal).toFixed(2)).padStart(12, ' ');
                text += `${name} ${qty} ${sub}\n`;
                if (item.modifiers && item.modifiers.length > 0) {
                    item.modifiers.forEach(mod => {
                        const modPrice = parseFloat(mod.additional_price || 0);
                        text += `  + ${mod.modifier_name} ${modPrice > 0 ? '(₱' + modPrice.toFixed(2) + ')' : ''}\n`;
                    });
                }
            });

            text += '----------------------------------------\n';
            text += `Subtotal Amount : ₱${receiptData.subtotal}\n`;
            if (parseFloat(receiptData.discount) > 0) {
                text += `Discount Applied: -₱${receiptData.discount}\n`;
            }
            text += `TOTAL DUE       : ₱${receiptData.total}\n`;
            text += '========================================\n';
            text += `Payment Method  : ${receiptData.paymentMethod}\n`;
            text += `Amount Tendered : ₱${receiptData.tendered}\n`;
            text += `Change Given    : ₱${receiptData.change}\n`;
            text += '========================================\n';
            text += '       THANK YOU FOR YOUR VISIT!\n';
            text += '     Please come again to MultiPOS\n';
            text += '========================================\n';

            navigator.clipboard.writeText(text).then(() => {
                showToast('Receipt copied to clipboard as plain text!');
            }).catch(() => {
                showToast('Failed to copy receipt to clipboard.');
            });
        }

        // Show Toast Notification Helper
        function showToast(message) {
            const toast = document.getElementById('toastNotification');
            const msgEl = document.getElementById('toastMessage');
            if (toast && msgEl) {
                msgEl.textContent = message;
                toast.classList.add('show');
                clearTimeout(window._toastTimeout);
                window._toastTimeout = setTimeout(() => {
                    toast.classList.remove('show');
                }, 2800);
            }
        }

        // Keyboard Shortcut: Ctrl+P or Cmd+P
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 'P')) {
                e.preventDefault();
                executePrint();
            }
        });

        // Initialize Preferences from localStorage
        function loadSavedPreferences() {
            try {
                // Preferred Width Buffer
                const savedWidth = localStorage.getItem('pos_receipt_width_mode');
                if (savedWidth) {
                    const selectEl = document.getElementById('thermalWidthSelect');
                    if (selectEl) {
                        selectEl.value = savedWidth;
                        adjustThermalWidth(savedWidth);
                    }
                }

                // Preferred Alignment
                const savedAlign = localStorage.getItem('pos_receipt_alignment');
                if (savedAlign) {
                    const selectEl = document.getElementById('thermalAlignSelect');
                    if (selectEl) {
                        selectEl.value = savedAlign;
                        adjustThermalAlignment(savedAlign);
                    }
                }
            } catch (err) {
                console.warn('Could not load receipt preferences:', err);
            }
        }

        // Initialize on DOM Ready
        document.addEventListener('DOMContentLoaded', function() {
            renderAllBarcodes();
            loadSavedPreferences();
            
            <?php if ($autoprint): ?>
                setTimeout(() => {
                    executePrint();
                }, 600);
            <?php endif; ?>
        });
    </script>
</body>
</html>
