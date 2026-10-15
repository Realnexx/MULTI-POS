<footer class="site-footer shell"><div class="footer-top"><a class="footer-brand" href="<?= url('?route=home') ?>"><?= $escape($brand) ?><span>Good food. Good mood.</span></a><nav aria-label="Footer navigation"><a href="<?= url('?route=products') ?>">Menu</a><a href="<?= url('?route=about') ?>">Our story</a><a href="<?= url('?route=branches') ?>">Find us</a><a href="<?= url('?route=contact') ?>">Contact</a></nav><a class="text-link" href="#main">Back to top ↑</a></div><div class="footer-bottom"><span>© <?= date('Y') ?> <?= $escape($brand) ?>. All rights reserved.</span><span>Burgers &amp; good times.</span></div></footer>
<?php if ($current_route !== 'cart'): ?>
<a id="floating-cart" class="floating-cart" href="<?= url('?route=cart') ?>" aria-label="View cart, <?= (int)$cartCount ?> <?= (int)$cartCount === 1 ? 'item' : 'items' ?>" <?= $cartCount > 0 ? '' : 'hidden' ?>>
<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M5 8h14l1 13H4L5 8Zm3 0V6a4 4 0 0 1 8 0v2"/></svg>
<span>View cart</span><span class="floating-cart-count" data-cart-count><?= (int)$cartCount ?></span>
</a>
<?php endif; ?>
<div id="shop-toast" class="shop-toast" role="status" aria-live="polite" hidden><span></span><button type="button" aria-label="Dismiss notification">×</button></div>
<?php if ($flash): ?><div id="shop-flash" hidden data-message="<?= $escape(strip_tags($flash['message'])) ?>"></div><?php endif; ?>
</body></html>
