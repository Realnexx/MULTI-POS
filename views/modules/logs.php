<?php
require_once __DIR__ . '/../../models/Product.php';
require_once __DIR__ . '/../../models/Branch.php';
$logs = $logs ?? Product::get_inventory_logs() ?: [];
$products = $products ?? Product::get_all_products() ?: [];
$branches = $branches ?? Branch::get_all_active() ?: [];

$title = "Inventory Audit Logs & Adjustments";
ob_start();
?>

<!-- Header Section -->
<?php
$filtered_product = null;
if (isset($_GET['product_id']) && !empty($_GET['product_id'])) {
    foreach ($products as $p) {
        if ($p['id'] == $_GET['product_id']) {
            $filtered_product = $p;
            break;
        }
    }
}
?>
<div class="row align-items-center mb-4 g-3" data-aos="fade-down">
    <?php if ($filtered_product): ?>
        <div class="col-md-auto">
            <a href="<?= url('?route=inventory/products') ?>"
                class="btn btn-light rounded-circle shadow-sm d-flex align-items-center justify-content-center border-light-gray"
                style="width: 42px; height: 42px;" title="Back to Products">
                <i class="fa-solid fa-arrow-left text-muted"></i>
            </a>
        </div>
    <?php endif; ?>
    <div class="col-md-auto me-auto">
        <h4 class="fw-bold outfit-font text-dark mb-1">
            <?= $filtered_product ? 'Audit: ' . htmlspecialchars($filtered_product['name']) : 'Inventory Audit Logs' ?>
        </h4>
        <p class="text-muted small mb-0 d-flex align-items-center gap-2">
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2">Audit Trace</span>
            Chronological stock movement and manual adjustments.
        </p>
    </div>
    <div class="col-md-auto d-flex gap-2">
        <button type="button"
            class="btn btn-outline-dark rounded-pill px-3 fw-medium d-flex align-items-center gap-2 shadow-sm border-light-gray bg-white">
            <i class="fa-solid fa-print text-muted"></i> Print Log
        </button>
        <?php if (User::has_permission($_SESSION['user']['id'], 'manage_inventory')): ?>
            <button type="button"
                class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-2"
                data-bs-toggle="modal" data-bs-target="#adjustStockModal">
                <i class="fa-solid fa-scale-balanced"></i> Manual Adjust
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Stats Overview Cards -->
<?php
$total_in = 0;
$total_out = 0;
foreach ($logs as $l) {
    if ($l['action'] === 'stock_in' || $l['action'] === 'initial')
        $total_in += $l['quantity'];
    else
        $total_out += $l['quantity'];
}
?>
<div class="row g-4 mb-4">
    <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="0">
        <div class="card border-0 rounded-4 shadow-sm h-100 bg-white border border-light">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 text-success opacity-10">
                    <i class="fa-solid fa-circle-arrow-up fs-1"></i>
                </div>
                <div class="text-muted small text-uppercase fw-bold mb-1">Stock Inflow</div>
                <h2 class="text-dark fw-bold mb-0 outfit-font" id="stat_total_in">+0 <span
                        class="fs-6 fw-normal text-muted">Units</span></h2>
                <div class="mt-3">
                    <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold fs-8">Cumulative
                        Additions</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
        <div class="card border-0 rounded-4 shadow-sm h-100 bg-white border border-light">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 text-danger opacity-10">
                    <i class="fa-solid fa-circle-arrow-down fs-1"></i>
                </div>
                <div class="text-muted small text-uppercase fw-bold mb-1">Stock Outflow</div>
                <h2 class="text-dark fw-bold mb-0 outfit-font" id="stat_total_out">-0 <span
                        class="fs-6 fw-normal text-muted">Units</span></h2>
                <div class="mt-3">
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold fs-8">Cumulative
                        Deductions</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
        <div class="card border-0 rounded-4 shadow-sm h-100 bg-white border border-light">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 text-primary opacity-10">
                    <i class="fa-solid fa-calculator fs-1"></i>
                </div>
                <div class="text-muted small text-uppercase fw-bold mb-1">Net Movement</div>
                <h2 class="text-dark fw-bold mb-0 outfit-font" id="stat_net_movement">0</h2>
                <div class="mt-3">
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1 fw-bold fs-8">Net Delta
                        Value</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6" data-aos="fade-up" data-aos-delay="300">
        <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden"
            style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div class="card-body p-4 position-relative">
                <div class="position-absolute top-0 end-0 p-3 opacity-10">
                    <i class="fa-solid fa-boxes-stacked fs-1 text-white"></i>
                </div>
                <div class="text-white-50 small text-uppercase fw-bold mb-1">Action Events</div>
                <h2 class="text-white fw-bold mb-0 outfit-font" id="stat_log_count">0 <span
                        class="fs-6 fw-normal op-7">Logs</span></h2>
                <div class="mt-3">
                    <span class="badge bg-white-subtle text-white rounded-pill px-3 py-1 fw-bold fs-8"
                        style="background: rgba(255,255,255,0.1);">Total Audit Trace</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Orchestration Bar -->
<div class="card border-0 rounded-4 shadow-sm mb-4 bg-white border border-light overflow-hidden" data-aos="fade-up"
    data-aos-delay="400">
    <div class="card-body p-3">
        <div class="row g-3 align-items-center">
            <div class="col-md-6">
                <div class="input-group input-group-modern border rounded-pill overflow-hidden shadow-xs px-2"
                    style="background: #f8fafc;">
                    <span class="input-group-text bg-transparent border-0 text-muted"><i
                            class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="logSearch" class="form-control bg-transparent border-0 shadow-none py-2 fs-7"
                        placeholder="Search by remarks, product name, or user...">
                </div>
            </div>
            <div class="col-md-3">
                <select id="typeFilter" class="form-select border rounded-pill shadow-xs py-2 fs-7 bg-white">
                    <option value="all">Any Action Type</option>
                    <option value="stock_in">Stock In</option>
                    <option value="stock_out">Stock Out</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <div class="btn-group w-100 rounded-pill overflow-hidden shadow-xs border border-light-gray">
                    <button class="btn btn-white text-primary border-0 fw-bold fs-7 py-2 active">All Time</button>
                    <button class="btn btn-white text-muted border-0 fw-medium fs-7 py-2 border-start">Today</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 rounded-4 shadow-sm p-4 bg-white border border-light overflow-hidden" data-aos="fade-up"
    data-aos-delay="500">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 w-100" id="inventoryLogsTable">
            <thead class="table-light text-uppercase fs-7 text-muted fw-bold outfit-font">
                <tr>
                    <th class="py-3 px-4 rounded-start-3">ID</th>
                    <th class="py-3">Product Name</th>
                    <th class="py-3">Staff</th>
                    <th class="py-3">Type</th>
                    <th class="py-3">Batch</th>
                    <th class="py-3">Quantity</th>
                    <th class="py-3">Remarks</th>
                    <th class="py-3 px-4 rounded-end-3">Timestamp</th>
                </tr>
            </thead>
            <tbody>
                <!-- AJAX Load -->
            </tbody>
        </table>
    </div>
</div>

<!-- Adjust Stock Modal -->
<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                        style="width: 48px; height: 48px;">
                        <i class="fa-solid fa-scale-balanced fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold outfit-font text-dark mb-0">Manual Stock Adjustment</h5>
                        <p class="text-muted smaller mb-0">Record physical stock changes or overrides.</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=inventory/logs') ?>" method="POST">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="adjust_stock">

                <div class="modal-body p-4">
                    <!-- Searchable Product Selection -->
                    <div class="mb-4 position-relative">
                        <label class="form-label fw-bold small text-dark opacity-75">Search & Select Product</label>
                        <div class="input-group border rounded-3 shadow-xs overflow-hidden">
                            <span class="input-group-text bg-white border-0 text-muted"><i
                                    class="fa-solid fa-search"></i></span>
                            <input type="text" id="productSearchInput"
                                class="form-control border-0 py-2 fs-7 shadow-none"
                                placeholder="Type product name or SKU..." autocomplete="off"
                                value="<?= $filtered_product ? htmlspecialchars($filtered_product['name']) : '' ?>">
                        </div>
                        <input type="hidden" name="product_id" id="targetProductId"
                            value="<?= $filtered_product ? $filtered_product['id'] : '' ?>" required>

                        <!-- Suggestions Dropdown -->
                        <div id="productSuggestions"
                            class="position-absolute w-100 shadow-lg rounded-3 bg-white border mt-1 overflow-auto d-none"
                            style="max-height: 200px; z-index: 1000; top: 100%;">
                            <div class="list-group list-group-flush">
                                <?php foreach ($products as $prod): ?>
                                    <button type="button"
                                        class="list-group-item list-group-item-action fs-7 py-2 px-3 product-suggestion-item"
                                        data-id="<?= $prod['id'] ?>" data-name="<?= htmlspecialchars($prod['name']) ?>">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold text-dark"><?= htmlspecialchars($prod['name']) ?></span>
                                            <span class="badge bg-light text-muted border fw-normal">Stock:
                                                <?= $prod['stock'] ?></span>
                                        </div>
                                        <div class="smaller text-muted">SKU:
                                            #PROD-<?= str_pad($prod['id'], 4, '0', STR_PAD_LEFT) ?></div>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Adjustment Mode</label>
                            <select name="adjustment_type" id="adjTypeSelect"
                                class="form-select rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" required>
                                <option value="stock_in">Inflow (+ Add to Stock)</option>
                                <option value="stock_out">Outflow (- Subtract Stock)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Net Quantity</label>
                            <input type="number" name="quantity"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" min="1"
                                required placeholder="0">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold small text-dark opacity-75">Target Branch</label>
                        <select name="branch_id"
                            class="form-select rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7" required>
                            <option value="">-- Select Branch --</option>
                            <?php foreach ($branches as $branch): ?>
                                <option value="<?= $branch['id'] ?>" <?= (isset($_SESSION['user']['branch_id']) && $_SESSION['user']['branch_id'] == $branch['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($branch['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div id="batchInfoFields" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Batch Number</label>
                            <input type="text" name="batch_number" id="autoBatchNumber"
                                class="form-control rounded-3 border-light-gray shadow-xs py-2 px-3 fs-7"
                                placeholder="e.g. LOT-2023-01">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small text-dark opacity-75">Cost Price Override</label>
                            <div class="input-group border border-light-gray rounded-3 shadow-xs overflow-hidden">
                                <span class="input-group-text bg-white border-0 fs-7 text-muted">$</span>
                                <input type="number" step="0.01" name="cost_price"
                                    class="form-control border-0 py-2 fs-7 shadow-none" placeholder="Default cost">
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold small text-dark opacity-75">Justification / Remarks</label>
                        <textarea name="remarks" class="form-control rounded-4 p-3 shadow-xs fs-7" rows="3" required
                            placeholder="Describe the reason for this manual adjustment..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 p-4 justify-content-between">
                    <button type="button" class="btn btn-outline-light-gray text-dark rounded-pill px-4 fw-semibold"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">Process
                        Adjustment</button>
                </div>
            </form>
        </div>
    </div>
</div>
<style>
    .avatar-circle {
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .log-row {
        transition: all 0.2s ease;
    }

    .log-row:hover {
        background-color: #f8fafc !important;
    }

    .ls-1 {
        letter-spacing: 1px;
    }

    .op-7 {
        opacity: 0.7;
    }

    .bg-white-subtle {
        background: rgba(255, 255, 255, 0.1);
    }
</style>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        const tableId = '#inventoryLogsTable';
        const csrfToken = '<?= csrf_token() ?>';
        const productIdFilter = '<?= $_GET['product_id'] ?? '' ?>';

        const logsTable = $(tableId).DataTable({
            processing: true,
            serverSide: true,
            pageLength: 20,
            order: [[0, "desc"]],
            ajax: {
                url: "ajax/inventory_action.php",
                type: "POST",
                data: function (d) {
                    d.action = 'logs_datatable';
                    d.csrf_token = csrfToken;
                    d.product_id = productIdFilter;
                    d.custom_type = $('#typeFilter').val();
                    d.custom_search = $('#logSearch').val();
                },
                dataSrc: function (json) {
                    // Update Stats
                    const s = json.stats;
                    $('#stat_total_in').html(`+${Number(s.total_in).toLocaleString()} <span class="fs-6 fw-normal text-muted">Units</span>`);
                    $('#stat_total_out').html(`-${Number(s.total_out).toLocaleString()} <span class="fs-6 fw-normal text-muted">Units</span>`);
                    $('#stat_net_movement').text((s.net > 0 ? '+' : '') + Number(s.net).toLocaleString());
                    $('#stat_log_count').html(`${Number(s.count).toLocaleString()} <span class="fs-6 fw-normal op-7">Logs</span>`);
                    return json.data;
                }
            },
            columns: [
                {
                    data: "id",
                    render: (data) => `<span class="badge bg-light text-muted border rounded-pill px-2 fs-8">#${data}</span>`
                },
                {
                    data: "product_name",
                    render: (data, type, row) => `
                    <div class="fw-bold text-dark outfit-font mb-0">${data || 'Deleted Product'}</div>
                    <div class="text-muted smaller fw-medium">SKU: #PROD-${String(row.product_id).padStart(4, '0')}</div>
                `
                },
                {
                    data: "user_name",
                    render: (data) => `
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-circle rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center text-primary smaller fw-extrabold" style="width: 24px; height: 24px;">
                            ${(data || 'S').charAt(0).toUpperCase()}
                        </div>
                        <span class="text-dark fw-medium fs-7">${data || 'System'}</span>
                    </div>
                `
                },
                {
                    data: "action",
                    render: (data) => {
                        const isIn = (data === 'stock_in' || data === 'initial' || data === 'Purchase');
                        return isIn ?
                            `<span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-bold fs-8"><i class="fa-solid fa-level-up-alt me-1"></i> INFLOW</span>` :
                            `<span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1 fw-bold fs-8"><i class="fa-solid fa-level-down-alt me-1"></i> OUTFLOW</span>`;
                    }
                },
                {
                    data: "batch_number",
                    render: (data) => data ?
                        `<div class="fw-bold text-dark fs-7 mb-0">${data}</div>` :
                        `<span class="text-muted opacity-50 small">Standard Stock</span>`
                },
                {
                    data: "quantity",
                    render: (data, type, row) => {
                        const isIn = (row.action === 'stock_in' || row.action === 'initial' || row.action === 'Purchase');
                        return `
                        <div class="fw-extrabold ${isIn ? 'text-success' : 'text-danger'} fs-6">${isIn ? '+' : '-'}${data}</div>
                        <div class="smaller text-muted fw-medium text-uppercase ls-1">Units</div>
                    `;
                    }
                },
                {
                    data: "remarks",
                    render: (data) => `
                        <div class="text-dark small fw-medium mb-1">${data}</div>
                        <div class="text-muted smaller"><i class="fa-solid fa-tag me-1 text-light-gray"></i> Audit Note</div>
                `
                },
                {
                    data: "created_at",
                    render: (data) => {
                        const date = new Date(data);
                        const dateStr = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
                        const timeStr = date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
                        return `
                        <div class="text-dark fw-bold fs-7 mb-0">${dateStr}</div>
                        <div class="text-muted smaller">${timeStr}</div>
                    `;
                    }
                }
            ],
            dom: '<"d-none"f>rt<"d-flex align-items-center justify-content-between p-4 border-top bg-light"ip>',
            language: {
                processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                paginate: {
                    previous: '<i class="fa-solid fa-chevron-left"></i>',
                    next: '<i class="fa-solid fa-chevron-right"></i>'
                }
            }
        });

        // Custom Filters
        $('#logSearch').on('keyup', function () {
            logsTable.search(this.value).draw();
        });

        $('#typeFilter').on('change', function () {
            logsTable.draw();
        });

        // --- Product Search/Suggestions Logic ---
        const productInput = $('#productSearchInput');
        const suggestionsBox = $('#productSuggestions');
        const hiddenIdInput = $('#targetProductId');
        const suggestionItems = $('.product-suggestion-item');

        productInput.on('focus input', function () {
            const query = $(this).val().toLowerCase();
            let matchCount = 0;

            suggestionItems.each(function () {
                const name = $(this).data('name').toLowerCase();
                const id = $(this).data('id').toString();
                const matches = name.includes(query) || id.includes(query);

                $(this).toggle(matches);
                if (matches) matchCount++;
            });

            if (matchCount > 0) {
                suggestionsBox.removeClass('d-none');
            } else {
                suggestionsBox.addClass('d-none');
            }
        });

        // Selection
        $(document).on('click', '.product-suggestion-item', function () {
            const id = $(this).data('id');
            const name = $(this).data('name');

            productInput.val(name);
            hiddenIdInput.val(id);
            suggestionsBox.addClass('d-none');

            // Fetch Next Batch Number
            $.ajax({
                url: 'ajax/inventory_action.php',
                method: 'POST',
                data: { action: 'get_next_batch', product_id: id },
                success: function (response) {
                    const res = JSON.parse(response);
                    if (res.success) {
                        $('#autoBatchNumber').val(res.batch_number);
                    }
                }
            });
        });

        // Toggle Batch Info based on type
        $('#adjTypeSelect').on('change', function () {
            if ($(this).val() === 'stock_out') {
                $('#batchInfoFields').slideUp();
            } else {
                $('#batchInfoFields').slideDown();
            }
        });

        // Hide suggestions on outside click
        $(document).on('click', function (e) {
            if (!$(e.target).closest('.position-relative').length) {
                suggestionsBox.addClass('d-none');
            }
        });

        // Auto-populate batch on modal open if product pre-selected
        $('#adjustStockModal').on('shown.bs.modal', function () {
            const preSelectedId = hiddenIdInput.val();
            if (preSelectedId) {
                $.ajax({
                    url: 'ajax/inventory_action.php',
                    method: 'POST',
                    data: { action: 'get_next_batch', product_id: preSelectedId },
                    success: function (response) {
                        const res = JSON.parse(response);
                        if (res.success) {
                            $('#autoBatchNumber').val(res.batch_number);
                        }
                    }
                });
            }
        });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>