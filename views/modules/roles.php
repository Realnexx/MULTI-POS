<?php
$title = "Roles and Permissions";
ob_start();
?>
<section class="access-workspace roles-workspace">

<!-- Inject Tailwind & Montserrat/Inter Fonts -->
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<!-- jQuery is provided by the shared admin layout. -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&amp;family=Inter:wght@400;500;600&amp;display=swap" rel="stylesheet" />

<script id="tailwind-config">
    tailwind.config = {
        important: '#tw-roles',
        corePlugins: {
            preflight: false,
        },
        theme: {
            extend: {
                "colors": {
                    "surface-container-lowest": "#ffffff",
                    "on-secondary": "#ffffff",
                    "on-surface": "#191c1d",
                    "tertiary-fixed-dim": "#ffba27",
                    "secondary-fixed-dim": "#e0b6ff",
                    "background": "#f8f9fa",
                    "on-background": "#191c1d",
                    "surface-tint": "#5d87ff",
                    "surface-container": "#edeeef",
                    "secondary-fixed": "#f2daff",
                    "on-secondary-fixed-variant": "#6b00af",
                    "inverse-on-surface": "#f0f1f2",
                    "on-secondary-fixed": "#2e004e",
                    "on-primary-fixed": "#40000f",
                    "on-primary": "#ffffff",
                    "inverse-surface": "#2e3132",
                    "outline-variant": "#e2bec0",
                    "tertiary-container": "#996c00",
                    "surface": "#f8f9fa",
                    "surface-variant": "#e1e3e4",
                    "primary-fixed": "#ffdadb",
                    "primary-fixed-dim": "#ffb2b8",
                    "outline": "#8e6f71",
                    "inverse-primary": "#ffb2b8",
                    "error": "#ba1a1a",
                    "error-container": "#ffdad6",
                    "on-tertiary": "#ffffff",
                    "surface-container-high": "#e7e8e9",
                    "tertiary-fixed": "#ffdea9",
                    "surface-dim": "#d9dadb",
                    "on-secondary-container": "#fffbff",
                    "secondary-container": "#9e49e3",
                    "primary-container": "#da3054",
                    "on-tertiary-container": "#fffbff",
                    "surface-bright": "#f8f9fa",
                    "on-error-container": "#93000a",
                    "on-tertiary-fixed": "#271900",
                    "primary": "#5d87ff",
                    "secondary": "#5279e8",
                    "surface-container-highest": "#e1e3e4",
                    "on-error": "#ffffff",
                    "tertiary": "#ffb703",
                    "on-primary-container": "#fffbff",
                    "on-surface-variant": "#5a4042",
                    "surface-container-low": "#f3f4f5",
                    "on-tertiary-fixed-variant": "#5e4100",
                    "on-primary-fixed-variant": "#91002d"
                },
                "borderRadius": {
                    "DEFAULT": "0.25rem",
                    "lg": "0.5rem",
                    "xl": "0.75rem",
                    "full": "9999px"
                },
                "spacing": {
                    "margin-mobile": "16px",
                    "margin-desktop": "32px",
                    "base": "8px",
                    "md": "24px",
                    "gutter": "24px",
                    "sm": "12px",
                    "lg": "40px",
                    "xs": "4px",
                    "xl": "64px"
                },
                "fontFamily": {
                    "headline-lg": ["Montserrat"],
                    "label-md": ["Inter"],
                    "headline-md": ["Montserrat"],
                    "body-lg": ["Inter"],
                    "label-sm": ["Inter"],
                    "display-lg": ["Montserrat"],
                    "display-lg-mobile": ["Montserrat"],
                    "body-md": ["Inter"]
                },
                "fontSize": {
                    "headline-lg": ["32px", {
                        "lineHeight": "1.3",
                        "fontWeight": "600"
                    }],
                    "label-md": ["14px", {
                        "lineHeight": "1",
                        "letterSpacing": "0.05em",
                        "fontWeight": "600"
                    }],
                    "headline-md": ["24px", {
                        "lineHeight": "1.4",
                        "fontWeight": "600"
                    }],
                    "body-lg": ["18px", {
                        "lineHeight": "1.6",
                        "fontWeight": "400"
                    }],
                    "label-sm": ["12px", {
                        "lineHeight": "1",
                        "fontWeight": "500"
                    }],
                    "display-lg": ["48px", {
                        "lineHeight": "1.2",
                        "letterSpacing": "-0.02em",
                        "fontWeight": "700"
                    }],
                    "display-lg-mobile": ["36px", {
                        "lineHeight": "1.2",
                        "fontWeight": "700"
                    }],
                    "body-md": ["16px", {
                        "lineHeight": "1.6",
                        "fontWeight": "400"
                    }]
                }
            }
        }
    };
</script>

<style>
    /* Prevent default Bootstrap alert from flashing */
    main>.alert {
        display: none !important;
    }

    .transition-active {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Glassmorphism & Premium Cards */
    .glass-card {
        background: rgba(255, 255, 255, 0.8) !important;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5) !important;
    }

    .card-shadow {
        box-shadow: 0 4px 24px -1px rgba(0, 0, 0, 0.04), 0 2px 8px -1px rgba(0, 0, 0, 0.02);
    }

    .card-shadow-hover:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 32px -4px rgba(93, 135, 255, 0.08);
    }

    /* DISCLOSURE CARD STYLES */
    .disclosure-card {
        background: #ffffff;
        border: 1px solid rgba(0, 0, 0, 0.06);
        border-radius: 1.25rem;
        margin-bottom: 1rem;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    }

    .disclosure-card.is-open {
        box-shadow: 0 12px 32px rgba(93, 135, 255, 0.08);
        border-color: rgba(93, 135, 255, 0.2);
    }

    .disclosure-header {
        padding: 1.25rem 1.5rem;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: background 0.2s;
    }

    .disclosure-header:hover {
        background: #fafafa;
    }

    .disclosure-card.is-open .disclosure-header {
        border-bottom: 1px solid rgba(0, 0, 0, 0.04);
    }

    .disclosure-chevron {
        transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        color: #adb5bd;
    }

    .disclosure-card.is-open .disclosure-chevron {
        transform: rotate(180deg);
        color: #5d87ff;
    }

    .disclosure-body {
        display: none;
        /* Controlled by slideToggle */
        background: #fcfcfd;
    }

    /* Premium Switch Styling */
    .premium-switch .form-check-input {
        width: 38px !important;
        height: 20px !important;
        cursor: pointer;
        border-radius: 20px !important;
        border-color: rgba(0, 0, 0, 0.1);
    }

    .premium-switch .form-check-input:checked {
        background-color: #5d87ff !important;
        border-color: #5d87ff !important;
    }

    /* Matrix Toggles with Animation */
    .form-check-input {
        transition: all 0.2s ease;
    }

    .form-check-input:checked {
        background-color: #5d87ff !important;
        border-color: #5d87ff !important;
        transform: scale(1.05);
    }

    /* Role Card Active State Overlay */
    .role-card {
        position: relative;
        overflow: hidden;
    }

    .role-card.active-role::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 100px;
        height: 100px;
        background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
        pointer-events: none;
    }

    .role-card.active-role {
        background: linear-gradient(135deg, #5d87ff 0%, #7a9cff 100%) !important;
        border-color: #5d87ff !important;
        color: white !important;
        z-index: 10;
        box-shadow: 0 10px 25px -5px rgba(93, 135, 255, 0.4) !important;
    }

    .role-card.active-role .role-name,
    .role-card.active-role .role-desc,
    .role-card.active-role i:not(.radio-icon) {
        color: white !important;
    }

    .role-card.active-role .role-user-badge {
        background-color: rgba(255, 255, 255, 0.2) !important;
        color: white !important;
        backdrop-filter: blur(4px);
    }

    .role-card.active-role .radio-icon {
        color: white !important;
        filter: drop-shadow(0 0 4px rgba(255, 255, 255, 0.4));
    }

    /* Module Row Hover */
    .matrix-row:hover {
        background-color: #fff9fa;
    }

    /* Custom Scrollbar for Matrix */
    .matrix-container::-webkit-scrollbar {
        width: 6px;
    }

    .matrix-container::-webkit-scrollbar-thumb {
        background: #e2bec0;
        border-radius: 10px;
    }
</style>

<div id="tw-roles" class="bg-surface font-body-md text-on-surface p-0 m-0 w-full min-h-full">
    <!-- Header -->
    <style>
        .btn-rbac-action {
            background-color: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(8px);
            border: 1px solid rgba(93, 135, 255, 0.2);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-rbac-action:hover {
            background-color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(93, 135, 255, 0.1);
        }

        .btn-rbac-action .icon-circle {
            background-color: rgba(93, 135, 255, 0.1);
            color: #5d87ff;
            transition: all 0.3s ease;
        }

        .btn-rbac-action:hover .icon-circle {
            background-color: #5d87ff;
            color: white;
        }
    </style>

    <header class="access-heading"><div class="access-title"><span class="material-symbols-outlined">admin_panel_settings</span><div><h1>Roles and Permissions</h1><p>Choose what each staff role can view and manage.</p></div></div><div class="access-heading-actions"><button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#addPermissionModal">Add Permission</button><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createRoleModal">+ Add Role</button></div></header>

    <!-- Bento Layout Grid -->
    <div class="grid grid-cols-12 gap-gutter roles-layout">
        <!-- Left Column: System Roles List -->
        <section class="col-span-12 lg:col-span-4 flex flex-col gap-gutter">
            <div class="glass-card rounded-2xl p-md card-shadow">
                <div class="flex items-center justify-between mb-md">
                    <div>
                        <h3 class="font-headline-md text-headline-md text-on-surface mb-0 font-bold">Staff Roles</h3>
                        <p class="text-[11px] text-on-surface-variant opacity-60 mb-0">Select a role to modify its permissions.</p>
                    </div>
                    <span id="total-roles-badge" class="px-2.5 py-1 bg-primary/10 text-primary rounded-lg font-label-sm text-[10px] fw-black tracking-widest">00 TOTAL</span>
                </div>

                <!-- Search Bar Area -->
                <div class="relative mb-md">
                    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant/40" style="font-size: 20px;">search</span>
                    <input type="text"
                        id="role-search"
                        class="w-full bg-surface-container-low/50 border border-outline-variant/30 rounded-2xl py-3 ps-12 pe-4 font-body-md text-on-surface focus:outline-none focus:border-primary/50 transition-active placeholder:text-on-surface-variant/30"
                        placeholder="Search roles...">
                </div>

                <div class="space-y-sm pe-1" id="role-cards-container">
                    <!-- Roles will be loaded here via AJAX -->
                    <div class="p-5 text-center opacity-30">
                        <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                        <p class="small mb-0">Loading roles...</p>
                    </div>
                </div>
            </div>

            <!-- Pro Tip Card -->
            <div class="bg-primary/5 border border-primary/10 rounded-2xl p-md card-shadow relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-2 text-primary">
                        <span class="material-symbols-outlined" style="font-size: 20px;">security</span>
                        <h4 class="font-label-md text-label-md mb-0 font-bold uppercase tracking-wider">Access Tip</h4>
                    </div>
                    <p class="font-body-md text-body-md text-on-surface-variant opacity-90 mb-0">
                        Give each role only the access its staff need for their work.
                    </p>
                </div>
                <div class="absolute -right-4 -bottom-4 text-primary opacity-5 pointer-events-none rotate-12">
                    <span class="material-symbols-outlined" style="font-size: 80px;">lock</span>
                </div>
            </div>

        </section>

        <!-- Right Column: Permission Matrix -->
        <section class="col-span-12 lg:col-span-8">
            <form id="permission-matrix-form" action="<?= url('?route=admin/roles') ?>" method="POST" class="h-full m-0 p-0">
                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="role_id" id="matrix_role_id" value="">
                <input type="hidden" name="name" id="matrix_role_name" value="">
                <input type="hidden" name="description" id="matrix_role_desc" value="">

                <div class="glass-card rounded-2xl p-md md:p-lg card-shadow relative overflow-hidden flex flex-col">
                    <!-- Subtle background decoration -->
                    <div class="absolute top-0 right-0 w-80 h-80 bg-primary opacity-[0.03] rounded-full -mr-40 -mt-40 pointer-events-none"></div>

                    <div class="flex flex-col md:flex-row md:items-center justify-between mb-lg border-b border-outline-variant pb-md relative z-10 gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="bg-primary/10 text-primary px-2 py-1 rounded-md text-[9px] fw-black uppercase tracking-tighter shadow-sm">ROLE ACCESS</span>
                                <span class="text-on-surface-variant opacity-30 h6 mb-0">/</span>
                                <span class="text-primary font-label-sm fw-bold animate-pulse" id="matrix-active-role">Choose a role</span>
                            </div>
                            <h3 class="font-headline-md text-headline-md text-on-surface font-black tracking-tight mb-0" id="matrix-title">Role Permissions</h3>
                        </div>
                        <div class="flex gap-2">
                            <button type="button" id="btn-matrix-reset" class="px-5 py-2.5 border border-outline/20 rounded-xl font-label-sm text-label-sm hover:bg-surface-container transition-active text-on-surface font-bold bg-white/50 backdrop-blur-sm">Reset Changes</button>
                            <button type="submit" class="px-8 py-2.5 bg-primary text-on-primary rounded-xl font-label-md text-label-md hover:brightness-110 active:scale-95 transition-active border-0 font-bold shadow-lg shadow-primary/20">Save Changes</button>
                        </div>
                    </div>
                    <div class="flex-grow relative">
                        <div class="relative mb-4">
                            <span class="material-symbols-outlined absolute left-0 top-1/2 -translate-y-1/2 text-on-surface-variant opacity-40" style="font-size: 18px;">filter_list</span>
                            <input type="text" id="permission-search" class="w-full bg-transparent border-0 border-b border-outline/10 rounded-0 py-2 ps-7 pe-4 text-sm focus:ring-0 focus:border-primary transition-active" placeholder="Search permissions by section...">
                        </div>

                        <div class="matrix-container pe-2">
                            <div id="permissions-container" class="mt-3">
                                <!-- Disclosure Cards will be loaded here via AJAX -->
                                <div class="text-center py-5 opacity-30">
                                    <div class="spinner-border spinner-border-sm text-primary mb-2"></div>
                                    <p class="small mb-0">Loading permissions...</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center justify-between mt-xl pt-md border-top border-outline-variant relative z-10">
                            <div class="flex items-center gap-2 text-on-surface-variant opacity-60" style="font-size: 11px;">
                                <span class="material-symbols-outlined" style="font-size: 14px;">info</span>
                                <span>Select a role, then turn permissions on or off.</span>
                            </div>
                            <div class="text-on-surface-variant opacity-60" style="font-size: 11px;">
                                Save Changes applies your choices.
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>

    <!-- Available Permissions Section (New Management Feed) -->
    <div class="mt-xl">
        <div class="flex items-center justify-between mb-lg">
            <div>
                <h3 class="font-headline-md text-headline-md text-on-surface mb-0 font-bold tracking-tight">Available Permissions</h3>
                <p class="text-[11px] text-on-surface-variant opacity-60 mb-0">Manage the actions that can be assigned to staff roles.</p>
            </div>
            <div class="flex gap-2">
                <button type="button" class="px-4 py-2 bg-surface-container border border-outline/10 rounded-xl font-label-md text-[11px] fw-black uppercase tracking-widest text-on-surface-variant hover:bg-surface-container-high transition-active flex items-center gap-2" onclick="loadInitialData()">
                    <span class="material-symbols-outlined" style="font-size: 14px;">sync</span>
                    Refresh List
                </button>
            </div>
        </div>

        <div id="permission-registry-list" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 items-start">
            <!-- Loaded via AJAX -->
            <div class="col-span-full py-12 text-center opacity-20">
                <div class="spinner-border text-primary mb-3"></div>
                <p class="font-bold tracking-widest uppercase text-[10px]">Loading permissions...</p>
            </div>
        </div>
    </div>

    <!-- Edit Permission Modal -->
    <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered shadow-2xl" style="max-width: 500px;">
            <div class="modal-content overflow-hidden rounded-[32px] border-0 bg-surface-container-lowest shadow-2xl">
                <style>
                    .edit-perm-banner {
                        background: linear-gradient(135deg, #5279e8 0%, #3f37c9 100%);
                        padding: 25px 35px;
                        color: white;
                        position: relative;
                    }
                </style>
                <div class="edit-perm-banner d-flex align-items-center">
                    <div class="icon-box" style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 15px; border: 1px solid rgba(255,255,255,0.2);">
                        <span class="material-symbols-outlined" style="font-size: 28px;">edit_square</span>
                    </div>
                    <div>
                        <h4 class="mb-0 font-bold" style="letter-spacing: -0.5px;">Edit Permission</h4>
                        <p class="mb-0 text-[10px] opacity-75 fw-bold uppercase tracking-widest">Modification Protocol Active</p>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form id="editPermissionForm" method="POST" onsubmit="return false;" class="p-8 bg-white">
                    <input type="hidden" name="action" value="update_permission">
                    <input type="hidden" name="id" id="edit-perm-id">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="space-y-4">
                        <div>
                            <label class="text-[11px] font-black text-on-surface-variant uppercase tracking-widest opacity-60 mb-2 block">Section</label>
                            <input type="text" list="module-list" name="module" id="edit-perm-module"
                                class="w-full bg-surface-container border border-outline-variant/30 rounded-2xl py-4 px-4 font-bold text-on-surface focus:outline-none focus:border-primary/50 transition-all text-sm"
                                placeholder="Module Name" required>
                        </div>

                        <div>
                            <label class="text-[11px] font-black text-on-surface-variant uppercase tracking-widest opacity-60 mb-2 block">Permission Token</label>
                            <input type="text" name="name" id="edit-perm-name"
                                class="w-full bg-surface-container border border-outline-variant/30 rounded-2xl py-4 px-4 font-bold text-on-surface focus:outline-none focus:border-tertiary/50 transition-all text-sm"
                                placeholder="e.g. view_reports" required>
                        </div>

                        <div>
                            <label class="text-[11px] font-black text-on-surface-variant uppercase tracking-widest opacity-60 mb-2 block">System Mapping Description</label>
                            <textarea name="description" id="edit-perm-desc"
                                class="w-full bg-surface-container border border-outline-variant/30 rounded-2xl py-4 px-4 font-medium text-on-surface focus:outline-none focus:border-outline-variant transition-all text-sm"
                                rows="3" placeholder="Describe access level..."></textarea>
                        </div>
                    </div>

                    <div class="row g-3 mt-4">
                        <div class="col-4">
                            <button type="button" class="btn btn-light w-100 py-3 rounded-2xl font-bold uppercase text-[10px] tracking-widest" data-bs-dismiss="modal">Cancel</button>
                        </div>
                        <div class="col-8">
                            <button type="submit" class="btn btn-deploy w-100 py-3 text-white" style="background: #5279e8; box-shadow: 0 10px 20px rgba(114, 9, 183, 0.2);">Commit Changes</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Permission Confirmation Modal -->
    <div class="modal fade" id="deletePermissionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 rounded-[24px] shadow-2xl bg-surface-container-lowest p-8 overflow-hidden text-center">
                <div class="mb-4 w-16 h-16 rounded-full bg-error/10 text-error flex items-center justify-center mx-auto">
                    <span class="material-symbols-outlined" style="font-size: 32px;">warning</span>
                </div>
                <h4 class="font-bold text-headline-sm mb-2">Delete Permission?</h4>
                <p class="text-on-surface-variant small mb-6">Warning: This will remove '<span id="delete-perm-display-name" class="text-error font-bold"></span>' from all roles. This action is irreversible.</p>

                <form id="deletePermissionForm" method="POST" onsubmit="return false;" class="m-0">
                    <input type="hidden" name="action" value="delete_permission">
                    <input type="hidden" name="id" id="delete-perm-id">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <div class="flex gap-3">
                        <button type="button" class="flex-1 py-3 border border-outline/10 rounded-2xl font-bold text-[11px] uppercase tracking-widest hover:bg-surface-container transition-active" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="flex-1 py-3 bg-error text-white rounded-2xl font-bold text-[11px] uppercase tracking-widest shadow-lg shadow-error/20 hover:brightness-110 active:scale-95 transition-active border-0">Delete Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add Role Modal -->
    <div class="modal fade" id="createRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content border-0 rounded-2xl shadow-lg bg-surface-container-lowest p-6 text-on-surface">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface font-semibold mb-0">Add Role</h2>
                    <button type="button" class="w-10 h-10 rounded-full hover:bg-surface-container flex items-center justify-center transition-active border-0 bg-transparent text-on-surface-variant" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark fs-5"></i>
                    </button>
                </div>
                <form action="<?= url('?route=admin/roles') ?>" method="POST" class="space-y-4 m-0 p-0">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="create">

                    <div>
                        <label class="font-label-md text-label-md block mb-1.5 text-on-surface-variant font-semibold">Role Name</label>
                        <input type="text" name="name" class="w-full bg-surface-container-low border border-outline-variant rounded-lg p-2.5 focus:ring-2 focus:ring-primary text-body-md text-on-surface" placeholder="e.g. Supervisor" required>
                    </div>
                    <div>
                        <label class="font-label-md text-label-md block mb-1.5 text-on-surface-variant font-semibold">Description</label>
                        <textarea name="description" class="w-full bg-surface-container-low border border-outline-variant rounded-lg p-2.5 focus:ring-2 focus:ring-primary text-body-md text-on-surface" placeholder="Briefly describe responsibilities..." rows="3"></textarea>
                    </div>
                    <div>
                        <label class="font-label-md text-label-md block mb-1.5 text-on-surface-variant font-semibold">Clone from Template</label>
                        <select id="create_clone_select" name="clone_from" class="w-full bg-surface-container-low border border-outline-variant rounded-lg p-2.5 focus:ring-2 focus:ring-primary text-body-md text-on-surface">
                            <option value="">Empty Template</option>
                            <!-- Options will be loaded via JS -->
                        </select>
                    </div>

                    <!-- Hidden inputs for permission presets -->
                    <div id="create-modal-hidden-permissions" class="hidden">
                        <!-- Loaded via JS -->
                    </div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" class="flex-1 py-2.5 border border-outline rounded-pill font-label-md text-label-md hover:bg-surface-container transition-active bg-transparent text-on-surface font-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="flex-1 py-2.5 bg-primary text-on-primary rounded-pill font-label-md text-label-md hover:brightness-110 active:scale-95 transition-active border-0 font-semibold shadow-sm">Create Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Role Modal -->
    <div class="modal fade" id="editRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 480px;">
            <div class="modal-content border-0 rounded-2xl shadow-lg bg-surface-container-lowest p-6 text-on-surface">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-headline-md text-headline-md text-on-surface font-semibold mb-0">Edit Role</h2>
                    <button type="button" class="w-10 h-10 rounded-full hover:bg-surface-container flex items-center justify-center transition-active border-0 bg-transparent text-on-surface-variant" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark fs-5"></i>
                    </button>
                </div>
                <form action="<?= url('?route=admin/roles') ?>" method="POST" class="space-y-4 m-0 p-0">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="role_id" id="edit_role_id">

                    <div>
                        <label class="font-label-md text-label-md block mb-1.5 text-on-surface-variant font-semibold">Role Name</label>
                        <input type="text" name="name" id="edit_role_name" class="w-full bg-surface-container-low border border-outline-variant rounded-lg p-2.5 focus:ring-2 focus:ring-primary text-body-md text-on-surface" required>
                    </div>
                    <div>
                        <label class="font-label-md text-label-md block mb-1.5 text-on-surface-variant font-semibold">Description</label>
                        <textarea name="description" id="edit_role_desc" class="w-full bg-surface-container-low border border-outline-variant rounded-lg p-2.5 focus:ring-2 focus:ring-primary text-body-md text-on-surface" rows="3"></textarea>
                    </div>

                    <!-- Hidden inputs for active permissions -->
                    <div id="edit-modal-hidden-permissions" class="hidden"></div>

                    <div class="pt-4 flex gap-3">
                        <button type="button" class="flex-1 py-2.5 border border-outline rounded-pill font-label-md text-label-md hover:bg-surface-container transition-active bg-transparent text-on-surface font-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="flex-1 py-2.5 bg-primary text-on-primary rounded-pill font-label-md text-label-md hover:brightness-110 active:scale-95 transition-active border-0 font-semibold shadow-sm">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Delete Role Modal -->
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
            <div class="modal-content border-0 rounded-2xl shadow-lg bg-surface-container-lowest p-6 text-on-surface">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-headline-md text-headline-md text-error font-semibold mb-0">Confirm Deletion</h2>
                    <button type="button" class="w-10 h-10 rounded-full hover:bg-surface-container flex items-center justify-center transition-active border-0 bg-transparent text-on-surface-variant" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark fs-5"></i>
                    </button>
                </div>
                <form action="<?= url('?route=admin/roles') ?>" method="POST" class="space-y-4 m-0 p-0">
                    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="role_id" id="delete_role_id">

                    <div class="text-center py-4">
                        <i class="fa-solid fa-triangle-exclamation text-error text-6xl mb-2"></i>
                        <h5 class="fw-bold text-on-surface mb-2">Delete Role <span id="delete_role_name" class="text-primary font-bold"></span>?</h5>
                        <p class="text-on-surface-variant small mb-0">This action will remove the role from all assigned users. Ensure no users depend solely on this role for access.</p>
                    </div>

                    <div class="flex gap-3">
                        <button type="button" class="flex-1 py-2.5 border border-outline rounded-pill font-label-md text-label-md hover:bg-surface-container transition-active bg-transparent text-on-surface font-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="flex-1 py-2.5 bg-error text-on-primary rounded-pill font-label-md text-label-md hover:brightness-110 active:scale-95 transition-active border-0 font-semibold shadow-sm">Delete Role</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        let activeRoleId = null;

        // Data storage for synchronous lookup
        let rolesData = [];
        let permsData = {};

        // Track open state of registry groups to persist across dynamic refreshes
        let openRegistryStates = {};

        window.loadInitialData = function() {
            $.when(
                $.getJSON('?route=admin/roles/data'),
                $.getJSON('?route=admin/permissions/data')
            ).done(function(rolesRes, permsRes) {
                rolesData = rolesRes[0].roles;
                permsData = permsRes[0].permissions;

                renderRolesList(rolesData);
                renderPermissionsMatrix(permsData);
                renderPermissionRegistry(permsData);
                populateCloneSelect(rolesData);

                // Select first role
                if (rolesData.length > 0 && !activeRoleId) {
                    selectRole(rolesData[0].id);
                }
            });
        }

        function renderPermissionRegistry(perms) {
            const container = $('#permission-registry-list');
            container.empty();

            Object.keys(perms).forEach(module => {
                const modulePermissions = perms[module];
                const moduleSlug = module.toLowerCase().replace(/[^a-z0-9]/g, '-');

                let moduleHtml = `
                <div class="reg-disclosure glass-card rounded-2xl border-0 shadow-sm hover:shadow-md transition-all bg-white/40 backdrop-blur-md mb-4 overflow-hidden" id="reg-group-${moduleSlug}">
                    <!-- Header -->
                    <div class="reg-header p-5 flex items-center justify-between cursor-pointer active:bg-surface-container-low transition-all" 
                         onclick="toggleRegistryGroup('${moduleSlug}')">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center font-bold text-xs uppercase shadow-sm">
                                ${module.substring(0, 2)}
                            </div>
                            <div>
                                <h4 class="font-bold text-on-surface mb-0 tracking-tight text-sm">${module}</h4>
                                <p class="text-[10px] text-on-surface-variant opacity-50 uppercase fw-black tracking-widest">${modulePermissions.length} Permissions</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                             <div class="reg-chevron w-8 h-8 rounded-full bg-surface-container-low flex items-center justify-center text-on-surface-variant opacity-40 transition-transform"
                                  style="transform: ${openRegistryStates[moduleSlug] ? 'rotate(180deg)' : 'rotate(0deg)'}">
                                <span class="material-symbols-outlined" style="font-size: 18px;">expand_more</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Body (Collapsible) -->
                    <div class="reg-body p-5 pt-0 ${openRegistryStates[moduleSlug] ? '' : 'hidden'} border-t border-outline-variant/10">
                        <div class="space-y-3 mt-4">
                            ${modulePermissions.map(p => `
                                <div class="perm-registry-item flex items-center justify-between p-3 rounded-xl bg-surface-container-low/30 hover:bg-white transition-all border border-transparent hover:border-primary/10">
                                    <div class="overflow-hidden pe-2">
                                        <div class="font-bold text-[11px] text-on-surface mb-0.5 truncate">${p.name}</div>
                                        <div class="text-[10px] text-on-surface-variant opacity-60 truncate">${p.description || 'No system alias...'}</div>
                                    </div>
                                    <div class="action-btns flex gap-1">
                                        <button type="button" class="btn-perm-edit w-8 h-8 rounded-lg flex items-center justify-center transition-all border-0" 
                                                onclick="openEditPermissionModal(${p.id}, '${p.name}', '${p.description || ''}', '${p.module}')">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">edit</span>
                                        </button>
                                        ${!p.is_core ? `<button type="button" class="btn-perm-delete w-8 h-8 rounded-lg flex items-center justify-center transition-all border-0"
                                                onclick="openDeletePermissionModal(${p.id}, '${p.name}')">
                                            <span class="material-symbols-outlined" style="font-size: 16px;">delete</span>
                                        </button>` : ''}
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </div>
            `;
                container.append(moduleHtml);
            });
        }

        // Registry Toggle Handler
        window.toggleRegistryGroup = function(slug) {
            const card = $(`#reg-group-${slug}`);
            const body = card.find('.reg-body');
            const chevron = card.find('.reg-chevron');
            
            if (body.is(':visible')) {
                body.slideUp(250);
                chevron.css('transform', 'rotate(0deg)');
                card.removeClass('is-open ring-1 ring-primary/20 bg-white/70 shadow-lg');
                openRegistryStates[slug] = false;
            } else {
                // Accordion behavior: close all other open groups
                $('.reg-disclosure.is-open').each(function() {
                    const otherSlug = this.id.replace('reg-group-', '');
                    if (otherSlug !== slug) {
                        const $other = $(this);
                        $other.find('.reg-body').slideUp(250);
                        $other.find('.reg-chevron').css('transform', 'rotate(0deg)');
                        $other.removeClass('is-open ring-1 ring-primary/20 bg-white/70 shadow-lg');
                        openRegistryStates[otherSlug] = false;
                    }
                });

                body.slideDown(250);
                chevron.css('transform', 'rotate(180deg)');
                card.addClass('is-open ring-1 ring-primary/20 bg-white/70 shadow-lg');
                openRegistryStates[slug] = true;
            }
        };

        // Global Modal Triggers for Available Permissions
        window.openEditPermissionModal = function(id, name, desc, module) {
            $('#edit-perm-id').val(id);
            $('#edit-perm-name').val(name);
            const permission=Object.values(permsData).flat().find(p => Number(p.id) === Number(id));
            $('#edit-perm-name').prop('readOnly', !!(permission && permission.is_core));
            $('#edit-perm-desc').val(desc);
            $('#edit-perm-module').val(module);
            $('#editPermissionModal').modal('show');
        };

        window.openDeletePermissionModal = function(id, name) {
            $('#delete-perm-id').val(id);
            $('#delete-perm-display-name').text(name);
            $('#deletePermissionModal').modal('show');
        };

        function renderRolesList(roles) {
            const container = $('#role-cards-container');
            container.empty();
            $('#total-roles-badge').text(`${String(roles.length).padStart(2, '0')} TOTAL`);

            // Simple sorting for visual hierarchy: Admin > Manager > others
            const sortedRoles = [...roles].sort((a, b) => {
                const getRank = (name) => {
                    const n = name.toLowerCase();
                    if (n.includes('admin')) return 0;
                    if (n.includes('manager')) return 1;
                    return 2;
                };
                return getRank(a.name) - getRank(b.name);
            });

            sortedRoles.forEach((r, index) => {
                let icon = 'fa-shield-halved';
                let iconBg = 'bg-primary/10';
                let iconColor = 'text-primary';
                const nameLower = r.name.toLowerCase();

                // Determine indentation and hierarchy line
                let indent = '';
                let line = '';
                if (nameLower.includes('manager')) {
                    indent = 'ms-6';
                    line = '<div class="absolute left-[-16px] top-[-20px] bottom-1/2 w-4 border-l-2 border-b-2 border-outline-variant/30 rounded-bl-lg"></div>';
                } else if (!nameLower.includes('admin')) {
                    indent = 'ms-12';
                    line = '<div class="absolute left-[-16px] top-[-20px] bottom-1/2 w-4 border-l-2 border-b-2 border-outline-variant/30 rounded-bl-lg"></div>';
                }

                if (nameLower.includes('manager')) {
                    icon = 'fa-store';
                    iconBg = 'bg-secondary/10';
                    iconColor = 'text-secondary';
                } else if (nameLower.includes('cashier')) {
                    icon = 'fa-credit-card';
                    iconBg = 'bg-tertiary/10';
                    iconColor = 'text-tertiary';
                }

                const cardHtml = `
                <div class="relative ${indent}">
                    ${line}
                    <div class="role-card group p-3.5 mb-2 rounded-xl border border-outline-variant/30 bg-white/50 backdrop-blur-sm transition-active hover:card-shadow-raised flex items-center justify-between cursor-pointer"
                         id="role-card-${r.id}"
                         onclick="selectRole(${r.id})">
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-xl ${iconBg} flex items-center justify-center ${iconColor} transition-transform group-hover:scale-110">
                                <i class="fa-solid ${icon} fs-5"></i>
                            </div>
                            <div>
                                <p class="role-name font-label-md text-label-md text-on-surface font-bold mb-0">${r.name}</p>
                                <p class="role-desc font-label-sm text-[11px] text-on-surface-variant opacity-70 mb-0">${r.description || 'System role access'}</p>
                            </div>
                        </div>
                        <div class="flex flex-col items-end gap-1.5 min-w-[80px]">
                            <span class="role-user-badge px-2 py-0.5 bg-surface-container text-on-surface-variant rounded-pill font-label-sm text-[10px] fw-bold tracking-tight">${String(r.user_count).padStart(2, '0')} Users</span>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity" onclick="event.stopPropagation()">
                                ${r.can_edit !== false && r.name.toLowerCase().replace(/[\s_-]/g, '') !== 'superadmin' ? `<button type="button" class="text-secondary hover:bg-secondary/10 p-1.5 flex items-center justify-center rounded-full transition-active border-0 bg-transparent"
                                        onclick="openEditModal(${r.id})">
                                    <i class="fa-solid fa-pen" style="font-size:11px;"></i>
                                </button>` : ''}
                                ${r.can_delete !== false && r.is_system == 0 && r.name.toLowerCase().replace(/[\s_-]/g, '') !== 'superadmin' ? `
                                    <button type="button" class="text-error hover:bg-error/10 p-1.5 flex items-center justify-center rounded-full transition-active border-0 bg-transparent"
                                            onclick="openDeleteModal(${r.id}, '${r.name}')">
                                        <i class="fa-solid fa-trash" style="font-size:11px;"></i>
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>`;
                container.append(cardHtml);
            });
        }

        function renderPermissionsMatrix(grouped) {
            const container = $('#permissions-container');
            container.empty();

            const hiddenCreate = $('#create-modal-hidden-permissions');
            hiddenCreate.empty();

            Object.keys(grouped).forEach((module, index) => {
                const perms = grouped[module];
                const moduleLower = module.toLowerCase();

                let catIcon = 'fa-puzzle-piece';
                let catColor = 'text-primary';
                let catBg = 'bg-primary/5';

                if (moduleLower.includes('invent')) {
                    catIcon = 'fa-boxes-stacked';
                } else if (moduleLower.includes('sale') || moduleLower.includes('pos')) {
                    catIcon = 'fa-cash-register';
                    catColor = 'text-secondary';
                    catBg = 'bg-secondary/5';
                } else if (moduleLower.includes('report')) {
                    catIcon = 'fa-chart-line';
                    catColor = 'text-tertiary';
                    catBg = 'bg-tertiary/5';
                } else if (moduleLower.includes('user')) {
                    catIcon = 'fa-user-gear';
                }

                let cardHtml = `
                <div class="disclosure-card border-outline-variant/30 transition-active" data-module="${module}">
                    <div class="disclosure-header">
                        <div class="flex items-center gap-4">
                            <div class="w-11 h-11 rounded-2xl ${catBg} ${catColor} flex items-center justify-center shadow-sm border border-white">
                                <i class="fa-solid ${catIcon} text-lg"></i>
                            </div>
                            <div>
                                <h6 class="font-bold text-on-surface uppercase tracking-widest m-0" style="font-size: 13px;">${module}</h6>
                                <div class="text-[10px] text-on-surface-variant font-medium opacity-60">${perms.length} Permissions Available</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="flex items-center gap-2" onclick="event.stopPropagation()">
                                <button type="button" class="btn-bulk text-[10px] fw-bold text-primary px-3 py-1.5 rounded-xl hover:bg-primary/5 border border-primary/20 bg-transparent transition-all duration-300" 
                                        onclick="toggleModulePerms('${module}', true, this)">ALL</button>
                                <button type="button" class="btn-bulk text-[10px] fw-bold text-outline px-3 py-1.5 rounded-xl hover:bg-surface-container border border-outline/20 bg-transparent transition-all duration-300" 
                                        onclick="toggleModulePerms('${module}', false, this)">NONE</button>
                            </div>
                            <button type="button" class="disclosure-toggle" aria-label="Expand or collapse permissions" aria-expanded="false" aria-controls="permission-section-${index}"><span class="material-symbols-outlined disclosure-chevron" aria-hidden="true" style="font-size: 24px;">expand_more</span></button>
                        </div>
                    </div>
                    <div class="disclosure-body" id="permission-section-${index}">
                        <div class="p-2">`;

                perms.forEach(p => {
                    let pIcon = 'fa-circle-check';
                    let pColor = 'text-primary/30';
                    const pName = p.name.toLowerCase();

                    if (pName.includes('view') || pName.includes('access')) {
                        pIcon = 'fa-eye';
                    } else if (pName.includes('create') || pName.includes('add')) {
                        pIcon = 'fa-plus';
                    } else if (pName.includes('edit') || pName.includes('update')) {
                        pIcon = 'fa-pen-nib';
                    } else if (pName.includes('delete') || pName.includes('remove')) {
                        pIcon = 'fa-trash-alt';
                        pColor = 'text-error/40';
                    }

                    cardHtml += `
                    <div class="flex items-center justify-between px-4 py-3 rounded-xl hover:bg-surface-container-low transition-all permission-item mb-1" data-perm-name="${p.name}">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white flex items-center justify-center border border-outline-variant/10">
                                <i class="fa-solid ${pIcon} ${pColor} text-xs"></i>
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-on-surface-variant" style="font-size: 12px;">${$('<span>').text(p.description || p.name).html()}</span>
                                <span class="text-[9px] text-on-surface-variant/40 opacity-80 tracking-tighter">${$('<span>').text(p.name).html()}</span>
                            </div>
                        </div>
                        <div class="form-check form-switch premium-switch p-0 m-0">
                            <input class="form-check-input matrix-checkbox" type="checkbox" name="permissions[]" value="${p.id}" data-module="${module}"
                                   id="perm_${p.id}">
                        </div>
                    </div>`;

                    hiddenCreate.append(`<input type="checkbox" name="permissions[]" value="${p.id}" class="create-hidden-cb" id="create_cb_${p.id}">`);
                });

                cardHtml += `
                        </div>
                    </div>
                </div>`;

                container.append(cardHtml);
            });
        }

        // Custom disclosure toggle logic
        $(document).on('click', '.disclosure-header', function() {
            const card = $(this).closest('.disclosure-card');
            const body = card.find('.disclosure-body');

            const expanded = !card.hasClass('is-open');
            card.toggleClass('is-open', expanded);
            card.find('.disclosure-toggle').attr('aria-expanded', String(expanded));
            body.stop(true, true)[expanded ? 'slideDown' : 'slideUp'](200);
        });

        window.toggleModulePerms = function(module, state, btn) {
            const selector = `.matrix-checkbox[data-module="${module}"]`;
            $(selector).prop('checked', state).trigger('change');

            const $btn = $(btn);
            const originalClasses = $btn.attr('class');

            if (state) {
                // "ALL" indicator
                $btn.attr('style', 'background-color: #5d87ff !important; color: #ffffff !important; border-color: #5d87ff !important; box-shadow: 0 4px 12px rgba(93, 135, 255, 0.3) !important;');
                $btn.html('<i class="fa-solid fa-check-double me-1"></i> APPLIED');
            } else {
                // "NONE" indicator - High contrast dark
                $btn.attr('style', 'background-color: #1a1b26 !important; color: #ffffff !important; border-color: #1a1b26 !important; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;');
                $btn.html('<i class="fa-solid fa-xmark me-1"></i> CLEARED');
            }

            setTimeout(() => {
                $btn.attr('style', ''); // Clear inline styles
                $btn.html(state ? 'ALL' : 'NONE');
            }, 1200);
        };

        function populateCloneSelect(roles) {
            const select = $('#create_clone_select');
            roles.forEach(r => {
                select.append(`<option value="${r.id}">${r.name} (Base)</option>`);
            });

            select.on('change', function() {
                const roleId = $(this).val();
                if (!roleId) {
                    $('.create-hidden-cb').prop('checked', false);
                    return;
                }
                const role = rolesData.find(r => r.id == roleId);
                const perms = role && role.permission_ids ? role.permission_ids.split(',') : [];
                $('.create-hidden-cb').prop('checked', false);
                perms.forEach(pid => {
                    $(`#create_cb_${pid}`).prop('checked', true);
                });
            });
        }

        function fetchRolePermissions(roleId) {
            const dfd = $.Deferred();
            $.getJSON('?route=admin/roles/data').done(function(res) {
                const role = res.roles.find(r => r.id == roleId);
                dfd.resolve(role && role.permission_ids ? role.permission_ids.split(',') : []);
            });
            return dfd.promise();
        }

        // Role Selection
        window.selectRole = function(roleId) {
            activeRoleId = roleId;
            const role = rolesData.find(r => r.id == roleId);
            if (!role) return;

            $('.role-card').removeClass('active-role card-shadow-raised');
            $(`.role-card`).find('.radio-icon').removeClass('fa-solid fa-circle-dot text-white').addClass('fa-regular fa-circle text-outline');

            const activeCard = $(`#role-card-${roleId}`);
            activeCard.addClass('active-role card-shadow-raised');
            activeCard.find('.role-user-badge').addClass('bg-white/20 text-white').removeClass('bg-surface-container text-on-surface-variant');

            $('#matrix-active-role').text(role.name);
            $('#matrix-title').text(`${role.name} Permissions`);
            $('#matrix_role_id').val(role.id);
            $('#matrix_role_name').val(role.name);
            $('#matrix_role_desc').val(role.description);
            const superRole = role.name.toLowerCase().replace(/[\s_-]/g, '') === 'superadmin';
            const protectedRole = superRole || role.can_edit === false;
            $('#permission-matrix-form button[type=submit], #btn-matrix-reset, #permission-matrix-form .btn-bulk, #permission-matrix-form .matrix-checkbox').prop('disabled', protectedRole);
            $('#matrix-title').text(superRole ? 'Superadmin — Full Access (Protected)' : `${role.name} Permissions${protectedRole ? ' (View Only)' : ''}`);

            $('.matrix-checkbox').prop('checked', false);
            if (role.permission_ids) {
                role.permission_ids.split(',').forEach(pid => {
                    $(`#perm_${pid}`).prop('checked', true);
                });
            }
        };

        window.openEditModal = function(id) {
            const r = rolesData.find(x => x.id == id);
            if (!r || r.can_edit === false || r.name.toLowerCase().replace(/[\s_-]/g, '') === 'superadmin') return;
            $('#edit_role_id').val(r.id);
            $('#edit_role_name').val(r.name);
            $('#edit_role_desc').val(r.description);

            const hidden = $('#edit-modal-hidden-permissions').empty();
            if (r.permission_ids) {
                r.permission_ids.split(',').forEach(pid => {
                    hidden.append(`<input type="checkbox" name="permissions[]" value="${pid}" checked>`);
                });
            }
            new bootstrap.Modal('#editRoleModal').show();
        };

        window.openDeleteModal = function(id, name) {
            const role = rolesData.find(r => Number(r.id) === Number(id));
            if (!role || role.can_delete === false || Number(role.is_system) !== 0 || role.name.toLowerCase().replace(/[\s_-]/g, '') === 'superadmin') {
                showToast('This role is protected and cannot be deleted.', 'error');
                return;
            }
            $('#delete_role_id').val(id);
            $('#delete_role_name').text(name);
            new bootstrap.Modal('#deleteRoleModal').show();
        };

        loadInitialData();

        // Re-bind searches for dynamic content
        $(document).on('input', '#role-search', function() {
            const term = this.value.toLowerCase();
            $('.role-card').each(function() {
                const name = $(this).find('.role-name').text().toLowerCase();
                const desc = $(this).find('.role-desc').text().toLowerCase();
                $(this).toggle(name.includes(term) || desc.includes(term));
            });
        });

        $(document).on('input', '#permission-search', function() {
            const term = this.value.toLowerCase();
            $('.disclosure-card').each(function() {
                const moduleName = $(this).data('module').toLowerCase();
                let hasVisiblePerm = false;

                $(this).find('.permission-item').each(function() {
                    const permName = $(this).data('perm-name').toLowerCase();
                    const isMatch = permName.includes(term) || $(this).text().toLowerCase().includes(term) || moduleName.includes(term);
                    $(this).toggle(isMatch);
                    if (isMatch) hasVisiblePerm = true;
                });

                $(this).toggle(hasVisiblePerm || moduleName.includes(term));

                // Auto expand if searching for a specific permission
                if (term.length > 1 && hasVisiblePerm) {
                    $(this).addClass('is-open');
                    $(this).find('.disclosure-body').slideDown(200);
                }
            });
        });

        $('#btn-matrix-reset').on('click', function() {
            if (activeRoleId) {
                selectRole(activeRoleId);
            }
        });
    });
</script>



<!-- Add Permission Modal (Refined Design) -->
<style>
    #addPermissionModal .modal-content {
        border-radius: 24px;
        border: none;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    #addPermissionModal .premium-banner {
        background: linear-gradient(135deg, #5d87ff 0%, #5279e8 100%);
        padding: 30px 40px;
        color: white;
        position: relative;
    }

    #addPermissionModal .premium-banner::after {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="white" fill-opacity="0.05"/></svg>');
        background-size: 80px 80px;
        opacity: 0.2;
    }

    #addPermissionModal .icon-box {
        width: 54px;
        height: 54px;
        background: rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(8px);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 20px;
        border: 1px solid rgba(255, 255, 255, 0.3);
    }

    #addPermissionModal .form-label-custom {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.5px;
        color: #6c757d;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }

    #addPermissionModal .form-label-custom::before {
        content: '';
        width: 4px;
        height: 12px;
        background: #5d87ff;
        border-radius: 2px;
        margin-right: 8px;
    }

    #addPermissionModal .input-group-custom {
        position: relative;
        margin-bottom: 24px;
    }

    #addPermissionModal .input-group-custom i {
        position: absolute;
        left: 18px;
        top: 16px;
        color: #5d87ff;
        opacity: 0.5;
        transition: opacity 0.3s;
    }

    #addPermissionModal .input-group-custom input:focus+i,
    #addPermissionModal .input-group-custom textarea:focus+i {
        opacity: 1;
    }

    #addPermissionModal .input-custom {
        width: 100%;
        background: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: 16px;
        padding: 14px 20px 14px 48px;
        font-size: 14px;
        font-weight: 600;
        color: #2b2d42;
        transition: all 0.3s;
    }

    #addPermissionModal .input-custom:focus {
        background: #ffffff;
        border-color: #5d87ff;
        box-shadow: 0 0 0 4px rgba(93, 135, 255, 0.08);
        outline: none;
    }

    #addPermissionModal .btn-chip {
        border: 1px solid #e9ecef;
        background: #ffffff;
        padding: 6px 14px;
        border-radius: 30px;
        font-size: 10px;
        font-weight: 800;
        color: #495057;
        text-transform: uppercase;
        margin-right: 8px;
        margin-bottom: 8px;
        transition: all 0.2s;
    }

    #addPermissionModal .btn-chip:hover {
        border-color: #5d87ff;
        color: #5d87ff;
        background: rgba(93, 135, 255, 0.05);
    }

    #addPermissionModal .btn-deploy {
        background: linear-gradient(90deg, #5d87ff 0%, #7a9cff 100%);
        border: none;
        color: white;
        padding: 16px 32px;
        border-radius: 16px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 12px;
        box-shadow: 0 10px 20px rgba(93, 135, 255, 0.3);
        transition: all 0.3s;
    }

    #addPermissionModal .btn-deploy:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 24px rgba(93, 135, 255, 0.4);
        filter: brightness(1.05);
    }

    #addPermissionModal .btn-abort {
        background: #f1f3f5;
        border: none;
        color: #495057;
        padding: 16px 32px;
        border-radius: 16px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-size: 12px;
        transition: all 0.2s;
    }

    #addPermissionModal .btn-abort:hover {
        background: #e9ecef;
    }

    /* Unique stand-out buttons */
    .btn-perm-edit {
        background: rgba(114, 9, 183, 0.08);
        color: #5279e8;
        border: 1px solid rgba(114, 9, 183, 0.1);
    }

    .btn-perm-edit:hover {
        background: #5279e8;
        color: white;
        box-shadow: 0 4px 12px rgba(114, 9, 183, 0.2);
        transform: translateY(-2px) scale(1.05);
    }

    .btn-perm-delete {
        background: rgba(247, 37, 133, 0.08);
        color: #f72585;
        border: 1px solid rgba(247, 37, 133, 0.1);
    }

    .btn-perm-delete:hover {
        background: #f72585;
        color: white;
        box-shadow: 0 4px 12px rgba(247, 37, 133, 0.2);
        transform: translateY(-2px) scale(1.05);
    }
</style>

<div class="modal fade px-3" id="addPermissionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 500px;">
        <div class="modal-content">
            <div class="premium-banner d-flex align-items-center">
                <div class="icon-box">
                    <span class="material-symbols-outlined" style="font-size: 32px;">shield_person</span>
                </div>
                <div>
                    <h4 class="mb-1" style="font-weight: 900; letter-spacing: -0.5px;">Add Permission</h4>
                    <p class="mb-0 text-[10px] opacity-75 fw-bold uppercase tracking-widest">Choose the section and action below.</p>
                </div>
                <!-- Fixed Close Button -->
                <button type="button"
                    class="btn-close btn-close-white"
                    data-bs-dismiss="modal"
                    aria-label="Close"
                    style="position: absolute; top: 20px; right: 20px; z-index: 1051; opacity: 1; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));"></button>
            </div>

            <form id="addPermissionForm" method="POST" onsubmit="return false;" class="p-4 p-md-5 bg-white">
                <input type="hidden" name="action" value="add_permission">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <div class="form-label-custom">Section</div>
                <div class="input-group-custom">
                    <input type="text" list="module-list" name="module" id="perm-module-input"
                        class="input-custom"
                        placeholder="e.g. Inventory, Sales, Team..." required>
                    <i class="material-symbols-outlined">category</i>
                    <datalist id="module-list"></datalist>
                </div>

                <div class="d-flex flex-wrap mb-4 mt-n2">
                    <button type="button" class="btn btn-chip" onclick="$('#perm-module-input').val('Inventory')">Inventory</button>
                    <button type="button" class="btn btn-chip" onclick="$('#perm-module-input').val('Sales')">Sales</button>
                    <button type="button" class="btn btn-chip" onclick="$('#perm-module-input').val('POS')">POS System</button>
                    <button type="button" class="btn btn-chip" onclick="$('#perm-module-input').val('Users')">Users</button>
                </div>

                <div class="form-label-custom" style="&::before{background: #5279e8;}">Permission Name</div>
                <div class="input-group-custom">
                    <input type="text" name="name"
                        class="input-custom"
                        placeholder="e.g. view_reports, delete_products" required>
                    <i class="material-symbols-outlined">fingerprint</i>
                </div>

                <div class="input-group-custom mb-0">
                    <textarea name="description"
                        class="input-custom h-auto"
                        rows="2" placeholder="Describe the purpose of this permission..."></textarea>
                    <i class="material-symbols-outlined">description</i>
                </div>

                <div class="row g-3 mt-4">
                    <div class="col-5">
                        <button type="button" class="btn btn-abort w-100" data-bs-dismiss="modal">Cancel</button>
                    </div>
                    <div class="col-7">
                        <button type="submit" class="btn btn-deploy w-100">Save Permission</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Load modules for the datalist
        $('#addPermissionModal').on('show.bs.modal', function() {
            $.ajax({
                url: 'ajax/role_action.php',
                type: 'POST',
                data: {
                    action: 'get_modules',
                    csrf_token: $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    if (response.success) {
                        let options = '';
                        response.modules.forEach(m => {
                            options += `<option value="${m}">`;
                        });
                        $('#module-list').html(options);
                    }
                }
            });
        });

        // Handle Add Permission Form
        $('#addPermissionForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            const originalText = $btn.text();

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

            $.ajax({
                url: 'ajax/role_action.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        $('#addPermissionModal').modal('hide');
                        $('#addPermissionForm')[0].reset();
                        $btn.prop('disabled', false).text(originalText);
                        window.loadInitialData(); // Dynamic Refresh
                    } else {
                        showToast(response.message, 'danger');
                        $btn.prop('disabled', false).text(originalText);
                    }
                },
                error: function() {
                    showToast('Submission failed: Token may already exist or server is busy.', 'danger');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Handle Edit Permission Form
        $('#editPermissionForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Syncing...');

            $.ajax({
                url: 'ajax/role_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#editPermissionModal').modal('hide');
                        showToast(response.message, 'success');
                        $btn.prop('disabled', false).text('Commit Changes');
                        window.loadInitialData(); // Dynamic Refresh
                    } else {
                        showToast(response.message, 'danger');
                        $btn.prop('disabled', false).text('Commit Changes');
                    }
                },
                error: function() {
                    showToast('Sync interrupted: Potential duplicate or server error.', 'danger');
                    $btn.prop('disabled', false).text('Commit Changes');
                }
            });
        });

        // Handle Delete Permission Form
        $('#deletePermissionForm').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Purging...');

            $.ajax({
                url: 'ajax/role_action.php',
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#deletePermissionModal').modal('hide');
                        showToast(response.message, 'success');
                        $btn.prop('disabled', false).text('Delete Permission');
                        window.loadInitialData(); // Dynamic Refresh
                    } else {
                        showToast(response.message, 'danger');
                        $btn.prop('disabled', false).text('Delete Permission');
                    }
                },
                error: function() {
                    showToast('Deletion protocol failed on server.', 'danger');
                    $btn.prop('disabled', false).text('Delete Permission');
                }
            });
        });

        // Handle Role Matrix Save via AJAX
        $('#permission-matrix-form').on('submit', function(e) {
            e.preventDefault();
            const $btn = $(this).find('button[type="submit"]');
            const originalText = $btn.text();

            $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

            $.ajax({
                url: $(this).attr('action'),
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (!response || response.success !== true) { showToast(response && response.message || 'Could not save permissions.', 'error'); $btn.prop('disabled', false).text(originalText); return; }
                    showToast('Permissions saved.', 'success');
                    $btn.prop('disabled', false).text(originalText);
                    window.loadInitialData(); // Refresh State
                },
                error: function() {
                    showToast('Sync failure', 'danger');
                    $btn.prop('disabled', false).text(originalText);
                }
            });
        });
    });
</script>
<link rel="stylesheet" href="<?= url('assets/css/access-management.css') ?>">
</section>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
