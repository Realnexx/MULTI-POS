<?php
    session_start();
    
    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Permission checks
    $can_view = has_permission('Competitions', 'view', $conn);
    $can_manage = has_permission('Competitions', 'manage', $conn);
    $can_delete = has_permission('Competitions', 'delete', $conn);

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
  <title>All Competitions - Aurora Tabs</title>
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
                <div class="col-12 col-md-9 mb-3 mb-md-0">
                  <div class="mb-3">
                    <h4 class="fw-semibold mb-2">All Competitions</h4>
                    <nav aria-label="breadcrumb">
                      <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item">
                          <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item" aria-current="page">All Competitions</li>
                      </ol>
                    </nav>
                  </div>
                </div>
                <div class="col-12 col-md-3">
                  <div class="text-start text-md-end mb-3">
                    <?php if ($can_delete): ?>
                    <a href="deleted-competitions.php" class="btn btn-outline-danger btn-sm">
                      <i class="ti ti-trash me-1"></i>Deleted Competitions
                    </a>
                    <?php endif; ?>
                  </div>
                  <!-- Rocket image visible only on desktop -->
                  <div class="text-center mb-n5 d-none d-md-block">
                    <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="row" id="competitionsContainer">
            <!-- Add New Competition Card -->
            <?php if ($can_manage): ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="card border-dashed border-2 bg-transparent shadow-none rounded-2 h-100 d-flex align-items-center justify-content-center py-5">
                <div class="text-center p-4">
                  <div class="mb-3">
                    <i class="ti ti-plus fs-10 text-muted"></i>
                  </div>
                  <h6 class="fw-semibold fs-4 mb-3">Add New Competition</h6>
                  <p class="text-muted fs-2 mb-4">Start organizing a new event with custom rankings and voting.</p>
                  <a href="create-competition.php" class="btn btn-primary px-4">Create Now</a>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>

         <?php include 'components/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>
   <!-- Edit Competition Modal -->
   <div class="modal fade" id="editCompetitionModal" tabindex="-1" aria-hidden="true">
     <div class="modal-dialog modal-lg">
       <div class="modal-content">
         <div class="modal-header bg-primary text-white">
           <h5 class="modal-title text-white">Edit Competition</h5>
           <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
         </div>
         <form id="editCompetitionForm">
           <input type="hidden" name="action" value="update">
           <input type="hidden" name="id" id="editCompId">
           <div class="modal-body">
             <div class="row">
               <div class="col-md-6 mb-3">
                 <label class="form-label">Competition Title</label>
                 <input type="text" class="form-control" name="title" id="editCompTitle" required>
               </div>
               <div class="col-md-6 mb-3">
                 <label class="form-label">Category</label>
                 <select class="form-select" name="type" id="editCompType" required>
                    <option value="">Choose category...</option>
                    <optgroup label="Pageantry & Talents">
                        <option value="Beauty Pageant">Beauty Pageant</option>
                        <option value="Talent Show">Talent Show</option>
                        <option value="Dance Competition">Dance Competition</option>
                        <option value="Singing Contest">Singing Contest</option>
                    </optgroup>
                    <optgroup label="Academic & Skill">
                        <option value="Science Fair">Science Fair</option>
                        <option value="Academic Quiz Bee">Academic Quiz Bee</option>
                        <option value="Debate Championship">Debate Championship</option>
                        <option value="Cooking Challenge">Cooking Challenge</option>
                        <option value="Art & Design Expo">Art & Design Expo</option>
                    </optgroup>
                    <optgroup label="Sports & Gaming">
                        <option value="Sports Tournament">Sports Tournament</option>
                        <option value="Basketball League">Basketball League</option>
                        <option value="Chess Tournament">Chess Tournament</option>
                        <option value="Mobile Legends Tournament">Mobile Legends Tournament</option>
                    </optgroup>
                    <optgroup label="Multimedia">
                        <option value="Photography Contest">Photography Contest</option>
                        <option value="Film Festival">Film Festival</option>
                    </optgroup>
                 </select>
               </div>
             </div>
             <div class="mb-3">
               <label class="form-label">Description</label>
               <textarea class="form-control" name="description" id="editCompDesc" rows="2"></textarea>
             </div>
             <div class="row">
               <div class="col-md-3 mb-3">
                 <label class="form-label">Start Date</label>
                 <input type="date" class="form-control" name="start_date" id="editCompStartDate">
               </div>
               <div class="col-md-3 mb-3">
                 <label class="form-label">Start Time</label>
                 <input type="time" class="form-control" name="start_time" id="editCompStartTime">
               </div>
               <div class="col-md-3 mb-3">
                 <label class="form-label">End Date</label>
                 <input type="date" class="form-control" name="end_date" id="editCompEndDate">
               </div>
               <div class="col-md-3 mb-3">
                 <label class="form-label">End Time</label>
                 <input type="time" class="form-control" name="end_time" id="editCompEndTime">
               </div>
             </div>
             <div class="row">
               <div class="col-md-6 mb-3">
                 <label class="form-label">Venue</label>
                 <input type="text" class="form-control" name="venue" id="editCompVenue">
               </div>
               <div class="col-md-6 mb-3">
                 <label class="form-label">Status</label>
                 <select class="form-select" name="status" id="editCompStatus">
                    <option value="upcoming">Upcoming</option>
                    <option value="active">Active</option>
                    <option value="draft">Draft</option>
                    <option value="completed">Completed</option>
                 </select>
               </div>
             </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Lead Admin</label>
                    <select class="form-select" name="lead_admin_id" id="editCompAdmin">
                        <option value="">-- Select Admin --</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Scoring Formula</label>
                    <select class="form-select" name="formula_id" id="editCompFormula">
                        <option value="">-- Select Formula --</option>
                    </select>
                </div>
             </div>
             <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Unlock Limit</label>
                    <input type="number" class="form-control" name="unlock_limit" id="editCompUnlockLimit" min="0">
                </div>
             </div>
             <div class="form-check form-switch mt-2">
                <input class="form-check-input" type="checkbox" name="enable_audience_voting" id="editCompAudienceVoting">
                <label class="form-check-label fw-semibold">Enable Audience Voting</label>
             </div>
           </div>
           <div class="modal-footer border-top-0">
             <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
             <button type="submit" class="btn btn-primary px-4">Save Changes</button>
           </div>
         </form>
       </div>
     </div>
   </div>

    <?php include 'components/scripts.php'; ?>
  <script>
    $(document).ready(function() {
      // Permissions passed from PHP
      const PERMISSIONS = {
        manage: <?php echo $can_manage ? 'true' : 'false'; ?>,
        delete: <?php echo $can_delete ? 'true' : 'false'; ?>
      };

      loadCompetitions();
      loadDependencies();

      function loadDependencies() {
        $.post('endpoints/competitions_handler.php', { action: 'get_dependencies' }, function(response) {
            if (response.status === 'success') {
                const data = response.data;
                const adminSelects = $('#editCompAdmin');
                const formulaSelects = $('#editCompFormula');
                
                adminSelects.empty().append('<option value="">-- Select Admin --</option>');
                data.admins.forEach(admin => {
                    adminSelects.append(`<option value="${admin.id}">${admin.username} (${admin.role_name})</option>`);
                });

                formulaSelects.empty().append('<option value="">-- Select Formula --</option>');
                data.formulas.forEach(form => {
                    formulaSelects.append(`<option value="${form.id}">${form.name}</option>`);
                });
            }
        });
      }

      function loadCompetitions() {
        console.log('Loading competitions...');
        $.post('endpoints/competitions_handler.php', { action: 'list' })
          .done(function(response) {
            console.log('Handler response:', response);
            if (response.status === 'success') {
              const container = $('#competitionsContainer');
              // Clear only existing competition cards, preserve the "Add New" card
              container.find('.competition-card').remove();

              if (response.data.length === 0) {
                 console.log('No competitions found in database.');
              }

              response.data.forEach(comp => {
                const statusBadge = getStatusBadge(comp.status);
                const banner = comp.banner_url || 'src/assets/images/backgrounds/blog-bg-1.jpg';
                
                // Construct Action Buttons based on permissions
                let actionButtons = '';
                
                if (PERMISSIONS.manage) {
                    actionButtons += `
                      <div class="col-sm-4 col-12">
                        <button class="btn btn-outline-primary btn-sm w-100 py-2 edit-comp" data-id="${comp.id}"><i class="ti ti-edit fs-4 me-1"></i>Edit</button>
                      </div>`;
                }
                
                // Result button is always visible (view permission is assumed if we are here)
                actionButtons += `
                      <div class="col-sm-4 col-12">
                        <a href="final-results.php?comp_id=${comp.id}" class="btn btn-primary btn-sm w-100 py-2"><i class="ti ti-chart-bar fs-4 me-1"></i>Results</a>
                      </div>`;

                if (PERMISSIONS.delete) {
                    actionButtons += `
                      <div class="col-sm-4 col-12">
                        <button class="btn btn-outline-danger btn-sm w-100 py-2 delete-comp" data-id="${comp.id}">
                          <i class="ti ti-trash fs-4 me-1"></i>Delete
                        </button>
                      </div>`;
                }

                const card = `
                  <div class="col-md-6 col-lg-4 mb-4 competition-card">
                    <div class="card overflow-hidden rounded-2">
                      <div class="position-relative">
                        <a href="criteria.php?comp_id=${comp.id}" class="d-flex align-items-center justify-content-center bg-light w-100" style="height: 200px;">
                          <img src="${banner}" class="img-fluid" alt="..." style="max-height: 100%; width: auto; object-fit: contain; padding: 15px;">
                        </a>
                        <span class="badge bg-white text-dark fs-2 rounded-4 lh-sm mb-9 me-9 py-1 px-2 fw-semibold position-absolute bottom-0 end-0">${comp.type || 'General'}</span>
                      </div>
                      <div class="card-body pt-3 p-4">
                        <h6 class="fw-semibold fs-4 mb-2 text-truncate" title="${comp.title}">${comp.title}</h6>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                          <div class="d-flex align-items-center gap-2">
                            ${statusBadge}
                          </div>
                          <div class="d-flex align-items-center gap-2 text-muted">
                            <i class="ti ti-calendar fs-4"></i>
                            <span class="fs-2">${comp.start_date || 'No Date'}</span>
                          </div>
                        </div>
                        <div class="mt-3 row g-2 action-row">
                          ${actionButtons}
                        </div>
                      </div>
                    </div>
                  </div>
                `;
                
                // Insert before the "Add New" button card if it exists, otherwise append to container
                if (container.find('.border-dashed').length > 0) {
                    container.find('.border-dashed').closest('.col-md-6').before(card);
                } else {
                    container.append(card);
                }
              });
            } else {
              console.error('Error in response:', response.message);
            }
          })
          .fail(function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Status:', status);
            console.log('Response Text:', xhr.responseText);
          });
      }

      function getStatusBadge(status) {
        switch (status) {
          case 'active': 
            return `<span class="badge bg-light-success text-success fw-bold fs-3 px-3 py-2 rounded-pill d-inline-flex align-items-center shadow-sm border border-success border-opacity-10"><i class="ti ti-activity-heartbeat fs-5 me-1 text-success"></i>Live Now</span>`;
          case 'upcoming': 
            return `<span class="badge bg-light-primary text-primary fw-bold fs-3 px-3 py-2 rounded-pill d-inline-flex align-items-center shadow-sm border border-primary border-opacity-10"><i class="ti ti-calendar-event fs-5 me-1"></i>Upcoming</span>`;
          case 'completed': 
            return `<span class="badge bg-light-dark text-dark fw-bold fs-3 px-3 py-2 rounded-pill d-inline-flex align-items-center shadow-sm border border-dark border-opacity-10"><i class="ti ti-medal fs-5 me-1"></i>Completed</span>`;
          case 'draft': 
            return `<span class="badge bg-light-warning text-warning fw-bold fs-3 px-3 py-2 rounded-pill d-inline-flex align-items-center shadow-sm border border-warning border-opacity-10"><i class="ti ti-file-pencil fs-5 me-1"></i>Draft</span>`;
          default: 
            return `<span class="badge bg-light-info text-info fw-bold fs-3 px-3 py-2 rounded-pill d-inline-flex align-items-center shadow-sm border border-info border-opacity-10">${status}</span>`;
        }
      }

      $(document).on('click', '.edit-comp', function() {
        const id = $(this).data('id');
        $.post('endpoints/competitions_handler.php', { action: 'get', id: id }, function(response) {
          if (response.status === 'success') {
            const comp = response.data;
            $('#editCompId').val(comp.id);
            $('#editCompTitle').val(comp.title);
            $('#editCompType').val(comp.type);
            $('#editCompDesc').val(comp.description);
            $('#editCompStartDate').val(comp.start_date);
            $('#editCompStartTime').val(comp.start_time);
            $('#editCompEndDate').val(comp.end_date);
            $('#editCompEndTime').val(comp.end_time);
            $('#editCompVenue').val(comp.venue);
            $('#editCompStatus').val(comp.status);
            $('#editCompAdmin').val(comp.lead_admin_id);
            $('#editCompFormula').val(comp.formula_id);
            $('#editCompUnlockLimit').val(comp.unlock_limit);
            $('#editCompAudienceVoting').prop('checked', comp.enable_audience_voting == 1);
            
            $('#editCompetitionModal').modal('show');
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        });
      });

      $('#editCompetitionForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.post('endpoints/competitions_handler.php', formData, function(response) {
          if (response.status === 'success') {
            Swal.fire('Updated!', response.message, 'success');
            $('#editCompetitionModal').modal('hide');
            loadCompetitions();
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        });
      });

      $(document).on('click', '.delete-comp', function() {
        const id = $(this).data('id');
        Swal.fire({
          title: 'Are you sure?',
          text: "This will move the competition to trash!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/competitions_handler.php', { action: 'delete', id: id }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Deleted!', 'Competition has been deleted.', 'success');
                loadCompetitions();
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
