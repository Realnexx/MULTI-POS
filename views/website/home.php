<?php
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$currency = $store_settings['currency_symbol'] ?? '₱';
$brand = trim($store_settings['store_name'] ?? '') ?: 'Kairo Burger Co.';
$heroTitle = trim($content['hero_title'] ?? '') ?: 'Big bites. Good times.';
$heroSubtitle = trim($content['hero_subtitle'] ?? '') ?: 'Find your next favorite burger, pick your sides, and make a meal of it.';
$heroPhoto = 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1400&q=85';
$menuItems = []; $seen = [];
foreach (array_merge($featured ?? [], $best_sellers ?? [], $burgers ?? []) as $item) {
    if (isset($seen[$item['id']]) || !empty($item['deleted_at']) || strtolower($item['status'] ?? 'active') !== 'active' || !empty($item['is_ingredient']) || ($item['product_type'] ?? '') === 'ingredient' || (isset($item['is_sellable']) && !$item['is_sellable'])) continue;
    $seen[$item['id']] = true; $menuItems[] = $item;
    if (count($menuItems) === 6) break;
}
$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
$staffRoute = !empty($_SESSION['user']['id']) ? User::landing_route($_SESSION['user']['id']) : 'home';
$flash = get_flash();
?>
<?php $page_description = $heroSubtitle; require __DIR__ . '/../partials/shop_open.php'; ?>
<main id="main">
<section class="hero shell" aria-labelledby="hero-title">
<div class="hero-copy"><p class="eyebrow"><span class="little-star" aria-hidden="true">✳</span> A LITTLE HUNGRY? YOU’RE IN THE RIGHT PLACE.</p><h1 id="hero-title"><?= $escape($heroTitle) ?><span class="heading-dot" aria-hidden="true">✳</span></h1><p class="hero-description"><?= $escape($heroSubtitle) ?></p><div class="hero-actions"><a class="button button-red" href="<?= url('?route=products') ?>">Explore the menu <span aria-hidden="true">↗</span></a><a class="text-link" href="<?= url('?route=branches') ?>">Find a branch <span aria-hidden="true">→</span></a></div><div class="hero-note"><span class="note-lines" aria-hidden="true">〰</span><span>Bring your appetite.<br><strong>We’ll bring the burgers.</strong></span></div></div>
<div class="hero-visual"><div class="hero-photo"><img src="<?= $heroPhoto ?>" alt="Cheeseburger stacked with a beef patty, cheese, and vegetables" fetchpriority="high" width="900" height="1000"><div class="photo-caption"><span>THE ONLY PLAN YOU NEED</span><strong>Let’s eat.</strong><a href="#menu" aria-label="Browse our menu"><span aria-hidden="true">↓</span></a></div></div><div class="round-stamp" aria-hidden="true">MORE<br>BURGER.<br><span>MORE HAPPY.</span></div><span class="hero-spark" aria-hidden="true">✳</span></div>
</section>
<div class="flavor-strip" aria-hidden="true"><div>BIG BITES <span>✳</span> GOOD COMPANY <span>✳</span> GREAT BURGERS <span>✳</span> BIG BITES <span>✳</span> GOOD COMPANY <span>✳</span> GREAT BURGERS <span>✳</span></div></div>
<section id="menu" class="menu-section shell" aria-labelledby="menu-title"><div class="section-heading"><div><p class="eyebrow">THE GOOD STUFF</p><h2 id="menu-title">Meet your next craving.</h2></div><a class="text-link" href="<?= url('?route=products') ?>">View the full menu <span aria-hidden="true">↗</span></a></div>
<?php if (!empty($categories)): ?><nav class="category-links" aria-label="Menu categories"><a class="selected" href="<?= url('?route=products') ?>">Explore all</a><?php foreach (array_slice($categories, 0, 6) as $category): ?><a href="<?= url('?route=products&category_id='.(int)$category['id']) ?>"><?= $escape($category['name']) ?></a><?php endforeach; ?></nav><?php endif; ?>
<?php if ($menuItems): ?><div class="product-grid"><?php foreach ($menuItems as $item): $detailUrl = url('?route=product_detail&id='.(int)$item['id']); ?>
<?php require __DIR__ . '/../partials/shop_product_card.php'; ?>
<?php endforeach; ?></div><?php else: ?><div class="menu-empty"><span aria-hidden="true">✳</span><h3>Good things are coming to the menu.</h3><p>Browse the menu for the latest items, or contact us to find out what’s available.</p><a class="button button-red" href="<?= url('?route=products') ?>">Visit the menu <span aria-hidden="true">↗</span></a></div><?php endif; ?>
</section>
<section class="story-section shell" aria-labelledby="story-title"><div class="story-photo"><img src="https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=900&q=80" alt="A burger served with golden fries" loading="lazy" width="900" height="800"><span class="photo-tag">GOOD FOOD. BETTER COMPANY.</span></div><div class="story-copy"><p class="eyebrow">IT’S A BURGER KIND OF DAY</p><h2 id="story-title">Less scrolling.<br>More savoring.</h2><p><?= $escape(trim($content['about_text'] ?? '') ?: 'A quick lunch, a catch-up with friends, or a little reward at the end of the day. There’s always a good reason for a burger. Pick something you love and make yourself at home.') ?></p><a class="button button-cream" href="<?= url('?route=about') ?>">A little about us <span aria-hidden="true">↗</span></a><span class="story-star" aria-hidden="true">✳</span></div></section>
<section class="visit-section shell" aria-labelledby="visit-title"><div class="section-heading"><div><p class="eyebrow">SEE YOU AROUND</p><h2 id="visit-title">Your next burger stop.</h2></div><a class="text-link" href="<?= url('?route=branches') ?>">All branches <span aria-hidden="true">↗</span></a></div><div class="branch-grid"><?php foreach (array_slice($branches ?? [], 0, 3) as $index=>$branch): ?><article class="branch-card"><span class="branch-number">0<?= $index+1 ?></span><h3><?= $escape($branch['name']) ?></h3><p><?= $escape($branch['address'] ?? '') ?></p><?php if (!empty($branch['phone'])): ?><p><?= $escape($branch['phone']) ?></p><?php endif; ?><a class="text-link" href="<?= url('?route=branches') ?>">Branch details <span aria-hidden="true">↗</span></a></article><?php endforeach; ?><?php if (empty($branches)): ?><div class="branch-card"><h3>Let’s find your spot.</h3><p>Get in touch for branch information and availability.</p><a class="text-link" href="<?= url('?route=contact') ?>">Contact us <span aria-hidden="true">↗</span></a></div><?php endif; ?></div></section>
<section class="last-call"><div class="shell"><p class="eyebrow">FOLLOW YOUR APPETITE</p><h2>Make today<br>a burger day.</h2><a class="button button-cream" href="<?= url('?route=products') ?>">Let’s order <span aria-hidden="true">↗</span></a><span aria-hidden="true" class="last-call-star">✳</span></div></section>
</main>
<?php require __DIR__ . '/../partials/shop_close.php'; ?>
