<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/ActivityLog.php';


header('Content-Type: application/json');
$action = $_POST['action'] ?? '';
require_once __DIR__ . '/../middleware/access_policy.php';
authorize_action('inventory_action', $action);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}





try {
// Product helper endpoints obey the same branch boundary as the main editor.
if (in_array($action,['get_next_batch','get_product_modifiers','save_product_modifiers','check_recipe_availability','get_active_batches','get_batch_breakdown','get_product_recipe','save_product_recipe','prepare_recipe_batch','get_product_variants','save_product_variants','get_product_attribute_values','save_product_attributes'],true) && !empty($_POST['product_id'])) {
    RecipeInventory::branch(RecipeInventory::product((int)$_POST['product_id'])['branch_id']);
}
switch ($action) {
    case 'stock_transfer_create':
        require_once __DIR__.'/../models/StockTransfer.php';
        $id=StockTransfer::create($_POST['source_branch_id'] ?? '',$_POST['destination_branch_id'] ?? '',is_array($_POST['items'] ?? null) ? $_POST['items'] : [],$_POST['notes'] ?? '',$_POST['request_key'] ?? '');
        echo json_encode(['success'=>true,'message'=>'Stock transfer requested. Stock is unchanged.','redirect'=>url('?route=inventory/transfers&id='.$id)]);
        break;
    case 'stock_transfer_approve':
    case 'stock_transfer_reject':
    case 'stock_transfer_cancel':
    case 'stock_transfer_dispatch':
    case 'stock_transfer_receive':
        require_once __DIR__.'/../models/StockTransfer.php';
        $id=StockTransfer::transition((int)($_POST['transfer_id'] ?? 0),substr($action,15),$_POST['request_key'] ?? '',$_POST['notes'] ?? '',is_array($_POST['received'] ?? null) ? $_POST['received'] : []);
        echo json_encode(['success'=>true,'message'=>'Transfer updated.','redirect'=>url('?route=inventory/transfers&id='.$id)]);
        break;
    case 'transfer_product_branch':
        require_once __DIR__ . '/../models/ProductTransfer.php';
        $transfer=ProductTransfer::run((int)($_POST['product_id'] ?? 0),$_POST['destination_branch_id'] ?? '',$_SESSION['user']['id'],$_POST['request_key'] ?? '',(string)($_POST['source_branch_id'] ?? ''));
        ActivityLog::log('Product Branch Transfer','Transferred product #'.$transfer['product_id'].' to '.$transfer['destination_name'].'; operation recorded.');
        set_flash('success','Product and its stock transferred to '.$transfer['destination_name'].'.');
        echo json_encode(['success'=>true,'message'=>'Product and its stock transferred to '.$transfer['destination_name'].'.','transfer'=>$transfer]);
        break;
    case 'get_category_products':
        $cat_id = (int) ($_POST['category_id'] ?? 0);
        $products = Product::get_all_products($cat_id);
        echo json_encode(['success' => true, 'products' => $products]);
        break;

    case 'quick_restock':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $quantity = $_POST['quantity'] ?? '0';
        $remarks = $_POST['remarks'] ?? 'Quick restock via AJAX';
        $user_id = $_SESSION['user']['id'];

        if ($product_id > 0 && $quantity > 0) {
            $success = Product::adjust_stock($product_id, $user_id, 'stock_in', $quantity, $remarks);
            if ($success) {
                ActivityLog::log('Quick Restock', "Restocked product #{$product_id} by {$quantity}");
            }
            echo json_encode(['success' => $success, 'message' => $success ? 'Stock updated successfully' : 'Failed to update stock']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    case 'adjust_stock':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $quantity = $_POST['quantity'] ?? '0';
        $adj_type = $_POST['adjustment_type'] ?? 'stock_in';
        $remarks = $_POST['remarks'] ?? 'Manual adjustment via AJAX';
        $user_id = $_SESSION['user']['id'];
        $batch_number = $_POST['batch_number'] ?? null;
        $cost_price = isset($_POST['cost_price']) ? (float) $_POST['cost_price'] : null;
        $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
        $selected_batch_id = !empty($_POST['selected_batch_id']) ? (int) $_POST['selected_batch_id'] : null;
        $expiry_date = $_POST['expiry_date'] ?? null;
        $selling_price = isset($_POST['selling_price']) ? (float) $_POST['selling_price'] : null;

        if ($product_id > 0 && $quantity > 0 && in_array($adj_type, ['stock_in', 'stock_out', 'waste'])) {
            $success = Product::adjust_stock($product_id, $user_id, $adj_type, $quantity, $remarks, $batch_number, $cost_price, $branch_id, $selected_batch_id, $expiry_date, $selling_price);
            if ($success) {
                ActivityLog::log('Stock Adjustment', "{$adj_type} product #{$product_id} by {$quantity} — {$remarks}");
            }
            $recipeAddition=$adj_type==='stock_in' && RecipeInventory::product($product_id)['product_type']==='recipe';
            echo json_encode(['success' => $success, 'message' => $success ? ($recipeAddition ? 'Finished stock added. Recipe ingredients consumed once; sales will use finished stock.' : 'Stock adjusted successfully') : 'Failed to adjust stock']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
        }
        break;

    case 'get_next_batch':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            $next_batch = Product::get_next_batch_number($product_id);
            echo json_encode(['success' => true, 'batch_number' => $next_batch]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'get_next_product_id':
        $next_id = Product::get_next_product_id();
        echo json_encode(['success' => true, 'next_id' => $next_id]);
        break;

    case 'get_product_modifiers':
        $prod_id = intval($_POST['product_id'] ?? 0);
        RecipeInventory::branch(RecipeInventory::product($prod_id)['branch_id']);
        $mods = Product::get_modifiers($prod_id);
        echo json_encode(['success' => true, 'modifiers' => $mods]);
        break;

    case 'save_product_modifiers':
        $prod_id = intval($_POST['product_id'] ?? 0);
        $modifiers = $_POST['modifiers'] ?? [];
        if ($prod_id > 0 && Product::save_modifiers($prod_id, $modifiers)) {
            ActivityLog::log('Modifiers Saved', "Saved modifiers for product #{$prod_id}");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to save modifiers']);
        }
        break;

    case 'check_recipe_availability':
        $prod_id = intval($_POST['product_id'] ?? 0);
        $qty = max(1, intval($_POST['quantity'] ?? 1));
        $result = Product::check_recipe_stock($prod_id, $qty);
        echo json_encode(array_merge(['success' => true], $result));
        break;

    case 'products_datatable':
        $draw = (int) ($_POST['draw'] ?? 0);
        $start = (int) ($_POST['start'] ?? 0);
        $length = (int) ($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $order_col = (int) ($_POST['order'][0]['column'] ?? 0);
        $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

        $branch_id = empty($_POST['branch_id']) && User::has_all_branch_access($_SESSION['user']['id']) ? null : RecipeInventory::branch($_POST['branch_id'] ?? null);
        $category_id = !empty($_POST['category_id']) ? (int) $_POST['category_id'] : null;
        $stock_status = $_POST['stock_status'] ?? 'all';

        $products = Product::get_dt_products($start, $length, $search, $order_col, $order_dir, $branch_id, $category_id, $stock_status);
        $recordsTotal = Product::get_total_products_count();
        $recordsFiltered = Product::count_dt_products_filtered($search, $branch_id, $category_id, $stock_status);

        // Enrich products with branch stocks and performance
        $global_perf = Product::get_branch_performance_stats(null); // Global
        $active_branches = Branch::for_current_user();
        $branch_perf_data = [];
        foreach ($active_branches as $ab) {
            $branch_perf_data[$ab['id']] = Product::get_branch_performance_stats($ab['id']);
        }

        foreach ($products as &$prod) {
            require_once __DIR__.'/../models/ProductTransfer.php';
            $prod['can_transfer'] = ProductTransfer::canTransfer($_SESSION['user']['id'],$prod['branch_id']);
            $prod['can_request_transfer'] = User::has_permission($_SESSION['user']['id'],'transfer_products') && $prod['track_stock'] && !in_array($prod['product_type'],['variant','service'],true) && !in_array($prod['inventory_type'],['non-stock','unlimited'],true) && ($prod['product_type']!=='recipe' || $prod['recipe_mode']==='prepared_batch');
            $prod['can_view_history'] = User::has_permission($_SESSION['user']['id'],'view_stock_history');
            $prod['branch_stocks'] = Product::get_branch_stocks($prod['id']);
            if ($prod['product_type'] === 'recipe' && ($prod['recipe_mode'] ?? '') === 'made_to_order') {
                $prod['stock'] = RecipeInventory::catalogAvailability($prod,$branch_id);
                foreach ($prod['branch_stocks'] as &$location) {
                    $location['branch_stock'] = RecipeInventory::availability($prod,(int)$location['id']);
                }
                unset($location);
                $prod['stock_is_recipe_capacity'] = true;
            }
            $prod['global_performance_badge'] = $global_perf[$prod['id']] ?? null;
            $prod['branch_performance_badges'] = [];
            foreach ($active_branches as $ab) {
                $prod['branch_performance_badges'][$ab['id']] = $branch_perf_data[$ab['id']][$prod['id']] ?? null;
            }
            // Add permission checks or other derived data here if needed
            $prod['can_edit'] = User::has_permission($_SESSION['user']['id'], 'edit_product');
            $prod['can_delete'] = User::has_permission($_SESSION['user']['id'], 'delete_product');
            $prod['can_manage_stock'] = User::has_permission($_SESSION['user']['id'], 'manage_inventory');
            $prod['image_path'] = get_product_image($prod['image_url'], $prod['id']);

            // For variant products, attach variant price range and total stock for display
            if ($prod['product_type'] === 'variant') {
                $variants = Product::get_variants($prod['id']);
                $v_prices = array_map(function ($v) { return (float) $v['price']; }, $variants);
                $v_stocks = array_map(function ($v) { return (int) $v['stock']; }, $variants);
                $prod['variant_min_price'] = $v_prices ? min($v_prices) : 0;
                $prod['variant_max_price'] = $v_prices ? max($v_prices) : 0;
                $prod['variant_total_stock'] = $v_stocks ? array_sum($v_stocks) : 0;
            }
        }

        $stats = Product::get_inventory_stats($branch_id, $category_id, $search, $stock_status);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $products,
            "stats" => $stats
        ]);
        break;

    case 'get_categories':
        $categories = $GLOBALS['conn']->query("SELECT id, name FROM categories ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'categories' => $categories]);
        break;

    case 'get_active_batches':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
        if ($product_id > 0) {
            $batches = Product::get_active_batches($product_id, $branch_id);
            echo json_encode(['success' => true, 'batches' => $batches]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        }
        break;

    case 'get_branches':
        $branches = Branch::for_current_user();
        echo json_encode(['success' => true, 'branches' => $branches]);
        break;

    case 'get_batch_breakdown':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            $data = Product::get_product_batch_breakdown($product_id);
            echo json_encode(['success' => true, 'batches' => $data['batches'], 'was_synced' => $data['was_synced'], 'base_cost' => $data['product_base_cost'], 'base_price' => $data['product_base_price']]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        }
        break;

    case 'update_batch_data':
        $batch_id = (int) ($_POST['batch_id'] ?? 0);
        $quantity = $_POST['quantity'] ?? '0';
        $cost_price = (float) ($_POST['cost_price'] ?? 0);
        $selling_price = isset($_POST['selling_price']) ? (float) $_POST['selling_price'] : null;

        if ($batch_id > 0) {
            $success = Product::update_product_batch($batch_id, $quantity, $cost_price, $selling_price);
            if ($success) {
                ActivityLog::log('Batch Updated', "Updated batch #{$batch_id} — qty: {$quantity}, cost: {$cost_price}");
            }
            echo json_encode(['success' => $success]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid batch ID']);
        }
        break;

    case 'get_product_recipe':
        $product_id = (int) ($_POST['product_id'] ?? 0);
        if ($product_id > 0) {
            $recipeProduct=RecipeInventory::product($product_id);
            RecipeInventory::branch($recipeProduct['branch_id']);
            $recipe = Product::get_recipes($product_id);
            $physicalBranch=RecipeInventory::branch($recipeProduct['branch_id'] ?? ($_POST['branch_id'] ?? null));
            echo json_encode(['success' => true, 'recipe' => $recipe, 'product'=>['type'=>$recipeProduct['product_type'],'mode'=>$recipeProduct['recipe_mode'] ?? 'legacy','branch_id'=>$recipeProduct['branch_id'],'physical_stock'=>InventoryQuantity::format(RecipeInventory::balance($product_id,$physicalBranch)),'global_access'=>User::has_all_branch_access($_SESSION['user']['id'])]]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID']);
        }
        break;

    case 'save_product_recipe':
        $product_id = (int)($_POST['product_id'] ?? 0);
        RecipeInventory::atomic(function () use ($product_id) {
            $mods = isset($_POST['modifiers_json']) ? json_decode($_POST['modifiers_json'],true) : null;
            if (isset($_POST['modifiers_json']) && !is_array($mods)) throw new DomainException('Invalid extras.');
            RecipeInventory::lockProducts(array_merge([$product_id],array_column($_POST['ingredients'] ?? [],'ingredient_id'),array_filter(array_column($mods ?? [],'ingredient_product_id'))));
            Product::save_recipes($product_id, $_POST['ingredients'] ?? []);
            if ($mods !== null) Product::save_modifiers($product_id,$mods);
        });
        echo json_encode(['success'=>true,'message'=>'Recipe saved. Stock is unchanged.']);
        break;

    case 'prepare_recipe_batch':
        $batch = RecipeInventory::prepare((int)($_POST['product_id'] ?? 0), $_POST['quantity'] ?? '', $_SESSION['user']['id'], $_POST['branch_id'] ?? null, $_POST['request_key'] ?? '');
        echo json_encode(['success'=>true,'batch_id'=>$batch,'message'=>'Batch prepared; ingredients consumed once.']);
        break;

    case 'reverse_sale_stock':
        if (!User::has_permission($_SESSION['user']['id'], 'manage_inventory')) throw new DomainException('Inventory permission is required.');
        RecipeInventory::reverseSale((int)($_POST['sale_id'] ?? 0),$_POST['policy'] ?? '',$_SESSION['user']['id'],$_POST['request_key'] ?? '');
        echo json_encode(['success'=>true,'message'=>'Recorded stock return completed once.']);
        break;

    case 'record_waste':
        if (!User::has_permission($_SESSION['user']['id'], 'manage_inventory')) throw new DomainException('Inventory permission is required.');
        RecipeInventory::adjust((int)($_POST['product_id'] ?? 0), $_SESSION['user']['id'], 'waste', $_POST['quantity'] ?? '', 'Waste: ' . ($_POST['remarks'] ?? ''), $_POST['branch_id'] ?? null, null,null,null,null,null,$_POST['request_key'] ?? '');
        echo json_encode(['success'=>true,'message'=>'Waste recorded.']);
        break;

    case 'get_ingredients_list':
        // Get all products marked as ingredients
        $ingredients = Product::get_all_ingredients();
        echo json_encode(['success' => true, 'ingredients' => $ingredients]);
        break;

    case 'logs_datatable':
        $draw = (int) ($_POST['draw'] ?? 0);
        $start = (int) ($_POST['start'] ?? 0);
        $length = (int) ($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $order_col = (int) ($_POST['order'][0]['column'] ?? 0);
        $order_dir = $_POST['order'][0]['dir'] ?? 'desc';

        $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
        $product_id = !empty($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        $start_date = !empty($_POST['start_date']) ? $_POST['start_date'] : null;
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;

        $logs = Product::get_dt_inventory_logs($start, $length, $search, $order_col, $order_dir, $branch_id, $start_date, $end_date, $product_id);
        $recordsTotal = Product::get_total_inventory_logs_count($branch_id, $start_date, $end_date, $product_id);
        $recordsFiltered = Product::count_dt_inventory_logs_filtered($search, $branch_id, $start_date, $end_date, $product_id);

        // Stats for current filtered view (or total if no filter)
        $total_in = 0;
        $total_out = 0;

        // This is a bit expensive if we query all, but for stats we usually want the totals of the filtered set
        // However, for simplicity and performance, we'll return the stats for the whole system or the branch if specified
        $product_id = !empty($_POST['product_id']) ? (int) $_POST['product_id'] : null;
        $all_logs = Product::get_inventory_logs_stats($branch_id, $start_date, $end_date, $product_id);
        foreach ($all_logs as $l) {
            $is_in = ($l['action'] === 'stock_in' || $l['action'] === 'initial' || $l['action'] === 'Purchase');
            if ($is_in)
                $total_in += $l['quantity'];
            else
                $total_out += $l['quantity'];
        }

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $logs,
            "stats" => [
                "total_in" => $total_in,
                "total_out" => $total_out,
                "net" => $total_in - $total_out,
                "count" => $recordsFiltered
            ]
        ]);
        break;

    case 'get_attributes':
        $attributes = Product::get_attributes();
        echo json_encode(['success' => true, 'attributes' => $attributes]);
        break;

    case 'save_attribute':
        $name = sanitize($_POST['name'] ?? '');
        if (Product::save_attribute($name)) {
            ActivityLog::log('Attribute Created', "Created attribute '{$name}'");
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;

    case 'get_product_variants':
        $prod_id = intval($_POST['product_id'] ?? 0);
        $variants = Product::get_variants($prod_id);
        echo json_encode(['success' => true, 'variants' => $variants]);
        break;

    case 'save_product_variants':
        $prod_id = intval($_POST['product_id'] ?? 0);
        $variants = $_POST['variants'] ?? [];
        if (!is_array($variants)) {
            $variants = [];
        }
        if ($prod_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
            exit;
        }
        try {
            if (Product::save_variants($prod_id, $variants)) {
                ActivityLog::log('Variants Saved', "Saved variants for product #{$prod_id}");
                echo json_encode(['success' => true, 'message' => 'Variants saved successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save variants in the database.']);
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'get_product_attribute_values':
        $prod_id = intval($_POST['product_id'] ?? 0);
        $values = Product::get_product_attribute_values($prod_id);
        echo json_encode(['success' => true, 'values' => $values]);
        break;

    case 'save_product_attributes':
        $prod_id = intval($_POST['product_id'] ?? 0);
        $attributes = $_POST['attributes'] ?? [];
        if (!is_array($attributes)) {
            $attributes = [];
        }
        if ($prod_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
            exit;
        }
        $clean_attrs = [];
        foreach ($attributes as $k => $v) {
            $trimmed_k = trim($k);
            if ($trimmed_k !== '') {
                $clean_attrs[$trimmed_k] = trim($v);
            }
        }
        try {
            if (Product::save_product_attributes($prod_id, $clean_attrs)) {
                ActivityLog::log('Attributes Saved', "Updated custom attributes for product #{$prod_id}");
                echo json_encode(['success' => true, 'message' => 'Custom attributes saved successfully!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save attributes in database.']);
            }
        } catch (Throwable $e) {
            echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
        break;

    case 'sales_dt':
        require_once __DIR__ . '/../models/Sale.php';
        $draw = (int) ($_POST['draw'] ?? 0);
        $start = (int) ($_POST['start'] ?? 0);
        $length = (int) ($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $order_col = (int) ($_POST['order'][0]['column'] ?? 0);
        $order_dir = $_POST['order'][0]['dir'] ?? 'desc';
        $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
        $date_from = $_POST['date_from'] ?? null;
        $date_to = $_POST['date_to'] ?? null;

        $sales = Sale::get_dt_sales($start, $length, $search, $order_col, $order_dir, $branch_id, $date_from, $date_to);
        $recordsTotal = Sale::get_total_sales_count();
        $recordsFiltered = Sale::count_dt_sales_filtered($search, $branch_id, $date_from, $date_to);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $sales
        ]);
        break;

    case 'logs_dt':
        require_once __DIR__ . '/../models/Product.php';
        $draw = (int) ($_POST['draw'] ?? 0);
        $start = (int) ($_POST['start'] ?? 0);
        $length = (int) ($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $order_col = (int) ($_POST['order'][0]['column'] ?? 0);
        $order_dir = $_POST['order'][0]['dir'] ?? 'desc';
        $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
        $date_from = $_POST['date_from'] ?? null;
        $date_to = $_POST['date_to'] ?? null;

        $logs = Product::get_dt_inventory_logs($start, $length, $search, $order_col, $order_dir, $branch_id, $date_from, $date_to);
        $recordsTotal = Product::get_total_inventory_logs_count();
        $recordsFiltered = Product::count_dt_inventory_logs_filtered($search, $branch_id, $date_from, $date_to);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $logs
        ]);
        break;

    case 'expenses_dt':
        require_once __DIR__ . '/../models/Expense.php';
        $draw = (int) ($_POST['draw'] ?? 0);
        $start = (int) ($_POST['start'] ?? 0);
        $length = (int) ($_POST['length'] ?? 10);
        $search = $_POST['search']['value'] ?? '';
        $order_col = (int) ($_POST['order'][0]['column'] ?? 0);
        $order_dir = $_POST['order'][0]['dir'] ?? 'desc';
        $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
        $date_from = $_POST['date_from'] ?? null;
        $date_to = $_POST['date_to'] ?? null;

        $expenses = Expense::get_dt_expenses($start, $length, $search, $order_col, $order_dir, $branch_id, $date_from, $date_to);
        $recordsTotal = Expense::get_total_expenses_count();
        $recordsFiltered = Expense::count_dt_expenses_filtered($search, $branch_id, $date_from, $date_to);

        echo json_encode([
            "draw" => $draw,
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsFiltered,
            "data" => $expenses
        ]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
}

} catch (Throwable $error) {
    error_log('Inventory request failed: ' . $error->getMessage());
    echo json_encode(['success'=>false,'message'=>($error instanceof DomainException || $error instanceof InvalidArgumentException)
        ? $error->getMessage() : 'Inventory operation failed. Check the manual inventory migration.']);
}
