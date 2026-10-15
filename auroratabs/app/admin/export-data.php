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
    <title>Export Data - Aurora Tabs</title>
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
                                    <h4 class="fw-semibold mb-8">Export Data Central</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Export</li>
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
                        <div class="col-lg-8 mx-auto">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="card-title fw-semibold mb-4">Export Configuration</h5>
                                    <form>
                                        <!-- Step 1: Select Data -->
                                        <div class="mb-4">
                                            <label class="form-label d-block fw-bold">1. Select Data to Export</label>
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="dataCandidates" checked>
                                                        <label class="form-check-label" for="dataCandidates">Candidates Information</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="dataJudges" checked>
                                                        <label class="form-check-label" for="dataJudges">Judges Profile</label>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="dataScores">
                                                        <label class="form-check-label" for="dataScores">Scoring Logs (Audit Trail)</label>
                                                    </div>
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input" type="checkbox" id="dataVotes">
                                                        <label class="form-check-label" for="dataVotes">Voting Transactions</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Step 2: Filters -->
                                        <div class="mb-4">
                                            <label class="form-label d-block fw-bold">2. Fine-tune Selection (Optional)</label>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label for="filterComp" class="form-label small">Competition</label>
                                                    <select class="form-select" id="filterComp">
                                                        <option value="all">All Competitions</option>
                                                        <!-- Dynamic Competitions -->
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="filterDateFrom" class="form-label small">From Date</label>
                                                    <input type="date" class="form-control" id="filterDateFrom">
                                                </div>
                                                <div class="col-md-3">
                                                    <label for="filterDateTo" class="form-label small">To Date</label>
                                                    <input type="date" class="form-control" id="filterDateTo">
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <!-- Step 3: Format -->
                                        <div class="mb-4">
                                            <label class="form-label d-block fw-bold">3. Choose Export Format</label>
                                            <div class="d-flex gap-4 mt-2">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="fmtCSV" checked>
                                                    <label class="form-check-label" for="fmtCSV">
                                                        <i class="ti ti-file-type-csv text-success fs-5 me-1"></i> CSV (Spreadsheet)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="fmtExcel">
                                                    <label class="form-check-label" for="fmtExcel">
                                                        <i class="ti ti-file-spreadsheet text-primary fs-5 me-1"></i> Excel (.xlsx)
                                                    </label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="exportFormat" id="fmtPDF">
                                                    <label class="form-check-label" for="fmtPDF">
                                                        <i class="ti ti-file-type-pdf text-danger fs-5 me-1"></i> PDF Document
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-grid mt-5">
                                            <button type="button" class="btn btn-primary p-3 fw-bold fs-4" id="btnExport">
                                                <i class="ti ti-download me-2"></i> Prepare and Download Data
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="alert alert-light-info d-flex align-items-center" role="alert">
                                <i class="ti ti-info-circle me-2 fs-5 text-info"></i>
                                <div>
                                    Generating large exports (e.g. detailed voting logs) may take a few moments. Do not close the window while the preparation is in progress.
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
            // 1. Load Competitions
            function loadCompetitions() {
                $.ajax({
                    url: 'endpoints/competitions_handler.php',
                    method: 'POST',
                    data: { action: 'list' },
                    success: function(res) {
                        if (res.status === 'success') {
                            let html = '<option value="all">All Competitions</option>';
                            res.data.forEach(function(comp) {
                                html += `<option value="${comp.id}">${comp.title}</option>`;
                            });
                            $('#filterComp').html(html);
                        }
                    }
                });
            }
            loadCompetitions();

            // 2. Export Button Click
            $('#btnExport').on('click', function() {
                const types = [];
                if ($('#dataCandidates').is(':checked')) types.push('candidates');
                if ($('#dataJudges').is(':checked')) types.push('judges');
                if ($('#dataScores').is(':checked')) types.push('scores');
                if ($('#dataVotes').is(':checked')) types.push('votes');

                if (types.length === 0) {
                    Swal.fire('Error', 'Please select at least one data category to export.', 'error');
                    return;
                }

                const compId = $('#filterComp').val();
                const dateFrom = $('#filterDateFrom').val();
                const dateTo = $('#filterDateTo').val();
                const format = $('input[name="exportFormat"]:checked').attr('id').replace('fmt', '').toLowerCase();
                
                // Show loading state
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Preparing Data...');

                if (format === 'pdf') {
                    // Open the print-friendly PDF page
                    const params = $.param({
                        types: JSON.stringify(types),
                        competition_id: compId,
                        date_from: dateFrom,
                        date_to: dateTo
                    });
                    window.open(`print-export-pdf.php?${params}`, '_blank');
                    
                    // Reset button state
                    setTimeout(function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }, 1000);
                    return;
                }

                // For CSV/Excel: Create a hidden form to handle the download (POST request)
                const $form = $('<form>', {
                    action: 'endpoints/export_handler.php',
                    method: 'POST',
                    target: '_blank'
                });

                $form.append($('<input>', { type: 'hidden', name: 'types', value: JSON.stringify(types) }));
                $form.append($('<input>', { type: 'hidden', name: 'competition_id', value: compId }));
                $form.append($('<input>', { type: 'hidden', name: 'date_from', value: dateFrom }));
                $form.append($('<input>', { type: 'hidden', name: 'date_to', value: dateTo }));
                $form.append($('<input>', { type: 'hidden', name: 'format', value: format }));
                
                $('body').append($form);
                $form.submit();
                $form.remove();

                // Re-enable button after a short delay
                setTimeout(function() {
                    $btn.prop('disabled', false).html(originalHtml);
                }, 2000);
            });
        });
    </script>
</body>

</html>
