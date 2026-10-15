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
  <title>Transaction History - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
    @media (max-width: 767.98px) {
      .table-responsive-stack td {
        font-size: 13px;
      }
      .table-responsive-stack td::before {
        font-size: 11px;
        text-transform: uppercase;
        color: #7c8fac;
      }
      #transactionsTable h6 {
        font-size: 14px;
      }
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

          <!-- Dashboard Header -->
          <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center">
                <div class="col-9">
                  <h4 class="fw-semibold mb-8">Transaction Ledger</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Transactions</li>
                    </ol>
                  </nav>
                </div>
                <div class="col-3 text-center">
                  <div class="mb-n5">
                    <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" width="100" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Summary Stats -->
          <div class="row mb-4">
            <div class="col-md-3">
              <div class="card shadow-none border">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light-primary text-primary p-2 rounded-2">
                      <i class="ti ti-currency-peso fs-6"></i>
                    </div>
                    <div class="ms-3">
                      <h6 class="mb-0 fw-semibold">Total Revenue</h6>
                      <h4 class="mb-0 fw-bold" id="statRevenue">₱0.00</h4>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-primary" role="progressbar" style="width: 75%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card shadow-none border">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light-success text-success p-2 rounded-2">
                      <i class="ti ti-chart-bar fs-6"></i>
                    </div>
                    <div class="ms-3">
                      <h6 class="mb-0 fw-semibold">Subscriptions</h6>
                      <h4 class="mb-0 fw-bold" id="statSubs">₱0.00</h4>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 60%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card shadow-none border">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light-warning text-warning p-2 rounded-2">
                      <i class="ti ti-ticket fs-6"></i>
                    </div>
                    <div class="ms-3">
                      <h6 class="mb-0 fw-semibold">Vote Bundles</h6>
                      <h4 class="mb-0 fw-bold" id="statVotes">₱0.00</h4>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 45%"></div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="card shadow-none border">
                <div class="card-body p-4">
                  <div class="d-flex align-items-center mb-3">
                    <div class="bg-light-info text-info p-2 rounded-2">
                      <i class="ti ti-users fs-6"></i>
                    </div>
                    <div class="ms-3">
                      <h6 class="mb-0 fw-semibold">Paying Clients</h6>
                      <h4 class="mb-0 fw-bold" id="statClients">0</h4>
                    </div>
                  </div>
                  <div class="progress" style="height: 4px;">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 80%"></div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Transactions Table -->
          <div class="card">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center mb-4">
                <h5 class="card-title fw-semibold">Global Transaction History</h5>
                <div class="btn-group">
                  <button class="btn btn-outline-primary btn-sm rounded-start" id="exportCsv">Export CSV</button>
                  <button class="btn btn-outline-primary btn-sm rounded-end" id="printPdf">Print PDF</button>
                </div>
              </div>
              <div class="table-responsive">
                <table class="table align-middle table-hover table-responsive-stack" id="transactionsTable">
                  <thead class="bg-light">
                    <tr>
                      <th>Trans ID</th>
                      <th>Date</th>
                      <th>Entity/Client</th>
                      <th>Amount</th>
                      <th>Status</th>
                      <th class="text-end">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Transaction Details Modal -->
          <div class="modal fade" id="txnDetailsModal" tabindex="-1" aria-labelledby="txnDetailsModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header border-bottom">
                  <h5 class="modal-title fw-bold" id="txnDetailsModalLabel">Transaction Details</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                  <div class="text-center mb-4">
                    <h3 class="fw-bold mb-0 text-primary" id="detAmount">₱0.00</h3>
                    <span class="badge rounded-pill" id="detStatusBadge">Success</span>
                  </div>
                  <div class="row g-3">
                    <div class="col-6">
                      <label class="text-muted small text-uppercase fw-bold">Transaction Ref</label>
                      <p class="mb-0 fw-semibold" id="detRef">#TRX-00000</p>
                    </div>
                    <div class="col-6">
                      <label class="text-muted small text-uppercase fw-bold">Date & Time</label>
                      <p class="mb-0 fw-semibold" id="detDate">Jan 01, 2026</p>
                    </div>
                    <div class="col-12 border-top pt-3">
                      <label class="text-muted small text-uppercase fw-bold">Customer Info</label>
                      <div class="d-flex align-items-center mt-1">
                        <div class="bg-light-primary text-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;" id="detInitials">JD</div>
                        <div class="ms-3">
                          <h6 class="mb-0 fw-bold" id="detName">John Doe</h6>
                          <small class="text-muted" id="detEmail">john@example.com</small>
                        </div>
                      </div>
                    </div>
                    <div class="col-12 border-top pt-3">
                      <label class="text-muted small text-uppercase fw-bold">Description</label>
                      <p class="mb-0" id="detDesc">Transaction description goes here...</p>
                    </div>
                    <div class="col-6 border-top pt-3">
                      <label class="text-muted small text-uppercase fw-bold">Payment Method</label>
                      <p class="mb-0 fw-semibold" id="detGateway">GCash</p>
                    </div>
                  </div>
                </div>
                <div class="modal-footer bg-light border-top">
                  <button type="button" class="btn btn-primary w-100" onclick="window.print()">Print This View</button>
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
      var table = $('#transactionsTable').DataTable({
        "order": [[ 1, "desc" ]],
        "language": {
          "search": "Search:",
          "paginate": {
            "next": '<i class="ti ti-chevron-right"></i>',
            "previous": '<i class="ti ti-chevron-left"></i>'
          }
        },
        "dom": '<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4"lf>rt<"d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-4"ip>'
      });

      // Fetch dashboard stats
      $.post('endpoints/finance_handler.php', { action: 'get_dashboard_stats' }, function(response) {
        if (response.status === 'success') {
          $('#statRevenue').text('₱' + parseFloat(response.data.total_revenue).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $('#statSubs').text('₱' + parseFloat(response.data.subscriptions).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $('#statVotes').text('₱' + parseFloat(response.data.vote_bundles).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
          $('#statClients').text(response.data.paying_clients);
        }
      }, 'json');

      // Fetch DataTables
      $.post('endpoints/finance_handler.php', { action: 'get_transactions' }, function(response) {
        if (response.status === 'success') {
          table.clear();
          response.data.forEach(function(txn) {
              var dateObj = new Date(txn.created_at);
              var timeStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' });
              
              var initials = (txn.first_name ? txn.first_name.charAt(0).toUpperCase() : 'U');
              var name = txn.first_name ? (txn.first_name + ' ' + txn.last_name) : 'Unknown User';
              var subEmail = txn.email || 'No Email';
              
              var statusBadge = '';
              if (txn.status === 'success') statusBadge = '<span class="badge bg-light-success text-success fs-2">Success</span>';
              else if (txn.status === 'pending') statusBadge = '<span class="badge bg-light-warning text-warning fs-2">Pending</span>';
              else if (txn.status === 'failed') statusBadge = '<span class="badge bg-light-danger text-danger fs-2">Failed</span>';
              else if (txn.status === 'refunded') statusBadge = '<span class="badge bg-light-secondary text-secondary fs-2">Refunded</span>';
              else statusBadge = `<span class="badge border text-dark fs-2">${txn.status}</span>`;

              var actions = `
                <button class="btn btn-sm btn-light-primary text-primary me-1 view-txn" data-id="${txn.id}" title="View Details"><i class="ti ti-eye"></i></button>
                <button class="btn btn-sm btn-light-info text-info download-txn" data-id="${txn.id}" title="Download Receipt"><i class="ti ti-download"></i></button>
              `;

              table.row.add([
                  `<code>${txn.transaction_code || txn.id}</code>`,
                  timeStr,
                  `<div class="d-flex align-items-center">
                    <div class="bg-primary text-white rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">${initials}</div>
                    <div class="ms-3">
                      <h6 class="mb-0 fw-semibold">${name}</h6>
                      <small class="text-muted">${subEmail}</small>
                    </div>
                  </div>`,
                  `<span class="fw-bold text-dark">₱${parseFloat(txn.amount).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</span>`,
                  statusBadge,
                  actions
              ]).node().setAttribute('data-full-json', JSON.stringify(txn));
          });
          table.draw();
        }
      }, 'json');

      // View Transaction
      $(document).on('click', '.view-txn', function() {
          const rowData = JSON.parse($(this).closest('tr').attr('data-full-json'));
          
          $('#detRef').text(rowData.transaction_code || rowData.id);
          $('#detAmount').text('₱' + parseFloat(rowData.amount).toLocaleString(undefined, {minimumFractionDigits: 2}));
          $('#detDesc').text(rowData.description || 'No description');
          $('#detGateway').text(rowData.gateway || 'N/A');
          
          var dateObj = new Date(rowData.created_at);
          $('#detDate').text(dateObj.toLocaleString('en-US', { month: 'short', day: 'numeric', year: 'numeric', hour: '2-digit', minute: '2-digit' }));
          
          var name = rowData.first_name ? (rowData.first_name + ' ' + rowData.last_name) : 'Unknown User';
          $('#detName').text(name);
          $('#detEmail').text(rowData.email || 'No email');
          $('#detInitials').text(rowData.first_name ? rowData.first_name.charAt(0).toUpperCase() : 'U');

          // Status Badge
          var badgeClass = 'bg-light-primary text-primary';
          if (rowData.status === 'success') badgeClass = 'bg-light-success text-success';
          else if (rowData.status === 'failed') badgeClass = 'bg-light-danger text-danger';
          else if (rowData.status === 'pending') badgeClass = 'bg-light-warning text-warning';
          
          $('#detStatusBadge').attr('class', 'badge rounded-pill ' + badgeClass).text(rowData.status.toUpperCase());

          $('#txnDetailsModal').modal('show');
      });

      // Simple implementation for download/receipt
      $(document).on('click', '.download-txn', function() {
          const rowData = JSON.parse($(this).closest('tr').attr('data-full-json'));
          // For now, toggle the modal and let them print, or just trigger window.print
          // We could generate a text blob as a receipt
          const receiptContent = `
TRANSACTION RECEIPT - AURORA TABS
----------------------------------
Ref: ${rowData.transaction_code || rowData.id}
Date: ${rowData.created_at}
Customer: ${rowData.first_name || ''} ${rowData.last_name || ''}
Email: ${rowData.email || ''}
----------------------------------
Description: ${rowData.description}
Amount: PHP ${parseFloat(rowData.amount).toLocaleString()}
Status: ${rowData.status.toUpperCase()}
Gateway: ${rowData.payment_gateway || 'N/A'}
----------------------------------
Thank you for your purchase!
          `;
          
          const blob = new Blob([receiptContent], { type: 'text/plain' });
          const url = window.URL.createObjectURL(blob);
          const a = document.createElement('a');
          a.href = url;
          a.download = `Receipt_${rowData.transaction_code || rowData.id}.txt`;
          a.click();
          window.URL.revokeObjectURL(url);
      });

      // Export CSV
      $('#exportCsv').on('click', function() {
          const form = $('<form>', {
              action: 'endpoints/finance_handler.php',
              method: 'POST'
          }).append($('<input>', {
              type: 'hidden',
              name: 'action',
              value: 'export_csv'
          }));
          $('body').append(form);
          form.submit();
          form.remove();
      });

      // Print PDF
      $('#printPdf').on('click', function() {
          window.print();
      });

    });
  </script>
</body>

</html>
