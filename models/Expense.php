<?php
class Expense {
    public static function get_all_filtered($branch_id = null, $start_date = null, $end_date = null) {
        global $conn;
        $sql = "SELECT e.*, b.name as branch_name 
                FROM expenses e 
                LEFT JOIN branches b ON e.branch_id = b.id";
        
        $where = [];
        if ($branch_id) {
            $where[] = "e.branch_id = " . intval($branch_id);
        }
        if ($start_date) $where[] = "e.expense_date >= '" . $conn->real_escape_string($start_date) . "'";
        if ($end_date) $where[] = "e.expense_date <= '" . $conn->real_escape_string($end_date) . "'";

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $sql .= " ORDER BY e.expense_date DESC";
        $result = $conn->query($sql);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function get_all() {
        global $conn;
        $result = $conn->query("SELECT e.*, b.name as branch_name 
                                FROM expenses e 
                                LEFT JOIN branches b ON e.branch_id = b.id 
                                ORDER BY e.expense_date DESC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public static function create($data) {
        global $conn;
        $branch_id = !empty($data['branch_id']) ? intval($data['branch_id']) : 'NULL';
        $category = $conn->real_escape_string($data['category']);
        $amount = floatval($data['amount']);
        $date = $conn->real_escape_string($data['expense_date']);
        $description = $conn->real_escape_string($data['description'] ?? '');

        if ($conn->query("INSERT INTO expenses (branch_id, category, amount, expense_date, description) 
                             VALUES ($branch_id, '$category', $amount, '$date', '$description')")) {
            return $conn->insert_id;
        }
        return false;
    }

    public static function delete($id) {
        global $conn;
        $id = intval($id);
        return $conn->query("DELETE FROM expenses WHERE id = $id");
    }

    public static function get_by_id($id) {
        global $conn;
        $id = intval($id);
        $res = $conn->query("SELECT * FROM expenses WHERE id = $id");
        return $res ? $res->fetch_assoc() : null;
    }

    public static function update($id, $data) {
        global $conn;
        $id = intval($id);
        $branch_id = !empty($data['branch_id']) ? intval($data['branch_id']) : 'NULL';
        $category = $conn->real_escape_string($data['category']);
        $amount = floatval($data['amount']);
        $date = $conn->real_escape_string($data['expense_date']);
        $description = $conn->real_escape_string($data['description'] ?? '');

        return $conn->query("UPDATE expenses SET 
                             branch_id = $branch_id, 
                             category = '$category', 
                             amount = $amount, 
                             expense_date = '$date', 
                             description = '$description' 
                             WHERE id = $id");
    }

    public static function get_dt_expenses($start, $length, $search, $order_col, $order_dir, $branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $order_dir = strtoupper((string)$order_dir) === 'ASC' ? 'ASC' : 'DESC';
        $sql = "SELECT e.*, b.name as branch_name 
                FROM expenses e 
                LEFT JOIN branches b ON e.branch_id = b.id
                WHERE 1=1";

        if ($branch_id !== null) {
            $sql .= " AND e.branch_id = " . (int) $branch_id;
        }
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND e.expense_date >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND e.expense_date <= '$ed'";
        }
        if (!empty($search)) {
            $s = "%$search%";
            $sql .= " AND (e.category LIKE ? OR e.description LIKE ? OR b.name LIKE ?)";
        }

        $order_cols = ['e.expense_date', 'e.category', 'e.description', 'b.name', 'e.amount'];
        $order_by = $order_cols[$order_col] ?? 'e.expense_date';
        $sql .= " ORDER BY $order_by $order_dir LIMIT ?, ?";

        $stmt = $conn->prepare($sql);
        if (!empty($search)) {
            $stmt->bind_param("sssii", $s, $s, $s, $start, $length);
        } else {
            $stmt->bind_param("ii", $start, $length);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        return $rows;
    }

    public static function count_dt_expenses_filtered($search, $branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $sql = "SELECT COUNT(*) as count 
                FROM expenses e 
                LEFT JOIN branches b ON e.branch_id = b.id
                WHERE 1=1";

        if ($branch_id !== null) {
            $sql .= " AND e.branch_id = " . (int) $branch_id;
        }
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND e.expense_date >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND e.expense_date <= '$ed'";
        }
        if (!empty($search)) {
            $s = "%$search%";
            $stmt = $conn->prepare($sql . " AND (e.category LIKE ? OR e.description LIKE ? OR b.name LIKE ?)");
            $stmt->bind_param("sss", $s, $s, $s);
            $stmt->execute();
            $result = $stmt->get_result();
            $count = ($result && $row = $result->fetch_assoc()) ? (int) $row['count'] : 0;
            $stmt->close();
            return $count;
        } else {
            $result = $conn->query($sql);
            return ($result && $row = $result->fetch_assoc()) ? (int) $row['count'] : 0;
        }
    }

    public static function get_total_expenses_count($branch_id = null, $start_date = null, $end_date = null)
    {
        global $conn;
        $sql = "SELECT COUNT(*) as count FROM expenses e WHERE 1=1";
        if ($branch_id !== null) {
            $sql .= " AND e.branch_id = " . (int) $branch_id;
        }
        if ($start_date) {
            $sd = $conn->real_escape_string($start_date);
            $sql .= " AND e.expense_date >= '$sd'";
        }
        if ($end_date) {
            $ed = $conn->real_escape_string($end_date);
            $sql .= " AND e.expense_date <= '$ed'";
        }
        $result = $conn->query($sql);
        return ($result && $row = $result->fetch_assoc()) ? (int) $row['count'] : 0;
    }

    public static function get_stats($branch_id = null, $start_date = null, $end_date = null) {
        global $conn;
        $stats = [
            'total_this_month' => 0,
            'count_today' => 0,
            'highest_category' => 'N/A'
        ];

        // Base where for the period
        if ($start_date && $end_date) {
            $where = "WHERE expense_date BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "'";
        } else {
            $where = "WHERE MONTH(expense_date) = MONTH(CURRENT_DATE()) AND YEAR(expense_date) = YEAR(CURRENT_DATE())";
        }

        if ($branch_id) {
            $where .= " AND branch_id = " . intval($branch_id);
        }

        // Total for the period
        $res = $conn->query("SELECT SUM(amount) as total FROM expenses $where");
        if ($res && $row = $res->fetch_assoc()) {
            $stats['total_this_month'] = $row['total'] ?? 0;
        }

        $where_today = "WHERE expense_date = CURRENT_DATE()";
        if ($branch_id) {
            $where_today .= " AND branch_id = " . intval($branch_id);
        }

        // Count today
        $res = $conn->query("SELECT COUNT(*) as count FROM expenses $where_today");
        if ($res && $row = $res->fetch_assoc()) {
            $stats['count_today'] = $row['count'] ?? 0;
        }

        // Highest category for the same period
        $res = $conn->query("SELECT category FROM expenses $where GROUP BY category ORDER BY SUM(amount) DESC LIMIT 1");
        if ($res && $row = $res->fetch_assoc()) {
            $stats['highest_category'] = $row['category'];
        }

        return $stats;
    }
}
