<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? 'Admin Dashboard - Stacey Halu Halu' ?></title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts: Inter, Outfit, Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;600;700;800&family=Montserrat:wght@600;700;800&display=swap" rel="stylesheet">
    <!-- Material Symbols Outlined -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <!-- DataTables Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>">
    <!-- AOS Animations -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <!-- jQuery (Moved to head for view compatibility) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        [data-bs-toggle="collapse"]:not(.collapsed) .dropdown-chevron {
            transform: rotate(180deg);
        }

        .submenu-link:hover {
            color: #ff4d6d !important;
            background-color: #ffedef !important;
        }

        /* Force visibility for sidebar collapse */
        .sidebar-dropdown .collapse.show {
            display: block !important;
            height: auto !important;
            visibility: visible !important;
            opacity: 1 !important;
        }

        .sidebar-link.active {
            background: #ffedef !important;
            color: #ff4d6d !important;
            border-right: 3px solid #ff4d6d;
        }

        .sidebar-link.active .sidebar-icon-wrap {
            background: #ff4d6d !important;
        }

        .sidebar-link.active .sidebar-icon-wrap .material-symbols-outlined {
            color: #ffffff !important;
        }

        /* PREMIUM SIDEBAR FIX */
        aside.premium-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 260px;
            z-index: 1050 !important;
            background: rgba(255, 255, 255, 0.98) !important;
            backdrop-filter: blur(20px);
            border-right: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .sidebar-brand-area {
            padding: 24px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.04);
        }

        .sidebar-category-label {
            margin-top: 15px;
            margin-bottom: 5px;
            font-size: 10px;
            font-weight: 800;
            color: #adb5bd;
            letter-spacing: 1.2px;
            padding-left: 15px !important;
        }

        .sidebar-link {
            margin: 4px 12px !important;
            border-radius: 12px !important;
            padding: 10px 14px !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
        }

        .sidebar-link:hover:not(.active) {
            background: rgba(255, 77, 109, 0.05) !important;
            transform: translateX(4px);
        }

        .sidebar-link.active {
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(255, 77, 109, 0.25) !important;
        }

        .dropdown-chevron {
            transition: transform 0.3s ease;
        }

        .sidebar-link:not(.collapsed) .dropdown-chevron {
            transform: rotate(180deg);
        }

        .sidebar-footer {
            background: rgba(248, 249, 250, 0.5);
            backdrop-filter: blur(5px);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            padding: 15px 12px !important;
        }

        .submenu-link {
            position: relative;
            margin-left: 15px !important;
            border-left: 1px solid #eee;
        }

        .submenu-link.active::before {
            content: '';
            position: absolute;
            left: -1px;
            top: 50%;
            transform: translateY(-50%);
            height: 15px;
            width: 2px;
            background: #ff4d6d;
        }

        /* Flip Timer Styles */
        .flip-timer {
            display: flex;
            gap: 2px;
            font-family: 'Inter', sans-serif;
            font-weight: 700;
            font-size: 14px;
            color: #333;
        }

        .flip-unit {
            background: #fff;
            border: 1px solid #eee;
            border-radius: 4px;
            padding: 0px 4px;
            min-width: 24px;
            text-align: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            position: relative;
            display: inline-block;
            transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .flip-unit::after {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            width: 100%;
            height: 1px;
            background: rgba(0, 0, 0, 0.05);
        }

        .flip-sep {
            font-size: 14px;
            color: #ff4d6d;
            align-self: center;
            animation: timerBlink 1s infinite;
            font-weight: bold;
        }

        @keyframes timerBlink {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.3;
            }
        }
    </style>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
</head>

<body class="admin-layout">

    <div class="d-flex" id="wrapper" style="overflow: visible;">
        <?php
        // Force refresh user session data from DB to ensure latest avatar/info
        if (isset($_SESSION['user']['id'])) {
            $refreshed_user = User::find_by_id($_SESSION['user']['id']);
            if ($refreshed_user) {
                $_SESSION['user']['name'] = $refreshed_user['name'];
                $_SESSION['user']['email'] = $refreshed_user['email'];
                $_SESSION['user']['avatar_url'] = $refreshed_user['avatar_url'] ?: $refreshed_user['image'] ?: null;
            }
        }

        // Fetch Store Settings for Sidebar
        $sidebar_settings = Setting::get_by_category('general');
        $sidebar_store_name = $sidebar_settings['store_name'] ?? 'Stacey Halo Halo';
        $sidebar_logo = $sidebar_settings['store_logo'] ?? 'assets/img/logo.png';

        // Handle Role-based Branch Display
        $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users'); // admin check
        $sidebar_branch_display = $is_admin ? 'General' : ($_SESSION['user']['branch'] ?? 'Main Branch');
        ?>

        <!-- ===================== SIDEBAR ===================== -->
        <aside class="premium-sidebar" id="sidebar-wrapper">

            <!-- Brand Area -->
            <div class="sidebar-brand-area d-flex justify-content-center p-0" style="height: 80px; border: none; background: transparent; overflow: visible;">
                <img src="<?= url($sidebar_logo) ?>" alt="Logo" class="sidebar-logo-img" style="width: 160px; height: 100px; object-fit: contain; transform: translateY(-10px);">
            </div>



            <!-- Navigation -->
            <nav class="sidebar-nav">
                <?php $route = $_GET['route'] ?? ''; ?>

                <!-- GROUP: OVERVIEW -->
                <div class="sidebar-category-label">Overview</div>
                <?php if (User::has_permission($_SESSION['user']['id'], 'view_dashboard')): ?>
                    <a href="<?= url('?route=dashboard') ?>" class="sidebar-link <?= $route === 'dashboard' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#e3f0ff"><span class="material-symbols-outlined" style="color:#1a73e8">speed</span></span>
                        Dashboard
                    </a>
                <?php endif; ?>
                <?php if (User::has_permission($_SESSION['user']['id'], 'view_reports')): ?>
                    <a href="<?= url('?route=admin/reports') ?>" class="sidebar-link <?= $route === 'admin/reports' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#e6f4f1"><span class="material-symbols-outlined" style="color:#00796b">analytics</span></span>
                        Performance Stats
                    </a>
                <?php endif; ?>

                <!-- GROUP: POINT OF SALE -->
                <div class="sidebar-category-label">Store Operations</div>
                <?php if (User::has_permission($_SESSION['user']['id'], 'access_pos')): ?>
                    <a href="<?= url('?route=pos') ?>" class="sidebar-link <?= $route === 'pos' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#f0e6ff"><span class="material-symbols-outlined" style="color:#7c3aed">point_of_sale</span></span>
                        POS Cashier
                    </a>
                    <a href="<?= url('?route=pos/history') ?>" class="sidebar-link <?= $route === 'pos/history' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#fff0e6"><span class="material-symbols-outlined" style="color:#ea580c">history</span></span>
                        History
                    </a>
                <?php endif; ?>

                <!-- GROUP: INVENTORY & FINANCE -->
                <div class="sidebar-category-label">Management</div>
                <?php if (User::has_permission($_SESSION['user']['id'], 'view_product') || User::has_permission($_SESSION['user']['id'], 'manage_inventory')): ?>
                    <a href="<?= url('?route=inventory/products') ?>" class="sidebar-link <?= $route === 'inventory/products' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#ffedef"><span class="material-symbols-outlined" style="color:#ff4d6d">inventory_2</span></span>
                        Stock & Inventory
                    </a>
                <?php endif; ?>

                <?php if (User::has_permission($_SESSION['user']['id'], 'manage_inventory')): ?>
                    <a href="<?= url('?route=inventory/logs') ?>" class="sidebar-link <?= $route === 'inventory/logs' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#fff7ed"><span class="material-symbols-outlined" style="color:#ea580c">history_edu</span></span>
                        Inventory Audit
                    </a>
                <?php endif; ?>

                <a href="<?= url('?route=admin/branches') ?>" class="sidebar-link <?= $route === 'admin/branches' ? 'active' : '' ?>">
                    <span class="sidebar-icon-wrap" style="background:#e8f5e9"><span class="material-symbols-outlined" style="color:#2e7d32">storefront</span></span>
                    Business Branches
                </a>

                <a href="<?= url('?route=admin/expenses') ?>" class="sidebar-link <?= $route === 'admin/expenses' ? 'active' : '' ?>">
                    <span class="sidebar-icon-wrap" style="background:#e1f5fe"><span class="material-symbols-outlined" style="color:#0288d1">account_balance_wallet</span></span>
                    Financial Ledger
                </a>

                <!-- GROUP: SYSTEM -->
                <div class="sidebar-category-label">Administration</div>
                <?php if (User::has_permission($_SESSION['user']['id'], 'manage_users') || User::has_permission($_SESSION['user']['id'], 'manage_roles')): ?>
                    <div class="sidebar-dropdown">
                        <a href="#usersSubmenu" data-bs-toggle="collapse" class="sidebar-link <?= ($route === 'admin/roles' || $route === 'admin/users') ? 'active' : 'collapsed' ?>" aria-expanded="<?= ($route === 'admin/roles' || $route === 'admin/users') ? 'true' : 'false' ?>">
                            <span class="sidebar-icon-wrap" style="background:#fff3e0"><span class="material-symbols-outlined" style="color:#e65100">manage_accounts</span></span>
                            Human Resources
                            <span class="material-symbols-outlined ms-auto dropdown-chevron" style="font-size:20px; color:#999; transition: transform 0.2s;">expand_more</span>
                        </a>
                        <div class="collapse <?= ($route === 'admin/roles' || $route === 'admin/users') ? 'show' : '' ?>" id="usersSubmenu">
                            <div class="ps-4 py-1">
                                <?php if (User::has_permission($_SESSION['user']['id'], 'manage_users')): ?>
                                    <a href="<?= url('?route=admin/users') ?>" class="submenu-link text-decoration-none <?= $route === 'admin/users' ? 'fw-semibold active' : '' ?>" style="font-size: 13px; color: <?= $route === 'admin/users' ? '#ff4d6d' : '#666' ?>; padding: 8px 12px; border-radius: 8px; transition: all 0.2s; background: <?= $route === 'admin/users' ? '#ffedef' : 'transparent' ?>; display: block; margin-bottom: 2px;">
                                        Team Members
                                    </a>
                                <?php endif; ?>
                                <?php if (User::has_permission($_SESSION['user']['id'], 'manage_roles')): ?>
                                    <a href="<?= url('?route=admin/roles') ?>" class="submenu-link text-decoration-none <?= $route === 'admin/roles' ? 'fw-semibold active' : '' ?>" style="font-size: 13px; color: <?= $route === 'admin/roles' ? '#ff4d6d' : '#666' ?>; padding: 8px 12px; border-radius: 8px; transition: all 0.2s; background: <?= $route === 'admin/roles' ? '#ffedef' : 'transparent' ?>; display: block; margin-bottom: 2px;">
                                        Access Tokens
                                    </a>
                                <?php endif; ?>
                                <a href="<?= url('?route=admin/activity_logs') ?>" class="submenu-link text-decoration-none <?= $route === 'admin/activity_logs' ? 'fw-semibold active' : '' ?>" style="font-size: 13px; color: <?= $route === 'admin/activity_logs' ? '#ff4d6d' : '#666' ?>; padding: 8px 12px; border-radius: 8px; transition: all 0.2s; background: <?= $route === 'admin/activity_logs' ? '#ffedef' : 'transparent' ?>; display: block;">
                                    Activity Logs
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (User::has_permission($_SESSION['user']['id'], 'manage_settings')): ?>
                    <a href="<?= url('?route=admin/settings') ?>" class="sidebar-link <?= $route === 'admin/settings' ? 'active' : '' ?>">
                        <span class="sidebar-icon-wrap" style="background:#f7f7f8"><span class="material-symbols-outlined" style="color:#555">settings</span></span>
                        System Settings
                    </a>
                <?php endif; ?>
                <a href="<?= url('?route=logout') ?>" class="sidebar-link" onclick="openLogoutModal(event)">
                    <span class="sidebar-icon-wrap" style="background:#fff0f3"><span class="material-symbols-outlined" style="color:#ff4d6d">logout</span></span>
                    Logout
                </a>
            </nav>



        </aside>
        <!-- ===================== END SIDEBAR ===================== -->

        <!-- Page Content Wrapper -->
        <div id="page-content-wrapper" class="flex-grow-1 w-100" style="margin-left: 260px;">

            <!-- Top Navbar (Fixed) - Enhanced -->
            <nav class="navbar py-0 px-0" id="admin-topbar"
                style="position:fixed;top:0;left:260px;right:0;z-index:999;
                    background:rgba(255,255,255,0.93);
                    backdrop-filter:blur(14px);-webkit-backdrop-filter:blur(14px);
                    border-bottom:1px solid rgba(0,0,0,0.06);
                    box-shadow:0 2px 16px rgba(0,0,0,0.05);
                    transition:left 0.3s cubic-bezier(0.16,1,0.3,1);">
                <div class="d-flex align-items-center w-100 px-4" style="height:58px;gap:12px;">

                    <!-- Hamburger Toggle -->
                    <button class="btn p-0 d-flex align-items-center justify-content-center flex-shrink-0" id="sidebarToggle"
                        style="width:36px;height:36px;border:1px solid #eee;border-radius:10px;background:#fafafa;color:#555;transition:all 0.2s;">
                        <i class="fa-solid fa-bars" style="font-size:14px;"></i>
                    </button>

                    <!-- Page Title + Branch -->
                    <div class="d-none d-md-flex flex-column justify-content-center ms-1" style="line-height:1.25;">
                        <span class="fw-bold text-dark" style="font-family:'Montserrat',sans-serif;font-size:15px;"><?= $title ?? 'Dashboard' ?></span>
                        <span style="font-size:11px;color:#aaa;font-family:'Inter',sans-serif;"><?= htmlspecialchars($sidebar_branch_display) ?></span>
                    </div>

                    <div class="flex-grow-1"></div>

                    <!-- Date Chip -->
                    <div class="d-none d-sm-flex align-items-center gap-3 px-3"
                        style="height:34px;background:#f7f7f8;border:1px solid #eee;border-radius:20px;font-size:12px;color:#555;font-family:'Inter',sans-serif;font-weight:500;white-space:nowrap;">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-regular fa-calendar" style="color:#ff4d6d;"></i>
                            <?= date('M d, Y') ?>
                        </div>
                        <div class="vr" style="height:14px;opacity:0.1;"></div>
                        <div class="flip-timer" id="flip-timer">
                            <div class="flip-unit" id="hr">00</div>
                            <div class="flip-sep">:</div>
                            <div class="flip-unit" id="min">00</div>
                            <div class="flip-sep">:</div>
                            <div class="flip-unit" id="sec">00</div>
                            <div class="ms-1" id="ampm" style="font-size: 10px; color: #ff4d6d; text-transform: uppercase;">PM</div>
                        </div>
                    </div>

                    <!-- View Website -->
                    <a href="<?= url('?route=home') ?>" target="_blank"
                        class="d-none d-lg-flex align-items-center gap-2 px-3 text-decoration-none fw-semibold"
                        style="height:34px;background:#ffedef;border:1px solid #ffccd5;border-radius:20px;font-size:12px;color:#ff4d6d;font-family:'Inter',sans-serif;transition:all 0.2s;white-space:nowrap;">
                        <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:10px;"></i>
                        Website
                    </a>

                    <!-- Notification Bell -->
                    <button class="btn p-0 d-flex align-items-center justify-content-center position-relative flex-shrink-0"
                        style="width:36px;height:36px;border:1px solid #eee;border-radius:10px;background:#fafafa;color:#555;">
                        <i class="fa-regular fa-bell" style="font-size:15px;"></i>
                        <span class="position-absolute" style="top:6px;right:7px;width:7px;height:7px;background:#ff4d6d;border-radius:50%;border:1.5px solid #fff;"></span>
                    </button>

                    <!-- User Dropdown -->
                    <div class="dropdown">
                        <button class="btn p-0 d-flex align-items-center gap-2 dropdown-toggle"
                            style="background:transparent;border:none;outline:none;box-shadow:none;"
                            type="button" data-bs-toggle="dropdown">
                            <div class="d-flex align-items-center justify-content-center fw-bold text-white flex-shrink-0"
                                style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#ff4d6d,#7209b7);font-size:13px;font-family:'Inter',sans-serif;box-shadow:0 3px 10px rgba(255,77,109,0.3);overflow:hidden;">
                                <?php $user_avatar_url = get_user_avatar($_SESSION['user']['avatar_url'] ?? '', $_SESSION['user']['name'] ?? 'U'); ?>
                                <img src="<?= $user_avatar_url ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                            </div>
                            <div class="d-none d-md-block text-start" style="line-height:1.2;">
                                <div style="font-size:13px;font-weight:600;color:#2b2d42;font-family:'Inter',sans-serif;"><?= htmlspecialchars(explode(' ', $_SESSION['user']['name'])[0]) ?></div>
                                <div style="font-size:10px;color:#aaa;font-family:'Inter',sans-serif;"><?= htmlspecialchars($_SESSION['user']['role'] ?? 'Management') ?></div>
                            </div>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end border-0 py-1 mt-2"
                            style="border-radius:14px;box-shadow:0 8px 30px rgba(0,0,0,0.12);min-width:210px;">
                            <li class="px-3 pt-2 pb-2" style="border-bottom:1px solid #f5f5f5;">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="d-flex align-items-center justify-content-center fw-bold text-white shadow-sm"
                                        style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#ff4d6d,#7209b7);font-size:14px;flex-shrink:0;overflow:hidden;">
                                        <img src="<?= get_user_avatar($_SESSION['user']['avatar_url'] ?? '', $_SESSION['user']['name'] ?? 'U') ?>" alt="Profile" style="width:100%;height:100%;object-fit:cover;">
                                    </div>
                                    <div>
                                        <div style="font-size:13px;font-weight:600;color:#2b2d42;font-family:'Inter',sans-serif;"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
                                        <div style="font-size:11px;color:#aaa;font-family:'Inter',sans-serif;"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></div>
                                    </div>
                                </div>
                            </li>
                            <li class="px-2 pt-1">
                                <a class="dropdown-item d-flex align-items-center gap-2 py-2 px-2"
                                    href="<?= url('?route=logout') ?>" onclick="openLogoutModal(event)"
                                    style="font-size:13px;color:#ff4d6d;font-family:'Inter',sans-serif;font-weight:500;border-radius:8px;">
                                    <i class="fa-solid fa-right-from-bracket"></i> Logout
                                </a>
                            </li>
                        </ul>
                    </div>

                </div>
            </nav>

            <!-- Main Content Area (offset for fixed navbar 58px) -->
            <main class="container-fluid px-4 py-3" style="margin-top:58px">

                <!-- Flash Messages -->
                <?php $flash = get_flash(); ?>
                <style>
                    /* Premium Toast Styling Redesigned */
                    .premium-toast {
                        position: fixed;
                        top: 24px;
                        right: 24px;
                        min-width: 320px;
                        max-width: 400px;
                        background: rgba(255, 255, 255, 0.95);
                        backdrop-filter: blur(10px);
                        color: #1a1b2e;
                        padding: 1rem 1.25rem;
                        border-radius: 1.25rem;
                        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
                        z-index: 9999;
                        display: flex;
                        align-items: center;
                        gap: 1rem;
                        transform: translateX(120%);
                        transition: all 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);
                        visibility: hidden;
                        border: 1px solid rgba(0, 0, 0, 0.05);
                        border-bottom: 2px solid transparent;
                    }

                    .premium-toast.show {
                        transform: translateX(0);
                        visibility: visible;
                    }

                    .premium-toast.success {
                        border-bottom-color: #10b981;
                    }

                    .premium-toast.warning {
                        border-bottom-color: #f59e0b;
                    }

                    .premium-toast.error {
                        border-bottom-color: #ef4444;
                    }

                    .premium-toast-icon {
                        width: 42px;
                        height: 42px;
                        border-radius: 14px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        flex-shrink: 0;
                        transition: all 0.3s;
                    }

                    .premium-toast-icon.success {
                        background: #10b98120;
                        color: #10b981;
                    }

                    .premium-toast-icon.warning {
                        background: #f59e0b20;
                        color: #f59e0b;
                    }

                    .premium-toast-icon.danger {
                        background: #ef444420;
                        color: #ef4444;
                    }

                    .premium-toast-content {
                        flex-grow: 1;
                    }

                    .premium-toast-content h6 {
                        margin: 0 0 2px 0;
                        font-size: 0.85rem;
                        font-weight: 800;
                        text-transform: uppercase;
                        letter-spacing: 0.8px;
                        color: #0f172a;
                    }

                    .premium-toast-content p {
                        margin: 0;
                        font-size: 0.85rem;
                        color: #64748b;
                        font-weight: 600;
                        line-height: 1.4;
                    }

                    .premium-toast-close {
                        background: none;
                        width: 28px;
                        height: 28px;
                        border-radius: 8px;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: #94a3b8;
                        border: none;
                        transition: all 0.2s;
                        cursor: pointer;
                    }

                    .premium-toast-close:hover {
                        background-color: #f1f5f9;
                        color: #0f172a;
                    }
                </style>
                <div class="premium-toast" id="globalToast">
                    <div class="premium-toast-icon" id="toastIconContainer">
                        <i class="fa-solid fa-check" id="toastIcon"></i>
                    </div>
                    <div class="premium-toast-content">
                        <h6 id="toastTitle">Success</h6>
                        <p id="toastMessage">Action completed successfully.</p>
                    </div>
                    <button type="button" class="premium-toast-close" onclick="hideToast()">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <script>
                    let toastTimer;

                    function showToast(message, type = 'success') {
                        const toast = document.getElementById('globalToast');
                        const title = document.getElementById('toastTitle');
                        const msg = document.getElementById('toastMessage');
                        const icon = document.getElementById('toastIcon');
                        const iconContainer = document.getElementById('toastIconContainer');

                        // Reset classes
                        toast.classList.remove('success', 'warning', 'error');
                        iconContainer.classList.remove('success', 'warning', 'danger');

                        // Map type
                        const typeMap = {
                            'success': {
                                title: 'Protocol Success',
                                icon: 'fa-check',
                                class: 'success'
                            },
                            'warning': {
                                title: 'Attention Needed',
                                icon: 'fa-triangle-exclamation',
                                class: 'warning'
                            },
                            'error': {
                                title: 'System Error',
                                icon: 'fa-circle-xmark',
                                class: 'error'
                            }
                        };
                        const config = typeMap[type] || typeMap['success'];

                        title.innerText = config.title;
                        msg.innerText = message;

                        toast.classList.add(config.class);
                        iconContainer.classList.add(type === 'error' ? 'danger' : config.class);
                        icon.className = 'fa-solid ' + config.icon;

                        toast.classList.add('show');

                        clearTimeout(toastTimer);
                        toastTimer = setTimeout(hideToast, 4500);
                    }

                    function hideToast() {
                        document.getElementById('globalToast').classList.remove('show');
                    }

                    // Handle legacy flash messages
                    <?php if ($flash): ?>
                        document.addEventListener("DOMContentLoaded", function() {
                            showToast("<?= addslashes($flash['message']) ?>", "<?= $flash['type'] ?>");
                        });
                    <?php endif; ?>
                </script>

                <?= $content ?? '' ?>
                <!-- Footer -->
                <footer class="mt-5 py-4 border-top" style="background: rgba(255,255,255,0.4); border-color: rgba(0,0,0,0.05) !important;">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 px-2">
                        <div class="d-flex align-items-center gap-3">
                            <span class="fw-bold" style="font-family:'Montserrat', sans-serif; font-size: 13px; color: #2b2d42;"><?= htmlspecialchars($sidebar_store_name) ?></span>
                            <span class="text-muted d-none d-sm-block" style="font-size: 12px;">&copy; <?= date('Y') ?> All Rights Reserved. <?= htmlspecialchars($sidebar_settings['footer_tagline'] ?? 'Sweet Efficiency in every scoop.') ?></span>
                        </div>
                        <div class="d-flex align-items-center gap-4" style="font-size: 12px; font-weight: 500;">
                            <a href="<?= url('?route=privacy') ?>" class="text-muted text-decoration-none" style="transition: color 0.2s;" onmouseover="this.style.color='#ff4d6d'" onmouseout="this.style.color='#6c757d'">Privacy Policy</a>
                            <a href="<?= url('?route=terms') ?>" class="text-muted text-decoration-none" style="transition: color 0.2s;" onmouseover="this.style.color='#ff4d6d'" onmouseout="this.style.color='#6c757d'">Terms of Service</a>
                            <a href="<?= url('?route=contact') ?>" class="text-muted text-decoration-none" style="transition: color 0.2s;" onmouseover="this.style.color='#ff4d6d'" onmouseout="this.style.color='#6c757d'">Contact Us</a>
                        </div>
                    </div>
                </footer>
            </main>
        </div>
    </div>

    <!-- DataTables JS & Bootstrap 5 Integration -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Custom JS -->
    <script src="<?= url('assets/js/main.js') ?>"></script>
    <!-- AOS Animations -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init({
            duration: 800,
            easing: 'ease-in-out',
            once: true,
            mirror: false
        });
    </script>
    <script>
        function updateFlipTimer() {
            const now = new Date();
            let hours = now.getHours();
            const ampm = hours >= 12 ? 'pm' : 'am';
            hours = hours % 12;
            hours = hours ? hours : 12;

            const mins = String(now.getMinutes()).padStart(2, '0');
            const secs = String(now.getSeconds()).padStart(2, '0');
            const hStr = String(hours).padStart(2, '0');

            animateUnit('hr', hStr);
            animateUnit('min', mins);
            animateUnit('sec', secs);
            const ampmEl = document.getElementById('ampm');
            if (ampmEl) ampmEl.textContent = ampm;
        }

        function animateUnit(id, value) {
            const el = document.getElementById(id);
            if (el && el.textContent !== value) {
                el.style.transform = 'rotateX(90deg)';
                el.style.opacity = '0';
                setTimeout(() => {
                    el.textContent = value;
                    el.style.transform = 'rotateX(0deg)';
                    el.style.opacity = '1';
                }, 150);
            }
        }

        setInterval(updateFlipTimer, 1000);
        updateFlipTimer();

        $("#sidebarToggle").click(function(e) {
            e.preventDefault();
            $("#wrapper").toggleClass("toggled");
            if ($("#wrapper").hasClass("toggled")) {
                $("#admin-topbar").css("left", "0");
            } else {
                $("#admin-topbar").css("left", "260px");
            }
        });
    </script>

    <!-- Global Logout Modal -->
    <?php require_once __DIR__ . '/../partials/logout_modal.php'; ?>

</body>

</html>