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

$is_admin = User::has_all_branch_access($_SESSION['user']['id']);

// ── PIN Verification Mode ────────────────────────────────────────
if (!empty($require_pin)):
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<?php require __DIR__.'/../partials/site_favicon.php'; ?>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>PIN Required · <?= htmlspecialchars($store_name ?? 'POS') ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "#b60e3d",
                        secondary: "#8429c8",
                        "on-primary": "#ffffff",
                        "on-surface": "#191c1d",
                        "on-surface-variant": "#5a4042",
                        "surface-container-lowest": "#ffffff",
                        "surface-container": "#edeeef",
                        "surface-container-high": "#e7e8e9",
                        "surface-container-low": "#f3f4f5",
                        "background": "#f8f9fa",
                        "primary-fixed-dim": "#ffb2b8",
                        error: "#ba1a1a"
                    },
                    fontFamily: { sans: ["Inter", "sans-serif"] }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .pin-dot { transition: all 0.15s ease; }
        .pin-btn { transition: all 0.12s ease; user-select: none; }
        .pin-btn:active { transform: scale(0.94); }
        .shake { animation: shake 0.4s ease-in-out; }
        @keyframes shake { 0%,100% { transform: translateX(0); } 25% { transform: translateX(-6px); } 75% { transform: translateX(6px); } }
        .fade-in { animation: fadeIn 0.3s ease forwards; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
    </style>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script src="<?= url('assets/admin/libs/jquery/dist/jquery.min.js') ?>"></script>
    <script src="<?= url('assets/js/request-security.js') ?>"></script>
    
</head>
<body class="bg-background min-h-screen flex items-center justify-center p-4" style="font-family: 'Inter', sans-serif;">
<a href="<?= url('?route=admin/online_orders') ?>" class="btn btn-outline-primary btn-sm m-2">Online Orders</a>
    <div class="w-full max-w-[400px] fade-in">
        <!-- Brand header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-primary to-secondary flex items-center justify-center text-white shadow-lg mx-auto mb-4">
                <span class="material-symbols-outlined text-3xl font-bold">lock</span>
            </div>
            <h1 class="font-extrabold text-xl text-on-surface m-0" style="font-weight: 800;"><?= htmlspecialchars($store_name ?? 'POS System') ?></h1>
            <p class="text-sm text-on-surface-variant opacity-70 mt-1.5 mb-0">Enter your PIN to unlock the POS terminal</p>
        </div>

        <!-- PIN Card -->
        <div class="bg-surface-container-lowest rounded-3xl border border-outline-variant/20 shadow-xl p-8" id="pinCard">
            <!-- PIN Dots -->
            <div class="flex justify-center gap-3 mb-8" id="pinDots">
                <?php for ($i = 0; $i < 4; $i++): ?>
                    <div class="pin-dot w-4 h-4 rounded-full border-2 border-outline-variant/50 bg-surface-container" data-idx="<?= $i ?>"></div>
                <?php endfor; ?>
            </div>

            <!-- Error message -->
            <div id="pinError" class="text-center text-error text-sm font-bold mb-4 hidden shake px-4 py-3 rounded-2xl" style="background: #fff0f1; border: 1px solid #ffcdd2;">Invalid PIN. Try again.</div>

            <!-- Numpad -->
            <div class="grid grid-cols-3 gap-3 mb-4">
                <?php for ($i = 1; $i <= 9; $i++): ?>
                    <button type="button" onclick="pressPin('<?= $i ?>')" class="pin-btn w-full aspect-square rounded-2xl bg-surface-container-low hover:bg-surface-container-high active:bg-primary-fixed-dim text-on-surface font-bold text-xl border-0 cursor-pointer transition-all" style="font-weight: 700;"><?= $i ?></button>
                <?php endfor; ?>
                <button type="button" onclick="pressPin('clear')" class="pin-btn w-full aspect-square rounded-2xl bg-transparent hover:bg-surface-container-low text-on-surface-variant font-bold text-xs border-0 cursor-pointer transition-all uppercase tracking-wider" style="font-weight: 600;">Clear</button>
                <button type="button" onclick="pressPin('0')" class="pin-btn w-full aspect-square rounded-2xl bg-surface-container-low hover:bg-surface-container-high active:bg-primary-fixed-dim text-on-surface font-bold text-xl border-0 cursor-pointer transition-all" style="font-weight: 700;">0</button>
                <button type="button" onclick="pressPin('backspace')" class="pin-btn w-full aspect-square rounded-2xl bg-transparent hover:bg-surface-container-low text-on-surface-variant border-0 cursor-pointer transition-all flex items-center justify-center">
                    <span class="material-symbols-outlined text-xl">backspace</span>
                </button>
            </div>

            <!-- Unlock button -->
            <button id="unlockBtn" onclick="submitPin()" disabled class="w-full py-3.5 rounded-2xl bg-gradient-to-r from-primary to-secondary text-on-primary font-bold text-sm flex items-center justify-center gap-2 shadow-lg hover:brightness-105 active:scale-[0.98] transition-all disabled:from-gray-300 disabled:to-gray-400 disabled:opacity-50 disabled:cursor-not-allowed border-0 cursor-pointer" style="font-weight: 700;">
                <span class="material-symbols-outlined text-[18px]">key</span>
                Unlock Terminal
            </button>
        </div>

        <p class="text-center text-[10px] text-on-surface-variant opacity-40 mt-6">Authorized personnel only · POS Terminal Access</p>
    </div>

    <script>
    let pinValue = '';
    const maxPin = 4;

    function updateDots() {
        document.querySelectorAll('.pin-dot').forEach((dot, i) => {
            if (i < pinValue.length) {
                dot.className = 'pin-dot w-4 h-4 rounded-full bg-primary border-2 border-primary';
            } else {
                dot.className = 'pin-dot w-4 h-4 rounded-full border-2 border-outline-variant/50 bg-surface-container';
            }
        });
        document.getElementById('unlockBtn').disabled = pinValue.length !== maxPin;
    }

    function hideError() {
        document.getElementById('pinError').classList.add('hidden');
    }

    function pressPin(key) {
        if (key === 'clear') {
            pinValue = '';
        } else if (key === 'backspace') {
            pinValue = pinValue.slice(0, -1);
        } else if (pinValue.length < maxPin) {
            pinValue += key;
        }
        hideError();
        updateDots();
    }

    function submitPin() {
        if (pinValue.length !== maxPin) return;
        const btn = document.getElementById('unlockBtn');
        btn.disabled = true;
        btn.innerHTML = '<span class="material-symbols-outlined text-[18px] animate-spin">sync</span> Verifying...';

        $.ajax({
            url: '<?= url('ajax/pos_pin.php') ?>',
            method: 'POST',
            data: { pin: pinValue, csrf_token: document.querySelector('meta[name="csrf-token"]').content },
            dataType: 'json',
            success: function(res) {
                if (res.success) {
                    window.location.reload();
                } else {
                    const errEl = document.getElementById('pinError');
                    errEl.textContent = res.message || 'Invalid PIN. Please try again.';
                    errEl.classList.remove('hidden');
                    document.getElementById('pinCard').classList.add('shake');
                    setTimeout(() => document.getElementById('pinCard').classList.remove('shake'), 500);
                    pinValue = '';
                    updateDots();
                    btn.disabled = false;
                    btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">key</span> Unlock Terminal';
                }
            },
            error: function(xhr) {
                let msg = 'Connection error. Please try again.';
                try {
                    const r = JSON.parse(xhr.responseText);
                    if (r.message) msg = r.message;
                } catch(e) {}
                document.getElementById('pinError').textContent = msg;
                document.getElementById('pinError').classList.remove('hidden');
                pinValue = '';
                updateDots();
                btn.disabled = false;
                btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">key</span> Unlock Terminal';
            }
        });
    }

    // Keyboard support
    document.addEventListener('keydown', function(e) {
        if (e.key >= '0' && e.key <= '9') { pressPin(e.key); }
        else if (e.key === 'Backspace') { pressPin('backspace'); }
        else if (e.key === 'Escape') { pressPin('clear'); }
        else if (e.key === 'Enter') { submitPin(); }
    });
    </script>
</body>
</html>
<?php
exit;
// ── End PIN Verification Mode ────────────────────────────────────
endif;

ob_start();

// ── Branch Selection Mode (Admin without terminal) ──────────────
if (!empty($branch_select_mode)):
?>
<main class="flex-grow flex items-center justify-center h-full overflow-hidden bg-background">
    <div class="w-full max-w-2xl mx-auto px-6 py-8">
        <div class="text-center mb-10">
            <div class="w-16 h-16 rounded-2xl product-count-badge flex items-center justify-center text-white shadow-lg mx-auto mb-5">
                <span class="material-symbols-outlined text-3xl font-bold">hub</span>
            </div>
            <h1 class="font-extrabold text-2xl text-on-surface m-0 font-headline-md"><?= htmlspecialchars($store_name ?? 'POS System') ?></h1>
            <p class="text-sm text-on-surface-variant opacity-70 mt-2 mb-0">Select a POS terminal to initialize this session.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <?php if (empty($branches)): ?>
                <div class="col-span-full text-center py-12 opacity-60">
                    <span class="material-symbols-outlined text-4xl text-on-surface-variant block mb-3">warning</span>
                    <p class="font-bold text-sm text-on-surface-variant">No active branches found.</p>
                    <p class="text-xs text-on-surface-variant opacity-60">Contact your administrator to set up branches.</p>
                </div>
            <?php else: ?>
                <?php foreach ($branches as $branch): ?>
                    <a href="<?= url('?route=pos&select_branch=' . (int)$branch['id']) ?>" class="group block bg-surface-container-lowest rounded-2xl border-2 border-outline-variant/30 p-6 text-center text-decoration-none hover:border-primary hover:bg-primary-fixed-dim/10 transition-all duration-300 hover:shadow-[0_8px_25px_-5px_rgba(182,14,61,0.12)]">
                        <div class="w-14 h-14 rounded-2xl bg-primary-fixed-dim/30 flex items-center justify-center mx-auto mb-4 group-hover:bg-primary-fixed-dim transition-all duration-300">
                            <span class="material-symbols-outlined text-3xl text-primary">storefront</span>
                        </div>
                        <h3 class="font-bold text-base text-on-surface mb-1 font-headline-md group-hover:text-primary transition-colors"><?= htmlspecialchars($branch['name']) ?></h3>
                        <p class="text-xs text-on-surface-variant opacity-60 mb-0">Activate this terminal</p>
                        <div class="mt-4 inline-flex items-center gap-1.5 text-[10px] font-bold uppercase tracking-wider text-primary opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <span>Launch POS</span>
                            <span class="material-symbols-outlined text-[14px] font-bold">arrow_forward</span>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        <div class="text-center mt-10">
            <p class="text-[10px] text-on-surface-variant opacity-40">Administrator access · MultiPOS Terminal Selection</p>
        </div>
    </div>
</main>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/pos_layout.php';
return;
endif;
// ── End Branch Selection Mode ───────────────────────────────────
?>

<main class="flex-grow flex flex-col md:flex-row h-full overflow-hidden">
    <!-- Left Section: Product Grid -->
    <section class="flex-grow flex flex-col h-full bg-background overflow-hidden relative">
        <!-- Premium Header Strip -->
        <div class="page-header-gradient px-margin-desktop py-3 flex items-center justify-between shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl product-count-badge flex items-center justify-center text-white shadow-md">
                    <span class="material-symbols-outlined text-[18px] font-bold">point_of_sale</span>
                </div>
                <div>
                    <h2 class="font-bold text-sm text-on-surface m-0 leading-none font-headline-md">Point of Sale</h2>
                    <p class="font-label-sm text-[10px] text-on-surface-variant opacity-70 m-0 mt-0.5">
                        <?= count($products) ?> product<?= count($products) !== 1 ? 's' : '' ?> available
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[10px] font-bold text-on-surface-variant opacity-50 uppercase tracking-wider">Menu</span>
                <span class="text-[10px] font-bold text-primary uppercase tracking-wider">
                    <?php if (!empty($product_type)): ?>
                        <?= ucfirst($product_type) ?>
                    <?php else: ?>
                        All Items
                    <?php endif; ?>
                </span>
            </div>
        </div>
        <div class="pos-header-accent"></div>

        <!-- Category Tabs -->
        <div class="px-margin-desktop py-md flex gap-sm overflow-x-auto cat-tab-scroll bg-background shrink-0 border-b border-outline-variant border-opacity-20">
            <?php
            $total_cat_count = array_sum(array_column($categories, 'product_count'));
            $is_all_flavors = empty($category_id);
            ?>
            <a href="<?= url('?route=pos' . (!empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (!empty($product_type) ? '&product_type=' . urlencode($product_type) : '')) ?>" 
               class="group px-5 py-2 rounded-full font-label-md text-xs shrink-0 text-decoration-none transition-all duration-300 flex items-center gap-2 <?= $is_all_flavors ? 'bg-primary text-on-primary shadow-md' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant border-opacity-30 hover:border-primary hover:border-opacity-40 hover:text-primary' ?>">
                <span class="material-symbols-outlined text-[14px] font-bold">restaurant_menu</span>
                <span>All Flavors</span>
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full min-w-[20px] h-5 transition-all duration-300 <?= $is_all_flavors ? 'bg-white/20 text-white' : 'bg-surface-container-highest text-on-surface-variant group-hover:bg-primary-fixed group-hover:text-primary' ?>">
                    <?= $total_cat_count ?>
                </span>
            </a>
            <?php foreach ($categories as $cat): ?>
                <?php $is_active = ($category_id === (int)$cat['id']); ?>
                <a href="<?= url('?route=pos&category_id=' . $cat['id'] . (!empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '') . (!empty($product_type) ? '&product_type=' . urlencode($product_type) : '')) ?>" 
                   class="group px-5 py-2 rounded-full font-label-md text-xs shrink-0 text-decoration-none transition-all duration-300 flex items-center gap-2 <?= $is_active ? 'bg-primary text-on-primary shadow-md' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant border-opacity-30 hover:border-primary hover:border-opacity-40 hover:text-primary' ?>">
                    <span><?= htmlspecialchars($cat['name']) ?></span>
                    <span class="inline-flex items-center justify-center px-2 py-0.5 text-[10px] font-bold rounded-full min-w-[20px] h-5 transition-all duration-300 <?= $is_active ? 'bg-white/20 text-white' : 'bg-surface-container-highest text-on-surface-variant group-hover:bg-primary-fixed group-hover:text-primary' ?>">
                        <?= $cat['product_count'] ?>
                    </span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Type Filter -->
        <div class="px-margin-desktop py-2 flex items-center gap-2 bg-surface-container-lowest shrink-0 border-b border-outline-variant border-opacity-10">
            <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider opacity-60">Filter:</span>
            <?php
            $type_counts = $type_counts ?? [];
            $total_type_count = array_sum($type_counts);
            ?>
            <a href="<?= url('?route=pos' . (!empty($category_id) ? '&category_id=' . $category_id : '') . (!empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '')) ?>"
               class="px-3 py-1 rounded-full text-[11px] font-bold text-decoration-none transition-all duration-200 inline-flex items-center gap-1.5 <?= empty($product_type) ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant border-opacity-30 hover:border-primary hover:text-primary' ?>">
                All
                <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full min-w-[16px] h-4 <?= empty($product_type) ? 'bg-white/20 text-white' : 'bg-surface-container-highest text-on-surface-variant' ?>"><?= $total_type_count ?></span>
            </a>
            <?php
            $type_filters = ['simple' => 'Simple', 'variant' => 'Variant', 'recipe' => 'Recipe', 'ingredient' => 'Ingredient'];
            foreach ($type_filters as $type_val => $type_label):
                $is_active_type = ($product_type === $type_val);
                $type_count = $type_counts[$type_val] ?? 0;
            ?>
                <a href="<?= url('?route=pos&product_type=' . $type_val . (!empty($category_id) ? '&category_id=' . $category_id : '') . (!empty($_GET['search']) ? '&search=' . urlencode($_GET['search']) : '')) ?>"
                   class="px-3 py-1 rounded-full text-[11px] font-bold text-decoration-none transition-all duration-200 inline-flex items-center gap-1.5 <?= $is_active_type ? 'bg-primary text-on-primary shadow-sm' : 'bg-surface-container-low text-on-surface-variant border border-outline-variant border-opacity-30 hover:border-primary hover:text-primary' ?>">
                    <?= $type_label ?>
                    <span class="inline-flex items-center justify-center px-1.5 py-0.5 text-[10px] font-bold rounded-full min-w-[16px] h-4 <?= $is_active_type ? 'bg-white/20 text-white' : 'bg-surface-container-highest text-on-surface-variant' ?>"><?= $type_count ?></span>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Product Items Grid -->
        <div class="flex-grow product-grid-scroll min-h-0 px-margin-desktop py-md grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-gutter content-start">
            <?php if (empty($products)): ?>
                <div class="col-span-full flex flex-col items-center justify-center py-16 my-8">
                    <div class="w-20 h-20 bg-surface-container rounded-2xl flex items-center justify-center text-on-surface-variant opacity-40 mb-4 border border-outline-variant border-opacity-20">
                        <span class="material-symbols-outlined text-4xl">search_off</span>
                    </div>
                    <h5 class="font-bold text-base text-on-surface mb-1 font-headline-md">No products available</h5>
                    <p class="text-xs text-on-surface-variant opacity-60 mb-0">Try adjusting your search or category filter.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $prod): ?>
                    <div class="product-card group bg-surface-container-lowest rounded-2xl border border-outline-variant/20 cursor-pointer hover:border-primary/50 hover:shadow-[0_8px_25px_-5px_rgba(182,14,61,0.12)] transition-all duration-300 <?= $prod['stock'] <= 0 ? 'opacity-50' : '' ?>"
                         id="product-card-<?= $prod['id'] ?>"
                         data-product-id="<?= $prod['id'] ?>"
                         data-product-name="<?= htmlspecialchars($prod['name'], ENT_QUOTES) ?>"
                         data-product-price="<?= $prod['price'] ?>"
                         data-product-stock="<?= $prod['stock'] ?>"
                         data-product-threshold="<?= $prod['low_stock_threshold'] ?>">
                        <div class="rounded-t-2xl overflow-hidden">
                            <div class="aspect-[3/4] bg-surface-container relative">
                                <img src="<?= get_product_image($prod['image_url'], $prod['id']) ?>" 
                                     alt="<?= htmlspecialchars($prod['name']) ?>" 
                                     class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                
                                <!-- Image overlay on hover -->
                                <div class="absolute inset-0 bg-gradient-to-t from-black/0 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                
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
                        </div>
                        <div class="p-3">
                            <h3 class="font-bold text-sm text-on-surface mb-2 line-clamp-2 font-label-md group-hover:text-primary transition-colors leading-snug"><?= htmlspecialchars($prod['name']) ?></h3>
                            <div class="flex items-center justify-between pt-2 border-t border-outline-variant/10">
                                <span class="font-extrabold text-sm text-primary">₱<?= number_format($prod['price'], 2) ?></span>
                                <button class="w-8 h-8 rounded-full bg-primary-fixed-dim hover:bg-primary hover:text-on-primary text-primary flex items-center justify-center transition-all duration-200 border-0 cursor-pointer group/btn">
                                    <span class="material-symbols-outlined text-[18px] font-bold group-hover/btn:scale-110 transition-transform">add</span>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </section>

    <!-- Right Section: Order Summary (Cart) -->
    <section class="w-full md:w-[420px] bg-surface-container-lowest h-full border-s border-outline-variant flex flex-col shadow-lg z-10 shrink-0 relative">
        <!-- Premium Cart Accent Bar -->
        <div class="cart-accent-bar shrink-0"></div>
        
        <!-- Order Info Header -->
        <div class="p-md bg-surface border-b border-outline-variant">
        <div class="flex justify-between items-start mb-md">
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-primary-fixed-dim flex items-center justify-center text-primary shrink-0">
                        <span class="material-symbols-outlined text-[18px] font-bold">shopping_cart</span>
                    </div>
                    <div>
                        <h2 class="font-bold text-sm text-on-surface m-0 leading-tight font-headline-md">Active Order</h2>
                        <p class="font-label-sm text-[10px] text-on-surface-variant opacity-70 m-0 mt-0.5"><?= htmlspecialchars($_SESSION['user']['name'] ?? 'Staff') ?></p>
                    </div>
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
            <div id="cartEmptyState" class="flex flex-col items-center justify-center h-full text-center py-10 px-6 empty-cart-glow">
                <div class="w-16 h-16 bg-surface-container rounded-2xl flex items-center justify-center text-on-surface-variant opacity-30 mb-4 border border-outline-variant border-opacity-20">
                    <span class="material-symbols-outlined text-3xl">shopping_cart</span>
                </div>
                <h4 class="font-bold text-sm text-on-surface mb-1 font-headline-md opacity-60">Cart is empty</h4>
                <p class="text-[11px] text-on-surface-variant opacity-40 mb-0 max-w-[200px]">Tap or click a product to start adding items to this order.</p>
            </div>
        </div>

        <!-- Summary & Actions -->
        <div class="p-md bg-surface border-t border-outline-variant shrink-0">
            <!-- Cart item count indicator -->
            <div class="flex items-center gap-2 mb-3 px-1">
                <div class="w-1.5 h-1.5 rounded-full bg-primary opacity-60"></div>
                <span class="text-[10px] font-bold text-on-surface-variant uppercase tracking-wider opacity-50">
                    <span id="cartItemCount">0</span> item<span id="cartItemCountPlural">s</span>
                </span>
            </div>

            <div class="space-y-2 mb-3 bg-surface-container-low rounded-2xl p-4 border border-outline-variant border-opacity-20">
                <div class="flex justify-between text-on-surface-variant font-medium text-sm items-center">
                    <span class="text-xs">Subtotal</span>
                    <span id="cartSubtotal" class="font-bold text-on-surface">₱0.00</span>
                </div>
                
                <div class="flex justify-between text-on-surface-variant font-medium text-sm items-center">
                    <span class="text-xs">Discount</span>
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] text-on-surface-variant opacity-50">₱</span>
                        <input type="number" id="cartDiscount" class="w-20 text-end rounded-lg px-2 py-1 text-xs outline-none border border-outline-variant border-opacity-50 focus:border-primary font-bold text-primary shadow-none bg-surface-container-lowest" value="0" min="0" step="0.01" onchange="updateCartDiscount()">
                    </div>
                </div>
                
                <div class="summary-divider my-2"></div>
                
                <div class="flex justify-between items-center">
                    <span class="font-bold text-sm text-on-surface m-0">Total</span>
                    <span class="font-extrabold text-lg text-primary m-0 animate-scale-in" id="cartTotal">₱0.00</span>
                </div>
            </div>

            <!-- Proceed to Payment Action -->
            <button id="proceedToPayBtn" class="w-full py-3.5 rounded-xl bg-gradient-to-r from-primary to-secondary text-on-primary font-headline-md text-sm font-bold flex items-center justify-center gap-2 shadow-md hover:shadow-lg hover:brightness-105 active:scale-[0.98] transition-all disabled:from-gray-400 disabled:to-gray-500 disabled:opacity-50 disabled:cursor-not-allowed border-0 cursor-pointer" onclick="openPaymentModal()" disabled>
                Proceed to Payment
                <span class="material-symbols-outlined text-[18px]">payments</span>
            </button>
        </div>
    </section>
</main>

<!-- Payment Modal Overlay -->
<div id="paymentModal" class="fixed inset-0 bg-black/60 modal-backdrop-premium z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[460px] rounded-3xl border border-outline-variant border-opacity-40 shadow-2xl p-6 relative mx-4 transform scale-95 transition-transform duration-300" id="paymentModalContent">
        <!-- Premium Accent Bar -->
        <div class="cart-accent-bar rounded-t-3xl absolute top-0 left-0 right-0"></div>
        
        <!-- Close Button -->
        <button onclick="closePaymentModal()" class="absolute top-4 right-4 p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer" title="Cancel">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>

        <div class="flex items-center gap-3 mb-4 mt-2">
            <div class="w-10 h-10 rounded-xl bg-primary-fixed-dim flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[20px] font-bold">payments</span>
            </div>
            <div>
                <h3 class="font-bold text-lg text-on-surface m-0 font-headline-md">Payment Method</h3>
                <p class="font-label-sm text-[10px] text-on-surface-variant opacity-70 m-0 mt-0.5">Select payment channel and complete transaction.</p>
            </div>
        </div>

        <!-- Dynamic Total Due Banner -->
        <div class="bg-gradient-to-r from-primary-fixed-dim/40 to-primary-fixed-dim/20 border border-primary/10 p-4 rounded-2xl flex justify-between items-center mb-4">
            <span class="font-bold text-xs text-primary uppercase tracking-wider">Total Due</span>
            <span class="font-extrabold text-2xl text-primary m-0" id="modalTotalDue">₱0.00</span>
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
<div id="receiptModal" class="fixed inset-0 bg-black/60 modal-backdrop-premium z-50 flex justify-center hidden opacity-0 transition-opacity duration-300 overflow-y-auto py-10 no-scrollbar">
    <div class="bg-surface-container-lowest w-full max-w-[460px] rounded-3xl border border-outline-variant border-opacity-40 shadow-2xl p-6 relative mx-4 transform scale-95 transition-transform duration-300 h-fit" id="receiptModalContent">
        <!-- Premium Accent Bar -->
        <div class="cart-accent-bar rounded-t-3xl absolute top-0 left-0 right-0"></div>
        
        <!-- Close Button -->
        <button onclick="closeReceiptModal()" class="absolute top-4 right-4 p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer" title="New Order">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>

        <!-- Receipt Body Container -->
        <div id="receiptModalBody" class="px-1 pr-2 mt-2">
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


<!-- Modifier Selection Modal Overlay -->
<div id="modifierModal" class="fixed inset-0 bg-black/60 modal-backdrop-premium z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[420px] rounded-3xl border border-outline-variant border-opacity-40 shadow-2xl p-6 relative mx-4 transform scale-95 transition-transform duration-300" id="modifierModalContent">
        <div class="cart-accent-bar rounded-t-3xl absolute top-0 left-0 right-0"></div>
        <button onclick="closeModifierModal()" class="absolute top-4 right-4 p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-outline-variant border-opacity-20 mt-2">
            <div class="w-10 h-10 rounded-xl bg-primary-fixed-dim flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[20px] font-bold">tune</span>
            </div>
            <div>
                <h3 class="font-bold text-base text-on-surface m-0 font-headline-md" id="modifierModalTitle">Customize Order</h3>
                <p class="text-xs text-on-surface-variant opacity-70 m-0 mt-0.5" id="modifierModalSubtitle">Select add-ons for your item</p>
            </div>
        </div>
        <div id="modifierList" class="space-y-2 max-h-[300px] overflow-y-auto mb-4">
        </div>
        <div class="flex justify-between items-center p-3 bg-surface-container-high rounded-xl mb-4">
            <span class="font-bold text-sm text-on-surface">Total</span>
            <span class="font-extrabold text-lg text-primary" id="modifierTotalPrice">₱0.00</span>
        </div>
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModifierModal()" class="py-3 rounded-xl border border-outline-variant text-on-surface-variant font-bold text-xs hover:bg-surface-container-high transition-all bg-transparent cursor-pointer">Cancel</button>
            <button onclick="confirmAddWithModifiers()" class="py-3 rounded-xl bg-gradient-to-r from-primary to-secondary text-on-primary font-bold text-xs hover:brightness-105 active:scale-[0.98] transition-all border-0 cursor-pointer shadow-md flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[16px]">shopping_cart</span>
                Add to Cart
            </button>
        </div>
    </div>
</div>

<!-- Validation Warning Modal Overlay -->
<div id="validationModal" class="fixed inset-0 bg-black/75 modal-backdrop-premium z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[400px] rounded-3xl border border-error/20 shadow-2xl p-6 relative mx-4 text-center transform scale-95 transition-transform duration-300 overflow-hidden" id="validationModalContent">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-error to-primary opacity-60"></div>
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
<div id="confirmModal" class="fixed inset-0 bg-black/75 modal-backdrop-premium z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[400px] rounded-3xl border border-outline-variant border-opacity-30 shadow-2xl p-6 relative mx-4 text-center transform scale-95 transition-transform duration-300 overflow-hidden" id="confirmModalContent">
        <div class="absolute top-0 left-0 right-0 h-1 bg-gradient-to-r from-primary to-secondary opacity-60"></div>
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
</div>

<!-- Keyboard Shortcuts Modal -->
<div id="hotkeysModal" class="fixed inset-0 bg-black/60 modal-backdrop-premium z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-surface-container-lowest w-full max-w-[600px] rounded-3xl border border-outline-variant border-opacity-40 shadow-2xl p-6 relative mx-4 transform scale-95 transition-transform duration-300">
        <div class="cart-accent-bar rounded-t-3xl absolute top-0 left-0 right-0"></div>
        <button onclick="closeHotkeysModal()" class="absolute top-4 right-4 p-2 text-on-surface-variant hover:text-error hover:bg-error-container hover:bg-opacity-10 rounded-full transition-all bg-transparent border-0 cursor-pointer">
            <span class="material-symbols-outlined text-[22px]">close</span>
        </button>
        <div class="flex items-center gap-3 mb-4 pb-4 border-b border-outline-variant border-opacity-20 mt-2">
            <div class="w-10 h-10 rounded-xl bg-primary-fixed-dim flex items-center justify-center text-primary shrink-0">
                <span class="material-symbols-outlined text-[20px] font-bold">keyboard</span>
            </div>
            <div>
                <h3 class="font-bold text-base text-on-surface m-0 font-headline-md">Keyboard Shortcuts</h3>
                <p class="font-label-sm text-[10px] text-on-surface-variant opacity-70 m-0 mt-0.5">Quick keys for POS operations</p>
            </div>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 text-[12px] text-on-surface-variant font-medium">
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">F1</span>
                <span class="font-semibold text-on-surface">Dine-in</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">F2</span>
                <span class="font-semibold text-on-surface">Take-out</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">F4</span>
                <span class="font-semibold text-on-surface">Clear Cart</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">F7</span>
                <span class="font-semibold text-on-surface">Focus Search</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">F8</span>
                <span class="font-semibold text-on-surface">Pay Cart</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">F9</span>
                <span class="font-semibold text-on-surface">Complete Pay</span>
            </div>
            <div class="flex items-center gap-2 p-3 bg-surface-container-low rounded-xl border border-outline-variant border-opacity-25">
                <span class="font-black font-mono text-[11px] bg-primary text-on-primary px-2 py-1 rounded-lg shadow-xs min-w-[36px] text-center">Esc</span>
                <span class="font-semibold text-on-surface">Close Modals</span>
            </div>
        </div>
    </div>
</div>

<script>
function openHotkeysModal() {
    const modal = document.getElementById('hotkeysModal');
    const content = modal.querySelector('div');
    modal.classList.remove('hidden');
    setTimeout(() => {
        modal.classList.remove('opacity-0');
        content.classList.remove('scale-95');
    }, 10);
}
function closeHotkeysModal() {
    const modal = document.getElementById('hotkeysModal');
    const content = modal.querySelector('div');
    modal.classList.add('opacity-0');
    content.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
document.addEventListener("DOMContentLoaded", function() {
    loadCart();
});
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/pos_layout.php';
?>
