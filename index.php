<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/config/db.php';

// Check for database initialization route
if (($_GET['init'] ?? '') === '1') {
    require_once __DIR__ . '/config/init_db.php';
    exit;
}

// Require Controllers
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/AdminController.php';
require_once __DIR__ . '/controllers/PosController.php';
require_once __DIR__ . '/controllers/ExpenseController.php';
require_once __DIR__ . '/controllers/InventoryController.php';
require_once __DIR__ . '/controllers/WebsiteController.php';

// Instantiate Controllers
$authController = new AuthController();
$adminController = new AdminController();
$posController = new PosController();
$expenseController = new ExpenseController();
$inventoryController = new InventoryController();
$websiteController = new WebsiteController();

// Route Dispatcher
$route = $_GET['route'] ?? 'home';

switch ($route) {
    // Public Website Routes
    case 'home':
        $websiteController->home();
        break;
    case 'products':
        $websiteController->products();
        break;
    case 'product_detail':
        $websiteController->product_detail();
        break;
    case 'about':
        $websiteController->about();
        break;
    case 'contact':
        $websiteController->contact();
        break;
    case 'branches':
        $websiteController->branches();
        break;
    case 'cart':
        $websiteController->cart();
        break;
    case 'cart/add':
        $websiteController->cart_add();
        break;
    case 'cart/remove':
        $websiteController->cart_remove();
        break;
    case 'cart/update':
        $websiteController->cart_update();
        break;

    // Authentication Routes
    case 'login':
        $authController->login();
        break;
    case 'customer_login':
        $authController->customer_login();
        break;
    case 'register':
        $authController->register();
        break;
    case 'setup_profile':
        $authController->setup_profile();
        break;
    case 'notification':
        redirect('?route=home');
        break;
    case 'logout':
        $authController->logout();
        break;

    // Admin Dashboard & Management Routes
    case 'dashboard':
        $adminController->dashboard();
        break;
    case 'admin/expenses':
        $expenseController->index();
        break;
    case 'admin/users':
        $adminController->users();
        break;
    case 'admin/users/data':
        $adminController->getUserData();
        break;
    case 'admin/users/single':
        $adminController->getSingleUser();
        break;
    case 'admin/roles/data':
        $adminController->getRoles();
        break;
    case 'admin/permissions/data':
        $adminController->getPermissionsData();
        break;
    case 'admin/expenses/data':
        $expenseController->data();
        break;
    case 'admin/expenses/print':
        $expenseController->print();
        break;
    case 'admin/expenses/summary_print':
        $expenseController->summary_print();
        break;
    case 'admin/roles':
        $adminController->roles();
        break;
    case 'admin/cms':
        $adminController->cms();
        break;
    case 'admin/reports':
        $adminController->reports();
        break;
    case 'admin/reports/print':
        $adminController->reportsPrint();
        break;
    case 'admin/branches':
        $adminController->branches();
        break;
    case 'admin/branches/report':
        $adminController->branchesReport();
        break;
    case 'admin/settings':
        $adminController->settings();
        break;
    case 'admin/activity_logs':
        $adminController->activityLogs();
        break;
    case 'admin/activity_logs/data':
        $adminController->getActivityLogsData();
        break;
    case 'admin/activity_logs/print':
        $adminController->activityLogsPrint();
        break;

    // Cashier POS Routes
    case 'pos':
        $posController->index();
        break;
    case 'pos/history':
        $posController->history();
        break;
    case 'pos/history/data':
        $posController->historyData();
        break;
    case 'pos/history/update_status':
        $posController->updateStatus();
        break;
    case 'pos/receipt':
        $posController->receipt();
        break;

    // Inventory Management Routes
    case 'inventory/products':
        $inventoryController->products();
        break;
    case 'inventory/categories':
        $inventoryController->categories();
        break;
    case 'inventory/logs':
        $inventoryController->logs();
        break;
    case 'inventory/low_stock':
        $inventoryController->low_stock();
        break;

    // AJAX Endpoints Dispatch
    case 'ajax/pos_cart':
        require __DIR__ . '/ajax/pos_cart.php';
        break;
    case 'ajax/inventory_action':
        require __DIR__ . '/ajax/inventory_action.php';
        break;
    case 'ajax/role_action':
        require __DIR__ . '/ajax/role_action.php';
        break;
    case 'ajax/cms_action':
        require __DIR__ . '/ajax/cms_action.php';
        break;
    case 'ajax/branch_action':
        require __DIR__ . '/ajax/branch_action.php';
        break;
    case 'ajax/expense_action':
        require __DIR__ . '/ajax/expense_action.php';
        break;

    // Default 404 Fallback
    default:
        header("HTTP/1.0 404 Not Found");
        echo "<div style='text-align:center; padding: 50px; font-family: sans-serif;'>
                <h1>404 - Page Not Found</h1>
                <p>The requested route '{$route}' does not exist.</p>
                <a href='" . url('?route=home') . "' style='padding: 10px 20px; background: #ff4d6d; color: white; text-decoration: none; border-radius: 20px;'>Return to Homepage</a>
              </div>";
        break;
}
