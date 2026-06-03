<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title><?= htmlspecialchars($content['hero_title'] ?? 'Stacy Halo Halo | Sweet Efficiency') ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <!-- AOS Animations CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "surface-container-lowest": "#ffffff",
                        "on-secondary": "#ffffff",
                        "on-surface": "#191c1d",
                        "tertiary-fixed-dim": "#ffba27",
                        "secondary-fixed-dim": "#e0b6ff",
                        "background": "#f8f9fa",
                        "on-background": "#191c1d",
                        "surface-tint": "#ba1340",
                        "surface-container": "#edeeef",
                        "secondary-fixed": "#f2daff",
                        "on-secondary-fixed-variant": "#6b00af",
                        "inverse-on-surface": "#f0f1f2",
                        "on-secondary-fixed": "#2e004e",
                        "on-primary-fixed": "#40000f",
                        "on-primary": "#ffffff",
                        "inverse-surface": "#2e3132",
                        "outline-variant": "#e2bec0",
                        "tertiary-container": "#996c00",
                        "surface": "#f8f9fa",
                        "surface-variant": "#e1e3e4",
                        "primary-fixed": "#ffdadb",
                        "primary-fixed-dim": "#ffb2b8",
                        "outline": "#8e6f71",
                        "inverse-primary": "#ffb2b8",
                        "error": "#ba1a1a",
                        "error-container": "#ffdad6",
                        "on-tertiary": "#ffffff",
                        "surface-container-high": "#e7e8e9",
                        "tertiary-fixed": "#ffdea9",
                        "surface-dim": "#d9dadb",
                        "on-secondary-container": "#fffbff",
                        "secondary-container": "#9e49e3",
                        "primary-container": "#da3054",
                        "on-tertiary-container": "#fffbff",
                        "surface-bright": "#f8f9fa",
                        "on-error-container": "#93000a",
                        "on-tertiary-fixed": "#271900",
                        "primary": "#b60e3d",
                        "secondary": "#8429c8",
                        "surface-container-highest": "#e1e3e4",
                        "on-error": "#ffffff",
                        "tertiary": "#7a5500",
                        "on-primary-container": "#fffbff",
                        "on-surface-variant": "#5a4042",
                        "surface-container-low": "#f3f4f5",
                        "on-tertiary-fixed-variant": "#5e4100",
                        "on-primary-fixed-variant": "#91002d"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px",
                        "pill": "9999px"
                    },
                    "spacing": {
                        "margin-mobile": "16px",
                        "margin-desktop": "32px",
                        "base": "8px",
                        "md": "24px",
                        "gutter": "24px",
                        "sm": "12px",
                        "lg": "40px",
                        "xs": "4px",
                        "xl": "64px"
                    },
                    "fontFamily": {
                        "headline-lg": ["Montserrat"],
                        "label-md": ["Inter"],
                        "headline-md": ["Montserrat"],
                        "body-lg": ["Inter"],
                        "label-sm": ["Inter"],
                        "display-lg": ["Montserrat"],
                        "display-lg-mobile": ["Montserrat"],
                        "body-md": ["Inter"]
                    },
                    "fontSize": {
                        "headline-lg": ["32px", {
                            "lineHeight": "1.3",
                            "fontWeight": "600"
                        }],
                        "label-md": ["14px", {
                            "lineHeight": "1",
                            "letterSpacing": "0.05em",
                            "fontWeight": "600"
                        }],
                        "headline-md": ["24px", {
                            "lineHeight": "1.4",
                            "fontWeight": "600"
                        }],
                        "body-lg": ["18px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }],
                        "label-sm": ["12px", {
                            "lineHeight": "1",
                            "fontWeight": "500"
                        }],
                        "display-lg": ["48px", {
                            "lineHeight": "1.2",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }],
                        "display-lg-mobile": ["36px", {
                            "lineHeight": "1.2",
                            "fontWeight": "700"
                        }],
                        "body-md": ["16px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }]
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-background text-on-background font-body-md overflow-x-clip relative w-full">
    <?php require __DIR__ . '/../partials/header_home.php'; ?>

    <main class="overflow-x-hidden w-full max-w-full">
        <!-- Hero Section -->
        <section class="relative min-h-[819px] flex items-center overflow-hidden hero-gradient">
            <div class="absolute inset-0 z-0 pointer-events-none">
                <video autoplay loop muted playsinline class="w-full h-full object-cover absolute inset-0 z-0 animate-scale-in">
                    <source src="<?= url('assets/video/bg.mp4') ?>" type="video/mp4">
                    Your browser does not support the video tag.
                </video>
                <div class="absolute inset-0 bg-gradient-to-r from-white/95 via-white/80 to-white/30 z-10"></div>
            </div>
            <div class="container mx-auto px-margin-desktop relative z-20">
                <div class="max-w-2xl" data-aos="fade-right" data-aos-duration="1000">
                    <span class="inline-block px-md py-xs bg-primary-fixed text-on-primary-fixed-variant rounded-pill font-label-md text-label-md mb-md shadow-xs" data-aos="zoom-in" data-aos-delay="100"><i class="fa-solid fa-star me-1 text-tertiary-fixed-dim"></i> PREMIUM DESSERT SHOP</span>
                    <h1 class="font-display-lg text-display-lg mb-md leading-tight text-on-background" data-aos="fade-up" data-aos-delay="200">
                        <?= htmlspecialchars($content['hero_title'] ?? 'Sweet Efficiency in') ?> <br /> <span class="text-primary italic">Every Scoop.</span>
                    </h1>
                    <p class="font-body-lg text-body-lg text-on-surface-variant mb-lg max-w-lg" data-aos="fade-up" data-aos-delay="400">
                        <?= htmlspecialchars($content['hero_subtitle'] ?? "We've perfected the science of the parfait. Enjoy our signature layered desserts made with precision and the freshest ingredients delivered to your doorstep.") ?>
                    </p>
                    <div class="flex flex-col sm:flex-row gap-3 md:gap-4 mt-6 w-full sm:w-auto" data-aos="fade-up" data-aos-delay="600">
                        <a href="<?= url('?route=products') ?>" class="bg-primary hover:bg-primary-container text-white px-6 py-3.5 md:px-8 md:py-4 rounded-full font-semibold text-sm md:text-lg shadow-lg hover:shadow-xl active:scale-95 transition-all flex items-center justify-center gap-2 btn-animated w-full sm:w-auto">
                            <i class="fa-solid fa-cart-shopping"></i> Order Now
                            <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                        <a href="<?= url('?route=products') ?>" class="bg-surface-container-lowest border border-outline-variant hover:bg-surface-container text-on-surface px-6 py-3.5 md:px-8 md:py-4 rounded-full font-semibold text-sm md:text-lg active:scale-95 transition-all flex items-center justify-center gap-2 shadow-sm hover:shadow btn-animated w-full sm:w-auto">
                            <i class="fa-solid fa-book-open text-primary"></i> View Menu
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories & Filter -->
        <section class="py-16 md:py-24 bg-surface-container-lowest">
            <div class="container mx-auto px-6 md:px-12 lg:px-24">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 mb-12">
                    <div class="max-w-xl" data-aos="fade-up" data-aos-duration="800">
                        <h2 class="font-display-lg text-3xl md:text-4xl font-bold text-on-background mb-3">Explore Our Flavors</h2>
                        <p class="font-body-md text-on-surface-variant text-base md:text-lg">Carefully curated layers of joy. Filter by category to find your perfect refreshment.</p>
                    </div>
                    <div class="flex gap-3 overflow-x-auto pb-3 w-full md:w-auto custom-scrollbar flex-nowrap" data-aos="fade-left" data-aos-duration="800" data-aos-delay="200">
                        <a href="<?= url('?route=products') ?>" class="px-6 py-2.5 rounded-full bg-primary text-white font-semibold text-sm md:text-base shadow-sm whitespace-nowrap inline-block hover:bg-primary-container transition-all btn-animated flex-shrink-0"><i class="fa-solid fa-shapes me-1"></i> All Items</a>
                        <?php foreach ($categories as $cat): ?>
                            <a href="<?= url('?route=products&category_id=' . $cat['id']) ?>" class="px-6 py-2.5 rounded-full bg-surface-container hover:bg-surface-container-high text-on-surface-variant font-semibold text-sm md:text-base whitespace-nowrap transition-all inline-block shadow-xs btn-animated flex-shrink-0"><i class="fa-solid fa-tag me-1 text-primary"></i> <?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php
                // Fallback Stitch images if database image is default
                $stitch_images = [
                    "https://lh3.googleusercontent.com/aida-public/AB6AXuCMRCcZvGukWIf0WGBH8mjNtYrLbKGmyMyr1k98OEyIA6PFVVsy7HlK6spYzrfGGZAv64SMH0VxU6ZQrx9RfIWUdxgy-11QbrsN4AAB6iJgwSjE2NVgY1MEWmRklmJSqpdZ7AMtXUGQ8CP9pbSyMQOo-X1EpyWySEJyBZWu58QOGG6S7GluaAGnuVWbKiw7eXdyrD0Ybk_5IrLGAyOh-du6uRBiy8mKdEcgZ5f1zIe5Ek4AbEnAZs2vbBUHDYUAgPVEGExHKYP7U3M",
                    "https://lh3.googleusercontent.com/aida-public/AB6AXuCIt5CSeJJ2kkqxn51omKtMRHh5m0SsqDvn1rngdOAgi9jOAaTvxWiwREtIC9cfUcVYT0V4cZzIWvlBGBBR7Baiy9XGNIrZOFUL5gshsR2Z931tne_i9jroq4EFVRvLDiCgjRbPRzYjMuQPqOyEcCIx7i_6WkP2gjfxIfbRLqUAjvZwEQaztLwojwqJr0DbFMPvnA4grryxznoLc1A-qO-bt5eYNPcFuoOryquvcVakMQr1FBTGJuKw2rcPv_LrVyksZ1FDNWSeOqI",
                    "https://lh3.googleusercontent.com/aida-public/AB6AXuAh6eGMBPVKS1sQ8BYTXAJ4wzoo3LCytQL3zKEMKAJ_4ZwZopDEjnq0lA6dfGsQ4Ch1MzViW4w2RZ66vlPIm1wLNdhdBPNVl7Qvyn3-4R1GIc2234aMfYM343xLXxlQq8PF-0BKm1ebLjtj7LiQDNyCkJpmecKlU_YNROpVgBz8pXcsISUh3eGCFahtJ_fPdE46-yRsH6Qyf-4K1ZUgMlSBUwlj-YYAICd7sfQ9nWoobY3VF49RsrNya1BdezX1lVV46VHk6KJnuns",
                    "https://lh3.googleusercontent.com/aida-public/AB6AXuBGVnk0eA7kTF7Ol2FHuK2JJ7_4P3DNPocaoWb2iT5nMetP3HVKQM-HMm1hae1Vrf3E0HeTtivJdZufBb5zp048NfMtBsUKIgwig7xbMCpKPWL1MtJ8RvU51-VtF9TL23y2W9BtxBqM7ki99V14RHA4rX_oAPzk17U4Pxxz10vxX8NdaNPVzpMHumD_gunj-hO7l-rFXPD_N2nP9jNKkTJlJbSlSC3TR0am9rv6-dcQAwfynrMobmSQX1TNAo_cs95ac58JgDTQFIQ"
                ];

                $display_products = !empty($best_sellers) ? array_values($best_sellers) : [
                    ['id' => 1, 'name' => 'Classic Halo-halo', 'price' => 185, 'description' => 'Our original 12-layer recipe that started it all. Every ingredient is house-made daily.', 'image_url' => $stitch_images[0]],
                    ['id' => 2, 'name' => 'Ube Special', 'price' => 215, 'description' => 'Double the Ube, double the joy. Featuring our artisanal Ube Halaya blend.', 'image_url' => $stitch_images[1]],
                    ['id' => 3, 'name' => 'Mango Graham', 'price' => 165, 'description' => 'Fresh Philippine mangoes layered with buttery graham and sweet cream.', 'image_url' => $stitch_images[2]],
                    ['id' => 4, 'name' => 'Melon Cooler', 'price' => 95, 'description' => 'Shredded cantaloupe in light syrup. The ultimate thirst quencher.', 'image_url' => $stitch_images[3]]
                ];

                // Ensure images use stitch fallbacks if missing or default
                foreach ($display_products as $key => &$prod) {
                    $prod['image_url'] = get_product_image($prod['image_url'] ?? '', $prod['id'] ?? $key);
                }
                ?>

                <!-- Featured Products Bento Grid -->
                <div class="grid grid-cols-1 md:grid-cols-12 gap-6 md:gap-8">
                    <!-- Featured Card 1: Large -->
                    <?php if (isset($display_products[0])): $prod = $display_products[0]; ?>
                        <div class="md:col-span-8 group cursor-pointer hover-scale-card" onclick="goToProduct(<?= $prod['id'] ?>)" data-aos="fade-up" data-aos-duration="1000">
                            <div class="relative h-[400px] md:h-[500px] rounded-2xl overflow-hidden card-shadow">
                                <img alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= $prod['image_url'] ?>" />
                                <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/40 to-transparent flex flex-col justify-end p-6 md:p-8">
                                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
                                        <div>
                                            <span class="bg-tertiary-fixed text-on-tertiary-fixed px-3 py-1 rounded-full text-xs font-bold mb-3 inline-block uppercase tracking-wider shadow-sm"><i class="fa-solid fa-crown me-1"></i> Best Seller</span>
                                            <h3 class="font-display-lg text-2xl md:text-3xl font-bold text-white mb-2"><?= htmlspecialchars($prod['name']) ?></h3>
                                            <p class="text-white/90 font-body-md text-sm md:text-base max-w-md"><?= htmlspecialchars($prod['description']) ?></p>
                                        </div>
                                        <div class="flex sm:flex-col items-center sm:items-end justify-between w-full sm:w-auto border-t sm:border-0 border-white/20 pt-4 sm:pt-0 mt-2 sm:mt-0">
                                            <span class="block font-headline-md text-2xl md:text-3xl font-extrabold text-white sm:mb-3">₱<?= number_format($prod['price'], 2) ?></span>
                                            <button class="bg-white text-primary px-6 py-2.5 rounded-full font-semibold text-sm md:text-base flex items-center gap-2 hover:bg-primary hover:text-white transition-all shadow-md btn-animated">
                                                <i class="fa-solid fa-cart-shopping"></i>
                                                <span>Order</span>
                                                <i class="fa-solid fa-arrow-right ms-1"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Featured Card 2 -->
                    <?php if (isset($display_products[1])): $prod = $display_products[1]; ?>
                        <div class="md:col-span-4 group cursor-pointer hover-scale-card" onclick="goToProduct(<?= $prod['id'] ?>)" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="200">
                            <div class="flex flex-col h-full bg-surface-container-lowest rounded-2xl overflow-hidden card-shadow border border-surface-container">
                                <div class="h-56 md:h-64 relative overflow-hidden bg-gray-100">
                                    <img alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= $prod['image_url'] ?>" />
                                </div>
                                <div class="p-6 flex flex-col flex-grow">
                                    <h3 class="font-headline-md text-xl font-bold mb-2 text-on-background"><?= htmlspecialchars($prod['name']) ?></h3>
                                    <p class="font-body-md text-on-surface-variant text-sm md:text-base mb-6 flex-grow"><?= htmlspecialchars($prod['description']) ?></p>
                                    <div class="flex justify-between items-center mt-auto pt-4 border-t border-surface-container">
                                        <span class="font-headline-md text-xl font-extrabold text-primary">₱<?= number_format($prod['price'], 2) ?></span>
                                        <button class="bg-primary/10 text-primary px-4 py-2 rounded-xl font-semibold text-sm hover:bg-primary hover:text-white transition-all flex items-center gap-1.5 btn-animated">
                                            <i class="fa-solid fa-cart-plus"></i>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Additional Grid Items -->
                    <?php if (isset($display_products[2])): $prod = $display_products[2]; ?>
                        <div class="md:col-span-4 group cursor-pointer hover-scale-card" onclick="goToProduct(<?= $prod['id'] ?>)" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                            <div class="flex flex-col h-full bg-surface-container-lowest rounded-2xl overflow-hidden card-shadow border border-surface-container">
                                <div class="h-56 md:h-64 relative overflow-hidden bg-gray-100">
                                    <img alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= $prod['image_url'] ?>" />
                                </div>
                                <div class="p-6 flex flex-col flex-grow">
                                    <h3 class="font-headline-md text-xl font-bold mb-2 text-on-background"><?= htmlspecialchars($prod['name']) ?></h3>
                                    <p class="font-body-md text-on-surface-variant text-sm md:text-base mb-6 flex-grow"><?= htmlspecialchars($prod['description']) ?></p>
                                    <div class="flex justify-between items-center mt-auto pt-4 border-t border-surface-container">
                                        <span class="font-headline-md text-xl font-extrabold text-primary">₱<?= number_format($prod['price'], 2) ?></span>
                                        <button class="bg-primary/10 text-primary px-4 py-2 rounded-xl font-semibold text-sm hover:bg-primary hover:text-white transition-all flex items-center gap-1.5 btn-animated">
                                            <i class="fa-solid fa-cart-plus"></i>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($display_products[3])): $prod = $display_products[3]; ?>
                        <div class="md:col-span-4 group cursor-pointer hover-scale-card" onclick="goToProduct(<?= $prod['id'] ?>)" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                            <div class="flex flex-col h-full bg-surface-container-lowest rounded-2xl overflow-hidden card-shadow border border-surface-container">
                                <div class="h-56 md:h-64 relative overflow-hidden bg-gray-100">
                                    <img alt="<?= htmlspecialchars($prod['name']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105" src="<?= $prod['image_url'] ?>" />
                                </div>
                                <div class="p-6 flex flex-col flex-grow">
                                    <h3 class="font-headline-md text-xl font-bold mb-2 text-on-background"><?= htmlspecialchars($prod['name']) ?></h3>
                                    <p class="font-body-md text-on-surface-variant text-sm md:text-base mb-6 flex-grow"><?= htmlspecialchars($prod['description']) ?></p>
                                    <div class="flex justify-between items-center mt-auto pt-4 border-t border-surface-container">
                                        <span class="font-headline-md text-xl font-extrabold text-primary">₱<?= number_format($prod['price'], 2) ?></span>
                                        <button class="bg-primary/10 text-primary px-4 py-2 rounded-xl font-semibold text-sm hover:bg-primary hover:text-white transition-all flex items-center gap-1.5 btn-animated">
                                            <i class="fa-solid fa-cart-plus"></i>
                                            <span>Add</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Custom Mix CTA Card -->
                    <div class="md:col-span-4 group cursor-pointer hover-scale-card" onclick="window.location.href='<?= url('?route=products&category_id=4') ?>'" data-aos="zoom-in" data-aos-duration="1000" data-aos-delay="600">
                        <div class="bg-primary rounded-2xl p-8 flex flex-col justify-center items-center text-center text-white card-shadow h-full relative overflow-hidden">
                            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl animate-pulse-glow"></div>
                            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl animate-pulse-glow"></div>
                            <h3 class="font-display-lg text-2xl font-bold mb-4">Want a custom mix?</h3>
                            <p class="font-body-md text-white/90 text-sm md:text-base mb-8 max-w-xs">Build your own parfait with our selection of over 20 premium toppings.</p>
                            <button class="bg-white text-primary px-8 py-3 rounded-full font-semibold text-sm md:text-base hover:bg-gray-100 transition-all shadow-md active:scale-95 btn-animated"><i class="fa-solid fa-wand-magic-sparkles me-2"></i> Customize Now</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Parfait Progress Section -->
        <section class="py-16 md:py-24 bg-surface-container-low">
            <div class="container mx-auto px-6 md:px-12 lg:px-24">
                <div class="bg-surface-container-lowest rounded-2xl p-8 md:p-12 card-shadow flex flex-col lg:flex-row items-center gap-12 hover-scale-card" data-aos="fade-up" data-aos-duration="1000">
                    <div class="lg:w-1/2" data-aos="fade-right" data-aos-duration="1000" data-aos-delay="200">
                        <h2 class="font-display-lg text-3xl md:text-4xl font-bold mb-4 text-on-background">Real-Time Layering Tracking</h2>
                        <p class="font-body-lg text-base md:text-lg text-on-surface-variant mb-8">Watch your halo-halo come to life with our "Parfait Progress" tracker. From base prep to the final scoop, we keep you informed.</p>
                        <!-- Specialty Component: The Parfait Progress -->
                        <div class="space-y-6">
                            <div class="flex items-start md:items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white shrink-0 mt-1 md:mt-0 shadow-sm">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check</span>
                                </div>
                                <div class="flex-grow pb-4 border-b border-surface-container">
                                    <h4 class="font-headline-md text-lg md:text-xl font-bold text-on-background mb-1">The Base Layer</h4>
                                    <p class="text-on-surface-variant font-body-md text-sm md:text-base">Sourcing & preparing premium beans and jellies.</p>
                                </div>
                            </div>
                            <div class="flex items-start md:items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary flex items-center justify-center text-white shrink-0 mt-1 md:mt-0 shadow-sm">
                                    <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">check</span>
                                </div>
                                <div class="flex-grow pb-4 border-b border-surface-container">
                                    <h4 class="font-headline-md text-lg md:text-xl font-bold text-on-background mb-1">Shaving Excellence</h4>
                                    <p class="text-on-surface-variant font-body-md text-sm md:text-base">Precision shaved ice with creamy milk infusion.</p>
                                </div>
                            </div>
                            <div class="flex items-start md:items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-primary-container flex items-center justify-center text-on-primary-container shrink-0 mt-1 md:mt-0 animate-pulse shadow-sm">
                                    <span class="material-symbols-outlined">hourglass_top</span>
                                </div>
                                <div class="flex-grow pb-4 border-b border-surface-container">
                                    <h4 class="font-headline-md text-lg md:text-xl font-bold text-on-background mb-1">Top Tier Finish</h4>
                                    <p class="text-on-surface-variant font-body-md text-sm md:text-base">Topping with artisanal ube ice cream and leche flan.</p>
                                </div>
                            </div>
                            <div class="flex items-start md:items-center gap-4">
                                <div class="w-10 h-10 rounded-full bg-surface-container flex items-center justify-center text-on-surface-variant shrink-0 mt-1 md:mt-0">
                                    <span class="material-symbols-outlined">local_shipping</span>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="font-headline-md text-lg md:text-xl font-bold text-on-surface-variant/50 mb-1">Out for Delivery</h4>
                                    <p class="text-on-surface-variant/50 font-body-md text-sm md:text-base">Our riders ensure the chill stays until the first bite.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="lg:w-1/2 relative h-[350px] md:h-[500px] w-full rounded-2xl overflow-hidden shadow-2xl" data-aos="fade-left" data-aos-duration="1000" data-aos-delay="400">
                        <img alt="Prep Station" class="w-full h-full object-cover animate-float" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAlF7rM-Ra0288-aIlhfWDdUgewk9JYMv9In0_rsZHKd9eD9syMDJVhwM-YIUETQaEChwRhmdHkAG1c9Jc013hUizw40t8ErGgdNmnNYx4ftjJfsmnDfJ4D6T3zaXC7r0-YP4XBKZ9FyejiQIpDNPkdGxsqhYRanw7HpYTTMwPQS-hBO0qm-PCry7Cd3k5x-QyE-hzL8Exg_hBBqMCLcRSOuQ9MEpJSCsve8lcSTO5GiIcbur8hUGv_pD8ileR8vhbnMcEY8MPcsLw" />
                    </div>
                </div>
            </div>
        </section>

        <!-- Newsletter / CTA -->
        <section class="py-16 md:py-24 bg-primary relative overflow-hidden" data-aos="zoom-in-up" data-aos-duration="1000">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -mr-48 -mt-48 blur-3xl animate-pulse-glow"></div>
            <div class="container mx-auto px-6 md:px-12 lg:px-24 text-center relative z-10">
                <h2 class="font-display-lg text-3xl md:text-5xl font-bold text-white mb-4">Ready for a Treat?</h2>
                <p class="font-body-lg text-base md:text-lg text-white/90 mb-10 max-w-2xl mx-auto">Join the Halo Halo Club and get exclusive offers, flavor drops, and 15% off your first order.</p>
                <form class="flex flex-col sm:flex-row justify-center gap-4 max-w-xl mx-auto" onsubmit="event.preventDefault(); alert('Thank you for subscribing!');">
                    <input class="flex-grow rounded-full border-0 px-6 py-3.5 text-base text-on-surface focus:ring-4 focus:ring-primary-container shadow-inner" placeholder="Enter your email" type="email" required />
                    <button type="submit" class="bg-surface-container-lowest text-primary px-8 py-3.5 rounded-full font-semibold text-base hover:bg-gray-100 transition-all shadow-lg active:scale-95 whitespace-nowrap btn-animated"><i class="fa-solid fa-paper-plane me-2"></i> Join Now</button>
                </form>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <?php require __DIR__ . '/../partials/footer_home.php'; ?>

    <!-- FAB for quick order -->
    <div class="fixed bottom-6 right-0 left-0 z-50 md:hidden pointer-events-none flex justify-end px-6 max-w-full mx-auto">
        <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty')); ?>
        <a href="<?= url('?route=cart') ?>" class="bg-primary text-white rounded-full shadow-2xl active:scale-95 transition-all flex items-center justify-center w-14 h-14 pointer-events-auto relative">
            <i class="fa-solid fa-cart-shopping text-xl"></i>
            <span id="mobileCartBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center border-2 border-primary <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
        </a>
    </div>

    <!-- jQuery & Bootstrap 5 JS Bundle -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- AOS Animations JS -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize AOS Animations
            AOS.init({
                duration: 800,
                easing: 'ease-in-out',
                once: true,
                mirror: false
            });

            const navbar = document.querySelector('header');
            window.addEventListener('scroll', () => {
                if (window.scrollY > 50) {
                    navbar.classList.add('py-2', 'shadow-md');
                    navbar.classList.remove('py-base');
                } else {
                    navbar.classList.remove('py-2', 'shadow-md');
                    navbar.classList.add('py-base');
                }
            });

            const mobileMenuBtn = document.getElementById('mobileMenuBtn');
            const mobileMenu = document.getElementById('mobileMenu');
            const mobileMenuIcon = document.getElementById('mobileMenuIcon');
            if (mobileMenuBtn && mobileMenu && mobileMenuIcon) {
                mobileMenuBtn.addEventListener('click', () => {
                    const isHidden = mobileMenu.classList.contains('hidden');
                    if (isHidden) {
                        mobileMenu.classList.remove('hidden');
                        mobileMenuIcon.textContent = 'close';
                        mobileMenuIcon.classList.add('rotate-90');
                    } else {
                        mobileMenu.classList.add('hidden');
                        mobileMenuIcon.textContent = 'menu';
                        mobileMenuIcon.classList.remove('rotate-90');
                    }
                });
            }
        });
    </script>
</body>

</html>