 <?php
  require_once '../auth/helpers/security_helper.php';
  require_once '../auth/config/db_connection.php';
  
  // Pass $conn explicitly to avoid scope issues
  $can_view_notifications = has_permission('Notifications', 'view', $conn);
  $can_view_messages = has_permission('Messages', 'view', $conn);
  $can_view_profile = has_permission('My Account', 'view', $conn);
  $can_view_account_settings = has_permission('Account', 'view', $conn);
  $can_view_security_settings = has_permission('Security Settings', 'view', $conn);
  $can_view_activity_logs = has_permission('Activity Logs', 'view', $conn);
  ?>
  <script>
    // Global Configuration for AJAX Endpoints
    window.APP_CONFIG = {
        admin_base: 'http://localhost/auroratabs/app/admin/',
        profile_handler: 'http://localhost/auroratabs/app/admin/endpoints/profile_handler.php'
    };
  </script>
 <header class="app-header sticky-top">
            <nav class="navbar navbar-expand-lg navbar-light">
              <ul class="navbar-nav">
                <li class="nav-item d-block d-xl-none">
                  <a class="nav-link sidebartoggler " id="headerCollapse" href="javascript:void(0)">
                    <i class="ti ti-menu-2"></i>
                  </a>
                </li>
             
              </ul>
              <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
                <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end">
                  <?php if ($can_view_notifications): ?>
                   <li class="nav-item dropdown">
                  <a class="nav-link " href="javascript:void(0)" id="drop1" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <iconify-icon icon="solar:bell-linear" class="fs-6"></iconify-icon>
                    <div class="notification bg-primary rounded-circle"></div>
                  </a>
                  <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop1">
                    <div class="message-body">
                      <div class="px-3 py-2 border-bottom">
                         <h6 class="mb-0 fw-semibold">Notifications</h6>
                      </div>
                      <a href="javascript:void(0)" class="dropdown-item py-2 border-bottom">
                        <div class="d-flex align-items-center">
                          <div class="bg-light-primary text-primary p-2 rounded-circle me-3">
                            <i class="ti ti-crown fs-4"></i>
                          </div>
                          <div>
                            <p class="mb-0 fs-3 fw-semibold">New Pro Subscription</p>
                            <small class="text-muted">Matahom sa 2026</small>
                          </div>
                        </div>
                      </a>
                      <a href="javascript:void(0)" class="dropdown-item py-2 border-bottom">
                        <div class="d-flex align-items-center">
                          <div class="bg-light-success text-success p-2 rounded-circle me-3">
                            <i class="ti ti-user-plus fs-4"></i>
                          </div>
                          <div>
                            <p class="mb-0 fs-3 fw-semibold">New Judge Registered</p>
                            <small class="text-muted">3 people joined today</small>
                          </div>
                        </div>
                      </a>
                      <div class="p-2 text-center">
                        <a href="notifications.php" class="text-primary small fw-semibold">View all notifications</a>
                      </div>
                    </div>
                  </div>
                </li>
                <?php endif; ?>
                <?php if ($can_view_messages): ?>
                <li class="nav-item">
                  <a class="nav-link" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#messagesModal">
                    <iconify-icon icon="solar:chat-line-linear" class="fs-6"></iconify-icon>
                    <div class="notification bg-success rounded-circle"></div>
                  </a>
                </li>
                <?php endif; ?>
                     <li class="nav-item dropdown">
                    <a class="nav-link " href="javascript:void(0)" id="drop2" data-bs-toggle="dropdown"
                      aria-expanded="false">
                      <img src="src/assets/images/profile/user1.jpg" id="headerProfileImage" alt="" width="35" height="35"
                        class="rounded-circle">
                    </a>
                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up" aria-labelledby="drop2">
                      <div class="message-body">
                        <?php if ($can_view_profile): ?>
                        <a href="profile.php" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-user fs-6"></i>
                          <p class="mb-0 fs-3">My Profile</p>
                        </a>
                        <?php endif; ?>
                        <?php if ($can_view_account_settings): ?>
                        <a href="general-settings.php" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-settings fs-6"></i>
                          <p class="mb-0 fs-3">Account Settings</p>
                        </a>
                        <?php endif; ?>
                        <?php if ($can_view_security_settings): ?>
                        <a href="security-settings.php" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-shield-lock fs-6"></i>
                          <p class="mb-0 fs-3">Security</p>
                        </a>
                        <?php endif; ?>
                        <?php if ($can_view_activity_logs): ?>
                        <a href="activity-logs.php" class="d-flex align-items-center gap-2 dropdown-item">
                          <i class="ti ti-list-check fs-6"></i>
                          <p class="mb-0 fs-3">Activity Logs</p>
                        </a>
                        <?php endif; ?>
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#logoutModal"
                          class="btn btn-outline-primary mx-3 mt-2 d-block">Logout</a>
                      </div>
                    </div>
                  </li>
                </ul>
              </div>
            </nav>
          </header>