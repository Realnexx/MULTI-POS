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

    $can_view = has_permission('Student Attendance', 'view', $conn);
    $can_export = has_permission('Student Attendance', 'export', $conn);

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
    <title>Attendance Logs - Aurora Tabs</title>
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
        #attendanceTable {
            width: 100% !important;
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

                    <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">OJT Attendance Logs</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Attendance Tracking</li>
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
                                <h5 class="card-title fw-semibold">Daily Attendance Records</h5>
                                <div class="d-flex gap-2">
                                    <div id="customFilterContainer" class="d-flex gap-2" style="display: none !important;">
                                        <input type="date" class="form-control" id="customDate" style="display: none;">
                                        <input type="month" class="form-control" id="customMonth" style="display: none;">
                                    </div>
                                    <select class="form-select w-auto" id="genderFilter">
                                        <option value="all">All Genders</option>
                                        <option value="Male">Male</option>
                                        <option value="Female">Female</option>
                                    </select>
                                    <select class="form-select w-auto" id="logFilter">
                                        <option value="all">View All Logs</option>
                                        <option value="today">Today Only</option>
                                        <option value="week">This Week</option>
                                        <option value="month">This Month</option>
                                        <option value="date">Custom Date</option>
                                        <option value="custom_month">Custom Month</option>
                                    </select>
                                    <button class="btn btn-outline-primary" onclick="table.ajax.reload()">
                                        <i class="ti ti-refresh me-1"></i>
                                    </button>
                                    <?php if ($can_export): ?>
                                    <button class="btn btn-primary" id="btnPrint">
                                        <i class="ti ti-printer me-1"></i> Print Logs
                                    </button>
                                    <button class="btn btn-success" id="btnExport">
                                        <i class="ti ti-file-spreadsheet me-1"></i> Export Excel
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table align-middle mb-0 table-hover" id="attendanceTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Date</th>
                                            <th class="border-bottom-0">Student Name</th>
                                            <th class="border-bottom-0 text-center">In (AM)</th>
                                            <th class="border-bottom-0 text-center">Break</th>
                                            <th class="border-bottom-0 text-center">Out (PM)</th>
                                            <th class="border-bottom-0 text-center">Total Hours</th>
                                            <th class="border-bottom-0">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
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
            table = $('#attendanceTable').DataTable({
                "ajax": {
                    "url": "endpoints/ojt_handler.php",
                    "type": "POST",
                    "data": function(d) {
                        d.action = "get_attendance_logs";
                        d.filter = $('#logFilter').val();
                        d.sex = $('#genderFilter').val();
                        d.custom_date = $('#customDate').val();
                        d.custom_month = $('#customMonth').val();
                    },
                    "dataSrc": function(json) {
                        return json.status === 'success' ? json.data : [];
                    }
                },
                "columns": [
                    { "data": "date", "render": function(data) {
                        return `<span class="fw-bold text-dark">${data}</span>`;
                    }},
                    { "data": "fullname", "render": function(data, type, row) {
                        let sexIcon = row.sex === 'Male' ? 
                            '<iconify-icon icon="solar:user-bold-duotone" class="text-primary fs-2"></iconify-icon>' : 
                            '<iconify-icon icon="solar:user-bold-duotone" class="text-danger fs-2"></iconify-icon>';
                        return `
                            <div class="d-flex align-items-center gap-2">
                                ${sexIcon}
                                <div>
                                    <h6 class="fw-semibold mb-0">${data}</h6>
                                    <p class="mb-0 fs-2 text-muted">${row.student_id} | ${row.course}</p>
                                </div>
                            </div>
                        `;
                    }},
                    { "data": "check_in", "className": "text-center" },
                    { "data": null, "className": "text-center", "render": function(data) {
                        if(data.break_out !== '--:--' || data.break_in !== '--:--') {
                            return `<div class="fs-2 text-muted">${data.break_out} - ${data.break_in}</div>`;
                        }
                        return '--:--';
                    }},
                    { "data": "check_out", "className": "text-center" },
                    { "data": "total_hours", "className": "text-center", "render": function(data) {
                        return `<span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">${data} hrs</span>`;
                    }},
                    { "data": "status", "render": function(data, type, row) {
                        return `<span class="badge ${row.status_class} fw-semibold">${data}</span>`;
                    }}
                ],
                "order": [[0, "desc"]],
                "pageLength": 25,
                "processing": true
            });

            // Filter Change
            $('#logFilter, #genderFilter').change(function() {
                const val = $('#logFilter').val();
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

            // Custom Input Change
            $('#customDate, #customMonth').change(function() {
                table.ajax.reload();
            });

            // Print Click
            $('#btnPrint').click(function() {
                const filter = $('#logFilter').val();
                const sex = $('#genderFilter').val();
                const custom_date = $('#customDate').val();
                const custom_month = $('#customMonth').val();
                
                window.open(`print_ojt_attendance.php?filter=${filter}&sex=${sex}&custom_date=${custom_date}&custom_month=${custom_month}`, 'PrintWindow', 'width=1100,height=850,resizable=yes,scrollbars=yes');
            });

            // Export Click
            $('#btnExport').click(function() {
                const filter = $('#logFilter').val();
                const sex = $('#genderFilter').val();
                const custom_date = $('#customDate').val();
                const custom_month = $('#customMonth').val();
                
                window.location.href = `export_ojt_attendance.php?filter=${filter}&sex=${sex}&custom_date=${custom_date}&custom_month=${custom_month}`;
            });
        });
    </script>
</body>

</html>
