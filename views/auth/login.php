<?php
$title = "Admin Portal | Stacey Halu Halu - Sweet Efficiency";
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

    <!-- Split Screen Container -->
    <main class="relative w-full max-w-7xl min-h-screen md:min-h-[800px] md:max-h-[900px] bg-surface-container-lowest flex flex-col md:flex-row shadow-[0_4px_25px_rgba(0,0,0,0.06)] rounded-none md:rounded-2xl overflow-hidden">
        <!-- Left Side: Immersive Visual (Hidden on mobile, beautiful cinematic display on desktop) -->
        <section class="hidden md:flex md:w-1/2 relative bg-stone-950 overflow-hidden">
            <!-- Replaced pink mix-blend-overlay with clean dark overlay to highlight food photography beautifully -->
            <img alt="Layered Halo-Halo" class="absolute inset-0 w-full h-full object-cover opacity-85 transition-transform duration-[15s] ease-out scale-110 contrast-105 brightness-[0.75]" id="loginBgImg" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCUYXqc60HBOKZVMjcrca5qCmhIk9PN5FXAiPw5ZnqyVIbDcGUqzsAL_IjY_Oc2jCKyf3a7FsRM5Ob3rGJ6xBS3LW3Oa9TiK-pgMFIrP93jaQX3VmAww5_o6rTQYdGfM55IEMQKgI8WTWY1cnx04jkh0XWZ39fALtGI_mWQ686buwmgYvvO1E2gTgOydgifoo-CFPVrno_o1CiDbi-cBgohopWmMoT0H86AyWZpd4wfAGCpLNL_kx5dGHOB4eHsE8o-7lgxJn-aYNk" />
            <div class="relative z-10 flex flex-col justify-end p-xl w-full bg-gradient-to-t from-black/90 via-black/40 to-transparent h-full">
                <h1 class="font-display-lg text-display-lg text-white mb-md text-shadow-premium leading-tight">Sweet Efficiency in every layer.</h1>
                <p class="font-body-lg text-body-lg text-surface-variant opacity-90 max-w-md">Experience the perfect blend of tradition and modern operational excellence. Manage your sweet journey with precision.</p>
                <div class="mt-lg flex gap-sm items-center">
                    <div class="h-1.5 w-12 bg-primary rounded-full"></div>
                    <div class="h-1.5 w-4 bg-white/40 rounded-full"></div>
                    <div class="h-1.5 w-4 bg-white/40 rounded-full"></div>
                </div>
            </div>
            <!-- Floating Aesthetic Element -->
            <div class="absolute top-margin-desktop left-margin-desktop">
                <div class="flex items-center gap-xs bg-black/40 backdrop-blur-md px-md py-sm rounded-full border border-white/10 shadow-sm">
                    <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">restaurant</span>
                    <span class="font-label-md text-white tracking-widest uppercase text-[12px] font-bold">Premium Dessert Network</span>
                </div>
            </div>
        </section>

        <!-- Right Side: Login Form (Fully responsive height and spacing on mobile) -->
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
                    <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Admin Portal</h2>
                    <p class="font-body-md text-on-surface-variant">Log in to manage operations, view statistics, and configure settings.</p>
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

                <form class="space-y-md" action="<?= url('?route=login') ?>" method="POST" id="loginForm">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                    <!-- Username / Email Field -->
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface-variant block ml-1" for="username">Username</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">account_circle</span>
                            <input class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200"
                                id="username" name="username" placeholder="admin" required type="text" autofocus />
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="space-y-xs">
                        <div class="flex justify-between items-center px-1">
                            <label class="font-label-md text-label-md text-on-surface-variant" for="password">Password</label>
                            <a class="font-label-sm text-label-sm text-primary hover:underline transition-all text-decoration-none font-semibold" href="#">Forgot Password?</a>
                        </div>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">lock</span>
                            <input class="w-full pl-12 pr-12 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200"
                                id="password" name="password" placeholder="••••••••" required type="password" />
                            <button class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" onclick="togglePassword()" type="button">
                                <span class="material-symbols-outlined" id="pw-toggle-icon" style="font-size: 20px;">visibility</span>
                            </button>
                        </div>
                    </div>

                    <!-- Access Role Grid Selectors -->
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface-variant block ml-1">Access Role</label>
                        <div class="grid grid-cols-2 gap-sm">
                            <!-- Admin Option -->
                            <div>
                                <input type="radio" name="role" value="Admin" id="role-admin" checked class="sr-only peer" />
                                <label for="role-admin" class="flex items-center justify-center gap-2 p-3 bg-surface-container-low border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 rounded-xl cursor-pointer hover:bg-surface-container-high transition-all font-label-md text-on-surface font-bold text-sm select-none">
                                    <span class="material-symbols-outlined text-outline peer-checked:text-primary text-xl" style="font-variation-settings: 'FILL' 1;">shield_person</span>
                                    <span>Admin</span>
                                </label>
                            </div>
                            <!-- Cashier Option -->
                            <div>
                                <input type="radio" name="role" value="Cashier" id="role-cashier" class="sr-only peer" />
                                <label for="role-cashier" class="flex items-center justify-center gap-2 p-3 bg-surface-container-low border-2 border-transparent peer-checked:border-primary peer-checked:bg-primary/5 rounded-xl cursor-pointer hover:bg-surface-container-high transition-all font-label-md text-on-surface font-bold text-sm select-none">
                                    <span class="material-symbols-outlined text-outline peer-checked:text-primary text-xl" style="font-variation-settings: 'FILL' 1;">storefront</span>
                                    <span>Cashier</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Branch Selection Dropdown -->
                    <div class="space-y-xs">
                        <label class="font-label-md text-label-md text-on-surface-variant block ml-1" for="branch">Active Branch</label>
                        <div class="relative">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">location_on</span>
                            <select class="w-full pl-12 pr-10 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200 appearance-none cursor-pointer"
                                style="background-image: none;" id="branch" name="branch" required>
                                <?php if (!empty($db_branches)): ?>
                                    <?php foreach ($db_branches as $b): ?>
                                        <option value="<?= htmlspecialchars($b['name']) ?>"><?= htmlspecialchars($b['name']) ?></option>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <option value="Quezon City (Flagship)">Quezon City (Flagship)</option>
                                <?php endif; ?>
                            </select>
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-outline pointer-events-none" style="font-size: 20px;">expand_more</span>
                        </div>
                    </div>
                    <!-- Remember Me -->
                    <div class="flex items-center gap-base px-1">
                        <input class="w-5 h-5 rounded border-outline-variant text-primary focus:ring-primary/20 cursor-pointer" id="remember" type="checkbox" />
                        <label class="font-label-md text-label-md text-on-surface-variant cursor-pointer select-none" for="remember">Stay logged in for 30 days</label>
                    </div>

                    <!-- Login Button -->
                    <button class="w-full py-4 bg-primary text-white font-label-md text-label-md uppercase tracking-widest rounded-full shadow-[0_12px_30px_rgba(182,14,61,0.15)] hover:scale-[1.01] active:scale-95 transition-all duration-200 mt-md font-bold flex items-center justify-center gap-2"
                        type="submit" id="submitBtn">
                        <span>Login to Admin Suite</span>
                        <span class="material-symbols-outlined text-[18px]">login</span>
                    </button>

                    <!-- Centered Back to Website Link -->
                    <div class="text-center mt-4">
                        <a href="<?= url('?route=home') ?>" class="inline-flex items-center gap-2 text-on-surface-variant hover:text-primary transition-all font-label-md font-bold text-decoration-none py-2 px-4 rounded-full hover:bg-surface-container-low">
                            <span class="material-symbols-outlined text-lg">arrow_back</span>
                            <span>Back to Website</span>
                        </a>
                    </div>
                </form>
            </div>

            <!-- Accessibility/Legal Footer for Login -->
            <div class="mt-auto pt-lg w-full flex flex-col sm:flex-row justify-between items-center border-t border-outline-variant text-label-sm text-outline gap-2">
                <span>© 2026 Stacey Halu Halu</span>
                <div class="flex gap-md">
                    <a class="hover:text-primary transition-colors text-decoration-none" href="#">Privacy</a>
                    <a class="hover:text-primary transition-colors text-decoration-none" href="#">Terms</a>
                </div>
            </div>
        </section>
    </main>

    <!-- jQuery & Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const pwInput = document.getElementById('password');
            const icon = document.getElementById('pw-toggle-icon');
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
            const img = document.getElementById('loginBgImg');
            if (img && window.innerWidth > 768) {
                const moveX = (e.clientX - window.innerWidth / 2) / 100;
                const moveY = (e.clientY - window.innerHeight / 2) / 100;
                img.style.transform = `scale(1.1) translate(${moveX}px, ${moveY}px)`;
            }
        });

        // Form button loader feedback
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', () => {
                const btn = document.getElementById('submitBtn');
                if (btn) {
                    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Logging in...</span>`;
                    btn.classList.add('opacity-75');
                }
            });
        }

        // Auto-fill username/password based on role selection
        $(document).ready(function() {
            function updateCredentials() {
                const role = $('input[name="role"]:checked').val();
                if (role === 'Admin') {
                    $('#username').val('admin');
                    $('#password').val('admin123');
                } else if (role === 'Cashier') {
                    $('#username').val('cashier');
                    $('#password').val('cashier123');
                }
            }

            // Set initial credentials if they are empty
            if ($('#username').val() === '' || $('#username').val() === 'admin' || $('#username').val() === 'cashier') {
                updateCredentials();
            }

            // Update when user changes the active role option
            $('input[name="role"]').on('change', function() {
                updateCredentials();
            });
        });
    </script>
</body>

</html>