<?php
$summary = $summary ?? [];
$totals = $totals ?? ['sales' => 0, 'expenses' => 0, 'others' => 0, 'net' => 0];
$store_name = $store_name ?? 'Stacey Halo Halo';
$selected_branch_name = $selected_branch_name ?? 'Global Network';
$start_date = $start_date ?? date('Y-m-01');
$end_date = $end_date ?? date('Y-m-t');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cashflow Summary - <?= htmlspecialchars($store_name) ?></title>
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

    <!-- Header Section -->
    <div class="flex justify-between items-center border-b-2 border-slate-900 pb-3 mb-3">
        <div class="flex items-center gap-3">
            <?php 
                $logo = Setting::get('store_logo', 'assets/img/logo.png');
            ?>
            <img src="<?= url($logo) ?>" alt="Logo" class="w-24 h-24 object-contain grayscale-0">
            <div>
                <h1 class="header-font text-lg font-black text-slate-900 tracking-tighter uppercase">Summary Audit</h1>
                <p class="text-slate-500 font-bold text-[8px] uppercase tracking-widest"><?= htmlspecialchars($store_name) ?> • Strategic Analytics</p>
            </div>
        </div>
        
        <div class="text-right">
            <p class="text-[8px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Report Date</p>
            <p class="text-[10px] font-bold text-slate-900"><?= date('M d, Y') ?></p>
        </div>
    </div>

    <!-- Meta Info -->
    <div class="grid grid-cols-2 gap-2 mb-4 bg-slate-50 p-2 rounded-lg border border-slate-100">
        <div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Enterprise Origin</p>
            <p class="text-[9px] font-bold text-slate-700"><?= htmlspecialchars($selected_branch_name) ?></p>
        </div>
        <div class="text-right">
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest mb-0.5">Audit Interval</p>
            <p class="text-[9px] font-bold text-slate-700"><?= date('d/m/y', strtotime($start_date)) ?> – <?= date('d/m/y', strtotime($end_date)) ?></p>
        </div>
    </div>

    <!-- Summary Table -->
    <div class="mb-6">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="border-b-2 border-slate-900">
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900">Transaction Date</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900 text-right">Sales</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900 text-right">Core Exp</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900 text-right">Other</th>
                    <th class="py-1 text-[8px] font-black uppercase tracking-widest text-slate-900 text-right">Net</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($summary as $day): ?>
                <tr>
                    <td class="py-1 text-[9px] font-bold text-slate-700"><?= date('d/m/y', strtotime($day['date'])) ?></td>
                    <td class="py-1 text-[9px] font-bold text-slate-600 text-right">₱<?= number_format($day['sales'], 2) ?></td>
                    <td class="py-1 text-[9px] font-bold text-rose-400 text-right">₱<?= number_format($day['expenses'], 2) ?></td>
                    <td class="py-1 text-[9px] font-bold text-amber-500 text-right">₱<?= number_format($day['others'], 2) ?></td>
                    <td class="py-1 text-[9px] font-black text-slate-900 text-right <?= $day['net'] < 0 ? 'text-rose-600' : 'text-emerald-600' ?>">
                        ₱<?= number_format($day['net'], 2) ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="border-t-2 border-slate-900 bg-slate-50">
                    <td class="py-2 text-[8px] font-black text-slate-900 uppercase tracking-widest">Aggregate Sums</td>
                    <td class="py-2 text-[9px] font-black text-slate-900 text-right">₱<?= number_format($totals['sales'], 2) ?></td>
                    <td class="py-2 text-[9px] font-black text-rose-600 text-right">₱<?= number_format($totals['expenses'], 2) ?></td>
                    <td class="py-2 text-[9px] font-black text-amber-600 text-right">₱<?= number_format($totals['others'], 2) ?></td>
                    <td class="py-2 text-[9px] font-black text-slate-900 text-right px-2">
                        ₱<?= number_format($totals['net'], 2) ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Authorization -->
    <div class="mt-8 flex justify-between gap-12">
        <div class="flex-1">
            <div class="border-b border-slate-900 mb-1 h-4"></div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Financial Auditor</p>
        </div>
        <div class="flex-1">
            <div class="border-b border-slate-900 mb-1 h-4"></div>
            <p class="text-[7px] font-black text-slate-400 uppercase tracking-widest">Corporate Executive</p>
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
