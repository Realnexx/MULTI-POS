<?php
    session_start();
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
    <title>Tabulator Management - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .competition-card { 
            transition: all 0.3s ease; 
            cursor: pointer; 
            border: 1px solid rgba(0,0,0,0.05);
        }
        .competition-card:hover { 
            transform: translateY(-5px); 
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
            border-color: var(--bs-primary);
        }
        .tabulator-badge { 
            margin: 2px; 
            font-size: 0.75rem; 
            padding: 5px 10px;
            border-radius: 20px;
        }
        .tab-checkbox-list { 
            max-height: 400px; 
            overflow-y: auto; 
            scrollbar-width: thin;
        }
        .card-icon-container {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
        }
    </style>
</head>
<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full" data-sidebar-position="fixed" data-header-position="fixed">
        <?php include 'components/sidebar.php'; ?>
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <?php include 'components/header.php'; ?>

                    <div class="top-part card bg-light-primary shadow-none position-relative overflow-hidden mb-4 rounded-4">
                        <div class="card-body px-4 py-4">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-bolder mb-2 text-primary">🧮 Tabulator Management</h4>
                                    <p class="mb-0 text-muted">Assign and manage multiple tabulators for each competition.</p>
                                    <nav aria-label="breadcrumb" class="mt-3">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item"><a class="text-primary text-decoration-none" href="index.php">Admin</a></li>
                                            <li class="breadcrumb-item"><a class="text-primary text-decoration-none" href="#">Tabulation</a></li>
                                            <li class="breadcrumb-item text-muted" aria-current="page">Tabulators</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3 text-center">
                                    <iconify-icon icon="solar:calculator-minimalistic-bold-duotone" class="text-primary opacity-25" style="font-size: 6rem; position: absolute; right: 20px; top: 50%; transform: translateY(-50%);"></iconify-icon>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-semibold mb-0">Active Competitions</h5>
                        <div class="input-group" style="max-width: 300px;">
                            <span class="input-group-text bg-transparent border-end-0"><i class="ti ti-search"></i></span>
                            <input type="text" id="compSearch" class="form-control border-start-0" placeholder="Search competition...">
                        </div>
                    </div>

                    <div class="row" id="competitionGrid">
                        <!-- Competitions will be loaded here -->
                        <div class="col-12 text-center py-5">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2 text-muted">Initialising Tabulator System...</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Assignment Modal -->
    <div class="modal fade" id="assignmentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom-0 pt-4 px-4">
                    <div>
                        <h5 class="modal-title fw-bolder" id="modalCompTitle">Manage Tabulators</h5>
                        <p class="text-muted small mb-0">Toggle switches to assign/unassign tabulators.</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body px-4 pb-4">
                    <ul class="nav nav-pills nav-fill bg-light p-1 rounded-pill mb-3" id="pills-tab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active rounded-pill px-4" id="pills-tabs-tab" data-bs-toggle="pill" data-bs-target="#pills-tabs" type="button" role="tab" aria-selected="true">Tabulators</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill px-4" id="pills-orgs-tab" data-bs-toggle="pill" data-bs-target="#pills-orgs" type="button" role="tab" aria-selected="false">Chairman / Organizer</button>
                        </li>
                    </ul>
                    <div class="mb-3">
                        <input type="text" id="tabSearch" class="form-control form-control-sm" placeholder="Filter people...">
                    </div>
                    <div class="tab-content" id="pills-tabContent">
                        <div class="tab-pane fade show active" id="pills-tabs" role="tabpanel">
                            <div id="tabulatorList" class="tab-checkbox-list border rounded-3 p-1">
                                <!-- Tabulators will be loaded here -->
                            </div>
                        </div>
                        <div class="tab-pane fade" id="pills-orgs" role="tabpanel">
                            <div id="organizerList" class="tab-checkbox-list border rounded-3 p-1">
                                <!-- Organizers will be loaded here -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4">
                    <button type="button" class="btn btn-primary w-100 rounded-pill py-2 fw-bold" data-bs-dismiss="modal">Save & Close</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            let competitions = [];
            let tabulators = [];
            let assignments = {};
            let activeCompId = null;

            function loadData() {
                $.post('endpoints/tabulator_handler.php', { action: 'get_data' }, function(res) {
                    if (res.status === 'success') {
                        competitions = res.competitions;
                        tabulators = res.tabulators;
                        assignments = res.assignments;
                        organizers = res.organizers;
                        organizerAssignments = res.organizer_assignments;
                        renderGrid();
                    } else {
                        console.error('Data loading failed:', res.message);
                    }
                }).fail(function() {
                    $('#competitionGrid').html('<div class="col-12 text-center py-5 text-danger"><h5>Error connecting to server</h5></div>');
                });
            }

            function renderGrid(filter = '') {
                let html = '';
                const filteredComps = competitions.filter(c => c.title.toLowerCase().includes(filter.toLowerCase()));
                
                if (filteredComps.length === 0) {
                    $('#competitionGrid').html('<div class="col-12 text-center py-5"><h5>No matching competitions found</h5></div>');
                    return;
                }

                filteredComps.forEach(comp => {
                    const assignedIds = assignments[comp.id] || [];
                    const assignedList = tabulators
                        .filter(t => assignedIds.includes(parseInt(t.id)))
                        .map(t => `<span class="badge bg-light-primary text-primary tabulator-badge">${t.first_name} ${t.last_name}</span>`)
                        .join('');

                    const orgIds = organizerAssignments[comp.id] || [];
                    const orgList = organizers
                        .filter(o => orgIds.includes(parseInt(o.id)))
                        .map(o => `<span class="badge bg-light-warning text-warning tabulator-badge">${o.first_name} ${o.last_name}</span>`) // Different color for organizers
                        .join('');

                    const badgeCount = assignedIds.length > 0 ? 
                        `<span class="badge bg-primary rounded-circle ms-auto">${assignedIds.length}</span>` : '';

                    html += `
                        <div class="col-md-6 col-lg-4 col-xl-3 mb-4 comp-item">
                            <div class="card h-100 competition-card shadow-sm rounded-4" data-id="${comp.id}" data-title="${comp.title}">
                                <div class="card-body p-4 d-flex flex-column">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="card-icon-container bg-primary-subtle text-primary me-3">
                                            <iconify-icon icon="solar:medal-ribbon-bold" class="fs-7"></iconify-icon>
                                        </div>
                                        <div class="overflow-hidden">
                                            <h6 class="fw-bolder mb-1 text-truncate">${comp.title}</h6>
                                            <span class="text-muted small">Competition ID: #${comp.id}</span>
                                        </div>
                                        ${badgeCount}
                                    </div>
                                    <div class="mt-auto">
                                        <div class="mb-3">
                                            <label class="d-block text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.65rem;">Chairman / Organizer</label>
                                            <div class="d-flex flex-wrap gap-1" style="min-height: 1.2rem;">
                                                ${orgList || '<span class="text-muted opacity-40 small">Not assigned</span>'}
                                            </div>
                                        </div>
                                        <div>
                                            <label class="d-block text-muted small fw-bold text-uppercase mb-1" style="letter-spacing: 0.5px; font-size: 0.65rem;">Tabulation Team</label>
                                            <div id="assigned-list-${comp.id}" class="d-flex flex-wrap gap-1" style="min-height: 1.2rem;">
                                                ${assignedList || '<span class="text-muted opacity-40 small">Not assigned</span>'}
                                            </div>
                                        </div>
                                        <button class="btn btn-primary btn-sm w-100 mt-3 rounded-pill manage-btn" data-id="${comp.id}" data-title="${comp.title}">
                                            Change Assignments
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });
                $('#competitionGrid').html(html);
            }

            // Search filtering
            $('#compSearch').on('keyup', function() {
                renderGrid($(this).val());
            });

            $('#tabSearch').on('keyup', function() {
                renderTabulatorList($(this).val());
            });

            $(document).on('click', '.manage-btn, .competition-card', function(e) {
                const target = $(this).hasClass('manage-btn') ? $(this) : $(this);
                activeCompId = target.data('id');
                $('#modalCompTitle').text(target.data('title'));
                
                $('#tabSearch').val('');
                renderTabulatorList();
                renderOrganizerList();
                $('#assignmentModal').modal('show');
                e.stopPropagation();
            });

            function renderTabulatorList(filter = '') {
                const assignedIds = assignments[activeCompId] || [];
                let html = '';
                const filteredTabs = tabulators.filter(t => 
                    t.username.toLowerCase().includes(filter.toLowerCase()) || 
                    t.first_name.toLowerCase().includes(filter.toLowerCase()) || 
                    t.last_name.toLowerCase().includes(filter.toLowerCase())
                );

                filteredTabs.forEach(tab => {
                    const isChecked = assignedIds.includes(parseInt(tab.id)) ? 'checked' : '';
                    html += `
                        <div class="form-check form-switch p-3 border-bottom d-flex align-items-center justify-content-between tab-row">
                            <div class="d-flex align-items-center">
                                <div class="bg-light p-2 rounded-circle me-3">
                                    <iconify-icon icon="solar:user-bold" class="text-secondary fs-5"></iconify-icon>
                                </div>
                                <div>
                                    <label class="form-check-label fw-bold d-block mb-0 cursor-pointer" for="tab-${tab.id}">
                                        ${tab.first_name} ${tab.last_name}
                                    </label>
                                    <span class="text-muted small">@${tab.username}</span>
                                </div>
                            </div>
                            <input class="form-check-input ms-0 tab-toggle" type="checkbox" value="${tab.id}" id="tab-${tab.id}" ${isChecked} style="width: 2.5rem; height: 1.25rem; cursor: pointer;">
                        </div>
                    `;
                });
                $('#tabulatorList').html(html || '<div class="p-4 text-center text-muted">No tabulators found</div>');
            }

            function renderOrganizerList(filter = '') {
                const assignedIds = organizerAssignments[activeCompId] || [];
                let html = '';
                const filteredOrgs = organizers.filter(o => 
                    o.username.toLowerCase().includes(filter.toLowerCase()) || 
                    o.first_name.toLowerCase().includes(filter.toLowerCase()) || 
                    o.last_name.toLowerCase().includes(filter.toLowerCase())
                );

                filteredOrgs.forEach(org => {
                    const isChecked = assignedIds.includes(parseInt(org.id)) ? 'checked' : '';
                    html += `
                        <div class="form-check form-switch p-3 border-bottom d-flex align-items-center justify-content-between tab-row">
                            <div class="d-flex align-items-center">
                                <div class="bg-light-warning p-2 rounded-circle me-3">
                                    <iconify-icon icon="solar:crown-bold" class="text-warning fs-5"></iconify-icon>
                                </div>
                                <div>
                                    <label class="form-check-label fw-bold d-block mb-0 cursor-pointer" for="org-${org.id}">
                                        ${org.first_name} ${org.last_name}
                                    </label>
                                    <span class="text-muted small">@${org.username}</span>
                                </div>
                            </div>
                            <input class="form-check-input ms-0 org-toggle" type="checkbox" value="${org.id}" id="org-${org.id}" ${isChecked} style="width: 2.5rem; height: 1.25rem; cursor: pointer;">
                        </div>
                    `;
                });
                $('#organizerList').html(html || '<div class="p-4 text-center text-muted">No organizers found</div>');
            }

            $(document).on('change', '.tab-toggle, .org-toggle', function() {
                const type = $(this).hasClass('org-toggle') ? 'organizer' : 'tabulator';
                const userId = parseInt($(this).val());
                const assigned = $(this).is(':checked');
                const btn = $(this);
                
                btn.prop('disabled', true);
                
                $.post('endpoints/tabulator_handler.php', {
                    action: 'toggle_assignment',
                    competition_id: activeCompId,
                    user_id: userId,
                    assigned: assigned,
                    type: type
                }, function(res) {
                    btn.prop('disabled', false);
                    if (res.status === 'success') {
                        const targetAssignments = (type === 'organizer') ? organizerAssignments : assignments;
                        
                        if (!targetAssignments[activeCompId]) targetAssignments[activeCompId] = [];
                        if (assigned) {
                            if (!targetAssignments[activeCompId].includes(userId)) {
                                targetAssignments[activeCompId].push(userId);
                            }
                        } else {
                            targetAssignments[activeCompId] = targetAssignments[activeCompId].filter(id => id !== userId);
                        }
                        renderGrid($('#compSearch').val());
                    } else {
                        Swal.fire('Error', res.message, 'error');
                        btn.prop('checked', !assigned); // Revert
                    }
                }).fail(function() {
                    btn.prop('disabled', false);
                    Swal.fire('Error', 'Connection failed', 'error');
                    btn.prop('checked', !assigned);
                });
            });

            loadData();
        });
    </script>
</body>
</html>
