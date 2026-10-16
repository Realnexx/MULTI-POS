<style>
    /* ── Aurora Manual Stock Adjustment Modal Styles ── */
    #stockAdjustmentModal .modal-content {
        border-radius: 22px !important;
        border: 1px solid #e8edf5 !important;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.14), 0 4px 16px rgba(93, 135, 255, 0.06) !important;
        overflow: hidden;
        background: #ffffff;
    }

    #stockAdjustmentModal .adj-modal-accent-bar {
        height: 4px;
        width: 100%;
        background: linear-gradient(90deg, #5d87ff, #49beff);
    }

    #stockAdjustmentModal .modal-header {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    #stockAdjustmentModal .view-modal-btn-close {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: #f1f5f9;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #64748b;
        transition: all 0.2s ease;
        margin-left: auto !important;
        flex-shrink: 0;
        cursor: pointer;
    }
    #stockAdjustmentModal .view-modal-btn-close:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: scale(1.08);
    }

    /* Aurora Form Controls */
    #stockAdjustmentModal .form-control {
        border: 1.5px solid #edf2f7 !important;
        border-radius: 10px !important;
        background-color: #ffffff !important;
        font-size: 0.83rem;
        color: #1e293b;
        padding: 9px 12px;
        transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }
    #stockAdjustmentModal .form-control:focus {
        border-color: #5d87ff !important;
        box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.12) !important;
        outline: none;
    }

    /* Aurora Select Dropdown with Visible Chevron */
    #stockAdjustmentModal .form-select {
        border: 1.5px solid #edf2f7 !important;
        border-radius: 10px !important;
        background-color: #ffffff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.2' d='m3 6 5 5 5-5'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right 0.85rem center !important;
        background-size: 13px 9px !important;
        padding: 9px 2.2rem 9px 12px !important;
        font-size: 0.83rem;
        color: #1e293b;
        cursor: pointer;
        appearance: none;
        -webkit-appearance: none;
        -moz-appearance: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-image 0.2s ease;
    }
    #stockAdjustmentModal .form-select:hover:not(:disabled) {
        border-color: #cbd5e1 !important;
    }
    #stockAdjustmentModal .form-select:focus {
        border-color: #5d87ff !important;
        box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.12) !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%235d87ff' stroke-linecap='round' stroke-linejoin='round' stroke-width='2.2' d='m3 6 5 5 5-5'/%3e%3c/svg%3e") !important;
        outline: none;
    }
    #stockAdjustmentModal .form-select:disabled {
        background-color: #f8fafc !important;
        color: #94a3b8 !important;
        cursor: not-allowed;
    }

    /* Spec Cards */
    .adj-spec-card {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        background: #ffffff;
        padding: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        margin-bottom: 14px;
        transition: border-color 0.2s ease;
    }
    .adj-spec-card:hover {
        border-color: #e2e8f0;
    }
    .adj-card-label {
        font-size: 0.68rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 12px;
    }

    /* Buttons */
    .btn-modal-secondary {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 8px 20px;
        border-radius: 50px;
        transition: all 0.2s ease;
    }
    .btn-modal-secondary:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-1px);
    }
    .btn-modal-primary {
        background: linear-gradient(135deg, #5d87ff 0%, #436fe8 100%);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.82rem;
        padding: 8px 24px;
        border-radius: 50px;
        box-shadow: 0 4px 14px rgba(93, 135, 255, 0.28);
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-modal-primary:hover {
        background: linear-gradient(135deg, #4d77ef 0%, #365ec8 100%);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 20px rgba(93, 135, 255, 0.38);
    }
    .btn-modal-primary:active, .btn-modal-secondary:active {
        transform: scale(0.97);
    }

    /* Search suggestions dropdown item */
    .adj-product-item {
        transition: background-color 0.15s ease;
        cursor: pointer;
    }
    .adj-product-item:hover {
        background-color: #f8fafc !important;
    }

    /* Target product badge styling */
    #stockAdjustmentModal .bg-primary-subtle {
        background-color: #eef2ff !important;
        color: #5d87ff !important;
        border-radius: 50px !important;
        font-weight: 700 !important;
        padding: 4px 10px !important;
        font-size: 0.7rem !important;
        display: inline-block;
    }
</style>

<div class="modal fade" id="stockAdjustmentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 600px;">
        <div class="modal-content border-0">
            <!-- Top Gradient Accent Bar -->
            <div class="adj-modal-accent-bar"></div>

            <form id="stockAdjustmentForm" novalidate>
<input type="hidden" name="request_key" value="<?= bin2hex(random_bytes(16)) ?>">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="adjust_stock">
                <input type="hidden" name="product_id" id="adj_prod_id" required>

                <!-- Modal Header -->
                <div class="modal-header border-0 px-4 pt-4 pb-2 d-flex align-items-start justify-content-between w-100">
                    <div class="d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                            style="width: 46px; height: 46px; border-radius: 14px; background: #eef2ff; color: #5d87ff;">
                            <span class="material-symbols-outlined" style="font-size: 24px;">tune</span>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="modal-title fw-800 text-dark mb-0" style="font-size: 1.15rem; letter-spacing: -0.3px;">Manual Stock Adjustment</h5>
                                <span class="badge rounded-pill" style="background: #eef2ff; color: #5d87ff; font-size: 0.68rem; font-weight: 700; padding: 4px 9px;">Stock Ledger</span>
                            </div>
                            <div id="adj_prod_display_text" class="text-muted mt-1" style="font-size: 0.78rem;">
                                Record physical stock changes, discrepancies, or inventory overrides.
                            </div>
                        </div>
                    </div>
                    <button type="button" class="view-modal-btn-close shadow-none ms-auto" data-bs-dismiss="modal" aria-label="Close" title="Close">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-4 pt-2 pb-2">
                    <div id="adj-recipe-panel" class="alert alert-info d-none" aria-live="polite">
                        <strong>Recipe stock adjustment</strong><p id="adj-recipe-message" class="small my-2"></p>
                        <div id="adj-recipe-requirements" class="small mb-2"></div>
                        <label id="adj-recipe-confirm-label" class="small d-none"><input type="checkbox" name="confirm_recipe_preparation" value="1" id="adj-recipe-confirm"> I confirm these units are being prepared. Switch this product to finished-stock sales so its recipe ingredients are not deducted again at checkout.</label>
                    </div>
                    <!-- Searchable Product Selection (Visible only when adjusting without a pre-selected row) -->
                    <div class="adj-spec-card position-relative" id="adj_product_search_container">
                        <div class="adj-card-label">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #5d87ff;">search</span>
                            Select Target Product
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-0 text-muted" style="border-radius: 10px 0 0 10px !important;">
                                <span class="material-symbols-outlined" style="font-size: 18px;">search</span>
                            </span>
                            <input type="text" id="productSearchInput"
                                class="form-control border-0 py-2 fs-7 shadow-none"
                                style="border-radius: 0 10px 10px 0 !important; background: #f8fafc !important;"
                                placeholder="Type product name or SKU to search..." autocomplete="off">
                        </div>

                        <!-- Suggestions Dropdown -->
                        <div id="productSuggestions"
                            class="position-absolute w-100 shadow-lg rounded-3 bg-white border mt-1 overflow-auto d-none"
                            style="max-height: 220px; z-index: 1050; left: 0; top: 100%;">
                            <div class="list-group list-group-flush">
                                <?php if (!empty($products)): ?>
                                    <?php foreach ($products as $prod): ?>
                                        <button type="button"
                                            class="list-group-item list-group-item-action fs-7 py-2 px-3 product-suggestion-item adj-product-item border-bottom"
                                            data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>"
                                            data-stock="<?= $prod['stock'] ?>"
                                            data-max-threshold="<?= $prod['max_stock_threshold'] ?>"
                                            data-cost-price="<?= $prod['cost_price'] ?>"
                                            data-price="<?= $prod['price'] ?>">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="fw-bold text-dark">
                                                    <?= htmlspecialchars($prod['name']) ?>
                                                </span>
                                                <span class="badge rounded-pill bg-light text-muted border fw-semibold">
                                                    Stock: <?= $prod['stock'] ?>
                                                </span>
                                            </div>
                                            <div class="smaller text-muted" style="font-size: 0.72rem;">
                                                SKU: #PROD-<?= str_pad($prod['id'], 4, '0', STR_PAD_LEFT) ?>
                                            </div>
                                        </button>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Card 1: Adjustment Type & Quantity -->
                    <div class="adj-spec-card">
                        <div class="adj-card-label">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #5d87ff;">swap_vert</span>
                            Adjustment Mode & Net Quantity
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Adjustment Direction</label>
                                <select name="adjustment_type" id="adjTypeSelect" class="form-select" required>
                                    <option value="stock_in">Inflow (+ Add to Stock)</option>
                                    <option value="stock_out">Outflow (- Subtract Stock)</option>
                                    <option value="waste">Waste / Spoilage (consume stock)</option>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Quantity Units</label>
                                <input type="number" name="quantity" class="form-control" min="0.0001" step="0.0001" required placeholder="Enter quantity (e.g. 10)">
                            </div>
                        </div>
                    </div>

                    <!-- Card 2: Branch & Expiry Date -->
                    <div class="adj-spec-card">
                        <div class="adj-card-label">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #5d87ff;">storefront</span>
                            Location & Expiration
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Target Branch</label>
                                <input type="hidden" name="branch_id" id="adj_branch_id_hidden">
                                <select id="adj_branch_id" class="form-select" required disabled>
                                    <option value="">-- Select Branch --</option>
                                    <?php if (!empty($branches)): ?>
                                        <?php foreach ($branches as $branch): ?>
                                            <option value="<?= $branch['id'] ?>" <?= (isset($_SESSION['user']['branch_id']) && $_SESSION['user']['branch_id'] == $branch['id']) ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($branch['name']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Expiration Date (Optional)</label>
                                <input type="date" name="expiry_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <!-- Card 3: Batch Selection for Stock Out (Visible only when Outflow selected) -->
                    <div id="batchSelectionField" class="adj-spec-card d-none">
                        <div class="adj-card-label">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #f59e0b;">layers</span>
                            Deduct From Batch (FIFO)
                        </div>
                        <div>
                            <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Select Source Batch</label>
                            <select name="selected_batch_id" id="adj_batch_id_select" class="form-select">
                                <option value="">-- Auto (FIFO: Oldest First) --</option>
                            </select>
                            <div class="d-flex align-items-center gap-1 text-muted mt-2" style="font-size: 0.72rem;">
                                <span class="material-symbols-outlined" style="font-size: 14px; color: #64748b;">info</span>
                                <span>Default FIFO automatically allocates deductions from the earliest expiring lots.</span>
                            </div>
                        </div>
                    </div>

                    <!-- Card 4: New Batch Info for Stock In (Visible on Inflow) -->
                    <div id="batchInfoFields" class="adj-spec-card">
                        <div class="adj-card-label">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #10b981;">inventory</span>
                            Batch Identification & Valuation
                        </div>
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small fw-bold text-dark mb-0" style="font-size: 0.75rem;">Batch Number / Lot Code</label>
                                <span class="badge rounded-pill" style="background: #eef2ff; color: #5d87ff; font-size: 0.65rem; font-weight: 700;">Auto Generated</span>
                            </div>
                            <input type="text" name="batch_number" id="autoBatchNumber" class="form-control" placeholder="e.g. LOT-2026-001">
                        </div>
                        <div class="row g-3">
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Cost Price (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border text-muted" style="border-radius: 10px 0 0 10px !important; border-color: #edf2f7 !important; font-size: 0.83rem;">₱</span>
                                    <input type="number" step="0.01" name="cost_price" class="form-control" style="border-radius: 0 10px 10px 0 !important;" placeholder="0.00">
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small fw-bold text-dark mb-1" style="font-size: 0.75rem;">Selling Price (₱)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border text-muted" style="border-radius: 10px 0 0 10px !important; border-color: #edf2f7 !important; font-size: 0.83rem;">₱</span>
                                    <input type="number" step="0.01" name="selling_price" class="form-control" style="border-radius: 0 10px 10px 0 !important;" placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card 5: Remarks / Justification -->
                    <div class="adj-spec-card">
                        <div class="adj-card-label">
                            <span class="material-symbols-outlined" style="font-size: 15px; color: #5d87ff;">description</span>
                            Audit Remarks & Justification
                            <span class="text-danger ms-1">*</span>
                        </div>
                        <textarea name="remarks" class="form-control" rows="3" required
                            placeholder="State reason for manual adjustment (e.g. Physical inventory count discrepancy, damaged delivery, supplier replacement, waste write-off)..."></textarea>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="modal-footer border-0 px-4 pb-4 pt-3 d-flex justify-content-end align-items-center gap-2" style="border-top: 1px solid #f1f5f9 !important;">
                    <button type="button" class="btn btn-modal-secondary" data-bs-dismiss="modal">Discard</button>
                    <button type="submit" class="btn btn-modal-primary">
                        <span class="material-symbols-outlined" style="font-size: 16px;">sync_alt</span> Process Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
