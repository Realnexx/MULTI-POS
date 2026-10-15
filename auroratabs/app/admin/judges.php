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
  <title>Judges - Aurora Tabs</title>
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
                   <div class="d-block d-md-flex justify-content-between align-items-center mb-8">
                    <h4 class="fw-semibold mb-2 mb-md-0">Judges</h4>
                   
                  </div>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Judges</li>
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
                   
                   
                   <div class="table-responsive">
                    <table id="judgesTable" class="table table-hover align-middle table-responsive-stack">
                      <thead class="bg-light">
                        <tr>
                          <th>Judge</th>
                          <th>Username</th>
                          <th>Role/Title</th>
                          <th>Status</th>
                         
                        </tr>
                      </thead>
                       <tbody id="judgesTableBody">
                         <!-- Dynamic Content -->
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
      const judgesTable = $('#judgesTable').DataTable({
        "responsive": true,
        "dom": '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mb-4"fl>' +
          'rt' +
          '<"d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4"ip>',
        "language": {
          "search": "_INPUT_",
          "searchPlaceholder": "Search judges..."
        },
        "createdRow": function(row, data, dataIndex) {
            const headers = $(row).closest('table').find('thead th');
            $(row).find('td').each(function(index) {
                $(this).attr('data-label', $(headers[index]).text());
            });
        }
      });

      loadJudges();

      function loadJudges() {
        $.post('endpoints/admin_users_handler.php', { action: 'list_judges' }, function(response) {
            if (response.status === 'success') {
                judgesTable.clear();
                response.data.forEach(judge => {
                    const statusBadge = judge.status === 'active' 
                                ? '<span class="badge bg-light-success text-success">Active</span>' 
                                : '<span class="badge bg-light-danger text-danger">Suspended</span>';
                    
                    const judgeInfo = `<div class="d-flex align-items-center">
                                          <span class="avatar avatar-xs rounded-circle bg-light-primary text-primary d-flex align-items-center justify-content-center fw-bold me-3" style="width:40px; height:40px;">
                                              ${judge.first_name.charAt(0)}${judge.last_name.charAt(0)}
                                          </span>
                                          <div>
                                            <h6 class="mb-1 fw-semibold">${judge.first_name} ${judge.last_name}</h6>
                                            <p class="fs-2 mb-0 text-muted">${judge.email}</p>
                                          </div>
                                        </div>`;

                    judgesTable.row.add([
                        judgeInfo,
                        `<div class="d-flex align-items-center gap-2">
                            <span class="badge bg-light-primary text-primary fw-semibold">${judge.username}</span>
                            <i class="ti ti-copy fs-4 cursor-pointer text-muted copy-username" data-username="${judge.username}" title="Copy"></i>
                         </div>`,
                        judge.role_name, // Or a specific 'designation' field if available
                        statusBadge,
                      
                    ]);
                });
                judgesTable.draw();
            }
        });
      }

      $(document).on('click', '.copy-username', function() {
          const username = $(this).data('username');
          navigator.clipboard.writeText(username);
          Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'success',
              title: 'Username copied to clipboard',
              showConfirmButton: false,
              timer: 1500
          });
      });
    });

    function submitJudgeForm() {
        var myModalEl = document.getElementById('addJudgeModal');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        modal.hide();
    }

    function previewJudgeImage(input) {
        var previewContainer = document.getElementById('judgeImagePreviewContainer');
        var previewImage = document.getElementById('judgeImagePreview');
        
        if (input.files && input.files[0]) {
            var reader = new FileReader();

            reader.onload = function (e) {
                previewImage.src = e.target.result;
                previewContainer.style.display = 'block';
            }

            reader.readAsDataURL(input.files[0]);
        } else {
             previewImage.src = '#';
             previewContainer.style.display = 'none';
        }
    }
  </script>
</body>
</html>
