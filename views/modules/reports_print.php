<?php
require_once __DIR__ . '/../../models/Setting.php';
$store_name = isset($store_name) && !empty($store_name) ? $store_name : Setting::get('store_name', 'Stacey Halo Halo');
$store_logo = isset($store_logo) && !empty($store_logo) ? $store_logo : Setting::get('store_logo', '');

// Defensive initialization for report variables
$selected_branch_name = $selected_branch_name ?? 'Global Network';
$type = $type ?? 'sales';
$data = $data ?? [];
$stats = $stats ?? [];
$start_date = $start_date ?? null;
$end_date = $end_date ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report - <?= htmlspecialchars($store_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #312e81;
            --accent-color: #4f46e5;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #111827;
            background: #fff;
            padding: 20px;
            font-size: 11px;
            line-height: 1.4;
        }
        .report-wrapper {
            max-width: 1000px;
            margin: 0 auto;
        }
        .report-header {
            border-bottom: 2px solid #111827;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .logo-box {
            max-width: 140px;
            margin-right: 35px;
            background: transparent;
        }
        .logo-box img {
            max-height: 80px;
            width: auto;
            object-fit: contain;
        }
        .store-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 24px;
            color: var(--primary-color);
            letter-spacing: -0.02em;
            text-transform: uppercase;
        }
        .report-title {
            font-weight: 800;
            font-size: 18px;
            color: #111827;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .meta-strip {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 12px 20px;
            margin-bottom: 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .meta-item label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 1px;
        }
        .meta-item span {
            font-weight: 700;
            font-size: 12px;
            color: #111827;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-box {
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .stat-box .val {
            font-weight: 800;
            font-size: 16px;
            color: var(--accent-color);
        }
        .stat-box .lab {
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            text-transform: uppercase;
            margin-top: 2px;
        }
        .table thead th {
            background: #f9fafb;
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #374151;
            border-bottom: 1px solid #111827;
            padding: 8px 10px;
        }
        .table tbody td {
            padding: 6px 10px;
            font-size: 10.5px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: middle;
        }
        .badge-status {
            font-size: 9px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            border: 1px solid transparent;
        }
        .badge-completed { border-color: #059669; color: #059669; }
        .badge-refunded { border-color: #d97706; color: #d97706; }
        .badge-cancelled { border-color: #dc2626; color: #dc2626; }
        
        @media print {
            body { padding: 0 !important; }
            .no-print { display: none !important; }
            .meta-strip { background: #eee !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { margin: 1cm; }
        }
    </style>
<link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">
</head>
<body onload="window.print()">
    <div class="report-wrapper">
        <!-- Header -->
        <div class="report-header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center">
                <?php if (!empty($store_logo)): ?>
                    <div class="logo-box">
                        <img src="<?= htmlspecialchars($store_logo) ?>" alt="Store Logo">
                    </div>
                <?php endif; ?>
                <div>
                    <div class="store-name"><?= htmlspecialchars($store_name) ?></div>
                    <div class="report-title"><?= $type === 'sales' ? 'Sales Distribution Report' : 'Inventory Audit Log' ?></div>
                </div>
            </div>
            <div class="text-end">
                <div style="font-size: 9px; font-weight: 700; color: #6b7280; text-transform: uppercase;">Generated At</div>
                <div style="font-weight: 700;"><?= date('M d, Y | h:i A') ?></div>
            </div>
        </div>

        <!-- Meta Strip -->
        <div class="meta-strip">
            <div class="meta-item">
                <label>Operational Unit</label>
                <span><?= htmlspecialchars($selected_branch_name) ?></span>
            </div>
            
            <?php if (isset($applied_filters)): ?>
                <?php if ($applied_filters['status'] !== 'All'): ?>
                    <div class="meta-item">
                        <label>Status</label>
                        <span><?= htmlspecialchars($applied_filters['status']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($applied_filters['channels'])): ?>
                    <div class="meta-item">
                        <label>Channel</label>
                        <span><?= implode(', ', $applied_filters['channels']) ?></span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($applied_filters['locations'])): ?>
                    <div class="meta-item">
                        <label>Location Filter</label>
                        <span><?= implode(', ', $applied_filters['locations']) ?></span>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <div class="meta-item">
                <label>Audit Period</label>
                <span>
                    <?= $start_date ? date('M d, Y', strtotime($start_date)) : 'Earliest' ?> 
                    - 
                    <?= $end_date ? date('M d, Y', strtotime($end_date)) : 'Latest' ?>
                </span>
            </div>
            <div class="meta-item d-none d-print-block">
                <label>Validation</label>
                <span>OFFICIAL SYSTEM RECORD</span>
            </div>
        </div>

        <!-- Summary Statistics -->
        <div class="summary-grid">
            <?php if ($type === 'sales'): ?>
                <div class="stat-box">
                    <div class="val">₱<?= number_format($stats['revenue'] ?? 0, 2) ?></div>
                    <div class="lab">Net Final Revenue</div>
                </div>
                <div class="stat-box">
                    <div class="val text-danger">₱<?= number_format($stats['discounts'] ?? 0, 2) ?></div>
                    <div class="lab">Total Concessions</div>
                </div>
                <div class="stat-box">
                    <div class="val text-dark"><?= number_format($stats['count'] ?? 0) ?></div>
                    <div class="lab">Total Order Count</div>
                </div>
            <?php else: ?>
                <div class="stat-box">
                    <div class="val"><?= count($data) ?></div>
                    <div class="lab">Logged Mutations</div>
                </div>
                <div class="stat-box">
                    <div class="val">ACTIVE</div>
                    <div class="lab">Audit Integrity</div>
                </div>
                <div class="stat-box">
                    <div class="val text-dark">LATEST</div>
                    <div class="lab">Asset Status</div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Data Table -->
        <div class="table-responsive">
            <?php if ($type === 'sales'): ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Trans ID</th>
                            <th>Operator</th>
                            <th>Products</th>
                            <th class="text-end">Gross</th>
                            <th class="text-end">Discount</th>
                            <th class="text-end">Final Net</th>
                            <th style="width: 100px;">Method</th>
                            <th class="text-end" style="width: 140px;">Timestamp</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="8" class="text-center py-4">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $sale): ?>
                                <tr>
                                    <td class="fw-bold">#<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></td>
                                    <td><?= htmlspecialchars($sale['cashier_name'] ?? 'System') ?></td>
                                    <td class="small text-muted"><?= htmlspecialchars($sale['products'] ?? '—') ?></td>
                                    <td class="text-end">₱<?= number_format($sale['total_amount'], 2) ?></td>
                                    <td class="text-end text-danger">-<?= number_format($sale['discount_amount'], 2) ?></td>
                                    <td class="text-end fw-bold">₱<?= number_format($sale['final_amount'], 2) ?></td>
                                    <td><?= htmlspecialchars($sale['payment_method']) ?></td>
                                    <td class="text-end text-muted"><?= date('M d, y | h:i A', strtotime($sale['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th style="width: 80px;">Log ID</th>
                            <th>Asset Name</th>
                            <th>Operator</th>
                            <th style="width: 90px;">Mutation</th>
                            <th class="text-center" style="width: 70px;">Qty</th>
                            <th>Remarks</th>
                            <th class="text-end" style="width: 140px;">Execution Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($data)): ?>
                            <tr><td colspan="7" class="text-center py-4">No records found.</td></tr>
                        <?php else: ?>
                            <?php foreach ($data as $log): ?>
                                <tr>
                                    <td class="text-muted fw-bold">ID-<?= $log['id'] ?></td>
                                    <td class="fw-bold"><?= htmlspecialchars($log['product_name']) ?></td>
                                    <td><?= htmlspecialchars($log['user_name']) ?></td>
                                    <td>
                                        <?php if ($log['action'] === 'stock_in' || $log['action'] === 'initial'): ?>
                                            <span class="text-success fw-bold">INFLOW</span>
                                        <?php else: ?>
                                            <span class="text-danger fw-bold">OUTFLOW</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold"><?= ($log['action'] === 'stock_out' ? '-' : '+') . $log['quantity'] ?></td>
                                    <td class="text-muted small"><?= htmlspecialchars($log['remarks']) ?></td>
                                    <td class="text-end text-muted"><?= date('M d, y | h:i A', strtotime($log['created_at'])) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="mt-4 pt-3 border-top text-center no-print">
            <button onclick="window.print()" class="btn btn-dark btn-sm rounded-pill px-4 fw-bold">Print Now</button>
            <button onclick="window.close()" class="btn btn-link text-muted btn-sm ms-2">Close</button>
        </div>

        <div class="text-center text-muted mt-4 opacity-50" style="font-size: 8px;">
            SYSTEM-GENERATED AUDIT RECORD | OFFICIAL USE ONLY | &copy; <?= date('Y') ?> <?= htmlspecialchars($store_name) ?>
        </div>
    </div>
</body>
</html>
