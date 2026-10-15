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
    <title>Judge Scoring - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .candidate-card { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border: none; border-radius: 20px; overflow: hidden; }
        .candidate-card:hover { transform: translateY(-10px); box-shadow: 0 20px 40px rgba(0,0,0,0.1) !important; }
        .candidate-img-wrapper { position: relative; height: 350px; overflow: hidden; }
        .candidate-img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
        .candidate-card:hover .candidate-img { transform: scale(1.05); }
        .score-input { border-radius: 12px; border: 2px solid #f1f5f9; padding: 12px; font-weight: 700; text-align: center; font-size: 1.25rem; transition: all 0.2s; }
        .score-input:focus { border-color: var(--bs-primary); background-color: #f8fafc; outline: none; box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.1); }
        .criteria-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; font-weight: 700; margin-bottom: 8px; display: block; }
        .candidate-number { position: absolute; top: 15px; left: 15px; background: rgba(0,0,0,0.7); backdrop-filter: blur(10px); color: white; padding: 6px 16px; border-radius: 50px; font-weight: 800; font-size: 1rem; z-index: 2; border: 1px solid rgba(255,255,255,0.25); box-shadow: 0 4px 15px rgba(0,0,0,0.4); }
        .submit-all-btn { height: 60px; border-radius: 15px; font-weight: 800; font-size: 1.1rem; letter-spacing: 0.5px; }
        .nav-category { 
            border: none; 
            border-bottom: 3px solid transparent; 
            padding: 12px 20px; 
            font-weight: 700; 
            color: #64748b; 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            position: relative;
            min-width: 120px;
            text-align: center;
        }
        .nav-category:hover { color: var(--bs-primary); filter: brightness(0.95); }
        .nav-category.active { 
            color: white !important; 
            background: linear-gradient(135deg, var(--bs-primary) 10%, #2563eb 100%) !important; 
            border-bottom-color: #1d4ed8; 
            box-shadow: 0 10px 20px rgba(66, 133, 244, 0.2);
            transform: translateY(-2px);
            border-radius: 12px 12px 0 0;
        }
        .nav-tabs { 
            border-bottom: 2px solid #f1f5f9; 
            gap: 12px; 
            padding: 0 15px;
            flex-wrap: nowrap;
            overflow-x: auto;
            -ms-overflow-style: none;
            scrollbar-width: none;
            white-space: nowrap;
            display: flex;
            padding-bottom: 1px;
        }
        .nav-tabs::-webkit-scrollbar { display: none; }
        
        .category-tab-container { 
            background: white; 
            border-radius: 20px; 
            margin-bottom: 30px; 
            padding-top: 15px; 
            position: sticky; 
            top: 70px; 
            z-index: 100;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .category-tab-container::after {
            content: '';
            position: absolute;
            right: 0;
            top: 15px;
            bottom: 0;
            width: 50px;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.9));
            pointer-events: none;
            border-radius: 0 20px 20px 0;
            z-index: 101;
        }
        .total-score-badge {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 2px solid var(--bs-primary);
            border-radius: 12px;
            padding: 10px 18px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.25);
        }
        .name-overlay {
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.7) 50%, transparent 100%);
            text-shadow: 0 2px 5px rgba(0,0,0,0.9);
        }
        .locked-badge {
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            border: 1px solid rgba(255,255,255,0.3);
            font-size: 0.85rem;
            padding: 6px 14px;
        }

        /* Category Badges Styling */
        .cat-badge {
            font-weight: 700;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
        }
        .nav-category.active .cat-badge { 
            background: rgba(255, 255, 255, 0.2) !important;
            color: white !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
        }
        .nav-category:not(.active) .cat-badge-major { background-color: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .nav-category:not(.active) .cat-badge-minor { background-color: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
        .nav-category:not(.active) .cat-badge-standard { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .nav-category:not(.active) .cat-badge-special { background-color: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        
        .cat-completed-check {
            width: 18px;
            height: 18px;
            background: #10b981;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 9px;
            margin-left: 5px;
            box-shadow: 0 2px 5px rgba(16, 185, 129, 0.3);
            border: 2px solid white;
            flex-shrink: 0;
        }
        .nav-category.active .cat-completed-check {
            background: white;
            color: var(--bs-primary);
            box-shadow: none;
            border-color: rgba(255,255,255,0.5);
        }
        
        .cat-badge-container {
            display: flex;
            gap: 4px;
            margin-top: 4px;
            justify-content: center;
        }
        .cat-name-container {
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body>
    <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
        data-sidebar-position="fixed" data-header-position="fixed">

        <?php include 'components/sidebar.php'; ?>
        
        <div class="body-wrapper">
            <div class="body-wrapper-inner">
                <div class="container-fluid">
                    <?php include 'components/header.php'; ?>

                    <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">Live Scoring Portal</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Scoring Mode</li>
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

                    <div class="row align-items-center mb-4 mt-2" id="scoringPortalHeader">
                        <div class="col-md-10">
                            <span class="badge bg-primary-subtle text-primary fw-bolder px-3 py-2 rounded-pill mb-2">LIVE SCORING PORTAL</span>
                            <h2 class="fw-bolder mb-0" id="competitionTitle">Loading...</h2>
                            <p class="text-muted mb-0" id="currentCategoryText">Awaiting category selection...</p>
                        </div>
                    </div>

                    <div id="unauthorizedMessage" class="d-none">
                        <div class="row justify-content-center py-5">
                            <div class="col-md-7 text-center">
                                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                                    <div class="card-body p-5">
                                        <div class="mb-4">
                                            <i class="ti ti-lock-square-rounded fs-13 text-danger"></i>
                                        </div>
                                        <h2 class="fw-bolder mb-3" id="errorTitle">Access Restricted</h2>
                                        <p class="text-muted fs-4 mb-4" id="errorMessage">This portal is reserved for judges only. Please contact the administrator if you believe this is a mistake.</p>
                                        <a href="index.php" class="btn btn-primary rounded-pill px-5 py-3 fw-bold">Return to Dashboard</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="scoringInterface" class="d-none">
                        <div class="category-tab-container shadow-sm mb-4">
                            <ul class="nav nav-tabs" id="categoryTabs" role="tablist">
                                <!-- Categories loaded via AJAX -->
                            </ul>
                        </div>

                        <div id="scoringLockedAlert" class="alert alert-warning border-0 shadow-sm rounded-4 mb-4 d-none">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-lock fs-7 me-3"></i>
                                    <div>
                                        <h5 class="alert-heading fw-bold mb-1">Scoring Locked</h5>
                                        <p class="mb-0">You have already finalized and locked the scores for this category. Changes are no longer permitted.</p>
                                    </div>
                                </div>
                                <button id="selfUnlockBtn" class="btn btn-sm btn-outline-warning fw-bold ms-4 flex-shrink-0">
                                    <i class="ti ti-lock-open me-1"></i> Unlock
                                    <span id="unlockCountBadge" class="badge bg-warning text-dark ms-1">Used: 0</span>
                                </button>
                            </div>
                        </div>

                        <div class="row" id="scoringContentWrapper">
                            <!-- Sidebar for departments -->
                            <div class="col-md-6 col-lg-5 col-xl-4 mb-4 d-none" id="departmentFilterColumn">
                                <div class="sticky-top" style="top: 80px; z-index: 10;">
                                    <h6 class="fw-bolder text-primary mb-3 text-uppercase"><i class="ti ti-filter me-2"></i>Filter by Dept</h6>
                                    <div class="list-group shadow-sm" id="inPageDeptFilter">
                                        <!-- Populated via JS -->
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Candidates Main Area -->
                            <div class="col-md-6 col-lg-7 col-xl-8" id="candidatesMainArea">
                                <div class="row" id="candidatesContainer">
                                    <!-- Dynamically loaded -->
                                </div>
                                
                                <!-- Placeholder when no department is selected -->
                                <div id="noDeptSelectedPlaceholder" class="col-12 text-center py-5 d-none">
                                    <div class="mb-3"><iconify-icon icon="solar:buildings-2-bold-duotone" class="display-3 text-primary opacity-50"></iconify-icon></div>
                                    <h3 class="text-dark fw-bold">Select a Department</h3>
                                    <p class="text-muted fs-4 max-w-500 mx-auto">Please select a department from the list on the left to view and score the candidates.</p>
                                </div>
                            </div>
                        </div>

                        <div class="card mt-4 border-primary border-2 shadow-lg mb-5 d-none" id="finalizeCard" style="border-style: dashed;">
                            <div class="card-body p-4 text-center">
                                <h4 class="fw-bolder text-primary mb-3">Finalize All Scores</h4>
                                <p class="text-muted mb-4 mx-auto" style="max-width: 600px;">Review all entries for accuracy. Once submitted, you cannot change the scores for this category without administrator intervention.</p>
                                <button class="btn btn-primary px-5 py-3 rounded-pill fw-bold" id="finalSubmitBtn">
                                    <i class="ti ti-upload me-2 fs-5"></i> SUBMIT & LOCK FINAL RESULTS
                                </button>
                            </div>
                        </div>
                    </div>

                    <?php include 'components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Finalize Summary Modal -->
    <div class="modal fade" id="finalizeSummaryModal" tabindex="-1" aria-labelledby="finalizeSummaryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white p-4 rounded-top-4">
                    <h5 class="modal-title fw-bolder text-white" id="finalizeSummaryModalLabel">Scoring Summary & Final Review</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm rounded-3 mb-4">
                        <div class="d-flex">
                            <i class="ti ti-info-circle fs-6 me-2"></i>
                            <div>
                                <h6 class="fw-bold mb-1">Final Review Required</h6>
                                <p class="mb-0 small">Please review the rankings below. Once you confirm, scores for this category will be <strong>permanently locked</strong>.</p>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Rank</th>
                                    <th>Candidate</th>
                                    <th class="text-center">Total Score</th>
                                    <th class="text-center pe-4">Status</th>
                                </tr>
                            </thead>
                            <tbody id="summaryTableBody">
                                <!-- Populated via JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer p-4 pt-0 border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Go Back & Edit</button>
                    <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" id="confirmLockBtn">
                        <i class="ti ti-lock me-2"></i> CONFIRM & LOCK FINAL RESULTS
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'components/scripts.php'; ?>
    <script>
        let currentCompId = 0;
        let currentCatId = 0;
        let currentAssignmentId = 0;
        let isLocked = false;
        let unlockLimit = 3;
        let categoryUnlockCounts = {};

        // --- HELPER WRAPPER FOR CANDIDATE CARD ---
    function generateCandidateCard(cand, criteriaList, scoresArray, candLocksMap, isCategoryLocked, layoutType = 'standalone') {
        const candId = cand.id;
        
        // Find if candidate is locked
        const candLock = candLocksMap.find(l => l.candidate_id == candId);
        const isCandLocked = isCategoryLocked || (candLock && candLock.is_locked == 1);
        const inputState = isCandLocked ? 'disabled readonly' : '';
        const rowId = `cand-row-${candId}`;
        const lockBadge = isCandLocked ? `<span class="badge bg-danger locked-badge rounded-pill"><i class="ti ti-lock me-1"></i>Locked</span>` : '';
        
        // Adjust elements based on layout
        const isComp = layoutType === 'paired';
        const imgHeight = isComp ? '350px' : '400px'; // Shorter image for paired, increased from 250px
        const genderFs = isComp ? 'fs-5' : 'fs-4';
        const nameFs = isComp ? 'fs-5' : 'h4';
        
        const genderIcon = cand.gender === 'male' ? `<i class="ti ti-gender-male text-info ${genderFs} shadow-sm bg-white rounded-circle p-1" style="line-height:1;"></i>` : (cand.gender === 'female' ? `<i class="ti ti-gender-female text-danger ${genderFs} shadow-sm bg-white rounded-circle p-1" style="line-height:1;"></i>` : '');
        
        let criteriaHtml = '';
        if (isComp) {
             // Compact paired layout: inputs in a row, flexed
             criteriaHtml += `<div class="row g-2">`;
             criteriaList.forEach(crit => {
                const dbScore = scoresArray.find(s => s.candidate_id == cand.id && s.criteria_id == crit.id);
                // Check local storage as backup/latest
                const localScore = localStorage.getItem(`aurora_score_${currentAssignmentId}_${currentCatId}_${candId}_${crit.id}`);
                const scoreVal = (localScore !== null && localScore !== '') ? localScore : (dbScore ? dbScore.score_value : '');
                
                criteriaHtml += `
                    <div class="col-12 mb-2">
                        <div class="bg-light rounded px-3 py-2">
                            <label class="criteria-label d-block text-truncate mb-1" style="font-size: 0.70rem;" title="${crit.name}">
                                ${crit.name} <span class="text-primary fw-bold">(${parseFloat(crit.weight_percent).toFixed(1)}%)</span>
                            </label>
                            <input type="number" 
                                   class="form-control score-input text-center fw-bold w-100" 
                                   placeholder="0.00" 
                                   min="0" 
                                   max="${crit.weight_percent}" 
                                   step="0.01"
                                   data-cand="${cand.id}"
                                   data-crit="${crit.id}"
                                   value="${scoreVal}"
                                   ${inputState}>
                        </div>
                    </div>`;
            });
            criteriaHtml += `</div>`;
        } else {
            // Standalone layout: stacked inputs
            criteriaList.forEach(crit => {
                const dbScore = scoresArray.find(s => s.candidate_id == cand.id && s.criteria_id == crit.id);
                const localScore = localStorage.getItem(`aurora_score_${currentAssignmentId}_${currentCatId}_${candId}_${crit.id}`);
                const scoreVal = (localScore !== null && localScore !== '') ? localScore : (dbScore ? dbScore.score_value : '');
                
                criteriaHtml += `
                    <div class="col-12 mb-3">
                        <label class="criteria-label">${crit.name} (${parseFloat(crit.weight_percent).toFixed(1)}%)</label>
                        <input type="number" 
                               class="form-control score-input" 
                               placeholder="0.00" 
                               min="0" 
                               max="${crit.weight_percent}" 
                               step="0.01"
                               data-cand="${cand.id}"
                               data-crit="${crit.id}"
                               value="${scoreVal}"
                               ${inputState}>
                    </div>`;
            });
        }

        const photoSrc = cand.photo_url ? (cand.photo_url.startsWith('uploads') ? '../' + cand.photo_url : cand.photo_url) : 'https://via.placeholder.com/400x500?text=No+Photo';

        let html = `
        <div class="card candidate-card shadow-sm h-100 ${isCandLocked ? 'opacity-75' : ''}" id="${rowId}">
            <div class="candidate-img-wrapper" style="height: ${imgHeight}; overflow: hidden; position: relative;">
                <div class="candidate-number" style="${isComp ? 'font-size: 1rem; padding: 5px 15px;' : ''}">#${cand.candidate_number}</div>
                <div class="position-absolute top-0 end-0 m-2 m-md-3 z-1 d-flex flex-column align-items-end gap-2">
                    ${lockBadge}
                    <div class="total-score-badge text-center" style="${isComp ? 'padding: 8px;' : ''}">
                        <small class="criteria-label mb-1" style="font-size: ${isComp?'0.55rem':'0.65rem'}; line-height: 1; font-weight: 800;">TOTAL SCORE</small>
                        <div class="fw-bolder ${isComp?'fs-5':'fs-4'} text-primary cand-total-display" id="total-display-${cand.id}" style="line-height: 1;">0.00</div>
                    </div>
                </div>
                <img src="${photoSrc}" class="candidate-img ${isComp ? '' : 'w-100'}" style="${isComp ? 'width: 100%; height: 100%; object-fit: cover; object-position: top;' : ''}" alt="Candidate">
                <div class="position-absolute bottom-0 start-0 w-100 p-3 p-md-4 text-white name-overlay" style="${isComp ? 'padding: 10px !important; background: linear-gradient(to top, rgba(0,0,0,0.9), transparent);' : ''}">
                    <div class="mb-1 fw-bold text-white mb-0 d-flex align-items-center gap-2 ${nameFs}" style="text-shadow: 0 2px 4px rgba(0,0,0,0.8);">
                        ${cand.first_name} ${cand.last_name} ${genderIcon}
                    </div>
                    <p class="mb-0 fw-semibold" style="font-size: ${isComp?'0.75rem':'0.875rem'}; text-shadow: 0 1px 3px rgba(0,0,0,0.8); color: #e2e8f0;">From: ${cand.hometown || 'N/A'}</p>
                </div>
            </div>
            <div class="card-body p-3 p-md-4 d-flex flex-column">
                <div class="${isComp ? 'flex-grow-1 d-flex align-items-center' : 'row'}">
                    <div class="w-100">
                        ${criteriaHtml}
                    </div>
                </div>
            </div>
        </div>`;
        
        return html;
    }

    // --- IN-PAGE FILTER INJECTION ---
    function injectInPageFilters(departmentsData) {
        const filterContainer = $('#inPageDeptFilter');
        filterContainer.empty();
        
        if (departmentsData.length > 0) {
            $('#departmentFilterColumn').removeClass('d-none');
            $('#candidatesMainArea').removeClass('col-md-12 col-lg-12 col-xl-12').addClass('col-md-6 col-lg-7 col-xl-8');
            
            let html = '';
            
            departmentsData.forEach(deptInfo => {
                const dept = deptInfo.name;
                const progressText = `${deptInfo.scored}/${deptInfo.total}`;
                const isComplete = deptInfo.scored === deptInfo.total && deptInfo.total > 0;
                
                // Color badges depending on completion status
                const badgeBg = isComplete ? 'bg-success' : 'bg-warning border border-warning text-dark';
                
                html += `
                <button type="button" class="list-group-item list-group-item-action dept-filter-btn border-0 mb-2 rounded-3 shadow-sm" data-dept="${dept}" style="transition: all 0.2s ease;">
                    <div class="d-flex align-items-center p-1 w-100">
                        <div class="bg-light-primary text-primary rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 40px; height: 40px;">
                            <i class="ti ti-building fs-6"></i>
                        </div>
                        <span class="fs-4 fw-semibold text-wrap flex-grow-1 text-start" style="line-height: 1.2;">${dept}</span>
                        <span class="badge ${badgeBg} ms-2 rounded-pill px-2 py-1">${progressText}</span>
                    </div>
                </button>`;
            });
            
            filterContainer.html(html);
            
            // Bind click events to filters
            $('.dept-filter-btn').off('click').on('click', function(e) {
                e.preventDefault();
                // Reset all
                $('.dept-filter-btn').removeClass('active bg-primary text-white shadow-lg').addClass('bg-white text-dark');
                $('.dept-filter-btn').find('.bg-light-primary').removeClass('bg-white text-primary').addClass('bg-light-primary text-primary');
                
                // Set active
                const btn = $(this);
                btn.removeClass('bg-white text-dark').addClass('active bg-primary text-white shadow-lg');
                btn.find('.bg-light-primary').removeClass('bg-light-primary text-primary').addClass('bg-white text-primary');
                
                const selectedDept = btn.data('dept');
                sessionStorage.setItem('lastDeptTab', selectedDept);
                
                $('#noDeptSelectedPlaceholder').addClass('d-none');
                $('#candidatesContainer').removeClass('d-none');
                
                $('.department-group').hide();
                $(`.department-group[data-dept="${selectedDept}"]`).fadeIn();
            });
            
            // Initial state: hide candidates, show placeholder
            $('#candidatesContainer').addClass('d-none');
            $('#noDeptSelectedPlaceholder').removeClass('d-none');
        } else {
            $('#departmentFilterColumn').addClass('d-none');
            $('#candidatesMainArea').removeClass('col-md-6 col-lg-7 col-xl-8').addClass('col-md-12 col-lg-12 col-xl-12');
            $('#candidatesContainer').removeClass('d-none');
            $('#noDeptSelectedPlaceholder').addClass('d-none');
        }
    }

    $(document).ready(function() {
            function initJudge() {
                AuroraLoader.showOverlay('#main-wrapper', 'Validating Judge Identity...');
                $.post('endpoints/scoring_handler.php', { action: 'get_judge_data' }, function(response) {
                    AuroraLoader.hideOverlay('#main-wrapper');
                    if (response.status === 'success') {
                        const data = response.data;
                        currentCompId = data.competition.competition_id;
                        currentAssignmentId = data.competition.assignment_id;
                        unlockLimit = parseInt(data.competition.unlock_limit || 3);
                        $('#competitionTitle').text(data.competition.competition_title);
                        $('#scoringInterface').removeClass('d-none');

                        if (data.categories.length > 0) {
                            let tabsHtml = '';
                            
                            // Helper to decode HTML entities like &amp; to &
                            const decodeHtml = (html) => {
                                const txt = document.createElement("textarea");
                                txt.innerHTML = html;
                                return txt.value;
                            };

                            data.categories.forEach((cat, index) => {
                                const cleanName = decodeHtml(cat.name);
                                let badgeHtml = '';
                                
                                if (cat.level) {
                                    const lvl = cat.level.toString().toLowerCase();
                                    const badgeClass = lvl === 'major' ? 'cat-badge-major' : 'cat-badge-minor';
                                    badgeHtml += `<span class="badge cat-badge ${badgeClass} rounded-pill px-2 py-1" style="font-size: 0.6rem; text-transform: uppercase;">${cat.level}</span>`;
                                }

                                if (cat.type) {
                                    const tpe = cat.type.toString().toLowerCase();
                                    const typeClass = tpe === 'standard' ? 'cat-badge-standard' : 'cat-badge-special';
                                    badgeHtml += `<span class="badge cat-badge ${typeClass} rounded-pill px-2 py-1" style="font-size: 0.6rem; text-transform: uppercase;">${cat.type}</span>`;
                                }

                                const isLocked = cat.is_locked == 1;
                                categoryUnlockCounts[cat.id] = parseInt(cat.unlock_count || 0);
                                const checkHtml = isLocked ? `<div class="cat-completed-check" title="Finalized"><i class="ti ti-check"></i></div>` : '';

                                tabsHtml += `
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link nav-category d-flex flex-column ${index === 0 ? '' : ''}" 
                                                data-id="${cat.id}" 
                                                data-name="${cleanName}" 
                                                type="button">
                                            <div class="cat-name-container">
                                                <span>${cleanName}</span>
                                                ${checkHtml}
                                            </div>
                                            <div class="cat-badge-container">
                                                ${badgeHtml}
                                            </div>
                                        </button>
                                    </li>`;
                            });
                            $('#categoryTabs').html(tabsHtml);
                            
                            // Auto-load saved category or first category
                            let savedCat = sessionStorage.getItem('lastCategoryTab');
                            let targetCat = $('.nav-category').filter(`[data-id="${savedCat}"]`);
                            if (targetCat.length) {
                                targetCat.click();
                            } else {
                                $('.nav-category').first().click();
                            }
                        } else {
                            $('#candidatesContainer').html('<div class="col-12 text-center py-5"><p class="text-muted fs-4">No categories have been created for this competition yet.</p></div>');
                        }
                    } else {
                        $('#competitionTitle').text('Access restricted');
                        $('#currentCategoryText').text('Security Validation Failed');
                        $('#unauthorizedMessage').removeClass('d-none');
                        $('#errorTitle').text(response.type === 'unauthorized' ? 'Unauthorized Access' : 'No Assignment');
                        $('#errorMessage').text(response.message);
                    }
                }).fail(function(jqxhr, textStatus, error) {
                    AuroraLoader.hideOverlay('#main-wrapper');
                    console.error('initJudge AJAX Error:', textStatus, error, jqxhr.responseText);
                    $('#unauthorizedMessage').removeClass('d-none');
                    $('#errorTitle').text('Connection Error');
                    $('#errorMessage').text('Could not communicate with the server. Please refresh the page or contact support.');
                });
            }

            function loadCategory(catId, catName) {
                currentCatId = catId;
                isLocked = false;
                $('#scoringLockedAlert').addClass('d-none');
                $('#finalizeCard').addClass('d-none');
                
                // Show Skeletons
                let skeletonHtml = '';
                for(let i=0; i<3; i++) skeletonHtml += AuroraLoader.getSkeletonCard();
                $('#candidatesContainer').html(skeletonHtml);

                $.post('endpoints/scoring_handler.php', { 
                    action: 'get_category_details', 
                    category_id: catId, 
                    competition_id: currentCompId 
                }, function(response) {
                    if (response.status === 'success') {
                        const data = response.data;
                        // Store full data globally as backup for indicator refreshes
                        window.currentCategoryData = data; 
                        
                        const catLocked = data.is_locked == 1;
                        const unlockCount = parseInt(data.unlock_count || 0);
                        categoryUnlockCounts[catId] = unlockCount; // Keep our local map updated

                        isLocked = catLocked; // Update global state
                        if (catLocked) {
                            $('#scoringLockedAlert').removeClass('d-none');
                            
                            const remaining = Math.max(0, unlockLimit - unlockCount);
                            $('#unlockCountBadge').text(`${unlockCount}/${unlockLimit} Used`);
                            
                            if (unlockCount >= unlockLimit) {
                                $('#selfUnlockBtn').prop('disabled', true)
                                    .addClass('btn-outline-secondary').removeClass('btn-outline-warning')
                                    .attr('title', 'Unlock limit reached');
                                $('#unlockCountBadge').removeClass('bg-warning').addClass('bg-secondary text-white');
                            } else {
                                $('#selfUnlockBtn').prop('disabled', false)
                                    .addClass('btn-outline-warning').removeClass('btn-outline-secondary')
                                    .attr('title', `You have ${remaining} unlocks remaining`);
                                $('#unlockCountBadge').removeClass('bg-secondary text-white').addClass('bg-warning');
                            }

                            $('#finalizeCard').addClass('d-none');
                        } else {
                            $('#scoringLockedAlert').addClass('d-none');
                            $('#finalizeCard').removeClass('d-none');
                        }

                        let html = '';
                        if (data.candidates.length === 0) {
                            html = '<div class="col-12 text-center py-5"><p class="text-muted fs-4">No candidates assigned to this competition.</p></div>';
                        } else {
                        // Group candidates by department
                        const depts = {};
                        const numCriteria = data.criteria.length;
                        
                        // 1. Prepare departments and calculate progress (DB + Local)
                        data.candidates.forEach(cand => {
                            const dept = cand.department || 'Unknown Department';
                            if (!depts[dept]) depts[dept] = { male: [], female: [], other: [], total: 0, scored: 0 };
                            
                            depts[dept].total++;
                            
                            const candScores = data.scores.filter(s => s.candidate_id == cand.id);
                            let fullyScored = true;
                            data.criteria.forEach(crit => {
                                const dbScore = candScores.find(s => s.criteria_id == crit.id);
                                const localScore = localStorage.getItem(`aurora_score_${currentAssignmentId}_${currentCatId}_${cand.id}_${crit.id}`);
                                if (!((dbScore && dbScore.score_value !== null && dbScore.score_value !== '') || (localScore !== null && localScore !== ''))) {
                                    fullyScored = false;
                                }
                            });
                            if (fullyScored && numCriteria > 0) depts[dept].scored++;
                            
                            const gender = (cand.gender || 'other').toLowerCase();
                            if (depts[dept][gender]) {
                                depts[dept][gender].push(cand);
                            } else {
                                depts[dept]['other'].push(cand);
                            }
                        });

                        // Prepare department summary for filters
                        const deptData = Object.keys(depts).map(d => ({
                            name: d,
                            total: depts[d].total,
                            scored: depts[d].scored
                        }));

                        // 1. Inject In-Page Filters
                        injectInPageFilters(deptData);

                        // 2. Render Candidates
                        Object.keys(depts).forEach(dept => {
                            const group = depts[dept];
                            const hasBoth = group.male.length > 0 && group.female.length > 0;
                            const maxLen = Math.max(group.male.length, group.female.length);
                            
                            // Create a section for each department, initially visible
                            html += `<div class="col-12 department-group" data-dept="${dept}">`;
                            html += `<div class="card bg-primary-subtle border-0 mb-4 shadow-sm"><div class="card-body py-3 px-4"><h3 class="fw-bolder text-primary mb-0"><iconify-icon icon="solar:buildings-2-bold-duotone" class="fs-6 align-middle me-2"></iconify-icon>${dept}</h3></div></div>`;
                            
                            if (group.male.length === 0 && group.female.length === 0 && group.other.length === 0) {
                                html += `<p class="text-muted ms-3">No candidates in this department.</p>`;
                            } else if (hasBoth) {
                                // Pair them side by side
                                for (let i = 0; i < maxLen; i++) {
                                    html += `<div class="row mb-4 align-items-stretch">`;
                                    
                                    // Male candidate (Left)
                                    html += `<div class="col-md-6 col-xl-6 mb-3 mb-md-0 d-flex flex-column">`;
                                    if (group.male[i]) {
                                        html += generateCandidateCard(group.male[i], data.criteria, data.scores, data.candidate_locks, isLocked, 'paired');
                                    }
                                    html += `</div>`;
                                    
                                    // Female candidate (Right)
                                    html += `<div class="col-md-6 col-xl-6 d-flex flex-column">`;
                                    if (group.female[i]) {
                                        html += generateCandidateCard(group.female[i], data.criteria, data.scores, data.candidate_locks, isLocked, 'paired');
                                    }
                                    html += `</div>`;
                                    
                                    html += `</div>`; // end row
                                }
                            } else {
                                // Standalone grid (No pairs)
                                html += `<div class="row">`;
                                const allInDept = [...group.male, ...group.female, ...group.other];
                                allInDept.forEach(cand => {
                                    html += `<div class="col-xl-4 col-md-6 mb-4 d-flex flex-column">`;
                                    html += generateCandidateCard(cand, data.criteria, data.scores, data.candidate_locks, isLocked, 'standalone');
                                    html += `</div>`;
                                });
                                html += `</div>`; // end row
                            }
                            
                            // If it had pairs but ALSO has 'other' genders, render 'others' generically at the bottom
                            if (hasBoth && group.other.length > 0) {
                                html += `<div class="row">`;
                                group.other.forEach(cand => {
                                    html += `<div class="col-xl-4 col-md-6 mb-4 d-flex flex-column">`;
                                    html += generateCandidateCard(cand, data.criteria, data.scores, data.candidate_locks, isLocked, 'standalone');
                                    html += `</div>`;
                                });
                                html += `</div>`;
                            }
                            
                            html += `</div>`; // Close department-group
                        });
                        }
                        
                        // Fake delay for modern feel (optional, but makes skeleton visible)
                        setTimeout(() => {
                            $('#candidatesContainer').hide().html(html).fadeIn(400);
                            $('#currentCategoryText').html(`${catName} Session &bull; Live Scoring`);
                            
                            // Initialize Totals
                            data.candidates.forEach(cand => updateTotal(cand.id));

                            // Restore selected department
                            let savedDept = sessionStorage.getItem('lastDeptTab');
                            if (savedDept) {
                                let targetBtn = $(`.dept-filter-btn[data-dept="${savedDept}"]`);
                                if (targetBtn.length) {
                                    targetBtn.click();
                                }
                            }
                        }, 300);
                    }
                });
            }

            function updateTotal(candId) {
                let total = 0;
                $(`.score-input[data-cand="${candId}"]`).each(function() {
                    total += parseFloat($(this).val() || 0);
                });
                $(`#total-display-${candId}`).text(total.toFixed(2));
                
                // Refresh department indicators
                refreshDeptProgress();
            }

            function refreshDeptProgress() {
                if (!window.currentCategoryData) return;
                const data = window.currentCategoryData;
                const depts = {};
                const numCriteria = data.criteria.length;

                data.candidates.forEach(cand => {
                    const dept = cand.department || 'Unknown Department';
                    if (!depts[dept]) depts[dept] = { total: 0, scored: 0 };
                    depts[dept].total++;

                    let fullyScored = true;
                    data.criteria.forEach(crit => {
                        const localVal = localStorage.getItem(`aurora_score_${currentAssignmentId}_${currentCatId}_${cand.id}_${crit.id}`);
                        // If localVal is empty, check if we have a DB value in the original data
                        const dbScore = data.scores.find(s => s.candidate_id == cand.id && s.criteria_id == crit.id);
                        const hasScore = (localVal !== null && localVal !== '') || (dbScore && dbScore.score_value !== null && dbScore.score_value !== '');
                        if (!hasScore) fullyScored = false;
                    });
                    if (fullyScored && numCriteria > 0) depts[dept].scored++;
                });

                // Update the badges in the filter list
                Object.keys(depts).forEach(dept => {
                    const info = depts[dept];
                    const btn = $(`.dept-filter-btn[data-dept="${dept}"]`);
                    const badge = btn.find('.badge');
                    badge.text(`${info.scored}/${info.total}`);
                    
                    if (info.scored === info.total && info.total > 0) {
                        badge.removeClass('bg-warning border-warning text-dark').addClass('bg-success text-white border-0');
                    } else {
                        badge.removeClass('bg-success text-white border-0').addClass('bg-warning border-warning text-dark');
                    }
                });
            }

            // Real-time Save
            $(document).on('change', '.score-input', function() {
                const input = $(this);
                const val = parseFloat(input.val());
                const max = parseFloat(input.attr('max'));
                
                if (val > max) {
                    Swal.fire('Limit Exceeded', `Maximum score for this criteria is ${max}`, 'warning');
                    input.val(max.toFixed(2));
                }

                if (isNaN(val)) return;

                input.addClass('border-primary');
                
                // Update local total display immediately
                updateTotal(input.data('cand'));

                // Save to localStorage
                const candId = input.data('cand');
                const critId = input.data('crit');
                const scoreValue = input.val();
                localStorage.setItem(`aurora_score_${currentAssignmentId}_${currentCatId}_${candId}_${critId}`, scoreValue);

                $.post('endpoints/scoring_handler.php', {
                    action: 'save_score',
                    assignment_id: currentAssignmentId,
                    candidate_id: candId,
                    criteria_id: critId,
                    score_value: scoreValue,
                    competition_id: currentCompId,
                    category_id: currentCatId
                }, function(response) {
                    if (response.status === 'success') {
                        input.removeClass('border-primary').addClass('border-success');
                        setTimeout(() => input.removeClass('border-success'), 1000);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                        input.addClass('border-danger');
                        // Revert local total if save failed? Usually not needed as user will fix it.
                    }
                });
            });

            $(document).on('input', '.score-input', function() {
                const input = $(this);
                updateTotal(input.data('cand'));
                
                // Also save on input (real-time as they type)
                const candId = input.data('cand');
                const critId = input.data('crit');
                localStorage.setItem(`aurora_score_${currentAssignmentId}_${currentCatId}_${candId}_${critId}`, input.val());
            });

            // Self-Unlock
            $('#selfUnlockBtn').click(function() {
                Swal.fire({
                    title: 'Unlock Scores?',
                    html: `This will allow you to edit your scores again.<br><strong>Note:</strong> This unlock action will be recorded and visible to the administrator.`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e9a11a',
                    confirmButtonText: '<i class="ti ti-lock-open me-1"></i> Yes, Unlock',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        AuroraLoader.showOverlay('#main-wrapper', 'Unlocking Category...');
                        $.post('endpoints/scoring_handler.php', {
                            action: 'unlock_self',
                            competition_id: currentCompId,
                            category_id: currentCatId
                        }, function(response) {
                            AuroraLoader.hideOverlay('#main-wrapper');
                            if (response.status === 'success') {
                                Swal.fire({
                                    title: 'Unlocked!',
                                    text: response.message,
                                    icon: 'success',
                                    confirmButtonColor: '#5d87ff'
                                }).then(() => {
                                    // Refresh all judge data to update global counts/checks
                                    initJudge(); 
                                    // Reload the current category specifically
                                    setTimeout(() => {
                                        loadCategory(currentCatId, $('.nav-category.active').data('name'));
                                    }, 300);
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                                if (response.message.includes('limit')) {
                                    initJudge(); // Refresh to disable button if limit reached
                                }
                            }
                        }).fail(function(jqxhr, textStatus, error) {
                            AuroraLoader.hideOverlay('#main-wrapper');
                            console.error('Self-Unlock AJAX Error:', textStatus, error, jqxhr.responseText);
                            Swal.fire('Server Error', 'Could not unlock. Check console for details.', 'error');
                        });
                    }
                });
            });

            // Final Submit
            $('#finalSubmitBtn').click(function() {
                if (isLocked) return;

                // Frontend Validation: check if all score inputs have a value
                let hasEmptyScore = false;
                $('.score-input').each(function() {
                    const val = $(this).val();
                    if (val === '' || val === null || isNaN(parseFloat(val))) {
                        hasEmptyScore = true;
                        $(this).addClass('border-danger border-2');
                    } else {
                        $(this).removeClass('border-danger border-2');
                    }
                });

                if (hasEmptyScore) {
                    Swal.fire({
                        title: 'Incomplete Scoring',
                        text: 'Please ensure all criteria for all candidates are properly scored before finalizing. Missing entries have been highlighted in red.',
                        icon: 'warning',
                        confirmButtonColor: '#5d87ff'
                    });
                    return;
                }

                // 1. Calculate Totals and Collect Data
                let summaryData = [];
                let hasMale = false;
                let hasFemale = false;
                
                $('.candidate-card').each(function() {
                    const card = $(this);
                    const name = card.find('h4').length ? card.find('h4').text().trim() : card.find('.mb-1.fw-bold').text().trim();
                    const num = card.find('.candidate-number').text().trim().replace('#', '');
                    const img = card.find('.candidate-img').attr('src');
                    
                    let gender = 'other';
                    if (card.find('.ti-gender-male').length) {
                        gender = 'male';
                        hasMale = true;
                    } else if (card.find('.ti-gender-female').length) {
                        gender = 'female';
                        hasFemale = true;
                    }

                    // For standalone layout, the name might be in h4 or mb-1 fw-bold
                    let cleanName = name;
                    // remove gender icon text if any
                    cleanName = cleanName.replace(/Male|Female/i, '').trim();

                    let total = 0;
                    card.find('.score-input').each(function() {
                        total += parseFloat($(this).val() || 0);
                    });

                    summaryData.push({
                        name: cleanName,
                        number: num,
                        img: img,
                        total: total,
                        gender: gender
                    });
                });

                const isPaired = hasMale && hasFemale;

                // 2. Rank Candidates (Descending) and Populate Modal Table
                let html = '';

                if (isPaired) {
                    const maleData = summaryData.filter(item => item.gender === 'male').sort((a, b) => b.total - a.total);
                    const femaleData = summaryData.filter(item => item.gender === 'female').sort((a, b) => b.total - a.total);
                    const otherData = summaryData.filter(item => item.gender === 'other').sort((a, b) => b.total - a.total);

                    html += `<tr><td colspan="4" class="bg-light fw-bold text-primary py-2"><i class="ti ti-gender-male me-2"></i>Male Candidates</td></tr>`;
                    maleData.forEach((item, index) => {
                        html += generateSummaryRow(item, index + 1);
                    });

                    html += `<tr><td colspan="4" class="bg-light fw-bold text-danger py-2"><i class="ti ti-gender-female me-2"></i>Female Candidates</td></tr>`;
                    femaleData.forEach((item, index) => {
                        html += generateSummaryRow(item, index + 1);
                    });

                    if (otherData.length > 0) {
                        html += `<tr><td colspan="4" class="bg-light fw-bold text-secondary py-2">Other Candidates</td></tr>`;
                        otherData.forEach((item, index) => {
                            html += generateSummaryRow(item, index + 1);
                        });
                    }
                } else {
                    summaryData.sort((a, b) => b.total - a.total);
                    summaryData.forEach((item, index) => {
                        html += generateSummaryRow(item, index + 1);
                    });
                }

                function generateSummaryRow(item, rank) {
                    let rankBadge = '';
                    if (rank === 1) rankBadge = '<span class="badge bg-warning text-dark"><i class="ti ti-crown me-1"></i>1st</span>';
                    else if (rank === 2) rankBadge = '<span class="badge bg-secondary text-white">2nd</span>';
                    else if (rank === 3) rankBadge = '<span class="badge bg-danger bg-opacity-75 text-white">3rd</span>';
                    else rankBadge = `<span class="badge bg-light text-muted">${rank}th</span>`;

                    return `
                        <tr>
                            <td class="ps-4">${rankBadge}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="${item.img}" class="rounded-circle me-3 shadow-sm" width="40" height="40" style="object-fit: cover;">
                                    <div>
                                        <h6 class="mb-0 fw-bold">${item.name}</h6>
                                        <small class="text-muted">#${item.number}</small>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center fw-bolder text-primary fs-5">${item.total.toFixed(2)}</td>
                            <td class="text-center pe-4">
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Ready</span>
                            </td>
                        </tr>`;
                }

                $('#summaryTableBody').html(html);
                $('#finalizeSummaryModal').modal('show');
            });

            $('#confirmLockBtn').click(function() {
                const btn = $(this);
                btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Locking Results...');

                $.post('endpoints/scoring_handler.php', {
                    action: 'finalize',
                    competition_id: currentCompId,
                    category_id: currentCatId
                }, function(response) {
                    $('#finalizeSummaryModal').modal('hide');
                    btn.prop('disabled', false).html('<i class="ti ti-lock me-2"></i> CONFIRM & LOCK FINAL RESULTS');

                    if (response.status === 'success') {
                        Swal.fire({
                            title: 'Scores Locked!',
                            text: response.message,
                            icon: 'success',
                            confirmButtonColor: '#5d87ff'
                        });
                        
                        // Add checkmark to current tab
                        const activeTab = $('.nav-category.active');
                        if (activeTab.find('.cat-completed-check').length === 0) {
                            activeTab.find('.cat-name-container').append('<div class="cat-completed-check" title="Finalized"><i class="ti ti-check"></i></div>');
                        }
                        
                        loadCategory(currentCatId, activeTab.text());
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }).fail(function(jqxhr, textStatus, error) {
                    $('#finalizeSummaryModal').modal('hide');
                    btn.prop('disabled', false).html('<i class="ti ti-lock me-2"></i> CONFIRM & LOCK FINAL RESULTS');
                    console.error("AJAX Error:", textStatus, error, jqxhr.responseText);
                    Swal.fire('Server Error', 'Could not complete the request. Check console for details.', 'error');
                });
            });

            $(document).on('click', '.nav-category', function() {
                $('.nav-category').removeClass('active');
                $(this).addClass('active');
                sessionStorage.setItem('lastCategoryTab', $(this).data('id'));
                loadCategory($(this).data('id'), $(this).data('name'));
            });

            initJudge();
        });
    </script>
</body>

</html>

