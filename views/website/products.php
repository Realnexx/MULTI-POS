<?php
// Safe defaults — overridden by WebsiteController::products() at runtime
$title       = $title       ?? "Our Menu | Stacey Halu Halu - Sweet Efficiency";
$category_id = $category_id ?? null;
$search      = $search      ?? null;
$categories  = $categories  ?? [];
$products    = $products    ?? [];
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-lowest": "#ffffff",
                        "inverse-primary": "#ffb2b8",
                        "outline": "#8e6f71",
                        "surface-container-low": "#f3f4f5",
                        "surface-container-highest": "#e1e3e4",
                        "on-surface-variant": "#5a4042",
                        "outline-variant": "#e2bec0",
                        "tertiary-fixed-dim": "#ffba27",
                        "on-secondary-fixed-variant": "#6b00af",
                        "primary-container": "#da3054",
                        "on-background": "#191c1d",
                        "tertiary-fixed": "#ffdea9",
                        "tertiary": "#7a5500",
                        "error": "#ba1a1a",
                        "on-tertiary": "#ffffff",
                        "on-tertiary-container": "#fffbff",
                        "tertiary-container": "#996c00",
                        "surface-dim": "#d9dadb",
                        "surface": "#f8f9fa",
                        "inverse-surface": "#2e3132",
                        "on-primary": "#ffffff",
                        "secondary-fixed": "#f2daff",
                        "on-surface": "#191c1d",
                        "on-primary-fixed": "#40000f",
                        "surface-container": "#edeeef",
                        "surface-variant": "#e1e3e4",
                        "secondary-fixed-dim": "#e0b6ff",
                        "on-secondary-fixed": "#2e004e",
                        "on-primary-container": "#fffbff",
                        "secondary-container": "#9e49e3",
                        "primary": "#b60e3d",
                        "background": "#f8f9fa",
                        "surface-tint": "#ba1340",
                        "primary-fixed": "#ffdadb",
                        "on-error": "#ffffff",
                        "error-container": "#ffdad6",
                        "primary-fixed-dim": "#ffb2b8",
                        "on-primary-fixed-variant": "#91002d",
                        "on-secondary": "#ffffff",
                        "secondary": "#8429c8",
                        "on-tertiary-fixed-variant": "#5e4100",
                        "surface-container-high": "#e7e8e9",
                        "on-tertiary-fixed": "#271900",
                        "on-secondary-container": "#fffbff",
                        "inverse-on-surface": "#f0f1f2",
                        "surface-bright": "#f8f9fa",
                        "on-error-container": "#93000a"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px",
                        "pill": "9999px"
                    },
                    "spacing": {
                        "gutter": "24px",
                        "xl": "64px",
                        "margin-mobile": "16px",
                        "xs": "4px",
                        "base": "8px",
                        "margin-desktop": "32px",
                        "md": "24px",
                        "sm": "12px",
                        "lg": "40px"
                    },
                    "fontFamily": {
                        "headline-lg": ["Montserrat"],
                        "body-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"],
                        "display-lg": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "label-sm": ["Inter"],
                        "label-md": ["Inter"]
                    },
                    "fontSize": {
                        "headline-lg": ["32px", {
                            "lineHeight": "1.3",
                            "fontWeight": "600"
                        }],
                        "body-md": ["16px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }],
                        "headline-md": ["24px", {
                            "lineHeight": "1.4",
                            "fontWeight": "600"
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
                        "body-lg": ["18px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }],
                        "label-sm": ["12px", {
                            "lineHeight": "1",
                            "fontWeight": "500"
                        }],
                        "label-md": ["14px", {
                            "lineHeight": "1",
                            "letterSpacing": "0.05em",
                            "fontWeight": "600"
                        }]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }

        .sticky-subnav {
            top: 72px;
        }

        .no-scrollbar::-webkit-scrollbar {
            display: none;
        }

        .no-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .product-card-hover {
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.3s ease;
        }

        .product-card-hover:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(182, 14, 61, 0.12);
        }
    </style>
</head>

<body class="bg-background text-on-surface overflow-x-hidden">

    <!-- Header (same as home.php) -->
    <?php require __DIR__ . '/../partials/header_home.php'; ?>

    <main>
        <!-- Hero Section -->
        <section class="relative bg-surface-container-lowest overflow-hidden py-xl">
            <div class="max-w-7xl mx-auto px-margin-desktop flex flex-col items-center text-center">
                <span class="text-primary font-label-md text-label-md uppercase tracking-widest mb-base">Artisanal Craftsmanship</span>
                <h1 class="font-display-lg text-display-lg mb-md text-on-background">Our Menu</h1>
                <p class="font-body-lg text-body-lg max-w-2xl text-on-surface-variant">
                    Discover our architectural approach to Filipino sweets. Every parfait is a symphony of distinct layers, precisely chilled and masterfully assembled.
                </p>
            </div>
            <div class="absolute -right-20 -bottom-20 w-96 h-96 bg-primary opacity-5 rounded-full blur-3xl pointer-events-none"></div>
        </section>

        <!-- Category + Search Filter (sticky subnav) -->
        <div class="sticky sticky-subnav z-40 bg-white/90 backdrop-blur-md border-b border-outline-variant shadow-sm">
            <div class="max-w-7xl mx-auto px-margin-desktop py-3">

                <!-- Row 1: Category pills with scroll fade effect -->
                <div class="relative mb-3">
                    <!-- Left fade -->
                    <div class="pointer-events-none absolute left-0 top-0 bottom-0 w-8 bg-gradient-to-r from-white/90 to-transparent z-10"></div>
                    <!-- Right fade (scroll hint) -->
                    <div class="pointer-events-none absolute right-0 top-0 bottom-0 w-12 bg-gradient-to-l from-white/90 to-transparent z-10"></div>

                    <div class="flex items-center gap-2 overflow-x-auto pb-1" id="categoryPills"
                        style="scrollbar-width:thin; scrollbar-color:#e2bec0 transparent;">
                        <!-- All pill -->
                        <?php $totalCount = array_sum(array_column($categories, 'product_count')); ?>
                        <a href="<?= url('?route=products') ?>"
                            class="flex-shrink-0 flex items-center gap-1.5 whitespace-nowrap font-label-md text-label-md rounded-full px-4 py-2 border transition-all duration-200
                              <?= (empty($category_id) && empty($search))
                                    ? 'bg-primary text-white border-primary shadow-md scale-105'
                                    : 'bg-white text-on-surface-variant border-outline-variant hover:border-primary hover:text-primary hover:bg-primary/5' ?>">
                            <i class="fa-solid fa-border-all text-xs"></i>
                            <span>All</span>
                            <span class="<?= (empty($category_id) && empty($search)) ? 'bg-white/30 text-white' : 'bg-primary/10 text-primary' ?> text-xs font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[20px] text-center">
                                <?= $totalCount ?>
                            </span>
                        </a>

                        <?php foreach ($categories as $cat): ?>
                            <?php $isActive = ($category_id === (int)$cat['id']); ?>
                            <a href="<?= url('?route=products&category_id=' . $cat['id'] . (!empty($search) ? '&search=' . urlencode($search) : '')) ?>"
                                class="flex-shrink-0 flex items-center gap-1.5 whitespace-nowrap font-label-md text-label-md rounded-full px-4 py-2 border transition-all duration-200
                                  <?= $isActive
                                        ? 'bg-primary text-white border-primary shadow-md scale-105'
                                        : 'bg-white text-on-surface-variant border-outline-variant hover:border-primary hover:text-primary hover:bg-primary/5' ?>">
                                <i class="fa-solid fa-tag text-xs <?= $isActive ? 'text-white/80' : 'text-primary' ?>"></i>
                                <span><?= htmlspecialchars($cat['name']) ?></span>
                                <span class="<?= $isActive ? 'bg-white/30 text-white' : 'bg-primary/10 text-primary' ?> text-xs font-bold px-1.5 py-0.5 rounded-full leading-none min-w-[20px] text-center">
                                    <?= (int)$cat['product_count'] ?>
                                </span>
                            </a>
                        <?php endforeach; ?>

                        <!-- Scroll hint icon at the end -->
                        <span class="flex-shrink-0 text-on-surface-variant/40 pl-1 pr-2">
                            <i class="fa-solid fa-angles-right text-xs animate-pulse"></i>
                        </span>
                    </div>
                </div>

                <!-- Row 2: Enhanced Search bar -->
                <form action="<?= url('') ?>" method="GET" class="w-full">
                    <input type="hidden" name="route" value="products">
                    <?php if ($category_id): ?>
                        <input type="hidden" name="category_id" value="<?= $category_id ?>">
                    <?php endif; ?>
                    <div class="flex items-center bg-white rounded-2xl border-2 <?= !empty($search) ? 'border-primary shadow-[0_0_0_4px_rgba(182,14,61,0.08)]' : 'border-outline-variant' ?> focus-within:border-primary focus-within:shadow-[0_0_0_4px_rgba(182,14,61,0.08)] transition-all duration-200 overflow-hidden">
                        <!-- Search icon -->
                        <span class="pl-4 pr-3 shrink-0 <?= !empty($search) ? 'text-primary' : 'text-on-surface-variant' ?>">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <!-- Input -->
                        <input type="text" name="search"
                            class="flex-1 bg-transparent border-0 py-3 font-body-md text-base focus:outline-none text-on-surface placeholder:text-on-surface-variant/50"
                            placeholder="Search dishes, drinks, desserts…"
                            value="<?= htmlspecialchars($search ?? '') ?>"
                            autocomplete="off">
                        <!-- Result count + clear (when searching) -->
                        <?php if (!empty($search)): ?>
                            <span class="shrink-0 mx-2 bg-primary/10 text-primary font-label-sm text-label-sm px-2.5 py-1 rounded-full whitespace-nowrap">
                                <i class="fa-solid fa-list me-1"></i><?= count($products) ?> result<?= count($products) !== 1 ? 's' : '' ?>
                            </span>
                            <a href="<?= url('?route=products' . ($category_id ? '&category_id=' . $category_id : '')) ?>"
                                title="Clear search"
                                class="shrink-0 w-8 h-8 mr-2 flex items-center justify-center rounded-full bg-surface-container hover:bg-primary hover:text-white text-on-surface-variant transition-all">
                                <i class="fa-solid fa-xmark text-sm"></i>
                            </a>
                        <?php endif; ?>
                        <!-- Submit -->
                        <button type="submit"
                            class="shrink-0 bg-primary hover:bg-primary-container text-white font-label-md text-label-md px-6 py-3 transition-all active:scale-95 flex items-center gap-2">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <span class="hidden sm:inline">Search</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Products Grid -->
        <section class="py-xl max-w-7xl mx-auto px-margin-desktop">
            <?php if (!empty($search) || !empty($category_id)): ?>
                <div class="mb-lg flex items-center justify-between flex-wrap gap-sm">
                    <div>
                        <h2 class="font-headline-lg text-headline-lg text-on-background">
                            <?= !empty($category_id) ? htmlspecialchars($categories[array_search($category_id, array_column($categories, 'id'))]['name'] ?? 'Products') : 'Search Results' ?>
                        </h2>
                        <?php if (!empty($search)): ?>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Results for "<strong><?= htmlspecialchars($search) ?></strong>"</p>
                        <?php endif; ?>
                    </div>
                    <a href="<?= url('?route=products') ?>" class="font-label-md text-label-md text-primary hover:underline flex items-center gap-xs">
                        <i class="fa-solid fa-xmark"></i> Clear filters
                    </a>
                </div>
            <?php else: ?>
                <div class="mb-lg">
                    <h2 class="font-headline-lg text-headline-lg text-on-background">All Items</h2>
                    <p class="font-body-md text-body-md text-on-surface-variant mt-xs">Everything we craft with love and precision.</p>
                </div>
            <?php endif; ?>

            <?php if (empty($products)): ?>
                <!-- Empty State -->
                <div class="flex flex-col items-center justify-center py-xl text-center">
                    <div class="w-24 h-24 rounded-full bg-primary/10 flex items-center justify-center mb-md">
                        <i class="fa-solid fa-face-sad-tear text-primary text-4xl"></i>
                    </div>
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-sm">No treats found</h3>
                    <p class="font-body-md text-body-md text-on-surface-variant max-w-md mb-lg">We couldn't find any items matching your search. Try exploring our other delicious options!</p>
                    <a href="<?= url('?route=products') ?>" class="bg-primary hover:bg-primary-container text-white px-xl py-sm rounded-full font-label-md text-label-md transition-all active:scale-95 shadow-sm">
                        <i class="fa-solid fa-book-open me-1"></i> View All Menu
                    </a>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-gutter">
                    <?php foreach ($products as $prod): ?>
                        <div class="bg-surface-container-lowest rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.06)] flex flex-col group product-card-hover">
                            <!-- Image -->
                            <div onclick="goToProduct(<?= $prod['id'] ?>)" class="relative overflow-hidden h-56 cursor-pointer">
                                <img src="<?= get_product_image($prod['image_url'], $prod['id']) ?>"
                                     alt="<?= htmlspecialchars($prod['name']) ?>"
                                     class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700">
                                <!-- Badges -->
                                <?php if ($prod['is_best_seller']): ?>
                                    <span class="absolute top-3 right-3 bg-tertiary-fixed text-on-tertiary-fixed font-label-md text-label-md px-sm py-xs rounded-full shadow-sm">
                                        <i class="fa-solid fa-crown me-1"></i> Best Seller
                                    </span>
                                <?php elseif ($prod['is_featured']): ?>
                                    <span class="absolute top-3 right-3 bg-primary text-white font-label-md text-label-md px-sm py-xs rounded-full shadow-sm">
                                        <i class="fa-solid fa-star me-1"></i> Featured
                                    </span>
                                <?php endif; ?>
                                <!-- Category badge -->
                                <span class="absolute bottom-3 left-3 bg-surface/80 backdrop-blur-sm text-on-surface-variant font-label-sm text-label-sm px-sm py-xs rounded-full border border-outline-variant">
                                    <i class="fa-solid fa-tag me-1 text-primary"></i><?= htmlspecialchars($prod['category_name'] ?? 'Dessert') ?>
                                </span>
                            </div>
                            <!-- Body -->
                            <div class="p-md flex flex-col flex-grow">
                                <div class="flex justify-between items-start mb-sm">
                                    <h3 onclick="goToProduct(<?= $prod['id'] ?>)" class="font-headline-md text-headline-md text-on-surface hover:text-primary cursor-pointer transition-colors"><?= htmlspecialchars($prod['name']) ?></h3>
                                    <span class="text-primary font-bold font-body-lg text-body-lg ml-sm shrink-0">₱<?= number_format($prod['price'], 2) ?></span>
                                </div>
                                <p class="font-body-md text-body-md text-on-surface-variant mb-md flex-grow">
                                    <?= htmlspecialchars($prod['description']) ?>
                                </p>
                                <div class="grid grid-cols-2 gap-sm mt-auto">
                                    <button type="button" onclick="addToCart(<?= $prod['id'] ?>)"
                                        class="py-sm px-xs border border-primary text-primary hover:bg-primary/5 rounded-full font-label-md text-label-md transition-all active:scale-95 flex items-center justify-center gap-xs cursor-pointer">
                                        <span class="material-symbols-outlined text-base">add_shopping_cart</span> Add to Cart
                                    </button>
                                    <button type="button" onclick="buyNow(<?= $prod['id'] ?>)"
                                        class="py-sm px-xs bg-primary hover:bg-primary-container text-white rounded-full font-label-md text-label-md transition-all active:scale-95 flex items-center justify-center gap-xs cursor-pointer">
                                        <span class="material-symbols-outlined text-base">shopping_bag</span> Buy Now
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <!-- Custom Studio CTA -->
        <section class="max-w-7xl mx-auto px-margin-desktop pb-xl">
            <div class="relative bg-primary text-white rounded-xl overflow-hidden px-md py-lg md:p-xl flex flex-col md:flex-row items-center justify-between gap-lg">
                <!-- Text + Button -->
                <div class="relative z-10 w-full md:w-3/5 text-center md:text-left">
                    <h2 class="font-headline-lg text-headline-lg md:font-display-lg md:text-display-lg mb-sm md:mb-md">The Custom Studio</h2>
                    <p class="font-body-md text-body-md md:font-body-lg md:text-body-lg opacity-90 mb-md md:mb-lg">
                        Select your base, pick artisanal layers, and top it off with our house-made ice creams. Your flavor profile, engineered by you.
                    </p>
                    <a href="<?= url('?route=products&category_id=4') ?>"
                        class="block w-full sm:w-auto sm:inline-block text-center bg-surface-container-lowest text-primary px-lg py-sm md:px-xl md:py-md rounded-full font-label-md text-label-md font-bold shadow-lg hover:scale-105 transition-all active:scale-95">
                        <i class="fa-solid fa-wand-magic-sparkles me-2"></i>Start Building Your Layer
                    </a>
                </div>
                <!-- Parfait Visual (hidden on mobile) -->
                <div class="hidden md:flex md:w-2/5 justify-center">
                    <div class="w-56 h-72 bg-white/20 backdrop-blur-md border border-white/30 rounded-t-full relative flex flex-col-reverse p-4 gap-2">
                        <div class="h-8 bg-tertiary-fixed rounded-full animate-pulse" style="animation-delay:0.1s"></div>
                        <div class="h-8 bg-secondary-fixed rounded-full animate-pulse" style="animation-delay:0.2s"></div>
                        <div class="h-12 bg-primary-fixed rounded-full animate-pulse" style="animation-delay:0.3s"></div>
                        <div class="h-8 bg-outline-variant rounded-full animate-pulse" style="animation-delay:0.4s"></div>
                        <div class="h-16 bg-white/60 rounded-full animate-pulse" style="animation-delay:0.5s"></div>
                        <span class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 font-headline-md text-white font-black text-center leading-tight">YOUR<br />PARFAIT</span>
                    </div>
                </div>
                <div class="absolute -top-10 -right-10 w-64 h-64 bg-white/10 rounded-full pointer-events-none"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-black/5 rounded-full pointer-events-none"></div>
            </div>
        </section>
    </main>

    <!-- Footer (same as home.php) -->
    <?php require __DIR__ . '/../partials/footer_home.php'; ?>

    <!-- Mobile FAB -->
    <div class="fixed bottom-6 right-6 z-50 md:hidden">
        <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty')); ?>
        <a href="<?= url('?route=cart') ?>" class="bg-primary text-white rounded-full shadow-2xl active:scale-95 transition-all flex items-center justify-center w-14 h-14 relative">
            <i class="fa-solid fa-cart-shopping text-xl"></i>
            <span id="mobileCartBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center border-2 border-primary <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
        </a>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Mobile menu toggle
            const btn = document.getElementById('mobileMenuBtn');
            const menu = document.getElementById('mobileMenu');
            const icon = document.getElementById('mobileMenuIcon');
            if (btn && menu && icon) {
                btn.addEventListener('click', () => {
                    const hidden = menu.classList.contains('hidden');
                    menu.classList.toggle('hidden', !hidden);
                    icon.textContent = hidden ? 'close' : 'menu';
                    icon.classList.toggle('rotate-90', hidden);
                });
            }

            // Sticky header scroll effect
            const header = document.getElementById('mainHeader');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    header.classList.add('shadow-md');
                } else {
                    header.classList.remove('shadow-md');
                }
            });

            // Subnav active category highlight on scroll (intersection observer)
            const sections = document.querySelectorAll('section[id]');
            // Card hover lift already handled by CSS
        });

        // Toast helper function
        function showToast(htmlContent) {
            let container = document.getElementById('toast-container');
            if (!container) {
                container = document.createElement('div');
                container.id = 'toast-container';
                container.className = "fixed top-24 right-6 z-[9999] flex flex-col gap-2 pointer-events-none";
                document.body.appendChild(container);
            }
            const toast = document.createElement('div');
            toast.className = "bg-surface-container-highest/90 backdrop-blur-md border border-primary/20 text-on-surface p-4 rounded-xl shadow-lg flex items-center gap-3 transition-all duration-500 translate-y-4 opacity-0 pointer-events-auto max-w-sm";
            toast.innerHTML = `
                <span class="material-symbols-outlined text-primary text-xl">check_circle</span>
                <div class="flex-1 font-body-md text-sm">${htmlContent}</div>
                <button onclick="this.parentElement.remove()" class="text-on-surface-variant hover:text-primary transition-colors cursor-pointer bg-transparent border-0 outline-none">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>
            `;
            container.appendChild(toast);
            
            // Trigger transition
            setTimeout(() => {
                toast.classList.remove('translate-y-4', 'opacity-0');
            }, 50);

            // Auto remove
            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-y-[-10px]');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }

        // Add to Cart via AJAX
        function addToCart(id) {
            const formData = new FormData();
            formData.append('product_id', id);
            formData.append('qty', 1);
            formData.append('ajax', 1);

            fetch('<?= url("?route=cart/add") ?>', {
                method: 'POST',
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (data.status === 'success') {
                    showToast(data.message);
                    
                    // Update cart badges
                    const badges = document.querySelectorAll('#mobileCartBadge, #navCartBadge');
                    badges.forEach(b => {
                        b.textContent = data.cart_count;
                        b.classList.remove('hidden');
                    });
                } else {
                    showToast('⚠ ' + data.message);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('⚠ Error adding item to cart.');
            });
        }

        // Buy Now via Form POST
        function buyNow(id) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= url("?route=cart/add") ?>';
            
            const prodInput = document.createElement('input');
            prodInput.type = 'hidden';
            prodInput.name = 'product_id';
            prodInput.value = id;
            form.appendChild(prodInput);

            const qtyInput = document.createElement('input');
            qtyInput.type = 'hidden';
            qtyInput.name = 'qty';
            qtyInput.value = 1;
            form.appendChild(qtyInput);

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>
</html>