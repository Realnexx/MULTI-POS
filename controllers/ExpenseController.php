<?php
require_once 'models/Expense.php';
require_once 'models/Branch.php';
require_once 'models/Sale.php';
require_once 'models/Setting.php';

class ExpenseController
{
    public function index()
    {
        require_permission('manage_expenses');
        $branch_id = $_GET['branch_id'] ?? null;
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        
        $store_name = Setting::get('store_name', 'Stacey Halo Halo');
        $expenses = Expense::get_all_filtered($branch_id); // Still needed for some legacy parts or global view
        $branches = Branch::for_current_user(false);
        $expense_stats = Expense::get_stats($branch_id, $start_date, $end_date);

        // Fetch Monthly Revenue (Sales)
        global $conn;
        $month = date('m');
        $year = date('Y');
        
        if ($start_date && $end_date) {
            $sales_where = "WHERE DATE(created_at) BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "' AND status = 'Completed'";
        } else {
            $sales_where = "WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year' AND status = 'Completed'";
        }

        if ($branch_id) {
            $sales_where .= " AND branch_id = " . intval($branch_id);
        }

        $query = "SELECT SUM(final_amount) as total FROM sales $sales_where";
        $revenue_res = $conn->query($query);
        $total_revenue = $revenue_res ? (float)($revenue_res->fetch_assoc()['total'] ?? 0.00) : 0.00;

        $stats = [
            'total_revenue' => $total_revenue,
            'total_expenses' => $expense_stats['total_this_month'],
            'net_profit' => $total_revenue - $expense_stats['total_this_month'],
            'pending_invoices' => 0.00, // Placeholder
            'highest_category' => $expense_stats['highest_category'],
            'count_today' => $expense_stats['count_today']
        ];

        // Fetch Weekly Cashflow Data (Last 7 Days)
        $weekly_labels = [];
        $weekly_income = [];
        $weekly_expenses = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $day_name = date('D', strtotime($date));
            $weekly_labels[] = $day_name;

            $income_where = "WHERE DATE(created_at) = '$date' AND status = 'Completed'";
            $exp_where = "WHERE DATE(expense_date) = '$date'";
            if ($branch_id) {
                $income_where .= " AND branch_id = " . intval($branch_id);
                $exp_where .= " AND branch_id = " . intval($branch_id);
            }

            // Income for this day
            $income_q = $conn->query("SELECT SUM(final_amount) as total FROM sales $income_where");
            $income_row = $income_q->fetch_assoc();
            $weekly_income[] = (float)($income_row['total'] ?? 0);

            // Expenses for this day
            $expense_q = $conn->query("SELECT SUM(amount) as total FROM expenses $exp_where");
            $expense_row = $expense_q->fetch_assoc();
            $weekly_expenses[] = (float)($expense_row['total'] ?? 0);
        }

        $cashflow_data = [
            'labels' => $weekly_labels,
            'income' => $weekly_income,
            'expenses' => $weekly_expenses
        ];

        include 'views/modules/expenses.php';
    }

    public function data() {
        require_permission('manage_expenses');
        global $conn;
        header('Content-Type: application/json');

        // DataTables parameters
        $draw = intval($_GET['draw'] ?? 1);
        $start = intval($_GET['start'] ?? 0);
        $length = intval($_GET['length'] ?? 10);
        $search = $_GET['search']['value'] ?? '';
        $branch_id = $_GET['branch_id'] ?? null;
        $start_date = $_GET['start_date'] ?? null;
        $end_date = $_GET['end_date'] ?? null;
        $order_col_idx = intval($_GET['order'][0]['column'] ?? 0);
        $order_dir = $_GET['order'][0]['dir'] ?? 'desc';

        $columns = ['expense_date', 'description', 'category', 'branch_name', 'amount'];
        $order_col = $columns[$order_col_idx] ?? 'expense_date';

        // Base Query
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
        if (!empty($search)) {
            $s = $conn->real_escape_string($search);
            $where[] = "(e.category LIKE '%$s%' OR e.description LIKE '%$s%' OR b.name LIKE '%$s%')";
        }

        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        // Total records count (Total in database)
        $total_res = $conn->query("SELECT COUNT(*) as count FROM expenses");
        $recordsTotal = $total_res->fetch_assoc()['count'] ?? 0;

        // Filtered records count (Taking search and branch into account)
        $filtered_res = $conn->query("SELECT COUNT(*) as count FROM ($sql) as filtered");
        $recordsFiltered = $filtered_res->fetch_assoc()['count'] ?? 0;

        // Ordering & Paging
        $sql .= " ORDER BY $order_col $order_dir LIMIT $start, $length";
        
        $data_res = $conn->query($sql);
        $data = [];
        while ($row = $data_res->fetch_assoc()) {
            $data[] = $row;
        }

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
        exit;
    }

    public function print() {
        require_permission('manage_expenses');
        global $conn;
        $branch_id = $_GET['branch_id'] ?? null;
        $start_date = $this->printDate($_GET['start_date'] ?? null, date('Y-m-01'));
        $end_date = $this->printDate($_GET['end_date'] ?? null, date('Y-m-t'));
        if ($start_date > $end_date) { http_response_code(400); exit('The start date must be before the end date.'); }

        $expenses = Expense::get_all_filtered($branch_id, $start_date, $end_date);
        $search = trim((string) ($_GET['search'] ?? ''));
        if ($search !== '') {
            $count = Expense::count_dt_expenses_filtered($search, $branch_id ?: null, $start_date, $end_date);
            $expenses = Expense::get_dt_expenses(0, max(1, $count), $search, 0, 'DESC', $branch_id ?: null, $start_date, $end_date);
        }
        $branches = Branch::for_current_user(false);
        $expense_stats = Expense::get_stats($branch_id, $start_date, $end_date);
        
        // Fetch Monthly Revenue (Sales) for the period
        $month = date('m');
        $year = date('Y');
        
        if ($start_date && $end_date) {
            $sales_where = "WHERE DATE(created_at) BETWEEN '" . $conn->real_escape_string($start_date) . "' AND '" . $conn->real_escape_string($end_date) . "' AND status = 'Completed'";
        } else {
            $sales_where = "WHERE MONTH(created_at) = '$month' AND YEAR(created_at) = '$year' AND status = 'Completed'";
        }

        if ($branch_id) {
            $sales_where .= " AND branch_id = " . intval($branch_id);
        }

        $query = "SELECT SUM(final_amount) as total FROM sales $sales_where";
        $revenue_res = $conn->query($query);
        $total_revenue = $revenue_res ? (float)($revenue_res->fetch_assoc()['total'] ?? 0.00) : 0.00;

        $stats = [
            'total_revenue' => $total_revenue,
            'total_expenses' => $expense_stats['total_this_month'],
            'net_profit' => $total_revenue - $expense_stats['total_this_month'],
            'highest_category' => $expense_stats['highest_category']
        ];
        
        $selected_branch_name = "Global Network";
        if ($branch_id) {
            foreach ($branches as $b) {
                if ($b['id'] == $branch_id) {
                    $selected_branch_name = $b['name'];
                    break;
                }
            }
        }

        $store_name = Setting::get('store_name', 'Stacey Halo Halo');
        
        include 'views/modules/expenses_print.php';
        exit;
    }

    public function summary_print() {
        require_permission('manage_expenses');
        global $conn;
        $access = User::access_context((int)$_SESSION['user']['id']);
        if (!$access['global'] && !$access['branch_id']) { http_response_code(403); exit('Ask an administrator to assign your branch first.'); }
        $branch_id = $access['global'] ? (!empty($_GET['branch_id']) ? (int)$_GET['branch_id'] : null) : $access['branch_id'];
        if ($branch_id !== null && !in_array((int)$branch_id, array_map('intval',array_column(Branch::for_current_user(false),'id')),true)) { http_response_code(403); exit('Select an accessible branch.'); }
        $monthly_report = isset($_GET['month']);
        if ($monthly_report) {
            $month = (string)$_GET['month'];
            $monthDate = DateTime::createFromFormat('!Y-m', $month);
            if (!$monthDate || $monthDate->format('Y-m') !== $month) { http_response_code(400); exit('Select a valid reporting month.'); }
            $start_date = $monthDate->format('Y-m-01');
            $end_date = $monthDate->format('Y-m-t');
        } else {
            $start_date = $this->printDate($_GET['start_date'] ?? null, date('Y-m-01'));
            $end_date = $this->printDate($_GET['end_date'] ?? null, date('Y-m-t'));
        }
        if ($start_date > $end_date || (strtotime($end_date)-strtotime($start_date))/86400 > 3660) { http_response_code(400); exit('Choose a valid report range of up to ten years.'); }

        $branches = Branch::for_current_user(false);
        $store_name = Setting::get('store_name', 'Stacey Halo Halo');
        
        $selected_branch_name = "Global Network";
        if ($branch_id) {
            foreach ($branches as $b) {
                if ($b['id'] == $branch_id) {
                    $selected_branch_name = $b['name'];
                    break;
                }
            }
        }

        // Aggregate daily data
        $summary = [];
        $current = strtotime($start_date);
        $last = strtotime($end_date);

        while ($current <= $last) {
            $date = date('Y-m-d', $current);
            $summary[$date] = [
                'date' => $date,
                'sales' => 0,
                'expenses' => 0,
                'others' => 0,
                'net' => 0
            ];
            $current = strtotime("+1 day", $current);
        }

        // Fetch Sales
        $sales_q = "SELECT DATE(created_at) as date, SUM(final_amount) as total FROM sales WHERE status = 'Completed' AND DATE(created_at) BETWEEN '$start_date' AND '$end_date'";
        if ($branch_id) $sales_q .= " AND branch_id = " . intval($branch_id);
        $sales_q .= " GROUP BY DATE(created_at)";
        $res = $conn->query($sales_q);
        while ($row = $res->fetch_assoc()) {
            if (isset($summary[$row['date']])) $summary[$row['date']]['sales'] = (float)$row['total'];
        }

        // Fetch Expenses
        $exp_q = "SELECT expense_date as date, category, SUM(amount) as total FROM expenses WHERE expense_date BETWEEN '$start_date' AND '$end_date'";
        if ($branch_id) $exp_q .= " AND branch_id = " . intval($branch_id);
        $exp_q .= " GROUP BY expense_date, category";
        $res = $conn->query($exp_q);
        while ($row = $res->fetch_assoc()) {
            if (isset($summary[$row['date']])) {
                if ($row['category'] === 'Other' || $row['category'] === 'Miscellaneous') {
                    $summary[$row['date']]['others'] += (float)$row['total'];
                } else {
                    $summary[$row['date']]['expenses'] += (float)$row['total'];
                }
            }
        }

        // Calculate Net and Filter out empty days if range is long? 
        // No, user wants grouped by day.
        $totals = ['sales' => 0, 'expenses' => 0, 'others' => 0, 'net' => 0];
        foreach ($summary as &$day) {
            $day['net'] = $day['sales'] - $day['expenses'] - $day['others'];
            $totals['sales'] += $day['sales'];
            $totals['expenses'] += $day['expenses'];
            $totals['others'] += $day['others'];
            $totals['net'] += $day['net'];
        }

        include 'views/modules/expenses_summary_print.php';
        exit;
    }
    private function printDate($value, $fallback)
    {
        if (!$value) return $fallback;
        $date = DateTime::createFromFormat('!Y-m-d', (string) $value);
        if (!$date || $date->format('Y-m-d') !== $value) { http_response_code(400); exit('Please choose a valid date.'); }
        return $value;
    }

}
