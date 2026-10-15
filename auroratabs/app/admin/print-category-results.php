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
  <title>Category Rankings - Print View</title>
  <link rel="shortcut icon" type="image/png" href="src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="src/assets/css/styles.min.css" />
  <style>
      @page {
          size: 8.5in 13in;
          margin: 1in;
      }
      body {
          background: white;
          color: #1a1a1a;
          font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
          line-height: 1.4;
          margin: 0;
          padding: 0;
      }
      .print-container {
          max-width: 100%;
      }
      
      .letterhead {
          display: grid;
          grid-template-columns: 60px 1fr 60px;
          align-items: center;
          margin-bottom: 3rem;
          gap: 2rem;
      }
      .letterhead-logo {
          width: 50px;
          height: auto;
          filter: grayscale(100%);
      }
      .letterhead-info {
          text-align: center;
      }
      .org-name {
          font-weight: 800;
          font-size: 1.3rem;
          text-transform: uppercase;
          letter-spacing: 2px;
          margin: 0;
          color: #000;
      }
      .org-contact {
          font-size: 0.75rem;
          color: #666;
          text-transform: uppercase;
          letter-spacing: 1px;
          margin-top: 4px;
      }

      .report-header {
          margin-bottom: 2.5rem;
          border-bottom: 1.5px solid #000;
          padding-bottom: 1rem;
          text-align: center;
      }
      .report-title {
          font-weight: 700;
          font-size: 1.4rem;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          margin: 0;
          color: #000;
      }
      .report-subtitle {
          font-weight: 700;
          font-size: 1.1rem;
          color: #333;
          text-transform: uppercase;
          letter-spacing: 1.5px;
          margin-top: 8px;
          border-top: 1px dashed #ccc;
          display: inline-block;
          padding: 4px 20px 0;
      }
      .report-meta {
          margin-top: 1rem;
          display: flex;
          justify-content: center;
          gap: 2.5rem;
          font-size: 0.7rem;
          color: #666;
          text-transform: uppercase;
          letter-spacing: 0.5px;
      }
      .meta-item b { 
          color: #000; 
          margin-right: 4px;
          font-weight: 700;
      }

      .results-table {
          width: 100%;
          border-collapse: collapse;
          margin-bottom: 4rem;
      }
      .results-table th {
          text-align: left;
          padding: 0.75rem 0.4rem;
          font-weight: 700;
          font-size: 0.65rem;
          text-transform: uppercase;
          letter-spacing: 0.5px;
          color: #000;
          border-bottom: 2px solid #000;
          background-color: #f9f9f9;
      }
      .results-table td {
          padding: 0.8rem 0.4rem;
          border-bottom: 1px solid #eee;
          vertical-align: middle;
          font-size: 0.85rem;
      }
      .rank-box {
          font-weight: 700;
          font-size: 1.1rem;
          color: #000;
          text-align: center;
      }
      .candidate-info .name {
          font-weight: 700;
          font-size: 0.95rem;
          display: block;
          color: #000;
      }
      .candidate-info .num {
          font-size: 0.65rem;
          color: #666;
          text-transform: uppercase;
      }
      .score-cell {
          text-align: center;
          color: #444;
      }
      .average-cell {
          text-align: right;
          font-weight: 800;
          font-size: 1.1rem;
          color: #000;
      }

      .signatories-section {
          margin-top: 5rem;
          page-break-inside: avoid;
      }
      .signatory-grid {
          display: grid;
          grid-template-columns: repeat(3, 1fr);
          gap: 3rem 2rem;
      }
      .signatory-item {
          text-align: left;
      }
      .signature-line {
          border-top: 1px solid #000;
          width: 100%;
          margin-bottom: 0.5rem;
      }
      .signatory-name {
          font-weight: 700;
          text-transform: uppercase;
          font-size: 0.75rem;
          margin: 0;
          color: #000;
      }
      .signatory-role {
          font-size: 0.6rem;
          color: #888;
          text-transform: uppercase;
          letter-spacing: 1px;
          margin: 0;
      }
      
      .page-footer {
          position: fixed;
          bottom: 0;
          left: 0;
          right: 0;
          text-align: center;
          padding: 1.5rem 0;
          font-size: 0.6rem;
          color: #999;
          text-transform: uppercase;
          letter-spacing: 1px;
      }

      @media print {
          .no-print { display: none !important; }
          body { -webkit-print-color-adjust: exact; }
          .page-footer { position: fixed; bottom: 0; }
      }
  </style>
</head>
<body>

  <div class="print-container">
      
      <div class="no-print position-fixed top-0 end-0 p-3">
          <button class="btn btn-primary" onclick="window.print()"><i class="ti ti-printer me-1"></i> Print PDF</button>
      </div>

      <div class="letterhead">
          <img src="src/assets/images/logos/aurora-logo.png" class="letterhead-logo" alt="Logo">
          <div class="letterhead-info">
              <h1 class="org-name">Aurora Tabs</h1>
              <p class="org-contact">Reyal Solutions | Professional Tabulation System</p>
          </div>
          <img src="src/assets/images/logos/aurora-logo.png" class="letterhead-logo" alt="Logo">
      </div>

      <div class="report-header">
          <h2 class="report-title" id="printCompTitle">--</h2>
          <div class="report-subtitle" id="printCatName">--</div>
          
          <div class="report-meta">
              <div class="meta-item"><b>Date</b> <span id="printDate">--</span></div>
              <div class="meta-item"><b>Venue</b> <span id="printVenue">--</span></div>
              <div class="meta-item"><b>Status</b> <span id="printStatus">--</span></div>
          </div>
      </div>

      <table class="results-table" id="printTable">
          <thead id="printTableHead">
              <!-- Dynamic -->
          </thead>
          <tbody id="printTableBody">
              <!-- Dynamic -->
          </tbody>
      </table>

      <div class="signatories-section">
          <div class="signatory-grid" id="printSignatories">
              <!-- Dynamic -->
          </div>
      </div>

      <div class="page-footer">
          <?php date_default_timezone_set('Asia/Manila'); ?>
          Official Category Tabulation Report | Generated by Aurora Tabs on <?php echo date('F d, Y | h:i A'); ?>
      </div>

  </div>

  <script src="src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script>
      $(document).ready(function() {
          const urlParams = new URLSearchParams(window.location.search);
          const compId = urlParams.get('competition_id');
          const catId = urlParams.get('category_id');

          if(compId && catId) {
              loadData(compId, catId);
          } else {
              alert("Invalid Parameters");
              window.close();
          }

          function loadData(cid, catid) {
            $.post('endpoints/rankings_handler.php', { 
                  action: 'get_rankings', 
                  competition_id: cid,
                  category_id: catid
              }, function(response) {
                  if(response.status === 'success') {
                      // Header
                      $('#printCompTitle').text(response.competition_title);
                      $('#printCatName').text(response.category_name);
                      $('#printDate').text(response.competition_date);
                      $('#printVenue').text(response.competition_venue);
                      $('#printStatus').text(response.is_verified ? 'OFFICIALLY VERIFIED' : 'PRELIMINARY RESULTS');

                      // Table Head
                      const judges = response.judges;
                      let headHtml = `
                        <tr>
                            <th style="width: 50px; text-align: center;">Rank</th>
                            <th>Candidate Information</th>
                            ${judges.map(j => `<th style="text-align: center;">J${j.id}</th>`).join('')}
                            <th style="width: 100px; text-align: right;">Average</th>
                        </tr>
                      `;
                      $('#printTableHead').html(headHtml);

                      // Table Body
                      const tbody = $('#printTableBody');
                      tbody.empty();
                      
                      response.data.forEach(d => {
                          const row = `
                             <tr>
                                <td class="rank-box">${d.rank}</td>
                                <td class="candidate-info">
                                    <span class="name">${d.name}</span>
                                    <span class="num">#${d.number}</span>
                                </td>
                                ${d.judge_scores.map(s => `<td class="score-cell">${parseFloat(s).toFixed(2)}</td>`).join('')}
                                <td class="average-cell">${d.average_formatted}</td>
                            </tr>
                          `;
                          tbody.append(row);
                      });

                      // Signatories
                      const sigContainer = $('#printSignatories');
                      sigContainer.empty();
                      judges.forEach(j => {
                          const item = `
                            <div class="signatory-item">
                                <div class="signature-line"></div>
                                <h6 class="signatory-name">${j.name}</h6>
                                <p class="signatory-role">Official Judge</p>
                            </div>
                          `;
                          sigContainer.append(item);
                      });
                  }
              });
          }
      });
  </script>
</body>
</html>
