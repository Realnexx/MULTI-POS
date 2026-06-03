<?php
require_once __DIR__ . '/../config/db.php';

class Product
{
    public static function get_all_products($category_id = null, $search = null, $branch_id = null)
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
            $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
            $search_param = "%" . $search . "%";
            $params[] = $search_param;
            $params[] = $search_param;
            $types .= "ss";
        }

        if ($branch_id) {
            $sql .= " AND EXISTS (SELECT 1 FROM product_batches pb WHERE pb.product_id = p.id AND pb.branch_id = ?)";
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

    public static function get_all_categories()
    {
        global $conn;
        $query = "SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON c.id = p.category_id GROUP BY c.id ORDER BY c.name ASC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
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

    public static function create($category_id, $name, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $is_featured, $is_best_seller)
    {
        global $conn;
        $stmt = $conn->prepare("INSERT INTO products (category_id, name, description, price, cost_price, stock, low_stock_threshold, image_url, is_featured, is_best_seller) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issddiisii", $category_id, $name, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $is_featured, $is_best_seller);
        if ($stmt->execute()) {
            $prod_id = $stmt->insert_id;
            $stmt->close();
            return $prod_id;
        }
        $stmt->close();
        return false;
    }

    public static function update($id, $category_id, $name, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $is_featured, $is_best_seller)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, price = ?, cost_price = ?, stock = ?, low_stock_threshold = ?, image_url = ?, is_featured = ?, is_best_seller = ? WHERE id = ?");
        $stmt->bind_param("issddiisiii", $category_id, $name, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $is_featured, $is_best_seller, $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function delete($id)
    {
        global $conn;
        $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        $stmt->close();
        return $success;
    }

    public static function adjust_stock($product_id, $user_id, $action, $quantity, $remarks, $batch_number = null, $cost_price = null, $branch_id = null)
    {
        global $conn;
        $product = self::find_by_id($product_id);
        if (!$product)
            return false;

        // If branch_id not provided, try to get from session or use branch_id associated with user
        if ($branch_id === null) {
            $u = $conn->query("SELECT branch_id FROM users WHERE id = " . intval($user_id))->fetch_assoc();
            $branch_id = $u['branch_id'] ?? null;
        }

        $batch_id = null;
        if ($action === 'stock_in') {
            // Dynamically increment batch number per product
            if (empty($batch_number)) {
                $res = $conn->query("SELECT COUNT(*) as count FROM product_batches WHERE product_id = $product_id");
                $next_index = ($res && $row = $res->fetch_assoc()) ? ((int) $row['count'] + 1) : 1;
                $batch_number = "BATCH-" . str_pad($product_id, 3, '0', STR_PAD_LEFT) . "-" . str_pad($next_index, 3, '0', STR_PAD_LEFT);
            }

            $cost_price = $cost_price ?: $product['cost_price'];
            $qty = (int) $quantity;
            $cost = (float) $cost_price;

            $stmt = $conn->prepare("INSERT INTO product_batches (product_id, batch_number, quantity, cost_price, branch_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("isidi", $product_id, $batch_number, $qty, $cost, $branch_id);
            $stmt->execute();
            $batch_id = $stmt->insert_id;
            $stmt->close();

            // Update main product stock cache
            $new_stock = $product['stock'] + $quantity;
            $conn->query("UPDATE products SET stock = $new_stock WHERE id = $product_id");
        } elseif ($action === 'stock_out') {
            return self::deduct_stock_fifo($product_id, $quantity, $user_id, $remarks, $branch_id);
        }

        // Insert log
        $stmt_log = $conn->prepare("INSERT INTO inventory_logs (product_id, user_id, action, quantity, remarks, batch_id, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_log->bind_param("iisisi i", $product_id, $user_id, $action, $quantity, $remarks, $batch_id, $branch_id);
        $success = $stmt_log->execute();
        $stmt_log->close();
        return $success;
    }

    public static function deduct_stock_fifo($product_id, $quantity, $user_id, $remarks, $branch_id = null)
    {
        global $conn;
        $remaining_to_deduct = $quantity;

        if ($branch_id === null) {
            $u = $conn->query("SELECT branch_id FROM users WHERE id = " . intval($user_id))->fetch_assoc();
            $branch_id = $u['branch_id'] ?? null;
        }

        // Get all active batches for this product at the specific branch, oldest first (FIFO)
        $sql = "SELECT * FROM product_batches WHERE product_id = ? AND quantity > 0 AND status = 'active'";
        if ($branch_id !== null) {
            $sql .= " AND branch_id = " . intval($branch_id);
        }
        $sql .= " ORDER BY created_at ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $batches = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($batches as $batch) {
            if ($remaining_to_deduct <= 0)
                break;

            $deduct_from_this_batch = min($batch['quantity'], $remaining_to_deduct);
            $new_batch_qty = $batch['quantity'] - $deduct_from_this_batch;
            $new_status = ($new_batch_qty <= 0) ? 'depleted' : 'active';

            // Update batch
            $stmt_upd = $conn->prepare("UPDATE product_batches SET quantity = ?, status = ? WHERE id = ?");
            $stmt_upd->bind_param("isi", $new_batch_qty, $new_status, $batch['id']);
            $stmt_upd->execute();
            $stmt_upd->close();

            $batch_remarks = "FIFO Deduction from Batch " . $batch['batch_number'] . ". " . $remarks;
            $action = 'stock_out';
            $deduct_qty = (int) $deduct_from_this_batch;
            $b_id = (int) $batch['id'];

            $stmt_log = $conn->prepare("INSERT INTO inventory_logs (product_id, user_id, action, quantity, remarks, batch_id, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt_log->bind_param("iisisi i", $product_id, $user_id, $action, $deduct_qty, $batch_remarks, $b_id, $branch_id);
            $stmt_log->execute();
            $stmt_log->close();

            $remaining_to_deduct -= $deduct_from_this_batch;
        }

        // Update main product stock cache
        $product = self::find_by_id($product_id);
        $new_total_stock = max(0, $product['stock'] - $quantity);
        $conn->query("UPDATE products SET stock = $new_total_stock WHERE id = $product_id");

        return true;
    }

    public static function get_product_batches($product_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT * FROM product_batches WHERE product_id = ? ORDER BY created_at DESC");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_next_batch_number($product_id)
    {
        global $conn;
        $res = $conn->query("SELECT COUNT(*) as count FROM product_batches WHERE product_id = " . (int) $product_id);
        $next_index = ($res && $row = $res->fetch_assoc()) ? ((int) $row['count'] + 1) : 1;
        return "BATCH-" . str_pad($product_id, 3, '0', STR_PAD_LEFT) . "-" . str_pad($next_index, 3, '0', STR_PAD_LEFT);
    }

    public static function get_dt_inventory_logs($start, $length, $search, $order_col, $order_dir, $branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;

        $sql = "SELECT l.*, p.name as product_name, u.name as user_name, b.batch_number 
                FROM inventory_logs l 
                LEFT JOIN products p ON l.product_id = p.id 
                LEFT JOIN users u ON l.user_id = u.id 
                LEFT JOIN product_batches b ON l.batch_id = b.id
                WHERE 1=1";

        if ($branch_id !== null) {
            $sql .= " AND u.branch_id = " . (int) $branch_id;
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

        $order_cols = ['l.id', 'p.name', 'u.name', 'l.action', 'b.batch_number', 'l.quantity', 'l.remarks', 'l.created_at'];
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

    public static function count_dt_inventory_logs_filtered($search, $branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        if (empty($search) && !$start_date && !$end_date)
            return self::get_total_inventory_logs_count($branch_id, $start_date, $end_date);

        $sql = "SELECT COUNT(*) as count 
                FROM inventory_logs l 
                LEFT JOIN products p ON l.product_id = p.id 
                LEFT JOIN users u ON l.user_id = u.id 
                WHERE 1=1";

        if ($branch_id !== null) {
            $sql .= " AND u.branch_id = " . (int) $branch_id;
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

    public static function get_total_inventory_logs_count($branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $sql = "SELECT COUNT(*) as count FROM inventory_logs l JOIN users u ON l.user_id = u.id WHERE 1=1";
        if ($branch_id !== null) {
            $sql .= " AND u.branch_id = " . (int) $branch_id;
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
            $sql .= " AND u.branch_id = " . (int) $branch_id;
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
                LEFT JOIN product_batches pb ON b.id = pb.branch_id AND pb.product_id = ? AND pb.status = 'active'
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
}
