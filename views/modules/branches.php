<?php
$title = "Branch Management";
ob_start();
?>

<link rel="stylesheet" href="<?= url('assets/css/branch-management.css') ?>?v=<?= filemtime(__DIR__.'/../../assets/css/branch-management.css') ?>">
<div id="tw-branches" class="branch-workspace">
    <header class="branches-heading">
        <div><h2><span class="material-symbols-outlined">storefront</span> Branch Management</h2><p>Manage your locations, managers and branch teams.</p></div>
        <div class="branches-actions"><a class="btn btn-light" href="<?= url('?route=admin/branches/report') ?>" target="_blank" rel="noopener">Export Audit</a><button class="btn btn-primary" onclick="openCreateModal()">+ Add Branch</button></div>
    </header>
    <div class="branches-stats">
        <div><span>Total Branches</span><strong id="stat-total-branches">--</strong></div>
        <div><span>Active Regions</span><strong id="stat-active-regions">--</strong></div>
        <div><span>Top Performing</span><strong id="stat-top-performing">--</strong></div>
        <div><span>Maintenance / Offline</span><strong id="stat-offline-branches">--</strong></div>
    </div>
    <section class="branches-registry">
        <div class="branches-toolbar"><h4>Location Registry</h4><input type="search" id="branchSearch" class="form-control" aria-label="Search branches" placeholder="Search branches..."></div>
        <div id="branchesContainer"><div class="branches-empty">Loading branches...</div></div>
        <div class="branches-footer"><span id="branchCountFooter" aria-live="polite">Loading branches...</span><nav aria-label="Branch pages"><ul class="pagination" id="branchPagination"></ul></nav></div>
    </section>

    <div class="modal fade branch-modal" id="editBranchModal" tabindex="-1" aria-labelledby="modal_title" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable"><div class="modal-content">
        <div class="modal-header"><div class="branch-modal-heading"><span class="material-symbols-outlined" id="modal_icon_container"><span id="modal_icon">storefront</span></span><div><h2 id="modal_title">Add Branch</h2><p id="modal_subtitle">Set up the location and assign your team.</p></div></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
        <div class="modal-body"><form id="editBranchForm" enctype="multipart/form-data"><input type="hidden" name="branch_id" id="edit_branch_id">
          <div class="branch-modal-grid"><section class="branch-photo-section"><h3>Branch Photo</h3><div id="edit_image_preview"><span class="material-symbols-outlined">add_a_photo</span></div><label for="branch_image" class="form-label">Upload Photo</label><input type="file" id="branch_image" name="image" accept="image/*" class="form-control" onchange="previewImage(this, 'edit_image_preview')"><p id="preview_name_text" class="branch-preview-name">New Branch</p></section>
          <section><h3>Branch Details</h3><div class="branch-fields"><div><label for="edit_branch_name">Branch Name</label><input class="form-control" type="text" name="name" id="edit_branch_name" required placeholder="e.g. Main Branch"></div><div><label for="edit_branch_phone">Phone Number</label><input class="form-control" type="text" name="phone" id="edit_branch_phone" required placeholder="+63 9XX XXX XXXX"></div><div class="branch-field-wide"><label for="edit_branch_address">Address</label><textarea class="form-control" name="address" id="edit_branch_address" required rows="2" placeholder="Complete branch address"></textarea></div><div><label for="edit_branch_status">Status</label><select class="form-select" name="status" id="edit_branch_status"><option>Active</option><option>Maintenance</option><option>Offline</option></select></div><div><label for="edit_branch_manager_id">Manager</label><select class="form-select" name="manager_id" id="edit_branch_manager_id"><option value="">Unassigned</option></select></div></div></section></div>
          <section class="branch-staff-section"><h3>Branch Team</h3><p>Assign available staff to this location.</p><div id="edit_branch_staff_container" class="custom-scrollbar"></div></section>
        </form></div><div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button><button type="submit" form="editBranchForm" id="updateBranchBtn" class="btn btn-primary"><span id="btn_text">Save Branch</span><span class="material-symbols-outlined" id="btn_icon">check</span></button></div>
      </div></div>
    </div>

</div>


<script src="<?= url('assets/js/branch-overview.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/branch-overview.js') ?>"></script>
<script src="<?= url('assets/js/branch-manager-picker.js') ?>?v=<?= filemtime(__DIR__.'/../../assets/js/branch-manager-picker.js') ?>"></script>
<script>
    $(document).ready(function() {
        new BranchOverviewModal(document.getElementById('tw-branches'), <?= json_encode(url('?route=ajax/branch_action')) ?>);
        const managerPicker = new BranchManagerPicker(document.getElementById('edit_branch_manager_id'));
        loadBranches();
        loadPotentialManagers();

        function loadBranches() {
            $.ajax({
                url: "<?= url('?route=ajax/branch_action&action=list') ?>",
                method: 'GET',
                success: function(response) {
                    if(response.success) {
                        renderBranches(response.data);
                        updateStats(response.stats);
                    }
                },
                error: function(xhr, status, error) {
                    $("#branchesContainer").html('<div class="col-span-full p-8 text-center text-red-400">Connection error: ' + error + ' (' + xhr.status + ')</div>');
                }
            });
        }

        function updateStats(stats) {
            if(!stats) return;
            $("#stat-total-branches").text(stats.total.toString().padStart(2, '0'));
            $("#stat-offline-branches").text(stats.offline.toString().padStart(2, '0'));
            $("#stat-top-performing").text(stats.top_performing);
            $("#stat-active-regions").text(stats.regions.toString().padStart(2, '0'));
        }

        function loadPotentialManagers() {
            $.getJSON("<?= url('?route=ajax/branch_action&action=get_managers') ?>", function(response) {
                if (response.success) {
                    managerPicker.setManagers(response.data);
                }
            });
        }

        let branchRecords = [], branchPage = 1;
        const branchPageSize = 6;
        function escapeBranch(value) {
            return $('<div>').text(value == null ? '' : String(value)).html().replace(/"/g, '&quot;').replace(/'/g, '&#39;');
        }
        function renderBranches(branches) {
            branchRecords = branches || [];
            drawBranchPage();
        }
        function drawBranchPage() {
            const query = $('#branchSearch').val().toLowerCase().trim();
            const records = branchRecords.filter(b => [b.name, b.address, b.phone, b.manager_name, b.status].join(' ').toLowerCase().includes(query));
            const pages = Math.max(1, Math.ceil(records.length / branchPageSize));
            branchPage = Math.min(branchPage, pages);
            const start = (branchPage - 1) * branchPageSize;
            $('#branchesContainer').html(records.slice(start, start + branchPageSize).map(branch => `
                <article class="branch-card">
                    <div class="branch-cover"><img src="${escapeBranch(branch.full_image_url)}" alt="${escapeBranch(branch.name)}" loading="lazy" decoding="async"></div>
                    <div class="branch-details">
                        <div class="branch-card-meta"><small>BRANCH #${escapeBranch(String(branch.id).padStart(3, '0'))}</small><span class="branch-status ${branch.status === 'Active' ? 'is-active' : ''}"><span aria-hidden="true"></span>${escapeBranch(branch.status)}</span></div>
                        <h5>${escapeBranch(branch.name)}</h5>
                        <div class="branch-manager"><span class="material-symbols-outlined" aria-hidden="true">person</span><div><span>Branch manager</span><strong>${escapeBranch(branch.manager_name || 'Unassigned')}</strong></div></div>
                        <div class="branch-contact"><span class="material-symbols-outlined" aria-hidden="true">location_on</span><p>${escapeBranch(branch.address || 'No address provided')}</p></div>
                        <div class="branch-contact"><span class="material-symbols-outlined" aria-hidden="true">call</span><p>${escapeBranch(branch.phone || 'No phone provided')}</p></div>
                        <div class="branch-card-actions"><button type="button" class="btn branch-view" data-id="${Number(branch.id)}" aria-label="View ${escapeBranch(branch.name)}"><span class="material-symbols-outlined" aria-hidden="true">visibility</span>View</button><button type="button" class="btn branch-delete" data-id="${Number(branch.id)}" aria-label="Delete ${escapeBranch(branch.name)}"><span class="material-symbols-outlined" aria-hidden="true">delete</span>Delete</button><button type="button" class="btn btn-primary branch-edit" data-id="${Number(branch.id)}" aria-label="Configure ${escapeBranch(branch.name)}"><span class="material-symbols-outlined" aria-hidden="true">tune</span>Configure</button></div>
                    </div>
                </article>`).join('') || '<div class="branches-empty">No branches match your search.</div>');
            $('#branchCountFooter').text(records.length ? `Showing ${start + 1}–${Math.min(start + branchPageSize, records.length)} of ${records.length} branches` : '0 branches');
            function pageButton(page, label, disabled, active, aria) {
                return `<li class="page-item ${disabled ? 'disabled' : ''} ${active ? 'active' : ''}"><button type="button" class="page-link" data-page="${page}" ${disabled ? 'disabled' : ''} ${active ? 'aria-current="page"' : ''} aria-label="${aria || 'Page ' + page}">${label}</button></li>`;
            }
            let pagination = pageButton(branchPage - 1, '‹', branchPage === 1, false, 'Previous page');
            const visible = new Set([1, pages, branchPage - 1, branchPage, branchPage + 1].filter(n => n > 0 && n <= pages));
            let previous = 0;
            [...visible].sort((a,b) => a-b).forEach(page => {
                if (previous && page - previous > 1) pagination += '<li class="page-item disabled"><span class="page-link">…</span></li>';
                pagination += pageButton(page, page, false, page === branchPage);
                previous = page;
            });
            pagination += pageButton(branchPage + 1, '›', branchPage === pages, false, 'Next page');
            $('#branchPagination').html(pagination);
        }
        $('#branchPagination').on('click', 'button:not(:disabled)', function() { branchPage = Number(this.dataset.page); drawBranchPage(); });
        $('#branchesContainer').on('click', '.branch-edit', function() { editBranch(Number(this.dataset.id)); });
        $('#branchesContainer').on('click', '.branch-delete', function() {
            const branch = branchRecords.find(b => Number(b.id) === Number(this.dataset.id));
            if (branch) deleteBranch(Number(branch.id), branch.name);
        });

        const branchModalInstance = new bootstrap.Modal(document.getElementById('editBranchModal'));

        window.openCreateModal = function() {
            $("#editBranchForm")[0].reset();
            managerPicker.close();
            managerPicker.setValue('');
            loadPotentialManagers();
            $("#edit_branch_id").val("");
            $("#modal_title").text("Add Branch");
            $("#modal_subtitle").text("Set up the location and assign your team.");
            $("#modal_icon").text("add_location_alt");
            $("#btn_text").text("Create Branch");
            $("#btn_icon").text("check");
            $("#edit_image_preview").html('<div class="text-center group-hover:scale-110 transition-transform duration-500"><span class="material-symbols-outlined text-5xl text-zinc-300 mb-4">add_a_photo</span><p class="text-[10px] font-bold text-zinc-400 uppercase tracking-widest">Choose a branch photo</p></div>');
            $("#preview_name_text").text("New Branch");

            
            // Show modal
            branchModalInstance.show();

            $("#edit_branch_staff_container").html('<p class="branch-staff-empty">Loading staff...</p>');
            // Load potential staff
            $.get("<?= url('?route=ajax/branch_action&action=get_staff') ?>", function(staffRes) {
                if(staffRes.success) {
                    renderStaffSelection(staffRes.potential, []);
                }
            });
        };

        window.editBranch = function(id) {
            $("#editBranchForm")[0].reset();
            managerPicker.close();
            managerPicker.setValue('');
            loadPotentialManagers();
            $("#edit_branch_id").val(id);
            $("#modal_title").text("Edit Branch");
            $("#modal_subtitle").text("Update the location details and assigned team.");
            $("#modal_icon").text("storefront");
            $("#btn_text").text("Save Changes");
            $("#btn_icon").text("check");
            
            $("#edit_branch_staff_container").html('<p class="branch-staff-empty">Loading staff...</p>');
            $("#edit_image_preview").html('<span class="material-symbols-outlined text-4xl text-zinc-300">add_a_photo</span>');
            
            // Show modal
            branchModalInstance.show();

            // Load staff roster
            $.get("<?= url('?route=ajax/branch_action&action=get_staff') ?>&branch_id=" + id, function(staffRes) {
                if(staffRes.success) {
                    const assignedIds = staffRes.assigned.map(u => u.id);
                    renderStaffSelection(staffRes.potential, assignedIds);
                }
            });

            // Load branch data
            $.getJSON("<?= url('?route=ajax/branch_action&action=get&id=') ?>" + id, function(response) {
                if (response.success) {
                    const b = response.data;
                    $("#edit_branch_id").val(b.id);
                    $("#edit_branch_name").val(b.name);
                    $("#edit_branch_address").val(b.address);
                    $("#edit_branch_phone").val(b.phone);
                    $("#edit_branch_status").val(b.status);
                    managerPicker.setValue(b.manager_id || "");
                    $("#preview_name_text").text(b.name);
                    
                    if(b.full_image_url) {
                        $("#edit_image_preview").html(`<img src="${escapeBranch(b.full_image_url)}" class="w-full h-full object-cover">`);
                    }
                }
            });
        };

        function renderStaffSelection(potential, assignedIds) {
            const currentBranchId = $("#edit_branch_id").val();
            const staffHtml = (potential || []).map(user => {
                const assigned = assignedIds.some(id => Number(id) === Number(user.id));
                const elsewhere = user.branch_id && Number(user.branch_id) !== Number(currentBranchId);
                return `<label class="branch-staff-option ${elsewhere ? 'is-unavailable' : ''}"><span class="branch-staff-avatar">${escapeBranch(user.name.charAt(0))}</span><span class="branch-staff-info"><strong>${escapeBranch(user.name)}</strong><small>${escapeBranch(user.role_name || 'Staff')}${user.current_branch_name ? ' · ' + escapeBranch(user.current_branch_name) : ''}</small></span><input class="form-check-input" type="checkbox" name="staff_ids[]" value="${Number(user.id)}" ${assigned ? 'checked' : ''} ${elsewhere ? 'disabled' : ''}></label>`;
            }).join('');
            $("#edit_branch_staff_container").html(staffHtml || '<p class="branch-staff-empty">No available staff accounts found.</p>');
        }

        window.previewImage = function(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $(`#${previewId}`).html(`<img src="${e.target.result}" class="w-full h-full object-cover opacity-0 transition-opacity duration-500" onload="this.classList.remove('opacity-0')">`);
                }
                reader.readAsDataURL(input.files[0]);
            }
        };

        window.closeModal = function(id) {
            branchModalInstance.hide();
        };

        $("#edit_branch_name").on("input", function() {
            $("#preview_name_text").text($(this).val() || "New Branch");
        });



        $("#editBranchForm").on("submit", function(e) {
            e.preventDefault();
            const btn = $("#updateBranchBtn");
            const originalHtml = btn.html();
            
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> SYNCING...');
            
            const formData = new FormData(this);
            const isEdit = $("#edit_branch_id").val() !== "";
            const actionUrl = isEdit ? "<?= url('?route=ajax/branch_action&action=update') ?>" : "<?= url('?route=ajax/branch_action&action=create') ?>";
            
            $.ajax({
                url: actionUrl,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        showToast(response.message, 'success');
                        closeModal('editBranchModal');
                        loadBranches();
                    } else {
                        showToast(response.message, 'error');
                    }
                },
                error: function(xhr) {
                    const message = xhr.responseJSON && xhr.responseJSON.message;
                    const text = (xhr.responseText || '').trim();
                    showToast(message || (text && !text.includes('<') && text.length < 500 ? text : 'Unable to save the branch (HTTP ' + xhr.status + '). Please refresh and try again.'), 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalHtml);
                }
            });
        });

        window.deleteBranch = async function(id, name) {
            let validation;
            try {
                const response = await $.getJSON("<?= url('?route=ajax/branch_action&action=validate_delete') ?>", {id});
                if (!response.success || !response.data) throw new Error('Validation unavailable');
                validation = response.data;
            } catch(error) {
                Swal.fire({icon:'error',title:'Unable to validate branch',text:'Please try again. No branch was deleted.'});
                return;
            }
            if (!validation.allowed) {
                Swal.fire({icon:'info',title:'Branch cannot be deleted',html:'<p>This branch has linked records:</p><ul style="text-align:left">'+validation.reasons.map(reason=>'<li>'+escapeBranch(reason)+'</li>').join('')+'</ul><p>Keep the records and set the branch status to Offline instead.</p>',confirmButtonText:'Understood',customClass:{popup:'branch-confirm-dialog'}});
                return;
            }
            Swal.fire({
                title: 'Delete empty branch?',
                text: `No linked records were found for ${name}. Deletion is permanent and will be validated again before proceeding.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#5d87ff',
                cancelButtonColor: '#191c1d',
                confirmButtonText: 'Delete branch',
                background: '#ffffff',
                customClass: {
                    popup: 'branch-confirm-dialog',
                    title: 'font-headline font-bold text-2xl',
                    confirmButton: 'rounded-full px-8 py-3 font-bold text-xs uppercase tracking-widest',
                    cancelButton: 'rounded-full px-8 py-3 font-bold text-xs uppercase tracking-widest'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post("<?= url('?route=ajax/branch_action&action=delete') ?>", {id: id}, function(response) {
                        if (response.success) {
                            showToast(response.message, 'success');
                            loadBranches();
                        } else {
                            showToast(response.message, 'error');
                        }
                    });
                }
            });
        };

        $("#branchSearch").on("input", function() { branchPage = 1; drawBranchPage(); });
    });
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
?>
