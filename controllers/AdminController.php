<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../models/Role.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Website.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Setting.php';

class AdminController {
    public function dashboard() {
        require_permission('view_dashboard');
        require_once __DIR__ . '/../models/Branch.php';

        $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
        $all_branches = $is_admin ? Branch::get_all_active() : [];

        // Determine which branch to display data for
        $selected_branch = 'all'; // Default fallback
        if ($is_admin) {
            // Admin: use GET param, fallback to session, fallback to 'all'
            $selected_branch = $_GET['branch'] ?? $_SESSION['dashboard_branch'] ?? 'all';
            $_SESSION['dashboard_branch'] = $selected_branch; // persist selection
        } else {
            // Non-admin: locked to their own branch
            $selected_branch = $_SESSION['user']['branch'] ?? null;
        }

        $branch_filter = ($selected_branch === 'all') ? null : $selected_branch;

        // Date Range logic
        $date_range = $_GET['date_range'] ?? null; // e.g., "2023-01-01 to 2023-01-07"
        $start_date = null;
        $end_date = null;

        if ($date_range) {
            $dates = explode(' to ', $date_range);
            $start_date = $dates[0] ?? null;
            $end_date = $dates[1] ?? $start_date; // fallback to start_date if single date
        }

        $stats        = Sale::get_dashboard_stats($branch_filter, $start_date, $end_date);
        $recent_sales = Sale::get_recent_transactions(10, $branch_filter, $start_date, $end_date);
        $low_stock    = Product::get_low_stock_products();
        $top_selling  = Sale::get_top_selling_products(3, $branch_filter, $start_date, $end_date);

        require __DIR__ . '/../views/modules/dashboard.php';
    }

    public function users() {
        require_permission('manage_users');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $firstname = sanitize($_POST['firstname'] ?? '');
                $middlename = sanitize($_POST['middlename'] ?? '');
                $lastname = sanitize($_POST['lastname'] ?? '');
                $extname = sanitize($_POST['extname'] ?? '');
                $email = sanitize($_POST['email'] ?? '');
                $password = $_POST['password'] ?? '';
                $roles = $_POST['roles'] ?? [];

                if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
                    set_flash('danger', 'All fields are required.');
                } elseif (User::find_by_email($email)) {
                    set_flash('danger', 'Email already exists.');
                } else {
                    $username = explode('@', $email)[0];
                    $image = $this->handleUserImage($_FILES['image'] ?? null);
                    User::create($firstname, $middlename, $lastname, $extname, $username, $email, $password, $roles, $image);
                    User::log_activity($_SESSION['user']['id'], 'USER_CREATE', 'Created user: ' . $email);
                    set_flash('success', 'User created successfully.');
                }
                redirect('?route=admin/users');
            } elseif ($action === 'edit') {
                $id = intval($_POST['user_id'] ?? 0);
                $firstname = sanitize($_POST['firstname'] ?? '');
                $middlename = sanitize($_POST['middlename'] ?? '');
                $lastname = sanitize($_POST['lastname'] ?? '');
                $extname = sanitize($_POST['extname'] ?? '');
                $email = sanitize($_POST['email'] ?? '');
                $password = !empty($_POST['password']) ? $_POST['password'] : null;
                $roles = $_POST['roles'] ?? [];

                $image = $this->handleUserImage($_FILES['image'] ?? null);
                User::update($id, $firstname, $middlename, $lastname, $extname, $email, $password, $roles, $image);
                User::log_activity($_SESSION['user']['id'], 'USER_UPDATE', 'Updated user: ' . $email . ' (ID: ' . $id . ')');
                set_flash('success', 'User updated successfully.');
                redirect('?route=admin/users');
            } elseif ($action === 'delete') {
                $id = intval($_POST['user_id'] ?? 0);
                if ($id === $_SESSION['user']['id']) {
                    set_flash('danger', 'You cannot delete your own account.');
                } else {
                    User::delete($id);
                    User::log_activity($_SESSION['user']['id'], 'USER_DELETE', 'Deleted user ID: ' . $id);
                    set_flash('success', 'User deleted successfully.');
                }
                redirect('?route=admin/users');
            }
        }

        require __DIR__ . '/../views/modules/users.php';
    }

    public function getUserData() {
        require_permission('manage_users');
        header('Content-Type: application/json');
        
        $draw = intval($_GET['draw'] ?? 0);
        $start = intval($_GET['start'] ?? 0);
        $length = intval($_GET['length'] ?? 10);
        $search = $_GET['search']['value'] ?? '';
        $order_col = intval($_GET['order'][0]['column'] ?? 0);
        $order_dir = $_GET['order'][0]['dir'] ?? 'DESC';

        $users = User::get_dt_users($start, $length, $search, $order_col, $order_dir);
        $totalRecords = User::count_all_users();
        $totalFiltered = User::count_filtered_users($search);

        $role_counts = User::get_role_distribution();

        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalFiltered,
            'data' => $users,
            'role_counts' => $role_counts
        ]);
        exit;
    }

    public function getRoles() {
        require_permission('manage_roles');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'roles' => Role::get_all_roles()
        ]);
        exit;
    }

    public function getPermissionsData() {
        require_permission('manage_roles');
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'permissions' => Role::get_all_permissions()
        ]);
        exit;
    }

    public function getSingleUser() {
        require_permission('manage_users');
        header('Content-Type: application/json');
        $id = intval($_GET['id'] ?? 0);
        $user = User::find_by_id($id);
        
        if ($user) {
            // Include roles since find_by_id doesn't natively include them in the same format as ALL
            $roles = User::get_user_roles($id);
            $user['roles'] = implode(', ', array_column($roles, 'name'));
            
            echo json_encode(['success' => true, 'user' => $user]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
        exit;
    }

    public function roles() {
        require_permission('manage_roles');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'create') {
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $permissions = $_POST['permissions'] ?? [];

                if (empty($name)) {
                    set_flash('danger', 'Role name is required.');
                } else {
                    Role::create($name, $description, $permissions);
                    User::log_activity($_SESSION['user']['id'], 'ROLE_CREATE', 'Created role: ' . $name);
                    set_flash('success', 'Role created successfully.');
                }
                redirect('?route=admin/roles');
            } elseif ($action === 'edit') {
                $id = intval($_POST['role_id'] ?? 0);
                $name = sanitize($_POST['name'] ?? '');
                $description = sanitize($_POST['description'] ?? '');
                $permissions = $_POST['permissions'] ?? [];

                Role::update($id, $name, $description, $permissions);
                User::log_activity($_SESSION['user']['id'], 'ROLE_UPDATE', 'Updated role: ' . $name . ' (ID: ' . $id . ')');
                set_flash('success', 'Role updated successfully.');
                redirect('?route=admin/roles');
            } elseif ($action === 'delete') {
                $id = intval($_POST['role_id'] ?? 0);
                if (Role::delete($id)) {
                    User::log_activity($_SESSION['user']['id'], 'ROLE_DELETE', 'Deleted role ID: ' . $id);
                    set_flash('success', 'Role deleted successfully.');
                } else {
                    set_flash('danger', 'Cannot delete system role or role in use.');
                }
                redirect('?route=admin/roles');
            }
        }

        $roles = Role::get_all_roles();
        $permissions_grouped = Role::get_all_permissions();
        require __DIR__ . '/../views/modules/roles.php';
    }

    public function cms() {
        require_permission('access_website_admin');
        enforce_csrf();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'update_content') {
                $content = [
                    'hero_title' => sanitize($_POST['hero_title'] ?? ''),
                    'hero_subtitle' => sanitize($_POST['hero_subtitle'] ?? ''),
                    'about_text' => sanitize($_POST['about_text'] ?? ''),
                    'contact_address' => sanitize($_POST['contact_address'] ?? ''),
                    'contact_phone' => sanitize($_POST['contact_phone'] ?? ''),
                    'contact_email' => sanitize($_POST['contact_email'] ?? '')
                ];
                Website::update_content($content);
                set_flash('success', 'Website content updated successfully.');
                redirect('?route=admin/cms');
            } elseif ($action === 'mark_read') {
                $id = intval($_POST['message_id'] ?? 0);
                Website::mark_message_read($id);
                set_flash('success', 'Message marked as read.');
                redirect('?route=admin/cms');
            }
        }

        $content = Website::get_content();
        $messages = Website::get_all_messages();
        require __DIR__ . '/../views/modules/cms.php';
    }

    public function reports() {
        require_once __DIR__ . '/../models/Branch.php';
        require_permission('view_reports');
        
        $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
        $branches = $is_admin ? (Branch::get_all_active() ?: []) : [];
        
        require __DIR__ . '/../views/modules/reports.php';
    }

    public function reportsPrint() {
        require_once __DIR__ . '/../models/Branch.php';
        require_once __DIR__ . '/../models/Product.php';
        require_permission('view_reports');

        $type = $_GET['type'] ?? 'sales';
        $branch_id_req = $_GET['branch_id'] ?? 'all';
        $start_date = $_GET['start_date'] ?? $_GET['date_from'] ?? null;
        $end_date = $_GET['end_date'] ?? $_GET['date_to'] ?? null;
        
        // Advanced filters from POS History
        $payment_status = $_GET['payment_status'] ?? 'All';
        $raw_channels = isset($_GET['channels']) ? (is_array($_GET['channels']) ? $_GET['channels'] : [$_GET['channels']]) : [];
        $raw_locations = isset($_GET['locations']) ? (is_array($_GET['locations']) ? $_GET['locations'] : [$_GET['locations']]) : [];

        $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');
        $branch_id = ($is_admin && $branch_id_req !== 'all') ? (int)$branch_id_req : ($is_admin ? null : ($_SESSION['user']['branch_id'] ?? null));

        $data = [];
        
        if ($type === 'sales') {
            // Build filters for Sale::get_filtered_sales
            $filters = [
                'search' => $_GET['search'] ?? '',
                'date_from' => $start_date,
                'date_to' => $end_date,
                'payment_status' => $payment_status,
                'channels' => array_filter($raw_channels),
                'locations' => array_filter($raw_locations)
            ];

            // If we have advanced filters beyond just date/branch, use the more robust get_filtered_sales
            if (!empty($filters['channels']) || !empty($filters['locations']) || $filters['payment_status'] !== 'All' || !empty($filters['search'])) {
                $data = Sale::get_filtered_sales($filters, 1, 5000, 's.id', 'DESC');
                
                // Recalculate stats for these specific filtered results
                $rev = 0; $disc = 0; $count = count($data);
                foreach($data as $s) {
                    if ($s['status'] === 'Completed') {
                        $rev += $s['final_amount'];
                        $disc += $s['discount_amount'];
                    }
                }
                $stats = ['revenue' => $rev, 'discounts' => $disc, 'count' => $count];
            } else {
                $stats = Sale::get_summary_stats($branch_id, $start_date, $end_date);
                $data = Sale::get_dt_sales(0, 5000, '', 0, 'desc', $branch_id, $start_date, $end_date);
            }
        } else {
            $stats = Sale::get_summary_stats($branch_id, $start_date, $end_date);
            $data = Product::get_dt_inventory_logs(0, 5000, '', 0, 'desc', $branch_id, $start_date, $end_date);
        }

        $branches = Branch::get_all();
        $selected_branch_name = "Global Network";
        if ($branch_id) {
            foreach ($branches as $b) {
                if ($b['id'] == $branch_id) {
                    $selected_branch_name = $b['name'];
                    break;
                }
            }
        }

        // Pass these for displaying filters in the print view
        $applied_filters = [
            'status' => $payment_status,
            'channels' => $raw_channels,
            'locations' => $raw_locations,
            'search' => $_GET['search'] ?? ''
        ];

        $store_name = Setting::get('store_name', 'Stacey Halo Halo');
        $store_logo = Setting::get('store_logo', '');
        
        require __DIR__ . '/../views/modules/reports_print.php';
        exit;
    }

    public function branches() {
        require_once __DIR__ . '/../models/Branch.php';
        // Require permission or manage_users as fallback
        if (User::has_permission($_SESSION['user']['id'], 'manage_branches')) {
            require_permission('manage_branches');
        } else {
            require_permission('view_dashboard');
        }
        
        $branches = Branch::get_all();
        $stats = Branch::get_stats();
        $settings = Setting::get_by_category('general');
        require __DIR__ . '/../views/modules/branches.php';
    }

    public function branchesReport() {
        require_once __DIR__ . '/../models/Branch.php';
        require_permission('view_reports');
        
        $branches = Branch::get_all();
        $stats = Branch::get_stats();
        $settings = Setting::get_by_category('general');
        
        require __DIR__ . '/../views/modules/branch_report.php';
    }

    public function settings() {
        require_permission('manage_settings');
        enforce_csrf();

        $cashiers = []; // Pre-initialize to prevent undefined variable error

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $category = $_POST['setting_category'] ?? 'general';
            
            // Handle Password Update specifically
            if ($category === 'password') {
                $current_pass = $_POST['current_password'] ?? '';
                $new_pass = $_POST['new_password'] ?? '';
                $confirm_pass = $_POST['confirm_password'] ?? '';
                
                $user = User::find_by_id($_SESSION['user']['id']);
                if (!$user || !password_verify($current_pass, $user['password'])) {
                    set_flash('danger', 'Current password is incorrect.');
                } elseif ($new_pass !== $confirm_pass) {
                    set_flash('danger', 'New passwords do not match.');
                } elseif (strlen($new_pass) < 6) {
                    set_flash('danger', 'New password must be at least 6 characters.');
                } else {
                    User::update_password($user['id'], $new_pass);
                    User::log_activity($user['id'], 'PASSWORD_CHANGE', 'User changed their own password.');
                    set_flash('success', 'Password updated successfully.');
                }
                redirect('?route=admin/settings&tab=password');
            }

            // Handle User PIN updates specifically if they exist
            if (isset($_POST['user_pins']) && is_array($_POST['user_pins'])) {
                foreach ($_POST['user_pins'] as $user_id => $pin) {
                    $user_id = (int)$user_id;
                    $pin = substr($pin, 0, 6); // Updated to 6 digits
                    $GLOBALS['conn']->query("UPDATE users SET pos_pin = '$pin' WHERE id = $user_id");
                }
            }
            $data = $_POST['settings'][$category] ?? [];
            
            // Handle logo upload if present
            if (isset($_FILES['store_logo']) && $_FILES['store_logo']['error'] === UPLOAD_ERR_OK) {
                $targetDir = __DIR__ . '/../assets/img/';
                if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);
                $ext = pathinfo($_FILES['store_logo']['name'], PATHINFO_EXTENSION);
                $filename = 'logo_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['store_logo']['tmp_name'], $targetDir . $filename)) {
                    $data['store_logo'] = 'assets/img/' . $filename;
                }
            }

            Setting::update_all($data, $category);
            User::log_activity($_SESSION['user']['id'], 'SETTINGS_UPDATE', 'Updated settings category: ' . $category);
            set_flash('success', 'Settings updated successfully.');
            redirect('?route=admin/settings&tab=' . $category);
        }

        $all_settings = [
            'general' => Setting::get_by_category('general'),
            'financial' => Setting::get_by_category('financial'),
            'pos' => Setting::get_by_category('pos'),
            'appearance' => Setting::get_by_category('appearance'),
            'security' => Setting::get_by_category('security')
        ];

        // Fetch Cashiers for PIN management
        $res = $GLOBALS['conn']->query("SELECT u.id, u.name, u.pos_pin, b.name as branch_name 
                                        FROM users u 
                                        LEFT JOIN branches b ON u.branch_id = b.id 
                                        JOIN user_roles ur ON u.id = ur.user_id 
                                        JOIN roles r ON ur.role_id = r.id 
                                        WHERE r.name = 'Cashier' OR r.name = 'Staff'
                                        ORDER BY b.name, u.name");
        if ($res) {
            while($row = $res->fetch_assoc()) $cashiers[] = $row;
        }

        require __DIR__ . '/../views/modules/settings.php';
    }
    private function handleUserImage($file) {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) return null;

        $targetDir = __DIR__ . '/../assets/img/users/';
        if (!file_exists($targetDir)) mkdir($targetDir, 0777, true);

        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $ext;
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            return 'assets/img/users/' . $filename;
        }
    }
    public function activityLogs() {
        require_once __DIR__ . '/../models/ActivityLog.php';
        require_permission('manage_users');

        $users = User::get_all_users();
        $total_logs = ActivityLog::countAll();
        
        require __DIR__ . '/../views/modules/activity_logs.php';
    }

    public function activityLogsPrint() {
        require_once __DIR__ . '/../models/ActivityLog.php';
        require_permission('manage_users');

        $filters = [
            'user_id' => $_GET['user_id'] ?? null,
            'action' => $_GET['action'] ?? null,
            'date_from' => $_GET['date_from'] ?? null,
            'date_to' => $_GET['date_to'] ?? null
        ];

        // Fetch logs with filters (unlimited for print, or a high limit like 1000)
        $logs = ActivityLog::getDTLogs(0, 1000, $filters, 'l.created_at', 'DESC');
        
        $total_logs = count($logs);
        
        require __DIR__ . '/../views/modules/activity_logs_print.php';
    }

    public function getActivityLogsData() {
        require_once __DIR__ . '/../models/ActivityLog.php';
        require_permission('manage_users');

        $params = $_GET;
        $start = $params['start'] ?? 0;
        $length = $params['length'] ?? 10;
        $search = $params['search']['value'] ?? '';

        $filters = [
            'user_id' => $params['user_id'] ?? null,
            'action' => $params['action'] ?? null,
            'date_from' => $params['date_from'] ?? null,
            'date_to' => $params['date_to'] ?? null,
            'search' => $search
        ];

        $order_col_idx = $params['order'][0]['column'] ?? 4;
        $order_dir = $params['order'][0]['dir'] ?? 'desc';
        $order_cols = ['u.name', 'l.action', 'l.description', 'l.ip_address', 'l.created_at'];
        $order_col = $order_cols[$order_col_idx] ?? 'l.created_at';

        $recordsTotal = ActivityLog::countAll();
        $recordsFiltered = ActivityLog::countFiltered($filters);
        $logs = ActivityLog::getDTLogs($start, $length, $filters, $order_col, $order_dir);

        $data = [];
        foreach ($logs as $l) {
            $avatar = !empty($l['avatar_url']) ? $l['avatar_url'] : '';
            $user_initial = strtoupper(substr($l['user_name'] ?? 'S', 0, 1));
            
            $actionClass = 'bg-secondary-subtle text-secondary';
            if (strpos($l['action'], 'DELETE') !== false || strpos($l['action'], 'CANCEL') !== false) $actionClass = 'bg-danger-subtle text-danger';
            if (strpos($l['action'], 'CREATE') !== false || strpos($l['action'], 'LOGIN') !== false) $actionClass = 'bg-success-subtle text-success';
            if (strpos($l['action'], 'UPDATE') !== false) $actionClass = 'bg-warning-subtle text-warning';

            $data[] = [
                // User Column
                '<div class="d-flex align-items-center gap-3">
                    <div class="avatar-circle rounded-circle bg-secondary-subtle d-flex align-items-center justify-content-center fw-bold text-secondary" style="width: 32px; height: 32px; font-size: 12px; overflow:hidden;">' . 
                    (!empty($avatar) ? '<img src="' . htmlspecialchars($avatar) . '" class="w-100 h-100 object-fit-cover">' : $user_initial) . 
                    '</div>
                    <div>
                        <div class="fw-bold text-dark small">' . htmlspecialchars($l['user_name'] ?? 'System') . '</div>
                        <div class="text-muted smaller" style="font-size: 10px;">User ID: #' . ($l['user_id'] ?? '0') . '</div>
                    </div>
                </div>',
                // Action
                '<span class="badge ' . $actionClass . ' rounded-pill px-3 py-1 fw-bold smaller">' . htmlspecialchars($l['action']) . '</span>',
                // Description
                '<span class="text-muted small">' . htmlspecialchars($l['description']) . '</span>',
                // IP Address
                '<code class="small text-secondary bg-light px-2 py-1 rounded">' . htmlspecialchars($l['ip_address']) . '</code>',
                // Timestamp
                '<div class="text-dark fw-medium small">' . date('M d, Y', strtotime($l['created_at'])) . '</div>
                 <div class="text-muted smaller" style="font-size: 10px;">' . date('h:i:s A', strtotime($l['created_at'])) . '</div>'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode([
            "draw" => intval($params['draw'] ?? 1),
            "recordsTotal" => intval($recordsTotal),
            "recordsFiltered" => intval($recordsFiltered),
            "data" => $data
        ]);
        exit;
    }
}
