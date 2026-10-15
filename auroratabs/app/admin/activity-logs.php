<?php
    session_start();
    
    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activity Logs - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .badge-create { background-color: #d1f3e0; color: #137333; }
        .badge-update { background-color: #e8f0fe; color: #1a73e8; }
        .badge-delete { background-color: #fce8e6; color: #d93025; }
        .badge-login { background-color: #fef7e0; color: #f29900; }
        .log-details { font-size: 0.85rem; color: #5f6368; }
    </style>
</head>

<body>
    <!--  Body Wrapper -->
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <!-- Sidebar Start -->
        <?php include 'components/sidebar.php'; ?>
        <!--  Sidebar End -->
        <!--  Main wrapper -->
        <div class="body-wrapper">

            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <!--  Header Start -->
                    <?php include 'components/header.php'; ?>
                    <!--  Header End -->

                    <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">System Audit Trail</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Activity Logs</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3">
                                    <div class="text-center mb-n5">
                                        <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-semibold">Recent Activities</h5>
                                <div class="btn-group">
                                    <button id="exportLogs" class="btn btn-outline-secondary btn-sm"><i class="ti ti-download me-1"></i> Export Logs</button>
                                    <button id="deleteSelected" class="btn btn-outline-danger btn-sm d-none"><i class="ti ti-trash me-1"></i> Delete Selected (<span id="selectedCount">0</span>)</button>
                                    <button id="clearLogs" class="btn btn-outline-danger btn-sm"><i class="ti ti-layers-subtract me-1"></i> Clear Old Logs</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-responsive-stack" id="logsTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 40px;">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" id="selectAll">
                                                </div>
                                            </th>
                                            <th>Timestamp</th>
                                            <th>User</th>
                                            <th>Action</th>
                                            <th>Module</th>
                                            <th>Details</th>
                                            <th>IP Address</th>
                                        </tr>
                                    </thead>
                                    <tbody id="logsTableBody">
                                        <!-- Dynamic Content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <?php include 'components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            const logsTable = $('#logsTable').DataTable({
                "pageLength": 25,
                "order": [[1, "desc"]],
                "columnDefs": [
                    { "orderable": false, "targets": 0 }
                ],
                "dom": '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4"fl>rtip',
                "language": {
                    "search": "Filter Logs:",
                },
                "createdRow": function(row, data, dataIndex) {
                    const headers = $(row).closest('table').find('thead th');
                    $(row).find('td').each(function(index) {
                        $(this).attr('data-label', $(headers[index]).text());
                    });
                }
            });

            loadLogs();

            function loadLogs() {
                $.post('endpoints/activity_logs_handler.php', { action: 'list' }, function(response) {
                    if (response.status === 'success') {
                        logsTable.clear();
                        response.data.forEach(log => {
                            let actionBadge = '';
                            const action = log.action.toLowerCase();
                            
                            if (action === 'created') actionBadge = '<span class="badge badge-create">Created</span>';
                            else if (action === 'updated') actionBadge = '<span class="badge badge-update">Updated</span>';
                            else if (action === 'deleted') actionBadge = '<span class="badge badge-delete text-white">Deleted</span>';
                            else if (action === 'login') actionBadge = '<span class="badge badge-login">Login</span>';
                            else actionBadge = `<span class="badge bg-light-secondary text-secondary">${log.action}</span>`;

                            const adminInfo = `<div class="d-flex align-items-center">
                                                <span class="avatar avatar-xs rounded-circle bg-light-secondary text-secondary d-flex align-items-center justify-content-center fw-bold me-2" style="width:30px; height:30px; font-size:10px;">
                                                    ${log.admin_name ? log.admin_name.charAt(0).toUpperCase() : '?'}
                                                </span>
                                                <span class="fw-semibold">${log.admin_name || 'System'}</span>
                                            </div>`;

                            logsTable.row.add([
                                `<div class="form-check"><input class="form-check-input log-checkbox" type="checkbox" value="${log.id}"></div>`,
                                `<span class="fs-2 text-muted">${log.created_at}</span>`,
                                adminInfo,
                                actionBadge,
                                `<span class="badge bg-light text-dark border">${log.module}</span>`,
                                `<span class="log-details">${log.details}</span>`,
                                `<code class="fs-2 text-muted">${log.ip_address}</code>`
                            ]);
                        });
                        logsTable.draw();
                        updateSelectedCount();
                    }
                });
            }

            // Select All
            $('#selectAll').on('change', function() {
                $('.log-checkbox').prop('checked', $(this).is(':checked'));
                updateSelectedCount();
            });

            // Individual Checkbox
            $(document).on('change', '.log-checkbox', function() {
                updateSelectedCount();
                if ($('.log-checkbox:checked').length === $('.log-checkbox').length) {
                    $('#selectAll').prop('checked', true);
                } else {
                    $('#selectAll').prop('checked', false);
                }
            });

            function updateSelectedCount() {
                const count = $('.log-checkbox:checked').length;
                $('#selectedCount').text(count);
                if (count > 0) {
                    $('#deleteSelected').removeClass('d-none');
                } else {
                    $('#deleteSelected').addClass('d-none');
                }
            }

            // Export Logs
            $('#exportLogs').on('click', function() {
                window.location.href = 'endpoints/activity_logs_handler.php?action=export';
            });

            // Delete Selected
            $('#deleteSelected').on('click', function() {
                const ids = [];
                $('.log-checkbox:checked').each(function() {
                    ids.push($(this).val());
                });

                if (ids.length === 0) return;

                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are about to delete ${ids.length} log entries.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete them',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/activity_logs_handler.php', { action: 'delete_bulk', ids: ids }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadLogs();
                                $('#selectAll').prop('checked', false);
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            // Clear Old Logs
            $('#clearLogs').on('click', function() {
                Swal.fire({
                    title: 'Select Cutoff Date',
                    html: `
                        <p class="text-muted fs-2">All logs created <b>BEFORE</b> the selected date will be permanently deleted.</p>
                        <input type="date" id="cutoffDate" class="form-control" value="${new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0]}">
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Clear Logs',
                    confirmButtonColor: '#d33',
                    preConfirm: () => {
                        const date = $('#cutoffDate').val();
                        if (!date) {
                            Swal.showValidationMessage('Selection is required');
                        }
                        return date;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/activity_logs_handler.php', { action: 'clear', date: result.value }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Cleared!', response.message, 'success');
                                loadLogs();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
