<style>
    /* ── Aurora Stock Audit Trails Modal Styles (Compact Edition) ── */
    #stockHistoryModal .modal-content {
        border-radius: 20px !important;
        border: 1px solid #e8edf5 !important;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.14), 0 4px 16px rgba(93, 135, 255, 0.06) !important;
        overflow: hidden;
        background: #ffffff;
    }

    #stockHistoryModal .history-modal-accent-bar {
        height: 4px;
        width: 100%;
        background: linear-gradient(90deg, #5d87ff, #49beff);
    }

    #stockHistoryModal .modal-header {
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        width: 100% !important;
    }

    #stockHistoryModal .view-modal-btn-close {
        width: 32px;
        height: 32px;
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
    #stockHistoryModal .view-modal-btn-close:hover {
        background: #e2e8f0;
        color: #1e293b;
        transform: scale(1.08);
    }

    /* Product Target Banner */
    .history-product-card {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        background: #ffffff;
        padding: 10px 16px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
        margin-bottom: 10px;
    }

    .history-kpi-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 10px;
        border-radius: 8px;
        font-size: 0.72rem;
        font-weight: 700;
        border: 1px solid transparent;
        white-space: nowrap;
    }

    /* Compact History Table Card Container */
    .history-table-wrapper {
        border: 1px solid #edf2f7;
        border-radius: 14px;
        background: #ffffff;
        overflow: hidden;
        box-shadow: 0 1px 6px rgba(0, 0, 0, 0.02);
    }

    #historyTable thead th {
        background: #f8fafc !important;
        color: #64748b !important;
        font-size: 0.65rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.6px !important;
        text-transform: uppercase !important;
        border-bottom: 1px solid #edf2f7 !important;
        padding: 8px 12px !important;
        white-space: nowrap;
    }

    #historyTable tbody td {
        padding: 7px 12px !important;
        border-bottom: 1px solid #f1f5f9 !important;
        vertical-align: middle !important;
        font-size: 0.78rem;
    }

    .history-remarks-truncate {
        max-width: 200px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        display: inline-block;
        vertical-align: middle;
        cursor: help;
    }

    #historyTable tbody tr:hover td {
        background-color: #f8fafc !important;
    }

    /* DataTables Controls in Audit Modal */
    #stockHistoryModal .dataTables_wrapper {
        padding: 8px 12px;
    }

    #stockHistoryModal .dataTables_wrapper .dataTables_filter input {
        border: 1.5px solid #edf2f7 !important;
        border-radius: 8px !important;
        padding: 4px 10px !important;
        font-size: 0.76rem !important;
        outline: none !important;
        transition: all 0.2s ease !important;
    }
    #stockHistoryModal .dataTables_wrapper .dataTables_filter input:focus {
        border-color: #5d87ff !important;
        box-shadow: 0 0 0 3px rgba(93, 135, 255, 0.12) !important;
    }

    #stockHistoryModal .dataTables_wrapper .dataTables_length select {
        border: 1.5px solid #edf2f7 !important;
        border-radius: 8px !important;
        padding: 3px 20px 3px 8px !important;
        font-size: 0.76rem !important;
        outline: none !important;
    }

    #stockHistoryModal .dataTables_wrapper .pagination .page-item .page-link {
        border-radius: 6px !important;
        margin: 0 1px !important;
        border: 1px solid #e2e8f0 !important;
        color: #64748b !important;
        font-size: 0.74rem !important;
        font-weight: 600 !important;
        padding: 3px 9px !important;
    }
    #stockHistoryModal .dataTables_wrapper .pagination .page-item.active .page-link {
        background: #5d87ff !important;
        border-color: #5d87ff !important;
        color: #ffffff !important;
        box-shadow: 0 2px 5px rgba(93, 135, 255, 0.3) !important;
    }

    /* Buttons */
    .btn-modal-secondary {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        color: #64748b;
        font-weight: 700;
        font-size: 0.8rem;
        padding: 6px 18px;
        border-radius: 50px;
        transition: all 0.2s ease;
    }
    .btn-modal-secondary:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-1px);
    }
</style>

<div class="modal fade" id="stockHistoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0">
            <!-- Top Gradient Accent Bar -->
            <div class="history-modal-accent-bar"></div>

            <!-- Modal Header -->
            <div class="modal-header border-0 px-4 pt-3 pb-1 d-flex align-items-start justify-content-between w-100">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 40px; height: 40px; border-radius: 12px; background: #eef2ff; color: #5d87ff;">
                        <span class="material-symbols-outlined" style="font-size: 22px;">history</span>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-800 text-dark mb-0" style="font-size: 1.1rem; letter-spacing: -0.3px;">Stock Audit Trails</h5>
                            <span class="badge rounded-pill" style="background: #eef2ff; color: #5d87ff; font-size: 0.65rem; font-weight: 700; padding: 3px 8px;">Immutable Ledger</span>
                        </div>
                        <p class="text-muted mb-0 mt-0" id="historyModalSubtitle" style="font-size: 0.75rem;">
                            Viewing historical adjustments, lot movements, and audit records for this product.
                        </p>
                    </div>
                </div>
                <button type="button" class="view-modal-btn-close shadow-none ms-auto" data-bs-dismiss="modal" aria-label="Close" title="Close">
                    <span class="material-symbols-outlined" style="font-size: 16px;">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body px-4 pt-1 pb-2">
                <!-- Product Target Banner & Live KPI Metrics -->
                <div class="history-product-card">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="d-flex align-items-center justify-content-center flex-shrink-0"
                                style="width: 32px; height: 32px; border-radius: 10px; background: #f8fafc; border: 1px solid #edf2f7; color: #5d87ff;">
                                <span class="material-symbols-outlined" style="font-size: 18px;">inventory_2</span>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <h6 class="mb-0 fw-800 text-dark" id="historyProductName" style="font-size: 0.95rem; letter-spacing: -0.2px;">Product Name</h6>
                                    <span class="badge rounded-pill" style="background: #eef2ff; color: #5d87ff; font-size: 0.67rem; font-weight: 700;" id="historyProductSKU">SKU: #0000</span>
                                </div>
                            </div>
                        </div>

                        <!-- Live Metrics (Compact Pills) -->
                        <div class="d-flex flex-wrap align-items-center gap-2">
                            <div class="history-kpi-pill" style="background: #f0fdf4; border-color: #bbf7d0; color: #16a34a;" title="Total Historical Inflow">
                                <span class="material-symbols-outlined" style="font-size: 13px;">arrow_downward</span>
                                <span>Inflow: <strong id="historyTotalIn">0</strong></span>
                            </div>
                            <div class="history-kpi-pill" style="background: #fff1f2; border-color: #fecdd3; color: #e11d48;" title="Total Historical Outflow">
                                <span class="material-symbols-outlined" style="font-size: 13px;">arrow_upward</span>
                                <span>Outflow: <strong id="historyTotalOut">0</strong></span>
                            </div>
                            <div class="history-kpi-pill" style="background: #eef2ff; border-color: #c7d2fe; color: #4338ca;" title="Net Stock Movement">
                                <span class="material-symbols-outlined" style="font-size: 13px;">balance</span>
                                <span>Net: <strong id="historyNet">0</strong></span>
                            </div>
                            <div class="history-kpi-pill" style="background: #f8fafc; border-color: #e2e8f0; color: #64748b;" title="Total Logged Records">
                                <span class="material-symbols-outlined" style="font-size: 13px;">receipt_long</span>
                                <span>Logs: <strong id="historyCount">0</strong></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compact Table Wrapper -->
                <div class="history-table-wrapper">
                    <div class="table-responsive">
                        <table id="historyTable" class="table align-middle w-100 mb-0">
                            <thead>
                                <tr>
                                    <th style="min-width: 130px;">Operator</th>
                                    <th style="min-width: 110px;">Action Type</th>
                                    <th style="min-width: 120px;">Batch / Lot</th>
                                    <th style="min-width: 90px;">Unit Cost</th>
                                    <th style="min-width: 80px;">Delta Qty</th>
                                    <th style="min-width: 150px; max-width: 220px;">Remarks / Reason</th>
                                    <th style="min-width: 130px;">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded via DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="modal-footer border-0 px-4 pb-3 pt-2 d-flex justify-content-between align-items-center" style="border-top: 1px solid #f1f5f9 !important;">
                <div class="d-flex align-items-center gap-2 text-muted" style="font-size: 0.73rem;">
                    <span class="material-symbols-outlined" style="font-size: 15px; color: #5d87ff;">verified_user</span>
                    <span>All inventory audit logs are timestamped and preserved for compliance and loss prevention.</span>
                </div>
                <button type="button" class="btn btn-modal-secondary" data-bs-dismiss="modal">
                    Close Audit Trail
                </button>
            </div>
        </div>
    </div>
</div>