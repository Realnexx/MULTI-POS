
<?php
    session_start();
    
    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
?><!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Judge Locks - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Judge Locks</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Judge Locks</li>
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
                     <h5 class="mb-2 mb-md-0">Lock Management</h5>
                     <div class="d-flex gap-2">
                         <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterComp" data-bs-toggle="dropdown" aria-expanded="false">
                              Competition: All
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterComp">
                              <li><a class="dropdown-item" href="#">All</a></li>
                              <li><a class="dropdown-item" href="#">Matahom sa Ubay 2026</a></li>
                            </ul>
                          </div>
                          <div class="d-flex align-items-center gap-2" id="unlockLimitWrapper" style="display:none !important;">
                              <span class="fs-2 fw-semibold text-muted text-uppercase">Limit:</span>
                              <input type="number" id="unlockLimitInput" class="form-control form-control-sm text-center fw-bold" style="width: 50px;" min="0" value="3">
                              <button class="btn btn-sm btn-primary" id="saveLimitBtn" title="Save Limit"><i class="ti ti-device-floppy"></i></button>
                          </div>
                          <button class="btn btn-sm btn-outline-primary" id="lockAllBtn"><i class="ti ti-lock me-2"></i>Lock All Judges</button>
                     </div>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="judgeLocksTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Competition</th>
                          <th>Category</th>
                          <th>Judge</th>
                          <th>Status</th>
                          <th>Unlocks</th>
                          <th>Last Updated</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="judgeLocksTableBody">
                         <!-- Dynamically populated via AJAX -->
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
      let currentFilterComp = 0;
      const judgeLocksTable = $('#judgeLocksTable').DataTable({
        "responsive": true,
        "ordering": false,
        "dom": '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search locks..."
        }
      });

      function loadCompetitions() {
        $.post('endpoints/judge_submissions_handler.php', { action: 'get_competitions' }, function(response) {
          if (response.status === 'success') {
            let html = '<li><a class="dropdown-item filter-comp" data-id="0" data-title="All" href="#">All</a></li>';
            response.data.forEach(comp => {
              html += `<li><a class="dropdown-item filter-comp" data-id="${comp.id}" data-title="${comp.title}" href="#">${comp.title}</a></li>`;
            });
            $('#filterComp').next('.dropdown-menu').html(html);
          }
        });
      }

      function loadLocks(compId = 0) {
        AuroraLoader.showOverlay('#main-wrapper', 'Fetching Lock Status...');
        $.post('endpoints/judge_submissions_handler.php', { 
          action: 'list_locks', 
          competition_id: compId 
        }, function(response) {
          AuroraLoader.hideOverlay('#main-wrapper');
          if (response.status === 'success') {
            judgeLocksTable.clear();
            response.data.forEach(lock => {
              let statusBadge = '';
              let actionBtn = '';

              if (lock.is_category_locked > 0) {
                statusBadge = '<span class="badge bg-light-danger text-danger"><i class="ti ti-lock me-1"></i>Fully Locked</span>';
                actionBtn = `<button class="btn btn-sm btn-outline-primary unlock-cat" 
                                data-judge="${lock.judge_id}" 
                                data-comp="${lock.competition_id}" 
                                data-cat="${lock.category_id}" 
                                title="Unlock Entire Category">
                                <i class="ti ti-lock-open me-1"></i> Unlock
                             </button>`;
              } else if (lock.locked_candidates_count > 0) {
                statusBadge = `<span class="badge bg-light-warning text-warning"><i class="ti ti-alert-circle me-1"></i>Partial (${lock.locked_candidates_count}/${lock.total_candidates_count})</span>`;
                actionBtn = `<div class="btn-group">
                                <button class="btn btn-sm btn-outline-danger lock-cat" 
                                  data-judge="${lock.judge_id}" 
                                  data-comp="${lock.competition_id}" 
                                  data-cat="${lock.category_id}" 
                                  title="Lock Entire Category">
                                  <i class="ti ti-lock me-1"></i> Full Lock
                                </button>
                                <button class="btn btn-sm btn-outline-primary unlock-cat border-start-0" 
                                  data-judge="${lock.judge_id}" 
                                  data-comp="${lock.competition_id}" 
                                  data-cat="${lock.category_id}" 
                                  title="Unlock All Candidates">
                                  <i class="ti ti-lock-open me-1"></i> Unlock
                                </button>
                             </div>`;
              } else {
                statusBadge = '<span class="badge bg-light-success text-success"><i class="ti ti-lock-open me-1"></i>Open</span>';
                actionBtn = `<button class="btn btn-sm btn-outline-danger lock-cat" 
                                data-judge="${lock.judge_id}" 
                                data-comp="${lock.competition_id}" 
                                data-cat="${lock.category_id}" 
                                title="Lock Category">
                                <i class="ti ti-lock me-1"></i> Lock
                             </button>`;
              }

              const lastUpdated = lock.last_updated ? new Date(lock.last_updated).toLocaleString() : '--';
              const unlockCount = parseInt(lock.unlock_count || 0);
              let unlockBadge = '';
              if (unlockCount === 0) {
                unlockBadge = `<span class="badge bg-light-secondary text-muted">0 times</span>`;
              } else if (unlockCount <= 2) {
                unlockBadge = `<span class="badge bg-light-warning text-warning fw-bold">${unlockCount}x</span>`;
              } else {
                unlockBadge = `<span class="badge bg-light-danger text-danger fw-bold">${unlockCount}x <i class="ti ti-alert-triangle"></i></span>`;
              }

              judgeLocksTable.row.add([
                `<strong>${lock.competition_title}</strong>`,
                lock.category_name,
                `<div class="d-flex align-items-center">
                    <img src="${lock.judge_avatar || 'src/assets/images/profile/user-default.jpg'}" width="35" class="rounded-circle me-2" alt="" />
                    <div>
                      <h6 class="mb-0 fw-semibold">${lock.judge_name}</h6>
                      <p class="fs-2 mb-0 text-muted">@${lock.judge_user}</p>
                    </div>
                </div>`,
                statusBadge,
                unlockBadge,
                lastUpdated,
                actionBtn
              ]);
            });
            judgeLocksTable.draw();
          }
        });
      }

      // Initial Load
      loadCompetitions();
      loadLocks();

      // Filter Competition
      $(document).on('click', '.filter-comp', function(e) {
        e.preventDefault();
        currentFilterComp = $(this).data('id');
        $('#filterComp').text(`Competition: ${$(this).data('title')}`);
        
        if (currentFilterComp > 0) {
            $('#unlockLimitWrapper').attr('style', 'display: flex !important');
            fetchUnlockLimit(currentFilterComp);
        } else {
            $('#unlockLimitWrapper').attr('style', 'display: none !important');
        }
        
        loadLocks(currentFilterComp);
      });

      function fetchUnlockLimit(compId) {
          $.post('endpoints/judge_submissions_handler.php', {
              action: 'get_unlock_limit',
              competition_id: compId
          }, function(response) {
              if (response.status === 'success') {
                  $('#unlockLimitInput').val(response.unlock_limit);
              }
          });
      }

      $('#saveLimitBtn').on('click', function() {
          const limit = $('#unlockLimitInput').val();
          if (currentFilterComp <= 0) return;

          $(this).prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');

          $.post('endpoints/judge_submissions_handler.php', {
              action: 'update_unlock_limit',
              competition_id: currentFilterComp,
              unlock_limit: limit
          }, (response) => {
              $(this).prop('disabled', false).html('<i class="ti ti-device-floppy"></i>');
              if (response.status === 'success') {
                  Swal.fire({
                      toast: true,
                      position: 'top-end',
                      icon: 'success',
                      title: response.message,
                      showConfirmButton: false,
                      timer: 1500
                  });
              } else {
                  Swal.fire('Error', response.message, 'error');
              }
          });
      });

      // Unlock Category
      $(document).on('click', '.unlock-cat', function() {
        const btn = $(this);
        Swal.fire({
          title: 'Unlock Category?',
          text: "All candidates in this category will be editable for the judge.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Yes, Unlock!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/judge_submissions_handler.php', {
              action: 'unlock_category',
              judge_id: btn.data('judge'),
              competition_id: btn.data('comp'),
              category_id: btn.data('cat')
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Unlocked', response.message, 'success');
                loadLocks(currentFilterComp);
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            });
          }
        });
      });

      // Lock Category
      $(document).on('click', '.lock-cat', function() {
        const btn = $(this);
        Swal.fire({
          title: 'Lock Category?',
          text: "The judge will NOT be able to change any scores for this category.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          confirmButtonText: 'Yes, Lock!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/judge_submissions_handler.php', {
              action: 'lock',
              judge_id: btn.data('judge'),
              competition_id: btn.data('comp'),
              category_id: btn.data('cat')
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Locked', response.message, 'success');
                loadLocks(currentFilterComp);
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            });
          }
        });
      });

      // Lock All for Competition
      $('#lockAllBtn').click(function() {
        if (currentFilterComp === 0) {
          Swal.fire('Wait', 'Please select a specific competition first to use Lock All.', 'info');
          return;
        }

        Swal.fire({
          title: 'Lock All Judges?',
          text: "This will lock ALL judges and categories for the selected competition.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          confirmButtonText: 'Yes, Lock All!'
        }).then((result) => {
          if (result.isConfirmed) {
            // I'll need a backend action for this too, or loop. Better backend.
            // Let's assume I add 'lock_all_comp'
            $.post('endpoints/judge_submissions_handler.php', {
              action: 'lock_all_comp',
              competition_id: currentFilterComp
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Locked All', response.message, 'success');
                loadLocks(currentFilterComp);
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
