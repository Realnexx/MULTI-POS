<?php
    session_start();
    
    require_once("../auth/config/db_connection.php");
    require_once '../auth/helpers/security_helper.php';

    // Auth check - redirect to login if not authenticated
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }

    // Permission check
    if (!has_permission('Competitions', 'manage', $conn)) {
        header("Location: restricted.php");
        exit();
    }
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Competition - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .plan-select {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .plan-select:hover {
            border-color: #5d87ff !important;
        }
        .plan-select.active {
            border-color: #5d87ff !important;
            background-color: #ecf2ff;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
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

          <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center">
                <div class="col-9">
                  <h4 class="fw-semibold mb-8">Create Competition</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Create Competition</li>
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
                  <h5 class="mb-3">New Competition Details</h5>
                  <form id="createCompetitionForm">
                    <input type="hidden" name="action" value="create">
                    <div class="row">
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="compTitle" class="form-label">Competition Title</label>
                          <input type="text" class="form-control" name="title" id="compTitle" placeholder="e.g. Matahom sa Ubay 2026" required>
                        </div>
                      </div>
                      <div class="col-md-6">
                        <div class="mb-3">
                          <label for="compType" class="form-label">Category</label>
                          <select class="form-select" name="type" id="compType" required>
                            <option value="">Choose category...</option>
                            <optgroup label="Pageantry & Talents">
                                <option value="Beauty Pageant">Beauty Pageant</option>
                                <option value="Talent Show">Talent Show</option>
                                <option value="Dance Competition">Dance Competition</option>
                                <option value="Singing Contest">Singing Contest</option>
                            </optgroup>
                            <optgroup label="Academic & Skill">
                                <option value="Science Fair">Science Fair</option>
                                <option value="Academic Quiz Bee">Academic Quiz Bee</option>
                                <option value="Debate Championship">Debate Championship</option>
                                <option value="Cooking Challenge">Cooking Challenge</option>
                                <option value="Art & Design Expo">Art & Design Expo</option>
                            </optgroup>
                            <optgroup label="Sports & Gaming">
                                <option value="Sports Tournament">Sports Tournament</option>
                                <option value="Basketball League">Basketball League</option>
                                <option value="Chess Tournament">Chess Tournament</option>
                                <option value="Mobile Legends Tournament">Mobile Legends Tournament</option>
                            </optgroup>
                            <optgroup label="Multimedia">
                                <option value="Photography Contest">Photography Contest</option>
                                <option value="Film Festival">Film Festival</option>
                            </optgroup>
                          </select>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-12">
                        <div class="mb-3">
                          <label for="compDesc" class="form-label">Description</label>
                          <textarea class="form-control" name="description" id="compDesc" rows="3" placeholder="Provide a brief description of the event..."></textarea>
                        </div>
                      </div>
                    </div>
                    
                    <div class="row">
                      <div class="col-md-3">
                        <div class="mb-3">
                          <label for="compDate" class="form-label">Start Date</label>
                          <input type="date" class="form-control" name="start_date" id="compDate">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="mb-3">
                          <label for="startTime" class="form-label">Start Time</label>
                          <input type="time" class="form-control" name="start_time" id="startTime">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="mb-3">
                          <label for="endDate" class="form-label">End Date</label>
                          <input type="date" class="form-control" name="end_date" id="endDate">
                        </div>
                      </div>
                      <div class="col-md-3">
                        <div class="mb-3">
                          <label for="endTime" class="form-label">End Time</label>
                          <input type="time" class="form-control" name="end_time" id="endTime">
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-4">
                        <div class="mb-3">
                          <label for="compVenue" class="form-label">Venue</label>
                          <input type="text" class="form-control" name="venue" id="compVenue" placeholder="e.g. Municipal Gym">
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="mb-3">
                          <label for="compStatus" class="form-label">Initial Status</label>
                          <select class="form-select" name="status" id="compStatus">
                            <option value="upcoming">Upcoming</option>
                            <option value="active">Active</option>
                            <option value="draft" selected>Draft</option>
                          </select>
                        </div>
                      </div>
                      <div class="col-md-4">
                        <div class="mb-3">
                          <label for="assignedAdmin" class="form-label">Assigned Lead Admin</label>
                          <select class="form-select" name="lead_admin_id" id="assignedAdmin">
                            <option value="">-- Select Admin --</option>
                          </select>
                        </div>
                      </div>
                    </div>

                    <div class="row mt-4">
                      <div class="col-12">
                         <div class="card bg-light-primary shadow-none mb-3">
                            <div class="card-body p-3">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                    <div class="d-flex align-items-center">
                                        <div class="bg-primary text-white rounded-circle p-2 me-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                            <i class="ti ti-math-function fs-5"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-bold">Scoring Configuration</h6>
                                            <p class="small text-muted mb-0">Select how scores will be calculated</p>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#simulationModal">
                                        <i class="ti ti-device-gamepad me-1"></i> Simulate Formula
                                    </button>
                                </div>
                            </div>
                         </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-12">
                        <div class="mb-3">
                          <label for="compFormula" class="form-label">Scoring Formula</label>
                          <select class="form-select" name="formula_id" id="compFormula">
                            <option value="">-- Select Formula --</option>
                          </select>
                          <div class="form-text text-primary">
                             <i class="ti ti-info-circle me-1"></i> This formula will be applied to all criteria results.
                          </div>
                        </div>
                      </div>
                    </div>

                    <div class="row mt-3">
                      <div class="col-12">
                        <label class="form-label fw-semibold">Select Pricing Plan</label>
                        <div class="row">
                          <div class="col-md-4">
                            <div class="card border shadow-none p-3 mb-3 cursor-pointer plan-select" data-plan="starter">
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="pricing_plan" id="planStarter" value="starter" checked>
                                <label class="form-check-label fw-bold d-block" for="planStarter">
                                  Starter <span class="badge bg-light-primary text-primary ms-1">₱10,000</span>
                                </label>
                                <small class="text-muted">25 Candidates, 5 Judges</small>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="card border shadow-none p-3 mb-3 plan-select" data-plan="pro">
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="pricing_plan" id="planPro" value="pro">
                                <label class="form-check-label fw-bold d-block" for="planPro">
                                  Pro <span class="badge bg-primary text-white ms-1">₱25,000</span>
                                </label>
                                <small class="text-muted">Unlimited Candidates, 15 Judges</small>
                              </div>
                            </div>
                          </div>
                          <div class="col-md-4">
                            <div class="card border shadow-none p-3 mb-3 plan-select" data-plan="enterprise">
                              <div class="form-check">
                                <input class="form-check-input" type="radio" name="pricing_plan" id="planEnterprise" value="enterprise">
                                <label class="form-check-label fw-bold d-block" for="planEnterprise">
                                  Enterprise <span class="badge bg-dark text-white ms-1">Custom</span>
                                </label>
                                <small class="text-muted">Full Support, Multi-day Pageants</small>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="text-center mt-n2 mb-4">
                          <a href="javascript:void(0)" class="text-primary fw-semibold small" data-bs-toggle="modal" data-bs-target="#planDetailModal">
                            <i class="ti ti-info-circle me-1"></i> See detailed plan inclusions
                          </a>
                        </div>
                      </div>
                    </div>

                    <div class="row">
                      <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-2">
                          <input class="form-check-input" type="checkbox" name="enable_audience_voting" id="enableAudienceVoting">
                          <label class="form-check-label fw-semibold" for="enableAudienceVoting">Enable Audience Voting</label>
                        </div>
                      </div>
                    </div>

                   <div class="mt-4 d-flex flex-column flex-sm-row gap-2">
                      <button type="submit" class="btn btn-primary px-4">
                        Save Competition
                      </button>

                      <button type="button"
                              class="btn btn-light-danger text-danger px-4"
                              onclick="history.back()">
                        Cancel
                      </button>
                    </div>

                  </form>
                </div>
              </div>
            </div>
          </div>

                    <!-- Simulation Modal -->
                    <div class="modal fade" id="simulationModal" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content border-0">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title text-white">Formula Simulator</h5>
                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body">
                                    <p class="small text-muted mb-3">Input mock scores (0-100) to see how the selected formula processes them.</p>
                                    
                                    <div id="simScoreInputs" class="row g-2 mb-4 text-center">
                                        <div class="col"><input type="number" class="form-control sim-input" value="85" placeholder="85"></div>
                                        <div class="col"><input type="number" class="form-control sim-input" value="92" placeholder="92"></div>
                                        <div class="col"><input type="number" class="form-control sim-input" value="78" placeholder="78"></div>
                                        <div class="col"><input type="number" class="form-control sim-input" value="95" placeholder="95"></div>
                                        <div class="col"><input type="number" class="form-control sim-input" value="88" placeholder="88"></div>
                                    </div>

                                    <div class="card bg-light border-0">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold">Applied Formula:</span>
                                                <span id="simFormulaName" class="badge bg-primary">Standard Average</span>
                                            </div>
                                            <hr class="my-2 opacity-25">
                                            <div id="simLogicDisplay" class="small text-muted mb-3 italic">
                                                (85 + 92 + 78 + 95 + 88) / 5
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <h4 class="mb-0 fw-bolder">Final Result</h4>
                                                <h2 id="simResultValue" class="text-primary fw-bolder mb-0">87.60%</h2>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-primary px-4 w-100" id="runSimBtn">Update Calculation</button>
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
          // Fetch dynamic dependencies
          loadDependencies();

          function loadDependencies() {
              $.post('endpoints/competitions_handler.php', { action: 'get_dependencies' }, function(response) {
                  if (response.status === 'success') {
                      const data = response.data;
                      
                      // Admins
                      const adminSelect = $('#assignedAdmin');
                      adminSelect.empty().append('<option value="">-- Select Admin --</option>');
                      data.admins.forEach(admin => {
                          adminSelect.append(`<option value="${admin.id}">${admin.username} (${admin.role_name})</option>`);
                      });

                      // Formulas
                      const formulaSelect = $('#compFormula');
                      formulaSelect.empty().append('<option value="">-- Select Formula --</option>');
                      data.formulas.forEach(form => {
                          formulaSelect.append(`<option value="${form.id}">${form.name}</option>`);
                      });
                  }
              });
          }

          // Plan Selection UI
          $('.plan-select').on('click', function() {
              $('.plan-select').removeClass('active');
              $(this).addClass('active');
              $(this).find('input[type="radio"]').prop('checked', true);
          });

          // Form Submission
          $('#createCompetitionForm').on('submit', function(e) {
              e.preventDefault();
              const formData = $(this).serialize();

              Swal.fire({
                  title: 'Confirm Creation',
                  text: "Are you sure you want to save this competition?",
                  icon: 'question',
                  showCancelButton: true,
                  confirmButtonText: 'Yes, save it'
              }).then((result) => {
                  if (result.isConfirmed) {
                      $.post('endpoints/competitions_handler.php', formData, function(response) {
                          if (response.status === 'success') {
                              Swal.fire({
                                  title: 'Success!',
                                  text: response.message,
                                  icon: 'success',
                                  confirmButtonText: 'View Competitions'
                              }).then(() => {
                                  window.location.href = 'competitions.php';
                              });
                          } else {
                              Swal.fire('Error', response.message, 'error');
                          }
                      });
                  }
              });
          });

          // Formula Simulation Logic
          function runSimulation() {
              const formulaId = $('#compFormula').val();
              const scores = $('.sim-input').map(function() { return parseFloat($(this).val()) || 0; }).get();
              
              if (!formulaId) {
                  $('#simLogicDisplay').text('Please select a formula first.');
                  $('#simResultValue').text('--');
                  return;
              }

              $.post('endpoints/formulas_handler.php', { action: 'get', id: formulaId }, function(response) {
                  if (response.status === 'success') {
                      const formula = response.data;
                      const components = JSON.parse(formula.components);
                      const formulaName = formula.name;
                      $('#simFormulaName').text(formulaName);

                      // Simulate evaluation
                      // Note: We'll use a simplified version of the evaluator from formula-builder
                      let expression = '';
                      let logicStr = '';
                      
                      const inputs = {
                          'score': scores.reduce((a, b) => a + b, 0) / scores.length, // Avg for generic score variable
                          'judge_count': scores.length,
                          'category_weight': 20, // Dummy
                          'category_result': scores.reduce((a, b) => a + b, 0),
                          'count': scores.length
                      };

                      components.forEach(comp => {
                          if (comp.type === 'variable') {
                              expression += (inputs[comp.value] || 0) + ' ';
                              logicStr += `[${comp.value}] `;
                          } else if (comp.type === 'function') {
                              expression += comp.value + ' ';
                              logicStr += `${comp.value} `;
                          } else if (comp.value === '×') {
                              expression += '* ';
                              logicStr += '* ';
                          } else if (comp.value === '÷') {
                              expression += '/ ';
                              logicStr += '/ ';
                          } else {
                              expression += comp.value + ' ';
                              logicStr += comp.value + ' ';
                          }
                      });

                      try {
                          let evalExpr = expression.replace(/SUM/g, '').replace(/AVG/g, '').replace(/MAX/g, 'Math.max').replace(/MIN/g, 'Math.min');
                          const result = eval(evalExpr);
                          $('#simResultValue').text((isFinite(result) ? result.toFixed(2) : '0.00') + '%');
                          $('#simLogicDisplay').html(`<strong>Logic:</strong> ${logicStr}<br><small class="text-muted">Simulated using average of scores as variable inputs.</small>`);
                      } catch (e) {
                          $('#simResultValue').text('Error');
                          $('#simLogicDisplay').text('Incompatible formula for quick simulation.');
                      }
                  }
              });
          }

          $('#runSimBtn').on('click', runSimulation);
          $('#simulationModal').on('show.bs.modal', runSimulation);
      });
  </script>
</body>

</html>
