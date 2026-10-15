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
  <title>Formula Builder - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <!-- SortableJS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Sortable/1.15.0/Sortable.min.js"></script>
  <style>
    .draggable-item {
        cursor: grab;
        transition: all 0.2s;
        user-select: none;
    }
    .draggable-item:active {
        cursor: grabbing;
    }
    .formula-canvas {
        min-height: 200px;
        border: 2px dashed #dfe5ef;
        border-radius: 8px;
        background-color: #f8f9fa;
        transition: background-color 0.2s;
    }
    .formula-canvas.drag-over {
        background-color: #eef2f6;
        border-color: #5d87ff;
    }
    .canvas-item {
        display: inline-flex;
        align-items: center;
        margin: 5px;
        padding: 5px 12px;
        background: #fff;
        border: 1px solid #dfe5ef;
        border-radius: 50px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        font-weight: 500;
        cursor: grab;
    }
    .canvas-item .remove-btn {
        margin-left: 8px;
        cursor: pointer;
        opacity: 0.6;
    }
    .canvas-item .remove-btn:hover {
        opacity: 1;
        color: #ff4b4b;
    }
    .canvas-item.operator {
        background-color: #eef2f6;
        color: #2a3547;
    }
    .canvas-item.variable {
        background-color: #ecf2ff;
        color: #5d87ff;
        border-color: #ecf2ff;
    }
    .canvas-item.function {
        background-color: #fbf2ef;
        color: #fa896b;
        border-color: #fbf2ef;
    }
    .formula-preview {
        font-family: 'Courier New', Courier, monospace;
        font-size: 1.1rem;
        background: #2a3547;
        color: #fff;
        padding: 15px;
        border-radius: 8px;
        min-height: 60px;
        display: flex;
        align-items: center;
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
                  <h4 class="fw-semibold mb-8">Formula Builder</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="formula-management.php">Formula Management</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Builder</li>
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
            <!-- Left Column: Toolbar -->
            <div class="col-lg-4 d-none d-lg-block">
                <div class="card sticky-top" style="top: 100px; z-index: 1;">
                    <div class="card-body">
                        <h6 class="card-title fw-semibold mb-3">Components</h6>
                        
                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Variables</label>
                            <div id="toolbar-variables" class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="score">Score</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="rank">Rank</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="weight">Weight</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="deduction">Deduction</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="category_weight">Category Weight</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="judge_count">Judge Count</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="criteria_score">Criteria Score</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="criteria_weighted">Criteria Weighted</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="category_score">Category Score</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="category_weighted">Category Weighted</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="total_category_score">Total Category Score</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="total_criteria_score">Total Criteria Score</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="average_score">Average Score</div>
                                <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="total_score">Total Score</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Numbers</label>
                            <div class="input-group input-group-sm mb-2">
                                <input type="number" id="customNumberInput" class="form-control" placeholder="0.00">
                                <button class="btn btn-outline-primary" type="button" id="addNumberBtn">Add</button>
                            </div>
                            <div id="toolbar-numbers" class="d-flex flex-wrap gap-2">
                                <!-- Generated numbers go here -->
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label text-muted small text-uppercase fw-bold">Operators</label>
                            <div id="toolbar-operators" class="d-flex flex-wrap gap-2">
                                <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="+">+</div>
                                <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="-">-</div>
                                <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="*">×</div>
                                <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="/">÷</div>
                                <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="(">(</div>
                                <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value=")">)</div>
                            </div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label text-muted small text-uppercase fw-bold">Functions</label>
                            <div id="toolbar-functions" class="d-flex flex-wrap gap-2">
                                <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="SUM">SUM</div>
                                <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="AVG">AVG</div>
                                <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="MAX">MAX</div>
                                <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="MIN">MIN</div>
                                <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="COUNT">COUNT</div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Mobile Toolbar (Accordion) -->
            <div class="col-12 d-lg-none mb-3">
                <div class="accordion" id="mobileToolbarAccordion">
                  <div class="accordion-item">
                    <h2 class="accordion-header" id="headingOne">
                      <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <i class="ti ti-tools me-2"></i> Open Component Toolbar
                      </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#mobileToolbarAccordion">
                      <div class="accordion-body">
                         <!-- Standard Toolbar Cloned Here via JS or duplicated structure -->
                         <div class="row">
                            <div class="col-12 mb-3">
                                <label class="fw-bold mb-2">Variables</label>
                                <div id="mobile-toolbar-variables" class="d-grid gap-2" style="grid-template-columns: 1fr 1fr;">
                                     <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="score">Score</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="rank">Rank</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="weight">Weight</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="deduction">Deduction</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="category_weight">Category Weight</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="judge_count">Judge Count</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="criteria_score">Criteria Score</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="criteria_weighted">Criteria Weighted</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="category_score">Category Score</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="category_weighted">Category Weighted</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="total_category_score">Total Category Score</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="total_criteria_score">Total Criteria Score</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="average_score">Average Score</div>
                                    <div class="draggable-item badge bg-light-primary text-primary fs-3 p-2 rounded w-100 text-start" data-type="variable" data-value="total_score">Total Score</div>
                                </div>
                            </div>
                             <div class="col-12 mb-3">
                                <label class="fw-bold mb-2">Operators</label>
                                <div id="mobile-toolbar-operators" class="d-flex flex-wrap gap-2">
                                     <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="+">+</div>
                                    <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="-">-</div>
                                    <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="*">×</div>
                                    <div class="draggable-item badge bg-light text-dark border fs-3 p-2 rounded" data-type="operator" data-value="/">÷</div>
                                </div>
                            </div>
                             <div class="col-12">
                                <label class="fw-bold mb-2">Functions</label>
                                <div id="mobile-toolbar-functions" class="d-flex flex-wrap gap-2">
                                     <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="SUM">SUM</div>
                                    <div class="draggable-item badge bg-light-danger text-danger fs-3 p-2 rounded" data-type="function" data-value="AVG">AVG</div>
                                </div>
                            </div>
                         </div>
                      </div>
                    </div>
                  </div>
                </div>
            </div>

            <!-- Right Column: Canvas & Settings -->
            <div class="col-lg-8">
              <div class="card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-4">
                    <h5 class="mb-0">Formula Definition</h5>
                    <div class="dropdown">
                        <button class="btn btn-outline-info btn-sm dropdown-toggle" type="button" id="scenarioDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-template me-1"></i>Quick Scenarios
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="scenarioDropdown">
                            <li><a class="dropdown-item preset-link" href="javascript:void(0)" data-scenario="category_final">Category Final Score</a></li>
                            <li><a class="dropdown-item preset-link" href="javascript:void(0)" data-scenario="overall_sum">Overall Ranking Score</a></li>
                            <li><a class="dropdown-item preset-link" href="javascript:void(0)" data-scenario="olympic">Olympic Scoring</a></li>
                            <li><a class="dropdown-item preset-link" href="javascript:void(0)" data-scenario="weighted">Weighted Score (%)</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item preset-link text-muted small" href="javascript:void(0)" data-scenario="average">Basic Average</a></li>
                        </ul>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label class="form-label">Formula Name</label>
                    <input type="text" class="form-control" id="formulaName" placeholder="Enter formula name">
                    <input type="hidden" id="formulaId" value="<?php echo $_GET['id'] ?? ''; ?>">
                  </div>

                  <div class="mb-4">
                    <label class="form-label d-flex justify-content-between">
                        <span>Builder Canvas</span>
                        <a href="javascript:void(0)" class="text-danger small text-decoration-none" onclick="clearCanvas()">Clear All</a>
                    </label>
                    <p class="text-muted small mb-2">Drag and drop components here to build your formula.</p>
                    
                    <div id="formula-canvas" class="formula-canvas p-3 mb-3 d-flex flex-wrap align-items-center">
                        <!-- Dropped items will appear here -->
                        <span class="text-muted fst-italic w-100 text-center empty-state">Drop components here</span>
                    </div>

                    <div class="bg-light p-3 rounded border">
                        <label class="form-label text-muted small fw-bold text-uppercase">Visual Interpretation</label>
                        <div id="visual-preview" class="formula-preview">
                            <span class="opacity-50">Formula is empty...</span>
                        </div>
                    </div>
                  </div>

                  <hr>

                  <div class="row mt-4">
                      <div class="col-md-12">
                           <h6 class="fw-semibold mb-3">Settings</h6>
                           <div class="form-check form-switch mb-2">
                              <input class="form-check-input" type="checkbox" role="switch" id="activeSwitch" checked>
                              <label class="form-check-label" for="activeSwitch">Active Formula</label>
                            </div>
                            <div class="form-check form-switch mb-3">
                              <input class="form-check-input" type="checkbox" role="switch" id="publicSwitch">
                              <label class="form-check-label" for="publicSwitch">Visible to Judges</label>
                            </div>
                      </div>
                  </div>
                  
                  <div class="d-flex justify-content-end gap-2 mt-4">
                        <a href="formula-management.php" class="btn btn-outline-danger">Cancel</a>
                        <button type="button" class="btn btn-primary" id="saveFormula">Save Formula</button>
                    </div>

                </div>
              </div>

              <!-- Formula Simulator Card -->
              <div class="card bg-light-primary border-primary-subtle shadow-none">
                  <div class="card-body">
                      <div class="d-flex align-items-center mb-3">
                          <div class="bg-primary text-white rounded p-2 me-3">
                              <i class="ti ti-test-pipe fs-6"></i>
                          </div>
                          <h5 class="card-title mb-0 fw-semibold text-primary">Formula Simulator</h5>
                      </div>
                      <p class="text-muted small mb-4">Test your formula logic with sample values before saving.</p>
                      
                      <div id="simulatorInputs" class="row g-3 mb-4">
                          <!-- Dynamic Inputs will appear here -->
                          <div class="col-12 text-center py-3 opacity-50">
                              <span class="small">Add variables to the canvas to start simulating...</span>
                          </div>
                      </div>

                      <div class="bg-white p-3 rounded border border-primary-subtle">
                          <label class="form-label text-muted small fw-bold text-uppercase d-block mb-2">Simulated Result</label>
                          <div class="d-flex justify-content-between align-items-center">
                              <h2 id="simulationResult" class="mb-0 fw-bold text-primary">--</h2>
                              <button id="runSimulation" class="btn btn-primary btn-sm">
                                  <i class="ti ti-refresh me-1"></i>Recalculate
                              </button>
                          </div>
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
    document.addEventListener('DOMContentLoaded', function () {
        
        const canvasEl = document.getElementById('formula-canvas');
        const formulaId = document.getElementById('formulaId').value;

        // --- SortableJS Initialization ---

        function initToolbar(id, groupName) {
            const el = document.getElementById(id);
            if(el) {
                new Sortable(el, {
                    group: {
                        name: groupName,
                        pull: 'clone',
                        put: false
                    },
                    sort: false,
                    animation: 150
                });
            }
        }

        initToolbar('toolbar-variables', 'shared');
        initToolbar('toolbar-operators', 'shared');
        initToolbar('toolbar-functions', 'shared');
        initToolbar('toolbar-numbers', 'shared');
        initToolbar('mobile-toolbar-variables', 'shared');
        initToolbar('mobile-toolbar-operators', 'shared');
        initToolbar('mobile-toolbar-functions', 'shared');

        new Sortable(canvasEl, {
            group: 'shared',
            animation: 150,
            onAdd: function (evt) {
                const item = evt.item;
                transformToCanvasItem(item);
                updatePreview();
                checkEmptyState();
                initSimulator();
            },
            onUpdate: function () {
                updatePreview();
                initSimulator();
            },
            onRemove: function() {
                updatePreview();
                checkEmptyState();
                initSimulator();
            }
        });

        // --- Loading ---
        if (formulaId) {
            loadFormula(formulaId);
        }

        function loadFormula(id) {
            $.post('endpoints/formulas_handler.php', { action: 'get', id: id }, function(response) {
                if (response.status === 'success') {
                    const data = response.data;
                    $('#formulaName').val(data.name);
                    $('#activeSwitch').prop('checked', data.is_active == 1);
                    $('#publicSwitch').prop('checked', data.is_public == 1);
                    
                    try {
                        const components = JSON.parse(data.components);
                        if (components && components.length > 0) {
                            canvasEl.innerHTML = ''; // Clear empty state
                            components.forEach(comp => {
                                const el = document.createElement('div');
                                el.setAttribute('data-type', comp.type);
                                el.setAttribute('data-value', comp.value);
                                canvasEl.appendChild(el);
                                transformToCanvasItem(el);
                            });
                            updatePreview();
                            checkEmptyState();
                        }
                    } catch (e) {
                        console.error("Error parsing components", e);
                    }
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        }

        // --- Logic ---

        function transformToCanvasItem(element) {
            const type = element.getAttribute('data-type');
            const value = element.getAttribute('data-value');
            
            element.className = '';
            element.classList.add('canvas-item');
            if(type) element.classList.add(type);

            element.innerHTML = `
                <span>${value}</span>
                <i class="ti ti-x remove-btn"></i>
            `;

            element.querySelector('.remove-btn').addEventListener('click', function() {
                element.remove();
                updatePreview();
                checkEmptyState();
            });
        }

        function updatePreview() {
            const items = canvasEl.querySelectorAll('.canvas-item');
            let formulaString = '';
            
            if (items.length === 0) {
                document.getElementById('visual-preview').innerHTML = '<span class="opacity-50">Formula is empty...</span>';
                return;
            }

            items.forEach(item => {
                const val = item.getAttribute('data-value');
                let displayVal = val;
                if(DisplayMap[val]) displayVal = DisplayMap[val];
                formulaString += displayVal + ' ';
            });

            document.getElementById('visual-preview').textContent = formulaString.trim();
        }
        
        function checkEmptyState() {
             const items = canvasEl.querySelectorAll('.canvas-item');
             const emptyState = canvasEl.querySelector('.empty-state');
             if (items.length > 0) {
                 if(emptyState) emptyState.style.display = 'none';
             } else {
                 if(emptyState) {
                     emptyState.style.display = 'block';
                 } else {
                      canvasEl.innerHTML = '<span class="text-muted fst-italic w-100 text-center empty-state">Drop components here</span>';
                 }
             }
        }

        window.clearCanvas = function() {
            canvasEl.innerHTML = '<span class="text-muted fst-italic w-100 text-center empty-state">Drop components here</span>';
            updatePreview();
        };

        // --- Saving ---
        $('#saveFormula').on('click', function() {
            const name = $('#formulaName').val();
            const id = $('#formulaId').val();
            const items = canvasEl.querySelectorAll('.canvas-item');
            
            if (!name) {
                Swal.fire('Warning', 'Please enter a formula name', 'warning');
                return;
            }

            if (items.length === 0) {
                Swal.fire('Warning', 'Formula canvas is empty', 'warning');
                return;
            }

            const components = [];
            let expression = '';

            items.forEach(item => {
                const type = item.getAttribute('data-type');
                const value = item.getAttribute('data-value');
                components.push({ type, value });
                expression += value + ' ';
            });

            const data = {
                action: 'save',
                id: id,
                name: name,
                expression: expression.trim(),
                components: JSON.stringify(components),
                is_active: $('#activeSwitch').is(':checked') ? 1 : 0,
                is_public: $('#publicSwitch').is(':checked') ? 1 : 0
            };

            $.post('endpoints/formulas_handler.php', data, function(response) {
                if (response.status === 'success') {
                    Swal.fire('Success', response.message, 'success').then(() => {
                        window.location.href = 'formula-management.php';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            });
        });

        // --- Numeric Input ---
        $('#addNumberBtn').on('click', function() {
            const val = $('#customNumberInput').val();
            if (val === '' || isNaN(val)) return;
            
            const numDiv = document.createElement('div');
            numDiv.className = 'draggable-item badge bg-light text-dark border fs-3 p-2 rounded';
            numDiv.setAttribute('data-type', 'number');
            numDiv.setAttribute('data-value', val);
            numDiv.textContent = val;
            
            document.getElementById('toolbar-numbers').appendChild(numDiv);
            $('#customNumberInput').val('');
        });

        // --- Presets ---
        $('.preset-link').on('click', function() {
            const scenario = $(this).data('scenario');
            let components = [];

            if (scenario === 'category_final') {
                components = [
                    { type: 'operator', value: '(' },
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'score' },
                    { type: 'operator', value: '*' },
                    { type: 'variable', value: 'category_weight' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '/' },
                    { type: 'number', value: '100' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '/' },
                    { type: 'variable', value: 'judge_count' }
                ];
                $('#formulaName').val('Category Final Score (Weighted)');
            } else if (scenario === 'overall_sum') {
                components = [
                    { type: 'function', value: 'SUM' },
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'category_result' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '/' },
                    { type: 'variable', value: 'judge_count' }
                ];
                $('#formulaName').val('Overall Average Result');
            } else if (scenario === 'olympic') {
                components = [
                    { type: 'operator', value: '(' },
                    { type: 'function', value: 'SUM' },
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'score' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '-' },
                    { type: 'function', value: 'MAX' },
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'score' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '-' },
                    { type: 'function', value: 'MIN' },
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'score' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '/' },
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'judge_count' },
                    { type: 'operator', value: '-' },
                    { type: 'number', value: '2' },
                    { type: 'operator', value: ')' }
                ];
                $('#formulaName').val('Olympic Scoring');
            } else if (scenario === 'weighted') {
                 components = [
                    { type: 'operator', value: '(' },
                    { type: 'variable', value: 'score' },
                    { type: 'operator', value: '*' },
                    { type: 'variable', value: 'category_weight' },
                    { type: 'operator', value: ')' },
                    { type: 'operator', value: '/' },
                    { type: 'number', value: '100' }
                ];
                $('#formulaName').val('Weighted Category Score');
            } else if (scenario === 'average') {
                components = [
                    { type: 'variable', value: 'score' },
                    { type: 'operator', value: '/' },
                    { type: 'variable', value: 'judge_count' }
                ];
            }

            if (components.length > 0) {
                canvasEl.innerHTML = '';
                components.forEach(comp => {
                    const el = document.createElement('div');
                    el.setAttribute('data-type', comp.type);
                    el.setAttribute('data-value', comp.value);
                    canvasEl.appendChild(el);
                    transformToCanvasItem(el);
                });
                updatePreview();
                checkEmptyState();
                initSimulator();
            }
        });

        // --- Simulator Logic ---
        function initSimulator() {
            const items = canvasEl.querySelectorAll('.canvas-item[data-type="variable"]');
            const variables = new Set();
            items.forEach(item => variables.add(item.getAttribute('data-value')));

            const container = $('#simulatorInputs');
            const currentValues = {};
            
            // Preserve current values if possible
            container.find('input').each(function() {
                currentValues[$(this).attr('name')] = $(this).val();
            });

            container.empty();

            if (variables.size === 0) {
                container.append('<div class="col-12 text-center py-3 opacity-50"><span class="small">Add variables to the canvas to start simulating...</span></div>');
                $('#simulationResult').text('--');
                return;
            }

            variables.forEach(v => {
                const label = DisplayMap[v] || v;
                const val = currentValues[v] || '';
                const field = `
                    <div class="col-md-6 col-lg-4">
                        <label class="small fw-bold text-muted">${label}</label>
                        <input type="number" name="${v}" class="form-control form-control-sm sim-input" value="${val}" placeholder="0.00">
                    </div>
                `;
                container.append(field);
            });

            runSimulation();
        }

        $(document).on('input', '.sim-input', runSimulation);
        $('#runSimulation').on('click', runSimulation);

        function runSimulation() {
            const items = canvasEl.querySelectorAll('.canvas-item');
            if (items.length === 0) return;

            const inputs = {};
            $('.sim-input').each(function() {
                inputs[$(this).attr('name')] = parseFloat($(this).val()) || 0;
            });

            let expression = '';
            items.forEach(item => {
                const type = item.getAttribute('data-type');
                const val = item.getAttribute('data-value');

                if (type === 'variable') {
                    expression += (inputs[val] || 0) + ' ';
                } else if (type === 'function') {
                    expression += val + ' '; 
                } else if (val === '×') {
                    expression += '* ';
                } else if (val === '÷') {
                    expression += '/ ';
                } else {
                    expression += val + ' ';
                }
            });

            try {
                // Pre-process basic functions for simulation
                let evalExpr = expression.replace(/SUM/g, '')
                                         .replace(/AVG/g, '')
                                         .replace(/COUNT/g, '')
                                         .replace(/MAX/g, 'Math.max')
                                         .replace(/MIN/g, 'Math.min');
                
                const result = eval(evalExpr);
                $('#simulationResult').text(isFinite(result) ? result.toFixed(2) : 'Error');
            } catch (e) {
                $('#simulationResult').text('Error');
            }
        }

        const DisplayMap = {
            '*': '×',
            '/': '÷',
            'SUM': 'SUM',
            'AVG': 'AVERAGE',
            'MAX': 'MAX',
            'MIN': 'MIN',
            'COUNT': 'COUNT',
            'score': '[Score]',
            'rank': '[Rank]',
            'weight': '[Weight]',
            'deduction': '[Deduction]',
            'category_weight': '[Category Weight]',
            'judge_count': '[Judge Count]',
            'category_result': '[Category Result Sum]',
            'criteria_score': '[Criteria Score]',
            'criteria_weighted': '[Criteria Weighted]',
            'category_score': '[Category Score]',
            'category_weighted': '[Category Weighted]',
            'total_category_score': '[Total Category Score]',
            'total_criteria_score': '[Total Criteria Score]',
            'average_score': '[Average Score]',
            'total_score': '[Total Score]'
        };

        // Initialize simulator if elements already exist (on load)
        initSimulator();

    });
  </script>
</body>
</html>
