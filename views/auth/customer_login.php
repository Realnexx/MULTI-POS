<?php
$title = "Club Member Login | Stacey Halu Halu - Sweet Efficiency";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
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
                        "display-lg": ["48px", {"lineHeight": "1.2", "letterSpacing": "-0.02em", "fontWeight": "700"}],
                        "headline-md": ["24px", {"lineHeight": "1.4", "fontWeight": "600"}],
                        "headline-lg": ["32px", {"lineHeight": "1.3", "fontWeight": "600"}],
                        "body-md": ["16px", {"lineHeight": "1.6", "fontWeight": "400"}],
                        "label-sm": ["12px", {"lineHeight": "1", "fontWeight": "500"}],
                        "label-md": ["14px", {"lineHeight": "1", "letterSpacing": "0.05em", "fontWeight": "600"}],
                        "body-lg": ["18px", {"lineHeight": "1.6", "fontWeight": "400"}]
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
        <img alt="Premium Shaved Ice Dessert" class="absolute inset-0 w-full h-full object-cover opacity-85 transition-transform duration-[15s] ease-out scale-110 contrast-105 brightness-[0.75]" src="https://lh3.googleusercontent.com/aida-public/AB6AXuIt5CSeJJ2kkqxn51omKtMRHh5m0SsqDvn1rngdOAgi9jOAaTvxWiwREtIC9cfUcVYT0V4cZzIWvlBGBBR7Baiy9XGNIrZOFUL5gshsR2Z931tne_i9jroq4EFVRvLDiCgjRbPRzYjMuQPqOyEcCIx7i_6WkP2gjfxIfbRLqUAjvZwEQaztLwojwqJr0DbFMPvnA4grryxznoLc1A-qO-bt5eYNPcFuoOryquvcVakMQr1FBTGJuKw2rcPv_LrVyksZ1FDNWSeOqI"/>
        <div class="relative z-10 flex flex-col justify-end p-xl w-full bg-gradient-to-t from-black/90 via-black/40 to-transparent h-full">
            <h1 class="font-display-lg text-display-lg text-white mb-md text-shadow-premium leading-tight">Sweetness Awaits Your Arrival.</h1>
            <p class="font-body-lg text-body-lg text-surface-variant opacity-90 max-w-md">Log in to customize your signature layers, view nearest store coordinates, select favorite treats, and unlock VIP privileges.</p>
            <div class="mt-lg flex gap-sm items-center">
                <div class="h-1.5 w-12 bg-primary rounded-full"></div>
                <div class="h-1.5 w-4 bg-white/40 rounded-full"></div>
                <div class="h-1.5 w-4 bg-white/40 rounded-full"></div>
            </div>
        </div>
        <!-- Floating Brand Element -->
        <div class="absolute top-margin-desktop left-margin-desktop">
            <div class="flex items-center gap-xs bg-black/40 backdrop-blur-md px-md py-sm rounded-full border border-white/10 shadow-sm">
                <span class="material-symbols-outlined text-white" style="font-variation-settings: 'FILL' 1;">favorite</span>
                <span class="font-label-md text-white tracking-widest uppercase text-[12px] font-bold">Stacey Halu Halu Club</span>
            </div>
        </div>
    </section>

    <!-- Right Side: Customer Login Form -->
    <section class="w-full md:w-1/2 flex flex-col justify-between px-margin-mobile md:px-xl py-8 md:py-xl bg-surface-container-lowest min-h-screen md:min-h-0">
        <!-- Brand Header -->
        <div class="w-full flex justify-between items-center mb-lg">
            <a href="<?= url('?route=home') ?>" class="font-headline-md text-headline-md font-bold text-primary flex items-center gap-2 text-decoration-none">
                <span><i class="fa-solid fa-ice-cream"></i></span>
                <span class="text-xl">Stacey Halu Halu</span>
            </a>
            <a href="<?= url('?route=login') ?>" class="font-label-sm text-label-sm text-outline hover:text-primary border border-outline/30 px-sm py-1.5 rounded-full transition-all text-decoration-none font-bold">
                Admin Portal
            </a>
        </div>

        <div class="w-full max-w-md mx-auto my-auto py-4">
            <div class="mb-lg">
                <h2 class="font-headline-lg text-headline-lg text-on-surface mb-xs">Welcome Back!</h2>
                <p class="font-body-md text-on-surface-variant">Log in to access your custom signature profile, select closest local branches, and order with sweet efficiency.</p>
            </div>

            <form class="space-y-md" action="<?= url('?route=customer_login') ?>" method="POST" id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                <!-- Username / Email Field -->
                <div class="space-y-xs">
                    <label class="font-label-md text-label-md text-on-surface-variant block ml-1" for="username">Username or Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline" style="font-size: 20px;">account_circle</span>
                        <input class="w-full pl-12 pr-4 py-3.5 bg-surface-container-low border-transparent focus:border-primary focus:ring-0 rounded-xl font-body-md text-on-surface form-input-shadow transition-all duration-200" 
                               id="username" name="username" placeholder="Enter username or email" required type="text" autofocus/>
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
                               id="password" name="password" placeholder="••••••••" required type="password"/>
                        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-outline hover:text-on-surface transition-colors" onclick="togglePassword()" type="button">
                            <span class="material-symbols-outlined" id="pw-toggle-icon" style="font-size: 20px;">visibility</span>
                        </button>
                    </div>
                </div>

                <!-- Complete Button -->
                <button class="w-full bg-primary hover:bg-primary-container text-white py-3.5 rounded-xl font-label-md text-label-md font-bold transition-all shadow-lg shadow-primary/20 hover:shadow-primary/30 active:scale-[0.98] duration-150 flex items-center justify-center gap-xs" 
                        type="submit">
                    <span>Log In to Stacey Halu Halu</span>
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
            </form>

            <div class="mt-xl text-center">
                <p class="font-body-md text-on-surface-variant">
                    Not a member of the Club yet? 
                    <a href="<?= url('?route=register') ?>" class="text-primary hover:underline font-bold transition-all ml-1 text-decoration-none">
                        Join the Club
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer terms info -->
        <div class="w-full text-center mt-lg border-t border-outline/10 pt-md">
            <p class="font-label-sm text-[11px] text-outline">&copy; <?= date('Y') ?> Stacey Halu Halu. Enjoyed with love and sweet efficiency.</p>
        </div>
    </section>
</main>

<script>
    function togglePassword() {
        const pwField = document.getElementById("password");
        const toggleIcon = document.getElementById("pw-toggle-icon");
        if (pwField && toggleIcon) {
            if (pwField.type === "password") {
                pwField.type = "text";
                toggleIcon.textContent = "visibility_off";
            } else {
                pwField.type = "password";
                toggleIcon.textContent = "visibility";
            }
        }
    }
</script>
</body>
</html>
