<?php
$title = "Admin Dashboard";

// Safety fallbacks to prevent undefined variable errors
$stats = $stats ?? [
    'sales_today' => 0.00,
    'orders_today' => 0,
    'avg_order_value' => 0.00,
    'total_users' => 0,
    'sales_chart_data' => []
];
$recent_sales = $recent_sales ?? [];
$low_stock = $low_stock ?? [];
$top_selling = $top_selling ?? [];
$all_branches = $all_branches ?? [];
$selected_branch = $selected_branch ?? 'all';

ob_start();

// Helper to generate dynamic sparkline SVG path based on last 7 days data
function get_sparkline_path($data_points)
{
    if (empty($data_points)) {
        return "M0,15 L100,15";
    }
    $count = count($data_points);
    $max = max($data_points);
    $min = min($data_points);
    $range = $max - $min;
    if ($range == 0) $range = 1;

    $points = [];
    foreach ($data_points as $idx => $val) {
        $x = ($count > 1) ? ($idx / ($count - 1)) * 100 : 50;
        $y = 20 - (($val - $min) / $range) * 16 - 2; // Fits beautifully in height 20
        $points[] = "$x,$y";
    }
    return "M" . implode(" L", $points);
}

// Generate sparkline path
$sparkline_path = get_sparkline_path($stats['sales_chart_data']);
?>

<!-- Inject Tailwind & Montserrat/Inter Fonts -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet" />
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet" />
<!-- Flatpickr for Date Range Selection -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<style>
    .flatpickr-day.selected,
    .flatpickr-day.startRange,
    .flatpickr-day.endRange,
    .flatpickr-day.selected.inRange,
    .flatpickr-day.startRange.inRange,
    .flatpickr-day.endRange.inRange,
    .flatpickr-day.selected:focus,
    .flatpickr-day.startRange:focus,
    .flatpickr-day.endRange:focus,
    .flatpickr-day.selected:hover,
    .flatpickr-day.startRange:hover,
    .flatpickr-day.endRange:hover,
    .flatpickr-day.selected.prevMonthDay,
    .flatpickr-day.startRange.prevMonthDay,
    .flatpickr-day.endRange.prevMonthDay,
    .flatpickr-day.selected.nextMonthDay,
    .flatpickr-day.startRange.nextMonthDay,
    .flatpickr-day.endRange.nextMonthDay {
        background: #ba1340 !important;
        border-color: #ba1340 !important;
    }
</style>

<script id="tailwind-config">
    tailwind.config = {
        important: '#tw-dashboard',
        corePlugins: {
            preflight: false,
        },
        darkMode: "class",
        theme: {
            extend: {
                "colors": {
                    "on-error-container": "#93000a",
                    "primary-container": "#da3054",
                    "tertiary": "#7a5500",
                    "on-secondary-fixed": "#2e004e",
                    "error-container": "#ffdad6",
                    "outline": "#8e6f71",
                    "tertiary-fixed": "#ffdea9",
                    "on-secondary": "#ffffff",
                    "on-primary-container": "#fffbff",
                    "surface-tint": "#ba1340",
                    "surface-bright": "#f8f9fa",
                    "surface-container-low": "#f3f4f5",
                    "surface-container-highest": "#e1e3e4",
                    "on-surface": "#191c1d",
                    "on-error": "#ffffff",
                    "on-secondary-container": "#fffbff",
                    "on-primary-fixed-variant": "#91002d",
                    "inverse-on-surface": "#f0f1f2",
                    "on-primary-fixed": "#40000f",
                    "secondary-fixed": "#f2daff",
                    "on-primary": "#ffffff",
                    "tertiary-fixed-dim": "#ffba27",
                    "surface-container": "#edeeef",
                    "secondary-fixed-dim": "#e0b6ff",
                    "error": "#ba1a1a",
                    "outline-variant": "#e2bec0",
                    "on-tertiary-container": "#fffbff",
                    "on-secondary-fixed-variant": "#6b00af",
                    "inverse-surface": "#2e3132",
                    "on-tertiary-fixed-variant": "#5e4100",
                    "on-background": "#191c1d",
                    "secondary": "#8429c8",
                    "primary-fixed-dim": "#ffb2b8",
                    "inverse-primary": "#ffb2b8",
                    "primary-fixed": "#ffdadb",
                    "surface-variant": "#e1e3e4",
                    "surface-container-lowest": "#ffffff",
                    "on-tertiary": "#ffffff",
                    "background": "#f8f9fa",
                    "secondary-container": "#9e49e3",
                    "surface": "#f8f9fa",
                    "on-surface-variant": "#5a4042",
                    "tertiary-container": "#996c00",
                    "surface-container-high": "#e7e8e9",
                    "primary": "#b60e3d",
                    "on-tertiary-fixed": "#271900",
                    "surface-dim": "#d9dadb"
                },
                "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px",
                    "pill": "9999px"
                },
                "spacing": {
                    "xl": "64px",
                    "base": "8px",
                    "md": "24px",
                    "sm": "12px",
                    "margin-desktop": "32px",
                    "xs": "4px",
                    "margin-mobile": "16px",
                    "gutter": "24px",
                    "lg": "40px"
                },
                "fontFamily": {
                    "headline-md": ["Montserrat"],
                    "label-sm": ["Inter"],
                    "display-lg-mobile": ["Montserrat"],
                    "display-lg": ["Montserrat"],
                    "label-md": ["Inter"],
                    "body-md": ["Inter"],
                    "body-lg": ["Inter"],
                    "headline-lg": ["Montserrat"]
                },
                "fontSize": {
                    "headline-md": ["24px", {
                        "lineHeight": "1.4",
                        "fontWeight": "600"
                    }],
                    "label-sm": ["12px", {
                        "lineHeight": "1",
                        "fontWeight": "500"
                    }],
                    "display-lg-mobile": ["36px", {
                        "lineHeight": "1.2",
                        "fontWeight": "700"
                    }],
                    "display-lg": ["48px", {
                        "lineHeight": "1.2",
                        "letterSpacing": "-0.02em",
                        "fontWeight": "700"
                    }],
                    "label-md": ["14px", {
                        "lineHeight": "1",
                        "letterSpacing": "0.05em",
                        "fontWeight": "600"
                    }],
                    "body-md": ["16px", {
                        "lineHeight": "1.6",
                        "fontWeight": "400"
                    }],
                    "body-lg": ["18px", {
                        "lineHeight": "1.6",
                        "fontWeight": "400"
                    }],
                    "headline-lg": ["32px", {
                        "lineHeight": "1.3",
                        "fontWeight": "600"
                    }]
                }
            },
        },
    }
</script>

<style>
    .material-symbols-outlined {
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }

    .custom-shadow {
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    }

    .custom-shadow-hover:hover {
        box-shadow: 0 12px 30px rgba(255, 77, 109, 0.12);
    }

    .sparkline {
        stroke-dasharray: 100;
        stroke-dashoffset: 100;
        animation: dash 2s ease-in-out forwards;
    }

    @keyframes dash {
        to {
            stroke-dashoffset: 0;
        }
    }

    /* Scope Tailwind dashboard wrapper to fill admin layout content area */
    #tw-dashboard {
        width: 100%;
        min-height: 100%;
    }
</style>

<!-- Main Wrapper scoped for Tailwind styling -->
<div id="tw-dashboard" class="bg-surface font-body-md text-on-surface p-0 m-0">

    <!-- Header Section -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center gap-md mb-lg">
        <div>
            <?php
            $current_branch = $selected_branch ?? 'all';
            $display_branch = ($current_branch === 'all' || !$current_branch)
                ? 'All Branches'
                : htmlspecialchars($current_branch);

            $display_date = "Today";
            if (!empty($_GET['date_range'])) {
                $display_date = htmlspecialchars($_GET['date_range']);
            }
            ?>
            <h2 class="font-headline-lg text-headline-lg text-on-surface"><?= $display_branch ?> Performance</h2>
            <p class="font-body-md text-body-md text-on-surface-variant">Period: <?= $display_date ?>. Welcome back, manager.</p>
        </div>
        <div class="flex items-center gap-sm flex-wrap">

            <form method="GET" action="<?= url('?route=dashboard') ?>" id="dashboard-filter-form" class="flex items-center gap-sm flex-wrap">
                <input type="hidden" name="route" value="dashboard">

                <?php if (!empty($all_branches)): ?>
                    <!-- Branch Selector (Admin Only) -->
                    <div class="flex items-center gap-xs bg-surface-container-lowest border border-outline-variant rounded-lg px-sm py-xs hover:border-primary transition-all">
                        <span class="material-symbols-outlined text-primary" style="font-size:18px;">storefront</span>
                        <select name="branch" id="branch-select"
                            onchange="document.getElementById('dashboard-filter-form').submit()"
                            class="font-label-md text-label-md bg-transparent border-none outline-none cursor-pointer pr-1"
                            style="font-family:'Inter',sans-serif;font-size:13px;color:#2b2d42;min-width:140px;">
                            <option value="all" <?= ($current_branch === 'all') ? 'selected' : '' ?>>🏢 All Branches</option>
                            <?php foreach ($all_branches as $b): ?>
                                <option value="<?= htmlspecialchars($b['name']) ?>"
                                    <?= ($current_branch === $b['name']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($b['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endif; ?>

                <!-- Date Range Selector -->
                <div class="bg-surface-container-lowest flex items-center px-sm py-xs rounded-lg border border-outline-variant hover:border-primary transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-primary mr-xs" style="font-size:18px;">calendar_today</span>
                    <input type="text" name="date_range" id="date-range-picker"
                        class="font-label-md text-label-md bg-transparent border-none outline-none cursor-pointer"
                        placeholder="Filter by Date Range"
                        value="<?= htmlspecialchars($_GET['date_range'] ?? '') ?>"
                        style="font-family:'Inter',sans-serif;font-size:13px;color:#2b2d42;min-width:180px;" readonly>
                </div>
            </form>

            <button class="p-sm bg-surface-container-lowest rounded-full border border-outline-variant hover:bg-primary-fixed transition-all flex items-center justify-center">
                <span class="material-symbols-outlined text-primary">notifications</span>
            </button>
        </div>
    </header>


    <!-- Key Performance Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-gutter mb-lg">
        <!-- Total Sales -->
        <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow custom-shadow-hover transition-all border border-transparent hover:border-primary-fixed">
            <div class="flex justify-between items-start mb-sm">
                <div class="p-xs bg-primary-fixed rounded-lg">
                    <span class="material-symbols-outlined text-primary">payments</span>
                </div>
                <span class="font-label-sm text-label-sm text-tertiary flex items-center bg-tertiary-fixed px-xs py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-sm">trending_up</span> 12%
                </span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant">Total Sales (Period)</p>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-xs">₱<?= number_format($stats['sales_today'], 2) ?></h3>
            <div class="h-8 w-full mt-xs">
                <svg class="w-full h-full" viewbox="0 0 100 20">
                    <path class="sparkline" d="<?= $sparkline_path ?>" fill="none" stroke="#b60e3d" stroke-width="2"></path>
                </svg>
            </div>
        </div>

        <!-- Orders Today -->
        <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow custom-shadow-hover transition-all border border-transparent hover:border-primary-fixed">
            <div class="flex justify-between items-start mb-sm">
                <div class="p-xs bg-secondary-fixed rounded-lg">
                    <span class="material-symbols-outlined text-secondary">shopping_cart</span>
                </div>
                <span class="font-label-sm text-label-sm text-tertiary flex items-center bg-tertiary-fixed px-xs py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-sm">trending_up</span> 8%
                </span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant">Orders (Period)</p>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-xs" id="orders-today-val"><?= number_format($stats['orders_today']) ?></h3>
            <div class="h-8 w-full mt-xs">
                <svg class="w-full h-full" viewbox="0 0 100 20">
                    <path class="sparkline" d="M0,18 Q20,5 40,15 T60,8 T100,12" fill="none" stroke="#8429c8" stroke-width="2"></path>
                </svg>
            </div>
        </div>

        <!-- Avg Order Value -->
        <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow custom-shadow-hover transition-all border border-transparent hover:border-primary-fixed">
            <div class="flex justify-between items-start mb-sm">
                <div class="p-xs bg-tertiary-fixed rounded-lg">
                    <span class="material-symbols-outlined text-tertiary">analytics</span>
                </div>
                <span class="font-label-sm text-label-sm text-on-error-container flex items-center bg-error-container px-xs py-0.5 rounded-full">
                    <span class="material-symbols-outlined text-sm">trending_down</span> 2%
                </span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant">Avg. Order Value</p>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-xs">₱<?= number_format($stats['avg_order_value'], 2) ?></h3>
            <div class="h-8 w-full mt-xs">
                <svg class="w-full h-full" viewbox="0 0 100 20">
                    <path class="sparkline" d="M0,5 Q30,10 50,15 T100,18" fill="none" stroke="#7a5500" stroke-width="2"></path>
                </svg>
            </div>
        </div>

        <!-- Customer Growth -->
        <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow custom-shadow-hover transition-all border border-transparent hover:border-primary-fixed">
            <div class="flex justify-between items-start mb-sm">
                <div class="p-xs rounded-lg" style="background:#e8f5e9">
                    <span class="material-symbols-outlined" style="color:#2e7d32;font-variation-settings:'FILL' 1;">group_add</span>
                </div>
                <span class="font-label-sm text-label-sm flex items-center px-xs py-0.5 rounded-full" style="background:#e8f5e9;color:#2e7d32">
                    <span class="material-symbols-outlined text-sm">trending_up</span> +24%
                </span>
            </div>
            <p class="font-label-md text-label-md text-on-surface-variant">Customer Growth</p>
            <h3 class="font-headline-md text-headline-md text-on-surface mb-xs">+<?= number_format($stats['total_users']) ?></h3>
            <div class="h-8 w-full mt-xs">
                <svg class="w-full h-full" viewbox="0 0 100 20">
                    <path class="sparkline" d="M0,18 L20,14 L40,16 L60,10 L80,6 L100,2" fill="none" stroke="#2e7d32" stroke-width="2"></path>
                </svg>
            </div>
        </div>
    </div>

    <!-- Dashboard Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-gutter">
        <!-- Left Column: Live Shop Status & Sales Chart -->
        <div class="lg:col-span-8 space-y-gutter">
            <!-- Live Shop Status (Bento Item) -->
            <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow border border-outline-variant">
                <div class="flex justify-between items-center mb-md">
                    <div class="flex items-center gap-xs">
                        <span class="flex h-3 w-3 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-primary"></span>
                        </span>
                        <h4 class="font-headline-md text-headline-md text-on-surface mb-0">Live Shop Status</h4>
                    </div>
                    <div class="flex gap-xs">
                        <span class="font-label-sm bg-primary-fixed text-primary px-sm py-1 rounded-full">4 Registers Active</span>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-md">
                    <!-- Shop Activity Metric 1 -->
                    <div class="flex items-center gap-sm p-sm bg-surface-container-low rounded-lg">
                        <span class="material-symbols-outlined text-primary text-3xl">hourglass_empty</span>
                        <div>
                            <p class="font-label-sm text-on-surface-variant mb-0">Prep Time</p>
                            <p class="font-headline-md text-headline-md mb-0">6.4m</p>
                        </div>
                    </div>
                    <!-- Shop Activity Metric 2 -->
                    <div class="flex items-center gap-sm p-sm bg-surface-container-low rounded-lg">
                        <span class="material-symbols-outlined text-secondary text-3xl">moped</span>
                        <div>
                            <p class="font-label-sm text-on-surface-variant mb-0">Active Deliveries</p>
                            <p class="font-headline-md text-headline-md mb-0">18</p>
                        </div>
                    </div>
                    <!-- Shop Activity Metric 3 -->
                    <div class="flex items-center gap-sm p-sm bg-surface-container-low rounded-lg">
                        <span class="material-symbols-outlined text-tertiary text-3xl">sentiment_very_satisfied</span>
                        <div>
                            <p class="font-label-sm text-on-surface-variant mb-0">Customer Score</p>
                            <p class="font-headline-md text-headline-md mb-0">4.9/5</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Selling Layers (Asymmetric Bar Chart) -->
            <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow border border-outline-variant">
                <h4 class="font-headline-md text-headline-md text-on-surface mb-md">Top Selling Products</h4>
                <div class="space-y-md">
                    <?php
                    $max_qty = 1;
                    foreach ($top_selling as $ts) {
                        if ($ts['total_qty'] > $max_qty) {
                            $max_qty = $ts['total_qty'];
                        }
                    }
                    $bar_colors = ['bg-secondary', 'bg-primary-container', 'bg-tertiary-fixed-dim'];
                    $i = 0;
                    foreach ($top_selling as $ts):
                        $pct = ($ts['total_qty'] > 0) ? round(($ts['total_qty'] / $max_qty) * 100) : 35; // Fallback minimum pct
                        $color = $bar_colors[$i % count($bar_colors)];
                        $i++;
                    ?>
                        <div class="space-y-xs">
                            <div class="flex justify-between items-center">
                                <span class="font-label-md"><?= htmlspecialchars($ts['name']) ?></span>
                                <span class="font-label-md font-bold"><?= number_format($ts['total_qty']) ?> servings</span>
                            </div>
                            <div class="w-full h-8 bg-surface-container-high rounded-full overflow-hidden">
                                <div class="h-full <?= $color ?> rounded-full transition-all duration-500" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-surface-container-lowest rounded-xl custom-shadow border border-outline-variant overflow-hidden">
                <div class="p-md flex justify-between items-center border-b border-outline-variant">
                    <h4 class="font-headline-md text-headline-md text-on-surface mb-0">Recent Transaction Feed</h4>
                    <?php if (User::has_permission($_SESSION['user']['id'], 'access_pos')): ?>
                        <a href="<?= url('?route=pos/history') ?>" class="text-primary font-label-md hover:underline transition-all no-underline">View All</a>
                    <?php endif; ?>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left table-auto">
                        <thead class="bg-surface-container-low border-b border-outline-variant">
                            <tr>
                                <th class="px-md py-sm font-label-md text-on-surface-variant">Order ID</th>
                                <th class="px-md py-sm font-label-md text-on-surface-variant">Customer/Cashier</th>
                                <th class="px-md py-sm font-label-md text-on-surface-variant">Status</th>
                                <th class="px-md py-sm font-label-md text-on-surface-variant text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($recent_sales)): ?>
                                <tr>
                                    <td colspan="4" class="px-md py-md text-center text-muted">No transactions found.</td>
                                </tr>
                            <?php else: ?>
                                <?php
                                $statuses = [
                                    ['label' => 'Ready',      'bg' => '#fff3cd', 'color' => '#856404'],
                                    ['label' => 'Prep',       'bg' => '#ffe5d0', 'color' => '#9e3d00'],
                                    ['label' => 'Delivering', 'bg' => '#e8d5ff', 'color' => '#5b009e'],
                                ];
                                $si = 0;
                                foreach (array_slice($recent_sales, 0, 5) as $sale):
                                    $st = $statuses[$si % count($statuses)];
                                    $si++;
                                    // Abbreviated customer name: "Elena G."
                                    $parts = explode(' ', trim($sale['cashier_name'] ?? 'System'));
                                    $abbr = count($parts) > 1
                                        ? $parts[0] . ' ' . strtoupper(substr($parts[1], 0, 1)) . '.'
                                        : ($parts[0] ?? 'System');
                                ?>
                                    <tr class="border-b border-outline-variant hover:bg-surface-container transition-colors">
                                        <td class="px-md py-sm font-body-md" style="color:#b60e3d;font-weight:600">#SHH-<?= str_pad($sale['id'], 4, '0', STR_PAD_LEFT) ?></td>
                                        <td class="px-md py-sm font-body-md text-on-surface"><?= htmlspecialchars($abbr) ?></td>
                                        <td class="px-md py-sm">
                                            <span style="background:<?= $st['bg'] ?>;color:<?= $st['color'] ?>;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;font-family:'Inter',sans-serif;"><?= $st['label'] ?></span>
                                        </td>
                                        <td class="px-md py-sm font-body-md text-on-surface text-right font-bold">₱<?= number_format($sale['final_amount'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Right Column: Stock Warnings & Daily Flow Status -->
        <div class="lg:col-span-4 space-y-gutter">
            <!-- Low Stock Warnings -->
            <div class="bg-surface-container-lowest p-md rounded-xl custom-shadow border border-outline-variant">
                <div class="flex justify-between items-center mb-md">
                    <h4 class="font-headline-md text-headline-md text-on-surface mb-0">Stock Warnings</h4>
                    <span class="material-symbols-outlined text-error" style="font-variation-settings: 'FILL' 1;">warning</span>
                </div>
                <div class="space-y-sm">
                    <?php if (empty($low_stock)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="material-symbols-outlined text-success text-3xl mb-2">check_circle</i>
                            <p class="mb-0">All products sufficiently stocked!</p>
                        </div>
                    <?php else: ?>
                        <?php
                        $idx = 0;
                        foreach (array_slice($low_stock, 0, 3) as $item):
                            if ($idx === 0):
                        ?>
                                <div class="flex items-center justify-between p-sm bg-error-container rounded-lg">
                                    <div class="flex items-center gap-sm">
                                        <span class="material-symbols-outlined text-on-error-container">kitchen</span>
                                        <div>
                                            <p class="font-label-md text-on-error-container font-bold mb-0"><?= htmlspecialchars($item['name']) ?></p>
                                            <p class="font-label-sm text-on-error-container opacity-80 mb-0"><?= $item['stock'] ?> units left</p>
                                        </div>
                                    </div>
                                    <a href="<?= url('?route=inventory/products') ?>" class="text-xs bg-on-error-container text-white px-sm py-1 rounded-full font-bold no-underline hover:opacity-90">Restock</a>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center justify-between p-sm bg-surface-container-low rounded-lg border-l-4 border-tertiary">
                                    <div class="flex items-center gap-sm">
                                        <span class="material-symbols-outlined text-on-surface-variant">icecream</span>
                                        <div>
                                            <p class="font-label-md text-on-surface font-bold mb-0"><?= htmlspecialchars($item['name']) ?></p>
                                            <p class="font-label-sm text-on-surface-variant mb-0"><?= $item['stock'] ?> units left</p>
                                        </div>
                                    </div>
                                    <a href="<?= url('?route=inventory/products') ?>" class="text-xs text-primary font-bold no-underline hover:underline">Order Now</a>
                                </div>
                        <?php
                            endif;
                            $idx++;
                        endforeach;
                        ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Daily Flow Status (Parfait Stepper) -->
            <div class="bg-primary p-md rounded-xl custom-shadow text-on-primary">
                <h4 class="font-headline-md text-headline-md mb-md text-white">Daily Flow Status</h4>
                <div class="relative space-y-md">
                    <div class="absolute left-4 top-0 bottom-0 w-1 bg-white/20 rounded-full"></div>
                    <!-- Layer 1 -->
                    <div class="relative flex items-start gap-md pl-10">
                        <div class="absolute left-1 w-6 h-6 rounded-full bg-tertiary-fixed-dim ring-4 ring-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xs text-on-tertiary-fixed">check</span>
                        </div>
                        <div>
                            <p class="font-label-md text-white mb-0">Stock Arrival</p>
                            <p class="font-label-sm text-white/80 mb-0">Completed at 06:00 AM</p>
                        </div>
                    </div>
                    <!-- Layer 2 -->
                    <div class="relative flex items-start gap-md pl-10">
                        <div class="absolute left-1 w-6 h-6 rounded-full bg-secondary ring-4 ring-primary flex items-center justify-center">
                            <span class="material-symbols-outlined text-xs text-white">check</span>
                        </div>
                        <div>
                            <p class="font-label-md text-white mb-0">Morning Prep</p>
                            <p class="font-label-sm text-white/80 mb-0">Completed at 09:30 AM</p>
                        </div>
                    </div>
                    <!-- Layer 3 -->
                    <div class="relative flex items-start gap-md pl-10">
                        <div class="absolute left-1 w-6 h-6 rounded-full bg-white ring-4 ring-primary flex items-center justify-center">
                            <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                        </div>
                        <div>
                            <p class="font-label-md text-white mb-0">Lunch Peak Service</p>
                            <p class="font-label-sm text-white/80 mb-0">In Progress (High Volume)</p>
                        </div>
                    </div>
                    <!-- Layer 4 -->
                    <div class="relative flex items-start gap-md pl-10 opacity-50">
                        <div class="absolute left-1 w-6 h-6 rounded-full bg-white/30 ring-4 ring-primary flex items-center justify-center"></div>
                        <div>
                            <p class="font-label-md text-white mb-0">Afternoon Shift Change</p>
                            <p class="font-label-sm text-white/80 mb-0">Scheduled for 03:00 PM</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Promo Banner -->
            <div class="relative rounded-xl overflow-hidden aspect-video custom-shadow">
                <img alt="Promotional Banner" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBkncVz5QLkIbdQK04E9Y8zPOYFCrzQ_PoLiGG3BuPPUS6kFoNq5-MdbflsyuNWq-JuI3cNy7KBtpfzGTsOgYqF2bVnPKQ9aOhxnT1eurUCNZxaET1aF8hMw4rg-asZ0wu5c4l5XjNB4PXRJcxFRA7jzlVqGBH6TMZN6SkF6-Mg86eN2c9qRFJL7l3jOA-AwnKJ8kaMLQxyzI7qBQk2mC3iwnsT7RhFlqJueWZPBzGnwcBy2tmdgs3LHrFRTPMvoNBKN8_DHqZLRaQ" />
                <div class="absolute inset-0 bg-gradient-to-t from-primary/80 to-transparent flex flex-col justify-end p-md">
                    <p class="font-label-md text-white mb-0">Marketing Feature</p>
                    <h5 class="font-headline-md text-white mb-0">New Ube-Flan Fusion Trend</h5>
                    <p class="font-label-sm text-white/90 mb-0">Click to launch social promo</p>
                </div>
            </div>
        </div>
    </div>



</div>

<script>
    document.querySelectorAll('.custom-shadow-hover').forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.classList.add('scale-[1.01]', 'duration-300');
        });
        card.addEventListener('mouseleave', () => {
            card.classList.remove('scale-[1.01]');
        });
    });

    // Simulating Live POS Counter update dynamically (only if today is selected or no filter)
    let ordersCounter = <?= (int)($stats['orders_today'] ?? 0) ?>;
    let isFilterApplied = <?= (!empty($_GET['date_range'])) ? 'true' : 'false' ?>;

    if (ordersCounter > 0 && !isFilterApplied) {
        setInterval(() => {
            if (Math.random() > 0.75) {
                ordersCounter++;
                const orderCounterEl = document.getElementById('orders-today-val');
                if (orderCounterEl) {
                    orderCounterEl.textContent = ordersCounter;
                    orderCounterEl.classList.add('text-primary');
                    setTimeout(() => orderCounterEl.classList.remove('text-primary'), 800);
                }
            }
        }, 10000);
    }

    // Initialize Flatpickr
    flatpickr("#date-range-picker", {
        mode: "range",
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "M j, Y",
        onClose: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                document.getElementById('dashboard-filter-form').submit();
            }
        }
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>