<?php
require_once __DIR__ . '/rbac.php';
function access_page_rules() {
 return [
 'admin/monitoring'=>'view_monitoring',
 'admin/online_orders'=>'manage_online_orders',
 'dashboard'=>'view_dashboard', 'admin/users'=>'manage_users','admin/users/data'=>'manage_users','admin/users/single'=>'manage_users',
 'admin/roles'=>'manage_roles','admin/roles/data'=>['manage_roles','manage_users'],'admin/permissions/data'=>'manage_roles',
 'admin/expenses'=>'manage_expenses','admin/expenses/data'=>'manage_expenses','admin/expenses/print'=>'manage_expenses','admin/expenses/summary_print'=>'manage_expenses',
 'admin/cms'=>'access_website_admin','admin/reports'=>'view_reports','admin/reports/print'=>'view_reports',
 'admin/branches'=>'manage_branches','admin/branches/report'=>'manage_branches','admin/settings'=>'manage_settings',
 'admin/activity_logs'=>'view_activity_logs','admin/activity_logs/data'=>'view_activity_logs','admin/activity_logs/print'=>'view_activity_logs',
 'pos'=>'access_pos','pos/history'=>'access_pos','pos/history/data'=>'access_pos','pos/history/stats'=>'access_pos','pos/receipt'=>'access_pos','pos/history/update_status'=>'refund_sales',
 'inventory/transfers'=>['transfer_products','approve_stock_transfers','receive_stock_transfers','view_stock_transfers'],
 'pos/shifts'=>['manage_cashier_schedules','view_own_shifts','open_cash_shift','close_cash_shift','cash_drawer_movements','view_cash_shifts','manage_cash_shifts','approve_cash_shifts','manage_registers','view_shift_reports'],
 'inventory/suppliers'=>['view_suppliers','manage_suppliers'],
 'inventory/purchases'=>['view_purchase_orders','create_purchase_orders','approve_purchase_orders','receive_purchases','return_purchases'],
 'inventory/purchase_reports'=>'view_purchase_reports',
 'inventory/products'=>'view_product','inventory/recipes'=>'view_product','inventory/categories'=>'edit_product','inventory/logs'=>'manage_inventory','inventory/logs/print'=>'manage_inventory','inventory/low_stock'=>'manage_inventory'
 ];
}
function access_ajax_rules() {
 return [
 'inventory_action'=>[
  'stock_transfer_create'=>'transfer_products','stock_transfer_dispatch'=>'transfer_products','stock_transfer_cancel'=>'transfer_products',
  'stock_transfer_approve'=>'approve_stock_transfers','stock_transfer_reject'=>'approve_stock_transfers','stock_transfer_receive'=>'receive_stock_transfers',
  'transfer_product_branch'=>'reassign_products',
  'get_category_products'=>'view_product','quick_restock'=>'manage_inventory','adjust_stock'=>'manage_inventory',
  'get_next_batch'=>['add_product','edit_product','manage_inventory'],'get_next_product_id'=>'add_product',
  'get_product_modifiers'=>['view_product','access_pos'],'save_product_modifiers'=>'edit_product',
  'check_recipe_availability'=>['view_product','access_pos'],'products_datatable'=>'view_product',
  'get_categories'=>['view_product','add_product','edit_product'],'get_active_batches'=>'view_product','get_branches'=>['view_product','manage_branches'],
  'get_batch_breakdown'=>'view_product','update_batch_data'=>'edit_stock_batches','get_product_recipe'=>'view_product',
  'save_product_recipe'=>'edit_product','prepare_recipe_batch'=>'prepare_recipe_stock','reverse_sale_stock'=>'manage_inventory','record_waste'=>'manage_inventory',
  'get_ingredients_list'=>['add_product','edit_product'],'logs_datatable'=>'view_stock_history',
  'get_attributes'=>['view_product','edit_product','add_product'],'save_attribute'=>'edit_product','get_product_variants'=>['view_product','access_pos'],
  'save_product_variants'=>'edit_product','get_product_attribute_values'=>'view_product','save_product_attributes'=>'edit_product',
  'sales_dt'=>'manage_inventory','logs_dt'=>'manage_inventory','expenses_dt'=>'manage_inventory'],
 'branch_action'=>array_fill_keys(['list','get','overview','validate_delete','get_managers','get_staff','create','update','delete'],'manage_branches'),
 'expense_action'=>array_fill_keys(['single','create','update','delete'],'manage_expenses'),
 'role_action'=>array_fill_keys(['get_permissions','add_permission','get_modules','update_permission','delete_permission'],'manage_roles'),
 'cms_action'=>['mark_read'=>'access_website_admin'],
 'report_action'=>array_fill_keys(['get_dt_sales','get_dt_inventory_logs','get_summary_stats'],'view_reports'),
 'pos_cart'=>array_fill_keys(['load','set_order_type','add','update_qty','remove','clear','set_discount','get_receipt','checkout'],'access_pos'),
 'notification_action'=>array_fill_keys(['get_alerts','mark_read','mark_all_read'],['view_product','manage_inventory']),
 'pos_drawer'=>array_fill_keys(['status','transactions','open','close','lock','unlock','move'],'access_pos'),
 'cash_shift_audit'=>['get'=>['manage_cashier_schedules','view_own_shifts','open_cash_shift','close_cash_shift','cash_drawer_movements','view_cash_shifts','manage_cash_shifts','approve_cash_shifts','manage_registers','view_shift_reports']],
 'pos_pin'=>['verify'=>'access_pos']
 ];
}
function authorize_page($route) {
 if(!is_string($route)) access_denied('Invalid route.',400);
 if ($route === 'admin/start') {
  require_login();
  $context = User::access_context($_SESSION['user']['id']);
  if (!$context['staff']) access_denied();
  foreach (['role','branch','branch_id'] as $key) $_SESSION['user'][$key] = $context[$key];
  return;
 }
 $rules=access_page_rules();
 if(isset($rules[$route])) {
  require_permissions((array)$rules[$route]);
  enforce_branch_context();
  if (strpos($route, 'admin/branches') === 0 && !User::has_all_branch_access($_SESSION['user']['id'])) access_denied('Only Admin and Superadmin can manage branches.');
  if($_SERVER['REQUEST_METHOD']==='POST') enforce_csrf();
  if($route==='pos/history/update_status') require_write_request();
 } elseif(preg_match('~^(admin/|inventory/|pos/)~',$route) && strpos($route,'ajax/')!==0) {
  access_denied('This page is not available.',403);
 }
}
function authorize_action($endpoint,$action) {
 require_once __DIR__.'/session.php'; enforce_session_timeout();
 if(!is_string($action)) access_denied('Invalid action.',400);
 $rules=access_ajax_rules();
 if(!isset($rules[$endpoint][$action])) access_denied('Unknown action.',400);
 require_permissions((array)$rules[$endpoint][$action]);
 enforce_branch_context();
 if ($endpoint === 'branch_action' && !User::has_all_branch_access($_SESSION['user']['id'])) access_denied('Only Admin and Superadmin can manage branches.');
 if ($endpoint === 'expense_action' && in_array($action, ['single','update','delete'], true) && !User::has_all_branch_access($_SESSION['user']['id'])) {
  $expense = Expense::get_by_id((int)($_POST['id'] ?? $_GET['id'] ?? 0));
  if (!$expense || (int)$expense['branch_id'] !== (int)$_SESSION['user']['branch_id']) access_denied('This expense belongs to another branch.');
 }
 $reads=['list','get','overview','validate_delete','validate_delete','get_managers','get_staff','single','get_permissions','get_modules','get_alerts','get_receipt','load'];
 $read = in_array($action,$reads,true) || strpos($action,'get_')===0 || in_array($action,['products_datatable','logs_datatable','sales_dt','logs_dt','expenses_dt','get_category_products','check_recipe_availability'],true);
 if(($endpoint==='pos_cart' && !$read) || ($endpoint==='pos_drawer' && !in_array($action,['lock','close','unlock','status'],true))) {
  require_once __DIR__.'/../models/Setting.php';
  if(Setting::get('require_pos_pin','1')!=='0' && empty($_SESSION['pos_pin_verified'])) access_denied('Unlock the POS with your PIN before making sales.');
  require_once __DIR__.'/../models/CashierSchedule.php';
  try { CashierSchedule::assertAccess((int)$_SESSION['user']['id'],(int)($_SESSION['pos_branch_id'] ?? 0)); }
  catch(DomainException $e){access_denied($e->getMessage());}
 }
 if(!$read || $_SERVER['REQUEST_METHOD']==='POST') {
  if(!$read) require_write_request();
  enforce_csrf();
 }
}
function require_write_request() {
 if($_SERVER['REQUEST_METHOD']!=='POST') access_denied('Use POST for this action.',405);
 enforce_csrf();
}

// Refresh assignments from the database; never trust login fields or stale session roles.
function enforce_branch_context() {
 $context = User::access_context($_SESSION['user']['id']);
 foreach (['role','branch','branch_id'] as $key) $_SESSION['user'][$key] = $context[$key];
 if ($context['global']) return;
 if (!$context['staff']) access_denied();
 if (!$context['branch_id']) access_denied('Your account needs a branch. Ask an administrator to assign one before continuing.');
 foreach (['_GET','_POST','_REQUEST'] as $source) {
  if (!empty($GLOBALS[$source]['branch_id']) && $GLOBALS[$source]['branch_id'] !== 'all' && (int)$GLOBALS[$source]['branch_id'] !== $context['branch_id']) access_denied('You can only access your assigned branch.');
  $GLOBALS[$source]['branch_id'] = $context['branch_id'];
 }
 $_SESSION['pos_branch_id'] = $context['branch_id'];
}
