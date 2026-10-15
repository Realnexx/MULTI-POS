<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/RecipeInventory.php';

class Product
{
    public static function get_all_ingredients()
    {
        require_once __DIR__ . '/User.php';
        $sql="SELECT id,name,unit_type,cost_price FROM products WHERE (is_ingredient=1 OR product_type='ingredient') AND status='active' AND product_type!='recipe'";
        if (!User::has_all_branch_access($_SESSION['user']['id'] ?? 0)) {
            return RecipeInventory::rows($sql . ' AND (branch_id IS NULL OR branch_id = ?) ORDER BY name','i',[$_SESSION['user']['branch_id'] ?? null]);
        }
        return RecipeInventory::rows($sql . ' ORDER BY name');
    }

    public static function get_all_products($category_id = null, $search = null, $branch_id = null, $product_type = null)
    {
        global $conn;
        $sql = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE 1=1";
        $params = [];
        $types = "";

        if ($category_id) {
            $sql .= " AND p.category_id = ?";
            $params[] = $category_id;
            $types .= "i";
        }

        if ($search) {
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)";
            $search_param = "%" . $search . "%";
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ssss";
        }

        if ($product_type) {
            $sql .= " AND p.product_type = ?";
            $params[] = $product_type;
            $types .= "s";
        }

        if ($branch_id) {
            $sql .= " AND (p.branch_id IS NULL OR p.branch_id = ?)";
            $params[] = $branch_id;
            $types .= "i";
        }

        $sql .= " ORDER BY p.id DESC";

        $stmt = $conn->prepare($sql);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        foreach ($results as &$product) {
            if ($branch_id !== null) $product['stock'] = RecipeInventory::availability($product, $branch_id);
        }
        unset($product);
        return $results;
    }

    public static function get_featured_products()
    {
        global $conn;
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_featured = 1 ORDER BY p.id DESC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_best_sellers()
    {
        global $conn;
        $query = "SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.is_best_seller = 1 ORDER BY p.id DESC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function find_by_id($id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public static function get_related_products($category_id, $exclude_id, $limit = 3)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.category_id = ? AND p.id != ? ORDER BY RAND() LIMIT ?");
        $stmt->bind_param("iii", $category_id, $exclude_id, $limit);
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $results;
    }

    public static function get_all_categories($branch_id = null)
    {
        global $conn;
        $branch_join = $branch_id ? "AND (p.branch_id IS NULL OR p.branch_id = " . intval($branch_id) . ")" : "";
        $query = "SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id AND p.status = 'active' $branch_join GROUP BY c.id ORDER BY c.name ASC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_product_type_counts($branch_id = null)
    {
        global $conn;
        $branch_filter = $branch_id ? "AND (p.branch_id IS NULL OR p.branch_id = " . intval($branch_id) . ")" : "";
        $query = "SELECT p.product_type, COUNT(*) as count FROM products p WHERE p.product_type IS NOT NULL AND p.product_type != '' $branch_filter GROUP BY p.product_type";
        $result = $conn->query($query);
        $counts = [];
        while ($row = $result->fetch_assoc()) {
            $counts[$row['product_type']] = (int)$row['count'];
        }
        return $counts;
    }

    public static function create_category($name, $description)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function update_category($id, $name, $description)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $description, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function delete_category($id)
    {
        global $conn;
        $conn->query("UPDATE products SET category_id = NULL WHERE category_id = " . intval($id));
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function create($category_id, $name, $sku, $barcode, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $status, $is_featured, $is_best_seller, $max_stock_threshold = 100, $branch_id = null, $product_type = 'simple', $track_stock = 1, $is_sellable = 1, $is_ingredient = 0, $inventory_type = 'stocked', $json_attributes = null, $unit_type = 'pc', $is_discountable = 1)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, sku, barcode, description, price, cost_price, stock, low_stock_threshold, max_stock_threshold, image_url, status, is_featured, is_best_seller, branch_id, product_type, track_stock, is_sellable, is_ingredient, is_discountable, inventory_type, json_attributes, unit_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt)
            return false;
        $params = [
            $category_id,
            $name,
            $sku,
            $barcode,
            $description,
            $price,
            $cost_price,
            $stock,
            $low_stock_threshold,
            $max_stock_threshold,
            $image_url,
            $status,
            $is_featured,
            $is_best_seller,
            $branch_id,
            $product_type,
            $track_stock,
            $is_sellable,
            $is_ingredient,
            $is_discountable,
            $inventory_type,
            $json_attributes,
            $unit_type
        ];
        $types = "issssddsssssiiisiiiisss";
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            $prod_id = $stmt->insert_id;
            $stmt->close();
            return $prod_id;
        } else {
            error_log("Product::create execution failed: (" . $stmt->errno . ") " . $stmt->error);
            // Also store in session for debugging visibility in flash message temporarily
            $_SESSION['sql_error'] = "SQL Error: " . $stmt->error;
            $stmt->close();
            return false;
        }
    }

    public static function update($id, $category_id, $name, $sku, $barcode, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $status, $is_featured, $is_best_seller, $max_stock_threshold = 100, $branch_id = null, $product_type = 'simple', $track_stock = 1, $is_sellable = 1, $is_ingredient = 0, $inventory_type = 'stocked', $json_attributes = null, $unit_type = 'pc', $is_discountable = 1)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, sku = ?, barcode = ?, description = ?, price = ?, cost_price = ?, stock = stock + (0 * ?), low_stock_threshold = ?, max_stock_threshold = ?, image_url = ?, status = ?, is_featured = ?, is_best_seller = ?, branch_id = ?, product_type = ?, track_stock = ?, is_sellable = ?, is_ingredient = ?, is_discountable = ?, inventory_type = ?, json_attributes = ?, unit_type = ? WHERE id = ?");
        if (!$stmt)
            return false;
        $params = [
            $category_id,
            $name,
            $sku,
            $barcode,
            $description,
            $price,
            $cost_price,
            $stock,
            $low_stock_threshold,
            $max_stock_threshold,
            $image_url,
            $status,
            $is_featured,
            $is_best_seller,
            $branch_id,
            $product_type,
            $track_stock,
            $is_sellable,
            $is_ingredient,
            $is_discountable,
            $inventory_type,
            $json_attributes,
            $unit_type,
            $id
        ];
        $types = "issssddsssssiiisiiiisssi";
        $stmt->bind_param($types, ...$params);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function delete($id)
    {
        return RecipeInventory::atomic(function () use ($id) {
            $p=RecipeInventory::product($id); RecipeInventory::branch($p['branch_id']);
            RecipeInventory::lockProducts([$id]);
            if (RecipeInventory::rows('SELECT id FROM product_recipes WHERE ingredient_product_id = ? LIMIT 1 FOR UPDATE','i',[$id])
                || RecipeInventory::rows('SELECT id FROM product_modifiers WHERE ingredient_product_id = ? AND is_active=1 LIMIT 1 FOR UPDATE','i',[$id])) {
                throw new DomainException('Remove this ingredient from active recipes and extras before archiving it.');
            }
            // Preserve historical receipts, recipes and stock records.
            RecipeInventory::query("UPDATE products SET status = 'inactive', is_sellable = 0 WHERE id = ?",'i',[$id]);
            return true;
        });
    }

    public static function adjust_stock($product_id, $user_id, $action, $quantity, $remarks, $batch_number = null, $cost_price = null, $branch_id = null, $selected_batch_id = null, $expiry_date = null, $selling_price = null)
    {
        return RecipeInventory::adjust($product_id,$user_id,$action,$quantity,$remarks,$branch_id,$batch_number,$cost_price,$expiry_date,$selling_price,$selected_batch_id,$_POST['request_key'] ?? null);
    }

    public static function deduct_stock_fifo($product_id, $quantity, $user_id, $remarks, $branch_id = null)
    {
        return RecipeInventory::adjust($product_id,$user_id,'stock_out',$quantity,$remarks,$branch_id);
    }

    public static function get_recipes($product_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT pr.*, p.name as ingredient_name 
                                FROM product_recipes pr 
                                JOIN products p ON pr.ingredient_product_id = p.id 
                                WHERE pr.recipe_product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function save_recipes($product_id, $ingredients)
    {
        return RecipeInventory::saveRecipe($product_id, $ingredients);
    }

    public static function get_modifiers($product_id)
    {
        global $conn;
        $stmt = $conn->prepare(
            "SELECT pm.*, p.name as ingredient_name 
             FROM product_modifiers pm
             LEFT JOIN products p ON pm.ingredient_product_id = p.id
             WHERE pm.product_id = ? AND pm.is_active = 1 ORDER BY pm.id ASC"
        );
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function save_modifiers($product_id, $modifiers)
    {
        return RecipeInventory::saveModifiers($product_id, $modifiers);
    }

    public static function check_recipe_stock($product_id, $quantity = 1)
    {
        return RecipeInventory::checkRecipe($product_id, (int)$quantity, RecipeInventory::branch($_SESSION['pos_branch_id'] ?? null));
    }

    public static function get_variants($product_id)
    {
        global $conn;
        $sql = "SELECT * FROM product_variants WHERE product_id = ? ORDER BY id ASC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function save_variants($product_id, $variants)
    {
        global $conn;
        return RecipeInventory::atomic(function () use ($product_id, $variants) {
            global $conn;
            $parent = self::find_by_id($product_id);
            $parent_sku = $parent['sku'] ?? 'VAR';

            $keep_ids = [];
            $idx = 1;
            foreach ($variants as $variant) {
                $json_attr = isset($variant['json_attributes']) ? json_encode($variant['json_attributes']) : null;
                $v_name = trim($variant['variant_name'] ?? $variant['name'] ?? '');
                if ($v_name === '') {
                    continue;
                }
                $v_sku = !empty($variant['sku']) ? trim($variant['sku']) : ($parent_sku . '-' . strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $v_name), 0, 6)) . str_pad($idx, 2, '0', STR_PAD_LEFT));
                $idx++;

                $v_price = isset($variant['price']) && $variant['price'] !== '' ? (float)$variant['price'] : 0.00;
                $v_cost = isset($variant['cost_price']) && $variant['cost_price'] !== '' ? (float)$variant['cost_price'] : 0.00;
                $v_stock = isset($variant['stock']) && $variant['stock'] !== '' ? (int)$variant['stock'] : 0;
                $v_barcode = !empty($variant['barcode']) ? trim($variant['barcode']) : null;
                $v_id = !empty($variant['id']) ? (int)$variant['id'] : null;

                if (!empty($v_id)) {
                    $sql = "UPDATE product_variants SET variant_name = ?, sku = ?, price = ?, cost_price = ?, stock = ?, barcode = ?, json_attributes = ? WHERE id = ? AND product_id = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("ssddissii", $v_name, $v_sku, $v_price, $v_cost, $v_stock, $v_barcode, $json_attr, $v_id, $product_id);
                    $stmt->execute();
                    $stmt->close();
                    $keep_ids[] = $v_id;
                } else {
                    $sql = "INSERT INTO product_variants (product_id, variant_name, sku, price, cost_price, stock, barcode, json_attributes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("issddiss", $product_id, $v_name, $v_sku, $v_price, $v_cost, $v_stock, $v_barcode, $json_attr);
                    $stmt->execute();
                    $keep_ids[] = $conn->insert_id;
                    $stmt->close();
                }
            }

            if (!empty($keep_ids)) {
                $placeholders = implode(',', array_fill(0, count($keep_ids), '?'));
                $sql = "DELETE FROM product_variants WHERE product_id = ? AND id NOT IN ($placeholders)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("i" . str_repeat("i", count($keep_ids)), $product_id, ...$keep_ids);
                $stmt->execute();
                $stmt->close();
            } else {
                $conn->query("DELETE FROM product_variants WHERE product_id = " . (int) $product_id);
            }

            return true;
        });
    }

    public static function get_attributes()
    {
        global $conn;
        return $conn->query("SELECT * FROM product_attributes ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
    }

    public static function save_attribute($name)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT IGNORE INTO product_attributes (name) VALUES (?)");
        $stmt->bind_param("s", $name);
        return $stmt->execute();
    }

    public static function get_product_attribute_values($product_id)
    {
        // For simple products, we store in json_attributes field
        global $conn;
        $sql = "SELECT json_attributes FROM products WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res && !empty($res['json_attributes']) ? json_decode($res['json_attributes'], true) : [];
    }

    public static function save_product_attributes($product_id, $attributes)
    {
        global $conn;
        $json = (!empty($attributes) && count($attributes) > 0) ? json_encode($attributes) : null;
        $stmt = $conn->prepare("UPDATE products SET json_attributes = ? WHERE id = ?");
        if (!$stmt) return false;
        $stmt->bind_param("si", $json, $product_id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function get_product_batches($product_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM product_batches WHERE product_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_active_batches($product_id, $branch_id = null)
    {
        $branch_id=RecipeInventory::branch($branch_id);
        return RecipeInventory::rows("SELECT id,batch_number,quantity,cost_price FROM product_batches WHERE product_id=? AND branch_id <=> ? AND quantity>0 AND status='active' ORDER BY created_at,id",'ii',[$product_id,$branch_id]);
    }

    public static function get_next_batch_number($product_id)
    {
        global $conn;
        $res = $conn->query("SELECT COUNT(*) as count FROM product_batches WHERE product_id = " . (int) $product_id);
        $next_index = ($res && $row = $res->fetch_assoc()) ? ((int) $row['count'] + 1) : 1;
        return "BATCH-" . str_pad($product_id, 3, '0', STR_PAD_LEFT) . "-" . str_pad($next_index, 3, '0', STR_PAD_LEFT);
    }

    public static function get_next_product_id()
    {
        global $conn;
        $result = $conn->query("SHOW TABLE STATUS LIKE 'products'");
        $row = $result->fetch_assoc();
        return $row['Auto_increment'] ?? 1;
    }

    public static function get_dt_inventory_logs($start, $length, $search, $order_col, $order_dir, $branch_id = null, $start_date = null, $end_date = null, $product_id = null)
    {
        global $conn;
        $order_dir = strtoupper((string)$order_dir) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "SELECT l.*, p.name as product_name, u.name as user_name, b.batch_number, b.cost_price 
                FROM inventory_logs l 
                LEFT JOIN products p ON l.product_id = p.id 
                LEFT JOIN users u ON l.user_id = u.id 
                LEFT JOIN product_batches b ON l.batch_id = b.id
                WHERE 1=1";

        if ($product_id !== null) {
            $sql .= " AND l.product_id = " . (int) $product_id;
        }

        if ($branch_id !== null) {
            $sql .= " AND l.branch_id = " . (int) $branch_id;
        }

        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(l.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(l.created_at) <= '$ed'";
        }

        if (!empty($search)) {
            $s = "%$search%";
            $sql .= " AND (p.name LIKE ? OR u.name LIKE ? OR l.action LIKE ? OR l.remarks LIKE ?)";
        }

        $order_cols = ['p.name', 'l.action', 'l.quantity', 'u.name', 'l.remarks', 'l.created_at'];
        $order_by = $order_cols[$order_col] ?? 'l.id';
        $sql .= " ORDER BY $order_by $order_dir LIMIT ?, ?";

        $stmt = $conn->prepare($sql);

        if (!empty($search)) {
            $stmt->bind_param("ssssii", $s, $s, $s, $s, $start, $length);
        } else {
            $stmt->bind_param("ii", $start, $length);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $logs = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $logs;
    }

    public static function count_dt_inventory_logs_filtered($search, $branch_id = null, $start_date = null, $end_date = null, $product_id = null)
    {
        global $conn;
        if (empty($search) && !$start_date && !$end_date)
            return self::get_total_inventory_logs_count($branch_id, $start_date, $end_date, $product_id);

        $sql = "SELECT COUNT(*) as count 
                FROM inventory_logs l 
                LEFT JOIN products p ON l.product_id = p.id 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE 1=1";

        if ($product_id !== null) {
            $sql .= " AND l.product_id = " . (int) $product_id;
        }

        if ($branch_id !== null) {
            $sql .= " AND l.branch_id = " . (int) $branch_id;
        }

        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(l.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(l.created_at) <= '$ed'";
        }

        if (!empty($search)) {
            $sql .= " AND (p.name LIKE ? OR u.name LIKE ? OR l.action LIKE ? OR l.remarks LIKE ?)";
        }

        if (!empty($search)) {
            $stmt = $conn->prepare($sql);
            $s = "%$search%";
            $stmt->bind_param("ssss", $s, $s, $s, $s);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = ($result && $row = $result->fetch_assoc()) ? (int) $row['count'] : 0;
            $stmt->close();
            return $count;
        } else {
            $res = $conn->query($sql);
            return ($res && $row = $res->fetch_assoc()) ? (int) $row['count'] : 0;
        }
    }

    public static function get_total_inventory_logs_count($branch_id = null, $start_date = null, $end_date = null, $product_id = null)
    {
        global $conn;
        $sql = "SELECT COUNT(*) as count FROM inventory_logs l 
                LEFT JOIN users u ON l.user_id = u.id
                WHERE 1=1";
        if ($branch_id !== null) {
            $sql .= " AND l.branch_id = " . (int) $branch_id;
        }
        if ($product_id !== null) {
            $sql .= " AND l.product_id = " . (int) $product_id;
        }
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(l.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(l.created_at) <= '$ed'";
        }
        $res = $conn->query($sql);
        return $res ? (int) $res->fetch_assoc()['count'] : 0;
    }

    public static function get_inventory_logs_stats($branch_id = null, $start_date = null, $end_date = null, $product_id = null)
    {
        global $conn;
        $sql = "SELECT l.action, l.quantity 
                FROM inventory_logs l 
                JOIN users u ON l.user_id = u.id 
                WHERE 1=1";
        if ($product_id !== null) {
            $sql .= " AND l.product_id = " . (int) $product_id;
        }
        if ($branch_id !== null) {
            $sql .= " AND l.branch_id = " . (int) $branch_id;
        }
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(l.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(l.created_at) <= '$ed'";
        }
        $res = $conn->query($sql);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function get_inventory_logs()
    {
        global $conn;
        $query = "SELECT l.*, p.name as product_name, u.name as user_name 
                  FROM inventory_logs l 
                  LEFT JOIN products p ON l.product_id = p.id 
                  LEFT JOIN users u ON l.user_id = u.id 
                  ORDER BY l.id DESC LIMIT 500";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_low_stock_products()
    {
        global $conn;
        $query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.stock <= p.low_stock_threshold 
                  ORDER BY p.stock ASC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_branch_stocks($product_id)
    {
        global $conn;
        $sql = "SELECT b.id, b.name, COALESCE(SUM(pb.quantity), 0) as branch_stock
                FROM branches b
                LEFT JOIN product_batches pb ON b.id = pb.branch_id AND pb.product_id = ? AND pb.status = 'active' AND (pb.expiry_date IS NULL OR pb.expiry_date >= CURRENT_DATE)
                GROUP BY b.id";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_branch_performance_stats($branch_id = null)
    {
        global $conn;
        $branch_cond = $branch_id ? " AND s.branch_id = " . intval($branch_id) : "";

        // Use sales from the last 30 days for dynamic badges
        $query = "SELECT si.product_id, SUM(si.quantity) as total_qty
                  FROM sales_items si
                  JOIN sales s ON si.sale_id = s.id
                  WHERE s.status = 'Completed' $branch_cond
                  AND s.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                  GROUP BY si.product_id
                  ORDER BY total_qty DESC";

        $res = $conn->query($query);
        $stats = [];
        if ($res) {
            $data = $res->fetch_all(MYSQLI_ASSOC);
            $total_products = count($data);
            foreach ($data as $index => $row) {
                $pid = $row['product_id'];
                if ($index === 0 && $row['total_qty'] > 0) {
                    $stats[$pid] = 'Best Seller';
                } elseif (($index < 3 || ($total_products > 0 && ($index / $total_products) <= 0.2)) && $row['total_qty'] > 0) {
                    $stats[$pid] = 'Popular';
                }
            }
        }
        return $stats;
    }

    private static function catalog_rows($search, $branch_id, $category_id, $stock_status, $order_col = 0, $order_dir = 'asc')
    {
        require_once __DIR__ . '/User.php';
        $branch_id=(!$branch_id && User::has_all_branch_access($_SESSION['user']['id'] ?? 0)) ? null : RecipeInventory::branch($branch_id);
        $sql="SELECT p.*,c.name category_name,b.name branch_name,(SELECT COALESCE(SUM(pb.quantity),0) FROM product_batches pb WHERE pb.product_id=p.id AND pb.status='active' AND pb.quantity>0 AND (pb.expiry_date IS NULL OR pb.expiry_date>=CURRENT_DATE)) all_physical_stock FROM products p LEFT JOIN categories c ON c.id=p.category_id LEFT JOIN branches b ON b.id=p.branch_id WHERE 1=1";
        $params=[]; $types='';
        if ($branch_id !== null) { $sql.=' AND (p.branch_id IS NULL OR p.branch_id=?)'; $params[]=$branch_id; $types.='i'; }
        if ($category_id) { $sql.=' AND p.category_id=?'; $params[]=$category_id; $types.='i'; }
        if ($search !== '') {
            $sql.=' AND (p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
            for ($i=0;$i<4;$i++) $params[]='%' . $search . '%';
            $types.='ssss';
        }
        $columns=['p.name','c.name','p.price','p.cost_price','p.id','p.id','p.id'];
        $sql.=' ORDER BY ' . ($columns[(int)$order_col] ?? 'p.id') . (strtolower($order_dir)==='desc' ? ' DESC':' ASC');
        $rows=RecipeInventory::rows($sql,$types,$params); $result=[];
        foreach ($rows as $row) {
            $row['physical_stock']=$branch_id === null ? $row['all_physical_stock'] : InventoryQuantity::format(RecipeInventory::balance($row['id'],$branch_id));
            $row['stock']=$row['physical_stock'];
            if ($row['product_type']==='recipe' && ($row['recipe_mode'] ?? '')==='made_to_order') {
                $row['stock']=RecipeInventory::catalogAvailability($row,$branch_id);
                $row['stock_is_recipe_capacity']=true;
            }
            $amount=(float)$row['stock'];
            if ($stock_status==='low' && !($amount>0 && $amount<=(float)$row['low_stock_threshold'])) continue;
            if ($stock_status==='out' && $amount>0) continue;
            if ($stock_status==='overstock' && $amount<=(float)$row['max_stock_threshold']) continue;
            $result[]=$row;
        }
        return $result;
    }

    public static function get_dt_products($start, $length, $search, $order_col, $order_dir, $branch_id = null, $category_id = null, $stock_status = 'all')
    {
        $rows=self::catalog_rows((string)$search,$branch_id,$category_id,$stock_status,$order_col,$order_dir);
        return $length<0 ? array_slice($rows,max(0,(int)$start)) : array_slice($rows,max(0,(int)$start),max(0,(int)$length));
    }

    public static function get_total_products_count()
    {
        return count(self::catalog_rows('',null,null,'all'));
    }

    public static function count_dt_products_filtered($search, $branch_id = null, $category_id = null, $stock_status = 'all')
    {
        return count(self::catalog_rows((string)$search,$branch_id,$category_id,$stock_status));
    }

    public static function get_inventory_stats($branch_id = null, $category_id = null, $search = null, $stock_status = 'all')
    {
        $rows=self::catalog_rows((string)$search,$branch_id,$category_id,$stock_status);
        $stats=['total_items'=>count($rows),'low_stock_count'=>0,'total_valuation'=>0,'total_revenue'=>0];
        foreach ($rows as $row) {
            if ((float)$row['stock'] <= (float)$row['low_stock_threshold']) $stats['low_stock_count']++;
            // Virtual burger capacity is not extra physical inventory to value twice.
            $stats['total_valuation']+=(float)$row['cost_price']*(float)$row['physical_stock'];
            $stats['total_revenue']+=(float)$row['price']*(float)$row['physical_stock'];
        }
        return $stats;
    }

    public static function sync_product_stock($product_id)
    {
        return RecipeInventory::atomic(function () use ($product_id) {
            RecipeInventory::lockProducts([$product_id]);
            RecipeInventory::syncTotal($product_id);
            return true;
        });
    }

    public static function get_product_batch_breakdown($product_id)
    {
        global $conn;

        // Auto-Sync before fetching
        $synced = false; // Reading inventory never changes it.

        $scope = '';
        if (!User::has_all_branch_access($_SESSION['user']['id'] ?? 0)) {
            $scope = ' AND pb.branch_id = ' . (int)User::access_context($_SESSION['user']['id'] ?? 0)['branch_id'];
        }
        $sql = "SELECT pb.*, b.name as branch_name
                FROM product_batches pb 
                LEFT JOIN branches b ON pb.branch_id = b.id 
                WHERE pb.product_id = ? AND pb.status = 'active' $scope
                ORDER BY pb.created_at ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $batches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Get product base prices for fallback
        $prod = self::find_by_id($product_id);

        return [
            'batches' => $batches,
            'was_synced' => $synced,
            'product_base_cost' => $prod ? (float) $prod['cost_price'] : 0,
            'product_base_price' => $prod ? (float) $prod['price'] : 0
        ];
    }

    public static function update_product_batch($batch_id, $quantity, $cost_price, $selling_price = null)
    {
        return RecipeInventory::atomic(function () use ($batch_id,$quantity,$cost_price,$selling_price) {
            $rows=RecipeInventory::rows('SELECT * FROM product_batches WHERE id = ?','i',[$batch_id]);
            if (!$rows) throw new DomainException('Batch no longer exists.');
            $batch=$rows[0]; RecipeInventory::branch($batch['branch_id']);
            RecipeInventory::lockProducts([$batch['product_id']]);
            $batch=RecipeInventory::rows('SELECT * FROM product_batches WHERE id = ? FOR UPDATE','i',[$batch_id])[0];
            if (InventoryQuantity::parse($quantity,true) !== InventoryQuantity::parse($batch['quantity'],true)) {
                throw new DomainException('Use an audited stock adjustment to change batch quantity.');
            }
            if ($cost_price < 0 || $selling_price < 0) throw new DomainException('Prices cannot be negative.');
            RecipeInventory::query('UPDATE product_batches SET cost_price = ?, selling_price = ? WHERE id = ?',
                'ssi',[(string)$cost_price,(string)($selling_price ?? $batch['selling_price']),$batch_id]);
            return true;
        });
    }

    public static function generate_unique_sku()
    {
        global $conn;
        $prefix = "SKX-";
        $year = date('y');
        $sql = "SELECT sku FROM products WHERE sku LIKE '{$prefix}{$year}%' ORDER BY sku DESC LIMIT 1";
        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            $last_num = (int) substr($row['sku'], -5);
            $next_num = $last_num + 1;
        } else {
            $next_num = 1;
        }
        return $prefix . $year . str_pad($next_num, 5, '0', STR_PAD_LEFT);
    }

    public static function generate_unique_barcode()
    {
        global $conn;
        do {
            $barcode = '1' . str_pad(mt_rand(0, 99999999999), 11, '0', STR_PAD_LEFT);
            $res = $conn->query("SELECT id FROM products WHERE barcode = '$barcode'");
        } while ($res && $res->num_rows > 0);
        return $barcode;
    }

}