<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login | AuroraTabs</title>
  <link rel="shortcut icon" type="image/png" href="../admin/src/assets/images/logos/aurora-logo.png" />
  <link rel="stylesheet" href="../admin/src/assets/css/styles.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  
  <style>
    :root {
      --google-blue: #4285F4;
      --google-red: #EA4335;
      --google-yellow: #FBBC05;
      --google-green: #34A853;
      --primary-color: var(--google-blue);
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      overflow-x: hidden;
      background-color: #f0f4f8;
    }

    .split-screen {
      min-height: 100vh;
      display: flex;
    }

    .left-pane {
      display: none;
      width: 45%;
      position: relative;
      background: linear-gradient(135deg, #4285F4 0%, #1a73e8 100%);
      overflow: hidden;
    }
    
    .glass-overlay {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 90.2%);
      backdrop-filter: blur(5px);
      z-index: 1;
    }

    /* Decorative circles */
    .left-pane::before, .left-pane::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.1);
      z-index: 0;
    }

    .left-pane::before {
      width: 300px;
      height: 300px;
      top: -100px;
      left: -100px;
    }

    .left-pane::after {
      width: 200px;
      height: 200px;
      bottom: -50px;
      right: -50px;
    }

    .left-content {
      position: relative;
      z-index: 2;
      color: white;
      height: 100%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 5rem;
    }

    .feature-list {
      margin-top: 3rem;
      display: flex;
      flex-direction: column;
      gap: 1.5rem;
    }

    .feature-item {
      display: flex;
      align-items: center;
      gap: 1.25rem;
      animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
      opacity: 0;
    }

    .feature-icon {
      width: 48px;
      height: 48px;
      background: rgba(255, 255, 255, 0.2);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 1.5rem;
      backdrop-filter: blur(4px);
      border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .feature-text h3 {
      font-size: 1.1rem;
      font-weight: 600;
      margin-bottom: 0.25rem;
      color: #fff;
    }

    .feature-text p {
      font-size: 0.9rem;
      color: rgba(255, 255, 255, 0.8);
      margin: 0;
    }

    .right-pane {
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #ffffff;
      padding: 2rem;
      position: relative;
    }

    @media (min-width: 992px) {
      .left-pane {
        display: block;
      }
      .right-pane {
        width: 55%;
      }
    }

    .auth-form-wrapper {
      width: 100%;
      max-width: 450px;
    }
    
    .page-title {
        font-weight: 700;
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }
    
    .page-desc {
        color: #64748b;
        margin-bottom: 2rem;
        font-size: 1.1rem;
    }

    /* Form Controls */
    .form-floating > .form-control, .form-floating > .form-select {
      border-radius: 12px;
      border: 1px solid #e2e8f0;
      background-color: #f8fafc;
    }
    
    .form-floating > .form-control:focus, .form-floating > .form-select:focus {
      background-color: #fff;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.1);
    }
    
    .form-floating > label {
      color: #64748b;
    }

    /* Checkbox */
    .form-check-input {
        width: 1.2rem;
        height: 1.2rem;
        border-radius: 4px;
        border: 1px solid #cbd5e1;
    }
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }

    /* Buttons */
    .btn-primary {
      background: var(--primary-color);
      border: none;
      padding: 1rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: 1rem;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-primary:hover {
      background: #3367d6;
      transform: translateY(-1px);
      box-shadow: 0 4px 12px rgba(66, 133, 244, 0.3);
    }
    
    .mobile-back-btn {
        position: absolute;
        top: 1.5rem;
        left: 1.5rem;
        z-index: 100;
        display: flex;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        color: #64748b;
        text-decoration: none;
        transition: all 0.2s;
    }
    
    .mobile-back-btn:hover {
        transform: translateX(-2px);
        color: var(--primary-color);
        box-shadow: 0 6px 16px rgba(0,0,0,0.15);
    }
    
    .auth-badge {
        position: absolute;
        top: 1.5rem;
        right: 1.5rem;
        background: #f1f5f9;
        color: #475569;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        z-index: 10;
    }

    /* Micro-Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .auth-form-wrapper > * {
        opacity: 0;
        animation: fadeInUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .auth-form-wrapper > *:nth-child(1) { animation-delay: 0.1s; }
    .auth-form-wrapper > *:nth-child(2) { animation-delay: 0.2s; }
    .auth-form-wrapper > *:nth-child(3) { animation-delay: 0.3s; }
    .auth-form-wrapper > *:nth-child(4) { animation-delay: 0.4s; }
    
    .form-control {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .form-control:focus {
        transform: translateY(-2px);
    }
    
    .btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .btn:active {
        transform: scale(0.98);
    }

    .password-toggle {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 10;
        color: #64748b;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 30px;
        height: 30px;
    }

    .password-toggle:hover {
        color: var(--primary-color);
    }

  </style>
</head>

<body>
  <div class="split-screen">
    <!-- Visual Side -->
    <div class="left-pane">
       <div class="glass-overlay"></div>
      <div class="left-content">
        <div class="d-flex align-items-center gap-3 mb-4">
            <img src="../admin/src/assets/images/logos/aurora-logo.png" alt="Logo" width="48" style="filter: brightness(0) invert(1);">
            <h1 class="display-6 fw-bold mb-0 text-white">Elevate Your Command Center.</h1>
        </div>
        <p class="fs-5 fw-light mb-0 opacity-75">Advanced management tools for the modern administrator.</p>

        <div class="feature-list">
          <div class="feature-item" style="animation-delay: 0.5s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Real-time Analytics</h3>
              <p>Track your campaign performance with live data insights.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.6s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:shield-user-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Secure Access</h3>
              <p>Multi-layered security protocols to protect your sensitive data.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.7s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:magic-stick-3-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Streamlined Workflow</h3>
              <p>Manage everything from one intuitive, powerful dashboard.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Form Side -->
    <div class="right-pane">
      <a href="./index.php" class="mobile-back-btn" title="Back to Portal">
        <iconify-icon icon="lucide:arrow-left" width="20" height="20"></iconify-icon>
      </a>
      <div class="auth-badge">
        <iconify-icon icon="solar:shield-keyhole-bold-duotone" class="text-primary"></iconify-icon>
        Admin Portal
      </div>
      <div class="auth-form-wrapper">
        <div class="d-lg-none text-center mb-4">
           <img style="margin-top: 50px;" src="../admin/src/assets/images/logos/aurora-logo.png" alt="" height="120">
        </div>
        
        <div class="mb-4">
            <h2 class="page-title">Sign In</h2>
            <p class="page-desc">Enter your username and password to access your account.</p>
        </div>
       

        <form id="loginForm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="username" placeholder="name@example.com">
            <label for="username">Username</label>
          </div>
          
          <div class="form-floating mb-3 position-relative">
            <input type="password" class="form-control" id="password" placeholder="Password" style="padding-right: 45px;">
            <label for="password">Password</label>
            <span class="password-toggle" id="togglePassword">
                <iconify-icon icon="solar:eye-bold-duotone" width="22" id="eyeIcon"></iconify-icon>
            </span>
          </div>

          <div class="form-floating mb-4">
            <select class="form-select" id="roleSelect" aria-label="Select Role">
              <option selected disabled value="">Loading roles...</option>
            </select>
            <label for="roleSelect">Select Role</label>
          </div>
          
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
              <input class="form-check-input primary" type="checkbox" value="" id="rememberMe" checked>
              <label class="form-check-label text-dark ms-2 user-select-none" for="rememberMe">
                Remember this Device
              </label>
            </div>
            <a class="text-primary fw-bold text-decoration-none" href="./forgot-password.html">Forgot Password?</a>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 py-8 mb-4 rounded-2" id="signInBtn">
            <span id="btnText">Sign In</span>
            <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none" role="status" aria-hidden="true"></span>
          </button>
          
          <div id="lockoutMsg" class="alert alert-danger d-none text-center" role="alert">
            Too many attempts. Try again in <span id="countdown">30</span>s.
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script src="../admin/src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../admin/src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="../admin/src/assets/js/alert_utils.js"></script>

  <script>
    $(document).ready(function() {
        // Toggle Password Visibility
        $('#togglePassword').on('click', function() {
            const passwordInput = $('#password');
            const eyeIcon = $('#eyeIcon');
            
            if (passwordInput.attr('type') === 'password') {
                passwordInput.attr('type', 'text');
                eyeIcon.attr('icon', 'solar:eye-closed-bold-duotone');
            } else {
                passwordInput.attr('type', 'password');
                eyeIcon.attr('icon', 'solar:eye-bold-duotone');
            }
        });

        // Check Lockout Status on Load (IP only)
        checkLockout();

        // Check again when username is entered
        $('#username').on('blur', function() {
            checkLockout($(this).val());
        });

        function checkLockout(username = '') {
             $.ajax({
                url: 'endpoints/check_lockout.php',
                type: 'GET',
                data: { username: username },
                dataType: 'json',
                success: function(response) {
                    if(response.status === 'locked') {
                        startLockout(response.remaining_time);
                    }
                }
            });
        }

        function startLockout(seconds) {
            // Disable Inputs
            $('#username, #password, #roleSelect, #rememberMe, #signInBtn').prop('disabled', true);
            $('#lockoutMsg').removeClass('d-none');
            
            let counter = seconds;
            $('#countdown').text(counter);
            
            AlertUtils.showAlert('error', 'Limit Reached', 'Too many failed attempts. You are locked out.');

            const interval = setInterval(() => {
                counter--;
                $('#countdown').text(counter);
                
                if (counter <= 0) {
                    clearInterval(interval);
                    // Re-enable
                    $('#username, #password, #roleSelect, #rememberMe, #signInBtn').prop('disabled', false);
                    $('#lockoutMsg').addClass('d-none');
                }
            }, 1000);
        }

        // 1. Fetch Roles
        $.ajax({
            url: 'endpoints/get_roles.php', // Updated path
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    let options = '<option selected disabled value="">Select your Role</option>';
                    response.data.forEach(function(role) {
                        options += `<option value="${role.id}">${role.display_name}</option>`;
                    });
                    $('#roleSelect').html(options);
                } else {
                    AlertUtils.showAlert('error', 'Error', 'Failed to load roles: ' + response.message);
                }
            },
            error: function() {
                AlertUtils.showAlert('error', 'System Error', 'Could not connect to the server to fetch roles.');
            }
        });

        // 2. Handle Login
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const username = $('#username').val();
            const password = $('#password').val();
            const role = $('#roleSelect').val();
            
            if(!role) {
                AlertUtils.showToast('warning', 'Please select a role to continue.');
                return;
            }

            // Button Loading State
            const btnText = $('#btnText');
            const btnSpinner = $('#btnSpinner');
            const submitBtn = $('button[type="submit"]');

            btnText.text('Signing in...');
            btnSpinner.removeClass('d-none');
            submitBtn.prop('disabled', true);

            $.ajax({
                url: 'endpoints/auth_login.php', // Updated path
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify({
                    username: username,
                    password: password,
                    role: role
                }),
                success: function(response) {
                    if(response.status === 'success') {
                        AlertUtils.showToast('success', 'Signed in successfully')
                        .then(() => {
                            if(response.token) localStorage.setItem('auth_token', response.token);
                            window.location.href = response.redirect;
                        });
                    } else if (response.status === 'pending') {
                        AlertUtils.showAlert('info', 'Verification Required', response.message);
                        // Reset Button
                        btnText.text('Sign In');
                        btnSpinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    } else if (response.status === 'locked') {
                        startLockout(response.remaining_time);
                    } else {
                        AlertUtils.showAlert('error', 'Login Failed', response.message);
                        // Reset Button
                        btnText.text('Sign In');
                        btnSpinner.addClass('d-none');
                        submitBtn.prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                     AlertUtils.showAlert('error', 'Connection Error', 'Something went wrong. Please try again.');
                     // Reset Button
                    btnText.text('Sign In');
                    btnSpinner.addClass('d-none');
                    submitBtn.prop('disabled', false);
                }
            });
        });

        // 3. Auto-Logout / Inactivity Timer (Security Feature)
        let inactivityTime = function () {
            let time;
            // 5 minutes of inactivity on login page clears the form for security
            // (Simulating a kiosk or shared computer scenario)
            const timeoutDuration = 300000; // 5 mins in ms

            window.onload = resetTimer;
            document.onmousemove = resetTimer;
            document.onkeypress = resetTimer;

            function logout() {
                // Clear form inputs
                $('#username').val('');
                $('#password').val('');
                $('#roleSelect').val('');
                
                AlertUtils.showAlert('info', 'Session Timeout', 'The page has been refreshed due to inactivity.')
                .then(() => {
                    location.reload();
                });
            }

            function resetTimer() {
                clearTimeout(time);
                time = setTimeout(logout, timeoutDuration);
            }
        };

        inactivityTime();
    });
  </script>
</body>

</html>