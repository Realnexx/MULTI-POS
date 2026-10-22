<?php
$title = "Users";
ob_start();
?>
<?php $userFieldError=$_SESSION['user_field_error']??null;unset($_SESSION['user_field_error']); ?>
<script>window.userFormPolicy={strong:<?= Setting::get('strong_passwords','0')==='1'?'true':'false' ?>,error:<?= json_encode($userFieldError,JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_HEX_AMP) ?>};</script>
<script defer src="<?= url('assets/js/users-validation.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/users-validation.js') ?>"></script>
<section class="access-workspace users-workspace">

<div class="px-0">
    <header class="access-heading"><div class="access-title"><span class="material-symbols-outlined">group</span><div><h1>Users</h1><p>Manage staff accounts, roles and access.</p></div></div><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createUserModal"><span class="material-symbols-outlined" aria-hidden="true">person_add</span> Add user</button></header>

    <!-- Quick Stats Summary -->
    <div class="row g-3 mb-4" id="stats-cards-container">
        <!-- Dynamic stats cards will be injected here -->
        <div class="col-md-4">
            <div class="card border-0 rounded-4 shadow-sm p-3 h-100 bg-white border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                        style="width: 48px; height: 48px; background: #edf2ff; color: #5d87ff;">
                        <span class="material-symbols-outlined">group</span>
                    </div>
                    <div>
                        <div class="text-muted small fw-medium">Total Users</div>
                        <div id="total-users-count" class="h4 fw-bold mb-0 text-dark">...</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="card border-0 rounded-4 shadow-sm bg-white">
        <div class="card-header bg-white border-0 py-3 px-4">
            <div class="d-flex align-items-center justify-content-between">
                <div><h5 class="mb-1 fw-bold text-dark">Staff directory</h5><p class="small text-muted mb-0">Find team members and manage their account access.</p></div>
                <div id="table-search-container"></div>
            </div>
        </div>
        <div class="users-table-container">
            <table id="user-table" class="table table-hover align-middle mb-0 w-100">
                <thead class="bg-light">
                    <tr class="text-muted small text-uppercase fw-bold" style="letter-spacing: 0.5px;">
                        <th class="py-3 px-4 border-0">User</th>
                        <th class="py-3 border-0">Email</th>
                        <th class="py-3 border-0">Roles</th>
                        <th class="py-3 border-0">Date Added</th>
                        <th class="py-3 px-4 border-0 text-end">Actions</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <!-- Data will be loaded via DataTables -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    /* Premium Action Buttons - Enhanced and Unique */
    .action-btn {
        width: 38px;
        height: 38px;
        border-radius: 12px;
        border: 1px solid transparent;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .action-btn .material-symbols-outlined {
        font-size: 20px;
        z-index: 2;
    }

    /* View Button: Azure Blue */
    .btn-view-profile {
        background: #f0f9ff;
        color: #0288d1;
        border-color: #e0f2fe;
    }

    .btn-view-profile:hover {
        background: #0288d1;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(2, 136, 209, 0.25);
    }

    /* Edit Button: Vibrant Amber */
    .btn-edit-member {
        background: #fffbeb;
        color: #d97706;
        border-color: #fef3c7;
    }

    .btn-edit-member:hover {
        background: #d97706;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(217, 119, 6, 0.25);
    }

    /* Delete Button: Rose Red */
    .btn-delete-member {
        background: #fff1f2;
        color: #e11d48;
        border-color: #ffe4e6;
    }

    .btn-delete-member:hover {
        background: #e11d48;
        color: #ffffff;
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(225, 29, 72, 0.25);
    }

    /* Custom Tooltips */
    .custom-tooltip-wrapper {
        position: relative;
        display: inline-block;
    }

    .custom-tooltip {
        visibility: hidden;
        width: max-content;
        background-color: #1e293b;
        color: #fff;
        text-align: center;
        border-radius: 8px;
        padding: 6px 14px;
        position: absolute;
        z-index: 9999;
        bottom: 135%;
        left: 50%;
        transform: translateX(-50%) translateY(10px);
        opacity: 0;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 11px;
        font-weight: 600;
        pointer-events: none;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.15);
    }

    .custom-tooltip::after {
        content: "";
        position: absolute;
        top: 100%;
        left: 50%;
        margin-left: -5px;
        border-width: 5px;
        border-style: solid;
        border-color: #1e293b transparent transparent transparent;
    }

    .custom-tooltip-wrapper:hover .custom-tooltip {
        visibility: visible;
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }

    /* Modal Styling */
    .premium-modal .modal-content {
        border: none;
        border-radius: 28px;
        box-shadow: 0 30px 60px -12px rgba(0, 0, 0, 0.2);
    }

    .premium-modal .modal-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.06);
        padding: 24px 32px;
    }

    .premium-modal .modal-body {
        padding: 32px;
    }

    .premium-modal .modal-footer {
        border-top: 1px solid rgba(0, 0, 0, 0.06);
        padding: 24px 32px;
        background: #fcfcfc;
    }

    .profile-info-label {
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 700;
        color: #94a3b8;
        margin-bottom: 4px;
    }

    .profile-info-value {
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
    }

    .profile-section-title {
        font-size: 13px;
        font-weight: 800;
        color: #5d87ff;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .profile-section-title .material-symbols-outlined {
        font-size: 18px;
    }

    .profile-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 16px;
        height: 100%;
        border: 1px solid #f1f5f9;
        transition: all 0.2s;
    }

    .profile-card:hover {
        background: #ffffff;
        border-color: #e2e8f0;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    /* Custom Check Pill Styling */
    .custom-check-pill {
        position: relative;
    }

    .custom-check-pill input {
        display: none;
    }

    .custom-check-pill label {
        display: block;
        padding: 8px 18px;
        background: #f1f5f9;
        color: #64748b;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #e2e8f0;
        user-select: none;
    }

    .custom-check-pill input:checked + label {
        background: #5d87ff;
        color: #ffffff;
        border-color: #5d87ff;
        box-shadow: 0 4px 10px rgba(93, 135, 255, 0.25);
        transform: translateY(-2px);
    }

    .custom-check-pill label:hover {
        background: #e2e8f0;
        border-color: #cbd5e1;
    }

    .custom-check-pill input:checked + label:hover {
        background: #a00c36;
    }

    /* DataTable Controls Styling */
    .dataTables_length select {
        padding: 6px 32px 6px 12px !important;
        border-radius: 8px !important;
        border: 1px solid #e2e8f0 !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #475569 !important;
        background-color: #f8fafc !important;
        cursor: pointer !important;
    }

    .dataTables_length label {
        font-size: 13px !important;
        font-weight: 500 !important;
        color: #64748b !important;
    }

    .dataTables_info {
        font-size: 13px !important;
        color: #64748b !important;
    }

    .pagination .page-link {
        border-radius: 8px !important;
        margin: 0 2px !important;
        border: none !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #64748b !important;
        padding: 8px 14px !important;
    }

    .pagination .page-item.active .page-link {
        background-color: #5d87ff !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(93, 135, 255, 0.2) !important;
    }

    .pagination .page-item.disabled .page-link {
        background-color: transparent !important;
        color: #cbd5e1 !important;
    }
</style>

<!-- Enhanced View User Modal -->
<div class="modal fade premium-modal" id="viewUserModal" tabindex="-1" aria-label="User profile" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content overflow-hidden">
            <div class="modal-body p-0">
                <!-- Profile Header -->
                <div class="position-relative" style="height: 160px; background: linear-gradient(135deg, #5d87ff, #5d87ff);">
                    <div class="position-absolute top-0 end-0 p-4">
                        <span id="view-status-badge" class="badge rounded-pill bg-white text-success px-3 py-2 fw-bold shadow-sm" style="font-size: 10px;">
                            <span class="d-inline-block rounded-circle bg-success me-1" style="width: 6px; height: 6px;"></span>
                            ACTIVE
                        </span>
                    </div>
                </div>

                <div class="px-5 pb-5" style="margin-top: -50px;">
                    <div class="d-flex align-items-end gap-4 mb-5">
                        <div id="view_avatar_container" class="position-relative">
                            <div id="view_avatar_initials" class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-lg"
                                style="width: 110px; height: 110px; background: #ffffff; border: 5px solid #ffffff; font-size: 42px; color: #5d87ff !important;">
                            </div>
                        </div>
                        <div class="mb-2 mt-5">
                            <h3 id="view_name" class="fw-bold text-dark mb-1" style="font-family: 'Montserrat', sans-serif; margin-top: 20px;"></h3>
                            <div class="d-flex align-items-center gap-2 mt-1">
                                <span class="material-symbols-outlined text-muted" style="font-size: 14px;">mail</span>
                                <span id="view_email" class="text-muted fw-medium small"></span>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <!-- Primary Identity -->
                        <div class="col-12">
                            <div class="profile-section-title">
                                <span class="material-symbols-outlined">badge</span>
                                PERSONAL INFORMATION
                            </div>
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <div class="profile-card">
                                        <div class="profile-info-label">Username</div>
                                        <div id="view_username" class="profile-info-value">-</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="profile-card">
                                        <div class="profile-info-label">Birthdate</div>
                                        <div id="view_birthdate" class="profile-info-value">-</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="profile-card">
                                        <div class="profile-info-label">Role Category</div>
                                        <div id="view_role_cat" class="profile-info-value">-</div>
                                    </div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="profile-card">
                                        <div class="profile-info-label">Member ID</div>
                                        <div id="view_id" class="profile-info-value">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contact & Location -->
                        <div class="col-md-7">
                            <div class="profile-section-title">
                                <span class="material-symbols-outlined">location_on</span>
                                CONTACT & ADDRESS
                            </div>
                            <div class="profile-card h-auto mb-3">
                                <div class="profile-info-label">Home Address</div>
                                <div id="view_address_full" class="profile-info-value text-muted" style="font-size: 13px; line-height: 1.5;">-</div>
                            </div>
                            <div class="row g-3">
                                <div class="col-6">
                                    <div class="profile-card h-auto">
                                        <div class="profile-info-label">Assigned Store</div>
                                        <div id="view_store" class="profile-info-value">-</div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="profile-card h-auto">
                                        <div class="profile-info-label">Date Added</div>
                                        <div id="view_date" class="profile-info-value">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Access Roles & Stats -->
                        <div class="col-md-5">
                            <div class="profile-section-title">
                                <span class="material-symbols-outlined">security</span>
                                SYSTEM ACCESS
                            </div>
                            <div id="view_roles_list" class="d-flex flex-wrap gap-2 mb-4">
                                <!-- Roles render here -->
                            </div>

                            <div class="profile-section-title">
                                <span class="material-symbols-outlined">description</span>
                                BIOGRAPHY
                            </div>
                            <div class="profile-card h-auto">
                                <p id="view_bio" class="text-muted small mb-0 font-italic" style="line-height: 1.6;">-</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-5 px-5">
                <div class="d-flex gap-2 users-profile-actions">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal"><span class="material-symbols-outlined" aria-hidden="true">close</span> Close</button>
                    <button type="button" id="btn-edit-from-view" class="btn btn-primary rounded-pill px-4 fw-bold" style="background: #5d87ff; border: none;"><span class="material-symbols-outlined" aria-hidden="true">edit</span> Edit account</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Create User Modal -->
<div class="modal fade premium-modal" id="createUserModal" tabindex="-1" aria-label="Add user" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <div class="users-modal-heading"><span class="material-symbols-outlined" aria-hidden="true">person_add</span><div><h5 class="modal-title fw-bold text-dark">Add user</h5><p>Create a staff account and assign its roles.</p></div></div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=admin/users') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="create">

                <div class="modal-body">
                    <div class="text-center mb-4">
                        <div class="position-relative d-inline-block">
                            <div id="create-image-preview" class="rounded-circle border d-flex align-items-center justify-content-center bg-light shadow-sm" 
                                 style="width: 100px; height: 100px; overflow: hidden; background-size: cover; background-position: center;">
                                <span class="material-symbols-outlined text-muted" style="font-size: 40px;">add_a_photo</span>
                            </div>
                            <label for="create_image" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1 shadow-sm" 
                                   style="background: #5d87ff; border: none; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                            </label>
                            <input type="file" name="image" id="create_image" class="d-none" accept="image/*" onchange="previewImage(this, 'create-image-preview')">
                        </div>
                        <p class="text-muted small mt-2">JPG, PNG, GIF or WebP · Up to 10 MB</p>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">First Name</label>
                            <input type="text" name="firstname" class="form-control form-control-sm" required placeholder="Juana">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">Middle Name</label>
                            <input type="text" name="middlename" class="form-control form-control-sm" placeholder="Halu">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Last Name</label>
                            <input type="text" name="lastname" class="form-control form-control-sm" required placeholder="Halu">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Ext.</label>
                            <input type="text" name="extname" class="form-control form-control-sm" placeholder="Jr.">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email Address</label>
                        <input type="email" name="email" class="form-control" required placeholder="juana@staceyhaluhalu.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Temporary Password</label>
                        <input type="password" name="password" class="form-control" required placeholder="••••••••">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">POS Terminal PIN (optional)</label>
                        <input type="text" name="pos_pin" class="form-control" maxlength="4" pattern="\d{4}"
                               placeholder="1234" style="letter-spacing: 4px; font-weight: 700;">
                        <small class="text-muted">4-digit PIN for POS access. Defaults to <strong>1234</strong> if left empty.</small>
                    </div>
                    <div>
                        <label class="form-label d-block mb-3">Role</label>
                        <div id="create-user-roles-container" class="d-flex flex-wrap gap-2">
                            <!-- Roles will be loaded dynamically -->
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-600" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold" style="background: #5d87ff; border: none;">Create Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal fade premium-modal" id="editUserModal" tabindex="-1" aria-label="Edit user" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 28px; min-width: 0;">
            <div class="modal-header border-0 p-4 pb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" 
                         style="width: 48px; height: 48px; background: #fff1f2; color: #5d87ff;">
                        <span class="material-symbols-outlined" style="font-size: 24px;">manage_accounts</span>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Edit User</h5>
                        <p class="text-muted small mb-0">Update account credentials and roles.</p>
                    </div>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= url('?route=admin/users') ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">

                <div class="modal-body py-4 users-edit-grid">
                    <div class="users-edit-photo">
                        <div class="position-relative d-inline-block">
                            <div id="edit-image-preview" class="rounded-circle border d-flex align-items-center justify-content-center bg-light shadow-sm" 
                                 style="width: 100px; height: 100px; overflow: hidden; background-size: cover; background-position: center;">
                                <span class="material-symbols-outlined text-muted" style="font-size: 40px;">account_circle</span>
                            </div>
                            <label for="edit_image" class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1 shadow-sm" 
                                   style="background: #5d87ff; border: none; cursor: pointer;">
                                <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                            </label>
                            <input type="file" name="image" id="edit_image" class="d-none" accept="image/*" onchange="previewImage(this, 'edit-image-preview')">
                        </div>
                        <div><strong>Profile photo</strong><p>JPG, PNG, GIF or WebP · Up to 10 MB.</p><label for="edit_image" class="users-photo-link">Change photo</label></div>
                    </div>
                    <section class="users-edit-section users-edit-name">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #5d87ff;">person_edit</span>
                            Member Name Details
                        </label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="users-name-label" for="edit_firstname">First name</label><input type="text" name="firstname" id="edit_firstname" class="form-control border-0 bg-light shadow-none py-2" 
                                       required placeholder="First Name" style="border-radius: 10px; font-size: 13px;">
                            </div>
                            <div class="col-md-6">
                                <label class="users-name-label" for="edit_middlename">Middle name (optional)</label><input type="text" name="middlename" id="edit_middlename" class="form-control border-0 bg-light shadow-none py-2" 
                                       placeholder="Middle Name" style="border-radius: 10px; font-size: 13px;">
                            </div>
                            <div class="col-md-8">
                                <label class="users-name-label" for="edit_lastname">Last name</label><input type="text" name="lastname" id="edit_lastname" class="form-control border-0 bg-light shadow-none py-2" 
                                       required placeholder="Last Name" style="border-radius: 10px; font-size: 13px;">
                            </div>
                            <div class="col-md-4">
                                <label class="users-name-label" for="edit_extname">Suffix (optional)</label><input type="text" name="extname" id="edit_extname" class="form-control border-0 bg-light shadow-none py-2" 
                                       placeholder="Ext." style="border-radius: 10px; font-size: 13px;">
                            </div>
                        </div>
                    </section>

                    <section class="users-edit-section users-edit-email">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #5d87ff;">alternate_email</span>
                            Email Address
                        </label>
                        <input type="email" name="email" id="edit_email" class="form-control form-control-lg border-0 bg-light shadow-none" 
                               required placeholder="name@example.com" style="font-size: 15px; padding: 14px 20px; border-radius: 12px;">
                    </section>

                    <section class="users-edit-section">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #5d87ff;">lock_reset</span>
                            Security Password
                        </label>
                        <input type="password" name="password" class="form-control form-control-lg border-0 bg-light shadow-none" 
                               placeholder="********" style="font-size: 15px; padding: 14px 20px; border-radius: 12px;">
                        <p class="text-muted mt-2 mb-0" style="font-size: 11px;">Leave this field empty if you don't wish to update the current password.</p>
                    </section>

                    <section class="users-edit-section">
                        <label class="form-label fw-bold text-dark small mb-2 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #5d87ff;">dialpad</span>
                            POS Terminal PIN
                        </label>
                        <input type="password" inputmode="numeric" autocomplete="new-password" name="pos_pin" id="edit_pos_pin"
                               class="form-control form-control-lg border-0 bg-light shadow-none"
                               maxlength="4" pattern="\d{4}"
                               placeholder="****" style="font-size: 15px; padding: 14px 20px; border-radius: 12px; letter-spacing: 8px; font-weight: 700;">
                        <p class="text-muted mt-2 mb-0" style="font-size: 11px;">4-digit PIN used to access the POS terminal. Leave empty to keep current.</p>
                    </section>
                        
                    <section class="users-edit-section users-edit-roles">
                        <label class="form-label fw-bold text-dark small mb-3 d-flex align-items-center gap-2">
                            <span class="material-symbols-outlined" style="font-size: 18px; color: #5d87ff;">verified_user</span>
                            System Permissions & Roles
                        </label>
                        <div id="edit-user-roles-container" class="d-flex flex-wrap gap-2">
                            <!-- Roles loaded via AJAX -->
                        </div>
                    </section>
                </div>
                <div class="modal-footer border-0 p-4 pt-0 justify-content-end gap-2">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-bold d-flex align-items-center gap-2" 
                            data-bs-dismiss="modal" style="font-size: 13px; border-radius: 12px !important; padding: 10px 24px;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">close</span>
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold d-flex align-items-center gap-2" 
                            style="background: #5d87ff; border: none; font-size: 13px; border-radius: 12px !important; padding: 10px 24px; box-shadow: 0 4px 12px rgba(93, 135, 255, 0.15);">
                        <span class="material-symbols-outlined" style="font-size: 18px;">save</span>
                        Update Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Remove Member Modal -->
<div class="modal fade premium-modal" id="deleteUserModal" tabindex="-1" aria-label="Remove user" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content shadow-lg border-0 overflow-hidden">
            <div class="modal-body p-4 text-center">
                <div class="mx-auto mb-4 d-flex align-items-center justify-content-center rounded-circle"
                    style="width: 64px; height: 64px; background: #fff1f2; color: #e11d48;">
                    <span class="material-symbols-outlined text-4xl">warning</span>
                </div>
                <h5 class="fw-bold text-dark mb-2">Delete User?</h5>
                <p class="text-muted small mb-4">Are you sure you want to remove <span id="delete_user_name" class="fw-bold"></span>? This action will revoke all system access immediately.</p>

                <form action="<?= url('?route=admin/users') ?>" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="user_id" id="delete_user_id">

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-danger rounded-pill py-2 fw-bold shadow-sm">Yes, Remove Member</button>
                        <button type="button" class="btn btn-light rounded-pill py-2 fw-600" data-bs-dismiss="modal">No, Keep Member</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const currentUserId = <?= $_SESSION['user']['id'] ?>;
        let userDataTable;

        // Initialize DataTable
        userDataTable = $('#user-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '<?= url("?route=admin/users/data") ?>',
                type: 'GET'
            },
            columns: [
                { 
                    data: 'name',
                    render: function(data, type, row) {
                        const initials = data ? data.charAt(0).toUpperCase() : '?';
                        const userImg = row.image ? `<img src="${row.image}" alt="${escapeHtml(data)}" class="rounded-circle shadow-sm" style="width: 42px; height: 42px; object-fit: cover;">` : `
                            <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm" 
                                 style="width: 42px; height: 42px; background: linear-gradient(135deg, #5d87ff, #5d87ff); font-size: 14px;">
                                ${initials}
                            </div>`;
                        return `
                            <div class="d-flex align-items-center gap-3">
                                ${userImg}
                                <div>
                                    <div class="fw-bold text-dark">${escapeHtml(data)}</div>
                                    <span class="user-branch-badge ${row.branch_id ? '' : 'unassigned'}"><span class="material-symbols-outlined" aria-hidden="true">storefront</span>${escapeHtml(row.assigned_branch_name || (row.branch_id ? 'Branch #' + row.branch_id : 'No branch assigned'))}</span>
                                    <div class="text-muted" style="font-size: 11px;">#USER-${row.id.toString().padStart(4, '0')}</div>
                                </div>
                            </div>`;
                    }
                },
                { data: 'email', className: 'text-muted small' },
                { 
                    data: 'roles',
                    render: function(data) {
                        return `<div class="d-flex flex-wrap gap-1">${renderRoleBadges(data)}</div>`;
                    }
                },
                { 
                    data: 'created_at',
                    render: function(data) {
                        return `<span class="text-muted small">${formatDate(data)}</span>`;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-end px-4',
                    render: function(data, type, row) {
                        const isSelf = parseInt(row.id) === currentUserId;
                        return `
                            <div class="d-flex align-items-center justify-content-end gap-2">
                                <div class="custom-tooltip-wrapper">
                                    <button type="button" class="action-btn btn-view-profile view-user-btn" 
                                            data-id="${row.id}"
                                            data-bs-toggle="modal" data-bs-target="#viewUserModal">
                                        <span class="material-symbols-outlined">visibility</span>
                                    </button>
                                    <span class="custom-tooltip">View Profile</span>
                                </div>
                                ${row.can_manage !== false ? `<div class="custom-tooltip-wrapper">
                                    <button type="button" class="action-btn btn-edit-member edit-user-btn" 
                                            data-id="${row.id}"
                                            data-bs-toggle="modal" data-bs-target="#editUserModal">
                                        <span class="material-symbols-outlined">edit_note</span>
                                    </button>
                                    <span class="custom-tooltip">Edit Member</span>
                                </div>` : ''}
                                ${!isSelf && row.can_manage !== false ? `
                                <div class="custom-tooltip-wrapper">
                                    <button type="button" class="action-btn btn-delete-member delete-user-btn" 
                                            data-id="${row.id}"
                                            data-bs-toggle="modal" data-bs-target="#deleteUserModal">
                                        <span class="material-symbols-outlined">person_remove</span>
                                    </button>
                                    <span class="custom-tooltip danger">Remove Member</span>
                                </div>` : ''}
                            </div>`;
                    }
                }
            ],
            createdRow: function(row) {
                ['User','Email','Roles','Date added','Actions'].forEach(function(label,index) {
                    if(row.cells[index])row.cells[index].setAttribute('data-label',label);
                });
            },
            autoWidth: false,
            dom: '<"users-table-body"tr><"users-table-footer"lip>',
            language: {
                processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div>',
                emptyTable: '<div class="opacity-25 my-4"><span class="material-symbols-outlined" style="font-size: 48px;">person_search</span></div><p class="text-muted">No team members found.</p>'
            },
            initComplete: function() {
                // Move the search box to the designated container
                const searchHtml = `
                    <div class="position-relative">
                        <span class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y ms-3 text-muted" style="font-size: 18px;">search</span>
                        <input type="text" id="userSearch" aria-label="Search staff accounts" class="form-control rounded-pill ps-5 border-light bg-light small"
                            placeholder="Search users..." style="width: 250px;">
                    </div>`;
                $('#table-search-container').html(searchHtml);
                
                // Re-bind search event
                $("#userSearch").on("keyup", function() {
                    userDataTable.search(this.value).draw();
                });
            },
            drawCallback: function(settings) {
                const json = userDataTable.ajax.json();
                refreshStats(json);
            }
        });

        // Load Roles separately
        function loadRoles() {
            $.get('<?= url("?route=admin/roles/data") ?>', function(response) {
                if (response.success) {
                    renderRoleCheckboxes(response.roles.filter(r => r.can_assign !== false && (!r.is_superadmin || <?= json_encode(User::is_superadmin($_SESSION['user']['id'])) ?>)));
                }
            });
        }

        function refreshStats(json) {
            const info = userDataTable.page.info();
            $("#total-users-count").text(info.recordsDisplay);

            if (json && json.role_counts) {
                const container = $("#stats-cards-container");
                // Clear existing dynamic cards but keep the first one (Total Users)
                const totalMembersCard = container.find('.col-md-4').first();
                container.empty().append(totalMembersCard);

                json.role_counts.forEach(rc => {
                    let color = '#0288d1';
                    let bg = '#e3f2fd';
                    let icon = 'military_tech';
                    let border = 'info';

                    if (rc.name.toLowerCase().includes('admin')) {
                        color = '#5d87ff';
                        bg = '#edf2ff';
                        icon = 'admin_panel_settings';
                        border = 'primary';
                    } else if (rc.name.toLowerCase().includes('cashier')) {
                        color = '#2e7d32';
                        bg = '#e8f5e9';
                        icon = 'point_of_sale';
                        border = 'success';
                    }

                    const cardHtml = `
                        <div class="col-md-4">
                            <div class="card border-0 rounded-4 shadow-sm p-3 h-100 bg-white border-start border-4 border-${border}">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0"
                                        style="width: 48px; height: 48px; background: ${bg}; color: ${color};">
                                        <span class="material-symbols-outlined">${icon}</span>
                                    </div>
                                    <div>
                                        <div class="text-muted small fw-medium">${rc.name}s</div>
                                        <div class="h4 fw-bold mb-0 text-dark">${rc.count}</div>
                                    </div>
                                </div>
                            </div>
                        </div>`;
                    container.append(cardHtml);
                });
            }
        }

        function renderRoleBadges(roles) {
            if (!roles) return `<span class="px-2 py-1 rounded-pill fw-600" style="font-size: 11px; color: #666; background: #f0f0f0;">Member</span>`;
            return roles.split(', ').map(r => {
                let color = '#666';
                let bg = '#f0f0f0';
                if (r === 'Admin') {
                    color = '#5d87ff';
                    bg = '#edf2ff';
                }
                if (r === 'Cashier') {
                    color = '#2e7d32';
                    bg = '#e8f5e9';
                }
                return `<span class="px-2 py-1 rounded-pill fw-600" style="font-size: 11px; color: ${color}; background: ${bg};">${escapeHtml(r)}</span>`;
            }).join('');
        }

        function renderRoleCheckboxes(roles) {
            const createContainer = $("#create-user-roles-container");
            const editContainer = $("#edit-user-roles-container");
            createContainer.empty();
            editContainer.empty();

            roles.forEach(r => {
                const isSuperadmin = r.is_superadmin || String(r.name || '').toLowerCase().replace(/[\s_-]/g, '') === 'superadmin';
                if (isSuperadmin && !<?= json_encode(User::is_superadmin($_SESSION['user']['id'])) ?>) return;
                if (r.can_assign === false) return;
                const createHtml = `<div class="custom-check-pill"><input type="radio" name="roles[]" value="${r.id}" id="role_create_${r.id}"><label for="role_create_${r.id}">${escapeHtml(r.name)}</label></div>`;
                const editHtml = `<div class="custom-check-pill"><input type="radio" class="edit-role-checkbox" name="roles[]" value="${r.id}" data-role-name="${escapeHtml(r.name)}" id="role_edit_${r.id}"><label for="role_edit_${r.id}">${escapeHtml(r.name)}</label></div>`;
                createContainer.append(createHtml);
                editContainer.append(editHtml);
            });
        }

        function escapeHtml(text) {
            if (!text) return '';
            return text.replace(/[&<>"']/g, function(m) {
                return {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                } [m];
            });
        }

        function formatDate(dateStr) {
            if (!dateStr) return '-';
            return new Date(dateStr).toLocaleDateString('en-US', {
                month: 'short',
                day: '2-digit',
                year: 'numeric'
            });
        }

        window.previewImage = function(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(`#${previewId}`).css('background-image', `url(${e.target.result})`);
                    $(`#${previewId}`).html('');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Initialize
        loadRoles();

        // Delegate Modal Population
        $(document).on("click", ".view-user-btn", function() {
            const userId = $(this).data('id');
            $.get('<?= url("?route=admin/users/single") ?>', { id: userId }, function(response) {
                if (response.success) {
                    const user = response.user;
                    $("#view_name").text(user.name);
                    $("#view_email").text(user.email);
                    
                    const initials = user.name.charAt(0).toUpperCase();
                    if (user.image) {
                        $("#view_avatar_initials").html(`<img src="${user.image}" class="rounded-circle w-100 h-100 shadow-sm" style="object-fit: cover;">`);
                        $("#view_avatar_initials").css('background', 'white');
                    } else {
                        $("#view_avatar_initials").text(initials);
                        $("#view_avatar_initials").css('background', '#ffffff');
                    }

                    $("#view_username").text(user.username || '-');
                    $("#view_birthdate").text(formatDate(user.birthdate));
                    $("#view_id").text('#SHH-' + user.id.toString().padStart(4, '0'));

                    const roles = user.roles ? user.roles.split(', ') : ['Member'];
                    $("#view_role_cat").text(roles[0]);

                    let rolesHtml = '';
                    roles.forEach(r => {
                        let color = '#5d87ff';
                        let bg = '#edf2ff';
                        if (r === 'Cashier') {
                            color = '#2e7d32';
                            bg = '#e8f5e9';
                        }
                        rolesHtml += `<span class="px-3 py-1 rounded-pill fw-bold border" style="font-size: 11px; color: ${color}; background: ${bg}; border-color: ${color}20 !important;">${r}</span>`;
                    });
                    $("#view_roles_list").html(rolesHtml);

                    const fullAddress = [user.address, user.purok, user.barangay, user.town, user.city, user.province, user.country, user.zip]
                        .filter(Boolean).join(', ');
                    $("#view_address_full").text(fullAddress || 'No address provided');
                    $("#view_store").text(user.store || 'Main Outlet');
                    $("#view_date").text(formatDate(user.created_at));
                    $("#view_bio").text(user.bio || 'This member hasn\'t shared a bio yet.');

                    $("#btn-edit-from-view").off('click').on('click', function() {
                        $('#viewUserModal').modal('hide');
                        setTimeout(() => {
                            $(`.edit-user-btn[data-id="${userId}"]`).trigger('click');
                        }, 300);
                    });
                }
            });
        });

        $(document).on("click", ".edit-user-btn", function() {
            const userId = $(this).data('id');
            
            $.get('<?= url("?route=admin/users/single") ?>', { id: userId }, function(response) {
                if (response.success) {
                    const user = response.user;
                    $("#edit_user_id").val(user.id);
                    $("#edit_firstname").val(user.firstname);
                    $("#edit_middlename").val(user.middlename);
                    $("#edit_lastname").val(user.lastname);
                    $("#edit_extname").val(user.extname);
                    $("#edit_email").val(user.email);
                    $("#edit_pos_pin").val(user.pos_pin || '');
  
                    if (user.image) {
                        $("#edit-image-preview").html(`<img src="${user.image}" class="rounded-circle w-100 h-100" style="object-fit: cover;">`);
                        $("#edit-image-preview").css('background-image', 'none');
                    } else {
                        $("#edit-image-preview").html('<span class="material-symbols-outlined text-muted" style="font-size: 40px;">account_circle</span>');
                    }
 
                    const roles = user.roles ? user.roles.split(', ') : [];
                    $(".edit-role-checkbox").each(function() {
                        $(this).prop('checked', false);
                        const roleName = $(this).data('role-name');
                        if (roles.includes(roleName)) {
                            $(this).prop('checked', true);
                        }
                    });
                }
            });
        });

        $(document).on("click", ".delete-user-btn", function() {
            const userId = $(this).data('id');
            const rowData = userDataTable.row($(this).closest('tr')).data();
            if (rowData) {
                $("#delete_user_id").val(userId);
                $("#delete_user_name").text(rowData.name);
            }
        });
    });
</script>

<link rel="stylesheet" href="<?= url('assets/css/access-management.css') ?>">
<link rel="stylesheet" href="<?= url('assets/css/users-workspace.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/users-workspace.css') ?>">
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
