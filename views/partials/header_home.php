<?php
$current_route = $_GET['route'] ?? 'home';

// Desktop classes
$active_class = 'text-primary dark:text-primary-fixed-dim border-b-2 border-primary font-bold pb-1 font-label-md text-label-md';
$inactive_class = 'text-on-surface-variant dark:text-surface-variant hover:text-primary transition-colors font-label-md text-label-md';

// Mobile classes
$active_mobile_class = 'flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-bold transition-all';
$inactive_mobile_class = 'flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-surface-container text-on-surface-variant hover:text-primary transition-all';
?>
<!-- TopNavBar -->
<header class="bg-surface dark:bg-on-background shadow-sm sticky top-0 z-50 transition-all duration-300">
    <nav class="flex justify-between items-center w-full px-margin-desktop py-base max-w-full mx-auto relative">
        <!-- Left: Brand Logo -->
        <a href="<?= url('?route=home') ?>" class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed-dim flex items-center gap-2 text-decoration-none z-10">
            <span><i class="fa-solid fa-ice-cream"></i></span> <span class="hidden md:inline">Stacy Halo Halo</span>
        </a>

        <!-- Center: Centered Navlinks -->
        <div class="hidden md:flex items-center gap-lg absolute left-1/2 transform -translate-x-1/2 z-10">
            <a class="<?= $current_route === 'home' ? $active_class : $inactive_class ?> text-decoration-none" href="<?= url('?route=home') ?>">Home</a>
            <a class="<?= $current_route === 'products' ? $active_class : $inactive_class ?> text-decoration-none" href="<?= url('?route=products') ?>">Menu</a>
            <a class="<?= $current_route === 'branches' ? $active_class : $inactive_class ?> text-decoration-none" href="<?= url('?route=branches') ?>">Branches</a>
            <a class="<?= $current_route === 'about' ? $active_class : $inactive_class ?> text-decoration-none" href="<?= url('?route=about') ?>">About Us</a>
            <a class="<?= $current_route === 'contact' ? $active_class : $inactive_class ?> text-decoration-none" href="<?= url('?route=contact') ?>">Contact</a>
        </div>

        <div class="flex items-center gap-md z-10">
            <?php if (!empty($_SESSION['user'])): ?>
                <!-- Cart Button beside the profile dropdown -->
                <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty')); ?>
                <a href="<?= url('?route=cart') ?>" class="relative w-10 h-10 rounded-full bg-surface-container hover:bg-primary/10 text-on-surface-variant hover:text-primary transition-all flex items-center justify-center shadow-sm active:scale-95 text-decoration-none" title="View Cart">
                    <i class="fa-solid fa-cart-shopping text-[16px]"></i>
                    <span id="navCartBadge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
                </a>

                <!-- User Profile Dropdown -->
                <?php
                $user_avatar = $_SESSION['user']['avatar_url'] ?? '';
                if (empty($user_avatar) && isset($_SESSION['user']['id'])) {
                    $user_db = User::find_by_id($_SESSION['user']['id']);
                    $user_avatar = $user_db['avatar_url'] ?? '';
                }
                if (empty($user_avatar)) {
                    $user_avatar = 'https://lh3.googleusercontent.com/aida-public/AB6AXuCuom_75fEmAQrawrwRszprdNd7mbTW-uLVHnh7vK0oZIlXiJ51O3bRSjPy2zfOnXs19wgqHa58KiiU1XP4qCp1GqPikps7CByYEWxDFZDEMjR_MV2kTMmnzhbKDq-uDPvFY3WqP3eNZsYoc0oylqlF_NZHGa75-wHJprW5fm2be_lMFq6iWViq07kIzkDKckPdkivgPOHn7-THyinajDZk44gk0FhO2VLeV43FMUgIvOuEMbwlOnmb2a0qY4PnCTGczI2ufBAMAB8';
                }
                $user_roles = User::get_user_roles($_SESSION['user']['id']);
                $role_names = array_column($user_roles, 'name');
                $primary_role = !empty($role_names) ? $role_names[0] : 'Club Member';
                ?>
                <div class="relative">
                    <button id="profileDropdownBtn" class="flex items-center focus:outline-none focus:ring-2 focus:ring-primary/50 rounded-full transition-all duration-300 hover:scale-105 active:scale-95">
                        <img class="w-10 h-10 rounded-full border-2 border-primary object-cover shadow-md" src="<?= htmlspecialchars($user_avatar) ?>" alt="User profile picture">
                    </button>
                    <!-- Dropdown Panel (Aether-Glass Styled with micro-transitions) -->
                    <div id="profileDropdownMenu" class="absolute right-0 mt-3 w-72 bg-white/95 dark:bg-black/95 backdrop-blur-xl rounded-3xl shadow-[0_20px_50px_rgba(182,14,61,0.18)] border border-white/20 dark:border-white/10 py-4 z-50 origin-top-right transition-all duration-200 transform scale-95 opacity-0 pointer-events-none">
                        <!-- Upward caret arrow -->
                        <div class="absolute right-4 -top-2 w-4 h-4 bg-white/95 dark:bg-black/95 border-t border-l border-white/20 dark:border-white/10 rotate-45 z-40"></div>

                        <!-- Profile Card Header -->
                        <div class="px-5 pb-3 pt-1 border-b border-surface-container-high dark:border-white/10 mb-2 flex items-center gap-3">
                            <img class="w-12 h-12 rounded-full border-2 border-primary/40 object-cover shadow-sm" src="<?= htmlspecialchars($user_avatar) ?>" alt="Profile card avatar">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-bold text-on-surface truncate mb-0.5"><?= htmlspecialchars($_SESSION['user']['name']) ?></p>
                                <p class="text-[11px] text-on-surface-variant/80 truncate mb-1"><?= htmlspecialchars($_SESSION['user']['email'] ?? '') ?></p>
                                <span class="inline-flex items-center px-2 py-0.5 text-[9px] font-bold tracking-wider uppercase bg-primary/10 text-primary dark:bg-primary-container/20 dark:text-primary-fixed-dim rounded-full">
                                    <?= htmlspecialchars($primary_role) ?>
                                </span>
                            </div>
                        </div>

                        <!-- Menu Actions -->
                        <div class="px-2 space-y-1">
                            <?php if (User::has_permission($_SESSION['user']['id'], 'view_dashboard')): ?>
                                <a href="<?= url('?route=dashboard') ?>" class="group flex items-center gap-3 px-3 py-2 text-sm text-on-surface hover:bg-primary/5 rounded-2xl transition-all duration-300 text-decoration-none">
                                    <div class="w-8 h-8 rounded-xl bg-surface-container-high dark:bg-white/5 group-hover:bg-primary/15 flex items-center justify-center transition-all">
                                        <i class="fa-solid fa-chart-pie text-on-surface-variant/80 group-hover:text-primary transition-colors"></i>
                                    </div>
                                    <span class="group-hover:translate-x-1 group-hover:text-primary transition-all font-semibold">Dashboard</span>
                                </a>
                            <?php elseif (User::has_permission($_SESSION['user']['id'], 'access_pos')): ?>
                                <a href="<?= url('?route=pos') ?>" class="group flex items-center gap-3 px-3 py-2 text-sm text-on-surface hover:bg-primary/5 rounded-2xl transition-all duration-300 text-decoration-none">
                                    <div class="w-8 h-8 rounded-xl bg-surface-container-high dark:bg-white/5 group-hover:bg-primary/15 flex items-center justify-center transition-all">
                                        <i class="fa-solid fa-cash-register text-on-surface-variant/80 group-hover:text-primary transition-colors"></i>
                                    </div>
                                    <span class="group-hover:translate-x-1 group-hover:text-primary transition-all font-semibold">POS System</span>
                                </a>
                            <?php endif; ?>

                            <a href="<?= url('?route=setup_profile') ?>" class="group flex items-center gap-3 px-3 py-2 text-sm text-on-surface hover:bg-primary/5 rounded-2xl transition-all duration-300 text-decoration-none">
                                <div class="w-8 h-8 rounded-xl bg-surface-container-high dark:bg-white/5 group-hover:bg-primary/15 flex items-center justify-center transition-all">
                                    <i class="fa-solid fa-user text-on-surface-variant/80 group-hover:text-primary transition-colors"></i>
                                </div>
                                <span class="group-hover:translate-x-1 group-hover:text-primary transition-all font-semibold">Profile</span>
                            </a>

                            <a href="<?= url('?route=setup_profile') ?>" class="group flex items-center gap-3 px-3 py-2 text-sm text-on-surface hover:bg-primary/5 rounded-2xl transition-all duration-300 text-decoration-none">
                                <div class="w-8 h-8 rounded-xl bg-surface-container-high dark:bg-white/5 group-hover:bg-primary/15 flex items-center justify-center transition-all">
                                    <i class="fa-solid fa-gear text-on-surface-variant/80 group-hover:text-primary transition-colors"></i>
                                </div>
                                <span class="group-hover:translate-x-1 group-hover:text-primary transition-all font-semibold">Settings</span>
                            </a>

                            <div class="h-px bg-surface-container-high dark:bg-white/10 my-2 mx-3"></div>

                            <a href="<?= url('?route=logout') ?>" onclick="openLogoutModal(event)" class="group flex items-center gap-3 px-3 py-2 text-sm text-error hover:bg-error/5 rounded-2xl transition-all duration-300 text-decoration-none">
                                <div class="w-8 h-8 rounded-xl bg-error/10 flex items-center justify-center transition-all">
                                    <i class="fa-solid fa-right-from-bracket text-error"></i>
                                </div>
                                <span class="group-hover:translate-x-1 transition-all font-bold">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Not Logged In -->
                <a href="<?= url('?route=customer_login') ?>" class="hidden md:block font-label-md text-label-md text-on-surface-variant hover:text-primary transition-colors text-decoration-none"><i class="fa-solid fa-right-to-bracket me-1"></i> Login</a>
                <a href="<?= url('?route=products') ?>" class="bg-primary hover:bg-primary-container text-white px-md py-sm rounded-pill font-label-md text-label-md shadow-sm active:scale-95 transition-all inline-block text-decoration-none"><i class="fa-solid fa-utensils me-1"></i> Order Now</a>

                <?php $cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty')); ?>
                <a href="<?= url('?route=cart') ?>" class="relative w-10 h-10 rounded-full bg-surface-container hover:bg-primary/10 text-on-surface-variant hover:text-primary transition-all flex items-center justify-center shadow-sm active:scale-95 text-decoration-none" title="View Cart">
                    <i class="fa-solid fa-cart-shopping text-[16px]"></i>
                    <span id="navCartBadge" class="absolute -top-1 -right-1 bg-primary text-white text-[10px] font-bold rounded-full w-4 h-4 flex items-center justify-center <?= $cartCount > 0 ? '' : 'hidden' ?>"><?= $cartCount ?></span>
                </a>
            <?php endif; ?>

            <!-- Mobile menu button -->
            <button class="md:hidden w-10 h-10 rounded-full bg-surface-container hover:bg-primary hover:text-white text-on-surface transition-all flex items-center justify-center shadow-sm active:scale-95" id="mobileMenuBtn" aria-label="Toggle Menu">
                <span class="material-symbols-outlined text-xl transition-transform duration-300" id="mobileMenuIcon">menu</span>
            </button>
        </div>
    </nav>
    <!-- Mobile Menu Collapse -->
    <div class="md:hidden hidden px-6 py-6 bg-surface/95 backdrop-blur-md border-b border-surface-container shadow-xl transition-all duration-300" id="mobileMenu">
        <div class="flex flex-col gap-3 font-label-md max-w-lg mx-auto">
            <a href="<?= url('?route=home') ?>" class="<?= $current_route === 'home' ? $active_mobile_class : $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-house text-lg w-6"></i> Home</a>
            <a href="<?= url('?route=products') ?>" class="<?= $current_route === 'products' ? $active_mobile_class : $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-utensils text-lg w-6"></i> Menu</a>
            <a href="<?= url('?route=branches') ?>" class="<?= $current_route === 'branches' ? $active_mobile_class : $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-store text-lg w-6"></i> Branches</a>
            <a href="<?= url('?route=about') ?>" class="<?= $current_route === 'about' ? $active_mobile_class : $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-circle-info text-lg w-6"></i> About Us</a>
            <a href="<?= url('?route=contact') ?>" class="<?= $current_route === 'contact' ? $active_mobile_class : $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-envelope text-lg w-6"></i> Contact</a>
            <hr class="border-surface-container my-2">
            <?php if (!empty($_SESSION['user'])): ?>
                <?php if (User::has_permission($_SESSION['user']['id'], 'view_dashboard')): ?>
                    <a href="<?= url('?route=dashboard') ?>" class="<?= $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-chart-pie text-lg w-6"></i> Dashboard</a>
                <?php elseif (User::has_permission($_SESSION['user']['id'], 'access_pos')): ?>
                    <a href="<?= url('?route=pos') ?>" class="<?= $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-cash-register text-lg w-6"></i> POS System</a>
                <?php endif; ?>
                <a href="<?= url('?route=setup_profile') ?>" class="<?= $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-user text-lg w-6"></i> Profile</a>
                <a href="<?= url('?route=setup_profile') ?>" class="<?= $inactive_mobile_class ?> text-decoration-none"><i class="fa-solid fa-gear text-lg w-6"></i> Settings</a>
                <a href="<?= url('?route=logout') ?>" onclick="openLogoutModal(event)" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-error/10 text-error font-bold transition-all text-decoration-none"><i class="fa-solid fa-right-from-bracket text-lg w-6"></i> Logout</a>
            <?php else: ?>
                <a href="<?= url('?route=customer_login') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-primary/10 text-primary font-bold transition-all text-decoration-none"><i class="fa-solid fa-right-to-bracket text-lg w-6"></i> Login</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<!-- Include Global Logout Modal -->
<?php require_once __DIR__ . '/logout_modal.php'; ?>

<!-- Hidden form used by goToProduct() to POST navigate without exposing ID in URL -->
<form id="_productNavForm" method="POST" action="<?= url('?route=product_detail') ?>" style="display:none;">
    <input type="hidden" name="id" id="_productNavId" value="">
</form>
<script>
    function goToProduct(id) {
        document.getElementById('_productNavId').value = id;
        document.getElementById('_productNavForm').submit();
    }

    // Toggle profile dropdown menu on click with animation
    document.addEventListener('DOMContentLoaded', () => {
        const profileBtn = document.getElementById('profileDropdownBtn');
        const profileMenu = document.getElementById('profileDropdownMenu');

        if (profileBtn && profileMenu) {
            profileBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isClosed = profileMenu.classList.contains('opacity-0');
                if (isClosed) {
                    profileMenu.classList.remove('opacity-0', 'scale-95', 'pointer-events-none');
                    profileMenu.classList.add('opacity-100', 'scale-100', 'pointer-events-auto');
                } else {
                    profileMenu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                    profileMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                }
            });

            document.addEventListener('click', (e) => {
                if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
                    profileMenu.classList.remove('opacity-100', 'scale-100', 'pointer-events-auto');
                    profileMenu.classList.add('opacity-0', 'scale-95', 'pointer-events-none');
                }
            });
        }
    });
</script>