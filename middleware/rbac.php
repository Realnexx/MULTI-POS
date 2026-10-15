<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/auth.php';


function access_denied($message = 'You do not have access to this action.', $status = 403) {
    http_response_code($status);
    $route = $_GET['route'] ?? '';
    $json = strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest'
        || strpos($route, 'ajax/') === 0 || preg_match('~/(data|single|stats|update_status)$~', $route)
        || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/ajax/') !== false;
    if ($json) { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$message]); }
    else { require __DIR__ . '/../views/errors/access_denied.php'; }
    exit;
}
function require_permissions(array $permissions) {
    if (empty($_SESSION['user']['id'])) {
        if (strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest' || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/ajax/') !== false || strpos($_GET['route'] ?? '', 'ajax/') === 0) access_denied('Please sign in to continue.',401);
        require_login();
    }
    foreach ($permissions as $permission) if (User::has_permission($_SESSION['user']['id'], $permission)) return;
    access_denied();
}
function require_permission($permission_name) { require_permissions([$permission_name]); }
