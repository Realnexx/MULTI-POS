<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Branch.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Expense.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class InventoryController
{
    public function transfers()
    {
        require_once __DIR__.'/../models/StockTransfer.php';
        require_permissions(StockTransfer::permissions());
        $transferReady=StockTransfer::ready();
        $transfers=$transferReady ? StockTransfer::listing() : [];
        $selectedTransfer=null;
        if($transferReady && !empty($_GET['id'])) {
            try {$selectedTransfer=StockTransfer::detail((int)$_GET['id']);} catch(DomainException $error) {access_denied($error->getMessage());}
        }
        $transferBranches=Branch::get_all_active();
        $sourceBranches=Branch::for_current_user();
        $transferProducts=User::has_permission($_SESSION['user']['id'],'transfer_products') ? RecipeInventory::rows("SELECT id,name,branch_id,product_type,unit_type,is_ingredient FROM products WHERE status='active' AND track_stock=1 AND inventory_type NOT IN ('non-stock','unlimited') AND product_type NOT IN ('variant','service') AND (product_type<>'recipe' OR recipe_mode='prepared_batch') ORDER BY name,id") : [];
        if($transferProducts) {
            $stockByProduct=[];
            foreach(RecipeInventory::rows("SELECT product_id,branch_id,SUM(quantity) quantity FROM product_batches WHERE status='active' AND quantity>0 AND (expiry_date IS NULL OR expiry_date>=CURRENT_DATE) GROUP BY product_id,branch_id") as $balance) {
                $stockByProduct[$balance['product_id']][(string)$balance['branch_id']]=$balance['quantity'];
            }
            foreach($transferProducts as &$transferProduct) $transferProduct['branch_quantities']=$stockByProduct[$transferProduct['id']] ?? (object)[];
            unset($transferProduct);
        }
        $approvalRequired=Setting::get('stock_transfer_require_approval','1')==='1';
        require __DIR__.'/../views/modules/stock_transfers.php';
    }
    public function recipes()
    {
        require_permission('view_product');
        require_once __DIR__ . '/../models/RecipeLibrary.php';
        enforce_csrf();
        $selected_id = (int)($_GET['id'] ?? 0);
        $form_data = null;
        $form_rows = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            require_permission('edit_product');
            $selected_id = (int)($_POST['recipe_id'] ?? 0);
            $form_data = $_POST;
            $form_rows = $_POST['ingredients'] ?? [];
            try {
                $saved = RecipeLibrary::save($selected_id, (string)($_POST['name'] ?? ''), (string)($_POST['description'] ?? ''), (int)($_POST['category_id'] ?? 0), $_POST['branch_id'] ?? null, $form_rows, (string)($_POST['request_key'] ?? ''));
                ActivityLog::log('Recipe Saved', 'Configured recipe #' . $saved . '; stock unchanged.');
                set_flash('success','Recipe saved. No ingredients or finished stock were added or deducted.');
                redirect('?route=inventory/recipes&id=' . $saved);
            } catch (DomainException $error) { set_flash('danger',$error->getMessage()); }
            catch (InvalidArgumentException $error) { set_flash('danger',$error->getMessage()); }
            catch (Throwable $error) { error_log('Recipe save failed: ' . $error->getMessage()); set_flash('danger','Recipe could not be saved. Please check the ingredients and try again.'); }
        }
        $recipes = RecipeLibrary::available();
        $selected = null;
        foreach ($recipes as $recipe) if ((int)$recipe['id'] === $selected_id) $selected = $recipe;
        if ($selected_id && !$selected) access_denied('This recipe is not available in your branch.',404);
        if (!$form_data) {
            $form_data = $selected ?: ['name'=>'','description'=>'','category_id'=>'','branch_id'=>''];
            if ($selected && $selected['branch_id'] === null) $form_data['branch_id'] = 'global';
            $form_rows = $selected ? array_map(static function ($row) { return ['ingredient_id'=>$row['ingredient_product_id'],'quantity'=>$row['quantity'],'unit'=>$row['unit']]; },Product::get_recipes($selected_id)) : [];
        }
        $ingredients = Product::get_all_ingredients();
        $categories = Product::get_all_categories();
        $branches = Branch::for_current_user();
        require __DIR__ . '/../views/modules/recipes.php';
    }

    public function products()
    {
        require_permission('view_product');
        enforce_csrf();

        // CSV Export
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            require_permission('print_products');
            ActivityLog::log('Products Exported', "Exported product catalog to CSV by user #" . ($_SESSION['user']['id'] ?? '?'));

            $branch_id = !empty($_GET['branch_id']) ? (int) $_GET['branch_id'] : null;
            $category_id = !empty($_GET['category_id']) ? (int) $_GET['category_id'] : null;
            $stock_status = $_GET['stock_status'] ?? 'all';
            $search = $_GET['search'] ?? '';

            $products = Product::get_dt_products(0, 999999, $search, 0, 'asc', $branch_id, $category_id, $stock_status);

            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=products_export_' . date('Ymd_His') . '.csv');

            $output = fopen('php://output', 'w');

            // Metadata header
            $store_name = 'Inventory Report';
            fputcsv($output, ['Product Catalog Export']);
            fputcsv($output, ['Generated On:', date('M d, Y h:i A')]);
            $filter_parts = [];
            if ($branch_id) $filter_parts[] = "Branch ID: $branch_id";
            if ($category_id) $filter_parts[] = "Category ID: $category_id";
            if ($stock_status !== 'all') $filter_parts[] = "Stock: $stock_status";
            if ($search) $filter_parts[] = "Search: $search";
            if (!empty($filter_parts)) {
                fputcsv($output, ['Active Filters:', implode(' | ', $filter_parts)]);
            }
            fputcsv($output, []);

            // Headers
            fputcsv($output, ['ID', 'Name', 'SKU', 'Category', 'Price', 'Cost Price', 'Stock', 'Unit', 'Status', 'Product Type']);

            foreach ($products as $p) {
                fputcsv($output, [
                    $p['id'],
                    $p['name'],
                    $p['sku'] ?? '',
                    $p['category_name'] ?? '',
                    $p['price'],
                    $p['cost_price'],
                    $p['stock'],
                    $p['unit_type'] ?? 'pc',
                    $p['status'] ?? 'active',
                    $p['product_type'] ?? 'simple'
                ]);
            }

            fclose($output);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
            $action = $_POST['action'] ?? '';
            if (in_array($action,['inventory_count','inventory_loss','inventory_resolve','inventory_reorder'],true)) {
                require_permission('manage_inventory');
                require_once __DIR__.'/../models/InventoryControl.php';
                if ($action==='inventory_resolve') InventoryControl::resolve((int)($_POST['review_id'] ?? 0),$_POST['resolution'] ?? '',$_POST['notes'] ?? '');
                elseif ($action==='inventory_reorder') InventoryControl::reorder((int)($_POST['item_id'] ?? 0),$_POST['control_branch'] ?? null,$_POST['quantity'] ?? '');
                else InventoryControl::record((int)($_POST['item_id'] ?? 0),$_POST['control_branch'] ?? null,$action==='inventory_count'?'count':($_POST['loss_kind'] ?? ''),$_POST['quantity'] ?? '',$_POST['notes'] ?? '',$_POST['request_key'] ?? '');
                set_flash('success','Inventory record saved.');
                redirect('?route=inventory/products&controls=1&control_tab='.($action==='inventory_reorder'?'inventory_reorder':'inventory_history').'&control_branch='.(int)($_POST['control_branch'] ?? 0));
                return;
            }
            if ($action === 'create') {
                require_permission('add_product');
                $category_id = intval($_POST['category_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $price = floatval($_POST['price'] ?? 0);
                $cost_price = floatval($_POST['cost_price'] ?? 0);
                $stock = $_POST['stock'] ?? '0';
                $low_stock_threshold = $_POST['low_stock_threshold'] ?? '10';
                $max_stock_threshold = $_POST['max_stock_threshold'] ?? '100';
                $sku = sanitize($_POST['sku'] ?? '');
                $barcode = sanitize($_POST['barcode'] ?? '');

                if (empty($sku))
                    $sku = Product::generate_unique_sku();
                if (empty($barcode))
                    $barcode = Product::generate_unique_barcode();
                $status = sanitize($_POST['status'] ?? 'active');
                $product_type = sanitize($_POST['product_type'] ?? 'simple');
                $track_stock = isset($_POST['track_stock']) ? 1 : 0;
                $is_sellable = isset($_POST['is_sellable']) ? 1 : 0;
                $is_ingredient = isset($_POST['is_ingredient']) ? 1 : 0;
                $is_discountable = isset($_POST['is_discountable']) ? 1 : 0;
                $inventory_type = sanitize($_POST['inventory_type'] ?? 'stocked');
                $unit_type = InventoryQuantity::unit($_POST['unit_type'] ?? 'pc');
                InventoryQuantity::convert(InventoryQuantity::parse($stock,true),$unit_type,$unit_type);
                InventoryQuantity::parse($low_stock_threshold,true);
                InventoryQuantity::parse($max_stock_threshold,true);
                if ($product_type === 'ingredient') { $is_ingredient = 1; $is_sellable = 0; $track_stock = 1; }
                $opening_recipe_stock = 0;
                $recipe_create_key = (string)($_POST['recipe_create_key'] ?? '');
                $recipe_mode = $_POST['recipe_mode'] ?? 'prepared_batch';
                if ($product_type === 'recipe') {
                    if (!preg_match('/^(0|[1-9][0-9]{0,5})(?:\.0{1,2})?$/D', (string)$stock) || (int)$stock > 100000) {
                        throw new DomainException('Initial recipe stock must be a whole number from 0 to 100000.');
                    }
                    $opening_recipe_stock = (int)$stock;
                    if ($opening_recipe_stock > 0) {
                        require_permission('manage_inventory');
                        if (!preg_match('/^[a-f0-9]{32,64}$/D', $recipe_create_key)) throw new DomainException('Reload the product form before creating recipe stock.');
                        $recipe_mode = 'prepared_batch';
                    }
                    $stock = '0';
                    $track_stock = 1;
                    $is_sellable = 1;
                    $inventory_type = 'ingredient-based';
                    if (empty($_POST['recipe'])) throw new DomainException('Add ingredients or choose a saved recipe before saving this product.');
                }
                $clean_attrs = [];
                if (!empty($_POST['attributes']) && is_array($_POST['attributes'])) {
                    foreach ($_POST['attributes'] as $k => $v) {
                        $trimmed_k = trim($k);
                        if ($trimmed_k !== '') {
                            $clean_attrs[$trimmed_k] = trim($v);
                        }
                    }
                }
                $json_attributes = !empty($clean_attrs) ? json_encode($clean_attrs) : null;

                $is_featured = 0;
                $is_best_seller = 0;

                // Handle Image Upload
                $image_url = 'assets/img/default.png';
                if (!empty($_FILES['image']['name'])) {
                    $target_dir = __DIR__ . '/../assets/img/products/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = 'assets/img/products/' . $image_name;
                    }
                }

                if (empty($name)) {
                    set_flash('danger', 'Product name is required.');
                } elseif ($product_type !== 'ingredient' && $product_type !== 'variant' && $price <= 0) {
                    set_flash('danger', 'Product name and valid price are required.');
                } else {
                    if ($product_type === 'variant') {
                        $price = 0;
                        $stock = 0;
                    }
                    $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);
                    RecipeInventory::atomic(function () use ($category_id,$name,$sku,$barcode,$description,$price,$cost_price,$stock,$low_stock_threshold,$image_url,$status,$is_featured,$is_best_seller,$max_stock_threshold,$branch_id,$product_type,$track_stock,$is_sellable,$is_ingredient,$inventory_type,$json_attributes,$unit_type,$is_discountable,$opening_recipe_stock,$recipe_create_key,$recipe_mode) {
                    $creation = null;
                    if ($product_type === 'recipe' && $recipe_create_key !== '') {
                        $payload = $_POST;
                        unset($payload['csrf_token']);
                        ksort($payload);
                        $creation = RecipeInventory::operation('recipe_create', $recipe_create_key, $payload, $_SESSION['user']['id'], $branch_id);
                        if ($creation['result_id'] !== null) {
                            set_flash('success', 'Product already created. Opening stock was recorded once.');
                            return (int)$creation['result_id'];
                        }
                    }
                    $prod_id = Product::create(
                        $category_id,
                        $name,
                        $sku,
                        $barcode,
                        $description,
                        $price,
                        $cost_price,
                        $stock,
                        $low_stock_threshold,
                        $image_url,
                        $status,
                        $is_featured,
                        $is_best_seller,
                        $max_stock_threshold,
                        $branch_id,
                        $product_type,
                        $track_stock,
                        $is_sellable,
                        $is_ingredient,
                        $inventory_type,
                        $json_attributes,
                        $unit_type,
                        $is_discountable
                    );
                    if ($prod_id) {
                        ActivityLog::log('Product Created', "Created product '{$name}' (ID: {$prod_id})");

                        RecipeInventory::setMode($prod_id, $recipe_mode, true);
                        // Handle Variants
                        if (!empty($_POST['variants'])) {
                            Product::save_variants($prod_id, $_POST['variants']);
                            foreach ($_POST['variants'] as $variant) {
                                $v_stock = intval($variant['stock'] ?? 0);
                                if ($v_stock > 0) {
                                    $v_cost = floatval($variant['cost_price'] ?? 0);
                                    $v_name = sanitize($variant['variant_name'] ?? 'Variant');
                                    Product::adjust_stock($prod_id, $_SESSION['user']['id'], 'initial', $v_stock, "Initial stock for variant: {$v_name}", null, $v_cost, $branch_id);
                                }
                            }
                        }

                        // Handle Recipe
                        if ($product_type === 'recipe') {
                            if (!empty($_POST['recipe'])) {
                                Product::save_recipes($prod_id, $_POST['recipe']);
                            }
                            if (!empty($_POST['modifiers'])) {
                                Product::save_modifiers($prod_id, $_POST['modifiers']);
                            }
                            if ($opening_recipe_stock > 0) {
                                RecipeInventory::prepare($prod_id, $opening_recipe_stock, $_SESSION['user']['id'], $branch_id, hash('sha256', 'opening:' . $recipe_create_key));
                            }
                            if ($creation) RecipeInventory::complete($creation['id'], $prod_id, ['opening_stock'=>$opening_recipe_stock]);
                        }

                        if ($product_type !== 'variant' && $product_type !== 'recipe') {
                            $batch_number = sanitize($_POST['batch_number'] ?? '');
                            Product::adjust_stock($prod_id, $_SESSION['user']['id'], 'initial', $stock, 'Initial product creation', $batch_number, $cost_price, $branch_id);
                        }
                        set_flash('success', $opening_recipe_stock > 0
                            ? 'Product created with ' . $opening_recipe_stock . ' stock. Recipe ingredients deducted once.'
                            : 'Product created successfully.');
                    } else {
                        $error_msg = 'Failed to create product.';
                        if (isset($_SESSION['sql_error'])) {
                            $error_msg .= ' ' . $_SESSION['sql_error'];
                            unset($_SESSION['sql_error']);
                        }
                        set_flash('danger', $error_msg);
                    }
                    return $prod_id;
                    });
                }
                redirect('?route=inventory/products');
            } elseif ($action === 'edit') {
                require_permission('edit_product');
                $id = intval($_POST['product_id'] ?? 0);
                $category_id = intval($_POST['category_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $price = floatval($_POST['price'] ?? 0);
                $cost_price = floatval($_POST['cost_price'] ?? 0);
                $stock = $_POST['stock'] ?? '0';
                $low_stock_threshold = $_POST['low_stock_threshold'] ?? '10';
                $max_stock_threshold = $_POST['max_stock_threshold'] ?? '100';
                $sku = sanitize($_POST['sku'] ?? '');
                $barcode = sanitize($_POST['barcode'] ?? '');
                if (empty($sku))
                    $sku = Product::generate_unique_sku();
                if (empty($barcode))
                    $barcode = Product::generate_unique_barcode();
                $status = sanitize($_POST['status'] ?? 'active');
                $product_type = sanitize($_POST['product_type'] ?? 'simple');
                $track_stock = isset($_POST['track_stock']) ? 1 : 0;
                $is_sellable = isset($_POST['is_sellable']) ? 1 : 0;
                $is_ingredient = isset($_POST['is_ingredient']) ? 1 : 0;
                $is_discountable = isset($_POST['is_discountable']) ? 1 : 0;
                $inventory_type = sanitize($_POST['inventory_type'] ?? 'stocked');
                $unit_type = InventoryQuantity::unit($_POST['unit_type'] ?? 'pc');
                InventoryQuantity::convert(InventoryQuantity::parse($stock,true),$unit_type,$unit_type);
                InventoryQuantity::parse($low_stock_threshold,true);
                InventoryQuantity::parse($max_stock_threshold,true);
                if ($product_type === 'ingredient') { $is_ingredient = 1; $is_sellable = 0; $track_stock = 1; }
                if ($product_type === 'recipe') {
                    $stock = '0';
                    $is_sellable = 1;
                    $inventory_type = 'ingredient-based';
                    if (empty($_POST['recipe'])) throw new DomainException('Add ingredients before saving a recipe product.');
                }
                $clean_attrs = [];
                if (!empty($_POST['attributes']) && is_array($_POST['attributes'])) {
                    foreach ($_POST['attributes'] as $k => $v) {
                        $trimmed_k = trim($k);
                        if ($trimmed_k !== '') {
                            $clean_attrs[$trimmed_k] = trim($v);
                        }
                    }
                }
                $json_attributes = !empty($clean_attrs) ? json_encode($clean_attrs) : null;
                $is_featured = 0;
                $is_best_seller = 0;

                $existing = Product::find_by_id($id);
                $image_url = $existing['image_url'];

                if (!empty($_FILES['image']['name'])) {
                    $target_dir = __DIR__ . '/../assets/img/products/';
                    if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = 'assets/img/products/' . $image_name;
                    }
                }

                // Access middleware injects the staff branch into POST for scoping.
                // It is not an instruction to transfer this product's ownership.
                // Ownership changes belong to the audited Transfer Branch action.
                $branch_id = $existing['branch_id'] === null ? null : (int)$existing['branch_id'];
                if ($product_type === 'variant') {
                    $price = 0;
                    $stock = 0;
                }
                RecipeInventory::atomic(function () use ($id,$category_id,$name,$sku,$barcode,$description,$price,$cost_price,$stock,$low_stock_threshold,$image_url,$status,$is_featured,$is_best_seller,$max_stock_threshold,$branch_id,$product_type,$track_stock,$is_sellable,$is_ingredient,$inventory_type,$json_attributes,$unit_type,$is_discountable) {
                $old = RecipeInventory::product($id);
                RecipeInventory::branch($old['branch_id']);
                RecipeInventory::lockProducts(array_merge([$id],array_column($_POST['recipe'] ?? [],'ingredient_id'),array_filter(array_column($_POST['modifiers'] ?? [],'ingredient_product_id'))));
                RecipeInventory::validateIdentity($id,$unit_type,$branch_id,$product_type);
                Product::update(
                    $id,
                    $category_id,
                    $name,
                    $sku,
                    $barcode,
                    $description,
                    $price,
                    $cost_price,
                    $stock,
                    $low_stock_threshold,
                    $image_url,
                    $status,
                    $is_featured,
                    $is_best_seller,
                    $max_stock_threshold,
                    $branch_id,
                    $product_type,
                    $track_stock,
                    $is_sellable,
                    $is_ingredient,
                    $inventory_type,
                    $json_attributes,
                    $unit_type,
                    $is_discountable
                );

                RecipeInventory::setMode($id, $_POST['recipe_mode'] ?? RecipeInventory::product($id)['recipe_mode'], false, !empty($_POST['recipe_reconciled']));
                // Handle Variants from main form (optional but good for consistency)
                if (!empty($_POST['variants'])) {
                    Product::save_variants($id, $_POST['variants']);
                }

                // Handle Recipe from main form
                if ($product_type === 'recipe') {
                    if (!empty($_POST['recipe'])) {
                        Product::save_recipes($id, $_POST['recipe']);
                    }
                    Product::save_modifiers($id, $_POST['modifiers'] ?? []);
                }

                set_flash('success', 'Product updated successfully.');
                ActivityLog::log('Product Updated', "Updated product '{$name}' (ID: {$id})");
                return true;
                });
                redirect('?route=inventory/products');
            } elseif ($action === 'delete') {
                require_permission('delete_product');
                $id = intval($_POST['product_id'] ?? 0);
                $product = Product::find_by_id($id);
                $prod_name = $product ? $product['name'] : "ID {$id}";
                Product::delete($id);
                ActivityLog::log('Product Deleted', "Deleted product '{$prod_name}' (ID: {$id})");
                set_flash('success', 'Product deleted successfully.');
                redirect('?route=inventory/products');
            }
            } catch (Throwable $error) {
                error_log('Product save failed: ' . $error->getMessage());
                set_flash('danger', ($error instanceof DomainException || $error instanceof InvalidArgumentException)
                    ? $error->getMessage() : 'Product could not be saved. Check the manual inventory migration.');
                redirect('?route=inventory/products');
            }
        }

        $categories = Product::get_all_categories();
        $branches = Branch::for_current_user();
        $products = Product::get_all_products();
        $ingredients = Product::get_all_ingredients();

        require __DIR__ . '/../views/modules/products.php';
    }

    public function categories()
    {
        require_permission('edit_product');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';

            if ($action === 'create') {
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');

                if (empty($name)) {
                    set_flash('danger', 'Category name is required.');
                } else {
                    Product::create_category($name, $description);
                    ActivityLog::log('Category Created', "Created category '{$name}'");
                    set_flash('success', 'Category created successfully.');
                }
            } elseif ($action === 'edit') {
                $id = intval($_POST['category_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');

                if ($id && !empty($name)) {
                    Product::update_category($id, $name, $description);
                    ActivityLog::log('Category Updated', "Updated category ID {$id} to '{$name}'");
                    set_flash('success', 'Category updated successfully.');
                } else {
                    set_flash('danger', 'Invalid category data.');
                }
            } elseif ($action === 'delete') {
                global $conn;
                $id = intval($_POST['category_id'] ?? 0);

                if ($id) {
                    $cat = $conn->query("SELECT name FROM categories WHERE id = {$id}")->fetch_assoc();
                    $cat_name = $cat ? $cat['name'] : "ID {$id}";
                    Product::delete_category($id);
                    ActivityLog::log('Category Deleted', "Deleted category '{$cat_name}'");
                    set_flash('success', 'Category deleted successfully.');
                } else {
                    set_flash('danger', 'Invalid category.');
                }
            }

            redirect('?route=inventory/categories');
        }

        $categories = Product::get_all_categories();
        require __DIR__ . '/../views/modules/categories.php';
    }

    public function logs()
    {
        global $conn;
        require_permission('manage_inventory');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'adjust_stock') {
                $product_id = intval($_POST['product_id'] ?? 0);
                $quantity = $_POST['quantity'] ?? '0';
                $adj_type = $_POST['adjustment_type'] ?? 'stock_in';
                $remarks = sanitize($_POST['remarks'] ?? 'Manual stock adjustment');
                $branch_id = RecipeInventory::branch($_POST['branch_id'] ?? null);

                if ($product_id > 0 && $quantity > 0) {
                    try {
                        Product::adjust_stock($product_id, $_SESSION['user']['id'], $adj_type, $quantity, $remarks, null, null, $branch_id);
                    } catch (Throwable $error) {
                        set_flash('danger', ($error instanceof DomainException || $error instanceof InvalidArgumentException) ? $error->getMessage() : 'Stock adjustment failed. Check inventory setup.');
                        redirect('?route=inventory/logs');
                    }
                    set_flash('success', 'Stock adjustment recorded successfully.');
                } else {
                    set_flash('danger', 'Invalid parameters for stock adjustment.');
                }
                redirect('?route=inventory/logs');
            }
        }

        // Filter params
        $filter_branch_id = !empty($_GET['branch_id']) ? (int) $_GET['branch_id'] : null;
        $filter_date_from = $_GET['date_from'] ?? null;
        $filter_date_to = $_GET['date_to'] ?? null;

        // Resolve branch name for reports that filter by string name
        $filter_branch_name = null;
        if ($filter_branch_id) {
            $b = $conn->query("SELECT name FROM branches WHERE id = " . $filter_branch_id)->fetch_assoc();
            $filter_branch_name = $b ? $b['name'] : null;
        }

        $logs = Product::get_inventory_logs();
        $products = Product::get_all_products();
        $branches = Branch::for_current_user();

        // Sales / Revenue data (filtered)
        $sale_stats = Sale::get_summary_stats($filter_branch_id, $filter_date_from, $filter_date_to);
        $sale_stats_today = Sale::get_today_stats();
        $top_selling = Sale::get_top_selling_products(10, $filter_branch_id, $filter_date_from, $filter_date_to);
        $recent_filters = [];
        if ($filter_branch_id) $recent_filters['branch_id'] = $filter_branch_id;
        if ($filter_date_from) $recent_filters['date_from'] = $filter_date_from;
        if ($filter_date_to) $recent_filters['date_to'] = $filter_date_to;
        $recent_sales = Sale::get_filtered_sales($recent_filters, 1, 20);

        // Expenses data (filtered)
        $expenses = Expense::get_all_filtered($filter_branch_id, $filter_date_from, $filter_date_to);
        $expense_stats = Expense::get_stats($filter_branch_id, $filter_date_from, $filter_date_to);

        // Inventory stats (branch-filtered only)
        $inventory_stats = Product::get_inventory_stats($filter_branch_id);
        $low_stock_products = Product::get_low_stock_products();

        // Top performing branches
        $top_branches = Sale::get_top_branches_by_sales(10, $filter_branch_id, $filter_date_from, $filter_date_to);

        require __DIR__ . '/../views/modules/logs.php';
    }

    public function printLogs()
    {
        require_permission('manage_inventory');
        require_once __DIR__ . '/../models/Setting.php';
        $tabs = ['dashboard', 'sales', 'inventory', 'expenses', 'revenue'];
        $report_tab = in_array($_GET['tab'] ?? '', $tabs, true) ? $_GET['tab'] : 'dashboard';
        $branch_id = !empty($_GET['branch_id']) ? (int) $_GET['branch_id'] : null;
        $date_from = $this->printDate($_GET['date_from'] ?? null);
        $date_to = $this->printDate($_GET['date_to'] ?? null);
        if ($date_from && $date_to && $date_from > $date_to) { http_response_code(400); exit('The start date must be before the end date.'); }
        $search = trim((string) ($_GET['search'] ?? ''));
        $store_name = Setting::get('store_name', 'Stacey Halo Halo');
        $branch_name = 'All Branches';
        foreach (Branch::for_current_user(false) as $branch) {
            if ((int) $branch['id'] === $branch_id) $branch_name = $branch['name'];
        }
        $rows = [];
        $sale_stats = Sale::get_summary_stats($branch_id, $date_from, $date_to);
        if ($report_tab === 'sales') {
            $count = Sale::count_dt_sales_filtered($search, $branch_id, $date_from, $date_to);
            $rows = Sale::get_dt_sales(0, max(1, $count), $search, 0, 'DESC', $branch_id, $date_from, $date_to);
        } elseif ($report_tab === 'inventory') {
            $count = Product::count_dt_inventory_logs_filtered($search, $branch_id, $date_from, $date_to);
            $rows = Product::get_dt_inventory_logs(0, max(1, $count), $search, 5, 'DESC', $branch_id, $date_from, $date_to);
        } elseif ($report_tab === 'expenses') {
            $count = Expense::count_dt_expenses_filtered($search, $branch_id, $date_from, $date_to);
            $rows = Expense::get_dt_expenses(0, max(1, $count), $search, 0, 'DESC', $branch_id, $date_from, $date_to);
        }
        // These totals use the same date range, including when no date is selected.
        $all_expenses = Expense::get_all_filtered($branch_id, $date_from, $date_to);
        $expense_total = array_sum(array_column($all_expenses, 'amount'));
        $inventory_stats = Product::get_inventory_stats($branch_id);
        $top_selling = Sale::get_top_selling_products(10, $branch_id, $date_from, $date_to);
        $top_branches = Sale::get_top_branches_by_sales(10, $branch_id, $date_from, $date_to);
        require __DIR__ . '/../views/modules/inventory_logs_print.php';
    }

    private function printDate($value)
    {
        if (!$value) return null;
        $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
        if (!$date || $date->format('Y-m-d') !== $value) {
            http_response_code(400);
            exit('Please choose a valid date.');
        }
        return $value;
    }

    public function low_stock()
    {
        require_permission('manage_inventory');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = $_POST['quantity'] ?? '0';
            $remarks = sanitize($_POST['remarks'] ?? 'Quick restock from low stock alerts');

            if ($quantity > 0) {
                Product::adjust_stock($product_id, $_SESSION['user']['id'], 'stock_in', $quantity, $remarks);
                set_flash('success', 'Stock replenished successfully.');
            } else {
                set_flash('danger', 'Quantity must be greater than zero.');
            }
            redirect('?route=inventory/low_stock');
        }

        $low_stock = Product::get_low_stock_products();
        require __DIR__ . '/../views/modules/low_stock.php';
    }
}
