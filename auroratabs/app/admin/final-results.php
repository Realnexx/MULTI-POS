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
  <title>Final Results - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
      @media print {
          /* Hide non-printable elements */
          #main-wrapper > .left-sidebar,
          #main-wrapper > .body-wrapper > .body-wrapper-inner > .container-fluid > .header,
          .btn-print,
          .sidebar-nav,
          .left-sidebar, 
          .header,
          .customizer-btn {
              display: none !important;
          }
          
          /* Reset layout for print */
          #main-wrapper {
              margin: 0;
              padding: 0;
          }
          .body-wrapper {
              margin-left: 0 !important;
              padding: 0 !important;
          }
          .body-wrapper-inner {
              padding: 0 !important;
          }
          .container-fluid {
              padding: 0 !important;
              max-width: 100% !important;
          }
          .text-title {
              margin-top: 30px;
          }
          /* Ensure backgrounds print */
          body {
              -webkit-print-color-adjust: exact;
              print-color-adjust: exact;
              background: white;
          }

          .card {
              border: none !important;
              box-shadow: none !important;
          }
      }

      .signature-line {
          border-top: 1px solid #000;
          width: 80%;
          margin: 0 auto;
          margin-top: 40px;
      }

        /* Podium Ranking Styles */
        .podium-container { display: flex; align-items: flex-end; justify-content: center; gap: 15px; padding: 30px 0; margin-bottom: 40px; }
        .podium-item { text-align: center; width: 100%; max-width: 180px; }
        .podium-rank { width: 100%; border-radius: 15px 15px 0 0; position: relative; padding-top: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .podium-1 { height: 180px; background: linear-gradient(to top, #fcd34d, #fbbf24); color: #92400e; border: 2px solid #f59e0b; }
        .podium-2 { height: 140px; background: linear-gradient(to top, #e2e8f0, #cbd5e1); color: #475569; border: 2px solid #94a3b8; }
        .podium-3 { height: 110px; background: linear-gradient(to top, #fdba74, #f97316); color: #9a3412; border: 2px solid #ea580c; }
        .podium-avatar { width: 80px; height: 80px; border-radius: 50%; border: 4px solid white; object-fit: cover; margin-bottom: -40px; position: relative; z-index: 2; box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        .podium-number { position: absolute; bottom: 5px; left: 50%; transform: translateX(-50%); font-size: 2.5rem; font-weight: 900; opacity: 0.2; }
        
        @media (max-width: 768px) {
            .podium-container { flex-direction: column; align-items: center; gap: 50px; }
            .podium-rank { height: auto !important; padding-bottom: 20px; border-radius: 15px; }
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
          <!--  Header Start (Hidden in print) -->
        
            <?php include 'components/header.php'; ?>
        
          <!--  Header End -->

          <!-- Toolbar (Hidden in print) -->
          <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4 btn-print">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center g-3">
                <div class="col-md-7 col-12 text-center text-md-start">
                  <h4 class="fw-semibold mb-2">Official Results</h4>
                   <nav aria-label="breadcrumb">
                    <ol class="breadcrumb justify-content-center justify-content-md-start mb-0">
                      <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="index.php">Dashboard</a></li>
                      <li class="breadcrumb-item" aria-current="page">Final Results</li>
                    </ol>
                  </nav>
                </div>
                <div class="col-md-5 col-12">
                    <div class="d-flex flex-column flex-md-row gap-2 justify-content-md-end align-items-center">
                        <div class="d-flex align-items-center gap-2 w-100 w-md-auto">
                            <label class="small fw-bold text-muted text-nowrap mb-0 d-md-none d-lg-block">VIEWING:</label>
                            <select class="form-select form-select-sm rounded-pill border-primary text-primary fw-bold w-100" id="finalCompSelect">
                                <option value="Miss Matahom Grand Finals 2026">Grand Finals</option>
                                <option value="Miss Matahom Talent Competition">Talent Night</option>
                                <option value="Miss Matahom Swimsuit Prelims">Swimsuit Prelims</option>
                            </select>
                        </div>
                        <button class="btn btn-primary btn-sm rounded-pill px-3 py-2 w-100 w-md-auto" id="btnPrint">
                            <i class="ti ti-printer me-1"></i> Print Report
                        </button>
                    </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Printable Area -->
          <div class="card">
              <div class="card-body p-4 p-md-5">
                  <!-- Report Header -->
                  <div class="text-center mb-5">
                      <h2 class="fw-bold text-title text-uppercase mb-1" id="reportTitle">--</h2>
                      <h4 class="text-muted mb-3" id="reportDescription">Official Final Results</h4>
                      <p class="mb-0">Date: <span id="reportDate">--</span> &nbsp;|&nbsp; Venue: <span id="reportVenue">Ubay Cultural Center</span></p>
                  </div>

                  <!-- Loading & Empty States -->
                  <div class="text-center w-100 py-5 text-muted" id="loading-state">
                      <div class="spinner-border text-primary" role="status">
                          <span class="visually-hidden">Loading...</span>
                      </div>
                      <p class="mt-2">Loading results...</p>
                  </div>
                  
                  <div class="text-center w-100 py-5 text-muted" id="empty-state" style="display:none;">
                      <i class="ti ti-folder-off fs-8 d-block mb-3 text-light-emphasis"></i>
                      <h6 class="text-muted fw-semibold">No Final Results</h6>
                      <p class="mb-0 fs-2">Results have not been finalized/calculated for this competition yet.</p>
                  </div>

                  <!-- Dynamic Results Wrapper -->
                  <div id="dynamic-results-container" style="display:none;"></div>

                  <!-- Signatories -->
                  <div class="row mt-5 pt-5 justify-content-center" id="signatories-container">
                      <!-- Dynamic Judges -->
                  </div>
                  
                  <div class="text-center mt-5 pt-4 text-muted small">
                      <?php
                        date_default_timezone_set('Asia/Manila');
                        ?>
                        <p>Generated by Aurora Tabs v1.0 on <?php echo date('F d, Y \a\t h:i A'); ?></p>
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

          // 1. Load Competitions
          function loadCompetitions() {
            $.post('endpoints/final_results_handler.php', { action: 'get_competitions' }, function(response) {
                if(response.status === 'success') {
                    let options = '';
                    let firstId = '';
                    response.data.forEach((comp, index) => {
                        if(index === 0) firstId = comp.id;
                        options += `<option value="${comp.id}">${comp.title}</option>`;
                    });
                    $('#finalCompSelect').html(options);
                    
                    if(firstId) {
                        currentCompId = firstId;
                        loadResults(firstId);
                    }
                }
            });
          }

          loadCompetitions();

          // 2. Load Results
          function loadResults(compId) {
              $('#loading-state').show();
              $('#empty-state').hide();
              $('#dynamic-results-container').hide().empty();
              $('#signatories-container').empty();
              
              $.post('endpoints/final_results_handler.php', { 
                  action: 'get_final_results', 
                  competition_id: compId 
              }, function(response) {
                  // Prevent stale
                  if(currentCompId && String(compId) !== String(currentCompId)) return;
                  
                  $('#loading-state').hide();

                  if(response.status === 'success') {
                        // Update Header ALWAYS (even if no results)
                        $('#reportTitle').text(response.competition_title);
                        $('#reportDescription').text(response.competition_description);
                        $('#reportDate').text(response.competition_date);
                        $('#reportVenue').text(response.competition_venue);
                        
                        // Always render judges if available
                        renderSignatories(response.judges);

                      if(response.data.length === 0) {
                          $('#empty-state').fadeIn();
                      } else {
                        renderResults(response.data);
                        $('#dynamic-results-container').fadeIn();
                      }
                  } else {
                      // Fallback error
                      $('#empty-state').fadeIn(); // Or error state
                  }
              });
          }

          function renderSignatories(judges) {
              const container = $('#signatories-container');
              container.empty();
              
              if(!judges || judges.length === 0) return;
              
              // Calculate columns based on count (max 3 or 4 per row?)
              // Use Bootstrap col-4 or col-3
              
              judges.forEach(j => {
                  const item = `
                    <div class="col-4 text-center mb-4">
                        <div class="signature-line"></div>
                        <h6 class="fw-bold mb-0 text-uppercase">${j.name}</h6>
                        <p class="small text-muted">Official Judge</p>
                    </div>
                  `;
                  container.append(item);
              });
              
              // Optionally add Head Tabulator / Organizer hardcoded or fetched?
              // For now just judges as requested.
          }

          // 3. Render Results dynamically
          function renderResults(data) {
              const males = data.filter(d => d.gender && d.gender.toLowerCase() === 'male');
              const females = data.filter(d => d.gender && d.gender.toLowerCase() === 'female');
              const isSplit = males.length > 0 && females.length > 0;

              const container = $('#dynamic-results-container');
              container.empty();

              if (isSplit) {
                  // Render Female First traditionally, then Male
                  renderGenderBlock('Female Rankings', rankArray(females));
                  renderGenderBlock('Male Rankings', rankArray(males));
              } else {
                  renderGenderBlock('Overall Rankings', rankArray(data));
              }
          }

          function rankArray(arr) {
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

                  // Generate Remarks dynamically using ordinal numbers
                  if (rank === 1) {
                      res.remarks = "Champion";
                  } else if (rank === 2) {
                      res.remarks = "1st Runner Up";
                  } else if (rank === 3) {
                      res.remarks = "2nd Runner Up";
                  } else {
                      let runner_up_num = rank - 1;
                      let suffix = 'th';
                      if (runner_up_num % 10 === 1 && runner_up_num % 100 !== 11) {
                          suffix = 'st';
                      } else if (runner_up_num % 10 === 2 && runner_up_num % 100 !== 12) {
                          suffix = 'nd';
                      } else if (runner_up_num % 10 === 3 && runner_up_num % 100 !== 13) {
                          suffix = 'rd';
                      }
                      res.remarks = runner_up_num + suffix + " Runner Up";
                  }
              });
              return arr;
          }

          function renderGenderBlock(title, data) {
              if (data.length === 0) return;

              const winner = data.find(d => d.rank == 1);
              const second = data.find(d => d.rank == 2);
              const third = data.find(d => d.rank == 3);
              
              const podiumIds = [winner?.candidate_id, second?.candidate_id, third?.candidate_id].filter(id => id);

              let html = `
              <div class="mb-5">
                  <h4 class="fw-bold mb-4 text-center text-primary border-bottom pb-2">${title}</h4>
                  <div class="podium-container">
                      ${second ? buildPodiumCard(second, 2) : ''}
                      ${winner ? buildPodiumCard(winner, 1) : ''}
                      ${third  ? buildPodiumCard(third, 3) : ''}
                  </div>
              `;

              html += `
                  <h5 class="fw-bold border-bottom pb-2 mb-3 mt-4">${title} List</h5>
                  <div class="table-responsive mb-5">
                      <table class="table table-bordered align-middle table-responsive-stack">
                          <thead class="bg-light text-center">
                              <tr>
                                  <th style="width: 80px;">Rank</th>
                                  <th>Candidate Name</th>
                                  <th style="width: 150px;">Final Score</th>
                                  <th>Remarks</th>
                              </tr>
                          </thead>
                          <tbody>
              `;

              data.forEach(d => {
                  let remarksClass = d.rank <= 3 ? 'text-success fw-bold' : '';
                  let rankClass = d.rank <= 3 ? 'fw-bold fs-4' : '';
                  const candDept = d.department ? `<br><small class="text-muted"><i class="ti ti-building me-1"></i>${d.department}</small>` : '';

                  html += `
                     <tr>
                        <td class="text-center ${rankClass}" data-label="Rank">${d.rank}</td>
                        <td data-label="Candidate Name">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-light-primary text-primary me-2">#${d.number}</span>
                                <div>
                                    <span class="fw-bold d-block">${d.name}</span>
                                    ${candDept}
                                </div>
                            </div>
                        </td>
                        <td class="text-center fw-bold" data-label="Final Score">${d.score_formatted}</td>
                        <td class="text-center ${remarksClass}" data-label="Remarks">${d.remarks || ''}</td>
                    </tr>
                  `;
              });

              html += `</tbody></table></div></div>`;
              $('#dynamic-results-container').append(html);
          }

          function buildPodiumCard(data, place) {
              let classes = "";
              let crown = "";
              let badgeClass = "badge bg-white text-dark rounded-pill px-3 py-1 fs-5 shadow-sm border mt-3 d-inline-block";
              let imgStyle = "";

              if (place === 1) {
                  classes = "order-first order-md-0";
                  crown = '<i class="ti ti-crown fs-6 mb-1 d-block"></i>';
                  badgeClass = "badge bg-white text-primary rounded-pill px-3 py-2 fs-4 shadow-sm border border-primary mt-3 d-inline-block";
                  imgStyle = 'style="width: 100px; height: 100px; margin-bottom: -50px;"';
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
                          <div class="mt-auto text-center z-index-1"><span class="${badgeClass}">${data.score_formatted}</span></div>
                      </div>
                      <div class="podium-number">${data.rank}</div>
                  </div>
              </div>
              `;
          }


          $('#finalCompSelect').on('change', function() {
              const selectedValue = $(this).val();
              const selectedText = $(this).find("option:selected").text();
              
              currentCompId = selectedValue;
              
              Swal.fire({
                  title: 'Switching Results',
                  text: `Updating report for ${selectedText}...`,
                  allowOutsideClick: false,
                  didOpen: () => {
                      Swal.showLoading();
                  }
              });

              loadResults(selectedValue);

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

          $('#btnPrint').on('click', function() {
              if(currentCompId) {
                  window.open(`print-final-results.php?competition_id=${currentCompId}`, 'printWindow', 'width=1000,height=900,scrollbars=yes,resizable=yes');
              } else {
                  Swal.fire('Error', 'Please select a competition first', 'error');
              }
          });
      });
  </script>
</body>
</html>
