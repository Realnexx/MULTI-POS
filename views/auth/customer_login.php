<?php
$store_name = $store_name ?? 'MultiPOS';
$store_logo = $store_logo ?? 'assets/img/logo.png';
$title = "Customer Login | " . htmlspecialchars($store_name) . " - MultiPOS";
?>
<!doctype html>
<html lang="en">

<head>
<?php require __DIR__.'/../partials/site_favicon.php'; ?>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title><?= htmlspecialchars($title) ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">

    <!-- Modernize & AuroraTabs Admin Assets -->
    
    <link rel="stylesheet" href="<?= url('assets/admin/css/styles.min.css') ?>" />
    <link rel="stylesheet" href="<?= url('assets/admin/css/custom.css') ?>" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
    <script src="<?= url('assets/admin/libs/jquery/dist/jquery.min.js') ?>"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', sans-serif !important;
            background: #f4f6fa;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* Ambient Glow & Grid Accents */
        .auth-bg-ambient {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 0;
            pointer-events: none;
            background:
                radial-gradient(circle at 15% 20%, rgba(93, 135, 255, 0.12) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(19, 222, 185, 0.10) 0%, transparent 45%),
                radial-gradient(circle at 50% 50%, rgba(93, 135, 255, 0.05) 0%, transparent 60%);
        }

        .auth-card {
            border: 1px solid rgba(226, 232, 240, 0.85);
            border-radius: 20px;
            box-shadow: 0 16px 40px rgba(0, 0, 0, 0.05), 0 2px 8px rgba(93, 135, 255, 0.04);
            background: #ffffff;
            backdrop-filter: blur(16px);
            transition: all 0.3s ease;
        }

        /* Prevent auto-zoom on iOS Safari by keeping font-size >= 16px */
        .form-control-modern {
            height: 48px;
            border-radius: 12px;
            border: 1px solid #dfe5ef;
            padding-left: 44px;
            font-size: 16px;
            color: #1e293b;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }

        .form-control-modern:focus {
            background-color: #ffffff;
            border-color: #5d87ff;
            box-shadow: 0 0 0 4px rgba(93, 135, 255, 0.12);
        }

        .input-icon-left {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #7c8fac;
            font-size: 20px;
            pointer-events: none;
            z-index: 5;
        }

        .password-toggle-btn {
            position: absolute;
            right: 4px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #7c8fac;
            width: 44px;
            height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 5;
            transition: color 0.15s ease;
            border-radius: 8px;
        }

        .password-toggle-btn:hover {
            color: #5d87ff;
        }

        .btn-submit-modern {
            min-height: 48px;
            border-radius: 12px;
            font-weight: 700;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 18px rgba(93, 135, 255, 0.32);
            transition: all 0.2s ease;
        }

        .btn-submit-modern:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 22px rgba(93, 135, 255, 0.40);
        }

        .btn-submit-modern:active {
            transform: translateY(1px);
        }

        .btn-portal-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        /* Mobile specific adjustments */
        @media (max-width: 575.98px) {
            .auth-card {
                border-radius: 16px;
                box-shadow: 0 6px 20px rgba(0, 0, 0, 0.05);
            }
            .brand-title-text {
                max-width: 140px;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .btn-portal-pill {
                padding: 5px 10px;
                font-size: 0.75rem;
            }
        }
    </style>
</head>

<body>
    <!-- Background Ambient Glow -->
    <div class="auth-bg-ambient"></div>

    <div class="position-relative min-vh-100 d-flex flex-column justify-content-between p-2 p-sm-3 p-md-4 z-1">
        <!-- Top Navigation Bar -->
        <header class="container-fluid max-w-7xl d-flex justify-content-between align-items-center py-2 px-2 px-sm-3">
            <a href="<?= url('?route=home') ?>" class="d-flex align-items-center gap-2 text-decoration-none min-w-0">
                <img src="<?= url($store_logo) ?>" alt="<?= htmlspecialchars($store_name) ?>"
                    onerror="this.src='<?= url('assets/admin/images/logos/favicon.png') ?>'"
                    style="max-height: 36px; width: auto; object-fit: contain;" class="flex-shrink-0">
                <span class="fw-bold text-dark fs-5 fs-sm-5 tracking-tight brand-title-text"><?= htmlspecialchars($store_name) ?></span>
            </a>

            <div class="d-flex align-items-center gap-2 flex-shrink-0">
                <a href="<?= url('?route=login') ?>" class="btn-portal-pill btn-light-primary text-primary" title="Switch to Staff Portal">
                    <i class="ti ti-shield-lock"></i>
                    <span class="d-none d-sm-inline">Staff Login</span>
                    <span class="d-inline d-sm-none">Staff</span>
                </a>
            </div>
        </header>

        <!-- Main Authentication Form Container -->
        <main class="container py-2 py-sm-4 my-auto px-2 px-sm-3">
            <div class="row justify-content-center">
                <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
                    <div class="card auth-card border-0 mb-0">
                        <div class="card-body p-3 p-sm-4 p-md-5">

                            <!-- Brand / Title Section -->
                            <div class="text-center mb-3 mb-sm-4">
                                <div class="d-inline-flex p-3 rounded-circle bg-light-primary text-primary mb-2 mb-sm-3">
                                    <iconify-icon icon="solar:bag-heart-bold-duotone" class="fs-8"></iconify-icon>
                                </div>
                                <h3 class="fw-bold text-dark mb-1 fs-5 fs-sm-4">Customer Portal</h3>
                                <p class="text-muted fs-3 mb-0">Sign in to browse products, track orders, and order online</p>
                            </div>

                            <!-- Flash Message Banner -->
                            <?php $flash = get_flash();
                            if ($flash): ?>
                                <div class="alert alert-<?= $flash['type'] === 'danger' ? 'danger' : 'success' ?> alert-dismissible fade show rounded-3 py-3 px-3 mb-3 mb-sm-4 d-flex align-items-center gap-2" role="alert">
                                    <i class="fa-solid <?= $flash['type'] === 'danger' ? 'fa-circle-exclamation text-danger' : 'fa-circle-check text-success' ?> fs-5"></i>
                                    <div class="fs-3 text-dark fw-medium"><?= htmlspecialchars($flash['message']) ?></div>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Login Form -->
                            <form action="<?= url('?route=customer_login') ?>" method="POST" id="customerLoginForm">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

                                <!-- Username / Email Field -->
                                <div class="mb-3">
                                    <label class="form-label text-dark fw-semibold fs-3 mb-2" for="username">Username or Email</label>
                                    <div class="position-relative">
                                        <i class="ti ti-user input-icon-left"></i>
                                        <input class="form-control form-control-modern" id="username" name="username"
                                            placeholder="Enter your username or email" required type="text" autofocus />
                                    </div>
                                </div>

                                <!-- Password Field -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-1">
                                        <label class="form-label text-dark fw-semibold fs-3 mb-0" for="password">Password</label>
                                        <a href="#" class="text-primary fw-semibold fs-2 text-decoration-none">Forgot Password?</a>
                                    </div>
                                    <div class="position-relative">
                                        <i class="ti ti-lock input-icon-left"></i>
                                        <input class="form-control form-control-modern" style="padding-right: 48px;"
                                            id="password" name="password" placeholder="••••••••" required type="password" />
                                        <button class="password-toggle-btn" onclick="togglePassword()" type="button" aria-label="Toggle password visibility">
                                            <i class="ti ti-eye" id="pw-toggle-icon"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Remember Device -->
                                <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input primary" type="checkbox" id="remember" checked />
                                        <label class="form-check-label text-dark fs-3 cursor-pointer select-none" for="remember">
                                            Remember this device
                                        </label>
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="submit" class="btn btn-primary btn-submit-modern w-100 fs-4 mb-3 d-flex align-items-center justify-content-center gap-2" id="submitBtn">
                                    <span>Sign In to Your Account</span>
                                    <i class="ti ti-arrow-right fs-5"></i>
                                </button>

                                <!-- Create Account Option -->
                                <div class="text-center mb-3">
                                    <span class="text-muted fs-3">Don't have an account yet?</span>
                                    <a href="<?= url('?route=register') ?>" class="text-primary fw-bold fs-3 text-decoration-none ms-1">Create Account</a>
                                </div>

                                <!-- Back to Home Link -->
                                <div class="text-center">
                                    <a href="<?= url('?route=home') ?>" class="text-muted fw-semibold fs-3 text-decoration-none hover-text-primary d-inline-flex align-items-center gap-1 py-1">
                                        <i class="ti ti-arrow-left"></i>
                                        <span>Back to Website</span>
                                    </a>
                                </div>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="container-fluid max-w-7xl py-2 text-center text-muted fs-2 px-2">
            <div>&copy; <?= date('Y') ?> <?= htmlspecialchars($store_name) ?> · All rights reserved.</div>
        </footer>
    </div>

    <!-- Bootstrap Bundle JS -->
    <script src="<?= url('assets/admin/libs/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>

    <script>
        // Password Visibility Toggle
        function togglePassword() {
            const pwInput = document.getElementById('password');
            const icon = document.getElementById('pw-toggle-icon');
            if (pwInput.type === 'password') {
                pwInput.type = 'text';
                icon.className = 'ti ti-eye-off';
            } else {
                pwInput.type = 'password';
                icon.className = 'ti ti-eye';
            }
        }

        // Button Loading State on Submit
        const customerLoginForm = document.getElementById('customerLoginForm');
        if (customerLoginForm) {
            customerLoginForm.addEventListener('submit', () => {
                const btn = document.getElementById('submitBtn');
                if (btn) {
                    btn.innerHTML = `<i class="fa-solid fa-spinner fa-spin"></i> <span>Signing in...</span>`;
                    btn.classList.add('opacity-75');
                    btn.disabled = true;
                }
            });
        }
    </script>
</body>

</html>
