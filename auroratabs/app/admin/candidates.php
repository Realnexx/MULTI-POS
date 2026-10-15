<?php
    session_start();
    
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    // Permission checks
    $can_view = has_permission('Candidates', 'view', $conn);
    $can_manage = has_permission('Candidates', 'manage', $conn);
    $can_delete = has_permission('Candidates', 'delete', $conn);

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
  <title>All Candidates - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">All Candidates</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Candidates</li>
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
            <div class="col-12">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Existing Candidates</h5>
                    <div class="test-end">
                      <?php if ($can_delete): ?>
                      <a href="deleted-candidates.php" class="btn btn-outline-danger me-2">
                          <i class="ti ti-trash me-2"></i>Deleted Candidates
                      </a>
                      <?php endif; ?>
                      <?php if ($can_manage): ?>
                      <button class="btn btn-primary" id="addCandidateBtn">
                        <i class="ti ti-plus me-2"></i>Add New Candidate
                      </button>
                      <?php endif; ?>
                    </div>
                  </div>
                  
                  <div class="table-responsive">
                    <table id="candidatesTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Candidate</th>
                          <th>Competition</th>
                          <th>Dept / College</th>
                          <th>Number</th>
                          <th>Gender</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="candidatesTableBody">
                        <!-- Loaded via AJAX -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <?php include 'components/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Candidate Modal -->
  <div class="modal fade" id="candidateModal" tabindex="-1" aria-labelledby="candidateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="candidateModalLabel">Add New Candidate</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="candidateForm">
            <input type="hidden" id="candId">
            <!-- Personal Information -->
            <h6 class="fw-semibold mb-3 text-primary">Personal Information</h6>
            <div class="row">
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="firstName" name="first_name" required>
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="middleName" class="form-label">Middle Name</label>
                  <input type="text" class="form-control" id="middleName" name="middle_name">
                </div>
              </div>
              <div class="col-md-4">
                <div class="mb-3">
                  <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="lastName" name="last_name" required>
                </div>
              </div>
            </div>

            <div class="row">
               <div class="col-md-4">
                <div class="mb-3">
                  <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                  <select class="form-select" id="gender" name="gender" required>
                    <option value="" selected disabled>Select Gender</option>
                    <option value="female">Female</option>
                    <option value="male">Male</option>
                    <option value="other">Other</option>
                  </select>
                </div>
              </div>
               <div class="col-md-4">
                <div class="mb-3">
                  <label for="age" class="form-label">Age <span class="text-danger">*</span></label>
                  <input type="number" class="form-control" id="age" name="age" required>
                </div>
              </div>
               <div class="col-md-4">
                <div class="mb-3">
                  <label for="height" class="form-label">Height <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="height" name="height" placeholder="e.g. 5'8&quot;" required>
                </div>
              </div>
            </div>
             <div class="mb-3">
              <label for="hometown" class="form-label">Hometown</label>
              <input type="text" class="form-control" id="hometown" name="hometown" placeholder="e.g. Los Angeles, CA">
            </div>

            <!-- Competition Details -->
            <hr class="my-4">
            <h6 class="fw-semibold mb-3 text-primary">Competition Details</h6>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                      <label for="competition" class="form-label">Competition <span class="text-danger">*</span></label>
                       <select class="form-select" id="competition" name="competition_id" required>
                        <option value="" selected disabled>Select Competition</option>
                        <!-- Loaded via AJAX -->
                      </select>
                    </div>
                </div>
                 <div class="col-md-6">
                    <div class="mb-3">
                      <label for="candidateNumber" class="form-label">Candidate Number <span class="text-danger">*</span></label>
                      <input type="number" class="form-control" id="candidateNumber" name="candidate_number" placeholder="e.g. 15" required>
                    </div>
                </div>
            </div>
            <div class="mb-3">
              <label for="department" class="form-label">Department / College <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="department" name="department" placeholder="e.g. College of Technology" required>
            </div>

             <!-- Media & Biography -->
            <hr class="my-4">
            <h6 class="fw-semibold mb-3 text-primary">Media & Biography</h6>
            <div class="mb-3">
              <label for="photoUpload" class="form-label">Candidate Photo</label>
              <input type="hidden" id="existingPhotoUrl" name="existing_photo_url">
              <input class="form-control" type="file" id="photoUpload" name="photo_file" accept="image/*">
              <div class="form-text">Choose an image file to upload (JPEG, PNG, SVG).</div>
              <div class="mt-2" id="imagePreviewContainer" style="display: none;">
                  <img id="imagePreview" src="#" alt="Image Preview" class="img-thumbnail" style="max-height: 200px;">
              </div>
            </div>

            <div class="mb-3">
              <label for="biography" class="form-label">Biography</label>
              <textarea class="form-control" id="biography" name="biography" rows="4" placeholder="Candidate biography..."></textarea>
            </div>

          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-primary" id="saveCandidateBtn">Save Candidate</button>
        </div>
      </div>
    </div>
  </div>

  <?php include 'components/scripts.php'; ?>
  <script>
    // Permissions passed from PHP
    const PERMISSIONS = {
        manage: <?php echo $can_manage ? 'true' : 'false'; ?>,
        delete: <?php echo $can_delete ? 'true' : 'false'; ?>
    };

    $(document).ready(function() {
      const candidatesTable = $('#candidatesTable').DataTable({
        "responsive": true,
        "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
               'rt' +
               '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search candidates..."
        }
      });

      function loadCompetitions() {
          $.post('endpoints/candidates_handler.php', { action: 'list_competitions' }, function(response) {
              if (response.status === 'success') {
                  let options = '<option value="" selected disabled>Select Competition</option>';
                  response.data.forEach(comp => {
                      options += `<option value="${comp.id}">${comp.title}</option>`;
                  });
                  $('#competition').html(options);
              }
          });
      }

      function loadCandidates() {
          $.post('endpoints/candidates_handler.php', { action: 'list' }, function(response) {
              if (response.status === 'success') {
                  candidatesTable.clear();
                  response.data.forEach(cand => {
                      const candidateImage = cand.photo_url ? '../' + cand.photo_url : 'src/assets/images/profile/user-default.jpg';
                      const candidateCell = `
                        <div class="d-flex align-items-center">
                          <div class="me-3">
                            <img src="${candidateImage}" style="width: 40px; height: 40px; object-fit: cover;" class="rounded-circle" alt="Candidate" />
                          </div>
                          <div>
                            <h6 class="mb-1 fw-semibold">${cand.first_name} ${cand.last_name}</h6>
                            <p class="fs-2 mb-0 text-muted">ID: CAND-${cand.id.toString().padStart(3, '0')}</p>
                          </div>
                        </div>`;
                      
                      let actionCell = '<div class="btn-group">';
                      
                      if (PERMISSIONS.manage) {
                          actionCell += `<button class="btn btn-sm btn-outline-primary edit-cand" data-cand='${JSON.stringify(cand)}'><i class="ti ti-edit"></i></button>`;
                      }
                      
                      if (PERMISSIONS.delete) {
                          actionCell += `<button class="btn btn-sm btn-outline-danger delete-cand" data-id="${cand.id}"><i class="ti ti-trash"></i></button>`;
                      }
                      
                      actionCell += '</div>';

                      candidatesTable.row.add([
                          candidateCell,
                          cand.competition_title || '<span class="text-muted">No Competition</span>',
                          cand.department,
                          `<span class="badge bg-light-primary text-primary rounded-circle">${cand.candidate_number.toString().padStart(2, '0')}</span>`,
                          cand.gender.charAt(0).toUpperCase() + cand.gender.slice(1),
                          actionCell
                      ]);

                      const lastRow = candidatesTable.row(':last').node();
                      $(lastRow).find('td').eq(0).attr('data-label', 'Candidate');
                      $(lastRow).find('td').eq(1).attr('data-label', 'Competition');
                      $(lastRow).find('td').eq(2).attr('data-label', 'Dept / College');
                      $(lastRow).find('td').eq(3).attr('data-label', 'Number');
                      $(lastRow).find('td').eq(4).attr('data-label', 'Gender');
                      $(lastRow).find('td').eq(5).attr('data-label', 'Action');
                  });
                  candidatesTable.draw();
              }
          });
      }

      loadCandidates();
      loadCompetitions();

      // Modal Handling
      $('#addCandidateBtn').on('click', function() {
          $('#candidateForm')[0].reset();
          $('#candId').val('');
          $('#candidateNumber').val(''); // Clear on add
          $('#existingPhotoUrl').val('');
          $('#photoUpload').val('');
          $('#imagePreviewContainer').hide();
          $('#imagePreview').attr('src', '#');
          $('#candidateModalLabel').text('Add New Candidate');
          $('#candidateModal').modal('show');
      });

      // Photo Upload Preview
      $('#photoUpload').on('change', function() {
          const file = this.files[0];
          if (file) {
              const reader = new FileReader();
              reader.onload = function(e) {
                  $('#imagePreview').attr('src', e.target.result);
                  $('#imagePreviewContainer').show();
              }
              reader.readAsDataURL(file);
          } else {
              const existing = $('#existingPhotoUrl').val();
              if (existing) {
                  const previewPath = existing.startsWith('uploads') ? '../' + existing : existing;
                  $('#imagePreview').attr('src', previewPath);
                  $('#imagePreviewContainer').show();
              } else {
                  $('#imagePreviewContainer').hide();
              }
          }
      });

      // Auto-populate candidate number
      function autoPopulateNumber() {
          const competitionId = $('#competition').val();
          const gender = $('#gender').val();
          
          // Only auto-populate if we are adding a new candidate (no ID) and both are selected
          if (!$('#candId').val() && competitionId && gender) {
              $.post('endpoints/candidates_handler.php', {
                  action: 'get_next_number',
                  competition_id: competitionId,
                  gender: gender
              }, function(response) {
                  if (response.status === 'success') {
                      $('#candidateNumber').val(response.data.next_number);
                  }
              });
          }
      }

      $('#competition, #gender').on('change', autoPopulateNumber);

      $(document).on('click', '.edit-cand', function() {
          const cand = $(this).data('cand');
          $('#candId').val(cand.id);
          $('#firstName').val(cand.first_name);
          $('#middleName').val(cand.middle_name);
          $('#lastName').val(cand.last_name);
          $('#gender').val(cand.gender);
          $('#age').val(cand.age);
          $('#height').val(cand.height);
          $('#hometown').val(cand.hometown);
          $('#competition').val(cand.competition_id);
          $('#candidateNumber').val(cand.candidate_number);
          $('#department').val(cand.department);
          $('#existingPhotoUrl').val(cand.photo_url || '');
          $('#photoUpload').val('');
          
          if (cand.photo_url) {
              const previewPath = cand.photo_url.startsWith('uploads') ? '../' + cand.photo_url : cand.photo_url;
              $('#imagePreview').attr('src', previewPath);
              $('#imagePreviewContainer').show();
          } else {
              $('#imagePreviewContainer').hide();
          }
          
          $('#biography').val(cand.biography);
          
          $('#candidateModalLabel').text('Edit Candidate');
          $('#candidateModal').modal('show');
      });

      $('#saveCandidateBtn').on('click', function() {
          const id = $('#candId').val();
          const action = id ? 'update' : 'create';
          
          const formData = new FormData();
          formData.append('action', action);
          if (id) formData.append('id', id);
          formData.append('first_name', $('#firstName').val());
          formData.append('middle_name', $('#middleName').val());
          formData.append('last_name', $('#lastName').val());
          formData.append('gender', $('#gender').val());
          formData.append('age', $('#age').val());
          formData.append('height', $('#height').val());
          formData.append('hometown', $('#hometown').val());
          formData.append('competition_id', $('#competition').val());
          formData.append('candidate_number', $('#candidateNumber').val());
          formData.append('department', $('#department').val());
          formData.append('biography', $('#biography').val());
          formData.append('existing_photo_url', $('#existingPhotoUrl').val() || '');
          
          const photoFile = $('#photoUpload')[0].files[0];
          if (photoFile) {
              formData.append('photo_file', photoFile);
          }

          // Frontend Validation
          if (!formData.get('first_name') || !formData.get('last_name') || !formData.get('gender') || !formData.get('age') || !formData.get('height') || !formData.get('competition_id') || !formData.get('candidate_number') || !formData.get('department')) {
              Swal.fire('Warning', 'Please fill in all required fields marked with an asterisk (*).', 'warning');
              return;
          }

          $.ajax({
              url: 'endpoints/candidates_handler.php',
              type: 'POST',
              data: formData,
              processData: false,
              contentType: false,
              success: function(response) {
                  if (response.status === 'success') {
                      Swal.fire('Success', response.message, 'success');
                      $('#candidateModal').modal('hide');
                      loadCandidates();
                  } else {
                      Swal.fire('Error', response.message, 'error');
                  }
              },
              error: function() {
                  Swal.fire('Error', 'An unexpected error occurred while saving.', 'error');
              }
          });
      });

      $(document).on('click', '.delete-cand', function() {
          const id = $(this).data('id');
          Swal.fire({
              title: 'Are you sure?',
              text: "Move this candidate to trash?",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Yes, delete it!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('endpoints/candidates_handler.php', { action: 'delete', id: id }, function(response) {
                      if (response.status === 'success') {
                          Swal.fire('Deleted!', response.message, 'success');
                          loadCandidates();
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
