<?php
$page_title = 'Sign in';
$page_styles = ['assets/css/burger-login.css'];
require __DIR__ . '/../partials/shop_open.php';
?>
<main id="main" class="signin-page shell">
<section class="signin-layout" aria-labelledby="signin-title">
<div class="signin-art"><img src="https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=1100&q=85" alt="A cheeseburger stacked with cheese and vegetables" width="900" height="1100" fetchpriority="high"><span class="signin-stamp" aria-hidden="true">GOOD FOOD.<br>GOOD MOOD.<span>THAT’S THE PLAN.</span></span><div class="signin-caption"><p class="eyebrow">BURGERS &amp; GOOD TIMES</p><h2>Your next<br>good bite awaits.</h2><a href="<?= url('?route=products') ?>">Take a peek at the menu <span aria-hidden="true">↗</span></a></div></div>
<div class="signin-panel"><a class="text-link signin-back" href="<?= url('?route=home') ?>">← Back to the website</a><p class="eyebrow">GOOD TO SEE YOU AGAIN</p><h1 id="signin-title">Welcome<br>back.</h1><p class="signin-description">Sign in to your account and pick up where you left off.</p>
<?php if ($flash): ?><div class="signin-feedback <?= $flash['type'] === 'danger' ? 'is-error' : '' ?>" role="<?= $flash['type'] === 'danger' ? 'alert' : 'status' ?>"><span><?= $escape(strip_tags($flash['message'])) ?></span><button type="button" aria-label="Dismiss message">×</button></div><?php $flash = null; endif; ?>
<form class="shop-form signin-form" id="loginForm" action="<?= url('?route=login') ?>" method="post">
<input type="hidden" name="csrf_token" value="<?= $escape(csrf_token()) ?>">
<label for="username">Username or email<input id="username" name="username" maxlength="254" value="<?= $escape(AuthSecurity::input('username') ?: AuthSecurity::input('email')) ?>" type="text" autocomplete="username" placeholder="Enter your username or email" required autocapitalize="none" spellcheck="false"></label>
<div class="signin-password-field"><label for="password">Password</label><div class="signin-password"><input id="password" name="password" type="password" autocomplete="current-password" maxlength="72" placeholder="Enter your password" required><button type="button" id="togglePassword" aria-controls="password" aria-label="Show password" aria-pressed="false">Show</button></div><a class="signin-forgot" href="<?= url('?route=forgot_password') ?>">Forgot your password?</a></div>
<button type="submit" class="button button-red" id="submitBtn"><span class="signin-button-label">Sign in</span><span aria-hidden="true">↗</span></button>
</form>
<p class="auth-session-note">For your security, you’ll be signed out after <?= (int)(AuthSecurity::config()['idle_timeout']/60) ?> minutes of inactivity.</p>
<p class="signin-register">New around here? <a href="<?= url('?route=register') ?>">Create an account ↗</a></p>
<div class="signin-footnote"><span aria-hidden="true">✳</span><p>Same place. Same good taste.<br>A little more you.</p></div>
</div></section></main>
<script src="<?= url('assets/js/burger-login.js') ?>" defer></script>
<?php require __DIR__ . '/../partials/shop_close.php'; ?>
