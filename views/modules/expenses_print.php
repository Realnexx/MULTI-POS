<?php
$expenses = $expenses ?? [];
$stats = $stats ?? ['total_revenue' => 0, 'total_expenses' => 0, 'net_profit' => 0];
$store_name = $store_name ?? 'Stacey Halo Halo';
$selected_branch_name = $selected_branch_name ?? 'Global Network';
$start_date = $start_date ?? null;
$end_date = $end_date ?? null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Report - <?= htmlspecialchars($store_name) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700;800&family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { padding: 0; margin: 0; }
            @page { margin: 0.8cm; }
        }
        body { font-family: 'Inter', sans-serif; background-color: #fff; color: #1e293b; line-height: 1.25; }
        .header-font { font-family: 'Montserrat', sans-serif; }
    </style>
</head>
<body class="p-4">

    <!-- Print Header -->
    <div class="flex justify-between items-center border-b-2 border-slate-900 pb-3 mb-3">
        <div class="flex items-center gap-3">
            <?php 
                $logo = Setting::get('store_logo', 'assets/img/logo.png');
            ?>
            <img src="<?= url($logo) ?>" alt="Logo" class="w-24 h-24 object-contain grayscale-0">
            <div>
                <h1 class="header-font text-lg font-black text-slate-900 tracking-tighter uppercase">Expenditure Audit</h1>
                <p class="text-slate-500 font-bold text-[8px] uppercase tracking-widest"><?= htmlspecialchars($store_name) ?> • Financial Division</p>
            </div>
        </div>
        
        <div class="text-right">
            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Audit Interval</p>
            <p class="text-[10px] font-bold text-slate-900">
                <?= $start_date ? date('M d, Y', strtotime($start_date)) : 'Current Month' ?> – <?= $end_date ? date('M d, Y', strtotime($end_date)) : 'N/A' ?>
            </p>
        </div>
    </div>

    <!-- Meta Information -->
    <div class="grid grid-cols-4 gap-2 mb-4 bg-slate-50 p-2 rounded-lg border border-slate-100">
        <div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Origin Source</p>
            <p class="text-[9px] font-bold text-slate-700"><?= htmlspecialchars($selected_branch_name) ?></p>
        </div>
        <div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Scope Magnitude</p>
            <p class="text-[9px] font-bold text-slate-700"><?= count($expenses) ?> Records</p>
        </div>
        <div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Aggregate Sum</p>
            <p class="text-[9px] font-bold text-rose-600">₱<?= number_format($stats['total_expenses'], 2) ?></p>
        </div>
        <div class="text-right">
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Audit Timestamp</p>
            <p class="text-[9px] font-bold text-slate-700"><?= date('M d, Y | H:i') ?></p>
        </div>
    </div>

    <!-- Transaction Table -->
    <div class="mb-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-[1.5px] border-slate-900">
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900">Date</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900">Description / Vendor</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900">Type</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900">Hub</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900 text-right">Value (₱)</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($expenses as $e): ?>
                <tr>
                    <td class="py-1 text-[9px] font-bold text-slate-900 whitespace-nowrap"><?= date('d.m.y', strtotime($e['expense_date'])) ?></td>
                    <td class="py-1 text-[9px] font-medium text-slate-600 uppercase text-[8px]"><?= htmlspecialchars($e['description'] ?: 'General Outflow') ?></td>
                    <td class="py-1 text-[8px] font-black uppercase text-slate-400"><?= htmlspecialchars($e['category']) ?></td>
                    <td class="py-1 text-[8px] font-bold text-slate-400"><?= htmlspecialchars($e['branch_name'] ?: 'Core') ?></td>
                    <td class="py-1 text-[9px] font-black text-slate-900 text-right">₱<?= number_format($e['amount'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-[1.5px] border-slate-900">
                    <td colspan="4" class="py-2 text-[8px] font-black text-slate-900 text-right uppercase tracking-widest">Total Period Expenditure</td>
                    <td class="py-2 text-[9px] font-black text-slate-900 text-right">₱<?= number_format($stats['total_expenses'], 2) ?></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Signatures Section -->
    <div class="mt-8 flex justify-between gap-12">
        <div class="flex-1">
            <div class="border-b border-slate-900 mb-1 h-4"></div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Verification Auditor</p>
        </div>
        <div class="flex-1">
            <div class="border-b border-slate-900 mb-1 h-4"></div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Executive Authorization</p>
        </div>
    </div>

    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
