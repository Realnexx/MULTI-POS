<?php
$title = 'Staff Home';
$staffPages = [
    'view_dashboard' => ['dashboard', 'Dashboard', 'View sales, stock and daily operations.', 'space_dashboard'],
    'access_pos' => ['pos', 'Open POS', 'Start an order and serve your next customer.', 'point_of_sale'],
    'view_product' => ['inventory/products', 'Products', 'Browse products and check availability.', 'inventory_2'],
    'manage_expenses' => ['admin/expenses', 'Expenses', 'Record and review branch expenses.', 'receipt_long'],
    'view_reports' => ['admin/reports', 'Reports', 'Review sales and business performance.', 'bar_chart'],
    'manage_users' => ['admin/users', 'Users', 'Manage your team and staff accounts.', 'group'],
    'manage_roles' => ['admin/roles', 'Roles', 'Manage roles and access permissions.', 'admin_panel_settings'],
];
ob_start();
?>
<link rel="stylesheet" href="<?= url('assets/css/staff-home.css') ?>?v=1">
<section class="staff-home" aria-labelledby="staff-welcome">
    <div class="staff-hero">
        <div class="staff-hero-copy">
            <p class="staff-eyebrow"><span></span>Staff workspace</p>
            <h1 id="staff-welcome">Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?><span class="staff-heading-dot">.</span></h1>
            <p class="staff-intro">Your workspace for a smooth day at the counter.</p>
            <div class="staff-branch">
                <span class="material-symbols-outlined" aria-hidden="true">storefront</span>
                <span><span class="staff-branch-label">Your branch</span><strong><?= htmlspecialchars($context['branch']) ?></strong></span>
            </div>
        </div>
        <div class="staff-hero-art" aria-hidden="true">
            <div class="staff-art-ring"></div>
            <span class="material-symbols-outlined">storefront</span>
            <div class="staff-art-caption">Ready for the day</div>
        </div>
    </div>
    <?php if (!$context['global'] && !$context['branch_id']): ?>
        <div class="staff-assignment" role="status">
            <span class="material-symbols-outlined" aria-hidden="true">info</span>
            <div><strong>Let's set up your branch</strong><p>Ask an administrator to assign your branch before you start working.</p></div>
        </div>
    <?php else: ?>
        <div class="staff-section-heading"><div><h2>Get started</h2><p>Choose where you'd like to work.</p></div><span class="staff-section-label">Quick access</span></div>
        <div class="staff-page-grid">
            <?php $staffPageCount = 0; foreach ($staffPages as $permission => $link): if (User::has_permission($_SESSION['user']['id'], $permission)): $staffPageCount++; ?>
                <a class="staff-page-card <?= $permission === 'access_pos' ? 'staff-page-primary' : '' ?>" href="<?= url('?route='.$link[0]) ?>">
                    <span class="staff-page-icon material-symbols-outlined" aria-hidden="true"><?= $link[3] ?></span>
                    <span class="staff-page-copy"><strong><?= $link[1] ?></strong><span><?= $link[2] ?></span></span>
                    <span class="staff-page-arrow material-symbols-outlined" aria-hidden="true">arrow_forward</span>
                </a>
            <?php endif; endforeach; ?>
        </div>
        <?php if (!$staffPageCount): ?><p class="staff-assignment">No pages are available yet. Ask an administrator to review your access.</p><?php endif; ?>
    <?php endif; ?>
</section>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin.php'; ?>
