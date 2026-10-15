<?php
    session_start();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Access Restricted - Aurora Tabs</title>
    <?php include 'components/links.php'; ?>
    <style>
        .error-box {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8fafc;
        }
        .error-card {
            max-width: 500px;
            width: 100%;
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>
    <div class="error-box">
        <div class="error-card">
            <iconify-icon icon="solar:shield-warning-bold-duotone" class="text-danger mb-4" style="font-size: 80px;"></iconify-icon>
            <h1 class="fw-bold mb-3">Access Restricted</h1>
            <p class="text-muted mb-4 fs-4">Oops! It looks like you don't have the necessary permissions to access this page. Please contact your administrator if you believe this is an error.</p>
            <div class="d-flex gap-2 justify-content-center">
                <a href="index.php" class="btn btn-primary px-4 py-2 rounded-pill">
                    <i class="ti ti-home me-1"></i> Back to Dashboard
                </a>
                <a href="javascript:history.back()" class="btn btn-outline-secondary px-4 py-2 rounded-pill">
                    <i class="ti ti-arrow-left me-1"></i> Go Back
                </a>
            </div>
        </div>
    </div>
    <?php include 'components/scripts.php'; ?>
</body>
</html>
