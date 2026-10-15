<?php
$destination = empty($_SESSION['user']['id']) ? 'login' : User::landing_route($_SESSION['user']['id']);
$heading = $status === 419 ? 'Please refresh this page' : ($status === 401 ? 'Please sign in' : 'Access denied');
?>
<!doctype html><html lang="en"><head><?php require __DIR__.'/../partials/site_favicon.php'; ?><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title><?= htmlspecialchars($heading) ?></title>
<link rel="stylesheet" href="<?= url('assets/css/access-denied.css') ?>"></head><body><main class="access-card">
<div class="access-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M12 3 4 6v6c0 5 8 9 8 9s8-4 8-9V6l-8-3Z"/><path d="M12 8v5m0 3v.1"/></svg></div>
<p class="eyebrow">ACCOUNT ACCESS · <?= (int)$status ?></p><h1><?= htmlspecialchars($heading) ?></h1>
<p class="message"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></p>
<div class="help">If you need access, ask your administrator to check your permissions and assigned branch.</div>
<div class="actions"><a class="primary" href="<?= url('?route=' . $destination) ?>"><?= $destination === 'login' ? 'Sign in' : 'Go to my home page' ?></a><a href="<?= url('?route=home') ?>">Visit website</a></div>
<p class="footnote">Your account settings keep each branch’s information secure.</p>
</main></body></html>
