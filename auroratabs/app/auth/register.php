<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Create Account | AuroraTabs</title>
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
      max-width: 500px;
    }

    /* Wizard Steps */
    .step-indicator {
      display: flex;
      justify-content: center;
      margin-bottom: 2rem;
      gap: 0.5rem;
    }

    .step-dot {
      width: 10px;
      height: 10px;
      background-color: #e2e8f0;
      border-radius: 50%;
      transition: all 0.3s ease;
    }

    .step-dot.active {
      background-color: var(--primary-color);
      transform: scale(1.3);
    }
    
    .step-dot.completed {
        background: var(--primary-color);
        border-color: var(--primary-color);
        transform: scale(0.9);
        opacity: 0.6;
    }

    /* Password Toggle */
    .password-wrapper {
        position: relative;
    }
    
    .password-toggle {
        position: absolute;
        right: 1rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 10;
        color: #64748b;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
    }
    
    .password-toggle:hover {
        background-color: rgba(66, 133, 244, 0.1);
        color: var(--primary-color);
    }

    .form-step {
      display: none;
      animation: fadeIn 0.4s ease-out;
    }

    .form-step.active {
      display: block;
    }
    
    .step-title {
        font-weight: 700;
        font-size: 1.75rem;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }
    
    .step-desc {
        color: #64748b;
        margin-bottom: 2rem;
        font-size: 1rem;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Form Controls */
     .form-floating > .form-control {
      border-radius: 16px;
      border: 2px solid transparent;
      background-color: #f1f5f9;
      padding: 1rem 3.5rem 1rem 0.75rem;
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

    /* Password Strength */
    .password-strength {
      height: 4px;
      background-color: #e2e8f0;
      border-radius: 2px;
      margin-top: 0.5rem;
      overflow: hidden;
      display: flex;
    }

    .strength-bar {
      height: 100%;
      width: 0;
      transition: width 0.3s ease, background-color 0.3s ease;
    }

    .strength-text {
      font-size: 0.75rem;
      margin-top: 0.25rem;
      text-align: right;
      color: #64748b;
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
    
    .btn-outline-secondary {
      border-radius: 16px;
      padding: 1rem;
      font-weight: 600;
      border: 2px solid #e2e8f0;
      color: #64748b;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
      background: transparent;
      transition: all 0.2s;
    }
    
    .btn-outline-secondary:hover {
        background-color: #fff;
        border-color: #cbd5e1;
        color: #1e293b;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
    }
    
    .btn-outline-secondary:hover {
        background-color: #f1f5f9;
        color: #1e293b;
    }
    
    .hint-text {
        font-size: 0.8rem;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 0.25rem;
        margin-top: 0.25rem;
    }

    .summary-item {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 8px;
        margin-bottom: 0.5rem;
        border: 1px solid #e2e8f0;
    }
    
    .summary-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.25rem;
    }
    
    .summary-value {
        font-weight: 500;
        color: #334155;
    }

    .success-icon {
        font-size: 4rem;
        color: var(--google-green);
        margin-bottom: 1rem;
        animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    @keyframes popIn {
        0% { transform: scale(0); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
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
    
    .step-dot {
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
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
        <p class="fs-5 fw-light mb-0 opacity-75">Start your journey with the next generation of social engagement.</p>

        <div class="feature-list">
          <div class="feature-item" style="animation-delay: 0.5s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Easy Registration</h3>
              <p>Create your account in just a few simple steps.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.6s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Verified Identity</h3>
              <p>Secure voting and engagement for authentic participation.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.7s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:magic-stick-3-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Tailored Experience</h3>
              <p>Personalized dashbard to track your favorite events.</p>
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
        <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="text-primary"></iconify-icon>
        Audience Registration
      </div>
      <div class="auth-form-wrapper">
        <div class="d-lg-none text-center mb-4">
           <img style="margin-top: 50px;" src="../admin/src/assets/images/logos/aurora-logo.png" alt="" height="120">
        </div>
        
        <!-- Step Indicators -->
        <div class="step-indicator" id="stepIndicator">
            <div class="step-dot active" data-step="1"></div>
            <div class="step-dot" data-step="2"></div>
            <div class="step-dot" data-step="3"></div>
            <div class="step-dot" data-step="4"></div>
            <div class="step-dot" data-step="5"></div>
            <div class="step-dot" data-step="6"></div>
        </div>

        <form id="registrationForm" onsubmit="event.preventDefault();">
            
          <!-- STEP 1: Name Information -->
          <div class="form-step active" id="step-1">
            <h2 class="step-title">Let's Get Started</h2>
            <p class="step-desc">Tell us your name as it appears on official records.</p>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="fname" placeholder="First Name" required>
                <label for="fname">First Name</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="mname" placeholder="Middle Name">
                <label for="mname">Middle Name (optional)</label>
            </div>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="lname" placeholder="Last Name" required>
                <label for="lname">Last Name</label>
            </div>
            
            <div class="hint-text mb-4">
                <iconify-icon icon="solar:lightbulb-bold" class="text-warning"></iconify-icon>
                This allows proper certificates, reports, and analytics later.
            </div>

            <button type="button" class="btn btn-primary w-100" onclick="goToStep(2)">
                Next <iconify-icon icon="lucide:arrow-right"></iconify-icon>
            </button>
            <div class="text-center mt-3">
                <p class="mb-0 text-muted">Already have an account? <a href="./login-audience.php" class="text-primary fw-bold text-decoration-none">Sign In</a></p>
            </div>
          </div>

          <!-- STEP 2: Account Credentials -->
          <div class="form-step" id="step-2">
             <h2 class="step-title">Create Your Login</h2>
             <p class="step-desc">This will be your login information for Aurora Tabs.</p>

             <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" placeholder="name@example.com" required>
                <label for="email">Email Address</label>
             </div>

             <div class="form-floating mb-1 position-relative">
                <input type="password" class="form-control" id="password" placeholder="Password" required>
                <label for="password">Password</label>
                <div class="password-toggle" data-target="password">
                    <iconify-icon icon="solar:eye-bold-duotone" width="22"></iconify-icon>
                </div>
             </div>
             
             <div class="password-strength mb-3">
                <div class="strength-bar" id="strengthBar"></div>
             </div>
             
             <div class="d-flex justify-content-between text-muted small mb-3">
                 <span>At least 8 characters</span>
                 <span>Letters and numbers recommended</span>
             </div>

             <div class="form-floating mb-3 position-relative">
                <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm Password" required>
                <label for="confirmPassword">Confirm Password</label>
                <div class="password-toggle" data-target="confirmPassword">
                    <iconify-icon icon="solar:eye-bold-duotone" width="22"></iconify-icon>
                </div>
                <div class="invalid-feedback" id="passwordMatchError">Passwords do not match</div>
             </div>
             
             <div class="form-floating mb-4">
                <input type="text" class="form-control" id="passwordHint" placeholder="Password Hint">
                <label for="passwordHint">Password Hint</label>
             </div>

             <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary w-50" onclick="goToStep(1)">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
                <button type="button" class="btn btn-primary w-50" onclick="goToStep(3)">
                    Next <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </button>
             </div>
          </div>

          <!-- STEP 3: Contact Information -->
          <div class="form-step" id="step-3">
            <h2 class="step-title">Contact Details</h2>
            <p class="step-desc">Used for notifications, voting verification, and account recovery.</p>
            
            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="mobile" placeholder="Mobile Number" required>
                <label for="mobile">Mobile Number</label>
            </div>
            
            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="username" placeholder="Username">
                <label for="username">Username (optional)</label>
                <div class="form-text">We'll auto-generate one if left empty.</div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary w-50" onclick="goToStep(2)">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
                <button type="button" class="btn btn-primary w-50" onclick="goToReview()">
                    Next <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </button>
            </div>
          </div>

          <!-- STEP 4: Review & Agreement -->
          <div class="form-step" id="step-4">
            <h2 class="step-title">Review Your Information</h2>
            <p class="step-desc">Please double check your details before proceeding.</p>
            
            <div class="mb-4">
                <div class="summary-item">
                    <div class="summary-label">Full Name</div>
                    <div class="summary-value" id="summaryName">--</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Email Address</div>
                    <div class="summary-value" id="summaryEmail">--</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Mobile Number</div>
                    <div class="summary-value" id="summaryMobile">--</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Username</div>
                    <div class="summary-value" id="summaryUsername">--</div>
                </div>
            </div>
            
            <div class="mb-4">
                <div class="form-check mb-2">
                  <input class="form-check-input" type="checkbox" value="" id="checkTerms" required>
                  <label class="form-check-label" for="checkTerms">
                    I agree to the <a href="#" class="text-primary text-decoration-none">Terms & Conditions</a>
                  </label>
                </div>
                <div class="form-check">
                  <input class="form-check-input" type="checkbox" value="" id="checkPrivacy" required>
                  <label class="form-check-label" for="checkPrivacy">
                    I agree to the <a href="#" class="text-primary text-decoration-none">Privacy Policy</a>
                  </label>
                </div>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary w-50" onclick="goToStep(3)">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
                <button type="button" class="btn btn-primary w-50" onclick="completeRegistration()">
                    Verify Email <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </button>
            </div>
          </div>

          <!-- STEP 5: OTP Verification -->
          <div class="form-step" id="step-5">
            <h2 class="step-title">Verify Your Email</h2>
            <p class="step-desc">We've sent a verification code to your email address.</p>
            
            <div class="text-center mb-4">
                <iconify-icon icon="solar:letter-unread-bold-duotone" width="64" class="text-primary mb-3"></iconify-icon>
                <p class="mb-2">Enter the 6-digit code sent to:</p>
                <p class="fw-bold text-dark" id="displayEmail">user@example.com</p>
            </div>

            <div class="form-floating mb-3">
                <input type="text" class="form-control text-center fw-bold fs-4" id="otpCode" placeholder="000000" maxlength="6" style="letter-spacing: 0.5rem;">
                <label for="otpCode" class="text-center w-100">Enter Code</label>
            </div>
            <div class="text-center mb-4">
                <small class="text-muted">
                    Didn't receive the code? 
                    <span id="otpCountdownWrapper">
                        Resend in <span id="otpTimer" class="fw-bold text-primary">03:00</span>
                    </span>
                    <a href="javascript:void(0)" id="resendOtpBtn" class="text-primary fw-bold text-decoration-none" style="display: none;">Resend Now</a>
                </small>
            </div>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary w-50" onclick="goToStep(4)">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
                <button type="button" class="btn btn-primary w-50" onclick="verifyOtp()">
                    Verify & Create <iconify-icon icon="lucide:check-circle"></iconify-icon>
                </button>
            </div>
          </div>

           <!-- STEP 6: Success -->
          <div class="form-step text-center" id="step-6">
             <div class="mt-4">
                 <iconify-icon icon="solar:check-circle-bold" class="success-icon"></iconify-icon>
                 <h2 class="step-title mb-3">Account Created Successfully!</h2>
                 <p class="text-muted mb-4 fs-5">Welcome to Aurora Tabs. Your account is ready.</p>
                 
                 <a href="./index.html" class="btn btn-primary w-100 py-3">
                     Go to Dashboard
                 </a>
             </div>
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
    let currentStep = 1;
    
    function goToStep(step) {
        if(step > currentStep) {
            // Validate before moving forward
            if(!validateStep(currentStep)) return;
        }
        
        // Update UI
        $('.form-step').removeClass('active');
        $(`#step-${step}`).addClass('active');
        
        // Update Dots
        $('.step-dot').each(function(index) {
            const dotStep = index + 1;
            $(this).removeClass('active completed');
            if (dotStep === step) {
                $(this).addClass('active');
            } else if (dotStep < step) {
                $(this).addClass('completed');
            }
        });
        
        currentStep = step;
        
        // Hide indicators on success step
        if(step === 6) {
            $('#stepIndicator').css('opacity', '0');
        }
    }
    
    function validateStep(step) {
        let isValid = true;
        
        if (step === 1) {
            const fname = $('#fname').val();
            const lname = $('#lname').val();
             if(!fname || !lname) {
                 isValid = false;
                 Swal.fire({
                     icon: 'warning',
                     title: 'Required Fields',
                     text: 'Please enter your first and last name.',
                     confirmButtonColor: 'var(--primary-color)'
                 });
             }
        }
        
        if (step === 2) {
             const email = $('#email').val();
             const password = $('#password').val();
             const confirm = $('#confirmPassword').val();
             
             if(!email || !password || !confirm) {
                 Swal.fire({
                     icon: 'info',
                     title: 'Credentials Required',
                     text: 'Please fill in all credential fields.',
                     confirmButtonColor: 'var(--primary-color)'
                 });
                 isValid = false;
             } else if(password !== confirm) {
                 $('#passwordMatchError').show();
                 Swal.fire({
                     icon: 'error',
                     title: 'Mismatch',
                     text: 'Passwords do not match.',
                     confirmButtonColor: 'var(--primary-color)'
                 });
                 isValid = false;
             } else {
                 $('#passwordMatchError').hide();
             }
        }
        
        if (step === 3) {
            const mobile = $('#mobile').val();
            if(!mobile) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Contact Info',
                    text: 'Please enter your mobile number.',
                    confirmButtonColor: 'var(--primary-color)'
                });
                isValid = false;
            }
        }
        
        return isValid;
    }
    
    function goToReview() {
        if(validateStep(3)) {
            // Populate Summary
            const fname = $('#fname').val();
            const mname = $('#mname').val();
            const lname = $('#lname').val();
            
            const fullName = `${fname} ${mname} ${lname}`.trim().replace(/\s+/g, ' ');
            $('#summaryName').text(fullName);
            $('#summaryEmail').text($('#email').val());
            $('#summaryMobile').text($('#mobile').val());
            $('#summaryUsername').text($('#username').val() || 'Auto-generated');
            
            // Populate OTP email
            $('#displayEmail').text($('#email').val());

            goToStep(4);
        }
    }
    
    // Password Visibility Toggle
    $('.password-toggle').on('click', function() {
        const targetId = $(this).data('target');
        const $input = $(`#${targetId}`);
        const $icon = $(this).find('iconify-icon');
        
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $icon.attr('icon', 'solar:eye-closed-bold-duotone');
        } else {
            $input.attr('type', 'password');
            $icon.attr('icon', 'solar:eye-bold-duotone');
        }
    });

    // Check Email Availability
    $('#email').on('blur', function() {
        const email = $(this).val();
        if(email && email.includes('@')) {
            $.ajax({
                url: 'endpoints/check_email.php',
                type: 'POST',
                data: JSON.stringify({ email: email }),
                contentType: 'application/json',
                success: function(response) {
                    if(response.status === 'exists') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Email Taken',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('#email').addClass('is-invalid');
                    } else {
                        $('#email').removeClass('is-invalid').addClass('is-valid');
                    }
                }
            });
        }
    });

    // Check Username Availability
    $('#username').on('blur', function() {
        const username = $(this).val();
        if(username && username.length >= 3) {
            $.ajax({
                url: 'endpoints/check_username.php',
                type: 'POST',
                data: JSON.stringify({ username: username }),
                contentType: 'application/json',
                success: function(response) {
                    if(response.status === 'exists') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Username Taken',
                            text: response.message,
                            toast: true,
                            position: 'top-end',
                            showConfirmButton: false,
                            timer: 3000
                        });
                        $('#username').addClass('is-invalid');
                    } else {
                        $('#username').removeClass('is-invalid').addClass('is-valid');
                    }
                }
            });
        }
    });

    function verifyOtp() {
        const code = $('#otpCode').val();
        if(!code || code.length < 6) {
            Swal.fire('Invalid Code', 'Please enter the 6-digit verification code.', 'warning');
            return;
        }

        console.log('--- OTP Verification Started ---');
        console.log('Payload:', { otp: code });

        const btn = $(event.target);
        const originalText = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...');

        $.ajax({
            url: 'endpoints/auth_verify_otp.php',
            type: 'POST',
            data: JSON.stringify({ otp: code }),
            contentType: 'application/json',
            success: function(response) {
                console.log('Verification Response:', response);
                if(response.status === 'success') {
                    localStorage.setItem('token', response.token);
                    Swal.fire({
                        icon: 'success',
                        title: 'Verified!',
                        text: 'Account activated successfully.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        goToStep(6);
                        setTimeout(() => {
                            window.location.href = response.redirect;
                        }, 2000);
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
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
    }
    
    function completeRegistration() {
        if(!$('#checkTerms').is(':checked') || !$('#checkPrivacy').is(':checked')) {
            Swal.fire('Agreement Required', 'Please agree to the Terms and Privacy Policy.', 'warning');
            return;
        }

        const btn = $(event.target);
        const originalText = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Sending Code...');

        const data = {
            fname: $('#fname').val(),
            mname: $('#mname').val(),
            lname: $('#lname').val(),
            email: $('#email').val(),
            password: $('#password').val(),
            mobile: $('#mobile').val(),
            role_id: 5,
            username: $('#username').val(),
            passwordHint: $('#passwordHint').val()
        };

        console.log('--- Registration Started ---');
        console.log('Payload:', data);

        $.ajax({
            url: 'endpoints/auth_register.php',
            type: 'POST',
            data: JSON.stringify(data),
            contentType: 'application/json',
            success: function(response) {
                console.log('Registration Response:', response);
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Verification Code Sent',
                        text: 'Check your email for the code.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    goToStep(5);
                    startOtpTimer(); // Start the countdown
                } else {
                    Swal.fire('Registration Error', response.message, 'error');
                    btn.prop('disabled', false);
                    btn.html(originalText);
                }
            },
            error: function() {
                Swal.fire('Error', 'An error occurred during registration.', 'error');
                btn.prop('disabled', false);
                btn.html(originalText);
            }
        });
    }
    
    // OTP Timer Logic
    let otpTimerInterval;
    function startOtpTimer() {
        clearInterval(otpTimerInterval);
        let timeLeft = 180; // 3 minutes
        
        $('#otpCountdownWrapper').show();
        $('#resendOtpBtn').hide();
        
        updateTimerDisplay(timeLeft);
        
        otpTimerInterval = setInterval(() => {
            timeLeft--;
            updateTimerDisplay(timeLeft);
            
            if (timeLeft <= 0) {
                clearInterval(otpTimerInterval);
                $('#otpCountdownWrapper').hide();
                $('#resendOtpBtn').show();
            }
        }, 1000);
    }
    
    function updateTimerDisplay(seconds) {
        const mins = Math.floor(seconds / 60);
        const secs = seconds % 60;
        $('#otpTimer').text(`${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`);
    }
    
    // Resend OTP Handler
    $('#resendOtpBtn').on('click', function() {
        const btn = $(this);
        const originalText = btn.text();
        btn.addClass('disabled').text('Sending...');
        
        $.ajax({
            url: 'endpoints/auth_resend_otp.php',
            type: 'POST',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Code Resent',
                        text: response.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                    startOtpTimer(); // Restart the timer
                } else {
                    Swal.fire('Error', response.message, 'error');
                    btn.removeClass('disabled').text(originalText);
                }
            },
            error: function() {
                Swal.fire('Error', 'Failed to resend code. Please try again.', 'error');
                btn.removeClass('disabled').text(originalText);
            }
        });
    });
    const passwordInput = document.getElementById('password');
    const strengthBar = document.getElementById('strengthBar');

    passwordInput.addEventListener('input', function() {
        const val = this.value;
        let strength = 0;
        if(val.length >= 8) strength += 25;
        if(/[A-Za-z]/.test(val) && /[0-9]/.test(val)) strength += 50;
        if(/[^A-Za-z0-9]/.test(val)) strength += 25;
        
        if(val.length < 5) strength = 10; // Minimum visual

        strengthBar.style.width = strength + '%';
        
        if(strength < 50) {
            strengthBar.style.backgroundColor = 'var(--google-red)';
        } else if(strength < 75) {
            strengthBar.style.backgroundColor = 'var(--google-yellow)';
        } else {
            strengthBar.style.backgroundColor = 'var(--google-green)';
        }
    });

  </script>
</body>

</html>