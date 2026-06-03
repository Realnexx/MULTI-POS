<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/auth.php';


function require_permission($permission_name) {
    require_login();

    $user_id = $_SESSION['user']['id'];
    if (!User::has_permission($user_id, $permission_name)) {
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Access Denied: You lack the required permission (' . $permission_name . ').']);
            exit;
        }

        set_flash('danger', 'Access Denied: You do not have permission to access this module (' . $permission_name . ').');
        redirect('?route=dashboard');
    }
}
