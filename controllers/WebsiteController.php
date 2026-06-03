<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Website.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../middleware/auth.php';

class WebsiteController {
    public function home() {
        $content = Website::get_content();
        $featured = Product::get_featured_products();
        $best_sellers = Product::get_best_sellers();
        $categories = Product::get_all_categories();
        require __DIR__ . '/../views/website/home.php';
    }

    public function products() {
        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;

        $categories = Product::get_all_categories();
        $products = Product::get_all_products($category_id, $search);

        require __DIR__ . '/../views/website/products.php';
    }

    public function product_detail() {
        // Accept POST (hidden id) or GET (fallback)
        $id = isset($_POST['id']) ? intval($_POST['id'])
            : (isset($_GET['id']) ? intval($_GET['id']) : 0);
        $product = Product::find_by_id($id);

        if (!$product) {
            set_flash('danger', 'Product not found.');
            redirect('?route=products');
        }

        $related_products = Product::get_related_products($product['category_id'], $product['id'], 3);

        require __DIR__ . '/../views/website/product_detail.php';
    }

    public function about() {
        $content = Website::get_content();
        require __DIR__ . '/../views/website/about.php';
    }

    public function contact() {
        $content = Website::get_content();
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $subject = sanitize($_POST['subject'] ?? '');
            $message = sanitize($_POST['message'] ?? '');

            if (empty($name) || empty($email) || empty($message)) {
                set_flash('danger', 'Name, email, and message are required.');
            } else {
                Website::save_contact_message($name, $email, $subject, $message);
                set_flash('success', 'Thank you! Your message has been sent successfully. We will get back to you soon.');
                redirect('?route=contact');
            }
        }

        require __DIR__ . '/../views/website/contact.php';
    }

    public function branches() {
        $content = Website::get_content();
        $title = "Stacey Halu Halu - Our Branches";
        require __DIR__ . '/../views/website/branches.php';
    }

    public function cart() {
        $session_id = session_id();
        $user_id = $_SESSION['user']['id'] ?? null;
        $_SESSION['cart'] = Cart::get_items($session_id, $user_id);
        $cart = $_SESSION['cart'];
        require __DIR__ . '/../views/website/cart.php';
    }

    public function cart_add() {
        $id  = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $qty = isset($_POST['qty']) ? max(1, intval($_POST['qty'])) : 1;
        $is_ajax = (isset($_POST['ajax']) && $_POST['ajax'] == '1') || (isset($_GET['ajax']) && $_GET['ajax'] == '1');

        $product = Product::find_by_id($id);
        if (!$product) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
                exit;
            }
            set_flash('danger', 'Product not found.');
            redirect('?route=products');
        }

        // Validate qty against stock
        if ($qty > $product['stock']) {
            $qty = $product['stock'];
        }
        if ($qty < 1) {
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'This product is out of stock.']);
                exit;
            }
            set_flash('danger', 'This product is out of stock.');
            redirect('?route=products');
        }

        $session_id = session_id();
        $user_id = $_SESSION['user']['id'] ?? null;
        
        // Add to database
        Cart::add($id, $qty, $session_id, $user_id);

        // Synchronize session cart from database
        $_SESSION['cart'] = Cart::get_items($session_id, $user_id);
        $cartCount = array_sum(array_column($_SESSION['cart'], 'qty'));

        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 'success',
                'message' => '✓ <strong>' . htmlspecialchars($product['name']) . '</strong> added to your cart!',
                'cart_count' => $cartCount
            ]);
            exit;
        }

        set_flash('success', '✓ <strong>' . htmlspecialchars($product['name']) . '</strong> added to your cart!');
        redirect('?route=cart');
    }

    public function cart_remove() {
        $id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $session_id = session_id();
        $user_id = $_SESSION['user']['id'] ?? null;
        
        Cart::remove($id, $session_id, $user_id);
        $_SESSION['cart'] = Cart::get_items($session_id, $user_id);

        set_flash('success', 'Item removed from cart.');
        redirect('?route=cart');
    }

    public function cart_update() {
        $id  = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
        $qty = isset($_POST['qty']) ? max(0, intval($_POST['qty'])) : 0;
        $session_id = session_id();
        $user_id = $_SESSION['user']['id'] ?? null;

        Cart::update($id, $qty, $session_id, $user_id);
        $_SESSION['cart'] = Cart::get_items($session_id, $user_id);

        set_flash('success', 'Cart updated.');
        redirect('?route=cart');
    }
}
