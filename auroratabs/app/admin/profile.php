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
  <title>Admin Profile - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
    .profile-cover {
      height: 180px;
      background: linear-gradient(135deg, #5d87ff 0%, #3a5cc0 100%);
      border-radius: 12px 12px 0 0;
    }
    .profile-avatar-wrap {
      margin-top: -60px;
      text-align: center;
    }
    .profile-avatar {
      width: 120px;
      height: 120px;
      border: 5px solid #fff;
      box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.1);
    }
    .profile-tab .nav-link {
        color: #2a3547;
        font-weight: 600;
        padding: 15px 20px;
        border: none;
        border-bottom: 2px solid transparent;
        transition: all 0.3s ease;
    }
    .profile-tab .nav-link.active {
        color: #5d87ff;
        border-bottom-color: #5d87ff;
        background: transparent;
    }
    .profile-tab .nav-link i {
        font-size: 1.2rem;
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
                  <h4 class="fw-semibold mb-8">My Profile</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item">
                        <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                      </li>
                      <li class="breadcrumb-item" aria-current="page">User Profile</li>
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

          <div class="row">
            <!-- Profile Info Column -->
            <div class="col-lg-4">
                <div class="card overflow-hidden">
                    <div class="profile-cover"></div>
                    <div class="card-body p-4">
                        <div class="profile-avatar-wrap mb-3 position-relative d-inline-block">
                            <img src="src/assets/images/profile/user-8.jpg" id="profileImageDisplay" class="profile-avatar rounded-circle img-fluid" alt="User Avatar">
                            <button class="btn btn-primary rounded-circle position-absolute bottom-0 end-0 p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="$('#avatarModal').modal('show')">
                                <i class="ti ti-pencil fs-4"></i>
                            </button>
                        </div>
                        <div class="text-center border-bottom pb-4 mb-4">
                            <h4 class="fw-semibold mb-1" id="display_name">Loading...</h4>
                            <p class="text-muted mb-3" id="display_username">@loading</p>
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                <span class="badge bg-light-primary text-primary fw-semibold" id="display_status">ACTIVE</span>
                                <span class="badge bg-light-info text-info fw-semibold" id="display_role">Role: --</span>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Contact Information</h6>
                            <ul class="list-unstyled mb-0">
                                <li class="d-flex align-items-center gap-2 mb-3">
                                    <i class="ti ti-mail text-primary fs-5"></i>
                                    <span class="text-muted" id="display_email">...</span>
                                </li>
                                <li class="d-flex align-items-center gap-2 mb-3">
                                    <i class="ti ti-phone text-primary fs-5"></i>
                                    <span class="text-muted" id="display_mobile">...</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Column -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-head">
                        <ul class="nav nav-pills profile-tab justify-content-start border-bottom px-4" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active d-flex align-items-center gap-2" id="pills-account-tab" data-bs-toggle="pill" data-bs-target="#pills-account" type="button" role="tab" aria-controls="pills-account" aria-selected="true">
                                    <i class="ti ti-user-circle"></i>
                                    <span class="d-none d-md-block">Account Settings</span>
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link d-flex align-items-center gap-2" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab" aria-controls="pills-security" aria-selected="false">
                                    <i class="ti ti-shield-lock"></i>
                                    <span class="d-none d-md-block">Security</span>
                                </button>
                            </li>
                        </ul>
                    </div>
                    <div class="card-body p-4">
                        <div class="tab-content" id="pills-tabContent">
                            <!-- Account Settings Tab -->
                            <div class="tab-pane fade show active" id="pills-account" role="tabpanel" aria-labelledby="pills-account-tab">
                                <h5 class="fw-semibold mb-4">Edit Personal Details</h5>
                                <form id="profileForm">
                                    <div class="row">
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">First Name</label>
                                            <input type="text" class="form-control" name="fname" id="input_fname" required>
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Middle Name</label>
                                            <input type="text" class="form-control" name="mname" id="input_mname">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label fw-semibold">Last Name</label>
                                            <input type="text" class="form-control" name="lname" id="input_lname" required>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Email Address</label>
                                            <input type="email" class="form-control" name="email" id="input_email" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Phone Number</label>
                                            <input type="text" class="form-control" name="mobile" id="input_mobile">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Username</label>
                                        <input type="text" class="form-control" name="username" id="input_username">
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4 save-profile-btn">Save Changes</button>
                                    </div>
                                </form>
                            </div>

                            <!-- Security Tab -->
                            <div class="tab-pane fade" id="pills-security" role="tabpanel" aria-labelledby="pills-security-tab">
                                <h5 class="fw-semibold mb-4">Update Password</h5>
                                <div class="alert alert-light-warning text-warning border-0 p-3 mb-4">
                                    <div class="d-flex align-items-center">
                                        <i class="ti ti-alert-triangle fs-5 me-2"></i>
                                        <span>After updating your password, you may be required to re-login.</span>
                                    </div>
                                </div>
                                <form id="passwordForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Current Password</label>
                                        <input type="password" class="form-control" name="current_password" placeholder="••••••••" required>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">New Password</label>
                                            <input type="password" class="form-control" name="new_password" id="new_password" placeholder="Minimum 8 characters" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label fw-semibold">Confirm New Password</label>
                                            <input type="password" class="form-control" name="confirm_password" placeholder="Re-enter password" required>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-primary px-4">Update Security</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity Card -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title fw-semibold mb-4">Recent Activity Logs</h5>
                        <div class="table-responsive">
                            <table class="table align-middle text-nowrap mb-0">
                                <thead>
                                    <tr>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Time</th>
                                    </tr>
                                </thead>
                                <tbody id="activity_log_body">
                                    <tr><td colspan="3" class="text-center text-muted">Loading activity...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
          </div>

          <!-- Avatar Upload Modal -->
          <div class="modal fade" id="avatarModal" tabindex="-1" aria-labelledby="avatarModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title" id="avatarModalLabel">Update Profile Picture</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                  <div class="mb-3">
                    <img id="avatarPreview" src="src/assets/images/profile/user-1.jpg" class="rounded-circle img-fluid mb-3" style="width: 150px; height: 150px; object-fit: cover;" alt="Preview">
                    <br>
                    <button class="btn btn-outline-primary btn-sm" onclick="document.getElementById('avatarUpload').click()">Choose Image</button>
                    <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
                  </div>
                  <small class="text-muted">Allowed: JPG, PNG, WEBP. Max: 5MB</small>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                  <button type="button" class="btn btn-primary" id="saveAvatarBtn">Save changes</button>
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
        
        loadProfile();

        function loadProfile() {
            $.post('endpoints/profile_handler.php', { action: 'get_profile' }, function(response) {
                if(response.status === 'success') {
                    const u = response.data.user;
                    const logs = response.data.logs;

                    // Populate Display
                    $('#display_name').text(`${u.first_name} ${u.last_name}`);
                    $('#display_username').text('@' + u.username);
                    $('#display_status').text(u.status.toUpperCase());
                    $('#display_role').text(u.role_name ? u.role_name : 'Role: ' + u.role_id);
                    $('#display_email').text(u.email);
                    $('#display_mobile').text(u.mobile || 'N/A');
                    
                    if(u.avatar_url) {
                        const avatarPath = window.APP_CONFIG.admin_base + u.avatar_url;
                        $('#profileImageDisplay').attr('src', avatarPath);
                        $('#avatarPreview').attr('src', avatarPath); 
                        $('#headerProfileImage').attr('src', avatarPath);
                    }

                    // Populate Form Inputs
                    $('#input_fname').val(u.first_name);
                    $('#input_mname').val(u.middle_name);
                    $('#input_lname').val(u.last_name);
                    $('#input_email').val(u.email);
                    $('#input_mobile').val(u.mobile);
                    $('#input_username').val(u.username);

                    // Populate Activity Logs
                    let logHtml = '';
                    if(logs.length > 0) {
                        logs.forEach(log => {
                            let bgClass = 'bg-light-secondary text-secondary';
                            let icon = 'ti-activity';
                            const act = log.action.toLowerCase();
                            
                            if(act.includes('login')) { bgClass = 'bg-light-success text-success'; icon = 'ti-login'; }
                            else if(act.includes('create')) { bgClass = 'bg-light-primary text-primary'; icon = 'ti-plus'; }
                            else if(act.includes('update')) { bgClass = 'bg-light-info text-info'; icon = 'ti-pencil'; }
                            else if(act.includes('delete')) { bgClass = 'bg-light-danger text-danger'; icon = 'ti-trash'; }

                            logHtml += `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="${bgClass} p-2 rounded-circle me-3">
                                                <i class="ti ${icon}"></i>
                                            </div>
                                            <span class="fw-semibold">${log.action}</span>
                                        </div>
                                    </td>
                                    <td>${log.details}</td>
                                    <td><span class="text-muted">${new Date(log.created_at).toLocaleString('en-US', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' })}</span></td>
                                </tr>
                            `;
                        });
                    } else {
                        logHtml = '<tr><td colspan="3" class="text-center text-muted">No recent activity.</td></tr>';
                    }
                    $('#activity_log_body').html(logHtml);
                } else {
                    Swal.fire('Error', 'Failed to load profile data', 'error');
                }
            }, 'json');
        }

        // Avatar Preview
        $('#avatarUpload').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#avatarPreview').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });

        // Save Avatar
        $('#saveAvatarBtn').on('click', function() {
            const fileInput = document.getElementById('avatarUpload');
            const file = fileInput.files[0];
            
            if (!file) {
                Swal.fire('Warning', 'Please select an image first.', 'warning');
                return;
            }

            const formData = new FormData();
            formData.append('avatar', file);
            formData.append('action', 'update_avatar');
            
            const btn = $(this);
            btn.prop('disabled', true).text('Uploading...');

            $.ajax({
                url: 'endpoints/profile_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                         $('#avatarModal').modal('hide');
                         const toast = Swal.mixin({
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000,
                            timerProgressBar: true
                        });
                        toast.fire({ icon: 'success', title: 'Avatar updated' });
                        loadProfile();
                        $('#avatarUpload').val(''); // Reset input
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to upload avatar', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text('Save changes');
                }
            });
        });

        // Profile Update
        $('#profileForm').on('submit', function(e) {
            e.preventDefault();
            const btn = $('.save-profile-btn');
            const originalText = btn.text();
            
            // Create FormData object to include the action
            const formData = new FormData(this);
            formData.append('action', 'update_details');

            btn.prop('disabled', true).text('Saving...');
            
            $.ajax({
                url: 'endpoints/profile_handler.php',
                type: 'POST',
                data: formData, // Use FormData for flexibility
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.fire('Success', response.message, 'success').then(() => {
                           loadProfile(); // Reload data without refreshing page
                        });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to update profile', 'error');
                },
                complete: function() {
                    btn.prop('disabled', false).text(originalText);
                }
            });
        });

        // Password Update
        $('#passwordForm').on('submit', function(e) {
            e.preventDefault();
            const newPass = $('#new_password').val();
            const confirmPass = $('input[name="confirm_password"]').val();

            if(newPass.length < 8) {
                Swal.fire('Warning', 'New password must be at least 8 characters', 'warning');
                return;
            }

            if(newPass !== confirmPass) {
                Swal.fire('Error', 'New passwords do not match', 'error');
                return;
            }
            
            // Create FormData object to include the action
            const formData = new FormData(this);
            formData.append('action', 'update_password');

            $.ajax({
                url: 'endpoints/profile_handler.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'success') {
                         Swal.fire('Success', response.message, 'success').then(() => {
                            $('#passwordForm')[0].reset();
                         });
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to update password', 'error');
                }
            });
        });
    });
  </script>
</body>

</html>
