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
  <title>Live Results - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
      .podium-card {
          transition: transform 0.3s ease;
      }
      .podium-card:hover {
          transform: translateY(-5px);
      }
      .rank-1 { border-top: 5px solid #ffd700; background: linear-gradient(to bottom, #fffdf0, #ffffff); }
      .rank-2 { border-top: 5px solid #c0c0c0; background: linear-gradient(to bottom, #f8f9fa, #ffffff); }
      .rank-3 { border-top: 5px solid #cd7f32; background: linear-gradient(to bottom, #fff5ec, #ffffff); }
      
      .live-indicator {
          display: inline-block;
          width: 10px;
          height: 10px;
          background-color: #fa896b;
          border-radius: 50%;
          animation: pulse 1.5s infinite;
          margin-right: 5px;
      }
            @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(250, 137, 107, 0.7); }
            70% { box-shadow: 0 0 0 10px rgba(250, 137, 107, 0); }
            100% { box-shadow: 0 0 0 0 rgba(250, 137, 107, 0); }
        }

        /* Standard Podium Styles */
        .podium-container { display: flex; align-items: flex-end; justify-content: center; gap: 15px; padding: 30px 0; margin-bottom: 40px; }
        .podium-item { text-align: center; width: 100%; max-width: 200px; }
        .podium-rank { width: 100%; border-radius: 15px 15px 0 0; position: relative; padding-top: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .podium-1 { height: 200px; background: linear-gradient(to top, #fcd34d, #fbbf24); color: #92400e; border: 2px solid #f59e0b; }
        .podium-2 { height: 160px; background: linear-gradient(to top, #e2e8f0, #cbd5e1); color: #475569; border: 2px solid #94a3b8; }
        .podium-3 { height: 130px; background: linear-gradient(to top, #fdba74, #f97316); color: #9a3412; border: 2px solid #ea580c; }
        .podium-avatar { width: 90px; height: 90px; border-radius: 50%; border: 4px solid white; object-fit: cover; margin-bottom: -45px; position: relative; z-index: 2; box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .podium-number { position: absolute; bottom: 5px; left: 50%; transform: translateX(-50%); font-size: 3rem; font-weight: 900; opacity: 0.2; }
        .vote-badge-podium { background: rgba(255,255,255,0.9); padding: 4px 12px; border-radius: 50px; font-weight: 800; font-size: 1.1rem; display: inline-block; margin-top: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); }

        @media (max-width: 768px) {
            .podium-container { flex-direction: column; align-items: center; gap: 60px; padding-top: 50px; }
            .podium-rank { height: auto !important; padding-bottom: 30px; border-radius: 15px; }
            .podium-item { max-width: 100%; }
        }
    </style>
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

          <div class="top-part card bg-light-primary shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center g-3">
                <div class="col-md-7 col-12 text-center text-md-start">
                  <div class="d-flex align-items-center justify-content-center justify-content-md-start mb-2">
                       <span class="live-indicator"></span>
                       <span class="text-danger fw-bold text-uppercase fs-2 letter-spacing-1">Live Updates</span>
                  </div>
                  <h4 class="fw-bold mb-1" id="liveCompName">Matahom sa Ubay 2026</h4>
                  <p class="text-muted mb-0">Finals Night Results (Real-time)</p>
                </div>
                <div class="col-md-5 col-12">
                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end align-items-center">
                        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                            <label class="small fw-bold text-muted text-nowrap mb-0 d-md-none d-lg-block">COMPETITION:</label>
                            <select class="form-select form-select-sm rounded-pill border-primary text-primary fw-bold w-100" id="liveCompSelect">
                                <option value="Miss Matahom Grand Finals 2026" selected>Grand Finals</option>
                                <option value="Miss Matahom Talent Competition">Talent Night</option>
                                <option value="Miss Matahom Swimsuit Prelims">Swimsuit Prelims</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-sm rounded-pill px-3 py-2 w-100 w-md-auto" onclick="toggleFullScreen()">
                            <i class="ti ti-maximize me-1"></i> Full Screen
                        </button>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <div id="loading-state" class="text-center w-100 py-5 text-muted">
              <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
              </div>
              <p class="mt-2">Waiting for results...</p>
          </div>
          
          <div id="no-results-message" class="text-center w-100 py-5 text-muted" style="display:none;">
              <i class="ti ti-chart-bar-off fs-8 d-block mb-3 text-light-emphasis"></i>
              <h6 class="text-muted fw-semibold">No Results Found</h6>
              <p class="mb-0 fs-2">Scores have not been calculated yet for this competition.</p>
          </div>

          <!-- Dynamic Results Wrapper -->
          <div id="dynamic-results-container"></div>

          <?php include 'components/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>
  <?php include 'components/scripts.php'; ?>
  <script>
      function toggleFullScreen() {
          if (!document.fullscreenElement) {
              document.documentElement.requestFullscreen();
          } else {
            if (document.exitFullscreen) {
              document.exitFullscreen();
            }
          }
      }

      $(document).ready(function() {
          let pollInterval;

          let currentCompId = null;

          // 1. Load Competitions
          function loadCompetitions() {
            $.post('endpoints/recalculate_handler.php', { action: 'get_competitions' }, function(response) {
                if(response.status === 'success') {
                    let options = '';
                    let firstId = '';
                    response.data.forEach((comp, index) => {
                        if(index === 0) firstId = comp.id;
                        options += `<option value="${comp.id}">${comp.title}</option>`;
                    });
                    $('#liveCompSelect').html(options);
                    
                    if(firstId) {
                        currentCompId = firstId;
                        loadResults(firstId);
                        startPolling(firstId);
                    }
                }
            });
          }

          loadCompetitions();

          // 2. Load Results
          function loadResults(compId) {
              $.post('endpoints/live_results_handler.php', { 
                  action: 'get_live_results', 
                  competition_id: compId 
              }, function(response) {
                  if(response.status === 'success') {
                      // Prevent stale updates
                      if (currentCompId && String(compId) !== String(currentCompId)) {
                          return;
                      }
                      
                      $('#loading-state').hide(); // Stop loading spinner

                      if(response.data.length === 0) {
                          $('#no-results-message').fadeIn();
                          $('#dynamic-results-container').empty();
                      } else {
                          $('#no-results-message').hide();
                          $('#liveCompName').text(response.competition_title); // Update Title
                          renderResults(response.data);
                      }
                  }
              });
          }

          // 3. Render Results
          function renderResults(data) {
              const males = data.filter(d => d.gender && d.gender.toLowerCase() === 'male');
              const females = data.filter(d => d.gender && d.gender.toLowerCase() === 'female');
              const isSplit = males.length > 0 && females.length > 0;

              $('#dynamic-results-container').empty();

              if (isSplit) {
                  // Render Female First traditionally, then Male
                  renderGenderBlock('Female Rankings', rankArray(females));
                  renderGenderBlock('Male Rankings', rankArray(males));
              } else {
                  renderGenderBlock('Overall Rankings', rankArray(data));
              }
          }

          function rankArray(arr) {
              // 4. Sort and Rank (Re-ranking locally in JS for splits)
              arr.sort((a, b) => b.score - a.score);
              let rank = 1;
              let actual_rank = 1;
              let prev_score = -1;
              const epsilon = 0.0001;

              arr.forEach(res => {
                  if (prev_score !== -1 && Math.abs(res.score - prev_score) > epsilon) {
                      rank = actual_rank;
                  }
                  res.rank = rank;
                  prev_score = res.score;
                  actual_rank++;
              });
              return arr;
          }

          function renderGenderBlock(title, data) {
              if (data.length === 0) return;

              const winner = data[0] || null;
              const second = data[1] || null;
              const third = data[2] || null;

              const podiumIds = [winner?.candidate_id, second?.candidate_id, third?.candidate_id].filter(id => id);
              const rest = data.filter(d => !podiumIds.includes(d.candidate_id));

              let html = `
              <div class="mb-5">
                  <div class="d-flex align-items-center justify-content-center mb-4">
                      <h4 class="fw-bold mb-0 text-primary">${title}</h4>
                  </div>
                  <div class="podium-container">
                      ${second ? buildPodiumCard(second, 2) : ''}
                      ${winner ? buildPodiumCard(winner, 1) : ''}
                      ${third  ? buildPodiumCard(third, 3) : ''}
                  </div>
              `;

              html += `
                  <div class="card mt-4">
                    <div class="card-body">
                      <div class="table-responsive">
                        <table class="table align-middle text-nowrap mb-0 table-responsive-stack">
                          <thead class="text-dark fs-4">
                            <tr>
                              <th class="border-bottom-0">Rank</th>
                              <th class="border-bottom-0">Candidate</th>
                              <th class="border-bottom-0">Score</th>
                              <th class="border-bottom-0">Status</th>
                            </tr>
                          </thead>
                          <tbody>
              `;

              if (rest.length === 0) {
                  html += `<tr><td colspan="4" class="text-center text-muted">No other candidates.</td></tr>`;
              } else {
                  rest.forEach(d => {
                      let statusBadge = d.rank <= 5 
                        ? '<span class="badge bg-light-success text-success rounded-3 fw-semibold">Finalist</span>'
                        : '<span class="badge bg-light-secondary text-secondary rounded-3 fw-semibold">Participant</span>';

                      const candDept = d.department ? `<br><small class="text-muted"><i class="ti ti-building me-1"></i>${d.department}</small>` : '';

                      html += `
                         <tr>
                          <td class="border-bottom-0"><h6 class="fw-semibold mb-0">${d.rank}</h6></td>
                          <td class="border-bottom-0">
                              <div class="d-flex align-items-center">
                                    <span class="badge bg-light-primary text-primary me-2">#${d.number}</span>
                                    <div>
                                        <span class="fw-normal d-block">${d.name}</span>
                                        ${candDept}
                                    </div>
                              </div>
                          </td>
                          <td class="border-bottom-0"><p class="mb-0 fw-normal">${d.score_formatted}</p></td>
                          <td class="border-bottom-0">
                            <div class="d-flex align-items-center gap-2">
                              ${statusBadge}
                            </div>
                          </td>
                        </tr>
                      `;
                  });
              }

              html += `</tbody></table></div></div></div></div>`;
              $('#dynamic-results-container').append(html);
          }

          function buildPodiumCard(data, place) {
              let classes = "";
              let crown = "";
              let badgeClass = "badge bg-white text-dark rounded-pill px-3 py-1 fs-5 shadow-sm border mt-3";
              let imgStyle = "";

              if (place === 1) {
                  classes = "order-first order-md-0";
                  crown = '<i class="ti ti-crown fs-7 mb-1 d-block text-warning"></i>';
                  badgeClass = "badge bg-white text-primary rounded-pill px-3 py-2 fs-4 shadow-sm border border-primary mt-3";
                  imgStyle = 'style="width: 110px; height: 110px; margin-bottom: -55px;"';
              }

              const avatar = data.avatar || 'src/assets/images/profile/user-1.jpg';
              const candDept = data.department ? `<small class="text-muted d-block mt-1 truncate-text" style="max-width:120px; font-size:10px;">${data.department}</small>` : '';

              return `
              <div class="podium-item ${classes}">
                  <img src="${avatar}" class="podium-avatar" alt="" ${imgStyle}>
                  <div class="podium-rank podium-${place} d-flex flex-column">
                      <div class="p-3 flex-grow-1 d-flex flex-column ${place === 1 ? 'mt-4' : 'mt-2'} pb-4">
                          <div>
                              ${crown}
                              <h6 class="fw-bold mb-0 text-dark mx-auto" style="max-width: 140px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">${data.name}</h6>
                              ${candDept}
                          </div>
                          <div class="mt-auto z-index-1"><span class="${badgeClass}">${data.score_formatted}</span></div>
                      </div>
                      <div class="podium-number ${place !== 1 ? 'text-dark' : ''}">${data.rank}</div>
                  </div>
              </div>
              `;
          }

          function startPolling(sysId) {
              if(pollInterval) clearInterval(pollInterval);
              pollInterval = setInterval(() => {
                  loadResults(sysId);
              }, 5000); // Poll every 5 seconds
          }

          // Handle Change
          $('#liveCompSelect').on('change', function() {
              const selectedValue = $(this).val();
              const selectedText = $(this).find("option:selected").text();
              
              currentCompId = selectedValue;
              
              // Reset UI states immediately
              $('#loading-state').show();
              $('#no-results-message').hide();
              $('#dynamic-results-container').empty();

              Swal.fire({
                  title: 'Switching Results',
                  text: `Updating live data for ${selectedText}...`,
                  allowOutsideClick: false,
                  didOpen: () => {
                      Swal.showLoading();
                  }
              });

              loadResults(selectedValue);
              startPolling(selectedValue);

              setTimeout(() => {
                  Swal.close();
                  Swal.fire({
                      toast: true,
                      position: 'top-end',
                      icon: 'success',
                      title: `Now viewing ${selectedText}`,
                      showConfirmButton: false,
                      timer: 1500
                  });
              }, 800);
          });
      });
  </script>
</body>
</html>
