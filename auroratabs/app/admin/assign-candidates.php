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
  <title>Assign Candidates - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Assign Candidates</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Assign Candidates</li>
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
            <div class="col-md-5">
              <div class="card">
                <div class="card-body">
                  <h5 class="mb-4">Assign Selection</h5>
                  <form id="assignmentForm">
                    <div class="mb-3">
                      <label for="selectCompetition" class="form-label">Target Competition</label>
                      <select class="form-select" id="selectCompetition" name="competition_id" required>
                        <option value="" selected disabled>Choose competition...</option>
                        <!-- Loaded via AJAX -->
                      </select>
                    </div>

                    <div class="mb-4">
                      <label class="form-label d-flex justify-content-between">
                        <span>Select Candidates</span>
                        <small class="text-primary cursor-pointer" id="selectAll">Select All</small>
                      </label>
                      <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;" id="candidateList">
                        <!-- Loaded via AJAX -->
                      </div>
                      <div class="form-text">Choose the candidates you want to move to the selected competition.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="bulkAssignBtn">Assign Selected Candidates</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-md-7">
               <div class="card">
                <div class="card-body">
                   <div class="d-flex justify-content-between align-items-center mb-3">
                     <h5 class="mb-0">Current Assignments</h5>
                     <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterCompBtn" data-bs-toggle="dropdown" aria-expanded="false">
                          All Competitions
                        </button>
                        <ul class="dropdown-menu" id="filterCompMenu" aria-labelledby="filterCompBtn">
                          <li><a class="dropdown-item filter-comp" href="#" data-id="0" data-title="All Competitions">All</a></li>
                          <!-- Loaded via AJAX -->
                        </ul>
                      </div>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="assignmentsTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Candidate</th>
                          <th>Competition</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="assignmentsTableBody">
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
  <?php include 'components/scripts.php'; ?>
  <script>
    $(document).ready(function() {
      let currentFilterComp = 0;
      const assignmentsTable = $('#assignmentsTable').DataTable({
        "responsive": true,
        "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
               'rt' +
               '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search assigned..."
        }
      });

      function loadCompetitions() {
          $.post('endpoints/assignment_handler.php', { action: 'list_competitions' }, function(response) {
              if (response.status === 'success') {
                  let options = '<option value="" selected disabled>Choose competition...</option>';
                  let filterHtml = '<li><a class="dropdown-item filter-comp" href="#" data-id="0" data-title="All Competitions">All</a></li>';
                  
                  response.data.forEach(comp => {
                      options += `<option value="${comp.id}">${comp.title}</option>`;
                      filterHtml += `<li><a class="dropdown-item filter-comp" href="#" data-id="${comp.id}" data-title="${comp.title}">${comp.title}</a></li>`;
                  });
                  
                  $('#selectCompetition').html(options);
                  $('#filterCompMenu').html(filterHtml);
              }
          });
      }

      function loadCandidates() {
          $.post('endpoints/assignment_handler.php', { action: 'list_all_candidates' }, function(response) {
              if (response.status === 'success') {
                  // Load Checkbox List
                  let listHtml = '';
                  response.data.forEach(cand => {
                      listHtml += `
                        <div class="form-check mb-2">
                          <input class="form-check-input cand-check" type="checkbox" value="${cand.id}" id="cand_${cand.id}">
                          <label class="form-check-label d-flex align-items-center" for="cand_${cand.id}">
                            <img src="${cand.photo_url || 'src/assets/images/profile/user-default.jpg'}" width="30" class="rounded-circle me-2" alt="">
                            <div>
                                <span class="fw-semibold">${cand.first_name} ${cand.last_name}</span>
                                <br><small class="text-muted">${cand.competition_title || 'Unassigned'}</small>
                            </div>
                          </label>
                        </div>`;
                  });
                  $('#candidateList').html(listHtml);

                  // Load Table
                  assignmentsTable.clear();
                  response.data.forEach(cand => {
                      if (currentFilterComp != 0 && cand.competition_id != currentFilterComp) return;

                      const candidateCell = `
                        <div class="d-flex align-items-center">
                          <img src="${cand.photo_url || 'src/assets/images/profile/user-default.jpg'}" width="35" class="rounded-circle me-2" alt="" />
                          <h6 class="mb-0 fw-semibold">${cand.first_name} ${cand.last_name}</h6>
                        </div>`;
                      
                      const actionCell = `
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-sm btn-light-danger text-danger unassign-btn" data-id="${cand.id}" title="Unassign"><i class="ti ti-x"></i></button>
                        </div>`;

                      assignmentsTable.row.add([
                          candidateCell,
                          cand.competition_title || '<span class="text-muted">Unassigned</span>',
                          actionCell
                      ]);

                      const lastRow = assignmentsTable.row(':last').node();
                      $(lastRow).find('td').eq(0).attr('data-label', 'Candidate');
                      $(lastRow).find('td').eq(1).attr('data-label', 'Competition');
                      $(lastRow).find('td').eq(2).attr('data-label', 'Action');
                  });
                  assignmentsTable.draw();
              }
          });
      }

      loadCompetitions();
      loadCandidates();

      // Select All Toggle
      $('#selectAll').on('click', function() {
          const checkboxes = $('.cand-check');
          const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
          checkboxes.prop('checked', !allChecked);
          $(this).text(allChecked ? 'Select All' : 'Deselect All');
      });

      // Bulk Assign
      $('#assignmentForm').on('submit', function(e) {
          e.preventDefault();
          const selected = [];
          $('.cand-check:checked').each(function() {
              selected.push($(this).val());
          });
          
          const compId = $('#selectCompetition').val();

          if (selected.length === 0) {
              Swal.fire('Error', 'Please select at least one candidate.', 'error');
              return;
          }

          if (!compId) {
              Swal.fire('Error', 'Please select a target competition.', 'error');
              return;
          }

          $.post('endpoints/assignment_handler.php', { 
              action: 'assign_bulk', 
              candidate_ids: selected,
              competition_id: compId 
          }, function(response) {
              if (response.status === 'success') {
                  Swal.fire('Success', response.message, 'success');
                  loadCandidates();
                  $('#selectAll').text('Select All');
              } else {
                  Swal.fire('Error', response.message, 'error');
              }
          });
      });

      // Filter
      $(document).on('click', '.filter-comp', function(e) {
          e.preventDefault();
          currentFilterComp = $(this).data('id');
          $('#filterCompBtn').text($(this).data('title'));
          loadCandidates();
      });

      // Unassign
      $(document).on('click', '.unassign-btn', function() {
          const id = $(this).data('id');
          Swal.fire({
              title: 'Unassign Candidate?',
              text: "This will move the candidate to the default competition.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, unassign'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('endpoints/assignment_handler.php', { action: 'unassign', id: id }, function(response) {
                      if (response.status === 'success') {
                          Swal.fire('Success', response.message, 'success');
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
