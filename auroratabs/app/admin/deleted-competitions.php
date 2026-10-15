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
    if (!has_permission('Competitions', 'delete', $conn)) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Deleted Competitions - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Deleted Competitions</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Deleted Competitions</li>
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
                     <button class="btn btn-outline-danger" id="emptyTrashBtn"><i class="ti ti-trash me-2"></i>Empty Trash</button>
                   </div>
                   
                    <div class="table-responsive">
                     <table id="deletedCompetitionsTable" class="table table-hover align-middle">
                       <thead class="bg-light">
                         <tr>
                           <th>Competition</th>
                           <th>Created</th>
                           <th>Deleted</th>
                           <th>By</th>
                           <th class="text-end">Action</th>
                         </tr>
                       </thead>
                        <tbody id="deletedContainer">
                          <!-- Dynamic Content -->
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
       const table = $('#deletedCompetitionsTable').DataTable({
         responsive: true,
         dom: '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' + 'rt' + '<"d-flex justify-content-between align-items-center mt-4"ip>',
       });

       loadDeleted();

       function loadDeleted() {
         $.post('endpoints/competitions_handler.php', { action: 'list_deleted' }, function(response) {
           if (response.status === 'success') {
             table.clear().draw();
             response.data.forEach(comp => {
               const row = [
                 `
                   <div class="d-flex align-items-center">
                     <div class="me-3">
                       <span class="round-40 text-bg-light-danger d-flex align-items-center justify-content-center rounded-circle">
                         <i class="ti ti-trash fs-6 text-danger"></i>
                       </span>
                     </div>
                     <div>
                       <h6 class="mb-0 fw-semibold">${comp.title}</h6>
                       <p class="fs-2 mb-0 text-muted">${comp.type}</p>
                     </div>
                   </div>
                 `,
                 comp.created_at,
                 comp.deleted_at,
                 comp.deleted_by_name || 'System',
                 `
                   <div class="d-flex justify-content-end gap-2">
                     <button class="btn btn-sm btn-light-success text-success restore-comp" data-id="${comp.id}" title="Restore">
                       <i class="ti ti-refresh me-1"></i>Restore
                     </button>
                     <button class="btn btn-sm btn-light-danger text-danger purge-comp" data-id="${comp.id}" title="Permanently Delete">
                       <i class="ti ti-trash"></i>
                     </button>
                   </div>
                 `
               ];
               table.row.add(row).draw(false);
             });
           }
         });
       }

       $(document).on('click', '.restore-comp', function() {
         const id = $(this).data('id');
         Swal.fire({
           title: 'Restore Competition?',
           text: "This will move the competition back to active list.",
           icon: 'info',
           showCancelButton: true,
           confirmButtonText: 'Yes, restore it!'
         }).then((result) => {
           if (result.isConfirmed) {
             $.post('endpoints/competitions_handler.php', { action: 'restore', id: id }, function(response) {
               if (response.status === 'success') {
                 Swal.fire('Restored!', response.message, 'success');
                 loadDeleted();
               }
             });
           }
         });
       });

       $(document).on('click', '.purge-comp', function() {
         const id = $(this).data('id');
         Swal.fire({
           title: 'Are you absolutely sure?',
           text: "This action is PERMANENT and cannot be undone!",
           icon: 'error',
           showCancelButton: true,
           confirmButtonColor: '#d33',
           confirmButtonText: 'Yes, PURGE it!'
         }).then((result) => {
           if (result.isConfirmed) {
             $.post('endpoints/competitions_handler.php', { action: 'permanent_delete', id: id }, function(response) {
               if (response.status === 'success') {
                 Swal.fire('Purged!', response.message, 'success');
                 loadDeleted();
               }
             });
           }
         });
       });

       $('#emptyTrashBtn').on('click', function() {
          Swal.fire({
           title: 'Empty Trash Bin?',
           text: "ALL deleted competitions will be permanently lost!",
           icon: 'warning',
           showCancelButton: true,
           confirmButtonColor: '#d33',
           confirmButtonText: 'Yes, Empty All'
         }).then((result) => {
           if (result.isConfirmed) {
             $.post('endpoints/competitions_handler.php', { action: 'empty_trash' }, function(response) {
               if (response.status === 'success') {
                 Swal.fire('Emptied!', response.message, 'success');
                 loadDeleted();
               }
             });
           }
         });
       });

     });
   </script>
</body>
</html>
