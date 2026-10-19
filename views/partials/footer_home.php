<?php
// Premium Tailwind Footer for Homepage (Aether-Glass Design System)
?>
<footer class="bg-surface dark:bg-on-background border-t border-surface-container dark:border-outline-variant pt-16 pb-12 relative overflow-hidden z-10">
    <!-- Decorative subtle glow -->
    <div class="absolute -bottom-24 -left-24 w-96 h-96 bg-primary/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -top-24 -right-24 w-96 h-96 bg-tertiary-fixed/5 rounded-full blur-3xl pointer-events-none"></div>

    <div class="container mx-auto px-6 md:px-12 lg:px-24 max-w-full">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-8 lg:gap-12 mb-12">
            <!-- Col 1: Brand & Bio -->
            <div class="lg:col-span-4 flex flex-col gap-4">
                <?php $footer_brand = !empty($store_settings['store_name']) ? ucwords($store_settings['store_name']) : 'Kairo POS'; ?>
                <a href="<?= url('?route=home') ?>" class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed-dim flex items-center gap-2 text-decoration-none">
                    <span class="w-10 h-10 rounded-xl bg-amber-500/10 text-amber-500 flex items-center justify-center"><i class="fa-solid fa-box text-xl"></i></span>
                    <span class="font-extrabold text-xl md:text-2xl"><?= htmlspecialchars($footer_brand) ?></span>
                </a>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant pr-4">
                    <?= htmlspecialchars($content['about_text'] ?? 'Flexible POS, inventory and multi-branch operations.') ?>
                </p>
                <div class="flex gap-3 mt-2">
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container-highest dark:bg-surface-container flex items-center justify-center text-primary dark:text-primary-fixed-dim hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white transition-all transform hover:-translate-y-1 shadow-sm">
                        <i class="fa-brands fa-facebook-f text-base"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container-highest dark:bg-surface-container flex items-center justify-center text-primary dark:text-primary-fixed-dim hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white transition-all transform hover:-translate-y-1 shadow-sm">
                        <i class="fa-brands fa-instagram text-base"></i>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-surface-container-highest dark:bg-surface-container flex items-center justify-center text-primary dark:text-primary-fixed-dim hover:bg-primary hover:text-white dark:hover:bg-primary dark:hover:text-white transition-all transform hover:-translate-y-1 shadow-sm">
                        <i class="fa-brands fa-tiktok text-base"></i>
                    </a>
                </div>
            </div>

            <!-- Col 2: Quick Links -->
            <div class="lg:col-span-2 flex flex-col gap-3">
                <h5 class="font-bold text-lg text-on-background dark:text-white border-b-2 border-primary/20 pb-2 inline-block w-max mb-2">Quick Links</h5>
                <a href="<?= url('?route=home') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Home
                </a>
                <a href="<?= url('?route=products') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Product catalog
                </a>
                <a href="<?= url('?route=branches') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Store Locations
                </a>
                <a href="<?= url('?route=about') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Our Story
                </a>
                <a href="<?= url('?route=contact') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Contact Us
                </a>
            </div>

            <!-- Col 3: Categories -->
            <div class="lg:col-span-3 flex flex-col gap-3">
                <h5 class="font-bold text-lg text-on-background dark:text-white border-b-2 border-primary/20 pb-2 inline-block w-max mb-2">Product catalog</h5>
                <a href="<?= url('?route=products&category_id=47') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-box text-xs text-amber-500"></i> Published products
                </a>
                <a href="<?= url('?route=products&category_id=48') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-fire-burner text-xs text-amber-500"></i> Product categories
                </a>
                <a href="<?= url('?route=products&category_id=45') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-blender text-xs text-amber-500"></i> Hand-Spun Shakes & Drinks
                </a>
                <a href="<?= url('?route=products&category_id=4') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max text-decoration-none">
                    <i class="fa-solid fa-cheese text-xs text-amber-500"></i> Add-ons & Extra Sauces
                </a>
            </div>

            <!-- Col 4: Visit Us -->
            <div class="lg:col-span-3 flex flex-col gap-3">
                <h5 class="font-bold text-lg text-on-background dark:text-white border-b-2 border-primary/20 pb-2 inline-block w-max mb-2">Visit Us</h5>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant flex items-start gap-3 mb-1">
                    <i class="fa-solid fa-location-dot mt-1 text-primary dark:text-primary-fixed-dim flex-shrink-0"></i>
                    <span><?= htmlspecialchars($content['contact_address'] ?? 'Purok 3, Union, Ubay, Bohol, Philippines') ?></span>
                </p>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-phone text-primary dark:text-primary-fixed-dim flex-shrink-0"></i>
                    <span><?= htmlspecialchars($content['contact_phone'] ?? '+63 963 493 0711') ?></span>
                </p>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-envelope text-primary dark:text-primary-fixed-dim flex-shrink-0"></i>
                    <span><?= htmlspecialchars($content['contact_email'] ?? 'Contact us through the contact page') ?></span>
                </p>
            </div>
        </div>

        <hr class="border-surface-container dark:border-outline-variant my-8">

        <!-- Bottom Bar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="font-body-md text-sm text-on-surface-variant dark:text-surface-variant mb-0">
                &copy; <?= date('Y') ?> <?= htmlspecialchars($footer_brand) ?>. All rights reserved. Connected sales and inventory.
            </p>
            <div class="flex flex-wrap justify-center gap-6">
                <a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all font-label-sm text-xs hover:underline" href="<?= url('?route=about') ?>"><i class="fa-solid fa-shield-halved me-1"></i> Privacy Policy</a>
                <a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all font-label-sm text-xs hover:underline" href="<?= url('?route=about') ?>"><i class="fa-solid fa-file-contract me-1"></i> Terms of Service</a>
                <a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all font-label-sm text-xs hover:underline" href="<?= url('?route=contact') ?>"><i class="fa-solid fa-headset me-1"></i> Contact Us</a>
            </div>
        </div>
    </div>
</footer>
