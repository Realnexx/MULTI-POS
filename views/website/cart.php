<?php
$title = "Your Cart | Stacey Halu Halu";
$cart = $cart ?? [];
$total = array_sum(array_map(fn($i) => $i['price'] * $i['qty'], $cart));
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<title><?= $title ?></title>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<meta name="csrf-token" content="<?= csrf_token() ?>">
<script id="tailwind-config">
    tailwind.config = {
        darkMode: "class",
        theme: { extend: {
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
            "spacing": {"margin-mobile":"16px","lg":"40px","xl":"64px","margin-desktop":"32px","base":"8px","gutter":"24px","xs":"4px","md":"24px","sm":"12px"},
            "fontFamily": {"display-lg":["Montserrat"],"headline-md":["Montserrat"],"headline-lg":["Montserrat"],"body-md":["Inter"],"body-lg":["Inter"],"label-md":["Inter"],"label-sm":["Inter"],"display-lg-mobile":["Montserrat"]},
            "fontSize": {"label-sm":["12px",{"lineHeight":"1","fontWeight":"500"}],"body-lg":["18px",{"lineHeight":"1.6","fontWeight":"400"}],"display-lg":["48px",{"lineHeight":"1.2","letterSpacing":"-0.02em","fontWeight":"700"}],"headline-md":["24px",{"lineHeight":"1.4","fontWeight":"600"}],"headline-lg":["32px",{"lineHeight":"1.3","fontWeight":"600"}],"label-md":["14px",{"lineHeight":"1","letterSpacing":"0.05em","fontWeight":"600"}],"body-md":["16px",{"lineHeight":"1.6","fontWeight":"400"}],"display-lg-mobile":["36px",{"lineHeight":"1.2","fontWeight":"700"}]}
        }}
    }
</script>
<style>
    .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; display: inline-block; line-height: 1; vertical-align: middle; }
</style>
</head>
<body class="bg-background text-on-surface font-body-md">

<?php require_once __DIR__ . '/../partials/header_home.php'; ?>

<main class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop py-xl">
    <!-- Breadcrumbs / Page Title -->
    <div class="mb-md">
        <div class="flex items-center gap-sm text-sm text-on-surface-variant mb-sm">
            <a href="<?= url('?route=home') ?>" class="hover:text-primary transition-colors">Home</a>
            <span>/</span>
            <span class="text-primary font-semibold">Your Cart</span>
        </div>
        <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-primary">Your Sweet Selection</h1>
        <p class="font-body-md text-on-surface-variant">Review your artisanal Philippine delights before checkout.</p>
    </div>

    <!-- Flash message -->
    <?php $flash = get_flash(); if ($flash): ?>
    <div class="mb-md p-4 rounded-xl <?= $flash['type'] === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200' ?>">
        <?= $flash['message'] ?>
    </div>
    <?php endif; ?>

    <?php if (empty($cart)): ?>
    <!-- Empty Cart -->
    <div class="text-center py-24 flex flex-col items-center gap-md">
        <span class="material-symbols-outlined text-[80px] text-on-surface-variant/40">shopping_bag</span>
        <h2 class="font-headline-md text-headline-md text-on-surface-variant">Your cart is empty</h2>
        <p class="text-on-surface-variant font-body-md">Looks like you haven't added anything yet.</p>
        <a href="<?= url('?route=products') ?>" class="bg-primary text-on-primary font-label-md text-label-md px-8 py-3 rounded-full hover:opacity-90 active:scale-95 transition-all no-underline">
            Browse Our Menu
        </a>
    </div>

    <?php else: ?>
    <!-- Cart Content Grid -->
    <div class="flex flex-col lg:flex-row gap-gutter">
        <!-- Shopping Cart Content -->
        <div class="lg:w-2/3">
            <!-- Cart Items Container -->
            <div class="space-y-gutter">
                <?php foreach ($cart as $item): ?>
                <div class="bg-surface-container-lowest rounded-xl p-md flex flex-col md:flex-row gap-md shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-all hover:shadow-[0_12px_30px_rgba(182,14,61,0.12)] border border-surface-variant/30">
                    <!-- Image -->
                    <div class="w-full md:w-40 h-40 rounded-lg overflow-hidden shrink-0">
                        <img alt="<?= htmlspecialchars($item['name']) ?>" 
                             class="w-full h-full object-cover" 
                             src="<?= get_product_image($item['image_url'], $item['id']) ?>"/>
                    </div>
                    <!-- Details -->
                    <div class="flex-grow flex flex-col justify-between py-xs">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-headline-md text-on-surface"><?= htmlspecialchars($item['name']) ?></h3>
                                <p class="font-label-md text-primary uppercase tracking-wider">
                                    <?= htmlspecialchars($item['category_name'] ?? 'Premium Layer') ?>
                                </p>
                            </div>
                            <span class="font-headline-md text-primary">₱<?= number_format($item['price'], 2) ?></span>
                        </div>
                        <div class="flex flex-wrap items-center justify-between gap-sm mt-sm">
                            <!-- Qty Control -->
                            <form method="POST" action="<?= url('?route=cart/update') ?>" class="flex items-center gap-xs">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <div class="flex items-center bg-surface-container-low rounded-full px-sm py-xs">
                                    <button type="submit" name="qty" value="<?= max(0, $item['qty'] - 1) ?>"
                                            class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary-fixed rounded-full transition-colors">
                                        <span class="material-symbols-outlined">remove</span>
                                    </button>
                                    <span class="mx-md font-label-md text-on-surface w-4 text-center"><?= $item['qty'] ?></span>
                                    <button type="submit" name="qty" value="<?= min($item['stock'], $item['qty'] + 1) ?>"
                                            <?= $item['qty'] >= $item['stock'] ? 'disabled class="w-8 h-8 flex items-center justify-center text-on-surface-variant/30 rounded-full cursor-not-allowed"' : 'class="w-8 h-8 flex items-center justify-center text-primary hover:bg-primary-fixed rounded-full transition-colors"' ?>>
                                        <span class="material-symbols-outlined">add</span>
                                    </button>
                                </div>
                            </form>
                            <!-- Remove -->
                            <form method="POST" action="<?= url('?route=cart/remove') ?>">
                                <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="flex items-center gap-xs text-on-surface-variant hover:text-error transition-colors font-label-md">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                    Remove
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Parfait Progress Stepper (Decorative) -->
            <div class="mt-xl hidden md:block">
                <h4 class="font-label-md text-on-surface-variant uppercase tracking-widest mb-md text-center">Your Order Journey</h4>
                <div class="flex justify-between items-center max-w-2xl mx-auto relative">
                    <div class="absolute h-1 w-full bg-surface-container-highest top-1/2 -translate-y-1/2 z-0"></div>
                    <div class="absolute h-1 w-1/3 bg-primary top-1/2 -translate-y-1/2 z-0"></div>
                    <div class="z-10 flex flex-col items-center gap-xs">
                        <div class="w-10 h-10 rounded-full bg-primary text-on-primary flex items-center justify-center shadow-md">
                            <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
                        </div>
                        <span class="font-label-sm text-primary">Selection</span>
                    </div>
                    <div class="z-10 flex flex-col items-center gap-xs">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center">
                            <span class="material-symbols-outlined">local_shipping</span>
                        </div>
                        <span class="font-label-sm text-on-surface-variant">Shipping</span>
                    </div>
                    <div class="z-10 flex flex-col items-center gap-xs">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center">
                            <span class="material-symbols-outlined">payments</span>
                        </div>
                        <span class="font-label-sm text-on-surface-variant">Payment</span>
                    </div>
                    <div class="z-10 flex flex-col items-center gap-xs">
                        <div class="w-10 h-10 rounded-full bg-surface-container-highest text-on-surface-variant flex items-center justify-center">
                            <span class="material-symbols-outlined">check_circle</span>
                        </div>
                        <span class="font-label-sm text-on-surface-variant">Enjoy</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar: Sweetness Summary -->
        <div class="lg:w-1/3">
            <div class="sticky top-24 bg-surface-container-lowest rounded-xl p-md shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-surface-variant/30">
                <h2 class="font-headline-lg text-on-surface mb-md">Sweetness Summary</h2>
                <div class="space-y-sm mb-lg">
                    <div class="flex justify-between font-body-md text-on-surface-variant">
                        <span>Subtotal</span>
                        <span>₱<?= number_format($total, 2) ?></span>
                    </div>
                    <div class="flex justify-between font-body-md text-on-surface-variant">
                        <span>Shipping</span>
                        <span class="text-secondary font-medium">Free / Pickup</span>
                    </div>
                    <div class="pt-sm border-t border-surface-variant">
                        <div class="flex justify-between items-end">
                            <span class="font-headline-md text-on-surface">Total</span>
                            <span class="font-display-lg-mobile text-primary leading-none">₱<?= number_format($total, 2) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Send Order Request -->
                <form method="POST" action="<?= url('?route=contact') ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="subject" value="Cart Order Request">
                    <input type="hidden" name="name" value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>">
                    <input type="hidden" name="email" value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>">
                    <input type="hidden" name="message" value="<?php
                        $lines = ["I'd like to order the following:"];
                        foreach ($cart as $item) {
                            $lines[] = '- ' . $item['name'] . ' × ' . $item['qty'] . ' = ₱' . number_format($item['price'] * $item['qty'], 2);
                        }
                        $lines[] = 'Total: ₱' . number_format($total, 2);
                        echo htmlspecialchars(implode("\n", $lines));
                    ?>">
                    <button type="submit" class="w-full bg-primary text-on-primary py-md rounded-full font-headline-md shadow-md active:scale-[0.98] transition-all hover:bg-primary-container">
                        Proceed to Checkout
                    </button>
                </form>

                <div class="mt-md flex items-center justify-center gap-sm text-on-surface-variant font-label-sm">
                    <span class="material-symbols-outlined text-[16px]">lock</span>
                    Secure SSL Encrypted Checkout
                </div>
                <div class="mt-xl flex items-center gap-md grayscale opacity-50 justify-center">
                    <span class="material-symbols-outlined text-4xl">credit_card</span>
                    <span class="material-symbols-outlined text-4xl">account_balance_wallet</span>
                    <span class="material-symbols-outlined text-4xl">contactless</span>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</main>

<!-- Footer -->
<?php require __DIR__ . '/../partials/footer_home.php'; ?>

<!-- Global Logout Modal -->
<?php require_once __DIR__ . '/../partials/logout_modal.php'; ?>

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

        // Soft interaction for buttons
        document.querySelectorAll('button').forEach(btn => {
            btn.addEventListener('mouseenter', () => {
                if(btn.classList.contains('bg-primary')) {
                    btn.classList.add('shadow-lg');
                }
            });
            btn.addEventListener('mouseleave', () => {
                btn.classList.remove('shadow-lg');
            });
        });
    });
</script>
</body>
</html>
