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
    <title>Competition Summary - Aurora Tabs</title>
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
                                    <h4 class="fw-semibold mb-8">Competition Summary</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Summary</li>
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

                    <!-- Summary Cards -->
                    <div class="row">
                        <div class="col-lg-3 col-md-6">
                            <div class="card bg-light-primary shadow-none border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="round-40 rounded-circle bg-primary d-flex align-items-center justify-content-center text-white me-3">
                                            <i class="ti ti-layout-grid fs-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-semibold mb-0" id="totalCount">0</h4>
                                            <p class="text-muted mb-0">Total</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card bg-light-success shadow-none border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="round-40 rounded-circle bg-success d-flex align-items-center justify-content-center text-white me-3">
                                            <i class="ti ti-activity fs-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-semibold mb-0" id="activeCount">0</h4>
                                            <p class="text-muted mb-0">Live/Active</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card bg-light-warning shadow-none border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="round-40 rounded-circle bg-warning d-flex align-items-center justify-content-center text-white me-3">
                                            <i class="ti ti-lock fs-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-semibold mb-0" id="completedCount">0</h4>
                                            <p class="text-muted mb-0">Completed</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="card bg-light-info shadow-none border-0">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="round-40 rounded-circle bg-info d-flex align-items-center justify-content-center text-white me-3">
                                            <i class="ti ti-file-text fs-6"></i>
                                        </div>
                                        <div>
                                            <h4 class="fw-semibold mb-0" id="draftCount">0</h4>
                                            <p class="text-muted mb-0">In Draft</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Competition Table -->
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-semibold">Event Status Overview</h5>
                                <a href="competitions.php" class="btn btn-primary btn-sm">Manage All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle mb-0 table-hover table-responsive-stack" id="summaryTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Competition Name</th>
                                            <th class="border-bottom-0">Status</th>
                                            <th class="border-bottom-0 text-center">Candidates</th>
                                            <th class="border-bottom-0 text-center">Judges</th>
                                            <th class="border-bottom-0">Quick Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">
                                                <div class="spinner-border text-primary" role="status">
                                                    <span class="visually-hidden">Loading...</span>
                                                </div>
                                                <p class="mt-2 text-muted">Loading competition summary...</p>
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
            function loadSummary() {
                $.ajax({
                    url: 'endpoints/summary_handler.php',
                    method: 'POST',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Update Cards
                            $('#totalCount').text(response.counts.total);
                            $('#activeCount').text(response.counts.active);
                            $('#completedCount').text(response.counts.completed);
                            $('#draftCount').text(response.counts.draft);

                            // Update Table
                            const tbody = $('#summaryTable tbody');
                            tbody.empty();
                            response.competitions.forEach(comp => {
                                let statusBadge = '';
                                const status = (comp.status || '').toLowerCase();
                                if (status === 'active' || status === 'live') {
                                    statusBadge = '<span class="badge bg-light-success text-success">Live</span>';
                                } else if (status === 'completed' || status === 'finished') {
                                    statusBadge = '<span class="badge bg-light-warning text-warning">Completed</span>';
                                } else {
                                    statusBadge = '<span class="badge bg-light-info text-info">Draft</span>';
                                }

                                let actions = '';
                                if (status === 'active' || status === 'live' || status === 'completed' || status === 'finished') {
                                    actions = `
                                        <a href="rankings.php?competition_id=${comp.id}" class="btn btn-sm btn-outline-primary" title="Rankings"><i class="ti ti-list-details"></i></a>
                                        <a href="live-results.php?competition_id=${comp.id}" class="btn btn-sm btn-outline-success" title="Live Board"><i class="ti ti-device-tv"></i></a>
                                        <a href="final-results.php?competition_id=${comp.id}" class="btn btn-sm btn-outline-dark" title="Final Reports"><i class="ti ti-file-analytics"></i></a>
                                    `;
                                } else {
                                    actions = `<a href="competitions.php" class="btn btn-sm btn-outline-secondary" title="Setup"><i class="ti ti-settings"></i></a>`;
                                }

                                tbody.append(`
                                    <tr>
                                        <td class="border-bottom-0" data-label="Competition Name">
                                            <h6 class="fw-semibold mb-1">${comp.title}</h6>
                                            <span class="fs-2 text-muted">${comp.description || 'No description'}</span>
                                        </td>
                                        <td class="border-bottom-0" data-label="Status">${statusBadge}</td>
                                        <td class="border-bottom-0 text-center" data-label="Cand.">${comp.cand_count}</td>
                                        <td class="border-bottom-0 text-center" data-label="Judges">${comp.judge_count}</td>
                                        <td class="border-bottom-0" data-label="Quick Actions">
                                            <div class="d-flex flex-wrap gap-2 justify-content-start">${actions}</div>
                                        </td>
                                    </tr>
                                `);
                            });

                            // Re-init DataTable
                            if ($.fn.DataTable.isDataTable('#summaryTable')) {
                                $('#summaryTable').DataTable().destroy();
                            }
                            $('#summaryTable').DataTable({
                                "pageLength": 10,
                                "ordering": true,
                                "language": {
                                    "search": "Filter Competitions:",
                                }
                            });
                        }
                    }
                });
            }

            loadSummary();
        });
    </script>
</body>

</html>
