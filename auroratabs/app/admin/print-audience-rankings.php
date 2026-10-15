<?php
    session_start();
    
    // Auth check - redirect to login if not authenticated
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
  <title>People's Choice - Official Rankings</title>
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
        
        /* Paper Container for Screen - Replicates Long Bondpaper */
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

        /* Official Slanted Dual Watermark */
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
        .watermark-logo {
            width: 3.5in;
            height: auto;
            margin-bottom: 0.5rem;
        }
        .watermark-brand {
            font-family: 'Libre Baskerville', serif;
            font-size: 3.5rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 5px;
            color: #000;
            margin-bottom: 0.5rem;
        }
        .watermark-status {
            font-size: 4rem;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 15px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 5px 20px;
        }

        .content-wrapper {
            position: relative;
            z-index: 1;
        }

        /* Letterhead - Compact & Formal */
        .letterhead {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 0.8rem;
            position: relative;
            padding-bottom: 0.5rem;
        }
        .letterhead-logo {
            width: 65px;
            height: auto;
            position: absolute;
            left: 0;
        }
        .letterhead-info {
            text-align: center;
        }
        .gov-text {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-bottom: 1px;
            font-weight: 600;
            color: #333;
        }
        .org-name {
            font-family: 'Libre Baskerville', serif;
            font-weight: 700;
            font-size: 1.5rem;
            text-transform: uppercase;
            margin: 0;
            line-height: 1.1;
        }
        .org-tagline {
            font-size: 0.75rem;
            font-weight: 500;
            color: #444;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }
        
        .header-line {
            border-top: 2px solid #000;
            border-bottom: 0.5px solid #000;
            height: 3px;
            margin-bottom: 1.2rem;
        }

        /* Report Header - Formal & Tight */
        .report-header {
            margin-bottom: 1.5rem;
            text-align: center;
        }
        .report-title {
            font-family: 'Libre Baskerville', serif;
            font-weight: 700;
            font-size: 1.6rem;
            text-transform: uppercase;
            margin: 0;
            color: #000;
        }
        .report-subtitle {
            font-weight: 800;
            font-size: 0.85rem;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 5px;
            margin-top: 2px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            display: inline-block;
            padding: 1px 20px;
        }
        .report-meta {
            margin-top: 0.8rem;
            display: flex;
            justify-content: center;
            gap: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .meta-item b { font-weight: 800; text-decoration: underline; }

        /* Results Table - Compact Professional */
        .results-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1.5rem;
        }
        .results-table th {
            background: #f2f2f2 !important;
            padding: 0.5rem;
            font-weight: 800;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            border: 1px solid #000;
            text-align: center;
        }
        .results-table td {
            padding: 0.4rem 0.6rem;
            border: 1px solid #000;
            vertical-align: middle;
            font-size: 0.85rem;
        }
        .results-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .rank-cell {
            font-weight: 800;
            text-align: center;
            width: 50px;
            font-size: 1rem;
        }
        .candidate-cell {
            font-weight: 700;
            text-transform: uppercase;
        }
        .dept-cell {
            color: #333;
            font-size: 0.75rem;
            font-weight: 500;
        }
        .votes-cell {
            text-align: center;
            font-weight: 800;
            width: 80px;
        }
        .popularity-cell {
            text-align: center;
            font-weight: 700;
            width: 110px;
        }

        .gender-divider {
            background: #e0e0e0;
            padding: 5px;
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.75rem;
            border-left: 1px solid #000;
            border-right: 1px solid #000;
            border-top: 1px solid #000;
            text-align: center;
            letter-spacing: 2px;
        }

        /* Signatories - Compact & Distributed */
        .signatories-section {
            margin-top: 2rem;
            page-break-inside: avoid;
        }
        .sh-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem 1.5rem;
            margin-bottom: 2rem;
        }
        .sh-item {
            text-align: center;
        }
        .sh-label {
            text-align: left;
            font-size: 0.62rem;
            font-weight: 700;
            text-transform: uppercase;
            margin-bottom: 2rem;
            color: #555;
        }
        .sh-line {
            border-bottom: 0.5px solid #000;
            margin-bottom: 2px;
            width: 100%;
        }
        .sh-name {
            font-weight: 800;
            text-transform: uppercase;
            font-size: 0.75rem;
            margin: 0;
            min-height: 1rem;
        }
        .sh-role {
            font-size: 0.6rem;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            margin: 0;
        }

        /* Footer */
        .page-footer {
            position: absolute;
            bottom: 0.4in;
            left: 0.8in;
            right: 0.8in;
            text-align: center;
            font-size: 0.6rem;
            color: #777;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-top: 0.5px solid #ccc;
            padding-top: 8px;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: white; }
            .print-container {
                margin: 0;
                padding: 0.6in 0.8in;
                box-shadow: none;
                width: 100%;
                min-height: auto;
            }
            .page-footer {
                position: fixed;
                bottom: 0.4in;
                left: 0.8in;
                right: 0.8in;
            }
        }
        
        /* Fixed Action Button for screen */
        .action-bar {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>
</head>
<body>

    <div class="action-bar no-print">
        <button class="btn btn-dark shadow-lg px-4 py-2" onclick="window.print()">
            <i class="ti ti-printer me-2"></i> PRINT TABULATION
        </button>
    </div>

    <div class="print-container">
        <div class="watermark-container">
            <img src="src/assets/images/logos/aurora-logo.png" class="watermark-logo" alt="Logo">
            <div class="watermark-brand">Aurora Tabs Professional</div>
            <div class="watermark-status">Official Results</div>
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
                <h2 class="report-title" id="printTitle">Loading...</h2>
                <div class="report-subtitle">Official People's Choice Rankings</div>
                
                <div class="report-meta">
                    <div class="meta-item"><b>Date: </b> <?php echo date('F d, Y'); ?></div>
                    <div class="meta-item"><b>Venue: </b> <span id="printVenue">--</span></div>
                    <div class="meta-item"><b>Time: </b> <?php echo date('h:i A'); ?></div>
                </div>
            </div>

            <div id="print-results-content">
                <!-- Dynamic Content -->
            </div>

            <div class="signatories-section" id="printSignatoriesContainer">
                <!-- Grouped Signatories rows will be added here -->
            </div>
        </div>

        <div class="page-footer">
            Digital Certificate of Result • Aurora Tabs Professional v1.0 • Confidentiality Guaranteed
        </div>

    </div>

    <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const compId = urlParams.get('competition_id');

            if(compId) {
                loadRankings(compId);
            } else {
                alert("Error: No Competition Selected");
            }

            function loadRankings(id) {
                $.post('endpoints/voting_handler.php', { 
                    action: 'get_rankings', 
                    competition_id: id 
                }, function(response) {
                    if(response.status === 'success') {
                        $('#printTime').text('<?php echo $print_time; ?>');
                        $('#printTitle').text(response.competition.title);
                        $('#printVenue').text(response.competition.venue || 'Cultural Center');
                        renderData(response.data);
                        renderSignatories(response.judges, response.tabulators, response.organizers);
                    } else {
                        alert(response.message);
                    }
                });
            }

            function renderSignatories(judges, tabulators, organizers) {
                const container = $('#printSignatoriesContainer');
                container.empty();
                
                // Row 1: Preparation (Tabulators)
                if (tabulators && tabulators.length > 0) {
                    let html = '<div class="sh-grid">';
                    tabulators.forEach(t => {
                        html += `
                            <div class="sh-item">
                                <div class="sh-label">Prepared by:</div>
                                <div class="sh-line"></div>
                                <h6 class="sh-name">${t.name}</h6>
                                <p class="sh-role">Official Tabulator</p>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.append(html);
                } else {
                    container.append(`
                        <div class="sh-grid">
                            <div class="sh-item">
                                <div class="sh-label">Prepared by:</div>
                                <div class="sh-line"></div>
                                <h6 class="sh-name">__________________________</h6>
                                <p class="sh-role">Event Tabulator</p>
                            </div>
                        </div>
                    `);
                }

                // Row 2: Attestation (Board of Judges)
                if (judges && judges.length > 0) {
                    let html = '<div class="sh-grid" style="margin-top: 3rem;">';
                    judges.forEach(j => {
                        html += `
                            <div class="sh-item">
                                <div class="sh-label">Attested by:</div>
                                <div class="sh-line"></div>
                                <h6 class="sh-name">${j.name}</h6>
                                <p class="sh-role">Official Judge</p>
                            </div>
                        `;
                    });
                    html += '</div>';
                    container.append(html);
                }

                // Row 3: Final Certification & Approval
                container.append(`
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
                            <h6 class="sh-name">${organizers && organizers.length > 0 ? organizers.map(o => o.name).join(' / ') : '__________________________'}</h6>
                            <p class="sh-role">Event Chairman / Organizer</p>
                        </div>
                    </div>
                `);
            }

            function renderData(data) {
                const female = data.filter(c => (c.gender || '').toLowerCase() === 'female');
                const male   = data.filter(c => (c.gender || '').toLowerCase() === 'male');
                const others = data.filter(c => !['female','male'].includes((c.gender || '').toLowerCase()));
                
                const container = $('#print-results-content');
                container.empty();

                const hasBoth = female.length > 0 && male.length > 0;

                if (hasBoth) {
                    if (female.length > 0) renderTable(female, 'Female Division', container);
                    if (male.length > 0) renderTable(male, 'Male Division', container);
                    if (others.length > 0) renderTable(others, 'Other Categories', container);
                } else {
                    renderTable(data, '', container);
                }
            }

            function renderTable(candidates, title, container) {
                let html = '';
                if (title) {
                    html += `<div class="gender-divider">${title}</div>`;
                }

                html += `
                <table class="results-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Rank</th>
                            <th>Candidate Name</th>
                            <th>Department</th>
                            <th style="width: 80px;">Votes</th>
                            <th style="width: 110px;">Popularity</th>
                        </tr>
                    </thead>
                    <tbody>
                `;

                candidates.forEach((c, i) => {
                    const rank = i + 1;
                    html += `
                        <tr>
                            <td class="rank-cell">${rank}</td>
                            <td class="candidate-cell">${c.first_name} ${c.last_name}</td>
                            <td class="dept-cell">${c.department || 'N/A'}</td>
                            <td class="votes-cell">${c.total_votes.toLocaleString()}</td>
                            <td class="popularity-cell">${c.popularity}%</td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                container.append(html);
            }
        });
    </script>
</body>
</html>
