<?php
$title = "Create Account | Stacey Halu Halu - Sweet Efficiency";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
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
                        "2xl": "1rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "margin-desktop": "32px",
                        "xs": "4px",
                        "gutter": "24px",
                        "base": "8px",
                        "margin-mobile": "16px",
                        "xl": "64px",
                        "lg": "40px",
                        "sm": "12px",
                        "md": "24px"
                    },
                    "fontFamily": {
                        "display-lg": ["Montserrat"],
                        "headline-md": ["Montserrat"],
                        "headline-lg": ["Montserrat"],
                        "body-md": ["Inter"],
                        "label-sm": ["Inter"],
                        "label-md": ["Inter"],
                        "body-lg": ["Inter"]
                    },
                    "fontSize": {
                        "display-lg": ["48px", {
                            "lineHeight": "1.2",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "700"
                        }],
                        "headline-md": ["24px", {
                            "lineHeight": "1.4",
                            "fontWeight": "600"
                        }],
                        "headline-lg": ["32px", {
                            "lineHeight": "1.3",
                            "fontWeight": "600"
                        }],
                        "body-md": ["16px", {
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
                        }],
                        "body-lg": ["18px", {
                            "lineHeight": "1.6",
                            "fontWeight": "400"
                        }]
                    }
                }
            }
        }
    </script>
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }

        .glass-effect {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }

        .form-input-shadow:focus {
            box-shadow: 0 0 0 4px rgba(182, 14, 61, 0.15);
        }
    </style>
</head>

<body class="bg-background text-on-background min-h-screen flex items-center justify-center p-0 md:p-6 lg:p-margin-desktop overflow-x-hidden selection:bg-primary-fixed-dim">
    <?php require_once __DIR__ . '/../partials/toast.php'; ?>

    <!-- Split Screen Container -->
    <main class="relative w-full max-w-7xl min-h-screen md:min-h-[800px] md:max-h-[900px] bg-surface-container-lowest flex flex-col md:flex-row shadow-[0_4px_25px_rgba(0,0,0,0.06)] rounded-none md:rounded-2xl overflow-hidden">
        <!-- Left Side: Immersive Visual (Hidden on mobile, cinematic display on desktop) -->
        <section class="hidden md:flex md:w-1/2 relative bg-stone-950 overflow-hidden">
            <!-- Replaced pink mix-blend-overlay with clean dark overlay to highlight food photography beautifully -->
            <img alt="Layered Halo-Halo" class="absolute inset-0 w-full h-full object-cover opacity-75 transition-transform duration-[15s] ease-out scale-110 contrast-105 brightness-[0.70]" id="registerBgImg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCUYXqc60HBOKZVMjcrca5qCmhIk9PN5FXAiPw5ZnqyVIbDcGUqzsAL_IjY_Oc2jCKyf3a7FsRM5Ob3rGJ6xBS3LW3Oa9TiK-pgMFIrP93jaQX3VmAww5_o6rTQYdGfM55IEMQKgI8WTWY1cnx04jkh0XWZ39fALtGI_mWQ686buwmgYvvO1E2gTgOydgifoo-CFPVrno_o1CiDbi-cBgohopWmMoT0H86AyWZpd4wfAGCpLNL_kx5dGHOB4eHsE8o-7lgxJn-aYNk" />

            <div class="relative z-10 flex flex-col justify-between p-xl w-full h-full bg-gradient-to-t from-black/95 via-black/30 to-black/50">
                <!-- Brand Header -->
                <div class="flex items-center gap-xs bg-white/10 backdrop-blur-md px-md py-sm rounded-full border border-white/20 shadow-sm self-start">
                    <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">stars</span>
                    <span class="font-label-md text-white tracking-widest uppercase text-[12px] font-bold">Join the Club</span>
                </div>

                <!-- Perks Highlights Bento Grid -->
                <div class="space-y-6 mb-lg">
                    <h1 class="font-display-lg text-display-lg text-white text-shadow-premium leading-tight">Join the Halu Halu Club</h1>

                    <div class="space-y-4">
                        <!-- Perk 1 -->
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 transition-all hover:bg-white/10">
                            <span class="material-symbols-outlined text-primary text-2xl bg-primary-fixed/20 p-2.5 rounded-xl" style="font-variation-settings: 'FILL' 1;">water_drop</span>
                            <div>
                                <h3 class="text-white font-headline-md text-base font-bold mb-1">Flavor Drops</h3>
                                <p class="text-white/70 font-body-md text-sm leading-normal">Be the first to taste our limited-edition monthly seasonal layers.</p>
                            </div>
                        </div>

                        <!-- Perk 2 -->
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 transition-all hover:bg-white/10">
                            <span class="material-symbols-outlined text-primary text-2xl bg-primary-fixed/20 p-2.5 rounded-xl" style="font-variation-settings: 'FILL' 1;">track_changes</span>
                            <div>
                                <h3 class="text-white font-headline-md text-base font-bold mb-1">Live Tracking</h3>
                                <p class="text-white/70 font-body-md text-sm leading-normal">Watch your parfait layers come together in real-time with Parfait Progress.</p>
                            </div>
                        </div>

                        <!-- Perk 3 -->
                        <div class="flex items-start gap-4 p-4 rounded-2xl bg-white/5 backdrop-blur-md border border-white/10 transition-all hover:bg-white/10">
                            <span class="material-symbols-outlined text-primary text-2xl bg-primary-fixed/20 p-2.5 rounded-xl" style="font-variation-settings: 'FILL' 1;">stars</span>
                            <div>
                                <h3 class="text-white font-headline-md text-base font-bold mb-1">Member Perks</h3>
                                <p class="text-white/70 font-body-md text-sm leading-normal">Earn points on every scoop and unlock exclusive boutique rewards.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Right Side: Register Form (Responsive heights and sizes on mobile devices) -->
        <section class="w-full md:w-1/2 flex flex-col justify-between px-margin-mobile md:px-xl py-8 md:py-xl bg-surface-container-lowest min-h-screen md:min-h-0">
            <!-- Top Nav / Brand Header -->
            <div class="w-full flex justify-between items-center mb-lg">
                <a href="<?= url('?route=home') ?>" class="font-headline-md text-headline-md font-bold text-primary flex items-center gap-2 text-decoration-none">
                    <span><i class="fa-solid fa-ice-cream"></i></span>
                    <span class="text-xl">Stacey Halu Halu</span>
                </a>
            </div>

            <div class="w-full max-w-md mx-auto my-auto py-4">
                <div class="mb-lg">
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Create Your Account</h2>
                    <p class="font-body-md text-on-surface-variant">Experience sweet efficiency in every layer.</p>
                </div>

                <!-- Dynamic Flash Alert Block -->
                <?php $flash = get_flash();
                if ($flash): ?>
                    <div class="alert alert-<?= $flash['type'] === 'danger' ? 'danger' : 'success' ?> shadow-sm rounded-xl border-0 py-3.5 px-4 mb-md flex items-center gap-3" role="alert">
                        <?php if ($flash['type'] === 'success'): ?>
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                        <?php else: ?>
                            <i class="fa-solid fa-circle-exclamation text-danger fs-5"></i>
                        <?php endif; ?>
                        <div class="font-body-md text-sm text-on-surface leading-tight"><?= htmlspecialchars($flash['message']) ?></div>
                    </div>
                <?php endif; ?>

                <form class="space-y-md" action="<?= url('?route=register') ?>" method="POST" id="registerForm">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Username Field -->
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface-variant block ml-1" for="username">Username</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">account_circle</span>
                            <input class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200"
                                id="username" name="username" placeholder="Choose a username" required type="text" autofocus />
                        </div>
                    </div>

                    <!-- Password Fields Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-md">
                        <!-- Password Field -->
                        <div class="space-y-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant block ml-1" for="password">Password</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">lock</span>
                                <input class="w-full pl-12 pr-12 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200"
                                    id="password" name="password" placeholder="••••••••" required type="password" />
                                <button class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" onclick="togglePassword('password', 'pw-toggle-icon-1')" type="button">
                                    <span class="material-symbols-outlined" id="pw-toggle-icon-1" style="font-size: 20px;">visibility</span>
                                </button>
                            </div>
                        </div>

                        <!-- Confirm Password Field -->
                        <div class="space-y-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant block ml-1" for="confirm_password">Confirm Password</label>
                            <div class="relative">
                                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">lock</span>
                                <input class="w-full pl-12 pr-12 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200"
                                    id="confirm_password" name="confirm_password" placeholder="••••••••" required type="password" />
                                <button class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" onclick="togglePassword('confirm_password', 'pw-toggle-icon-2')" type="button">
                                    <span class="material-symbols-outlined" id="pw-toggle-icon-2" style="font-size: 20px;">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="flex items-center gap-base px-1">
                        <input class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" id="terms" type="checkbox" required />
                        <label class="font-label-md text-label-md text-on-surface-variant cursor-pointer select-none leading-normal" for="terms">
                            I agree to the <a href="#" class="text-primary hover:underline font-bold text-decoration-none">Terms of Service</a> and <a href="#" class="text-primary hover:underline font-bold text-decoration-none">Privacy Policy</a>
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button class="w-full py-4 bg-primary text-white font-label-md text-label-md uppercase tracking-widest rounded-full shadow-[0_12px_30px_rgba(182,14,61,0.15)] hover:scale-[1.01] active:scale-95 transition-all duration-200 mt-md font-bold flex items-center justify-center gap-2"
                        type="submit" id="submitBtn">
                        <span>Create Account</span>
                        <span class="material-symbols-outlined text-[18px]">person_add</span>
                    </button>

                    <!-- Centered Back to Website Link -->
                    <div class="text-center mt-4">
                        <a href="<?= url('?route=home') ?>" class="inline-flex items-center gap-2 text-on-surface-variant hover:text-primary transition-all font-label-md font-bold text-decoration-none py-2 px-4 rounded-full hover:bg-surface-container-low">
                            <span class="material-symbols-outlined text-lg">arrow_back</span>
                            <span>Back to Website</span>
                        </a>
                    </div>
                </form>

                <!-- Login Footer -->
                <div class="mt-xl text-center">
                    <p class="font-body-md text-body-md text-on-surface-variant">
                        Already a member?
                        <a class="font-label-md text-label-md text-primary ml-1 hover:underline underline-offset-4 font-bold text-decoration-none" href="<?= url('?route=customer_login') ?>">Log in here</a>
                    </p>
                </div>
            </div>

            <!-- Accessibility/Legal Footer for Login -->
            <div class="mt-auto pt-lg w-full flex flex-col sm:flex-row justify-between items-center border-t border-outline-variant text-label-sm text-outline gap-2">
                <span>© 2024 Stacey Halu Halu. Sweet Efficiency in every layer.</span>
                <div class="flex gap-md">
                    <a class="hover:text-primary transition-colors text-decoration-none" href="#">Privacy Policy</a>
                    <a class="hover:text-primary transition-colors text-decoration-none" href="#">Terms of Service</a>
                </div>
            </div>
        </section>
    </main>

    <!-- jQuery & Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const pwInput = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                icon.innerText = 'visibility_off';
            } else {
                pwInput.type = 'password';
                icon.innerText = 'visibility';
            }
        }

        // Interactive mouse move parallax movement
        document.addEventListener('mousemove', (e) => {
            const img = document.getElementById('registerBgImg');
            if (img && window.innerWidth > 768) {
                const moveX = (e.clientX - window.innerWidth / 2) / 100;
                const moveY = (e.clientY - window.innerHeight / 2) / 100;
                img.style.transform = `scale(1.1) translate(${moveX}px, ${moveY}px)`;
            }
        });

        // Form button loader feedback
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', () => {
                const btn = document.getElementById('submitBtn');
                if (btn) {
                    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Creating account...</span>`;
                    btn.classList.add('opacity-75');
                }
            });
        }
    </script>
</body>

</html>