<?php
if (!empty($_SESSION['user']['id']) && !class_exists('User')) require_once __DIR__ . '/../../models/User.php';
if (!isset($store_settings)) {
    require_once __DIR__ . '/../../models/Setting.php';
    $store_settings = Setting::get_by_category('general');
}
$escape = static function ($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); };
$brand = trim($store_settings['store_name'] ?? '') ?: 'Kairo Burger Co.';
$currency = $store_settings['currency_symbol'] ?? '₱';
$current_route = $_GET['route'] ?? 'home';
if ($current_route === 'product_detail') $current_route = 'products';
if (isset($conn) && $conn instanceof mysqli && class_exists('Cart')) $_SESSION['cart'] = Cart::get_items(session_id(), $_SESSION['user']['id'] ?? null);
$cartCount = array_sum(array_column($_SESSION['cart'] ?? [], 'qty'));
$staffRoute = !empty($_SESSION['user']['id']) ? User::landing_route($_SESSION['user']['id']) : 'home';
if (!isset($flash)) $flash = get_flash();
$is_public_item = static function ($item) {
    return empty($item['deleted_at']) && strtolower($item['status'] ?? 'active') === 'active'
        && empty($item['is_ingredient']) && ($item['product_type'] ?? '') !== 'ingredient'
        && (!isset($item['is_sellable']) || $item['is_sellable']);
};
