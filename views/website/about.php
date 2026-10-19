<?php $page_title = 'Our story'; require __DIR__ . '/../partials/shop_open.php'; ?>
<main id="main" class="inner-page shell">
<div class="page-intro"><p class="eyebrow">POS &amp; INVENTORY</p><h1>One platform.<br>Connected operations.</h1><p>Flexible tools for sales, inventory and multi-branch operations.</p></div>
<section class="about-story"><div class="editorial-photo"><img src="<?= url('assets/img/platform-operations.svg') ?>" width="900" height="800" alt="Connected business operations illustration" fetchpriority="high"></div><div class="about-copy"><p class="eyebrow">A LITTLE ABOUT US</p><h2>Built around<br>your business.</h2><div class="body-copy"><?= nl2br($escape('Manage sales, inventory, purchasing and branch operations in a flexible system. Our online catalog connects customers with products selected for publication, while the staff workspace keeps day-to-day operations organized.')) ?></div><a class="button button-red" href="<?= url('?route=products') ?>">Explore our products ↗</a></div></section>
<section class="public-callout"><p class="eyebrow">COME ON OVER</p><h2>Find your nearest branch.</h2><p>Find branch locations, contact details and directions.</p><a class="button button-red" href="<?= url('?route=branches') ?>">Find us ↗</a></section>
</main>
<?php require __DIR__ . '/../partials/shop_close.php'; ?>
