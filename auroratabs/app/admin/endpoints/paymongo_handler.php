<?php
session_start();
require_once '../../auth/config/db_connection.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$action = $_POST['action'] ?? '';

// ─── Helper: read a setting ──────────────────────────────────────────────────
function getSetting($conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['setting_value'] : $default;
}

// ─── Action: Create PayMongo Checkout Session ────────────────────────────────
if ($action === 'create_checkout') {
    $package_id = (int)($_POST['package_id'] ?? 0);
    $user_id    = (int)$_SESSION['user_id'];

    if (!$package_id) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid package']);
        exit;
    }

    // Check PayMongo is enabled
    $pm_active = getSetting($conn, 'paymongo_active', '0');
    if ($pm_active !== '1') {
        echo json_encode(['status' => 'error', 'message' => 'Online payment is not enabled. Please contact the administrator.']);
        exit;
    }

    // Fetch package
    $stmt = $conn->prepare("SELECT * FROM vote_packages WHERE id = ? AND is_active = 1 LIMIT 1");
    $stmt->bind_param("i", $package_id);
    $stmt->execute();
    $pkg = $stmt->get_result()->fetch_assoc();
    if (!$pkg) {
        echo json_encode(['status' => 'error', 'message' => 'Package not found or inactive']);
        exit;
    }

    // Secret key
    $secret_key = getSetting($conn, 'paymongo_secret_key');
    if (empty($secret_key)) {
        echo json_encode(['status' => 'error', 'message' => 'PayMongo is not configured. Please set your API keys in Voting Settings.']);
        exit;
    }

    // Admin-enabled payment methods
    $methods_str = getSetting($conn, 'paymongo_methods', 'gcash,card,paymaya');
    $methods     = array_filter(array_map('trim', explode(',', $methods_str)));
    if (empty($methods)) $methods = ['gcash', 'card', 'paymaya'];

    // ── Fee pass-through formula ─────────────────────────────────────────────
    // If enabled, the buyer pays gross = net / (1 - fee%) so the seller
    // receives exactly the package price after PayMongo deducts its fee.
    //   Example: net = ₱1.00, fee = 4%  →  gross = 1.00 / 0.96 = ₱1.042 → ₱1.05
    $fee_passthrough = getSetting($conn, 'paymongo_fee_passthrough', '0') === '1';
    $fee_rate        = max(0, min(50, (float)getSetting($conn, 'paymongo_fee_rate', '4')));

    $net_amount = floatval($pkg['price']);
    if ($fee_passthrough && $fee_rate > 0) {
        // Gross up: net ÷ (1 − fee%), rounded up to nearest centavo
        $charge_amount = ceil(($net_amount / (1 - $fee_rate / 100)) * 100) / 100;
    } else {
        $charge_amount = $net_amount;
    }
    $amount_cents = (int)round($charge_amount * 100);
    $base_url    = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
    $txn_code    = 'VTPKG-' . $user_id . '-' . $package_id . '-' . time();
    $success_url = $base_url . '/auroratabs/app/admin/payment-success.php'
                  . '?package_id=' . $package_id
                  . '&user_id='    . $user_id
                  . '&ref='        . urlencode($txn_code);
    $cancel_url  = $base_url . '/auroratabs/app/admin/audience-voting.php';

    // Record pending transaction first (so payment-success.php can complete it)
    $desc = 'Vote Package: ' . $pkg['name'] . ' (' . $pkg['votes_count'] . ' votes)';
    $stmt = $conn->prepare(
        "INSERT INTO transactions (transaction_code, user_id, amount, status, gateway, description, created_at)
         VALUES (?, ?, ?, 'pending', 'paymongo', ?, NOW())"
    );
    $stmt->bind_param("sids", $txn_code, $user_id, $pkg['price'], $desc);
    $stmt->execute();
    $transaction_id = $conn->insert_id;

    // Update success URL with transaction id
    $success_url .= '&txn=' . $transaction_id;

    // PayMongo Checkout Session payload
    $payload = [
        'data' => [
            'attributes' => [
                'send_email_receipt'   => false,
                'show_description'     => true,
                'show_line_items'      => true,
                'reference_number'     => $txn_code,
                'payment_method_types' => array_values($methods),
                'line_items' => [[
                    'currency'    => 'PHP',
                    'amount'      => $amount_cents,
                    'description' => $pkg['votes_count'] . ' votes' . ($fee_passthrough ? ' (incl. ' . $fee_rate . '% payment fee)' : ''),
                    'name'        => $pkg['name'],
                    'quantity'    => 1,
                ]],
                'success_url' => $success_url,
                'cancel_url'  => $cancel_url,
                'description' => 'Aurora Tabs — ' . $pkg['name'],
            ]
        ]
    ];

    $ch = curl_init('https://api.paymongo.com/v1/checkout_sessions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($secret_key . ':'),
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT    => 15,
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $pm = json_decode($raw, true);

    if ($httpCode !== 200 || empty($pm['data']['attributes']['checkout_url'])) {
        // Roll back the pending transaction row
        $conn->query("DELETE FROM transactions WHERE id = $transaction_id");
        $errMsg = $pm['errors'][0]['detail'] ?? 'Unknown PayMongo error';
        echo json_encode(['status' => 'error', 'message' => 'PayMongo: ' . $errMsg]);
        exit;
    }

    // Store the PayMongo checkout session id in reference_number for later
    $session_id = $pm['data']['id'];
    $stmt = $conn->prepare("UPDATE transactions SET reference_number = ? WHERE id = ?");
    $stmt->bind_param("si", $session_id, $transaction_id);
    $stmt->execute();

    echo json_encode([
        'status'       => 'success',
        'checkout_url' => $pm['data']['attributes']['checkout_url'],
    ]);
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
