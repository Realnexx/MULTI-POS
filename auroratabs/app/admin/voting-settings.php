<?php
    session_start();
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
  <title>Voting Settings - Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
    .method-card { border: 2px solid #f1f5f9; border-radius: 16px; padding: 16px 20px; cursor: pointer; transition: all 0.25s; user-select: none; }
    .method-card:hover { border-color: #6366f1; background: #f8faff; }
    .method-card.active { border-color: #6366f1; background: #eef2ff; }
    .method-logo { height: 28px; object-fit: contain; }
    .key-toggle { cursor: pointer; }
    .pm-badge { font-size: 0.7rem; padding: 2px 8px; border-radius: 50px; }
  </style>
</head>

<body>
  <div class="page-wrapper" id="main-wrapper" data-layout="vertical" data-navbarbg="skin6" data-sidebartype="full"
    data-sidebar-position="fixed" data-header-position="fixed">

    <?php include 'components/sidebar.php'; ?>

    <div class="body-wrapper">
      <div class="body-wrapper-inner">
        <div class="container-fluid">
          <?php include 'components/header.php'; ?>

          <div class="top-part card bg-light-info shadow-none position-relative overflow-hidden mb-4">
            <div class="card-body px-4 py-3">
              <div class="row align-items-center">
                <div class="col-9">
                  <h4 class="fw-semibold mb-8">Voting Settings</h4>
                  <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a class="text-muted text-decoration-none" href="index.php">Dashboard</a></li>
                      <li class="breadcrumb-item" aria-current="page">Voting Settings</li>
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

          <div class="row">

            <!-- ─── General Config ────────────────────────── -->
            <div class="col-lg-5">
              <div class="card">
                <div class="card-header bg-primary">
                  <h5 class="mb-0 text-white"><i class="ti ti-settings me-2"></i>General Configuration</h5>
                </div>
                <div class="card-body">
                  <div class="mb-3">
                    <label for="currencySymbol" class="form-label">Currency Symbol</label>
                    <select class="form-select" id="currencySymbol">
                      <option value="PHP" selected>PHP (₱)</option>
                      <option value="USD">USD ($)</option>
                      <option value="EUR">EUR (€)</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label for="votePrice" class="form-label">Price per Vote</label>
                    <div class="input-group">
                      <span class="input-group-text">₱</span>
                      <input type="number" class="form-control" id="votePrice" value="5.00" step="0.01">
                    </div>
                    <div class="form-text">Amount charged for a single vote unit.</div>
                  </div>
                  <div class="mb-3">
                    <label for="minVotes" class="form-label">Minimum Purchase</label>
                    <input type="number" class="form-control" id="minVotes" value="10">
                  </div>
                  <hr>
                  <h6 class="fw-semibold mb-3">Restrictions</h6>
                  <div class="form-check form-switch mb-3">
                    <input class="form-check-input" type="checkbox" role="switch" id="ipLimit">
                    <label class="form-check-label" for="ipLimit">Limit Votes by IP Address</label>
                  </div>
                  <div class="mb-3">
                    <label for="maxVotesIP" class="form-label">Max Votes per IP (Daily)</label>
                    <input type="number" class="form-control" id="maxVotesIP" value="1000" disabled>
                  </div>
                  <div class="d-flex justify-content-end mt-4">
                    <button type="button" class="btn btn-primary">Save Changes</button>
                  </div>
                </div>
              </div>
            </div>

            <!-- ─── PayMongo Settings ─────────────────────── -->
            <div class="col-lg-7">
              <div class="card border-0 shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between"
                     style="background: linear-gradient(135deg,#5865f2,#7c3aed); border-radius: 12px 12px 0 0;">
                  <div class="d-flex align-items-center gap-3">
                    <i class="ti ti-brand-stripe fs-4 text-white opacity-75"></i>
                    <h5 class="mb-0 text-white fw-bold">PayMongo Configuration</h5>
                  </div>
                  <div class="d-flex align-items-center gap-2">
                    <label class="form-check-label text-white fw-bold small" for="pmActive" id="pmActiveLabel">Disabled</label>
                    <div class="form-check form-switch mb-0">
                      <input class="form-check-input" type="checkbox" role="switch" id="pmActive"
                             style="width:2.5rem; height:1.3rem;">
                    </div>
                  </div>
                </div>
                <div class="card-body p-4">

                  <!-- API Keys -->
                  <div class="mb-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                      <h6 class="fw-bold mb-0">API Keys</h6>
                      <span class="badge bg-warning text-dark pm-badge">Server-side only</span>
                    </div>

                    <div class="mb-3">
                      <label class="form-label small fw-bold text-muted text-uppercase">Secret Key</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="pmSecretKey"
                               placeholder="sk_live_... or sk_test_...">
                        <button class="btn btn-outline-secondary" type="button" id="toggleSecret">
                          <i class="ti ti-eye"></i>
                        </button>
                      </div>
                      <div class="form-text">Never expose this in any frontend code.</div>
                    </div>

                    <div class="mb-4">
                      <label class="form-label small fw-bold text-muted text-uppercase">Public Key</label>
                      <div class="input-group">
                        <input type="text" class="form-control" id="pmPublicKey"
                               placeholder="pk_live_... or pk_test_...">
                        <button class="btn btn-outline-secondary" type="button" id="copyPublic"
                                title="Copy to clipboard">
                          <i class="ti ti-copy"></i>
                        </button>
                      </div>
                    </div>

                    <div class="d-flex align-items-center gap-3">
                      <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3"
                              id="btnFetchMethods">
                        <i class="ti ti-plug me-1"></i> Test Keys &amp; Fetch Methods
                      </button>
                      <span id="fetchStatus" class="small"></span>
                    </div>
                  </div>

                  <hr>

                  <!-- Payment Methods Grid -->
                  <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                      <h6 class="fw-bold mb-0">Enabled Payment Methods</h6>
                      <span class="small text-muted">Click cards to toggle</span>
                    </div>
                    <div class="row g-3" id="pmMethodsContainer">
                      <div class="col-12 text-center text-muted small py-4">
                        <i class="ti ti-plug-connected-x d-block fs-4 mb-2 opacity-50"></i>
                        Enter your secret key and click <strong>"Test Keys &amp; Fetch Methods"</strong>
                        to auto-detect available payment methods.
                      </div>
                    </div>
                  </div>

                  <hr>

                  <!-- ── Fee Pass-Through ──────────────────────────── -->
                  <div class="mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                      <div>
                        <h6 class="fw-bold mb-0">Fee Pass-Through</h6>
                        <small class="text-muted">Gross up price so you always receive the exact package amount</small>
                      </div>
                      <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" role="switch" id="pmFeePassthrough">
                      </div>
                    </div>

                    <div id="feeDetails" class="mt-3">
                      <div class="row g-3 align-items-start">
                        <div class="col-sm-4">
                          <label class="form-label small fw-bold text-muted text-uppercase">PayMongo Fee Rate</label>
                          <div class="input-group input-group-sm">
                            <input type="number" class="form-control" id="pmFeeRate"
                                   value="4" min="0" max="50" step="0.1">
                            <span class="input-group-text">%</span>
                          </div>
                        </div>
                        <div class="col-sm-8">
                          <label class="form-label small fw-bold text-muted text-uppercase">Live Formula Preview (per ₱1.00)</label>
                          <div class="d-flex align-items-center gap-2 bg-light rounded-3 p-3">
                            <div class="text-center flex-fill">
                              <div class="fw-bold text-muted">₱1.00</div>
                              <div class="text-muted" style="font-size:0.68rem;">Package price</div>
                            </div>
                            <i class="ti ti-arrow-right text-muted"></i>
                            <div class="text-center flex-fill">
                              <div class="fw-bold text-warning" id="previewCharge">₱1.05</div>
                              <div class="text-muted" style="font-size:0.68rem;">Buyer pays</div>
                            </div>
                            <i class="ti ti-arrow-right text-muted"></i>
                            <div class="text-center flex-fill">
                              <div class="fw-bold text-success">₱1.00</div>
                              <div class="text-muted" style="font-size:0.68rem;">You receive</div>
                            </div>
                          </div>
                          <div class="small text-muted mt-1" style="font-size:0.7rem;">
                            Formula: charge = net ÷ (1 − fee%) · rounded up to nearest centavo
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <hr>

                  <div class="d-flex align-items-center justify-content-between">
                    <small class="text-muted">
                      <i class="ti ti-shield-check me-1 text-success"></i>
                      Payments are processed on PayMongo's secure hosted page.
                    </small>
                    <button type="button" class="btn btn-primary px-4 rounded-pill" id="btnSavePmSettings">
                      <i class="ti ti-device-floppy me-1"></i> Save Settings
                    </button>
                  </div>
                </div>
              </div>

              <!-- Info note -->
              <div class="card bg-light-warning border-0 mt-3">
                <div class="card-body py-3">
                  <div class="d-flex align-items-start gap-3">
                    <i class="ti ti-info-circle fs-5 text-warning mt-1 flex-shrink-0"></i>
                    <p class="mb-0 small">
                      Use <strong>test keys</strong> (<code>sk_test_</code> / <code>pk_test_</code>)
                      while developing. Switch to <strong>live keys</strong> for production.
                      <a href="https://dashboard.paymongo.com" target="_blank"
                         class="fw-bold text-warning">Open PayMongo Dashboard →</a>
                    </p>
                  </div>
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
    // All known PayMongo payment methods with display metadata
    const PM_METHODS_META = {
      gcash:    { label: 'GCash',              icon: '📱', color: '#00a8e8' },
      card:     { label: 'Credit / Debit Card', icon: '💳', color: '#6366f1' },
      paymaya:  { label: 'Maya (PayMaya)',      icon: '🌐', color: '#27ae60' },
      grab_pay: { label: 'GrabPay',            icon: '🟢', color: '#00b14f' },
      dob:      { label: 'BancNet OB',          icon: '🏦', color: '#c0392b' },
      billease: { label: 'BillEase (BNPL)',     icon: '📋', color: '#ff6b35' },
      qrph:     { label: 'QR Ph',              icon: '🔳', color: '#333'    },
    };

    let enabledMethods = new Set();

    // Defined at script scope so both $(ready) and loadPmSettings() can call it
    function updateFeePreview() {
      const rate  = parseFloat($('#pmFeeRate').val()) || 0;
      const gross = rate > 0 ? Math.ceil((1 / (1 - rate / 100)) * 100) / 100 : 1.00;
      $('#previewCharge').text('₱' + gross.toFixed(2));
    }

    $(document).ready(function () {
      loadPmSettings();

      // IP limit toggle
      $('#ipLimit').change(function () {
        $('#maxVotesIP').prop('disabled', !this.checked);
      });

      // Enable/Disable toggle label
      $('#pmActive').change(function () {
        $('#pmActiveLabel').text(this.checked ? 'Enabled' : 'Disabled');
      });

      // Show/hide secret key
      $('#toggleSecret').click(function () {
        const inp = $('#pmSecretKey');
        const isPass = inp.attr('type') === 'password';
        inp.attr('type', isPass ? 'text' : 'password');
        $(this).find('i').toggleClass('ti-eye ti-eye-off');
      });

      // Copy public key
      $('#copyPublic').click(function () {
        const val = $('#pmPublicKey').val();
        if (!val) return;
        navigator.clipboard.writeText(val);
        $(this).find('i').removeClass('ti-copy').addClass('ti-check');
        setTimeout(() => $(this).find('i').removeClass('ti-check').addClass('ti-copy'), 1500);
      });

      // Wire fee rate input to live preview
      $('#pmFeeRate').on('input change', updateFeePreview);

      // Fetch available methods from PayMongo
      $('#btnFetchMethods').click(function () {
        const secret = $('#pmSecretKey').val().trim();
        if (!secret || secret.includes('*')) {
          $('#fetchStatus').html('<span class="text-danger"><i class="ti ti-x me-1"></i>Enter a valid secret key first.</span>');
          return;
        }
        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Testing...');
        $('#fetchStatus').html('');

        $.ajax({
          url: 'endpoints/voting_settings_handler.php',
          method: 'POST',
          dataType: 'json',
          data: { action: 'fetch_paymongo_methods', secret_key: secret },
          success: function (res) {
            if (res.status === 'success') {
              const methods = res.methods || [];
              $('#fetchStatus').html(`<span class="text-success"><i class="ti ti-check me-1"></i>${methods.length} method(s) available</span>`);
              renderMethods(methods);
            } else {
              $('#fetchStatus').html(`<span class="text-danger"><i class="ti ti-x me-1"></i>${res.message || 'Invalid key'}</span>`);
            }
          },
          error: function () {
            $('#fetchStatus').html('<span class="text-danger">Connection error.</span>');
          },
          complete: function () {
            btn.prop('disabled', false).html('<i class="ti ti-plug me-1"></i> Test Keys &amp; Fetch Methods');
          }
        });
      });

      // Save all PayMongo settings
      $('#btnSavePmSettings').click(function () {
        const btn = $(this);
        const orig = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
          url: 'endpoints/voting_settings_handler.php',
          method: 'POST',
          dataType: 'json',
          data: {
            action:          'save_paymongo_settings',
            active:          $('#pmActive').is(':checked') ? '1' : '0',
            secret_key:      $('#pmSecretKey').val().trim(),
            public_key:      $('#pmPublicKey').val().trim(),
            methods:         [...enabledMethods].join(','),
            fee_passthrough: $('#pmFeePassthrough').is(':checked') ? '1' : '0',
            fee_rate:        parseFloat($('#pmFeeRate').val()) || 4,
          },
          success: function (res) {
            if (res.status === 'success') {
              Swal.fire({ title: 'Saved!', text: res.message, icon: 'success', timer: 2000, showConfirmButton: false });
              loadPmSettings();
            } else {
              Swal.fire('Error', res.message, 'error');
            }
          },
          complete: function () { btn.prop('disabled', false).html(orig); }
        });
      });
    });

    function loadPmSettings() {
      $.ajax({
        url: 'endpoints/voting_settings_handler.php',
        method: 'POST',
        dataType: 'json',
        data: { action: 'get_paymongo_settings' },
        success: function (res) {
          if (res.status !== 'success') return;
          const d = res.data;
          $('#pmActive').prop('checked', d.active);
          $('#pmActiveLabel').text(d.active ? 'Enabled' : 'Disabled');
          if (d.secret_key) $('#pmSecretKey').val(d.secret_key);
          $('#pmPublicKey').val(d.public_key || '');
          enabledMethods = new Set(d.enabled_methods || []);
          // Restore fee pass-through
          $('#pmFeePassthrough').prop('checked', d.fee_passthrough || false);
          $('#pmFeeRate').val(d.fee_rate || 4);
          updateFeePreview();


          // Always render ALL known methods so admin can toggle without clicking Fetch
          renderMethods(Object.keys(PM_METHODS_META));
        }
      });
    }

    function renderMethods(methods) {
      const container = $('#pmMethodsContainer');
      container.empty();

      if (!methods || !methods.length) {
        container.html('<div class="col-12 text-center text-muted small py-3">No methods returned.</div>');
        return;
      }

      methods.forEach(function (m) {
        const meta  = PM_METHODS_META[m] || { label: m.toUpperCase(), icon: '💰', color: '#6366f1' };
        const isOn  = enabledMethods.has(m);

        const col = $(`
          <div class="col-md-4">
            <div class="method-card d-flex align-items-center gap-3 ${isOn ? 'active' : ''}"
                 data-method="${m}" role="button">
              <span class="fs-4">${meta.icon}</span>
              <div class="flex-grow-1 overflow-hidden">
                <div class="fw-bold small text-truncate">${meta.label}</div>
                <div class="small text-muted">${m}</div>
              </div>
              <div class="form-check form-switch mb-0">
                <input class="form-check-input method-chk" type="checkbox" role="switch"
                       ${isOn ? 'checked' : ''} style="pointer-events:none;">
              </div>
            </div>
          </div>
        `);

        col.find('.method-card').on('click', function () {
          const method = $(this).data('method');
          const chk    = $(this).find('.method-chk');
          const nowOn  = !chk.is(':checked');
          chk.prop('checked', nowOn);
          $(this).toggleClass('active', nowOn);
          nowOn ? enabledMethods.add(method) : enabledMethods.delete(method);
        });

        container.append(col);
      });
    }
  </script>
</body>
</html>
