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
  <title>Deleted Judges - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Deleted Judges</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Deleted Judges</li>
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
                     <button class="btn btn-outline-danger"><i class="ti ti-trash me-2"></i>Empty Trash</button>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="deletedJudgesTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Judge</th>
                          <th>Role/Title</th>
                          <th>Access Code</th>
                          <th>Date Deleted</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody>
                        <tr>
                          <td data-label="Judge">
                            <div class="d-flex align-items-center">
                              <img src="src/assets/images/profile/user5.jpg" width="35" class="rounded-circle me-2" alt="" />
                              <div>
                                <h6 class="mb-0 fw-semibold">Simoun Ibarra</h6>
                                <p class="fs-2 mb-0 text-muted">simoun@ex.com</p>
                              </div>
                            </div>
                          </td>
                          <td data-label="Role/Title">Guest Judge</td>
                          <td data-label="Access Code">JDG-9912</td>
                          <td data-label="Date Deleted">Jan 10, 2026</td>
                          <td data-label="Action">
                             <div class="d-flex justify-content-end gap-2">
                               <button class="btn btn-sm btn-success text-white" title="Restore"><i class="ti ti-refresh"></i> Restore</button>
                               <button class="btn btn-sm btn-danger text-white" title="Delete Permanently"><i class="ti ti-trash-x"></i></button>
                             </div>
                          </td>
                        </tr>
                        <tr>
                          <td data-label="Judge">
                            <div class="d-flex align-items-center">
                              <img src="src/assets/images/profile/user6.jpg" width="35" class="rounded-circle me-2" alt="" />
                              <div>
                                <h6 class="mb-0 fw-semibold">Dona Victorina</h6>
                                <p class="fs-2 mb-0 text-muted">N/A</p>
                              </div>
                            </div>
                          </td>
                          <td data-label="Role/Title">Tabulator</td>
                          <td data-label="Access Code">TAB-001</td>
                          <td data-label="Date Deleted">Jan 12, 2026</td>
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
      $('#deletedJudgesTable').DataTable({
        "responsive": true,
        "dom": '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
      });
    });
  </script>
</body>
</html>
