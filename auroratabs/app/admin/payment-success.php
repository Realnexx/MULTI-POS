<?php
session_start();
require_once '../auth/config/db_connection.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth/login.php");
    exit;
}

$package_id     = (int)($_GET['package_id'] ?? 0);
$user_id        = (int)($_GET['user_id']    ?? 0);
$transaction_id = (int)($_GET['txn']        ?? 0);

// Security: logged-in user must match
if ($user_id !== (int)$_SESSION['user_id']) {
    die("Unauthorized");
}

$success    = false;
$message    = '';
$votes_added = 0;

if ($package_id && $transaction_id) {
    // Fetch package
    $stmt = $conn->prepare("SELECT * FROM vote_packages WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();

    // Fetch pending transaction (idempotent check)
    $stmt = $conn->prepare("SELECT * FROM transactions WHERE id = ? AND user_id = ? AND status = 'pending' LIMIT 1");
    $stmt->bind_param("ii", $transaction_id, $user_id);
    $stmt->execute();
    $txn = $stmt->get_result()->fetch_assoc();

    if ($pkg && $txn) {
        // Mark transaction success
        $stmt = $conn->prepare("UPDATE transactions SET status = 'success' WHERE id = ?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();

        // Credit votes
        $votes_added = (int)$pkg['votes_count'];
        $stmt = $conn->prepare("UPDATE users SET coins = coins + ? WHERE id = ?");
        $stmt->bind_param("ii", $votes_added, $user_id);
        $stmt->execute();

        $success = true;
        $message = number_format($votes_added) . ' votes have been added to your wallet!';

    } elseif (!$txn) {
        // Already processed (idempotent — page reload)
        $success = true;
        $message = 'Your payment was already processed. Votes have been credited to your wallet.';
    } else {
        $message = 'Unable to verify your payment. Please contact support with your transaction reference.';
    }
} else {
    $message = 'Invalid payment return. Please contact support.';
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= $success ? 'Payment Successful' : 'Payment Issue'; ?> — Aurora Tabs</title>
  <?php include 'components/links.php'; ?>
  <style>
    body { background: #f0f4ff; display: flex; min-height: 100vh; align-items: center; justify-content: center; }
    .result-wrap { max-width: 460px; width: 100%; }
    .result-card { border-radius: 24px; overflow: hidden; box-shadow: 0 20px 60px rgba(0,0,0,0.12); }
    .result-header { padding: 48px 32px 32px; text-align: center; }
    .result-icon  { font-size: 4rem; display: block; margin-bottom: 16px; }
    .votes-pill   { background: #f0fdf4; border: 2px solid #bbf7d0; border-radius: 16px;
                    padding: 16px 24px; text-align: center; margin-bottom: 24px; }
    .votes-number { font-size: 3rem; font-weight: 800; color: #16a34a; line-height: 1; }
  </style>
</head>
<body>
  <div class="result-wrap">
    <div class="result-card">
      <div class="result-header <?= $success ? 'bg-success text-white' : 'bg-danger text-white' ?>">
        <span class="result-icon"><?= $success ? '🎉' : '⚠️' ?></span>
        <h3 class="fw-bolder mb-1"><?= $success ? 'Payment Successful!' : 'Payment Issue' ?></h3>
        <p class="mb-0 opacity-75 small">
          <?= $success ? 'Your votes have been credited to your wallet.' : 'Something went wrong with your payment.' ?>
        </p>
      </div>
      <div class="bg-white p-4">
        <?php if ($success && $votes_added > 0): ?>
        <div class="votes-pill">
          <div class="votes-number">+<?= number_format($votes_added) ?></div>
          <div class="text-muted small mt-1">votes added to your wallet</div>
        </div>
        <?php endif; ?>

        <p class="text-muted text-center mb-4 small"><?= htmlspecialchars($message) ?></p>

        <a href="audience-voting.php" class="btn btn-primary w-100 rounded-pill py-3 fw-bold">
          <i class="ti ti-arrow-left me-2"></i>Back to Voting Portal
        </a>

        <?php if (!$success): ?>
        <p class="text-center mt-3 small text-muted">
          Transaction ref: <code><?= htmlspecialchars($_GET['ref'] ?? 'N/A') ?></code>
        </p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
