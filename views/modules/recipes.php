<?php
$title = 'Recipe Management';
$esc = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$can_edit = User::has_permission($_SESSION['user']['id'],'edit_product');
$can_create = $can_edit && User::has_permission($_SESSION['user']['id'],'add_product');
$global_access = User::has_all_branch_access($_SESSION['user']['id']);
if ($selected && $selected['branch_id'] === null && !$global_access) $can_edit = false;
$scope_editable = !$selected || (!$selected['is_sellable'] && !$selected['track_stock']);
$scope_value = (string)($form_data['branch_id'] ?? '');
if (!$selected && $scope_value === '' && !$global_access) $scope_value = (string)($_SESSION['user']['branch_id'] ?? '');
$request_key = $form_data['request_key'] ?? bin2hex(random_bytes(16));
ob_start();
?>
<link rel="stylesheet" href="<?= url('assets/css/recipe-workspace.css?v='.filemtime(__DIR__.'/../../assets/css/recipe-workspace.css')) ?>">
<section id="recipe-management" class="recipe-library-layout">

    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div><span class="recipe-eyebrow">INVENTORY WORKSPACE</span><h1 class="h3 fw-bold"><i class="ti ti-notebook" aria-hidden="true"></i> Recipe Management</h1><p class="text-muted mb-0">Configure ingredients for any product: food, drinks, bundles or other prepared items.</p></div>
        <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="<?= url('?route=inventory/products') ?>"><i class="ti ti-box me-2" aria-hidden="true"></i>Products &amp; stock</a><?php if ($can_create): ?><a class="btn btn-primary" href="<?= url('?route=inventory/recipes&create=1') ?>" <?= !$selected?'data-bs-toggle="modal" data-bs-target="#recipe-editor-modal"':'' ?>><i class="ti ti-plus me-2" aria-hidden="true"></i>New recipe</a><?php endif; ?></div>
    </div>
    <div class="recipe-workspace-stats"><?php foreach([['Saved recipes',count($recipes),'notebook'],['Available ingredients',count($ingredients),'box'],['Shared recipes',count(array_filter($recipes,function($r){return $r['branch_id']===null;})),'building-community']] as $stat): ?><div class="recipe-stat"><span><i class="ti ti-<?= $stat[2] ?>" aria-hidden="true"></i></span><div><small><?= $stat[0] ?></small><strong><?= $stat[1] ?></strong></div></div><?php endforeach; ?></div>
    <div class="alert alert-info recipe-workspace-notice"><i class="ti ti-info-circle" aria-hidden="true"></i><div><strong>Recipe setup does not change stock.</strong><br>Saving a recipe only saves its ingredients. To consume ingredients, select the recipe in Add New Product and enter Initial Stock, or prepare an additional batch for an existing product.</div></div>
    <div class="row g-4">
        <div class="col-12 recipe-library-column">
            <div class="card"><div class="card-body">
                <div class="recipe-panel-heading"><span class="recipe-panel-icon"><i class="ti ti-books" aria-hidden="true"></i></span><div><h2 class="h5">Recipe library</h2><p>Select a recipe to view its ingredients.</p></div></div>
                <label class="form-label" for="recipe-search">Find a recipe</label><input class="form-control mb-3" id="recipe-search" type="search" placeholder="Search recipe name">
                <div id="recipe-list" class="list-group list-group-flush" >
                    <?php foreach ($recipes as $item): ?>
                    <a class="list-group-item list-group-item-action <?= (int)$item['id']===$selected_id?'active':'' ?>" href="<?= url('?route=inventory/recipes&id='.(int)$item['id']) ?>" data-recipe-name="<?= $esc($item['name']) ?>">
                        <span class="recipe-list-icon"><i class="ti ti-chef-hat" aria-hidden="true"></i></span><strong class="d-block"><?= $esc($item['name']) ?></strong><small><?= (int)$item['ingredient_count'] ?> ingredients · <?= $esc($item['branch_name'] ?: 'Global (All branches)') ?><?= !$item['is_sellable']?' · Definition only':'' ?></small>
                    </a>
                    <?php endforeach; ?>
                    <?php if (!$recipes): ?><p class="text-muted my-2">Create your first recipe, then choose it when adding a product.</p><?php endif; ?>
                </div>
                <p id="recipe-search-empty" class="text-muted d-none mt-3">No matching recipes.</p>
            </div></div>
        </div>
        <div class="modal fade" id="recipe-editor-modal" tabindex="-1" aria-labelledby="recipe-editor-title" aria-hidden="true"><div class="modal-dialog modal-xl modal-dialog-centered"><div class="modal-content"><div class="modal-header"><div><h2 class="modal-title h5" id="recipe-editor-title"><i class="ti ti-notebook me-2" aria-hidden="true"></i><?= $selected?'Configure recipe':'Create a recipe' ?></h2><p class="text-muted small mb-0 mt-1">Define the components needed for one finished unit.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body recipe-editor-column"><div class="recipe-editor-body">
                <form method="post" action="<?= url('?route=inventory/recipes'.($selected_id?'&id='.$selected_id:'')) ?>" id="recipe-definition-form">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="recipe_id" value="<?= $selected_id ?>">
                    <input type="hidden" name="request_key" value="<?= $esc($request_key) ?>">
                    <fieldset <?= (!$can_edit || (!$selected && !$can_create))?'disabled':'' ?>>
                        <div class="recipe-modal-layout"><div class="recipe-modal-details"><h3 class="recipe-section-title"><span>01</span> Recipe details</h3>
                        <label for="recipe-name" class="form-label">Recipe name</label><input class="form-control mb-3" id="recipe-name" name="name" maxlength="255" required value="<?= $esc($form_data['name']) ?>" placeholder="e.g. Product kit, prepared item or bundle">
                        <label for="recipe-description" class="form-label">Description</label><textarea class="form-control mb-3" id="recipe-description" name="description" rows="2"><?= $esc($form_data['description']) ?></textarea>
                        <?php if (!$selected): ?>
                        <div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label" for="recipe-category">Category</label><select required class="form-select" name="category_id" id="recipe-category"><option value="">Choose category</option><?php foreach ($categories as $category): ?><option value="<?= (int)$category['id'] ?>" <?= (int)$form_data['category_id']===(int)$category['id']?'selected':'' ?>><?= $esc($category['name']) ?></option><?php endforeach; ?></select></div>
                        </div>
                        <?php else: ?><input type="hidden" name="category_id" value="<?= (int)$selected['category_id'] ?>"><?php endif; ?>
                        <div class="mb-3"><label class="form-label" for="recipe-branch">Branch</label>
                        <?php if ($scope_editable): ?>
                        <select required class="form-select" name="branch_id" id="recipe-branch"><option value="">Choose branch</option>
                            <?php if ($global_access): ?><option value="global" <?= $scope_value==='global'?'selected':'' ?>>Global (All branches)</option><?php endif; ?>
                            <?php foreach ($branches as $branch): ?><option value="<?= (int)$branch['id'] ?>" <?= $scope_value===(string)$branch['id']?'selected':'' ?>><?= $esc($branch['name']) ?></option><?php endforeach; ?>
                        </select>
                        <?php else: ?><input type="hidden" name="branch_id" id="recipe-branch" value="<?= $esc($scope_value) ?>"><p class="form-control-plaintext"><?= $esc($selected['branch_name'] ?: 'Global (All branches)') ?></p><?php endif; ?>
                        <p id="recipe-branch-help" class="small text-muted mt-2 mb-0">Choose a branch to see its ingredients and shared ingredients. Global recipes use shared ingredients and are available to every branch; each branch keeps its own stock.</p></div>
                        </div><div class="recipe-modal-ingredients">
                        <div class="recipe-ingredients-heading d-flex flex-wrap justify-content-between gap-2 align-items-center mb-2"><h3 class="recipe-section-title"><span>02</span> Ingredients per unit</h3><button type="button" class="btn btn-sm btn-outline-primary" id="recipe-add-row"><i class="ti ti-plus me-1" aria-hidden="true"></i>Add ingredient</button></div>
                        <p class="small text-muted">Choose any stocked ingredient and its quantity/unit. For an offer or bundle, configure everything included in one offer.</p>
                        <div id="recipe-definition-rows"></div>
                        </div><div class="recipe-preview-panel"><h3 class="recipe-section-title"><span>03</span> Requirements preview</h3>
                            <label for="recipe-yield-preview" class="form-label">Preview requirements for</label><input id="recipe-yield-preview" type="number" value="1" min="1" max="100000" step="1" class="form-control mb-2" style="max-width:180px" aria-label="Stock units to preview">
                            <div id="recipe-definition-preview" class="small" aria-live="polite"></div>
                            <p class="small text-muted mb-0 mt-2">Preview only. Saving does not add or deduct inventory.</p>
                        </div>
                        </div><div class="recipe-save-bar"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button><span><i class="ti ti-shield-check me-1" aria-hidden="true"></i>Stock stays unchanged until preparation.</span><button class="btn btn-primary" type="submit" <?= !$ingredients?'disabled':'' ?>><i class="ti ti-device-floppy me-2" aria-hidden="true"></i>Save recipe</button></div>
                        <?php if (!$ingredients): ?><p class="text-danger mt-2">Add Raw Ingredient products in Products &amp; Stock before configuring a recipe.</p><?php endif; ?>
                    </fieldset>
                </form>
            </div></div>
        </div></div></div>
    </div>
    <script type="application/json" id="recipe-definition-data"><?= json_encode(['ingredients'=>$ingredients,'rows'=>$form_rows],JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?></script>
</section>
<script src="<?= url('assets/js/recipe-management.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/recipe-management.js') ?>" defer></script>
<?php if($selected || isset($_GET['create']) || ($_SERVER['REQUEST_METHOD']??'GET')==='POST'): ?><script>document.addEventListener('DOMContentLoaded',function(){bootstrap.Modal.getOrCreateInstance(document.getElementById('recipe-editor-modal')).show();});</script><?php endif; ?>
<?php $content=ob_get_clean(); require __DIR__.'/../layouts/admin.php'; ?>
