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
  <title>Voting Status - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Voting Status Toggle</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Voting Toggle</li>
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
            
            <div class="col-md-6 col-lg-4">
                <div class="card border-top border-success border-3">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light-success text-success">Active</span>
                             <div class="d-flex gap-3 align-items-center">
                                 <div class="form-check form-switch" data-bs-toggle="tooltip" title="Toggle Voting">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggle1" checked>
                                    <label class="form-check-label fs-2 text-muted" for="toggle1">Voting</label>
                                </div>
                                <div class="form-check form-switch" data-bs-toggle="tooltip" title="Toggle Payments">
                                    <input class="form-check-input" type="checkbox" role="switch" id="togglePay1" checked>
                                    <label class="form-check-label fs-2 text-muted" for="togglePay1">Payment</label>
                                </div>
                            </div>
                        </div>
                        <h5 class="card-title fw-semibold">Matahom sa Ubay 2026</h5>
                        <p class="text-muted mb-4">Finals Night</p>
                        
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0 fw-semibold">1,245</h6>
                                <span class="fs-2 text-muted">Total Votes</span>
                            </div>
                             <button class="btn btn-sm btn-outline-primary">Manage</button>
                        </div>
                    </div>
                </div>
            </div>

             <div class="col-md-6 col-lg-4">
                <div class="card border-top border-danger border-3">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light-danger text-danger">Closed</span>
                             <div class="d-flex gap-3 align-items-center">
                                 <div class="form-check form-switch" data-bs-toggle="tooltip" title="Toggle Voting">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggle2">
                                    <label class="form-check-label fs-2 text-muted" for="toggle2">Voting</label>
                                </div>
                                <div class="form-check form-switch" data-bs-toggle="tooltip" title="Toggle Payments">
                                    <input class="form-check-input" type="checkbox" role="switch" id="togglePay2">
                                    <label class="form-check-label fs-2 text-muted" for="togglePay2">Payment</label>
                                </div>
                            </div>
                        </div>
                        <h5 class="card-title fw-semibold">Regional Science Fair</h5>
                        <p class="text-muted mb-4">Poster Making Category</p>
                        
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0 fw-semibold">328</h6>
                                <span class="fs-2 text-muted">Total Votes</span>
                            </div>
                             <button class="btn btn-sm btn-outline-primary">Manage</button>
                        </div>
                    </div>
                </div>
            </div>

             <div class="col-md-6 col-lg-4">
                <div class="card border-top border-warning border-3">
                    <div class="card-body">
                         <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge bg-light-warning text-warning">Paused</span>
                             <div class="d-flex gap-3 align-items-center">
                                 <div class="form-check form-switch" data-bs-toggle="tooltip" title="Toggle Voting">
                                    <input class="form-check-input" type="checkbox" role="switch" id="toggle3">
                                    <label class="form-check-label fs-2 text-muted" for="toggle3">Voting</label>
                                </div>
                                <div class="form-check form-switch" data-bs-toggle="tooltip" title="Toggle Payments">
                                    <input class="form-check-input" type="checkbox" role="switch" id="togglePay3">
                                    <label class="form-check-label fs-2 text-muted" for="togglePay3">Payment</label>
                                </div>
                            </div>
                        </div>
                        <h5 class="card-title fw-semibold">Matahom sa Ubay 2026</h5>
                        <p class="text-muted mb-4">Talent Portion</p>
                        
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="mb-0 fw-semibold">890</h6>
                                <span class="fs-2 text-muted">Total Votes</span>
                            </div>
                             <button class="btn btn-sm btn-outline-primary">Manage</button>
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
</body>
</html>
