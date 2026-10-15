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
  <title>Vote Packages - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Vote Packages</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Vote Packages</li>
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
                     <h5 class="mb-2 mb-md-0">Package List</h5>
                     <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#packageModal">
                        <i class="ti ti-plus me-1"></i>Add New Package
                     </button>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="votePackagesTable" class="table table-hover align-middle">
                      <thead class="bg-light">
                        <tr>
                          <th>Package Name</th>
                          <th>Votes</th>
                          <th>Price</th>
                          <th>Promo/Bonus</th>
                          <th>Status</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="votePackagesTableBody">
                        <!-- Dynamic content -->
                      </tbody>
                    </table>
                   </div>
                </div>
              </div>
            </div>
          </div>
 
          <!-- Modal -->
          <div class="modal fade" id="packageModal" tabindex="-1" aria-hidden="true">
              <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content border-0 shadow-lg rounded-4">
                      <div class="modal-header">
                          <h5 class="modal-title fw-bold">Package Details</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      </div>
                      <div class="modal-body p-4">
                          <form id="packageForm">
                              <input type="hidden" id="pkgId">
                              <div class="mb-3">
                                  <label for="pkgName" class="form-label fw-semibold">Package Name</label>
                                  <input type="text" class="form-control rounded-3" id="pkgName" placeholder="e.g. Starter Bundle" required>
                              </div>
                              <div class="row">
                                  <div class="col-md-6 mb-3">
                                      <label for="pkgVotes" class="form-label fw-semibold">Number of Votes</label>
                                      <input type="number" class="form-control rounded-3" id="pkgVotes" required>
                                  </div>
                                  <div class="col-md-6 mb-3">
                                      <label for="pkgPrice" class="form-label fw-semibold">Price (₱)</label>
                                      <input type="number" class="form-control rounded-3" id="pkgPrice" step="0.01" required>
                                  </div>
                              </div>
                              <div class="mb-3">
                                  <label for="pkgBonus" class="form-label fw-semibold">Promo/Bonus Label</label>
                                  <input type="text" class="form-control rounded-3" id="pkgBonus" placeholder="Optional (e.g. Save 10%)">
                              </div>
                              <div class="form-check form-switch mb-1">
                                  <input class="form-check-input" type="checkbox" role="switch" id="pkgActive" checked>
                                  <label class="form-check-label fw-bold" for="pkgActive">Active Status</label>
                              </div>
                          </form>
                      </div>
                      <div class="modal-footer border-0">
                          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                          <button type="button" class="btn btn-primary rounded-pill px-4" id="btnSavePackage">Save Package</button>
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
      let packageTable = $('#votePackagesTable').DataTable({
        "responsive": true,
        "dom": '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search packages..."
        }
      });

      function loadPackages() {
        $.ajax({
          url: 'endpoints/vote_package_handler.php',
          type: 'GET',
          data: { action: 'fetch' },
          success: function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') {
              packageTable.clear();
              res.data.forEach(pkg => {
                const statusBadge = pkg.is_active 
                  ? '<span class="badge bg-light-success text-success">Active</span>'
                  : '<span class="badge bg-light-secondary text-secondary">Inactive</span>';
                
                const promoLabel = pkg.promo_label 
                  ? `<span class="badge bg-light-info text-info">${pkg.promo_label}</span>`
                  : '--';

                const actions = `
                  <div class="d-flex justify-content-end gap-2">
                    <button class="btn btn-sm btn-outline-primary btn-edit" data-pkg='${JSON.stringify(pkg)}' title="Edit"><i class="ti ti-pencil"></i></button>
                    <button class="btn btn-sm btn-outline-danger btn-delete" data-id="${pkg.id}" title="Delete"><i class="ti ti-trash"></i></button>
                  </div>
                `;

                packageTable.row.add([
                  `<div class="d-flex align-items-center">
                    <div class="bg-light-primary text-primary rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                      <i class="ti ti-${pkg.votes_count >= 100 ? 'diamond' : 'package'} fs-6"></i>
                    </div>
                    <h6 class="mb-0 fw-semibold">${pkg.name}</h6>
                  </div>`,
                  `<span class="fw-bold fs-4">${pkg.votes_count}</span>`,
                  `₱${parseFloat(pkg.price).toLocaleString(undefined, {minimumFractionDigits: 2})}`,
                  promoLabel,
                  statusBadge,
                  actions
                ]);
              });
              packageTable.draw();
            }
          }
        });
      }

      loadPackages();

      // Reset form on "Add New"
      $('[data-bs-target="#packageModal"]').click(function() {
        $('#pkgId').val('');
        $('#packageForm')[0].reset();
        $('#pkgActive').prop('checked', true);
        $('.modal-title').text('Add New Package');
      });

      // Edit Button
      $(document).on('click', '.btn-edit', function() {
        const pkg = $(this).data('pkg');
        $('#pkgId').val(pkg.id);
        $('#pkgName').val(pkg.name);
        $('#pkgVotes').val(pkg.votes_count);
        $('#pkgPrice').val(pkg.price);
        $('#pkgBonus').val(pkg.promo_label);
        $('#pkgActive').prop('checked', pkg.is_active);
        $('.modal-title').text('Edit Package');
        $('#packageModal').modal('show');
      });

      // Save Package
      $('#btnSavePackage').click(function() {
        const data = {
          action: 'save',
          id: $('#pkgId').val(),
          name: $('#pkgName').val(),
          votes: $('#pkgVotes').val(),
          price: $('#pkgPrice').val(),
          promo_label: $('#pkgBonus').val(),
          is_active: $('#pkgActive').is(':checked') ? 1 : 0
        };

        if (!data.name || !data.votes || !data.price) {
          Swal.fire('Error', 'Please fill all required fields.', 'error');
          return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        $.ajax({
          url: 'endpoints/vote_package_handler.php',
          type: 'POST',
          data: data,
          success: function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') {
              Swal.fire('Saved!', res.message, 'success');
              $('#packageModal').modal('hide');
              loadPackages();
            } else {
              Swal.fire('Error', res.message, 'error');
            }
            btn.prop('disabled', false).text('Save Package');
          },
          error: function() {
            Swal.fire('Error', 'Failed to connect to server.', 'error');
            btn.prop('disabled', false).text('Save Package');
          }
        });
      });

      // Delete Package
      $(document).on('click', '.btn-delete', function() {
        const id = $(this).data('id');
        Swal.fire({
          title: 'Are you sure?',
          text: "This package will be permanently removed!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Yes, delete it!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.ajax({
              url: 'endpoints/vote_package_handler.php',
              type: 'POST',
              data: { action: 'delete', id: id },
              success: function(response) {
                const res = JSON.parse(response);
                if (res.status === 'success') {
                  Swal.fire('Deleted!', res.message, 'success');
                  loadPackages();
                } else {
                  Swal.fire('Error', res.message, 'error');
                }
              }
            });
          }
        });
      });
    });
  </script>
</body>
</html>
