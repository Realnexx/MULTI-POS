<?php
// Safety fallbacks
$expenses = $expenses ?? [];
$branches = $branches ?? [];
$stats = $stats ?? [
    'total_revenue' => 0.00,
    'total_expenses' => 0.00,
    'net_profit' => 0.00,
    'highest_category' => 'N/A'
];
$cashflow_data = $cashflow_data ?? [
    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'income' => [0, 0, 0, 0, 0, 0, 0],
    'expenses' => [0, 0, 0, 0, 0, 0, 0]
];
$store_name = $store_name ?? 'Stacey Halo Halo';
$branch_id = $branch_id ?? null;
$start_date = $start_date ?? null;
$end_date = $end_date ?? null;

$title = "Financial Management";
ob_start();

// Refresh session logic just in case
if (isset($_SESSION['user']['id'])) {
    $refreshed_user = User::find_by_id($_SESSION['user']['id']);
    if ($refreshed_user) {
        $_SESSION['user']['avatar_url'] = $refreshed_user['avatar_url'] ?: $refreshed_user['image'] ?: null;
    }
}
?>

<!-- Inject Tailwind & Montserrat/Inter Fonts -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />

<script id="tailwind-config">
    tailwind.config = {
        important: '#tw-expenses',
        corePlugins: {
            preflight: false,
        },
        theme: {
            extend: {
                "colors": {
                    "primary": "#ba1340",
                    "secondary": "#8429c8",
                    "surface": "#f8f9fa",
                    "outline-variant": "#e2bec0"
                },
                fontFamily: {
                    headline: ['Montserrat', 'sans-serif'],
                    sans: ['Inter', 'sans-serif'],
                },
                borderRadius: {
                    "3xl": "24px",
                    "4xl": "32px"
                }
            }
        }
    }
</script>

<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.8);
    }

    /* Premium DataTables Styling */
    .dataTables_length select {
        padding: 8px 32px 8px 16px !important;
        border-radius: 12px !important;
        border: 1px solid #f1f5f9 !important;
        background-color: #f8fafc !important;
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #64748b !important;
        outline: none !important;
        cursor: pointer;
        transition: all 0.2s;
    }

    .dataTables_length select:focus {
        border-color: #ba1340 !important;
        background-color: #ffffff !important;
        box-shadow: 0 0 0 4px rgba(186, 19, 64, 0.05) !important;
    }

    .dataTables_info {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #94a3b8 !important;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
</style>

<div id="tw-expenses" class="font-sans text-slate-800 p-0 m-0">

    <!-- Header Section -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
        <div>
            <h1 class="font-headline text-3xl font-extrabold text-slate-900 tracking-tight flex items-center gap-3">
                <span class="p-2 bg-primary/10 rounded-xl">
                    <span class="material-symbols-outlined text-primary text-3xl">account_balance_wallet</span>
                </span>
                Financial Analytics
            </h1>
            <p class="text-slate-500 font-medium mt-1">Strategic oversight for <span class="text-primary font-bold"><?= htmlspecialchars($store_name) ?></span> operations.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
            <!-- Date Filter Selector -->
            <div class="flex items-center gap-2 bg-white border border-slate-200 rounded-2xl px-4 py-2.5 shadow-sm">
                <span class="material-symbols-outlined text-slate-400 text-lg">calendar_month</span>
                <div class="flex items-center gap-1">
                    <input type="date" id="start-date" value="<?= htmlspecialchars($start_date ?? '') ?>" 
                           class="bg-transparent border-0 text-[11px] font-bold text-slate-700 focus:ring-0 p-0 w-24 cursor-pointer">
                    <span class="text-slate-300">/</span>
                    <input type="date" id="end-date" value="<?= htmlspecialchars($end_date ?? '') ?>" 
                           class="bg-transparent border-0 text-[11px] font-bold text-slate-700 focus:ring-0 p-0 w-24 cursor-pointer">
                </div>
                <button type="button" id="today-btn" class="bg-slate-50 text-[9px] font-black uppercase tracking-widest px-2 py-1 rounded-lg border border-slate-100 hover:bg-slate-900 hover:text-white transition-all ml-1">Today</button>
            </div>
            <!-- Branch Filter Selector -->
            <div class="relative group">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 text-lg">location_on</span>
                </div>
                <select id="branch-filter" class="appearance-none bg-white border border-slate-200 text-slate-700 text-xs font-bold rounded-2xl pl-10 pr-10 py-3.5 focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all cursor-pointer shadow-sm hover:border-slate-300">
                    <option value="">Global Network</option>
                    <?php foreach ($branches as $b): ?>
                        <option value="<?= $b['id'] ?>" <?= ($branch_id == $b['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="material-symbols-outlined text-slate-400 text-lg">expand_more</span>
                </div>
            </div>

            <button type="button" data-bs-toggle="modal" data-bs-target="#addExpenseModal"
                class="bg-primary text-white px-6 py-3.5 rounded-2xl font-bold text-sm hover:shadow-2xl hover:shadow-primary/30 transform hover:-translate-y-0.5 transition-all flex items-center gap-2 border-0 shadow-xl shadow-primary/20">
                <span class="material-symbols-outlined text-xl">add_circle</span>
                New Ledger Entry
            </button>
        </div>
    </header>

    <!-- Bento Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-10">
        <!-- Monthly Revenue -->
        <div class="glass-card p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between group transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">trending_up</span>
                </div>
                <div class="flex flex-col items-end">
                    <span class="text-[10px] font-black text-emerald-600 px-2 py-0.5 rounded-full bg-emerald-50 border border-emerald-100 uppercase tracking-widest">Active</span>
                </div>
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1"><?= ($start_date && $end_date) ? 'Period' : 'Monthly' ?> Revenue</p>
                <h3 class="text-2xl font-black text-slate-900">₱<?= number_format($stats['total_revenue'], 2) ?></h3>
            </div>
        </div>

        <!-- Monthly Expenses -->
        <div class="glass-card p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between group transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">receipt_long</span>
                </div>
                <span class="text-[10px] font-black text-rose-600 px-2 py-0.5 rounded-full bg-rose-50 border border-rose-100 uppercase tracking-widest">Outflow</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1"><?= ($start_date && $end_date) ? 'Period' : 'Monthly' ?> Expenses</p>
                <h3 class="text-2xl font-black text-slate-900">₱<?= number_format($stats['total_expenses'], 2) ?></h3>
            </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-slate-900 p-6 rounded-3xl shadow-xl flex flex-col justify-between group transition-all relative overflow-hidden">
            <div class="absolute -right-4 -top-4 opacity-5">
                <span class="material-symbols-outlined text-[8rem] text-white">savings</span>
            </div>
            <div class="flex items-center justify-between mb-4 relative z-10">
                <div class="w-12 h-12 rounded-2xl bg-white/10 text-white flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">database</span>
                </div>
                <span class="text-[10px] font-black text-emerald-400 px-2 py-0.5 rounded-full bg-emerald-400/10 border border-emerald-400/20 uppercase tracking-widest">Growth</span>
            </div>
            <div class="relative z-10">
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Estimated Net Profit</p>
                <h3 class="text-2xl font-black text-white">₱<?= number_format($stats['net_profit'], 2) ?></h3>
            </div>
        </div>

        <!-- Efficiency Card -->
        <div class="glass-card p-6 rounded-3xl shadow-sm border border-slate-100 flex flex-col justify-between group transition-all">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-2xl bg-violet-50 text-violet-600 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">monitoring</span>
                </div>
                <span class="text-[10px] font-black text-violet-600 px-2 py-0.5 rounded-full bg-violet-50 border border-violet-100 uppercase tracking-widest">Top Cat</span>
            </div>
            <div>
                <p class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] mb-1">Highest Burn</p>
                <h3 class="text-2xl font-black text-slate-900"><?= $stats['highest_category'] ?></h3>
            </div>
        </div>
    </div>

    <!-- Analytics Mid Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mb-10">
        <!-- Main Performance Chart -->
        <div class="lg:col-span-8 glass-card p-8 rounded-4xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h4 class="font-bold text-slate-900 flex items-center gap-2">
                        Weekly Cashflow Matrix
                        <span class="material-symbols-outlined text-slate-300 text-sm">info</span>
                    </h4>
                    <p class="text-xs text-slate-500 font-medium mt-1">Comparison between Gross Income and Operational Outflow.</p>
                </div>
                <div class="flex gap-2">
                    <div class="flex items-center gap-2 px-3 py-1 bg-violet-50 rounded-lg">
                        <div class="w-2 h-2 rounded-full bg-violet-500"></div>
                        <span class="text-[10px] font-bold text-violet-600 uppercase">Income</span>
                    </div>
                    <div class="flex items-center gap-2 px-3 py-1 bg-rose-50 rounded-lg">
                        <div class="w-2 h-2 rounded-full bg-rose-500"></div>
                        <span class="text-[10px] font-bold text-rose-600 uppercase">Expenses</span>
                    </div>
                </div>
            </div>
            <div class="h-[320px]">
                <canvas id="incomeExpensesChart"></canvas>
            </div>
        </div>

        <!-- Corporate Profile / Quote -->
        <div class="lg:col-span-4 bg-primary p-12 rounded-4xl shadow-2xl flex flex-col justify-between text-white relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-8 opacity-10 transform translate-x-4 -translate-y-4 group-hover:translate-x-0 group-hover:translate-y-0 transition-transform duration-700">
                <span class="material-symbols-outlined text-[10rem]">restaurant_menu</span>
            </div>

            <div class="relative z-10">
                <div class="bg-white/10 w-fit p-3 rounded-2xl mb-8">
                    <span class="material-symbols-outlined text-3xl">analytics</span>
                </div>
                <h4 class="text-2xl font-black leading-tight mb-4 tracking-tight">"Profit is not an event, it's a process of layers."</h4>
                <div class="w-12 h-1 bg-white/30 rounded-full"></div>
            </div>

            <div class="relative z-10">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <img src="<?= get_user_avatar($_SESSION['user']['avatar_url'] ?? '', $_SESSION['user']['name'] ?? 'Admin') ?>"
                            class="w-14 h-14 rounded-2xl object-cover border-2 border-white/20 shadow-lg" alt="Profile">
                        <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-400 rounded-full border-2 border-primary"></div>
                    </div>
                    <div>
                        <p class="text-sm font-black m-0 tracking-tight"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Administrator') ?></p>
                        <p class="text-[10px] font-bold uppercase tracking-widest opacity-60 m-0">System Executive</p>
                    </div>
                </div>
                <div class="mt-8">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-[10px] font-black uppercase tracking-widest opacity-60">System Stability</span>
                        <span class="text-[10px] font-black uppercase tracking-widest">Optimal</span>
                    </div>
                    <div class="w-full bg-white/10 h-1.5 rounded-full">
                        <div class="bg-white h-full rounded-full w-[94%]"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Ledger -->
    <div class="glass-card rounded-4xl border border-slate-100 shadow-sm overflow-hidden mb-12">
        <div class="p-8 pb-4 flex flex-col md:flex-row items-center justify-between gap-4">
            <div>
                <h4 class="font-bold text-slate-900 m-0 tracking-tight">Transaction Ledger</h4>
                <p class="text-xs text-slate-500 font-medium mt-1">Detailed history of all financial transactions.</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Data Search -->
                <div class="bg-white px-4 py-3 rounded-2xl flex items-center gap-3 border border-slate-200 transition-all focus-within:border-primary focus-within:ring-4 focus-within:ring-primary/5 w-48 md:w-64 shadow-sm">
                    <span class="material-symbols-outlined text-slate-400 text-lg">search</span>
                    <input type="text" id="ledger-search" placeholder="Search transactions..." class="bg-transparent border-0 outline-none text-xs font-bold text-slate-700 w-full placeholder:text-slate-400">
                </div>

                <!-- High-Impact Action Cluster -->
                <div class="flex items-center gap-2">
                    <button id="print-ledger-btn" class="flex items-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl font-bold text-[11px] shadow-lg shadow-primary/20 hover:shadow-primary/40 hover:-translate-y-0.5 transition-all active:scale-95 group" title="Detailed Export Audit">
                        <span class="material-symbols-outlined text-[18px]">print</span>
                        <span class="hidden lg:inline uppercase tracking-widest">Audit</span>
                    </button>

                    <button id="print-summary-btn" class="flex items-center gap-2 px-4 py-2.5 bg-indigo-600 text-white rounded-xl font-bold text-[11px] shadow-lg shadow-indigo-200 hover:shadow-indigo-300 hover:-translate-y-0.5 transition-all active:scale-95 group" title="Cashflow Summary Audit">
                        <span class="material-symbols-outlined text-[18px]">assessment</span>
                        <span class="hidden lg:inline uppercase tracking-widest">Summary</span>
                    </button>

                    <button class="w-11 h-11 bg-emerald-500 text-white rounded-xl flex items-center justify-center shadow-lg shadow-emerald-200 hover:shadow-emerald-300 hover:-translate-y-0.5 transition-all active:scale-95 group" title="Export CSV Data">
                        <span class="material-symbols-outlined text-[20px]">download</span>
                    </button>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto px-4 pb-4">
            <table id="ledger-table" class="w-full text-left border-separate border-spacing-y-2">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Chronology</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Context</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Classification</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400">Origin</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-right">Amount</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center border-0">Verification</th>
                        <th class="px-6 py-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 text-center border-0">Operations</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <!-- Dynamic Ledger Content -->
                </tbody>
            </table>
            <div id="ledger-pagination" class="mt-6 flex items-center justify-between px-4"></div>
        </div>
    </div>

    <!-- Standardized Bootstrap 5 Modal -->
    <div class="modal fade premium-modal" id="addExpenseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                <div class="modal-header border-0 p-5 pb-0">
                    <div class="d-flex align-items-center gap-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 56px; height: 56px; background: #fff1f2; color: #b60e3d;">
                            <span class="material-symbols-outlined" style="font-size: 32px;">account_balance_wallet</span>
                        </div>
                        <div>
                            <h4 class="modal-title fw-800 text-dark mb-0" style="font-family: 'Montserrat', sans-serif;">Outflow Authorization</h4>
                            <p class="text-muted small mb-0">Record a strategic operational expenditure.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="addExpenseForm">
                    <div class="modal-body p-5">
                        <div class="row g-4">
                            <!-- Classification & Origin -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Classification</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><span class="material-symbols-outlined text-muted" style="font-size: 18px;">category</span></span>
                                    <select name="category" required class="form-select border-0 bg-light shadow-none py-3" style="border-radius: 0 12px 12px 0; font-size: 14px; font-weight: 600;">
                                        <option value="Supplies">Bakery Inventory</option>
                                        <option value="Utilities">Base Utilities</option>
                                        <option value="Repair">Technical Maintenance</option>
                                        <option value="Salary">Human Capital</option>
                                        <option value="Marketing">Strategic Promotion</option>
                                        <option value="Other">Miscellaneous</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Origin Hub</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><span class="material-symbols-outlined text-muted" style="font-size: 18px;">location_on</span></span>
                                    <select name="branch_id" class="form-select border-0 bg-light shadow-none py-3" style="border-radius: 0 12px 12px 0; font-size: 14px; font-weight: 600;">
                                        <option value="">HQ Core Hub</option>
                                        <?php foreach ($branches as $b): ?>
                                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Magnitude & Date -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Magnitude (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 font-black text-muted">₱</span>
                                    <input type="number" step="0.01" name="amount" required class="form-control border-0 bg-light shadow-none py-3"
                                        placeholder="0.00" style="border-radius: 0 12px 12px 0; font-size: 15px; font-weight: 800;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Chronology</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><span class="material-symbols-outlined text-muted" style="font-size: 18px;">calendar_month</span></span>
                                    <input type="date" name="expense_date" value="<?= date('Y-m-d') ?>" required
                                        class="form-control border-0 bg-light shadow-none py-3" style="border-radius: 0 12px 12px 0; font-size: 14px; font-weight: 600;">
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small mb-2">Narrative Details</label>
                                <textarea name="description" rows="4" class="form-control border-0 bg-light shadow-none p-4"
                                    placeholder="Describe the purpose or vendor for this transaction..."
                                    style="border-radius: 16px; font-size: 14px; line-height: 1.6;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-5 pt-0 gap-3">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal" style="font-size: 13px; padding: 12px 24px;">Discard</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold d-flex align-items-center gap-2"
                            style="background: #b60e3d; border: none; font-size: 13px; padding: 12px 32px; box-shadow: 0 4px 12px rgba(182, 14, 61, 0.15);">
                            <span class="material-symbols-outlined" style="font-size: 20px;">publish</span>
                            Commit To Ledger
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Expense Modal -->
    <div class="modal fade premium-modal" id="editExpenseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 28px;">
                <div class="modal-header border-0 p-5 pb-0">
                    <div class="d-flex align-items-center gap-4">
                        <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm"
                            style="width: 56px; height: 56px; background: #e0f2fe; color: #0284c7;">
                            <span class="material-symbols-outlined" style="font-size: 32px;">edit_square</span>
                        </div>
                        <div>
                            <h4 class="modal-title fw-800 text-dark mb-0" style="font-family: 'Montserrat', sans-serif;">Modify Ledger Entry</h4>
                            <p class="text-muted small mb-0">Adjust the details of an existing expenditure.</p>
                        </div>
                    </div>
                    <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editExpenseForm">
                    <input type="hidden" name="id" id="edit_expense_id">
                    <div class="modal-body p-5">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Classification</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><span class="material-symbols-outlined text-muted" style="font-size: 18px;">category</span></span>
                                    <select name="category" id="edit_category" required class="form-select border-0 bg-light shadow-none py-3" style="border-radius: 0 12px 12px 0; font-size: 14px; font-weight: 600;">
                                        <option value="Supplies">Bakery Inventory</option>
                                        <option value="Utilities">Base Utilities</option>
                                        <option value="Repair">Technical Maintenance</option>
                                        <option value="Salary">Human Capital</option>
                                        <option value="Marketing">Strategic Promotion</option>
                                        <option value="Other">Miscellaneous</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Origin Hub</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><span class="material-symbols-outlined text-muted" style="font-size: 18px;">location_on</span></span>
                                    <select name="branch_id" id="edit_branch_id" class="form-select border-0 bg-light shadow-none py-3" style="border-radius: 0 12px 12px 0; font-size: 14px; font-weight: 600;">
                                        <option value="">HQ Core Hub</option>
                                        <?php foreach ($branches as $b): ?>
                                            <option value="<?= $b['id'] ?>"><?= htmlspecialchars($b['name']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Magnitude (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0 font-black text-muted">₱</span>
                                    <input type="number" step="0.01" name="amount" id="edit_amount" required class="form-control border-0 bg-light shadow-none py-3" 
                                           placeholder="0.00" style="border-radius: 0 12px 12px 0; font-size: 15px; font-weight: 800;">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark small mb-2">Chronology</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-0"><span class="material-symbols-outlined text-muted" style="font-size: 18px;">calendar_month</span></span>
                                    <input type="date" name="expense_date" id="edit_expense_date" required 
                                           class="form-control border-0 bg-light shadow-none py-3" style="border-radius: 0 12px 12px 0; font-size: 14px; font-weight: 600;">
                                </div>
                            </div>

                            <div class="col-12">
                                <label class="form-label fw-bold text-dark small mb-2">Narrative Details</label>
                                <textarea name="description" id="edit_description" rows="4" class="form-control border-0 bg-light shadow-none p-4" 
                                          placeholder="Describe the purpose or vendor for this transaction..." 
                                          style="border-radius: 16px; font-size: 14px; line-height: 1.6;"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-0 p-5 pt-0 gap-3">
                        <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal" style="font-size: 13px; padding: 12px 24px;">Discard</button>
                        <button type="submit" class="btn btn-info text-white rounded-pill px-5 fw-bold d-flex align-items-center gap-2" 
                                style="background: #0284c7; border: none; font-size: 13px; padding: 12px 32px; box-shadow: 0 4px 12px rgba(2, 132, 199, 0.15);">
                            <span class="material-symbols-outlined" style="font-size: 20px;">save_as</span>
                            Update Ledger
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            function closeModal(id) {
                bootstrap.Modal.getInstance(document.getElementById(id))?.hide();
            }

            // High-Fidelity Chart.js initialization
            const ctx = document.getElementById('incomeExpensesChart').getContext('2d');

            // Gradient definitions
            const gradientIncome = ctx.createLinearGradient(0, 0, 0, 400);
            gradientIncome.addColorStop(0, '#8429c8');
            gradientIncome.addColorStop(1, '#a855f7');

            const gradientExpense = ctx.createLinearGradient(0, 0, 0, 400);
            gradientExpense.addColorStop(0, '#ba1340');
            gradientExpense.addColorStop(1, '#f43f5e');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($cashflow_data['labels']) ?>,
                    datasets: [{
                            label: 'Income',
                            data: <?= json_encode($cashflow_data['income']) ?>,
                            backgroundColor: '#8429c8',
                            borderRadius: 12,
                            barThickness: 16,
                            hoverBackgroundColor: '#a855f7'
                        },
                        {
                            label: 'Expenses',
                            data: <?= json_encode($cashflow_data['expenses']) ?>,
                            backgroundColor: '#ba1340',
                            borderRadius: 12,
                            barThickness: 16,
                            hoverBackgroundColor: '#f43f5e'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: {
                                family: 'Montserrat',
                                weight: 'bold',
                                size: 12
                            },
                            bodyFont: {
                                family: 'Inter',
                                size: 12
                            },
                            cornerRadius: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    weight: 'bold',
                                    size: 10
                                },
                                color: '#94a3b8',
                                callback: value => '₱' + value / 1000 + 'k'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Inter',
                                    weight: 'bold',
                                    size: 11
                                },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });

            // High-Performance Transaction Ledger Initialization
            const ledgerTable = $('#ledger-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "<?= url('?route=admin/expenses/data') ?>",
                    type: "GET",
                    data: function(d) {
                        d.branch_id = $('#branch-filter').val();
                        d.start_date = $('#start-date').val();
                        d.end_date = $('#end-date').val();
                    }
                },
                columns: [{
                        data: 'expense_date',
                        render: function(data) {
                            const date = new Date(data);
                            return `
                        <div class="text-xs font-black text-slate-900">${date.toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' })}</div>
                        <div class="text-[10px] text-slate-400 font-bold uppercase mt-1">${date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })}</div>
                    `;
                        }
                    },
                    {
                        data: 'description',
                        render: (data) => `<div class="text-xs font-bold text-slate-700">${data || 'Outflow Record'}</div>`
                    },
                    {
                        data: 'category',
                        render: function(data) {
                            return `
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-indigo-50 text-indigo-600 text-[10px] font-black uppercase tracking-wider border border-indigo-100">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                            ${data}
                        </span>
                    `;
                        }
                    },
                    {
                        data: 'branch_name',
                        render: (data) => `
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-slate-300 text-lg">store</span>
                        <span class="text-xs font-bold text-slate-600">${data || 'Corporate Core'}</span>
                    </div>
                `
                    },
                    {
                        data: 'amount',
                        className: 'text-right font-black text-slate-900 text-sm',
                        render: (data) => `₱${parseFloat(data).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                    },
                    {
                        data: null,
                        className: 'text-center border-0',
                        orderable: false,
                        render: () => `
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-600 text-[10px] font-black uppercase tracking-wider border border-emerald-100">
                                <span class="material-symbols-outlined text-sm">check_circle</span>
                                Captured
                            </div>
                        `
                    },
                    {
                        data: null,
                        className: 'text-center border-0',
                        orderable: false,
                        render: (data, type, row) => `
                            <div class="flex items-center justify-center gap-2">
                                <button type="button" class="w-9 h-9 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center hover:bg-sky-600 hover:text-white transition-all border border-sky-100 shadow-sm edit-expense-btn" data-id="${row.id}">
                                    <span class="material-symbols-outlined text-[20px]">edit_note</span>
                                </button>
                                <button type="button" class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all border border-rose-100 shadow-sm delete-expense-btn" data-id="${row.id}">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </div>
                        `
                    }
                ],
                order: [
                    [0, 'desc']
                ],
                dom: '<"px-8 pt-4 pb-0"l>rt<"flex items-center justify-between mt-6 px-8 pb-8"ip>',
                language: {
                    lengthMenu: "Show _MENU_ entries",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        previous: '<span class="material-symbols-outlined">chevron_left</span>',
                        next: '<span class="material-symbols-outlined">chevron_right</span>'
                    }
                },
                pageLength: 10,
                drawCallback: function() {
                    $('.expense-row').addClass('transition-all');
                    // Re-apply rounded corners to row cells
                    $('#ledger-table tbody tr').addClass('expense-row group bg-white cursor-default transition-all');
                    $('#ledger-table tbody tr td:first-child').addClass('rounded-l-2xl border-l-[3px] border-transparent');
                    $('#ledger-table tbody tr td:last-child').addClass('rounded-r-2xl');
                }
            });

            // Custom Ledger Search integration
            $("#ledger-search").on("keyup", function() {
                ledgerTable.search($(this).val()).draw();
            });

            // Enhanced Filter Logic
            function applyFilters() {
                const branchId = $('#branch-filter').val();
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                
                let url = "<?= url('?route=admin/expenses') ?>";
                const params = [];
                if (branchId) params.push("branch_id=" + branchId);
                if (startDate) params.push("start_date=" + startDate);
                if (endDate) params.push("end_date=" + endDate);
                
                if (params.length > 0) {
                    url += (url.includes('?') ? '&' : '?') + params.join('&');
                }
                window.location.href = url;
            }

            $("#branch-filter, #start-date, #end-date").on("change", function() {
                // If this was a date change, only trigger if both dates are set
                if ($(this).attr('type') === 'date') {
                    if ($('#start-date').val() && $('#end-date').val()) {
                        applyFilters();
                    }
                } else {
                    applyFilters();
                }
            });

            $("#today-btn").on("click", function() {
                const today = new Date().toISOString().split('T')[0];
                $('#start-date').val(today);
                $('#end-date').val(today);
                applyFilters();
            });

            // Print Logic
            $("#print-ledger-btn").on("click", function() {
                const branchId = $('#branch-filter').val();
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                
                let printUrl = "<?= url('?route=admin/expenses/print') ?>";
                const params = [];
                if (branchId) params.push("branch_id=" + branchId);
                if (startDate) params.push("start_date=" + startDate);
                if (endDate) params.push("end_date=" + endDate);
                
                if (params.length > 0) {
                    printUrl += "&" + params.join('&');
                }
                
                window.open(printUrl, '_blank');
            });

            // Summary Audit Print Logic
            $("#print-summary-btn").on("click", function() {
                const branchId = $('#branch-filter').val();
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                
                let printUrl = "<?= url('?route=admin/expenses/summary_print') ?>";
                const params = [];
                if (branchId) params.push("branch_id=" + branchId);
                if (startDate) params.push("start_date=" + startDate);
                if (endDate) params.push("end_date=" + endDate);
                
                if (params.length > 0) {
                    printUrl += (printUrl.includes('?') ? '&' : '?') + params.join('&');
                }
                
                window.open(printUrl, '_blank');
            });

            // Edit Expense Logic
            $(document).on("click", ".edit-expense-btn", function() {
                const id = $(this).data('id');
                $.get("<?= url('?route=ajax/expense_action&action=single') ?>", { id: id }, function(res) {
                    if (res.success) {
                        const e = res.expense;
                        $("#edit_expense_id").val(e.id);
                        $("#edit_category").val(e.category);
                        $("#edit_branch_id").val(e.branch_id);
                        $("#edit_amount").val(e.amount);
                        $("#edit_expense_date").val(e.expense_date);
                        $("#edit_description").val(e.description);
                        $("#editExpenseModal").modal('show');
                    } else {
                        showToast('Access Error', res.message, 'error');
                    }
                });
            });

            // Delete Expense Logic
            $(document).on("click", ".delete-expense-btn", function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Authorize Deletion?',
                    text: "This will permanently remove the record from the financial core.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ba1340',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Yes, Purge Record',
                    cancelButtonText: 'Abort',
                    background: '#fff',
                    borderRadius: '24px',
                    customClass: {
                        title: 'font-headline fw-800',
                        confirmButton: 'rounded-pill px-4',
                        cancelButton: 'rounded-pill px-4'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get("<?= url('?route=ajax/expense_action&action=delete') ?>", { id: id }, function(res) {
                            if (res.success) {
                                showToast('Record purged successfully.', 'success');
                                ledgerTable.ajax.reload();
                            } else {
                                showToast('Purge Failed', res.message, 'error');
                            }
                        });
                    }
                });
            });

            $("#addExpenseForm").on("submit", function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="animate-pulse">Authorizing...</span>');

                $.ajax({
                    url: "<?= url('?route=ajax/expense_action&action=create') ?>",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showToast('Transaction committed to corporate ledger.', 'success');
                            $("#addExpenseModal").modal('hide');
                            ledgerTable.ajax.reload(); // Reload table instead of full page
                            btn.prop('disabled', false).html(originalHtml);
                            $("#addExpenseForm")[0].reset();
                        } else {
                            showToast('Ledger Error', res.message, 'error');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        showToast('Secure Link Dropped', 'Failed to communicate with financial core.', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $("#editExpenseForm").on("submit", function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="animate-pulse">Updating...</span>');

                $.ajax({
                    url: "<?= url('?route=ajax/expense_action&action=update') ?>",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showToast('Ledger record updated successfully.', 'success');
                            $("#editExpenseModal").modal('hide');
                            ledgerTable.ajax.reload();
                            btn.prop('disabled', false).html(originalHtml);
                        } else {
                            showToast('Update Failed', res.message, 'error');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        showToast('Secure Link Dropped', 'Failed to communicate with financial core.', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>