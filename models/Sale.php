<?php
require_once __DIR__ . '/../config/db.php';

class Sale
{
    public static function create_sale($cashier_id, $branch_id, $total_amount, $discount_amount, $final_amount, $amount_tendered, $change_amount, $payment_method, $items = [], $order_type = 'Dine-in')
    {
        global $conn;
        $conn->begin_transaction();

        try {
            $stmt = $conn->prepare("INSERT INTO sales (cashier_id, branch_id, total_amount, discount_amount, final_amount, amount_tendered, change_amount, payment_method, order_type, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed')");
            $stmt->bind_param("iidddddss", $cashier_id, $branch_id, $total_amount, $discount_amount, $final_amount, $amount_tendered, $change_amount, $payment_method, $order_type);
            $stmt->execute();
            $sale_id = $stmt->insert_id;
            $stmt->close();

            $stmt_item = $conn->prepare("INSERT INTO sales_items (sale_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)");

            foreach ($items as $item) {
                $subtotal = $item['quantity'] * $item['price'];
                $stmt_item->bind_param("iiidd", $sale_id, $item['product_id'], $item['quantity'], $item['price'], $subtotal);
                $stmt_item->execute();

                // Deduct stock using FIFO and Branch ID
                Product::deduct_stock_fifo($item['product_id'], $item['quantity'], $cashier_id, "Sale #HH-" . str_pad($sale_id, 6, '0', STR_PAD_LEFT), $branch_id);
            }

            $stmt_item->close();

            $conn->commit();
            return $sale_id;
        } catch (Exception $e) {
            $conn->rollback();
            return false;
        }
    }

    public static function get_all_sales()
    {
        global $conn;
        $query = "SELECT s.*, u.name as cashier_name FROM sales s LEFT JOIN users u ON s.cashier_id = u.id ORDER BY s.id DESC";
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_recent_transactions($limit = 10, $branch = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $branch_cond = '';
        if ($branch && $branch !== 'all') {
            $b = $conn->real_escape_string($branch);
            $branch_cond = " AND u.branch = '$b'";
        }

        $date_cond = '';
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $date_cond .= " AND DATE(s.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $date_cond .= " AND DATE(s.created_at) <= '$ed'";
        }

        $query = "SELECT s.*, u.name as cashier_name, u.branch as branch_name
                  FROM sales s
                  LEFT JOIN users u ON s.cashier_id = u.id
                  WHERE 1=1 $branch_cond $date_cond
                  ORDER BY s.id DESC LIMIT " . intval($limit);
        return $conn->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function find_sale_by_id($sale_id)
    {
        global $conn;
        $stmt = $conn->prepare("SELECT s.*, u.name as cashier_name FROM sales s LEFT JOIN users u ON s.cashier_id = u.id WHERE s.id = ?");
        $stmt->bind_param("i", $sale_id);
        $stmt->execute();
        $sale = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($sale) {
            $stmt_items = $conn->prepare("SELECT si.*, p.name as product_name FROM sales_items si LEFT JOIN products p ON si.product_id = p.id WHERE si.sale_id = ?");
            $stmt_items->bind_param("i", $sale_id);
            $stmt_items->execute();
            $sale['items'] = $stmt_items->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt_items->close();
        }

        return $sale;
    }

    public static function get_dashboard_stats($branch = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $stats = [
            'sales_today' => 0.00,
            'orders_today' => 0,
            'avg_order_value' => 0.00,
            'total_products' => 0,
            'low_stock_count' => 0,
            'total_users' => 0,
            'sales_chart_labels' => [],
            'sales_chart_data' => []
        ];

        // Branch filter clause (join on users.branch)
        $branch_join = "LEFT JOIN users u ON s.cashier_id = u.id";
        $branch_cond = '';
        if ($branch && $branch !== 'all') {
            $b = $conn->real_escape_string($branch);
            $branch_cond = " AND u.branch = '$b'";
        }

        // Date filter
        $today = date('Y-m-d');
        $date_cond = '';
        if ($start_date && $end_date) {
            $sd = $conn->real_escape_string($start_date);
            $ed = $conn->real_escape_string($end_date);
            $date_cond = " AND DATE(s.created_at) BETWEEN '$sd' AND '$ed'";
        } elseif ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $date_cond = " AND DATE(s.created_at) >= '$sd'";
        } elseif ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $date_cond = " AND DATE(s.created_at) <= '$ed'";
        } else {
            $date_cond = " AND DATE(s.created_at) = '$today'";
        }

        // Sales and orders for selected period
        $query_sales = $conn->query(
            "SELECT SUM(s.final_amount) as total, COUNT(*) as count
             FROM sales s $branch_join
             WHERE s.status = 'Completed' $date_cond $branch_cond"
        );
        if ($row = $query_sales->fetch_assoc()) {
            $stats['sales_today'] = $row['total'] ? (float) $row['total'] : 0.00;
            $stats['orders_today'] = $row['count'] ? (int) $row['count'] : 0;
            $stats['avg_order_value'] = $stats['orders_today'] > 0 ? ($stats['sales_today'] / $stats['orders_today']) : 0.00;
        }

        // Total products
        $query_prod = $conn->query("SELECT COUNT(*) as count FROM products");
        $stats['total_products'] = $query_prod->fetch_assoc()['count'] ?? 0;

        // Low stock count
        $query_low = $conn->query("SELECT COUNT(*) as count FROM products WHERE stock <= low_stock_threshold");
        $stats['low_stock_count'] = $query_low->fetch_assoc()['count'] ?? 0;

        // Total users
        $query_users = $conn->query("SELECT COUNT(*) as count FROM users");
        $stats['total_users'] = $query_users->fetch_assoc()['count'] ?? 0;

        // Last 7 days sales for sparkline (ending at end_date or today)
        $end_dt = $end_date ? $end_date : $today;
        $chart_query = $conn->query(
            "SELECT DATE(s.created_at) as date, SUM(s.final_amount) as total
             FROM sales s $branch_join
             WHERE s.created_at >= DATE('$end_dt') - INTERVAL 6 DAY AND s.created_at <= DATE('$end_dt') + INTERVAL 1 DAY $branch_cond
             GROUP BY DATE(s.created_at)
             ORDER BY DATE(s.created_at) ASC"
        );

        $days = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("$end_dt -$i days"));
            $days[$d] = 0;
        }

        if ($chart_query) {
            while ($row = $chart_query->fetch_assoc()) {
                if (isset($days[$row['date']])) {
                    $days[$row['date']] = (float) $row['total'];
                }
            }
        }

        foreach ($days as $date => $total) {
            $stats['sales_chart_labels'][] = date('M d', strtotime($date));
            $stats['sales_chart_data'][] = $total;
        }

        return $stats;
    }

    public static function get_top_selling_products($limit = 3, $branch = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $branch_join = "LEFT JOIN users u ON s.cashier_id = u.id";
        $branch_cond = '';
        if ($branch && $branch !== 'all') {
            $b = $conn->real_escape_string($branch);
            $branch_cond = " AND u.branch = '$b'";
        }

        $date_cond = '';
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $date_cond .= " AND DATE(s.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $date_cond .= " AND DATE(s.created_at) <= '$ed'";
        }

        $query = "SELECT p.name, SUM(si.quantity) as total_qty
                  FROM sales_items si
                  LEFT JOIN products p ON si.product_id = p.id
                  LEFT JOIN sales s ON si.sale_id = s.id
                  $branch_join
                  WHERE 1=1 $branch_cond $date_cond
                  GROUP BY si.product_id
                  ORDER BY total_qty DESC LIMIT " . intval($limit);
        $res = $conn->query($query);
        if ($res) {
            $data = $res->fetch_all(MYSQLI_ASSOC);
            if (!empty($data))
                return $data;
        }
        $fallback_query = "SELECT name, 0 as total_qty FROM products ORDER BY price DESC LIMIT " . intval($limit);
        return $conn->query($fallback_query)->fetch_all(MYSQLI_ASSOC);
    }

    public static function get_filtered_sales($filters = [], $page = 1, $limit = 10, $order_by = 's.id', $order_dir = 'DESC')
    {
        global $conn;

        $where_clauses = ["1=1"];

        // Search term
        if (!empty($filters['search'])) {
            $s = $conn->real_escape_string($filters['search']);
            // Allow searching by order id (stripped or direct), or cashier name
            $id_search = preg_replace('/[^0-9]/', '', $s);
            if (!empty($id_search)) {
                $where_clauses[] = "(s.id = " . intval($id_search) . " OR u.name LIKE '%$s%')";
            } else {
                $where_clauses[] = "u.name LIKE '%$s%'";
            }
        }

        // Date range
        if (!empty($filters['date_from'])) {
            $df = $conn->real_escape_string($filters['date_from']);
            $where_clauses[] = "DATE(s.created_at) >= '$df'";
        }
        if (!empty($filters['date_to'])) {
            $dt = $conn->real_escape_string($filters['date_to']);
            $where_clauses[] = "DATE(s.created_at) <= '$dt'";
        }

        // Payment Status
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'All') {
            $status = $filters['payment_status'];
            if ($status === 'Paid') {
                $where_clauses[] = "s.status = 'Completed'";
            } else {
                $st = $conn->real_escape_string($status);
                $where_clauses[] = "s.status = '$st'";
            }
        }

        // Sales Channel (order_type)
        if (!empty($filters['channels'])) {
            $mapped_channels = [];
            foreach ($filters['channels'] as $c) {
                if ($c === 'POS Terminal') {
                    $mapped_channels[] = "'Dine-in'";
                    $mapped_channels[] = "'Take-out'";
                } elseif ($c === 'Online Store') {
                    $mapped_channels[] = "'Online'";
                } else {
                    $mapped_channels[] = "'" . $conn->real_escape_string($c) . "'";
                }
            }
            if (!empty($mapped_channels)) {
                $where_clauses[] = "s.order_type IN (" . implode(",", $mapped_channels) . ")";
            }
        }

        // Store Location (branch)
        if (!empty($filters['locations'])) {
            $location_list = array_map(function ($l) use ($conn) {
                return "'" . $conn->real_escape_string($l) . "'";
            }, $filters['locations']);
            $where_clauses[] = "u.branch IN (" . implode(",", $location_list) . ")";
        }

        $where_str = implode(" AND ", $where_clauses);
        $offset = intval(($page - 1) * $limit);
        $limit_val = intval($limit);

        // Safely validate order_by and order_dir to prevent SQL injection
        $allowed_columns = [
            's.id' => 's.id',
            's.created_at' => 's.created_at',
            'u.name' => 'u.name',
            's.order_type' => 's.order_type',
            's.final_amount' => 's.final_amount',
            's.payment_method' => 's.payment_method',
            's.status' => 's.status'
        ];
        $order_by_clean = isset($allowed_columns[$order_by]) ? $allowed_columns[$order_by] : 's.id';
        $order_dir_clean = strtoupper($order_dir) === 'ASC' ? 'ASC' : 'DESC';

        $query = "SELECT s.*, u.name as cashier_name, u.branch as branch_name 
                  FROM sales s 
                  LEFT JOIN users u ON s.cashier_id = u.id 
                  WHERE $where_str 
                  ORDER BY $order_by_clean $order_dir_clean 
                  LIMIT $offset, $limit_val";

        $res = $conn->query($query);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function get_filtered_sales_count($filters = [])
    {
        global $conn;

        $where_clauses = ["1=1"];

        // Search term
        if (!empty($filters['search'])) {
            $s = $conn->real_escape_string($filters['search']);
            $id_search = preg_replace('/[^0-9]/', '', $s);
            if (!empty($id_search)) {
                $where_clauses[] = "(s.id = " . intval($id_search) . " OR u.name LIKE '%$s%')";
            } else {
                $where_clauses[] = "u.name LIKE '%$s%'";
            }
        }

        // Date range
        if (!empty($filters['date_from'])) {
            $df = $conn->real_escape_string($filters['date_from']);
            $where_clauses[] = "DATE(s.created_at) >= '$df'";
        }
        if (!empty($filters['date_to'])) {
            $dt = $conn->real_escape_string($filters['date_to']);
            $where_clauses[] = "DATE(s.created_at) <= '$dt'";
        }

        // Payment Status
        if (!empty($filters['payment_status']) && $filters['payment_status'] !== 'All') {
            $status = $filters['payment_status'];
            if ($status === 'Paid') {
                $where_clauses[] = "s.status = 'Completed'";
            } else {
                $st = $conn->real_escape_string($status);
                $where_clauses[] = "s.status = '$st'";
            }
        }

        // Sales Channel (order_type)
        if (!empty($filters['channels'])) {
            $mapped_channels = [];
            foreach ($filters['channels'] as $c) {
                if ($c === 'POS Terminal') {
                    $mapped_channels[] = "'Dine-in'";
                    $mapped_channels[] = "'Take-out'";
                } elseif ($c === 'Online Store') {
                    $mapped_channels[] = "'Online'";
                } else {
                    $mapped_channels[] = "'" . $conn->real_escape_string($c) . "'";
                }
            }
            if (!empty($mapped_channels)) {
                $where_clauses[] = "s.order_type IN (" . implode(",", $mapped_channels) . ")";
            }
        }

        // Store Location (branch)
        if (!empty($filters['locations'])) {
            $location_list = array_map(function ($l) use ($conn) {
                return "'" . $conn->real_escape_string($l) . "'";
            }, $filters['locations']);
            $where_clauses[] = "u.branch IN (" . implode(",", $location_list) . ")";
        }

        $where_str = implode(" AND ", $where_clauses);

        $query = "SELECT COUNT(*) as count 
                  FROM sales s 
                  LEFT JOIN users u ON s.cashier_id = u.id 
                  WHERE $where_str";

        $res = $conn->query($query);
        if ($res) {
            $row = $res->fetch_assoc();
            return (int) $row['count'];
        }
        return 0;
    }

    public static function get_total_sales_count($branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $sql = "SELECT COUNT(*) as count FROM sales s WHERE 1=1";
        if ($branch_id !== null) {
            $sql .= " AND s.branch_id = " . (int) $branch_id;
        }
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(s.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(s.created_at) <= '$ed'";
        }
        $res = $conn->query($sql);
        return $res ? (int) $res->fetch_assoc()['count'] : 0;
    }

    public static function get_today_stats()
    {
        global $conn;
        $today = date('Y-m-d');

        // Today's revenue
        $rev_query = $conn->query("SELECT SUM(final_amount) as total FROM sales WHERE DATE(created_at) = '$today' AND status = 'Completed'");
        $revenue = 0.00;
        if ($rev_query && $row = $rev_query->fetch_assoc()) {
            $revenue = (float) ($row['total'] ?? 0.00);
        }

        // Today's orders count
        $count_query = $conn->query("SELECT COUNT(*) as total FROM sales WHERE DATE(created_at) = '$today'");
        $orders_count = 0;
        if ($count_query && $row = $count_query->fetch_assoc()) {
            $orders_count = (int) ($row['total'] ?? 0);
        }

        // Today's refunded / cancelled count
        $refund_query = $conn->query("SELECT COUNT(*) as total FROM sales WHERE DATE(created_at) = '$today' AND status IN ('Refunded', 'Cancelled')");
        $refund_count = 0;
        if ($refund_query && $row = $refund_query->fetch_assoc()) {
            $refund_count = (int) ($row['total'] ?? 0);
        }

        $refund_rate = 0.0;
        if ($orders_count > 0) {
            $refund_rate = round(($refund_count / $orders_count) * 100, 1);
        }

        return [
            'revenue' => $revenue,
            'orders' => $orders_count,
            'refund_rate' => $refund_rate
        ];
    }

    public static function get_dt_sales($start, $length, $search, $order_col, $order_dir, $branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;

        $sql = "SELECT s.*, u.name as cashier_name 
                FROM sales s 
                LEFT JOIN users u ON s.cashier_id = u.id 
                WHERE 1=1";

        if ($branch_id !== null) {
            $sql .= " AND s.branch_id = " . (int) $branch_id;
        }

        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(s.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(s.created_at) <= '$ed'";
        }

        if (!empty($search)) {
            $s = "%$search%";
            $sql .= " AND (s.id LIKE ? OR u.name LIKE ? OR s.payment_method LIKE ? OR s.status LIKE ?)";
        }

        $order_cols = ['s.id', 'u.name', 's.total_amount', 's.discount_amount', 's.final_amount', 's.payment_method', 's.status', 's.created_at'];
        $order_by = $order_cols[$order_col] ?? 's.id';
        $sql .= " ORDER BY $order_by $order_dir LIMIT ?, ?";

        $stmt = $conn->prepare($sql);

        if (!empty($search)) {
            $stmt->bind_param("ssssii", $s, $s, $s, $s, $start, $length);
        } else {
            $stmt->bind_param("ii", $start, $length);
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $sales = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $sales;
    }

    public static function count_dt_sales_filtered($search, $branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        if (empty($search) && !$start_date && !$end_date)
            return self::get_total_sales_count($branch_id);

        $sql = "SELECT COUNT(*) as count 
                FROM sales s 
                LEFT JOIN users u ON s.cashier_id = u.id 
                WHERE 1=1";

        if ($branch_id !== null) {
            $sql .= " AND s.branch_id = " . (int) $branch_id;
        }

        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(s.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(s.created_at) <= '$ed'";
        }

        if (!empty($search)) {
            $sql .= " AND (s.id LIKE ? OR u.name LIKE ? OR s.payment_method LIKE ? OR s.status LIKE ?)";
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

    public static function get_summary_stats($branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $sql = "SELECT 
                    SUM(final_amount) as revenue,
                    SUM(discount_amount) as discounts,
                    COUNT(*) as count
                  FROM sales s WHERE s.status = 'Completed'";

        if ($branch_id !== null) {
            $sql .= " AND s.branch_id = " . (int) $branch_id;
        }

        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND DATE(s.created_at) >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND DATE(s.created_at) <= '$ed'";
        }

        $res = $conn->query($sql);
        return $res ? $res->fetch_assoc() : ['revenue' => 0, 'discounts' => 0, 'count' => 0];
    }

    public static function update_status($sale_id, $status)
    {
        global $conn;
        $stmt = $conn->prepare("UPDATE sales SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $sale_id);
        $res = $stmt->execute();
        $stmt->close();
        return $res;
    }
}
