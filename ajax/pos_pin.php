<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Setting.php';
require_once __DIR__ . '/../models/SystemPolicy.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('pos_pin','verify');

// CSRF Validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'Security token mismatch.']);
    exit;
}

$pin = is_string($_POST['pin'] ?? null) ? trim($_POST['pin']) : '';
$user_id = (int)($_SESSION['user']['id'] ?? 0);

if (empty($pin) || strlen($pin) !== 4 || !ctype_digit($pin)) {
    echo json_encode(['success' => false, 'message' => 'Please enter a valid 4-digit PIN.']);
    exit;
}

if ($user_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'User session invalid.']);
    exit;
}

require_once __DIR__.'/../models/CashierSchedule.php';
require_once __DIR__.'/../models/AuthSecurity.php';
try {
    CashierSchedule::assertAccess($user_id,(int)($_SESSION['pos_branch_id'] ?? 0));
    if(AuthSecurity::consume('pos-pin','user:'.$user_id,5,60))throw new DomainException('Too many PIN attempts. Wait one minute before trying again.');
} catch (DomainException $e) { echo json_encode(['success'=>false,'message'=>$e->getMessage()]);exit; }

$is_admin = User::has_permission($user_id, 'manage_settings');

// Check if user has a PIN set
$user_data = User::find_by_id($user_id);
$has_personal_pin = !empty($user_data['pos_pin']);
$master_pin = Setting::get('master_pos_pin', '');
$has_master_pin = !empty($master_pin);

if (!$has_personal_pin && !($is_admin && $has_master_pin)) {
    echo json_encode(['success' => false, 'message' => 'No POS PIN configured for your account. Contact administrator.']);
    exit;
}

$verified = false;

// Check user's personal PIN
if ($has_personal_pin && User::verify_pin($user_id, $pin)) {
    $verified = true;
}

// For admins, also check master PIN
if (!$verified && $is_admin && $has_master_pin && SystemPolicy::verifyPin($pin,$master_pin)) {
    $verified = true;
}

if ($verified) {
    AuthSecurity::clear_limit('pos-pin','user:'.$user_id);
    $_SESSION['pos_entry_grant']=time();
    $_SESSION['pos_pin_verified'] = true;
    $_SESSION['pos_pin_verified_at'] = time();
    echo json_encode(['success' => true, 'message' => 'PIN verified successfully.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid PIN. Please try again.']);
}
