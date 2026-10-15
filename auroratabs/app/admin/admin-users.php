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
    <title>Admin Users - Aurora Tabs</title>
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
                                    <h4 class="fw-semibold mb-8">User Management</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Admin Users</li>
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
                            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
                                <h5 class="card-title fw-semibold mb-0">System Users</h5>
                                <div class="d-flex flex-wrap gap-2">
                                    <a href="deleted-users.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-trash me-1"></i> Deleted Users
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                        <i class="ti ti-user-plus me-1"></i> Add New User
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle mb-0 table-hover table-responsive-stack" id="usersTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Full Name</th>
                                            <th class="border-bottom-0">Username</th>
                                            <th class="border-bottom-0">Role</th>
                                            <th class="border-bottom-0">Status</th>
                                            <th class="border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <tbody id="usersTableBody">
                                        <!-- Dynamic Content -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- User Modal (Add/Edit) -->
                    <div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="userForm" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="userModalTitle">Add New Administrator</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="userId">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">First Name</label>
                                            <input type="text" class="form-control" id="firstName" placeholder="First Name" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Last Name</label>
                                            <input type="text" class="form-control" id="lastName" placeholder="Last Name" required>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Username</label>
                                        <input type="text" class="form-control" id="username" required>
                                    </div>
                                    <div class="mb-3" id="passwordField">
                                        <label class="form-label">Password</label>
                                        <input type="password" class="form-control" id="password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">User Role</label>
                                        <select class="form-select" id="role" required>
                                            <!-- Roles populated via JS -->
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Account Status</label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" id="activeStatus" checked>
                                            <label class="form-check-label" for="activeStatus">Active</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="saveUserBtn">Save User</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Reset Password Modal -->
                    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <form id="resetPasswordForm" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">Reset Password</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="resetPwdUserId">
                                    <div class="mb-3">
                                        <label class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="newPassword" required minlength="6">
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm">Reset Password</button>
                                </div>
                            </form>
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
            const usersTable = $('#usersTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "language": {
                    "search": "Filter Users:",
                },
                "createdRow": function(row, data, dataIndex) {
                    // Automatically add data-label to each cell based on header text
                    const headers = $(row).closest('table').find('thead th');
                    $(row).find('td').each(function(index) {
                        $(this).attr('data-label', $(headers[index]).text());
                    });
                }
            });

            loadRoles();
            loadUsers();

            function loadRoles() {
                $.post('../auth/endpoints/get_roles_all.php', function(response) {
                    if (response.status === 'success') {
                        let options = '<option value="">Select Role</option>';
                        response.data.forEach(role => {
                            options += `<option value="${role.id}">${role.display_name}</option>`;
                        });
                        $('#role').html(options);
                    }
                });
            }

            let allUsers = []; // Global store for user data

            function loadUsers() {
                $.post('endpoints/admin_users_handler.php', { action: 'list' }, function(response) {
                    if (response.status === 'success') {
                        usersTable.clear();
                        allUsers = response.data; // Store for easy access
                        
                        response.data.forEach(user => {
                            const statusBadge = user.status === 'active' 
                                ? '<span class="badge bg-success">Active</span>' 
                                : '<span class="badge bg-danger">Suspended</span>';
                            
                            const avatar = `<div class="d-flex align-items-center">
                                                <span class="avatar avatar-xs rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold" style="width:35px; height:35px;">
                                                    ${user.first_name.charAt(0)}${user.last_name.charAt(0)}
                                                </span>
                                                <div class="ms-3">
                                                    <h6 class="fw-semibold mb-0">${user.first_name} ${user.last_name}</h6>
                                                    <span class="fs-2 text-muted">${user.email}</span>
                                                </div>
                                            </div>`;

                            usersTable.row.add([
                                avatar,
                                user.username,
                                `<span class="badge bg-light-primary text-primary">${user.role_name}</span>`,
                                statusBadge,
                                `<div class="d-flex gap-1 justify-content-start flex-wrap">
                                    <button class="btn btn-sm btn-outline-info edit-user" data-id="${user.id}" title="Edit Profile"><i class="ti ti-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-warning reset-pwd" data-id="${user.id}" title="Reset Password"><i class="ti ti-key"></i></button>
                                    <button class="btn btn-sm btn-outline-secondary toggle-status" data-id="${user.id}" data-status="${user.status}" title="Toggle Status"><i class="ti ti-toggle-left"></i></button>
                                    <button class="btn btn-sm btn-outline-danger delete-user" data-id="${user.id}" title="Delete Account"><i class="ti ti-trash"></i></button>
                                </div>`
                            ]);
                        });
                        usersTable.draw();
                    }
                });
            }

            // Reset Modal for Add
            $('[data-bs-target="#addUserModal"]').on('click', function() {
                $('#userForm')[0].reset();
                $('#userId').val('');
                $('#passwordField').show();
                $('#password').prop('required', true);
                $('#userModalTitle').text('Add New Administrator');
                $('#saveUserBtn').text('Save User');
                $('#userModal').modal('show');
            });

            // Edit User
            $(document).on('click', '.edit-user', function() {
                const id = $(this).data('id');
                const user = allUsers.find(u => u.id == id);
                
                if (!user) return;

                $('#userId').val(user.id);
                $('#firstName').val(user.first_name);
                $('#lastName').val(user.last_name);
                $('#email').val(user.email);
                $('#username').val(user.username);
                $('#role').val(user.role_id);
                $('#activeStatus').prop('checked', user.status === 'active');
                
                $('#passwordField').hide();
                $('#password').prop('required', false);
                $('#userModalTitle').text('Edit User: ' + user.username);
                $('#saveUserBtn').text('Save Changes');
                $('#userModal').modal('show');
            });

            // Submit Form
            $('#userForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#userId').val();
                const data = {
                    action: id ? 'update' : 'create',
                    id: id,
                    first_name: $('#firstName').val(),
                    last_name: $('#lastName').val(),
                    email: $('#email').val(),
                    username: $('#username').val(),
                    password: $('#password').val(),
                    role_id: $('#role').val(),
                    status: $('#activeStatus').is(':checked') ? 'active' : 'suspended'
                };

                $.post('endpoints/admin_users_handler.php', data, function(response) {
                    if (response.status === 'success') {
                        $('#userModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        loadUsers();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });

            // Toggle Status
            $(document).on('click', '.toggle-status', function() {
                const id = $(this).data('id');
                const status = $(this).data('status');
                $.post('endpoints/admin_users_handler.php', { action: 'toggle_status', id: id, status: status }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Updated', response.message, 'success');
                        loadUsers();
                    }
                });
            });

            // Delete User
            $(document).on('click', '.delete-user', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "User will be moved to deleted list!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/admin_users_handler.php', { action: 'delete', id: id }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadUsers();
                            }
                        });
                    }
                });
            });

            // Reset Password
            $(document).on('click', '.reset-pwd', function() {
                 $('#resetPwdUserId').val($(this).data('id'));
                 $('#resetPasswordForm')[0].reset();
                 $('#resetPasswordModal').modal('show');
            });

            $('#resetPasswordForm').on('submit', function(e) {
                e.preventDefault();
                $.post('endpoints/admin_users_handler.php', {
                    action: 'reset_password',
                    id: $('#resetPwdUserId').val(),
                    password: $('#newPassword').val()
                }, function(response) {
                    if (response.status === 'success') {
                        $('#resetPasswordModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                    }
                });
            });
        });
    </script>
</body>

</html>
