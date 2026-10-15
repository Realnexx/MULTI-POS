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
    <title>Roles & Permissions - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        #modulesTable_filter input {
            width: 150px !important;
            margin-left: 0.5rem;
        }
        #rolesTable_filter input {
            width: 150px !important;
            margin-left: 0.5rem;
        }
        .dataTables_filter {
            margin-bottom: 15px;
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
                                    <h4 class="fw-semibold mb-8">Access Control</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Roles & Permissions</li>
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

                    <div class="row">
                        <!-- Left Column: Modules Setup -->
                        <div class="col-lg-5">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title fw-semibold">System Modules</h5>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModuleModal">
                                            <i class="ti ti-plus me-1"></i> New Module
                                        </button>
                                    </div>
                                    <p class="text-muted small">Modules represent system pages or features that can be restricted.</p>
                                    
                                    <div class="table-responsive">
                                        <table class="table align-middle table-hover table-responsive-stack" id="modulesTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Module Name</th>
                                                    <th class="text-end">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody id="modulesTableBody">
                                                <!-- Dynamic Content -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Roles & Permission Setting -->
                        <div class="col-lg-7">
                            <div class="card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <h5 class="card-title fw-semibold">User Roles</h5>
                                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                            <i class="ti ti-plus me-1"></i> New Role
                                        </button>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table align-middle table-hover table-responsive-stack" id="rolesTable">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>Role Title</th>
                                                    <th>Description</th>
                                                    <th>Permissions</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody id="rolesTableBody">
                                                <!-- Dynamic Content -->
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add Role Modal -->
                    <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <form id="roleForm" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="roleModalTitle">Create New Role</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="roleId">
                                    <div class="mb-3">
                                        <label class="form-label">Role Title</label>
                                        <input type="text" class="form-control" id="roleName" placeholder="e.g. Moderator" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" id="roleDesc" rows="3" placeholder="Briefly describe what this role does..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary" id="saveRoleBtn">Save Role</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Add Module Modal -->
                    <div class="modal fade" id="addModuleModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-sm">
                            <form id="moduleForm" class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="moduleModalTitle">Create Page Module</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" id="moduleId">
                                    <div class="mb-3">
                                        <label class="form-label">Module Name</label>
                                        <input type="text" class="form-control" id="moduleName" placeholder="Dashboard, Reports, etc." required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Description</label>
                                        <textarea class="form-control" id="moduleDesc" rows="2" placeholder="Module description..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" class="btn btn-primary btn-sm" id="saveModuleBtn">Add Module</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Permissions Modal -->
                    <div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title text-white" id="permModalTitle">Permission Matrix: Tabulator</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-0">
                                    <input type="hidden" id="permRoleId">
                                    <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                                        <span class="text-muted small">Configure access levels for each module.</span>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-primary" id="selectAllPerms">Select All</button>
                                            <button type="button" class="btn btn-outline-secondary" id="deselectAllPerms">Deselect All</button>
                                        </div>
                                    </div>
                                    <table class="table table-striped align-middle mb-0">
                                        <thead class="bg-light">
                                            <tr>
                                                <th class="ps-4">Module / Page</th>
                                                <th class="text-center">View</th>
                                                <th class="text-center">Manage</th>
                                                <th class="text-center">Delete</th>
                                                <th class="text-center">Export</th>
                                            </tr>
                                        </thead>
                                        <tbody id="permMatrixBody">
                                            <!-- Dynamic Content -->
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="button" class="btn btn-primary" id="savePermsBtn">Update Permissions</button>
                                </div>
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
            loadModules();
            loadRoles();
            let modulesDataTable = $('#modulesTable').DataTable({
                "pageLength": 10,
                "searching": true,
                "language": { "search": "Filter:" }
            });

            let rolesDataTable = $('#rolesTable').DataTable({
                "pageLength": 10,
                "searching": true,
                "language": { "search": "Filter:" }
            });

            // Load Modules
            function loadModules() {
                $.post('endpoints/modules_handler.php', { action: 'list' }, function(response) {
                    if (response.status === 'success') {
                        modulesDataTable.clear();
                        response.data.forEach(module => {
                            modulesDataTable.row.add([
                                module.name,
                                `<div class="text-end">
                                    <button class="btn btn-sm btn-light text-info me-1 edit-module" data-id="${module.id}" data-name="${module.name}" data-desc="${module.description}" title="Edit Module"><i class="ti ti-edit"></i></button>
                                    <button class="btn btn-sm btn-light text-danger delete-module" data-id="${module.id}"><i class="ti ti-trash"></i></button>
                                </div>`
                            ]);
                        });
                        modulesDataTable.draw();
                    }
                });
            }

            // Load Roles
            function loadRoles() {
                $.post('endpoints/roles_handler.php', { action: 'list' }, function(response) {
                    if (response.status === 'success') {
                        rolesDataTable.clear();
                        response.data.forEach(role => {
                            rolesDataTable.row.add([
                                `<span class="fw-bold">${role.name}</span>`,
                                role.description,
                                `<span class="badge bg-light-primary text-primary fw-semibold">${role.perm_count} Modules</span>`,
                                `<div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-outline-info edit-role" data-id="${role.id}" data-name="${role.name}" data-desc="${role.description}" title="Edit Role"><i class="ti ti-edit"></i></button>
                                    <button class="btn btn-sm btn-outline-primary set-permissions" data-id="${role.id}" data-name="${role.name}" title="Set Permissions">
                                        <i class="ti ti-shield-check"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-role" data-id="${role.id}" title="Delete Role"><i class="ti ti-trash"></i></button>
                                </div>`
                            ]);
                        });
                        rolesDataTable.draw();
                    }
                });
            }

            loadModules();
            loadRoles();

            // Reset module modal
            $('[data-bs-target="#addModuleModal"]').on('click', function() {
                $('#moduleForm')[0].reset();
                $('#moduleId').val('');
                $('#moduleModalTitle').text('Create Page Module');
                $('#saveModuleBtn').text('Add Module');
            });

            // Reset role modal
            $('[data-bs-target="#addRoleModal"]').on('click', function() {
                $('#roleForm')[0].reset();
                $('#roleId').val('');
                $('#roleModalTitle').text('Create New Role');
                $('#saveRoleBtn').text('Save Role');
            });

            // Handle Module Form Submit
            $('#moduleForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#moduleId').val();
                const action = id ? 'update' : 'create';
                const data = {
                    action: action,
                    id: id,
                    name: $('#moduleName').val(),
                    description: $('#moduleDesc').val()
                };

                $.post('endpoints/modules_handler.php', data, function(response) {
                    if (response.status === 'success') {
                        $('#addModuleModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        loadModules();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });

            // Handle Role Form Submit
            $('#roleForm').on('submit', function(e) {
                e.preventDefault();
                const id = $('#roleId').val();
                const action = id ? 'update' : 'create';
                const data = {
                    action: action,
                    id: id,
                    name: $('#roleName').val(),
                    description: $('#roleDesc').val()
                };

                $.post('endpoints/roles_handler.php', data, function(response) {
                    if (response.status === 'success') {
                        $('#addRoleModal').modal('hide');
                        Swal.fire('Success', response.message, 'success');
                        loadRoles();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });

            // Populate Module Edit Modal
            $(document).on('click', '.edit-module', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const desc = $(this).data('desc');

                $('#moduleId').val(id);
                $('#moduleName').val(name);
                $('#moduleDesc').val(desc);
                $('#moduleModalTitle').text('Edit Page Module');
                $('#saveModuleBtn').text('Save Changes');
                $('#addModuleModal').modal('show');
            });

            // Populate Role Edit Modal
            $(document).on('click', '.edit-role', function() {
                const id = $(this).data('id');
                const name = $(this).data('name');
                const desc = $(this).data('desc');

                $('#roleId').val(id);
                $('#roleName').val(name);
                $('#roleDesc').val(desc);
                $('#roleModalTitle').text('Edit Role');
                $('#saveRoleBtn').text('Save Changes');
                $('#addRoleModal').modal('show');
            });

            // Handle Delete Module
            $(document).on('click', '.delete-module', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "Deleting this module will affect role permissions!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/modules_handler.php', { action: 'delete', id: id }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadModules();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            // Handle Delete Role
            $(document).on('click', '.delete-role', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('endpoints/roles_handler.php', { action: 'delete', id: id }, function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Deleted!', response.message, 'success');
                                loadRoles();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        });
                    }
                });
            });

            // Handle Open Permissions Modal
            $(document).on('click', '.set-permissions', function() {
                const roleId = $(this).data('id');
                const roleName = $(this).data('name');

                $('#permRoleId').val(roleId);
                $('#permModalTitle').text('Permission Matrix: ' + roleName);
                $('#permMatrixBody').html('<tr><td colspan="5" class="text-center">Loading permissions...</td></tr>');
                $('#permissionsModal').modal('show');

                $.post('endpoints/permissions_handler.php', { action: 'get_role_permissions', role_id: roleId }, function(response) {
                    if (response.status === 'success') {
                        let html = '';
                        response.data.forEach(p => {
                            html += `
                                <tr class="perm-row" data-module-id="${p.module_id}">
                                    <td class="ps-4 fw-semibold">${p.module_name}</td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input check-view" ${p.can_view == 1 ? 'checked' : ''}></td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input check-manage" ${p.can_manage == 1 ? 'checked' : ''}></td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input check-delete" ${p.can_delete == 1 ? 'checked' : ''}></td>
                                    <td class="text-center"><input type="checkbox" class="form-check-input check-export" ${p.can_export == 1 ? 'checked' : ''}></td>
                                </tr>
                            `;
                        });
                        $('#permMatrixBody').html(html || '<tr><td colspan="5" class="text-center text-muted">No modules found.</td></tr>');
                    } else {
                        $('#permMatrixBody').html(`<tr><td colspan="5" class="text-center text-danger">${response.message}</td></tr>`);
                    }
                });
            });

            // Handle Save Permissions
            $('#savePermsBtn').on('click', function() {
                const roleId = $('#permRoleId').val();
                let permissions = [];

                $('.perm-row').each(function() {
                    permissions.push({
                        module_id: $(this).data('module-id'),
                        can_view: $(this).find('.check-view').is(':checked') ? 1 : 0,
                        can_manage: $(this).find('.check-manage').is(':checked') ? 1 : 0,
                        can_delete: $(this).find('.check-delete').is(':checked') ? 1 : 0,
                        can_export: $(this).find('.check-export').is(':checked') ? 1 : 0
                    });
                });

                const btn = $(this);
                btn.prop('disabled', true).text('Saving...');

                $.post('endpoints/permissions_handler.php', {
                    action: 'save_permissions',
                    role_id: roleId,
                    permissions: permissions
                }, function(response) {
                    btn.prop('disabled', false).text('Update Permissions');
                    if (response.status === 'success') {
                        Swal.fire('Saved!', response.message, 'success');
                        $('#permissionsModal').modal('hide');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                });
            });

            // Select All / Deselect All
            $('#selectAllPerms').on('click', function() {
                $('#permMatrixBody').find('.form-check-input').prop('checked', true);
            });

            $('#deselectAllPerms').on('click', function() {
                $('#permMatrixBody').find('.form-check-input').prop('checked', false);
            });
        });
    </script>
</body>

</html>
