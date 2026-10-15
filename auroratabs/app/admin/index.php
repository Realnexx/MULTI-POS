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
  <title>Aurora Tabs</title>
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

          <!--  Row 1 -->
          <div class="row top-part">
            <div class="col-lg-8 d-flex align-items-strech">
              <div class="card w-100">
                <div class="card-body">
                  <div class="d-flex align-items-center justify-content-between mb-1">
                    <div class="">
                      <h5 class="card-title fw-semibold">Voting Trends</h5>
                    </div>
                    <div class="dropdown">
                      <button id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"
                        class="rounded-circle btn-transparent rounded-circle btn-sm px-1 btn shadow-none">
                        <i class="ti ti-dots-vertical fs-7 d-block"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="#">Refresh Status</a></li>
                        <li>
                          <a class="dropdown-item" href="#">View All Stats</a>
                        </li>
                      </ul>
                    </div>
                  </div>
                  <div id="profit"></div>
                </div>
              </div>
            </div>
            <div class="col-lg-4">
              <div class="row">
                <div class="col-lg-12 col-sm-6">
                  <!-- Active Competitions -->
                  <div class="card overflow-hidden">
                    <div class="card-body p-4">
                      <h5 class="card-title mb-10 fw-semibold">Active Competitions</h5>
                      <div class="row align-items-center">
                        <div class="col-7">
                          <h4 class="fw-semibold mb-3" id="active-comp-count">0</h4>
                          <div class="d-flex align-items-center mb-2">
                            <span
                              class="me-1 rounded-circle bg-light-success round-20 d-flex align-items-center justify-content-center" id="comp-growth-icon">
                              <i class="ti ti-arrow-up-left text-success"></i>
                            </span>
                            <p class="text-dark me-1 fs-3 mb-0" id="comp-growth-val">+0</p>
                            <p class="fs-3 mb-0">this week</p>
                          </div>
                          <div class="d-flex align-items-center">
                            <div class="me-3">
                              <span class="round-8 bg-primary rounded-circle me-2 d-inline-block"></span>
                              <span class="fs-2">Live: <span id="comp-live-count">0</span></span>
                            </div>
                            <div>
                              <span class="round-8 bg-warning rounded-circle me-2 d-inline-block"></span>
                              <span class="fs-2">Upcoming: <span id="comp-upcoming-count">0</span></span>
                            </div>
                          </div>
                        </div>
                        <div class="col-5">
                          <div class="d-flex justify-content-center">
                            <div id="grade"></div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-lg-12 col-sm-6">
                  <!-- Total Votes -->
                  <div class="card">
                    <div class="card-body">
                      <div class="row alig n-items-start">
                        <div class="col-8">
                          <h5 class="card-title mb-10 fw-semibold">Total Votes Cast</h5>
                          <h4 class="fw-semibold mb-3" id="total-votes-count">0</h4>
                          <div class="d-flex align-items-center pb-1">
                            <span
                              class="me-2 rounded-circle bg-light-success round-20 d-flex align-items-center justify-content-center" id="votes-growth-icon">
                              <i class="ti ti-arrow-up-left text-success"></i>
                            </span>
                            <p class="text-dark me-1 fs-3 mb-0" id="votes-growth-val">+0%</p>
                            <p class="fs-3 mb-0">since yesterday</p>
                          </div>
                        </div>
                        <div class="col-4">
                          <div class="d-flex justify-content-end">
                            <div
                              class="text-white bg-primary rounded-circle p-6 d-flex align-items-center justify-content-center">
                              <i class="ti ti-box fs-6"></i>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div id="earning"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="row">
            <div class="col-lg-4 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="mb-4">
                    <h5 class="card-title fw-semibold">Recent System Activity</h5>
                  </div>
                  <div style="height: 380px;" data-simplebar>
                    <ul class="timeline-widget mb-0 position-relative" id="activity-timeline">
                      <!-- Dynamic Activity Logs -->
                      <li class="col-12 text-center py-4 text-muted small">Loading activity logs...</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-lg-8 d-flex align-items-stretch">
              <div class="card w-100">
                <div class="card-body p-4">
                  <div class="d-flex mb-4 justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Recent Judge Submissions</h5>

                    <div class="dropdown">
                      <button id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false"
                        class="rounded-circle btn-transparent rounded-circle btn-sm px-1 btn shadow-none">
                        <i class="ti ti-dots-vertical fs-7 d-block"></i>
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="#">View Submissions</a></li>
                        <li>
                          <a class="dropdown-item" href="#">Manage Judges</a>
                        </li>
                      </ul>
                    </div>
                  </div>

                  <div class="table-responsive" data-simplebar>
                    <table class="table table-borderless align-middle text-nowrap">
                      <thead>
                        <tr>
                          <th scope="col">Judge</th>
                          <th scope="col">Competition</th>
                          <th scope="col">Status</th>
                          <th scope="col">Submissions</th>
                        </tr>
                      </thead>
                      <tbody id="judge-submissions-list">
                        <tr>
                          <td colspan="4" class="text-center py-4 text-muted small">Loading recent submissions...</td>
                        </tr>
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
    $(function () {
      let profitChart, gradeChart;

      function updateDashboard() {
        $.ajax({
          url: 'endpoints/dashboard_handler.php',
          method: 'POST',
          dataType: 'json',
          success: function (res) {
            if (res.status === 'success') {
              const d = res.data;

              // 1. Competition Stats
              $('#active-comp-count').text(d.competitions.active);
              $('#comp-live-count').text(d.competitions.active);
              $('#comp-upcoming-count').text(d.competitions.upcoming);
              $('#comp-growth-val').text('+' + d.competitions.upcoming);

              // 2. Total Votes
              $('#total-votes-count').text(d.votes.total.toLocaleString());
              const growth = d.votes.growth;
              $('#votes-growth-val').text((growth >= 0 ? '+' : '') + growth + '%');
              $('#votes-growth-icon').html(`<i class="ti ti-arrow-${growth >= 0 ? 'up-left text-success' : 'down-right text-danger'}"></i>`);

              // 3. System Activity
              let actHtml = '';
              if (d.activity.length === 0) {
                actHtml = '<li class="col-12 text-center py-4 text-muted small">No recent activity.</li>';
              } else {
                d.activity.forEach(a => {
                  let color = 'primary';
                  if (a.type.includes('delete') || a.type.includes('remove')) color = 'danger';
                  else if (a.type.includes('update') || a.type.includes('edit')) color = 'warning';
                  else if (a.type.includes('add') || a.type.includes('create') || a.type.includes('start')) color = 'success';
                  else if (a.type.includes('score') || a.type.includes('submit') || a.type.includes('submit')) color = 'info';

                  actHtml += `
                    <li class="timeline-item d-flex position-relative overflow-hidden">
                      <div class="timeline-time text-dark flex-shrink-0 text-end">${a.time}</div>
                      <div class="timeline-badge-wrap d-flex flex-column align-items-center">
                        <span class="timeline-badge border-2 border border-${color} flex-shrink-0 my-2"></span>
                        <span class="timeline-badge-border d-block flex-shrink-0"></span>
                      </div>
                      <div class="timeline-desc fs-3 text-dark mt-n1">
                        <span class="fw-semibold">${a.module}</span>: ${a.details}
                        <small class="d-block text-muted">by ${a.admin}</small>
                      </div>
                    </li>`;
                });
              }
              $('#activity-timeline').html(actHtml);

              // 4. Judge Submissions
              let subHtml = '';
              if (d.judge_submissions.length === 0) {
                subHtml = '<tr><td colspan="4" class="text-center py-4">No recent submissions found.</td></tr>';
              } else {
                d.judge_submissions.forEach(s => {
                  const isDone = s.scored_count >= s.total_candidates;
                  const photo = s.avatar_url ? s.avatar_url : `https://ui-avatars.com/api/?name=${s.first_name}+${s.last_name}&background=random`;
                  
                  subHtml += `
                    <tr>
                      <td>
                        <div class="d-flex align-items-center">
                          <div class="me-3">
                            <img src="${photo}" width="40" height="40" class="rounded-circle shadow-sm" alt="${s.first_name}" />
                          </div>
                          <div>
                            <h6 class="mb-0 fw-bolder">${s.first_name} ${s.last_name}</h6>
                            <p class="fs-2 mb-0 text-muted">Judge</p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="fs-3 mb-0 text-truncate" style="max-width: 150px;" title="${s.comp_title}">${s.comp_title}</p>
                      </td>
                      <td>
                        <span class="badge ${isDone ? 'bg-light-success text-success' : 'bg-light-primary text-primary'} rounded-pill px-3 py-2 fs-2">
                          ${isDone ? 'Finished' : 'In Progress'}
                        </span>
                      </td>
                      <td>
                        <p class="fs-3 mb-0 fw-semibold ${isDone ? 'text-success' : 'text-primary'}">
                          ${s.scored_count}/${s.total_candidates}
                        </p>
                      </td>
                    </tr>`;
                });
              }
              $('#judge-submissions-list').html(subHtml);

              // 5. Update Charts
              renderDashboardCharts(d.trends, d.competitions);
            }
          }
        });
      }

      function renderDashboardCharts(trends, comps) {
        // Profit Trend Bar Chart (Voting Trends)
        if (!profitChart) {
          const profitOpt = {
            series: [{ name: "Votes Cast", data: trends.data }],
            chart: { type: "bar", height: 350, toolbar: { show: false }, fontFamily: 'inherit' },
            colors: ["#5d87ff"],
            plotOptions: { bar: { columnWidth: "35%", borderRadius: 4, endingShape: "rounded" } },
            dataLabels: { enabled: false },
            grid: { borderColor: "rgba(0,0,0,0.05)", strokeDashArray: 3, padding: { top: 0, bottom: 0 } },
            xaxis: { categories: trends.labels, axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: "#a1aab2" } } },
            yaxis: { labels: { style: { colors: "#a1aab2" } } },
            tooltip: { theme: "dark" }
          };
          $('#profit').empty(); // Clear static chart
          profitChart = new ApexCharts(document.querySelector("#profit"), profitOpt);
          profitChart.render();
        } else {
          profitChart.updateSeries([{ data: trends.data }]);
          profitChart.updateOptions({ xaxis: { categories: trends.labels } });
        }

        // Grade Donut Chart (Competition Status)
        if (!gradeChart) {
          const gradeOpt = {
            series: [comps.active, comps.upcoming, comps.completed],
            labels: ["Live", "Upcoming", "Completed"],
            chart: { type: "donut", height: 180, fontFamily: 'inherit' },
            colors: ["#13deb9", "#ffae1f", "#5d87ff"],
            plotOptions: { pie: { donut: { size: '80%', labels: { show: true, name: { show: true, fontSize: "12px", offsetY: 5 }, value: { show: false } } } } },
            dataLabels: { enabled: false },
            legend: { show: false },
            stroke: { show: false },
            tooltip: { theme: "dark", fillSeriesColor: false }
          };
          $('#grade').empty(); // Clear static chart
          gradeChart = new ApexCharts(document.querySelector("#grade"), gradeOpt);
          gradeChart.render();
        } else {
          gradeChart.updateSeries([comps.active, comps.upcoming, comps.completed]);
        }
      }

      // Initial Load
      updateDashboard();

      // Refresh data every 1 minute
      setInterval(updateDashboard, 60000);
    });
  </script>
</body>

</html>