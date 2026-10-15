<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Organizer Login | AuroraTabs</title>
  <link rel="shortcut icon" type="image/png" href="../admin/src/assets/images/logos/favicon.png" />
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
    .form-floating > .form-control {
      border-radius: 16px;
      border: 2px solid transparent;
      background-color: #f1f5f9;
      padding: 1rem 0.75rem;
      height: calc(3.5rem + 2px);
      line-height: 1.25;
      font-weight: 500;
      color: #334155;
      font-size: 1rem;
    }
    
    .form-floating > .form-control:focus {
      background-color: #fff;
      border-color: #e2e8f0;
      box-shadow: 0 4px 20px rgba(0,0,0,0.05);
      transform: translateY(-2px);
    }
    
    .form-floating > label {
      color: #64748b;
      padding: 1rem 0.75rem;
    }
    
    .form-floating > .form-control:focus ~ label,
    .form-floating > .form-control:not(:placeholder-shown) ~ label {
        transform: scale(0.85) translateY(-0.75rem) translateX(0.15rem);
        color: var(--primary-color);
        font-weight: 600;
    }
    
    /* Buttons */
    .btn-primary {
      background: linear-gradient(135deg, var(--google-blue), #3b82f6);
      border: none;
      padding: 1rem;
      border-radius: 16px;
      font-weight: 700;
      font-size: 1.05rem;
      letter-spacing: 0.5px;
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      box-shadow: 0 4px 12px rgba(66, 133, 244, 0.2);
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 20px rgba(66, 133, 244, 0.3);
      background: linear-gradient(135deg, #3b82f6, var(--google-blue));
    }
    
    .social-auth-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      padding: 0.8rem;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      background: #fff;
      color: #0f172a;
      text-decoration: none;
      transition: all 0.2s;
      font-weight: 600;
    }

    .social-auth-btn:hover {
      background: #f8fafc;
      border-color: #cbd5e1;
      transform: translateY(-2px);
      box-shadow: 0 4px 6px rgba(0,0,0,0.05);
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
            <h1 class="display-6 fw-bold mb-0">Elevate Your Command Center.</h1>
        </div>
        <p class="fs-5 fw-light mb-0 opacity-75">Manage your events, track results, and create memorable experiences.</p>

        <div class="feature-list">
          <div class="feature-item" style="animation-delay: 0.5s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:clapperboard-edit-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Event Operations</h3>
              <p>Full control over your event lifecycle and participants.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.6s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:ranking-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Real-time Results</h3>
              <p>Monitor live scoring and instantly generate final reports.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.7s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:user-speak-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Organizer Support</h3>
              <p>Dedicated tools to ensure a smooth administrative experience.</p>
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
        <iconify-icon icon="solar:clapperboard-edit-bold-duotone" class="text-primary"></iconify-icon>
        Organizer Sign In
      </div>
      <div class="auth-form-wrapper">
        <div class="d-lg-none text-center mb-4">
           <img style="margin-top: 50px;" src="../admin/src/assets/images/logos/aurora-logo.png" alt="" height="120">
        </div>
        
        <div class="mb-4">
            <h2 class="page-title">Organizer Sign In</h2>
            <p class="page-desc">Access your event management dashboard.</p>
        </div>

        <form id="loginForm">
          <div class="form-floating mb-3">
            <input type="text" class="form-control" id="username" placeholder="Username" required>
            <label for="username">Username</label>
          </div>
          
          <div class="form-floating mb-4 position-relative">
            <input type="password" class="form-control" id="password" placeholder="Password" required style="padding-right: 45px;">
            <label for="password">Password</label>
            <span class="password-toggle" id="togglePassword">
                <iconify-icon icon="solar:eye-bold-duotone" width="22" id="eyeIcon"></iconify-icon>
            </span>
          </div>
          
          <div class="d-flex align-items-center justify-content-between mb-4">
            <div class="form-check">
              <input class="form-check-input primary" type="checkbox" value="" id="flexCheckChecked" checked>
              <label class="form-check-label text-dark ms-2 user-select-none" for="flexCheckChecked">
                Remember Me
              </label>
            </div>
            <a class="text-primary fw-bold text-decoration-none" href="#">Forgot Password?</a>
          </div>
          
          <button type="submit" class="btn btn-primary w-100 mb-4">
              Sign In <iconify-icon icon="lucide:log-in"></iconify-icon>
          </button>
          
          <div class="position-relative text-center mb-4">
            <hr class="text-muted opacity-25">
            <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">or continue with</span>
          </div>

          <div class="row g-2 mb-4">
            <div class="col-6">
                <a href="#" class="social-auth-btn">
                    <iconify-icon icon="logos:google-icon"></iconify-icon>
                    <span>Google</span>
                </a>
            </div>
            <div class="col-6">
                <a href="#" class="social-auth-btn">
                     <iconify-icon icon="logos:facebook"></iconify-icon>
                    <span>Facebook</span>
                </a>
            </div>
          </div>
          
          <div class="text-center">
            <p class="mb-0 text-muted">Don't have an account? <a href="./register-organizer.php" class="text-primary fw-bold text-decoration-none">Create Organizer Account</a></p>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="../admin/src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="../admin/src/assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

    $('#loginForm').on('submit', function(e) {
        e.preventDefault();
        
        const btn = $(this).find('button[type="submit"]');
        const originalText = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Signing in...');

        const data = {
            username: $('#username').val(),
            password: $('#password').val()
        };

        $.ajax({
            url: 'endpoints/auth_login_organizer.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                if(response.status === 'success') {
                    localStorage.setItem('token', response.token);
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Welcome back.',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        window.location.href = response.redirect;
                    });
                } else if(response.status === 'pending') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Verification Required',
                        text: response.message,
                        confirmButtonText: 'Verify Now'
                    }).then(() => {
                        // Automatically resend OTP before redirecting
                        Swal.fire({
                            title: 'Sending Code...',
                            html: 'We are sending a fresh verification code to your email.',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                                $.ajax({
                                    url: 'endpoints/auth_resend_otp.php',
                                    type: 'POST',
                                    data: { email: response.email, user_id: response.user_id },
                                    success: function() {
                                        // Use dynamic form for POST redirect
                                        const form = $('<form>', {
                                            action: './verify-otp.php',
                                            method: 'POST'
                                        }).append($('<input>', {
                                            type: 'hidden',
                                            name: 'email',
                                            value: response.email
                                        })).append($('<input>', {
                                            type: 'hidden',
                                            name: 'uid',
                                            value: response.user_id
                                        }));
                                        $('body').append(form);
                                        form.submit();
                                    },
                                    error: function() {
                                        // Still redirect even if resend fails
                                        const form = $('<form>', {
                                            action: './verify-otp.php',
                                            method: 'POST'
                                        }).append($('<input>', {
                                            type: 'hidden',
                                            name: 'email',
                                            value: response.email
                                        })).append($('<input>', {
                                            type: 'hidden',
                                            name: 'uid',
                                            value: response.user_id
                                        }));
                                        $('body').append(form);
                                        form.submit();
                                    }
                                });
                            }
                        });
                    });
                    btn.prop('disabled', false);
                    btn.html(originalText);
                } else if(response.status === 'locked') {
                    let timerInterval;
                    Swal.fire({
                        icon: 'warning',
                        title: 'System Locked',
                        html: `Too many failed attempts. Please wait <b id="lockout-timer">${response.remaining_time}</b> seconds.`,
                        showConfirmButton: false,
                        timer: response.remaining_time * 1000,
                        timerProgressBar: true,
                        didOpen: () => {
                            const b = Swal.getHtmlContainer().querySelector('#lockout-timer');
                            let timeLeft = response.remaining_time;
                            timerInterval = setInterval(() => {
                                timeLeft--;
                                if (b) b.textContent = timeLeft;
                            }, 1000);
                        },
                        willClose: () => {
                            clearInterval(timerInterval);
                        }
                    });
                    btn.prop('disabled', false);
                    btn.html(originalText);
                } else {
                    Swal.fire('Login Failed', response.message, 'error');
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred. Please try again.', 'error');
                btn.prop('disabled', false);
                btn.html(originalText);
            }
        });
        });
    });
  </script>
</body>

</html>
