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
  <title>Criteria Weights - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Criteria Weights</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Criteria Weights</li>
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
                  <h5 class="mb-4">Select Context</h5>
                  <form>
                    <div class="mb-3">
                      <label for="selectCompetition" class="form-label">Competition</label>
                      <select class="form-select" id="selectCompetition">
                        <option value="">Select Competition</option>
                      </select>
                    </div>
                    <div class="mb-4">
                      <label for="selectCategory" class="form-label">Category</label>
                      <select class="form-select" id="selectCategory" disabled>
                        <option value="">Select Category</option>
                      </select>
                    </div>
                    <div class="d-grid">
                        <button type="button" class="btn btn-primary" id="loadCriteriaBtn">Load Criteria</button>
                    </div>
                  </form>
                </div>
              </div>
              
              <div class="card bg-success text-white" id="weightCard">
                  <div class="card-body text-center">
                      <h6 class="text-white opacity-75 text-uppercase fw-bold ls-1">Total Weight</h6>
                      <h1 class="text-white mb-0 display-4 fw-bold" id="totalWeightDisplay">100%</h1>
                      <div class="mt-2">
                          <span class="badge bg-white text-success fw-bold" id="statusBadge"><i class="ti ti-check me-1"></i>Valid</span>
                      </div>
                  </div>
              </div>

            </div>

            <div class="col-md-8">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                     <h5 class="mb-0">Manage Weights</h5>
                     <button class="btn btn-sm btn-outline-primary" id="addCriteriaBtn"><i class="ti ti-plus me-1"></i>Add Criteria</button>
                  </div>
                  
                  <div class="table-responsive">
                    <table class="table align-middle">
                      <thead class="bg-light">
                        <tr>
                          <th>Criteria Name</th>
                          <th style="width: 150px;">Weight (%)</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                      <tbody id="criteriaTableBody">
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted small">
                                Select competition and category to load criteria
                            </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>

                  <hr>
                  <div class="d-flex justify-content-end">
                      <button type="button" class="btn btn-success btn-lg px-4" id="saveWeightsBtn" disabled>Save Changes</button>
                  </div>

                </div>
              </div>
            </div>
          </div>

          <?php include 'components/footer.php'; ?>
        </div>
      </div>
    </div>

    <!-- Add Criteria Modal -->
    <div class="modal fade" id="addCriteriaModal" tabindex="-1" aria-labelledby="addCriteriaModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="addCriteriaModalLabel">Add New Criteria</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <form id="addCriteriaForm">
              <div class="mb-3">
                <label for="criteriaName" class="form-label">Criteria Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="criteriaName" required>
              </div>
              <div class="mb-3">
                <label for="criteriaWeight" class="form-label">Weight (%) <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="criteriaWeight" min="0" max="100" step="0.5" required>
                <small class="text-muted">Remaining: <span id="remainingWeight" class="fw-bold">0</span>%</small>
              </div>
              <div class="mb-3">
                <label for="criteriaOrder" class="form-label">Display Order <span class="text-danger">*</span></label>
                <input type="number" class="form-control" id="criteriaOrder" min="1" required>
              </div>
            </form>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-primary" id="saveCriteriaBtn">Save Criteria</button>
          </div>
        </div>
      </div>
    </div>

  </div>
  <?php include 'components/scripts.php'; ?>
  <script>
    $(document).ready(function() {
        let currentTotal = 0;
        let currentCategoryId = null;

        function calculateTotal() {
            let total = 0;
            $('.weight-input').each(function() {
                let val = parseFloat($(this).val()) || 0;
                total += val;
            });
            
            currentTotal = total;
            $('#totalWeightDisplay').text(total.toFixed(0) + '%');
            
            if(total === 100) {
                $('#statusBadge').removeClass('bg-danger text-white').addClass('bg-white text-success').html('<i class="ti ti-check me-1"></i>Valid');
                $('#weightCard').removeClass('bg-danger bg-primary').addClass('bg-success');
                $('#saveWeightsBtn').prop('disabled', false);
            } else {
                $('#statusBadge').removeClass('bg-white text-success bg-white text-primary').addClass('bg-danger text-white').html('<i class="ti ti-alert-circle me-1"></i>Invalid');
                $('#weightCard').removeClass('bg-success bg-primary').addClass('bg-danger');
                $('#saveWeightsBtn').prop('disabled', true);
                $('#addCriteriaBtn').prop('disabled', currentCategoryId === null);
            }
        }

        function loadCompetitions() {
            $.post('endpoints/criteria_handler.php', { action: 'get_competitions' }, function(response) {
                if (response.status === 'success') {
                    let html = '<option value="">Select Competition</option>';
                    response.data.forEach(comp => {
                        html += `<option value="${comp.id}">${comp.title}</option>`;
                    });
                    $('#selectCompetition').html(html);
                }
            });
        }

        $('#selectCompetition').change(function() {
            const compId = $(this).val();
            if (compId) {
                $('#selectCategory').prop('disabled', false).html('<option value="">Loading...</option>');
                $.post('endpoints/criteria_handler.php', { 
                    action: 'get_categories_by_comp', 
                    competition_id: compId 
                }, function(response) {
                    if (response.status === 'success') {
                        let html = '<option value="">Select Category</option>';
                        response.data.forEach(cat => {
                            html += `<option value="${cat.id}">${cat.name}</option>`;
                        });
                        $('#selectCategory').html(html);
                    }
                });
            } else {
                $('#selectCategory').prop('disabled', true).html('<option value="">Select Category</option>');
            }
        });

        $('#loadCriteriaBtn').click(function() {
            const catId = $('#selectCategory').val();
            if (!catId) {
                Swal.fire('Error', 'Please select a category first.', 'error');
                return;
            }

            currentCategoryId = catId;
            AuroraLoader.showOverlay('#main-wrapper', 'Fetching Criteria...');
            $.post('endpoints/criteria_handler.php', { 
                action: 'get_by_category', 
                category_id: catId 
            }, function(response) {
                AuroraLoader.hideOverlay('#main-wrapper');
                if (response.status === 'success') {
                    let html = '';
                    if (response.data.length === 0) {
                        html = '<tr><td colspan="3" class="text-center py-4">No criteria found for this category.</td></tr>';
                    } else {
                        response.data.forEach(crit => {
                            html += `
                                <tr>
                                    <td>
                                        <h6 class="mb-0 fw-semibold">${crit.name}</h6>
                                    </td>
                                    <td>
                                        <div class="input-group">
                                            <input type="number" class="form-control weight-input" 
                                               data-id="${crit.id}" 
                                               value="${crit.weight_percent}" 
                                               min="0" max="100" step="0.5">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-light-danger text-danger btn-delete-crit" data-id="${crit.id}">
                                            <i class="ti ti-trash"></i>
                                        </button>
                                    </td>
                                </tr>`;
                        });
                    }
                    $('#criteriaTableBody').html(html);
                    calculateTotal();
                }
            });
        });

        $(document).on('input', '.weight-input', calculateTotal);

        $('#saveWeightsBtn').click(function() {
            if (currentTotal !== 100) {
                Swal.fire('Invalid Total', 'Total weight must be exactly 100%.', 'warning');
                return;
            }

            const weights = [];
            $('.weight-input').each(function() {
                weights.push({
                    id: $(this).data('id'),
                    weight: $(this).val()
                });
            });

            AuroraLoader.showOverlay('#main-wrapper', 'Saving Weights...');
            $.post('endpoints/criteria_handler.php', {
                action: 'update_weights',
                weights: weights
            }, function(response) {
                AuroraLoader.hideOverlay('#main-wrapper');
                if (response.status === 'success') {
                    Swal.fire('Success', 'Criteria weights updated successfully.', 'success');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        });

        $(document).on('click', '.btn-delete-crit', function() {
            const id = $(this).data('id');
            const row = $(this).closest('tr');

            Swal.fire({
                title: 'Are you sure?',
                text: "This criterion will be moved to the archive.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.post('endpoints/criteria_handler.php', {
                        action: 'delete',
                        id: id
                    }, function(response) {
                        if (response.status === 'success') {
                            row.fadeOut(300, function() {
                                $(this).remove();
                                calculateTotal();
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    });
                }
            });
        });

        // Add Criteria Button
        $('#addCriteriaBtn').click(function() {
            if (!currentCategoryId) {
                Swal.fire('Error', 'Please load a category first.', 'error');
                return;
            }

            if (currentTotal >= 100) {
                Swal.fire({
                    icon: 'info',
                    title: 'Category Complete',
                    text: 'This category already has 100% weight allocated. You cannot add more criteria.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const remaining = 100 - currentTotal;
            $('#remainingWeight').text(remaining.toFixed(1));
            $('#criteriaWeight').attr('max', remaining).val(remaining);
            
            // Get next display order
            const maxOrder = Math.max(0, ...$('.weight-input').map(function() {
                return parseInt($(this).closest('tr').index()) + 1;
            }).get());
            $('#criteriaOrder').val(maxOrder + 1);
            
            $('#addCriteriaForm')[0].reset();
            $('#criteriaWeight').val(remaining);
            $('#criteriaOrder').val(maxOrder + 1);
            $('#addCriteriaModal').modal('show');
        });

        // Save New Criteria
        $('#saveCriteriaBtn').click(function() {
            const name = $('#criteriaName').val().trim();
            const weight = parseFloat($('#criteriaWeight').val());
            const order = parseInt($('#criteriaOrder').val());

            if (!name || isNaN(weight) || isNaN(order)) {
                Swal.fire('Error', 'Please fill all required fields.', 'error');
                return;
            }

            const remaining = 100 - currentTotal;
            if (weight > remaining) {
                Swal.fire('Error', `Weight cannot exceed remaining ${remaining.toFixed(1)}%.`, 'warning');
                return;
            }

            AuroraLoader.showOverlay('#main-wrapper', 'Adding Criteria...');
            $.post('endpoints/criteria_handler.php', {
                action: 'create',
                name: name,
                weight: weight,
                display_order: order,
                category_id: currentCategoryId,
                is_required: 'false'
            }, function(response) {
                AuroraLoader.hideOverlay('#main-wrapper');
                if (response.status === 'success') {
                    $('#addCriteriaModal').modal('hide');
                    Swal.fire('Success', 'Criteria added successfully.', 'success');
                    // Reload criteria
                    $('#loadCriteriaBtn').click();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        });

        // Initial Load
        loadCompetitions();
    });
  </script>
</body>
</html>
