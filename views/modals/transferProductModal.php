<div class="modal fade" id="transferProductModal" tabindex="-1" aria-labelledby="transfer-product-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content">
        <div class="modal-header"><h2 id="transfer-product-title" class="modal-title h5">Transfer product to another branch</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <form id="transfer-product-form">
            <div class="modal-body">
                <input type="hidden" name="product_id"><input type="hidden" name="source_branch_id"><input type="hidden" name="request_key">
                <p id="transfer-product-description" class="fw-bold"></p>
                <p class="text-muted small">Moves this product and all its remaining stock to the destination. Batch costs, expiry dates and transaction history are preserved. This does not create additional stock.</p>
                <label class="form-label" for="transfer-destination">Destination branch</label>
                <select id="transfer-destination" class="form-select" name="destination_branch_id" required><option value="">Choose a branch</option><?php foreach ($transferBranches as $transfer_branch): ?><option value="<?= (int)$transfer_branch['id'] ?>"><?= htmlspecialchars($transfer_branch['name'],ENT_QUOTES,'UTF-8') ?></option><?php endforeach; ?></select>
                <p id="transfer-product-feedback" class="small mt-3 mb-0" role="status" aria-live="polite"></p>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Transfer product &amp; stock</button></div>
        </form>
    </div></div>
</div>
