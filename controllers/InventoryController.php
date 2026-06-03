<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Branch.php';

class InventoryController
{
    public function products()
    {
        require_permission('view_product');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                require_permission('add_product');
                $category_id = intval($_POST['category_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $price = floatval($_POST['price'] ?? 0);
                $cost_price = floatval($_POST['cost_price'] ?? 0);
                $stock = intval($_POST['stock'] ?? 0);
                $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 10);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;

                // Handle Image Upload
                $image_url = 'assets/images/default.png';
                if (!empty($_FILES['image']['name'])) {
                    $target_dir = __DIR__ . '/../assets/images/';
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = 'assets/images/' . $image_name;
                    }
                }

                if (empty($name) || $price <= 0) {
                    set_flash('danger', 'Product name and valid price are required.');
                } else {
                    $branch_id = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : ($_SESSION['user']['branch_id'] ?? null);
                    $prod_id = Product::create($category_id, $name, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $is_featured, $is_best_seller);
                    if ($prod_id) {
                        Product::adjust_stock($prod_id, $_SESSION['user']['id'], 'initial', $stock, 'Initial product creation', null, null, $branch_id);
                        set_flash('success', 'Product created successfully.');
                    } else {
                        set_flash('danger', 'Failed to create product.');
                    }
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
                $stock = intval($_POST['stock'] ?? 0);
                $low_stock_threshold = intval($_POST['low_stock_threshold'] ?? 10);
                $is_featured = isset($_POST['is_featured']) ? 1 : 0;
                $is_best_seller = isset($_POST['is_best_seller']) ? 1 : 0;

                $existing = Product::find_by_id($id);
                $image_url = $existing['image_url'];

                if (!empty($_FILES['image']['name'])) {
                    $target_dir = __DIR__ . '/../assets/images/';
                    $image_name = time() . '_' . basename($_FILES['image']['name']);
                    $target_file = $target_dir . $image_name;
                    if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_url = 'assets/images/' . $image_name;
                    }
                }

                Product::update($id, $category_id, $name, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $is_featured, $is_best_seller);
                set_flash('success', 'Product updated successfully.');
                redirect('?route=inventory/products');
            } elseif ($action === 'delete') {
                require_permission('delete_product');
                $id = intval($_POST['product_id'] ?? 0);
                Product::delete($id);
                set_flash('success', 'Product deleted successfully.');
                redirect('?route=inventory/products');
            }
        }

        $category_id = intval($_GET['category'] ?? 0);
        $search = sanitize($_GET['search'] ?? '');
        $branch_filter = intval($_GET['branch'] ?? 0);

        $products = Product::get_all_products($category_id, $search, $branch_filter);
        $categories = Product::get_all_categories();
        $branches = Branch::get_all_active();

        $branch_performance = [];
        foreach ($branches as $b) {
            $branch_performance[$b['id']] = Product::get_branch_performance_stats($b['id']);
        }

        $global_performance = Product::get_branch_performance_stats(null);

        require __DIR__ . '/../views/modules/products.php';
    }

    public function categories()
    {
        require_permission('edit_product');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = sanitize($_POST['name'] ?? '');
            $description = sanitize($_POST['description'] ?? '');

            if (empty($name)) {
                set_flash('danger', 'Category name is required.');
            } else {
                Product::create_category($name, $description);
                set_flash('success', 'Category created successfully.');
            }
            redirect('?route=inventory/categories');
        }

        $categories = Product::get_all_categories();
        require __DIR__ . '/../views/modules/categories.php';
    }

    public function logs()
    {
        require_permission('manage_inventory');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'adjust_stock') {
                $product_id = intval($_POST['product_id'] ?? 0);
                $quantity = intval($_POST['quantity'] ?? 0);
                $adj_type = $_POST['adjustment_type'] ?? 'stock_in';
                $remarks = sanitize($_POST['remarks'] ?? 'Manual stock adjustment');
                $branch_id = !empty($_POST['branch_id']) ? intval($_POST['branch_id']) : null;

                if ($product_id > 0 && $quantity > 0) {
                    Product::adjust_stock($product_id, $_SESSION['user']['id'], $adj_type, $quantity, $remarks, null, null, $branch_id);
                    set_flash('success', 'Stock adjustment recorded successfully.');
                } else {
                    set_flash('danger', 'Invalid parameters for stock adjustment.');
                }
                redirect('?route=inventory/logs');
            }
        }

        $logs = Product::get_inventory_logs();
        $products = Product::get_all_products();
        require __DIR__ . '/../views/modules/logs.php';
    }

    public function low_stock()
    {
        require_permission('manage_inventory');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $product_id = intval($_POST['product_id'] ?? 0);
            $quantity = intval($_POST['quantity'] ?? 0);
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
