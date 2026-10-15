<?php
// Safety fallbacks
$expenses = $expenses ?? [];
$branches = $branches ?? [];
$stats = $stats ?? [
    'total_revenue' => 0.00,
    'total_expenses' => 0.00,
    'net_profit' => 0.00,
    'highest_category' => 'N/A'
];
$cashflow_data = $cashflow_data ?? [
    'labels' => ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
    'income' => [0, 0, 0, 0, 0, 0, 0],
    'expenses' => [0, 0, 0, 0, 0, 0, 0]
];
$store_name = $store_name ?? 'Stacey Halo Halo';
$branch_id = $branch_id ?? null;
$start_date = $start_date ?? null;
$end_date = $end_date ?? null;

$title = "Expenses";
ob_start();

// Refresh session logic just in case
if (isset($_SESSION['user']['id'])) {
    $refreshed_user = User::find_by_id($_SESSION['user']['id']);
    if ($refreshed_user) {
        $_SESSION['user']['avatar_url'] = $refreshed_user['avatar_url'] ?: $refreshed_user['image'] ?: null;
    }
}
?>

<link rel="stylesheet" href="<?= url('assets/css/expense-management.css') ?>">
<div id="tw-expenses" class="expenses-workspace">
    <header class="expenses-heading">
<div class="expenses-title">
<span class="material-symbols-outlined">account_balance_wallet</span>
<div>
<h1>Expenses</h1>
<p>Track sales and business spending for <?= htmlspecialchars($store_name) ?>.</p>
</div>
</div>
<div class="expense-heading-actions"><button type="button" class="btn btn-light expense-print-button" id="print-report-btn"><span class="material-symbols-outlined" aria-hidden="true">print</span>Print Report</button><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">+ Add Expense</button></div>
</header>
    <div class="expenses-filters">
<div>
<label for="start-date">From</label>
<input class="form-control" type="date" id="start-date" value="<?= htmlspecialchars($start_date ?? '') ?>">
</div>
<div>
<label for="end-date">To</label>
<input class="form-control" type="date" id="end-date" value="<?= htmlspecialchars($end_date ?? '') ?>">
</div>
<button type="button" id="today-btn" class="btn btn-light">Today</button>
<button type="button" id="clear-dates-btn" class="btn btn-light">Clear Dates</button>
<div class="expense-branch-filter">
<label for="branch-filter">Branch</label>
<select class="form-select" id="branch-filter">
<option value="">All Branches</option>
<?php foreach ($branches as $b): ?>
<option value="<?= (int)$b['id'] ?>" <?= ($branch_id == $b['id']) ? 'selected' : '' ?>>
<?= htmlspecialchars($b['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
</div>
    <div class="expense-stats">
        <div>
<span>
<?= ($start_date && $end_date) ? 'Sales in Selected Dates' : 'Sales This Month' ?>
</span>
<strong>₱<?= number_format($stats['total_revenue'], 2) ?>
</strong>
</div>
        <div>
<span>
<?= ($start_date && $end_date) ? 'Expenses in Selected Dates' : 'Expenses This Month' ?>
</span>
<strong>₱<?= number_format($stats['total_expenses'], 2) ?>
</strong>
</div>
        <div>
<span>Estimated Profit</span>
<strong>₱<?= number_format($stats['net_profit'], 2) ?>
</strong>
<small>Sales minus expenses</small>
</div>
        <div>
<span>Biggest Expense Category</span>
<strong class="expense-category-total">
<?= htmlspecialchars(['Utilities' => 'Bills', 'Repair' => 'Repairs', 'Salary' => 'Staff Pay', 'Marketing' => 'Advertising', 'Other' => 'Other'][$stats['highest_category'] ?? ''] ?? ($stats['highest_category'] ?? 'N/A')) ?>
</strong>
</div>
    </div>
    <section class="expense-panel expense-chart">
<div class="expense-panel-heading">
<div>
<h2>Sales and Expenses</h2>
<p>Compare money earned with money spent.</p>
</div>
<div class="expense-chart-key">
<span>
<i>
</i>Sales</span>
<span>
<i>
</i>Expenses</span>
</div>
</div>
<div class="expense-chart-canvas">
<canvas id="incomeExpensesChart" aria-label="Sales and expenses chart" role="img">
</canvas>
</div>
</section>
    <section class="expense-panel">
<div class="expense-panel-heading expense-list-heading">
<div>
<h2>Expense List</h2>
<p>View, edit or delete recorded expenses.</p>
</div>
<div class="expense-list-tools">
<input type="search" id="ledger-search" class="form-control" placeholder="Search expenses..." aria-label="Search expenses">
<button type="button" id="print-ledger-btn" class="btn btn-light expense-print-button"><span class="material-symbols-outlined" aria-hidden="true">print</span>Print List</button>
<button type="button" id="print-summary-btn" class="btn btn-light expense-print-button"><span class="material-symbols-outlined" aria-hidden="true">print</span>Print Summary</button>
</div>
</div>
<div class="expense-table-container">
<table id="ledger-table" class="table align-middle">
<thead>
<tr>
<th>Date</th>
<th>Description</th>
<th>Category</th>
<th>Branch</th>
<th>Amount</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
<tbody>
</tbody>
</table>
</div>
</section>
<div class="modal fade expense-modal" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseTitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
<div class="modal-content">
<div class="modal-header">
<div class="expense-modal-heading">
<span class="material-symbols-outlined">receipt_long</span>
<div>
<h2 id="addExpenseTitle">Add Expense</h2>
<p>Enter what you spent and when.</p>
</div>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
</button>
</div>
<form id="addExpenseForm">
<div class="modal-body">
<div class="row g-3">
<div class="col-md-6">
<label for="add_category" class="form-label">Category</label>
<select class="form-select" name="category" id="add_category" required>
<option value="Supplies">Supplies</option>
<option value="Utilities">Water, Electricity and Other Bills</option>
<option value="Repair">Repairs</option>
<option value="Salary">Staff Pay</option>
<option value="Marketing">Advertising</option>
<option value="Other">Other</option>
</select>
</div>
<div class="col-md-6">
<label for="add_branch_id" class="form-label">Branch</label>
<select class="form-select" name="branch_id" id="add_branch_id">
<option value="">No Specific Branch</option>
<?php foreach ($branches as $b): ?>
<option value="<?= (int)$b['id'] ?>" >
<?= htmlspecialchars($b['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6">
<label for="add_amount" class="form-label">Amount (₱)</label>
<input class="form-control" type="number" name="amount" id="add_amount" step="0.01" min="0.01" required placeholder="0.00">
</div>
<div class="col-md-6">
<label for="add_expense_date" class="form-label">Date</label>
<input class="form-control" type="date" name="expense_date" id="add_expense_date" value="<?= date('Y-m-d') ?>" required>
</div>
<div class="col-12">
<label class="form-label" for="add_description">Description</label>
<textarea class="form-control" name="description" id="add_description" rows="3" placeholder="What was this expense for? For example, cooking oil or an electricity bill.">
</textarea>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary">Save Expense</button>
</div>
</form>
</div>
</div>
</div>
<div class="modal fade expense-modal" id="editExpenseModal" tabindex="-1" aria-labelledby="editExpenseTitle" aria-hidden="true">
<div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg">
<div class="modal-content">
<div class="modal-header">
<div class="expense-modal-heading">
<span class="material-symbols-outlined">receipt_long</span>
<div>
<h2 id="editExpenseTitle">Edit Expense</h2>
<p>Update the expense details below.</p>
</div>
</div>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close">
</button>
</div>
<form id="editExpenseForm">
<div class="modal-body">
<div class="row g-3">
<input type="hidden" name="id" id="edit_expense_id">
<div class="col-md-6">
<label for="edit_category" class="form-label">Category</label>
<select class="form-select" name="category" id="edit_category" required>
<option value="Supplies">Supplies</option>
<option value="Utilities">Water, Electricity and Other Bills</option>
<option value="Repair">Repairs</option>
<option value="Salary">Staff Pay</option>
<option value="Marketing">Advertising</option>
<option value="Other">Other</option>
</select>
</div>
<div class="col-md-6">
<label for="edit_branch_id" class="form-label">Branch</label>
<select class="form-select" name="branch_id" id="edit_branch_id">
<option value="">No Specific Branch</option>
<?php foreach ($branches as $b): ?>
<option value="<?= (int)$b['id'] ?>" >
<?= htmlspecialchars($b['name']) ?>
</option>
<?php endforeach; ?>
</select>
</div>
<div class="col-md-6">
<label for="edit_amount" class="form-label">Amount (₱)</label>
<input class="form-control" type="number" name="amount" id="edit_amount" step="0.01" min="0.01" required placeholder="0.00">
</div>
<div class="col-md-6">
<label for="edit_expense_date" class="form-label">Date</label>
<input class="form-control" type="date" name="expense_date" id="edit_expense_date" required>
</div>
<div class="col-12">
<label class="form-label" for="edit_description">Description</label>
<textarea class="form-control" name="description" id="edit_description" rows="3" placeholder="What was this expense for? For example, cooking oil or an electricity bill.">
</textarea>
</div>
</div>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
<button type="submit" class="btn btn-primary">Save Changes</button>
</div>
</form>
</div>
</div>
</div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        $(function() {
            const categoryLabels = { Supplies: 'Supplies', Utilities: 'Bills', Repair: 'Repairs', Salary: 'Staff Pay', Marketing: 'Advertising', Other: 'Other' };
            function escapeExpense(value) { return $('<div>').text(value == null ? '' : String(value)).html(); }

            function closeModal(id) {
                bootstrap.Modal.getInstance(document.getElementById(id))?.hide();
            }

            // High-Fidelity Chart.js initialization
            const ctx = document.getElementById('incomeExpensesChart').getContext('2d');

            // Gradient definitions
            const gradientIncome = ctx.createLinearGradient(0, 0, 0, 400);
            gradientIncome.addColorStop(0, '#5d87ff');
            gradientIncome.addColorStop(1, '#7b9eff');

            const gradientExpense = ctx.createLinearGradient(0, 0, 0, 400);
            gradientExpense.addColorStop(0, '#f38b78');
            gradientExpense.addColorStop(1, '#ed7560');

            if (window.Chart) new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($cashflow_data['labels']) ?>,
                    datasets: [{
                            label: 'Sales',
                            data: <?= json_encode($cashflow_data['income']) ?>,
                            backgroundColor: '#5d87ff',
                            borderRadius: 12,
                            barThickness: 16,
                            hoverBackgroundColor: '#7b9eff'
                        },
                        {
                            label: 'Expenses',
                            data: <?= json_encode($cashflow_data['expenses']) ?>,
                            backgroundColor: '#f38b78',
                            borderRadius: 12,
                            barThickness: 16,
                            hoverBackgroundColor: '#ed7560'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: '#0f172a',
                            padding: 12,
                            titleFont: {
                                family: 'Plus Jakarta Sans, sans-serif',
                                weight: 'bold',
                                size: 12
                            },
                            bodyFont: {
                                family: 'Plus Jakarta Sans, sans-serif',
                                size: 12
                            },
                            cornerRadius: 12,
                            displayColors: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            suggestedMax: 100,
                            grid: {
                                color: '#f1f5f9',
                                drawBorder: false
                            },
                            ticks: {
                                font: {
                                    family: 'Plus Jakarta Sans, sans-serif',
                                    weight: 'bold',
                                    size: 10
                                },
                                color: '#94a3b8',
                                callback: value => '₱' + Number(value).toLocaleString('en-US', { maximumFractionDigits: 0 })
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Plus Jakarta Sans, sans-serif',
                                    weight: 'bold',
                                    size: 11
                                },
                                color: '#64748b'
                            }
                        }
                    }
                }
            });

            // High-Performance Transaction Ledger Initialization
            const ledgerTable = $('#ledger-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "<?= url('?route=admin/expenses/data') ?>",
                    type: "GET",
                    data: function(d) {
                        d.branch_id = $('#branch-filter').val();
                        d.start_date = $('#start-date').val();
                        d.end_date = $('#end-date').val();
                    }
                },
                columns: [
                    { data: 'expense_date', render: function(data, type) { if(type !== 'display') return data; const date = new Date(String(data).slice(0,10) + 'T00:00:00'); return isNaN(date) ? escapeExpense(data) : date.toLocaleDateString('en-US', {month:'short',day:'2-digit',year:'numeric'}); } },
                    { data: 'description', render: data => escapeExpense(data || 'No description') },
                    { data: 'category', render: data => `<span class="expense-category">${escapeExpense(categoryLabels[data] || data)}</span>` },
                    { data: 'branch_name', render: data => escapeExpense(data || 'No Specific Branch') },
                    { data: 'amount', className: 'expense-amount', render: (data, type) => type === 'display' ? `₱${Number(data).toLocaleString('en-US', {minimumFractionDigits:2,maximumFractionDigits:2})}` : data },
                    { data: null, orderable: false, className: 'text-center', render: () => '<span class="expense-recorded">Recorded</span>' },
                    { data: null, orderable: false, className: 'text-center', render: (data,type,row) => `<div class="expense-row-actions"><button type="button" class="edit-expense-btn" data-id="${Number(row.id)}" aria-label="Edit expense" title="Edit expense"><span class="material-symbols-outlined">edit</span></button><button type="button" class="delete-expense-btn" data-id="${Number(row.id)}" aria-label="Delete expense" title="Delete expense"><span class="material-symbols-outlined">delete</span></button></div>` }
                ],
                order: [
                    [0, 'desc']
                ],
                autoWidth: false,
                dom: '<"expense-table-scroll"rt><"expense-table-footer"lip>',
                language: {
                    lengthMenu: "Show _MENU_",
                    emptyTable: "No expenses recorded yet.",
                    zeroRecords: "No expenses match your search.",
                    info: "Showing _START_–_END_ of _TOTAL_ expenses",
                    infoEmpty: "0 expenses",
                    processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
                    paginate: {
                        previous: '<span class="material-symbols-outlined">chevron_left</span>',
                        next: '<span class="material-symbols-outlined">chevron_right</span>'
                    }
                },
                pageLength: 10,

            });

            // Custom Ledger Search integration
            $("#ledger-search").on("input", function() {
                ledgerTable.search($(this).val()).draw();
            });

            // Enhanced Filter Logic
            function applyFilters() {
                const branchId = $('#branch-filter').val();
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                
                let url = "<?= url('?route=admin/expenses') ?>";
                const params = [];
                if (branchId) params.push("branch_id=" + branchId);
                if (startDate) params.push("start_date=" + startDate);
                if (endDate) params.push("end_date=" + endDate);
                
                if (params.length > 0) {
                    url += (url.includes('?') ? '&' : '?') + params.join('&');
                }
                window.location.href = url;
            }

            $("#branch-filter, #start-date, #end-date").on("change", function() {
                // If this was a date change, only trigger if both dates are set
                if ($(this).attr('type') === 'date') {
                    if ($('#start-date').val() && $('#end-date').val()) {
                        applyFilters();
                    }
                } else {
                    applyFilters();
                }
            });

            $("#clear-dates-btn").on("click", function() {
                $('#start-date, #end-date').val('');
                applyFilters();
            });

            $("#today-btn").on("click", function() {
                const today = new Date().toISOString().split('T')[0];
                $('#start-date').val(today);
                $('#end-date').val(today);
                applyFilters();
            });

            // Print Logic
            $("#print-ledger-btn, #print-report-btn").on("click", function() {
                const branchId = $('#branch-filter').val();
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                
                let printUrl = "<?= url('?route=admin/expenses/print') ?>";
                const params = [];
                if (branchId) params.push("branch_id=" + branchId);
                if (startDate) params.push("start_date=" + startDate);
                if (endDate) params.push("end_date=" + endDate);
                
                if (params.length > 0) {
                    printUrl += "&" + params.join('&');
                }
                
                const search = $('#ledger-search').val().trim();
                if (search) printUrl += '&search=' + encodeURIComponent(search);
                window.open(printUrl, '_blank');
            });

            // Summary Audit Print Logic
            $("#print-summary-btn").on("click", function() {
                const branchId = $('#branch-filter').val();
                const startDate = $('#start-date').val();
                const endDate = $('#end-date').val();
                
                let printUrl = "<?= url('?route=admin/expenses/summary_print') ?>";
                const params = [];
                if (branchId) params.push("branch_id=" + branchId);
                if (startDate) params.push("start_date=" + startDate);
                if (endDate) params.push("end_date=" + endDate);
                
                if (params.length > 0) {
                    printUrl += (printUrl.includes('?') ? '&' : '?') + params.join('&');
                }
                
                window.open(printUrl, '_blank');
            });

            // Edit Expense Logic
            $(document).on("click", ".edit-expense-btn", function() {
                const id = $(this).data('id');
                $.get("<?= url('?route=ajax/expense_action&action=single') ?>", { id: id }, function(res) {
                    if (res.success) {
                        const e = res.expense;
                        $("#edit_expense_id").val(e.id);
                        $("#edit_category").val(e.category);
                        $("#edit_branch_id").val(e.branch_id);
                        $("#edit_amount").val(e.amount);
                        $("#edit_expense_date").val(e.expense_date);
                        $("#edit_description").val(e.description);
                        bootstrap.Modal.getOrCreateInstance(document.getElementById('editExpenseModal')).show();
                    } else {
                        showToast(res.message || 'Could not open this expense.', 'error');
                    }
                });
            });

            // Delete Expense Logic
            $(document).on("click", ".delete-expense-btn", function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Delete Expense?',
                    text: "This expense will be permanently deleted.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#5d87ff',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Delete Expense',
                    cancelButtonText: 'Cancel',
                    background: '#fff',
                    borderRadius: '24px',
                    customClass: {
                        popup: 'expense-confirm-dialog',
                        confirmButton: 'rounded-pill px-4',
                        cancelButton: 'rounded-pill px-4'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post("<?= url('?route=ajax/expense_action&action=delete') ?>", { id: id }, function(res) {
                            if (res.success) {
                                showToast('Expense deleted.', 'success');
                                ledgerTable.ajax.reload();
                            } else {
                                showToast(res.message || 'Could not delete this expense.', 'error');
                            }
                        });
                    }
                });
            });

            $("#addExpenseForm").on("submit", function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="animate-pulse">Saving...</span>');

                $.ajax({
                    url: "<?= url('?route=ajax/expense_action&action=create') ?>",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showToast('Expense saved.', 'success');
                            closeModal('addExpenseModal');
                            ledgerTable.ajax.reload(); // Reload table instead of full page
                            btn.prop('disabled', false).html(originalHtml);
                            $("#addExpenseForm")[0].reset();
                        } else {
                            showToast(res.message || 'Could not save this expense.', 'error');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        showToast('Could not connect. Please try again.', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            $("#editExpenseForm").on("submit", function(e) {
                e.preventDefault();
                const btn = $(this).find('button[type="submit"]');
                const originalHtml = btn.html();
                btn.prop('disabled', true).html('<span class="animate-pulse">Saving...</span>');

                $.ajax({
                    url: "<?= url('?route=ajax/expense_action&action=update') ?>",
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.success) {
                            showToast('Expense updated.', 'success');
                            closeModal('editExpenseModal');
                            ledgerTable.ajax.reload();
                            btn.prop('disabled', false).html(originalHtml);
                        } else {
                            showToast(res.message || 'Could not save the changes.', 'error');
                            btn.prop('disabled', false).html(originalHtml);
                        }
                    },
                    error: function() {
                        showToast('Could not connect. Please try again.', 'error');
                        btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });
        });
    </script>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>