<?php
require_once __DIR__ . '/../config/db.php';

class ActivityLog {
    public static function log($action, $description = null) {
        global $conn;
        $user_id = $_SESSION['user']['id'] ?? null;
        $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        $stmt = $conn->prepare("INSERT INTO activity_logs (user_id, action, description, ip_address) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $action, $description, $ip_address);
        return $stmt->execute();
    }

    public static function countAll() {
        global $conn;
        $res = $conn->query("SELECT COUNT(*) as count FROM activity_logs");
        return $res->fetch_assoc()['count'];
    }

    public static function countFiltered($filters = []) {
        global $conn;
        $where = ["1=1"];
        $params = [];
        $types = "";

        if (!empty($filters['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['action'])) {
            $where[] = "action LIKE ?";
            $params[] = "%" . $filters['action'] . "%";
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $s = "%" . $filters['search'] . "%";
            $where[] = "(action LIKE ? OR description LIKE ? OR ip_address LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s;
            $types .= "sss";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "created_at >= ?";
            $params[] = $filters['date_from'] . " 00:00:00";
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $where[] = "created_at <= ?";
            $params[] = $filters['date_to'] . " 23:59:59";
            $types .= "s";
        }

        $query = "SELECT COUNT(*) as count FROM activity_logs WHERE " . implode(" AND ", $where);
        $stmt = $conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['count'];
    }

    public static function getDTLogs($start, $length, $filters = [], $order_col = 'created_at', $order_dir = 'DESC') {
        global $conn;
        $where = ["1=1"];
        $params = [];
        $types = "";

        if (!empty($filters['user_id'])) {
            $where[] = "l.user_id = ?";
            $params[] = $filters['user_id'];
            $types .= "i";
        }

        if (!empty($filters['action'])) {
            $where[] = "l.action LIKE ?";
            $params[] = "%" . $filters['action'] . "%";
            $types .= "s";
        }

        if (!empty($filters['search'])) {
            $s = "%" . $filters['search'] . "%";
            $where[] = "(l.action LIKE ? OR l.description LIKE ? OR l.ip_address LIKE ? OR u.name LIKE ?)";
            $params[] = $s; $params[] = $s; $params[] = $s; $params[] = $s;
            $types .= "ssss";
        }

        if (!empty($filters['date_from'])) {
            $where[] = "l.created_at >= ?";
            $params[] = $filters['date_from'] . " 00:00:00";
            $types .= "s";
        }

        if (!empty($filters['date_to'])) {
            $where[] = "l.created_at <= ?";
            $params[] = $filters['date_to'] . " 23:59:59";
            $types .= "s";
        }

        $query = "SELECT l.*, u.name as user_name, u.avatar_url 
                  FROM activity_logs l 
                  LEFT JOIN users u ON l.user_id = u.id 
                  WHERE " . implode(" AND ", $where) . " 
                  ORDER BY $order_col $order_dir 
                  LIMIT ?, ?";
        
        $params[] = $start;
        $params[] = $length;
        $types .= "ii";

        $stmt = $conn->prepare($query);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
