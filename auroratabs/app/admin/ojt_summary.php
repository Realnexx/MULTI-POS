<?php
    ob_start();
    session_start();
    
    // Auth check
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $timezone = "Asia/Manila";
    if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    $can_view = has_permission('Hours Summary', 'view', $conn);
    $can_export = has_permission('Hours Summary', 'export', $conn);

    if (!$can_view) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hours Summary - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .table-responsive {
            overflow-x: hidden !important;
        }
        @media (max-width: 991px) {
            .table-responsive {
                overflow-x: auto !important;
            }
        }
        #summaryTable {
            width: 100% !important;
        }
        .progress {
            height: 10px;
            border-radius: 10px;
        }
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

                    <div class="top-part card bg-light-success shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">OJT Progress Dashboard</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Hours Tracking</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3">
                                    <div class="text-center mb-n5">
                                        <img src="src/assets/images/backgrounds/welcome-bg.svg" alt="" class="img-fluid mb-n4" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h5 class="card-title fw-semibold mb-1">OJT Hours Summary</h5>
                                    <div class="d-flex align-items-center">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold fs-2">Required: 486 Hours</span>
                                    </div>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div id="customFilterContainer" class="d-flex gap-2" style="display: none !important;">
                                        <input type="date" class="form-control" id="customDate" style="display: none;">
                                        <input type="month" class="form-control" id="customMonth" style="display: none;">
                                    </div>
                                    <select class="form-select w-auto" id="genderFilter">
                                        <option value="all">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <select class="form-select w-auto" id="summaryFilter">
                                        <option value="all">Reference: Today</option>
                                        <option value="date">Ref: Custom Date</option>
                                        <option value="custom_month">Ref: Custom Month</option>
                                    </select>
                                    <button class="btn btn-outline-success" onclick="table.ajax.reload()">
                                        <i class="ti ti-refresh"></i>
                                    </button>
                                    <?php if ($can_export): ?>
                                    <button class="btn btn-primary" id="btnPrint">
                                        <i class="ti ti-printer me-1"></i> Print Summary
                                    </button>
                                    <button class="btn btn-primary" id="btnPrintAll">
                                        <i class="ti ti-printer me-1"></i> Print All Summary
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover" id="summaryTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Student</th>
                                            <th class="border-bottom-0 text-center">In Ref. Day</th>
                                            <th class="border-bottom-0 text-center">In Ref. Week</th>
                                            <th class="border-bottom-0 text-center">In Ref. Month</th>
                                            <th class="border-bottom-0 text-center">Rendered</th>
                                            <th class="border-bottom-0 text-center">Remaining</th>
                                            <th class="border-bottom-0" style="width: 200px;">Progress</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data loaded via AJAX -->
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
        let table;
        $(document).ready(function() {
            table = $('#summaryTable').DataTable({
                "ajax": {
                    "url": "endpoints/ojt_handler.php",
                    "type": "POST",
                    "data": function(d) {
                        d.action = "get_hours_summary";
                        d.filter = $('#summaryFilter').val();
                        d.sex = $('#genderFilter').val();
                        d.custom_date = $('#customDate').val();
                        d.custom_month = $('#customMonth').val();
                    },
                    "dataSrc": function(json) {
                        return json.status === 'success' ? json.data : [];
                    }
                },
                "columns": [
                    { "data": "fullname", "render": function(data, type, row) {
                        let sexIcon = row.sex === 'Male' ? 
                            '<iconify-icon icon="solar:user-bold-duotone" class="text-primary fs-2"></iconify-icon>' : 
                            '<iconify-icon icon="solar:user-bold-duotone" class="text-danger fs-2"></iconify-icon>';
                        return `
                            <div class="d-flex align-items-center gap-2">
                                ${sexIcon}
                                <div>
                                    <h6 class="fw-semibold mb-0">${data}</h6>
                                    <p class="mb-0 fs-2 text-muted">${row.student_id}</p>
                                </div>
                            </div>
                        `;
                    }},
                    { "data": "today", "className": "text-center" },
                    { "data": "week", "className": "text-center" },
                    { "data": "month", "className": "text-center" },
                    { "data": "cumulative", "className": "text-center", "render": function(data) {
                        return `<span class="badge bg-success-subtle text-success border border-success-subtle fw-bold">${data} hrs</span>`;
                    }},
                    { "data": "remaining", "className": "text-center", "render": function(data) {
                        return `<span class="badge bg-danger-subtle text-danger border border-danger-subtle fw-bold">${data} hrs</span>`;
                    }},
                    { "data": "progress", "render": function(data) {
                        let color = 'bg-primary';
                        if (data >= 100) color = 'bg-success';
                        else if (data >= 75) color = 'bg-info';
                        else if (data >= 50) color = 'bg-warning';
                        else if (data > 0) color = 'bg-primary';
                        else color = 'bg-light-secondary';

                        return `
                            <div class="d-flex align-items-center gap-2">
                                <div class="progress w-100 bg-light-secondary">
                                    <div class="progress-bar ${color}" role="progressbar" style="width: ${data}%" aria-valuenow="${data}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <span class="fw-semibold fs-2 text-muted">${data}%</span>
                            </div>
                        `;
                    }}
                ],
                "order": [[6, "desc"]],
                "pageLength": 25,
                "processing": true
            });

            // Filter Change
            $('#summaryFilter, #genderFilter').change(function() {
                const val = $('#summaryFilter').val();
                $('#customFilterContainer').attr('style', 'display: none !important;');
                $('#customDate, #customMonth').hide();

                if (val === 'date') {
                    $('#customFilterContainer').attr('style', 'display: flex !important;');
                    $('#customDate').show();
                } else if (val === 'custom_month') {
                    $('#customFilterContainer').attr('style', 'display: flex !important;');
                    $('#customMonth').show();
                }
                
                table.ajax.reload();
            });

            // Print Click
            $('#btnPrint').click(function() {
                const filter = $('#summaryFilter').val();
                const sex = $('#genderFilter').val();
                const custom_date = $('#customDate').val();
                const custom_month = $('#customMonth').val();
                
                window.open(`print_ojt_summary.php?filter=${filter}&sex=${sex}&custom_date=${custom_date}&custom_month=${custom_month}`, 'PrintWindow', 'width=1100,height=850,resizable=yes,scrollbars=yes');
            });

             // Print Click
            $('#btnPrintAll').click(function() {
                const filter = $('#summaryFilter').val();
                const sex = $('#genderFilter').val();
                
                window.open(`print_ojt_summary_all.php?filter=${filter}&sex=${sex}`, 'PrintWindow', 'width=1100,height=850,resizable=yes,scrollbars=yes');
            });

            // Custom Input Change
            $('#customDate, #customMonth').change(function() {
                table.ajax.reload();
            });
        });
    </script>
</body>

</html>
