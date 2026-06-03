<?php
$title = "Cashier POS System";

// Fallback for categories if not passed from controller
if (!isset($categories)) {
    require_once __DIR__ . '/../../models/Product.php';
    $categories = Product::get_all_categories() ?: [];
}
// Fallback for category_id if not passed
if (!isset($category_id)) {
    $category_id = isset($_GET['category_id']) ? (int)$_GET['category_id'] : null;
}

// Fallback for active_branch_id if not passed
if (!isset($active_branch_id)) {
    require_once __DIR__ . '/../../models/User.php';
    $active_branch_id = $_SESSION['pos_branch_id'] ?? ($_SESSION['user']['branch_id'] ?? null);
}

$is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');

ob_start();
?>

<main class="flex-grow flex flex-col md:flex-row h-full overflow-hidden">
    <!-- Left Section: Product Grid -->
    <section class="flex-grow flex flex-col h-full bg-background overflow-hidden relative">
        <!-- Category Tabs -->
        <div class="px-margin-desktop py-md flex gap-sm overflow-x-auto no-scrollbar bg-background shrink-0 border-b border-outline-variant border-opacity-20">
            <?php $total_products_count = array_sum(array_column($categories, 'product_count')); ?>
            <a href="<?= url('?route=pos') ?>" 
               class="group px-5 py-2 rounded-full font-label-md text-xs shrink-0 text-decoration-none transition-all duration-300 flex items-center gap-2 <?= empty($category_id) ? 'bg-primary text-on-primary shadow-md hover:bg-opacity-95' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant border-opacity-30 hover:border-primary hover:border-opacity-40 hover:text-primary' ?>">
                <span>All Flavors</span>
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full min-w-[20px] h-5 transition-all duration-300 <?= empty($category_id) ? 'bg-white/20 text-white' : 'bg-surface-container-highest text-on-surface-variant group-hover:bg-primary-fixed group-hover:text-primary' ?>">
                    <?= $total_products_count ?>
                </span>
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php $is_active = ($category_id === (int)$cat['id']); ?>
                <a href="<?= url('?route=pos&category_id=' . $cat['id'] . (!empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '')) ?>" 
                   class="group px-5 py-2 rounded-full font-label-md text-xs shrink-0 text-decoration-none transition-all duration-300 flex items-center gap-2 <?= $is_active ? 'bg-primary text-on-primary shadow-md hover:bg-opacity-95' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant border-opacity-30 hover:border-primary hover:border-opacity-40 hover:text-primary' ?>">
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full min-w-[20px] h-5 transition-all duration-300 <?= $is_active ? 'bg-white/20 text-white' : 'bg-surface-container-highest text-on-surface-variant group-hover:bg-primary-fixed group-hover:text-primary' ?>">
                        <?= $cat['product_count'] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Items Grid -->
        <div class="flex-grow overflow-y-auto px-margin-desktop py-md grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-gutter content-start">
            <?php if (empty($products)): ?>
                <div class="col-span-full flex flex-col items-center justify-center py-10 my-10 opacity-50">
                    <span class="material-symbols-outlined text-6xl mb-md text-primary">inventory_2</span>
                    <h5 class="fw-bold outfit-font text-dark mb-1">No products available</h5>
                    <p class="text-muted small mb-0">Try adjusting your search or category filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <div class="product-card group bg-surface-container-lowest rounded-2xl overflow-hidden border border-outline-variant border-opacity-20 cursor-pointer hover:border-primary hover:border-opacity-50 hover:shadow-lg transition-all duration-300 flex flex-col <?= $prod['stock'] <= 0 ? 'opacity-50' : '' ?>"
                         id="product-card-<?= $prod['id'] ?>"
                         data-product-id="<?= $prod['id'] ?>"
                         data-product-name="<?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>"
                         data-product-price="<?= $prod['price'] ?>"
                         data-product-stock="<?= $prod['stock'] ?>"
                         data-product-threshold="<?= $prod['low_stock_threshold'] ?>">
                        <div class="h-44 bg-surface-container overflow-hidden relative">
                            <img src="<?= get_product_image($prod['image_url'], $prod['id']) ?>" 
                                 alt="<?= htmlspecialchars($prod['name']) ?>" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                            
                            <div class="stock-badge-wrapper absolute top-2 right-2">
                                <?php if ($prod['stock'] <= 0): ?>
                                    <span class="bg-error/90 backdrop-blur-md text-on-error text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] font-bold">block</span> Sold Out
                                    </span>
                                <?php elseif ($prod['stock'] <= $prod['low_stock_threshold']): ?>
                                    <span class="bg-amber-500/90 backdrop-blur-md text-white text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] font-bold">warning</span> Low Stock: <?= $prod['stock'] ?>
                                    </span>
                                <?php else: ?>
                                    <span class="bg-emerald-500/90 backdrop-blur-md text-white text-[10px] px-2.5 py-1 rounded-lg font-bold uppercase tracking-wider shadow-sm flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[12px] font-bold">check_circle</span> In Stock: <?= $prod['stock'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="p-4 flex flex-col justify-between flex-grow">
                            <div>
                                <h3 class="font-bold text-sm text-on-surface mb-1 line-clamp-2 font-label-md h-10 group-hover:text-primary transition-colors"><?= htmlspecialchars($prod['name']) ?></h3>
                            </div>
                            <div class="flex items-center justify-between mt-auto pt-2">
                                <span class="font-extrabold text-sm text-primary">₱<?= number_format($prod['price'], 2) ?></span>
                                <button class="w-8 h-8 rounded-full bg-primary-fixed-dim hover:bg-primary hover:text-on-primary text-primary flex items-center justify-center transition-all border-0 cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px] font-bold">add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Keyboard Shortcuts Collapsible Panel -->
        <div id="hotkeysGuidePanel" class="bg-surface-container border-t border-outline-variant border-opacity-35 shrink-0 transition-all duration-300">
            <!-- Header (Toggle Bar) -->
            <button type="button" onclick="toggleHotkeysGuide()" class="w-full py-2.5 px-margin-desktop flex items-center justify-between text-on-surface-variant bg-transparent border-0 hover:bg-surface-container-high transition-colors cursor-pointer text-xs font-semibold select-none">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">keyboard</span>
                    <span>POS Keyboard Shortcuts Guide</span>
                    <span class="bg-primary/10 text-primary text-[10px] px-2 py-0.5 rounded-full font-bold">F1 - F10</span>
                </div>
                <div class="flex items-center gap-1.5 opacity-80">
                    <span id="hotkeysGuideStatusText">Click to expand</span>
                    <span id="hotkeysGuideChevron" class="material-symbols-outlined text-[18px] transition-transform duration-300" style="transform: rotate(0deg);">keyboard_arrow_up</span>
                </div>
            </button>
            
            <!-- Expanded Content Grid -->
            <div id="hotkeysGuideContent" class="hidden px-margin-desktop pb-4 pt-1 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-7 gap-3 text-[11px] text-on-surface-variant font-medium select-none border-t border-outline-variant border-opacity-10">
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">F1</span>
                    <span class="truncate">Dine-in</span>
                </div>
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">F2</span>
                    <span class="truncate">Take-out</span>
                </div>
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">F4</span>
                    <span class="truncate">Clear Cart</span>
                </div>
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">F7</span>
                    <span class="truncate">Focus Search</span>
                </div>
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">F8</span>
                    <span class="truncate">Pay Cart</span>
                </div>
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">F9</span>
                    <span class="truncate">Complete Pay</span>
                </div>
                <div class="flex items-center gap-1.5 p-2 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25 hover:border-primary hover:border-opacity-25 transition-all">
                    <span class="font-black font-mono text-[10px] bg-primary text-on-primary px-1.5 py-0.5 rounded shadow-xs">Esc</span>
                    <span class="truncate">Close Modals</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Right Section: Order Summary (Cart) -->
    <section class="w-full md:w-[420px] bg-surface-container-lowest h-full border-s border-outline-variant flex flex-col shadow-lg z-10 shrink-0">
        <!-- Order Info Header -->
        <div class="p-md bg-surface border-b border-outline-variant">
        <div class="flex justify-between items-start mb-md">
                <div>
                    <h2 class="font-bold text-lg text-on-surface m-0 leading-tight">Active Order</h2>
                    <p class="font-label-sm text-[11px] text-on-surface-variant opacity-80 m-0 mt-1">Cashier: <?= htmlspecialchars($_SESSION['user']['name'] ?? 'Staff') ?></p>
                </div>
                <button onclick="clearCart()" class="p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer" title="Clear Cart">
                    <span class="material-symbols-outlined text-[22px]">delete_sweep</span>
                </button>
            </div>
            <?php 
            $active_order_type = $_SESSION['pos_order_type'] ?? 'Dine-in';
            $dine_in_active = ($active_order_type === 'Dine-in');
            ?>
            <div class="flex bg-surface-container-high p-1 rounded-xl w-full gap-1 border border-outline-variant border-opacity-30">
                <button id="btnOrderTypeDineIn" onclick="setOrderType('Dine-in')" class="flex-grow py-2 rounded-lg font-label-md text-xs border-0 cursor-pointer transition-all duration-300 <?= $dine_in_active ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant font-medium hover:bg-surface-container-highest bg-transparent' ?>">Dine-in</button>
                <button id="btnOrderTypeTakeOut" onclick="setOrderType('Take-out')" class="flex-grow py-2 rounded-lg font-label-md text-xs border-0 cursor-pointer transition-all duration-300 <?= !$dine_in_active ? 'bg-surface-container-lowest text-primary font-bold shadow-xs' : 'text-on-surface-variant font-medium hover:bg-surface-container-highest bg-transparent' ?>">Take-out</button>
            </div>
            <input type="hidden" id="orderType" value="<?= htmlspecialchars($active_order_type) ?>">
        </div>

        <!-- Cart Items List -->
        <div class="flex-grow overflow-y-auto p-md space-y-3" id="cartItemsList">
            <!-- Dynamically populated via AJAX (main.js) -->
        </div>

        <!-- Summary & Actions -->
        <div class="p-md bg-surface border-t border-outline-variant space-y-md shrink-0">
            
            <div class="space-y-2 mb-3">
                <div class="flex justify-between text-on-surface-variant font-medium text-sm items-center">
                    <span>Subtotal</span>
                    <span id="cartSubtotal" class="font-bold text-on-surface">₱0.00</span>
                </div>
                
                <div class="flex justify-between text-on-surface-variant font-medium text-sm items-center mt-2">
                    <span>Discount (₱)</span>
                    <input type="number" id="cartDiscount" class="w-28 text-end rounded-lg px-2.5 py-1 text-sm outline-none border border-outline-variant border-opacity-65 focus:border-primary font-bold text-primary shadow-none" value="0" min="0" step="0.01" onchange="updateCartDiscount()">
                </div>
                
                <div class="flex justify-between items-center pt-3 mt-3 border-t border-outline-variant">
                    <span class="font-bold text-base text-on-surface m-0">Total</span>
                    <span class="font-extrabold text-xl text-primary m-0 animate-scale-in" id="cartTotal">₱0.00</span>
                </div>
            </div>

            <!-- Proceed to Payment Action -->
            <button id="proceedToPayBtn" class="w-full py-3.5 mt-4 rounded-xl bg-gradient-to-r from-primary to-secondary text-on-primary font-headline-md text-sm font-bold flex items-center justify-center gap-2 shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all disabled:from-gray-400 disabled:to-gray-500 disabled:opacity-50 disabled:cursor-not-allowed border-0 cursor-pointer" onclick="openPaymentModal()" disabled>
                Proceed to Payment
                <span class="material-symbols-outlined text-[18px]">payments</span>
            </button>
        </div>
    </section>
</main>

<!-- Payment Modal Overlay -->
<div id="paymentModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[460px] rounded-3xl border border-outline-variant border-opacity-40 shadow-2xl p-6 relative mx-4 transform scale-95 transition-transform duration-300 animate-in fade-in zoom-in-95 duration-200" id="paymentModalContent">
        <!-- Close Button -->
        <button onclick="closePaymentModal()" class="absolute top-4 right-4 p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer" title="Cancel">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>

        <h3 class="font-bold text-xl text-on-surface mb-1 font-headline-md text-center">Payment Method</h3>
        <p class="font-label-sm text-[11px] text-on-surface-variant opacity-75 mb-4 text-center">Select payment channel and complete transaction.</p>

        <!-- Dynamic Total Due Banner -->
        <div class="bg-primary-fixed-dim bg-opacity-40 border border-primary border-opacity-10 p-4 rounded-2xl flex justify-between items-center mb-4">
            <span class="font-bold text-xs text-primary uppercase tracking-wider">Total Due</span>
            <span class="font-extrabold text-xl text-primary m-0" id="modalTotalDue">₱0.00</span>
        </div>

        <div class="space-y-4">
            <!-- Payment Method Selection Segmented Group -->
            <div class="space-y-2">
                <label class="font-bold text-[10px] text-on-surface-variant uppercase tracking-wider block">Payment Channel</label>
                <div class="grid grid-cols-3 gap-2" id="paymentMethodSelectGroup">
                    <button type="button" onclick="setPaymentMethod('Cash')" id="btnPayCash" class="flex flex-col items-center justify-center py-2.5 rounded-xl border border-outline-variant border-opacity-40 transition-all font-bold text-xs gap-1 cursor-pointer bg-gradient-to-r from-primary to-secondary text-on-primary shadow-md">
                        <span class="material-symbols-outlined text-[20px]">payments</span>
                        <span>Cash</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('GCash')" id="btnPayGCash" class="flex flex-col items-center justify-center py-2.5 rounded-xl border border-outline-variant border-opacity-40 transition-all font-bold text-xs gap-1 cursor-pointer bg-surface-container-low text-on-surface-variant hover:border-primary hover:text-primary">
                        <span class="material-symbols-outlined text-[20px]">account_balance_wallet</span>
                        <span>GCash</span>
                    </button>
                    <button type="button" onclick="setPaymentMethod('Credit Card')" id="btnPayCard" class="flex flex-col items-center justify-center py-2.5 rounded-xl border border-outline-variant border-opacity-40 transition-all font-bold text-xs gap-1 cursor-pointer bg-surface-container-low text-on-surface-variant hover:border-primary hover:text-primary">
                        <span class="material-symbols-outlined text-[20px]">credit_card</span>
                        <span>Card</span>
                    </button>
                </div>
                <input type="hidden" id="paymentMethod" value="Cash">
            </div>

            <!-- Amount Tendered Field -->
            <div class="space-y-1.5" id="tenderedSection">
                <label class="font-bold text-[10px] text-on-surface-variant uppercase tracking-wider block">Tendered</label>
                <div class="relative flex items-center">
                    <span class="absolute left-4 font-extrabold text-primary text-lg">₱</span>
                    <input type="number" id="amountTendered" class="w-full bg-surface-container border border-outline-variant border-opacity-50 rounded-xl pl-9 pr-4 py-3 text-right font-black text-primary focus:outline-none focus:border-primary focus:ring-1 focus:ring-primary text-lg shadow-none" placeholder="0.00" min="0" step="0.01" oninput="calculateChange()">
                </div>
            </div>

            <!-- Quick Cash Presets -->
            <div class="space-y-2 bg-surface-container-low p-4 rounded-2xl border border-outline-variant border-opacity-35" id="presetsSection">
                <span class="font-bold text-[10px] text-on-surface-variant uppercase tracking-wider block opacity-75">Quick Cash Presets</span>
                <div class="grid grid-cols-5 gap-2">
                    <button type="button" onclick="selectQuickCash(50)" class="py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant border-opacity-40 hover:border-primary hover:text-primary hover:bg-primary-fixed text-xs font-bold text-on-surface-variant cursor-pointer transition-all">₱50</button>
                    <button type="button" onclick="selectQuickCash(100)" class="py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant border-opacity-40 hover:border-primary hover:text-primary hover:bg-primary-fixed text-xs font-bold text-on-surface-variant cursor-pointer transition-all">₱100</button>
                    <button type="button" onclick="selectQuickCash(200)" class="py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant border-opacity-40 hover:border-primary hover:text-primary hover:bg-primary-fixed text-xs font-bold text-on-surface-variant cursor-pointer transition-all">₱200</button>
                    <button type="button" onclick="selectQuickCash(500)" class="py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant border-opacity-40 hover:border-primary hover:text-primary hover:bg-primary-fixed text-xs font-bold text-on-surface-variant cursor-pointer transition-all">₱500</button>
                    <button type="button" onclick="selectQuickCash(1000)" class="py-2.5 rounded-xl bg-surface-container-lowest border border-outline-variant border-opacity-40 hover:border-primary hover:text-primary hover:bg-primary-fixed text-xs font-bold text-on-surface-variant cursor-pointer transition-all">₱1k</button>
                </div>
                <div class="mt-2.5">
                    <button type="button" onclick="selectQuickCash('exact')" class="w-full py-2.5 rounded-xl bg-primary-fixed text-primary text-xs font-extrabold cursor-pointer border border-primary border-opacity-20 hover:bg-primary hover:text-on-primary transition-all">Exact Amount</button>
                </div>
            </div>

            <!-- Change Banner -->
            <div class="flex justify-between items-center py-2 px-1 border-t border-outline-variant border-opacity-20" id="changeSection">
                <span class="font-bold text-[10px] text-on-surface-variant uppercase tracking-wider">Change</span>
                <span id="changeAmount" class="font-extrabold text-2xl text-primary m-0">₱0.00</span>
            </div>
        </div>

        <!-- Action Button -->
        <button id="checkoutBtn" class="w-full py-4 mt-6 rounded-2xl bg-gradient-to-r from-primary to-secondary text-on-primary font-headline-md text-sm font-bold flex items-center justify-center gap-2 shadow-lg hover:shadow-xl hover:brightness-105 active:scale-[0.98] transition-all disabled:from-gray-400 disabled:to-gray-500 disabled:opacity-50 disabled:cursor-not-allowed border-0 cursor-pointer" onclick="processCheckout()" disabled>
            Complete Payment
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
        </button>

        <!-- Success Overlay -->
        <div id="paymentSuccessScreen" class="hidden absolute inset-0 bg-surface-container-lowest rounded-3xl flex flex-col items-center justify-center p-6 text-center z-10 animate-in fade-in zoom-in duration-300">
            <div class="w-20 h-20 bg-emerald-100 rounded-full flex items-center justify-center mb-5 text-emerald-500 shadow-md">
                <span class="material-symbols-outlined text-5xl font-black animate-pulse">check_circle</span>
            </div>
            <h3 class="font-extrabold text-2xl text-on-surface mb-2 font-headline-md">Payment Completed!</h3>
            <p class="font-label-sm text-xs text-on-surface-variant opacity-75 mb-0">Generating receipt...</p>
        </div>
    </div>
</div>


<!-- Receipt Modal Overlay -->
<div id="receiptModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex justify-center hidden opacity-0 transition-opacity duration-300 overflow-y-auto py-10 no-scrollbar">
    <div class="bg-surface-container-lowest w-full max-w-[460px] rounded-3xl border border-outline-variant border-opacity-40 shadow-2xl p-6 relative mx-4 transform scale-95 transition-transform duration-300 h-fit" id="receiptModalContent">
        <!-- Close Button -->
        <button onclick="closeReceiptModal()" class="absolute top-4 right-4 p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer" title="New Order">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>

        <!-- Receipt Body Container -->
        <div id="receiptModalBody" class="px-1 pr-2">
            <!-- Dynamically populated via AJAX (main.js) -->
        </div>

        <!-- Receipt Footer Actions -->
        <div class="grid grid-cols-2 gap-3 mt-6 pt-4 border-t border-outline-variant border-opacity-35">
            <button type="button" onclick="printModalReceipt()" class="py-3 rounded-xl border border-primary text-primary font-bold text-xs hover:bg-primary-fixed transition-all flex items-center justify-center gap-2 cursor-pointer bg-transparent">
                <span class="material-symbols-outlined text-[18px]">print</span>
                Print Receipt
            </button>
            <button type="button" onclick="closeReceiptModal()" class="py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-on-primary font-bold text-xs hover:brightness-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 border-0 cursor-pointer shadow-md">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                New Order
            </button>
        </div>
    </div>
</div>


<!-- Validation Warning Modal Overlay -->
<div id="validationModal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[400px] rounded-3xl border border-error/20 shadow-2xl p-6 relative mx-4 text-center transform scale-95 transition-transform duration-300" id="validationModalContent">
        <!-- Warning Circle Icon -->
        <div class="w-16 h-16 bg-error-container/40 rounded-full flex items-center justify-center text-error shadow-md mx-auto mb-5 border border-error-container relative">
            <span class="material-symbols-outlined text-4xl font-black animate-bounce mt-1">warning</span>
            <span class="absolute top-1 right-1 w-3 h-3 bg-error rounded-full border-2 border-white animate-ping"></span>
        </div>
        
        <!-- Header -->
        <h3 id="validationModalTitle" class="font-extrabold text-lg text-on-surface mb-2 font-headline-md text-error uppercase tracking-wide">Validation Error</h3>
        
        <!-- Content Message -->
        <p id="validationModalMessage" class="font-label-sm text-xs text-on-surface-variant opacity-85 mb-6 px-2 leading-relaxed">
            Amount tendered is less than the total due amount.
        </p>
        
        <!-- Action Button -->
        <button onclick="closeValidationModal()" class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-error to-primary hover:brightness-105 active:scale-[0.98] text-on-error font-bold text-xs tracking-wider uppercase transition-all shadow-md border-0 cursor-pointer flex items-center justify-center gap-2">
            <span id="validationModalBtnIcon" class="material-symbols-outlined text-[16px] font-bold">undo</span>
            <span id="validationModalBtnText">Correct Payment Amount</span>
        </button>
    </div>
</div>


<!-- Custom Confirmation Modal Overlay -->
<div id="confirmModal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[400px] rounded-3xl border border-outline-variant border-opacity-30 shadow-2xl p-6 relative mx-4 text-center transform scale-95 transition-transform duration-300" id="confirmModalContent">
        <!-- Help/Question Circle Icon -->
        <div class="w-16 h-16 bg-primary-fixed-dim/40 rounded-full flex items-center justify-center text-primary shadow-md mx-auto mb-5 border border-primary-fixed-dim relative">
            <span class="material-symbols-outlined text-4xl font-black animate-pulse">help_center</span>
        </div>
        
        <!-- Header -->
        <h3 class="font-extrabold text-lg text-on-surface mb-2 font-headline-md uppercase tracking-wide">Clear Active Order</h3>
        
        <!-- Content Message -->
        <p class="font-label-sm text-xs text-on-surface-variant opacity-85 mb-6 px-2 leading-relaxed">
            Are you sure you want to clear the active order? This will remove all items from your current cart.
        </p>
        
        <!-- Action Buttons -->
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeConfirmModal()" class="py-3 rounded-xl border border-outline-variant text-on-surface-variant font-bold text-xs hover:bg-surface-container-high transition-all flex items-center justify-center gap-2 cursor-pointer bg-transparent">
                Cancel
            </button>
            <button onclick="executeClearCart()" class="py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-on-primary font-bold text-xs hover:brightness-105 active:scale-[0.98] transition-all flex items-center justify-center gap-2 border-0 cursor-pointer shadow-md">
                Clear Order
            </button>
        </div>
    </div>
<script>
document.addEventListener("DOMContentLoaded", function() {
    loadCart();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/pos_layout.php';
?>
