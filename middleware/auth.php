<?php
require_once __DIR__ . '/../config/db.php';

function require_login() {
    if (empty($_SESSION['user'])) {
        set_flash('danger', 'Please log in to access this page.');
        redirect('?route=login');
    }
}

function require_guest() {
    if (!empty($_SESSION['user'])) {
        redirect('?route=dashboard');
    }
}

function enforce_csrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!verify_csrf($token)) {
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'CSRF token mismatch.']);
                exit;
            }
            set_flash('danger', 'Invalid security token. Please try again.');
            redirect($_SERVER['HTTP_REFERER'] ?? '?route=home');
        }
    }
}
