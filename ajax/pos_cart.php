<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/ActivityLog.php';
require_once __DIR__.'/../models/Promotion.php';
require_once __DIR__.'/../models/AuthSecurity.php';
require_once __DIR__.'/../models/CheckoutPolicy.php';

header('Content-Type: application/json');
$action = $_POST['action'] ?? '';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('pos_cart', $action);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// CSRF Validation


// RBAC Check


if (!isset($_SESSION['pos_cart'])) {
    $_SESSION['pos_cart'] = [];
}
if (!isset($_SESSION['pos_discount'])) {
    $_SESSION['pos_discount'] = 0.00;
}
if (!isset($_SESSION['pos_order_type'])) {
    $_SESSION['pos_order_type'] = 'Dine-in';
}

$_SESSION['pos_checkout_key'] = $_SESSION['pos_checkout_key'] ?? bin2hex(random_bytes(16));

try {
require_once __DIR__.'/../models/CashShift.php';
if(CashShift::posIntegrated() && !in_array($action,['load','get_receipt'],true)) {
    RecipeInventory::atomic(function(){CashShift::context((int)$_SESSION['user']['id'],RecipeInventory::branch($_SESSION['pos_branch_id'] ?? null),true);});
}

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
        $name = '';
        $price = 0;
        $branch = RecipeInventory::branch($_SESSION['pos_branch_id'] ?? null);
        $product = RecipeInventory::product($id);
        $max_stock = RecipeInventory::availability($product,$branch);
        $name = $product['name'];
        $price = (float)$product['price'];
        $modifiers_raw = $_POST['modifiers'] ?? '';
        $modifiers = $modifiers_raw ? json_decode($modifiers_raw, true) : [];
        if (!is_array($modifiers)) throw new DomainException('Invalid modifiers.');
        $quote = RecipeInventory::quoteItem($id,1,$modifiers,$branch);
        $modifiers = $quote['modifiers'];
        if (empty($_SESSION['pos_cart'])) $_SESSION['pos_checkout_key'] = bin2hex(random_bytes(16));
        $productId = $id;
        $signature = array_column($modifiers,'id'); sort($signature);
        $lineId = null;
        foreach ($_SESSION['pos_cart'] as $cartId=>$cartItem) {
            $existing = array_column($cartItem['modifiers'] ?? [],'id'); sort($existing);
            if ((int)($cartItem['product_id'] ?? $cartItem['id']) === $productId && $existing === $signature) $lineId = $cartId;
        }
        if ($lineId === null) {
            $_SESSION['pos_line_seq'] = max((int)($_SESSION['pos_line_seq'] ?? 0), ...array_merge([0], array_map('intval', array_keys($_SESSION['pos_cart'])))) + 1;
            $lineId = $_SESSION['pos_line_seq'];
        }
        $id = $lineId;

        // Calculate modifier extra cost
        $modifier_extra = 0;
        foreach ($modifiers as $m) {
            $modifier_extra += (float)($m['additional_price'] ?? 0);
        }

        if ($max_stock <= 0) throw new DomainException('This product has no available stock at the selected branch.');
        if ($id > 0 && $max_stock > 0) {
            $product = Product::find_by_id($productId);
            $image_url = $product ? get_product_image($product['image_url'], $product['id']) : '';

            if (isset($_SESSION['pos_cart'][$id])) {
                if ($_SESSION['pos_cart'][$id]['quantity'] < $max_stock) {
                    $_SESSION['pos_cart'][$id]['quantity']++;
                    // Recalculate subtotal including stored modifiers
                    $existing_modifiers = $_SESSION['pos_cart'][$id]['modifiers'] ?? [];
                    $mod_total = $price;
                    foreach ($existing_modifiers as $em) {
                        $mod_total += (float)($em['additional_price'] ?? 0);
                    }
                    $_SESSION['pos_cart'][$id]['subtotal'] = $_SESSION['pos_cart'][$id]['quantity'] * $mod_total;
                    $_SESSION['pos_cart'][$id]['image_url'] = $image_url;
                } else {
                    echo json_encode(['success' => false, 'message' => 'Cannot exceed available stock']);
                    exit;
                }
            } else {
                $_SESSION['pos_cart'][$id] = [
                    'id' => $id,
                    'product_id' => $productId,
                    'name' => $name,
                    'price' => $price,
                    'quantity' => 1,
                    'subtotal' => $price + $modifier_extra,
                    'max_stock' => $max_stock,
                    'image_url' => $image_url,
                    'bundle_components'=>$quote['bundle_components']??[],
                    'modifiers' => $modifiers
                ];
            }
        }
        break;

    case 'update_qty':
        $id = (int)($_POST['product_id'] ?? 0);
        $rawQuantity = (string)($_POST['quantity'] ?? '0');
        if (!preg_match('/^(0|[1-9][0-9]{0,5})$/D',$rawQuantity)) throw new DomainException('Enter a whole sale quantity.');
        $qty = (int)$rawQuantity;

        if (isset($_SESSION['pos_cart'][$id])) {
            $branch = RecipeInventory::branch($_SESSION['pos_branch_id'] ?? null);
            $max_stock = RecipeInventory::availability(RecipeInventory::product($_SESSION['pos_cart'][$id]['product_id'] ?? $id),$branch);
            $_SESSION['pos_cart'][$id]['max_stock'] = $max_stock;
            if ($qty > 0 && $qty <= $max_stock) {
                $_SESSION['pos_cart'][$id]['quantity'] = $qty;
                $mod_total = $_SESSION['pos_cart'][$id]['price'];
                $existing_modifiers = $_SESSION['pos_cart'][$id]['modifiers'] ?? [];
                foreach ($existing_modifiers as $em) {
                    $mod_total += (float)($em['additional_price'] ?? 0);
                }
                $_SESSION['pos_cart'][$id]['subtotal'] = $qty * $mod_total;
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
        unset($_SESSION['pos_promo_code'],$_SESSION['pos_promo_customer'],$_SESSION['pos_discount_approval']);
        $_SESSION['pos_cart'] = [];
        $_SESSION['pos_discount'] = 0.00;
        break;

    case 'get_promotions':
        $branch=RecipeInventory::branch($_SESSION['pos_branch_id']??null);$email=trim(is_string($_POST['customer_email']??null)?$_POST['customer_email']:'');$account=$email===''?null:User::find_by_email($email);$customer=$account&&!User::access_context($account['id'])['staff']?(int)$account['id']:null;
        echo json_encode(['success'=>true,'offers'=>Promotion::available(Promotion::posQuotes($_SESSION['pos_cart'],$branch),$branch,'pos',$customer)]);exit;
    case 'set_promotion':
        if(AuthSecurity::consume('pos-promo-code',(string)$_SESSION['user']['id'],30,60))throw new DomainException('Please wait before trying another promo code.');
        $branch=RecipeInventory::branch($_SESSION['pos_branch_id']??null);$code=$_POST['promo_code']??'';
        $email=trim(is_string($_POST['customer_email']??'')?($_POST['customer_email']??''):'');$account=$email===''?null:User::find_by_email($email);$customer=$account?(int)$account['id']:null;
        if($email!==''&&(!$account||User::access_context($customer)['staff']))throw new DomainException('Enter the email of a registered customer account.');
        Promotion::financial(Promotion::posQuotes($_SESSION['pos_cart'],$branch),$branch,'pos',$customer,$code);
        $_SESSION['pos_promo_code']=strtoupper(trim($code));$_SESSION['pos_promo_customer']=$customer;$_SESSION['pos_discount']=0;unset($_SESSION['pos_discount_approval']);break;
    case 'request_discount_approval':
        if(AuthSecurity::consume('discount-approval',(string)$_SESSION['user']['id'],15,3600))throw new DomainException('Too many approval requests. Please try again later.');
        $branch=RecipeInventory::branch($_SESSION['pos_branch_id']??null);
        $_SESSION['pos_discount_approval']=Promotion::requestApproval((int)$_SESSION['user']['id'],$branch,Promotion::posQuotes($_SESSION['pos_cart'],$branch),$_POST['discount']??'0',$_POST['reason']??'');
        echo json_encode(['success'=>true,'approval_id'=>$_SESSION['pos_discount_approval'],'message'=>'Approval requested. A supervisor can review it under Promos & Discounts. Once approved, apply your manual discount again.']);exit;
    case 'set_discount':
        $discount = (float)($_POST['discount'] ?? 0);
        Promotion::financial(Promotion::posQuotes($_SESSION['pos_cart'],RecipeInventory::branch($_SESSION['pos_branch_id']??null)),RecipeInventory::branch($_SESSION['pos_branch_id']??null),'pos',null,'',$_POST['discount']??'0',false,(int)$_SESSION['user']['id'],$_SESSION['pos_discount_approval']??null);
        unset($_SESSION['pos_promo_code']);
        $_SESSION['pos_discount'] = $discount;
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
            'receipt' => CheckoutPolicy::forSale($sale_id),
            'receipt' => CheckoutPolicy::forSale($sale_id),
            'store' => [
                'name' => $settings['store_name'] ?? 'Stacey Halu',
                'address' => $settings['store_address'] ?? '123 Sweet Tooth Blvd, Sugar City',
                'phone' => $settings['store_phone'] ?? '+63 912 345 6789'
            ]
        ]);
        exit;
        break;

    case 'checkout':
        $requestKey = $_POST['request_key'] ?? '';
        if (!hash_equals($_SESSION['pos_checkout_key'], $requestKey)) throw new DomainException('Cart changed. Reload before checkout.');
        $prior = RecipeInventory::rows('SELECT id, cashier_id FROM sales WHERE request_key = ?', 's', [$requestKey]);
        if ($prior && (int)$prior[0]['cashier_id'] === (int)$_SESSION['user']['id']) {
            echo json_encode(['success'=>true,'sale_id'=>(int)$prior[0]['id'],'message'=>'Order already completed.']);
            exit;
        }
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

        $change_amount = $amount_tendered - $final_amount;
        $cashier_id = $_SESSION['user']['id'];
        $branch_id = RecipeInventory::branch($_SESSION['pos_branch_id'] ?? null);

        $items = [];
        foreach ($_SESSION['pos_cart'] as $item) {
            $items[] = [
                'product_id' => $item['product_id'] ?? $item['id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'modifiers' => $item['modifiers'] ?? []
            ];
        }

        $sale_id = Sale::create_sale($cashier_id, $branch_id, $subtotal, $discount, $final_amount, $amount_tendered, $change_amount, $payment_method, $items, $order_type,CashShift::posIntegrated(),['code'=>$_SESSION['pos_promo_code']??'','customer_id'=>$_SESSION['pos_promo_customer']??null,'approval_id'=>$_SESSION['pos_discount_approval']??null,'expected_total'=>$_SESSION['pos_expected_total']??null]);

        if ($sale_id) {
            $actual=RecipeInventory::rows('SELECT final_amount FROM sales WHERE id = ?','i',[$sale_id])[0];
            $final_amount=(float)$actual['final_amount'];
            unset($_SESSION['pos_promo_code'],$_SESSION['pos_promo_customer'],$_SESSION['pos_discount_approval']);
            $_SESSION['pos_cart'] = [];
            $_SESSION['pos_discount'] = 0.00;
            $_SESSION['pos_order_type'] = 'Dine-in';

            ActivityLog::log('Order Completed', "Order #{$sale_id} completed — ₱" . number_format($final_amount, 2) . " via {$payment_method}");

            // Retrieve updated stock levels
            $updated_stocks = [];
            $res_stocks = $conn->query("SELECT * FROM products");
            if ($res_stocks) {
                while ($row = $res_stocks->fetch_assoc()) {
                    $updated_stocks[] = [
                        'id' => (int)$row['id'],
                        'stock' => RecipeInventory::availability($row,$branch_id),
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
            echo json_encode(['success' => false, 'message' => $_SESSION['checkout_error'] ?? 'Failed to process transaction']);
            exit;
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        exit;
}

} catch (Throwable $error) {
    error_log('Cart request failed: ' . $error->getMessage());
    echo json_encode(['success'=>false,'message'=>($error instanceof DomainException || $error instanceof InvalidArgumentException)
        ? $error->getMessage() : 'Cart could not be processed. Check the inventory migration.']);
    exit;
}

// Calculate current summary
$subtotal = array_sum(array_column($_SESSION['pos_cart'], 'subtotal'));
$discount = $_SESSION['pos_discount'];
try{$financial=Promotion::financial(Promotion::posQuotes($_SESSION['pos_cart'],RecipeInventory::branch($_SESSION['pos_branch_id']??null)),RecipeInventory::branch($_SESSION['pos_branch_id']??null),'pos',$_SESSION['pos_promo_customer']??null,$_SESSION['pos_promo_code']??'',(string)$discount,false,(int)$_SESSION['user']['id'],$_SESSION['pos_discount_approval']??null);$discount=$financial['discount_cents']/100;$_SESSION['pos_expected_total']=$financial['total_cents'];$total=$financial['total_cents']/100;}catch(DomainException $e){
    // Cart edits must still render when they invalidate a selected offer or approval.
    if(in_array($action,['add','update_qty','remove'],true)){
        try{
            $branch=RecipeInventory::branch($_SESSION['pos_branch_id']??null);
            $financial=Promotion::financial(Promotion::posQuotes($_SESSION['pos_cart'],$branch),$branch,'pos',$_SESSION['pos_promo_customer']??null,'','0',false,(int)$_SESSION['user']['id'],null);
            unset($_SESSION['pos_promo_code'],$_SESSION['pos_discount_approval']);
            $_SESSION['pos_discount']=0.00;$discount=0;$total=$financial['total_cents']/100;$_SESSION['pos_expected_total']=$financial['total_cents'];
            $discountNotice='The previous discount no longer applies to this cart and was removed. Select another offer if needed.';
        }catch(DomainException $invalidCart){echo json_encode(['success'=>false,'message'=>$invalidCart->getMessage()]);exit;}
    }else{echo json_encode(['success'=>false,'message'=>$e->getMessage()]);exit;}
}

// Backfill image_url for any items missing it (e.g. from existing sessions)
foreach ($_SESSION['pos_cart'] as $id => $item) {
    if(RecipeInventory::product($item['product_id']??$id)['product_type']==='combo'){
        $bundleQuote=RecipeInventory::quoteItem($item['product_id']??$id,$item['quantity'],$item['modifiers']??[],RecipeInventory::branch($_SESSION['pos_branch_id']??null));
        $_SESSION['pos_cart'][$id]['bundle_components']=$bundleQuote['bundle_components'];
    }
    if (empty($item['image_url'])) {
        $product = Product::find_by_id($item['product_id'] ?? $id);
        $_SESSION['pos_cart'][$id]['image_url'] = $product ? get_product_image($product['image_url'], $product['id']) : '';
    }
}

echo json_encode([
    'success' => true,
    'cart' => array_values($_SESSION['pos_cart']),
    'request_key' => $_SESSION['pos_checkout_key'],
    'subtotal' => $subtotal,
    'discount' => $discount,
    'total' => $total,
    'discount_notice'=>$discountNotice??null,
    'manual_discount'=>(float)$_SESSION['pos_discount'],
    'promo_code'=>$_SESSION['pos_promo_code']??'',
    'promotion'=>$financial['promotion']??null,
    'financial' => $financial
]);
