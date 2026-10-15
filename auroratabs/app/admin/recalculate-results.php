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
  <title>Recalculate Results - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Recalculate Results</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Recalculate Results</li>
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
            <div class="col-md-5">
              <div class="card">
                <div class="card-body">
                  <h5 class="mb-4">Manual Trigger</h5>
                  <p class="text-muted mb-4 small">Use this tool to force a recalculation of scores if you notice discrepancies or after modifying formulas/weights retrospectively.</p>
                  
                  <form id="recalculateForm">
                      <div class="mb-3">
                          <label for="selectCompetition" class="form-label">Select Competition</label>
                          <select class="form-select" id="selectCompetition" required>
                            <option value="">Loading...</option>
                          </select>
                      </div>
                      
                      <div class="alert alert-warning" role="alert">
                          <div class="d-flex">
                            <i class="ti ti-alert-triangle me-2 fs-4"></i>
                            <div class="small">Warning: This action will overwrite existing Calculated Final Scores for the selected competition. Validate weights before proceeding.</div>
                          </div>
                      </div>

                      <button type="submit" class="btn btn-primary w-100 py-2">
                          <i class="ti ti-refresh me-2"></i> Recalculate Now
                      </button>
                  </form>
                </div>
              </div>
            </div>

            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0">Recalculation History</h5>
                            <button class="btn btn-sm btn-outline-secondary" id="refreshHistoryBtn"><i class="ti ti-reload"></i></button>
                        </div>
                         <div class="table-responsive">
                            <table id="recalculateHistoryTable" class="table table-sm align-middle table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Competition</th>
                                        <th>Date & Time</th>
                                        <th>Triggered By</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- AJAX -->
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
      // 1. Initialize DataTable
      var historyTable = $('#recalculateHistoryTable').DataTable({
        "responsive": true,
        "pageLength": 10,
        "order": [[ 1, "desc" ]],
        "dom": 'rtp',
        "language": {
          "emptyTable": "No history found."
        }
      });

      // 2. Load Competitions
      function loadCompetitions() {
        $.post('endpoints/recalculate_handler.php', { action: 'get_competitions' }, function(response) {
            if(response.status === 'success') {
                let options = '<option value="">Choose competition...</option>';
                response.data.forEach(comp => {
                    options += `<option value="${comp.id}">${comp.title}</option>`;
                });
                $('#selectCompetition').html(options);
            } else {
                Swal.fire('Error', 'Failed to load competitions', 'error');
            }
        });
      }

      // 3. Load History
      function loadHistory(compId = 0) {
        historyTable.clear().draw(); // Clear first
        $.post('endpoints/recalculate_handler.php', { action: 'get_history', competition_id: compId }, function(response) {
            if(response.status === 'success') {
                response.data.forEach(log => {
                    let statusBadge = log.status === 'success' 
                        ? '<span class="badge bg-light-success text-success">Success</span>' 
                        : '<span class="badge bg-light-danger text-danger">Failed</span>';
                    
                    if (log.status === 'failed') {
                        statusBadge += ` <i class="ti ti-info-circle text-danger ms-1" data-bs-toggle="tooltip" title="${log.details}"></i>`;
                    }

                    historyTable.row.add([
                        `<span class="fw-semibold">${log.competition_title}</span>`,
                        log.created_at,
                        log.triggered_by_name || 'System',
                        statusBadge
                    ]);
                });
                historyTable.draw();
                $('[data-bs-toggle="tooltip"]').tooltip();
            }
        });
      }

      // Initial Load
      loadCompetitions();
      loadHistory();

      // Refresh Listener
      $('#refreshHistoryBtn').click(function() {
          loadHistory();
      });

      // Filter History by Competition Select
      $('#selectCompetition').change(function() {
          loadHistory($(this).val());
      });

      // 4. Handle Submit
      $('#recalculateForm').on('submit', function(e) {
          e.preventDefault();
          const compId = $('#selectCompetition').val();
          if(!compId) {
              Swal.fire('Required', 'Please select a competition first.', 'warning');
              return;
          }

          Swal.fire({
            title: 'Are you sure?',
            text: "This will recalculate all scores based on current weights and judge inputs. Existing final rankings will be updated.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#5d87ff',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Recalculate!'
          }).then((result) => {
            if (result.isConfirmed) {
                // Show Loading
                Swal.fire({
                    title: 'Recalculating...',
                    text: 'Please wait while we crunch the numbers.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.post('endpoints/recalculate_handler.php', { 
                    action: 'recalculate', 
                    competition_id: compId 
                }, function(response) {
                    if(response.status === 'success') {
                        Swal.fire('Success!', response.message, 'success');
                        loadHistory(compId);
                    } else {
                        Swal.fire('Calculation Failed', response.message, 'error');
                        loadHistory(compId);
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Server Error', 'error');
                });
            }
          });
      });
    });
  </script>
</body>
</html>
