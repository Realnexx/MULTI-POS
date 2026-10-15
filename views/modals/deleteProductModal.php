<div class="modal fade" id="deleteProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-trash-can fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold outfit-font text-dark mb-0">Confirm Deletion</h5>
                        <p class="text-muted smaller mb-0">Protective deletion protocol.</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/products') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="product_id" id="delete_prod_id">

                <div class="modal-body p-4 text-center">
                    <i class="fa-solid fa-triangle-exclamation fs-1 text-danger mb-3 d-block"></i>
                    <h5 class="fw-bold text-dark mb-2">Delete <span id="delete_prod_name"></span>?</h5>
                    <p class="text-muted small mb-0">This action will remove the product from the catalog. Related
                        sales
                        items and inventory logs will remain intact for reporting.</p>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">Delete
                        Product</button>
                </div>
            </form>
        </div>
    </div>
</div>