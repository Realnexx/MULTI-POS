<?php
// Review and back up the database first. Never invoked by requests or deployment.
if (PHP_SAPI !== 'cli' || !in_array('--apply', $argv ?? [], true)) {
    http_response_code(403);
    exit("Manual CLI migration only: php tools/migrate-legacy.php --apply\n");
}
require_once __DIR__ . '/../config/db.php';
// Perform simple dynamic schema migration for order_type in sales table
$check_order_type = $conn->query("SHOW COLUMNS FROM sales LIKE 'order_type'");
if ($check_order_type && $check_order_type->num_rows == 0) {
    $conn->query("ALTER TABLE sales ADD COLUMN order_type VARCHAR(50) DEFAULT 'Dine-in'");
}

$check_sales_branch = $conn->query("SHOW COLUMNS FROM sales LIKE 'branch_id'");
if ($check_sales_branch && $check_sales_branch->num_rows == 0) {
    $conn->query("ALTER TABLE sales ADD COLUMN branch_id INT NULL");
}

$check_product_branch = $conn->query("SHOW COLUMNS FROM products LIKE 'branch_id'");
if ($check_product_branch && $check_product_branch->num_rows == 0) {
    $conn->query("ALTER TABLE products ADD COLUMN branch_id INT NULL");
}

// Ensure Branches has image_url and manager_id
$check_branch_cols = $conn->query("SHOW COLUMNS FROM branches");
$existing_cols = [];
while ($c = $check_branch_cols->fetch_assoc())
    $existing_cols[] = $c['Field'];

if (!in_array('image_url', $existing_cols)) {
    $conn->query("ALTER TABLE branches ADD COLUMN image_url VARCHAR(255) DEFAULT NULL");
}
if (!in_array('manager_id', $existing_cols)) {
    $conn->query("ALTER TABLE branches ADD COLUMN manager_id INT NULL");
}

// Ensure Users has branch_id and pos_pin
$check_user_cols = $conn->query("SHOW COLUMNS FROM users");
$u_cols = [];
while ($uc = $check_user_cols->fetch_assoc())
    $u_cols[] = $uc['Field'];

if (!in_array('branch_id', $u_cols)) {
    $conn->query("ALTER TABLE users ADD COLUMN branch_id INT NULL");
}
if (!in_array('pos_pin', $u_cols)) {
    $conn->query("ALTER TABLE users ADD COLUMN pos_pin VARCHAR(4) DEFAULT NULL");
}

// PINs must be assigned individually through the authorized settings UI.

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

$check_selling_price = $conn->query("SHOW COLUMNS FROM product_batches LIKE 'selling_price'");
if ($check_selling_price && $check_selling_price->num_rows == 0) {
    $conn->query("ALTER TABLE product_batches ADD COLUMN selling_price DECIMAL(10,2) DEFAULT NULL AFTER cost_price");
}
$check_pb_branch = $conn->query("SHOW COLUMNS FROM product_batches LIKE 'branch_id'");
if ($check_pb_branch && $check_pb_branch->num_rows == 0) {
    $conn->query("ALTER TABLE product_batches ADD COLUMN branch_id INT DEFAULT NULL AFTER selling_price");
}

/**
 * AUTO MIGRATION FOR UNIVERSAL PRODUCT ARCHITECTURE
 */
$columns_to_add = [
    "product_type" => "ENUM('simple', 'recipe', 'ingredient', 'service', 'variant', 'combo', 'weighted', 'digital') DEFAULT 'simple' AFTER category_id",
    "track_stock" => "TINYINT(1) DEFAULT 1",
    "is_sellable" => "TINYINT(1) DEFAULT 1",
    "is_ingredient" => "TINYINT(1) DEFAULT 0",
    "inventory_type" => "ENUM('stocked', 'non-stock', 'ingredient-based', 'unlimited') DEFAULT 'stocked'",
    "json_attributes" => "JSON DEFAULT NULL",
    "unit_type" => "VARCHAR(50) DEFAULT 'pc'"
];

foreach ($columns_to_add as $col => $definition) {
    $check = $conn->query("SHOW COLUMNS FROM products LIKE '$col'");
    if ($check && $check->num_rows == 0) {
        $conn->query("ALTER TABLE products ADD COLUMN $col $definition");
    } else {
        // Ensure ENUMs are updated
        if ($col == 'product_type' || $col == 'inventory_type') {
            $conn->query("ALTER TABLE products MODIFY COLUMN $col $definition");
        }
    }
}

// Create product_recipes table
$conn->query("CREATE TABLE IF NOT EXISTS product_recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recipe_product_id INT NOT NULL,
    ingredient_product_id INT NOT NULL,
    quantity DECIMAL(15,4) NOT NULL,
    unit VARCHAR(50) DEFAULT 'pc',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (recipe_product_id) REFERENCES products(id) ON DELETE CASCADE,
    FOREIGN KEY (ingredient_product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create product_attributes table
$conn->query("CREATE TABLE IF NOT EXISTS product_attributes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create product_variants table
$conn->query("CREATE TABLE IF NOT EXISTS product_variants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    variant_name VARCHAR(255) NOT NULL,
    sku VARCHAR(100) DEFAULT NULL,
    price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    cost_price DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    stock INT NOT NULL DEFAULT 0,
    barcode VARCHAR(100) DEFAULT NULL,
    json_attributes JSON DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create sale_item_modifiers table for receipt modifier persistence
$conn->query("CREATE TABLE IF NOT EXISTS sale_item_modifiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_item_id INT NOT NULL,
    modifier_name VARCHAR(255) NOT NULL,
    additional_price DECIMAL(10,2) DEFAULT 0.00,
    ingredient_product_id INT NULL,
    FOREIGN KEY (sales_item_id) REFERENCES sales_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Create stock_notifications table for tracking No Stock, Low Stock, and Overstock alerts
$conn->query("CREATE TABLE IF NOT EXISTS stock_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    branch_id INT NULL,
    alert_type ENUM('out_of_stock', 'low_stock', 'overstock') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    current_stock INT NOT NULL DEFAULT 0,
    threshold INT NOT NULL DEFAULT 0,
    status ENUM('unread', 'read', 'resolved') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_product (product_id),
    INDEX idx_alert_type (alert_type),
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");


echo "Legacy migration completed. Review all additional schema prerequisites.\n";
