<?php
require_once __DIR__.'/../../models/DatabaseBackup.php';
$backupConfig=DatabaseBackup::settings();
$backupInstalled=(bool)$GLOBALS['conn']->query("SHOW TABLES LIKE 'database_backups'")->num_rows;
$backups=$backupInstalled?DatabaseBackup::recent():[];
$backupEscape=static function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
?>
<div class="tab-pane fade <?= $active_tab==='backup'?'show active':'' ?>" id="tab-backup">
 <div class="settings-section-card p-4">
  <h5>Database backups</h5><p class="text-muted">Encrypted copies of all branches, sales, inventory, users and application settings. Includes the payment encryption key. Product photos and server configuration need separate file backups.</p>
  <?php if(!$backupInstalled): ?><div class="alert alert-warning">The administrator must install the backup tables before using this page.</div><?php else: ?>
  <div class="alert alert-info">Keep a copy off this computer. Download the recovery key separately and store it securely: encrypted backups cannot be recovered without it. Times below are UTC; scheduled hour is Philippine time.</div>
  <?php if(!$backupConfig['runner_seen'] || strtotime($backupConfig['runner_seen'].' UTC')<time()-7200): ?><div class="alert alert-warning">The scheduled runner has not checked in during the last two hours. Scheduling requires the server task described in DATABASE-BACKUPS.md. Manual backups remain available.</div><?php endif; ?>
  <?php if($backupConfig['last_error']): ?><div class="alert alert-danger"><?= $backupEscape($backupConfig['last_error']) ?></div><?php endif; ?>
  <form method="post" action="<?= url('?route=admin/settings&tab=backup') ?>" class="row g-3">
   <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="setting_category" value="backup">
   <div class="col-12"><label><input type="checkbox" name="backup[enabled]" value="1" <?= $backupConfig['enabled']?'checked':'' ?>> Enable daily database backups</label></div>
   <div class="col-sm-6"><label class="form-label" for="backup-hour">Daily hour (0–23, Philippine time)</label><input id="backup-hour" class="form-control" name="backup[hour]" type="number" min="0" max="23" value="<?= $backupConfig['hour'] ?>" required></div>
   <div class="col-sm-6"><label class="form-label" for="backup-retention">Completed backups to keep (1–90)</label><input id="backup-retention" class="form-control" name="backup[retention]" type="number" min="1" max="90" value="<?= $backupConfig['retention'] ?>" required></div>
   <div class="col-12"><label class="form-label" for="backup-password">Current account password</label><input id="backup-password" class="form-control" type="password" name="current_password" autocomplete="current-password" required><small class="text-muted">Required for every backup action. Downloads contain confidential data.</small></div>
   <div class="col-12 d-flex flex-wrap gap-2"><button class="btn btn-primary" name="backup_action" value="save">Save schedule</button><button class="btn btn-outline-primary" name="backup_action" value="create">Back up now</button><button class="btn btn-outline-secondary" name="backup_action" value="key">Download recovery key</button></div>
  </form>
  <hr><p class="text-muted small">Last successful backup: <?= $backupEscape($backupConfig['last_success']?:'None') ?> · Runner last checked: <?= $backupEscape($backupConfig['runner_seen']?:'Never') ?>. Restore verification creates a temporary database, checks every table and row count, then removes only that temporary database. It requires database creation privileges. Pause schema migrations while backing up.</p>
  <div class="table-responsive"><table class="table align-middle"><thead><tr><th>Created (UTC)</th><th>Status</th><th>Size</th><th>Restore checked (UTC)</th><th>Actions</th></tr></thead><tbody>
  <?php foreach($backups as $b): ?><tr><td><?= $backupEscape($b['created_at']) ?></td><td><?= $backupEscape($b['status']) ?></td><td><?= number_format($b['bytes']/1024,1) ?> KB</td><td><?= $backupEscape($b['error_code']==='verification_failed'?'Verification failed':($b['verified_at']?:'Not checked')) ?></td><td><?php if($b['status']==='complete'): ?><form method="post" action="<?= url('?route=admin/settings&tab=backup') ?>" class="d-flex flex-wrap gap-2"><input type="hidden" name="csrf_token" value="<?= csrf_token() ?>"><input type="hidden" name="setting_category" value="backup"><input type="hidden" name="backup_id" value="<?= $backupEscape($b['id']) ?>"><input type="password" class="form-control form-control-sm" style="max-width:180px" name="current_password" autocomplete="current-password" aria-label="Current password for backup action" placeholder="Current password" required><button class="btn btn-sm btn-outline-primary" name="backup_action" value="download">Download</button><button class="btn btn-sm btn-outline-secondary" name="backup_action" value="verify">Verify restore</button></form><?php endif; ?></td></tr><?php endforeach; ?>
  <?php if(!$backups): ?><tr><td colspan="5" class="text-muted">No backups yet.</td></tr><?php endif; ?></tbody></table></div>
  <?php endif; ?>
 </div>
</div>
