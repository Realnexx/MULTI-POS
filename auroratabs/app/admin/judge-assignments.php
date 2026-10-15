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
  <title>Judge Assignments - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Judge Assignments</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Judge Assignments</li>
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
                  <h5 class="mb-4">Assign Judges</h5>
                  <form id="judgeAssignmentForm">
                    <div class="mb-3">
                      <label for="selectCompetition" class="form-label">Target Competition</label>
                      <select class="form-select" id="selectCompetition" name="competition_id" required>
                        <option value="" selected disabled>Choose competition...</option>
                        <!-- Loaded via AJAX -->
                      </select>
                    </div>

                    <div class="mb-3">
                      <label for="selectRole" class="form-label">Assignment Role</label>
                      <select class="form-select" id="selectRole" name="role" required>
                        <option value="member">Member</option>
                        <option value="chairman">Chairman</option>
                      </select>
                    </div>

                    <div class="mb-4">
                      <label class="form-label d-flex justify-content-between">
                        <span>Select Judges</span>
                        <small class="text-primary cursor-pointer" id="selectAllJudges">Select All</small>
                      </label>
                      <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;" id="judgeList">
                         <!-- Loaded via AJAX -->
                      </div>
                      <div class="form-text">Choose the judges you want to add to the selected competition.</div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100" id="assignJudgesBtn">Assign Selected Judges</button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-md-7">
               <div class="card">
                <div class="card-body">
                   <div class="d-block d-md-flex justify-content-between align-items-center mb-3">
                     <h5 class="mb-2 mb-md-0">Current Assignments</h5>
                     <div class="dropdown">
                        <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100" type="button" id="filterCompBtn" data-bs-toggle="dropdown" aria-expanded="false">
                          All Competitions
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" id="filterCompMenu" aria-labelledby="filterCompBtn">
                           <li><a class="dropdown-item filter-comp" href="#" data-id="0" data-title="All Competitions">All</a></li>
                           <!-- Loaded via AJAX -->
                        </ul>
                      </div>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="judgeAssignmentsTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Judge</th>
                          <th>Competition</th>
                          <th>Role</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="judgeAssignmentsTableBody">
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
      const judgeAssignmentsTable = $('#judgeAssignmentsTable').DataTable({
        "responsive": true,
        "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
               'rt' +
               '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search assignments..."
        }
      });

      function loadCompetitions() {
          $.post('endpoints/judge_assignments_handler.php', { action: 'list_competitions' }, function(response) {
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

      function loadJudges() {
          $.post('endpoints/judge_assignments_handler.php', { action: 'list_judges' }, function(response) {
              if (response.status === 'success') {
                  let listHtml = '';
                  response.data.forEach(judge => {
                      listHtml += `
                        <div class="form-check mb-2">
                          <input class="form-check-input judge-check" type="checkbox" value="${judge.id}" id="judge_${judge.id}">
                          <label class="form-check-label d-flex align-items-center" for="judge_${judge.id}">
                            <img src="${judge.avatar_url || 'src/assets/images/profile/user-default.jpg'}" width="30" class="rounded-circle me-2" alt="">
                            <div>
                                <span class="d-block lh-1">${judge.first_name} ${judge.last_name}</span>
                            </div>
                          </label>
                        </div>`;
                  });
                  $('#judgeList').html(listHtml);
              }
          });
      }

      function loadAssignments() {
          $.post('endpoints/judge_assignments_handler.php', { action: 'list_assignments' }, function(response) {
              if (response.status === 'success') {
                  judgeAssignmentsTable.clear();
                  response.data.forEach(ja => {
                      if (currentFilterComp != 0 && ja.competition_id != currentFilterComp) return;

                      const judgeCell = `
                        <div class="d-flex align-items-center">
                          <img src="${ja.avatar_url || 'src/assets/images/profile/user-default.jpg'}" width="35" class="rounded-circle me-2" alt="" />
                          <h6 class="mb-0 fw-semibold">${ja.first_name} ${ja.last_name}</h6>
                        </div>`;
                      
                      const roleBadge = ja.role === 'chairman' ? 
                        '<span class="badge bg-light-primary text-primary">Chairman</span>' : 
                        '<span class="badge bg-light-info text-info">Member</span>';

                      const actionCell = `
                        <div class="d-flex justify-content-end">
                            <button class="btn btn-sm btn-light-danger text-danger unassign-btn" data-id="${ja.id}" title="Unassign"><i class="ti ti-x"></i></button>
                        </div>`;

                      judgeAssignmentsTable.row.add([
                          judgeCell,
                          ja.competition_title,
                          roleBadge,
                          actionCell
                      ]);

                      const lastRow = judgeAssignmentsTable.row(':last').node();
                      $(lastRow).find('td').eq(0).attr('data-label', 'Judge');
                      $(lastRow).find('td').eq(1).attr('data-label', 'Competition');
                      $(lastRow).find('td').eq(2).attr('data-label', 'Role');
                      $(lastRow).find('td').eq(3).attr('data-label', 'Action');
                  });
                  judgeAssignmentsTable.draw();
              }
          });
      }

      loadCompetitions();
      loadJudges();
      loadAssignments();

      // Select All Toggle
      $('#selectAllJudges').on('click', function() {
          const checkboxes = $('.judge-check');
          const allChecked = checkboxes.length === checkboxes.filter(':checked').length;
          checkboxes.prop('checked', !allChecked);
          $(this).text(allChecked ? 'Select All' : 'Deselect All');
      });

      // Bulk Assign
      $('#judgeAssignmentForm').on('submit', function(e) {
          e.preventDefault();
          const selected = [];
          $('.judge-check:checked').each(function() {
              selected.push($(this).val());
          });
          
          const compId = $('#selectCompetition').val();
          const role = $('#selectRole').val();

          if (selected.length === 0) {
              Swal.fire('Error', 'Please select at least one judge.', 'error');
              return;
          }

          if (!compId) {
              Swal.fire('Error', 'Please select a target competition.', 'error');
              return;
          }

          $.post('endpoints/judge_assignments_handler.php', { 
              action: 'assign_bulk', 
              judge_ids: selected,
              competition_id: compId,
              role: role 
          }, function(response) {
              if (response.status === 'success') {
                  Swal.fire('Success', response.message, 'success');
                  loadAssignments();
                  $('.judge-check').prop('checked', false);
                  $('#selectAllJudges').text('Select All');
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
          loadAssignments();
      });

      // Unassign
      $(document).on('click', '.unassign-btn', function() {
          const id = $(this).data('id');
          Swal.fire({
              title: 'Unassign Judge?',
              text: "This judge will be removed from this competition.",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, unassign'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('endpoints/judge_assignments_handler.php', { action: 'unassign', id: id }, function(response) {
                      if (response.status === 'success') {
                          Swal.fire('Success', response.message, 'success');
                          loadAssignments();
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
