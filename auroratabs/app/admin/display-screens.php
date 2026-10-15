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
  <title>Display Screens - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">External Displays</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Display Screens</li>
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
            <!-- Screen Card: Live Results -->
            <div class="col-md-6 col-lg-4">
                <div class="card hover-img position-relative overflow-hidden border-top border-4 border-primary">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                             <span class="round-50 d-inline-flex align-items-center justify-content-center rounded-circle bg-light-primary text-primary">
                                <i class="ti ti-trophy fs-6"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-2">Live Results</h5>
                        <p class="mb-4 text-muted fs-3">Real-time podium and rankings for the audience.</p>
                        
                         <div class="d-grid gap-2">
                             <a href="live-results.php" target="_blank" class="btn btn-primary"><i class="ti ti-external-link me-2"></i>Launch Screen</a>
                             <button class="btn btn-outline-secondary" onclick="copyLink('live-results.php')"><i class="ti ti-copy me-2"></i>Copy Link</button>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Screen Card: Voting Status -->
            <div class="col-md-6 col-lg-4">
                <div class="card hover-img position-relative overflow-hidden border-top border-4 border-warning">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                             <span class="round-50 d-inline-flex align-items-center justify-content-center rounded-circle bg-light-warning text-warning">
                                <i class="ti ti-chart-bar fs-6"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-2">Voting Dashboard</h5>
                        <p class="mb-4 text-muted fs-3">Public view of current voting stats and status.</p>
                        
                         <div class="d-grid gap-2">
                             <a href="voting-toggle.php" target="_blank" class="btn btn-warning"><i class="ti ti-external-link me-2"></i>Launch Screen</a>
                              <button class="btn btn-outline-secondary" onclick="copyLink('voting-toggle.php')"><i class="ti ti-copy me-2"></i>Copy Link</button>
                         </div>
                    </div>
                </div>
            </div>

            <!-- Screen Card: Judge Status -->
             <div class="col-md-6 col-lg-4">
                <div class="card hover-img position-relative overflow-hidden border-top border-4 border-success">
                    <div class="card-body p-4 text-center">
                        <div class="mb-4">
                             <span class="round-50 d-inline-flex align-items-center justify-content-center rounded-circle bg-light-success text-success">
                                <i class="ti ti-users fs-6"></i>
                            </span>
                        </div>
                        <h5 class="fw-semibold mb-2">Judge Monitor</h5>
                        <p class="mb-4 text-muted fs-3">See who has submitted scores in real-time.</p>
                        
                         <div class="d-grid gap-2">
                             <a href="judge-assignments.php" target="_blank" class="btn btn-success"><i class="ti ti-external-link me-2"></i>Launch Screen</a>
                              <button class="btn btn-outline-secondary" onclick="copyLink('judge-assignments.php')"><i class="ti ti-copy me-2"></i>Copy Link</button>
                         </div>
                    </div>
                </div>
            </div>
          </div>
          
          <div class="card mt-4 bg-light-info">
              <div class="card-body">
                  <h5 class="card-title fw-semibold mb-3"><i class="ti ti-device-projector me-2"></i>Usage Instructions</h5>
                  <ul class="mb-0 text-muted">
                      <li class="mb-2">Click <strong>"Launch Screen"</strong> to open the view in a new browser tab.</li>
                      <li class="mb-2">Drag the new tab to your projector or extended display.</li>
                      <li>Press <strong>F11</strong> (or click the Full Screen button on the page) to maximize the view.</li>
                  </ul>
              </div>
          </div>

          <?php include 'components/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>
  <?php include 'components/scripts.php'; ?>
  <script>
      function copyLink(url) {
          const fullUrl = window.location.origin + window.location.pathname.replace('display-screens.php', '') + url;
          navigator.clipboard.writeText(fullUrl).then(() => {
              alert('Link copied to clipboard: ' + fullUrl);
          });
      }
  </script>
</body>
</html>
