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
    <title>Security Settings - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .security-card {
            border-top: 4px solid #5d87ff;
        }
        .security-card.warning {
            border-top-color: #ffae1f;
        }
        .security-card.danger {
            border-top-color: #fa896b;
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

                    <div class="top-part card bg-light-danger shadow-none position-relative overflow-hidden mb-4">
                        <div class="card-body px-4 py-3">
                            <div class="row align-items-center">
                                <div class="col-9">
                                    <h4 class="fw-semibold mb-8">System Security Policies</h4>
                                    <nav aria-label="breadcrumb">
                                        <ol class="breadcrumb">
                                            <li class="breadcrumb-item">
                                                <a class="text-muted text-decoration-none" href="index.php">Dashboard</a>
                                            </li>
                                            <li class="breadcrumb-item" aria-current="page">Security Settings</li>
                                        </ol>
                                    </nav>
                                </div>
                                <div class="col-3">
                                    <div class="text-center mb-n5">
                                        <img src="src/assets/images/backgrounds/rocket.png" alt="" class="img-fluid mb-n4" style="filter: hue-rotate(320deg);" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="securitySettingsForm">
                        <div class="row">
                            <!-- Password Policy -->
                            <div class="col-md-6 mb-4">
                                <div class="card security-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="bg-light-primary text-primary p-2 rounded-circle me-3">
                                                <i class="ti ti-lock fs-6"></i>
                                            </span>
                                            <h5 class="card-title fw-semibold mb-0">Password Policy</h5>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Minimum Password Length</label>
                                            <input type="number" class="form-control" name="min_password_length" id="min_password_length" value="8">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password Complexity</label>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="require_special_chars" id="require_special_chars" value="1" checked>
                                                <label class="form-check-label" for="require_special_chars">Require special characters (!@#$%^&*)</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="require_numbers" id="require_numbers" value="1" checked>
                                                <label class="form-check-label" for="require_numbers">Require numbers</label>
                                            </div>
                                            <div class="form-check mb-2">
                                                <input class="form-check-input" type="checkbox" name="require_mixed_case" id="require_mixed_case" value="1" checked>
                                                <label class="form-check-label" for="require_mixed_case">Require mixed case (A-Z, a-z)</label>
                                            </div>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Password Expiration (Days)</label>
                                            <input type="number" class="form-control" name="password_expiration_days" id="password_expiration_days" value="90">
                                            <p class="text-muted small mt-1">Set to 0 to disable expiration.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Session Management -->
                            <div class="col-md-6 mb-4">
                                <div class="card security-card h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="bg-light-info text-info p-2 rounded-circle me-3">
                                                <i class="ti ti-clock fs-6"></i>
                                            </span>
                                            <h5 class="card-title fw-semibold mb-0">Session Management</h5>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Ideal Timeout (Minutes)</label>
                                            <input type="number" class="form-control" name="session_timeout_minutes" id="session_timeout_minutes" value="30">
                                            <p class="text-muted small mt-1">Automatically logout inactive administrators.</p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Concurrent Login Limit</label>
                                            <input type="number" class="form-control" name="concurrent_login_limit" id="concurrent_login_limit" value="1">
                                            <p class="text-muted small mt-1">Number of active sessions per account.</p>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" name="allow_remember_me" id="allow_remember_me" value="1" checked>
                                            <label class="form-check-label" for="allow_remember_me">Allow "Remember Me" on login</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Brute-Force & Lockout -->
                            <div class="col-md-6 mb-4">
                                <div class="card security-card danger h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="bg-light-danger text-danger p-2 rounded-circle me-3">
                                                <i class="ti ti-shield-x fs-6"></i>
                                            </span>
                                            <h5 class="card-title fw-semibold mb-0">Brute-Force Protection</h5>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Max Login Attempts</label>
                                            <input type="number" class="form-control" name="max_login_attempts" id="max_login_attempts" value="5">
                                            <p class="text-muted small mt-1">Number of failed tries before account lockout.</p>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Lockout Duration (Minutes)</label>
                                            <input type="number" class="form-control" name="lockout_duration_minutes" id="lockout_duration_minutes" value="15">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Advanced Authentication -->
                            <div class="col-md-6 mb-4">
                                <div class="card security-card warning h-100">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-4">
                                            <span class="bg-light-warning text-warning p-2 rounded-circle me-3">
                                                <i class="ti ti-topology-star-ring fs-6"></i>
                                            </span>
                                            <h5 class="card-title fw-semibold mb-0">Authentication Toggles</h5>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" name="enforce_2fa" id="enforce_2fa" value="1">
                                            <label class="form-check-label fw-bold" for="enforce_2fa">Enforce 2FA for all Super Admins</label>
                                        </div>
                                        <div class="form-check form-switch mb-3">
                                            <input class="form-check-input" type="checkbox" role="switch" name="ip_whitelisting_enabled" id="ip_whitelisting_enabled" value="1">
                                            <label class="form-check-label fw-bold" for="ip_whitelisting_enabled">IP Whitelisting Required</label>
                                        </div>
                                        <div class="mb-0">
                                            <label class="form-label">Allowed IPs (CSV)</label>
                                            <textarea class="form-control" name="allowed_ips" id="allowed_ips" rows="2" placeholder="e.g. 192.168.1.1, 203.0.113.5"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card shadow-sm border-top border-4 border-primary">
                            <div class="card-body d-flex flex-md-row flex-column justify-content-between align-items-center py-4">
                                <div class="mb-md-0 mb-3 text-center text-md-start">
                                    <h5 class="fw-semibold mb-1">System-Wide Deployment</h5>
                                    <p class="text-muted small mb-0"><i class="ti ti-info-circle me-1"></i> Policy changes apply immediately to all administrative sessions.</p>
                                </div>
                                <button type="submit" class="btn btn-primary px-5 py-2 btn-apply-policy" id="btnApplyPoliciesMain">
                                    <i class="ti ti-shield-check me-2 fs-5"></i> Apply Security Policies
                                </button>
                            </div>
                        </div>
                    </form>

                    <?php include 'components/footer.php'; ?>
                </div>
            </div>
        </div>
    </div>
    <?php include 'components/scripts.php'; ?>
    <script>
        $(document).ready(function() {
            // Load current settings
            loadSecuritySettings();

            function loadSecuritySettings() {
                AuroraLoader.showOverlay('#main-wrapper', 'Retrieving Security Policies...');
                $.get('endpoints/security_handler.php', { action: 'get_settings' }, function(response) {
                    console.log('Fetched Settings:', response);
                    AuroraLoader.hideOverlay('#main-wrapper');
                    if (response.status === 'success') {
                        const settings = response.data;
                        Object.keys(settings).forEach(key => {
                            const input = $(`[name="${key}"]`);
                            if (input.is(':checkbox')) {
                                input.prop('checked', settings[key] === '1');
                            } else {
                                input.val(settings[key]);
                            }
                        });
                    } else {
                        console.error('Fetch Failed:', response);
                    }
                }, 'json').fail(function(err) {
                    AuroraLoader.hideOverlay('#main-wrapper');
                    console.error('Fetch Error:', err.responseText || err);
                });
            }

            $('#securitySettingsForm').on('submit', function(e) {
                e.preventDefault();
                const btn = $('#btnApplyPoliciesMain');
                const originalText = btn.html();
                
                // Collect setting data
                const settings = {};
                $(this).find('input, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (!name) return;
                    
                    if ($(this).is(':checkbox')) {
                        settings[name] = $(this).is(':checked') ? '1' : '0';
                    } else {
                        settings[name] = $(this).val();
                    }
                });

                console.log('Applying Settings:', settings);
                btn.html('<span class="spinner-border spinner-border-sm me-2"></span> Applying...').prop('disabled', true);
                
                $.post('endpoints/security_handler.php', { 
                    action: 'update_settings', 
                    settings: settings 
                }, function(response) {
                    console.log('Update Response:', response);
                    btn.html(originalText).prop('disabled', false);
                    
                    if (response.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Settings Saved',
                            text: response.message,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        });
                    } else {
                        console.error('Update Failed:', response);
                        Swal.fire({
                            icon: 'error',
                            title: 'Update Failed',
                            text: response.message || 'Check console for details.'
                        });
                    }
                }, 'json').fail(function(err) {
                    console.error('Update AJAX Error:', err.responseText || err);
                    btn.html(originalText).prop('disabled', false);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Connection error while applying policies. Check console for details.'
                    });
                });
            });
        });
    </script>
</body>

</html>
