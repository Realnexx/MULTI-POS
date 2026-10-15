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
  <title>Notifications - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
    .notify-item {
      transition: all 0.3s ease;
      border-left: 4px solid transparent;
    }
    .notify-item:hover {
      background-color: #f8f9fa;
    }
    .notify-unread {
      background-color: #f0f5ff;
      border-left-color: #5d87ff;
    }
    .notify-icon {
      width: 45px;
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }
  </style>
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
                  <h4 class="fw-semibold mb-8">System Notifications</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Notifications</li>
                    </ol>
                  </nav>
                </div>
                <div class="col-3 text-center">
                  <div class="mb-n5">
                    <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" width="100" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                <h5 class="card-title fw-semibold mb-0">Inbox</h5>
                <div class="d-flex gap-2">
                  <button class="btn btn-light-primary text-primary btn-sm fw-semibold">
                    <i class="ti ti-checks me-1"></i> Mark All as Read
                  </button>
                  <button class="btn btn-light-danger text-danger btn-sm fw-semibold">
                    <i class="ti ti-trash me-1"></i> Clear All
                  </button>
                </div>
              </div>

              <div class="table-responsive">
                <table class="table align-middle table-hover table-responsive-stack" id="notificationsTable">
                  <thead class="bg-light">
                    <tr>
                      <th class="border-0">Alert</th>
                      <th class="border-0">Details</th>
                      <th class="border-0">Category</th>
                      <th class="border-0">Time</th>
                      <th class="border-0 text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Notification Item 1 -->
                    <tr class="notify-item notify-unread">
                      <td data-label="Alert">
                        <div class="notify-icon bg-light-primary text-primary rounded-circle">
                          <i class="ti ti-crown fs-6"></i>
                        </div>
                      </td>
                      <td data-label="Details">
                        <h6 class="mb-1 fw-bold">New Subscription: Pro Plan</h6>
                        <p class="mb-0 text-muted small">Matahom sa 2026 has upgraded to a Pro Plan. Payment #PAY-9921 confirmed.</p>
                      </td>
                      <td data-label="Category"><span class="badge bg-light-primary text-primary">Payments</span></td>
                      <td data-label="Time"><span class="text-muted small">Just now</span></td>
                      <td data-label="Actions" class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                          <a href="transactions.php" class="btn btn-sm btn-primary py-1 px-3 fs-2">View</a>
                          <button class="btn btn-sm btn-light py-1 px-2 fs-2" title="Dismiss"><i class="ti ti-x"></i></button>
                        </div>
                      </td>
                    </tr>

                    <!-- Notification Item 2 -->
                    <tr class="notify-item notify-unread">
                      <td data-label="Alert">
                        <div class="notify-icon bg-light-success text-success rounded-circle">
                          <i class="ti ti-user-plus fs-6"></i>
                        </div>
                      </td>
                      <td data-label="Details">
                        <h6 class="mb-1 fw-bold">New Judge Application</h6>
                        <p class="mb-0 text-muted small">A new judge application is pending review for the "Bohol Queen" category.</p>
                      </td>
                      <td data-label="Category"><span class="badge bg-light-success text-success">Management</span></td>
                      <td data-label="Time"><span class="text-muted small">3 mins ago</span></td>
                      <td data-label="Actions" class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                          <a href="judges-list.php" class="btn btn-sm btn-success py-1 px-3 fs-2">Approve</a>
                          <button class="btn btn-sm btn-light py-1 px-2 fs-2" title="Dismiss"><i class="ti ti-x"></i></button>
                        </div>
                      </td>
                    </tr>

                    <!-- Notification Item 3 -->
                    <tr class="notify-item">
                      <td data-label="Alert">
                        <div class="notify-icon bg-light-warning text-warning rounded-circle">
                          <i class="ti ti-alert-triangle fs-6"></i>
                        </div>
                      </td>
                      <td data-label="Details">
                        <h6 class="mb-1 fw-bold">System Maintenance Scheduled</h6>
                        <p class="mb-0 text-muted small">Scheduled maintenance on Jan 18, 2026 at 02:00 AM. 15 mins downtime.</p>
                      </td>
                      <td data-label="Category"><span class="badge bg-light-warning text-warning">System</span></td>
                      <td data-label="Time"><span class="text-muted small">2 hours ago</span></td>
                      <td data-label="Actions" class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                          <button class="btn btn-sm btn-light-warning text-warning py-1 px-2 fs-2">Details</button>
                        </div>
                      </td>
                    </tr>

                    <!-- Notification Item 4 -->
                    <tr class="notify-item">
                      <td data-label="Alert">
                        <div class="notify-icon bg-light-info text-info rounded-circle">
                          <i class="ti ti-ticket fs-6"></i>
                        </div>
                      </td>
                      <td data-label="Details">
                        <h6 class="mb-1 fw-bold">Large Vote Bundle Purchase</h6>
                        <p class="mb-0 text-muted small">User 'marvin_k' purchased 5,000 votes for Candidate #5.</p>
                      </td>
                      <td data-label="Category"><span class="badge bg-light-info text-info">Voting</span></td>
                      <td data-label="Time"><span class="text-muted small">Yesterday</span></td>
                      <td data-label="Actions" class="text-end">
                        <div class="d-flex gap-2 justify-content-end">
                          <a href="vote-purchases.php" class="btn btn-sm btn-info py-1 px-3 fs-2">Verify</a>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
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
      $('#notificationsTable').DataTable({
        "order": [[ 3, "asc" ]],
        "language": {
          "search": "Filter Notifications:",
          "paginate": {
            "next": '<i class="ti ti-chevron-right"></i>',
            "previous": '<i class="ti ti-chevron-left"></i>'
          }
        },
        "dom": '<"d-flex justify-content-between align-items-center mb-3"f>rt<"d-flex justify-content-between align-items-center mt-3"ip>'
      });
    });
  </script>
</body>

</html>
