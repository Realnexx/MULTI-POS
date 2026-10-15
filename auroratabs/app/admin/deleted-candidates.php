<?php
    session_start();
    
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    // Permission check
    if (!has_permission('Deleted Candidates', 'delete', $conn)) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Deleted Candidates - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Deleted Candidates</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Deleted Candidates</li>
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
                     <h5 class="mb-0">Trash Bin</h5>
                     <button id="emptyTrashBtn" class="btn btn-outline-danger"><i class="ti ti-trash me-2"></i>Empty Trash</button>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="deletedCandidatesTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Candidate</th>
                          <th>Former Competition</th>
                          <th>Date Deleted</th>
                          <th>Deleted By</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="deletedCandidatesTableBody">
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
      const deletedCandidatesTable = $('#deletedCandidatesTable').DataTable({
        "responsive": true,
        "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
               'rt' +
               '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search trash..."
        }
      });

      function loadDeletedCandidates() {
          $.post('endpoints/candidates_handler.php', { action: 'list_deleted' }, function(response) {
              if (response.status === 'success') {
                  deletedCandidatesTable.clear();
                  response.data.forEach(cand => {
                      const candidateCell = `
                        <div class="d-flex align-items-center">
                          <img src="${cand.photo_url || 'src/assets/images/profile/user-default.jpg'}" width="35" class="rounded-circle me-2" alt="" />
                          <div>
                            <h6 class="mb-0 fw-semibold">${cand.first_name} ${cand.last_name}</h6>
                            <p class="fs-2 mb-0 text-muted">ID: CAND-${cand.id.toString().padStart(3, '0')}</p>
                          </div>
                        </div>`;
                      
                      const deleter = cand.deleter_fname ? `${cand.deleter_fname} ${cand.deleter_lname}` : 'System';
                      const dateDeleted = cand.deleted_at ? new Date(cand.deleted_at).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) : '--';

                      const actionCell = `
                        <div class="d-flex justify-content-end gap-2">
                          <button class="btn btn-sm btn-success text-white restore-cand" data-id="${cand.id}" title="Restore"><i class="ti ti-refresh"></i> Restore</button>
                          <button class="btn btn-sm btn-danger text-white purge-cand" data-id="${cand.id}" title="Delete Permanently"><i class="ti ti-trash-x"></i></button>
                        </div>`;

                      deletedCandidatesTable.row.add([
                          candidateCell,
                          cand.competition_title || '<span class="text-muted">No Competition</span>',
                          dateDeleted,
                          deleter,
                          actionCell
                      ]);

                      const lastRow = deletedCandidatesTable.row(':last').node();
                      $(lastRow).find('td').eq(0).attr('data-label', 'Candidate');
                      $(lastRow).find('td').eq(1).attr('data-label', 'Former Competition');
                      $(lastRow).find('td').eq(2).attr('data-label', 'Date Deleted');
                      $(lastRow).find('td').eq(3).attr('data-label', 'Deleted By');
                      $(lastRow).find('td').eq(4).attr('data-label', 'Action');
                  });
                  deletedCandidatesTable.draw();
              }
          });
      }

      loadDeletedCandidates();

      $(document).on('click', '.restore-cand', function() {
          const id = $(this).data('id');
          $.post('endpoints/candidates_handler.php', { action: 'restore', id: id }, function(response) {
              if (response.status === 'success') {
                  Swal.fire('Restored', response.message, 'success');
                  loadDeletedCandidates();
              } else {
                  Swal.fire('Error', response.message, 'error');
              }
          });
      });

      $(document).on('click', '.purge-cand', function() {
          const id = $(this).data('id');
          Swal.fire({
              title: 'Purge Candidate?',
              text: "This action is permanent and cannot be undone!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Yes, purge it!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('endpoints/candidates_handler.php', { action: 'permanent_delete', id: id }, function(response) {
                      if (response.status === 'success') {
                          Swal.fire('Purged!', response.message, 'success');
                          loadDeletedCandidates();
                      } else {
                          Swal.fire('Error', response.message, 'error');
                      }
                  });
              }
          });
      });

      $('#emptyTrashBtn').on('click', function() {
          Swal.fire({
              title: 'Empty Trash?',
              text: "All candidates in trash will be permanently deleted!",
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#d33',
              cancelButtonColor: '#3085d6',
              confirmButtonText: 'Yes, empty it!'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('endpoints/candidates_handler.php', { action: 'empty_trash' }, function(response) {
                      if (response.status === 'success') {
                          Swal.fire('Emptied!', response.message, 'success');
                          loadDeletedCandidates();
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
