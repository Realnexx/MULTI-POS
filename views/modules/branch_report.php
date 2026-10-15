<?php
// Defensive initialization to prevent undefined variable errors
$settings = $settings ?? [];
$branches = $branches ?? [];
$stats = $stats ?? [];

$store_name = $settings['store_name'] ?? 'Stacey Halu Halu';
$store_logo = $settings['store_logo'] ?? 'assets/img/logo.png';
$store_address = $settings['store_address'] ?? '123 Sweet Tooth Blvd, Sugar City';
$report_date = date('F j, Y g:i A');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Network Audit Report | <?= $store_name ?></title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <style>
        :root {
            --primary: #ff4d6d;
            --zinc-900: #09090b;
            --zinc-100: #f4f4f5;
            --zinc-500: #71717a;
            --zinc-100-rgb: 244, 244, 245;
        }

        @media print {
            .no-print { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: white !important; }
            .report-container { box-shadow: none !important; border: none !important; width: 100% !important; max-width: none !important; padding: 0 !important; }
            .page-break-inside { page-break-inside: avoid; }
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            background: #f8f9fa; 
            font-family: 'Inter', sans-serif; 
            color: var(--zinc-900);
            padding: 40px 20px;
        }

        .report-container {
            max-width: 1000px;
            margin: 0 auto;
            background: white;
            padding: 50px;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }

        /* Header Styles */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 60px;
            padding-bottom: 30px;
            border-bottom: 2px solid var(--zinc-100);
        }

        .brand-section { display: flex; align-items: center; gap: 20px; }
        .logo-wrap {
            width: 140px;
            height: 140px;
            background: transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .logo-wrap img { width: 100%; height: 100%; object-fit: contain; padding: 0; }
        
        .brand-info h1 { font-family: 'Outfit', sans-serif; font-weight: 800; font-size: 24px; text-transform: uppercase; letter-spacing: 1px; }
        .brand-info p { font-size: 12px; color: var(--zinc-500); width: 250px; line-height: 1.5; margin-top: 5px; }

        .report-meta { text-align: right; }
        .report-label { 
            background: var(--zinc-900); 
            color: white; 
            padding: 6px 15px; 
            border-radius: 8px; 
            font-size: 10px; 
            font-weight: 800; 
            letter-spacing: 2px;
            display: inline-block;
            margin-bottom: 15px;
        }
        .meta-id { font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 18px; margin-bottom: 5px; }
        .meta-date { font-size: 11px; color: var(--zinc-500); font-weight: 500; }

        /* Stats Grid */
        .stats-summary {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 50px;
        }
        .stat-box {
            background: var(--zinc-100);
            padding: 20px;
            border-radius: 15px;
            text-align: center;
        }
        .stat-box p { font-size: 10px; font-weight: 800; text-transform: uppercase; color: var(--zinc-500); letter-spacing: 1px; margin-bottom: 8px; }
        .stat-box h2 { font-family: 'Outfit', sans-serif; font-size: 24px; font-weight: 800; }

        /* Table Styles */
        .report-title { 
            font-family: 'Outfit', sans-serif; 
            font-size: 18px; 
            font-weight: 800; 
            margin-bottom: 25px; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
        }
        .report-title span { color: var(--primary); }

        table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        th { 
            text-align: left; 
            padding: 15px; 
            font-size: 11px; 
            font-weight: 800; 
            text-transform: uppercase; 
            letter-spacing: 1px; 
            color: var(--zinc-500);
            border-bottom: 2px solid var(--zinc-100);
        }
        td { 
            padding: 20px 15px; 
            font-size: 13px; 
            border-bottom: 1px solid var(--zinc-100);
            vertical-align: middle;
        }

        .branch-identity { display: flex; align-items: center; gap: 15px; }
        .branch-img { width: 45px; height: 45px; border-radius: 12px; object-fit: cover; }
        .branch-name { font-weight: 700; font-size: 14px; margin-bottom: 2px; }
        .branch-id { font-size: 10px; font-weight: 800; color: var(--zinc-500); text-transform: uppercase; }

        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-offline { background: #fee2e2; color: #991b1b; }
        .status-maintenance { background: #fef3c7; color: #92400e; }

        .manager-cell { font-weight: 600; color: var(--zinc-900); }
        .staff-count { font-weight: 800; color: var(--primary); font-family: 'Outfit', sans-serif; font-size: 16px; }

        .footer-note {
            margin-top: 60px;
            padding-top: 30px;
            border-top: 1px dashed var(--zinc-500);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .authority-sig { text-align: center; }
        .sig-line { width: 200px; border-top: 1px solid var(--zinc-900); margin-top: 30px; }
        .sig-name { font-size: 12px; font-weight: 800; text-transform: uppercase; margin-top: 5px; }
        .sig-title { font-size: 10px; color: var(--zinc-500); }

        .actions { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 10px; z-index: 100; }
        .btn-action { 
            padding: 12px 25px; 
            border-radius: 15px; 
            border: none; 
            font-weight: 800; 
            font-size: 12px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 10px; 
            transition: all 0.3s;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        .btn-print { background: var(--zinc-900); color: white; }
        .btn-close { background: white; color: var(--zinc-900); }
        .btn-action:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(0,0,0,0.2); }
    </style>
<link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">
</head>
<body>

    <div class="actions no-print">
        <button onclick="window.print()" class="btn-action btn-print">
            <span class="material-symbols-outlined">print</span>
            PRINT AUDIT
        </button>
        <button onclick="window.location.href='<?= url('?route=admin/branches') ?>'" class="btn-action btn-close">
            <span class="material-symbols-outlined">arrow_back</span>
            DASHBOARD
        </button>
    </div>

    <div class="report-container">
        <!-- Header -->
        <header class="report-header">
            <div class="brand-section">
                <div class="logo-wrap">
                    <img src="<?= url($store_logo) ?>" alt="Brand Logo">
                </div>
                <div class="brand-info">
                    <h1><?= $store_name ?></h1>
                    <p><?= $store_address ?></p>
                </div>
            </div>
            <div class="report-meta">
                <span class="report-label">NETWORK AUDIT</span>
                <div class="meta-id">HALU-NA-<?= date('Ymd') ?></div>
                <div class="meta-date">Issued on <?= $report_date ?></div>
            </div>
        </header>

        <!-- Stats Summary -->
        <div class="stats-summary">
            <div class="stat-box">
                <p>Total Stations</p>
                <h2><?= str_pad($stats['total'] ?? 0, 2, '0', STR_PAD_LEFT) ?></h2>
            </div>
            <div class="stat-box">
                <p>Active Regions</p>
                <h2><?= str_pad($stats['regions'] ?? 0, 2, '0', STR_PAD_LEFT) ?></h2>
            </div>
            <div class="stat-box">
                <p>Operational</p>
                <h2 style="color: #166534;"><?= str_pad($stats['active'] ?? 0, 2, '0', STR_PAD_LEFT) ?></h2>
            </div>
            <div class="stat-box">
                <p>Top Performer</p>
                <h2 style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 16px;"><?= $stats['top_performing'] ?? 'N/A' ?></h2>
            </div>
        </div>

        <!-- Detail Table -->
        <h3 class="report-title">
            <span class="material-symbols-outlined">analytics</span>
            Branch Operational Roster
        </h3>

        <table>
            <thead>
                <tr>
                    <th>Branch Identity</th>
                    <th>Station Manager</th>
                    <th>Operational Team</th>
                    <th>Current Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($branches as $branch): 
                    $status_class = 'status-active';
                    if($branch['status'] === 'Offline') $status_class = 'status-offline';
                    if($branch['status'] === 'Maintenance') $status_class = 'status-maintenance';
                    
                    $staff_count = 0;
                    if(!empty($branch['staff_list'])) {
                        $staff_count = count(explode(',', $branch['staff_list']));
                    }
                ?>
                <tr class="page-break-inside">
                    <td>
                        <div class="branch-identity">
                            <img src="<?= get_branch_image($branch['image_url'], $branch['id']) ?>" class="branch-img">
                            <div>
                                <div class="branch-name"><?= $branch['name'] ?></div>
                                <div class="branch-id">ID: #B<?= str_pad($branch['id'], 3, '0', STR_PAD_LEFT) ?></div>
                            </div>
                        </div>
                    </td>
                    <td class="manager-cell"><?= $branch['manager_name'] ?? 'Unassigned' ?></td>
                    <td>
                        <div class="staff-count"><?= str_pad($staff_count, 2, '0', STR_PAD_LEFT) ?></div>
                        <div style="font-size: 9px; color: var(--zinc-500); font-weight: 800; text-transform: uppercase;">Personnel</div>
                    </td>
                    <td>
                        <span class="status-badge <?= $status_class ?>"><?= $branch['status'] ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Footer -->
        <footer class="footer-note">
            <div>
                <p style="font-size: 10px; color: var(--zinc-500); max-width: 400px; line-height: 1.6;">
                    * This is a system-generated operational report. Information is accurate as of the time of issuance. Branch performance is reviewed monthly against corporate growth targets and regional quality metrics.
                </p>
            </div>
            <div class="authority-sig">
                <div class="sig-line"></div>
                <div class="sig-name"><?= $settings['founder_name'] ?? 'Stacey Halu' ?></div>
                <div class="sig-title"><?= $settings['founder_title'] ?? 'Founder & CEO' ?></div>
            </div>
        </footer>
    </div>

</body>
</html>
