<?php
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$currency = $store_settings['currency_symbol'] ?? '₱';
$brand = trim($store_settings['store_name'] ?? '') ?: 'Kairo POS';
$heroTitle = 'One system. Every part of your business.';
$heroSubtitle = 'A flexible multi-branch POS and inventory system that brings sales, stock, purchasing and online orders together.';
$menuItems = []; $seen = [];
foreach (array_merge($featured ?? [], $best_sellers ?? [], $catalog_products ?? []) as $item) {
    if (isset($seen[$item['id']]) || !empty($item['deleted_at']) || strtolower($item['status'] ?? 'active') !== 'active' || !empty($item['is_ingredient']) || ($item['product_type'] ?? '') === 'ingredient' || (isset($item['is_sellable']) && !$item['is_sellable'])) continue;
    $seen[$item['id']] = true; $menuItems[] = $item;
    if (count($menuItems) === 6) break;
}
$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
$staffRoute = !empty($_SESSION['user']['id']) ? User::landing_route($_SESSION['user']['id']) : 'home';
$flash = get_flash();
?>
<?php $page_description = $heroSubtitle; $page_styles=['assets/css/platform-home.css']; require __DIR__ . '/../partials/shop_open.php'; ?>
<main id="main">
<section class="hero shell" aria-labelledby="hero-title">
<div class="hero-copy"><p class="eyebrow"><span class="little-star" aria-hidden="true">✳</span> A CONNECTED SYSTEM FOR YOUR BUSINESS</p><h1 id="hero-title"><?= $escape($heroTitle) ?><span class="heading-dot" aria-hidden="true">✳</span></h1><p class="hero-description"><?= $escape($heroSubtitle) ?></p><div class="hero-actions"><a class="button button-red" href="<?= url('?route=pricing') ?>">Explore pricing <span aria-hidden="true">↗</span></a><a class="text-link" href="<?= url('?route=branches') ?>">Find a branch <span aria-hidden="true">→</span></a></div><div class="hero-note"><span class="note-lines" aria-hidden="true">〰</span><span>From checkout to stock control.<br><strong>Keep your business connected.</strong></span></div></div>
<div class="platform-preview" aria-label="Connected business operations"><span class="platform-label">MULTI-BRANCH POS &amp; INVENTORY</span><h2>Everything works<br>better together.</h2><div class="platform-grid"><article><span>01</span><h3>Point of sale</h3><p>Sales, payments and cashier shifts.</p></article><article><span>02</span><h3>Inventory</h3><p>Stock levels, movements and alerts.</p></article><article><span>03</span><h3>Branches</h3><p>Locations, teams and performance.</p></article><article><span>04</span><h3>Online catalog</h3><p>Your business, presented online.</p></article></div><p class="platform-footnote">Built to adapt to your products and your workflow.</p></div>
</section>
<div class="flavor-strip" aria-hidden="true"><div>POINT OF SALE <span>✳</span> MULTI-BRANCH <span>✳</span> INVENTORY <span>✳</span> POINT OF SALE <span>✳</span> MULTI-BRANCH <span>✳</span> INVENTORY <span>✳</span></div></div>
<section class="menu-section shell"><div class="section-heading"><div><p class="eyebrow">THREE WAYS TO GET STARTED</p><h2>Built around your business.</h2></div><a class="text-link" href="<?= url('?route=pricing') ?>">Compare pricing →</a></div><p>Choose basic multi-branch development, an advanced system with proposed integrations, or a commercial subscription for a single branch.</p></section>
<section class="system-section shell" aria-labelledby="story-title"><div><p class="eyebrow">FLEXIBLE BY DESIGN</p><h2 id="story-title">Your products.<br>Your way of working.</h2><p>Connect checkout, inventory and purchasing across your branches. Publish the products you want customers to see, while keeping internal stock and ingredients within your workspace.</p></div><div class="system-features"><article><h3>Sell with confidence</h3><p>Keep transactions, payment records and cashier shifts together.</p></article><article><h3>Understand your stock</h3><p>Track inventory, transfers and purchasing in one place.</p></article><article><h3>Stay connected</h3><p>Bring branch operations and your online catalog into the same system.</p></article></div></section>
<section class="visit-section shell" aria-labelledby="visit-title"><div class="section-heading"><div><p class="eyebrow">SEE YOU AROUND</p><h2 id="visit-title">Find your nearest branch.</h2></div><a class="text-link" href="<?= url('?route=branches') ?>">All branches <span aria-hidden="true">↗</span></a></div><div class="branch-grid"><?php foreach (array_slice($branches ?? [], 0, 3) as $index=>$branch): ?><article class="branch-card"><span class="branch-number">0<?= $index+1 ?></span><h3><?= $escape($branch['name']) ?></h3><p><?= $escape($branch['address'] ?? '') ?></p><?php if (!empty($branch['phone'])): ?><p><?= $escape($branch['phone']) ?></p><?php endif; ?><a class="text-link" href="<?= url('?route=branches') ?>">Branch details <span aria-hidden="true">↗</span></a></article><?php endforeach; ?><?php if (empty($branches)): ?><div class="branch-card"><h3>Let’s find your spot.</h3><p>Get in touch for branch information and availability.</p><a class="text-link" href="<?= url('?route=contact') ?>">Contact us <span aria-hidden="true">↗</span></a></div><?php endif; ?></div></section>
<section class="last-call"><div class="shell"><p class="eyebrow">EXPLORE WHAT’S AVAILABLE</p><h2>Find the right fit<br>for your business.</h2><a class="button button-cream" href="<?= url('?route=pricing') ?>">Compare plans <span aria-hidden="true">↗</span></a><span aria-hidden="true" class="last-call-star">✳</span></div></section>
</main>
<?php require __DIR__ . '/../partials/shop_close.php'; ?>
