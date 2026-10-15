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
  <title>Vote Logs - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Vote Logs</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Vote Logs</li>
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
                   <div class="d-block d-md-flex justify-content-between align-items-center mb-4">
                     <h5 class="mb-2 mb-md-0">Transaction History</h5>
                     <div class="d-flex flex-column flex-md-row gap-2 mt-2 mt-md-0">
                         <input type="date" id="filterDate" class="form-control form-control-sm w-100 w-md-auto">
                         <div class="dropdown w-100 w-md-auto">
                            <button class="btn btn-sm btn-outline-secondary dropdown-toggle w-100 w-md-auto" type="button" id="filterComp" data-bs-toggle="dropdown" aria-expanded="false">
                              Competition: All
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="filterComp">
                              <li><a class="dropdown-item" href="#">All</a></li>
                              <li><a class="dropdown-item" href="#">Matahom sa Ubay 2026</a></li>
                            </ul>
                          </div>
                          <button class="btn btn-sm btn-outline-success w-100 w-md-auto"><i class="ti ti-file-export me-1"></i>Export CSV</button>
                     </div>
                   </div>
                   
                   <div class="table-responsive">
                    <table id="voteLogsTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Transaction ID</th>
                          <th>Voter</th>
                          <th>Candidate</th>
                          <th>Votes</th>
                          <th>Amount</th>
                          <th>Payment</th>
                          <th>Timestamp</th>
                        </tr>
                      </thead>
                       <tbody>
                       </tbody>
                    </table>
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
    $(document).ready(function() {
      var table = $('#voteLogsTable').DataTable({
        "responsive": true,
        "dom": '<"d-flex justify-content-between align-items-center mb-4"f<"d-flex align-items-center"l>>' +
          'rt' +
          '<"d-flex justify-content-between align-items-center mt-4"ip>',
        "order": [[ 6, "desc" ]], // Sort by Timestamp desc
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search logs..."
        }
      });

      // Fetch ALL competitions for the dropdown
      $.post('endpoints/voting_handler.php', { action: 'get_all_competitions' }, function(response) {
         if (response.status === 'success') {
             var ul = $('#filterComp').next('.dropdown-menu');
             ul.empty();
             ul.append('<li><a class="dropdown-item comp-filter" href="#" data-id="0">All</a></li>');
             response.data.forEach(function(comp) {
                 ul.append('<li><a class="dropdown-item comp-filter" href="#" data-id="' + comp.id + '">' + comp.title + '</a></li>');
             });
         }
      }, 'json');

      let currentCompId = 0;
      let currentDateFilter = '';

      function loadLogs(compId, dateFilter = '') {
          $.post('endpoints/voting_handler.php', { action: 'get_vote_logs', competition_id: compId, date: dateFilter }, function(response) {
              if (response.status === 'success') {
                  table.clear();
                  response.data.forEach(function(log) {
                      var voterName = log.voter_first ? (log.voter_first + ' ' + log.voter_last) : 'Anonymous';
                      var voterSub = log.voter_email ? log.voter_email : (log.ip_address || 'Unknown IP');
                      var initial = voterName.charAt(0).toUpperCase();

                      var txnId = log.transaction_code ? log.transaction_code : 'VOTE-' + String(log.vote_id).padStart(6, '0');
                      var amount = log.amount ? '₱' + parseFloat(log.amount).toFixed(2) : '<span class="text-muted">Free/Wallet</span>';
                      
                      var gatewayIcon = 'ti-wallet';
                      if(log.amount && log.gateway === 'GCash') gatewayIcon = 'ti-wallet';
                      else if(log.gateway === 'Stripe') gatewayIcon = 'ti-brand-stripe';
                      else if(log.gateway === 'PayPal') gatewayIcon = 'ti-brand-paypal';
                      
                      var payment = log.amount ? `<i class="ti ${gatewayIcon} text-primary fs-5 me-1"></i>${log.gateway}` : '<span class="badge bg-light-secondary text-secondary">System Balance</span>';

                      var dateObj = new Date(log.created_at);
                      var timeStr = dateObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });

                      table.row.add([
                          `<span class="fw-semibold">${txnId}</span>`,
                          `<div class="d-flex align-items-center">
                              <span class="avatar avatar-xs rounded-circle bg-light-primary text-primary me-2">${initial}</span>
                              <div>
                                  <h6 class="mb-0 fw-semibold">${voterName}</h6>
                                  <small class="text-muted">${voterSub}</small>
                              </div>
                          </div>`,
                          `<div class="d-flex align-items-center">
                              <span class="badge bg-light-primary text-primary me-2">#${log.candidate_number}</span>
                              <span class="fw-semibold">${log.cand_first} ${log.cand_last}</span>
                          </div>`,
                          `<span class="badge bg-light-info text-info fs-2">${log.vote_count} Votes</span>`,
                          amount,
                          payment,
                          timeStr
                      ]);
                  });
                  table.draw();
              }
          }, 'json');
      }

      // Initial load
      loadLogs(0);

      // Handle competition filter clicks
      $(document).on('click', '.comp-filter', function(e) {
          e.preventDefault();
          var id = $(this).data('id');
          var title = $(this).text();
          $('#filterComp').text('Competition: ' + title);
          currentCompId = id;
          loadLogs(currentCompId, currentDateFilter);
      });

      // Handle date filter
      $('#filterDate').on('change', function() {
          currentDateFilter = $(this).val();
          loadLogs(currentCompId, currentDateFilter);
      });
    });
  </script>
</body>
</html>
