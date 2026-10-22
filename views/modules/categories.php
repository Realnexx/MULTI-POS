<?php
require_once __DIR__ . '/../../models/Product.php';
$categories = $categories ?? Product::get_all_categories() ?: [];
$all_products = Product::get_all_products() ?: [];

// Pre-map products by category ID for 0ms instant modal display
$products_by_category = [];
foreach ($all_products as $p) {
    $cid = intval($p['category_id'] ?? 0);
    $products_by_category[$cid][] = [
        'id' => intval($p['id']),
        'name' => $p['name'],
        'sku' => $p['sku'] ?? '',
        'barcode' => $p['barcode'] ?? '',
        'price' => floatval($p['price'] ?? 0),
        'cost_price' => floatval($p['cost_price'] ?? 0),
        'stock' => intval($p['stock'] ?? 0),
        'status' => $p['status'] ?? 'active',
        'product_type' => $p['product_type'] ?? 'product',
        'image_url' => !empty($p['image_url']) ? $p['image_url'] : '',
        'unit_type' => $p['unit_type'] ?? ''
    ];
}

// Compute KPI Stats
$total_categories = count($categories);
$total_products = 0;
$empty_categories = 0;
$top_category = null;
$max_products = -1;

foreach ($categories as $cat) {
    $count = intval($cat['product_count'] ?? 0);
    $total_products += $count;
    if ($count === 0) {
        $empty_categories++;
    }
    if ($count > $max_products) {
        $max_products = $count;
        $top_category = $cat;
    }
}
$avg_products = $total_categories > 0 ? round($total_products / $total_categories, 1) : 0;

$title = "Product Categories Management";
ob_start();
?>

<!-- Custom Modern Styles for Categories Management -->
<style>
/* ================================================================
   AURORA POS — PRODUCT CATEGORIES MANAGEMENT MODERN STYLES
   ================================================================ */

/* Page Header */
.cat-page-header {
    margin-top: 0.25rem;
    margin-bottom: 1.5rem;
}
.cat-badge-pill {
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
    letter-spacing: 0.3px;
}
.cat-page-title {
    font-size: 1.6rem;
    font-weight: 800;
    color: #2a3547;
    letter-spacing: -0.5px;
    margin-bottom: 4px;
}
.cat-page-subtitle {
    font-size: 0.88rem;
    color: #7c8fac;
    margin-bottom: 0;
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
.cat-kpi-card {
    border-radius: 18px;
    border: 1px solid #e8edf5;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
    background: #ffffff;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    height: 100%;
}
.cat-kpi-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
}
.cat-kpi-glow {
    height: 4px;
    width: 100%;
}
.cat-kpi-icon {
    width: 46px;
    height: 46px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.cat-kpi-value {
    font-size: 1.75rem;
    font-weight: 800;
    letter-spacing: -0.5px;
    line-height: 1.1;
    color: #2a3547;
}
.cat-kpi-label {
    font-size: 0.72rem;
    font-weight: 700;
    color: #9eaac7;
    text-transform: uppercase;
    letter-spacing: 0.6px;
}

/* Add Category Primary Button */
.btn-cat-primary {
    background: linear-gradient(135deg, #5d87ff 0%, #4570ea 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 50px;
    padding: 10px 24px;
    font-weight: 700;
    font-size: 0.88rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 14px rgba(93, 135, 255, 0.35);
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}
.btn-cat-primary::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
    transition: left 0.6s ease;
}
.btn-cat-primary:hover {
    background: linear-gradient(135deg, #4d77ef 0%, #3560da 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(93, 135, 255, 0.45);
}
.btn-cat-primary:hover::after {
    left: 100%;
}
.btn-cat-primary i {
    transition: transform 0.25s ease;
}
.btn-cat-primary:hover i {
    transform: rotate(90deg) scale(1.15);
}

/* Toolbar & Filter Bar */
.cat-toolbar {
    background: #f8fafc;
    border-radius: 16px;
    padding: 12px 16px;
    border: 1px solid #eef2f6;
}
.cat-search-box {
    position: relative;
    max-width: 340px;
    width: 100%;
}
.cat-search-box input {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    padding: 8px 16px 8px 38px;
    font-size: 0.85rem;
    font-weight: 500;
    color: #2a3547;
    width: 100%;
    outline: none;
    transition: all 0.2s ease;
}
.cat-search-box input:focus {
    border-color: #5d87ff;
    box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.15);
}
.cat-search-box i {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 1rem;
    pointer-events: none;
}
.cat-search-clear {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #94a3b8;
    cursor: pointer;
    font-size: 0.9rem;
    display: none;
    padding: 0;
}
.cat-search-clear:hover {
    color: #ef4444;
}

/* Filter Pills */
.cat-filter-btn {
    border-radius: 50px;
    padding: 7px 16px;
    font-size: 0.8rem;
    font-weight: 700;
    color: #64748b;
    background: #ffffff;
    border: 1px solid #e2e8f0;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    user-select: none;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
}
.cat-filter-btn:hover {
    background: #ffffff;
    color: #5d87ff;
    border-color: #cbd5e1;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
}
.cat-filter-btn.active {
    background: linear-gradient(135deg, #5d87ff 0%, #4570ea 100%);
    color: #ffffff;
    border-color: #5d87ff;
    box-shadow: 0 3px 10px rgba(93, 135, 255, 0.35);
    transform: translateY(-1px);
}
.cat-filter-btn .badge-pill-count {
    background: rgba(0, 0, 0, 0.08);
    border-radius: 20px;
    padding: 1px 7px;
    font-size: 0.72rem;
    font-weight: 700;
}
.cat-filter-btn.active .badge-pill-count {
    background: rgba(255, 255, 255, 0.25);
    color: #ffffff;
}

/* View Switcher */
.cat-view-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #64748b;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.cat-view-btn:hover {
    background: #f8fafc;
    color: #2a3547;
    transform: translateY(-1px);
}
.cat-view-btn.active {
    background: #5d87ff;
    color: #ffffff;
    border-color: #5d87ff;
    box-shadow: 0 2px 6px rgba(93, 135, 255, 0.25);
}

/* Category Table */
.cat-table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.cat-table thead th {
    background: #f8fafc;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    padding: 14px 18px;
    border-bottom: 1px solid #edf2f7;
}
.cat-table thead th:first-child {
    border-top-left-radius: 14px;
}
.cat-table thead th:last-child {
    border-top-right-radius: 14px;
}
.cat-table tbody tr {
    transition: background 0.15s ease, transform 0.15s ease;
    border-bottom: 1px solid #f1f5f9;
}
.cat-table tbody tr:hover {
    background: #fbfcfe;
}
.cat-table tbody td {
    padding: 16px 18px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
}
.cat-id-badge {
    font-size: 0.75rem;
    font-weight: 700;
    color: #8fa0b5;
    background: #f1f5f9;
    border-radius: 8px;
    padding: 3px 8px;
    display: inline-block;
    font-family: monospace;
}
.cat-avatar {
    width: 42px;
    height: 42px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1rem;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}
.cat-name-cell {
    font-size: 0.95rem;
    font-weight: 700;
    color: #2a3547;
    margin-bottom: 2px;
}
.cat-desc-cell {
    font-size: 0.82rem;
    color: #8292a8;
    max-width: 420px;
    line-height: 1.35;
}

/* Count Badges — Clickable to View Assigned Items */
.cat-count-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    border-radius: 50px;
    padding: 6px 14px;
    font-size: 0.78rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    border: 1px solid transparent;
    text-decoration: none;
    line-height: 1.2;
}
.cat-count-badge:hover {
    transform: translateY(-2px);
}
.cat-count-badge i.ti-chevron-right {
    transition: transform 0.2s ease;
}
.cat-count-badge:hover i.ti-chevron-right {
    transform: translateX(2px);
}
.cat-count-active {
    background: #eff6ff;
    color: #2563eb;
    border-color: rgba(37, 99, 235, 0.22);
}
.cat-count-active:hover {
    background: #dbeafe;
    color: #1d4ed8;
    border-color: #93c5fd;
    box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
}
.cat-count-empty {
    background: #fffbeb;
    color: #d97706;
    border-color: rgba(217, 119, 6, 0.2);
}
.cat-count-empty:hover {
    background: #fef3c7;
    color: #b45309;
    border-color: #fde68a;
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
}

/* ================================================================
   ENHANCED ACTION BUTTONS (PILL STYLE WITH ICONS & HOVER GLOW)
   ================================================================ */
.cat-action-pill {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 14px;
    border-radius: 50px;
    font-size: 0.78rem;
    font-weight: 700;
    border: 1px solid transparent;
    transition: all 0.22s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    line-height: 1.2;
    text-decoration: none;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    white-space: nowrap;
}
.cat-action-pill i {
    font-size: 0.95rem;
    transition: transform 0.22s ease;
}

/* Edit Pill Button (Sleek Royal Blue) */
.cat-action-edit {
    background: #eff6ff;
    color: #2563eb;
    border-color: rgba(37, 99, 235, 0.22);
}
.cat-action-edit:hover {
    background: linear-gradient(135deg, #5d87ff 0%, #2563eb 100%);
    color: #ffffff !important;
    border-color: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
}
.cat-action-edit:hover i {
    transform: scale(1.15) rotate(-6deg);
}
.cat-action-edit:active {
    transform: translateY(0);
}

/* Delete Pill Button */
.cat-action-delete {
    background: #fff1f2;
    color: #e11d48;
    border-color: rgba(225, 29, 72, 0.2);
}
.cat-action-delete:hover {
    background: linear-gradient(135deg, #e11d48 0%, #f43f5e 100%);
    color: #ffffff !important;
    border-color: #e11d48;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(225, 29, 72, 0.32);
}
.cat-action-delete:hover i {
    transform: scale(1.15);
}
.cat-action-delete:active {
    transform: translateY(0);
}

/* Modal Action Buttons */
.btn-modal-cancel {
    background: #f1f5f9;
    color: #475569;
    border: 1px solid #e2e8f0;
    border-radius: 50px;
    padding: 8px 20px;
    font-weight: 700;
    font-size: 0.84rem;
    transition: all 0.2s ease;
}
.btn-modal-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
    transform: translateY(-1px);
}
.btn-modal-save {
    background: linear-gradient(135deg, #5d87ff 0%, #4570ea 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 50px;
    padding: 8px 24px;
    font-weight: 700;
    font-size: 0.84rem;
    box-shadow: 0 4px 14px rgba(93, 135, 255, 0.35);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-modal-save:hover {
    background: linear-gradient(135deg, #4d77ef 0%, #3560da 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(93, 135, 255, 0.45);
}
.btn-modal-danger {
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    color: #ffffff !important;
    border: none;
    border-radius: 50px;
    padding: 8px 24px;
    font-weight: 700;
    font-size: 0.84rem;
    box-shadow: 0 4px 14px rgba(225, 29, 72, 0.35);
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}
.btn-modal-danger:hover {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(225, 29, 72, 0.45);
}

/* Grid / Card View Mode */
.cat-grid-card {
    border-radius: 18px;
    border: 1px solid #e8edf5;
    background: #ffffff;
    box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
    padding: 20px;
    display: flex;
    flex-direction: column;
    height: 100%;
    position: relative;
    overflow: hidden;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.cat-grid-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.08);
}
.cat-grid-card-glow {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
}
.cat-grid-desc {
    font-size: 0.82rem;
    color: #7c8fac;
    line-height: 1.4;
    margin-bottom: 16px;
    flex-grow: 1;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Modal Redesign (Aurora Modal Standard) */
.aurora-modal .modal-content {
    border-radius: 24px;
    border: none;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.18);
    overflow: hidden;
}
.aurora-modal-strip {
    height: 5px;
    width: 100%;
}
.aurora-modal-strip-primary {
    background: linear-gradient(90deg, #5d87ff, #13deb9);
}
.aurora-modal-strip-warning {
    background: linear-gradient(90deg, #f59e0b, #fbbf24);
}
.aurora-modal-strip-danger {
    background: linear-gradient(90deg, #ef4444, #f87171);
}
.aurora-modal .modal-header {
    padding: 20px 24px 16px;
    border-bottom: 1px solid #f1f5f9;
}
.modal-header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.aurora-modal .modal-body {
    padding: 24px;
}
.aurora-modal .modal-footer {
    padding: 16px 24px;
    border-top: 1px solid #f1f5f9;
    background: #fafbfc;
}
.cat-form-control {
    border-radius: 12px;
    border: 1px solid #cbd5e1;
    padding: 10px 14px;
    font-size: 0.88rem;
    font-weight: 500;
    transition: all 0.2s ease;
}
.cat-form-control:focus {
    border-color: #5d87ff;
    box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.15);
}
.cat-tag-pill {
    font-size: 0.75rem;
    padding: 3px 10px;
    border-radius: 50px;
    background: #f1f5f9;
    color: #475569;
    border: 1px dashed #cbd5e1;
    cursor: pointer;
    transition: all 0.15s ease;
    user-select: none;
}
.cat-tag-pill:hover {
    background: #eff6ff;
    color: #2563eb;
    border-color: #93c5fd;
}

/* Empty State */
.cat-empty-state {
    padding: 60px 20px;
    text-align: center;
}
.cat-empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 20px;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    color: #94a3b8;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 16px;
    font-size: 2rem;
}

/* Shared dashboard and report visual treatment. */
.cat-kpi-glow { display:none; }
.cat-kpi-card { border:1px solid #e1eaf0; background:linear-gradient(145deg,#fff,#f8fbfd); box-shadow:0 5px 20px rgba(25,62,82,.045); }
.cat-kpi-label { color:#62758b; }
.cat-filter-btn,.cat-view-btn { min-height:44px; }
.cat-view-btn { min-width:44px; }
.cat-filter-btn.active,.cat-view-btn.active { background:#008aa5; border-color:#008aa5; color:#fff; box-shadow:0 3px 9px rgba(0,138,165,.2); }
.cat-filter-btn.active::before { content:'\2713'; font-weight:800; }
.cat-filter-btn.active .badge-pill-count { background:rgba(255,255,255,.2); color:#fff; }
.cat-filter-btn:focus-visible,.cat-view-btn:focus-visible,.btn-cat-primary:focus-visible { outline:3px solid #64cfe0; outline-offset:3px; }
@media(max-width:575px) { .cat-filter-btn { padding:8px 12px; font-size:.78rem; } .cat-kpi-value { font-size:1.5rem; } .cat-page-title { font-size:1.4rem; } }
@media(prefers-reduced-motion:reduce) { .cat-kpi-card,.cat-filter-btn,.cat-view-btn,.btn-cat-primary,.btn-cat-primary::after { transition:none; } .cat-kpi-card:hover,.btn-cat-primary:hover { transform:none; } }
</style>
<link rel="stylesheet" href="<?= url('assets/css/categories-responsive.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/categories-responsive.css') ?>">

<!-- Main Categories Container -->
<div class="categories-page">

    <!-- Header Section -->
    <div class="cat-page-header d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div>
            <div class="cat-badge-pill">
                <i class="ti ti-folders"></i> INVENTORY & CATALOG
            </div>
            <h3 class="cat-page-title">Product Categories</h3>
            <p class="cat-page-subtitle">Organize your menu items and retail products into structured groups for seamless POS access.</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-cat-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="ti ti-plus" style="font-size: 1.05rem;"></i> Add New Category
            </button>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row g-3 mb-4">
        <!-- 1: Total Categories -->
        <div class="col-xl-3 col-sm-6">
            <div class="cat-kpi-card">
                <div class="cat-kpi-glow" style="background: linear-gradient(90deg, #5d87ff, #13deb9);"></div>
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="cat-kpi-label mb-1">Total Categories</div>
                        <div class="cat-kpi-value"><?= number_format($total_categories) ?></div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                            <span class="text-success fw-bold"><i class="ti ti-check"></i> Active</span> groups in system
                        </div>
                    </div>
                    <div class="cat-kpi-icon" style="background: #eef2ff; color: #5d87ff;">
                        <i class="ti ti-category" style="font-size: 1.4rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2: Total Active Products -->
        <div class="col-xl-3 col-sm-6">
            <div class="cat-kpi-card">
                <div class="cat-kpi-glow" style="background: linear-gradient(90deg, #10b981, #059669);"></div>
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="cat-kpi-label mb-1">Categorized Items</div>
                        <div class="cat-kpi-value"><?= number_format($total_products) ?></div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                            Active products mapped
                        </div>
                    </div>
                    <div class="cat-kpi-icon" style="background: #ecfdf5; color: #10b981;">
                        <i class="ti ti-packages" style="font-size: 1.4rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3: Average Products / Category -->
        <div class="col-xl-3 col-sm-6">
            <div class="cat-kpi-card">
                <div class="cat-kpi-glow" style="background: linear-gradient(90deg, #0284c7, #38bdf8);"></div>
                <div class="p-4 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="cat-kpi-label mb-1">Avg Items / Group</div>
                        <div class="cat-kpi-value"><?= $avg_products ?></div>
                        <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                            Average density per category
                        </div>
                    </div>
                    <div class="cat-kpi-icon" style="background: #e0f2fe; color: #0284c7;">
                        <i class="ti ti-chart-bar" style="font-size: 1.4rem;"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4: Top Category or Empty Categories -->
        <div class="col-xl-3 col-sm-6">
            <div class="cat-kpi-card">
                <?php if ($empty_categories > 0): ?>
                    <div class="cat-kpi-glow" style="background: linear-gradient(90deg, #f59e0b, #d97706);"></div>
                    <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="cat-kpi-label mb-1">Unassigned Groups</div>
                            <div class="cat-kpi-value" style="color: #d97706;"><?= $empty_categories ?></div>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                Categories with 0 products
                            </div>
                        </div>
                        <div class="cat-kpi-icon" style="background: #fffbeb; color: #d97706;">
                            <i class="ti ti-alert-triangle" style="font-size: 1.4rem;"></i>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="cat-kpi-glow" style="background: linear-gradient(90deg, #06b6d4, #0284c7);"></div>
                    <div class="p-4 d-flex align-items-center justify-content-between">
                        <div>
                            <div class="cat-kpi-label mb-1">Top Category</div>
                            <div class="cat-kpi-value fs-5 text-truncate" style="max-width: 150px;" title="<?= htmlspecialchars($top_category['name'] ?? 'N/A') ?>">
                                <?= htmlspecialchars($top_category['name'] ?? 'N/A') ?>
                            </div>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;">
                                <span class="fw-bold text-dark"><?= intval($top_category['product_count'] ?? 0) ?></span> products assigned
                            </div>
                        </div>
                        <div class="cat-kpi-icon" style="background: #ecfeff; color: #0891b2;">
                            <i class="ti ti-award" style="font-size: 1.4rem;"></i>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Main Card Container -->
    <div class="aurora-card p-4">
        
        <!-- Controls & Filters Toolbar -->
        <div class="cat-toolbar mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3">
            
            <!-- Left: Search Box -->
            <div class="cat-search-box">
                <i class="ti ti-search"></i>
                <input type="text" id="categorySearchInput" placeholder="Search categories, description..." autocomplete="off">
                <button type="button" class="cat-search-clear" id="categorySearchClear" title="Clear search">
                    <i class="ti ti-x"></i>
                </button>
            </div>

            <!-- Center: Filter Pills -->
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="cat-filter-btn active" data-filter="all">
                    All Categories <span class="badge-pill-count" id="countAll"><?= $total_categories ?></span>
                </button>
                <button type="button" class="cat-filter-btn" data-filter="active">
                    With Products <span class="badge-pill-count" id="countActive"><?= $total_categories - $empty_categories ?></span>
                </button>
                <button type="button" class="cat-filter-btn" data-filter="empty">
                    Empty <span class="badge-pill-count" id="countEmpty"><?= $empty_categories ?></span>
                </button>
            </div>

            <!-- Right: View Toggle (Table / Grid) -->
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small me-1 d-none d-md-inline" style="font-size: 0.78rem; font-weight: 600;">VIEW:</span>
                <button type="button" class="cat-view-btn active" id="viewModeTable" title="Table View">
                    <i class="ti ti-list"></i>
                </button>
                <button type="button" class="cat-view-btn" id="viewModeGrid" title="Grid View">
                    <i class="ti ti-layout-grid"></i>
                </button>
            </div>
        </div>

        <?php if (empty($categories)): ?>
            <!-- Zero State: No categories in DB -->
            <div class="cat-empty-state">
                <div class="cat-empty-icon">
                    <i class="ti ti-folder-off"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No Categories Found</h5>
                <p class="text-muted small mb-3" style="max-width: 400px; margin: 0 auto;">
                    You haven't created any product categories yet. Create your first category to start organizing your catalog.
                </p>
                <button type="button" class="btn btn-cat-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                    <i class="ti ti-plus"></i> Create First Category
                </button>
            </div>
        <?php else: ?>

            <?php
            // Palettes for rotating category icon badges
            $palettes = [
                ['bg' => '#eff6ff', 'color' => '#2563eb', 'glow' => 'linear-gradient(90deg, #2563eb, #3b82f6)'],
                ['bg' => '#ecfdf5', 'color' => '#059669', 'glow' => 'linear-gradient(90deg, #059669, #10b981)'],
                ['bg' => '#fffbeb', 'color' => '#d97706', 'glow' => 'linear-gradient(90deg, #d97706, #f59e0b)'],
                ['bg' => '#e0f2fe', 'color' => '#0284c7', 'glow' => 'linear-gradient(90deg, #0284c7, #38bdf8)'],
                ['bg' => '#fff1f2', 'color' => '#e11d48', 'glow' => 'linear-gradient(90deg, #e11d48, #f43f5e)'],
                ['bg' => '#ecfeff', 'color' => '#0891b2', 'glow' => 'linear-gradient(90deg, #0891b2, #06b6d4)'],
                ['bg' => '#fff7ed', 'color' => '#ea580c', 'glow' => 'linear-gradient(90deg, #ea580c, #f97316)']
            ];
            ?>

            <!-- TABLE VIEW -->
            <div id="categoriesTableView" class="table-responsive">
                <table class="cat-table" id="categoriesTable">
                    <thead>
                        <tr>
                            <th class="d-none" aria-hidden="true">ID</th>
                            <th>Category Info</th>
                            <th>Description</th>
                            <th class="text-center" style="width: 160px;">Assigned Items</th>
                            <th class="text-end" style="width: 190px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoriesTableBody">
                        <?php foreach ($categories as $index => $cat): 
                            $count = intval($cat['product_count'] ?? 0);
                            $pal = $palettes[$index % count($palettes)];
                            $initial = strtoupper(mb_substr(trim($cat['name'] ?: 'C'), 0, 1));
                            $search_data = strtolower($cat['name'] . ' ' . ($cat['description'] ?? ''));
                        ?>
                            <tr class="cat-item-row" 
                                data-id="<?= $cat['id'] ?>"
                                data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                data-desc="<?= htmlspecialchars($cat['description'] ?? '', ENT_QUOTES) ?>"
                                data-count="<?= $count ?>"
                                data-search="<?= htmlspecialchars($search_data, ENT_QUOTES) ?>">
                                
                                <!-- ID -->
                                <td data-label="ID" class="d-none" aria-hidden="true">
                                    <span class="cat-id-badge">#<?= $cat['id'] ?></span>
                                </td>

                                <!-- Category Info -->
                                <td data-label="Category">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="cat-avatar" style="background: <?= $pal['bg'] ?>; color: <?= $pal['color'] ?>;">
                                            <?= $initial ?>
                                        </div>
                                        <div>
                                            <div class="cat-name-cell"><?= htmlspecialchars($cat['name']) ?></div>
                                            <div class="text-muted small d-md-none" style="font-size: 0.72rem;">
                                                <?= $count ?> items
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Description -->
                                <td data-label="Description">
                                    <?php if (!empty($cat['description'])): ?>
                                        <div class="cat-desc-cell" title="<?= htmlspecialchars($cat['description']) ?>">
                                            <?= htmlspecialchars($cat['description']) ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted fst-italic small" style="font-size: 0.78rem;">No description provided</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Items Count -->
                                <td data-label="Assigned items" class="text-center">
                                    <?php if ($count > 0): ?>
                                        <button type="button" 
                                                class="cat-count-badge cat-count-active view-category-items-btn" 
                                                title="Click to view assigned items"
                                                data-id="<?= $cat['id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                                data-count="<?= $count ?>">
                                            <i class="ti ti-box" style="font-size: 0.85rem;"></i>
                                            <span><?= number_format($count) ?> <?= $count === 1 ? 'item' : 'items' ?></span>
                                            <i class="ti ti-chevron-right ms-1 opacity-75" style="font-size: 0.72rem;"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="cat-count-badge cat-count-empty view-category-items-btn" 
                                                title="Click to view category items"
                                                data-id="<?= $cat['id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                                data-count="0">
                                            <i class="ti ti-circle-minus" style="font-size: 0.85rem;"></i>
                                            <span>0 items</span>
                                        </button>
                                    <?php endif; ?>
                                </td>

                                <!-- Actions -->
                                <td data-label="Actions" class="text-end">
                                    <div class="d-inline-flex align-items-center gap-2">
                                        <button type="button" 
                                                class="cat-action-pill cat-action-edit edit-category-btn" 
                                                title="Edit Category Details"
                                                data-id="<?= $cat['id'] ?>" 
                                                data-name="<?= htmlspecialchars($cat['name']) ?>" 
                                                data-description="<?= htmlspecialchars($cat['description'] ?? '') ?>"
                                                data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                            <i class="ti ti-pencil"></i>
                                            <span>Edit</span>
                                        </button>
                                        <button type="button" 
                                                class="cat-action-pill cat-action-delete delete-category-btn" 
                                                title="Delete Category"
                                                data-id="<?= $cat['id'] ?>" 
                                                data-name="<?= htmlspecialchars($cat['name']) ?>" 
                                                data-count="<?= $count ?>"
                                                data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                            <i class="ti ti-trash"></i>
                                            <span>Delete</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- GRID CARDS VIEW (Initially Hidden or Toggled) -->
            <div id="categoriesGridView" class="row g-3 d-none">
                <?php foreach ($categories as $index => $cat): 
                    $count = intval($cat['product_count'] ?? 0);
                    $pal = $palettes[$index % count($palettes)];
                    $initial = strtoupper(mb_substr(trim($cat['name'] ?: 'C'), 0, 1));
                    $search_data = strtolower($cat['name'] . ' ' . ($cat['description'] ?? ''));
                ?>
                    <div class="col-xl-4 col-md-6 cat-grid-item" 
                         data-id="<?= $cat['id'] ?>"
                         data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                         data-desc="<?= htmlspecialchars($cat['description'] ?? '', ENT_QUOTES) ?>"
                         data-count="<?= $count ?>"
                         data-search="<?= htmlspecialchars($search_data, ENT_QUOTES) ?>">
                        <div class="cat-grid-card">
                            <div class="cat-grid-card-glow" style="background: <?= $pal['glow'] ?>;"></div>
                            
                            <!-- Top: Avatar & ID + Name -->
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="cat-avatar" style="background: <?= $pal['bg'] ?>; color: <?= $pal['color'] ?>; width: 48px; height: 48px; font-size: 1.15rem;">
                                    <?= $initial ?>
                                </div>
                                <div>
                                    
                                    <h5 class="fw-bold text-dark mb-0 fs-6"><?= htmlspecialchars($cat['name']) ?></h5>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="cat-grid-desc">
                                <?= !empty($cat['description']) ? htmlspecialchars($cat['description']) : '<span class="text-muted fst-italic">No description set</span>' ?>
                            </div>

                            <!-- Bottom Info Strip & Enhanced Actions -->
                            <div class="cat-grid-footer d-flex align-items-center justify-content-between pt-3 border-top mt-auto gap-2">
                                <div>
                                    <?php if ($count > 0): ?>
                                        <button type="button" 
                                                class="cat-count-badge cat-count-active view-category-items-btn" 
                                                title="Click to view assigned items"
                                                data-id="<?= $cat['id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                                data-count="<?= $count ?>">
                                            <i class="ti ti-box"></i> 
                                            <span><?= number_format($count) ?> <?= $count === 1 ? 'item' : 'items' ?></span>
                                            <i class="ti ti-chevron-right ms-1 opacity-75" style="font-size: 0.72rem;"></i>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" 
                                                class="cat-count-badge cat-count-empty view-category-items-btn" 
                                                title="Click to view category items"
                                                data-id="<?= $cat['id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES) ?>"
                                                data-count="0">
                                            <i class="ti ti-circle-minus"></i> 
                                            <span>0 Items</span>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" 
                                            class="cat-action-pill cat-action-edit edit-category-btn" 
                                            title="Edit Category Details"
                                            data-id="<?= $cat['id'] ?>" 
                                            data-name="<?= htmlspecialchars($cat['name']) ?>" 
                                            data-description="<?= htmlspecialchars($cat['description'] ?? '') ?>"
                                            data-bs-toggle="modal" data-bs-target="#editCategoryModal">
                                        <i class="ti ti-pencil"></i>
                                        <span>Edit</span>
                                    </button>
                                    <button type="button" 
                                            class="cat-action-pill cat-action-delete delete-category-btn" 
                                            title="Delete Category"
                                            data-id="<?= $cat['id'] ?>" 
                                            data-name="<?= htmlspecialchars($cat['name']) ?>" 
                                            data-count="<?= $count ?>"
                                            data-bs-toggle="modal" data-bs-target="#deleteCategoryModal">
                                        <i class="ti ti-trash"></i>
                                        <span>Delete</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- No Results State (from Search/Filter) -->
            <div id="categoriesNoResults" class="cat-empty-state d-none">
                <div class="cat-empty-icon">
                    <i class="ti ti-search-off"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No Matching Categories</h5>
                <p class="text-muted small mb-3" style="max-width: 380px; margin: 0 auto;">
                    No categories matched your search term or active filter. Try resetting your search query.
                </p>
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 fw-semibold" id="resetFilterBtn">
                    <i class="ti ti-rotate"></i> Reset Filters
                </button>
            </div>

        <?php endif; ?>

    </div>
</div>

<!-- ================================================================
     MODALS: CREATE, EDIT, DELETE (AURORA DESIGN SYSTEM)
     ================================================================ -->

<!-- 1: CREATE CATEGORY MODAL -->
<div class="modal fade aurora-modal" id="createCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="aurora-modal-strip aurora-modal-strip-primary"></div>
            
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-header-icon" style="background: #eef2ff; color: #5d87ff;">
                        <i class="ti ti-folder-plus" style="font-size: 1.3rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Create New Category</h5>
                        <p class="text-muted small mb-0" style="font-size: 0.78rem;">Add a new category for POS menus and item organization</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= url('?route=inventory/categories') ?>" method="POST" id="createCategoryForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-body">
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control cat-form-control" id="create_cat_name" required placeholder="e.g. Shaved Ice, Refreshers, Pastries" autofocus>
                        
                        <!-- Quick Suggestions -->
                        <div class="d-flex align-items-center gap-1 flex-wrap mt-2">
                            <span class="text-muted small me-1" style="font-size: 0.72rem;">Quick Suggestions:</span>
                            <span class="cat-tag-pill" onclick="fillCategoryName('Halo-Halo Specials')">Halo-Halo</span>
                            <span class="cat-tag-pill" onclick="fillCategoryName('Cold Beverages')">Beverages</span>
                            <span class="cat-tag-pill" onclick="fillCategoryName('Snacks & Bites')">Snacks</span>
                            <span class="cat-tag-pill" onclick="fillCategoryName('Sweet Desserts')">Desserts</span>
                            <span class="cat-tag-pill" onclick="fillCategoryName('Extra Add-ons')">Add-ons</span>
                        </div>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small mb-1">
                            Description <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <textarea name="description" class="form-control cat-form-control" rows="3" placeholder="Briefly describe what items belong in this category..."></textarea>
                        <div class="form-text text-muted small" style="font-size: 0.75rem;">
                            Helps cashiers identify products during checkout and POS searches.
                        </div>
                    </div>
                </div>

                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-modal-save">
                        <i class="ti ti-check"></i> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2: EDIT CATEGORY MODAL -->
<div class="modal fade aurora-modal" id="editCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="aurora-modal-strip aurora-modal-strip-warning"></div>
            
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-header-icon" style="background: #fffbeb; color: #d97706;">
                        <i class="ti ti-pencil" style="font-size: 1.3rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit Category Details</h5>
                        <p class="text-muted small mb-0" style="font-size: 0.78rem;">Update category title and operational notes</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= url('?route=inventory/categories') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="category_id" id="edit_cat_id">

                <div class="modal-body">
                    <!-- Category Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small mb-1">
                            Category Name <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" id="edit_cat_name" class="form-control cat-form-control" required>
                    </div>

                    <!-- Category Description -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-dark small mb-1">
                            Description <span class="text-muted fw-normal">(Optional)</span>
                        </label>
                        <textarea name="description" id="edit_cat_desc" class="form-control cat-form-control" rows="3"></textarea>
                    </div>
                </div>

                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-modal-save">
                        <i class="ti ti-device-floppy"></i> Update Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3: DELETE CATEGORY MODAL -->
<div class="modal fade aurora-modal" id="deleteCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="aurora-modal-strip aurora-modal-strip-danger"></div>
            
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-header-icon" style="background: #fff1f2; color: #e11d48;">
                        <i class="ti ti-trash" style="font-size: 1.3rem;"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-danger mb-0">Confirm Deletion</h5>
                        <p class="text-muted small mb-0" style="font-size: 0.78rem;">Permanent removal of category</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="<?= url('?route=inventory/categories') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="category_id" id="delete_cat_id">

                <div class="modal-body text-center py-4">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px; background: #fff1f2; color: #e11d48; border-radius: 50%;">
                        <i class="ti ti-alert-triangle" style="font-size: 2rem;"></i>
                    </div>
                    
                    <h5 class="fw-bold text-dark mb-2">Delete <span id="delete_cat_name" class="text-danger"></span>?</h5>
                    
                    <div id="delete_cat_callout" class="p-3 rounded-3 text-start small mb-0" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b;">
                        <!-- Injected via JavaScript -->
                    </div>
                </div>

                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-modal-danger" id="confirmDeleteCatBtn">
                        <i class="ti ti-trash"></i> Delete Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4: CATEGORY ASSIGNED ITEMS MODAL -->
<div class="modal fade aurora-modal" id="categoryItemsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="aurora-modal-strip" style="background: linear-gradient(90deg, #2563eb, #38bdf8);"></div>
            
            <div class="modal-header d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="modal-header-icon" style="background: #eff6ff; color: #2563eb;">
                        <i class="ti ti-boxes" style="font-size: 1.35rem;"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-dark mb-0" id="catItemsModalTitle">Category Items</h5>
                            <span class="badge rounded-pill px-3 py-1 fw-bold" id="catItemsModalBadge" style="background: #eff6ff; color: #2563eb; font-size: 0.75rem;">
                                0 items
                            </span>
                        </div>
                        <p class="text-muted small mb-0" style="font-size: 0.78rem;" id="catItemsModalSubtitle">
                            Products assigned to this category
                        </p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4">
                <!-- Search Filter Inside Modal -->
                <div class="d-flex align-items-center justify-content-between mb-3 gap-2 flex-wrap" id="catItemsModalFilterBar">
                    <div class="position-relative flex-grow-1" style="max-width: 320px;">
                        <i class="ti ti-search position-absolute text-muted" style="left: 14px; top: 50%; transform: translateY(-50%); font-size: 0.95rem;"></i>
                        <input type="text" id="catModalItemSearch" class="form-control rounded-pill ps-5 py-2 small" placeholder="Filter items by name, SKU..." style="font-size: 0.84rem; border: 1px solid #e2e8f0;">
                    </div>
                    <div class="small text-muted" id="catItemsMatchSummary" style="font-size: 0.8rem;">
                        Showing all items
                    </div>
                </div>

                <!-- Items Table Container -->
                <div id="catItemsListContainer" class="table-responsive rounded-3 border" style="border-color: #f1f5f9 !important;">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                        <thead class="bg-light text-muted text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                            <tr>
                                <th class="py-3 px-3">Product Name</th>
                                <th class="py-3">SKU / Barcode</th>
                                <th class="py-3">Type</th>
                                <th class="py-3 text-end">Price</th>
                                <th class="py-3 text-center">Stock</th>
                                <th class="py-3 text-end px-3">Status</th>
                            </tr>
                        </thead>
                        <tbody id="catItemsTableBody">
                            <!-- Populated dynamically via JavaScript -->
                        </tbody>
                    </table>
                </div>

                <!-- Empty State Inside Modal -->
                <div id="catItemsEmptyState" class="text-center py-5 d-none">
                    <div class="d-inline-flex align-items-center justify-content-center mb-3" style="width: 58px; height: 58px; border-radius: 18px; background: #f8fafc; border: 1px solid #e2e8f0; color: #94a3b8;">
                        <i class="ti ti-package-off" style="font-size: 1.75rem;"></i>
                    </div>
                    <h6 class="fw-bold text-dark mb-1" id="catItemsEmptyTitle">No Products Assigned</h6>
                    <p class="text-muted small mb-3" id="catItemsEmptyDesc" style="max-width: 340px; margin: 0 auto;">
                        There are currently no active products mapped to this category.
                    </p>
                    <a href="<?= url('?route=inventory/products') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-4 fw-semibold">
                        <i class="ti ti-plus me-1"></i> Add Products in Catalog
                    </a>
                </div>
            </div>

            <div class="modal-footer d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                    Close
                </button>
                <a href="<?= url('?route=inventory/products') ?>" class="btn btn-modal-save text-decoration-none">
                    <i class="ti ti-external-link"></i> Manage in Product Catalog
                </a>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     JAVASCRIPT LOGIC: LIVE SEARCH, FILTERING, VIEW MODES & MODALS
     ================================================================ -->
<script>
function fillCategoryName(name) {
    document.getElementById('create_cat_name').value = name;
}

document.addEventListener("DOMContentLoaded", function() {
    // 0. Preloaded Category Products Map
    const categoryProductsMap = <?= json_encode($products_by_category, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?> || {};

    // 0b. Render Category Items into Modal
    function renderCategoryItems(products, categoryName, categoryCount) {
        const tbody = document.getElementById('catItemsTableBody');
        const emptyState = document.getElementById('catItemsEmptyState');
        const listContainer = document.getElementById('catItemsListContainer');
        const searchInput = document.getElementById('catModalItemSearch');
        const summary = document.getElementById('catItemsMatchSummary');
        const filterBar = document.getElementById('catItemsModalFilterBar');

        if (searchInput) searchInput.value = '';

        if (!products || products.length === 0) {
            if (listContainer) listContainer.classList.add('d-none');
            if (filterBar) filterBar.classList.add('d-none');
            if (emptyState) {
                emptyState.classList.remove('d-none');
                document.getElementById('catItemsEmptyTitle').textContent = 'No Products Assigned';
                document.getElementById('catItemsEmptyDesc').textContent = `No products are currently assigned to "${categoryName}".`;
            }
            if (summary) summary.textContent = '0 items found';
            return;
        }

        if (listContainer) listContainer.classList.remove('d-none');
        if (filterBar) filterBar.classList.remove('d-none');
        if (emptyState) emptyState.classList.add('d-none');
        if (summary) summary.textContent = `${products.length} product${products.length === 1 ? '' : 's'} assigned`;

        tbody.innerHTML = products.map(p => {
            const initial = p.name ? p.name.charAt(0).toUpperCase() : 'P';
            const imgHtml = p.image_url 
                ? `<img src="<?= url('') ?>/${p.image_url}" class="rounded-3 border" style="width: 38px; height: 38px; object-fit: cover;" onerror="this.outerHTML='<div class=\\'cat-avatar\\' style=\\'width:38px;height:38px;font-size:0.9rem;background:#eff6ff;color:#2563eb;\\'>${initial}</div>'">`
                : `<div class="cat-avatar" style="width:38px;height:38px;font-size:0.9rem;background:#eff6ff;color:#2563eb;">${initial}</div>`;
            
            const typeBadge = p.product_type === 'ingredient'
                ? `<span class="badge rounded-pill bg-warning-subtle text-warning border px-2 py-1" style="font-size:0.72rem;">Ingredient</span>`
                : (p.product_type === 'variant' 
                    ? `<span class="badge rounded-pill bg-info-subtle text-info border px-2 py-1" style="font-size:0.72rem;">Variant</span>`
                    : `<span class="badge rounded-pill bg-primary-subtle text-primary border px-2 py-1" style="font-size:0.72rem;">Standard</span>`);

            const statusBadge = p.status === 'active'
                ? `<span class="badge bg-success-subtle text-success rounded-pill px-2 py-1" style="font-size:0.72rem;"><i class="ti ti-check me-1"></i>Active</span>`
                : `<span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1" style="font-size:0.72rem;">Inactive</span>`;

            const formattedPrice = '₱' + parseFloat(p.price || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const stockText = parseInt(p.stock || 0);
            const stockBadge = stockText > 10 
                ? `<span class="fw-bold text-dark">${stockText}</span> <span class="text-muted small">${p.unit_type || 'units'}</span>`
                : (stockText > 0 
                    ? `<span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1">${stockText} low</span>`
                    : `<span class="badge bg-danger-subtle text-danger rounded-pill px-2 py-1">Out of stock</span>`);

            const searchAttr = (p.name + ' ' + (p.sku || '') + ' ' + (p.barcode || '')).toLowerCase();

            return `
                <tr class="modal-prod-item" data-search="${searchAttr}">
                    <td data-label="Product" class="py-2 px-3">
                        <div class="d-flex align-items-center gap-3">
                            ${imgHtml}
                            <div>
                                <div class="fw-bold text-dark" style="font-size:0.88rem;">${p.name}</div>
                                <div class="text-muted small" style="font-size:0.75rem;">ID #${p.id}</div>
                            </div>
                        </div>
                    </td>
                    <td data-label="SKU / Barcode" class="py-2">
                        <div class="font-monospace text-muted small" style="font-size:0.78rem;">${p.sku || '<span class="text-muted fst-italic">No SKU</span>'}</div>
                        ${p.barcode ? `<div class="text-muted" style="font-size:0.7rem;">${p.barcode}</div>` : ''}
                    </td>
                    <td data-label="Type" class="py-2">
                        ${typeBadge}
                    </td>
                    <td data-label="Price" class="py-2 text-end fw-bold text-dark">
                        ${formattedPrice}
                    </td>
                    <td data-label="Stock" class="py-2 text-center">
                        ${stockBadge}
                    </td>
                    <td data-label="Status" class="py-2 text-end px-3">
                        ${statusBadge}
                    </td>
                </tr>
            `;
        }).join('');
    }

    // 0c. Click Handler for View Assigned Items
    $(document).on('click', '.view-category-items-btn', function(e) {
        e.preventDefault();
        const catId = parseInt($(this).data('id')) || 0;
        const catName = $(this).data('name') || 'Category';
        const catCount = parseInt($(this).data('count')) || 0;

        $('#catItemsModalTitle').text(catName);
        $('#catItemsModalBadge').text(`${catCount} ${catCount === 1 ? 'item' : 'items'}`);
        $('#catItemsModalSubtitle').text(`Showing active catalog items assigned to "${catName}"`);

        const items = categoryProductsMap[catId] || [];
        renderCategoryItems(items, catName, catCount);

        const modalEl = document.getElementById('categoryItemsModal');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    });

    // 0d. Search Filter Inside Items Modal
    const modalSearch = document.getElementById('catModalItemSearch');
    if (modalSearch) {
        modalSearch.addEventListener('input', function() {
            const q = this.value.trim().toLowerCase();
            const rows = document.querySelectorAll('.modal-prod-item');
            let matchCount = 0;
            rows.forEach(r => {
                const text = r.getAttribute('data-search') || '';
                const match = q.length === 0 || text.includes(q);
                r.classList.toggle('d-none', !match);
                if (match) matchCount++;
            });
            const summary = document.getElementById('catItemsMatchSummary');
            if (summary) {
                summary.textContent = q.length > 0 ? `${matchCount} matching items` : `${rows.length} items`;
            }
        });
    }

    // 1. Edit Category Button Handler
    $(".edit-category-btn").click(function() {
        $("#edit_cat_id").val($(this).data('id'));
        $("#edit_cat_name").val($(this).data('name'));
        $("#edit_cat_desc").val($(this).data('description'));
    });

    // 2. Delete Category Button Handler
    $(".delete-category-btn").click(function() {
        const count = parseInt($(this).data('count')) || 0;
        const name = $(this).data('name');
        $("#delete_cat_id").val($(this).data('id'));
        $("#delete_cat_name").text('"' + name + '"');

        if (count > 0) {
            $("#delete_cat_callout").html(`
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-alert-circle text-danger mt-1 fs-5"></i>
                    <div>
                        <strong>Attention:</strong> There are currently <strong>${count} product(s)</strong> assigned to this category.
                        Deleting this category will unlink them and mark them as <em>Uncategorized</em>.
                    </div>
                </div>
            `);
        } else {
            $("#delete_cat_callout").html(`
                <div class="d-flex align-items-start gap-2">
                    <i class="ti ti-circle-check text-success mt-1 fs-5"></i>
                    <div>
                        This category has no assigned products. It can be safely removed without affecting any inventory items.
                    </div>
                </div>
            `);
        }
    });

    // 3. Search & Live Filtering Logic
    const searchInput = document.getElementById('categorySearchInput');
    const searchClear = document.getElementById('categorySearchClear');
    const filterBtns = document.querySelectorAll('.cat-filter-btn');
    const resetBtn = document.getElementById('resetFilterBtn');
    
    let activeFilter = 'all'; // 'all', 'active', 'empty'

    function filterCategories() {
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        
        // Show/hide clear button
        if (searchClear) {
            searchClear.style.display = query.length > 0 ? 'block' : 'none';
        }

        const tableRows = document.querySelectorAll('.cat-item-row');
        const gridItems = document.querySelectorAll('.cat-grid-item');
        let visibleCount = 0;

        function checkItem(el) {
            const count = parseInt(el.getAttribute('data-count')) || 0;
            const searchData = el.getAttribute('data-search') || '';

            // Filter condition
            let passesFilter = true;
            if (activeFilter === 'active' && count <= 0) {
                passesFilter = false;
            } else if (activeFilter === 'empty' && count > 0) {
                passesFilter = false;
            }

            // Search query condition
            let passesSearch = true;
            if (query.length > 0 && !searchData.includes(query)) {
                passesSearch = false;
            }

            const visible = passesFilter && passesSearch;
            el.classList.toggle('d-none', !visible);
            return visible;
        }

        tableRows.forEach(row => {
            if (checkItem(row)) visibleCount++;
        });

        gridItems.forEach(item => {
            checkItem(item);
        });

        // Show/hide no results banner
        const noResults = document.getElementById('categoriesNoResults');
        const tableView = document.getElementById('categoriesTableView');
        const gridView = document.getElementById('categoriesGridView');

        if (noResults) {
            if (visibleCount === 0) {
                noResults.classList.remove('d-none');
                if (tableView) tableView.classList.add('d-none');
                if (gridView) gridView.classList.add('d-none');
            } else {
                noResults.classList.add('d-none');
                // Restore active view
                const isGrid = document.getElementById('viewModeGrid').classList.contains('active');
                if (isGrid) {
                    if (gridView) gridView.classList.remove('d-none');
                    if (tableView) tableView.classList.add('d-none');
                } else {
                    if (tableView) tableView.classList.remove('d-none');
                    if (gridView) gridView.classList.add('d-none');
                }
            }
        }
    }

    if (searchInput) {
        searchInput.addEventListener('input', filterCategories);
    }
    if (searchClear) {
        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            filterCategories();
            searchInput.focus();
        });
    }

    function syncCategorySelection() {
        document.querySelectorAll('.cat-filter-btn, .cat-view-btn').forEach(button => {
            button.setAttribute('aria-pressed', String(button.classList.contains('active')));
        });
    }
    syncCategorySelection();
    // Filter Buttons Click
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            syncCategorySelection();
            activeFilter = this.getAttribute('data-filter') || 'all';
            filterCategories();
        });
    });

    if (resetBtn) {
        resetBtn.addEventListener('click', function() {
            if (searchInput) searchInput.value = '';
            activeFilter = 'all';
            filterBtns.forEach(b => {
                b.classList.toggle('active', b.getAttribute('data-filter') === 'all');
                b.setAttribute('aria-pressed', String(b.classList.contains('active')));
            });
            filterCategories();
        });
    }

    // 4. View Mode Switcher (Table vs Grid)
    const btnTable = document.getElementById('viewModeTable');
    const btnGrid = document.getElementById('viewModeGrid');
    const tableView = document.getElementById('categoriesTableView');
    const gridView = document.getElementById('categoriesGridView');

    function setViewMode(mode) {
        if (!btnTable || !btnGrid) return;

        if (mode === 'grid') {
            btnGrid.classList.add('active');
            btnTable.classList.remove('active');
            if (gridView) gridView.classList.remove('d-none');
            if (tableView) tableView.classList.add('d-none');
            localStorage.setItem('aurora_category_view_mode', 'grid');
        } else {
            btnTable.classList.add('active');
            btnGrid.classList.remove('active');
            if (tableView) tableView.classList.remove('d-none');
            if (gridView) gridView.classList.add('d-none');
            localStorage.setItem('aurora_category_view_mode', 'table');
        }
        syncCategorySelection();
        filterCategories();
    }

    if (btnTable && btnGrid) {
        btnTable.addEventListener('click', () => setViewMode('table'));
        btnGrid.addEventListener('click', () => setViewMode('grid'));

        // Restore saved preference
        const savedView = localStorage.getItem('aurora_category_view_mode');
        if (savedView === 'grid') {
            setViewMode('grid');
        }
    }
});
</script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.css">
<script defer src="https://cdn.jsdelivr.net/npm/aos@2.3.4/dist/aos.js"></script>
<script defer src="<?= url('assets/js/dashboard-motion.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/dashboard-motion.js') ?>"></script>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
