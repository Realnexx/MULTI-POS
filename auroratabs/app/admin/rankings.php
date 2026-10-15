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
  <title>Detailed Rankings - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Detailed Rankings</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Rankings</li>
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
              <!-- Sidebar Filter -->
            <div class="col-md-4 col-lg-3">
              <div class="card">
                <div class="card-body">
                  <h5 class="mb-4">Option Filter</h5>
                  <form id="filterForm">
                    <div class="mb-3">
                      <label for="selectCompetition" class="form-label">Competition</label>
                      <select class="form-select" id="selectCompetition">
                        <!-- Dynamic -->
                      </select>
                    </div>
                    <div class="mb-3">
                      <label for="selectCategory" class="form-label">Category</label>
                      <select class="form-select" id="selectCategory">
                        <!-- Dynamic -->
                      </select>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="btnViewResults">View Results</button>
                        <hr>
                        <a href="#" id="btnPrintResult" class="btn btn-outline-success"><i class="ti ti-printer me-1"></i> Print Official Result</a>
                    </div>
                  </form>
                </div>
              </div>
              
              <div class="card bg-light-warning" id="statusCard" style="display:none;">
                  <div class="card-body">
                      <h6 class="fw-semibold text-warning" id="statusTitle"><i class="ti ti-alert-circle me-1"></i>Status: Pending</h6>
                      <p class="mb-0 small" id="statusText">Judges are still submitting scores.</p>
                  </div>
              </div>
            </div>

            <!-- Detailed Table -->
            <div class="col-md-8 col-lg-9">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                     <div>
                         <h5 class="mb-0">Tabulation Breakdown</h5>
                         <small class="text-muted" id="categoryNameDisplay">Detailed Category Breakdown</small>
                     </div>
                     <div id="statsBadge">
                         <!-- Dynamic Badges -->
                     </div>
                  </div>
                  
                  <!-- Loading State -->
                  <div id="loadingState" class="text-center py-5" style="display:none;">
                      <div class="spinner-border text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="mt-2">Fetching rankings...</p>
                  </div>

                  <!-- Empty State -->
                  <div id="emptyState" class="text-center py-5" style="display:none;">
                      <i class="ti ti-database-off fs-8 text-muted"></i>
                      <p class="mt-2">No results found for this selection.</p>
                  </div>

                  <div class="table-responsive" id="tableContainer" style="display:none;">
                    <table class="table align-middle table-hover table-bordered table-striped" id="rankingsTable">
                      <thead class="bg-light text-center" id="tableHead">
                        <!-- Dynamic -->
                      </thead>
                      <tbody id="tableBody">
                        <!-- Dynamic -->
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
    let currentCompId = null;
    let currentCatId = null;

    // 1. Initial Load of Competitions
    function loadCompetitions() {
        $.post('endpoints/rankings_handler.php', { action: 'get_competitions' }, function(response) {
            if (response.status === 'success') {
                let options = '<option value="">Select Competition</option>';
                response.data.forEach(comp => {
                    options += `<option value="${comp.id}">${comp.title}</option>`;
                });
                $('#selectCompetition').html(options);
            }
        });
    }

    loadCompetitions();

    // 2. Load Categories on Competition Change
    $('#selectCompetition').change(function() {
        currentCompId = $(this).val();
        if (currentCompId) {
            $.post('endpoints/rankings_handler.php', { action: 'get_categories', competition_id: currentCompId }, function(response) {
                if (response.status === 'success') {
                    let options = '<option value="">Select Category</option>';
                    response.data.forEach(cat => {
                        options += `<option value="${cat.id}">${cat.name}</option>`;
                    });
                    $('#selectCategory').html(options);
                    // Clear secondary data
                    $('#tableContainer, #statusCard, #statsBadge').hide();
                    $('#emptyState').show();
                }
            });
        } else {
            $('#selectCategory').html('<option value="">Select Category</option>');
        }
    });

    // 3. View Results Action
    $('#filterForm').submit(function(e) {
        e.preventDefault();
        const compId = $('#selectCompetition').val();
        const catId = $('#selectCategory').val();
        const catName = $('#selectCategory option:selected').text();

        if (!compId || !catId) {
            Swal.fire('Error', 'Please select both competition and category', 'error');
            return;
        }

        currentCompId = compId;
        currentCatId = catId;
        
        $('#categoryNameDisplay').text(catName);
        $('#loadingState').show();
        $('#tableContainer, #emptyState, #statusCard').hide();

        $.post('endpoints/rankings_handler.php', { 
            action: 'get_rankings', 
            competition_id: compId,
            category_id: catId
        }, function(response) {
            $('#loadingState').hide();

            if (response.status === 'success' && response.data.length > 0) {
                renderTable(response);
                renderStatus(response.is_verified);
                renderBadges(response.judges.length);
                $('#tableContainer, #statusCard').fadeIn();
                
                // Update Print Link
                $('#btnPrintResult').off('click').on('click', function(e) {
                    e.preventDefault();
                    const url = `print-category-results.php?competition_id=${compId}&category_id=${catId}`;
                    window.open(url, '_blank', 'width=1000,height=900,scrollbars=yes,resizable=yes');
                });
            } else {
                $('#emptyState').fadeIn();
            }
        });
    });

    function renderTable(response) {
        const judges = response.judges;
        const data = response.data;

        // Render Head
        let headHtml = `
            <tr>
                <th rowspan="2" class="align-middle" style="width: 50px;">Rank</th>
                <th rowspan="2" class="align-middle">Candidate</th>
                <th colspan="${judges.length}">Judges</th>
                <th rowspan="2" class="align-middle bg-light-success text-success fw-bold">Average</th>
            </tr>
            <tr>
                ${judges.map(j => `<th class="small text-muted" title="${j.name}">J${j.id}</th>`).join('')}
            </tr>
        `;
        $('#tableHead').html(headHtml);

        // Render Body
        let bodyHtml = '';
        data.forEach(item => {
            let avatarClass = 'bg-light-secondary text-secondary';
            if(item.rank === 1) avatarClass = 'bg-warning text-white';
            else if(item.rank === 2) avatarClass = 'bg-secondary text-white';
            else if(item.rank === 3) avatarClass = 'bg-light-danger text-danger';

            bodyHtml += `
                <tr>
                    <td class="text-center fw-bold fs-4">${item.rank}</td>
                    <td>
                        <div class="d-flex align-items-center">
                            <span class="avatar avatar-xs rounded-circle ${avatarClass} me-2">${item.number}</span>
                            <div>
                                <h6 class="mb-0 fw-semibold">${item.name}</h6>
                            </div>
                        </div>
                    </td>
                    ${item.judge_scores.map(s => `<td class="text-center">${parseFloat(s).toFixed(2)}</td>`).join('')}
                    <td class="text-center fw-bold text-success fs-4">${item.average_formatted}</td>
                </tr>
            `;
        });
        $('#tableBody').html(bodyHtml);
    }

    function renderStatus(isVerified) {
        const title = isVerified ? 'Status: Verified' : 'Status: Pending';
        const text = isVerified ? 'All judges have submitted their scores. Results are ready.' : 'Judges are still submitting or results are not yet locked.';
        const badgeClass = isVerified ? 'bg-light-success text-success' : 'bg-light-warning text-warning';
        
        $('#statusCard').removeClass('bg-light-warning bg-light-success').addClass(isVerified ? 'bg-light-success' : 'bg-light-warning');
        $('#statusTitle').removeClass('text-warning text-success').addClass(isVerified ? 'text-success' : 'text-warning').html(`<i class="ti ti-alert-circle me-1"></i>${title}`);
        $('#statusText').text(text);
    }

    function renderBadges(judgeCount) {
        $('#statsBadge').html(`
            <span class="badge bg-light-primary text-primary">${judgeCount} Judges</span>
            <span class="badge bg-light-info text-info">Average Calculation</span>
        `);
    }
});
</script>
</body>
</html>

