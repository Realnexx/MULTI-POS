<?php
require_once __DIR__ . '/../../models/Setting.php';
$store_name = Setting::get('store_name', 'Stacey Halo Halo');
$store_logo = Setting::get('store_logo', '');

// Defensive initialization
$logs = $logs ?? [];
$total_logs = $total_logs ?? 0;
$date_from = $_GET['date_from'] ?? 'All Time';
$date_to = $_GET['date_to'] ?? 'Now';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Activity Audit Report - <?= htmlspecialchars($store_name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Montserrat:wght@700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #b60e3d;
            --secondary-color: #8429c8;
        }
        body {
            font-family: 'Inter', sans-serif;
            color: #111827;
            background: #fff;
            padding: 30px;
            font-size: 11px;
            line-height: 1.4;
        }
        .report-wrapper {
            max-width: 1100px;
            margin: 0 auto;
        }
        .report-header {
            display: flex;
            align-items: center;
            border-bottom: 2px solid #111827;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .logo-box {
            max-width: 140px;
            margin-right: 30px;
        }
        .logo-box img {
            max-height: 70px;
            width: auto;
        }
        .store-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 24px;
            color: var(--primary-color);
            text-transform: uppercase;
            margin: 0;
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
        .summary-mini {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .stat-card {
            flex: 1;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 15px;
            text-align: center;
        }
        .stat-card label {
            display: block;
            font-size: 9px;
            font-weight: 700;
            color: #6b7280;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .stat-card h4 {
            margin: 0;
            font-weight: 800;
            color: var(--primary-color);
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            background: #f9fafb;
            color: #374151;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            padding: 12px 10px;
            border-bottom: 2px solid #e5e7eb;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #f3f4f6;
            vertical-align: top;
        }
        .badge-action {
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none !important; }
        }
    </style>
<link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">
</head>
<body>
    <div class="report-wrapper">
        <div class="report-header">
            <?php if ($store_logo): ?>
                <div class="logo-box">
                    <img src="<?= htmlspecialchars($store_logo) ?>" alt="Store Logo">
                </div>
            <?php endif; ?>
            <div>
                <h1 class="store-name"><?= htmlspecialchars($store_name ?? '') ?></h1>
                <div class="report-title">Activity Audit Report</div>
            </div>
        </div>

        <div class="meta-strip">
            <div class="meta-item">
                <label>Period</label>
                <span><?= htmlspecialchars($date_from ?? 'All Time') ?> — <?= htmlspecialchars($date_to ?? 'Now') ?></span>
            </div>
            <div class="meta-item text-end">
                <label>Generated On</label>
                <span><?= date('M d, Y h:i A') ?></span>
            </div>
        </div>

        <div class="summary-mini">
            <div class="stat-card">
                <label>Total Logged Events</label>
                <h4><?= number_format($total_logs) ?></h4>
            </div>
            <div class="stat-card">
                <label>Report Type</label>
                <h4>ADMIN AUDIT</h4>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width: 15%">User</th>
                    <th style="width: 15%">Action</th>
                    <th style="width: 40%">Description</th>
                    <th style="width: 15%">IP Address</th>
                    <th style="width: 15%">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($log['user_name'] ?? 'System') ?></td>
                    <td><span class="badge-action"><?= htmlspecialchars($log['action'] ?? '') ?></span></td>
                    <td class="text-muted"><?= htmlspecialchars($log['description'] ?? '') ?></td>
                    <td><code><?= htmlspecialchars($log['ip_address'] ?? '0.0.0.0') ?></code></td>
                    <td class="fw-medium"><?= date('M d, Y h:i A', strtotime($log['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted italic">No activity logs found for selected filters.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="no-print" style="position: fixed; top: 30px; right: 30px; display: flex; gap: 12px; z-index: 1000;">
            <button onclick="window.close()" class="btn btn-light px-4 py-2 fw-bold rounded-pill shadow-sm border d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-xmark"></i> Close
            </button>
            <button onclick="window.print()" class="btn btn-primary px-4 py-2 fw-bold rounded-pill shadow-lg transition-all hover:translate-y-[-2px] d-flex align-items-center gap-2">
                <i class="fa-solid fa-print"></i> Confirm & Print
            </button>
        </div>
    </div>
</body>
</html>
