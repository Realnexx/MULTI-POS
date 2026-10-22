<?php
// Creates and removes ONLY a unique pos_burger_test_* database. Never migrates the source.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (($argv[1] ?? '') === '--endpoint') {
    ini_set('session.save_path', $argv[2]);
    session_id('burger-endpoint-test');
    session_start();
    $_SESSION['user']=['id'=>1,'branch_id'=>1,'name'=>'Test cashier','email'=>'test@invalid.local'];
    $_SESSION['csrf_token']='test-csrf';
    $_SESSION['auth']=['started_at'=>time(),'last_activity'=>time(),'version'=>1];
    $_SESSION['pos_pin_verified']=true;
    $_POST=json_decode(base64_decode($argv[4]),true);
    $_SERVER['REQUEST_METHOD']='POST';
    $_SERVER['HTTP_X_REQUESTED_WITH']='XMLHttpRequest';
    if($argv[3]==='demo_guard'){$_SESSION['user']['id']=2;$_SERVER['SCRIPT_NAME']='/pos/ajax/test.php';require $argv[2].'/middleware/rbac.php';require_permission('access_pos');echo json_encode(['success'=>true]);exit;}
    if($argv[3]==='auth_factor') {
        unset($_SESSION['user'],$_SESSION['pending_mfa']);
        if(isset($_POST['_pending']))$_SESSION['pending_mfa']=$_POST['_pending'];
        $_SERVER['SCRIPT_NAME']='/pos/index.php';$_SERVER['HTTP_HOST']='localhost';
        if(isset($_POST['_get']))$_SERVER['REQUEST_METHOD']='GET';
        register_shutdown_function(function(){echo "\nAUTH_STATE:".json_encode(['authenticated'=>isset($_SESSION['user']),'pending'=>$_SESSION['pending_mfa']??null,'demo_notice'=>$_SESSION['demo_notice_pending']??null]);});
        require $argv[2].'/controllers/AuthController.php';(new AuthController())->login();exit;
    }
    if(in_array($argv[3],['pos_render','pos_fresh_render','pos_after_pin_render','pos_internal_render'],true)) {
        $_GET=['route'=>'pos']; $_SERVER['REQUEST_METHOD']='GET'; $_SESSION['pos_branch_id']=1;
        $_SERVER['SCRIPT_NAME']='/pos/index.php';$_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/pos/?route=pos';
        if($argv[3]==='pos_render')$_SESSION['pos_entry_grant']=time();
        if($argv[3]==='pos_fresh_render')unset($_SESSION['pos_entry_grant']);
        if($argv[3]==='pos_internal_render')$_COOKIE['pos_navigation']=$_SESSION['pos_navigation'] ?? '';
        require $argv[2].'/controllers/PosController.php';require_once $argv[2].'/middleware/access_policy.php';authorize_page('pos');(new PosController())->index();
    } elseif($argv[3]==='cash_shift_audit') {
        $_GET=$_POST;$_POST=[];$_SERVER['REQUEST_METHOD']='GET';$_SERVER['SCRIPT_NAME']='/pos/ajax/cash_shift_audit.php';require $argv[2].'/ajax/cash_shift_audit.php';
    } elseif(in_array($argv[3],['online_orders_render','online_orders_post'],true)) {
        $_GET=['route'=>'admin/online_orders'];if(isset($_POST['_tracking']))$_GET['tracking']=$_POST['_tracking'];
        $_SERVER['SCRIPT_NAME']='/pos/index.php';$_SERVER['HTTP_HOST']='localhost';$_SERVER['REQUEST_URI']='/pos/?route=admin/online_orders';
        if($argv[3]==='online_orders_render')$_SERVER['REQUEST_METHOD']='GET';
        require $argv[2].'/controllers/OnlineOrderController.php';require_once $argv[2].'/middleware/access_policy.php';authorize_page('admin/online_orders');(new OnlineOrderController())->index();
    } elseif(in_array($argv[3],['shift_post','shift_render'],true)) {
        $_GET=['route'=>'pos/shifts'];
        foreach (['branch_id','from','to','export','schedule_from','schedule_to','schedule_cashier','schedule_status','schedule_view'] as $field) if (isset($_POST['_'.$field])) $_GET[$field]=$_POST['_'.$field];
        if (isset($_POST['_shift_id'])) $_SESSION['cash_shift_selected'][1]=(int)$_POST['_shift_id'];
        $_SERVER['SCRIPT_NAME']='/pos/index.php'; $_SERVER['HTTP_HOST']='localhost'; $_SERVER['REQUEST_URI']='/pos/?route=pos/shifts';
        if ($argv[3]==='shift_render') $_SERVER['REQUEST_METHOD']='GET'; else $_SERVER['HTTP_X_MODAL_FORM']='1';
        require $argv[2].'/controllers/CashShiftController.php'; require_once $argv[2].'/middleware/access_policy.php';
        authorize_page('pos/shifts'); (new CashShiftController())->page();
    } elseif(in_array($argv[3],['purchasing_post','purchasing_render','purchasing_legacy'],true)) {
        $_GET=['route'=>$_POST['_route'] ?? 'inventory/purchases'];
        foreach (['id','branch_id','supplier_id','from','to','export'] as $field) if (isset($_POST['_'.$field])) $_GET[$field]=$_POST['_'.$field];
        $_SERVER['SCRIPT_NAME']='/pos/index.php'; $_SERVER['HTTP_HOST']='localhost'; $_SERVER['REQUEST_URI']='/pos/?route='.$_GET['route'];
        if ($argv[3]==='purchasing_render') {
            $_SERVER['REQUEST_METHOD']='GET';
            if (isset($_GET['id'])) { $_SESSION['purchase_selected'][1]=(int)$_GET['id']; unset($_GET['id']); }
        }
        else {
            $_SERVER['HTTP_X_MODAL_FORM']='1';
        }
        require $argv[2].'/controllers/PurchasingController.php';
        require_once $argv[2].'/middleware/access_policy.php';
        authorize_page($_GET['route']);
        (new PurchasingController())->page($_GET['route']);
    } elseif($argv[3]==='promotion_render') {
        $_SESSION['user']=['id'=>(int)($_POST['_user']??2),'branch_id'=>1,'name'=>'Promo Test User','email'=>'promo-test@invalid.local'];$_GET=['route'=>'admin/promotions'];$_SERVER['REQUEST_METHOD']='GET';$_SERVER['HTTP_HOST']='localhost';$_SERVER['SCRIPT_NAME']='/pos/index.php';
        require $argv[2].'/controllers/PromotionController.php';require_once $argv[2].'/middleware/access_policy.php';authorize_page('admin/promotions');(new PromotionController())->index();
    } elseif($argv[3]==='render_paymongo_settings') {
        $_SERVER['REQUEST_METHOD']='GET';$_GET=['route'=>'admin/settings','tab'=>$_POST['_tab']??'paymongo'];$_SESSION['auth']['mfa_verified']=true;$_SESSION['user']=['id'=>2,'name'=>'Gateway test admin','branch_id'=>1];
        $_SERVER['SCRIPT_NAME']='/pos/index.php';$_SERVER['HTTP_HOST']='localhost';
        require $argv[2].'/controllers/AdminController.php';(new AdminController())->settings();
    } elseif($argv[3]==='render_paymongo_tracking') {
        $_SERVER['REQUEST_METHOD']='GET';$_GET=['route'=>'order_tracking','tracking'=>$_POST['_tracking']];
        $_SERVER['SCRIPT_NAME']='/pos/index.php';$_SERVER['HTTP_HOST']='localhost';
        require $argv[2].'/controllers/WebsiteController.php';(new WebsiteController())->order_tracking();
    } elseif($argv[3]==='render_dashboard') {
        $_SERVER['REQUEST_METHOD']='GET';$_GET=['route'=>'dashboard','branch'=>'2'];
        $_SESSION['user']['branch_id']=2;$_SESSION['dashboard_branch']='all';
        require $argv[2].'/controllers/AdminController.php';(new AdminController())->dashboard();
    } elseif (in_array($argv[3], ['render_products','render_products_admin','render_recipes','render_recipes_admin','render_transfers','render_transfers_receiver','render_transfers_admin'], true)) {
        if (strpos($argv[3],'admin')!==false) $_SESSION['user']['id']=2;
        if ($argv[3]==='render_transfers_receiver') {$_SESSION['user']['id']=3;$_SESSION['user']['branch_id']=2;}
        $_SERVER['REQUEST_METHOD']='GET';
        $_SERVER['REQUEST_URI']='/pos/?route=inventory/products';
        $_SERVER['SCRIPT_NAME']='/pos/index.php';
        $_SERVER['HTTP_HOST']='localhost';
        $_GET=['route'=>strpos($argv[3],'render_products')===0 ? 'inventory/products' : 'inventory/recipes'];
        if(strpos($argv[3],'render_transfers')===0) $_GET=['route'=>'inventory/transfers','id'=>$_POST['transfer_id'] ?? null];
        require $argv[2] . '/controllers/InventoryController.php';
        if(strpos($argv[3],'render_transfers')===0) (new InventoryController())->transfers();
        elseif (strpos($argv[3],'render_products')!==0) (new InventoryController())->recipes();
        else (new InventoryController())->products();
    } elseif (in_array($argv[3], ['products','products_denied','products_export_denied','recipes_save'], true)) {
        if ($argv[3] === 'products_export_denied') $_GET['export']='csv';
        if (!in_array($argv[3],['products_denied','products_export_denied'],true)) register_shutdown_function(function () { echo json_encode(['flash'=>$_SESSION['flash'] ?? null]); });
        require $argv[2] . '/controllers/InventoryController.php';
        if ($argv[3] === 'recipes_save') (new InventoryController())->recipes();
        else (new InventoryController())->products();
    } elseif (in_array($argv[3],['inventory_action_admin','inventory_action_receiver'],true)) {
        $_SESSION['user']['id']=$argv[3]==='inventory_action_admin' ? 2 : 3;
        $_SESSION['user']['branch_id']=$argv[3]==='inventory_action_admin' ? 1 : 2;
        require $argv[2].'/ajax/inventory_action.php';
    } else require $argv[2] . '/ajax/' . $argv[3] . '.php';
    exit;
}
if (($argv[1] ?? '') === '--shift-worker') {
    ini_set('session.save_path',$argv[2]); require $argv[2].'/models/Product.php'; require $argv[2].'/models/CashShift.php';
    $_SESSION['user']=['id'=>1,'branch_id'=>1]; $p=json_decode(base64_decode($argv[4]),true);
    try {
        if ($argv[3]==='open') CashShift::open($p['register'],1,'10',$p['key']);
        elseif ($argv[3]==='out') CashShift::move($p['shift'],'cash_out','out','7','Concurrent withdrawal',$p['key']);
        elseif ($argv[3]==='close') CashShift::close($p['shift'],'100','Concurrent closing count',$p['key']);
        elseif ($argv[3]==='sale') RecipeInventory::checkout(1,1,[['product_id'=>$p['product'],'quantity'=>1]],'0','10','Cash','Dine-in',$p['key']);
        echo 'SUCCESS';
    } catch (Throwable $e) { echo 'REJECTED: '.$e->getMessage(); }
    exit;
}
if (($argv[1] ?? '') === '--purchase-worker') {
    ini_set('session.save_path',$argv[2]); require $argv[2].'/models/Purchasing.php';
    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    try { Purchasing::receive((int)$argv[3],$argv[5],date('Y-m-d'),json_decode(base64_decode($argv[4]),true),'Concurrent delivery',$argv[6]); echo 'SUCCESS'; }
    catch (Throwable $e) { echo 'REJECTED: '.$e->getMessage(); }
    exit;
}
if (($argv[1] ?? '') === '--promo-worker') {
    ini_set('session.save_path',$argv[2]);require $argv[2].'/models/Promotion.php';
    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    try{RecipeInventory::checkout(1,1,[['product_id'=>(int)$argv[3],'quantity'=>1]],'0','100','Cash','Dine-in',$argv[4],false,['code'=>'RACE']);echo 'PROMO_SUCCESS';}
    catch(DomainException $e){echo 'PROMO_REJECTED';}exit;
}
if (($argv[1] ?? '') === '--worker') {
    ini_set('session.save_path', $argv[2]);
    require $argv[2] . '/models/Product.php';
    $_SESSION['user'] = ['id'=>1,'branch_id'=>1];
    try {
        RecipeInventory::checkout(1,1,[['product_id'=>(int)$argv[3],'quantity'=>1]],0,1000,'Cash','Dine-in',$argv[4]);
        echo 'SUCCESS';
    } catch (Throwable $e) { echo 'REJECTED'; }
    exit;
}
$root = dirname(__DIR__,2);
$config = require $root . '/config/config.local.php';
$database = 'pos_burger_test_' . bin2hex(random_bytes(6));
$admin = new mysqli($config['database']['host'],$config['database']['user'],$config['database']['password']);
$workspace = $root . '/scratch/' . $database;
$source = $config['database']['name'];
if (!preg_match('/^[A-Za-z0-9_]+$/D',$source)) exit('Unsafe source database identifier.');
function check($condition,$label) {
    global $checks;
    if (!$condition) throw new RuntimeException('FAIL: ' . $label);
    $checks=($checks ?? 0)+1;
    echo 'PASS: ' . $label . PHP_EOL;
}
function reject(callable $work,$label) {
    try { $work(); } catch (DomainException $e) { check(true,$label); return; }
    catch (InvalidArgumentException $e) { check(true,$label); return; }
    throw new RuntimeException('FAIL: expected rejection: ' . $label);
}
try {
    $admin->query('CREATE DATABASE `' . $database . '`');
    $admin->select_db($database);
    $admin->query('SET FOREIGN_KEY_CHECKS=0');
    foreach ($admin->query('SHOW TABLES FROM `' . $source . '`') as $table) {
        $name = array_values($table)[0];
        if (!preg_match('/^[A-Za-z0-9_]+$/D',$name)) throw new RuntimeException('Unsafe table identifier.');
        $ddl=$admin->query('SHOW CREATE TABLE `' . $source . '`.`' . $name . '`')->fetch_array()[1];
        $admin->query($ddl);
    }
    $admin->query('SET FOREIGN_KEY_CHECKS=1');
    if (!$admin->query("SHOW COLUMNS FROM products LIKE 'recipe_mode'")->num_rows) {
        $admin->multi_query(file_get_contents($root . '/tools/migrations/20261015-burger-inventory.sql'));
        do { if ($r=$admin->store_result()) $r->free(); } while ($admin->more_results() && $admin->next_result());
    }
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-paymongo.sql'));
    do {if($r=$admin->store_result())$r->free();}while($admin->more_results()&&$admin->next_result());
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-stock-transfers.sql'));
    do { if($migrationResult=$admin->store_result()) $migrationResult->free(); } while($admin->more_results() && $admin->next_result());
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-inventory-controls.sql'));
    do { if($migrationResult=$admin->store_result()) $migrationResult->free(); } while($admin->more_results() && $admin->next_result());
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-purchasing.sql'));
    do { if($migrationResult=$admin->store_result()) $migrationResult->free(); } while($admin->more_results() && $admin->next_result());
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-cash-shifts.sql'));
    do { if($migrationResult=$admin->store_result()) $migrationResult->free(); } while($admin->more_results() && $admin->next_result());
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-pos-drawer.sql'));
    do { if($migrationResult=$admin->store_result()) $migrationResult->free(); } while($admin->more_results() && $admin->next_result());
    mkdir($workspace . '/tools/migrations',0755,true);
    foreach(glob($root.'/tools/migrations/*.sql') as $migration)copy($migration,$workspace.'/tools/migrations/'.basename($migration));
    mkdir($workspace . '/models',0755,true); mkdir($workspace . '/config',0755,true);
    mkdir($workspace . '/assets/js',0755,true);
    mkdir($workspace . '/assets/css',0755,true);
    copy($root.'/assets/css/inventory-controls.css',$workspace.'/assets/css/inventory-controls.css');
    copy($root.'/assets/css/purchasing.css',$workspace.'/assets/css/purchasing.css');
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-cashier-schedules.sql'));
    do { if($r=$admin->store_result())$r->free(); } while($admin->more_results() && $admin->next_result());
    $admin->multi_query(file_get_contents($root.'/tools/migrations/20261016-cashier-schedule-planning.sql'));
    do { if($r=$admin->store_result())$r->free(); } while($admin->more_results() && $admin->next_result());
    copy($root.'/assets/css/cash-shifts.css',$workspace.'/assets/css/cash-shifts.css');
    copy($root.'/assets/css/pos-drawer.css',$workspace.'/assets/css/pos-drawer.css');
    foreach (glob($root . '/assets/css/*.css') as $path) copy($path,$workspace . '/assets/css/' . basename($path));
    foreach (glob($root . '/assets/js/*.js') as $path) copy($path,$workspace . '/assets/js/' . basename($path));
    foreach (glob($root . '/models/*.php') as $path) copy($path,$workspace . '/models/' . basename($path));
    mkdir($workspace . '/ajax',0755,true);
    foreach (['pos_cart.php','pos_pin.php','inventory_action.php','pos_drawer.php','cash_shift_audit.php'] as $name) copy($root . '/ajax/' . $name,$workspace . '/ajax/' . $name);
    mkdir($workspace . '/controllers',0755,true);
    copy($root . '/controllers/InventoryController.php',$workspace . '/controllers/InventoryController.php');
    copy($root . '/controllers/WebsiteController.php',$workspace . '/controllers/WebsiteController.php');
    copy($root . '/controllers/AdminController.php',$workspace . '/controllers/AdminController.php');
    copy($root.'/controllers/AuthController.php',$workspace.'/controllers/AuthController.php');
    copy($root . '/controllers/PurchasingController.php',$workspace . '/controllers/PurchasingController.php');
    copy($root . '/controllers/CashShiftController.php',$workspace . '/controllers/CashShiftController.php');
    copy($root . '/controllers/PromotionController.php',$workspace . '/controllers/PromotionController.php');
    copy($root . '/assets/css/promotions.css',$workspace . '/assets/css/promotions.css');
    copy($root . '/controllers/PosController.php',$workspace . '/controllers/PosController.php');
    copy($root . '/controllers/OnlineOrderController.php',$workspace . '/controllers/OnlineOrderController.php');
    mkdir($workspace . '/middleware',0755,true);
    foreach (glob($root . '/middleware/*.php') as $path) copy($path,$workspace . '/middleware/' . basename($path));
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/views',FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile() && $file->getExtension()==='php') {
            $destination=$workspace . substr($file->getPathname(),strlen($root));
            if (!is_dir(dirname($destination))) mkdir(dirname($destination),0755,true);
            copy($file->getPathname(),$destination);
        }
    }
    // Versioned assets must exist in the isolated rendered-page workspace.
    foreach (['assets/admin/js/sidebarmenu.js','assets/js/demo-requests-admin.js','assets/css/demo-requests-admin.css','assets/css/admin-sidebar.css','assets/css/branch-management.css','assets/js/branch-manager-picker.js','assets/js/branch-overview.js'] as $asset) {
        if(!is_dir(dirname($workspace.'/'.$asset)))mkdir(dirname($workspace.'/'.$asset),0755,true);
        copy($root.'/'.$asset,$workspace.'/'.$asset);
    }
    foreach (['db.php','app.php','uploads.php','auth.php','permissions.php'] as $name) copy($root . '/config/' . $name,$workspace . '/config/' . $name);
    $config['database']['name']=$database;
    file_put_contents($workspace . '/config/config.local.php','<?php return ' . var_export($config,true) . ';');
    ini_set('session.save_path', $workspace);
    require $workspace . '/models/Product.php';
    require_once $workspace.'/models/SystemPolicy.php';SystemPolicy::migrate($conn);
    require_once $workspace.'/models/CheckoutPolicy.php';CheckoutPolicy::migrate($conn);
    require_once $workspace.'/models/ErrorMonitor.php';ErrorMonitor::migrate($conn);
    require_once $workspace.'/models/DemoAccount.php';DemoAccount::migrate($conn);

    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    require $workspace . '/models/Sale.php';
    require_once $workspace.'/models/Setting.php'; Setting::set('pos_drawer_integration','0','security'); Setting::set('cashier_schedule_enforcement','0','security');
    $conn->query("INSERT INTO branches (id,name) VALUES (1,'Test branch'),(2,'Other branch')");
    $conn->query("INSERT INTO users (id,name,email,password,branch_id) VALUES (1,'Test cashier','test@invalid.local','not-a-login',1)");
    $_SESSION['auth']=['started_at'=>time(),'last_activity'=>time(),'version'=>1];
    $conn->query("INSERT INTO categories (id,name) VALUES (1,'Test')");
    $conn->query("INSERT INTO roles (id,name) VALUES (1,'Test cashier')");
    $conn->query("INSERT INTO user_roles (user_id,role_id) VALUES (1,1)");
    foreach (['view_dashboard','access_pos','manage_inventory','view_product','edit_product','add_product','delete_product','print_products','view_stock_history','edit_stock_batches','prepare_recipe_stock','reassign_products','view_recipes','create_recipes','edit_recipes','manage_global_recipes'] as $permission) {
        RecipeInventory::query('INSERT INTO permissions (name,description,module) VALUES (?,?,?)','sss',[$permission,'Test only','Test']);
        $permissionId=$conn->insert_id;
        RecipeInventory::query('INSERT INTO role_permissions (role_id,permission_id) VALUES (1,?)','i',[$permissionId]);
    }
    function product($name,$type='ingredient',$unit='pc',$mode='legacy',$publish=true) {
        $id=Product::create(1,$name,uniqid(),uniqid(),'',100,1,'0','0','', 'active',0,0,'100000',1,$type,1,$type==='ingredient'?0:1,$type==='ingredient'?1:0,'stocked',null,$unit,1);
        RecipeInventory::query('UPDATE products SET recipe_mode = ? WHERE id = ?','si',[$mode,$id]);
        if($publish)RecipeInventory::query('UPDATE products SET show_on_website=1 WHERE id=?','i',[$id]);
        return $id;
    }
    if(($argv[1]??'')!=='--promotions-only'){
        require_once $workspace.'/models/Promotion.php';Promotion::migrate($conn);
        RecipeInventory::query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT 1,id FROM permissions WHERE name='approve_discount_overrides'");
    }
    if(($argv[1]??'')==='--bundles-only'){require $root.'/tools/tests/bundle-cases.php';echo "All {$checks} bundle checks passed. Source business data was not modified.\n";return;}
    if(($argv[1]??'')==='--stock-review-only'){require $root.'/tools/tests/stock-review-cases.php';echo "All {$checks} stock review checks passed. Source business data was not modified.\n";return;}
    if(($argv[1]??'')==='--promotions-only'){require $root.'/tools/tests/promotion-cases.php';echo "All {$checks} promotion checks passed. Source business data was not modified.\n";return;}
    if(($argv[1]??'')==='--website-permissions-only'){require $root.'/tools/tests/website-permission-cases.php';echo "All {$checks} website permission checks passed.\n";return;}
    if(($argv[1]??'')==='--pricing-only'){require $root.'/tools/tests/pricing-cases.php';echo "All {$checks} pricing checks passed.\n";return;}
    if(($argv[1]??'')==='--demo-requests-only'){require $root.'/tools/tests/demo-request-cases.php';echo "All {$checks} demo request checks passed. Source data was not modified.\n";return;}
    if(($argv[1]??'')==='--catalog-only'){require $root.'/tools/tests/public-catalog-cases.php';echo "All {$checks} public catalog checks passed.\n";return;}
    if(($argv[1]??'')==='--demo-only'){require $root.'/tools/tests/demo-account-cases.php';echo "All {$checks} demo account checks passed. Source data was not modified.\n";return;}
    if(($argv[1]??'')==='--settings-only'){require $root.'/tools/tests/system-settings-cases.php';echo "All {$checks} system settings checks passed. Source data was not modified.\n";return;}
    if(($argv[1]??'')==='--paymongo-only'){
        $conn->query("INSERT INTO users(id,name,email,password,branch_id) VALUES(2,'Gateway test admin','gateway-admin@invalid.local','not-a-login',1)");
        $conn->query("INSERT INTO roles(id,name) VALUES(2,'Admin')");$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES(2,2)');
        $csProduct=product('Gateway fixture product','simple');
        require $root.'/tools/tests/paymongo-cases.php';
        echo "All {$checks} PayMongo checks passed. No requests were sent to PayMongo; source data was not modified.\n";
        return;
    }
    $bun=product('Bun'); $patty=product('Patty'); $cheese=product('Cheese'); $lettuce=product('Lettuce','ingredient','g');
    $sauce=product('Sauce','ingredient','ml'); $wrap=product('Wrapper');
    foreach ([$bun,$patty,$cheese,$wrap] as $id) Product::adjust_stock($id,1,'stock_in','100','Test',null,null,1);
    Product::adjust_stock($lettuce,1,'stock_in','1000.5','Test',null,null,1);
    Product::adjust_stock($sauce,1,'stock_in','2000.5','Test',null,null,1);
    $burger=product('Burger','recipe','pc','made_to_order');
    $rows=[];
    foreach ([[$bun,'1','pc'],[$patty,'1','pc'],[$cheese,'1','pc'],[$lettuce,'0.015','kg'],[$sauce,'20','ml'],[$wrap,'1','pc']] as $r) {
        $rows[]=['ingredient_id'=>$r[0],'quantity'=>$r[1],'unit'=>$r[2]];
    }
    Product::save_recipes($burger,$rows);
    check(RecipeInventory::balance($bun,1)===1000000,'recipe save leaves ingredients unchanged');
    $key=bin2hex(random_bytes(16)); $items=[['product_id'=>$burger,'quantity'=>2,'price'=>0.01]];
    $sale=RecipeInventory::checkout(1,1,$items,0,1000,'Cash','Dine-in',$key);
    check(RecipeInventory::balance($bun,1)===980000 && RecipeInventory::balance($patty,1)===980000 && RecipeInventory::balance($cheese,1)===980000 && RecipeInventory::balance($wrap,1)===980000,'two burgers consume counted ingredients once');
    check(RecipeInventory::balance($lettuce,1)===9705000 && RecipeInventory::balance($sauce,1)===19605000,'weight/volume conversion and fractional opening stock preserved');
    check((float)RecipeInventory::rows('SELECT final_amount FROM sales WHERE id=?','i',[$sale])[0]['final_amount']===200.0,'server ignores forged client price');
    check(RecipeInventory::checkout(1,1,$items,0,1000,'Cash','Dine-in',$key)===$sale && RecipeInventory::balance($bun,1)===980000,'retry returns same sale without consuming twice');
    reject(function() use ($burger,$bun) { Product::save_recipes($burger,[['ingredient_id'=>$bun,'quantity'=>1,'unit'=>'pc'],['ingredient_id'=>$bun,'quantity'=>2,'unit'=>'pc']]); },'duplicate ingredients rejected');
    reject(function() use ($burger) { Product::save_recipes($burger,[['ingredient_id'=>$burger,'quantity'=>1,'unit'=>'pc']]); },'self-reference rejected');
    reject(function() use ($burger,$bun) { Product::save_recipes($burger,[['ingredient_id'=>$bun,'quantity'=>'0.5','unit'=>'pc']]); },'fractional counted ingredients rejected');
    check(count(Product::get_recipes($burger))===6,'failed recipe edits preserve prior recipe');
    $batchBurger=product('Prepared burger','recipe','pc','prepared_batch'); Product::save_recipes($batchBurger,$rows);
    $prepKey=bin2hex(random_bytes(16)); $prep=RecipeInventory::prepare($batchBurger,10,1,1,$prepKey);
    check(RecipeInventory::balance($bun,1)===880000 && RecipeInventory::balance($batchBurger,1)===100000,'prepare ten consumes ingredients and creates finished stock');
    check(RecipeInventory::prepare($batchBurger,10,1,1,$prepKey)===$prep,'production retry is idempotent');
    $batchSale=RecipeInventory::checkout(1,1,[['product_id'=>$batchBurger,'quantity'=>2]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($batchBurger,1)===80000 && RecipeInventory::balance($bun,1)===880000,'selling prepared stock does not consume base ingredients again');
    Product::save_modifiers($burger,[['modifier_name'=>'Extra cheese','additional_price'=>'10','ingredient_product_id'=>$cheese,'ingredient_quantity'=>'2','ingredient_unit'=>'pc']]);
    $mod=Product::get_modifiers($burger)[0];
    RecipeInventory::checkout(1,1,[['product_id'=>$burger,'quantity'=>1,'modifiers'=>[['id'=>$mod['id'],'additional_price'=>'0']]]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($cheese,1)===850000,'modifier quantity added to base recipe consumption');
    $simple=product('Simple packaged drink','simple'); Product::adjust_stock($simple,1,'stock_in','5','Test',null,null,1);
    $simpleSale=RecipeInventory::checkout(1,1,[['product_id'=>$simple,'quantity'=>2]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($simple,1)===30000,'ordinary stocked products retain FIFO sales');
    Sale::update_status($simpleSale,'Refunded');
    check(RecipeInventory::balance($simple,1)===30000,'status-only refund does not invent usable stock');
    check(!Sale::update_status($simpleSale,'Completed'),'refunded sale cannot be reopened through status edits');
    $variantParent=product('Variant catalog','variant');
    try {
        RecipeInventory::atomic(function() use ($variantParent) {
            Product::save_variants($variantParent,[['variant_name'=>'Large','price'=>150,'stock'=>0]]);
            throw new DomainException('Test outer rollback');
        });
    } catch (DomainException $expected) {}
    check(count(Product::get_variants($variantParent))===0,'variant helper cannot commit an outer transaction');
    $expired=product('Expired item','simple');
    Product::adjust_stock($expired,1,'stock_in','10','Expired test',null,null,1,null,'2000-01-01');
    check(RecipeInventory::balance($expired,1)===0,'expired batches do not contribute to availability');
    reject(function() use ($expired){RecipeInventory::checkout(1,1,[['product_id'=>$expired,'quantity'=>1]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16)));},'expired batches cannot be sold');
    RecipeInventory::adjust($expired,1,'waste','3','Expired stock discarded',1);
    check((float)RecipeInventory::rows('SELECT SUM(quantity) qty FROM product_batches WHERE product_id=?','i',[$expired])[0]['qty']===7.0,'expired stock can be recorded as waste without becoming sellable');
    $scarce=product('Scarce ingredient'); Product::adjust_stock($scarce,1,'stock_in','1','Test',null,null,1);
    $shortBatch=product('Short batch','recipe','pc','prepared_batch');
    Product::save_recipes($shortBatch,[['ingredient_id'=>$bun,'quantity'=>1,'unit'=>'pc'],['ingredient_id'=>$scarce,'quantity'=>1,'unit'=>'pc']]);
    $bunBefore=RecipeInventory::balance($bun,1);
    reject(function() use ($shortBatch){RecipeInventory::prepare($shortBatch,2,1,1,bin2hex(random_bytes(16)));},'production shortage rejects the entire batch');
    check(RecipeInventory::balance($bun,1)===$bunBefore && RecipeInventory::balance($shortBatch,1)===0,'partial production consumption is rolled back');
    $prior=RecipeInventory::balance($bun,1);
    reject(function() use ($burger){RecipeInventory::checkout(1,1,[['product_id'=>$burger,'quantity'=>999]],0,1000000,'Cash','Dine-in',bin2hex(random_bytes(16)));},'shortages reject whole order');
    check(RecipeInventory::balance($bun,1)===$prior,'failed checkout leaves stock unchanged');
    reject(function() use ($bun){RecipeInventory::adjust($bun,1,'stock_out','9999','Shortage',1);},'FIFO refuses partial shortage');
    check(RecipeInventory::balance($bun,2)===0,'other branch cannot borrow branch one inventory');
    $history=RecipeInventory::rows('SELECT snapshot FROM inventory_operations WHERE kind=? AND request_key=?','ss',['sale',$key])[0]['snapshot'];
    Product::save_recipes($burger,[['ingredient_id'=>$bun,'quantity'=>1,'unit'=>'pc']]);
    check(RecipeInventory::rows('SELECT snapshot FROM inventory_operations WHERE kind=? AND request_key=?','ss',['sale',$key])[0]['snapshot']===$history,'recipe edits preserve historical sale snapshot');
    $reverseKey=bin2hex(random_bytes(16));
    $sauceBeforeReturn=RecipeInventory::balance($sauce,1);
    RecipeInventory::reverseSale($sale,'void_before_preparation',1,$reverseKey);
    check(RecipeInventory::balance($sauce,1)===$sauceBeforeReturn+400000,'void restores original sauce consumption despite edited recipe');
    $returnedBuns=RecipeInventory::balance($bun,1);
    RecipeInventory::reverseSale($sale,'void_before_preparation',1,$reverseKey);
    check(RecipeInventory::balance($bun,1)===$returnedBuns,'stock return retry does not duplicate restoration');
    reject(function() use ($sale){RecipeInventory::reverseSale($sale,'void_before_preparation',1,bin2hex(random_bytes(16)));},'second distinct return of same sale rejected');
    RecipeInventory::reverseSale($batchSale,'return_finished',1,bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($batchBurger,1)===100000,'usable finished-goods return restores original batch');
    reject(function() use ($burger){RecipeInventory::checkout(1,2,[['product_id'=>$burger,'quantity'=>1]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16)));},'unauthorized branch checkout rejected');
    $legacy=product('Existing legacy recipe','recipe'); Product::save_recipes($legacy,[['ingredient_id'=>$bun,'quantity'=>1,'unit'=>'pc']]);
    reject(function() use ($legacy){RecipeInventory::checkout(1,1,[['product_id'=>$legacy,'quantity'=>1]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16)));},'existing recipe cannot silently switch consumption mode');
    $before=RecipeInventory::balance($bun,1);
    $salesBefore=(int)RecipeInventory::rows('SELECT COUNT(*) qty FROM sales')[0]['qty'];
    $conn->query("CREATE TRIGGER fail_movement BEFORE INSERT ON inventory_movements FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected test failure'");
    try { RecipeInventory::checkout(1,1,[['product_id'=>$burger,'quantity'=>1]],0,1000,'Cash','Dine-in',bin2hex(random_bytes(16))); }
    catch (mysqli_sql_exception $expected) {}
    $conn->query('DROP TRIGGER fail_movement');
    check(RecipeInventory::balance($bun,1)===$before && (int)RecipeInventory::rows('SELECT COUNT(*) qty FROM sales')[0]['qty']===$salesBefore,'write failure rolls back sale and every stock movement');
    // Exercise the actual cart endpoint with a persistent, isolated test session.
    function endpoint($action,$data,$json=true) {
        global $workspace;
        $pipes=[];
        $process=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,$action,base64_encode(json_encode($data+['csrf_token'=>'test-csrf']))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
        fclose($pipes[0]); $output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
        if(!$json)return $output;
        $result=json_decode($output,true);
        if (!is_array($result)) throw new RuntimeException('Endpoint returned invalid JSON: ' . $output . $error);
        return $result;
    }
    $beforeAdminSave=RecipeInventory::balance($bun,1);
    $ingredientForm=['action'=>'create','name'=>'Fractional admin ingredient','category_id'=>'1','product_type'=>'ingredient',
        'price'=>'0','cost_price'=>'2','stock'=>'0.125','unit_type'=>'g','track_stock'=>'1'];
    $ingredientSave=endpoint('products',$ingredientForm);
    $createdIngredient=RecipeInventory::rows('SELECT * FROM products WHERE name=?','s',['Fractional admin ingredient'])[0];
    check(($ingredientSave['flash']['type'] ?? '')==='success' && RecipeInventory::balance($createdIngredient['id'],1)===1250,'admin ingredient-first form preserves fractional opening stock');
    $ingredientForm['action']='edit'; $ingredientForm['product_id']=$createdIngredient['id']; $ingredientForm['stock']='0';
    $ingredientEdit=endpoint('products',$ingredientForm);
    check(($ingredientEdit['flash']['type'] ?? '')==='success' && RecipeInventory::balance($createdIngredient['id'],1)===1250,'editing ingredient details cannot overwrite physical stock');
    require_once $workspace . '/models/StockNotification.php';
    StockNotification::sync_stock_alerts(1);
    $alert=RecipeInventory::rows("SELECT * FROM stock_notifications WHERE product_id=? AND status='unread'",'i',[$createdIngredient['id']])[0];
    check((float)$alert['current_stock']===0.125 && $alert['alert_type']==='low_stock','ingredient alerts retain fractional quantities');
    check(!RecipeInventory::rows("SELECT id FROM stock_notifications WHERE product_id=? AND status='unread'",'i',[$burger]),'made-to-order burgers do not generate false finished-stock alerts');
    $form=['action'=>'create','name'=>'Admin form burger','category_id'=>'1','product_type'=>'recipe','recipe_mode'=>'made_to_order',
        'price'=>'150','cost_price'=>'10','stock'=>'0','unit_type'=>'pc','track_stock'=>'1','is_sellable'=>'1',
        'recipe'=>[['ingredient_id'=>$bun,'quantity'=>'1','unit'=>'pc']]];
    $adminSave=endpoint('products',$form);
    check(($adminSave['flash']['type'] ?? '')==='success','admin product form creates recipe successfully');
    $created=RecipeInventory::rows('SELECT * FROM products WHERE name=?','s',['Admin form burger'])[0];
    check($created['recipe_mode']==='made_to_order' && RecipeInventory::balance($bun,1)===$beforeAdminSave,'admin create records mode without consuming ingredients');
    $form['action']='edit'; $form['product_id']=$created['id']; $form['name']='Edited admin burger';
    $adminEdit=endpoint('products',$form);
    check(($adminEdit['flash']['type'] ?? '')==='success' && RecipeInventory::balance($bun,1)===$beforeAdminSave,'admin edit preserves ingredient balances');
    $form['recipe']=[['ingredient_id'=>$bun,'quantity'=>'1','unit'=>'kg']];
    $form['name']='Invalid edit must roll back';
    $invalidEdit=endpoint('products',$form);
    check(($invalidEdit['flash']['type'] ?? '')==='danger' && RecipeInventory::product($created['id'])['name']==='Edited admin burger','invalid admin recipe rolls back the whole product edit');
    // Opening stock is production, while recipe configuration/editing stays neutral.
    $openingForm=['action'=>'create','name'=>'B1T1 opening stock','category_id'=>'1','product_type'=>'recipe',
        'recipe_mode'=>'made_to_order','price'=>'150','cost_price'=>'10','stock'=>'10','unit_type'=>'pc',
        'recipe_create_key'=>bin2hex(random_bytes(16)),
        'recipe'=>[['ingredient_id'=>$bun,'quantity'=>'2','unit'=>'pc']]];
    $beforeOpening=RecipeInventory::balance($bun,1);
    $openingSave=endpoint('products',$openingForm);
    $openingProduct=RecipeInventory::rows('SELECT * FROM products WHERE name=?','s',[$openingForm['name']])[0];
    check(($openingSave['flash']['type'] ?? '')==='success' && RecipeInventory::balance($openingProduct['id'],1)===100000,'add product creates ten finished stock units');
    check($openingProduct['recipe_mode']==='prepared_batch' && RecipeInventory::balance($bun,1)===$beforeOpening-200000,'ten B1T1 units consume twenty burgers worth of ingredients');
    $openingRetry=endpoint('products',$openingForm);
    check(($openingRetry['flash']['type'] ?? '')==='success' && count(RecipeInventory::rows('SELECT id FROM products WHERE name=?','s',[$openingForm['name']]))===1 && RecipeInventory::balance($bun,1)===$beforeOpening-200000,'repeated product save neither duplicates product nor consumes stock twice');
    $changedOpening=$openingForm; $changedOpening['stock']='11';
    $changedSave=endpoint('products',$changedOpening);
    check(($changedSave['flash']['type'] ?? '')==='danger' && RecipeInventory::balance($openingProduct['id'],1)===100000,'used creation token rejects changed stock');
    RecipeInventory::checkout(1,1,[['product_id'=>$openingProduct['id'],'quantity'=>1]],0,1000,'Cash','Take-out',bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($openingProduct['id'],1)===90000 && RecipeInventory::balance($bun,1)===$beforeOpening-200000,'selling opening recipe stock never deducts its base ingredients again');
    $openingEdit=$openingForm; $openingEdit['action']='edit'; $openingEdit['product_id']=$openingProduct['id']; $openingEdit['recipe_mode']='prepared_batch';
    $openingEdit['recipe'][0]['quantity']='3'; $openingEdit['stock']='999';
    $editedOpening=endpoint('products',$openingEdit);
    check(($editedOpening['flash']['type'] ?? '')==='success' && RecipeInventory::balance($bun,1)===$beforeOpening-200000 && RecipeInventory::balance($openingProduct['id'],1)===90000,'editing recipe or posted stock cannot consume or overwrite existing inventory');
    $zeroForm=$openingForm; $zeroForm['name']='Recipe only zero opening'; $zeroForm['stock']='0'; $zeroForm['recipe_create_key']=bin2hex(random_bytes(16));
    $zeroSave=endpoint('products',$zeroForm);
    check(($zeroSave['flash']['type'] ?? '')==='success' && RecipeInventory::balance($bun,1)===$beforeOpening-200000,'zero opening stock saves recipe without ingredient consumption');
    foreach (['1.5','-1','100001'] as $invalidStock) {
        $invalidOpening=$openingForm; $invalidOpening['name']='Invalid opening '.$invalidStock; $invalidOpening['stock']=$invalidStock; $invalidOpening['recipe_create_key']=bin2hex(random_bytes(16));
        $invalidResult=endpoint('products',$invalidOpening);
        check(($invalidResult['flash']['type'] ?? '')==='danger' && !RecipeInventory::rows('SELECT id FROM products WHERE name=?','s',[$invalidOpening['name']]),'invalid opening quantity rejected: '.$invalidStock);
    }
    $shortForm=$openingForm; $shortForm['name']='Opening shortage must roll back'; $shortForm['stock']='1'; $shortForm['recipe_create_key']=bin2hex(random_bytes(16));
    $shortForm['recipe'][]=['ingredient_id'=>$sauce,'quantity'=>'999999','unit'=>'ml'];
    $shortBefore=RecipeInventory::balance($bun,1); $shortSave=endpoint('products',$shortForm);
    check(($shortSave['flash']['type'] ?? '')==='danger' && !RecipeInventory::rows('SELECT id FROM products WHERE name=?','s',[$shortForm['name']]) && RecipeInventory::balance($bun,1)===$shortBefore,'opening stock shortage rolls back product and all ingredient deductions');
    $inventoryPermission=RecipeInventory::rows("SELECT id FROM permissions WHERE name='manage_inventory'")[0]['id'];
    RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$inventoryPermission]);
    $deniedForm=$openingForm; $deniedForm['name']='Unauthorized opening'; $deniedForm['recipe_create_key']=bin2hex(random_bytes(16));
    $deniedSave=endpoint('products_denied',$deniedForm);
    check($deniedSave['success']===false && !RecipeInventory::rows('SELECT id FROM products WHERE name=?','s',[$deniedForm['name']]) && RecipeInventory::balance($bun,1)===$shortBefore,'opening stock requires inventory management permission');
    RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES (1,?)','i',[$inventoryPermission]);
    $regularForm=$openingForm; $regularForm['name']='Regular opening burger'; $regularForm['stock']='2'; $regularForm['recipe'][0]['quantity']='1'; $regularForm['recipe_create_key']=bin2hex(random_bytes(16));
    $regularSave=endpoint('products',$regularForm);
    check(($regularSave['flash']['type'] ?? '')==='success' && RecipeInventory::balance($bun,1)===$shortBefore-20000,'regular burger opening stock consumes one recipe per finished unit');
    require_once $workspace . '/models/RecipeLibrary.php';
    $libraryBefore=RecipeInventory::balance($sauce,1);
    $batchCount=(int)RecipeInventory::rows('SELECT COUNT(*) n FROM product_batches')[0]['n'];
    $definitionRows=[['ingredient_id'=>$sauce,'quantity'=>'25','unit'=>'ml'],['ingredient_id'=>$lettuce,'quantity'=>'0.015','unit'=>'kg']];
    $definitionKey=bin2hex(random_bytes(16));
    $definition=RecipeLibrary::save(0,'Iced tea reusable definition','Any prepared product',1,1,$definitionRows,$definitionKey);
    $definitionProduct=RecipeInventory::product($definition);
    check(!$definitionProduct['is_sellable'] && !$definitionProduct['track_stock'] && RecipeInventory::balance($definition,1)===0,'recipe library creates a non-sellable definition without tracked finished stock');
    StockNotification::sync_stock_alerts(1);
    check(!RecipeInventory::rows('SELECT id FROM stock_notifications WHERE product_id=?','i',[$definition]),'standalone recipe definitions do not generate false stock alerts');
    check(RecipeInventory::balance($sauce,1)===$libraryBefore && (int)RecipeInventory::rows('SELECT COUNT(*) n FROM product_batches')[0]['n']===$batchCount,'saving a reusable recipe creates no inventory batches or deductions');
    $libraryPageSave=endpoint('recipes_save',['recipe_id'=>'0','name'=>'Sauce base from recipe page','description'=>'Reusable formula','category_id'=>'1','branch_id'=>'1','ingredients'=>[['ingredient_id'=>$sauce,'quantity'=>'1','unit'=>'ml']],'request_key'=>bin2hex(random_bytes(16))]);
    check(($libraryPageSave['flash']['type'] ?? '')==='success' && RecipeInventory::balance($sauce,1)===$libraryBefore && count(RecipeInventory::rows('SELECT id FROM products WHERE name=?','s',['Sauce base from recipe page']))===1,'recipe management form saves a generic recipe without inventory changes');
    check(RecipeLibrary::save(0,'Iced tea reusable definition','Any prepared product',1,1,$definitionRows,$definitionKey)===$definition,'recipe definition retry creates no duplicate');
    check(in_array($definition,array_map('intval',array_column(RecipeLibrary::available(),'id')),true),'saved definitions are available to recipe management and product selection');
    $definitionRows[0]['quantity']='30';
    RecipeLibrary::save($definition,'Iced tea reusable definition','Updated formula',1,1,$definitionRows,bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($sauce,1)===$libraryBefore && (float)Product::get_recipes($definition)[0]['quantity']===30.0,'editing a drink recipe updates ingredients without stock changes');
    $invalidDefinition=$definitionRows; $invalidDefinition[]=$definitionRows[0];
    reject(function()use($definition,$invalidDefinition){RecipeLibrary::save($definition,'Invalid rename','',1,1,$invalidDefinition,bin2hex(random_bytes(16)));},'recipe library rejects duplicate ingredients atomically');
    check(RecipeInventory::product($definition)['name']==='Iced tea reusable definition','invalid library edit preserves the prior name and recipe');
    $copyForm=$regularForm; $copyForm['name']='Drink from saved recipe'; $copyForm['stock']='2'; $copyForm['recipe_create_key']=bin2hex(random_bytes(16)); $copyForm['recipe']=$definitionRows;
    $copySaved=endpoint('products',$copyForm);
    check(($copySaved['flash']['type'] ?? '')==='success' && RecipeInventory::balance($sauce,1)===$libraryBefore-600000,'a saved non-burger recipe produces initial stock using its configured quantities');
    $definitionRows[0]['quantity']='40';
    RecipeLibrary::save($definition,'Iced tea reusable definition','Updated later',1,1,$definitionRows,bin2hex(random_bytes(16)));
    $copiedProduct=RecipeInventory::rows('SELECT id FROM products WHERE name=?','s',[$copyForm['name']])[0]['id'];
    check((float)Product::get_recipes($copiedProduct)[0]['quantity']===30.0 && RecipeInventory::balance($sauce,1)===$libraryBefore-600000,'editing the source recipe never rewrites existing product copies or inventory');
    reject(function()use($definitionRows){RecipeLibrary::save(0,'Wrong branch recipe','',1,2,$definitionRows,bin2hex(random_bytes(16)));},'recipe library enforces branch ownership');
    RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=(SELECT id FROM permissions WHERE name=?)','s',['create_recipes']);
    reject(function()use($definitionRows){RecipeLibrary::save(0,'Unauthorized recipe','',1,1,$definitionRows,bin2hex(random_bytes(16)));},'recipe library enforces creation permission');
    RecipeInventory::query("INSERT INTO role_permissions(role_id,permission_id) SELECT 1,id FROM permissions WHERE name='create_recipes'");
    // Global definitions share ingredients, never physical branch balances.
    $conn->query("INSERT INTO roles(id,name) VALUES (2,'Admin')");
    $conn->query("INSERT INTO users(id,name,email,password,branch_id) VALUES(2,'Test Admin','admin@invalid.local','not-a-login',1)");
    $conn->query('INSERT INTO user_roles(user_id,role_id) VALUES(2,2)');
    $conn->query('INSERT INTO role_permissions(role_id,permission_id) SELECT 2,id FROM permissions');
    $sharedIngredient=Product::create(1,'Shared ingredient',uniqid(),uniqid(),'',0,2,'0','0','', 'active',0,0,'100',null,'ingredient',1,0,1,'stocked',null,'g',1);
    $otherIngredient=Product::create(1,'Branch two ingredient',uniqid(),uniqid(),'',0,3,'0','0','', 'active',0,0,'100',2,'ingredient',1,0,1,'stocked',null,'g',1);
    $sharedRows=[['ingredient_id'=>$sharedIngredient,'quantity'=>'5','unit'=>'g']];
    reject(function()use($sharedRows){RecipeLibrary::save(0,'Unauthorized global','',1,'global',$sharedRows,bin2hex(random_bytes(16)));},'branch staff cannot create all-branches recipes');
    $_SESSION['user']=['id'=>2,'branch_id'=>1];
    $sharedRecipe=RecipeLibrary::save(0,'Global shared recipe','',1,'global',$sharedRows,bin2hex(random_bytes(16)));
    check(RecipeInventory::product($sharedRecipe)['branch_id']===null,'global recipe scope stays global even when its admin has an assigned branch');
    reject(function()use($definitionRows){RecipeLibrary::save(0,'Invalid global components','',1,'global',$definitionRows,bin2hex(random_bytes(16)));},'global recipes reject branch-specific ingredient references');
    reject(function()use($otherIngredient){RecipeLibrary::save(0,'Wrong ingredients for branch one','',1,1,[['ingredient_id'=>$otherIngredient,'quantity'=>'1','unit'=>'g']],bin2hex(random_bytes(16)));},'server rejects ingredients outside the selected branch');
    RecipeLibrary::save($sharedRecipe,'Global shared recipe','',1,2,$sharedRows,bin2hex(random_bytes(16)));
    check((int)RecipeInventory::product($sharedRecipe)['branch_id']===2,'stock-free recipe definition can be assigned to a specific branch');
    RecipeLibrary::save($sharedRecipe,'Global shared recipe','',1,'global',$sharedRows,bin2hex(random_bytes(16)));
    check(RecipeInventory::product($sharedRecipe)['branch_id']===null,'stock-free recipe definition can be reassigned to all branches');
    RecipeInventory::adjust($sharedIngredient,2,'stock_in','10','Global item branch one',1);
    RecipeInventory::adjust($sharedIngredient,2,'stock_in','20','Global item branch two',2);
    RecipeLibrary::save($sharedRecipe,'Global shared recipe','No stock movement',1,'global',$sharedRows,bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($sharedIngredient,1)===100000 && RecipeInventory::balance($sharedIngredient,2)===200000,'saving a global recipe preserves separate physical stock for every branch');
    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    check(in_array($sharedRecipe,array_map('intval',array_column(RecipeLibrary::available(),'id')),true),'all-branches recipes are visible to assigned branch staff');
    reject(function()use($sharedRecipe,$sharedRows){RecipeLibrary::save($sharedRecipe,'Unauthorized shared edit','',1,'global',$sharedRows,bin2hex(random_bytes(16)));},'branch staff cannot alter a recipe shared by all branches');
    reject(function()use($sharedRecipe,$sharedRows){Product::save_recipes($sharedRecipe,$sharedRows);},'alternate recipe writer also protects global definitions from branch staff edits');
    require_once $workspace.'/models/ProductTransfer.php';
    $transferItem=product('Transfer decimal item','simple','g');
    Product::adjust_stock($transferItem,1,'stock_in','4.1251','Transfer live batch','ORIGINAL-A','4.50',1,null,'2030-12-31','12.50');
    Product::adjust_stock($transferItem,1,'stock_in','6','Transfer expired batch','ORIGINAL-B','7.25',1,null,'2020-01-01','15.00');
    $sourceBatches=Product::get_product_batches($transferItem);
    $transferKey=bin2hex(random_bytes(16));
    $transferPermissionId=(int)RecipeInventory::rows("SELECT id FROM permissions WHERE name='reassign_products'")[0]['id'];
    RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$transferPermissionId]);
    reject(function()use($transferItem,$transferKey){ProductTransfer::run($transferItem,2,1,$transferKey,'1');},'transfer is denied when its permission is switched off');
    RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$transferPermissionId]);
    $_SESSION['user']=['id'=>2,'branch_id'=>1];
    $transferResult=ProductTransfer::run($transferItem,2,2,$transferKey,'1');
    check((int)RecipeInventory::product($transferItem)['branch_id']===2 && RecipeInventory::balance($transferItem,1)===0 && RecipeInventory::balance($transferItem,2)===41251,'transfer moves product ownership and exact four-decimal usable stock');
    check($transferResult['quantity']==='10.1251' && count($transferResult['batches'])===2,'transfer includes expired physical stock without making it sellable');
    foreach ($transferResult['batches'] as $batchLink) {
        $old=RecipeInventory::rows('SELECT * FROM product_batches WHERE id=?','i',[$batchLink['source_batch']])[0];
        $new=RecipeInventory::rows('SELECT * FROM product_batches WHERE id=?','i',[$batchLink['destination_batch']])[0];
        check((float)$old['quantity']===0.0 && $new['cost_price']===$old['cost_price'] && $new['selling_price']===$old['selling_price'] && $new['expiry_date']===$old['expiry_date'],'transfer preserves original batch history, prices and expiry');
    }
    $transferAgain=ProductTransfer::run($transferItem,2,2,$transferKey,'1');
    check($transferAgain===$transferResult && RecipeInventory::balance($transferItem,2)===41251,'transfer retry cannot duplicate destination stock');
    $movementBalance=RecipeInventory::rows("SELECT SUM(m.quantity) q FROM inventory_movements m JOIN inventory_operations o ON o.id=m.operation_id WHERE o.kind='branch_transfer' AND m.product_id=?",'i',[$transferItem])[0]['q'];
    check((float)$movementBalance===0.0,'transfer ledger has balanced source and destination quantities');
    reject(function()use($transferItem){ProductTransfer::run($transferItem,2,2,bin2hex(random_bytes(16)),'2');},'same-branch transfer is rejected');
    reject(function()use($transferItem){ProductTransfer::run($transferItem,1,2,bin2hex(random_bytes(16)),'1');},'stale source branch cannot move a product again');
    reject(function()use($bun){ProductTransfer::run($bun,2,2,bin2hex(random_bytes(16)),'1');},'transfer cannot break ingredient references in existing recipes');
    reject(function()use($burger){ProductTransfer::run($burger,2,2,bin2hex(random_bytes(16)),'1');},'transfer cannot leave a recipe using another branch ingredients');
    $rollbackItem=product('Rollback transfer','simple','pc');
    Product::adjust_stock($rollbackItem,2,'stock_in','3','Rollback transfer stock',null,1,1);
    $conn->query("CREATE TRIGGER fail_transfer_receive BEFORE INSERT ON product_batches FOR EACH ROW BEGIN IF NEW.product_id=".(int)$rollbackItem." AND NEW.branch_id=2 THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected transfer failure'; END IF; END");
    try { ProductTransfer::run($rollbackItem,2,2,bin2hex(random_bytes(16)),'1'); throw new RuntimeException('Transfer failure was not injected'); } catch (mysqli_sql_exception $error) {}
    $conn->query('DROP TRIGGER fail_transfer_receive');
    check((int)RecipeInventory::product($rollbackItem)['branch_id']===1 && RecipeInventory::balance($rollbackItem,1)===30000 && RecipeInventory::balance($rollbackItem,2)===0,'failed destination write rolls back assignment and every stock movement');
    RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$transferPermissionId]);
    $endpointTransfer=endpoint('inventory_action',['action'=>'transfer_product_branch','product_id'=>$rollbackItem,'source_branch_id'=>'1','destination_branch_id'=>'2','request_key'=>bin2hex(random_bytes(16))]);
    check($endpointTransfer['success']===false && RecipeInventory::balance($rollbackItem,1)===30000,'transfer endpoint denies staff when transfer permission is off');
    RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$transferPermissionId]);
    $adminTransfer=endpoint('inventory_action_admin',['action'=>'transfer_product_branch','product_id'=>$rollbackItem,'source_branch_id'=>'1','destination_branch_id'=>'2','request_key'=>bin2hex(random_bytes(16))]);
    check($adminTransfer['success']===true && RecipeInventory::balance($rollbackItem,2)===30000,'authorized transfer endpoint moves assignment and stock successfully');
    $holdItem=product('Open order transfer item','simple','pc');
    RecipeInventory::adjust($holdItem,2,'stock_in','3','Pending order test',1);
    require_once $workspace.'/models/OnlineOrder.php';
    Cart::add($holdItem,1,'transfer-pending-order',1);
    OnlineOrder::place(1,'transfer-pending-order',1,'Transfer customer','transfer@invalid.local','09123456789',bin2hex(random_bytes(32)));
    reject(function()use($holdItem){ProductTransfer::run($holdItem,2,2,bin2hex(random_bytes(16)),'1');},'open online order reservations prevent product transfer');
    $historicalItem=product('Historical transfer item','simple','pc');
    RecipeInventory::adjust($historicalItem,2,'stock_in','3','Historical transfer test',1);
    $historicalSale=RecipeInventory::checkout(2,1,[['product_id'=>$historicalItem,'quantity'=>1]],0,1000,'Cash','Take-out',bin2hex(random_bytes(16)));
    ProductTransfer::run($historicalItem,2,2,bin2hex(random_bytes(16)),'1');
    reject(function()use($historicalSale){RecipeInventory::reverseSale($historicalSale,'return_finished',2,bin2hex(random_bytes(16)));},'historical returns cannot recreate stock in a product former branch');
    check(RecipeInventory::balance($historicalItem,1)===0 && RecipeInventory::balance($historicalItem,2)===20000,'rejected old-branch return preserves transferred quantities');
    $decimalRecipe=$regularForm;$decimalRecipe['name']='Two decimal opening display';$decimalRecipe['stock']='2.00';$decimalRecipe['recipe_create_key']=bin2hex(random_bytes(16));
    $decimalResult=endpoint('products',$decimalRecipe);
    check(($decimalResult['flash']['type'] ?? '')==='success','two-decimal stock display remains compatible with whole recipe opening quantities');
    $pipes=[];
    $transferPageProcess=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,'render_products_admin',base64_encode('{}')],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    fclose($pipes[0]);$transferHtml=stream_get_contents($pipes[1]);$transferErrors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($transferPageProcess);
    file_put_contents($root.'/scratch/product-transfers-rendered.html',$transferHtml);
    check(strpos($transferHtml,'transfer-product-form')!==false && !preg_match('/Warning:|Notice:|Fatal error/',$transferHtml.$transferErrors),'product actions render the transfer modal and two-decimal stock inputs without warnings');
    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    $globalEditProduct=product('Global general edit','simple','g');
    RecipeInventory::adjust($globalEditProduct,1,'stock_in','12.3456','Global edit fixture',1);
    RecipeInventory::query('UPDATE products SET branch_id=NULL WHERE id=?','i',[$globalEditProduct]);
    RecipeInventory::query("INSERT INTO categories (id,name) VALUES (2,'Updated category')");
    $globalEditData=['action'=>'edit','product_id'=>$globalEditProduct,'category_id'=>'2','name'=>'Updated global general info','description'=>'General details only',
        'product_type'=>'simple','unit_type'=>'g','price'=>'100','cost_price'=>'1','stock'=>'999','low_stock_threshold'=>'2.00','max_stock_threshold'=>'100.00',
        'branch_id'=>'','track_stock'=>'on','is_sellable'=>'on','inventory_type'=>'stocked'];
    $globalMoves=count(RecipeInventory::rows('SELECT id FROM inventory_movements WHERE product_id=?','i',[$globalEditProduct]));
    for ($editAttempt=0;$editAttempt<2;$editAttempt++) {
        $globalEditResponse=endpoint('products',$globalEditData);
        $globalEdited=RecipeInventory::product($globalEditProduct);
        check(($globalEditResponse['flash']['type'] ?? '')==='success' && $globalEdited['branch_id']===null && $globalEdited['unit_type']==='g' && (int)$globalEdited['category_id']===2,'general edit preserves global ownership and existing units on save '.($editAttempt+1));
        check(RecipeInventory::balance($globalEditProduct,1)===123456 && count(RecipeInventory::rows('SELECT id FROM inventory_movements WHERE product_id=?','i',[$globalEditProduct]))===$globalMoves,'repeated general edit never doubles stock or creates movements');
    }
    unset($globalEditData['branch_id']);
    check((endpoint('products',$globalEditData)['flash']['type'] ?? '')==='success' && RecipeInventory::product($globalEditProduct)['branch_id']===null,'omitted branch control preserves global ownership');
    $globalEditData['branch_id']='1';
    check((endpoint('products',$globalEditData)['flash']['type'] ?? '')==='success' && RecipeInventory::product($globalEditProduct)['branch_id']===null && RecipeInventory::balance($globalEditProduct,1)===123456,'staff branch injected into POST cannot reassign a global product or duplicate stock');
    $manualIngredient=product('Manual ingredient');
    RecipeInventory::adjust($manualIngredient,1,'stock_in','30','Manual fixture',1);
    $manualRecipe=product('Manual recipe','recipe','pc','made_to_order');
    Product::save_recipes($manualRecipe,[['ingredient_id'=>$manualIngredient,'quantity'=>'2','unit'=>'pc']]);
    $manualData=['action'=>'adjust_stock','product_id'=>$manualRecipe,'adjustment_type'=>'stock_in','quantity'=>'10','branch_id'=>1,'remarks'=>'Prepare ten','request_key'=>bin2hex(random_bytes(16))];
    $manualResult=endpoint('inventory_action',$manualData);
    check(!$manualResult['success'] && RecipeInventory::balance($manualIngredient,1)===300000 && RecipeInventory::product($manualRecipe)['recipe_mode']==='made_to_order','manual made-to-order stock requires explicit preparation confirmation');
    $manualData['confirm_recipe_preparation']='1';
    $manualResult=endpoint('inventory_action',$manualData);
    check($manualResult['success'] && RecipeInventory::balance($manualIngredient,1)===100000 && RecipeInventory::balance($manualRecipe,1)===100000 && RecipeInventory::product($manualRecipe)['recipe_mode']==='prepared_batch','manual preparation consumes ten recipe units and changes sales to finished stock');
    $manualRetry=endpoint('inventory_action',$manualData);
    check($manualRetry['success'] && RecipeInventory::balance($manualIngredient,1)===100000 && RecipeInventory::balance($manualRecipe,1)===100000,'manual receipt retry cannot duplicate preparation');
    $manualData['request_key']=bin2hex(random_bytes(16));$manualData['quantity']='6';
    $manualResult=endpoint('inventory_action',$manualData);
    check(!$manualResult['success'] && RecipeInventory::balance($manualIngredient,1)===100000 && RecipeInventory::balance($manualRecipe,1)===100000,'ingredient shortage rolls back complete manual receipt');
    $manualData['quantity']='1.5';$manualData['request_key']=bin2hex(random_bytes(16));
    check(!endpoint('inventory_action',$manualData)['success'],'manual recipe stock rejects fractional finished units');
    $manualData['quantity']='2.00';$manualData['request_key']=bin2hex(random_bytes(16));$manualData['expiry_date']='2027-12-31';
    check(endpoint('inventory_action',$manualData)['success'] && RecipeInventory::balance($manualIngredient,1)===60000 && RecipeInventory::balance($manualRecipe,1)===120000,'prepared recipe manual additions consume ingredients dynamically');
    check((bool)RecipeInventory::rows('SELECT id FROM product_batches WHERE product_id=? AND expiry_date=?','is',[$manualRecipe,'2027-12-31']),'manual recipe receipt preserves expiry date');
    foreach (['stock_out','waste'] as $manualAction) {
        $manualData['adjustment_type']=$manualAction;$manualData['quantity']='1';$manualData['request_key']=bin2hex(random_bytes(16));
        check(endpoint('inventory_action',$manualData)['success'] && RecipeInventory::balance($manualIngredient,1)===60000,'manual '.$manualAction.' removes finished stock without changing already-consumed ingredients');
    }
    RecipeInventory::checkout(1,1,[['product_id'=>$manualRecipe,'quantity'=>1]],0,1000,'Cash','Take-out',bin2hex(random_bytes(16)));
    check(RecipeInventory::balance($manualRecipe,1)===90000 && RecipeInventory::balance($manualIngredient,1)===60000,'checkout after manual preparation does not deduct ingredients twice');
    $manualContext=endpoint('inventory_action',['action'=>'get_product_recipe','product_id'=>$manualRecipe,'branch_id'=>1]);
    check($manualContext['success'] && $manualContext['product']['physical_stock']==='9.0000' && count($manualContext['recipe'])===1,'adjustment preview receives physical branch stock and saved ingredient rows');
    $pipes=[];
    $globalPageProcess=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,'render_recipes_admin',base64_encode('{}')],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    fclose($pipes[0]);$globalHtml=stream_get_contents($pipes[1]);$globalErrors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($globalPageProcess);
    file_put_contents($root.'/scratch/recipe-management-global-rendered.html',$globalHtml);
    check(strpos($globalHtml,'value="global"')!==false && !preg_match('/Warning:|Notice:|Fatal error/',$globalHtml.$globalErrors),'admin recipe page renders Global All branches and branch ingredient metadata without warnings');
    $pipes=[];
    $pageProcess=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,'render_recipes',base64_encode('{}')],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    fclose($pipes[0]); $recipeHtml=stream_get_contents($pipes[1]); $recipeErrors=stream_get_contents($pipes[2]); fclose($pipes[1]); fclose($pipes[2]); proc_close($pageProcess);
    file_put_contents($root . '/scratch/recipe-management-rendered.html',$recipeHtml);
    check(strpos($recipeHtml,'recipe-definition-form')!==false && strpos($recipeHtml,'recipe-library.js')!==false && !preg_match('/Warning:|Notice:|Fatal error/',$recipeHtml.$recipeErrors),'recipe management renders its paginated library and ingredient editor without warnings');
    $catalog=endpoint('inventory_action',['action'=>'products_datatable','draw'=>1,'start'=>0,'length'=>100,
        'search'=>['value'=>'Burger'],'order'=>[['column'=>0,'dir'=>'asc']]]);
    $burgerRow=null;
    foreach($catalog['data'] ?? [] as $row) if((int)$row['id']===$burger) $burgerRow=$row;
    check($burgerRow && !empty($burgerRow['stock_is_recipe_capacity']) && (int)$burgerRow['stock']===RecipeInventory::availability(RecipeInventory::product($burger),1),'admin catalog reports ingredient-based burger capacity');
    $cart=endpoint('pos_cart',['action'=>'add','product_id'=>$burger,'price'=>'0.01','stock'=>'99999']);
    check($cart['success'] && (float)$cart['subtotal']===100.0,'cart uses server product price');
    $cart=endpoint('pos_cart',['action'=>'add','product_id'=>$burger,'modifiers'=>json_encode([['id'=>$mod['id']]])]);
    check($cart['success'] && count($cart['cart'])===2,'same burger with different extras has separate cart lines');
    $checkout=endpoint('pos_cart',['action'=>'checkout','amount_tendered'=>'1000','payment_method'=>'Cash','request_key'=>$cart['request_key']]);
    check($checkout['success'],'real cart endpoint completes recipe checkout');
    $retry=endpoint('pos_cart',['action'=>'checkout','amount_tendered'=>'1000','payment_method'=>'Cash','request_key'=>$cart['request_key']]);
    check($retry['success'] && $retry['sale_id']===$checkout['sale_id'],'real endpoint retry after cleared cart returns original sale');
    $pipes=[];
    $render=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,'render_products',base64_encode('{}')],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    fclose($pipes[0]);$html=stream_get_contents($pipes[1]);$renderErrors=stream_get_contents($pipes[2]);
    fclose($pipes[1]);fclose($pipes[2]);proc_close($render);
    file_put_contents($workspace . '/rendered-catalog.html',$html);
    check(strpos($html,'create_recipe_mode')!==false && strpos($html,'edit_recipe_mode')!==false && strpos($html,'prepare_recipe_batch')!==false,'admin catalog renders new recipe and production controls');
    check(strpos($html,'Fatal error')===false && strpos($html,'Warning:')===false && strpos($html,'Notice:')===false,'admin catalog rendering has no PHP warnings');
    preg_match_all('/<script(?![^>]*\bsrc=)[^>]*>(.*?)<\/script>/si',$html,$scripts);
    foreach($scripts[1] as $index=>$script) file_put_contents($workspace . '/inline-' . $index . '.js',$script);
    // Race two independent connections for the last available bun.
    $remaining=RecipeInventory::balance($bun,1);
    RecipeInventory::adjust($bun,1,'waste',InventoryQuantity::format($remaining-10000),'Race fixture',1);
    $processes=[];
    for($i=0;$i<2;$i++) {
        $pipes=[];
        $proc=proc_open([PHP_BINARY,__FILE__,'--worker',$workspace,(string)$burger,bin2hex(random_bytes(16))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
        fclose($pipes[0]); $processes[]=[$proc,$pipes];
    }
    $results=[];
    foreach($processes as $p) { $results[]=trim(stream_get_contents($p[1][1])); fclose($p[1][1]); fclose($p[1][2]); proc_close($p[0]); }
    sort($results);
    check($results===['REJECTED','SUCCESS'] && RecipeInventory::balance($bun,1)===0,'concurrent last-stock checkouts permit exactly one sale');
    // Direct requests must fail after a role loses an action permission.
    $permissionActions=[
        'add_product'=>['get_next_product_id'],
        'delete_product'=>[], 'print_products'=>[],
        'edit_product'=>['save_product_recipe','save_product_modifiers','save_product_variants','save_product_attributes','save_attribute'],
        'manage_inventory'=>['adjust_stock','quick_restock','record_waste','reverse_sale_stock'],
        'view_stock_history'=>['logs_datatable'],
        'edit_stock_batches'=>['update_batch_data'],
        'prepare_recipe_stock'=>['prepare_recipe_batch'],
        'reassign_products'=>['transfer_product_branch'],
        'view_product'=>['products_datatable','get_product_recipe','get_batch_breakdown','get_product_attribute_values']
    ];
    foreach($permissionActions as $permission=>$actions) {
        $permissionId=(int)RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$permission])[0]['id'];
        RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$permissionId]);
        foreach($actions as $deniedAction) {
            $denied=endpoint('inventory_action',['action'=>$deniedAction,'product_id'=>$manualRecipe]);
            check(empty($denied['success']) && ($denied['message'] ?? '')==='You do not have access to this action.','revoking '.$permission.' blocks direct '.$deniedAction.' requests');
        }
        if(in_array($permission,['add_product','edit_product','delete_product'],true)) {
            $controllerAction=['add_product'=>'create','edit_product'=>'edit','delete_product'=>'delete'][$permission];
            $denied=endpoint('products_denied',['action'=>$controllerAction,'product_id'=>$manualRecipe]);
            check(empty($denied['success']) && ($denied['message'] ?? '')==='You do not have access to this action.','revoking '.$permission.' blocks the inventory page '.$controllerAction.' submission');
        }
        if($permission==='print_products') {
            $denied=endpoint('products_export_denied',[]);
            check(empty($denied['success']) && ($denied['message'] ?? '')==='You do not have access to this action.','revoking print_products blocks inventory CSV export');
        }
        RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$permissionId]);
    }
    $otherBranchRead=endpoint('inventory_action',['action'=>'get_product_attribute_values','product_id'=>$transferItem]);
    check(empty($otherBranchRead['success']),'product attribute helpers cannot read another branch product');
    require_once $workspace.'/models/PermissionSeeder.php';
    $conn->query("DELETE rp FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE p.name IN ('view_stock_history','edit_stock_batches','prepare_recipe_stock','reassign_products')");
    $conn->query("DELETE FROM permissions WHERE name IN ('view_stock_history','edit_stock_batches','prepare_recipe_stock','reassign_products')");
    PermissionSeeder::syncInventoryActions($conn);
    check(User::has_permission(1,'view_stock_history') && User::has_permission(1,'prepare_recipe_stock') && User::has_permission(2,'reassign_products'),'permission migration preserves existing role access to new product controls');
    $historyPermission=(int)RecipeInventory::rows("SELECT id FROM permissions WHERE name='view_stock_history'")[0]['id'];
    RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$historyPermission]);
    PermissionSeeder::syncInventoryActions($conn);
    check(!User::has_permission(1,'view_stock_history'),'re-running inventory permission migration does not restore revoked role permissions');
    require_once $workspace.'/models/Role.php';
    $inventoryPermissions=Role::get_all_permissions()['Inventory'];
    check(count(array_intersect(['view_stock_history','edit_stock_batches','prepare_recipe_stock','reassign_products'],array_column($inventoryPermissions,'name')))===4,'all new product action permissions appear in the Roles and Permissions inventory group');
    $adminPermissions=Role::get_role_permissions(2);
    $transferPermission=(int)RecipeInventory::rows("SELECT id FROM permissions WHERE name='reassign_products'")[0]['id'];
    Role::update(2,'Admin','Permission integration test',array_values(array_diff($adminPermissions,[$transferPermission])));
    $revokedTransfer=endpoint('inventory_action_admin',['action'=>'transfer_product_branch']);
    check(empty($revokedTransfer['success']) && !User::has_permission(2,'reassign_products'),'saving role permissions revokes branch transfer even for Admin');
    Role::update(2,'Admin','Permission integration test',$adminPermissions);
    check(User::has_permission(2,'reassign_products'),'saving role permissions restores the assigned action');
    $permissionCatalog=endpoint('inventory_action',['action'=>'products_datatable','draw'=>1,'start'=>0,'length'=>100,'search'=>['value'=>'Manual recipe']]);
    check(count($permissionCatalog['data'])===1 && empty($permissionCatalog['data'][0]['can_view_history']),'product actions hide stock history after its role permission is revoked');
    $reassignPermission=(int)RecipeInventory::rows("SELECT id FROM permissions WHERE name='reassign_products'")[0]['id'];
    RecipeInventory::query('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$reassignPermission]);
    $permissionOnlyItem=product('Permission-only transfer','simple','pc');
    RecipeInventory::adjust($permissionOnlyItem,1,'stock_in','3','Permission fixture',1);
    $originalPermissions=Role::get_role_permissions(1);
    $unrelatedPermissions=array_column(RecipeInventory::rows("SELECT id FROM permissions WHERE name IN ('manage_inventory','edit_product')"),'id');
    Role::update(1,'Manager','Transfer permission without other stock actions',array_values(array_diff($originalPermissions,$unrelatedPermissions)));
    check(ProductTransfer::canTransfer(1,1) && !ProductTransfer::canTransfer(1,2) && !ProductTransfer::canTransfer(1,null),'transfer permission enables assigned-branch products while protecting other and global sources');
    $permissionOnlyRequest=['action'=>'transfer_product_branch','product_id'=>$permissionOnlyItem,'source_branch_id'=>'1','destination_branch_id'=>'2','request_key'=>bin2hex(random_bytes(16))];
    $managerTransfer=endpoint('inventory_action',$permissionOnlyRequest);
    check($managerTransfer['success'] && RecipeInventory::balance($permissionOnlyItem,2)===30000 && RecipeInventory::balance($permissionOnlyItem,1)===0,'Manager transfer toggle works without Edit Products or Manage Inventory');
    check(endpoint('inventory_action',$permissionOnlyRequest)['success'] && RecipeInventory::balance($permissionOnlyItem,2)===30000,'Manager retry after transfer does not duplicate destination stock');
    $permissionOnlyRequest['source_branch_id']='2';$permissionOnlyRequest['destination_branch_id']='1';$permissionOnlyRequest['request_key']=bin2hex(random_bytes(16));
    check(!endpoint('inventory_action',$permissionOnlyRequest)['success'],'transfer permission cannot move stock out of another branch');
    $ingredientTransfer=product('Manager ingredient transfer','ingredient','g');
    // Stock setup is isolated from the role whose only stock action is Transfer.
    $_SESSION['user']=['id'=>2,'branch_id'=>1];
    RecipeInventory::adjust($ingredientTransfer,2,'stock_in','12.3456','Ingredient transfer fixture',1);
    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    $ingredientRequest=['action'=>'transfer_product_branch','product_id'=>$ingredientTransfer,'source_branch_id'=>'1','destination_branch_id'=>'2','request_key'=>bin2hex(random_bytes(16))];
    $ingredientResult=endpoint('inventory_action',$ingredientRequest);
    check($ingredientResult['success'] && RecipeInventory::balance($ingredientTransfer,1)===0 && RecipeInventory::balance($ingredientTransfer,2)===123456 && (int)RecipeInventory::product($ingredientTransfer)['branch_id']===2,'Manager transfer permission also transfers ingredients and exact fractional stock');
    check(endpoint('inventory_action',$ingredientRequest)['success'] && RecipeInventory::balance($ingredientTransfer,2)===123456,'ingredient transfer retries do not duplicate stock');
    check(RecipeInventory::product($ingredientTransfer)['is_ingredient']==1 && RecipeInventory::product($ingredientTransfer)['product_type']==='ingredient' && RecipeInventory::product($ingredientTransfer)['unit_type']==='g','transferred ingredients retain ingredient type and measurement unit');
    Role::update(1,'Manager','Restore fixture',$originalPermissions);
    require $root.'/tools/tests/inventory-control-cases.php';
    require $root.'/tools/tests/stock-transfer-cases.php';
    require_once $workspace.'/models/DashboardData.php';
    $_SESSION['user']=['id'=>3,'branch_id'=>2];
    RecipeInventory::checkout(3,2,[['product_id'=>$wfRecipeDestination,'quantity'=>1]],0,1000,'Cash','Take-out',bin2hex(random_bytes(16)));
    ActivityLog::log('Dashboard branch marker','Private branch two activity');
    $_SESSION['user']=['id'=>1,'branch_id'=>1];ActivityLog::log('Dashboard branch marker','Own branch activity');
    $_SESSION['user']=['id'=>1,'branch_id'=>2,'branch'=>'Wrong cached branch'];
    $dashboard=DashboardData::load('2','1900-01-01 to 2100-01-01');
    $expectedDashboard=RecipeInventory::rows("SELECT COALESCE(SUM(final_amount),0) total,COUNT(*) n FROM sales WHERE branch_id=1 AND status='Completed'")[0];
    check($dashboard['selected_branch']==='1' && (float)$dashboard['stats']['sales_today']===(float)$expectedDashboard['total'] && (int)$dashboard['stats']['orders_today']===(int)$expectedDashboard['n'],'dashboard totals use database branch assignment despite foreign URL and stale session');
    check(count(array_filter($dashboard['recent_sales'],static function($sale){return (int)$sale['branch_id']!==1;}))===0,'dashboard recent transactions never include another branch');
    check(count(array_filter($dashboard['low_stock'],static function($product){return $product['branch_id']!==null && (int)$product['branch_id']!==1;}))===0,'dashboard low stock excludes other branches');
    check(count(array_filter($dashboard['low_stock'],static function($p){return (float)$p['stock']!==(float)InventoryQuantity::format(RecipeInventory::balance($p['id'],1));}))===0,'dashboard low stock preserves exact fractional branch stock');
    $expectedUsers=(int)RecipeInventory::rows('SELECT COUNT(*) n FROM users WHERE branch_id=1 AND deleted_at IS NULL')[0]['n'];
    check($dashboard['stats']['total_users']===$expectedUsers,'dashboard staff count is limited to the assigned branch');
    check(count(array_filter($dashboard['recent_activities'],static function($event){return $event['description']==='Private branch two activity';}))===0,'dashboard activity feed excludes other branch actors');
    $_SESSION['user']=['id'=>2,'branch_id'=>1];
    check(DashboardData::load('all')['selected_branch']==='1','assigned administrator dashboard defaults to and stays within owned branch');
    RecipeInventory::query('UPDATE users SET branch_id=NULL WHERE id=2');
    $branchTwoDashboard=DashboardData::load('2');
    check($branchTwoDashboard['selected_branch']==='2' && DashboardData::load('all')['can_choose_branch'],'unassigned global administrator can explicitly choose a dashboard branch');
    check(array_sum($branchTwoDashboard['stats']['sales_chart_data'])===100.0 && count($branchTwoDashboard['top_selling'])===1 && $branchTwoDashboard['top_selling'][0]['name']==='Prepared transfer source','dashboard chart and top sellers include only sales from the selected branch');
    RecipeInventory::query('UPDATE users SET branch_id=1 WHERE id=2');
    $_SESSION['user']=['id'=>4,'branch_id'=>3];RecipeInventory::query('UPDATE users SET branch_id=NULL WHERE id=4');
    reject(function(){DashboardData::load('all');},'unassigned staff cannot fall back to all-branch dashboard data');
    $pipes=[];$process=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,'render_dashboard',base64_encode('{}')],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    fclose($pipes[0]);$dashboardHtml=stream_get_contents($pipes[1]);$dashboardErrors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($process);
    file_put_contents($root.'/scratch/dashboard-branch-rendered.html',$dashboardHtml);
    check(strpos($dashboardHtml,'Test branch')!==false && strpos($dashboardHtml,'id="branch-select"')===false && !preg_match('/Warning:|Notice:|Fatal error/',$dashboardHtml.$dashboardErrors),'actual dashboard renders assigned branch and hides cross-branch selector without warnings');
    require $root.'/tools/tests/purchasing-cases.php';
    require $root.'/tools/tests/cash-shift-cases.php';
    require $root.'/tools/tests/pos-drawer-cases.php';
    require $root.'/tools/tests/cashier-schedule-cases.php';
    require $root.'/tools/tests/cashier-planning-cases.php';
    require $root.'/tools/tests/online-order-permission-cases.php';
    require $root.'/tools/tests/cashier-schedule-interface-cases.php';
    require $root.'/tools/tests/dashboard-operations-cases.php';
    require $root.'/tools/tests/branch-overview-cases.php';
    require $root.'/tools/tests/paymongo-cases.php';
    require $root.'/tools/tests/branch-deletion-cases.php';
    echo "All {$checks} integration checks passed. Source database was not modified.\n";
} finally {
    if (!preg_match('/^pos_burger_test_[a-f0-9]{12}$/D',$database)) throw new RuntimeException('Unsafe cleanup target.');
    $admin->query('DROP DATABASE `' . $database . '`');
    // Keep no private test configuration behind.
    if (is_file($workspace . '/config/config.local.php')) unlink($workspace . '/config/config.local.php');
}
