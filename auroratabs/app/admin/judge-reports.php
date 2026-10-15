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
  <title>Judge Analytics - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Judge Analytics</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Reports</li>
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
          
           <!-- Filters -->
          <div class="card mb-4 shadow-sm border-0">
              <div class="card-body">
                  <div class="row g-3">
                      <div class="col-md-5">
                          <label class="form-label fw-semibold text-muted">Competition</label>
                          <select class="form-select" id="compFilter">
                              <option value="">All Competitions</option>
                          </select>
                      </div>
                       <div class="col-md-5">
                          <label class="form-label fw-semibold text-muted">Category / Criteria Group</label>
                          <select class="form-select" id="catFilter">
                              <option value="">All Categories</option>
                          </select>
                      </div>
                      <div class="col-md-2 d-flex align-items-end">
                          <button class="btn btn-primary w-100" id="btnFilter"><i class="ti ti-refresh me-1"></i> Refresh</button>
                      </div>
                  </div>
              </div>
          </div>

          <!-- Judge Performance Cards -->
          <div class="row" id="judgeCardsContainer">
              <!-- Loading Placeholder -->
              <div class="col-12 text-center py-5">
                  <div class="spinner-border text-primary" role="status"></div>
                  <p class="mt-2 text-muted">Analyzing judge performance...</p>
              </div>
          </div>

          <!-- Charts Section -->
          <div class="row">
               <div class="col-12">
                   <div class="card shadow-sm border-0">
                       <div class="card-body">
                           <h5 class="card-title fw-semibold">Scoring Trends</h5>
                           <p class="card-subtitle mb-4">Comparison of total scores given by each judge across candidates</p>
                           <div id="judge-chart" style="height: 350px;"></div>
                       </div>
                   </div>
               </div>
          </div>

          <!-- Audit Log Table -->
          <div class="card w-100 shadow-sm border-0 mt-4">
            <div class="card-body p-4">
              <div class="row align-items-center mb-4 g-3">
                  <div class="col-md-3">
                      <h5 class="card-title fw-semibold mb-0">Score Submission Log</h5>
                  </div>
                  <div class="col-md-5">
                      <select class="form-select form-select-sm" id="logCompFilter">
                          <option value="">All Competitions</option>
                      </select>
                  </div>
                  <div class="col-md-4 d-flex gap-2 justify-content-end">
                       <button class="btn btn-outline-success btn-sm" id="btnExportCSV"><i class="ti ti-file-download me-1"></i>Export CSV</button>
                       <button class="btn btn-outline-primary btn-sm" id="btnPrintLog"><i class="ti ti-printer me-1"></i>Print</button>
                  </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-striped table-responsive-stack" id="auditTable">
                  <thead>
                    <tr>
                      <th class="fw-semibold">Time</th>
                      <th class="fw-semibold">Judge</th>
                      <th class="fw-semibold">Candidate</th>
                      <th class="fw-semibold">Criteria</th>
                      <th class="fw-semibold">Score</th>
                      <th class="fw-semibold">Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- Loaded via AJAX -->
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
  <!-- ApexCharts is included in components/scripts.php -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
        let chart = null;
        let auditTable = null;

        function initChart() {
            var options = {
                series: [],
                chart: { height: 350, type: 'line', zoom: { enabled: false }, toolbar: { show: false }, fontFamily: 'inherit' },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                colors: ['#5D87FF', '#28a745', '#fa896b', '#ffae1f', '#539bff', '#13deb9'], 
                xaxis: { categories: [] },
                tooltip: { theme: 'dark' },
                noData: { text: "No scoring data available for selection." }
            };
            chart = new ApexCharts(document.querySelector("#judge-chart"), options);
            chart.render();
        }

        function loadCompetitions() {
            $.ajax({
                url: 'endpoints/judge_reports_handler.php',
                method: 'POST',
                data: { action: 'get_active_competitions' },
                success: function(response) {
                    if (response.status === 'success') {
                        const select = $('#compFilter');
                        const logSelect = $('#logCompFilter');
                        response.data.forEach(comp => {
                            select.append(`<option value="${comp.id}">${comp.title}</option>`);
                            logSelect.append(`<option value="${comp.id}">${comp.title}</option>`);
                        });
                    }
                },
                error: (xhr) => console.error("Filter Load Error:", xhr.responseText)
            });
        }

        function loadLog() {
            const compId = $('#logCompFilter').val();
            $.ajax({
                url: 'endpoints/judge_reports_handler.php',
                method: 'POST',
                data: { action: 'get_reports', competition_id: compId, category_id: '' },
                success: function(response) {
                    if (response.status !== 'success') return;
                    if ($.fn.DataTable.isDataTable('#auditTable')) {
                        $('#auditTable').DataTable().destroy();
                    }
                    const tbody = $('#auditTable tbody').empty();
                    if (response.logs) {
                        response.logs.forEach(log => {
                            tbody.append(`
                                <tr>
                                    <td class="text-center">${log.time}</td>
                                    <td><span class="fw-semibold">${log.judge}</span></td>
                                    <td>${log.candidate}</td>
                                    <td>${log.criteria}</td>
                                    <td class="text-center fw-bold text-dark">${log.score}</td>
                                    <td class="text-center"><span class="badge bg-light-success text-success">${log.status}</span></td>
                                </tr>
                            `);
                        });
                    }
                    auditTable = $('#auditTable').DataTable({
                        "order": [[ 0, "desc" ]],
                        "pageLength": 10,
                        "language": { "search": "Search Log:", "emptyTable": "No scoring records found." }
                    });
                }
            });
        }

        function loadCategories(compId) {
            const select = $('#catFilter').empty().append('<option value="">All Categories</option>');
            if (!compId) return;
            $.ajax({
                url: 'endpoints/judge_reports_handler.php',
                method: 'POST',
                data: { action: 'get_categories', competition_id: compId },
                success: function(response) {
                    if (response.status === 'success') {
                        response.data.forEach(cat => {
                            select.append(`<option value="${cat.id}">${cat.name}</option>`);
                        });
                    }
                }
            });
        }

        function loadReports() {
            const compId = $('#compFilter').val();
            const catId = $('#catFilter').val();
            
            $('#judgeCardsContainer').html('<div class="col-12 text-center py-5"><div class="spinner-border text-primary"></div><p class="mt-2 text-muted">Filtering analytics data...</p></div>');

            $.ajax({
                url: 'endpoints/judge_reports_handler.php',
                method: 'POST',
                data: { action: 'get_reports', competition_id: compId, category_id: catId },
                success: function(response) {
                    if (response.status === 'success') {
                        // 1. Judge Cards
                        const container = $('#judgeCardsContainer').empty();
                        if (!response.judges || response.judges.length === 0) {
                            container.append('<div class="col-12 text-center py-4"><p class="text-muted mb-0">No judge statistics found for this selection.</p></div>');
                        } else {
                            response.judges.forEach(judge => {
                                container.append(`
                                    <div class="col-md-4">
                                        <div class="card border-bottom border-${judge.color} border-4 card-hover shadow-sm border-0">
                                            <div class="card-body p-4">
                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                    <h5 class="fw-semibold mb-0">${judge.name}</h5>
                                                    <span class="badge bg-light-${judge.color} text-${judge.color}">${judge.label}</span>
                                                </div>
                                                <div class="d-flex align-items-center mb-3">
                                                    <div class="p-2 bg-light-primary rounded-circle me-3">
                                                        <i class="ti ti-gavel fs-6"></i>
                                                    </div>
                                                    <div>
                                                        <h4 class="fw-semibold mb-0">${judge.avg}</h4>
                                                        <p class="mb-0 fs-2 text-muted">Avg. Score</p>
                                                    </div>
                                                </div>
                                                 <p class="mb-0 small text-muted">Deviates <strong>${judge.deviation > 0 ? '+' : ''}${judge.deviation}%</strong> from group average.</p>
                                            </div>
                                        </div>
                                    </div>
                                `);
                            });
                        }

                        // 2. Chart Update
                        if (response.chart && response.chart.categories) {
                            chart.updateOptions({ xaxis: { categories: response.chart.categories } });
                            chart.updateSeries(response.chart.series);
                        }

                    }
                },
                error: (xhr) => {
                    console.error("Report Load Error:", xhr.responseText);
                    $('#judgeCardsContainer').html('<div class="col-12 text-center py-5 text-danger"><i class="ti ti-alert-circle fs-8"></i><p class="mt-2">Failed to load reports. Server returned an error.</p></div>');
                }
            });
        }

        initChart();
        loadCompetitions();
        loadReports();
        loadLog();

        $('#compFilter').change(function() {
            loadCategories($(this).val());
            loadReports();
        });

        $('#catFilter').change(function() {
            loadReports();
        });

        $('#btnFilter').click(function() {
            loadReports();
        });

        $('#logCompFilter').change(function() {
            loadLog();
        });

        $('#btnExportCSV').click(function() {
            if (!auditTable) return;
            const data = auditTable.rows({ filter: 'applied' }).data();
            let csv = 'Time,Judge,Candidate,Criteria,Score,Status\n';
            $(data).each(function() {
                const row = $(this);
                const time = row[0];
                const judge = $(row[1]).text().trim();
                const candidate = row[2];
                const criteria = row[3];
                const score = row[4];
                const status = $(row[5]).text().trim();
                csv += `"${time}","${judge}","${candidate}","${criteria}","${score}","${status}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.setAttribute("href", URL.createObjectURL(blob));
            link.setAttribute("download", "Judge_Scoring_Log.csv");
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });

        $('#btnPrintLog').click(function() {
            const compId = $('#logCompFilter').val();
            const url    = 'print-judge-log.php?competition_id=' + encodeURIComponent(compId);
            window.open(url, '_blank');
        });
    });
  </script>
  <style>
      @media print {
          .sidebar-nav, .header, .top-part, .card.mb-4, #btnFilter { display: none !important; }
          .body-wrapper { margin-left: 0 !important; }
          .container-fluid { padding: 0 !important; }
          .card { border: 1px solid #ddd !important; box-shadow: none !important; }
          .btn-export, .d-flex.gap-2 { display: none !important; }
      }
  </style>
</body>
</html>
