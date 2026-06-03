<?php
$title = "Our Branches | Stacey Halu Halu - Sweet Efficiency";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet"/>
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
        .glass-effect {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
        }
        .location-card:hover .map-overlay {
            transform: scale(1.06);
        }
        .map-container {
            overflow: hidden;
            position: relative;
        }
        .map-overlay {
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body class="bg-background text-on-surface overflow-x-hidden selection:bg-primary-fixed-dim">

<!-- Header -->
<?php require __DIR__ . '/../partials/header_home.php'; ?>

<main class="min-h-screen">
    <!-- Hero Section -->
    <section class="relative pt-xl pb-lg px-margin-mobile md:px-margin-desktop max-w-7xl mx-auto text-center">
        <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-md" data-aos="fade-up">
            Visit Our Studios
        </h1>
        <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
            Step into a world of vibrant layers and artisanal Philippine delights. Find the closest Stacey Halu Halu studio and indulge in our sweet efficiency.
        </p>
    </section>

    <!-- Locations Grid -->
    <section class="px-margin-mobile md:px-margin-desktop max-w-7xl mx-auto pb-xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
            <!-- Location Card 1: CBD -->
            <article class="location-card group bg-surface-container-lowest rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-all duration-300 hover:shadow-[0_12px_30px_rgba(182,14,61,0.12)] flex flex-col" data-aos="fade-up" data-aos-delay="200">
                <div class="map-container h-48 md:h-56">
                    <img alt="CBD Flagship Location" class="map-overlay w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB9jb7nm-Alxg1Sx1AW7MHzqLN6QW2KvSqMfFnNmMp2B_bUl0kZSzQLVYjo5QjbzHpHajlfN8Bo_PJ5XvAj9Al8W1_19hQXRAfaDZsi-ppuxLwfAaz9OK-M3ly9f8-qcIY_ljP41eZHLbHWP9vOLpamNondSORb2NuNvl0hxLQlNQG23icgPI-Rgkicnb-1Bj7LEH3IvixbLpZDS-clo3BLpBV-0A0x22zYjmoPgTJBZpHiYvbxNLFNCUb2u6RcTtLIIDxavOXzQIg"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/20 to-transparent"></div>
                    <div class="absolute top-4 right-4 bg-primary text-white px-3 py-1 rounded-full text-label-sm font-label-sm shadow-sm"><i class="fa-solid fa-crown me-1 text-[10px]"></i>Flagship</div>
                </div>
                <div class="p-md flex flex-col flex-grow">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-xs">Central Business District</h3>
                    <div class="flex items-start gap-2 text-on-surface-variant mb-sm">
                        <span class="material-symbols-outlined text-primary" data-icon="location_on">location_on</span>
                        <span class="font-body-md text-body-md">123 Ayala Avenue, Makati City, Metro Manila</span>
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant mb-md">
                        <span class="material-symbols-outlined text-primary" data-icon="schedule">schedule</span>
                        <span class="font-body-md text-body-md">10 AM - 10 PM Daily</span>
                    </div>
                    <div class="mt-auto">
                        <a href="https://maps.google.com/?q=Ayala+Avenue+Makati" target="_blank" class="w-full border-2 border-primary text-primary hover:bg-primary hover:text-white rounded-full py-sm px-md transition-all duration-200 flex items-center justify-center gap-2 font-label-md text-label-md text-center">
                            <span class="material-symbols-outlined" data-icon="directions">directions</span>
                            Get Directions
                        </a>
                    </div>
                </div>
            </article>

            <!-- Location Card 2: Greenbelt -->
            <article class="location-card group bg-surface-container-lowest rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-all duration-300 hover:shadow-[0_12px_30px_rgba(182,14,61,0.12)] flex flex-col" data-aos="fade-up" data-aos-delay="300">
                <div class="map-container h-48 md:h-56">
                    <img alt="Makati Greenbelt Location" class="map-overlay w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB56ibq2OLtRmJL4yHDuAEX8XiGGBXQtq9KBFihosgXtCWKfIq4IuHft_xvs6WhwFyxEtI24-pMrDbxJuvT-YQHAJOdum41UIOn4oPs49R93ekN0-QUYnHZQCakIPs-mOohFCdusbpWcOQuZNPLZe-9WDFNVA32E3FM65YOqTEjB6aHvFZTHUBMTSJaWuDU_gHA-molGdAu-fvjdnqAz02k4gEFI9-wXLB-wiVuSyWGzoJL26zY8j0bbYQEGiYZeob43HlU2G2BK-s"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/20 to-transparent"></div>
                    <div class="absolute top-4 right-4 bg-secondary text-white px-3 py-1 rounded-full text-label-sm font-label-sm shadow-sm"><i class="fa-solid fa-leaf me-1 text-[10px]"></i>Al Fresco</div>
                </div>
                <div class="p-md flex flex-col flex-grow">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-xs">Makati Greenbelt</h3>
                    <div class="flex items-start gap-2 text-on-surface-variant mb-sm">
                        <span class="material-symbols-outlined text-primary" data-icon="location_on">location_on</span>
                        <span class="font-body-md text-body-md">L1 Greenbelt 5, Legaspi Village, Makati</span>
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant mb-md">
                        <span class="material-symbols-outlined text-primary" data-icon="schedule">schedule</span>
                        <span class="font-body-md text-body-md">10 AM - 10 PM Daily</span>
                    </div>
                    <div class="mt-auto">
                        <a href="https://maps.google.com/?q=Greenbelt+5+Makati" target="_blank" class="w-full border-2 border-primary text-primary hover:bg-primary hover:text-white rounded-full py-sm px-md transition-all duration-200 flex items-center justify-center gap-2 font-label-md text-label-md text-center">
                            <span class="material-symbols-outlined" data-icon="directions">directions</span>
                            Get Directions
                        </a>
                    </div>
                </div>
            </article>

            <!-- Location Card 3: QC North -->
            <article class="location-card group bg-surface-container-lowest rounded-xl overflow-hidden shadow-[0_4px_20px_rgba(0,0,0,0.05)] transition-all duration-300 hover:shadow-[0_12px_30px_rgba(182,14,61,0.12)] flex flex-col" data-aos="fade-up" data-aos-delay="400">
                <div class="map-container h-48 md:h-56">
                    <img alt="Quezon City North Location" class="map-overlay w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuC2o5Jv4ETugTj7EjDr7XvVK1YINRxhlZFF96d4k7n6x8fJI6hM3YPej--Kv-G-i9goAwncjzHX42il2J9kLFhUbj9KiBjqegkSciHL2NYKw2Jixf5o6V03BdVcXiJmFQodXJL5pJKhY49attKns4xZ_vrjzBYuPHFcn7qHfaA6s2rz5CVDKtmBmvKUowp4IaKAEdehuXQ4VZ8bPBFzv7o4cfqN-sNxrBmxWts_X2njR_XGd4jSFybkYS62I-6vLxtPYqaFgBJbRMs"/>
                    <div class="absolute inset-0 bg-gradient-to-t from-primary/20 to-transparent"></div>
                    <div class="absolute top-4 right-4 bg-tertiary-container text-white px-3 py-1 rounded-full text-label-sm font-label-sm shadow-sm"><i class="fa-solid fa-store me-1 text-[10px]"></i>Express Lounge</div>
                </div>
                <div class="p-md flex flex-col flex-grow">
                    <h3 class="font-headline-md text-headline-md text-on-surface mb-xs">Quezon City North</h3>
                    <div class="flex items-start gap-2 text-on-surface-variant mb-sm">
                        <span class="material-symbols-outlined text-primary" data-icon="location_on">location_on</span>
                        <span class="font-body-md text-body-md">SM North EDSA, North Avenue, Quezon City</span>
                    </div>
                    <div class="flex items-center gap-2 text-on-surface-variant mb-md">
                        <span class="material-symbols-outlined text-primary" data-icon="schedule">schedule</span>
                        <span class="font-body-md text-body-md">10 AM - 10 PM Daily</span>
                    </div>
                    <div class="mt-auto">
                        <a href="https://maps.google.com/?q=SM+North+EDSA+Quezon+City" target="_blank" class="w-full border-2 border-primary text-primary hover:bg-primary hover:text-white rounded-full py-sm px-md transition-all duration-200 flex items-center justify-center gap-2 font-label-md text-label-md text-center">
                            <span class="material-symbols-outlined" data-icon="directions">directions</span>
                            Get Directions
                        </a>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <!-- Newsletter / Contact Section -->
    <section class="bg-primary-fixed py-xl" data-aos="fade-up">
        <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop text-center">
            <h2 class="font-headline-lg text-headline-lg text-on-primary-fixed mb-md">Can't find us?</h2>
            <p class="font-body-md text-body-md text-on-primary-fixed-variant mb-lg max-w-xl mx-auto">
                We're rapidly expanding our footprint to bring artisanal treats to every corner. Subscribe to get notified when we open in your neighborhood.
            </p>
            <form class="flex flex-col sm:flex-row gap-sm justify-center items-center max-w-md mx-auto" onsubmit="event.preventDefault(); alert('Subscribed!')">
                <input class="w-full rounded-full border-none bg-surface-container-lowest px-md py-sm focus:ring-2 focus:ring-primary text-body-md shadow-sm" placeholder="Enter your email" type="email" required/>
                <button type="submit" class="w-full sm:w-auto bg-primary hover:bg-primary-container text-white px-lg py-sm rounded-full font-label-md text-label-md active:scale-95 transition-all shadow-sm shrink-0">
                    Notify Me
                </button>
            </form>
        </div>
    </section>
</main>

<!-- Footer -->
<?php require __DIR__ . '/../partials/footer_home.php'; ?>

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

    // Micro-interaction for direction buttons
    document.querySelectorAll('a').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if(this.innerText.includes('Directions')) {
                const ripple = document.createElement('div');
                ripple.style.position = 'absolute';
                ripple.style.width = '10px';
                ripple.style.height = '10px';
                ripple.style.background = 'rgba(255,255,255,0.3)';
                ripple.style.borderRadius = '50%';
                ripple.style.pointerEvents = 'none';
                this.style.position = 'relative';
                this.style.overflow = 'hidden';
                this.appendChild(ripple);
                
                const rect = this.getBoundingClientRect();
                ripple.style.left = (e.clientX - rect.left) + 'px';
                ripple.style.top = (e.clientY - rect.top) + 'px';
                
                ripple.animate([
                    { transform: 'scale(1)', opacity: 1 },
                    { transform: 'scale(40)', opacity: 0 }
                ], {
                    duration: 600,
                    easing: 'ease-out'
                }).onfinish = () => ripple.remove();
            }
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
