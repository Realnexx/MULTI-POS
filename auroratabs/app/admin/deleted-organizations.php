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
  <title>Deleted Organizations - Aurora Tabs</title>
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

          <div class="top-part card bg-light-danger shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center">
                <div class="col-9">
                  <h4 class="fw-semibold mb-8">Deleted Organizations</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="organizations.php">Organizations</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Archive</li>
                    </ol>
                  </nav>
                </div>
                <div class="col-3">
                  <div class="text-center mb-n5">
                    <i class="ti ti-trash fs-12 text-danger opacity-25"></i>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="mb-0">Archived Organizations</h5>
                <a href="organizations.php" class="btn btn-outline-primary btn-sm">
                    <i class="ti ti-arrow-left me-1"></i>Back to Active
                </a>
              </div>
              <div class="table-responsive">
                <table id="deletedOrgsTable" class="table table-hover align-middle table-responsive-stack">
                  <thead class="bg-light">
                    <tr>
                      <th>Organization</th>
                      <th>Contact</th>
                      <th>Created At</th>
                      <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                      <!-- Dynamic Content -->
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
      const deletedTable = $('#deletedOrgsTable').DataTable({
        "responsive": true,
        "pageLength": 10,
        "ordering": true,
        "dom": '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4"fl>rtip',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search trash..."
        },
        "createdRow": function(row, data, dataIndex) {
          const headers = $(row).closest('table').find('thead th');
          $(row).find('td').each(function(index) {
            $(this).attr('data-label', $(headers[index]).text());
          });
        }
      });

      loadDeletedOrgs();

      function loadDeletedOrgs() {
        $.post('endpoints/organizations_handler.php', { action: 'list_deleted' }, function(response) {
            if (response.status === 'success') {
                deletedTable.clear();
                response.data.forEach(org => {
                    const orgInfo = `<div class="d-flex align-items-center">
                                      <span class="avatar avatar-xs rounded-circle bg-light-danger text-danger d-flex align-items-center justify-content-center fw-bold me-2" style="width:35px; height:35px;">
                                          ${org.name.charAt(0).toUpperCase()}
                                      </span>
                                      <span class="fw-semibold text-dark">${org.name}</span>
                                    </div>`;

                    deletedTable.row.add([
                        orgInfo,
                        `<div class="fs-2 text-muted">${org.email || 'N/A'}</div>`,
                        `<span class="fs-2 text-muted">${org.created_at}</span>`,
                        `<div class="btn-group">
                            <button class="btn btn-sm btn-outline-success restore-org" data-id="${org.id}" title="Restore"><i class="ti ti-refresh"></i></button>
                            <button class="btn btn-sm btn-outline-danger perm-delete-org" data-id="${org.id}" title="Delete Permanently"><i class="ti ti-trash"></i></button>
                        </div>`
                    ]);
                });
                deletedTable.draw();
            }
        });
      }

      $(document).on('click', '.restore-org', function() {
        const id = $(this).data('id');
        $.post('endpoints/organizations_handler.php', { action: 'restore', id: id }, function(response) {
            if (response.status === 'success') {
                Swal.fire('Restored', response.message, 'success');
                loadDeletedOrgs();
            }
        });
      });

      $(document).on('click', '.perm-delete-org', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Delete Permanently?',
            text: "This action cannot be undone!",
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, DELETE it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('endpoints/organizations_handler.php', { action: 'permanent_delete', id: id }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Deleted!', response.message, 'success');
                        loadDeletedOrgs();
                    }
                });
            }
        });
      });
    });
  </script>
</body>

</html>
