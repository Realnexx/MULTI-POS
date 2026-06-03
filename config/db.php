<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'stacey_halu_halu');

// Initial connection without DB name to ensure database exists
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$conn->select_db(DB_NAME);

if ($conn->error) {
    die("Failed to select database: " . $conn->error);
}

// Perform simple dynamic schema migration for order_type in sales table
$check_order_type = $conn->query("SHOW COLUMNS FROM sales LIKE 'order_type'");
if ($check_order_type && $check_order_type->num_rows == 0) {
    $conn->query("ALTER TABLE sales ADD COLUMN order_type VARCHAR(50) DEFAULT 'Dine-in'");
}

$check_sales_branch = $conn->query("SHOW COLUMNS FROM sales LIKE 'branch_id'");
if ($check_sales_branch && $check_sales_branch->num_rows == 0) {
    $conn->query("ALTER TABLE sales ADD COLUMN branch_id INT NULL");
}

// Ensure Branches has image_url and manager_id
$check_branch_cols = $conn->query("SHOW COLUMNS FROM branches");
$existing_cols = [];
while($c = $check_branch_cols->fetch_assoc()) $existing_cols[] = $c['Field'];

if (!in_array('image_url', $existing_cols)) {
    $conn->query("ALTER TABLE branches ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
}
if (!in_array('manager_id', $existing_cols)) {
    $conn->query("ALTER TABLE branches ADD COLUMN manager_id INT NULL");
}

// Ensure Users has branch_id and pos_pin
$check_user_cols = $conn->query("SHOW COLUMNS FROM users");
$u_cols = [];
while($uc = $check_user_cols->fetch_assoc()) $u_cols[] = $uc['Field'];

if (!in_array('branch_id', $u_cols)) {
    $conn->query("ALTER TABLE users ADD COLUMN branch_id INT NULL");
}
if (!in_array('pos_pin', $u_cols)) {
    $conn->query("ALTER TABLE users ADD COLUMN pos_pin VARCHAR(4) DEFAULT NULL");
}

// Ensure Expenses table exists
$conn->query("CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT NULL,
    category VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    expense_date DATE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Ensure Settings table exists
$conn->query("CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NULL,
    category VARCHAR(50) DEFAULT 'general',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)");

// Ensure Manager Role exists
$check_manager_role = $conn->query("SELECT id FROM roles WHERE name = 'Manager'");
if ($check_manager_role && $check_manager_role->num_rows == 0) {
    $conn->query("INSERT INTO roles (name, description, is_system) VALUES ('Manager', 'Branch Management access', 1)");
}

// Set charset
$conn->set_charset("utf8mb4");

/**
 * AUTO MIGRATION FOR BATCH MANAGEMENT
 * Ensuring critical tables and columns exist before any logic runs
 */
$conn->query("CREATE TABLE IF NOT EXISTS product_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    batch_number VARCHAR(50) NOT NULL,
    quantity INT NOT NULL DEFAULT 0,
    cost_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    expiry_date DATE DEFAULT NULL,
    status ENUM('active', 'depleted', 'expired') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;");

// check column exists
$check_batch_id = $conn->query("SHOW COLUMNS FROM inventory_logs LIKE 'batch_id'");
if ($check_batch_id && $check_batch_id->num_rows == 0) {
    $conn->query("ALTER TABLE inventory_logs ADD COLUMN batch_id INT DEFAULT NULL AFTER product_id");
}

// Global Helper Functions
if (!function_exists('url')) {
    function url($path = '') {
        $base = '/pos/';
        return $base . ltrim($path, '/');
    }
}

if (!function_exists('redirect')) {
    function redirect($path) {
        header("Location: " . url($path));
        exit;
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
}

if (!function_exists('verify_csrf')) {
    function verify_csrf($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}

if (!function_exists('set_flash')) {
    function set_flash($type, $message) {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }
}

if (!function_exists('get_flash')) {
    function get_flash() {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }
}

if (!function_exists('sanitize')) {
    function sanitize($data) {
        global $conn;
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = sanitize($val);
            }
            return $data;
        }
        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('get_product_image')) {
    function get_product_image($image_url, $product_id = 0) {
        // Fallback premium Stitch images for high-fidelity visual presentation
        $stitch_images = [
            "https://lh3.googleusercontent.com/aida-public/AB6AXuCMRCcZvGukWIf0WGBH8mjNtYrLbKGmyMyr1k98OEyIA6PFVVsy7HlK6spYzrfGGZAv64SMH0VxU6ZQrx9RfIWUdxgy-11QbrsN4AAB6iJgwSjE2NVgY1MEWmRklmJSqpdZ7AMtXUGQ8CP9pbSyMQOo-X1EpyWySEJyBZWu58QOGG6S7GluaAGnuVWbKiw7eXdyrD0Ybk_5IrLGAyOh-du6uRBiy8mKdEcgZ5f1zIe5Ek4AbEnAZs2vbBUHDYUAgPVEGExHKYP7U3M",
            "https://lh3.googleusercontent.com/aida-public/AB6AXuCIt5CSeJJ2kkqxn51omKtMRHh5m0SsqDvn1rngdOAgi9jOAaTvxWiwREtIC9cfUcVYT0V4cZzIWvlBGBBR7Baiy9XGNIrZOFUL5gshsR2Z931tne_i9jroq4EFVRvLDiCgjRbPRzYjMuQPqOyEcCIx7i_6WkP2gjfxIfbRLqUAjvZwEQaztLwojwqJr0DbFMPvnA4grryxznoLc1A-qO-bt5eYNPcFuoOryquvcVakMQr1FBTGJuKw2rcPv_LrVyksZ1FDNWSeOqI",
            "https://lh3.googleusercontent.com/aida-public/AB6AXuAh6eGMBPVKS1sQ8BYTXAJ4wzoo3LCytQL3zKEMKAJ_4ZwZopDEjnq0lA6dfGsQ4Ch1MzViW4w2RZ66vlPIm1wLNdhdBPNVl7Qvyn3-4R1GIc2234aMfYM343xLXxlQq8PF-0BKm1ebLjtj7LiQDNyCkJpmecKlU_YNROpVgBz8pXcsISUh3eGCFahtJ_fPdE46-yRsH6Qyf-4K1ZUgMlSBUwlj-YYAICd7sfQ9nWoobY3VF49RsrNya1BdezX1lVV46VHk6KJnuns",
            "https://lh3.googleusercontent.com/aida-public/AB6AXuBGVnk0eA7kTF7Ol2FHuK2JJ7_4P3DNPocaoWb2iT5nMetP3HVKQM-HMm1hae1Vrf3E0HeTtivJdZufBb5zp048NfMtBsUKIgwig7xbMCpKPWL1MtJ8RvU51-VtF9TL23y2W9BtxBqM7ki99V14RHA4rX_oAPzk17U4Pxxz10vxX8NdaNPVzpMHumD_gunj-hO7l-rFXPD_N2nP9jNKkTJlJbSlSC3TR0am9rv6-dcQAwfynrMobmSQX1TNAo_cs95ac58JgDTQFIQ"
        ];

        if (empty($image_url)) {
            return $stitch_images[abs((int)$product_id) % 4];
        }

        if (strpos($image_url, 'http://') === 0 || strpos($image_url, 'https://') === 0) {
            return $image_url;
        }

        $local_path = __DIR__ . '/../' . ltrim($image_url, '/');
        if (file_exists($local_path) && !is_dir($local_path)) {
            return url($image_url);
        }

        if (strpos($image_url, 'halo_supreme') !== false || strpos($image_url, 'halo_classic') !== false) {
            return $stitch_images[0];
        } elseif (strpos($image_url, 'halo_ube') !== false || strpos($image_url, 'taro_milktea') !== false) {
            return $stitch_images[1];
        } elseif (strpos($image_url, 'mango_graham') !== false) {
            return $stitch_images[2];
        } elseif (strpos($image_url, 'leche_flan') !== false) {
            return $stitch_images[3];
        }

        return $stitch_images[abs((int)$product_id) % 4];
    }
}

if (!function_exists('get_branch_image')) {
    function get_branch_image($image_url, $branch_id = 0) {
        $fallbacks = [
            "https://images.unsplash.com/photo-1555396273-367ea4eb4db5?q=80&w=800&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1552566626-52f8b828add9?q=80&w=800&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?q=80&w=800&auto=format&fit=crop",
            "https://images.unsplash.com/photo-1414235077428-338989a2e8c0?q=80&w=800&auto=format&fit=crop"
        ];
        if (empty($image_url)) return $fallbacks[abs((int)$branch_id) % 4];
        if (strpos($image_url, 'http') === 0) return $image_url;
        return url($image_url);
    }
}
if (!function_exists('get_user_avatar')) {
    function get_user_avatar($avatar_url, $user_name = '') {
        if (empty($avatar_url)) {
            return "https://ui-avatars.com/api/?name=" . urlencode($user_name) . "&background=ff4d6d&color=fff&bold=true";
        }
        if (strpos($avatar_url, 'http://') === 0 || strpos($avatar_url, 'https://') === 0) {
            return $avatar_url;
        }
        return url($avatar_url);
    }
}
