<?php require __DIR__ . '/shop_setup.php'; ?>
<!doctype html><html lang="en"><head>
<?php require __DIR__.'/site_favicon.php'; ?>
<meta name="referrer" content="no-referrer">
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $escape(($page_title ?? 'POS & inventory') . ' · ' . $brand) ?></title>
<meta name="description" content="<?= $escape($page_description ?? 'Explore our products and branches, powered by a flexible POS and inventory system.') ?>">
<meta name="csrf-token" content="<?= $escape(csrf_token()) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Barlow+Condensed:wght@600;700;800;900&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= url('assets/vendor/aos/aos.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/burger-home.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/burger-pages.css') ?>">
<?php foreach ($page_styles ?? [] as $stylesheet): ?>
<link rel="stylesheet" href="<?= $escape(url($stylesheet)) ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="<?= url('assets/css/burger-motion.css') ?>">
<script src="<?= url('assets/vendor/aos/aos.js') ?>" defer></script>
<script src="<?= url('assets/js/burger-home.js') ?>" defer></script>
<script src="<?= url('assets/js/burger-motion.js') ?>" defer></script>
</head><body class="burger-home">
<a class="skip-link" href="#main">Skip to content</a>
<?php require __DIR__ . '/shop_header.php'; ?>

<?php require __DIR__.'/demo_notice.php'; ?>
