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
    <title>General Settings - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .settings-card {
            transition: all 0.3s ease;
        }
        .settings-card:hover {
            transform: translateY(-5px);
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

                    <div class="top-part card bg-light-primary shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">General System Settings</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">General Settings</li>
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

                    <form>
                        <div class="row">
                            <!-- Left: Event Branding & Identity -->
                            <div class="col-lg-8">
                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title fw-semibold mb-4">Event Identity</h5>
                                        <div class="row">
                                            <div class="col-md-12 mb-3">
                                                <label for="eventName" class="form-label">Full Event Name</label>
                                                <input type="text" class="form-control" id="eventName" value="Aurora Grand Pageant 2026">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="eventDate" class="form-label">Event Date</label>
                                                <input type="date" class="form-control" id="eventDate" value="2026-05-20">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="eventVenue" class="form-label">Venue</label>
                                                <input type="text" class="form-control" id="eventVenue" value="Gran Ballroom, Aurora City">
                                            </div>
                                            <div class="col-md-12 mb-4">
                                                <label class="form-label">Event Logo</label>
                                                <div class="d-flex align-items-center gap-3">
                                                    <img src="src/assets/images/logos/aurora-logo.png" alt="Logo" class="rounded border p-1" width="80">
                                                    <div>
                                                        <button type="button" class="btn btn-outline-primary btn-sm mb-1">Change Logo</button>
                                                        <p class="text-muted small mb-0">Recommended: Square PNG, transparent background.</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr class="my-4">
                                        
                                        <h5 class="card-title fw-semibold mb-4">System Localization</h5>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="currency" class="form-label">Default Currency</label>
                                                <select class="form-select" id="currency">
                                                    <option value="PHP">Philippine Peso (₱)</option>
                                                    <option value="USD">US Dollar ($)</option>
                                                    <option value="EUR">Euro (€)</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="timezone" class="form-label">System Timezone</label>
                                                <select class="form-select" id="timezone">
                                                    <option value="Asia/Manila">(GMT+08:00) Manila</option>
                                                    <option value="UTC">Universal Coordinated Time (UTC)</option>
                                                    <option value="America/New_York">(GMT-05:00) Eastern Time</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="dateFormat" class="form-label">Date Format</label>
                                                <select class="form-select" id="dateFormat">
                                                    <option value="M d, Y">May 20, 2026</option>
                                                    <option value="Y-m-d">2026-05-20</option>
                                                    <option value="d/m/Y">20/05/2026</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Organization & Maintenance -->
                            <div class="col-lg-4">
                                <div class="card mb-4 bg-light-warning border-warning border shadow-none">
                                    <div class="card-body">
                                        <h5 class="card-title fw-semibold mb-3">Maintenance Mode</h5>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" id="maintenanceMode">
                                            <label class="form-check-label fw-bold" for="maintenanceMode">Offline for Maintenance</label>
                                        </div>
                                        <p class="small text-muted mb-0">When enabled, only administrators can access the system. Users will see a "Down for Maintenance" page.</p>
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-body">
                                        <h5 class="card-title fw-semibold mb-4">Organization Info</h5>
                                        <div class="mb-3">
                                            <label for="orgEmail" class="form-label">Support Email</label>
                                            <input type="email" class="form-control" id="orgEmail" value="support@auroratabs.com">
                                        </div>
                                        <div class="mb-3">
                                            <label for="orgPhone" class="form-label">Contact Number</label>
                                            <input type="text" class="form-control" id="orgPhone" value="+63 912 345 6789">
                                        </div>
                                        <div class="mb-3">
                                            <label for="orgTerms" class="form-label">Terms URL</label>
                                            <input type="url" class="form-control" id="orgTerms" placeholder="https://...">
                                        </div>
                                        
                                        <hr class="my-4">
                                        
                                        <button type="submit" class="btn btn-primary w-100 py-2">
                                            <i class="ti ti-device-floppy me-1"></i> Save All Changes
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <?php include 'components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/scripts.php'; ?>
</body>

</html>
