<?php
// Footer Component - AuroraTabs Modernize Style for POS
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../models/Setting.php';

$footer_settings = Setting::get_by_category('general');
$store_name = $footer_settings['store_name'] ?? 'kairo';
$tagline = 'Smart POS and inventory system by Reyal Solutions.';
?>

<div class="py-6 px-6 text-center mt-4">
  <p class="mb-0 fs-3 text-muted">
    &copy; <?= date('Y') ?> <strong class="text-dark"><?= htmlspecialchars($store_name) ?></strong>. <?= htmlspecialchars($tagline) ?> All rights reserved.
  </p>
</div>
