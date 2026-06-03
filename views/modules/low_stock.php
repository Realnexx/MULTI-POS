<?php
require_once __DIR__ . '/../../models/Product.php';
$low_stock = $low_stock ?? Product::get_low_stock_products() ?: [];

$title = "Low Stock Inventory Alerts";
ob_start();
?>

<!-- Header & Action -->
<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold outfit-font text-dark mb-1">Low Stock Alerts</h4>
        <p class="text-muted small mb-0">Items currently at or below their designated restock threshold requiring immediate attention.</p>
    </div>
    <a href="<?= url('?route=inventory/logs') ?>" class="btn btn-outline-primary rounded-pill px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
        <i class="fa-solid fa-clock-rotate-left"></i> View Audit Logs
    </a>
</div>

<!-- Low Stock Table -->
<div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light text-uppercase fs-7 text-muted fw-bold outfit-font">
                <tr>
                    <th class="py-3 px-4 rounded-start-3">Item</th>
                    <th class="py-3">Category</th>
                    <th class="py-3 text-center">Current Stock</th>
                    <th class="py-3 text-center">Threshold</th>
                    <th class="py-3">Status</th>
                    <th class="py-3 px-4 rounded-end-3 text-end">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($low_stock)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-check-circle fs-1 text-success mb-3 d-block"></i>
                            <h5 class="fw-bold text-dark mb-1">All Inventory Stable</h5>
                            <p class="small mb-0">No products are currently below their low stock threshold.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($low_stock as $prod): ?>
                        <tr>
                            <td class="py-3 px-4">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="avatar-circle bg-light rounded-3 overflow-hidden flex-shrink-0" style="width: 48px; height: 48px;">
                                        <img src="<?= get_product_image($prod['image_url'], $prod['id']) ?>" class="w-100 h-100 object-fit-cover" alt="">
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark outfit-font"><?= htmlspecialchars($prod['name']) ?></div>
                                        <div class="fs-8 text-muted">#<?= $prod['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3 text-muted"><?= htmlspecialchars($prod['category_name'] ?? 'Uncategorized') ?></td>
                            <td class="py-3 text-center">
                                <span class="fw-extrabold fs-5 <?= $prod['stock'] <= 0 ? 'text-danger' : 'text-warning' ?>"><?= $prod['stock'] ?></span>
                            </td>
                            <td class="py-3 text-center text-muted fw-semibold"><?= $prod['low_stock_threshold'] ?></td>
                            <td class="py-3">
                                <?php if ($prod['stock'] <= 0): ?>
                                    <span class="badge bg-danger rounded-pill px-3 py-1 fw-bold fs-7">Sold Out</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-1 fw-bold fs-7">Needs Restock</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-end">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-medium quick-restock-btn" 
                                        data-id="<?= $prod['id'] ?>" 
                                        data-name="<?= htmlspecialchars($prod['name']) ?>" 
                                        data-stock="<?= $prod['stock'] ?>"
                                        data-bs-toggle="modal" data-bs-target="#quickRestockModal">
                                    <i class="fa-solid fa-bolt me-1"></i> Quick Restock
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Quick Restock Modal -->
<div class="modal fade" id="quickRestockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary-subtle border-0 p-4">
                <h5 class="modal-title fw-bold outfit-font text-dark">Quick Inventory Restock</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/low_stock') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="quick_restock">
                <input type="hidden" name="product_id" id="restock_prod_id">
                
                <div class="modal-body p-4">
                    <div class="mb-3 p-3 bg-light-gray rounded-4 border border-light">
                        <span class="text-muted small d-block mb-1">Restocking Product</span>
                        <h5 class="fw-bold outfit-font text-dark mb-0" id="restock_prod_name"></h5>
                        <span class="fs-8 text-muted" id="restock_prod_curr"></span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small text-dark">Add Quantity (+)</label>
                        <input type="number" name="quantity" class="form-control rounded-pill px-3 py-2 shadow-xs" min="1" required placeholder="50" autofocus>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-medium small text-dark">Remarks / Batch Notes</label>
                        <textarea name="remarks" class="form-control rounded-4 p-3 shadow-xs" rows="3" required placeholder="Supplier delivery / Urgent restock batch"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Confirm Restock</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    $(".quick-restock-btn").click(function() {
        $("#restock_prod_id").val($(this).data('id'));
        $("#restock_prod_name").text($(this).data('name'));
        $("#restock_prod_curr").text("Current Stock Level: " + $(this).data('stock'));
    });
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
