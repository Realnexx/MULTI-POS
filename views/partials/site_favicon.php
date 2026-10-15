<?php
if (!class_exists('Setting')) require_once __DIR__.'/../../models/Setting.php';
$websiteLogo=trim((string)Setting::get('store_logo',''));
if($websiteLogo==='' || preg_match('~^(?:[a-z]+:|//)~i',$websiteLogo) && !preg_match('~^https?://~i',$websiteLogo))$websiteLogo='assets/admin/images/logos/favicon.png';
$websiteFavicon=preg_match('~^https?://~i',$websiteLogo)?$websiteLogo:url($websiteLogo);
?>
<link rel="icon" href="<?= htmlspecialchars($websiteFavicon,ENT_QUOTES,'UTF-8') ?>">
<link rel="apple-touch-icon" href="<?= htmlspecialchars($websiteFavicon,ENT_QUOTES,'UTF-8') ?>">
