<?php
$page_title='Verify your sign-in';$page_styles=['assets/css/burger-login.css'];require __DIR__.'/../partials/shop_open.php';
?>
<main class="shell" style="max-width:640px;padding:48px 20px">
 <h1><?= !empty($pending['verified'])?'Save your recovery codes':($pending['secret']??null?'Set up your authenticator':'Verify your sign-in') ?></h1>
 <?php if($factorError): ?><p class="signin-feedback is-error" role="alert"><?= $escape($factorError) ?></p><?php endif; ?>
 <?php if(!empty($pending['verified'])): ?>
  <p>Store these codes somewhere private. Each works once if you lose your authenticator. They will not be shown again.</p>
  <ul><?php foreach($pending['recovery'] as $code): ?><li><code><?= $escape($code) ?></code></li><?php endforeach; ?></ul>
 <?php elseif(!empty($pending['secret'])): ?>
  <p>In your authenticator app, add a time based account using this setup key. Enter its six-digit code to finish. Your sign-in expires after ten minutes.</p>
  <p><strong><?= $escape($user['email']) ?></strong><br><code style="overflow-wrap:anywhere"><?= $escape($pending['secret']) ?></code></p>
 <?php else: ?><p>Enter the six-digit code from your authenticator app, or one unused recovery code.</p><?php endif; ?>
 <form method="post" action="<?= url('?route=login&mfa=1') ?>" class="shop-form">
  <input type="hidden" name="csrf_token" value="<?= $escape(csrf_token()) ?>">
  <?php if(empty($pending['verified'])): ?><label for="factor-code">Verification or recovery code<input id="factor-code" name="code" autocomplete="one-time-code" maxlength="16" autofocus required></label><?php endif; ?>
  <button class="button button-red" type="submit"><?= !empty($pending['verified'])?'I saved my codes — continue':'Verify code' ?></button>
 </form><p><a href="<?= url('?route=login&restart=1') ?>">Start sign-in again</a></p>
</main>
<?php require __DIR__.'/../partials/shop_close.php'; ?>
