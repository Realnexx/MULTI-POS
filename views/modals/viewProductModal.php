<style>
    /* ── Aurora View Product Modal Styles ── */
    #viewProductModal .modal-content {
        border-radius: 22px !important;
        border: 1px solid #e8edf5 !important;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.14), 0 4px 16px rgba(93, 135, 255, 0.06) !important;
        overflow: hidden;
        background: #ffffff;
    }

    .view-modal-accent-bar {
        height: 4px;
        width: 100%;
        background: linear-gradient(90deg, #5d87ff, #49beff);
    }

    .view-stat-card {
        border-radius: 14px;
        border: 1px solid #edf2f7;
        background: #ffffff;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        position: relative;
    }
    .view-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(93, 135, 255, 0.08) !important;
    }

    .view-stat-icon {
        width: 38px;
        height: 38px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .view-table-card {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        overflow: hidden;
        background: #ffffff;
    }
    .view-table-card table thead th {
        background: #f8fafc !important;
        border: none !important;
        padding: 9px 14px !important;
        font-size: 0.65rem !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.6px !important;
        color: #8c98a9 !important;
    }
    .view-table-card table tbody td {
        padding: 9px 14px !important;
        vertical-align: middle !important;
        border-color: #f1f5f9 !important;
    }

    .batch-row {
        transition: background-color 0.15s ease;
    }
    .batch-row:hover {
        background-color: #f8fafc !important;
    }

    #viewProductModal .modal-header {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    .view-modal-btn-close {
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
    .view-modal-btn-close:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: scale(1.08);
    }

    /* Spec Tiles & Cardlets */
    .view-spec-tile {
        background: #ffffff;
        border: 1px solid #edf2f7 !important;
        border-radius: 12px;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .view-spec-tile:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
        transform: translateY(-1px);
    }

    .view-spec-card {
        background: #ffffff;
        border: 1px solid #edf2f7 !important;
        border-radius: 14px;
        transition: transform 0.18s ease, box-shadow 0.18s ease, border-color 0.18s ease;
    }
    .view-spec-card:hover {
        border-color: #cbd5e1 !important;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.04);
        transform: translateY(-1px);
    }

    .copy-btn-mini {
        width: 22px;
        height: 22px;
        border-radius: 6px;
        background: transparent;
        border: 1px solid transparent;
        color: #94a3b8;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        padding: 0;
        transition: all 0.15s ease;
    }
    .copy-btn-mini:hover {
        background: #f1f5f9;
        border-color: #e2e8f0;
        color: #475569;
    }
    .copy-btn-mini:active {
        transform: scale(0.92);
    }

    /* Modal Action Buttons */
    .btn-modal-secondary {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
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
        padding: 8px 22px;
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
</style>

<div class="modal fade" id="viewProductModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <!-- Top Gradient Accent -->
            <div class="view-modal-accent-bar"></div>

            <!-- Header -->
            <div class="modal-header border-0 px-4 pt-4 pb-2 d-flex align-items-start justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center"
                        style="width: 46px; height: 46px; border-radius: 14px; background: #eef2ff; color: #5d87ff;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">inventory_2</span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-800 text-dark mb-0" style="font-size: 1.15rem; font-weight: 800; letter-spacing: -0.3px;">Product Insights</h5>
                            <span class="badge rounded-pill" style="background: #eef2ff; color: #5d87ff; font-size: 0.68rem; font-weight: 700; padding: 4px 9px;">Catalog Item</span>
                        </div>
                        <p class="text-muted mb-0" style="font-size: 0.78rem;">Detailed specifications, stock allocation, recipe formulas, and batch valuation</p>
                    </div>
                </div>
                <button type="button" class="view-modal-btn-close shadow-none ms-auto" data-bs-dismiss="modal" aria-label="Close" title="Close">
                    <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                </button>
            </div>

            <div class="modal-body px-4 py-3">
                <div class="row g-4">
                    <!-- Left Column: Product Media, Identifiers & Description -->
                    <div class="col-lg-5">
                        <!-- Product Image Card -->
                        <div class="position-relative overflow-hidden mb-3"
                            style="aspect-ratio: 1/1; border-radius: 18px; border: 1px solid #edf2f7; box-shadow: 0 4px 16px rgba(0,0,0,0.03); background: #f8fafc;">
                            <img id="view_prod_image" src="" class="w-100 h-100 object-fit-cover"
                                onerror="this.src='assets/img/default.png'">
                            <span id="view_prod_badge_type" class="position-absolute top-0 start-0 m-3 badge rounded-pill px-3 py-1 fw-700"
                                style="font-size: 0.68rem; background: rgba(15, 23, 42, 0.72); color: #fff; backdrop-filter: blur(6px); letter-spacing: 0.4px;">
                            </span>
                        </div>

                        <!-- Category & Branch Spec Cards -->
                        <div class="row g-2 mb-3">
                            <!-- Category Cardlet -->
                            <div class="col-6">
                                <div class="view-spec-tile p-2 rounded-3 border h-100 d-flex align-items-center gap-2" id="view_prod_category_card"
                                    style="background: #ffffff; border-color: #edf2f7 !important;">
                                    <div class="view-spec-icon-box rounded-2 d-flex align-items-center justify-content-center" id="view_prod_category_icon_wrap"
                                        style="width: 32px; height: 32px; background: #eef2ff; color: #5d87ff; flex-shrink: 0;">
                                        <span class="material-symbols-outlined" id="view_prod_category_icon" style="font-size: 17px;">category</span>
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div class="view-spec-micro text-muted" style="font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1;">Category</div>
                                        <div id="view_prod_category" class="fw-800 text-truncate" style="font-size: 0.82rem; color: #1e293b;" title="-">-</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Branch Cardlet -->
                            <div class="col-6">
                                <div class="view-spec-tile p-2 rounded-3 border h-100 d-flex align-items-center gap-2" id="view_prod_branch"
                                    style="background: #ffffff; border-color: #edf2f7 !important;">
                                    <div class="view-spec-icon-box rounded-2 d-flex align-items-center justify-content-center"
                                        style="width: 32px; height: 32px; background: #e0f2fe; color: #0284c7; flex-shrink: 0;">
                                        <span class="material-symbols-outlined" style="font-size: 17px;">storefront</span>
                                    </div>
                                    <div style="min-width: 0; flex: 1;">
                                        <div class="view-spec-micro text-muted" style="font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; line-height: 1.1;">Store Branch</div>
                                        <div id="view_prod_branch_text" class="fw-800 text-truncate text-dark" style="font-size: 0.82rem;" title="Global">Global</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- SKU & Barcode Cardlets -->
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <div class="p-3 view-spec-card h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center"
                                                style="width: 26px; height: 26px; border-radius: 8px; background: #eef2ff; color: #5d87ff;">
                                                <span class="material-symbols-outlined" style="font-size: 15px;">qr_code_2</span>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px;">SKU Code</span>
                                        </div>
                                        <button type="button" class="copy-btn-mini copy-sku-btn" title="Copy SKU" data-target="#view_prod_sku">
                                            <span class="material-symbols-outlined" style="font-size: 13px;">content_copy</span>
                                        </button>
                                    </div>
                                    <div class="p-2 rounded-2 text-center" style="background: #f8fafc; border: 1px dashed #cbd5e1;">
                                        <div id="view_prod_sku" class="fw-800 text-dark mb-0 font-monospace" style="font-size: 0.84rem; letter-spacing: 0.5px; word-break: break-all;">-</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 view-spec-card h-100 d-flex flex-column justify-content-between">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="d-flex align-items-center justify-content-center"
                                                style="width: 26px; height: 26px; border-radius: 8px; background: #e0f2fe; color: #0284c7;">
                                                <span class="material-symbols-outlined" style="font-size: 15px;">barcode</span>
                                            </div>
                                            <span class="text-muted" style="font-size: 0.65rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.6px;">Barcode</span>
                                        </div>
                                        <div class="d-flex align-items-center gap-1">
                                            <button type="button" class="copy-btn-mini copy-barcode-btn" title="Copy Barcode Digits" data-target="#view_prod_barcode_text">
                                                <span class="material-symbols-outlined" style="font-size: 13px;">content_copy</span>
                                            </button>
                                            <button type="button" class="copy-btn-mini download-barcode-btn" id="view_prod_barcode_download" title="Download Barcode as PNG">
                                                <span class="material-symbols-outlined" style="font-size: 14px;">download</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="p-2 rounded-2 text-center d-flex flex-column align-items-center justify-content-center barcode-preview-box"
                                        style="background: #ffffff; border: 1px solid #edf2f7; min-height: 52px; cursor: pointer;"
                                        title="Click to download barcode as PNG">
                                        <canvas id="view_prod_barcode" style="max-height: 40px; width: auto; max-width: 100%; margin: 0 auto; display: block;"></canvas>
                                        <div id="view_prod_barcode_text" class="text-muted font-monospace mt-1 fw-700" style="font-size: 0.72rem; letter-spacing: 0.8px;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Description Card -->
                        <div class="p-3 rounded-3 border" style="background: #fafbff; border-color: #edf2f7 !important;">
                            <div class="text-muted mb-1 d-flex align-items-center gap-1" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.4px;">
                                <span class="material-symbols-outlined" style="font-size: 14px; color: #5d87ff;">notes</span>
                                Description
                            </div>
                            <p id="view_prod_description" class="text-dark small mb-0" style="line-height: 1.6; font-size: 0.82rem;">No description provided.</p>
                        </div>
                    </div>

                    <!-- Right Column: Pricing, Stock, Recipes & Batches -->
                    <div class="col-lg-7">
                        <!-- Product Title & ID Header -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center gap-2 mb-1">
                                <span id="view_prod_id" class="badge rounded-pill px-2 py-1 fw-700" style="font-size: 0.65rem; background: #eef2ff; color: #5d87ff;">#0000</span>
                                <span class="text-muted" style="font-size: 0.7rem; font-weight: 500;">Master Inventory Product</span>
                            </div>
                            <h2 id="view_prod_name" class="fw-800 text-dark mb-0" style="font-size: 1.45rem; letter-spacing: -0.3px;">Product Name</h2>
                        </div>

                        <!-- Selling Price & Cost Price KPI Cards -->
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <div class="p-3 view-stat-card d-flex align-items-center gap-3">
                                    <div class="view-stat-icon" style="background: #eef2ff; color: #5d87ff;">
                                        <span class="material-symbols-outlined" style="font-size: 20px;">sell</span>
                                    </div>
                                    <div>
                                        <div class="text-muted mb-0" style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Selling Price</div>
                                        <div id="view_prod_price" class="fw-800 mb-0" style="font-size: 1.35rem; color: #5d87ff; letter-spacing: -0.3px;">₱0.00</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-3 view-stat-card d-flex align-items-center gap-3">
                                    <div class="view-stat-icon" style="background: #f1f5f9; color: #64748b;">
                                        <span class="material-symbols-outlined" style="font-size: 20px;">payments</span>
                                    </div>
                                    <div>
                                        <div class="text-muted mb-0" style="font-size: 0.68rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">Cost Price</div>
                                        <div id="view_prod_cost" class="fw-800 text-dark mb-0" style="font-size: 1.35rem; letter-spacing: -0.3px;">₱0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Stock Analytics & Distribution -->
                        <div class="p-3 view-stat-card mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="view-stat-icon" style="width: 30px; height: 30px; background: #eef2ff; color: #5d87ff; border-radius: 8px;">
                                        <span class="material-symbols-outlined" style="font-size: 16px;">analytics</span>
                                    </div>
                                    <h6 class="fw-800 text-dark mb-0" style="font-size: 0.88rem;">Stock Analytics</h6>
                                </div>
                                <span id="view_stock_status" class="badge rounded-pill px-3 py-1 fw-700" style="font-size: 0.65rem;">IN STOCK</span>
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-4">
                                    <div class="text-center p-2 rounded-3" style="background: #f8fafc;">
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 600;">Available Stock</div>
                                        <div id="view_prod_stock" class="fw-800 text-dark mb-0" style="font-size: 1.25rem;">0</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 rounded-3" style="background: #f8fafc;">
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 600;">Low Threshold</div>
                                        <div id="view_prod_threshold" class="fw-800 mb-0" style="font-size: 1.25rem; color: #d97706;">0</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center p-2 rounded-3" style="background: #f8fafc;">
                                        <div class="text-muted mb-1" style="font-size: 0.65rem; font-weight: 600;">Max Target</div>
                                        <div id="view_prod_max" class="fw-800 mb-0" style="font-size: 1.25rem; color: #5d87ff;">0</div>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <div class="progress rounded-pill" style="height: 6px; background: #f1f5f9;">
                                    <div id="view_stock_progress" class="progress-bar rounded-pill" role="progressbar" style="background: #5d87ff; transition: width 0.5s ease;"></div>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2" style="font-size: 0.72rem; color: #8c98a9;">
                                    <span>Stock Utilization</span>
                                    <span class="fw-700 text-dark"><span id="view_stock_percent">0%</span> capacity</span>
                                </div>
                            </div>
                        </div>

                        <!-- Recipe Ingredients Section (Conditional) -->
                        <div id="view_recipe_section" class="d-none mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="material-symbols-outlined" style="font-size: 16px; color: #5d87ff;">receipt_long</span>
                                <span id="view_breakdown_title" class="fw-700 text-dark" style="font-size: 0.82rem;">Recipe Formula</span>
                            </div>
                            <p id="view_breakdown_note" class="small text-muted mb-2"></p>
                            <div class="view-table-card">
                                <table class="table table-sm mb-0" style="font-size: 0.78rem;">
                                    <thead>
                                        <tr>
                                            <th id="view_breakdown_item_heading" class="ps-3">Ingredient Item</th>
                                            <th class="text-center">Required Qty</th>
                                            <th id="view_breakdown_detail_heading" class="pe-3 text-end">Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_ingredients_list"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Modifiers Section (Conditional) -->
                        <div id="view_modifiers_section" class="d-none mb-3">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="material-symbols-outlined" style="font-size: 16px; color: #d97706;">tune</span>
                                <span class="fw-700 text-dark" style="font-size: 0.82rem;">Custom Modifiers & Add-ons</span>
                            </div>
                            <div class="view-table-card">
                                <table class="table table-sm mb-0" style="font-size: 0.78rem;">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Modifier Option</th>
                                            <th class="text-end">Extra Price</th>
                                            <th class="pe-3 text-end">Inventory Effect</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_modifiers_list"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Batch Breakdown Table Card -->
                        <div class="view-table-card mb-3">
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="background: #f8fafc;">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="material-symbols-outlined" style="font-size: 16px; color: #5d87ff;">layers</span>
                                    <h6 class="fw-700 text-dark mb-0" style="font-size: 0.82rem;">Batch Breakdown & Valuations</h6>
                                </div>
                                <span id="sync_badge" class="badge d-none rounded-pill px-2 py-1" style="font-size: 0.6rem; background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;">Live Synced</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0" style="font-size: 0.74rem;">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Batch</th>
                                            <th>Branch</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Cost</th>
                                            <th class="text-end">Selling</th>
                                            <th class="text-end">Cost Total</th>
                                            <th class="pe-3 text-end">Selling Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="view_batch_list">
                                        <tr>
                                            <td colspan="7" class="text-center py-3 text-muted">
                                                <div class="d-flex align-items-center justify-content-center gap-2">
                                                    <div class="spinner-border spinner-border-sm text-primary"></div>
                                                    <span>Loading batches...</span>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot style="background: #f8fafc; font-weight: 700;">
                                        <tr class="text-dark">
                                            <td colspan="2" class="ps-3 py-2">
                                                <span style="font-size: 0.72rem; color: #64748b;">Total Batch Valuation:</span>
                                            </td>
                                            <td id="view_batch_total_qty" class="text-center py-2" style="color: #5d87ff;">0</td>
                                            <td></td>
                                            <td></td>
                                            <td id="view_batch_total_cost" class="text-end py-2" style="color: #10b981;">₱0.00</td>
                                            <td id="view_batch_total_selling" class="pe-3 text-end py-2" style="color: #5d87ff;">₱0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="d-flex justify-content-end align-items-center gap-2 pt-3 mt-1 border-top" style="border-color: #f1f5f9 !important;">
                            <button type="button" class="btn btn-modal-secondary" data-bs-dismiss="modal"><span class="material-symbols-outlined inv-button-icon" aria-hidden="true">close</span>Close</button>
                            <button type="button" id="view_edit_btn" class="btn btn-modal-primary">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit_square</span>
                                Edit Product Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
