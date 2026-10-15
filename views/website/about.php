<?php $page_title = 'Our story'; require __DIR__ . '/../partials/shop_open.php'; ?>
<main id="main" class="inner-page shell">
<div class="page-intro"><p class="eyebrow">BURGERS &amp; GOOD TIMES</p><h1>Good food.<br>Better company.</h1><p>For the everyday cravings and the moments worth sharing.</p></div>
<section class="about-story"><div class="editorial-photo"><img src="https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=1100&q=85" width="900" height="800" alt="Burgers ready to share at the table" fetchpriority="high"></div><div class="about-copy"><p class="eyebrow">A LITTLE ABOUT US</p><h2>There’s always<br>room for a burger.</h2><div class="body-copy"><?= nl2br($escape(trim($content['about_text'] ?? '') ?: 'A quick lunch. A long catch-up. A little treat, just because. We believe the best meals are the ones you look forward to, and the ones you share. Find something you love on our menu and make yourself at home.')) ?></div><a class="button button-red" href="<?= url('?route=products') ?>">Get to know the menu ↗</a></div></section>
<section class="public-callout"><p class="eyebrow">COME ON OVER</p><h2>Your table is waiting.</h2><p>Find a branch near you and plan your next burger stop.</p><a class="button button-red" href="<?= url('?route=branches') ?>">Find us ↗</a></section>
</main>
<?php require __DIR__ . '/../partials/shop_close.php'; ?>
