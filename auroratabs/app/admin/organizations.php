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
  <title>Organizations - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Organizations</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Organizations</li>
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
            <div class="col-md-4">
              <div class="card">
                <div class="card-body">
                  <h5 class="mb-3" id="formTitle">Add Organization</h5>
                  <form id="orgForm">
                    <input type="hidden" id="orgId">
                    <div class="mb-3">
                      <label class="form-label">Organization Name <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="orgName" placeholder="e.g. Acme Corp" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Owner / Representative</label>
                      <input type="text" class="form-control" id="orgOwnerSearch" list="usersDatalist" placeholder="Search for a user...">
                      <datalist id="usersDatalist"></datalist>
                      <input type="hidden" id="orgOwnerId">
                      <div class="form-text">Select a system user to link as owner.</div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Email Address</label>
                      <input type="email" class="form-control" id="orgEmail" placeholder="contact@org.com">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Phone Number</label>
                      <input type="text" class="form-control" id="orgPhone" placeholder="+1 234 567 890">
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select class="form-select" id="orgStatus">
                        <option value="active">Active</option>
                        <option value="suspended">Suspended</option>
                      </select>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" id="saveBtn">Save Organization</button>
                        <button type="button" class="btn btn-outline-danger d-none" id="cancelEdit">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            
            <div class="col-md-8">
              <div class="card">
                <div class="card-body">
                  <div class="d-block d-md-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-2 mb-md-0">Organizations List</h5>
                    <div class="text-end">
                      <a href="deleted-organizations.php" class="btn btn-outline-danger btn-sm">
                          <i class="ti ti-trash me-1"></i>Deleted Archive
                      </a>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="orgsTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Organization</th>
                          <th>Contact Info</th>
                          <th>Created At</th>
                          <th>Status</th>
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
      const orgsTable = $('#orgsTable').DataTable({
        "responsive": true,
        "pageLength": 10,
        "ordering": true,
        "dom": '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4"fl>rtip',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Filter organizations..."
        },
        "createdRow": function(row, data, dataIndex) {
          const headers = $(row).closest('table').find('thead th');
          $(row).find('td').each(function(index) {
            $(this).attr('data-label', $(headers[index]).text());
          });
        }
      });

      let systemUsers = [];
      loadOrgs();
      loadUsers();

      function loadUsers() {
        $.post('endpoints/organizations_handler.php', { action: 'list_users' }, function(response) {
            if (response.status === 'success') {
                systemUsers = response.data;
                let datalistHtml = '';
                systemUsers.forEach(user => {
                    const fullName = `${user.first_name} ${user.last_name} (${user.username})`;
                    datalistHtml += `<option data-id="${user.id}" value="${fullName}">`;
                });
                $('#usersDatalist').html(datalistHtml);
            }
        });
      }

      $('#orgOwnerSearch').on('input', function() {
          const val = $(this).val();
          const option = $('#usersDatalist option').filter(function() {
              return this.value === val;
          });
          if (option.length) {
              $('#orgOwnerId').val(option.data('id'));
          } else {
              $('#orgOwnerId').val('');
          }
      });

      function loadOrgs() {
        $.post('endpoints/organizations_handler.php', { action: 'list' }, function(response) {
            if (response.status === 'success') {
                orgsTable.clear();
                response.data.forEach(org => {
                    const statusBadge = org.status === 'active' 
                        ? '<span class="badge bg-light-success text-success">Active</span>' 
                        : '<span class="badge bg-light-danger text-danger">Suspended</span>';
                    
                    const orgInfo = `<div class="d-flex align-items-center">
                                      <span class="avatar avatar-xs rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold me-2" style="width:35px; height:35px;">
                                          ${org.name.charAt(0).toUpperCase()}
                                      </span>
                                      <span class="fw-semibold text-dark">${org.name}</span>
                                    </div>`;

                    const contactInfo = `<div>
                                          <div class="fs-2 text-dark">${org.email || '<span class="text-muted">No Email</span>'}</div>
                                          <div class="fs-1 text-muted">${org.phone || ''}</div>
                                         </div>`;

                    orgsTable.row.add([
                        orgInfo,
                        contactInfo,
                        `<span class="fs-2 text-muted">${org.created_at}</span>`,
                        statusBadge,
                        `<div class="btn-group">
                            <button class="btn btn-sm btn-outline-primary edit-org" data-org='${JSON.stringify(org)}' title="Edit"><i class="ti ti-edit"></i></button>
                            <button class="btn btn-sm btn-outline-warning toggle-status" data-id="${org.id}" data-status="${org.status}" title="Toggle Status"><i class="ti ti-toggle-left"></i></button>
                            <button class="btn btn-sm btn-outline-danger delete-org" data-id="${org.id}" title="Delete"><i class="ti ti-trash"></i></button>
                        </div>`
                    ]);
                });
                orgsTable.draw();
            }
        });
      }

      $('#orgForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#orgId').val();
        const data = {
            action: id ? 'update' : 'create',
            id: id,
            name: $('#orgName').val(),
            email: $('#orgEmail').val(),
            phone: $('#orgPhone').val(),
            status: $('#orgStatus').val(),
            owner_id: $('#orgOwnerId').val()
        };

        $.post('endpoints/organizations_handler.php', data, function(response) {
            if (response.status === 'success') {
                Swal.fire('Success', response.message, 'success');
                resetForm();
                loadOrgs();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        });
      });

      $(document).on('click', '.edit-org', function() {
        const org = $(this).data('org');
        $('#orgId').val(org.id);
        $('#orgName').val(org.name);
        $('#orgEmail').val(org.email);
        $('#orgPhone').val(org.phone);
        $('#orgStatus').val(org.status);
        $('#orgOwnerId').val(org.owner_id);
        
        // Find user name for the search display
        const user = systemUsers.find(u => u.id == org.owner_id);
        if (user) {
            $('#orgOwnerSearch').val(`${user.first_name} ${user.last_name} (${user.username})`);
        } else {
            $('#orgOwnerSearch').val('');
        }

        $('#formTitle').text('Edit Organization');
        $('#saveBtn').text('Update Organization');
        $('#cancelEdit').removeClass('d-none');
      });

      $('#cancelEdit').on('click', resetForm);

      function resetForm() {
        $('#orgId').val('');
        $('#orgForm')[0].reset();
        $('#orgOwnerId').val('');
        $('#orgOwnerSearch').val('');
        $('#formTitle').text('Add Organization');
        $('#saveBtn').text('Save Organization');
        $('#cancelEdit').addClass('d-none');
      }

      $(document).on('click', '.toggle-status', function() {
        const id = $(this).data('id');
        const status = $(this).data('status');
        $.post('endpoints/organizations_handler.php', { action: 'toggle_status', id: id, status: status }, function(response) {
            if (response.status === 'success') {
                Swal.fire('Updated', response.message, 'success');
                loadOrgs();
            }
        });
      });

      $(document).on('click', '.delete-org', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "This organization will be moved to archive.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('endpoints/organizations_handler.php', { action: 'delete', id: id }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Archived', response.message, 'success');
                        loadOrgs();
                    }
                });
            }
        });
      });
    });
  </script>
</body>

</html>
