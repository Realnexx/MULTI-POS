<?php
require_once $workspace.'/models/ProductBundle.php';
$conn->query("INSERT INTO users(id,name,email,password,branch_id) VALUES(2,'Bundle administrator','bundle-admin@invalid.local','not-a-login',1)");$conn->query("INSERT INTO roles(id,name) VALUES(2,'Admin')");$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES(2,2)');
RecipeInventory::query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT 2,id FROM permissions WHERE name IN ('view_product','add_product','edit_product','manage_inventory','prepare_recipe_stock','access_pos','refund_sales','approve_discount_overrides')");
$_SESSION['user']=['id'=>2,'branch_id'=>1];
$ice=product('Bundle ice','ingredient','g');$milk=product('Bundle milk','ingredient','ml');$pizzaRaw=product('Pizza raw');$siomai=product('Siomai','simple');$halo=product('Halo halo','recipe','pc','made_to_order');$pizza=product('Prepared pizza','recipe','pc','prepared_batch');$bundle=product('Fiesta package','combo');
RecipeInventory::query("UPDATE products SET price=750,track_stock=0 WHERE id=?",'i',[$bundle]);
Product::adjust_stock($ice,2,'stock_in','100','Fixture',null,'2',1);Product::adjust_stock($milk,2,'stock_in','1000','Fixture',null,'1',1);Product::adjust_stock($pizzaRaw,2,'stock_in','20','Fixture',null,'5',1);Product::adjust_stock($siomai,2,'stock_in','10','Fixture',null,'25',1);
Product::save_recipes($halo,[['ingredient_id'=>$ice,'quantity'=>'1','unit'=>'g'],['ingredient_id'=>$milk,'quantity'=>'50','unit'=>'ml']]);Product::save_recipes($pizza,[['ingredient_id'=>$pizzaRaw,'quantity'=>'1','unit'=>'pc']]);RecipeInventory::prepare($pizza,10,2,1,bin2hex(random_bytes(16)));
$contents=[['item_product_id'=>$halo,'quantity'=>'3','station'=>'Dessert'],['item_product_id'=>$pizza,'quantity'=>'2','station'=>'Oven'],['item_product_id'=>$siomai,'quantity'=>'1','station'=>'Steamer']];ProductBundle::save($bundle,$contents);
check(count(ProductBundle::items($bundle))===3,'Package saves distinct sellable components and preparation stations');
check(RecipeInventory::availability(RecipeInventory::product($bundle),1)===5,'Bundle capacity uses the limiting prepared pizza component');
$inlineReport=RecipeInventory::capacityReport($bundle,1);$inlineBranch=$inlineReport['branches'][0];
check($inlineReport['mode']==='combo'&&$inlineBranch['capacity']===5&&count($inlineBranch['ingredients'])===4,'Inline package validation receives branch-scoped component stock requirements');
$inlinePizza=array_values(array_filter($inlineBranch['ingredients'],function($item){return $item['name']==='Prepared pizza';}))[0];
check((float)$inlinePizza['per_unit']*6-(float)$inlinePizza['available']===2.0,'Inline package validation can show the exact missing finished quantity for requested packages');
$quote=RecipeInventory::quoteItem($bundle,1,[],1);check($quote['needs'][$ice]===30000&&$quote['needs'][$milk]===1500000&&$quote['needs'][$pizza]===20000&&$quote['needs'][$siomai]===10000,'Bundle expands made-to-order raw ingredients and prepared/simple stock quantities');
$key=bin2hex(random_bytes(16));$sale=RecipeInventory::checkout(2,1,[['product_id'=>$bundle,'quantity'=>1]],'0','750','Cash','Dine-in',$key);
check(RecipeInventory::balance($ice,1)===970000&&RecipeInventory::balance($milk,1)===8500000&&RecipeInventory::balance($pizza,1)===80000&&RecipeInventory::balance($siomai,1)===90000,'One bundle sale deducts each component exactly once');
check(RecipeInventory::balance($pizzaRaw,1)===100000,'Selling prepared pizza in a bundle does not consume its raw ingredient again');
check(RecipeInventory::rows('SELECT final_amount FROM sales WHERE id=?','i',[$sale])[0]['final_amount']==='750.00','Bundle charges its fixed package price rather than component retail prices');
$cost=(float)RecipeInventory::rows('SELECT SUM(-quantity*unit_cost) cost FROM inventory_movements WHERE sale_item_id IN (SELECT id FROM sales_items WHERE sale_id=?)','i',[$sale])[0]['cost'];check(abs($cost-191)<0.0001,'Bundle COGS records actual consumed batch costs for gross profit');
check(RecipeInventory::checkout(2,1,[['product_id'=>$bundle,'quantity'=>1]],'0','750','Cash','Dine-in',$key)===$sale&&RecipeInventory::balance($pizza,1)===80000,'Bundle checkout retries are idempotent');
$receipt=CheckoutPolicy::forSale($sale);check(count($receipt['bundle_contents'][$bundle])===3&&$receipt['bundle_contents'][$bundle][0]['station']==='Dessert','Receipt snapshots preserve package contents and preparation stations');
RecipeInventory::query('UPDATE products SET name=? WHERE id=?','si',['Renamed item',$halo]);check(Sale::find_sale_by_id($sale)['items'][0]['bundle_components'][0]['name']==='Halo halo','Printed bundle contents preserve the sold name after product edits');
reject(function()use($sale){RecipeInventory::reverseSale($sale,'return_finished',2,bin2hex(random_bytes(16)));},'Mixed made-to-order bundle cannot be refunded as raw stock after preparation');
RecipeInventory::reverseSale($sale,'void_before_preparation',2,bin2hex(random_bytes(16)));check(RecipeInventory::balance($ice,1)===1000000&&RecipeInventory::balance($pizza,1)===100000,'Before-preparation void restores the bundle original raw and finished batches');
$shared=product('Shared-demand package','combo');RecipeInventory::query('UPDATE products SET track_stock=0 WHERE id=?','i',[$shared]);$halo2=product('Second raw recipe','recipe','pc','made_to_order');Product::save_recipes($halo2,[['ingredient_id'=>$milk,'quantity'=>'100','unit'=>'ml']]);ProductBundle::save($shared,[['item_product_id'=>$halo,'quantity'=>1],['item_product_id'=>$halo2,'quantity'=>1]]);check(RecipeInventory::availability(RecipeInventory::product($shared),1)===6,'Capacity aggregates ingredients shared by different package components');
reject(function()use($bundle){ProductBundle::save($bundle,[['item_product_id'=>$bundle,'quantity'=>1]]);},'Self-referencing packages are rejected');reject(function()use($bundle,$shared){ProductBundle::save($bundle,[['item_product_id'=>$shared,'quantity'=>1]]);},'Nested packages and cycles are rejected');reject(function()use($bundle,$halo){ProductBundle::save($bundle,[['item_product_id'=>$halo,'quantity'=>'1.5']]);},'Fractional sellable component quantities are rejected');
$other=product('Other branch item','simple');RecipeInventory::query('UPDATE products SET branch_id=2 WHERE id=?','i',[$other]);reject(function()use($bundle,$other){ProductBundle::save($bundle,[['item_product_id'=>$other,'quantity'=>1]]);},'Cross-branch components are rejected');
reject(function()use($bundle,$ice){ProductBundle::save($bundle,[['item_product_id'=>$ice,'quantity'=>1]]);},'Raw ingredients cannot be selected as sellable package items');
$before=RecipeInventory::balance($milk,1);reject(function()use($bundle){RecipeInventory::checkout(2,1,[['product_id'=>$bundle,'quantity'=>6]],'0','4500','Cash','Dine-in',bin2hex(random_bytes(16)));},'Component shortage blocks bundle checkout');check(RecipeInventory::balance($milk,1)===$before,'Failed bundle sale cannot partially deduct ingredients');
reject(function()use($bundle){Product::adjust_stock($bundle,2,'stock_in','2','Invalid package batch',null,null,1);},'Physical stock cannot be added directly to virtual packages');
$finishedBundle=product('Finished pair','combo');RecipeInventory::query('UPDATE products SET price=100,track_stock=0 WHERE id=?','i',[$finishedBundle]);ProductBundle::save($finishedBundle,[['item_product_id'=>$pizza,'quantity'=>1],['item_product_id'=>$siomai,'quantity'=>1]]);
$finishedSale=RecipeInventory::checkout(2,1,[['product_id'=>$finishedBundle,'quantity'=>1]],'0','100','Cash','Dine-in',bin2hex(random_bytes(16)));RecipeInventory::reverseSale($finishedSale,'return_finished',2,bin2hex(random_bytes(16)));
check(RecipeInventory::balance($pizza,1)===100000&&RecipeInventory::balance($siomai,1)===100000,'Finished-only package returns restore original finished product batches');
check(!RecipeInventory::rows('SELECT id FROM product_batches WHERE product_id=?','i',[$bundle]),'Package parent has no physical stock batches');
$available=ProductBundle::candidates(1,$bundle);$availableIds=array_map('intval',array_column($available,'id'));check(in_array($halo,$availableIds,true)&&!in_array($other,$availableIds,true)&&!in_array($ice,$availableIds,true)&&!in_array($shared,$availableIds,true),'Editor suggestions exclude other branches, ingredients and nested packages');
$sale=Sale::find_sale_by_id($finishedSale);ob_start();include $root.'/views/partials/preparation_ticket.php';$ticket=ob_get_clean();check(strpos($ticket,'Finished pair')!==false&&strpos($ticket,'General')!==false&&!preg_match('/Warning:|Notice:/',$ticket),'Preparation ticket renders grouped package contents without warnings');
reject(function()use($siomai){Product::delete($siomai);},'Products referenced by active packages cannot be deleted');
$unconfigured=product('Unconfigured burger','recipe','pc','made_to_order');$brokenPackage=product('Incomplete family package','combo');ProductBundle::save($brokenPackage,[['item_product_id'=>$unconfigured,'quantity'=>2]]);
$setupReport=RecipeInventory::capacityReport($brokenPackage,1);
check(strpos($setupReport['branches'][0]['problem'],'Included product "Unconfigured burger"')!==false&&strpos($setupReport['branches'][0]['problem'],'no recipe')!==false,'Package setup error identifies the included product whose recipe is missing');
RecipeInventory::query("DELETE rp FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=2 AND p.name='edit_product'");reject(function()use($bundle,$contents){ProductBundle::save($bundle,$contents);},'Bundle editing enforces database product permissions');

// History must follow transaction ownership, even if the cashier moves branches.
$_SESSION['user']=['id'=>2,'branch_id'=>1];
RecipeInventory::query('UPDATE sales SET branch_id=2 WHERE id=?','i',[$finishedSale]);
$branchName=RecipeInventory::rows('SELECT name FROM branches WHERE id=2')[0]['name'];
$historyFilter=['locations'=>[$branchName]];
$historyRows=Sale::get_filtered_sales($historyFilter,1,100);
check(count($historyRows)===1&&(int)$historyRows[0]['id']===$finishedSale&&Sale::get_filtered_sales_count($historyFilter)===1,'History location filters use sale branch rather than cashier legacy branch');
check(Sale::get_filtered_stats($historyFilter)['orders']===1,'History totals and rows use identical branch filters');
check(Sale::get_filtered_stats(['search'=>'NoSuchCashierHistory'])['orders']===0&&Sale::get_filtered_sales_count(['search'=>'NoSuchCashierHistory'])===0,'History search applies equally to KPI totals and record counts');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$staffRows=Sale::get_filtered_sales(['locations'=>[$branchName],'branch_id'=>2],1,100);
check(count($staffRows)>0&&count(array_filter($staffRows,function($r){return (int)$r['branch_id']!==1;}))===0,'Forged history branch and location filters cannot expose another branch to staff');
check(Sale::get_filtered_stats()['orders']===Sale::get_filtered_sales_count(),'Staff history KPIs and pagination count share the same branch scope');
RecipeInventory::query('UPDATE users SET branch_id=NULL WHERE id=1');
check(Sale::get_filtered_sales_count()===0&&Sale::get_filtered_stats()['orders']===0,'Unassigned staff history fails closed');
$_SESSION['user']=['id'=>2,'branch_id'=>1];
check(Sale::get_filtered_sales_count()===(int)RecipeInventory::rows('SELECT COUNT(*) n FROM sales')[0]['n'],'Administrator can oversee sales across branches despite having an assigned branch');

// Inventory reports rank only completed sales and keep zero-sales branches.
$ranked = Sale::get_top_branches_by_sales(100);
foreach ($ranked as $rankedRow) {
    $expected = RecipeInventory::rows("SELECT COALESCE(SUM(final_amount),0) revenue FROM sales WHERE status='Completed' AND branch_id=?",'i',[(int)$rankedRow['id']])[0]['revenue'];
    check(abs((float)$rankedRow['total_revenue']-(float)$expected)<0.001,'Branch leaderboard excludes pending, refunded and cancelled revenue');
}
$emptyPeriod = Sale::get_top_branches_by_sales(100,null,'2099-01-01','2099-01-02');
check(count($emptyPeriod)===count($ranked) && array_sum(array_column($emptyPeriod,'total_revenue'))==0,'Ranking retains every active branch when the selected period has no sales');
