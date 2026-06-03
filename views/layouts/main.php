<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Stacey Halu Halu - Premium Shaved Ice Desserts' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts (Inter & Outfit) -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <!-- Tailwind CSS with Forms and Container Queries -->
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
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
</head>
<body class="public-layout">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark premium-navbar sticky-top shadow-sm">
    <div class="container">
        <a class="navbar-brand fw-bold d-flex align-items-center gap-2" href="<?= url('?route=home') ?>">
            <span class="brand-icon"><i class="fa-solid fa-ice-cream text-accent"></i></span>
            <span class="brand-text d-none d-md-inline">Stacey Halu Halu</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0 fw-medium">
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['route'] ?? '') === 'home' || empty($_GET['route']) ? 'active' : '' ?>" href="<?= url('?route=home') ?>">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['route'] ?? '') === 'products' ? 'active' : '' ?>" href="<?= url('?route=products') ?>">Our Menu</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['route'] ?? '') === 'branches' ? 'active' : '' ?>" href="<?= url('?route=branches') ?>">Branches</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['route'] ?? '') === 'about' ? 'active' : '' ?>" href="<?= url('?route=about') ?>">About Us</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($_GET['route'] ?? '') === 'contact' ? 'active' : '' ?>" href="<?= url('?route=contact') ?>">Contact</a>
                </li>
            </ul>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('?route=products') ?>" class="btn btn-accent rounded-pill px-4 fw-semibold shadow-sm">
                    <i class="fa-solid fa-cart-shopping me-2"></i>Order Now
                </a>
                <?php if (!empty($_SESSION['user'])): ?>
                    <?php if (User::has_permission($_SESSION['user']['id'], 'view_dashboard')): ?>
                        <a href="<?= url('?route=dashboard') ?>" class="btn btn-outline-light rounded-pill px-4 fw-semibold">Dashboard</a>
                    <?php elseif (User::has_permission($_SESSION['user']['id'], 'access_pos')): ?>
                        <a href="<?= url('?route=pos') ?>" class="btn btn-outline-light rounded-pill px-4 fw-semibold">POS System</a>
                    <?php else: ?>
                        <div class="dropdown">
                            <button class="btn btn-outline-light rounded-pill px-4 fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-user me-2"></i><?= htmlspecialchars($_SESSION['user']['username'] ?? explode('@', $_SESSION['user']['email'])[0] ?? $_SESSION['user']['name']) ?>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end dropdown-glass shadow">
                                <li><a class="dropdown-item text-danger fw-medium" href="<?= url('?route=logout') ?>" onclick="openLogoutModal(event)"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</a></li>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?= url('?route=login') ?>" class="btn btn-outline-light rounded-pill px-4 fw-semibold">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Flash Messages -->
<?php require_once __DIR__ . '/../partials/toast.php'; ?>


<!-- Main Content -->
<main class="content-wrapper">
    <?= $content ?? '' ?>
</main>

<!-- Footer -->
<?php require __DIR__ . '/../partials/footer_home.php'; ?>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= url('assets/js/main.js') ?>"></script>

<!-- Global Logout Modal -->
<?php require_once __DIR__ . '/../partials/logout_modal.php'; ?>

</body>
</html>
