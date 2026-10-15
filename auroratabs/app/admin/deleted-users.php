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
    <title>Deleted Users - Aurora Tabs</title>
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

                    <div class="top-part card bg-light-danger shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Deleted Users Archive</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="admin-users.php">Admin Users</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Trash</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3">
                                    <div class="text-center mb-n5">
                                        <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" style="filter: grayscale(100%); opacity: 0.5;" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                                <h5 class="card-title fw-semibold mb-0">Removed Accounts</h5>
                                <a href="admin-users.php" class="btn btn-outline-primary">
                                    <i class="ti ti-arrow-left me-1"></i> Back to Users
                                </a>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle mb-0 table-hover table-responsive-stack" id="deletedUsersTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Full Name</th>
                                            <th class="border-bottom-0">Username</th>
                                            <th class="border-bottom-0">Previous Role</th>
                                            <th class="border-bottom-0">Deleted At</th>
                                            <th class="border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tbody id="deletedUsersTableBody">
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
            const deletedTable = $('#deletedUsersTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "language": {
                    "search": "Filter Trash:",
                },
                "createdRow": function(row, data, dataIndex) {
                    // Automatically add data-label to each cell based on header text
                    const headers = $(row).closest('table').find('thead th');
                    $(row).find('td').each(function(index) {
                        $(this).attr('data-label', $(headers[index]).text());
                    });
                }
            });

            loadDeletedUsers();

            function loadDeletedUsers() {
                $.post('endpoints/admin_users_handler.php', { action: 'list_deleted' }, function(response) {
                    if (response.status === 'success') {
                        deletedTable.clear();
                        response.data.forEach(user => {
                            const avatar = `<div class="d-flex align-items-center">
                                                <span class="avatar avatar-xs rounded-circle bg-light-danger text-danger d-flex align-items-center justify-content-center fw-bold" style="width:35px; height:35px;">
                                                    ${user.first_name.charAt(0)}${user.last_name.charAt(0)}
                                                </span>
                                                <div class="ms-3">
                                                    <h6 class="fw-semibold mb-0">${user.first_name} ${user.last_name}</h6>
                                                    <span class="fs-2 text-muted">${user.email}</span>
                                                </div>
                                            </div>`;

                            deletedTable.row.add([
                                avatar,
                                user.username,
                                `<span class="badge bg-light-info text-info">${user.role_name}</span>`,
                                user.updated_at, // Using updated_at as placeholder for deletion time if no specific column
                                `<div class="d-flex gap-2 justify-content-start">
                                    <button class="btn btn-sm btn-success restore-user" data-id="${user.id}" title="Restore User"><i class="ti ti-restore"></i> Restore</button>
                                    <button class="btn btn-sm btn-danger permanent-delete" data-id="${user.id}" title="Permanently Delete"><i class="ti ti-trash"></i></button>
                                </div>`
                            ]);
                        });
                        deletedTable.draw();
                    }
                });
            }

            // Restore User
            $(document).on('click', '.restore-user', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Restore User?',
                    text: "The user will be moved back to the active list.",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, restore'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/admin_users_handler.php', { action: 'restore', id: id }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Restored!', response.message, 'success');
                                loadDeletedUsers();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            // Permanent Delete
            $(document).on('click', '.permanent-delete', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Permanent Delete?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete forever'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/admin_users_handler.php', { action: 'permanent_delete', id: id }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadDeletedUsers();
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
