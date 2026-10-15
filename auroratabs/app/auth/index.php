<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Welcome | AuroraTabs</title>
  <link rel="shortcut icon" type="image/png" href="../admin/src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../admin/src/assets/css/styles.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      background-color: #f8fafc;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    
    /* Dynamic background */
    body::before {
        content: '';
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        background: 
            radial-gradient(circle at 10% 10%, rgba(66, 133, 244, 0.08), transparent 40%),
            radial-gradient(circle at 90% 90%, rgba(52, 168, 83, 0.08), transparent 40%);
        z-index: -1;
    }

    .portal-container {
      width: 100%;
      max-width: 1000px;
      padding: 2rem;
    }
    
    .hero-text {
        text-align: center;
        margin-bottom: 4rem;
        animation: fadeInUp 0.8s ease-out;
    }
    
    .hero-title {
        font-weight: 800;
        font-size: 3.5rem;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 1rem;
        letter-spacing: -1px;
    }
    
    .hero-subtitle {
        font-size: 1.25rem;
        color: #64748b;
        max-width: 600px;
        margin: 0 auto;
    }

    .role-cards {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 2rem;
        margin-bottom: 4rem;
    }

    .role-card {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 32px;
        padding: 3rem;
        text-align: center;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        color: inherit;
        box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.05);
        opacity: 0;
        animation: fadeInUp 0.8s ease-out forwards;
    }
    
    .role-card:nth-child(1) { animation-delay: 0.2s; }
    .role-card:nth-child(2) { animation-delay: 0.3s; }

    .role-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 30px 60px -12px rgba(66, 133, 244, 0.15);
        background: rgba(255, 255, 255, 0.95);
        border-color: white;
    }
    
    .role-icon {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        transition: transform 0.3s ease;
    }
    
    .role-card:hover .role-icon {
        transform: scale(1.1) rotate(5deg);
    }
    
    .icon-audience { background: rgba(52, 168, 83, 0.1); color: var(--google-green); }
    .icon-organizer { background: rgba(66, 133, 244, 0.1); color: var(--google-blue); }
    .icon-login { background: rgba(234, 67, 53, 0.1); color: var(--google-red); }

    .role-title {
        font-weight: 700;
        font-size: 1.5rem;
        margin-bottom: 0.5rem;
        color: #1e293b;
    }
    
    .role-desc {
        color: #64748b;
        font-size: 0.95rem;
        margin-bottom: 1.5rem;
        line-height: 1.5;
    }
    
    .role-action {
        color: var(--primary-color);
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .admin-link-container {
        text-align: center;
        margin-top: 3rem;
        opacity: 0;
        animation: fadeIn 1s ease-out 1s forwards;
    }
    
    .admin-link {
        color: #cbd5e1;
        font-size: 0.8rem;
        text-decoration: none;
        transition: color 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }
    
    .admin-link:hover {
        color: #94a3b8;
    }

    /* Micro-Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Floating shapes background - Updated */
    .bg-shape {
        position: absolute;
        z-index: -1;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.4;
        animation: float 25s infinite alternate;
    }
    
    .shape-1 {
        width: 600px;
        height: 600px;
        background: linear-gradient(135deg, #e0c3fc 0%, #8ec5fc 100%);
        top: -200px;
        right: -200px;
    }
    
    .shape-2 {
        width: 500px;
        height: 500px;
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        bottom: -100px;
        left: -100px;
        animation-delay: -5s;
    }
    
    @keyframes float {
        0% { transform: translate(0, 0); }
        100% { transform: translate(30px, 30px); }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .portal-container {
            padding: 1.5rem;
        }
        
        .hero-text {
            margin-bottom: 2.5rem;
        }
        
        .hero-title {
            font-size: 2.25rem;
            letter-spacing: -0.5px;
        }
        
        .hero-subtitle {
            font-size: 1rem;
        }
        
        .role-cards {
            grid-template-columns: 1fr;
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }
        
        .role-card {
            padding: 2rem;
            border-radius: 24px;
        }
        
        .role-icon {
            width: 64px;
            height: 64px;
            font-size: 2rem;
        }
        
        .role-title {
            font-size: 1.25rem;
        }
        
        .role-desc {
            font-size: 0.9rem;
        }
        
        .bg-shape {
            filter: blur(80px);
        }
        
        .shape-1 {
            width: 400px;
            height: 400px;
            top: -150px;
            right: -150px;
        }
        
        .shape-2 {
            width: 350px;
            height: 350px;
            bottom: -80px;
            left: -80px;
        }
        
        .admin-link-container {
            margin-top: 2rem;
        }
    }
    
    @media (max-width: 480px) {
        .portal-container {
            padding: 1rem;
        }
        
        .hero-title {
            font-size: 1.75rem;
        }
        
        .hero-subtitle {
            font-size: 0.95rem;
        }
        
        .role-card {
            padding: 1.5rem;
            border-radius: 20px;
        }
        
        .role-icon {
            width: 56px;
            height: 56px;
            font-size: 1.75rem;
            margin-bottom: 1rem;
        }
        
        .role-title {
            font-size: 1.15rem;
        }
        
        .btn {
            padding: 0.75rem 1rem !important;
            font-size: 0.9rem !important;
        }
        
        .bg-shape {
            display: none;
        }
    }
    
    /* Tablet specific */
    @media (min-width: 769px) and (max-width: 1024px) {
        .portal-container {
            max-width: 900px;
            padding: 1.75rem;
        }
        
        .hero-title {
            font-size: 3rem;
        }
        
        .role-cards {
            gap: 1.75rem;
        }
        
        .role-card {
            padding: 2.5rem;
        }
    }

  </style>
</head>

<body>
  
  <div class="bg-shape shape-1"></div>
  <div class="bg-shape shape-2"></div>

  <div class="portal-container">
    <div class="hero-text">
        <h1 class="hero-title">Welcome to AuroraTabs</h1>
        <p class="hero-subtitle">The next generation platform for social campaigns. Choose how you want to get started.</p>
    </div>

    <div class="role-cards">
        <!-- Audience -->
        <div class="role-card">
            <div class="role-icon icon-audience">
                <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
            </div>
            <h3 class="role-title">Audience</h3>
            <p class="role-desc">Vote for your favorites, follow campaigns, and be part of the community.</p>
            <div class="d-flex flex-column gap-3 mt-4">
                <a href="./register.php?role=audience" class="btn btn-primary text-white text-decoration-none w-100 py-3 rounded-pill fw-bold shadow-sm">
                    Get Started
                </a>
                <a href="./login-audience.php" class="btn btn-light w-100 py-3 rounded-pill fw-bold border-0 text-primary bg-white shadow-sm" style="transition: all 0.2s;">
                    Sign In
                </a>
            </div>
        </div>

        <!-- Organizer -->
        <div class="role-card">
            <div class="role-icon icon-organizer">
                <iconify-icon icon="solar:clapperboard-edit-bold-duotone"></iconify-icon>
            </div>
            <h3 class="role-title">Event Organizer</h3>
            <p class="role-desc">Create and manage competitions, track results, and engage with participants.</p>
            <div class="d-flex flex-column gap-3 mt-4">
                 <a href="./register-organizer.php" class="btn btn-primary text-white text-decoration-none w-100 py-3 rounded-pill fw-bold shadow-sm">
                    Create Account
                </a>
                <a href="./login-organizer.php" class="btn btn-light w-100 py-3 rounded-pill fw-bold border-0 text-primary bg-white shadow-sm" style="transition: all 0.2s;">
                    Sign In
                </a>
            </div>
        </div>
    </div>

    <div class="admin-link-container">
        <a href="./login-admin.php?type=admin" class="admin-link">
            <iconify-icon icon="solar:shield-keyhole-bold"></iconify-icon>
            Administrator Access
        </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
</body>

</html>
