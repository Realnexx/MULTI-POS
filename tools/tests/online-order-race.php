<?php
if(PHP_SAPI!=='cli')exit;session_save_path(__DIR__.'/../../scratch');require __DIR__.'/../../config/db.php';require __DIR__.'/../../models/OnlineOrder.php';require __DIR__.'/../../models/Product.php';
$s=json_decode(file_get_contents(__DIR__.'/../../scratch/rbac-test-state.json'),true);if(!preg_match('/^pos_rbac_test_[a-f0-9]+$/',$s['database']))exit(1);$conn->select_db($s['database']);
$mode=$argv[1] ?? 'setup';
if($mode==='setup'){
 $_SESSION['user']=['id'=>$s['users']['super'],'branch_id'=>501];
 RecipeInventory::query("INSERT INTO products(id,category_id,name,product_type,unit_type,branch_id,price) VALUES (906,701,'Last shared drink','simple','pc',501,50),(907,701,'Second fresh burger','recipe','pc',501,100)");RecipeInventory::query("UPDATE products SET recipe_mode='made_to_order' WHERE id=907");
 Product::save_recipes(907,[['ingredient_id'=>901,'quantity'=>'0.1','unit'=>'kg'],['ingredient_id'=>902,'quantity'=>'1','unit'=>'pc']]);
 RecipeInventory::adjust(906,$s['users']['super'],'stock_in','1','Last stock',501,null,null,null,null,null,bin2hex(random_bytes(32)));
 Cart::add(906,1,'race-cart',$s['users']['customer']);
 echo "Concurrency fixtures ready.\n";exit;
}
if($mode==='verify'){
 if(RecipeInventory::balance(906,501)!==0)throw new Exception('Last stock must be sold exactly once');
 $consumed=RecipeInventory::rows('SELECT SUM(quantity) quantity FROM inventory_movements WHERE product_id=906 AND quantity<0')[0]['quantity'];if((float)$consumed!==-1.0)throw new Exception('Duplicate stock consumption');
 // Check combined requirements across different recipes sharing the same ingredients.
 $customer=$s['users']['customer'];$_SESSION['user']=['id'=>$customer];Cart::clear('shared-shortage',$customer);$available=Cart::available(RecipeInventory::product(904),501);Cart::add(904,$available,'shared-shortage',$customer);Cart::add(907,1,'shared-shortage',$customer);$before=RecipeInventory::balance(901,501);
 try{OnlineOrder::place($customer,'shared-shortage',501,'Combined','customer@rbac.invalid','09123456789',bin2hex(random_bytes(32)));throw new Exception('Shared recipe shortages were accepted');}catch(DomainException $e){}
 if(RecipeInventory::balance(901,501)!==$before || count(Cart::get_items('shared-shortage',$customer))<2)throw new Exception('Shortage did not roll back');Cart::clear('shared-shortage',$customer);
 // Staff branch access is derived from the database, not a posted branch or stale session.
 $tracking=json_decode(file_get_contents(__DIR__.'/../../scratch/online-order-test-state.json'),true)['tracking'];$cashier=$s['users']['cashier'];$conn->query('UPDATE users SET branch_id=502 WHERE id='.$cashier);$_SESSION['user']=['id'=>$cashier,'branch_id'=>501];if(OnlineOrder::detail($tracking,$cashier,true)!==null)throw new Exception('Cross-branch order leaked');$conn->query('UPDATE users SET branch_id=501 WHERE id='.$cashier);
 echo "PASS concurrent POS/online last-stock isolation, combined recipe shortages/rollback and branch privacy\n";exit;
}
$ready=__DIR__.'/../../scratch/order-race-'.$mode.'.ready';file_put_contents($ready,'ready');$start=__DIR__.'/../../scratch/order-race.start';$deadline=microtime(true)+15;while(!is_file($start)&&microtime(true)<$deadline)usleep(1000);
try{
 if($mode==='online'){$_SESSION['user']=['id'=>$s['users']['customer']];OnlineOrder::place($s['users']['customer'],'race-cart',501,'Race','customer@rbac.invalid','09123456789',bin2hex(random_bytes(32)));}
 else{$_SESSION['user']=['id'=>$s['users']['cashier'],'branch_id'=>501];RecipeInventory::checkout($s['users']['cashier'],501,[['product_id'=>906,'quantity'=>1]],0,50,'Cash','Take-out',bin2hex(random_bytes(32)));}
 echo 'SUCCESS';
}catch(DomainException $e){echo 'REJECTED';}
