<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>OJT Attendance Tracker | AuroraTabs</title>
  <meta name="description" content="OJT Hours Tracking System">
  <meta name="keywords" content="OJT, Tracker, Attendance, Internship">

  <!-- Favicons -->
  <link href="public/assets/img/aurora-logo.png" rel="icon">
  <link href="public/assets/img/aurora-logo.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@100..900&family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="public/assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="public/assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="public/assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="public/assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="public/assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="public/assets/css/main.css" rel="stylesheet">
  
  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    :root {
      --primary-c: #4f46e5;
      --primary-light: #e0e7ff;
      --text-main: #1e293b;
      --text-muted: #64748b;
      --bg-surface: #ffffff;
      --bg-main: #f8fafc;
      --radius-xl: 24px;
      --radius-lg: 16px;
      --radius-md: 12px;
      --shadow-sm: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
      --shadow-lg: 0 20px 40px -4px rgba(0, 0, 0, 0.08); /* Softer, deeper shadow */
    }
    
    body {
        font-family: 'Outfit', sans-serif;
        background-color: var(--bg-main);
        color: var(--text-main);
        height: auto !important;
        overflow-y: auto !important;
        overflow-x: hidden;
    }

    /* Navbar Modernization */
    .header {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(12px);
        box-shadow: 0 1px 0 rgba(0,0,0,0.05);
    }
    
    /* Hero Section - Minimal & Clean */
    .hero {
        background-color: var(--bg-main);
        background-image: 
            radial-gradient(at 0% 0%, rgba(79, 70, 229, 0.05) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(6, 182, 212, 0.05) 0px, transparent 50%);
        padding: 120px 0 60px 0;
        min-height: 90vh; /* Full viewport mostly */
        display: flex;
        align-items: center;
    }
    
    .hero h1 {
        font-weight: 700;
        color: #0f172a;
        letter-spacing: -1px;
        margin-bottom: 1.5rem;
    }
    
    .hero p {
        font-weight: 400;
        color: var(--text-muted);
        font-size: 1.15rem;
        line-height: 1.7;
        max-width: 90%;
    }

    /* Tracker Card - The Centerpiece */
    .tracker-card {
        background: var(--bg-surface);
        border-radius: var(--radius-xl);
        padding: 3rem;
        box-shadow: var(--shadow-lg);
        border: 1px solid rgba(255,255,255,0.5);
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .tracker-card:hover {
        transform: translateY(-5px);
    }
    
    /* Elegant Clock */
    .clock-widget {
        text-align: center;
        margin-bottom: 2.5rem;
        position: relative;
    }
    
    .digital-clock {
        font-family: 'Outfit', sans-serif;
        font-size: 4rem;
        font-weight: 200; /* Thin, elegant weight */
        color: var(--text-main);
        line-height: 1;
        letter-spacing: -2px;
        font-variant-numeric: tabular-nums;
    }
    
    .date-display {
        color: var(--text-muted);
        font-size: 1rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-top: 0.5rem;
    }

    /* Modern Input */
    .form-group {
        position: relative;
    }

    .student-id-input {
        background: #f1f5f9;
        font-size: 1.35rem;
        padding: 1.25rem;
        border-radius: var(--radius-lg);
        border: 2px solid transparent;
        text-align: center;
        letter-spacing: 1.5px;
        font-weight: 600;
        color: var(--text-main);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    
    .student-id-input:focus {
        background: #fff;
        border-color: var(--primary-c);
        box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        outline: none;
    }
    
    .student-id-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
        letter-spacing: normal;
        font-size: 1.1rem;
    }

    /* Button Grid - Minimalist Icons */
    .action-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .btn-tracker {
        padding: 1.25rem;
        border-radius: var(--radius-lg);
        font-weight: 500;
        font-size: 0.95rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        transition: all 0.2s;
        border: 1px solid transparent;
        cursor: pointer;
        position: relative;
    }
    
    .btn-tracker i {
        font-size: 1.75rem;
        margin-bottom: -0.25rem;
        transition: transform 0.2s;
    }
    
    .btn-tracker:hover i {
        transform: scale(1.1);
    }

    /* Refined Button Color Schemes (Pastel/Soft with Strong Icons) */
    .btn-in { 
        background: #f0fdf4; 
        color: #166534; 
        border-color: #dcfce7;
    }
    .btn-in:hover { background: #dcfce7; transform: translateY(-2px); }
    
    .btn-lout { 
        background: #fffbeb; 
        color: #92400e; 
        border-color: #fef3c7; 
    }
    .btn-lout:hover { background: #fef3c7; transform: translateY(-2px); }
    
    .btn-lin { 
        background: #eff6ff; 
        color: #1e40af; 
        border-color: #dbeafe; 
    }
    .btn-lin:hover { background: #dbeafe; transform: translateY(-2px); }
    
    .btn-out { 
        background: #fef2f2; 
        color: #991b1b; 
        border-color: #fee2e2; 
    }
    .btn-out:hover { background: #fee2e2; transform: translateY(-2px); }

    /* Student Welcome Banner */
    .student-welcome {
        display: none;
        background: #4f46e5;
        color: white;
        padding: 1rem;
        border-radius: var(--radius-lg);
        margin-bottom: 1.5rem;
        text-align: center;
        box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.3);
    }
    
    .student-welcome.active {
        display: block;
        animation: slideDown 0.4s ease;
    }
    
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    /* Info Card Enhancements */
    .info-card-minimal {
        background: white;
        padding: 1.5rem;
        border-radius: var(--radius-lg);
        border: 1px solid #f1f5f9;
        margin-top: 2rem;
    }
    .info-card-minimal li {
        margin-bottom: 0.75rem;
        color: var(--text-muted);
        font-size: 0.95rem;
    }
    
    /* Footer Design System - High Contrast & Modern */
    .footer {
        background-color: #ffffff !important; /* Force white background */
        border-top: 1px solid #e2e8f0;
        padding-top: 4rem;
        padding-bottom: 2rem;
        color: #334155 !important; /* Slate-700 for good readability */
        font-size: 0.95rem;
    }

    .footer .footer-top {
        margin-bottom: 2rem;
    }
    
    .footer strong {
        color: #0f172a; /* Slate-900 for emphasis */
    }

    /* Headings */
    .footer h4 {
        font-size: 1.1rem;
        font-weight: 700;
        margin-bottom: 1.5rem;
        color: #0f172a !important; /* Slate-900 (Nearly Black) */
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    /* Links List */
    .footer ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .footer ul li {
        padding-bottom: 12px;
        display: flex;
        align-items: center;
    }
    
    .footer ul li i {
        font-size: 0.8rem;
        margin-right: 10px;
        color: #4f46e5; /* Primary Brand Color */
    }
    
    .footer ul li a {
        color: #475569 !important; /* Slate-600 */
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-block;
    }
    
    .footer ul li a:hover {
        color: #4f46e5 !important;
        transform: translateX(5px);
    }
    
    /* Content Text */
    .footer p {
        color: #475569 !important;
        line-height: 1.6;
        margin-bottom: 1rem;
    }
    
    /* Contact Info Special Styling */
    .footer-contact p {
        margin-bottom: 0.5rem;
    }

    /* Social Icons */
    .footer .social-links {
        margin-top: 1.5rem;
    }

    .footer .social-links a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 44px;
        height: 44px;
        border-radius: 12px; /* Modern squircle */
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
        font-size: 1.1rem;
        color: #4f46e5 !important;
        margin-right: 12px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
    }
    
    .footer .social-links a:hover {
        color: white !important;
        background-color: #4f46e5;
        border-color: #4f46e5;
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
    }
    
    /* Copyright Section */
    .footer .copyright {
        margin-top: 3rem;
        padding-top: 2rem;
        border-top: 1px solid #e2e8f0;
        color: #64748b; /* Slate-500 */
        font-size: 0.9rem;
    }
    
    .footer .credits {
        font-size: 0.85rem;
        margin-top: 0.5rem;
        color: #94a3b8;
    }
    .footer .credits a {
        color: #64748b;
        text-decoration: none;
        font-weight: 500;
    }

  </style>
</head>

<body class="index-page">

  <header id="header" class="header d-flex align-items-center fixed-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="public/assets/img/aurora-logo.png" alt="">
        <h1 class="sitename" style="color: #0f172a;">Aurora Tabs</h1>
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="#hero" class="active">Home</a></li>
          <li><a href="#about">About System</a></li>
          <li><a href="#faq">F.A.Q</a></li>
          <li><a href="#contact">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted flex-md-shrink-0" href="auth/login-admin.php">Admin Login</a>

    </div>
  </header>

  <main class="main">

    <!-- Hero Section -->
    <section id="hero" class="hero section">
      <div class="container">
        <div class="row gy-4 align-items-center">
          
          <!-- Left Column: Content -->
          <div class="col-lg-6 order-2 order-lg-1 d-flex flex-column justify-content-center">
            <h1 data-aos="fade-up">OJT Attendance Tracker</h1>
            <p data-aos="fade-up" data-aos-delay="100">
              Welcome to the official OJT Hour Tracking Kiosk. 
              Efficiently record your daily attendance, monitor your breaks, and ensure your internship hours are accurately calculated.
            </p>
            <div data-aos="fade-up" data-aos-delay="200">
              <div class="d-flex gap-3">
                <a href="#about" class="btn-get-started">How it works <i class="bi bi-arrow-down"></i></a>
              </div>
            </div>
            
            <div class="info-card-minimal" style="max-width: 400px;" data-aos="fade-up" data-aos-delay="300">
               <strong class="d-block text-main mb-3"><i class="bi bi-info-circle me-2 text-primary"></i>Daily Reminders</strong>
               <ul class="list-unstyled mb-0">
                 <li><i class="bi bi-dot"></i> Time-in before 8:00 AM to avoid being late.</li>
                 <li><i class="bi bi-dot"></i> Lunch break is automatically deducted (12-1 PM).</li>
                 <li><i class="bi bi-dot"></i> Always verify your name before confirming.</li>
               </ul>
            </div>
          </div>
          
          <!-- Right Column: Tracker Interface -->
          <div class="col-lg-6 order-1 order-lg-2" data-aos="zoom-in" data-aos-delay="100">
            <div class="tracker-card">
                
                <div class="clock-widget">
                    <div id="clock" class="digital-clock">00:00:00</div>
                    <div id="date" class="date-display">Monday, January 1, 2024</div>
                </div>

                <div id="studentWelcome" class="student-welcome">
                    Welcome back, <strong id="studentName">Student</strong>!
                </div>

                <div class="form-group mb-3">
                    <input type="text" id="studentId" class="form-control student-id-input" placeholder="Scan or Enter Student ID" autocomplete="off" autofocus>
                </div>

                <div class="action-grid">
                    <button class="btn-tracker btn-in" onclick="recordAttendance('check_in')">
                        <i class="bi bi-box-arrow-in-right"></i> Time In
                    </button>
                    <button class="btn-tracker btn-lout" onclick="recordAttendance('break_out')">
                        <i class="bi bi-cup-hot"></i> Lunch Out
                    </button>
                    <button class="btn-tracker btn-lin" onclick="recordAttendance('break_in')">
                        <i class="bi bi-box-arrow-in-left"></i> Lunch In
                    </button>
                    <button class="btn-tracker btn-out" onclick="recordAttendance('check_out')">
                        <i class="bi bi-box-arrow-right"></i> Time Out
                    </button>
                </div>
                
                <div class="text-center mt-3">
                    <small class="text-muted">System Time: <span id="server-indicator">Syncing...</span></small>
                </div>
            </div>
          </div>
          
        </div>
      </div>
    </section>

    <!-- About Section (Simplified) -->
    <section id="about" class="about section">
      <div class="container" data-aos="fade-up">
        <div class="section-title">
            <h2>About</h2>
            <p>Attendance Guidelines</p>
        </div>
        
        <div class="row">
             <div class="col-lg-6" data-aos="fade-right">
                <h3>Accurate Hour Tracking</h3>
                <p>The Aurora OJT Tracker ensures that every minute of your internship is accounted for. The system automatically calculates total hours based on your time-in and time-out records, deducting the mandatory one-hour lunch break.</p>
                <ul>
                    <li><i class="bi bi-check-circle text-primary"></i> <strong>AM Session:</strong> 8:00 AM - 12:00 PM</li>
                    <li><i class="bi bi-check-circle text-primary"></i> <strong>PM Session:</strong> 1:00 PM - 5:00 PM</li>
                    <li><i class="bi bi-check-circle text-primary"></i> <strong>Max Daily Hours:</strong> 8 Hours</li>
                </ul>
             </div>
             <div class="col-lg-6" data-aos="fade-left">
                <h3>Common Statuses</h3>
                <p>Your attendance records will be tagged with one of the following statuses:</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <h5 class="text-success">Present</h5>
                            <small>On-time arrival for both morning and afternoon sessions.</small>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 border rounded">
                            <h5 class="text-warning">Late</h5>
                            <small>Arrival after 8:15 AM (Morning) or 1:15 PM (Afternoon).</small>
                        </div>
                    </div>
                </div>
             </div>
        </div>
      </div>
    </section>

    <!-- FAQ Section (Kept from original but reduced/modified if needed, just keeping structure for now) -->
    <section id="faq" class="faq section bg-light">
      <div class="container section-title" data-aos="fade-up">
        <h2>F.A.Q</h2>
        <p>Frequently Asked Questions</p>
      </div>

      <div class="container">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <div class="faq-container">
              <div class="faq-item faq-active">
                <h3>What if I forget to time out?</h3>
                <div class="faq-content">
                  <p>Please contact your OJT Coordinator immediately to manually adjust your record. Consistent failure to time out may result in loss of tracked hours.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>
              <div class="faq-item">
                <h3>How do I view my total accumulated hours?</h3>
                <div class="faq-content">
                  <p>You can request a generated report from the OJT Admin dashboard, or check the weekly summary posted by your coordinator.</p>
                </div>
                <i class="faq-toggle bi bi-chevron-right"></i>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Contact</h2>
        <p>Support & Inquiries</p>
      </div>
      <div class="container" data-aos="fade-up">
         <div class="row gy-4">
            <div class="col-md-6">
                <div class="info-item border p-4 rounded text-center">
                    <i class="bi bi-envelope fs-2 text-primary"></i>
                    <h3 class="mt-3">Email Support</h3>
                    <p>support@auroratabs.com</p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-item border p-4 rounded text-center">
                    <i class="bi bi-telephone fs-2 text-primary"></i>
                    <h3 class="mt-3">OJT Coordinator</h3>
                    <p>+63 912 345 6789</p>
                </div>
            </div>
         </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="d-flex align-items-center">
            <span class="sitename" style="color: #0f172a;">Aurora Tabs</span>
          </a>
          <div class="footer-contact pt-3">
            <p>Aurora Tabs Headquarters</p>
            <p>Union, Bohol, Philippines</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+63 9367613275</span></p>
            <p class="mt-3"><strong>Email:</strong> <span>reyalsolution@gmail.com</span></p>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#hero">Home</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#about">About System</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#faq">F.A.Q</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#contact">Contact</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Terms of service</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Privacy policy</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Services</h4>
          <ul>
            <li><i class="bi bi-chevron-right"></i> <a href="#">OJT Tracking</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Attendance Management</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Report Generation</a></li>
            <li><i class="bi bi-chevron-right"></i> <a href="#">Student Analytics</a></li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12">
          <h4>Follow Us</h4>
          <p>Stay connected with us on social media for real-time updates and community highlights.</p>
          <div class="social-links d-flex">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

      </div>
    </div>

    <div class="container copyright text-center mt-4">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Aurora Tabs</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        Designed by <a href="https://bootstrapmade.com/">BootstrapMade @ Reyal Solutions</a>
      </div>
    </div>
  </footer>

  <!-- Scroll Top -->
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <!-- Vendor JS Files -->
  <script src="public/assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="public/assets/vendor/aos/aos.js"></script>
  <script src="public/assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="public/assets/vendor/swiper/swiper-bundle.min.js"></script>

  <!-- Main JS File -->
  <script src="public/assets/js/main.js"></script>

  <!-- Tracker Logic -->
  <script>
    // --- Clock Functionality ---
    function updateClock() {
      const now = new Date();
      
      // Time
      let hours = now.getHours();
      const minutes = now.getMinutes();
      const seconds = now.getSeconds();
      const ampm = hours >= 12 ? 'PM' : 'AM';
      hours = hours % 12;
      hours = hours ? hours : 12;
      
      const pad = (num) => num.toString().padStart(2, '0');
      const strTime = `${hours}:${pad(minutes)}:${pad(seconds)} ${ampm}`;
      
      const clockEl = document.getElementById('clock');
      if(clockEl) clockEl.textContent = strTime;
      
      // Date
      const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
      const dateEl = document.getElementById('date');
      if(dateEl) dateEl.textContent = now.toLocaleDateString('en-US', options);
      
      document.getElementById('server-indicator').textContent = 'Online';
      document.getElementById('server-indicator').style.color = '#198754';
    }
    
    setInterval(updateClock, 1000);
    updateClock();

    // --- Interaction Logic ---
    const studentInput = document.getElementById('studentId');
    const studentWelcome = document.getElementById('studentWelcome');
    const studentNameDisplay = document.getElementById('studentName');
    let typingTimer;

    // Focus input on load
    window.onload = function() {
        if(studentInput) studentInput.focus();
    };

    if(studentInput) {
        // Handle Input - Fetch Name on Pause
        studentInput.addEventListener('input', function() {
            clearTimeout(typingTimer);
            studentWelcome.classList.remove('active');
            
            if (this.value.length >= 2) {
                typingTimer = setTimeout(fetchStudentName, 500);
            }
        });

        // Allow Enter key
        studentInput.addEventListener('keypress', function(e) {
          if (e.key === 'Enter') {
             if(this.value.length > 0) {
                 fetchStudentName();
             }
          }
        });
    }

    function fetchStudentName() {
        const id = studentInput.value;
        if(!id) return;

        const formData = new FormData();
        formData.append('action', 'get_student_info');
        formData.append('student_id', id);

        fetch('endpoints/tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                studentNameDisplay.textContent = data.data.fullname;
                studentWelcome.classList.add('active');
            } else {
                studentWelcome.classList.remove('active');
            }
        })
        .catch(err => console.error(err));
    }

    function recordAttendance(type) {
        const id = studentInput.value;
        
        if (!id) {
            Swal.fire({
                icon: 'warning',
                title: 'ID Required',
                text: 'Please scan or enter your Student ID first.',
                confirmButtonColor: '#4154f1'
            });
            setTimeout(() => studentInput.focus(), 500);
            return;
        }

        const btnLabels = {
            'check_in': 'Time In',
            'break_out': 'Lunch Out',
            'break_in': 'Lunch In',
            'check_out': 'Time Out'
        };

        // Show loading state
        Swal.fire({
            title: 'Processing...',
            text: 'Recording your attendance',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData();
        formData.append('action', 'record_attendance');
        formData.append('student_id', id);
        formData.append('type', type);

        fetch('endpoints/tracker.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: btnLabels[type] + ' Success',
                    text: data.message,
                    timer: 3000,
                    showConfirmButton: false
                });
                // Clear input after success
                studentInput.value = '';
                studentWelcome.classList.remove('active');
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message,
                    confirmButtonColor: '#dc3545'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'System Error',
                text: 'Could not connect to the server.'
            });
        });
    }
  </script>

</body>

</html>