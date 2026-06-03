<?php
$title = "Our Story | Stacey Halu Halu - Sweet Efficiency";
?>
<!DOCTYPE html>
<html class="light" lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title><?= htmlspecialchars($title) ?></title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <!-- AOS Animations CSS -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "display-lg-mobile": ["36px", {"lineHeight": "1.2", "fontWeight": "700"}],
                        "display-lg": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "1", "fontWeight": "500"}],
                        "label-md": ["14px", {"lineHeight": "1", "letterSpacing": "0.05em", "fontWeight": "600"}]
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .parallax-bg {
            background-attachment: fixed;
            background-position: center;
            background-repeat: no-repeat;
            background-size: cover;
        }
        .text-shadow-premium {
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="bg-background text-on-surface overflow-x-hidden selection:bg-primary-fixed-dim">

<!-- Header -->
<?php require __DIR__ . '/../partials/header_home.php'; ?>

<main class="min-h-screen">
    <!-- Hero Section: Narrative Header -->
    <section class="relative h-[650px] flex items-center overflow-hidden">
        <div class="absolute inset-0 z-0">
            <img alt="Artisanal Halo-halo Background" class="w-full h-full object-cover grayscale-[10%] brightness-[0.4]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBTdmQpdvAyUAA8Hvu8_4nNJd8aSzDk5p2Z5vAl5NVblnquCzvEkA6B-5tQc63liH79Mml5wfS2tIaDm_aGJC2oA67k1pBHhY8Y4GWLBHF6YjXhGbhiv7jlyJoLecv0p3O8ANKQSChpY9huXTPXlBZ42Emw0aN2oAayIz264GihYEDD97p0XIAbuztcnzpt1dBlG20pz2B8HsS016Up63SB9F2QwJIZbTCtiT_Yt9o56HAtKmQZDj5yO0YztuEDHd3GBwWw-rBdw1s"/>
        </div>
        <div class="relative z-10 max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop text-white text-center md:text-left">
            <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg mb-md leading-tight max-w-3xl text-shadow-premium" data-aos="fade-up">
                The Story of Artisanal Philippine Delights
            </h1>
            <p class="font-body-lg text-body-lg max-w-2xl opacity-90 mx-auto md:mx-0 text-shadow-premium" data-aos="fade-up" data-aos-delay="100">
                Beyond the shaved ice lies a legacy of flavor, layering generations of tradition into every single cup.
            </p>
        </div>
    </section>

    <!-- Our Story Section -->
    <section class="py-xl bg-surface-container-lowest">
        <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop grid grid-cols-1 md:grid-cols-2 gap-xl items-center">
            <div class="order-2 md:order-1" data-aos="fade-right">
                <span class="text-primary font-label-md uppercase tracking-widest mb-xs block">Heritage & Inspiration</span>
                <h2 class="font-headline-lg text-headline-lg-mobile md:text-headline-lg mb-md text-on-background">A Tradition Reimagined</h2>
                <div class="space-y-md text-on-surface-variant font-body-md leading-relaxed">
                    <p class="text-lg">
                        <?= nl2br(htmlspecialchars($content['about_text'] ?? 'Stacey Halu Halu was born from a singular obsession: to elevate the humble street-side Halo-halo into a culinary masterpiece. What began in a small family kitchen in Manila has evolved into a celebration of the Philippine archipelago\'s finest ingredients.')) ?>
                    </p>
                    <p>
                        We source our Ube directly from local farmers in Bohol, our Mangoes from the sun-drenched groves of Guimaras, and our Coconuts from the coastlines of Quezon. Every element is selected with the precision of a jeweler and the heart of a storyteller.
                    </p>
                </div>
            </div>
            <div class="order-1 md:order-2 relative" data-aos="fade-left">
                <div class="aspect-[4/5] rounded-xl overflow-hidden shadow-[0_12px_30px_rgba(182,14,61,0.12)] transform md:rotate-2">
                    <img alt="Heritage Ingredients" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDueU1_jQ3LEZ6fSM_SBKKdUgTsopE0IhZpeLdxpd519uKFZuav9TkjA6f8uDZA_TWPCgfNO4iztV8MkHu84xLnX2lJ7F0tSFqq96KvPWHL01QpZgQ-qbTyVfh4j-qU02nQ9pgQqf2g5CDKOY4SAlkXM9f7xcTVFPYWAVTibGv1Iw4s8-cNo_OxtmOJxzJbURcRCApkPETgervM_nIAZ2Ec1fksAhP7u6vqVouECkUXcsaC1vuIPpxcqICP-M3FSVcdvJvtQ7uhoo8"/>
                </div>
            </div>
        </div>
    </section>

    <!-- The Art of Layering: Bento Grid -->
    <section class="py-xl bg-surface">
        <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
            <div class="text-center mb-xl" data-aos="fade-up">
                <h2 class="font-display-lg text-display-lg-mobile md:text-headline-lg text-primary">The Art of Layering</h2>
                <p class="font-body-lg text-on-surface-variant max-w-2xl mx-auto mt-sm">Our signature 12-layer process isn't just about ingredients; it's a structural symphony designed for the perfect spoonful.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                <!-- Layer Card 1 -->
                <div class="md:col-span-8 bg-surface-container shadow-[0_4px_20px_rgba(0,0,0,0.03)] rounded-xl p-lg flex flex-col md:flex-row gap-md items-center border border-white/20" data-aos="fade-up" data-aos-delay="100">
                    <div class="flex-1">
                        <h3 class="font-headline-md mb-xs text-primary">01. The Foundation</h3>
                        <p class="text-on-surface-variant">We begin with our house-made sweetened beans and gelatin, infused with pandan leaves for a subtle, earthy aroma that anchors the dessert.</p>
                    </div>
                    <div class="w-full md:w-1/2 aspect-video rounded-lg overflow-hidden shadow-sm">
                        <img alt="Ingredients Detail" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBkZZG8u0W72XEZym4BD2mog0Qf8hkESS95MIfEO0GwP1Nc8uKenigprUsi0FBIThagbzMIM7XlsJLOUdT6W6gArVXc9HEmXYkoUpnbsHCfYjUfDd7LKOajpSMx9HHOX6QqM3JyGNwWoKl_MDrcD-tnTp_1aw4SnwJwF9acdDbJVM9fKgMj6n-TScGdR2TfCsHSVKJNf_hjV1Lq2CGCCuO1doay3D5kBUnwIGLPG5d1QvE2VdgZ6wZBT0BeJj3T3igr0iRZ3WiINK0"/>
                    </div>
                </div>
                <!-- Layer Card 2 (Vertical) -->
                <div class="md:col-span-4 bg-primary-fixed text-on-primary-fixed shadow-[0_4px_20px_rgba(0,0,0,0.03)] rounded-xl p-lg flex flex-col justify-between" data-aos="fade-up" data-aos-delay="200">
                    <div>
                        <h3 class="font-headline-md mb-xs text-primary">02. Snow-Milk</h3>
                        <p class="opacity-90 text-on-primary-fixed-variant">Our ice is shaved to a micro-fluff consistency and immediately saturated with a proprietary blend of three types of premium milk.</p>
                    </div>
                    <div class="mt-lg">
                        <span class="material-symbols-outlined text-[64px] text-primary/40 animate-pulse">ac_unit</span>
                    </div>
                </div>
                <!-- Layer Card 3 -->
                <div class="md:col-span-4 bg-surface-container shadow-[0_4px_20px_rgba(0,0,0,0.03)] rounded-xl p-lg flex flex-col justify-between border border-white/20" data-aos="fade-up" data-aos-delay="300">
                    <div>
                        <h3 class="font-headline-md mb-xs text-secondary"><i class="fa-solid fa-award me-1"></i>03. The Gold</h3>
                        <p class="text-on-surface-variant">Velvety Leche Flan, steamed for hours to achieve a premium texture that melts before it hits your tongue.</p>
                    </div>
                    <div class="h-32 bg-white/40 rounded-lg flex items-center justify-center border border-dashed border-primary/20 mt-4">
                        <span class="font-display-lg text-primary/50 text-[18px] tracking-widest font-black uppercase">Leche Flan</span>
                    </div>
                </div>
                <!-- Layer Card 4 -->
                <div class="md:col-span-8 bg-surface-container shadow-[0_4px_20px_rgba(0,0,0,0.03)] rounded-xl p-lg overflow-hidden relative group border border-white/20" data-aos="fade-up" data-aos-delay="400">
                    <div class="relative z-10 max-w-md">
                        <h3 class="font-headline-md mb-xs text-primary">04. The Crown</h3>
                        <p class="text-on-surface-variant">A generous scoop of our signature Ube Halaya, hand-stirred for six hours to ensure unparalleled depth of flavor.</p>
                    </div>
                    <div class="absolute right-0 bottom-0 w-1/2 h-full hidden md:block transform translate-x-10 translate-y-10 group-hover:translate-x-0 group-hover:translate-y-0 transition-transform duration-500">
                        <img alt="Ube Scoop" class="w-full h-full object-cover rounded-tl-xl" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAHAKU0LeesX7pTGal9WSJB9YBpkLbGcy3sBrfpkglUlSvv_tJOK0UV49g_vC3n02nRllhIRhCp74a1nOx_r_3CGobQsVy5OV3KebaWaTLn-Jh9-2nIr7G0ytKn4UWmL8bMOASe9egDOSmQVYSo5iCqLDuSzuExD7F8gNdFK27u7AkPMS-4i9slWRAUVnOZgZqNFnedU4_YY3e6TBKXLdA5PpvfiYr-S0DslX2kadnNeR09CrVefCMTMXrUmOb12GjnWpTlagy6MPI"/>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Meet Stacey Section -->
    <section class="py-xl bg-surface-container-low overflow-hidden">
        <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
            <div class="flex flex-col lg:flex-row items-center gap-xl">
                <div class="lg:w-1/2 relative">
                    <!-- Decorative Shapes -->
                    <div class="absolute -top-10 -left-10 w-40 h-40 bg-primary/10 rounded-full blur-3xl"></div>
                    <div class="absolute -bottom-10 -right-10 w-60 h-60 bg-secondary/10 rounded-full blur-3xl"></div>
                    <div class="relative z-10 border-[16px] border-white shadow-xl rounded-xl overflow-hidden aspect-square">
                        <img alt="Stacey at Work" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB7XOTafwS3acdXg1C3eMgjRVIb6REakh_p3WYkjp-4-ikA4-QvkAl0JATz17n8gtHMKRJ5lr1Nk0XA_uH7XpkZSMnD94tGOvbhl66hLGEkIWVFPyalt0bqknC0nd9ry9EcxuavEEKsSZVBjGX6OYwJyidERClnOoz4ci3Tdku_wn-0nYkuMTkAOFlhAIvgW0y6oN8L3aYGeLgiQ8iIUXVK5oryswyZmDr4EwBXPAHrHjla1IT_AwViGB1EXyyfar4NuIxNPh_weFw"/>
                    </div>
                </div>
                <div class="lg:w-1/2">
                    <h2 class="font-display-lg text-display-lg-mobile md:text-headline-lg mb-md text-primary">Meet Stacey</h2>
                    <h4 class="font-headline-md text-on-background mb-sm">The Craft Behind the Chill</h4>
                    <p class="text-on-surface-variant font-body-lg mb-md leading-relaxed">
                        "Halo-halo is more than just a mix; it's a narrative of Philippine history. Every layer represents a different influence, and my job is to make sure those influences sing together in harmony."
                    </p>
                    <p class="text-on-surface-variant mb-lg">
                        Stacey has spent over a decade perfecting the "Parfait Progress"—our specialized assembly line that ensures every customer receives a dessert that looks exactly like a work of art. Her commitment to artisanal techniques in a high-velocity world is what sets Stacey Halu Halu apart.
                    </p>
                    <div class="flex gap-md">
                        <div class="text-center">
                            <div class="font-display-lg text-primary text-[36px]">12+</div>
                            <div class="font-label-sm text-on-surface-variant uppercase text-[11px] tracking-wider mt-1">Local Suppliers</div>
                        </div>
                        <div class="h-12 w-[1px] bg-outline-variant"></div>
                        <div class="text-center">
                            <div class="font-display-lg text-primary text-[36px]">6hr</div>
                            <div class="font-label-sm text-on-surface-variant uppercase text-[11px] tracking-wider mt-1">Ube Prep</div>
                        </div>
                        <div class="h-12 w-[1px] bg-outline-variant"></div>
                        <div class="text-center">
                            <div class="font-display-lg text-primary text-[36px]">100%</div>
                            <div class="font-label-sm text-on-surface-variant uppercase text-[11px] tracking-wider mt-1">Handcrafted</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-xl text-center bg-primary text-white">
        <div class="max-w-4xl mx-auto px-margin-mobile">
            <h2 class="font-display-lg text-display-lg-mobile md:text-display-lg mb-md text-shadow-premium">Ready to experience the layers?</h2>
            <p class="font-body-lg mb-lg opacity-90 max-w-2xl mx-auto">Visit any of our flagship locations or order online for a taste of the extraordinary.</p>
            <div class="flex flex-col sm:flex-row gap-md justify-center max-w-md mx-auto">
                <a href="<?= url('?route=branches') ?>" class="bg-white text-primary px-xl py-md rounded-pill font-headline-md shadow-md hover:shadow-lg transition-all active:scale-95 block text-center font-bold no-underline">Find a Location</a>
                <a href="<?= url('?route=products') ?>" class="border-2 border-white text-white px-xl py-md rounded-pill font-headline-md hover:bg-white/10 transition-all active:scale-95 block text-center font-bold no-underline">View the Menu</a>
            </div>
        </div>
    </section>
</main>

<!-- Footer -->
<?php require __DIR__ . '/../partials/footer_home.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Mobile navigation toggle
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    const mobileMenuIcon = document.getElementById('mobileMenuIcon');

    if (mobileMenuBtn && mobileMenu) {
        mobileMenuBtn.addEventListener('click', () => {
            const isOpen = !mobileMenu.classList.contains('hidden');
            if (isOpen) {
                mobileMenu.classList.add('hidden');
                mobileMenuIcon.textContent = 'menu';
                mobileMenuIcon.style.transform = 'rotate(0deg)';
            } else {
                mobileMenu.classList.remove('hidden');
                mobileMenuIcon.textContent = 'close';
                mobileMenuIcon.style.transform = 'rotate(90deg)';
            }
        });
    }

    // Interactive mouse parallax glow movement
    document.addEventListener('mousemove', (e) => {
        const blobs = document.querySelectorAll('.blur-3xl');
        const x = e.clientX / window.innerWidth;
        const y = e.clientY / window.innerHeight;
        
        blobs.forEach((blob, index) => {
            const speed = (index + 1) * 20;
            blob.style.transform = `translate(${x * speed}px, ${y * speed}px)`;
        });
    });
</script>
<!-- AOS Animations JS -->
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    });
</script>
</body>
</html>
