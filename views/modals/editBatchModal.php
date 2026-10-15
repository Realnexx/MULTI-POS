<div class="modal fade" id="editBatchModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <span class="material-symbols-outlined">edit_square</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold outfit-font text-dark mb-0">Edit Batch</h5>
                        <p class="text-muted smaller mb-0">Update batch specifications.</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <form id="editBatchForm">
                    <input type="hidden" name="batch_id" id="edit_batch_id">

                    <div class="mb-3">
                        <label class="form-label text-muted smaller fw-bold mb-1">Batch Information</label>
                        <div class="p-2 bg-light rounded-3 border">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="smaller text-muted">Batch #:</span>
                                <span id="edit_batch_number_text" class="smaller fw-bold text-dark">#0000</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="smaller text-muted">Branch:</span>
                                <span id="edit_batch_branch_text" class="smaller fw-bold text-primary">Global</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_batch_quantity"
                            class="form-label text-muted smaller fw-bold mb-1">Quantity</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3">
                                <span class="material-symbols-outlined fs-6 text-muted">inventory_2</span>
                            </span>
                            <input type="number" class="form-control border-start-0 rounded-end-3 fw-bold"
                                id="edit_batch_quantity" name="quantity" required min="0" step="0.0001" readonly placeholder="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_batch_cost" class="form-label text-muted smaller fw-bold mb-1">Unit Cost
                            (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3">
                                <span class="material-symbols-outlined fs-6 text-muted">payments</span>
                            </span>
                            <input type="number" step="0.01" class="form-control border-start-0 rounded-end-3 fw-bold"
                                id="edit_batch_cost" name="cost_price" required min="0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="edit_batch_selling" class="form-label text-muted smaller fw-bold mb-1">Selling Price
                            (₱)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0 rounded-start-3">
                                <span class="material-symbols-outlined fs-6 text-muted">sell</span>
                            </span>
                            <input type="number" step="0.01" class="form-control border-start-0 rounded-end-3 fw-bold"
                                id="edit_batch_selling" name="selling_price" min="0" placeholder="0.00">
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button"
                            class="btn btn-outline-light-gray flex-grow-1 rounded-pill fw-bold text-dark"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-warning flex-grow-1 rounded-pill fw-bold text-dark shadow-sm">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>