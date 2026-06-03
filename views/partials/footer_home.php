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
                <a href="<?= url('?route=home') ?>" class="font-headline-md text-headline-md font-bold text-primary dark:text-primary-fixed-dim flex items-center gap-2">
                    <span><i class="fa-solid fa-ice-cream"></i></span> Stacey Halu Halu
                </a>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant pr-4">
                    Premium shaved ice desserts crafted with love, authentic ingredients, and a modern twist. Experience ultimate sweetness in every scoop.
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
                <a href="<?= url('?route=home') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Home
                </a>
                <a href="<?= url('?route=products') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Our Menu
                </a>
                <a href="<?= url('?route=branches') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Branches
                </a>
                <a href="<?= url('?route=about') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> About Us
                </a>
                <a href="<?= url('?route=contact') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-angle-right text-xs text-primary"></i> Contact Us
                </a>
            </div>

            <!-- Col 3: Categories -->
            <div class="lg:col-span-3 flex flex-col gap-3">
                <h5 class="font-bold text-lg text-on-background dark:text-white border-b-2 border-primary/20 pb-2 inline-block w-max mb-2">Categories</h5>
                <a href="<?= url('?route=products&category_id=1') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-bowl-food text-xs text-tertiary-fixed-dim"></i> Halo-halo
                </a>
                <a href="<?= url('?route=products&category_id=2') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-glass-water text-xs text-tertiary-fixed-dim"></i> Drinks & Shakes
                </a>
                <a href="<?= url('?route=products&category_id=3') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-cake-candles text-xs text-tertiary-fixed-dim"></i> Desserts & Cakes
                </a>
                <a href="<?= url('?route=products&category_id=4') ?>" class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all flex items-center gap-2 font-body-md hover:translate-x-1 w-max">
                    <i class="fa-solid fa-ice-cream text-xs text-tertiary-fixed-dim"></i> Add-ons & Toppings
                </a>
            </div>

            <!-- Col 4: Visit Us -->
            <div class="lg:col-span-3 flex flex-col gap-3">
                <h5 class="font-bold text-lg text-on-background dark:text-white border-b-2 border-primary/20 pb-2 inline-block w-max mb-2">Visit Us</h5>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant flex items-start gap-3 mb-1">
                    <i class="fa-solid fa-location-dot mt-1 text-primary dark:text-primary-fixed-dim flex-shrink-0"></i>
                    <span>123 Sweet Tooth Blvd, Sugar City, Philippines</span>
                </p>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-phone text-primary dark:text-primary-fixed-dim flex-shrink-0"></i>
                    <span>+63 912 345 6789</span>
                </p>
                <p class="font-body-md text-on-surface-variant dark:text-surface-variant flex items-center gap-3 mb-1">
                    <i class="fa-solid fa-envelope text-primary dark:text-primary-fixed-dim flex-shrink-0"></i>
                    <span>hello@staceyhaluhalu.com</span>
                </p>
            </div>
        </div>

        <hr class="border-surface-container dark:border-outline-variant my-8">

        <!-- Bottom Bar -->
        <div class="flex flex-col md:flex-row justify-between items-center gap-4 text-center md:text-left">
            <p class="font-body-md text-sm text-on-surface-variant dark:text-surface-variant mb-0">
                &copy; <?= date('Y') ?> Stacey Halu Halu. All rights reserved. Sweet Efficiency in every scoop.
            </p>
            <div class="flex flex-wrap justify-center gap-6">
                <a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all font-label-sm text-xs hover:underline" href="<?= url('?route=about') ?>"><i class="fa-solid fa-shield-halved me-1"></i> Privacy Policy</a>
                <a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all font-label-sm text-xs hover:underline" href="<?= url('?route=about') ?>"><i class="fa-solid fa-file-contract me-1"></i> Terms of Service</a>
                <a class="text-on-surface-variant dark:text-surface-variant hover:text-primary dark:hover:text-primary-fixed-dim transition-all font-label-sm text-xs hover:underline" href="<?= url('?route=contact') ?>"><i class="fa-solid fa-headset me-1"></i> Contact Us</a>
            </div>
        </div>
    </div>
</footer>
