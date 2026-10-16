<?php
require_once __DIR__.'/../../models/CashShift.php';
if(CashShift::posIntegrated()){require __DIR__.'/pos_drawer_panel.php';return;}
if (CashShift::ready()):
$shiftBannerUser=(int)$_SESSION['user']['id'];
$shiftBanner=RecipeInventory::rows('SELECT s.id,s.branch_id,r.name register_name FROM cash_shifts s JOIN cash_registers r ON r.id=s.register_id WHERE s.active_cashier=?','i',[$shiftBannerUser]);
$shiftBannerCan=false; foreach(CashShift::permissions() as $p) if(User::has_permission($shiftBannerUser,$p)) $shiftBannerCan=true;
?>
<div style="padding:10px 20px;background:#eff5ff;border-bottom:1px solid #dce7f8;font-size:12px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-shrink:0;">
<span><?= $shiftBanner?'Active shift #'.(int)$shiftBanner[0]['id'].' · '.htmlspecialchars($shiftBanner[0]['register_name'],ENT_QUOTES,'UTF-8'):'No active cashier shift. Open a shift to track this drawer.' ?></span>
<?php if($shiftBannerCan && $shiftBanner): ?><form method="post" action="<?= url('?route=pos/shifts') ?>"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(),ENT_QUOTES,'UTF-8') ?>"><input type="hidden" name="action" value="shift_select"><input type="hidden" name="shift_id" value="<?= (int)$shiftBanner[0]['id'] ?>"><button type="submit" style="border:0;background:transparent;color:#315dac;font-weight:700;white-space:nowrap;">Manage shift →</button></form><?php elseif($shiftBannerCan): ?><a href="<?= url('?route=pos/shifts') ?>" style="color:#315dac;font-weight:700;white-space:nowrap;">Open shift →</a><?php endif; ?></div>
<?php endif; ?>
