<?php
// Sidebar Component - AuroraTabs Modernize Style for POS
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../models/User.php';
require_once __DIR__ . '/../../../models/Setting.php';

$user_id = $_SESSION['user']['id'] ?? 0;
$route = $_GET['route'] ?? 'dashboard';

// Fetch store settings
$sidebar_settings = Setting::get_by_category('general');
$sidebar_store_name = $sidebar_settings['store_name'] ?? 'Stacey Halo Halo';
$sidebar_logo = $sidebar_settings['store_logo'] ?? 'assets/img/logo.png';

// Permissions
$can_view_dashboard = User::has_permission($user_id, 'view_dashboard');
$can_view_reports   = User::has_permission($user_id, 'view_reports');
$can_access_pos     = User::has_permission($user_id, 'access_pos');
$can_manage_inv     = User::has_permission($user_id, 'manage_inventory');
$can_view_product   = User::has_permission($user_id, 'view_product');
$can_edit_product   = User::has_permission($user_id, 'edit_product');
$can_manage_users   = User::has_permission($user_id, 'manage_users');
$can_manage_roles   = User::has_permission($user_id, 'manage_roles');
$can_manage_settings= User::has_permission($user_id, 'manage_settings');
$can_manage_branches = User::has_permission($user_id, 'manage_branches') && User::has_all_branch_access($user_id);
$can_manage_expenses = User::has_permission($user_id, 'manage_expenses');
$can_view_logs = User::has_permission($user_id, 'view_activity_logs');
$can_manage_website = User::has_permission($user_id, 'access_website_admin');
?>

<aside class="left-sidebar">
  <div>
    <div class="brand-logo d-flex align-items-center justify-content-between px-4 py-3">
      <a href="<?= url($can_view_dashboard ? '?route=dashboard' : '?route=home') ?>" class="logo-wrap d-flex align-items-center gap-2 text-decoration-none">
        <img 
          src="<?= url($sidebar_logo) ?>" 
          alt="<?= htmlspecialchars($sidebar_store_name) ?>"
          class="logo-img"
          onerror="this.src='<?= url('assets/admin/images/logos/favicon.png') ?>'"
          style="max-height: 40px; width: auto; object-fit: contain;"
        />
        <div class="d-flex flex-column">
          <span class="sitename text-primary fw-bolder fs-4" style="line-height: 1.1;"><?= htmlspecialchars($sidebar_store_name) ?></span>
          <small class="text-muted fw-semibold" style="font-size: 11px;">Management Portal</small>
        </div>
      </a>

      <div class="close-btn d-xl-none d-block sidebartoggler cursor-pointer" id="sidebarCollapse">
        <i class="ti ti-x fs-8"></i>
      </div>
    </div>

    <!-- Sidebar navigation-->
    <nav class="sidebar-nav scroll-sidebar" data-simplebar="">
      <ul id="sidebarnav">

        <!-- OVERVIEW -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">OVERVIEW</span>
        </li>
        
        <?php if ($can_view_dashboard): ?>
        <li class="sidebar-item <?= $route === 'dashboard' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'dashboard' ? 'active' : '' ?>" href="<?= url('?route=dashboard') ?>" aria-expanded="false">
            <iconify-icon icon="solar:widget-add-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Dashboard</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($can_view_reports): ?>
        <li class="sidebar-item <?= $route === 'admin/reports' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'admin/reports' ? 'active' : '' ?>" href="<?= url('?route=admin/reports') ?>" aria-expanded="false">
            <iconify-icon icon="solar:chart-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Performance Stats</span>
          </a>
        </li>
        <?php endif; ?>

        <!-- STORE OPERATIONS -->
        <?php if ($can_access_pos): ?>
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">STORE OPERATIONS</span>
        </li>

        <li class="sidebar-item <?= $route === 'pos' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'pos' ? 'active' : '' ?>" href="<?= url('?route=pos') ?>" aria-expanded="false">
            <iconify-icon icon="solar:calculator-minimalistic-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">POS Cashier</span>
            <span class="badge bg-success ms-auto fs-1">Live</span>
          </a>
        </li>

        <li class="sidebar-item <?= $route === 'pos/history' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'pos/history' ? 'active' : '' ?>" href="<?= url('?route=pos/history') ?>" aria-expanded="false">
            <iconify-icon icon="solar:history-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Order History</span>
          </a>
        </li>

        <?php endif; ?>

        <?php if(User::has_permission($user_id,'manage_online_orders')): ?>
        <li class="sidebar-item"><a class="sidebar-link <?= $route === 'admin/online_orders' ? 'active' : '' ?>" href="<?= url('?route=admin/online_orders') ?>"><iconify-icon icon="solar:bag-4-line-duotone" class="fs-6"></iconify-icon><span class="hide-menu">Online Orders</span></a></li>
        <?php endif; ?>
        <!-- INVENTORY & FINANCE -->
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">INVENTORY &amp; FINANCE</span>
        </li>

        <?php if ($can_view_product): ?>
        <li class="sidebar-item <?= $route === 'inventory/products' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'inventory/products' ? 'active' : '' ?>" href="<?= url('?route=inventory/products') ?>" aria-expanded="false">
            <iconify-icon icon="solar:box-minimalistic-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Stock &amp; Inventory</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($can_edit_product): ?>
        <li class="sidebar-item <?= $route === 'inventory/categories' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'inventory/categories' ? 'active' : '' ?>" href="<?= url('?route=inventory/categories') ?>" aria-expanded="false">
            <iconify-icon icon="solar:tag-horizontal-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Categories</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($can_manage_inv): ?>
        <li class="sidebar-item <?= $route === 'inventory/logs' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'inventory/logs' ? 'active' : '' ?>" href="<?= url('?route=inventory/logs') ?>" aria-expanded="false">
            <iconify-icon icon="solar:document-text-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Inventory Logs</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($can_manage_branches): ?>
        <li class="sidebar-item <?= $route === 'admin/branches' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'admin/branches' ? 'active' : '' ?>" href="<?= url('?route=admin/branches') ?>" aria-expanded="false">
            <iconify-icon icon="solar:shop-2-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Business Branches</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($can_manage_expenses): ?>
        <li class="sidebar-item <?= $route === 'admin/expenses' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'admin/expenses' ? 'active' : '' ?>" href="<?= url('?route=admin/expenses') ?>" aria-expanded="false">
            <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Financial Ledger</span>
          </a>
        </li>
        <?php endif; ?>

        <?php if ($can_manage_website): ?><li class="sidebar-item"><a class="sidebar-link" href="<?= url('?route=admin/cms') ?>"><iconify-icon icon="solar:global-linear" class="fs-6"></iconify-icon><span class="hide-menu">Website Content</span></a></li><?php endif; ?>
        <!-- ADMINISTRATION & HR -->
        <?php if ($can_manage_users || $can_manage_roles || $can_view_logs): ?>
        <li class="nav-small-cap">
          <iconify-icon icon="solar:menu-dots-linear" class="nav-small-cap-icon fs-4"></iconify-icon>
          <span class="hide-menu">ADMINISTRATION</span>
        </li>

        <?php 
          $hr_active = in_array($route, ['admin/users', 'admin/roles', 'admin/activity_logs']);
        ?>
        <li class="sidebar-item <?= $hr_active ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg has-arrow <?= $hr_active ? 'active' : '' ?>" href="javascript:void(0)" aria-expanded="<?= $hr_active ? 'true' : 'false' ?>">
            <iconify-icon icon="solar:users-group-two-rounded-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">Human Resources</span>
          </a>
          <ul aria-expanded="<?= $hr_active ? 'true' : 'false' ?>" class="collapse first-level <?= $hr_active ? 'in' : '' ?>">
            <?php if ($can_manage_users): ?>
            <li class="sidebar-item">
              <a href="<?= url('?route=admin/users') ?>" class="sidebar-link <?= $route === 'admin/users' ? 'active text-primary fw-bold' : '' ?>">
                <span class="icon-small"></span>Team Members
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_manage_roles): ?>
            <li class="sidebar-item">
              <a href="<?= url('?route=admin/roles') ?>" class="sidebar-link <?= $route === 'admin/roles' ? 'active text-primary fw-bold' : '' ?>">
                <span class="icon-small"></span>Roles &amp; Permissions
              </a>
            </li>
            <?php endif; ?>
            <?php if ($can_view_logs): ?>
            <li class="sidebar-item">
              <a href="<?= url('?route=admin/activity_logs') ?>" class="sidebar-link <?= $route === 'admin/activity_logs' ? 'active text-primary fw-bold' : '' ?>">
                <span class="icon-small"></span>Activity Logs
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </li>
        <?php endif; ?>

        <?php if ($can_manage_settings): ?>
        <li class="sidebar-item <?= $route === 'admin/settings' ? 'selected' : '' ?>">
          <a class="sidebar-link primary-hover-bg <?= $route === 'admin/settings' ? 'active' : '' ?>" href="<?= url('?route=admin/settings') ?>" aria-expanded="false">
            <iconify-icon icon="solar:settings-line-duotone" class="fs-6"></iconify-icon>
            <span class="hide-menu">System Settings</span>
          </a>
        </li>
        <?php endif; ?>

        <!-- LOGOUT -->
        <li class="sidebar-item mt-3 mb-4">
          <a class="sidebar-link primary-hover-bg text-danger" href="javascript:void(0)" onclick="openLogoutModal(event)" aria-expanded="false">
            <iconify-icon icon="solar:logout-2-line-duotone" class="fs-6 text-danger"></iconify-icon>
            <span class="hide-menu text-danger">Logout</span>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>
