<link rel="stylesheet" href="<?= url('assets/css/product-transfer-modal.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/product-transfer-modal.css') ?>">
<div class="modal fade" id="transferProductModal" tabindex="-1" aria-labelledby="transfer-product-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><span class="ptm-header-icon material-symbols-outlined" aria-hidden="true">move_up</span><div><h2 id="transfer-product-title" class="modal-title h5">Transfer product to another branch</h2><p>Move inventory between your locations.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="transfer-product-form">
            <div class="modal-body">
                <input type="hidden" name="product_id"><input type="hidden" name="source_branch_id"><input type="hidden" name="request_key">
                <div class="ptm-product"><span class="material-symbols-outlined" aria-hidden="true">inventory_2</span><div><small>PRODUCT TO TRANSFER</small><p id="transfer-product-description" class="fw-bold"></p></div></div>
                <p class="ptm-explanation">Moves this product, its recipe ingredients and add-on ingredients to the destination. All remaining batches for these product records are consolidated from their source branches. Batch costs, expiry dates and history are preserved. Ingredients still needed by other branches must be resolved first. Separate product records with similar names are not merged.</p>
                <label class="form-label" for="transfer-destination">Destination branch</label>
                <select id="transfer-destination" class="form-select" name="destination_branch_id" required><option value="">Choose a branch</option><?php foreach ($transferBranches as $transfer_branch): ?><option value="<?= (int)$transfer_branch['id'] ?>"><?= htmlspecialchars($transfer_branch['name'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
                <p id="transfer-product-feedback" class="ptm-feedback" role="status" aria-live="polite" tabindex="-1"></p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">close</span>Cancel</button><button type="submit" class="btn btn-primary"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">move_up</span>Transfer product &amp; stock</button></div>
        </form>
    </div></div>
</div>
