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
        if ($start_date && $end_date) {
            $where[] = "e.expense_date BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "'";
        }

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

        return $conn->query("INSERT INTO expenses (branch_id, category, amount, expense_date, description) 
                             VALUES ($branch_id, '$category', $amount, '$date', '$description')");
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
