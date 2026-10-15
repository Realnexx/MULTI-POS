<!-- Reusable Admin Scripts Component (AuroraTabs Modernize Theme) -->
<!-- NOTE: jQuery is loaded in <head> via links.php to support inline view scripts -->
<script src="<?= url('assets/admin/libs/bootstrap/dist/js/bootstrap.bundle.min.js') ?>"></script>
<script src="<?= url('assets/admin/js/sidebarmenu.js') ?>"></script>
<script src="<?= url('assets/admin/js/app.min.js') ?>"></script>
<script src="<?= url('assets/admin/libs/apexcharts/dist/apexcharts.min.js') ?>"></script>
<script src="<?= url('assets/admin/libs/simplebar/dist/simplebar.js') ?>"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="<?= url('assets/css/admin-pagination.css') ?>">
<script src="<?= url('assets/js/admin-pagination.js') ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
<script src="<?= url('assets/admin/js/custom.js') ?>"></script>

<!-- Logout Confirmation Modal (AuroraTabs Style) -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow">
      <div class="modal-body p-4 text-center">
        <div class="text-danger mb-3">
          <i class="ti ti-logout fs-10" style="font-size: 3rem;"></i>
        </div>
        <h4 class="fw-bold mb-2">Logout Confirmation</h4>
        <p class="text-muted mb-4 fs-3">Are you sure you want to sign out of your account? Any unsaved changes might be lost.</p>
        <div class="d-flex gap-3 justify-content-center">
          <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Stay Logged In</button>
          <a href="<?= url('?route=logout') ?>" class="btn btn-danger rounded-pill px-4">Yes, Logout</a>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Global Toast Notification Container -->
<div id="aurora-toast-stack" style="
  position: fixed;
  top: 20px;
  right: 20px;
  z-index: 99999;
  display: flex;
  flex-direction: column;
  gap: 10px;
  pointer-events: none;
  max-width: 360px;
  width: calc(100vw - 40px);
"></div>

<style>
  /* ── Aurora Premium Toast ───────────────────────────── */
  .aurora-toast {
    position: relative;
    display: flex;
    align-items: flex-start;
    gap: 12px;
    background: #ffffff;
    border-radius: 16px;
    padding: 14px 16px 14px 14px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.10), 0 2px 8px rgba(0,0,0,0.06);
    border: 1px solid #e8edf5;
    pointer-events: auto;
    overflow: hidden;
    /* start state */
    opacity: 0;
    transform: translateX(30px) scale(0.97);
    transition: opacity 0.32s cubic-bezier(0.16,1,0.3,1),
                transform 0.32s cubic-bezier(0.16,1,0.3,1);
  }
  .aurora-toast.aurora-toast-in {
    opacity: 1;
    transform: translateX(0) scale(1);
  }
  .aurora-toast.aurora-toast-out {
    opacity: 0;
    transform: translateX(30px) scale(0.97);
  }

  /* Left accent bar */
  .aurora-toast-bar {
    position: absolute;
    left: 0; top: 0; bottom: 0;
    width: 4px;
    border-radius: 16px 0 0 16px;
  }

  /* Icon badge */
  .aurora-toast-icon {
    width: 38px; height: 38px;
    border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    font-size: 1.05rem;
    margin-left: 4px;
  }

  /* Text */
  .aurora-toast-title {
    font-size: 0.82rem;
    font-weight: 800;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
    font-family: 'Outfit', 'Plus Jakarta Sans', sans-serif;
  }
  .aurora-toast-msg {
    font-size: 0.80rem;
    color: #5a6a85;
    line-height: 1.45;
    margin: 0;
    font-weight: 500;
  }

  /* Close button */
  .aurora-toast-close {
    position: absolute;
    top: 10px; right: 10px;
    background: none; border: none;
    color: #9eaac7; cursor: pointer;
    font-size: 0.9rem; padding: 2px;
    line-height: 1;
    transition: color 0.15s;
  }
  .aurora-toast-close:hover { color: #2a3547; }

  /* Progress bar */
  .aurora-toast-progress {
    position: absolute;
    bottom: 0; left: 0;
    height: 3px;
    border-radius: 0 0 16px 16px;
    animation: aurora-toast-shrink linear forwards;
  }
  @keyframes aurora-toast-shrink {
    from { width: 100%; }
    to   { width: 0%; }
  }

  /* ── Type themes ── */
  /* success */
  .aurora-toast-success .aurora-toast-bar      { background: #13deb9; }
  .aurora-toast-success .aurora-toast-icon     { background: #e8fdf5; color: #0fb68c; }
  .aurora-toast-success .aurora-toast-title    { color: #0a5c47; }
  .aurora-toast-success .aurora-toast-progress { background: #13deb9; }

  /* danger / error */
  .aurora-toast-danger .aurora-toast-bar       { background: #fa5454; }
  .aurora-toast-danger .aurora-toast-icon      { background: #fff0f0; color: #e03131; }
  .aurora-toast-danger .aurora-toast-title     { color: #7f1d1d; }
  .aurora-toast-danger .aurora-toast-progress  { background: #fa5454; }

  /* warning */
  .aurora-toast-warning .aurora-toast-bar      { background: #ffae1f; }
  .aurora-toast-warning .aurora-toast-icon     { background: #fff8e6; color: #b45309; }
  .aurora-toast-warning .aurora-toast-title    { color: #78350f; }
  .aurora-toast-warning .aurora-toast-progress { background: #ffae1f; }

  /* info */
  .aurora-toast-info .aurora-toast-bar         { background: #5d87ff; }
  .aurora-toast-info .aurora-toast-icon        { background: #eef2ff; color: #3b5fe0; }
  .aurora-toast-info .aurora-toast-title       { color: #1e3a8a; }
  .aurora-toast-info .aurora-toast-progress    { background: #5d87ff; }
</style>

<script>
  // ── Aurora Premium Toast System ─────────────────────────────
  const AURORA_TOAST_DURATION = 4500; // ms

  const AURORA_TOAST_CONFIG = {
    success: { title: 'Success',     icon: 'fa-circle-check',     fa: true },
    danger:  { title: 'Error',       icon: 'fa-circle-xmark',     fa: true },
    error:   { title: 'Error',       icon: 'fa-circle-xmark',     fa: true },
    warning: { title: 'Warning',     icon: 'fa-triangle-exclamation', fa: true },
    info:    { title: 'Information', icon: 'fa-circle-info',      fa: true },
  };

  function showToast(message, type = 'success', title = null) {
    const normalType = (type === 'error') ? 'danger' : type;
    const cfg = AURORA_TOAST_CONFIG[type] || AURORA_TOAST_CONFIG.info;
    const stack = document.getElementById('aurora-toast-stack');
    if (!stack) return;

    const toast = document.createElement('div');
    toast.className = `aurora-toast aurora-toast-${normalType}`;

    const iconClass = cfg.fa ? `fa-solid ${cfg.icon}` : cfg.icon;
    const displayTitle = title || cfg.title;

    toast.innerHTML = `
      <div class="aurora-toast-bar"></div>
      <div class="aurora-toast-icon">
        <i class="${iconClass}"></i>
      </div>
      <div style="flex:1;min-width:0;padding-right:20px;">
        <div class="aurora-toast-title"></div>
        <p class="aurora-toast-msg"></p>
      </div>
      <button class="aurora-toast-close" onclick="dismissAuroraToast(this.closest('.aurora-toast'))">
        <i class="fa-solid fa-xmark"></i>
      </button>
      <div class="aurora-toast-progress" style="animation-duration:${AURORA_TOAST_DURATION}ms;"></div>
    `;

    toast.querySelector('.aurora-toast-title').textContent = displayTitle;
    toast.querySelector('.aurora-toast-msg').textContent = message;
    stack.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => {
      requestAnimationFrame(() => toast.classList.add('aurora-toast-in'));
    });

    // Auto dismiss
    setTimeout(() => dismissAuroraToast(toast), AURORA_TOAST_DURATION);
  }

  function dismissAuroraToast(toast) {
    if (!toast || toast.dataset.dismissed) return;
    toast.dataset.dismissed = '1';
    toast.classList.remove('aurora-toast-in');
    toast.classList.add('aurora-toast-out');
    setTimeout(() => toast.remove(), 350);
  }

  // Backwards-compat aliases
  window.showToast = showToast;

  // Handle PHP flash messages
  <?php
  if (function_exists('get_flash')) {
      $flash = get_flash();
      if ($flash):
  ?>
  document.addEventListener("DOMContentLoaded", function() {
    showToast("<?= addslashes(htmlspecialchars($flash['message'])) ?>", "<?= $flash['type'] ?>");
  });
  <?php
      endif;
  }
  ?>

  // Universal openLogoutModal compatibility function
  function openLogoutModal(event) {
    if (event) event.preventDefault();
    const logoutModalEl = document.getElementById('logoutModal');
    if (logoutModalEl && window.bootstrap) {
      const modal = bootstrap.Modal.getOrCreateInstance(logoutModalEl);
      modal.show();
    } else {
      window.location.href = "<?= url('?route=logout') ?>";
    }
  }
</script>

<script src="<?= url('assets/js/modal-forms.js') ?>?v=<?= filemtime(__DIR__ . '/../../../assets/js/modal-forms.js') ?>"></script>

