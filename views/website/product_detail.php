<?php
// Safe defaults
$title = htmlspecialchars($product['name'] ?? 'Product') . " | Stacey Halu Halu";
$related_products = $related_products ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= $title ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-tertiary-container": "#fffbff",
                        "on-primary-fixed-variant": "#91002d",
                        "background": "#f8f9fa",
                        "tertiary-fixed": "#ffdea9",
                        "surface-container": "#edeeef",
                        "on-primary-fixed": "#40000f",
                        "error": "#ba1a1a",
                        "primary-container": "#da3054",
                        "tertiary": "#7a5500",
                        "surface-container-highest": "#e1e3e4",
                        "on-primary-container": "#fffbff",
                        "on-surface-variant": "#5a4042",
                        "error-container": "#ffdad6",
                        "on-surface": "#191c1d",
                        "secondary-fixed": "#f2daff",
                        "primary-fixed": "#ffdadb",
                        "outline-variant": "#e2bec0",
                        "surface-container-low": "#f3f4f5",
                        "surface-container-lowest": "#ffffff",
                        "tertiary-container": "#996c00",
                        "secondary-container": "#9e49e3",
                        "on-tertiary-fixed-variant": "#5e4100",
                        "inverse-primary": "#ffb2b8",
                        "outline": "#8e6f71",
                        "surface": "#f8f9fa",
                        "on-secondary": "#ffffff",
                        "primary-fixed-dim": "#ffb2b8",
                        "inverse-surface": "#2e3132",
                        "surface-variant": "#e1e3e4",
                        "surface-dim": "#d9dadb",
                        "on-primary": "#ffffff",
                        "inverse-on-surface": "#f0f1f2",
                        "primary": "#b60e3d",
                        "on-secondary-fixed-variant": "#6b00af",
                        "on-error-container": "#93000a",
                        "on-error": "#ffffff",
                        "on-background": "#191c1d",
                        "surface-bright": "#f8f9fa",
                        "secondary-fixed-dim": "#e0b6ff",
                        "secondary": "#8429c8",
                        "on-tertiary-fixed": "#271900",
                        "on-secondary-fixed": "#2e004e",
                        "on-tertiary": "#ffffff",
                        "surface-tint": "#ba1340",
                        "tertiary-fixed-dim": "#ffba27",
                        "surface-container-high": "#e7e8e9",
                        "on-secondary-container": "#fffbff"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "16px",
                        "lg": "40px",
                        "xl": "64px",
                        "margin-desktop": "32px",
                        "base": "8px",
                        "gutter": "24px",
                        "xs": "4px",
                        "md": "24px",
                        "sm": "12px"
                    },
                    "fontFamily": {
                        "label-sm": ["Inter"],
                        "display-lg-mobile": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "display-lg": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "headline-lg": ["Montserrat"],
                        "label-md": ["Inter"],
                        "body-md": ["Inter"]
                    },
                    "fontSize": {
                        "label-sm": ["12px", {"lineHeight": "1", "fontWeight": "500"}],
                        "display-lg-mobile": ["36px", {"lineHeight": "1.2", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "display-lg": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "label-md": ["14px", {"lineHeight": "1", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}]
                    }
                },
            },
        }
    </script>
<style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            display: inline-block;
            line-height: 1;
            vertical-align: middle;
        }
        .parfait-layer-line::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            width: 8px;
            height: 1px;
            background-color: currentColor;
        }
        .scrolled-nav {
            backdrop-filter: blur(8px);
            background-color: rgba(248, 249, 250, 0.85);
        }
</style>
</head>
<body class="bg-background text-on-surface font-body-md selection:bg-primary-fixed">

<?php require_once __DIR__ . '/../partials/header_home.php'; ?>

<main class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop py-xl">
<div class="grid grid-cols-1 lg:grid-cols-12 gap-xl">
<!-- Hero Image Section -->
<div class="lg:col-span-7">
<div class="relative rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.05)] group">
<img alt="<?= htmlspecialchars($product['name']) ?>"
     class="w-full aspect-[4/5] object-cover transition-transform duration-700 group-hover:scale-105"
     src="<?= get_product_image($product['image_url'], $product['id']) ?>"/>
<div class="absolute top-6 left-6 flex flex-col gap-sm">
<?php if ($product['is_best_seller']): ?>
<span class="bg-primary text-on-primary font-label-md text-label-md px-4 py-1.5 rounded-full shadow-lg">Best Seller</span>
<?php elseif ($product['is_featured']): ?>
<span class="bg-primary text-on-primary font-label-md text-label-md px-4 py-1.5 rounded-full shadow-lg">Featured</span>
<?php endif; ?>
<span class="bg-surface/90 backdrop-blur text-primary font-label-md text-label-md px-4 py-1.5 rounded-full shadow-lg flex items-center gap-xs">
<span class="material-symbols-outlined text-[18px]" style="font-variation-settings: 'FILL' 1;">star</span>
<?= htmlspecialchars($product['category_name'] ?? 'Dessert') ?>
</span>
</div>
</div>
</div>
<!-- Product Info Section -->
<div class="lg:col-span-5 flex flex-col gap-lg">
<div class="flex flex-col gap-sm">
<h1 class="font-display-lg text-display-lg-mobile md:text-headline-lg text-primary"><?= htmlspecialchars($product['name']) ?></h1>
<p class="font-body-lg text-body-lg text-on-surface-variant leading-relaxed">
<?= htmlspecialchars($product['description']) ?>
</p>
<div class="flex items-baseline gap-sm mt-xs">
<span class="font-display-lg text-headline-lg text-primary">₱<?= number_format($product['price'], 2) ?></span>
<?php if ($product['stock'] > 10): ?>
<span class="text-xs font-semibold bg-green-100 text-green-700 px-3 py-1 rounded-full">In Stock (<?= $product['stock'] ?>)</span>
<?php elseif ($product['stock'] > 0): ?>
<span class="text-xs font-semibold bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full">Only <?= $product['stock'] ?> left</span>
<?php else: ?>
<span class="text-xs font-semibold bg-red-100 text-red-700 px-3 py-1 rounded-full">Sold Out</span>
<?php endif; ?>
</div>
</div>
<!-- Quantity & Actions - now a real cart form -->
<form method="POST" action="<?= url('?route=cart/add') ?>" id="addToCartForm">
<input type="hidden" name="product_id" value="<?= $product['id'] ?>">
<div class="bg-surface-container-low p-md rounded-xl flex flex-col gap-md">
<div class="flex items-center justify-between">
<span class="font-label-md text-label-md text-on-surface">SELECT QUANTITY</span>
<?php if ($product['stock'] > 0): ?>
<div class="flex items-center gap-md bg-surface rounded-full border border-outline-variant p-1">
<button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-primary/10 rounded-full transition-colors" onclick="updateQty(-1)">
<span class="material-symbols-outlined">remove</span>
</button>
<input type="number" name="qty" id="qtyInput" value="1" min="1" max="<?= $product['stock'] ?>"
    class="w-12 text-center font-semibold text-on-surface bg-transparent border-none focus:outline-none [appearance:textfield] [&::-webkit-outer-spin-button]:appearance-none [&::-webkit-inner-spin-button]:appearance-none"
    oninput="validateQty(this)">
<button type="button" class="w-10 h-10 flex items-center justify-center text-primary hover:bg-primary/10 rounded-full transition-colors" onclick="updateQty(1)">
<span class="material-symbols-outlined">add</span>
</button>
</div>
<?php else: ?>
<span class="text-xs font-semibold bg-red-100 text-red-700 px-3 py-1 rounded-full">Sold Out</span>
<?php endif; ?>
</div>
<p id="stockWarning" class="text-xs text-red-600 hidden">⚠ Max available: <?= $product['stock'] ?></p>
<?php if ($product['stock'] > 0): ?>
<button type="submit" class="w-full bg-primary text-on-primary font-headline-md text-headline-md py-4 rounded-full shadow-lg hover:shadow-xl active:scale-[0.98] transition-all flex items-center justify-center gap-sm">
<span class="material-symbols-outlined">shopping_bag</span>
Add to Cart — <span id="priceDisplay">₱<?= number_format($product['price'], 2) ?></span>
</button>
<?php else: ?>
<button disabled class="w-full bg-surface-container text-on-surface-variant font-headline-md text-headline-md py-4 rounded-full cursor-not-allowed flex items-center justify-center gap-sm">
Currently Unavailable
</button>
<?php endif; ?>
</div>
</form>
<!-- Layer breakdown from product description / layers field -->
<?php
$layers_raw = $product['layers'] ?? null;
$layers_arr = $layers_raw ? json_decode($layers_raw, true) : null;
if ($layers_arr && is_array($layers_arr)):
?>
<div class="flex flex-col gap-md">
<h3 class="font-label-md text-label-md text-on-surface flex items-center gap-sm">
<span class="material-symbols-outlined text-primary">layers</span>
THE LAYERS
</h3>
<div class="relative pl-6 border-l-2 border-primary-fixed">
<div class="flex flex-col gap-base">
<?php foreach (array_reverse($layers_arr) as $i => $layer): ?>
<div class="relative group">
<div class="absolute -left-[31px] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full <?= $i === 0 ? 'bg-primary' : 'bg-primary-fixed-dim' ?> ring-4 ring-background"></div>
<div class="flex items-center justify-between bg-surface p-sm rounded-lg border border-transparent hover:border-primary-fixed hover:shadow-sm transition-all cursor-default">
<span class="font-label-md <?= $i === 0 ? 'text-primary' : 'text-on-surface-variant' ?>"><?= htmlspecialchars(strtoupper($layer)) ?></span>
</div>
</div>
<?php endforeach; ?>
</div>
</div>
</div>
<?php else: ?>
<!-- Default layer breakdown if no layers data -->
<div class="flex flex-col gap-md">
<h3 class="font-label-md text-label-md text-on-surface flex items-center gap-sm">
<span class="material-symbols-outlined text-primary">layers</span>
THE ARTISAN PROCESS
</h3>
<div class="relative pl-6 border-l-2 border-primary-fixed">
<div class="flex flex-col gap-base">
<div class="relative group">
<div class="absolute -left-[31px] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-primary ring-4 ring-background"></div>
<div class="flex items-center justify-between bg-surface p-sm rounded-lg border border-transparent hover:border-primary-fixed hover:shadow-sm transition-all cursor-default">
<span class="font-label-md text-primary">HANDCRAFTED DAILY</span>
<span class="text-[12px] text-on-surface-variant font-medium">Fresh &amp; Authentic</span>
</div>
</div>
<div class="relative group">
<div class="absolute -left-[31px] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-primary-fixed-dim ring-4 ring-background"></div>
<div class="flex items-center justify-between bg-surface p-sm rounded-lg border border-transparent hover:border-primary-fixed hover:shadow-sm transition-all cursor-default">
<span class="font-label-md text-on-surface-variant">PREMIUM INGREDIENTS</span>
<span class="text-[12px] text-on-surface-variant/60">Heritage Quality</span>
</div>
</div>
<div class="relative group">
<div class="absolute -left-[31px] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-primary-fixed-dim ring-4 ring-background"></div>
<div class="flex items-center justify-between bg-surface p-sm rounded-lg border border-transparent hover:border-primary-fixed hover:shadow-sm transition-all cursor-default">
<span class="font-label-md text-on-surface-variant">ARTISANAL TECHNIQUE</span>
<span class="text-[12px] text-on-surface-variant/60">Time-Honored</span>
</div>
</div>
<div class="relative group">
<div class="absolute -left-[31px] top-1/2 -translate-y-1/2 w-3 h-3 rounded-full bg-primary ring-4 ring-background"></div>
<div class="flex items-center justify-between bg-surface p-sm rounded-lg border border-transparent hover:border-primary-fixed hover:shadow-sm transition-all cursor-default">
<span class="font-label-md text-primary">BEAUTIFULLY PRESENTED</span>
<span class="text-[12px] text-on-surface-variant font-medium">Instagrammable</span>
</div>
</div>
</div>
</div>
</div>
<?php endif; ?>

<!-- POS Add Button (staff only) -->
<?php if (!empty($_SESSION['user']) && User::has_permission($_SESSION['user']['id'], 'access_pos')): ?>
<button type="button"
    class="add-to-pos-btn w-full border-2 border-primary text-primary font-label-md text-label-md py-3 rounded-full hover:bg-primary hover:text-on-primary transition-all flex items-center justify-center gap-sm"
    data-id="<?= $product['id'] ?>"
    data-name="<?= htmlspecialchars($product['name']) ?>"
    data-price="<?= $product['price'] ?>">
    <i class="fa-solid fa-cash-register"></i> Add to POS Cart
</button>
<?php endif; ?>
</div>
</div>

<!-- View Cart Link -->
<div class="text-center mt-sm">
    <a href="<?= url('?route=cart') ?>" class="text-primary font-label-md text-label-md flex items-center justify-center gap-xs hover:underline no-underline">
        <span class="material-symbols-outlined">shopping_bag</span> View Cart
        <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty')); if ($cartCount > 0): ?>
        <span class="bg-primary text-on-primary text-xs rounded-full w-5 h-5 flex items-center justify-center"><?= $cartCount ?></span>
        <?php endif; ?>
    </a>
</div>

<!-- Related Products -->
<?php if (!empty($related_products)): ?>
<section class="mt-xl pt-xl border-t border-surface-variant">
<h2 class="font-display-lg text-headline-lg text-on-surface mb-lg">You might also like</h2>
<div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
<?php foreach ($related_products as $rel): ?>
<div onclick="goToProduct(<?= $rel['id'] ?>)" class="group cursor-pointer no-underline block">
<div class="relative aspect-square rounded-xl overflow-hidden mb-md shadow-sm">
<img class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110"
     alt="<?= htmlspecialchars($rel['name']) ?>"
     src="<?= get_product_image($rel['image_url'], $rel['id']) ?>"/>
<div class="absolute top-4 right-4 bg-surface/90 p-2 rounded-full shadow-sm opacity-0 group-hover:opacity-100 transition-opacity">
<span class="material-symbols-outlined text-primary">favorite</span>
</div>
</div>
<h4 class="font-headline-md text-headline-md text-on-surface group-hover:text-primary transition-colors"><?= htmlspecialchars($rel['name']) ?></h4>
<p class="text-on-surface-variant font-body-md">₱<?= number_format($rel['price'], 2) ?></p>
</div>
<?php endforeach; ?>
</div>
</section>
<?php endif; ?>

</main>

<!-- Footer -->
<footer class="w-full py-xl bg-surface-container-low dark:bg-surface-container-lowest border-t border-surface-variant dark:border-outline-variant">
<div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 md:grid-cols-4 gap-gutter">
<div class="col-span-1 md:col-span-1">
<span class="font-display-lg text-headline-md text-primary dark:text-primary-fixed-dim block mb-base">Stacey Halu Halu</span>
<p class="font-body-md text-body-md text-on-surface-variant max-w-[240px]">Artisanal Philippine Delights crafted for the modern connoisseur.</p>
</div>
<div class="flex flex-col gap-base">
<span class="font-label-md text-label-md text-primary">COMPANY</span>
<a class="text-on-surface-variant hover:text-primary transition-all font-body-md underline decoration-transparent hover:decoration-primary" href="#">Privacy</a>
<a class="text-on-surface-variant hover:text-primary transition-all font-body-md underline decoration-transparent hover:decoration-primary" href="#">Terms</a>
</div>
<div class="flex flex-col gap-base">
<span class="font-label-md text-label-md text-primary">EXPLORE</span>
<a class="text-on-surface-variant hover:text-primary transition-all font-body-md underline decoration-transparent hover:decoration-primary" href="<?= url('?route=contact') ?>">Contact</a>
<a class="text-on-surface-variant hover:text-primary transition-all font-body-md underline decoration-transparent hover:decoration-primary" href="<?= url('?route=branches') ?>">Branches</a>
</div>
<div class="flex flex-col gap-base">
<span class="font-label-md text-label-md text-primary">NEWSLETTER</span>
<div class="flex gap-xs">
<input class="bg-surface rounded-lg border-none focus:ring-2 focus:ring-primary w-full px-4" placeholder="Email" type="email"/>
<button class="bg-primary text-on-primary p-2 rounded-lg">
<span class="material-symbols-outlined">send</span>
</button>
</div>
<p class="text-[12px] text-on-surface-variant mt-2">© 2024 Stacey Halu Halu. All rights reserved.</p>
</div>
</div>
</footer>

<!-- Global Logout Modal -->
<?php require_once __DIR__ . '/../partials/logout_modal.php'; ?>

<script>
        const basePrice = <?= floatval($product['price']) ?>;
        const maxStock  = <?= intval($product['stock']) ?>;

        function updateQty(delta) {
            const input = document.getElementById('qtyInput');
            if (!input) return;
            let val = parseInt(input.value) || 1;
            val = Math.min(maxStock, Math.max(1, val + delta));
            input.value = val;
            updatePrice(val);
            toggleStockWarning(val);
        }

        function validateQty(input) {
            let val = parseInt(input.value) || 1;
            if (val > maxStock) { val = maxStock; input.value = val; }
            if (val < 1)        { val = 1;        input.value = val; }
            updatePrice(val);
            toggleStockWarning(val);
        }

        function updatePrice(qty) {
            const el = document.getElementById('priceDisplay');
            if (!el) return;
            const total = (qty * basePrice).toFixed(2);
            el.innerText = '₱' + parseFloat(total).toLocaleString('en-PH', {minimumFractionDigits: 2});
        }

        function toggleStockWarning(val) {
            const warn = document.getElementById('stockWarning');
            if (!warn) return;
            warn.classList.toggle('hidden', val < maxStock);
        }

        // Navbar scroll effect
        window.addEventListener('scroll', () => {
            const nav = document.getElementById('mainNav');
            if (!nav) return;
            if (window.scrollY > 50) {
                nav.classList.add('scrolled-nav');
                nav.classList.remove('bg-surface');
            } else {
                nav.classList.remove('scrolled-nav');
                nav.classList.add('bg-surface');
            }
        });

        // Fade in
        document.addEventListener('DOMContentLoaded', () => {
            document.body.style.opacity = '0';
            document.body.style.transition = 'opacity 0.5s ease-in-out';
            setTimeout(() => { document.body.style.opacity = '1'; }, 100);
        });
</script>
</body>
</html>
