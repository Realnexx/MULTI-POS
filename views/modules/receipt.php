<?php
if (!isset($sale)) {
    require_once __DIR__ . '/../../models/Sale.php';
    require_once __DIR__ . '/../../models/Setting.php';
    $sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $sale = Sale::find_sale_by_id($sale_id);
    
    if (!$sale) {
        die("Transaction not found. Please access this page through the History module.");
    }
    
    // Also fetch store settings if missing
    if (!isset($store_name)) {
        $settings = Setting::get_by_category('general');
        $store_name = $settings['store_name'] ?? 'Stacey Halu';
        $store_address = $settings['store_address'] ?? '123 Sweet Tooth Blvd, Sugar City';
        $store_phone = $settings['store_phone'] ?? '+63 912 345 6789';
    }
}
$title = "Transaction Receipt #" . str_pad($sale['id'], 6, '0', STR_PAD_LEFT);
ob_start();
?>

<div class="d-flex align-items-center justify-content-between mb-4 d-print-none">
    <a href="<?= url('?route=pos/history') ?>" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold d-flex align-items-center gap-2">
        <i class="fa-solid fa-arrow-left"></i> Back to History
    </a>
    <div class="d-flex gap-2">
        <a href="<?= url('?route=pos') ?>" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-cash-register"></i> New Sale
        </a>
        <button onclick="window.print()" class="btn btn-accent rounded-pill px-4 fw-semibold shadow-sm d-flex align-items-center gap-2">
            <i class="fa-solid fa-print"></i> Print Receipt
        </button>
    </div>
</div>

<!-- Receipt Container -->
<div class="row justify-content-center">
    <div class="col-lg-5 col-md-7">
        <div class="card border-0 rounded-4 shadow-lg p-5 bg-white border border-light receipt-card animate-scale-in">
            <!-- Decorative Status Badge -->
            <div class="position-absolute top-0 end-0 p-3">
                <span class="badge bg-success-subtle text-success border border-success border-opacity-20 rounded-pill px-3 py-1.5 fw-bold small">
                    <i class="fa-solid fa-circle-check me-1"></i> VALID TRANSACTION
                </span>
            </div>

            <!-- Header -->
            <div class="text-center mb-4 pb-3 border-bottom border-dashed border-light">
                <div class="avatar-circle bg-primary text-white mx-auto mb-3 shadow-md" style="width: 56px; height: 56px; font-size: 24px;">
                    <?= strtoupper(substr($store_name, 0, 1)) ?>
                </div>
                <h3 class="fw-extrabold outfit-font text-dark mb-1 tracking-tighter" style="font-size: 28px;"><?= htmlspecialchars($store_name) ?></h3>
                <p class="text-muted small mb-1 opacity-75 fw-medium"><?= htmlspecialchars($store_address) ?></p>
                <p class="text-muted small mb-0 opacity-75 fw-medium"><?= htmlspecialchars($store_phone) ?></p>
            </div>

            <!-- Transaction Details -->
            <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Receipt No:</span>
                <span class="fw-bold text-dark">#<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-2">
                <span>Date & Time:</span>
                <span class="fw-bold text-dark"><?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></span>
            </div>
            <div class="d-flex justify-content-between small text-muted mb-4 pb-3 border-bottom border-dashed border-light">
                <span>Cashier:</span>
                <span class="fw-bold text-dark"><?= htmlspecialchars($sale['cashier_name'] ?? 'System') ?></span>
            </div>

            <!-- Itemized List -->
            <div class="mb-4 pb-3 border-bottom border-dashed border-light">
                <div class="d-flex justify-content-between text-uppercase fs-8 fw-bold text-muted mb-2 outfit-font tracking-wide">
                    <span>Description</span>
                    <span>Subtotal</span>
                </div>
                <?php foreach ($sale['items'] as $item): ?>
                    <div class="d-flex justify-content-between mb-2 align-items-start">
                        <div>
                            <div class="fw-bold text-dark small"><?= htmlspecialchars($item['product_name']) ?></div>
                            <div class="fs-8 text-muted"><?= $item['quantity'] ?> x ₱<?= number_format($item['price'], 2) ?></div>
                        </div>
                        <span class="fw-bold text-dark small">₱<?= number_format($item['subtotal'], 2) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Totals -->
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Subtotal:</span>
                <span class="fw-bold text-dark">₱<?= number_format($sale['total_amount'], 2) ?></span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Discount:</span>
                <span class="fw-bold text-danger">- ₱<?= number_format($sale['discount_amount'], 2) ?></span>
            </div>
            <div class="d-flex justify-content-between text-dark fw-extrabold fs-5 mb-4 pt-2 border-top border-light align-items-center outfit-font">
                <span>Total Due:</span>
                <span class="text-primary">₱<?= number_format($sale['final_amount'], 2) ?></span>
            </div>

            <!-- Payment Info -->
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Payment Method:</span>
                <span class="fw-bold text-dark"><?= htmlspecialchars($sale['payment_method']) ?></span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-2">
                <span>Amount Tendered:</span>
                <span class="fw-bold text-dark">₱<?= number_format($sale['amount_tendered'], 2) ?></span>
            </div>
            <div class="d-flex justify-content-between text-muted small mb-4 pb-4 border-bottom border-dashed border-light">
                <span>Change:</span>
                <span class="fw-bold text-success">₱<?= number_format($sale['change_amount'], 2) ?></span>
            </div>

            <!-- Footer -->
            <div class="text-center text-muted small">
                <p class="mb-1 fw-medium text-dark">Thank you for your sweet visit!</p>
                <p class="fs-8 mb-0">Please come again to <?= htmlspecialchars($store_name) ?></p>
            </div>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
