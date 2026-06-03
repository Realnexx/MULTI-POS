<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../middleware/rbac.php';
require_once __DIR__ . '/../models/Product.php';
require_once __DIR__ . '/../models/Sale.php';
require_once __DIR__ . '/../models/Setting.php';

class PosController {
    public function index() {
        require_permission('access_pos');
        
        $is_admin = User::has_permission($_SESSION['user']['id'], 'manage_users');

        // Handle Branch Reset for Admin
        if ($is_admin && isset($_GET['reset_branch'])) {
            unset($_SESSION['pos_branch_id']);
            redirect('?route=pos');
        }

        // Handle Branch Selection for Admin
        if ($is_admin) {
            if (isset($_GET['select_branch'])) {
                $bid = intval($_GET['select_branch']);
                $_SESSION['pos_branch_id'] = $bid;
                
                require_once __DIR__ . '/../models/Branch.php';
                $b = Branch::find_by_id($bid);
                $bname = $b['name'] ?? 'Selected';
                set_flash('success', $bname . " terminal is now in use.");
                session_write_close();
                redirect('?route=pos');
            }

            if (!isset($_SESSION['pos_branch_id'])) {
                // We'll handle this with a modal in the view now
            }
        } else {
            // For Cashier/Staff, always use their assigned branch
            $_SESSION['pos_branch_id'] = $_SESSION['user']['branch_id'] ?? null;
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }

        $store_name = Setting::get('store_name', 'Stacey Halu');
        $active_branch_id = $_SESSION['pos_branch_id'] ?? null;

        $category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : null;
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;

        $categories = Product::get_all_categories() ?: [];
        $products = Product::get_all_products($category_id, $search) ?: [];

        // Fetch branches for modal if admin
        $branches = [];
        if ($is_admin) {
            require_once __DIR__ . '/../models/Branch.php';
            $branches = Branch::get_all_active();
        }

        require __DIR__ . '/../views/modules/pos.php';
    }

    public function history() {
        require_permission('access_pos');
        $store_name = Setting::get('store_name', 'Stacey Halu');

        // Capture filters
        $raw_channels = isset($_GET['channels']) && is_array($_GET['channels']) ? $_GET['channels'] : [];
        $raw_locations = isset($_GET['locations']) && is_array($_GET['locations']) ? $_GET['locations'] : [];
        
        $filters = [
            'search' => isset($_GET['search']) ? trim($_GET['search']) : '',
            'date_from' => isset($_GET['date_from']) ? trim($_GET['date_from']) : '',
            'date_to' => isset($_GET['date_to']) ? trim($_GET['date_to']) : '',
            'payment_status' => isset($_GET['payment_status']) ? trim($_GET['payment_status']) : 'All',
            'channels' => array_filter($raw_channels),
            'locations' => array_filter($raw_locations),
        ];

        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = 10;

        // Check if CSV export is requested
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            // Get ALL filtered sales for export
            $all_filtered_sales = Sale::get_filtered_sales($filters, 1, 999999);
            
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=transactions_export_' . date('Ymd_His') . '.csv');
            
            $output = fopen('php://output', 'w');
            
            // 1. Premium Metadata Header
            fputcsv($output, [$store_name . ' - Transaction History Report']);
            fputcsv($output, ['Generated On:', date('M d, Y h:i A')]);
            fputcsv($output, ['Report Period:', ($filters['date_from'] ?: 'All Time') . ' to ' . ($filters['date_to'] ?: 'Present')]);
            
            // Add Filter context if active
            $active_filters_str = [];
            if ($filters['payment_status'] !== 'All') $active_filters_str[] = "Status: " . $filters['payment_status'];
            if (!empty($filters['channels'])) $active_filters_str[] = "Channels: " . implode(', ', $filters['channels']);
            if (!empty($filters['locations'])) $active_filters_str[] = "Locations: " . implode(', ', $filters['locations']);
            if (!empty($active_filters_str)) {
                fputcsv($output, ['Active Filters:', implode(' | ', $active_filters_str)]);
            }
            
            fputcsv($output, []); // Spacer row
            
            // 2. CSV Table Headers
            fputcsv($output, ['Order ID', 'Date & Time', 'Cashier', 'Branch', 'Total Amount', 'Discount', 'Final Amount', 'Payment Method', 'Channel', 'Status']);
            
            $total_final = 0;
            $order_count = 0;

            foreach ($all_filtered_sales as $sale) {
                $final = (float)$sale['final_amount'];
                $total_final += $final;
                $order_count++;

                fputcsv($output, [
                    '#HH-' . str_pad($sale['id'], 6, '0', STR_PAD_LEFT),
                    date('Y-m-d H:i:s', strtotime($sale['created_at'])),
                    $sale['cashier_name'] ?? 'System',
                    $sale['branch_name'] ?? 'Main Branch',
                    $sale['total_amount'],
                    $sale['discount_amount'],
                    $final,
                    $sale['payment_method'],
                    $sale['order_type'],
                    $sale['status']
                ]);
            }
            
            // 3. Summary Footer Row
            fputcsv($output, []); // Spacer
            fputcsv($output, ['SUMMARY TOTALS']);
            fputcsv($output, ['Total Order Count:', $order_count]);
            fputcsv($output, ['Total Revenue (Final Amount):', number_format($total_final, 2, '.', '')]);
            
            fclose($output);
            exit;
        }

        // Fetch stats for view
        $today_stats = Sale::get_today_stats();

        // Fetch active branches for the location filter options
        require_once __DIR__ . '/../models/Branch.php';
        $active_branches = Branch::get_all_active();

        require __DIR__ . '/../views/modules/history.php';
    }

    public function receipt() {
        require_permission('access_pos');
        $settings = Setting::get_by_category('general');
        $store_name = $settings['store_name'] ?? 'Stacey Halu';
        $store_address = $settings['store_address'] ?? '123 Sweet Tooth Blvd, Sugar City';
        $store_phone = $settings['store_phone'] ?? '+63 912 345 6789';

        $sale_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
        $sale = Sale::find_sale_by_id($sale_id);

        if (!$sale) {
            set_flash('danger', 'Transaction not found.');
            redirect('?route=pos/history');
        }

        if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
            header('Content-Type: text/html');
            
            $paymentTheme = [
                'bg' => 'theme-cash',
                'border' => 'border-emerald-200/60',
                'text' => 'text-emerald-800',
                'accent' => 'text-emerald-600',
                'icon' => 'payments',
                'glow' => 'shadow-[0_4px_12px_rgba(16,185,129,0.04)]'
            ];

            if ($sale['payment_method'] === 'GCash') {
                $paymentTheme = [
                    'bg' => 'theme-gcash',
                    'border' => 'border-blue-200/60',
                    'text' => 'text-blue-800',
                    'accent' => 'text-blue-600',
                    'icon' => 'account_balance_wallet',
                    'glow' => 'shadow-[0_4px_12px_rgba(59,130,246,0.04)]'
                ];
            } elseif ($sale['payment_method'] === 'Credit Card') {
                $paymentTheme = [
                    'bg' => 'theme-card',
                    'border' => 'border-purple-200/60',
                    'text' => 'text-purple-800',
                    'accent' => 'text-purple-600',
                    'icon' => 'credit_card',
                    'glow' => 'shadow-[0_4px_12px_rgba(147,51,234,0.04)]'
                ];
            }

            $items_html = '';
            foreach ($sale['items'] as $item) {
                $items_html .= '
                    <div class="item-row">
                        <div class="item-main-row">
                            <span class="item-name">' . htmlspecialchars($item['product_name']) . '</span>
                            <div class="dot-leader"></div>
                            <span class="item-subtotal">₱' . number_format($item['subtotal'], 2) . '</span>
                        </div>
                        <div class="item-sub-row">
                            <span class="item-qty-price">' . $item['quantity'] . ' x ₱' . number_format($item['price'], 2) . '</span>
                        </div>
                    </div>
                ';
            }
            ?>
            <div class="thermal-ticket">
                <!-- Top serrated edge -->
                <div class="serrated-edge serrated-top"></div>
                
                <!-- Vintage "PAID" Ink Stamp -->
                <div class="stamp-paid">
                    <span class="stamp-title">OFFICIAL</span>
                    <span class="stamp-main">PAID</span>
                    <span class="stamp-method"><?= htmlspecialchars($sale['payment_method']) ?></span>
                </div>

                <!-- Header -->
                <div class="receipt-header pt-4">
                    <div class="store-badge-circle mb-3">
                        <span class="badge-letter"><?= strtoupper(substr($store_name, 0, 1)) ?></span>
                    </div>
                    <h3 class="receipt-brand font-black" style="font-size: 26px;"><?= htmlspecialchars($store_name) ?></h3>
                    <div class="receipt-contact opacity-90 mt-2">
                        <p class="mb-1"><span class="material-symbols-outlined scale-[0.8] opacity-70">location_on</span> <?= htmlspecialchars($store_address) ?></p>
                        <p><span class="material-symbols-outlined scale-[0.8] opacity-70">call</span> <?= htmlspecialchars($store_phone) ?></p>
                    </div>
                    <div class="header-divider mt-4">
                        <div class="divider-line"></div>
                        <span class="divider-text">OFFICIAL RECEIPT</span>
                        <div class="divider-line"></div>
                    </div>
                </div>

                <!-- Transaction Metadata -->
                <div class="receipt-metadata">
                    <div class="meta-row">
                        <span class="meta-label">Receipt No:</span>
                        <span class="meta-value highlight">#<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Date & Time:</span>
                        <span class="meta-value"><?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Cashier:</span>
                        <span class="meta-value">
                            <span class="material-symbols-outlined">badge</span>
                            <?= htmlspecialchars($sale['cashier_name'] ?? 'Staff') ?>
                        </span>
                    </div>
                    <div class="meta-row">
                        <span class="meta-label">Order Type:</span>
                        <span class="meta-value">
                            <span class="material-symbols-outlined"><?= $sale['order_type'] === 'Take-out' ? 'takeout_dining' : 'dine_in' ?></span>
                            <?= htmlspecialchars($sale['order_type'] ?? 'Dine-in') ?>
                        </span>
                    </div>
                </div>

                <!-- Itemized List -->
                <div class="receipt-items">
                    <div class="items-header">
                        <span>Item Description</span>
                        <span>Amount</span>
                    </div>
                    <div class="items-list">
                        <?= $items_html ?>
                    </div>
                </div>

                <!-- Totals Section -->
                <div class="receipt-summary">
                    <div class="summary-row">
                        <span class="summary-label">Subtotal:</span>
                        <span class="summary-value">₱<?= number_format($sale['total_amount'], 2) ?></span>
                    </div>
                    <div class="summary-row">
                        <span class="summary-label">Discount:</span>
                        <span class="summary-value discount">- ₱<?= number_format($sale['discount_amount'], 2) ?></span>
                    </div>
                    <div class="summary-row total-due-row">
                        <span class="summary-label">Total Due</span>
                        <span class="summary-value total-amount">₱<?= number_format($sale['final_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Payment details and Change Banner -->
                <div class="receipt-payment">
                    <div class="payment-row">
                        <span class="payment-label">Payment Method:</span>
                        <span class="payment-value">
                            <span class="material-symbols-outlined"><?= $paymentTheme['icon'] ?></span>
                            <?= htmlspecialchars($sale['payment_method']) ?>
                        </span>
                    </div>
                    <div class="payment-row">
                        <span class="payment-label">Amount Tendered:</span>
                        <span class="payment-value">₱<?= number_format($sale['amount_tendered'], 2) ?></span>
                    </div>
                    
                    <!-- Change Banner -->
                    <div class="change-banner <?= $paymentTheme['bg'] ?>">
                        <span class="change-label">
                            <span class="material-symbols-outlined">price_check</span> Change Paid
                        </span>
                        <span class="change-amount">₱<?= number_format($sale['change_amount'], 2) ?></span>
                    </div>
                </div>

                <!-- Simulated Barcode -->
                <div class="receipt-barcode">
                    <div class="barcode-lines">
                        <div class="line medium"></div>
                        <div class="space thin"></div>
                        <div class="line medium"></div>
                        <div class="space medium"></div>
                        <div class="line thin"></div>
                        <div class="space thick"></div>
                        <div class="line medium"></div>
                        <div class="space thin"></div>
                        <div class="line extra-thick"></div>
                        <div class="space medium"></div>
                        <div class="line thin"></div>
                        <div class="space medium"></div>
                        <div class="line medium"></div>
                        <div class="space thin"></div>
                        <div class="line medium"></div>
                        <div class="space thin"></div>
                        <div class="line extra-thick"></div>
                        <div class="space thin"></div>
                        <div class="line thick"></div>
                        <div class="space thin"></div>
                        <div class="line medium"></div>
                        <div class="space thin"></div>
                        <div class="line medium"></div>
                    </div>
                    <span class="barcode-text">*SHH-<?= str_pad($sale['id'], 6, '0', STR_PAD_LEFT) ?>*</span>
                </div>

                <!-- Footer -->
                <div class="receipt-footer mt-4">
                    <div class="footer-seal mb-3">
                        <div class="seal-line"></div>
                        <span class="material-symbols-outlined text-primary scale-110">verified</span>
                        <div class="seal-line"></div>
                    </div>
                    <p class="footer-thanks font-black tracking-widest" style="font-size: 11px;">THANK YOU FOR YOUR VISIT!</p>
                    <p class="footer-tagline mt-1 opacity-70 italic text-[9px]">Please keep this for your records.</p>
                    <div class="receipt-policy mt-4 pt-3 border-t border-dashed border-outline-variant border-opacity-20">
                        <p class="text-[8px] opacity-50 uppercase tracking-widest">No returns or exchanges without a receipt.</p>
                    </div>
                </div>
                
                <!-- Bottom serrated edge -->
                <div class="serrated-edge serrated-bottom"></div>
            </div>
            <?php
            exit;
        }

        require __DIR__ . '/../views/modules/receipt.php';
    }

    public function historyData() {
        require_permission('access_pos');
        
        // Handle POST or GET parameters
        $params = !empty($_POST) ? $_POST : $_GET;
        
        $draw = isset($params['draw']) ? intval($params['draw']) : 1;
        $start = isset($params['start']) ? intval($params['start']) : 0;
        $length = isset($params['length']) ? intval($params['length']) : 10;
        
        // Calculate page for SQL query
        $page = floor($start / $length) + 1;
        $limit = $length;
        
        // Capture advanced filters
        $raw_channels = isset($params['channels']) && is_array($params['channels']) ? $params['channels'] : [];
        $raw_locations = isset($params['locations']) && is_array($params['locations']) ? $params['locations'] : [];
        
        // Extract search term from DataTable standard query or our custom search input
        $search_term = '';
        if (isset($params['search']['value']) && trim($params['search']['value']) !== '') {
            $search_term = trim($params['search']['value']);
        } elseif (isset($params['search_input'])) {
            $search_term = trim($params['search_input']);
        }
        
        $filters = [
            'search' => $search_term,
            'date_from' => isset($params['date_from']) ? trim($params['date_from']) : '',
            'date_to' => isset($params['date_to']) ? trim($params['date_to']) : '',
            'payment_status' => isset($params['payment_status']) ? trim($params['payment_status']) : 'All',
            'channels' => array_filter($raw_channels),
            'locations' => array_filter($raw_locations),
        ];
        
        // Parse sorting parameters
        $order_column_idx = isset($params['order'][0]['column']) ? intval($params['order'][0]['column']) : 0;
        $order_dir = isset($params['order'][0]['dir']) ? trim($params['order'][0]['dir']) : 'desc';
        
        // Map DataTables column index to SQL columns
        $columns_map = [
            0 => 's.id',
            1 => 's.created_at',
            2 => 'u.name',
            3 => 's.id', // Customer column is Walk-in, sort by ID
            4 => 's.order_type',
            5 => 's.final_amount',
            6 => 's.payment_method',
            7 => 's.status'
        ];
        
        $order_by = isset($columns_map[$order_column_idx]) ? $columns_map[$order_column_idx] : 's.id';
        
        // Fetch counts and records
        $total_records = Sale::get_total_sales_count();
        $filtered_records = Sale::get_filtered_sales_count($filters);
        $sales = Sale::get_filtered_sales($filters, $page, $limit, $order_by, $order_dir);
        
        // Prepare output array
        $data = [];
        
        foreach ($sales as $sale) {
            // Channel Badge
            $chan_badge = ($sale['order_type'] === 'Dine-in' || $sale['order_type'] === 'Take-out') ? 'POS' : 'Web';
            $chan_bg = ($chan_badge === 'POS') ? 'bg-secondary bg-opacity-10 text-secondary' : 'bg-warning bg-opacity-10 text-warning-emphasis';
            $channel_badge_html = '<span class="badge ' . $chan_bg . ' rounded-pill px-3 py-1 fw-bold" style="font-size: 10px;">' . $chan_badge . '</span>';
            
            // Payment Badge
            $pay_bg = 'bg-success bg-opacity-10 text-success';
            if ($sale['status'] === 'Pending') {
                $pay_bg = 'bg-warning bg-opacity-10 text-warning-emphasis';
            } elseif ($sale['status'] === 'Refunded' || $sale['status'] === 'Cancelled') {
                $pay_bg = 'bg-danger bg-opacity-10 text-danger';
            }
            $payment_badge_html = '<span class="badge ' . $pay_bg . ' px-3 py-1.5 fw-semibold" style="font-size: 11px;">' . htmlspecialchars($sale['payment_method']) . '</span>';
            
            // Status Badge Dot
            $status_dot_bg = 'bg-success';
            if ($sale['status'] === 'Pending') {
                $status_dot_bg = 'bg-secondary';
            } elseif ($sale['status'] === 'Refunded' || $sale['status'] === 'Cancelled') {
                $status_dot_bg = 'bg-danger';
            }
            $status_html = '<div class="d-flex align-items-center gap-2">' .
                           '<div class="rounded-circle ' . $status_dot_bg . '" style="width: 8px; height: 8px;"></div>' .
                           '<span class="small fw-medium text-muted">' . htmlspecialchars($sale['status']) . '</span>' .
                           '</div>';
                           
            // Action buttons
            $action_buttons = [];
            
            // View Receipt button
            $action_buttons[] = '<button class="btn btn-xs action-btn-view view-receipt-btn action-btn d-inline-flex align-items-center gap-1 shadow-none" data-id="' . $sale['id'] . '" data-bs-toggle="tooltip" data-bs-title="View Receipt Summary"><i class="fa-solid fa-receipt"></i> View</button>';
            
            // Refund/Cancel button based on status
            if ($sale['status'] === 'Completed') {
                $action_buttons[] = '<button class="btn btn-xs action-btn-refund refund-btn action-btn d-inline-flex align-items-center gap-1 shadow-none" data-id="' . $sale['id'] . '" data-bs-toggle="tooltip" data-bs-title="Refund Completed Transaction"><i class="fa-solid fa-undo"></i> Refund</button>';
            } elseif ($sale['status'] === 'Pending') {
                $action_buttons[] = '<button class="btn btn-xs action-btn-cancel cancel-btn action-btn d-inline-flex align-items-center justify-content-center shadow-none" style="width: 28px; height: 28px; padding: 0 !important;" data-id="' . $sale['id'] . '" data-bs-toggle="tooltip" data-bs-title="Cancel Pending Transaction"><i class="fa-solid fa-xmark" style="font-size: 12px;"></i></button>';
            }
            
            $action_html = '<div class="d-flex align-items-center justify-content-end gap-2 action-cell">' . implode(' ', $action_buttons) . '</div>';
            
            $data[] = [
                '#HH-' . str_pad($sale['id'], 6, '0', STR_PAD_LEFT),
                date('M d, Y h:i A', strtotime($sale['created_at'])),
                htmlspecialchars($sale['cashier_name'] ?? 'System'),
                'Walk-in',
                $channel_badge_html,
                '₱' . number_format($sale['final_amount'], 2),
                $payment_badge_html,
                $status_html,
                $action_html,
                // Add ID as meta parameter for row click event in DT
                'DT_RowId' => 'row_' . $sale['id'],
                'DT_RowAttr' => [
                    'data-id' => $sale['id']
                ]
            ];
        }
        
        // Return JSON response
        header('Content-Type: application/json');
        echo json_encode([
            'draw' => $draw,
            'recordsTotal' => $total_records,
            'recordsFiltered' => $filtered_records,
            'data' => $data
        ]);
        exit;
    }

    public function updateStatus() {
        require_permission('access_pos');
        
        $params = !empty($_POST) ? $_POST : $_GET;
        $id = isset($params['id']) ? intval($params['id']) : 0;
        $status = isset($params['status']) ? trim($params['status']) : '';
        
        $allowed_statuses = ['Completed', 'Pending', 'Refunded', 'Cancelled'];
        
        if ($id > 0 && in_array($status, $allowed_statuses)) {
            $success = Sale::update_status($id, $status);
            header('Content-Type: application/json');
            echo json_encode(['success' => $success]);
            exit;
        }
        
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
        exit;
    }
}
