<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $title ?? 'Admin Dashboard - Stacey Halo Halo' ?></title>
  <meta name="csrf-token" content="<?= csrf_token() ?>">

  <!-- Reusable AuroraTabs Links (CSS, Fonts, Icons) -->
  <?php include __DIR__ . '/../components/admin/links.php'; ?>
</head>

<body>
  <!--  Body Wrapper -->
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <!-- Reusable AuroraTabs Sidebar -->
    <?php include __DIR__ . '/../components/admin/sidebar.php'; ?>

    <!--  Main wrapper -->
    <div class="body-wrapper">
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          
          <!-- Reusable AuroraTabs Header -->
          <?php include __DIR__ . '/../components/admin/header.php'; ?>

          <!-- Main Page Content -->
          <div class="page-content-area pb-4 pt-1">
            <div class="admin-skeleton" aria-hidden="true">
              <div class="admin-skeleton-bar admin-skeleton-heading"></div><div class="admin-skeleton-bar admin-skeleton-subtitle"></div>
              <div class="admin-skeleton-cards"><?php for ($i = 0; $i < 3; $i++): ?><div class="admin-skeleton-card"><div class="admin-skeleton-bar"></div><div class="admin-skeleton-bar"></div></div><?php endfor; ?></div>
              <?php for ($i = 0; $i < 5; $i++): ?><div class="admin-skeleton-row"><div class="admin-skeleton-bar"></div><div class="admin-skeleton-bar"></div><div class="admin-skeleton-bar"></div></div><?php endfor; ?>
            </div>
            <?= $content ?? '' ?>
          </div>

          <!-- Reusable AuroraTabs Footer -->
          <?php include __DIR__ . '/../components/admin/footer.php'; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Reusable AuroraTabs Scripts (JS, Modals, Toast) -->
  <?php include __DIR__ . '/../components/admin/scripts.php'; ?>
</body>

</html>