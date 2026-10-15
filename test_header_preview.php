<?php
session_start();
$_SESSION['user'] = [
    'id' => 1,
    'name' => 'Admin User',
    'email' => 'admin@gmail.com',
    'role' => 'Admin',
    'branch' => 'Main Branch'
];
$title = "Stock Notifications Header Preview";
$content = '<div class="card p-4 shadow-sm border-0 rounded-4">
    <h4 class="fw-bold mb-2">Header Stock Notification Test</h4>
    <p class="text-muted">Click the notification bell at the top right of the navigation bar to inspect the active badge count and open the styled stock alerts dropdown.</p>
</div>';
require __DIR__ . '/views/layouts/admin.php';
