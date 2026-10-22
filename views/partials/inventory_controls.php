<?php
if (User::has_permission($_SESSION['user']['id'],'manage_inventory')):
require_once __DIR__.'/../../models/InventoryControl.php';
$icEscape=static function ($v) { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); };
$icReady=InventoryControl::ready();
$icBranches=Branch::for_current_user();
$icBranch=null;
foreach ($icBranches as $b) if ((int)$b['id']===(int)($_GET['control_branch'] ?? $_SESSION['user']['branch_id'] ?? 0)) $icBranch=(int)$b['id'];
if (!$icBranch && $icBranches) $icBranch=(int)$icBranches[0]['id'];
$icItems=[]; $icReviews=[];
if ($icReady && $icBranch) {
    $icItems=RecipeInventory::rows("SELECT p.*,r.reorder_level,
        (SELECT COALESCE(SUM(quantity),0) FROM product_batches WHERE product_id=p.id AND branch_id=? AND status='active' AND quantity>0 AND (expiry_date IS NULL OR expiry_date>=CURRENT_DATE)) control_stock
        FROM products p LEFT JOIN inventory_reorder_levels r ON r.product_id=p.id AND r.branch_id=?
        WHERE (p.branch_id=? OR p.branch_id IS NULL) AND p.status='active' AND p.track_stock=1 AND p.inventory_type NOT IN ('non-stock','unlimited') AND p.product_type NOT IN ('variant','service') AND (p.product_type<>'recipe' OR p.recipe_mode='prepared_batch') ORDER BY p.name,p.id",'iii',[$icBranch,$icBranch,$icBranch]);
    $icReviews=RecipeInventory::rows('SELECT r.*,u.name actor_name,v.name resolver_name FROM inventory_reviews r LEFT JOIN users u ON u.id=r.created_by LEFT JOIN users v ON v.id=r.resolved_by WHERE r.branch_id=? ORDER BY (r.status=\'open\') DESC,r.id DESC LIMIT 100','i',[$icBranch]);
}
?>
<div class="modal fade inventory-workspace" id="inventoryControlModal" tabindex="-1" aria-labelledby="inventoryControlTitle" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content"><div class="modal-header"><div><span class="ic-eyebrow">BRANCH INVENTORY</span><h2 class="modal-title fs-4" id="inventoryControlTitle">Inventory controls</h2><p class="small text-muted mb-0">Stock counts, discrepancies, losses & reorder levels</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">
<?php if (!$icReady): ?>
<p class="alert alert-warning mt-3">Inventory controls setup is pending. Apply the inventory controls migration before using this section.</p>
<?php elseif (!$icBranch): ?>
<p class="mt-3">No active branch is available.</p>
<?php else: ?>
<form method="get" action="<?= url('index.php') ?>" class="d-flex gap-2 align-items-end my-3">
<input type="hidden" name="route" value="inventory/products"><input type="hidden" name="controls" value="1">
<input type="hidden" name="control_tab" value="inventory_count">
<label>Branch<select name="control_branch" class="form-select"><?php foreach ($icBranches as $b): ?><option value="<?= (int)$b['id'] ?>" <?= (int)$b['id']===$icBranch?'selected':'' ?>><?= $icEscape($b['name']) ?></option><?php endforeach; ?></select></label>
<button class="btn btn-outline-secondary"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">filter_alt</span>Show branch</button></form>
<p class="small text-muted">Count usable on-hand stock in the item's base unit. Record expired stock separately. Saving a count keeps stock unchanged until you reconcile its discrepancy. Count corrections do not consume recipe ingredients.</p>
<ul class="nav nav-pills ic-tabs mb-4" role="tablist" aria-label="Inventory operations">
<?php foreach (['inventory_count'=>['fact_check','Physical count'],'inventory_loss'=>['broken_image','Record loss'],'inventory_reorder'=>['notifications_active','Reorder levels'],'inventory_history'=>['history','Discrepancies & history']] as $tab=>$meta): ?>
<li class="nav-item" role="presentation"><button type="button" class="nav-link <?= $tab==='inventory_count'?'active':'' ?>" id="<?= $tab ?>-tab" data-bs-toggle="pill" data-bs-target="#<?= $tab ?>-pane" role="tab" aria-controls="<?= $tab ?>-pane" aria-selected="<?= $tab==='inventory_count'?'true':'false' ?>"><span class="material-symbols-outlined" aria-hidden="true"><?= $meta[0] ?></span><?= $meta[1] ?></button></li>
<?php endforeach; ?></ul><div class="tab-content">
<?php foreach (['inventory_count'=>'Record physical count','inventory_loss'=>'Record damaged, expired or lost stock','inventory_reorder'=>'Set reorder level'] as $icAction=>$icLabel): ?>
<div class="tab-pane fade <?= $icAction==='inventory_count'?'show active':'' ?>" id="<?= $icAction ?>-pane" role="tabpanel" aria-labelledby="<?= $icAction ?>-tab" tabindex="0"><h3 class="h6 mb-3"><?= $icLabel ?></h3>
<form method="post" action="<?= url('?route=inventory/products') ?>" class="row g-2 mt-1">
<input type="hidden" name="csrf_token" value="<?= $icEscape($_SESSION['csrf_token']) ?>">
<input type="hidden" name="action" value="<?= $icAction ?>"><input type="hidden" name="control_branch" value="<?= $icBranch ?>">
<input type="hidden" name="request_key" value="<?= bin2hex(random_bytes(16)) ?>">
<div class="col-md-6"><label class="d-block">Item<select name="item_id" class="form-select" required><option value="">Select item</option><?php foreach ($icItems as $p): ?><option value="<?= (int)$p['id'] ?>"><?= $icEscape($p['name'].' (#'.$p['id'].') — '.$p['unit_type'].'; stock '.$p['control_stock'].'; minimum '.$p['low_stock_threshold'].'; reorder '.($p['reorder_level'] ?? 'not set')) ?></option><?php endforeach; ?></select></label></div>
<div class="col-md-3"><label class="d-block"><?= $icAction==='inventory_count'?'Actual usable quantity':($icAction==='inventory_loss'?'Quantity to write off':'Reorder level') ?><input type="number" name="quantity" class="form-control" step="0.0001" min="<?= $icAction==='inventory_loss'?'0.0001':'0' ?>" required></label></div>
<?php if ($icAction==='inventory_loss'): ?><div class="col-md-3"><label class="d-block">Loss category<select name="loss_kind" class="form-select"><option value="damaged">Damaged</option><option value="expired">Expired</option><option value="lost">Lost</option></select></label></div><?php endif; ?>
<?php if ($icAction!=='inventory_reorder'): ?><div class="col-12"><label class="d-block">Reason / count reference<textarea name="notes" class="form-control" required maxlength="2000"></textarea></label></div><?php endif; ?>
<div class="col-12"><button type="submit" class="btn btn-primary"><?= $icAction==='inventory_loss'?'Record loss and deduct stock':'Save' ?></button></div>
</form>
<?php if ($icAction==='inventory_reorder'): ?>
<div class="ic-reorder-list mt-4"><h4 class="h6">Items at or below reorder level</h4><ul class="mb-0">
<?php $icDue=0; foreach ($icItems as $p): if ($p['reorder_level']===null || (float)$p['control_stock']>(float)$p['reorder_level']) continue; $icDue++; ?><li><?= $icEscape($p['name'].': '.$p['control_stock'].' '.$p['unit_type'].' / reorder '.$p['reorder_level']) ?></li><?php endforeach; ?></ul>
<?php if (!$icDue): ?><p class="small text-muted mb-0">No items currently meet a configured reorder level.</p><?php endif; ?></div>
<?php endif; ?></div>
<?php endforeach; ?>
<div class="tab-pane fade" id="inventory_history-pane" role="tabpanel" aria-labelledby="inventory_history-tab" tabindex="0">
<h3 class="h6 mt-3">Open discrepancies and latest records (up to 100)</h3>
<?php foreach ($icReviews as $r): ?>
<article class="ic-record p-3 mb-3">
<strong>#<?= (int)$r['id'] ?> <?= $icEscape($r['product_name']) ?> · <?= $icEscape(ucfirst($r['kind'])) ?> · <?= $icEscape($r['status']) ?></strong>
<p class="small mb-1"><?php if ($r['kind']==='count'): ?>Expected <?= $icEscape($r['expected_quantity']) ?> / counted <?= $icEscape($r['actual_quantity']) ?> / difference <?= $icEscape($r['difference']) ?><?php else: ?>Loss quantity <?= $icEscape(ltrim($r['difference'],'-')) ?><?php endif; ?> <?= $icEscape($r['unit']) ?></p>
<p class="small mb-1"><?= $icEscape($r['notes']) ?> · <?= $icEscape($r['actor_name']) ?> · <?= $icEscape($r['created_at']) ?></p>
<?php if ($r['resolved_at']): ?><p class="small mb-0">Closed by <?= $icEscape($r['resolver_name']) ?> at <?= $icEscape($r['resolved_at']) ?>. <?= $icEscape($r['resolution_notes']) ?><?php if ($r['operation_id']): ?> · Stock operation #<?= (int)$r['operation_id'] ?><?php endif; ?></p><?php endif; ?>
<?php if ($r['status']==='open'): ?>
<form method="post" action="<?= url('?route=inventory/products') ?>" class="mt-2">
<input type="hidden" name="csrf_token" value="<?= $icEscape($_SESSION['csrf_token']) ?>"><input type="hidden" name="action" value="inventory_resolve"><input type="hidden" name="review_id" value="<?= (int)$r['id'] ?>"><input type="hidden" name="control_branch" value="<?= $icBranch ?>">
<label class="d-block">Resolution reason<input name="notes" class="form-control mb-2" required maxlength="2000"></label>
<button type="submit" name="resolution" value="reconcile" class="btn btn-outline-primary"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">fact_check</span>Reconcile stock to count</button>
<button type="submit" name="resolution" value="dismiss" class="btn btn-outline-secondary"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">close</span>Dismiss without changing stock</button>
</form><?php endif; ?>
</article><?php endforeach; ?>
<?php if (!$icReviews): ?><p>No counts or categorized losses recorded for this branch.</p><?php endif; ?>
</div></div>
<?php endif; ?></div><div class="modal-footer"><span class="small text-muted me-auto">Changes are recorded in stock history.</span><button type="button" class="btn btn-light" data-bs-dismiss="modal"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">close</span>Close</button></div></div></div></div>
<?php endif; ?>
