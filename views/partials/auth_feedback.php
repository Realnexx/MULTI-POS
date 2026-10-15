<?php if ($flash): ?>
<div class="signin-feedback <?= $flash['type'] === 'danger' ? 'is-error' : '' ?>" role="<?= $flash['type'] === 'danger' ? 'alert' : 'status' ?>"><span><?= $escape(strip_tags($flash['message'])) ?></span><button type="button" aria-label="Dismiss message">×</button></div>
<?php $flash = null; endif; ?>
