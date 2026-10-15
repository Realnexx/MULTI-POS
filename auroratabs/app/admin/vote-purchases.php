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
    <title>Vote Purchases - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .revenue-card {
            border-left: 4px solid #5d87ff;
        }
        .revenue-card.pending { border-left-color: #ffae1f; }
        .revenue-card.success { border-left-color: #13deb9; }
        .revenue-card.failed { border-left-color: #fa896b; }
        .trans-id { font-family: 'Courier New', Courier, monospace; font-weight: 600; color: #5d87ff; }
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

                    <div class="top-part card bg-light-success shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Vote Purchase Ledger</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Vote Purchases</li>
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

                    <!-- Revenue Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card revenue-card success shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Total Revenue</h6>
                                    <h3 class="fw-bold mb-0">₱45,250.00</h3>
                                    <span class="text-success small fw-semibold"><i class="ti ti-arrow-up-right"></i> +12% from last week</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card revenue-card pending shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Pending Verification</h6>
                                    <h3 class="fw-bold mb-0">₱2,400.00</h3>
                                    <span class="text-warning small fw-semibold">15 transactions</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card revenue-card shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Total Votes Sold</h6>
                                    <h3 class="fw-bold mb-0">12,500</h3>
                                    <span class="text-primary small fw-semibold">Avg ₱3.62 per vote</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card revenue-card failed shadow-sm">
                                <div class="card-body">
                                    <h6 class="text-muted mb-2">Failed/Refunded</h6>
                                    <h3 class="fw-bold mb-0">₱850.00</h3>
                                    <span class="text-danger small fw-semibold">5 transactions</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-semibold">Transaction History</h5>
                                <div class="btn-group">
                                    <button class="btn btn-outline-primary btn-sm"><i class="ti ti-file-export me-1"></i> Export Excel</button>
                                    <button class="btn btn-primary btn-sm"><i class="ti ti-plus me-1"></i> Manual Purchase</button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table align-middle table-hover table-responsive-stack" id="purchasesTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th>Trans ID</th>
                                            <th>Date & Time</th>
                                            <th>Voter</th>
                                            <th>Candidate</th>
                                            <th>Votes</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td data-label="Trans ID"><span class="trans-id">#TXN-88219</span></td>
                                            <td data-label="Date & Time">2026-05-20 09:15 AM</td>
                                            <td data-label="Voter">Juan Dela Cruz</td>
                                            <td data-label="Candidate">Jane Doe (#04)</td>
                                            <td data-label="Votes"><span class="fw-bold">50</span></td>
                                            <td data-label="Amount">₱500.00</td>
                                            <td data-label="Method"><span class="badge bg-light-info text-info">GCash</span></td>
                                            <td data-label="Status"><span class="badge bg-light-success text-success">Completed</span></td>
                                            <td data-label="Action" class="text-end">
                                                <button class="btn btn-sm btn-light-primary" title="View Receipt"><i class="ti ti-receipt"></i></button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Trans ID"><span class="trans-id">#TXN-88220</span></td>
                                            <td data-label="Date & Time">2026-05-20 10:30 AM</td>
                                            <td data-label="Voter">Maria Clara</td>
                                            <td data-label="Candidate">John Smith (#12)</td>
                                            <td data-label="Votes"><span class="fw-bold">100</span></td>
                                            <td data-label="Amount">₱1,000.00</td>
                                            <td data-label="Method"><span class="badge bg-light-warning text-warning">Bank Transfer</span></td>
                                            <td data-label="Status"><span class="badge bg-light-warning text-warning">Pending</span></td>
                                            <td data-label="Action" class="text-end">
                                                <div class="d-flex gap-1 justify-content-end">
                                                    <button class="btn btn-sm btn-success" title="Verify Payment"><i class="ti ti-check"></i></button>
                                                    <button class="btn btn-sm btn-light-primary" title="View Receipt"><i class="ti ti-receipt"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Trans ID"><span class="trans-id">#TXN-88221</span></td>
                                            <td data-label="Date & Time">2026-05-20 11:45 AM</td>
                                            <td data-label="Voter">Pedro Penduko</td>
                                            <td data-label="Candidate">Jane Doe (#04)</td>
                                            <td data-label="Votes"><span class="fw-bold">20</span></td>
                                            <td data-label="Amount">₱200.00</td>
                                            <td data-label="Method"><span class="badge bg-light-info text-info">Maya</span></td>
                                            <td data-label="Status"><span class="badge bg-light-danger text-danger">Failed</span></td>
                                            <td data-label="Action" class="text-end">
                                                <button class="btn btn-sm btn-light-primary" title="View Detail"><i class="ti ti-eye"></i></button>
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
            $('#purchasesTable').DataTable({
                "pageLength": 25,
                "order": [[1, "desc"]],
                "language": {
                    "search": "Filter Purchases:",
                }
            });
        });
    </script>
</body>

</html>
