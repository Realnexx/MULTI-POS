<?php
// Creates and removes ONLY a unique pos_burger_test_* database. Never migrates the source.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
if (($argv[1] ?? '') === '--endpoint') {
    ini_set('session.save_path', $argv[2]);
    session_id('burger-endpoint-test');
    session_start();
    $_SESSION['user']=['id'=>1,'branch_id'=>1,'name'=>'Test cashier','email'=>'test@invalid.local'];
    $_SESSION['csrf_token']='test-csrf';
    $_POST=json_decode(base64_decode($argv[4]),true);
    $_SERVER['REQUEST_METHOD']='POST';
    $_SERVER['HTTP_X_REQUESTED_WITH']='XMLHttpRequest';
    if ($argv[3] === 'render_products') {
        $_SERVER['REQUEST_METHOD']='GET';
        $_SERVER['REQUEST_URI']='/pos/?route=inventory/products';
        $_SERVER['SCRIPT_NAME']='/pos/index.php';
        $_SERVER['HTTP_HOST']='localhost';
        $_GET=['route'=>'inventory/products'];
        require $argv[2] . '/controllers/InventoryController.php';
        (new InventoryController())->products();
    } elseif ($argv[3] === 'products') {
        register_shutdown_function(function () { echo json_encode(['flash'=>$_SESSION['flash'] ?? null]); });
        require $argv[2] . '/controllers/InventoryController.php';
        (new InventoryController())->products();
    } else require $argv[2] . '/ajax/' . $argv[3] . '.php';
    exit;
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
    $admin->multi_query(file_get_contents($root . '/tools/migrations/20261015-burger-inventory.sql'));
    do { if ($r=$admin->store_result()) $r->free(); } while ($admin->more_results() && $admin->next_result());
    mkdir($workspace . '/models',0755,true); mkdir($workspace . '/config',0755,true);
    foreach (glob($root . '/models/*.php') as $path) copy($path,$workspace . '/models/' . basename($path));
    mkdir($workspace . '/ajax',0755,true);
    foreach (['pos_cart.php','inventory_action.php'] as $name) copy($root . '/ajax/' . $name,$workspace . '/ajax/' . $name);
    mkdir($workspace . '/controllers',0755,true);
    copy($root . '/controllers/InventoryController.php',$workspace . '/controllers/InventoryController.php');
    mkdir($workspace . '/middleware',0755,true);
    foreach (glob($root . '/middleware/*.php') as $path) copy($path,$workspace . '/middleware/' . basename($path));
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/views',FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile() && $file->getExtension()==='php') {
            $destination=$workspace . substr($file->getPathname(),strlen($root));
            if (!is_dir(dirname($destination))) mkdir(dirname($destination),0755,true);
            copy($file->getPathname(),$destination);
        }
    }
    foreach (['db.php','app.php','uploads.php'] as $name) copy($root . '/config/' . $name,$workspace . '/config/' . $name);
    $config['database']['name']=$database;
    file_put_contents($workspace . '/config/config.local.php','<?php return ' . var_export($config,true) . ';');
    ini_set('session.save_path', $workspace);
    require $workspace . '/models/Product.php';
    $_SESSION['user']=['id'=>1,'branch_id'=>1];
    require $workspace . '/models/Sale.php';
    $conn->query("INSERT INTO branches (id,name) VALUES (1,'Test branch'),(2,'Other branch')");
    $conn->query("INSERT INTO users (id,name,email,password,branch_id) VALUES (1,'Test cashier','test@invalid.local','not-a-login',1)");
    $conn->query("INSERT INTO categories (id,name) VALUES (1,'Test')");
    $conn->query("INSERT INTO roles (id,name) VALUES (1,'Test cashier')");
    $conn->query("INSERT INTO user_roles (user_id,role_id) VALUES (1,1)");
    foreach (['access_pos','manage_inventory','view_product','edit_product','add_product'] as $permission) {
        RecipeInventory::query('INSERT INTO permissions (name,description,module) VALUES (?,?,?)','sss',[$permission,'Test only','Test']);
        $permissionId=$conn->insert_id;
        RecipeInventory::query('INSERT INTO role_permissions (role_id,permission_id) VALUES (1,?)','i',[$permissionId]);
    }
    function product($name,$type='ingredient',$unit='pc',$mode='legacy') {
        $id=Product::create(1,$name,uniqid(),uniqid(),'',100,1,'0','0','', 'active',0,0,'100000',1,$type,1,$type==='ingredient'?0:1,$type==='ingredient'?1:0,'stocked',null,$unit,1);
        RecipeInventory::query('UPDATE products SET recipe_mode = ? WHERE id = ?','si',[$mode,$id]);
        return $id;
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
    function endpoint($action,$data) {
        global $workspace;
        $pipes=[];
        $process=proc_open([PHP_BINARY,__FILE__,'--endpoint',$workspace,$action,base64_encode(json_encode($data+['csrf_token'=>'test-csrf']))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
        fclose($pipes[0]); $output=stream_get_contents($pipes[1]); $error=stream_get_contents($pipes[2]);
        fclose($pipes[1]); fclose($pipes[2]); proc_close($process);
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
    echo "All {$checks} integration checks passed. Source database was not modified.\n";
} finally {
    if (!preg_match('/^pos_burger_test_[a-f0-9]{12}$/D',$database)) throw new RuntimeException('Unsafe cleanup target.');
    $admin->query('DROP DATABASE `' . $database . '`');
    // Keep no private test configuration behind.
    if (is_file($workspace . '/config/config.local.php')) unlink($workspace . '/config/config.local.php');
}
