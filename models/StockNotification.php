<?php
require_once __DIR__ . '/../config/db.php';

class StockNotification
{
    /**
     * Automatically scan products and sync stock alert notifications in the database.
     */
    public static function sync_stock_alerts($branch_id = null)
    {
        global $conn;

        $sql = "SELECT p.*, c.name as category_name, b.name as branch_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id 
                LEFT JOIN branches b ON p.branch_id = b.id 
                WHERE (p.status = 'active' OR p.status IS NULL) AND p.track_stock = 1";

        if ($branch_id !== null && $branch_id > 0) {
            $sql .= " AND (p.branch_id = " . (int)$branch_id . " OR p.branch_id IS NULL)";
        }

        $res = $conn->query($sql);
        if (!$res) {
            return false;
        }

        while ($prod = $res->fetch_assoc()) {
            $prod_id = (int)$prod['id'];
            if (($prod['product_type'] ?? '') === 'recipe' && ($prod['recipe_mode'] ?? '') === 'made_to_order') {
                $resolve=$conn->prepare("UPDATE stock_notifications SET status='resolved' WHERE product_id=? AND status='unread'");
                $resolve->bind_param('i',$prod_id); $resolve->execute();
                continue; // Ingredients, rather than synthetic finished stock, drive alerts.
            }
            $stock = (float)$prod['stock'];
            $low_threshold = (float)$prod['low_stock_threshold'];
            $max_threshold = (float)$prod['max_stock_threshold'];
            $p_branch = !empty($prod['branch_id']) ? (int)$prod['branch_id'] : null;
            $name = $prod['name'];

            // Determine stock condition
            $alert_type = null;
            $threshold_val = 0;
            $title = '';
            $message = '';

            if ($stock <= 0) {
                $alert_type = 'out_of_stock';
                $threshold_val = $low_threshold;
                $title = "Out of Stock: {$name}";
                $message = "Product has reached 0 units. Restock is required immediately.";
            } elseif ($stock <= $low_threshold) {
                $alert_type = 'low_stock';
                $threshold_val = $low_threshold;
                $title = "Low Stock Alert: {$name}";
                $message = "Remaining inventory is down to {$stock} units (minimum threshold: {$low_threshold}).";
            } elseif ($max_threshold > 0 && $stock > $max_threshold) {
                $alert_type = 'overstock';
                $threshold_val = $max_threshold;
                $title = "Overstock Alert: {$name}";
                $message = "Current inventory is {$stock} units, exceeding the maximum limit of {$max_threshold}.";
            }

            if ($alert_type !== null) {
                // Check if unread notification of this type already exists for this product
                $check_sql = "SELECT id, current_stock FROM stock_notifications 
                              WHERE product_id = ? AND alert_type = ? AND status = 'unread' LIMIT 1";
                $check_stmt = $conn->prepare($check_sql);
                $check_stmt->bind_param("is", $prod_id, $alert_type);
                $check_stmt->execute();
                $existing = $check_stmt->get_result()->fetch_assoc();
                $check_stmt->close();

                if ($existing) {
                    // Update current stock and timestamp
                    $update_sql = "UPDATE stock_notifications 
                                   SET current_stock = ?, threshold = ?, message = ?, updated_at = CURRENT_TIMESTAMP 
                                   WHERE id = ?";
                    $up_stmt = $conn->prepare($update_sql);
                    $up_stmt->bind_param("ddsi", $stock, $threshold_val, $message, $existing['id']);
                    $up_stmt->execute();
                    $up_stmt->close();
                } else {
                    // Mark any prior conflicting unread alerts for this product as resolved
                    $res_sql = "UPDATE stock_notifications SET status = 'resolved' 
                                WHERE product_id = ? AND status = 'unread'";
                    $res_stmt = $conn->prepare($res_sql);
                    $res_stmt->bind_param("i", $prod_id);
                    $res_stmt->execute();
                    $res_stmt->close();

                    // Insert new alert record
                    $ins_sql = "INSERT INTO stock_notifications 
                                (product_id, branch_id, alert_type, title, message, current_stock, threshold, status) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, 'unread')";
                    $ins_stmt = $conn->prepare($ins_sql);
                    $ins_stmt->bind_param("iisssdd", $prod_id, $p_branch, $alert_type, $title, $message, $stock, $threshold_val);
                    $ins_stmt->execute();
                    $ins_stmt->close();
                }
            } else {
                // Stock is healthy; resolve any lingering unread notifications for this product
                $res_sql = "UPDATE stock_notifications SET status = 'resolved' 
                            WHERE product_id = ? AND status = 'unread'";
                $res_stmt = $conn->prepare($res_sql);
                $res_stmt->bind_param("i", $prod_id);
                $res_stmt->execute();
                $res_stmt->close();
            }
        }

        return true;
    }

    /**
     * Get counts of active unread alerts.
     */
    public static function get_alert_counts($branch_id = null)
    {
        global $conn;

        $sql = "SELECT 
                    COUNT(CASE WHEN sn.status = 'unread' THEN 1 END) as total,
                    COUNT(CASE WHEN sn.status = 'unread' AND sn.alert_type = 'out_of_stock' THEN 1 END) as out_of_stock,
                    COUNT(CASE WHEN sn.status = 'unread' AND sn.alert_type = 'low_stock' THEN 1 END) as low_stock,
                    COUNT(CASE WHEN sn.status = 'unread' AND sn.alert_type = 'overstock' THEN 1 END) as overstock
                FROM stock_notifications sn
                JOIN products p ON sn.product_id = p.id AND (p.status = 'active' OR p.status IS NULL)";

        if ($branch_id !== null && $branch_id > 0) {
            $sql .= " WHERE (sn.branch_id = " . (int)$branch_id . " OR sn.branch_id IS NULL)";
        }

        $res = $conn->query($sql);
        if ($res && $row = $res->fetch_assoc()) {
            return [
                'total' => (int)$row['total'],
                'out_of_stock' => (int)$row['out_of_stock'],
                'low_stock' => (int)$row['low_stock'],
                'overstock' => (int)$row['overstock']
            ];
        }

        return ['total' => 0, 'out_of_stock' => 0, 'low_stock' => 0, 'overstock' => 0];
    }

    /**
     * Retrieve unread or recent stock notifications with product metadata.
     */
    public static function get_active_alerts($limit = 25, $branch_id = null, $type = null)
    {
        global $conn;

        $sql = "SELECT sn.*, p.name as product_name, p.sku, p.image_url, p.price, 
                       c.name as category_name, b.name as branch_name 
                FROM stock_notifications sn
                JOIN products p ON sn.product_id = p.id AND (p.status = 'active' OR p.status IS NULL)
                LEFT JOIN categories c ON p.category_id = c.id
                LEFT JOIN branches b ON sn.branch_id = b.id
                WHERE sn.status = 'unread'";

        if ($branch_id !== null && $branch_id > 0) {
            $sql .= " AND (sn.branch_id = " . (int)$branch_id . " OR sn.branch_id IS NULL)";
        }

        if ($type !== null && in_array($type, ['out_of_stock', 'low_stock', 'overstock'])) {
            $sql .= " AND sn.alert_type = '" . $conn->real_escape_string($type) . "'";
        }

        $sql .= " ORDER BY 
                    CASE sn.alert_type 
                        WHEN 'out_of_stock' THEN 1 
                        WHEN 'low_stock' THEN 2 
                        WHEN 'overstock' THEN 3 
                    END, 
                    sn.updated_at DESC 
                  LIMIT " . (int)$limit;

        $res = $conn->query($sql);
        $alerts = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $row['time_ago'] = self::format_time_ago($row['updated_at']);
                $alerts[] = $row;
            }
        }

        return $alerts;
    }

    /**
     * Mark a single notification as read.
     */
    public static function mark_as_read($id, $branch_id = null)
    {
        global $conn;
        $id = (int)$id;
        $scope = $branch_id !== null ? ' AND branch_id='.(int)$branch_id : '';
        return $conn->query("UPDATE stock_notifications SET status = 'read' WHERE id = $id".$scope);
    }

    /**
     * Mark all active notifications as read.
     */
    public static function mark_all_as_read($branch_id = null)
    {
        global $conn;
        $sql = "UPDATE stock_notifications SET status = 'read' WHERE status = 'unread'";
        if ($branch_id !== null && $branch_id > 0) {
            $sql .= " AND (branch_id = " . (int)$branch_id . " OR branch_id IS NULL)";
        }
        return $conn->query($sql);
    }

    /**
     * Helper to format relative time.
     */
    private static function format_time_ago($timestamp)
    {
        $time = strtotime($timestamp);
        if (!$time) return 'Recently';

        $diff = time() - $time;
        if ($diff < 60) {
            return 'Just now';
        } elseif ($diff < 3600) {
            $mins = max(1, floor($diff / 60));
            return $mins . 'm ago';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $hours . 'h ago';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return $days . 'd ago';
        } else {
            return date('M j', $time);
        }
    }
}
