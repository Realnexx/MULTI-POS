<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Organizer Registration | AuroraTabs</title>
  <link rel="shortcut icon" type="image/png" href="../assets/images/logos/favicon.png" />
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
        width: 60%;
      }
    }

    .auth-form-wrapper {
      width: 100%;
      max-width: 600px;
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
    .password-toggle {
        position: absolute;
        right: 0.5rem;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        z-index: 10;
        color: #64748b;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
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

    /* Plan Cards */
    .plan-card {
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        padding: 1.5rem;
        cursor: pointer;
        transition: all 0.2s;
        margin-bottom: 1rem;
        position: relative;
    }
    
    .plan-card:hover {
        border-color: #94a3b8;
        background-color: #f8fafc;
    }
    
    .plan-card.selected {
        border-color: var(--primary-color);
        background-color: rgba(66, 133, 244, 0.05);
    }
    
    .plan-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.5rem;
    }
    
    .plan-name { font-weight: 700; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
    .plan-price { font-weight: 600; color: #1e293b; }
    .plan-desc { font-size: 0.9rem; color: #64748b; margin-bottom: 1rem; }
    
    .plan-features {
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.85rem;
        color: #475569;
    }
    
    .plan-features li {
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .plan-dot {
        width: 8px; 
        height: 8px; 
        border-radius: 50%;
        display: inline-block;
    }

    /* Password Strength */
    .password-strength-meter {
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
        transition: all 0.3s ease;
    }
    
    .strength-text {
        font-size: 0.75rem;
        margin-top: 0.25rem;
        color: #64748b;
        text-align: right;
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

    .btn-outline-secondary {
      border-radius: 12px;
      padding: 1rem;
      font-weight: 600;
      border: 1px solid #cbd5e1;
      color: #475569;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    /* Micro-Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(10px); }
      to { opacity: 1; transform: translateY(0); }
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
    
    /* Summary */
    .review-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    @media (min-width: 768px) {
        .review-grid {
            grid-template-columns: 1fr 1fr;
        }
    }
    
    .summary-group {
        background: #f8fafc;
        padding: 1rem;
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        margin-bottom: 1rem;
    }
    
    .summary-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #94a3b8;
        margin-bottom: 0.5rem;
    }
    
    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }
    
    .summary-label { color: #64748b; }
    .summary-val { font-weight: 500; color: #334155; text-align: right; }

    .summary-val { font-weight: 500; color: #334155; text-align: right; }

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

    /* Payment selection buttons hover fix */
    .btn-outline-primary:hover .text-dark,
    .btn-outline-primary:hover .text-muted,
    .btn-outline-primary:hover iconify-icon.text-primary,
    .btn-outline-secondary:hover .text-dark,
    .btn-outline-secondary:hover .text-muted,
    .btn-outline-secondary:hover iconify-icon.text-warning {
        color: #fff !important;
    }

    .btn-outline-primary:hover iconify-icon.text-primary,
    .btn-outline-secondary:hover iconify-icon.text-warning {
        filter: brightness(0) invert(1);
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
        <p class="fs-5 fw-light mb-0 opacity-75">From local pageants to major competitions, AuroraTabs gives you the tools to manage it all.</p>

        <div class="feature-list">
          <div class="feature-item" style="animation-delay: 0.5s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:calendar-mark-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Event Management</h3>
              <p>Host and manage multiple events with specialized settings.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.6s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:checklist-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Pro Scoring System</h3>
              <p>Automated tabulation and real-time results for your judges.</p>
            </div>
          </div>

          <div class="feature-item" style="animation-delay: 0.7s;">
            <div class="feature-icon">
              <iconify-icon icon="solar:global-bold-duotone"></iconify-icon>
            </div>
            <div class="feature-text">
              <h3>Global Reach</h3>
              <p>Stream your events and connect with a worldwide audience.</p>
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
        Organizer Registration
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
            <div class="step-dot" data-step="7"></div>
        </div>

        <form id="organizerForm" onsubmit="event.preventDefault();">
            
          <!-- STEP 1: Account Info -->
          <div class="form-step active" id="step-1">
            <h2 class="step-title">Create Your Account</h2>
            <p class="step-desc">Start by creating your Aurora Tabs admin account. This account will manage your event.</p>
            
            <div class="form-floating mb-3">
                <input type="text" class="form-control" id="fname" placeholder="First Name" required>
                <label for="fname">First Name</label>
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="mname" placeholder="Middle Name">
                        <label for="mname">Middle Name</label>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating">
                        <input type="text" class="form-control" id="lname" placeholder="Last Name" required>
                        <label for="lname">Last Name</label>
                    </div>
                </div>
            </div>
            
            <div class="form-floating mb-3">
                <input type="email" class="form-control" id="email" placeholder="Email Address" required>
                <label for="email">Email Address</label>
            </div>

               <div class="form-floating mb-3">
                <input type="text" class="form-control" id="username" placeholder="Username">
                <label for="username">Username </label>
               
            </div>
            
            <div class="row g-2 mb-3">
                <div class="col-6">
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="password" placeholder="Password" required oninput="checkStrength(this.value)">
                        <label for="password">Password</label>
                        <div class="password-toggle" data-target="password">
                            <iconify-icon icon="solar:eye-bold-duotone" width="20"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-floating position-relative">
                        <input type="password" class="form-control" id="confirmPassword" placeholder="Confirm" required>
                        <label for="confirmPassword">Confirm</label>
                        <div class="password-toggle" data-target="confirmPassword">
                            <iconify-icon icon="solar:eye-bold-duotone" width="20"></iconify-icon>
                        </div>
                    </div>
                </div>
                <div class="col-12 mt-0">
                    <div class="password-strength-meter">
                        <div class="strength-bar" id="strengthBar"></div>
                    </div>
                    <div class="strength-text" id="strengthText">Min 8 chars</div>
                </div>
            </div>
            
            <div class="form-floating mb-3">
                <input type="tel" class="form-control" id="mobile" placeholder="Mobile Number">
                <label for="mobile">Mobile Number (Optional)</label>
            </div>
            
         
            
            <div class="form-floating mb-4">
                <input type="text" class="form-control" id="orgName" placeholder="Organization">
                <label for="orgName">Organization / School Name (Optional)</label>
            </div>

            <button type="button" class="btn btn-primary w-100" onclick="goToStep(2)">
                Next: Event Details <iconify-icon icon="lucide:arrow-right"></iconify-icon>
            </button>
            <div class="text-center mt-3">
                <p class="mb-0 text-muted">Already have an account? <a href="./login-organizer.php" class="text-primary fw-bold text-decoration-none">Sign In</a></p>
            </div>
          </div>

          <!-- STEP 2: Event Details -->
          <div class="form-step" id="step-2">
             <h2 class="step-title">Event Details</h2>
             <p class="step-desc">Tell us about the event you’re organizing. Each event has its own settings.</p>

             <div class="form-floating mb-3">
                <input type="text" class="form-control" id="eventName" placeholder="Event Name" required>
                <label for="eventName">Event Name</label>
             </div>

             <div class="form-floating mb-3">
                <select class="form-select" id="eventType" aria-label="Event Type">
                  <option selected>Pageant</option>
                  <option value="1">Awards</option>
                  <option value="2">Competition</option>
                  <option value="3">School Event</option>
                  <option value="4">Other</option>
                </select>
                <label for="eventType">Event Type</label>
             </div>
             
             <div class="row g-2 mb-3">
                 <div class="col-6">
                     <div class="form-floating">
                        <input type="date" class="form-control" id="startDate" placeholder="Start Date" required>
                        <label for="startDate">Start Date</label>
                     </div>
                 </div>
                 <div class="col-6">
                     <div class="form-floating">
                        <input type="date" class="form-control" id="endDate" placeholder="End Date">
                        <label for="endDate">End Date</label>
                     </div>
                 </div>
             </div>

             <div class="form-floating mb-3">
                <input type="text" class="form-control" id="eventLocation" placeholder="Location" required>
                <label for="eventLocation">Event Location (City / Venue)</label>
             </div>
             
             <div class="form-floating mb-4">
                <textarea class="form-control" placeholder="Description" id="eventDesc" style="height: 100px"></textarea>
                <label for="eventDesc">Event Description (optional)</label>
             </div>

             <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary w-50" onclick="goToStep(1)">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
                <button type="button" class="btn btn-primary w-50" onclick="goToStep(3)">
                    Next: Choose a Plan <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </button>
             </div>
          </div>

          <!-- STEP 3: Choose Plan -->
          <div class="form-step" id="step-3">
            <h2 class="step-title">Choose Your Event Plan</h2>
            <p class="step-desc">Select the plan that best fits your event size. You can upgrade anytime.</p>
            
            <input type="hidden" id="selectedPlan" value="starter">
            
            <!-- Starter -->
            <div class="plan-card" onclick="selectPlan('starter', this)">
                <div class="plan-header">
                    <div class="plan-name">
                        <span class="plan-dot" style="background: var(--google-green);"></span> Starter
                    </div>
                    <div class="plan-price">₱0 / event</div>
                </div>
                <div class="plan-desc">Perfect for small community events and trials</div>
                <ul class="plan-features">
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Up to 10 Candidates</li>
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Up to 3 Judges</li>
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> QR Code Voting</li>
                </ul>
            </div>

            <!-- Pro -->
            <div class="plan-card selected" onclick="selectPlan('pro', this)">
                <div class="plan-header">
                    <div class="plan-name">
                        <span class="plan-dot" style="background: var(--google-blue);"></span> Pro
                    </div>
                    <div class="plan-price">₱2,500 / event</div>
                </div>
                <div class="plan-desc">Professional features for major competitions</div>
                <ul class="plan-features">
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Unlimited Candidates</li>
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Up to 10 Judges</li>
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Advanced Analytics & Exports</li>
                </ul>
            </div>
            
            <!-- Enterprise -->
            <div class="plan-card" onclick="selectPlan('enterprise', this)">
                <div class="plan-header">
                    <div class="plan-name">
                        <span class="plan-dot" style="background: var(--google-red);"></span> Enterprise
                    </div>
                    <div class="plan-price">₱7,500 / year</div>
                </div>
                 <div class="plan-desc">For agencies and venues with multiple events</div>
                 <ul class="plan-features">
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Unlimited Everything</li>
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> White-label Branding</li>
                    <li><iconify-icon icon="lucide:check" class="text-success"></iconify-icon> Custom Formula Builder</li>
                </ul>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary w-50" onclick="goToStep(2)">
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
                <button type="button" class="btn btn-primary w-50" onclick="goToReview()">
                    Next: Review <iconify-icon icon="lucide:arrow-right"></iconify-icon>
                </button>
            </div>
          </div>

          <!-- STEP 4: Review -->
          <div class="form-step" id="step-4">
            <h2 class="step-title">Review Your Details</h2>
            <p class="step-desc">Please double check your details before creating your account.</p>
            
            <div class="review-grid">
                <div class="summary-group">
                    <div class="summary-title">Account Info</div>
                    <div class="summary-row">
                        <span class="summary-label">Name</span>
                        <span class="summary-val" id="revName">--</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Email</span>
                        <span class="summary-val" id="revEmail">--</span>
                    </div>
                </div>
                
                <div class="summary-group">
                    <div class="summary-title">Selected Plan</div>
                    <div class="summary-row">
                        <span class="summary-label">Plan</span>
                        <span class="summary-val text-primary text-uppercase" id="revPlan">--</span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Cost</span>
                        <span class="summary-val" id="revCost">--</span>
                    </div>
                </div>
            </div>
            
            <div class="summary-group">
                <div class="summary-title">Event Info</div>
                 <div class="summary-row">
                    <span class="summary-label">Event Name</span>
                    <span class="summary-val" id="revEvent">--</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Date</span>
                    <span class="summary-val" id="revDate">--</span>
                </div>
                 <div class="summary-row">
                    <span class="summary-label">Location</span>
                    <span class="summary-val" id="revLoc">--</span>
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
                    Next: Verify Email <iconify-icon icon="lucide:arrow-right"></iconify-icon>
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
                    Verify & Proceed <iconify-icon icon="lucide:check-circle"></iconify-icon>
                </button>
            </div>
          </div>

          <!-- STEP 6: Payment -->
          <div class="form-step" id="step-6">
             <h2 class="step-title">Payment Method</h2>
             <p class="step-desc">Secure your event plan by completing your payment.</p>
             
             <div class="d-grid gap-3 mb-4">
                 <button type="button" class="btn btn-outline-primary p-3 d-flex align-items-center justify-content-between" onclick="completePayment('gcash')">
                     <div class="d-flex align-items-center gap-3">
                         <iconify-icon icon="logos:gcash" width="32"></iconify-icon>
                         <div class="text-start">
                             <div class="fw-bold text-dark">Pay with GCash</div>
                             <small class="text-muted">Instant activation</small>
                         </div>
                     </div>
                     <iconify-icon icon="lucide:chevron-right" class="text-primary"></iconify-icon>
                 </button>
                 
                 <div class="text-center position-relative my-2">
                     <hr class="text-muted opacity-25">
                     <span class="position-absolute top-50 start-50 translate-middle bg-white px-3 text-muted small">OR</span>
                 </div>
                 
                 <button type="button" class="btn btn-outline-secondary p-3 d-flex align-items-center justify-content-between" onclick="completePayment('later')">
                     <div class="d-flex align-items-center gap-3">
                         <iconify-icon icon="solar:clock-circle-bold-duotone" width="32" class="text-warning"></iconify-icon>
                         <div class="text-start">
                             <div class="fw-bold text-dark">Pay Later</div>
                             <small class="text-muted">Activate after manual verification</small>
                         </div>
                     </div>
                     <iconify-icon icon="lucide:chevron-right"></iconify-icon>
                 </button>
             </div>
             
             <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary w-100" onclick="goToStep(5)"> <!-- Back to OTP, not Review -->
                    <iconify-icon icon="lucide:arrow-left"></iconify-icon> Back
                </button>
             </div>
          </div>

          <!-- STEP 7: Success -->
          <div class="form-step text-center" id="step-7">
             <div class="mt-4">
                 <iconify-icon icon="solar:confetti-bold-duotone" class="success-icon"></iconify-icon>
                 <h2 class="step-title mb-3">Your Event Is Ready!</h2>
                 <p class="text-muted mb-4 fs-5">Your Aurora Tabs account and event have been successfully created.</p>
                 
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
            if(!validateStep(currentStep)) return;
        }
        
        $('.form-step').removeClass('active');
        $(`#step-${step}`).addClass('active');
        
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
        
        if(step === 7) {
            $('#stepIndicator').css('opacity', '0');
        }
    }
    
    function checkStrength(password) {
        const $bar = $('#strengthBar');
        const $text = $('#strengthText');
        let strength = 0;
        
        if (password.length >= 8) strength += 25;
        if (password.match(/[a-z]+/)) strength += 25;
        if (password.match(/[A-Z]+/)) strength += 25;
        if (password.match(/[0-9]+/) || password.match(/[$@#&!]+/)) strength += 25;

        $bar.css('width', strength + '%');
        
        if (strength <= 25) {
            $bar.css('background-color', '#ef4444');
            $text.text('Weak');
        } else if (strength <= 50) {
            $bar.css('background-color', '#eab308');
            $text.text('Fair');
        } else if (strength <= 75) {
            $bar.css('background-color', '#3b82f6');
            $text.text('Good');
        } else {
            $bar.css('background-color', '#22c55e');
            $text.text('Strong');
        }
    }
    
    function validateStep(step) {
        if(step === 1) {
            const fname = $('#fname').val();
            const lname = $('#lname').val();
            const email = $('#email').val();
            const password = $('#password').val();
            const confirm = $('#confirmPassword').val();
             
             if(!fname || !lname || !email || !password || !confirm) {
                 Swal.fire({
                     icon: 'warning',
                     title: 'Required Fields',
                     text: 'Please fill in all default required fields.',
                     confirmButtonColor: 'var(--primary-color)'
                 });
                 return false;
             }
             if(password !== confirm) {
                 Swal.fire({
                     icon: 'error',
                     title: 'Mismatch',
                     text: 'Passwords do not match.',
                     confirmButtonColor: 'var(--primary-color)'
                 });
                 return false;
             }
        }
        if(step === 2) {
             const ename = $('#eventName').val();
             const sdate = $('#startDate').val();
             const loc = $('#eventLocation').val();
             if(!ename || !sdate || !loc) {
                 Swal.fire({
                     icon: 'info',
                     title: 'Event Details',
                     text: 'Please fill in event name, date, and location.',
                     confirmButtonColor: 'var(--primary-color)'
                 });
                 return false;
             }
        }
        return true;
    }
    
    function selectPlan(plan, card) {
        $('#selectedPlan').val(plan);
        $('.plan-card').removeClass('selected');
        $(card).addClass('selected');
    }
    
    function goToReview() {
        if(validateStep(2)) { // Ensure step 2 was valid
            // Populate review
            const fname = $('#fname').val();
            const mname = $('#mname').val();
            const lname = $('#lname').val();
            $('#revName').text(`${fname} ${mname} ${lname}`.trim().replace(/\s+/g, ' '));
            
            $('#revEmail').text($('#email').val());
            
            $('#revEvent').text($('#eventName').val());
            $('#revDate').text($('#startDate').val());
            $('#revLoc').text($('#eventLocation').val());
            
            const plan = $('#selectedPlan').val();
            $('#revPlan').text(plan.charAt(0).toUpperCase() + plan.slice(1));
            
            let cost = '₱0';
            if(plan === 'pro') cost = '₱2,500';
            if(plan === 'enterprise') cost = '₱7,500';
            $('#revCost').text(cost);
            
            // Populate email for OTP
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
            Swal.fire('Error', 'Please enter the 6-digit code.', 'warning');
            return;
        }

        const btn = $(event.target);
        const originalText = btn.html();
        btn.prop('disabled', true);
        btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Verifying...');

        console.log('--- OTP Verification Started ---');
        console.log('Payload:', { otp: code });

        $.ajax({
            url: 'endpoints/auth_verify_otp.php',
            type: 'POST',
            data: JSON.stringify({ otp: code }),
            contentType: 'application/json',
            success: function(response) {
                console.log('Verification Response:', response);
                if(response.status === 'success') {
                    localStorage.setItem('token', response.token);
                    
                    // Store competition data for payment
                    if(response.comp_id) {
                        sessionStorage.setItem('comp_id', response.comp_id);
                        sessionStorage.setItem('plan', response.plan);
                    }
                    
                    Swal.fire({
                        icon: 'success',
                        title: 'Verified!',
                        text: 'Account activated. Please complete your setup.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        goToStep(6);
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
            username: $('#username').val(),
            password: $('#password').val(),
            mobile: $('#mobile').val(),
            orgName: $('#orgName').val(),
            eventName: $('#eventName').val(),
            eventType: $('#eventType').find(':selected').text(),
            startDate: $('#startDate').val(),
            endDate: $('#endDate').val(),
            eventLocation: $('#eventLocation').val(),
            selectedPlan: $('#selectedPlan').val(),
            role_id: 2 // Organizer
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

    function completePayment(method) {
        const comp_id = sessionStorage.getItem('comp_id');
        const plan = sessionStorage.getItem('plan') || 'starter';
        
        if(!comp_id) {
            Swal.fire('Error', 'Competition ID not found. Please try again.', 'error');
            return;
        }
        
        if(method === 'gcash') {
            // Redirect to GCash payment page
            window.location.href = `pay-gcash.php?plan=${plan}&comp_id=${comp_id}`;
        } else {
            // Pay Later - Update DB via AJAX
            Swal.fire({
                title: 'Processing...',
                text: 'Setting up your account...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });
            
            $.ajax({
                url: 'endpoints/auth_process_payment.php',
                type: 'POST',
                data: JSON.stringify({
                    comp_id: comp_id,
                    method: 'later',
                    amount: 0
                }),
                contentType: 'application/json',
                success: function(response) {
                    if(response.status === 'success') {
                        Swal.close();
                        goToStep(7);
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'Failed to process payment. Please try again.', 'error');
                }
            });
        }
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
  </script>
</body>

</html>
