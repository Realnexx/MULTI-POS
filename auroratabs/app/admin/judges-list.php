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
    <title>Judges Management - Aurora Tabs</title>
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
                                    <h4 class="fw-semibold mb-8">Judges Management</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Judges List</li>
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

                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h5 class="card-title fw-semibold">Event Judges</h5>
                                <div class="d-flex gap-2">
                                    <a href="deleted-judges.php" class="btn btn-outline-secondary">
                                        <i class="ti ti-trash me-1"></i> Deleted Judges
                                    </a>
                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJudgeModal">
                                        <i class="ti ti-user-plus me-1"></i> Add New Judge
                                    </button>
                                </div>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table text-nowrap align-middle mb-0 table-hover table-responsive-stack" id="judgesManagementTable">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="border-bottom-0">Judge</th>
                                            <th class="border-bottom-0">Access Code</th>
                                            <th class="border-bottom-0">Role</th>
                                            <th class="border-bottom-0">Status</th>
                                            <th class="border-bottom-0">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="border-bottom-0" data-label="Judge">
                                                <div class="d-flex align-items-center">
                                                    <img src="src/assets/images/profile/user10.jpg" class="rounded-circle" width="35" height="35" alt="">
                                                    <div class="ms-3">
                                                        <h6 class="fw-semibold mb-0">Mark Freeman</h6>
                                                        <span class="fs-2 text-muted">mark@example.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="border-bottom-0" data-label="Access Code">
                                                <span class="badge bg-light-primary text-primary fw-bold">JDG-8842</span>
                                            </td>
                                            <td class="border-bottom-0" data-label="Role">Chairman</td>
                                            <td class="border-bottom-0" data-label="Status">
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td class="border-bottom-0" data-label="Actions">
                                                <div class="d-flex gap-2 justify-content-start flex-wrap">
                                                    <button class="btn btn-sm btn-outline-info" title="Edit Profile"><i class="ti ti-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-warning" title="Reset Access Code"><i class="ti ti-key"></i></button>
                                                    <button class="btn btn-sm btn-outline-dark" title="Block Judge"><i class="ti ti-ban"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Toggle Status"><i class="ti ti-toggle-left"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Remove Judge"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="border-bottom-0" data-label="Judge">
                                                <div class="d-flex align-items-center">
                                                    <img src="src/assets/images/profile/user4.jpg" class="rounded-circle" width="35" height="35" alt="">
                                                    <div class="ms-3">
                                                        <h6 class="fw-semibold mb-0">Nina Oldman</h6>
                                                        <span class="fs-2 text-muted">nina@example.com</span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="border-bottom-0" data-label="Access Code">
                                                <span class="badge bg-light-primary text-primary fw-bold">JDG-3467</span>
                                            </td>
                                            <td class="border-bottom-0" data-label="Role">Member</td>
                                            <td class="border-bottom-0" data-label="Status">
                                                <span class="badge bg-success">Active</span>
                                            </td>
                                            <td class="border-bottom-0" data-label="Actions">
                                                <div class="d-flex gap-2 justify-content-start flex-wrap">
                                                    <button class="btn btn-sm btn-outline-info" title="Edit Profile"><i class="ti ti-edit"></i></button>
                                                    <button class="btn btn-sm btn-outline-warning" title="Reset Access Code"><i class="ti ti-key"></i></button>
                                                    <button class="btn btn-sm btn-outline-dark" title="Block Judge"><i class="ti ti-ban"></i></button>
                                                    <button class="btn btn-sm btn-outline-secondary" title="Toggle Status"><i class="ti ti-toggle-left"></i></button>
                                                    <button class="btn btn-sm btn-outline-danger" title="Remove Judge"><i class="ti ti-trash"></i></button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Add Judge Modal -->
                    <div class="modal fade" id="addJudgeModal" tabindex="-1" aria-labelledby="addJudgeModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="addJudgeModalLabel">Add New Judge</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="firstName" class="form-label">First Name</label>
                                                <input type="text" class="form-control" id="firstName" required>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="lastName" class="form-label">Last Name</label>
                                                <input type="text" class="form-control" id="lastName" required>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control" id="email">
                                        </div>
                                        <div class="mb-3">
                                            <label for="role" class="form-label">Role / Designation</label>
                                            <input type="text" class="form-control" id="role" placeholder="e.g. Chairman, Member">
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="accessCode" class="form-label">Access Code</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="accessCode" value="JDG-AUTO" readonly>
                                                    <button class="btn btn-outline-secondary" type="button" title="Regenerate"><i class="ti ti-refresh"></i></button>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="pin" class="form-label">Login PIN</label>
                                                <input type="text" class="form-control" id="pin" value="123456">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-outline-danger" data-bs-dismiss="modal">Cancel</button>
                                    <button type="button" class="btn btn-primary">Save Judge</button>
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
            $('#judgesManagementTable').DataTable({
                "pageLength": 10,
                "ordering": true,
                "language": {
                    "search": "Filter Judges:",
                }
            });
        });
    </script>
</body>

</html>
