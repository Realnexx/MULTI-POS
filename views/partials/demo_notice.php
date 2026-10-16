<?php
if (empty($_SESSION['user']['id'])) return;
require_once __DIR__.'/../../models/DemoAccount.php';
$demoAccount=DemoAccount::status((int)$_SESSION['user']['id']);
if (!$demoAccount['is_demo']) return;
$demoShowModal=($_SESSION['demo_notice_pending']??null)===(int)$_SESSION['user']['id'];
unset($_SESSION['demo_notice_pending']);
$demoExpiry=!empty($demoAccount['expires_at']) ? (new DateTimeImmutable($demoAccount['expires_at'],new DateTimeZone('UTC')))->setTimezone(new DateTimeZone('Asia/Manila')) : null;
?>
<link rel="stylesheet" href="<?= url('assets/css/demo-notice.css') ?>">
<aside class="demo-account-banner" role="status" aria-label="Demo account notice">
 <div><strong>Demo account</strong><span>This is a demo account. Please contact the developer.</span></div>
 <button type="button" data-demo-open>View demo details</button>
</aside>
<dialog id="demo-account-dialog" class="demo-account-dialog" aria-labelledby="demo-account-title" aria-describedby="demo-account-description" data-show-on-login="<?= $demoShowModal?'1':'0' ?>">
 <div class="demo-account-dialog-content">
  <span class="demo-account-label">Limited access</span>
  <h2 id="demo-account-title">You’re using a demo account</h2>
  <p id="demo-account-description">This is a demo account and can only be used for <strong>3 days</strong> from your first successful sign-in. Please contact the developer for continued access.</p>
  <?php if($demoExpiry): ?><p class="demo-account-expiry">Access expires on <time datetime="<?= htmlspecialchars($demoExpiry->format(DateTimeInterface::ATOM),ENT_QUOTES) ?>"><?= htmlspecialchars($demoExpiry->format('F j, Y \a\t g:i A'),ENT_QUOTES) ?></time> (Philippine time).</p><?php endif; ?>
  <form method="dialog"><button type="submit" data-demo-close autofocus>I understand</button></form>
 </div>
</dialog>
<script src="<?= url('assets/js/demo-notice.js') ?>" defer></script>
