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
  <title>Judge Submissions - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Judge Submissions</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Judge Submissions</li>
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
                     <h5 class="mb-2 mb-md-0">Submission Log</h5>
                     <div class="d-flex gap-2">
                          <div class="dropdown">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" id="filterCompBtn" data-bs-toggle="dropdown" aria-expanded="false">
                              Competition: All
                            </button>
                            <ul class="dropdown-menu" id="filterCompMenu" aria-labelledby="filterCompBtn">
                              <li><a class="dropdown-item filter-comp" href="#" data-id="0">All</a></li>
                              <!-- Competitions loaded via AJAX -->
                            </ul>
                          </div>
                          <button id="clearAllLocks" class="btn btn-sm btn-outline-danger" title="Unlock All for Selected"><i class="ti ti-trash"></i></button>
                     </div>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="judgeSubmissionsTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Judge</th>
                          <th>Competition</th>
                          <th>Candidate</th>
                          <th>Score</th>
                          <th>Status</th>
                          <th>Time Submitted</th>
                          <th>Action</th>
                        </tr>
                      </thead>
                       <tbody id="judgeSubmissionsTableBody">
                          <!-- Loaded via AJAX -->
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
      let judgeSubmissionsTable = $('#judgeSubmissionsTable').DataTable({
        "responsive": false,
        "dom": '<"row mb-3"<"col-12"f>><"row mb-3"<"col-12"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search submissions..."
        }
      });

      function loadCompetitions() {
        $.post('endpoints/judge_submissions_handler.php', { action: 'get_competitions' }, function(response) {
          if (response.status === 'success') {
            let html = '<li><a class="dropdown-item filter-comp" href="#" data-id="0" data-title="All">All</a></li>';
            response.data.forEach(comp => {
              html += `<li><a class="dropdown-item filter-comp" href="#" data-id="${comp.id}" data-title="${comp.title}">${comp.title}</a></li>`;
            });
            $('#filterCompMenu').html(html);
          }
        });
      }

      function loadSubmissions(compId = 0) {
        $.post('endpoints/judge_submissions_handler.php', { action: 'list', competition_id: compId }, function(response) {
          if (response.status === 'success') {
            judgeSubmissionsTable.clear();
            response.data.forEach(sub => {
              const statusBadge = sub.status === 'Submitted' 
                ? '<span class="badge bg-light-success text-success"><i class="ti ti-lock me-1"></i>Submitted</span>' 
                : '<span class="badge bg-light-warning text-warning"><i class="ti ti-clock me-1"></i>Pending</span>';
              
              const scoreText = sub.total_score > 0 ? `<span class="fw-bold fs-4">${parseFloat(sub.total_score).toFixed(2)}</span>` : '<span class="fw-bold fs-4">--</span>';
              const timeText = sub.time_submitted || '--';

              const actions = `
                <div class="btn-group">
                  <button class="btn btn-sm btn-outline-warning unlock-sub" title="Unlock/Reset" 
                    data-status="${sub.status}"
                    data-judge="${sub.judge_id}" 
                    data-comp="${sub.competition_id}" 
                    data-cat="${sub.category_id}" 
                    data-cand="${sub.candidate_id}"
                    data-judge-name="${sub.judge_name}">
                    <i class="ti ti-lock-open" style="pointer-events: none;"></i>
                  </button>
                </div>
              `;

              judgeSubmissionsTable.row.add([
                `<div class="d-flex align-items-center">
                    <img src="${sub.judge_avatar || 'src/assets/images/profile/user-default.jpg'}" width="35" class="rounded-circle me-2" alt="" />
                    <div>
                      <h6 class="mb-0 fw-semibold">${sub.judge_name}</h6>
                      <p class="fs-2 mb-0 text-muted">${sub.judge_role}</p>
                    </div>
                </div>`,
                `<div>
                  <div class="fw-semibold">${sub.competition_title}</div>
                  <small class="text-muted">${sub.category_name}</small>
                </div>`,
                `<div class="d-flex align-items-center">
                    <span class="badge bg-light-primary text-primary me-2">#${sub.candidate_number}</span>
                    <h6 class="mb-0 fw-semibold">${sub.candidate_name}</h6>
                 </div>`,
                scoreText,
                statusBadge,
                timeText,
                actions
              ]);

              // Add data-labels for mobile row style
              const lastRow = judgeSubmissionsTable.row(':last').node();
              if (lastRow) {
                $(lastRow).find('td').eq(0).attr('data-label', 'Judge');
                $(lastRow).find('td').eq(1).attr('data-label', 'Competition');
                $(lastRow).find('td').eq(2).attr('data-label', 'Candidate');
                $(lastRow).find('td').eq(3).attr('data-label', 'Score');
                $(lastRow).find('td').eq(4).attr('data-label', 'Status');
                $(lastRow).find('td').eq(5).attr('data-label', 'Time Submitted');
                $(lastRow).find('td').eq(6).attr('data-label', 'Action');
              }
            });
            judgeSubmissionsTable.draw();
          }
        });
      }

      loadCompetitions();
      loadSubmissions();

      // Filter by Competition
      $(document).on('click', '.filter-comp', function(e) {
        e.preventDefault();
        currentFilterComp = $(this).data('id');
        const title = $(this).data('title');
        $('#filterCompBtn').text(`Competition: ${title}`);
        loadSubmissions(currentFilterComp);
      });

      // Unlock Submission
      $(document).on('click', '.unlock-sub', function() {
        const btn = $(this);
        const status = btn.data('status');

        if (status === 'Pending') {
          Swal.fire('Not Locked', 'This submission is already pending and does not need to be unlocked.', 'info');
          return;
        }

        const judgeId = btn.data('judge');
        const compId = btn.data('comp');
        const catId = btn.data('cat');
        const candId = btn.data('cand');

        Swal.fire({
          title: 'Unlock candidate scores?',
          text: "The judge will be able to edit and re-submit scores for this specific candidate.",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#ffc107',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, Unlock!'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/judge_submissions_handler.php', { 
              action: 'unlock', 
              judge_id: judgeId, 
              competition_id: compId,
              category_id: catId,
              candidate_id: candId
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Unlocked', response.message, 'success');
                loadSubmissions(currentFilterComp);
              } else {
                Swal.fire('Error', response.message, 'error');
              }
            });
          }
        });
      });

      // Clear All Locks for Competition
      $('#clearAllLocks').on('click', function() {
        if (currentFilterComp === 0) {
          Swal.fire('Filter Required', 'Please select a specific competition first to unlock all scores.', 'info');
          return;
        }

        Swal.fire({
          title: 'Unlock ALL scores?',
          text: "All judges in this competition will be unlocked. This cannot be undone!",
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#3085d6',
          confirmButtonText: 'Yes, Unlock All'
        }).then((result) => {
          if (result.isConfirmed) {
            $.post('endpoints/judge_submissions_handler.php', { 
              action: 'clear_all', 
              competition_id: currentFilterComp
            }, function(response) {
              if (response.status === 'success') {
                Swal.fire('Cleared', response.message, 'success');
                loadSubmissions(currentFilterComp);
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
