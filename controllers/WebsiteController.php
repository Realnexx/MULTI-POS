<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Website.php';
require_once __DIR__ . '/../models/Cart.php';
require_once __DIR__ . '/../models/OnlineOrder.php';
require_once __DIR__ . '/../models/AuthSecurity.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../middleware/auth.php';

class WebsiteController {
    public function home() {
        global $conn;
        $content = Website::get_content();
        $featured = Product::get_featured_products();
        $best_sellers = Product::get_best_sellers();
        $categories = Product::get_all_categories();

        // Fetch store settings (currency, store name, etc.)
        $store_settings = [];
        $settings_res = $conn->query("SELECT setting_key, setting_value FROM settings");
        if ($settings_res) {
            while ($r = $settings_res->fetch_assoc()) {
                $store_settings[$r['setting_key']] = $r['setting_value'];
            }
        }

        // Fetch active branches
        $branches_res = $conn->query("SELECT * FROM branches WHERE status = 'Active' ORDER BY id ASC LIMIT 6");
        $branches = $branches_res ? $branches_res->fetch_all(MYSQLI_ASSOC) : [];

        // Use the catalog instead of a hard-coded category ID.
        $burgers = Product::get_all_products();

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

        $product['stock'] = Cart::available($product, $_SESSION['online_branch_id'] ?? null);
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
        require_once __DIR__ . '/../models/Branch.php';
        $branches = Branch::get_all_active();
        require __DIR__ . '/../views/website/branches.php';
    }

    public function cart() {
        $branches=Branch::get_all_active();
        if(isset($_GET['branch_id'])) {
            $requested=filter_var($_GET['branch_id'],FILTER_VALIDATE_INT);
            if(!$requested || !in_array($requested,array_map('intval',array_column($branches,'id')),true)){set_flash('danger','Select an active pickup branch.');redirect('?route=cart');}
            if(($_SESSION['online_branch_id'] ?? null)!==$requested)$_SESSION['online_checkout_key']=bin2hex(random_bytes(32));
            $_SESSION['online_branch_id']=$requested;
        }
        $selected_branch=$_SESSION['online_branch_id'] ?? null;
        if($selected_branch && !in_array((int)$selected_branch,array_map('intval',array_column($branches,'id')),true)){unset($_SESSION['online_branch_id']);$selected_branch=null;}
        $_SESSION['cart']=Cart::get_items(session_id(),$_SESSION['user']['id'] ?? null,$selected_branch);$cart=$_SESSION['cart'];
        if(empty($_SESSION['online_checkout_key']))$_SESSION['online_checkout_key']=bin2hex(random_bytes(32));
        require __DIR__.'/../views/website/cart.php';
    }
    private function cart_write() {if($_SERVER['REQUEST_METHOD']!=='POST')access_denied('Use the cart buttons to make changes.',405);enforce_csrf();}
    private function cart_result($message,$success=true) {
        $_SESSION['cart']=Cart::get_items(session_id(),$_SESSION['user']['id'] ?? null);
        if(isset($_POST['ajax']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')==='xmlhttprequest') {header('Content-Type: application/json');echo json_encode(['status'=>$success?'success':'error','message'=>$message,'cart_count'=>array_sum(array_column($_SESSION['cart'],'qty'))]);return;}
        set_flash($success?'success':'danger',$message);redirect('?route=cart');
    }
    public function cart_add() {
        $this->cart_write();
        try {Cart::add((int)AuthSecurity::input('product_id'),AuthSecurity::input('qty'),session_id(),$_SESSION['user']['id'] ?? null);$_SESSION['online_checkout_key']=bin2hex(random_bytes(32));$this->cart_result('Added to your cart.');}
        catch(DomainException $e){$this->cart_result($e->getMessage(),false);}
    }
    public function cart_update() {
        $this->cart_write();try {Cart::update((int)AuthSecurity::input('product_id'),AuthSecurity::input('qty'),session_id(),$_SESSION['user']['id'] ?? null);$_SESSION['online_checkout_key']=bin2hex(random_bytes(32));$this->cart_result('Cart updated.');}catch(DomainException $e){$this->cart_result($e->getMessage(),false);}
    }
    public function cart_remove() {$this->cart_write();Cart::remove((int)AuthSecurity::input('product_id'),session_id(),$_SESSION['user']['id'] ?? null);$_SESSION['online_checkout_key']=bin2hex(random_bytes(32));$this->cart_result('Item removed.');}
    public function checkout() {
        require_login();$this->cart_write();
        $key=AuthSecurity::input('request_key');if(empty($_SESSION['online_checkout_key'])||!hash_equals($_SESSION['online_checkout_key'],$key))access_denied('Your cart changed. Reload it before ordering.',409);
        $wait=AuthSecurity::consume('online-checkout', (string)$_SESSION['user']['id'],10,3600);if($wait){set_flash('danger','Too many order attempts. Please try again later.');redirect('?route=cart');}
        try {$order=OnlineOrder::place((int)$_SESSION['user']['id'],session_id(),(int)AuthSecurity::input('branch_id'),AuthSecurity::input('name'),AuthSecurity::input('email'),AuthSecurity::input('phone'),$key);$_SESSION['cart']=[];set_flash('success','Your order is placed. Keep your tracking number for pickup.');redirect('?route=order_tracking&tracking='.rawurlencode($order['tracking_number']));}
        catch(DomainException $e){set_flash('danger',$e->getMessage());redirect('?route=cart');}
        catch(Throwable $e){error_log('Online checkout failed: '.$e->getMessage());set_flash('danger','Your order could not be placed. Your cart has been kept. Please try again.');redirect('?route=cart');}
    }
    public function orders() {require_login();$orders=OnlineOrder::list_for((int)$_SESSION['user']['id']);require __DIR__.'/../views/website/orders.php';}
    public function order_tracking() {
        require_login();$tracking=isset($_GET['tracking'])&&is_string($_GET['tracking'])?$_GET['tracking']:'';
        $order=OnlineOrder::detail($tracking,(int)$_SESSION['user']['id']);if(!$order)access_denied('Order not found in your account.',404);
        require __DIR__.'/../views/website/order_tracking.php';
    }
}
