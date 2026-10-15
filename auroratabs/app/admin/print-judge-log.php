<?php
    session_start();
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../auth/login.php");
        exit();
    }
    date_default_timezone_set('Asia/Manila');
    $print_time = date('F d, Y, h:i A');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Score Submission Log - Official Record</title>
  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="src/assets/css/styles.min.css" />
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Libre+Baskerville:wght@700&family=Inter:wght@400;500;600;700;800&display=swap">
  <style>
    @page {
        size: 8.5in 13in;
        margin: 0;
    }
    body {
        background: #f0f2f5;
        color: #000;
        font-family: 'Inter', sans-serif;
        line-height: 1.3;
        margin: 0;
        padding: 0;
        -webkit-print-color-adjust: exact;
    }
    .print-container {
        width: 8.5in;
        min-height: 13in;
        background: white;
        margin: 2rem auto;
        padding: 0.6in 0.8in;
        box-shadow: 0 0 20px rgba(0,0,0,0.15);
        position: relative;
        box-sizing: border-box;
    }
    .watermark-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-45deg);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        pointer-events: none;
        z-index: 0;
        opacity: 0.025;
        filter: grayscale(100%);
        white-space: nowrap;
    }
    .watermark-logo  { width: 3.5in; height: auto; margin-bottom: 0.5rem; }
    .watermark-brand {
        font-family: 'Libre Baskerville', serif;
        font-size: 3.5rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 5px;
        color: #000; margin-bottom: 0.5rem;
    }
    .watermark-status {
        font-size: 4rem; font-weight: 900;
        text-transform: uppercase; letter-spacing: 15px;
        border-top: 2px solid #000; border-bottom: 2px solid #000;
        padding: 5px 20px;
    }
    .content-wrapper { position: relative; z-index: 1; }

    /* Letterhead */
    .letterhead {
        display: flex; align-items: center;
        justify-content: center; margin-bottom: 0.8rem;
        position: relative; padding-bottom: 0.5rem;
    }
    .letterhead-logo { width: 65px; height: auto; position: absolute; left: 0; }
    .letterhead-info { text-align: center; }
    .gov-text { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 1px; font-weight: 600; color: #333; }
    .org-name { font-family: 'Libre Baskerville', serif; font-weight: 700; font-size: 1.5rem; text-transform: uppercase; margin: 0; line-height: 1.1; }
    .org-tagline { font-size: 0.75rem; font-weight: 500; color: #444; text-transform: uppercase; letter-spacing: 1.5px; margin-top: 2px; }
    .header-line { border-top: 2px solid #000; border-bottom: 0.5px solid #000; height: 3px; margin-bottom: 1.2rem; }

    /* Report Header */
    .report-header { margin-bottom: 1.5rem; text-align: center; }
    .report-title { font-family: 'Libre Baskerville', serif; font-weight: 700; font-size: 1.6rem; text-transform: uppercase; margin: 0; color: #000; }
    .report-subtitle {
        font-weight: 800; font-size: 0.85rem; color: #000;
        text-transform: uppercase; letter-spacing: 5px; margin-top: 2px;
        border-top: 1px solid #000; border-bottom: 1px solid #000;
        display: inline-block; padding: 1px 20px;
    }
    .report-meta { margin-top: 0.8rem; display: flex; justify-content: center; gap: 2rem; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
    .meta-item b { font-weight: 800; text-decoration: underline; }

    /* Score Log Table */
    .results-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
    .results-table th {
        background: #f2f2f2 !important;
        padding: 0.5rem; font-weight: 800; font-size: 0.7rem;
        text-transform: uppercase; letter-spacing: 1px;
        border: 1px solid #000; text-align: center;
    }
    .results-table td { padding: 0.4rem 0.6rem; border: 1px solid #000; vertical-align: middle; font-size: 0.82rem; }
    .results-table tr:nth-child(even) { background-color: #f9f9f9; }
    .score-cell { font-weight: 800; text-align: center; }
    .judge-cell { font-weight: 700; text-transform: uppercase; }
    .status-cell { text-align: center; font-size: 0.75rem; color: #279c52; font-weight: 700; }
    .time-cell { color: #555; font-size: 0.78rem; text-align: center; white-space: nowrap; }

    /* Filter badge */
    .filter-badge {
        display: inline-block; background: #f0f0f0; border: 1px solid #ccc;
        border-radius: 4px; padding: 2px 10px;
        font-size: 0.7rem; font-weight: 600; margin-right: 5px;
        text-transform: uppercase; color: #333;
    }

    /* Signatories */
    .signatories-section { margin-top: 2rem; page-break-inside: avoid; }
    .sh-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem 1.5rem; margin-bottom: 2rem; }
    .sh-item { text-align: center; }
    .sh-label { text-align: left; font-size: 0.62rem; font-weight: 700; text-transform: uppercase; margin-bottom: 2rem; color: #555; }
    .sh-line { border-bottom: 0.5px solid #000; margin-bottom: 2px; width: 100%; }
    .sh-name { font-weight: 800; text-transform: uppercase; font-size: 0.75rem; margin: 0; min-height: 1rem; }
    .sh-role { font-size: 0.6rem; font-weight: 600; color: #555; text-transform: uppercase; margin: 0; }

    /* Footer */
    .page-footer {
        position: absolute; bottom: 0.4in; left: 0.8in; right: 0.8in;
        text-align: center; font-size: 0.6rem; color: #777;
        text-transform: uppercase; letter-spacing: 1px;
        border-top: 0.5px solid #ccc; padding-top: 8px;
    }

    /* Action bar */
    .action-bar { position: fixed; top: 20px; right: 20px; z-index: 9999; }

    @media print {
        .no-print { display: none !important; }
        body { background: white; }
        .print-container { margin: 0; padding: 0.6in 0.8in; box-shadow: none; width: 100%; min-height: auto; }
        .page-footer { position: fixed; bottom: 0.4in; left: 0.8in; right: 0.8in; }
    }
  </style>
</head>
<body>

    <div class="action-bar no-print">
        <button class="btn btn-dark shadow-lg px-4 py-2" onclick="window.print()">
            <i class="ti ti-printer me-2"></i> PRINT LOG
        </button>
        <a href="judge-reports.php" class="btn btn-outline-secondary ms-2">
            <i class="ti ti-arrow-left me-1"></i> Back
        </a>
    </div>

    <div class="print-container">
        <!-- Watermark -->
        <div class="watermark-container">
            <img src="src/assets/images/logos/aurora-logo.png" class="watermark-logo" alt="Logo">
            <div class="watermark-brand">Aurora Tabs Professional</div>
            <div class="watermark-status">Official Record</div>
        </div>

        <div class="content-wrapper">
            <!-- Letterhead -->
            <div class="letterhead">
                <img src="src/assets/images/logos/aurora-logo.png" class="letterhead-logo" alt="Logo">
                <div class="letterhead-info">
                    <div class="gov-text">Republic of the Philippines</div>
                    <h1 class="org-name">Aurora Tabs Professional</h1>
                    <p class="org-tagline">Advanced Automated Tabulation System</p>
                </div>
            </div>

            <div class="header-line"></div>

            <!-- Report Header -->
            <div class="report-header">
                <h2 class="report-title" id="printTitle">Score Submission Log</h2>
                <div class="report-subtitle">Official Judge Scoring Audit Record</div>
                <div class="report-meta">
                    <div class="meta-item"><b>Date: </b><?php echo date('F d, Y'); ?></div>
                    <div class="meta-item"><b>Competition: </b><span id="printComp">—</span></div>
                    <div class="meta-item"><b>Time: </b><?php echo date('h:i A'); ?></div>
                </div>
            </div>

            <!-- Loaded Table -->
            <div id="print-log-content">
                <p class="text-center text-muted">Loading audit log...</p>
            </div>

            <!-- Signatories -->
            <div class="signatories-section" id="printSignatories"></div>
        </div>

        <div class="page-footer">
            Digital Audit Record • Aurora Tabs Professional v1.0 • Confidential — For Official Use Only
        </div>
    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            const params = new URLSearchParams(window.location.search);
            const compId = params.get('competition_id') || '';
            const catId  = params.get('category_id')  || '';

            $.ajax({
                url: 'endpoints/judge_reports_handler.php',
                method: 'POST',
                data: { action: 'get_reports', competition_id: compId, category_id: catId },
                dataType: 'json',
                success: function (res) {
                    if (res.status !== 'success') return;

                    // Fetch competition name if available
                    if (compId) {
                        $.post('endpoints/judge_reports_handler.php', { action: 'get_active_competitions' }, function(r) {
                            if (r.status === 'success') {
                                const comp = r.data.find(c => c.id == compId);
                                if (comp) $('#printComp').text(comp.title);
                            }
                        });
                    }

                    // Build table
                    const logs = res.logs;
                    if (!logs || logs.length === 0) {
                        $('#print-log-content').html('<p style="text-align:center; font-style:italic; color:#666;">No scoring records found for the selected filters.</p>');
                        return;
                    }

                    let html = `
                        <table class="results-table">
                            <thead>
                                <tr>
                                    <th style="width:90px;">#</th>
                                    <th style="width:120px;">Time</th>
                                    <th>Judge</th>
                                    <th>Candidate</th>
                                    <th>Criteria</th>
                                    <th style="width:65px;">Score</th>
                                    <th style="width:70px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    logs.forEach((log, i) => {
                        html += `
                            <tr>
                                <td class="time-cell">${i + 1}</td>
                                <td class="time-cell">${log.time}</td>
                                <td class="judge-cell">${log.judge}</td>
                                <td>${log.candidate}</td>
                                <td>${log.criteria}</td>
                                <td class="score-cell">${log.score}</td>
                                <td class="status-cell">${log.status}</td>
                            </tr>
                        `;
                    });

                    html += `</tbody></table>`;
                    $('#print-log-content').html(html);

                    // Signatories — listing judges from cards
                    const judges = res.judges;
                    let sigHtml = '';
                    if (judges && judges.length > 0) {
                        sigHtml += `<div class="sh-grid" style="margin-top: 3rem;">`;
                        judges.forEach(j => {
                            sigHtml += `
                                <div class="sh-item">
                                    <div class="sh-label">Attested by:</div>
                                    <div class="sh-line"></div>
                                    <h6 class="sh-name">${j.name}</h6>
                                    <p class="sh-role">Official Judge</p>
                                </div>
                            `;
                        });
                        sigHtml += `</div>`;
                    }

                    sigHtml += `
                        <div class="sh-grid" style="margin-top: 3rem; border-top: 1px solid #f0f0f0; padding-top: 2rem; grid-template-columns: repeat(2, 1fr); gap: 1.5rem 5rem;">
                            <div class="sh-item">
                                <div class="sh-label">Certified Correct:</div>
                                <div class="sh-line"></div>
                                <h6 class="sh-name" style="font-size: 0.65rem;">Aurora Tabs Professional</h6>
                                <p class="sh-role">Electronic System Certification</p>
                            </div>
                            <div class="sh-item">
                                <div class="sh-label">Approved by:</div>
                                <div class="sh-line"></div>
                                <h6 class="sh-name">__________________________</h6>
                                <p class="sh-role">Event Chairman / Organizer</p>
                            </div>
                        </div>
                    `;

                    $('#printSignatories').html(sigHtml);
                }
            });
        });
    </script>
</body>
</html>
