<?php
require_once __DIR__ . '/../../models/Product.php';
$categories = $categories ?? Product::get_all_categories() ?: [];
$products = $products ?? Product::get_all_products() ?: [];

$is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
$title = "Inventory Products Management";
ob_start();
?>

<!-- Header Section -->
<div class="row align-items-center mb-4 g-3">
    <div class="col-md-auto me-auto">
        <h4 class="fw-bold outfit-font text-dark mb-1">Inventory Management</h4>
        <p class="text-muted small mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2">v2.4</span>
            Catalog orchestration and stock monitoring ecosystem.
        </p>
    </div>
    <div class="col-md-auto d-flex gap-2">
        <button type="button"
            class="btn btn-outline-dark rounded-pill px-3 fw-medium d-flex align-items-center gap-2 shadow-sm border-light-gray bg-white">
            <i class="fa-solid fa-file-export text-muted"></i> Export CSV
        </button>
        <?php if (User::has_permission($_SESSION['user']['id'], 'add_product')): ?>
            <button type="button"
                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#createProductModal">
                <i class="fa-solid fa-plus-circle"></i> New Product
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Overview Cards -->
<?php
$total_items = count($products);
$low_stock_count = 0;
$total_value = 0;
foreach ($products as $p) {
    if ($p['stock'] <= $p['low_stock_threshold'])
        $low_stock_count++;
    $total_value += ($p['price'] * $p['stock']);
}
?>
<div class="row g-4 mb-4">
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden"
            style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fa-solid fa-boxes-stacked fs-1 text-white"></i>
                </div>
                <div class="text-white-50 small text-uppercase fw-bold mb-1">Total Catalog</div>
                <h2 class="text-white fw-bold mb-0 outfit-font"><?= number_format($total_items) ?> <span
                        class="fs-6 fw-normal op-7">Items</span></h2>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold fs-8">Active
                        Inventory</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-6">
        <div class="card border-0 rounded-4 shadow-sm h-100 bg-white border border-light">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 text-warning opacity-10">
                    <i class="fa-solid fa-triangle-exclamation fs-1"></i>
                </div>
                <div class="text-muted small text-uppercase fw-bold mb-1">Low Stock Alerts</div>
                <h2 class="text-dark fw-bold mb-0 outfit-font"><?= number_format($low_stock_count) ?> <span
                        class="fs-6 fw-normal text-muted">Alerts</span></h2>
                <div class="mt-3">
                    <a href="<?= url('?route=inventory/low_stock') ?>"
                        class="text-primary text-decoration-none small fw-bold">View critical items <i
                            class="fa-solid fa-circle-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-4 col-md-12">
        <div class="card border-0 rounded-4 shadow-sm h-100 bg-white border border-light">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 text-primary opacity-10">
                    <i class="fa-solid fa-chart-line fs-1"></i>
                </div>
                <div class="text-muted small text-uppercase fw-bold mb-1">Inventory Valuation</div>
                <h2 class="text-dark fw-bold mb-0 outfit-font">₱<?= number_format($total_value, 2) ?></h2>
                <div class="mt-3">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-8">Current Asset
                        Value</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orchestration Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-white border border-light overflow-hidden">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-modern border rounded-pill overflow-hidden shadow-xs px-2"
                    style="background: #f8fafc;">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i
                            class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="inventorySearch"
                        class="form-control bg-transparent border-0 shadow-none py-2 fs-7"
                        placeholder="Search catalog by name, ID or description...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="categoryFilter" class="form-select border rounded-pill shadow-xs py-2 fs-7 bg-white">
                    <option value="all" <?= ($category_id === 0) ? 'selected' : '' ?>>All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat['name']) ?>" <?= ($category_id == $cat['id']) ? 'selected' : '' ?> data-id="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <select id="branchFilter" class="form-select border rounded-pill shadow-xs py-2 fs-7 bg-white">
                    <option value="0" <?= ($branch_filter === 0) ? 'selected' : '' ?>>All Branches</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= $branch['id'] ?>" <?= ($branch_filter == $branch['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($branch['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-1">
                <select id="stockFilter" class="form-select border rounded-pill shadow-xs py-2 fs-7 bg-white p-1">
                    <option value="all">Stock</option>
                    <option value="low">Low</option>
                    <option value="out">Out</option>
                </select>
            </div>
            <div class="col-md-2 text-end">
                <button type="button" id="clearFilters"
                    class="btn btn-outline-light-gray text-dark rounded-pill px-2 fw-semibold w-100 shadow-xs border-light-gray d-flex align-items-center justify-content-center gap-1"
                    style="height: 40px;">
                    <i class="fa-solid fa-rotate-right text-muted smaller"></i> Clear
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Products Table Card -->
<div class="card border-0 rounded-4 shadow-sm p-0 bg-white border border-light overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7 text-muted fw-bold outfit-font">
                <tr>
                    <th class="py-3 px-4 rounded-start-3">Item</th>
                    <th class="py-3">Category</th>
                    <th class="py-3">Price</th>
                    <th class="py-3">Cost Price</th>
                    <th class="py-3">Stock Level</th>
                    <th class="py-3 text-center">Badges</th>
                    <th class="py-3 px-4 rounded-end-3 text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">No products found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $prod):
                        $is_low = $prod['stock'] <= $prod['low_stock_threshold'];
                        $is_out = $prod['stock'] <= 0;
                        $stock_percent = $prod['stock'] > 0 ? min(100, ($prod['stock'] / ($prod['low_stock_threshold'] * 4)) * 100) : 0;
                        $stock_color = $is_out ? 'bg-danger' : ($is_low ? 'bg-warning' : 'bg-success');
                        ?>
                        <tr class="product-row"
                            data-category="<?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?>"
                            data-stock="<?= $is_out ? 'out' : ($is_low ? 'low' : 'normal') ?>">
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle rounded-3 overflow-hidden flex-shrink-0 bg-secondary-subtle d-flex align-items-center justify-content-center"
                                        style="width: 52px; height: 52px; border: 2px solid #f1f5f9;">
                                        <img src="<?= get_product_image($prod['image_url'], $prod['id']) ?>"
                                            class="w-100 h-100 object-fit-cover product-img-preview" alt=""
                                            onerror="this.src='assets/img/placeholder.png'">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark outfit-font fs-7 mb-0">
                                            <?= htmlspecialchars($prod['name']) ?>
                                        </div>
                                        <div class="text-muted smaller fw-medium mb-1">SKU:
                                            #PROD-<?= str_pad($prod['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                        <div class="fs-8 text-muted d-block text-truncate" style="max-width: 180px;">
                                            <?= htmlspecialchars($prod['description']) ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3">
                                <span class="badge bg-light text-dark rounded-pill px-3 py-1 fw-medium border shadow-xs fs-8">
                                    <i class="fa-solid fa-tag text-muted me-1 small"></i>
                                    <?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?>
                                </span>
                            </td>
                            <td class="py-3">
                                <div class="fw-extrabold text-primary mb-0">₱<?= number_format($prod['price'], 2) ?></div>
                                <div class="text-muted smaller fw-semibold mt-n1" style="font-size: 10px;">PROFIT:
                                    ₱<?= number_format($prod['price'] - $prod['cost_price'], 2) ?></div>
                            </td>
                            <td class="py-3">
                                <div class="text-muted fw-bold outfit-font mb-0">₱<?= number_format($prod['cost_price'], 2) ?>
                                </div>
                                <div class="text-muted smaller" style="font-size: 10px;">ACQUISITION</div>
                            </td>
                            <td class="py-3" style="min-width: 160px;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <span
                                        class="fw-bold fs-7 <?= $is_out ? 'text-danger' : ($is_low ? 'text-warning' : 'text-success') ?>">
                                        <?= $prod['stock'] ?> <span class="fw-normal text-muted smaller">Total</span>
                                    </span>
                                    <span class="text-muted smaller"
                                        style="font-size: 10px;"><?= round($stock_percent) ?>%</span>
                                </div>
                                <div class="progress rounded-pill bg-light mb-2" style="height: 6px;">
                                    <div class="progress-bar <?= $stock_color ?> progress-bar-striped progress-bar-animated"
                                        role="progressbar" style="width: <?= $stock_percent ?>%"></div>
                                </div>
                                <?php
                                $branch_stocks = Product::get_branch_stocks($prod['id']);
                                foreach ($branch_stocks as $bs):
                                    $perf = $branch_performance[$bs['id']][$prod['id']] ?? null;
                                    if ($bs['branch_stock'] > 0 || count($branch_stocks) <= 1): // Show if has stock, or if only one branch exists
                                        ?>
                                        <div class="d-flex justify-content-between align-items-center fs-9 text-muted mb-0">
                                            <div class="d-flex align-items-center gap-1">
                                                <span><?= htmlspecialchars($bs['name']) ?>:</span>
                                                <?php if ($perf): ?>
                                                    <span
                                                        class="badge-performance badge-performance-sm <?= ($perf === 'Best Seller') ? 'badge-best-seller' : 'badge-popular' ?>">
                                                        <i
                                                            class="fa-solid <?= ($perf === 'Best Seller') ? 'fa-crown' : 'fa-fire' ?>"></i><?= $perf ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <span class="fw-bold"><?= $bs['branch_stock'] ?></span>
                                        </div>
                                        <?php
                                    endif;
                                endforeach;
                                ?>
                            </td>
                            <td class="py-3 text-center">
                                <div class="d-flex flex-wrap justify-content-center gap-1">
                                    <?php
                                    $global_perf = $global_performance[$prod['id']] ?? null;
                                    if ($global_perf === 'Best Seller'):
                                        ?>
                                        <div class="badge-performance badge-best-seller" title="Overall Top Seller">
                                            <i class="fa-solid fa-crown"></i> BEST
                                        </div>
                                    <?php elseif ($global_perf === 'Popular'): ?>
                                        <div class="badge-performance badge-popular" title="High Sales Volume">
                                            <i class="fa-solid fa-fire"></i> POPULAR
                                        </div>
                                    <?php else: ?>
                                        <span class="text-muted smaller opacity-50">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <div class="dropdown">
                                    <button
                                        class="btn btn-light btn-sm rounded-circle d-inline-flex align-items-center justify-content-center p-2 shadow-xs border-light-gray"
                                        type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fa-solid fa-ellipsis-vertical text-muted"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end border-0 shadow-lg rounded-4 p-2">
                                        <li>
                                            <h6 class="dropdown-header text-uppercase fs-8 text-muted fw-bold px-3">Quick
                                                Actions</h6>
                                        </li>
                                        <?php if (User::has_permission($_SESSION['user']['id'], 'edit_product')): ?>
                                            <li>
                                                <button class="dropdown-item rounded-3 py-2 edit-product-btn"
                                                    data-id="<?= $prod['id'] ?>" data-category="<?= $prod['category_id'] ?>"
                                                    data-name="<?= htmlspecialchars($prod['name']) ?>"
                                                    data-description="<?= htmlspecialchars($prod['description']) ?>"
                                                    data-price="<?= $prod['price'] ?>" data-cost="<?= $prod['cost_price'] ?>"
                                                    data-stock="<?= $prod['stock'] ?>"
                                                    data-threshold="<?= $prod['low_stock_threshold'] ?>"
                                                    data-featured="<?= $prod['is_featured'] ?>"
                                                    data-bestseller="<?= $prod['is_best_seller'] ?>" data-bs-toggle="modal"
                                                    data-bs-target="#editProductModal">
                                                    <i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Details
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                        <li>
                                            <a class="dropdown-item rounded-3 py-2"
                                                href="<?= url('?route=inventory/logs&product_id=' . $prod['id']) ?>">
                                                <i class="fa-solid fa-clock-rotate-left text-info me-2"></i> History Logs
                                            </a>
                                        </li>
                                        <?php if (User::has_permission($_SESSION['user']['id'], 'delete_product')): ?>
                                            <li>
                                                <hr class="dropdown-divider opacity-50">
                                            </li>
                                            <li>
                                                <button class="dropdown-item rounded-3 py-2 text-danger delete-product-btn"
                                                    data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>"
                                                    data-bs-toggle="modal" data-bs-target="#deleteProductModal">
                                                    <i class="fa-solid fa-trash-can me-2"></i> Archive Product
                                                </button>
                                            </li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create Product Modal -->
<div class="modal fade" id="createProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-box-open fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold outfit-font text-dark mb-0">Add New Product</h5>
                        <p class="text-muted smaller mb-0">Expand your catalog with a new item.</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/products') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-dark opacity-75">Product Name</label>
                            <input type="text" name="name"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" required
                                placeholder="e.g., Special Halo Halo">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-dark opacity-75">Category</label>
                            <select name="category_id"
                                class="form-select rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" required>
                                <option value="" disabled selected>Select Category</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Description</label>
                        <textarea name="description" class="form-control rounded-4 p-3 shadow-xs" rows="3"
                            placeholder="Delicious blend of fresh mangoes..."></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Selling Price</label>
                            <div class="input-group input-group-modern shadow-xs rounded-3 overflow-hidden">
                                <span
                                    class="input-group-text bg-light border-light-gray text-muted fs-7 fw-bold">₱</span>
                                <input type="number" name="price" class="form-control border-light-gray py-2 fs-7"
                                    step="0.01" min="0" required placeholder="0.00">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Cost Price</label>
                            <div class="input-group input-group-modern shadow-xs rounded-3 overflow-hidden">
                                <span
                                    class="input-group-text bg-light border-light-gray text-muted fs-7 fw-bold">₱</span>
                                <input type="number" name="cost_price" class="form-control border-light-gray py-2 fs-7"
                                    step="0.01" min="0" placeholder="0.00">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Initial Stock</label>
                            <input type="number" name="stock"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 fs-7" min="0" required
                                placeholder="0">
                        </div>
                        <?php if ($is_admin): ?>
                            <div class="col-md-6">
                                <label class="form-label fw-bold small text-dark opacity-75">Target Branch</label>
                                <select name="branch_id" class="form-select rounded-3 border-light-gray shadow-xs py-2 fs-7"
                                    required>
                                    <?php foreach ($branches as $branch): ?>
                                        <option value="<?= $branch['id'] ?>" <?= (($_SESSION['user']['branch_id'] ?? null) == $branch['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($branch['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                            <?php else: ?>
                                <div class="col-md-6">
                                <?php endif; ?>
                                <label class="form-label fw-bold small text-dark opacity-75">Low Stock Alert</label>
                                <input type="number" name="low_stock_threshold"
                                    class="form-control rounded-3 border-light-gray shadow-xs py-2 fs-7" min="0"
                                    value="10" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-dark opacity-75">Product Cover Image</label>
                            <div class="p-3 border-2 border-dashed border-light-gray rounded-4 text-center bg-light-gray position-relative"
                                style="cursor: pointer;">
                                <i class="fa-solid fa-cloud-arrow-up fs-4 text-muted mb-2 d-block"></i>
                                <span class="text-muted smaller d-block fw-medium">Click or drag image here</span>
                                <input type="file" name="image"
                                    class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                    style="cursor: pointer;" accept="image/*">
                            </div>
                        </div>

                        <div class="d-flex gap-4 p-3 bg-light-gray rounded-4 border border-light">
                            <div class="form-check custom-checkbox mb-0">
                                <input class="form-check-input" type="checkbox" name="is_featured" value="1"
                                    id="feat_create">
                                <label class="form-check-label fw-medium text-dark small" for="feat_create">Featured
                                    Product</label>
                            </div>
                            <div class="form-check custom-checkbox mb-0">
                                <input class="form-check-input" type="checkbox" name="is_best_seller" value="1"
                                    id="best_create">
                                <label class="form-check-label fw-medium text-dark small" for="best_create">Best
                                    Seller</label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                        <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Create
                            Product</button>
                    </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Product Modal -->
<div class="modal fade" id="editProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-pen-nib fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold outfit-font text-dark mb-0">Edit Product</h5>
                        <p class="text-muted smaller mb-0">Modify attributes for <span id="edit_display_name"
                                class="fw-bold text-dark"></span></p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/products') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="product_id" id="edit_product_id">

                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-7">
                            <label class="form-label fw-bold small text-dark opacity-75">Product Name</label>
                            <input type="text" name="name" id="edit_prod_name"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold small text-dark opacity-75">Category</label>
                            <select name="category_id" id="edit_prod_cat"
                                class="form-select rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Description</label>
                        <textarea name="description" id="edit_prod_desc" class="form-control rounded-4 p-3 shadow-xs"
                            rows="3"></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Selling Price</label>
                            <div class="input-group input-group-modern shadow-xs rounded-3 overflow-hidden">
                                <span
                                    class="input-group-text bg-light border-light-gray text-muted fs-7 fw-bold">₱</span>
                                <input type="number" name="price" id="edit_prod_price"
                                    class="form-control border-light-gray py-2 fs-7" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Cost Price</label>
                            <div class="input-group input-group-modern shadow-xs rounded-3 overflow-hidden">
                                <span
                                    class="input-group-text bg-light border-light-gray text-muted fs-7 fw-bold">₱</span>
                                <input type="number" name="cost_price" id="edit_prod_cost"
                                    class="form-control border-light-gray py-2 fs-7" step="0.01" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Stock Position</label>
                            <input type="number" name="stock" id="edit_prod_stock"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 fs-7" min="0" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Alert Threshold</label>
                            <input type="number" name="low_stock_threshold" id="edit_prod_thresh"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 fs-7" min="0" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark opacity-75">Update Cover Image</label>
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-circle rounded-3 overflow-hidden flex-shrink-0 bg-light border"
                                style="width: 80px; height: 80px;">
                                <img id="edit_img_preview" src="" class="w-100 h-100 object-fit-cover"
                                    onerror="this.src='assets/img/placeholder.png'">
                            </div>
                            <div class="flex-grow-1 p-3 border-2 border-dashed border-light-gray rounded-4 text-center bg-light-gray position-relative"
                                style="cursor: pointer;">
                                <i class="fa-solid fa-camera-rotate fs-4 text-muted mb-2 d-block"></i>
                                <span class="text-muted smaller d-block fw-medium">Change image or leave blank</span>
                                <input type="file" name="image"
                                    class="position-absolute top-0 start-0 w-100 h-100 opacity-0"
                                    style="cursor: pointer;" accept="image/*"
                                    onchange="document.getElementById('edit_img_preview').src = window.URL.createObjectURL(this.files[0])">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-4 p-3 bg-light-gray rounded-4 border border-light">
                        <div class="form-check custom-checkbox mb-0">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="feat_edit">
                            <label class="form-check-label fw-medium text-dark small" for="feat_edit">Featured
                                Product</label>
                        </div>
                        <div class="form-check custom-checkbox mb-0">
                            <input class="form-check-input" type="checkbox" name="is_best_seller" value="1"
                                id="best_edit">
                            <label class="form-check-label fw-medium text-dark small" for="best_edit">Best
                                Seller</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Save
                        Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Product Modal -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-trash-can fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold outfit-font text-dark mb-0">Confirm Deletion</h5>
                        <p class="text-muted smaller mb-0">Protective deletion protocol.</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/products') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" id="delete_prod_id">

                <div class="modal-body p-4 text-center">
                    <i class="fa-solid fa-triangle-exclamation fs-1 text-danger mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">Delete <span id="delete_prod_name"></span>?</h5>
                    <p class="text-muted small mb-0">This action will remove the product from the catalog. Related sales
                        items and inventory logs will remain intact for reporting.</p>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">Delete
                        Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .badge-custom {
        font-size: 9px;
        font-weight: 800;
        padding: 4px 8px;
        border-radius: 6px;
        display: inline-flex;
        align-items: center;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .dropdown-item i {
        width: 20px;
        font-size: 0.9rem;
    }

    .progress {
        transition: all 0.3s ease;
    }

    .product-row {
        transition: all 0.2s ease;
        cursor: default;
    }

    .product-row:hover {
        background-color: #f8fafc !important;
    }

    .product-img-preview {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .product-row:hover .product-img-preview {
        transform: scale(1.1);
    }

    .smaller {
        font-size: 0.75rem;
    }

    .fs-7 {
        font-size: 0.85rem;
    }

    .shadow-xs {
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Edit Button Logic
        $(".edit-product-btn").click(function () {
            $("#edit_product_id").val($(this).data('id'));
            $("#edit_prod_cat").val($(this).data('category'));
            $("#edit_prod_name").val($(this).data('name'));
            $("#edit_prod_desc").val($(this).data('description'));
            $("#edit_display_name").text($(this).data('name'));
            $("#edit_prod_price").val($(this).data('price'));
            $("#edit_prod_cost").val($(this).data('cost'));
            $("#edit_prod_stock").val($(this).data('stock'));
            $("#edit_prod_thresh").val($(this).data('threshold'));
            $("#feat_edit").prop('checked', $(this).data('featured') == 1);
            $("#best_edit").prop('checked', $(this).data('bestseller') == 1);

            // Update edit image preview
            const rowImg = $(this).closest('tr').find('.product-img-preview').attr('src');
            $("#edit_img_preview").attr('src', rowImg);
        });

        // Delete Button Logic
        $(".delete-product-btn").click(function () {
            $("#delete_prod_id").val($(this).data('id'));
            $("#delete_prod_name").text($(this).data('name'));
        });

        // Real-time Search and Filtering
        const searchInput = document.getElementById('inventorySearch');
        const categoryFilter = document.getElementById('categoryFilter');
        const branchFilter = document.getElementById('branchFilter');
        const stockFilter = document.getElementById('stockFilter');
        const clearBtn = document.getElementById('clearFilters');
        const rows = document.querySelectorAll('.product-row');

        function filterInventory() {
            const query = searchInput.value.toLowerCase();
            const stockStatus = stockFilter.value;

            rows.forEach(row => {
                const text = row.innerText.toLowerCase();
                const rowStock = row.getAttribute('data-stock');

                const matchesSearch = text.includes(query);
                const matchesStock = stockStatus === 'all' || rowStock === stockStatus;

                if (matchesSearch && matchesStock) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // Branch and Category filters trigger a page reload for server-side filtering
        function applyServerFilters() {
            const branchId = branchFilter.value;
            const categoryId = categoryFilter.options[categoryFilter.selectedIndex].getAttribute('data-id') || 0;
            const search = searchInput.value;

            let url = '?route=inventory/products';
            if (branchId > 0) url += '&branch=' + branchId;
            if (categoryId > 0) url += '&category=' + categoryId;
            if (search) url += '&search=' + encodeURIComponent(search);

            window.location.href = url;
        }

        branchFilter.addEventListener('change', applyServerFilters);
        categoryFilter.addEventListener('change', applyServerFilters);

        // Quick client-side filters
        searchInput.addEventListener('input', filterInventory);
        stockFilter.addEventListener('change', filterInventory);

        // Clear All Filters
        clearBtn.addEventListener('click', function () {
            window.location.href = '?route=inventory/products';
        });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>