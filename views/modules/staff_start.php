<?php
$title = 'Staff Home';
ob_start();
?>
<section class="card border-0 shadow-sm"><div class="card-body p-4">
<p class="text-primary fw-semibold mb-2">STAFF WORKSPACE</p><h2 class="mb-2">Welcome, <?= htmlspecialchars($_SESSION['user']['name']) ?></h2>
<p class="text-muted">Your branch: <strong><?= htmlspecialchars($context['branch']) ?></strong></p>
<?php if (!$context['global'] && !$context['branch_id']): ?>
<div class="alert alert-primary">Ask an administrator to assign your branch before you start working.</div>
<?php else: ?>
<p class="text-muted">Choose an available page to get started.</p><div class="d-flex flex-wrap gap-2">
<?php foreach (['access_pos'=>['pos','Open POS'],'view_product'=>['inventory/products','Products'],'manage_expenses'=>['admin/expenses','Expenses'],'view_reports'=>['admin/reports','Reports'],'manage_users'=>['admin/users','Users'],'manage_roles'=>['admin/roles','Roles']] as $permission=>$link): if (User::has_permission($_SESSION['user']['id'], $permission)): ?>
<a class="btn btn-outline-primary" href="<?= url('?route='.$link[0]) ?>"><?= $link[1] ?></a>
<?php endif; endforeach; ?></div>
<?php endif; ?>
</div></section>
<?php $content = ob_get_clean(); require __DIR__ . '/../layouts/admin.php'; ?>
