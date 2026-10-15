<?php
    session_start();
    
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    // Permission checks
    $can_view = has_permission('Criteria', 'view', $conn);
    $can_manage = has_permission('Criteria', 'manage', $conn);
    $can_delete = has_permission('Criteria', 'delete', $conn);

    if (!$can_view) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Judging Criteria - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Judging Criteria</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Criteria</li>
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
            <?php if ($can_manage): ?>
            <div class="col-md-4">
              <div class="card">
                <div class="card-body">
                  <h5 class="mb-3" id="formTitle">Add New Criterion</h5>
                  <form id="criteriaForm">
                    <input type="hidden" id="critId">
                    <div class="mb-3">
                      <label class="form-label">Criterion Name</label>
                      <input type="text" class="form-control" id="critName" placeholder="e.g. Beauty & Poise" required>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Weight (%)</label>
                          <input type="number" class="form-control" id="critWeight" placeholder="0" required min="0" max="100">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label class="form-label">Order</label>
                          <input type="number" class="form-control" id="critOrder" placeholder="0">
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Category / Level</label>
                      <select class="form-select" id="critCategory">
                          <option value="">Select Category</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="critRequired" checked>
                        <label class="form-check-label" for="critRequired">Required for Tabulation</label>
                      </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1" id="saveBtn">Add Criterion</button>
                        <button type="button" class="btn btn-outline-danger d-none" id="cancelEdit">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
            </div>
            <?php endif; ?>
            
            <div class="<?php echo $can_manage ? 'col-md-8' : 'col-12'; ?>">
              <div class="card">
                <div class="card-body">
                  <div class="row mb-4 gx-2">
                    <div class="col-md-5">
                      <label class="form-label fw-semibold">Filter by Competition</label>
                      <select class="form-select" id="filterCompetition">
                        <option value="">All Competitions</option>
                      </select>
                    </div>
                    <div class="col-md-5">
                      <label class="form-label fw-semibold">Filter by Category</label>
                      <select class="form-select" id="filterCategory">
                        <option value="">All Categories</option>
                      </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                      <button class="btn btn-outline-secondary w-100" id="resetFilters">
                        <i class="ti ti-refresh me-1"></i>Reset
                      </button>
                    </div>
                  </div>

                  <div class="d-block d-md-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-2 mb-md-0">Judging Criteria List</h5>
                    <div class="text-end">
                      <?php if ($can_delete): ?>
                      <a href="deleted-criteria.php" class="btn btn-outline-danger btn-sm">
                          <i class="ti ti-trash me-1"></i>Deleted Archive
                      </a>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="criteriaTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Criterion</th>
                          <th>Weight</th>
                          <th>Order</th>
                          <th>Required</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="criteriaTableBody">
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

  // Permissions passed from PHP
  const PERMISSIONS = {
      manage: <?php echo $can_manage ? 'true' : 'false'; ?>,
      delete: <?php echo $can_delete ? 'true' : 'false'; ?>
  };

  $(document).ready(function() {
    const critTable = $('#criteriaTable').DataTable({
      "responsive": true,
      "pageLength": 10,
      "ordering": true,
      "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
             'rt' +
             '<"d-flex justify-content-between align-items-center mt-4"ip>',
      "language": {
        "search": "_INPUT_",
        "searchPlaceholder": "Filter criteria..."
      }
    });

    loadFilterCompetitions();
    loadCategories();
    loadCriteria();

    // Load competitions for filters
    function loadFilterCompetitions() {
        $.post('endpoints/criteria_handler.php', { action: 'get_competitions' }, function(response) {
            if (response.status === 'success') {
                let options = '<option value="">All Competitions</option>';
                response.data.forEach(comp => {
                    options += `<option value="${comp.id}">${comp.title}</option>`;
                });
                $('#filterCompetition').html(options);
            }
        });
    }

    // Handle filter competition change
    $('#filterCompetition').on('change', function() {
        const compId = $(this).val();
        $('#filterCategory').html('<option value="">All Categories</option>');
        
        if (compId) {
            $.post('endpoints/criteria_handler.php', { 
                action: 'get_categories_by_comp', 
                competition_id: compId 
            }, function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">All Categories</option>';
                    response.data.forEach(cat => {
                        options += `<option value="${cat.id}">${cat.name}</option>`;
                    });
                    $('#filterCategory').html(options);
                }
            });
        }
        loadCriteria();
    });

    // Handle filter category change
    $('#filterCategory').on('change', function() {
        loadCriteria();
    });

    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#filterCompetition').val('').trigger('change');
    });

    function loadCategories() {
        $.post('endpoints/criteria_handler.php', { action: 'list_categories' }, function(response) {
            if (response.status === 'success') {
                let options = '<option value="">Select Category</option>';
                response.data.forEach(cat => {
                    options += `<option value="${cat.id}">${cat.display_name}</option>`;
                });
                $('#critCategory').html(options);
            }
        });
    }

    function loadCriteria() {
        const filters = {
            action: 'list',
            competition_id: $('#filterCompetition').val(),
            category_id: $('#filterCategory').val()
        };
        $.post('endpoints/criteria_handler.php', filters, function(response) {
            if (response.status === 'success') {
                critTable.clear();
                response.data.forEach(crit => {
                    const reqBadge = crit.is_required == 1 
                        ? '<span class="badge bg-light-success text-success">Yes</span>' 
                        : '<span class="badge bg-light-danger text-danger">No</span>';
                    
                    let actionButtons = '<div class="btn-group">';
                    
                    if (PERMISSIONS.manage) {
                        actionButtons += `<button class="btn btn-sm btn-outline-primary edit-crit" data-crit='${JSON.stringify(crit)}'><i class="ti ti-edit"></i></button>`;
                    }
                    
                    if (PERMISSIONS.delete) {
                        actionButtons += `<button class="btn btn-sm btn-outline-danger delete-crit" data-id="${crit.id}"><i class="ti ti-trash"></i></button>`;
                    }
                    
                    actionButtons += '</div>';

                    critTable.row.add([
                        `<span class="fw-semibold">${crit.name}</span>`,
                        `${crit.weight_percent}%`,
                        crit.display_order,
                        reqBadge,
                        actionButtons
                    ]);

                    // Add data-labels for mobile row style
                    const lastRow = critTable.row(':last').node();
                    $(lastRow).find('td').eq(0).attr('data-label', 'Criterion');
                    $(lastRow).find('td').eq(1).attr('data-label', 'Weight');
                    $(lastRow).find('td').eq(2).attr('data-label', 'Order');
                    $(lastRow).find('td').eq(3).attr('data-label', 'Required');
                    $(lastRow).find('td').eq(4).attr('data-label', 'Action');
                });
                critTable.draw();
            }
        });
    }

    $('#criteriaForm').on('submit', function(e) {
        e.preventDefault();
        const id = $('#critId').val();
        const data = {
            action: id ? 'update' : 'create',
            id: id,
            name: $('#critName').val(),
            weight: $('#critWeight').val(),
            display_order: $('#critOrder').val(),
            category_id: $('#critCategory').val(),
            is_required: $('#critRequired').is(':checked')
        };

        $.post('endpoints/criteria_handler.php', data, function(response) {
            if (response.status === 'success') {
                Swal.fire('Success', response.message, 'success');
                resetForm();
                loadCriteria();
            } else {
                Swal.fire('Error', response.message, 'error');
            }
        });
    });

    $(document).on('click', '.edit-crit', function() {
        const crit = $(this).data('crit');
        $('#critId').val(crit.id);
        $('#critName').val(crit.name);
        $('#critWeight').val(crit.weight_percent);
        $('#critOrder').val(crit.display_order);
        $('#critCategory').val(crit.category_id);
        $('#critRequired').prop('checked', crit.is_required == 1);
        
        $('#formTitle').text('Edit Criterion');
        $('#saveBtn').text('Update Criterion');
        $('#cancelEdit').removeClass('d-none');
    });

    $('#cancelEdit').on('click', resetForm);

    function resetForm() {
        $('#critId').val('');
        $('#criteriaForm')[0].reset();
        $('#formTitle').text('Add New Criterion');
        $('#saveBtn').text('Add Criterion');
        $('#cancelEdit').addClass('d-none');
    }

    $(document).on('click', '.delete-crit', function() {
        const id = $(this).data('id');
        Swal.fire({
            title: 'Move to Archive?',
            text: "This criterion will be soft-deleted.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, archive it'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post('endpoints/criteria_handler.php', { action: 'delete', id: id }, function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Archived', response.message, 'success');
                        loadCriteria();
                    }
                });
            }
        });
    });
  });
</script>
</body>

</html>
