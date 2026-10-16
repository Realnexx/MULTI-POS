<?php
$title='Inter-Branch Stock Transfers';
$esc=static function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');};
$qty=static function($v){return number_format((float)$v,2,'.',',');};
$actor=(int)$_SESSION['user']['id'];$context=User::access_context($actor);
$inBranch=static function($branch)use($context){return $context['global'] || (int)$context['branch_id']===(int)$branch;};
$canRequest=User::has_permission($actor,'transfer_products');
ob_start();
?>
<style>
.transfer-request-card{border:1px solid #e5eaf2!important;border-radius:20px!important;overflow:hidden;box-shadow:0 6px 24px rgba(25,45,80,.04)}
.transfer-request-card>summary{padding:22px 24px;background:#f8faff;font-size:16px;color:#243656;display:flex;align-items:center;gap:12px;cursor:pointer;list-style:none}
.transfer-request-card>summary::-webkit-details-marker{display:none}
.transfer-request-card>summary:after{content:'expand_more';font-family:'Material Symbols Outlined';margin-left:auto;font-size:24px}
.transfer-request-card[open]>summary:after{content:'expand_less'}
#stock-transfer-request{padding:0 24px 24px}.transfer-heading-icon{background:#e6efff;color:#2876df;border-radius:12px;padding:10px}
.transfer-item-card{padding:18px;margin-bottom:14px;border:1px solid #e1e8f2;border-radius:16px;background:#fff;box-shadow:0 2px 8px rgba(25,45,80,.03)}
.transfer-item-label{font-size:11px;font-weight:700;letter-spacing:1px;color:#73839d}
#stock-transfer-request .form-label{font-size:13px;font-weight:600;color:#455570}
#stock-transfer-request .form-control,#stock-transfer-request .form-select{border-radius:10px;min-height:44px;border-color:#dce4ef}
.transfer-branches{padding:18px;background:#f7f9fc;border-radius:14px;margin:0 0 20px!important}
.transfer-policy{border:0;background:#edf5ff;color:#335c8a;border-radius:12px;padding:14px 16px;line-height:1.6}.transfer-notice-text,[data-transfer-form] .invalid-feedback{color:#946200!important}[data-transfer-form] .is-invalid{border-color:#dca52d!important;background-color:#fffbef!important;background-image:none!important}[data-transfer-form] .is-invalid:focus{box-shadow:0 0 0 .2rem rgba(220,165,45,.18)!important}
.transfer-submit-footer{border-top:1px solid #edf0f5;padding-top:20px;margin-top:20px;display:flex;justify-content:flex-end}
@media(max-width:575px){#stock-transfer-request{padding:0 14px 18px}.transfer-request-card>summary{padding:18px 14px}.transfer-item-card{padding:14px}.transfer-submit-footer .btn{width:100%}}
</style>
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h4 mb-0">Inter-Branch Stock Transfers</h1><?php if(User::has_permission($actor,'view_product')): ?><a href="<?= url('?route=inventory/products') ?>" class="btn btn-outline-primary">Products</a><?php endif; ?></div>
<p class="text-muted">Request stock, confirm dispatch, then confirm what arrived. Products and recipe ownership stay with their existing branches.</p>
<?php if(!$transferReady): ?><div class="alert alert-warning">Stock transfer setup is pending. Apply the stock transfer migration before creating requests.</div><?php else: ?>
<?php if($canRequest): ?>
<details class="card transfer-request-card mb-4" <?= !$selectedTransfer ? 'open' : '' ?>><summary class="fw-bold"><span class="material-symbols-outlined transfer-heading-icon" aria-hidden="true">local_shipping</span><span>New stock transfer request<span class="d-block small fw-normal text-muted mt-1">Move the same product between branches</span></span></summary>
<form id="stock-transfer-request" class="mt-3" novalidate data-transfer-form>
<input type="hidden" name="action" value="stock_transfer_create"><input type="hidden" name="request_key" value="<?= bin2hex(random_bytes(16)) ?>">
<div class="alert transfer-policy small">Approval is <?= $approvalRequired ? 'required. Another authorized approver must approve before dispatch.' : 'not required for new requests.' ?> Stock is deducted at dispatch and added at receiving. Requests do not reserve stock.</div>
<?php $initialSource='';foreach($sourceBranches as $b) if(!$context['global'] || (int)($_GET['source'] ?? 0)===(int)$b['id']) $initialSource=$b['name']; ?>
<div class="row g-3 transfer-branches"><div class="col-md-6"><label class="form-label" for="transfer-source-branch">From branch</label><input id="transfer-source-branch" class="form-control" type="search" list="transfer-source-branches" placeholder="Search source branch…" autocomplete="off" value="<?= $esc($initialSource) ?>" required><datalist id="transfer-source-branches"></datalist><input id="transfer-source-branch-id" name="source_branch_id" type="hidden"></div>
<div class="col-md-6"><label class="form-label" for="transfer-target-branch">To branch</label><input id="transfer-target-branch" class="form-control" type="search" list="transfer-target-branches" placeholder="Search destination branch…" autocomplete="off" required><datalist id="transfer-target-branches"></datalist><input id="transfer-target-branch-id" name="destination_branch_id" type="hidden"></div></div>
<p class="small text-muted mt-3">Search for a product below. Destination suggestions include only the same product name, type and unit. If it is missing, create the same product at the destination first.</p>
<div id="stock-transfer-rows" data-product="<?= (int)($_GET['product_id'] ?? 0) ?>"></div>
<button type="button" id="stock-transfer-add-item" class="btn btn-outline-primary btn-sm mt-2">Add another item</button>
<label class="form-label d-block mt-3">Notes<textarea name="notes" class="form-control" maxlength="4000" rows="2"></textarea></label>
<div data-transfer-feedback class="alert alert-warning d-none" role="alert" tabindex="-1"></div>
<div class="transfer-submit-footer"><button type="submit" class="btn btn-primary px-4 py-2"><span class="material-symbols-outlined me-2 align-middle" style="font-size:18px" aria-hidden="true">send</span>Create transfer request</button></div>
</form></details>
<?php endif; ?>
<?php if($selectedTransfer): $t=$selectedTransfer; ?>
<section class="card p-3 mb-4" id="stock-transfer-detail"><h2 class="h5">Transfer #<?= (int)$t['id'] ?> <span class="badge bg-primary"><?= $esc(str_replace('_',' ',$t['status'])) ?></span></h2>
<p>From <?= $esc(array_column($transferBranches,'name','id')[$t['source_branch_id']] ?? 'branch #'.$t['source_branch_id']) ?> to <?= $esc(array_column($transferBranches,'name','id')[$t['destination_branch_id']] ?? 'branch #'.$t['destination_branch_id']) ?></p>
<p class="small text-muted"><?= nl2br($esc($t['notes'])) ?></p>
<div class="table-responsive"><table class="table"><thead><tr><th>Source item → destination item</th><th>Unit</th><th>Requested</th><th>Dispatched</th><th>Received</th><th>Difference</th><th>Reason</th></tr></thead><tbody><?php foreach($t['items'] as $item): ?><tr><td><?= $esc($item['source_name']) ?> → <?= $esc($item['destination_name']) ?></td><td><?= $esc($item['unit']) ?></td><td><?= $qty($item['requested_quantity']) ?></td><td><?= $qty($item['dispatched_quantity']) ?></td><td><?= $item['received_quantity']===null ? '—' : $qty($item['received_quantity']) ?></td><td><?= $item['discrepancy_quantity']===null ? '—' : $qty($item['discrepancy_quantity']) ?></td><td><?= $esc($item['discrepancy_reason']) ?></td></tr><?php endforeach; ?></tbody></table></div>
<?php
$actions=[];
if($inBranch($t['source_branch_id'])) {
 if($t['status']==='pending_approval' && User::has_permission($actor,'approve_stock_transfers')) {
  if((int)$t['requested_by']!==$actor) $actions['approve']='Approve request';
  $actions['reject']='Reject request';
 }
 if($canRequest && in_array($t['status'],['pending_approval','approved'],true)) $actions['cancel']='Cancel request';
 if($canRequest && $t['status']==='approved') $actions['dispatch']='Confirm dispatch';
}
foreach($actions as $action=>$label): ?>
<form data-transfer-form class="border rounded p-3 my-2" novalidate><input type="hidden" name="action" value="stock_transfer_<?= $action ?>"><input type="hidden" name="transfer_id" value="<?= (int)$t['id'] ?>"><input type="hidden" name="request_key" value="<?= bin2hex(random_bytes(16)) ?>">
<label class="form-label d-block"><?= in_array($action,['reject','cancel'],true) ? 'Reason (required)' : 'Notes' ?><textarea name="notes" class="form-control" maxlength="4000" <?= in_array($action,['reject','cancel'],true) ? 'required' : '' ?>></textarea></label>
<?php if($action==='dispatch'): ?><p class="small">Confirm only when these quantities leave the source branch. This deducts stock and marks it in transit.</p><?php endif; ?>
<div data-transfer-feedback class="alert alert-warning d-none" role="alert" tabindex="-1"></div><button type="submit" class="btn <?= in_array($action,['cancel','reject'],true) ? 'btn-outline-danger' : 'btn-primary' ?>"><?= $label ?></button></form>
<?php endforeach; ?>
<?php if($t['status']==='pending_approval' && (int)$t['requested_by']===$actor): ?><p class="small text-muted">Another authorized approver must approve this request.</p><?php endif; ?>
<?php if($t['status']==='dispatched' && $inBranch($t['destination_branch_id']) && User::has_permission($actor,'receive_stock_transfers')): ?>
<form data-transfer-form novalidate class="border rounded p-3 mt-3"><h3 class="h6">Confirm receiving</h3><input type="hidden" name="action" value="stock_transfer_receive"><input type="hidden" name="transfer_id" value="<?= (int)$t['id'] ?>"><input type="hidden" name="request_key" value="<?= bin2hex(random_bytes(16)) ?>">
<p class="small">Enter actual quantities counted. Missing and excess stock require a reason. Confirming adds the actual quantities to destination stock, including any documented excess.</p>
<?php foreach($t['items'] as $item): ?><div class="row g-2 mb-3"><div class="col-md-5"><label class="form-label d-block"><?= $esc($item['destination_name']) ?> (<?= $esc($item['unit']) ?>)<input type="number" name="received[<?= (int)$item['id'] ?>][quantity]" class="form-control" min="0" step="<?= in_array(InventoryQuantity::unit($item['unit']),['g','kg','ml','l'],true) ? '0.0001' : '1' ?>" required placeholder="Actual quantity received"></label></div><div class="col-md-7"><label class="form-label d-block">Discrepancy reason<input name="received[<?= (int)$item['id'] ?>][reason]" class="form-control" maxlength="2000" data-dispatched="<?= $esc($item['dispatched_quantity']) ?>" placeholder="Required if actual differs from dispatched"></label></div></div><?php endforeach; ?>
<div data-transfer-feedback class="alert alert-warning d-none" role="alert" tabindex="-1"></div><button type="submit" class="btn btn-primary">Confirm received stock</button></form>
<?php endif; ?>
<h3 class="h6 mt-4">Transfer history and audit trail</h3><ul class="list-group"><?php foreach($t['events'] as $event): $details=json_decode($event['details'],true); ?><li class="list-group-item"><strong><?= $esc(ucfirst($event['action'])) ?></strong> — <?= $esc($event['actor_name']) ?> · <?= $esc($event['created_at']) ?><div class="small text-muted"><?= $esc($details['notes'] ?? '') ?></div><?php foreach($details['received'] ?? [] as $entry): ?><div class="small">Counted <?= $qty($entry['quantity']) ?> <?= $esc($entry['reason'] ?? '') ?></div><?php endforeach; ?></li><?php endforeach; ?></ul>
</section>
<?php endif; ?>
<section class="card p-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0">Pending and completed transfers</h2><label>Filter<select id="stock-transfer-filter" class="form-select"><option value="all">All transfers</option><option value="pending">Pending / in transit</option><option value="completed">Completed</option><option value="discrepancy">With discrepancies</option><option value="closed">Rejected / cancelled</option></select></label></div><div class="table-responsive"><table class="table"><thead><tr><th>Transfer</th><th>From</th><th>To</th><th>Status</th><th>Requested by</th><th>Date</th></tr></thead><tbody id="stock-transfer-list"><?php foreach($transfers as $transfer): ?><tr data-status="<?= $esc($transfer['status']) ?>"><td><a href="<?= url('?route=inventory/transfers&id='.(int)$transfer['id']) ?>">#<?= (int)$transfer['id'] ?></a></td><td><?= $esc($transfer['source_branch']) ?></td><td><?= $esc($transfer['destination_branch']) ?></td><td><?= $esc(str_replace('_',' ',$transfer['status'])) ?></td><td><?= $esc($transfer['requester']) ?></td><td><?= $esc($transfer['created_at']) ?></td></tr><?php endforeach; ?></tbody></table></div><p class="text-muted small" id="stock-transfer-empty" <?= $transfers ? 'hidden' : '' ?>>No transfers match this view.</p></section>
<?php endif; ?>
<script type="application/json" id="stock-transfer-branches"><?= json_encode(["source"=>$sourceBranches,"destination"=>$transferBranches],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script type="application/json" id="stock-transfer-products"><?= json_encode($transferProducts,JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
<script src="<?= url('assets/js/stock-transfers.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/stock-transfers.js') ?>"></script>
<?php $content=ob_get_clean();require __DIR__.'/../layouts/admin.php'; ?>
