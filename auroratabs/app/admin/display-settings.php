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
  <title>Display Settings - Aurora Tabs</title>
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
                  <h4 class="fw-semibold mb-8">Display Settings</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">Display Settings</li>
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

          <form id="logoForm">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title fw-semibold mb-4">Competition Logo</h5>
                            <div class="mb-4">
                                <label for="competitionSelect" class="form-label">Select Competition</label>
                                <select class="form-select" id="competitionSelect" required>
                                    <option value="">-- Choose Competition --</option>
                                </select>
                                <div class="form-text">Choose which event logo you want to update.</div>
                            </div>
                            <div class="mb-4 text-center">
                                <img id="logoPreview" src="src/assets/images/backgrounds/blog-bg-1.jpg" alt="Preview" class="img-fluid rounded border p-2" style="max-height: 250px; object-fit: contain;">
                            </div>
                            <div class="mb-3">
                                <label for="eventLogo" class="form-label">Upload New Logo</label>
                                <input class="form-control" type="file" id="eventLogo" accept="image/png, image/jpeg, image/gif, image/webp" required disabled>
                                <div class="form-text">Recommended size: 500x500px (PNG/JPG).</div>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top text-end">
                            <button type="submit" class="btn btn-primary" id="saveBtn" disabled>Save Changes</button>
                        </div>
                    </div>
                </div>
            </div>
          </form>

          <?php include 'components/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>
  <?php include 'components/scripts.php'; ?>
  <script>
    $(document).ready(function() {
        // Load all competitions
        $.post('endpoints/voting_handler.php', { action: 'get_all_competitions' }, function(response) {
            if (response.status === 'success') {
                response.data.forEach(function(comp) {
                    $('#competitionSelect').append(`<option value="${comp.id}">${comp.title}</option>`);
                });
            }
        }, 'json');

        // Handle competition selection
        $('#competitionSelect').on('change', function() {
            const compId = $(this).val();
            if (compId) {
                $('#eventLogo').prop('disabled', false);
                $('#saveBtn').prop('disabled', false);
                // Fetch existing logo
                var baseUrl = '../uploads/';
                $.post('endpoints/competitions_handler.php', { action: 'get', id: compId }, function(response) {
                    // Try to resolve old db path strings into actual image URLs nicely
                    if (response.status === 'success' && response.data.banner_url) {
                         var url = response.data.banner_url;
                         if(url.startsWith('../')) url = url.substring(3);
                         $('#logoPreview').attr('src', '../' + url); 
                    } else {
                        $('#logoPreview').attr('src', 'src/assets/images/backgrounds/blog-bg-1.jpg');
                    }
                }, 'json');
            } else {
                $('#eventLogo').prop('disabled', true);
                $('#saveBtn').prop('disabled', true);
                $('#logoPreview').attr('src', 'src/assets/images/backgrounds/blog-bg-1.jpg');
            }
        });

        // Handle image preview
        $('#eventLogo').on('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#logoPreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Submit form
        $('#logoForm').on('submit', function(e) {
            e.preventDefault();
            const compId = $('#competitionSelect').val();
            const fileInput = $('#eventLogo')[0].files[0];

            if (!compId || !fileInput) {
                Swal.fire('Error', 'Please select a competition and choose an image.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'upload_logo');
            formData.append('id', compId);
            formData.append('logo', fileInput);

            const btn = $('#saveBtn');
            const originalText = btn.html();
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Uploading...');

            $.ajax({
                url: 'endpoints/competitions_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Success', response.message, 'success');
                        $('#eventLogo').val('');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'A server error occurred during upload.', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).html(originalText);
                }
            });
        });
    });
  </script>
</body>
</html>
