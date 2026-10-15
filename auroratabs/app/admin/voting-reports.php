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
  <title>Voting Analytics - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Voting Reports</h4>
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

          <!-- Summary Cards -->
          <div class="row">
            <div class="col-sm-6 col-lg-4">
                <div class="card border-bottom border-primary border-4 card-hover">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-light-primary text-primary rounded-circle me-3">
                                <i class="ti ti-currency-dollar fs-6"></i>
                            </div>
                            <div>
                                <h4 class="fw-semibold mb-1" id="totalRevenue">₱ 0.00</h4>
                                <p class="mb-0 fs-3 text-muted">Total Gross Revenue</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-sm-6 col-lg-4">
                <div class="card border-bottom border-warning border-4 card-hover">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-light-warning text-warning rounded-circle me-3">
                                <i class="ti ti-ticket fs-6"></i>
                            </div>
                            <div>
                                <h4 class="fw-semibold mb-1" id="totalVotes">0</h4>
                                <p class="mb-0 fs-3 text-muted">Total Verified Votes</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
             <div class="col-sm-6 col-lg-4">
                <div class="card border-bottom border-success border-4 card-hover">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center">
                            <div class="p-2 bg-light-success text-success rounded-circle me-3">
                                <i class="ti ti-trophy fs-6"></i>
                            </div>
                            <div>
                                <h4 class="fw-semibold mb-1" id="topPerformer">None</h4>
                                <p class="mb-0 fs-3 text-muted" id="topPercentage">Top Performer (0%)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <!-- Charts Section -->
          <div class="row">
               <!-- Vote Distribution Chart -->
               <div class="col-lg-8">
                   <div class="card">
                       <div class="card-body">
                           <div class="d-sm-flex d-block align-items-center justify-content-between mb-9">
                               <div class="mb-3 mb-sm-0">
                                   <h5 class="card-title fw-semibold">Vote Distribution</h5>
                                   <p class="card-subtitle mb-0">Votes per candidate</p>
                               </div>
                               <div>
                                     <select class="form-select form-select-sm" id="competitionFilter">
                                         <option value="">All Competitions</option>
                                     </select>
                               </div>
                           </div>
                           <div id="vote-chart" style="height: 350px;"></div>
                       </div>
                   </div>
               </div>
               
               <!-- Devices / Revenue Chart -->
               <div class="col-lg-4">
                   <div class="card">
                        <div class="card-body">
                           <h5 class="card-title fw-semibold">Payment Methods</h5>
                           <p class="card-subtitle mb-4">Revenue source breakdown</p>
                           <div class="position-relative">
                               <div id="payment-chart" style="height: 280px;"></div>
                           </div>
                           <div class="d-flex align-items-center justify-content-center mt-3 gap-3 flex-wrap" id="paymentLegend">
                                <!-- Dynamic Legend -->
                           </div>
                        </div>
                   </div>
               </div>
          </div>

          <!-- Detailed Table -->
          <div class="card w-100">
            <div class="card-body p-4">
              <div class="row align-items-center mb-4">
                  <div class="col-md-5">
                      <h5 class="card-title fw-semibold mb-0 text-center text-md-start">Detailed Performance Breakdown</h5>
                  </div>
                  <div class="col-md-7 text-end">
                      <div class="d-inline-flex align-items-center gap-2 flex-wrap mt-3 mt-md-0">
                          <div class="d-flex align-items-center gap-2">
                              <label class="form-label mb-0 fs-2 fw-semibold text-muted">Competition:</label>
                              <select class="form-select form-select-sm" id="competitionFilterTable" style="width: 170px;">
                                  <option value="">All Competitions</option>
                              </select>
                          </div>
                          <div class="d-flex align-items-center gap-2">
                              <label class="form-label mb-0 fs-2 fw-semibold text-muted">Department:</label>
                              <select class="form-select form-select-sm" id="deptFilter" style="width: 140px;">
                                  <option value="">All Departments</option>
                              </select>
                          </div>
                          <button class="btn btn-outline-success btn-sm" id="btnDownloadCSV" style="height: 31px;"><i class="ti ti-file-download me-1"></i>Export CSV</button>
                      </div>
                  </div>
              </div>
              <div class="table-responsive">
                <table class="table text-nowrap align-middle mb-0 table-responsive-stack" id="performanceTable">
                  <thead class="text-dark fs-4">
                    <tr>
                      <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Rank</h6></th>
                      <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Candidate</h6></th>
                      <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Total Votes</h6></th>
                      <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Percentage</h6></th>
                      <th class="border-bottom-0"><h6 class="fw-semibold mb-0">Gross Revenue</h6></th>
                    </tr>
                  </thead>
                  <tbody>
                    <!-- DataTable handles rows -->
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
  <!-- ApexCharts -->
  <script>
    document.addEventListener("DOMContentLoaded", function () {
        let voteChart = null;
        let payChart = null;
        let performanceTable = null;

        function initCharts() {
            var voteOptions = {
                series: [{ name: "Votes", data: [] }],
                chart: { height: 350, type: 'bar', fontFamily: 'inherit', toolbar: { show: false } },
                colors: ['#5D87FF', '#2A3547', '#ffae1f', '#fa896b', '#539bff'],
                plotOptions: { bar: { columnWidth: '45%', distributed: true, borderRadius: 4 } },
                dataLabels: { enabled: false },
                legend: { show: false },
                xaxis: { categories: [], labels: { style: { fontSize: '12px' } } }
            };
            voteChart = new ApexCharts(document.querySelector("#vote-chart"), voteOptions);
            voteChart.render();

            var payOptions = {
                series: [],
                labels: [],
                chart: { type: 'donut', height: 250, fontFamily: 'inherit', toolbar: { show: false } },
                colors: ['#5D87FF', '#2A3547', '#ffae1f', '#fa896b', '#539bff'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    fontSize: '20px',
                                    fontWeight: 600,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => { return a + b }, 0).toLocaleString() + " PHP"
                                    }
                                }
                            }
                        }
                    }
                },
                legend: { show: false },
                dataLabels: { enabled: false },
                stroke: { show: false }
            };
            payChart = new ApexCharts(document.querySelector("#payment-chart"), payOptions);
            payChart.render();
        }

        function loadCompetitions() {
            $.ajax({
                url: 'endpoints/voting_reports_handler.php',
                method: 'POST',
                data: { action: 'get_active_competitions' },
                success: function(response) {
                    if (response.status === 'success') {
                        const selects = $('#competitionFilter, #competitionFilterTable');
                        const currentVal = $('#competitionFilter').val();
                        selects.empty().append('<option value="">All Competitions</option>');
                        response.data.forEach(comp => {
                            selects.append(`<option value="${comp.id}">${comp.title}</option>`);
                        });
                        selects.val(currentVal);
                    }
                }
            });
        }

        function loadReports(compId = null) {
            const competitionId = (compId === "" || compId === null) ? "" : compId;
            
            // Sync logic
            $('#competitionFilter').val(competitionId);
            $('#competitionFilterTable').val(competitionId);

            // Clear table and show loading state
            if (performanceTable) {
                performanceTable.clear().draw();
                $(performanceTable.table().body()).html('<tr><td colspan="5" class="text-center py-4"><div class="spinner-border text-primary" role="status"></div><p class="mt-2 text-muted">Updating analytics...</p></td></tr>');
            } else {
                // First initialization
                $('#performanceTable tbody').empty();
                performanceTable = $('#performanceTable').DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "autoWidth": false,
                    "responsive": true,
                    "language": { "search": "Search Candidate:", "emptyTable": "Loading performance data..." }
                });
            }

            $.ajax({
                url: 'endpoints/voting_reports_handler.php',
                method: 'POST',
                data: { action: 'get_reports', competition_id: competitionId },
                success: function(response) {
                    if (response.status === 'success') {
                        // Summary Update
                        $('#totalRevenue').text('₱ ' + response.summary.total_revenue.toLocaleString(undefined, {minimumFractionDigits: 2}));
                        $('#totalVotes').text(response.summary.total_votes.toLocaleString());
                        $('#topPerformer').text(response.summary.top_performer);
                        $('#topPercentage').text(`Top Performer (${response.summary.top_percentage}%)`);

                        // Charts Update
                        voteChart.updateOptions({ xaxis: { categories: response.dist_chart.map(i => i.name) } });
                        voteChart.updateSeries([{ name: 'Votes', data: response.dist_chart.map(i => i.votes) }]);

                        payChart.updateOptions({ labels: response.pay_chart.map(i => i.gateway) });
                        payChart.updateSeries(response.pay_chart.map(i => i.total));

                        const legend = $('#paymentLegend').empty();
                        const colors = ['text-primary', 'text-secondary', 'text-warning', 'text-danger', 'text-info'];
                        response.pay_chart.forEach((item, i) => {
                            legend.append(`<div class="d-flex align-items-center ${colors[i % colors.length]} fs-2"><i class="ti ti-circle-filled fs-2 me-2"></i> ${item.gateway}</div>`);
                        });

                        // Table Update using API
                        performanceTable.clear();
                        const departments = new Set();
                        response.table.forEach(item => {
                            if (item.department) departments.add(item.department);
                            const photo = item.photo ? '../' + item.photo : 'src/assets/images/profile/user-1.jpg';
                            const genderIcon = item.gender === 'male' ? '<i class="ti ti-gender-male text-primary ms-1"></i>' : (item.gender === 'female' ? '<i class="ti ti-gender-female text-danger ms-1"></i>' : '');

                            performanceTable.row.add([
                                `<span class="badge ${item.rank === 1 ? 'bg-light-success text-success' : 'bg-light text-muted'} fw-semibold fs-2">${item.rank}</span>`,
                                `<div class="d-flex align-items-center">
                                    <img src="${photo}" class="rounded-circle" width="40" height="40" alt="" onerror="this.src='src/assets/images/profile/user-1.jpg'">
                                    <div class="ms-3">
                                        <h6 class="fw-semibold mb-0">${item.name} ${genderIcon}</h6>
                                        <span class="fw-normal text-muted fs-2">Candidate #${item.number} | ${item.department || 'N/A'}</span>
                                    </div>
                                </div>`,
                                item.votes.toLocaleString(),
                                `<div class="d-flex align-items-center">
                                    <div class="progress w-100 me-3" style="height: 6px;"><div class="progress-bar ${item.rank === 1 ? 'bg-success' : 'bg-primary'}" role="progressbar" style="width: ${item.percentage}%"></div></div>
                                    <span>${item.percentage}%</span>
                                </div>`,
                                `<h6 class="fw-semibold mb-0 ${item.rank === 1 ? 'text-success' : ''}">₱ ${item.revenue.toLocaleString(undefined, {minimumFractionDigits: 2})}</h6>`
                            ]);
                        });
                        performanceTable.draw();

                        // Sync Dept Filter
                        const deptFilter = $('#deptFilter');
                        const currentDept = deptFilter.val();
                        deptFilter.empty().append('<option value="">All Departments</option>');
                        Array.from(departments).sort().forEach(dept => {
                            deptFilter.append(`<option value="${dept}" ${currentDept === dept ? 'selected' : ''}>${dept}</option>`);
                        });
                        if (currentDept) performanceTable.column(1).search(currentDept).draw();
                    }
                }
            });
        }

        initCharts();
        loadCompetitions();
        loadReports();

        $('#competitionFilter, #competitionFilterTable').change(function() {
            loadReports($(this).val());
        });

        $('#deptFilter').change(function() {
            if (performanceTable) performanceTable.column(1).search($(this).val()).draw();
        });

        $('#btnDownloadCSV').click(function() {
            const compTitle = $('#competitionFilterTable option:selected').text() || 'All Competitions';
            if (!performanceTable) return;
            
            const data = performanceTable.rows({ filter: 'applied' }).data();
            let csv = 'Rank,Candidate,Info,Total Votes,Percentage,Gross Revenue (PHP)\n';
            
            data.each(function(row) {
                const rank = $(row[0]).text().trim();
                const name = $(row[1]).find('h6').text().trim();
                const info = $(row[1]).find('span').text().trim();
                const votes = row[2].replace(/,/g, '');
                const percentage = $(row[3]).find('span').text().trim();
                const revenue = $(row[4]).text().trim().replace('₱', '').replace(/,/g, '').trim();
                csv += `"${rank}","${name}","${info}","${votes}","${percentage}","${revenue}"\n`;
            });

            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement("a");
            link.setAttribute("href", URL.createObjectURL(blob));
            link.setAttribute("download", `Voting_Report_${compTitle.replace(/\s+/g, '_')}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
  </script>
</body>
</html>
