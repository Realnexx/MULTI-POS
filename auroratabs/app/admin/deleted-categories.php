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
    if (!has_permission('Categories', 'delete', $conn)) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Deleted Categories - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Deleted Categories</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Deleted Categories</li>
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
                     <button id="emptyTrash" class="btn btn-outline-danger"><i class="ti ti-trash me-2"></i>Empty Trash</button>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="deletedCategoriesTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Category Name</th>
                          <th>Competition</th>
                          <th>Type</th>
                          <th>Date Deleted</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="deletedCategoriesTableBody">
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
      let deletedCategoriesTable = $('#deletedCategoriesTable').DataTable({
        "responsive": true,
        "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search trash..."
        }
      });

      function loadDeletedCategories() {
        $.post('endpoints/categories_handler.php', { action: 'list_deleted' }, function(response) {
          if (response.status === 'success') {
            deletedCategoriesTable.clear();
            response.data.forEach(function(cat) {
              let typeBadge = '';
              if (cat.type === 'major') typeBadge = '<span class="badge bg-light-primary text-primary">Major</span>';
              else if (cat.type === 'minor') typeBadge = '<span class="badge bg-light-info text-info">Minor</span>';
              else typeBadge = '<span class="badge bg-light-warning text-warning">Special</span>';

              deletedCategoriesTable.row.add([
                `<span class="fw-semibold">${cat.name}</span>`,
                cat.competition_title,
                typeBadge,
                cat.deleted_at,
                `<div class="btn-group">
                   <button class="btn btn-sm btn-success text-white restore-cat" data-id="${cat.id}" title="Restore"><i class="ti ti-refresh"></i> Restore</button>
                   <button class="btn btn-sm btn-danger text-white purge-cat" data-id="${cat.id}" title="Delete Permanently"><i class="ti ti-trash-x"></i></button>
                 </div>`
              ]);

              // Add data-labels for mobile row style
              const lastRow = deletedCategoriesTable.row(':last').node();
              $(lastRow).find('td').eq(0).attr('data-label', 'Category Name');
              $(lastRow).find('td').eq(1).attr('data-label', 'Competition');
              $(lastRow).find('td').eq(2).attr('data-label', 'Type');
              $(lastRow).find('td').eq(3).attr('data-label', 'Date Deleted');
              $(lastRow).find('td').eq(4).attr('data-label', 'Action');
            });
            deletedCategoriesTable.draw();
          }
        });
      }

      loadDeletedCategories();

      // Restore Category
      $(document).on('click', '.restore-cat', function() {
        let id = $(this).data('id');
        $.post('endpoints/categories_handler.php', { action: 'restore', id: id }, function(response) {
          if (response.status === 'success') {
            Swal.fire('Restored', response.message, 'success');
            loadDeletedCategories();
          } else {
            Swal.fire('Error', response.message, 'error');
          }
        });
      });

      // Purge Category (Permanent Delete)
      $(document).on('click', '.purge-cat', function() {
        let id = $(this).data('id');
        Swal.fire({
          title: 'Are you sure?',
          text: "This action cannot be undone!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, purge it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/categories_handler.php', { action: 'permanent_delete', id: id }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Purged', response.message, 'success');
                loadDeletedCategories();
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            });
          }
        });
      });

      // Empty Trash
      $('#emptyTrash').on('click', function() {
        Swal.fire({
          title: 'Empty Trash?',
          text: "All categories in the trash will be permanently deleted!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, empty it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/categories_handler.php', { action: 'empty_trash' }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Emptied', response.message, 'success');
                loadDeletedCategories();
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
