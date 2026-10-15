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
    $can_view = has_permission('Categories', 'view', $conn);
    $can_manage = has_permission('Categories', 'manage', $conn);
    $can_delete = has_permission('Categories', 'delete', $conn);

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
  <title>Competition Categories - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Competition Categories</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Categories</li>
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
                  <h5 class="mb-3">Add New Category</h5>
                  <form id="addCategoryForm">
                    <div class="mb-3">
                      <label for="compName" class="form-label">Competition</label>
                      <select class="form-select" id="compName" name="competition_id" required>
                        <option value="">Choose competition...</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="catName" class="form-label">Category Name</label>
                      <input type="text" class="form-control" id="catName" name="name" placeholder="e.g. Finals" required>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="catType" class="form-label">Category Type</label>
                          <select class="form-select" id="catType" name="type">
                            <option value="major">Major</option>
                            <option value="minor">Minor</option>
                            <option value="special">Special</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="catLevel" class="form-label">Level</label>
                          <input type="number" class="form-control" id="catLevel" name="level" value="1" placeholder="e.g. 1">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="displayOrder" class="form-label">Display Order</label>
                          <input type="number" class="form-control" id="displayOrder" name="display_order" value="0" placeholder="e.g. 1">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="weightPercent" class="form-label">Weight (%)</label>
                          <input type="number" step="0.01" class="form-control" id="weightPercent" name="weight_percent" value="0" placeholder="100">
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="catDesc" class="form-label">Description</label>
                      <textarea class="form-control" id="catDesc" name="description" rows="2" placeholder="Brief description..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Add Category</button>
                  </form>
                </div>
              </div>
            </div>
            <?php endif; ?>
            
            <div class="<?php echo $can_manage ? 'col-md-8' : 'col-12'; ?>">
              <div class="card">
                <div class="card-body">
                  <div class="row mb-4 gx-2">
                    <div class="col-md-10">
                      <label class="form-label fw-semibold">Filter by Competition</label>
                      <select class="form-select" id="filterCompetition">
                        <option value="">All Competitions</option>
                      </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                      <button class="btn btn-outline-secondary w-100" id="resetFilters">
                        <i class="ti ti-refresh me-1"></i>Reset
                      </button>
                    </div>
                  </div>

                  <div class="d-block d-md-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-2 mb-md-0">Existing Categories</h5>
                    <div class="text-end">
                      <?php if ($can_delete): ?>
                      <a href="deleted-categories.php" class="btn btn-outline-danger btn-sm">
                          <i class="ti ti-trash me-1"></i>Deleted Categories
                      </a>
                      <?php endif; ?>
                    </div>
                  </div>
                  <div class="table-responsive">
                    <table id="categoriesTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Category</th>
                          <th>Competition</th>
                          <th>Type</th>
                          <th>Level</th>
                          <th>Order</th>
                          <th>Weight</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="categoriesTableBody">
                        <!-- Content loaded via AJAX -->
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Edit Category Modal -->
          <div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
            <div class="modal-dialog">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="editCategoryModalLabel">Edit Category</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <form id="editCategoryForm">
                    <input type="hidden" id="editCatId" name="id">
                    <div class="mb-3">
                      <label for="editCompName" class="form-label">Competition</label>
                      <select class="form-select" id="editCompName" name="competition_id" required>
                        <option value="">Choose competition...</option>
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="editCatName" class="form-label">Category Name</label>
                      <input type="text" class="form-control" id="editCatName" name="name" required>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="editCatType" class="form-label">Category Type</label>
                          <select class="form-select" id="editCatType" name="type">
                            <option value="major">Major</option>
                            <option value="minor">Minor</option>
                            <option value="special">Special</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="editCatLevel" class="form-label">Level</label>
                          <input type="number" class="form-control" id="editCatLevel" name="level">
                        </div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="editDisplayOrder" class="form-label">Display Order</label>
                          <input type="number" class="form-control" id="editDisplayOrder" name="display_order">
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="editWeightPercent" class="form-label">Weight (%)</label>
                          <input type="number" step="0.01" class="form-control" id="editWeightPercent" name="weight_percent">
                        </div>
                      </div>
                    </div>
                    <div class="mb-3">
                      <label for="editCatDesc" class="form-label">Description</label>
                      <textarea class="form-control" id="editCatDesc" name="description" rows="2"></textarea>
                    </div>
                    <div class="modal-footer px-0 pb-0">
                      <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                      <button type="submit" class="btn btn-primary">Save Changes</button>
                    </div>
                  </form>
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
    let categoriesTable = $('#categoriesTable').DataTable({
      "responsive": true,
      "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
             'rt' +
             '<"d-flex justify-content-between align-items-center mt-4"ip>',
      "language": {
        "search": "_INPUT_",
        "searchPlaceholder": "Search categories..."
      }
    });

    function loadCompetitions() {
      $.post('endpoints/categories_handler.php', { action: 'get_competitions' }, function(response) {
        if (response.status === 'success') {
          let options = '<option value="">Choose competition...</option>';
          let filterOptions = '<option value="">All Competitions</option>';
          response.data.forEach(function(comp) {
            options += `<option value="${comp.id}">${comp.title}</option>`;
            filterOptions += `<option value="${comp.id}">${comp.title}</option>`;
          });
          $('#compName, #editCompName').html(options);
          $('#filterCompetition').html(filterOptions);
        }
      });
    }

    // Handle filter change
    $('#filterCompetition').on('change', function() {
        loadCategories();
    });

    // Reset filter
    $('#resetFilters').on('click', function() {
        $('#filterCompetition').val('').trigger('change');
    });

    function loadCategories() {
      const filters = {
        action: 'list',
        competition_id: $('#filterCompetition').val()
      };
      $.post('endpoints/categories_handler.php', filters, function(response) {
        if (response.status === 'success') {
          categoriesTable.clear();
          response.data.forEach(function(cat) {
            let typeBadge = '';
            if (cat.type === 'major') typeBadge = '<span class="badge bg-light-primary text-primary">Major</span>';
            else if (cat.type === 'minor') typeBadge = '<span class="badge bg-light-info text-info">Minor</span>';
            else typeBadge = '<span class="badge bg-light-warning text-warning">Special</span>';

              let actionButtons = '<div class="btn-group">';
              
              if (PERMISSIONS.manage) {
                  actionButtons += `<button class="btn btn-sm btn-outline-primary edit-cat" data-id="${cat.id}"><i class="ti ti-edit"></i></button>`;
              }
              
              if (PERMISSIONS.delete) {
                  actionButtons += `<button class="btn btn-sm btn-outline-danger delete-cat" data-id="${cat.id}"><i class="ti ti-trash"></i></button>`;
              }
              
              actionButtons += '</div>';

              categoriesTable.row.add([
                `<span class="fw-semibold">${cat.name}</span>`,
                cat.competition_title,
                typeBadge,
                cat.level,
                cat.display_order,
                cat.weight_percent + '%',
                actionButtons
              ]);

            // Add data-labels for mobile row style
            const lastRow = categoriesTable.row(':last').node();
            $(lastRow).find('td').eq(0).attr('data-label', 'Category');
            $(lastRow).find('td').eq(1).attr('data-label', 'Competition');
            $(lastRow).find('td').eq(2).attr('data-label', 'Type');
            $(lastRow).find('td').eq(3).attr('data-label', 'Level');
            $(lastRow).find('td').eq(4).attr('data-label', 'Order');
            $(lastRow).find('td').eq(5).attr('data-label', 'Weight');
            $(lastRow).find('td').eq(6).attr('data-label', 'Action');
          });
          categoriesTable.draw();
        }
      });
    }

    // Initial loads
    loadCompetitions();
    loadCategories();

    // Add Category
    $('#addCategoryForm').on('submit', function(e) {
      e.preventDefault();
      let formData = $(this).serialize() + '&action=create';
      $.post('endpoints/categories_handler.php', formData, function(response) {
        if (response.status === 'success') {
          Swal.fire('Success', response.message, 'success');
          $('#addCategoryForm')[0].reset();
          loadCategories();
        } else {
          Swal.fire('Error', response.message, 'error');
        }
      });
    });

    // Edit Category - Populate Modal
    $(document).on('click', '.edit-cat', function() {
      let id = $(this).data('id');
      $.post('endpoints/categories_handler.php', { action: 'get', id: id }, function(response) {
        if (response.status === 'success') {
          $('#editCatId').val(response.data.id);
          $('#editCompName').val(response.data.competition_id);
          $('#editCatName').val(response.data.name);
          $('#editCatType').val(response.data.type);
          $('#editCatLevel').val(response.data.level);
          $('#editDisplayOrder').val(response.data.display_order);
          $('#editWeightPercent').val(response.data.weight_percent);
          $('#editCatDesc').val(response.data.description);
          $('#editCategoryModal').modal('show');
        }
      });
    });

    // Update Category
    $('#editCategoryForm').on('submit', function(e) {
      e.preventDefault();
      let formData = $(this).serialize() + '&action=update';
      $.post('endpoints/categories_handler.php', formData, function(response) {
        if (response.status === 'success') {
          Swal.fire('Updated', response.message, 'success');
          $('#editCategoryModal').modal('hide');
          loadCategories();
        } else {
          Swal.fire('Error', response.message, 'error');
        }
      });
    });

    // Delete Category
    $(document).on('click', '.delete-cat', function() {
      let id = $(this).data('id');
      Swal.fire({
        title: 'Are you sure?',
        text: "This will move the category to the trash.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
      }).then((result) => {
        if (result.isConfirmed) {
          $.post('endpoints/categories_handler.php', { action: 'delete', id: id }, function(response) {
            if (response.status === 'success') {
              Swal.fire('Deleted', response.message, 'success');
              loadCategories();
            } else {
              Swal.fire('Error', response.message, 'error');
            }
          });
        }
      });
    });
  });
</script>
</body>

</html>
