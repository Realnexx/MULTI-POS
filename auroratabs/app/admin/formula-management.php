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
  <title>Formula Management - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Formula Management</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Formula Management</li>
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
                   <div class="d-block d-md-flex justify-content-between align-items-center mb-4">
                     <h5 class="mb-2 mb-md-0">Formula List</h5>
                     <div class="text-end">
                        <a href="formula-builder.php" class="btn btn-primary btn-sm me-2">
                            <i class="ti ti-plus me-1"></i>Create Formula
                        </a>
                        <a href="deleted-formulas.php" class="btn btn-outline-danger btn-sm">
                            <i class="ti ti-trash me-1"></i>Deleted Formulas
                        </a>
                     </div>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="formulaTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Formula Name</th>
                          <th>Type</th>
                          <th>Usage</th>
                          <th>Status</th>
                          <th>Last Modified</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="formulaTableBody">
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
      const formulaTable = $('#formulaTable').DataTable({
        "responsive": true,
        "dom": '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search formulas..."
        }
      });

      loadFormulas();

      function loadFormulas() {
          $.post('endpoints/formulas_handler.php', { action: 'list' }, function(response) {
              if (response.status === 'success') {
                  formulaTable.clear();
                  response.data.forEach(formula => {
                      const isActive = formula.is_active == 1;
                      const statusBadge = isActive 
                        ? '<span class="badge bg-light-success text-success">Active</span>' 
                        : '<span class="badge bg-light-danger text-danger">Inactive</span>';
                      
                      const usageInfo = formula.is_public == 1 ? 'Public' : 'Private';

                      formulaTable.row.add([
                          `<div class="d-flex align-items-center">
                                <div class="bg-light-primary text-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="ti ti-math-function fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">${formula.name}</h6>
                                    <p class="fs-2 mb-0 text-muted">ID: ${formula.id}</p>
                                </div>
                            </div>`,
                          `<span class="badge bg-light-info text-info">Custom</span>`,
                          `<span class="text-muted small">${usageInfo}</span>`,
                          statusBadge,
                          `<span class="fs-2 text-muted">${new Date(formula.created_at).toLocaleDateString()}</span>`,
                          `<div class="d-flex justify-content-end gap-2">
                               <a href="formula-builder.php?id=${formula.id}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="ti ti-pencil"></i></a>
                               <button class="btn btn-sm btn-outline-danger delete-formula" data-id="${formula.id}" data-name="${formula.name}" title="Delete"><i class="ti ti-trash"></i></button>
                             </div>`
                      ]);
                  });
                  formulaTable.draw();
              }
          });
      }

      $(document).on('click', '.delete-formula', function() {
          const id = $(this).data('id');
          const name = $(this).data('name');

          Swal.fire({
              title: 'Are you sure?',
              text: `Delete formula "${name}"?`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonText: 'Yes, delete it'
          }).then((result) => {
              if (result.isConfirmed) {
                  $.post('endpoints/formulas_handler.php', { action: 'delete', id: id }, function(response) {
                      if (response.status === 'success') {
                          Swal.fire('Deleted!', response.message, 'success');
                          loadFormulas();
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
