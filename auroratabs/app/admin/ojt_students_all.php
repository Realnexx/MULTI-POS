<?php
    require_once("../auth/config/ojt_db.php"); // Use the new connection
    ob_start();
    session_start();
    
    // Auth check (assuming standard session)
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }

    $timezone = "Asia/Manila";
    if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);

    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    $can_view = has_permission('Manage Students', 'view', $conn);
    $can_manage = has_permission('Manage Students', 'manage', $conn);

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
    <title>All OJT Students - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
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
                                    <h4 class="fw-semibold mb-8">OJT Student Registry</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">All Students</li>
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
                                <h5 class="card-title fw-semibold">Student Database (ECR)</h5>
                                <?php if ($can_manage): ?>
                                <button id="btnExport" class="btn btn-primary">
                                    <i class="ti ti-database-export me-1"></i> Export to Aurora DB
                                </button>
                                <?php endif; ?>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle mb-0 table-hover" id="studentsAllTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Student ID</th>
                                            <th class="border-bottom-0">Full Name</th>
                                            <th class="border-bottom-0">Course / Major</th>
                                            <th class="border-bottom-0">Sex</th>
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
        $(document).ready(function() {
            console.log("Initializing Student Registry AJAX request...");
            const table = $('#studentsAllTable').DataTable({
                "ajax": {
                    "url": "endpoints/ojt_handler.php",
                    "type": "POST",
                    "data": { "action": "get_students" },
                    "dataSrc": function(json) {
                        console.log("AJAX Response Received:", json);
                        if(json.status === 'success') {
                            console.log(`Successfully loaded ${json.data.length} student records.`);
                            return json.data;
                        } else {
                            console.error("Backend Error:", json.message);
                            return [];
                        }
                    },
                    "error": function(xhr, error, thrown) {
                        console.error("AJAX Request Failed!");
                        console.error("Status:", xhr.status);
                        console.error("Error:", error);
                        console.log("Response Text:", xhr.responseText);
                    }
                },
                "columns": [
                    { "data": "id", "render": function(data) {
                        return `<span class="fw-bold">${data}</span>`;
                    }},
                    { "data": "fullname", "render": function(data) {
                        return `<h6 class="fw-semibold mb-0">${data}</h6>`;
                    }},
                    { "data": null, "render": function(data) {
                        return `<div class="d-flex flex-column">
                                    <span>${data.course}</span>
                                    <small class="text-muted">${data.major}</small>
                                </div>`;
                    }},
                    { "data": "sex" },
                    { "data": "status", "render": function(data, type, row) {
                        return `<span class="badge ${row.status_class} fw-semibold">${data}</span>`;
                    }}
                ],
                "pageLength": 25,
                "ordering": true,
                "language": {
                    "search": "Filter Students:",
                    "processing": '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>'
                },
                "processing": true
            });

            // Export logic
            $('#btnExport').click(function() {
                Swal.fire({
                    title: 'Export to Aurora DB?',
                    text: "This will sync the current list of students to your local database.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Export!',
                    showLoaderOnConfirm: true,
                    preConfirm: () => {
                        return $.post('endpoints/ojt_handler.php', { action: 'export_students' })
                            .then(response => {
                                if (response.status !== 'success') {
                                    throw new Error(response.message);
                                }
                                return response;
                            })
                            .catch(error => {
                                Swal.showValidationMessage(`Request failed: ${error}`);
                            });
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire(
                            'Success!',
                            result.value.message,
                            'success'
                        );
                    }
                });
            });
        });
    </script>
</body>

</html>
