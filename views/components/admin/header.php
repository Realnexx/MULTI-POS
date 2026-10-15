<?php
// Header Component - AuroraTabs Modernize Style for POS
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../models/User.php';
require_once __DIR__ . '/../../../models/StockNotification.php';

$current_user = $_SESSION['user'] ?? [
    'name' => 'Admin User',
    'role' => 'Administrator',
    'email' => 'admin@pos.local',
    'branch' => 'Main Branch'
];
$user_avatar = get_user_avatar($current_user['avatar_url'] ?? '', $current_user['name'] ?? 'A');
$user_branch = $current_user['branch'] ?? 'General';

// Sync and fetch stock alerts
$header_branch_id = !empty($_SESSION['user']['branch_id']) ? (int)$_SESSION['user']['branch_id'] : null;
if (User::has_all_branch_access($_SESSION['user']['id'])) $header_branch_id=null;
$has_branch_scope = User::has_all_branch_access($_SESSION['user']['id']) || !empty($_SESSION['user']['branch_id']);
$can_stock_alerts = $has_branch_scope && (User::has_permission($_SESSION['user']['id'], 'view_product') || User::has_permission($_SESSION['user']['id'], 'manage_inventory'));
if ($can_stock_alerts) StockNotification::sync_stock_alerts($header_branch_id);
$stock_counts = $can_stock_alerts ? StockNotification::get_alert_counts($header_branch_id) : ['total'=>0,'out_of_stock'=>0,'low_stock'=>0,'overstock'=>0];
$initial_alerts = $can_stock_alerts ? StockNotification::get_active_alerts(25, $header_branch_id) : [];
?>

<style>
  /* ── Header Stock Notification Styles ── */
  .stock-notif-btn {
    position: relative !important;
  }
  .stock-notif-badge {
    position: absolute !important;
    top: -3px !important;
    right: -3px !important;
    font-size: 0.65rem !important;
    font-weight: 800 !important;
    padding: 2px 6px !important;
    border: 2px solid #ffffff !important;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.45) !important;
    line-height: 1.1 !important;
    animation: notifPulse 2.2s infinite ease-in-out;
  }
  @keyframes notifPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.08); }
  }

  .stock-notif-dropdown {
    width: 390px !important;
    max-width: 95vw !important;
    border-radius: 20px !important;
    border: 1px solid #e8edf5 !important;
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.12), 0 4px 16px rgba(93, 135, 255, 0.06) !important;
    overflow: hidden;
    background: #ffffff;
    padding: 0 !important;
  }

  .stock-notif-accent-bar {
    height: 3px;
    width: 100%;
    background: linear-gradient(90deg, #ef4444, #f59e0b 50%, #5d87ff);
  }

  /* Segmented Category Filter Pills */
  .notif-filter-bar {
    display: flex;
    align-items: center;
    gap: 4px;
    background: #f8fafc;
    border-bottom: 1px solid #edf2f7;
    padding: 6px 12px;
    overflow-x: auto;
  }
  .notif-filter-pill {
    border: none;
    background: transparent;
    border-radius: 8px;
    font-size: 0.72rem;
    font-weight: 700;
    color: #64748b;
    padding: 4px 8px;
    cursor: pointer;
    transition: all 0.15s ease;
    white-space: nowrap;
    display: inline-flex;
    align-items: center;
    gap: 4px;
  }
  .notif-filter-pill:hover {
    color: #1e293b;
    background: #eef2f6;
  }
  .notif-filter-pill.active {
    background: #ffffff;
    color: #5d87ff;
    box-shadow: 0 1px 4px rgba(0,0,0,0.06);
  }

  /* Alert Item Cards */
  .notif-item-card {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    transition: background-color 0.15s ease;
    text-decoration: none;
    color: inherit;
    position: relative;
  }
  .notif-item-card:hover {
    background-color: #f8fafc;
  }
  .notif-thumb {
    width: 38px;
    height: 38px;
    border-radius: 10px;
    object-fit: cover;
    border: 1px solid #edf2f7;
    flex-shrink: 0;
  }
  .notif-dismiss-btn {
    opacity: 0;
    transition: opacity 0.15s ease;
    border: none;
    background: #f1f5f9;
    border-radius: 50%;
    width: 24px;
    height: 24px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #64748b;
    cursor: pointer;
    flex-shrink: 0;
  }
  .notif-item-card:hover .notif-dismiss-btn {
    opacity: 1;
  }
  .notif-dismiss-btn:hover {
    background: #e2e8f0;
    color: #1e293b;
  }
</style>

<header class="app-header sticky-top">
  <nav class="navbar navbar-expand-lg navbar-light">
    <ul class="navbar-nav align-items-center">
      <li class="nav-item d-block d-xl-none me-2">
        <a class="header-icon-btn sidebartoggler" id="headerCollapse" href="javascript:void(0)" title="Toggle Sidebar">
          <i class="ti ti-menu-2 fs-5"></i>
        </a>
      </li>
      <li class="nav-item d-none d-lg-block">
        <div class="d-flex align-items-center gap-2">
          <span class="badge bg-light-primary text-primary fw-semibold px-3 py-2 rounded-pill fs-2 d-inline-flex align-items-center">
            <iconify-icon icon="solar:shop-2-bold" class="me-1 fs-3"></iconify-icon>
            <?= htmlspecialchars($user_branch) ?>
          </span>
          <span class="text-muted fs-2 d-none d-xl-inline-flex align-items-center gap-1">
            <i class="ti ti-calendar fs-3"></i>
            <?= date('l, F j, Y') ?>
          </span>
        </div>
      </li>
    </ul>

    <div class="navbar-collapse justify-content-end px-0" id="navbarNav">
      <ul class="navbar-nav flex-row ms-auto align-items-center justify-content-end gap-2">
        
        <!-- Live Website Link -->
        <li class="nav-item d-none d-sm-block">
          <a href="<?= url('?route=home') ?>" target="_blank" class="btn btn-sm btn-light-primary text-primary header-action-btn shadow-none">
            <i class="ti ti-external-link fs-4"></i>
            <span>Website</span>
          </a>
        </li>

        <!-- Quick POS Launcher -->
        <?php if (User::has_permission($_SESSION['user']['id'] ?? 0, 'access_pos')): ?>
        <li class="nav-item d-none d-sm-block">
          <?php if (User::has_permission($_SESSION['user']['id'], 'access_pos')): ?><a href="<?= url('?route=pos') ?>" class="btn btn-sm btn-primary header-action-btn shadow-sm">
            <iconify-icon icon="solar:calculator-minimalistic-line-duotone" class="fs-4"></iconify-icon>
            <span>Open POS</span>
          </a><?php endif; ?>
        </li>
        <?php endif; ?>

        <!-- Automated Stock Notifications Dropdown -->
        <li class="nav-item dropdown" id="stockNotificationNavContainer" <?= !$can_stock_alerts ? 'style="display:none"' : '' ?>>
          <a class="header-icon-btn stock-notif-btn" href="javascript:void(0)" id="dropNotifications" data-bs-toggle="dropdown" aria-expanded="false" title="Stock Inventory Alerts">
            <iconify-icon icon="solar:bell-linear" class="fs-5"></iconify-icon>
            <span class="stock-notif-badge badge rounded-pill bg-danger <?= ($stock_counts['total'] > 0) ? '' : 'd-none' ?>" id="headerNotifBadge">
              <?= $stock_counts['total'] > 99 ? '99+' : $stock_counts['total'] ?>
            </span>
          </a>

          <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up shadow-lg border-0 stock-notif-dropdown" aria-labelledby="dropNotifications">
            <!-- Top Gradient Accent -->
            <div class="stock-notif-accent-bar"></div>

            <!-- Header -->
            <div class="px-3 pt-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center justify-content-center" style="width: 28px; height: 28px; border-radius: 8px; background: #fee2e2; color: #ef4444;">
                  <iconify-icon icon="solar:danger-triangle-bold" class="fs-4"></iconify-icon>
                </div>
                <div>
                  <h6 class="mb-0 fw-800 text-dark" style="font-size: 0.88rem; letter-spacing: -0.2px;">Stock Alerts</h6>
                  <span class="text-muted" style="font-size: 0.68rem;" id="headerNotifSubtitle">
                    <span id="notifTotalCountText"><?= $stock_counts['total'] ?></span> active item<?= $stock_counts['total'] != 1 ? 's' : '' ?> requiring attention
                  </span>
                </div>
              </div>
              <button type="button" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold p-0" id="btnMarkAllNotifsRead" style="font-size: 0.72rem;">
                Mark all read
              </button>
            </div>

            <!-- Category Filter Bar -->
            <div class="notif-filter-bar">
              <button type="button" class="notif-filter-pill active" data-filter="all">
                All <span class="badge rounded-pill bg-light text-dark ms-1" id="pillCountAll"><?= $stock_counts['total'] ?></span>
              </button>
              <button type="button" class="notif-filter-pill" data-filter="out_of_stock">
                <span class="badge rounded-pill bg-danger text-white ms-1" id="pillCountOut"><?= $stock_counts['out_of_stock'] ?></span> No Stock
              </button>
              <button type="button" class="notif-filter-pill" data-filter="low_stock">
                <span class="badge rounded-pill bg-warning text-dark ms-1" id="pillCountLow"><?= $stock_counts['low_stock'] ?></span> Low
              </button>
              <button type="button" class="notif-filter-pill" data-filter="overstock">
                <span class="badge rounded-pill text-white ms-1" style="background:#8b5cf6;" id="pillCountOver"><?= $stock_counts['overstock'] ?></span> Over
              </button>
            </div>

            <!-- Scrollable Alerts List -->
            <div id="notifItemsList" style="max-height: 330px; overflow-y: auto;">
              <?php if (!empty($initial_alerts)): ?>
                <?php foreach ($initial_alerts as $alert): 
                  $is_out = $alert['alert_type'] === 'out_of_stock';
                  $is_low = $alert['alert_type'] === 'low_stock';
                  $is_over = $alert['alert_type'] === 'overstock';
                  
                  if ($is_out) {
                    $badge_style = 'background: #fee2e2; color: #b91c1c;';
                    $badge_label = 'No Stock';
                    $stock_text = '<span class="text-danger fw-bold">0 units</span> (Min: ' . $alert['threshold'] . ')';
                  } elseif ($is_low) {
                    $badge_style = 'background: #fef3c7; color: #b45309;';
                    $badge_label = 'Low Stock';
                    $stock_text = '<span class="text-warning fw-bold">' . $alert['current_stock'] . ' units</span> (Min: ' . $alert['threshold'] . ')';
                  } else {
                    $badge_style = 'background: #f3e8ff; color: #7e22ce;';
                    $badge_label = 'Overstock';
                    $stock_text = '<span style="color:#7e22ce;" class="fw-bold">' . $alert['current_stock'] . ' units</span> (Max: ' . $alert['threshold'] . ')';
                  }
                  
                  $prod_img = get_product_image($alert['image_url'] ?? '', $alert['product_id']);
                ?>
                  <div class="notif-item-card" data-type="<?= $alert['alert_type'] ?>" data-id="<?= $alert['id'] ?>">
                    <img src="<?= $prod_img ?>" class="notif-thumb" alt="Product"
                         onerror="this.src='https://ui-avatars.com/api/?name=<?= urlencode($alert['product_name']) ?>&background=eef2ff&color=5d87ff&bold=true&size=38'">
                    <div class="flex-grow-1 overflow-hidden">
                      <div class="d-flex align-items-center justify-content-between gap-1 mb-1">
                        <span class="fw-bold text-dark text-truncate" style="font-size: 0.81rem;"><?= htmlspecialchars($alert['product_name']) ?></span>
                        <span class="badge rounded-pill" style="<?= $badge_style ?> font-size: 0.65rem; font-weight: 700;"><?= $badge_label ?></span>
                      </div>
                      <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.72rem;">
                        <span><?= $stock_text ?></span>
                        <span style="font-size: 0.68rem;"><?= $alert['time_ago'] ?></span>
                      </div>
                    </div>
                    <button type="button" class="notif-dismiss-btn" title="Mark as resolved / read" data-id="<?= $alert['id'] ?>">
                      <iconify-icon icon="solar:check-read-linear" class="fs-3"></iconify-icon>
                    </button>
                  </div>
                <?php endforeach; ?>
              <?php endif; ?>

              <!-- Empty State (Shown when no active alerts) -->
              <div id="notifEmptyState" class="p-4 text-center <?= empty($initial_alerts) ? '' : 'd-none' ?>">
                <div class="d-flex align-items-center justify-content-center mx-auto mb-2"
                     style="width: 44px; height: 44px; border-radius: 50%; background: #f0fdf4; color: #16a34a;">
                  <iconify-icon icon="solar:check-circle-bold" class="fs-6"></iconify-icon>
                </div>
                <h6 class="fw-bold text-dark mb-1" style="font-size: 0.85rem;">Inventory Healthy</h6>
                <p class="text-muted small mb-0" style="font-size: 0.74rem;">All stock levels are within normal operating thresholds.</p>
              </div>
            </div>

            <!-- Dropdown Footer -->
            <div class="p-2 border-top bg-light text-center">
              <?php if (User::has_permission($_SESSION['user']['id'], 'view_product')): ?><a href="<?= url('?route=inventory/products') ?>" class="text-primary small fw-bold text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                <span>View Products Catalog & Inventory</span>
                <iconify-icon icon="solar:arrow-right-linear" class="fs-3"></iconify-icon>
              </a><?php endif; ?>
            </div>
          </div>
        </li>

        <!-- User Profile Dropdown -->
        <li class="nav-item dropdown ms-1">
          <a class="header-user-btn" href="javascript:void(0)" id="dropUserProfile" data-bs-toggle="dropdown" aria-expanded="false">
            <img 
              src="<?= $user_avatar ?>" 
              alt="<?= htmlspecialchars($current_user['name']) ?>" 
              class="header-user-avatar"
            />
            <div class="header-user-meta d-none d-md-flex">
              <span class="header-user-name"><?= htmlspecialchars(explode(' ', $current_user['name'])[0]) ?></span>
              <span class="header-user-role"><?= htmlspecialchars($current_user['role'] ?? 'Staff') ?></span>
            </div>
            <i class="ti ti-chevron-down fs-2 text-muted d-none d-md-inline ms-1"></i>
          </a>
          <div class="dropdown-menu dropdown-menu-end dropdown-menu-animate-up shadow-lg border-0 rounded-4" aria-labelledby="dropUserProfile" style="min-width: 260px;">
            <div class="message-body p-3">
              <div class="d-flex align-items-center gap-3 pb-3 border-bottom mb-2">
                <img src="<?= $user_avatar ?>" width="44" height="44" class="rounded-circle object-fit-cover border" />
                <div class="overflow-hidden">
                  <h6 class="mb-0 fw-bold text-truncate"><?= htmlspecialchars($current_user['name']) ?></h6>
                  <small class="text-muted text-truncate d-block"><?= htmlspecialchars($current_user['email'] ?? '') ?></small>
                  <span class="badge bg-light-primary text-primary mt-1 fs-1"><?= htmlspecialchars($current_user['role'] ?? 'Member') ?></span>
                </div>
              </div>

              <?php if (User::has_permission($_SESSION['user']['id'] ?? 0, 'manage_settings')): ?>
              <?php if (User::has_permission($_SESSION['user']['id'], 'manage_settings')): ?><a href="<?= url('?route=admin/settings') ?>" class="d-flex align-items-center gap-2 dropdown-item py-2 rounded-2">
                <i class="ti ti-settings fs-5 text-primary"></i>
                <p class="mb-0 fs-3">System Settings</p>
              </a><?php endif; ?>
              <?php endif; ?>

              <?php if (User::has_permission($_SESSION['user']['id'] ?? 0, 'manage_users')): ?>
              <?php if (User::has_permission($_SESSION['user']['id'], 'manage_users')): ?><a href="<?= url('?route=admin/users') ?>" class="d-flex align-items-center gap-2 dropdown-item py-2 rounded-2">
                <i class="ti ti-users fs-5 text-success"></i>
                <p class="mb-0 fs-3">Team Management</p>
              </a><?php endif; ?>
              <?php endif; ?>

              <div class="pt-2 border-top mt-2">
                <a href="javascript:void(0)" onclick="openLogoutModal(event)" class="btn btn-outline-danger btn-sm w-100 rounded-pill mt-1 d-flex align-items-center justify-content-center gap-1">
                  <i class="ti ti-logout fs-4"></i>
                  <span>Sign Out</span>
                </a>
              </div>
            </div>
          </div>
        </li>

      </ul>
    </div>
  </nav>
</header>

<script>
// Stock Notification Auto-Sync and Interactive Controls
(function() {
  function updateNotifBadges(counts) {
    const total = parseInt(counts.total) || 0;
    const badge = $('#headerNotifBadge');
    
    if (total > 0) {
      badge.text(total > 99 ? '99+' : total).removeClass('d-none');
    } else {
      badge.text('0').addClass('d-none');
    }
    
    $('#notifTotalCountText').text(total);
    $('#pillCountAll').text(total);
    $('#pillCountOut').text(counts.out_of_stock || 0);
    $('#pillCountLow').text(counts.low_stock || 0);
    $('#pillCountOver').text(counts.overstock || 0);

    if (total === 0) {
      $('#notifEmptyState').removeClass('d-none');
    }
  }

  // Segmented Pill Filtering
  $(document).on('click', '.notif-filter-pill', function(e) {
    e.stopPropagation();
    $('.notif-filter-pill').removeClass('active');
    $(this).addClass('active');

    const filter = $(this).data('filter');
    if (filter === 'all') {
      $('.notif-item-card').show();
    } else {
      $('.notif-item-card').each(function() {
        if ($(this).data('type') === filter) {
          $(this).show();
        } else {
          $(this).hide();
        }
      });
    }

    // Check visible cards
    if ($('.notif-item-card:visible').length === 0 && parseInt($('#pillCountAll').text()) > 0) {
      // no items in this filter
    }
  });

  // Mark Single Notification as Read / Resolved
  $(document).on('click', '.notif-dismiss-btn', function(e) {
    e.preventDefault();
    e.stopPropagation();
    const btn = $(this);
    const id = btn.data('id');
    const card = btn.closest('.notif-item-card');

    card.css('opacity', '0.4');
    $.post('ajax/notification_action.php', { action: 'mark_read', id: id }, function(res) {
      if (res.success && res.counts) {
        card.slideUp(180, function() { $(this).remove(); });
        updateNotifBadges(res.counts);
      } else {
        card.css('opacity', '1');
      }
    }, 'json').fail(function() {
      card.css('opacity', '1');
    });
  });

  // Mark All Notifications as Read
  $(document).on('click', '#btnMarkAllNotifsRead', function(e) {
    e.preventDefault();
    e.stopPropagation();
    
    $.post('ajax/notification_action.php', { action: 'mark_all_read' }, function(res) {
      if (res.success && res.counts) {
        $('.notif-item-card').slideUp(180, function() { $(this).remove(); });
        $('#notifEmptyState').removeClass('d-none');
        updateNotifBadges(res.counts);
      }
    }, 'json');
  });

  // Refresh notifications when opening dropdown
  $('#dropNotifications').on('show.bs.dropdown', function() {
    $.getJSON('ajax/notification_action.php?action=get_alerts', function(res) {
      if (res.success && res.counts) {
        updateNotifBadges(res.counts);
      }
    });
  });

  // Periodic background check every 60s
  setInterval(function() {
    $.getJSON('ajax/notification_action.php?action=get_alerts', function(res) {
      if (res.success && res.counts) {
        updateNotifBadges(res.counts);
      }
    });
  }, 60000);
})();
</script>
