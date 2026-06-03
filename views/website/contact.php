<?php
$title = "Contact Us | Stacey Halu Halu - Sweet Efficiency";
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
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
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
            vertical-align: middle;
        }

        .bg-studio-overlay {
            background-image: linear-gradient(rgba(248, 249, 250, 0.92), rgba(248, 249, 250, 0.96)), url(https://lh3.googleusercontent.com/aida-public/AB6AXuBNh9UEm324SGDph3EbpNdudOvS24OKSz5ueYUKvcNAYOeHVeXo8K_2YvyoFmYujnROJnmIwt9PKnrbF73ATJOCIQl4zNDgpEbk-bRTicWINwcTThSWcPScwtxTNL7UlRFxTFvNcSAvf9GnlnpE9NUH-C9Qn6RJYB0aWetpA1d3AI8gioIFBfUJwFdoNgJJ9vMX1wIfdr2NsybpMUDHTmDZbuYvhqCJlUf9KxYTo-khXkaxU1S3WrbuZV2s15vyAlFoanmdaeEV2Vk);
            background-size: cover;
            background-position: center;
        }

        .form-input-shadow:focus {
            box-shadow: 0 0 0 4px rgba(182, 14, 61, 0.1);
        }
    </style>
</head>

<body class="bg-background text-on-surface overflow-x-hidden selection:bg-primary-fixed-dim">

    <!-- Header -->
    <?php require __DIR__ . '/../partials/header_home.php'; ?>

    <!-- Flash Messages -->
    <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop mt-4">
        <?php $flash = get_flash();
        if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show shadow-sm rounded-4 border-0 d-flex align-items-center gap-3 py-3 px-4" role="alert">
                <?php if ($flash['type'] === 'success'): ?>
                    <i class="fa-solid fa-circle-check fs-4 text-success"></i>
                <?php else: ?>
                    <i class="fa-solid fa-circle-exclamation fs-4 text-danger"></i>
                <?php endif; ?>
                <div class="font-body-md"><?= $flash['message'] ?></div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    </div>

    <main class="min-h-screen">
        <!-- Hero Section -->
        <section class="relative py-xl bg-studio-overlay">
            <div class="max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop text-center py-8">
                <h1 class="font-display-lg text-display-lg-mobile md:text-display-lg text-primary mb-base" data-aos="fade-up">Let's Connect</h1>
                <p class="font-body-lg text-body-lg text-on-surface-variant max-w-2xl mx-auto" data-aos="fade-up" data-aos-delay="100">
                    Whether you're craving a custom dessert tower or want to discuss a sweet partnership, we're here to listen.
                </p>
            </div>
        </section>

        <!-- Main Content: Bento Grid Layout -->
        <section class="py-xl max-w-7xl mx-auto px-margin-mobile md:px-margin-desktop">
            <div class="grid grid-cols-1 md:grid-cols-12 gap-gutter">
                <!-- Get in Touch Form (7 Columns) -->
                <div class="md:col-span-7 bg-surface-container-lowest rounded-xl p-lg shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant/30" data-aos="fade-right" data-aos-delay="200">
                    <div class="mb-lg border-b border-surface-variant pb-base">
                        <h2 class="font-headline-lg text-headline-lg text-on-surface">Get in Touch</h2>
                        <p class="font-body-md text-on-surface-variant mt-xs">Send us a message and we'll get back to you within 24 hours.</p>
                    </div>
                    <form class="space-y-md" id="contactForm" action="<?= url('?route=contact') ?>" method="POST">
                        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-md">
                            <div class="space-y-xs">
                                <label class="font-label-md text-label-md text-on-surface-variant ml-xs" for="contactName">Your Name</label>
                                <input class="w-full bg-surface-container-low border-none rounded-lg p-sm font-body-md text-on-surface focus:ring-2 focus:ring-primary form-input-shadow transition-all"
                                    id="contactName" name="name" placeholder="Juan Dela Cruz" type="text" required
                                    value="<?= htmlspecialchars($_SESSION['user']['name'] ?? '') ?>" />
                            </div>
                            <div class="space-y-xs">
                                <label class="font-label-md text-label-md text-on-surface-variant ml-xs" for="contactEmail">Email Address</label>
                                <input class="w-full bg-surface-container-low border-none rounded-lg p-sm font-body-md text-on-surface focus:ring-2 focus:ring-primary form-input-shadow transition-all"
                                    id="contactEmail" name="email" placeholder="juan@example.ph" type="email" required
                                    value="<?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?>" />
                            </div>
                        </div>
                        <div class="space-y-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant ml-xs" for="contactSubject">Subject</label>
                            <select class="w-full bg-surface-container-low border-none rounded-lg p-sm font-body-md text-on-surface focus:ring-2 focus:ring-primary form-input-shadow transition-all appearance-none"
                                id="contactSubject" name="subject">
                                <option value="General Inquiry">General Inquiry</option>
                                <option value="Catering Request">Catering Request</option>
                                <option value="Franchising">Franchising</option>
                                <option value="Career Opportunities">Career Opportunities</option>
                                <option value="Feedback">Feedback</option>
                            </select>
                        </div>
                        <div class="space-y-xs">
                            <label class="font-label-md text-label-md text-on-surface-variant ml-xs" for="contactMessage">Message</label>
                            <textarea class="w-full bg-surface-container-low border-none rounded-lg p-sm font-body-md text-on-surface focus:ring-2 focus:ring-primary form-input-shadow transition-all"
                                id="contactMessage" name="message" placeholder="Tell us how we can help you..." rows="5" required></textarea>
                        </div>

                        <button class="w-full md:w-auto bg-primary text-white px-xl py-3 rounded-full font-label-md shadow-md hover:bg-primary-container active:scale-95 transition-all flex items-center justify-center gap-base font-bold"
                            type="submit" id="submitBtn">
                            <span>Send Message</span>
                            <span class="material-symbols-outlined text-[20px]">send</span>
                        </button>
                    </form>
                </div>

                <!-- Contact Info & Image (5 Columns) -->
                <div class="md:col-span-5 flex flex-col gap-gutter">
                    <!-- General Inquiries Card -->
                    <div class="bg-surface-container-lowest rounded-xl p-lg shadow-[0_4px_20px_rgba(0,0,0,0.03)] border border-surface-variant/30 flex-1" data-aos="fade-left" data-aos-delay="200">
                        <div class="mb-lg border-b border-surface-variant pb-base">
                            <h2 class="font-headline-lg text-headline-lg text-on-surface">General Inquiries</h2>
                        </div>
                        <div class="space-y-lg">
                            <div class="flex items-start gap-md">
                                <div class="w-12 h-12 rounded-full bg-primary-fixed flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-primary">mail</span>
                                </div>
                                <div>
                                    <h3 class="font-label-md text-on-surface font-bold">Email Us</h3>
                                    <p class="font-body-md text-on-surface-variant"><?= htmlspecialchars($content['contact_email'] ?? 'hello@staceyhaluhalu.com') ?></p>
                                    <p class="font-body-md text-on-surface-variant">events@staceyhaluhalu.com</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-md">
                                <div class="w-12 h-12 rounded-full bg-secondary-fixed flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-secondary">call</span>
                                </div>
                                <div>
                                    <h3 class="font-label-md text-on-surface font-bold">Call Us</h3>
                                    <p class="font-body-md text-on-surface-variant"><?= htmlspecialchars($content['contact_phone'] ?? '+63 912 345 6789') ?></p>
                                    <p class="font-body-md text-on-surface-variant">Mon - Sun, 9am - 9pm</p>
                                </div>
                            </div>
                            <div class="flex items-start gap-md">
                                <div class="w-12 h-12 rounded-full bg-tertiary-fixed flex items-center justify-center shrink-0">
                                    <span class="material-symbols-outlined text-tertiary">location_on</span>
                                </div>
                                <div>
                                    <h3 class="font-label-md text-on-surface font-bold">Visit the Studio</h3>
                                    <p class="font-body-md text-on-surface-variant"><?= htmlspecialchars($content['contact_address'] ?? '123 Sweet Tooth Blvd, Sugar City, Philippines') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Decorative Image Card -->
                    <div class="relative h-64 md:h-full overflow-hidden rounded-xl group min-h-[250px]" data-aos="fade-left" data-aos-delay="300">
                        <img class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDB7xUCXaIwwZeDCOsohNLCBM02HTssEDr9IemN7HV04_sf10t-pLzloe4u0u_PJgoPgEVti_5INe3068PK74fQivkejMzA2jLQ5RULzwe3CpWAZGDGODLDRxoQMpxMJXQql_n-uALpklP2abAOS0dXacoi_v3LRCi-LUsk480rarvT0eV1KGHeAkKxEu9h8JXgfOVugJvV0C1zFHXsCahJ8oF15jCGC8Uwqmrh_N9tY_vQAXUtkewXV5H1oraV6t4xeJhB3QAK6vU" />
                        <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                        <div class="absolute bottom-md left-md text-white">
                            <span class="font-label-md uppercase tracking-widest text-primary-fixed-dim">Join the Club</span>
                            <h4 class="font-headline-md text-white mb-0 text-shadow-premium">Sweet rewards await.</h4>
                        </div>
                    </div>
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

        // Micro-interactions for form submission feedback
        const contactForm = document.getElementById('contactForm');
        if (contactForm) {
            contactForm.addEventListener('submit', (e) => {
                const btn = document.getElementById('submitBtn');
                if (btn) {
                    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Sending...</span>`;
                    btn.classList.add('opacity-75');
                }
            });
        }

        // Hover effect on the Parfait-inspired contact icons
        document.querySelectorAll('.shrink-0').forEach(icon => {
            icon.addEventListener('mouseenter', () => {
                icon.classList.add('scale-110', 'rotate-6', 'transition-all', 'duration-300');
            });
            icon.addEventListener('mouseleave', () => {
                icon.classList.remove('scale-110', 'rotate-6');
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