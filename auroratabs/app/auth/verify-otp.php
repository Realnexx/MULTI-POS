<?php
$email = $_POST['email'] ?? $_GET['email'] ?? '';
$uid = $_POST['uid'] ?? $_GET['uid'] ?? '';

if (empty($email) || empty($uid)) {
    header("Location: login-audience.php");
    exit();
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Verify Email | AuroraTabs</title>
  <link rel="shortcut icon" type="image/png" href="../admin/src/assets/images/logos/aurora-logo.png" />
  <link rel="stylesheet" href="../admin/src/assets/css/styles.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f4f8; }
    .verify-card { max-width: 450px; width: 100%; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .btn-primary { border-radius: 12px; padding: 12px; font-weight: 600; }
    .form-control { border-radius: 12px; border: 1px solid #e2e8f0; background-color: #f8fafc; }
    .form-control:focus { background-color: #fff; border-color: #4285F4; box-shadow: 0 0 0 4px rgba(66, 133, 244, 0.1); }
  </style>
</head>

<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
  <div class="card verify-card bg-white p-4 p-md-5">
    <div class="text-center mb-4">
      <img src="../admin/src/assets/images/logos/aurora-logo.png" alt="Logo" height="50" class="mb-4">
      <h2 class="fw-bold mb-2">Verify Your Email</h2>
      <p class="text-muted">Enter the 6-digit code sent to your email.</p>
    </div>

    <div class="text-center mb-4">
        <iconify-icon icon="solar:letter-unread-bold-duotone" width="64" class="text-primary mb-3"></iconify-icon>
        <p class="mb-1 text-muted small">Code sent to:</p>
        <p class="fw-bold text-dark" id="displayEmail">loading...</p>
    </div>

    <div class="form-floating mb-3">
        <input type="text" class="form-control text-center fw-bold fs-4" id="otpCode" placeholder="000000" maxlength="6" style="letter-spacing: 0.5rem;">
        <label for="otpCode" class="text-center w-100">Verification Code</label>
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

    <button type="button" class="btn btn-primary w-100 mb-3" id="verifyBtn" onclick="verifyOtp()">
        Verify & Activate Account
    </button>
    <a href="login-audience.php" class="btn btn-link w-100 text-decoration-none text-muted">Back to Login</a>
  </div>

  <script src="../admin/src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <script>
    const email = '<?php echo htmlspecialchars($email); ?>';
    const userId = '<?php echo htmlspecialchars($uid); ?>';

    if (!email || !userId) {
        window.location.href = 'login-audience.php';
    }

    $('#displayEmail').text(email);

    let otpTimerInterval;
    function startOtpTimer() {
        clearInterval(otpTimerInterval);
        let timeLeft = 180;
        $('#otpCountdownWrapper').show();
        $('#resendOtpBtn').hide();
        
        const updateDisplay = (t) => {
            const m = Math.floor(t / 60).toString().padStart(2, '0');
            const s = (t % 60).toString().padStart(2, '0');
            $('#otpTimer').text(`${m}:${s}`);
        };

        updateDisplay(timeLeft);
        otpTimerInterval = setInterval(() => {
            timeLeft--;
            updateDisplay(timeLeft);
            if (timeLeft <= 0) {
                clearInterval(otpTimerInterval);
                $('#otpCountdownWrapper').hide();
                $('#resendOtpBtn').show();
            }
        }, 1000);
    }

    startOtpTimer();

    $('#resendOtpBtn').on('click', function() {
        $.ajax({
            url: 'endpoints/auth_resend_otp.php',
            type: 'POST',
            data: { email: email, user_id: userId },
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire('Sent!', 'A new code has been sent to your email.', 'success');
                    startOtpTimer();
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    });

    function verifyOtp() {
        const otp = $('#otpCode').val();
        if (otp.length !== 6) {
            Swal.fire('Invalid Code', 'Please enter the 6-digit code.', 'warning');
            return;
        }

        const btn = $('#verifyBtn');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Verifying...');

        $.ajax({
            url: 'endpoints/auth_verify_otp_standalone.php',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ otp: otp, user_id: userId }),
            success: function(response) {
                if(response.status === 'success') {
                    localStorage.setItem('token', response.token);
                    Swal.fire('Verified!', response.message, 'success').then(() => {
                        window.location.href = response.redirect;
                    });
                } else {
                    Swal.fire('Verification Failed', response.message, 'error');
                    btn.prop('disabled', false).text('Verify & Activate Account');
                }
            },
            error: function() {
                Swal.fire('Error', 'Something went wrong.', 'error');
                btn.prop('disabled', false).text('Verify & Activate Account');
            }
        });
    }
  </script>
</body>
</html>
