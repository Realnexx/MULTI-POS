<?php
session_start();
header('Content-Type: application/json');
require_once '../../auth/config/db_connection.php';
require_once '../../auth/helpers/security_helper.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']); exit;
}

$action = sanitize($_POST['action'] ?? $_GET['action'] ?? '');

// ─── Helpers ────────────────────────────────────────────────────────────────
function getSetting($conn, $key, $default = '') {
    $stmt = $conn->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['setting_value'] : $default;
}

function saveSetting($conn, $key, $value) {
    $stmt = $conn->prepare("INSERT INTO settings (setting_key, setting_value, `group`) VALUES (?, ?, 'payment') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $stmt->bind_param("ss", $key, $value);
    return $stmt->execute();
}

// ─── Actions ────────────────────────────────────────────────────────────────

// Lightweight public info: active status + fee config (no keys exposed)
if ($action === 'get_payment_fee_info') {
    echo json_encode([
        'status' => 'success',
        'data'   => [
            'active'          => getSetting($conn, 'paymongo_active', '0') === '1',
            'fee_passthrough' => getSetting($conn, 'paymongo_fee_passthrough', '0') === '1',
            'fee_rate'        => (float)getSetting($conn, 'paymongo_fee_rate', '4'),
        ]
    ]);
    exit;
}

// GET current PayMongo settings
if ($action === 'get_paymongo_settings') {
    $secret = getSetting($conn, 'paymongo_secret_key');
    $public = getSetting($conn, 'paymongo_public_key');
    $active = getSetting($conn, 'paymongo_active', '0');
    $methods = getSetting($conn, 'paymongo_methods', 'gcash,card,paymaya');

    // Mask secret key for display
    $masked_secret = $secret ? substr($secret, 0, 8) . str_repeat('*', max(0, strlen($secret) - 8)) : '';

    $fee_passthrough = getSetting($conn, 'paymongo_fee_passthrough', '0');
    $fee_rate        = getSetting($conn, 'paymongo_fee_rate', '4');

    echo json_encode([
        'status'  => 'success',
        'data'    => [
            'active'           => $active === '1',
            'public_key'       => $public,
            'secret_key'       => $masked_secret,
            'secret_set'       => !empty($secret),
            'enabled_methods'  => array_map('trim', explode(',', $methods)),
            'fee_passthrough'  => $fee_passthrough === '1',
            'fee_rate'         => (float)$fee_rate,
        ]
    ]);
    exit;
}

// SAVE PayMongo settings
if ($action === 'save_paymongo_settings') {
    $active     = isset($_POST['active']) && $_POST['active'] === '1' ? '1' : '0';
    $public_key = trim($_POST['public_key'] ?? '');
    $methods    = $_POST['methods'] ?? 'gcash,card,paymaya';

    // Only update secret if a new one was submitted (not masked placeholder)
    $new_secret = trim($_POST['secret_key'] ?? '');
    if (!empty($new_secret) && strpos($new_secret, '*') === false) {
        saveSetting($conn, 'paymongo_secret_key', $new_secret);
    }

    $fee_passthrough = isset($_POST['fee_passthrough']) && $_POST['fee_passthrough'] === '1' ? '1' : '0';
    $fee_rate        = max(0, min(100, (float)($_POST['fee_rate'] ?? 4)));

    saveSetting($conn, 'paymongo_active',          $active);
    saveSetting($conn, 'paymongo_public_key',       $public_key);
    saveSetting($conn, 'paymongo_methods',          $methods);
    saveSetting($conn, 'paymongo_fee_passthrough',  $fee_passthrough);
    saveSetting($conn, 'paymongo_fee_rate',         (string)$fee_rate);

    echo json_encode(['status' => 'success', 'message' => 'PayMongo settings saved.']);
    exit;
}

// TEST / FETCH available payment methods from PayMongo API using provided keys
if ($action === 'fetch_paymongo_methods') {
    // Accept a live key from POST, or fall back to the stored one
    $secret = trim($_POST['secret_key'] ?? '');
    if (empty($secret) || strpos($secret, '*') !== false) {
        $secret = getSetting($conn, 'paymongo_secret_key');
    }

    if (empty($secret)) {
        echo json_encode(['status' => 'error', 'message' => 'No secret key configured.']);
        exit;
    }

    // PayMongo does not have a public "list payment methods" endpoint,
    // but we can probe by creating a ₱1 payment intent — PayMongo echoes back
    // which of the requested methods are actually enabled on the account.
    $payload = json_encode([
        'data' => [
            'attributes' => [
                'amount'   => 100,   // ₱1 = 100 centavos (minimum)
                'currency' => 'PHP',
                'payment_method_allowed' => [
                    'gcash', 'card', 'paymaya', 'grab_pay',
                    'dob', 'qrph', 'billease',
                ],
                'description' => 'Method availability check',
            ]
        ]
    ]);


    $ch = curl_init('https://api.paymongo.com/v1/payment_intents');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($secret . ':'),
        ],
        CURLOPT_POSTFIELDS => $payload,
    ]);

    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $res = json_decode($raw, true);

    if ($httpCode === 200 && !empty($res['data'])) {
        // Return the methods from the returned object
        $allowed = $res['data']['attributes']['payment_method_allowed'] ?? [];
        echo json_encode([
            'status'  => 'success',
            'methods' => $allowed,
        ]);
    } else {
        $errMsg = $res['errors'][0]['detail'] ?? 'Invalid secret key or PayMongo error.';
        echo json_encode(['status' => 'error', 'message' => $errMsg]);
    }
    exit;
}

echo json_encode(['status' => 'error', 'message' => 'Unknown action']);
