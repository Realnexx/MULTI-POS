<!-- Reusable Admin Links Component (AuroraTabs Modernize Theme) -->
<?php require __DIR__.'/../../partials/site_favicon.php'; ?>
<link rel="stylesheet" href="<?= url('assets/admin/css/styles.min.css') ?>" />
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" />
<link rel="stylesheet" href="<?= url('assets/admin/css/custom.css') ?>" />
<!-- FontAwesome Icons for POS views compatibility -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" />
<!-- Material Symbols for icon compatibility -->
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet" />
<!-- Google Fonts: Plus Jakarta Sans / Inter -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<!-- jQuery in head for inline view scripts compatibility -->
<script src="<?= url('assets/admin/libs/jquery/dist/jquery.min.js') ?>"></script>
<script src="<?= url('assets/js/request-security.js') ?>"></script>
<link rel="stylesheet" href="<?= url('assets/css/admin-loading.css') ?>">
<script src="<?= url('assets/js/admin-loading.js') ?>"></script>

<style>
  body {
    font-family: 'Plus Jakarta Sans', sans-serif !important;
  }
  /* Global Layout Spacing - Eliminate excessive top spacing */
  .body-wrapper .container-fluid,
  .body-wrapper .body-wrapper-inner .container-fluid,
  #main-wrapper .body-wrapper .container-fluid,
  #main-wrapper[data-layout=vertical] .body-wrapper .container-fluid,
  #main-wrapper[data-layout=vertical][data-header-position=fixed] .body-wrapper .container-fluid,
  #main-wrapper[data-layout=vertical][data-header-position=fixed] .body-wrapper > .container-fluid {
    padding-top: 14px !important;
  }

  /* Sleek Floating Admin Header */
  .app-header {
    background: #ffffff !important;
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    border: 1px solid #eef2f6 !important;
    border-radius: 16px !important;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02) !important;
    margin-top: 0 !important;
    margin-bottom: 8px !important;
    position: sticky !important;
    top: 14px !important;
    z-index: 1025 !important;
    padding: 0 8px !important;
  }
  .page-content-area {
    padding-top: 0 !important;
  }
  .his-page-header,
  .rpt-page-header {
    margin-top: 0.25rem !important;
  }
  .app-header .navbar {
    min-height: 64px !important;
    padding: 6px 8px !important;
    display: flex !important;
    align-items: center !important;
  }
  .app-header .navbar .navbar-nav .nav-item .nav-link {
    height: auto !important;
    line-height: normal !important;
    padding: 6px 10px !important;
    display: flex !important;
    align-items: center !important;
    font-size: 1rem !important;
  }

  /* Header User Profile Pill Button */
  .header-user-btn {
    display: flex !important;
    align-items: center !important;
    gap: 9px !important;
    padding: 4px 12px 4px 5px !important;
    border-radius: 9999px !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
    text-decoration: none !important;
    line-height: normal !important;
  }
  .header-user-btn:hover,
  .header-user-btn[aria-expanded="true"] {
    background: #f1f5f9 !important;
    border-color: #cbd5e1 !important;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
  }
  .header-user-avatar {
    width: 36px !important;
    height: 36px !important;
    border-radius: 50% !important;
    object-fit: cover !important;
    border: 2px solid #5d87ff !important;
    flex-shrink: 0 !important;
  }
  .header-user-meta {
    display: flex !important;
    flex-direction: column !important;
    justify-content: center !important;
    text-align: left !important;
    line-height: 1.15 !important;
    padding-right: 2px !important;
  }
  .header-user-name {
    font-size: 0.8125rem !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    letter-spacing: -0.2px !important;
    margin: 0 !important;
    line-height: 1.2 !important;
    white-space: nowrap !important;
  }
  .header-user-role {
    font-size: 0.6875rem !important;
    font-weight: 600 !important;
    color: #64748b !important;
    margin: 0 !important;
    line-height: 1.1 !important;
    white-space: nowrap !important;
  }

  /* Header Circular Icon Button (Notifications, toggler) */
  .header-icon-btn {
    width: 38px !important;
    height: 38px !important;
    border-radius: 50% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    position: relative !important;
    background: #f8fafc !important;
    border: 1px solid #e2e8f0 !important;
    color: #64748b !important;
    transition: all 0.2s ease !important;
    text-decoration: none !important;
    padding: 0 !important;
  }
  .header-icon-btn:hover,
  .header-icon-btn[aria-expanded="true"] {
    background: #f1f5f9 !important;
    color: #5d87ff !important;
    border-color: #cbd5e1 !important;
  }
  .header-icon-btn .notification-dot {
    position: absolute !important;
    top: 7px !important;
    right: 8px !important;
    width: 8px !important;
    height: 8px !important;
    background-color: #5d87ff !important;
    border: 2px solid #ffffff !important;
    border-radius: 50% !important;
  }

  /* Header Action Pill Buttons */
  .header-action-btn {
    height: 36px !important;
    display: inline-flex !important;
    align-items: center !important;
    gap: 6px !important;
    padding: 0 14px !important;
    border-radius: 9999px !important;
    font-size: 0.78125rem !important;
    font-weight: 600 !important;
    transition: all 0.2s ease !important;
  }
  .brand-logo a {
    text-decoration: none;
  }
  .logo-img {
    max-height: 45px;
    width: auto;
    object-fit: contain;
  }
  .sitename {
    font-weight: 800;
    font-size: 1.15rem;
    color: #2a3547;
    letter-spacing: -0.5px;
  }
  .sidebar-nav ul .sidebar-item .sidebar-link {
    transition: all 0.2s ease-in-out;
  }
  .sidebar-nav ul .sidebar-item.selected > .sidebar-link,
  .sidebar-nav ul .sidebar-item > .sidebar-link.active {
    background-color: #5d87ff !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(93, 135, 255, 0.35);
  }
  .sidebar-nav ul .sidebar-item.selected > .sidebar-link iconify-icon,
  .sidebar-nav ul .sidebar-item > .sidebar-link.active iconify-icon,
  .sidebar-nav ul .sidebar-item.selected > .sidebar-link i,
  .sidebar-nav ul .sidebar-item > .sidebar-link.active i {
    color: #ffffff !important;
  }
  .sidebar-nav .nav-small-cap {
    font-size: 0.75rem;
    font-weight: 700;
    letter-spacing: 0.8px;
    color: #7c8fac;
  }
  /* Reusable badge styles */
  .badge-soft-primary { background-color: rgba(93, 135, 255, 0.12); color: #5d87ff; }
  .badge-soft-success { background-color: rgba(19, 222, 185, 0.12); color: #13deb9; }
  .badge-soft-warning { background-color: rgba(255, 174, 31, 0.12); color: #ffae1f; }
  .badge-soft-danger  { background-color: rgba(250, 137, 107, 0.12); color: #fa896b; }
</style>

<link rel="stylesheet" href="<?= url('assets/css/print-actions.css') ?>">

<link rel="stylesheet" href="<?= url('assets/css/admin-sidebar.css') ?>?v=<?= filemtime(__DIR__.'/../../../assets/css/admin-sidebar.css') ?>">
