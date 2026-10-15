<?php
session_start();
$plan = $_GET['plan'] ?? 'starter';
$comp_id = $_GET['comp_id'] ?? 0;

$prices = [
    'starter' => 0.00,
    'pro' => 1500.00,
    'enterprise' => 5000.00
];

$amount = $prices[$plan] ?? 0.00;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pay with GCash | AuroraTabs</title>
  <link rel="shortcut icon" type="image/png" href="../admin/src/assets/images/logos/favicon.png" />
  <link rel="stylesheet" href="../admin/src/assets/css/styles.min.css" />
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root { --gcash-blue: #007dfe; }
    body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f0f4f8; }
    .payment-card { max-width: 500px; margin: 50px auto; border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    .gcash-header { background-color: var(--gcash-blue); color: white; border-radius: 20px 20px 0 0; padding: 2rem; text-align: center; }
    .amount-display { font-size: 2.5rem; font-weight: 800; margin: 1rem 0; }
    .channel-item { border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; margin-bottom: 1rem; transition: all 0.2s; cursor: pointer; }
    .channel-item:hover { border-color: var(--gcash-blue); background-color: #f0f7ff; }
    .channel-item.selected { border-width: 2px; border-color: var(--gcash-blue); background-color: #f0f7ff; }
  </style>
</head>
<body>
  <div class="container">
    <div class="card payment-card">
      <div class="gcash-header">
        <img src="https://upload.wikimedia.org/wikipedia/commons/e/eb/GCash_logo.svg" alt="GCash" width="120" style="filter: brightness(0) invert(1);">
        <div class="amount-display">₱<?php echo number_format($amount, 2); ?></div>
        <p class="mb-0">Payment for <?php echo ucfirst($plan); ?> Plan</p>
      </div>
      <div class="card-body p-4">
        <h5 class="fw-bold mb-4">Select Payment Channel</h5>
        
        <div class="channel-item selected" onclick="selectChannel(this, '09123456789', 'Aurora Admin 1')">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-dark">Aurora Admin 1</div>
                    <div class="text-muted small">0912 345 6789</div>
                </div>
                <iconify-icon icon="solar:check-circle-bold" class="text-primary fs-5"></iconify-icon>
            </div>
        </div>

        <div class="channel-item" onclick="selectChannel(this, '09987654321', 'Aurora Admin 2')">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-bold text-dark">Aurora Admin 2</div>
                    <div class="text-muted small">0998 765 4321</div>
                </div>
                <iconify-icon icon="solar:check-circle-outline" class="text-muted fs-5"></iconify-icon>
            </div>
        </div>

        <div class="mb-4 mt-4">
            <label class="form-label fw-bold">Reference Number</label>
            <input type="text" id="refNum" class="form-control form-control-lg" placeholder="Enter Reference Number" required>
            <small class="text-muted">Enter the 13-digit reference number from GCash</small>
        </div>

        <button onclick="confirmPayment()" class="btn btn-primary w-100 py-3 fw-bold rounded-3">
            Confirm Payment
        </button>
        <a href="register-organizer.php" class="btn btn-link w-100 mt-2 text-decoration-none text-muted">Cancel Payment</a>
      </div>
    </div>
  </div>

  <script src="../admin/src/assets/libs/jquery/dist/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/iconify-icon@1.0.8/dist/iconify-icon.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    let selectedNumber = '09123456789';

    function selectChannel(el, num, name) {
        $('.channel-item').removeClass('selected').find('iconify-icon').attr('icon', 'solar:check-circle-outline').addClass('text-muted').removeClass('text-primary');
        $(el).addClass('selected').find('iconify-icon').attr('icon', 'solar:check-circle-bold').removeClass('text-muted').addClass('text-primary');
        selectedNumber = num;
    }

    function confirmPayment() {
        const refNum = $('#refNum').val();
        if(!refNum || refNum.length < 8) {
            Swal.fire('Error', 'Please enter a valid reference number.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Processing Payment',
            text: 'Please wait while we verify your transaction...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.ajax({
            url: 'endpoints/auth_process_payment.php',
            type: 'POST',
            data: JSON.stringify({
                comp_id: <?php echo $comp_id; ?>,
                method: 'gcash',
                reference: refNum,
                amount: <?php echo $amount; ?>
            }),
            contentType: 'application/json',
            success: function(response) {
                if(response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Payment Successful',
                        text: 'Your event plan has been activated.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = 'login-organizer.php?';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            }
        });
    }
  </script>
</body>
</html>
