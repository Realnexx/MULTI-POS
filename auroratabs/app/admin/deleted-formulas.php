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
  <title>Deleted Formulas - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Deleted Formulas</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="formula-management.php">Formula Management</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Deleted Formulas</li>
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
                     <h5 class="mb-2 mb-md-0">Trash Bin</h5>
                     <button class="btn btn-sm btn-danger text-white">Empty Trash</button>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="deletedFormulasTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Formula Name</th>
                          <th>Type</th>
                          <th>Date Deleted</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody>
                        <tr>
                          <td data-label="Formula Name">
                            <div class="d-flex align-items-center">
                                <div class="bg-light-secondary text-secondary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="ti ti-math-function fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">Old Ranking V1</h6>
                                    <p class="fs-2 mb-0 text-muted">Deprecated logic</p>
                                </div>
                            </div>
                          </td>
                          <td data-label="Type"><span class="badge bg-light-warning text-warning">Ranking</span></td>
                          <td data-label="Date Deleted">Jan 01, 2026</td>
                          <td data-label="Action">
                             <div class="d-flex justify-content-end gap-2">
                               <button class="btn btn-sm btn-success text-white" title="Restore"><i class="ti ti-refresh"></i> Restore</button>
                               <button class="btn btn-sm btn-danger text-white" title="Delete Permanently"><i class="ti ti-trash-x"></i></button>
                             </div>
                          </td>
                        </tr>
                        <tr>
                          <td data-label="Formula Name">
                             <div class="d-flex align-items-center">
                                <div class="bg-light-secondary text-secondary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="ti ti-sum fs-6"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-semibold">Test Summation</h6>
                                    <p class="fs-2 mb-0 text-muted">Draft</p>
                                </div>
                            </div>
                          </td>
                          <td data-label="Type"><span class="badge bg-light-info text-info">Sum</span></td>
                          <td data-label="Date Deleted">Dec 28, 2025</td>
                          <td data-label="Action">
                             <div class="d-flex justify-content-end gap-2">
                               <button class="btn btn-sm btn-success text-white" title="Restore"><i class="ti ti-refresh"></i> Restore</button>
                               <button class="btn btn-sm btn-danger text-white" title="Delete Permanently"><i class="ti ti-trash-x"></i></button>
                             </div>
                          </td>
                        </tr>
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
      $('#deletedFormulasTable').DataTable({
        "responsive": true,
        "dom": '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search deleted..."
        }
      });
    });
  </script>
</body>
</html>
