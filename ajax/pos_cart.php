<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';

header('Content-Type: application/json');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF Validation
if (empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    echo json_encode(['success' => false, 'message' => 'CSRF token mismatch']);
    exit;
}

// RBAC Check
if (empty($_SESSION['user']) || !User::has_permission($_SESSION['user']['id'], 'access_pos')) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
}
if (!isset($_SESSION['pos_discount'])) {
    $_SESSION['pos_discount'] = 0.00;
}
if (!isset($_SESSION['pos_order_type'])) {
    $_SESSION['pos_order_type'] = 'Dine-in';
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'load':
        // Just return current cart state
        break;

    case 'set_order_type':
        $order_type = $_POST['order_type'] ?? 'Dine-in';
        if ($order_type === 'Dine-in' || $order_type === 'Take-out') {
            $_SESSION['pos_order_type'] = $order_type;
            echo json_encode(['success' => true, 'order_type' => $order_type]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid order type']);
        }
        exit;
        break;

    case 'add':
        $id = (int)($_POST['product_id'] ?? 0);
        $name = $_POST['product_name'] ?? '';
        $price = (float)($_POST['price'] ?? 0);
        $max_stock = (int)($_POST['stock'] ?? 0);

        if ($id > 0 && $max_stock > 0) {
            $product = Product::find_by_id($id);
            $image_url = $product ? get_product_image($product['image_url'], $id) : '';

            if (isset($_SESSION['pos_cart'][$id])) {
                if ($_SESSION['pos_cart'][$id]['quantity'] < $max_stock) {
                    $_SESSION['pos_cart'][$id]['quantity']++;
                    $_SESSION['pos_cart'][$id]['subtotal'] = $_SESSION['pos_cart'][$id]['quantity'] * $price;
                    $_SESSION['pos_cart'][$id]['image_url'] = $image_url;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cannot exceed available stock']);
                    exit;
                }
            } else {
                $_SESSION['pos_cart'][$id] = [
                    'id' => $id,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => 1,
                    'subtotal' => $price,
                    'max_stock' => $max_stock,
                    'image_url' => $image_url
                ];
            }
        }
        break;

    case 'update_qty':
        $id = (int)($_POST['product_id'] ?? 0);
        $qty = (int)($_POST['quantity'] ?? 0);

        if (isset($_SESSION['pos_cart'][$id])) {
            $max_stock = $_SESSION['pos_cart'][$id]['max_stock'];
            if ($qty > 0 && $qty <= $max_stock) {
                $_SESSION['pos_cart'][$id]['quantity'] = $qty;
                $_SESSION['pos_cart'][$id]['subtotal'] = $qty * $_SESSION['pos_cart'][$id]['price'];
            } elseif ($qty <= 0) {
                unset($_SESSION['pos_cart'][$id]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Requested quantity exceeds stock']);
                exit;
            }
        }
        break;

    case 'remove':
        $id = (int)($_POST['product_id'] ?? 0);
        if (isset($_SESSION['pos_cart'][$id])) {
            unset($_SESSION['pos_cart'][$id]);
        }
        break;

    case 'clear':
        $_SESSION['pos_cart'] = [];
        $_SESSION['pos_discount'] = 0.00;
        break;

    case 'set_discount':
        $discount = (float)($_POST['discount'] ?? 0);
        $_SESSION['pos_discount'] = max(0, $discount);
        break;

    case 'get_receipt':
        $sale_id = (int)($_POST['sale_id'] ?? 0);
        $sale = Sale::find_sale_by_id($sale_id);
        if (!$sale) {
            echo json_encode(['success' => false, 'message' => 'Receipt transaction not found.']);
            exit;
        }

        require_once __DIR__ . '/../models/Setting.php';
        $settings = Setting::get_by_category('general');
        
        echo json_encode([
            'success' => true,
            'sale' => $sale,
            'store' => [
                'name' => $settings['store_name'] ?? 'Stacey Halu',
                'address' => $settings['store_address'] ?? '123 Sweet Tooth Blvd, Sugar City',
                'phone' => $settings['store_phone'] ?? '+63 912 345 6789'
            ]
        ]);
        exit;
        break;

    case 'checkout':
        if (empty($_SESSION['pos_cart'])) {
            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
            exit;
        }

        $payment_method = $_POST['payment_method'] ?? 'Cash';
        $amount_tendered = (float)($_POST['amount_tendered'] ?? 0);
        $order_type = $_POST['order_type'] ?? ($_SESSION['pos_order_type'] ?? 'Dine-in');
        
        $subtotal = array_sum(array_column($_SESSION['pos_cart'], 'subtotal'));
        $discount = $_SESSION['pos_discount'];
        $final_amount = max(0, $subtotal - $discount);

        if ($amount_tendered < $final_amount) {
            echo json_encode(['success' => false, 'message' => 'Amount tendered is less than total due']);
            exit;
        }

        $change_amount = $amount_tendered - $final_amount;
        $cashier_id = $_SESSION['user']['id'];
        $branch_id = $_SESSION['pos_branch_id'] ?? ($_SESSION['user']['branch_id'] ?? null);

        $items = [];
        foreach ($_SESSION['pos_cart'] as $item) {
            $items[] = [
                'product_id' => $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price']
            ];
        }

        $sale_id = Sale::create_sale($cashier_id, $branch_id, $subtotal, $discount, $final_amount, $amount_tendered, $change_amount, $payment_method, $items, $order_type);

        if ($sale_id) {
            $_SESSION['pos_cart'] = [];
            $_SESSION['pos_discount'] = 0.00;
            $_SESSION['pos_order_type'] = 'Dine-in';

            // Retrieve updated stock levels
            $updated_stocks = [];
            $res_stocks = $conn->query("SELECT id, stock, low_stock_threshold FROM products");
            if ($res_stocks) {
                while ($row = $res_stocks->fetch_assoc()) {
                    $updated_stocks[] = [
                        'id' => (int)$row['id'],
                        'stock' => (int)$row['stock'],
                        'threshold' => (int)$row['low_stock_threshold']
                    ];
                }
            }

            echo json_encode([
                'success' => true,
                'sale_id' => $sale_id,
                'updated_stocks' => $updated_stocks,
                'message' => 'Transaction completed successfully!'
            ]);
            exit;
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to process transaction']);
            exit;
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
}

// Calculate current summary
$subtotal = array_sum(array_column($_SESSION['pos_cart'], 'subtotal'));
$discount = $_SESSION['pos_discount'];
$total = max(0, $subtotal - $discount);

// Backfill image_url for any items missing it (e.g. from existing sessions)
foreach ($_SESSION['pos_cart'] as $id => $item) {
    if (empty($item['image_url'])) {
        $product = Product::find_by_id($id);
        $_SESSION['pos_cart'][$id]['image_url'] = $product ? get_product_image($product['image_url'], $id) : '';
    }
}

echo json_encode([
    'success' => true,
    'cart' => array_values($_SESSION['pos_cart']),
    'subtotal' => $subtotal,
    'discount' => $discount,
    'total' => $total
]);
