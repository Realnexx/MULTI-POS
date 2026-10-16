<?php
// Runs only inside burger-integration.php's disposable database.
require_once $workspace.'/models/StockTransfer.php';
require_once $workspace.'/models/Branch.php';
foreach(['transfer_products','approve_stock_transfers','receive_stock_transfers','view_stock_transfers'] as $workflowPermission) {
    $permission=RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$workflowPermission]);
    if(!$permission) {
        RecipeInventory::query('INSERT INTO permissions(name,description,module) VALUES(?,?,?)','sss',[$workflowPermission,$workflowPermission,'Inventory']);
        $pid=$conn->insert_id;
    } else $pid=$permission[0]['id'];
    RecipeInventory::query('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(1,?),(2,?)','ii',[$pid,$pid]);
}
RecipeInventory::query("INSERT INTO branches(id,name) VALUES(3,'Unrelated branch')");
RecipeInventory::query("INSERT INTO users(id,name,email,password,branch_id) VALUES(3,'Destination receiver','receiver@invalid.local','not-a-login',2),(4,'Unrelated staff','other@invalid.local','not-a-login',3)");
RecipeInventory::query('INSERT INTO user_roles(user_id,role_id) VALUES(3,1),(4,1)');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
Setting::set('stock_transfer_require_approval','1','security');
$wfSource=product('Workflow ingredient source','ingredient','g');
$wfDestination=product('Workflow ingredient source','ingredient','g');
RecipeInventory::query('UPDATE products SET branch_id=2 WHERE id=?','i',[$wfDestination]);
RecipeInventory::adjust($wfSource,1,'stock_in','10','Workflow first lot',1,'FIRST','2.50','2030-01-01','7.50');
RecipeInventory::adjust($wfSource,1,'stock_in','10','Workflow second lot',1,'SECOND','3.50','2031-01-01','8.50');
$wfRows=[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'12.5000']];
$wrongTransferProduct=product('Different ingredient with same unit','ingredient','g');
RecipeInventory::query('UPDATE products SET branch_id=2 WHERE id=?','i',[$wrongTransferProduct]);
reject(function()use($wfSource,$wrongTransferProduct){StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wrongTransferProduct,'quantity'=>'1']],'Wrong match',bin2hex(random_bytes(16)));},'same unit and type cannot transfer stock into a different product');
$wfKey=bin2hex(random_bytes(16));
$wfId=StockTransfer::create('1','2',$wfRows,'Source to destination',$wfKey);
check(RecipeInventory::balance($wfSource,1)===200000 && RecipeInventory::balance($wfDestination,2)===0,'stock request creates no stock movements');
check(StockTransfer::create('1','2',$wfRows,'Source to destination',$wfKey)===$wfId,'request retry returns original transfer');
reject(function()use($wfId){StockTransfer::transition($wfId,'dispatch',bin2hex(random_bytes(16)));},'pending approval cannot be dispatched');
reject(function()use($wfId){StockTransfer::transition($wfId,'approve',bin2hex(random_bytes(16)));},'approval-required request cannot be self-approved');
reject(function()use($wfSource){Product::delete($wfSource);},'open transfer protects source product from archiving');
reject(function()use($wfSource){ProductTransfer::run($wfSource,2,1,bin2hex(random_bytes(16)),'1');},'open transfer protects product ownership');
$_SESSION['user']=['id'=>2,'branch_id'=>1];
$wfApproveKey=bin2hex(random_bytes(16));StockTransfer::transition($wfId,'approve',$wfApproveKey,'Reviewed');StockTransfer::transition($wfId,'approve',$wfApproveKey,'Reviewed');
check(StockTransfer::detail($wfId)['status']==='approved','independent approver approves request with safe retry');
$_SESSION['user']=['id'=>3,'branch_id'=>2];
reject(function()use($wfId){StockTransfer::transition($wfId,'dispatch',bin2hex(random_bytes(16)));},'destination branch cannot dispatch source stock');
reject(function()use($wfId){StockTransfer::transition($wfId,'receive',bin2hex(random_bytes(16)));},'stock cannot be received before dispatch');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$wfDispatchKey=bin2hex(random_bytes(16));StockTransfer::transition($wfId,'dispatch',$wfDispatchKey,'Sent');StockTransfer::transition($wfId,'dispatch',$wfDispatchKey,'Sent');
check(RecipeInventory::balance($wfSource,1)===75000 && RecipeInventory::balance($wfDestination,2)===0,'dispatch deducts source once and keeps destination stock in transit');
$wfDetails=StockTransfer::detail($wfId);$wfItem=$wfDetails['items'][0];
check(count(RecipeInventory::rows('SELECT id FROM stock_transfer_allocations WHERE item_id=?','i',[$wfItem['id']]))===2,'dispatch records original FIFO batch allocations');
reject(function()use($wfId){StockTransfer::transition($wfId,'cancel',bin2hex(random_bytes(16)),'Cancel in transit');},'in-transit stock cannot be cancelled and silently restored');
$_SESSION['user']=['id'=>3,'branch_id'=>2];
reject(function()use($wfId,$wfItem){StockTransfer::transition($wfId,'receive',bin2hex(random_bytes(16)),'',[$wfItem['id']=>['quantity'=>'11.25','reason'=>'']]);},'quantity discrepancy requires a reason');
$wfReceiveKey=bin2hex(random_bytes(16));$wfReceived=[$wfItem['id']=>['quantity'=>'11.25','reason'=>'1.25 g damaged in transit']];
StockTransfer::transition($wfId,'receive',$wfReceiveKey,'Counted at destination',$wfReceived);
StockTransfer::transition($wfId,'receive',$wfReceiveKey,'Counted at destination',$wfReceived);
check(RecipeInventory::balance($wfDestination,2)===112500 && RecipeInventory::balance($wfSource,1)===75000,'receiving adds actual quantity once without changing source again');
$wfDetails=StockTransfer::detail($wfId);
check($wfDetails['status']==='completed_discrepancy' && $wfDetails['items'][0]['discrepancy_quantity']==='-1.2500' && count($wfDetails['events'])===4,'completed discrepancies retain requested dispatched received quantities and four audit events');
check(count(RecipeInventory::rows("SELECT id FROM activity_logs WHERE action LIKE 'Stock Transfer %' AND description LIKE ?",'s',['Transfer #'.$wfId.';%']))===4,'transfer transitions also appear once each in existing activity logs');
$wfLots=RecipeInventory::rows('SELECT quantity,cost_price,selling_price,expiry_date FROM product_batches WHERE product_id=? ORDER BY id','i',[$wfDestination]);
check($wfLots[0]['quantity']==='10.0000' && (float)$wfLots[0]['cost_price']===2.5 && $wfLots[0]['expiry_date']==='2030-01-01' && $wfLots[1]['quantity']==='1.2500' && (float)$wfLots[1]['cost_price']===3.5,'receiving preserves each source lot cost price expiry and partial quantities');
check((int)RecipeInventory::product($wfSource)['branch_id']===1 && (int)RecipeInventory::product($wfDestination)['branch_id']===2,'quantity transfers never reassign product ownership');
$_SESSION['user']=['id'=>4,'branch_id'=>3];
reject(function()use($wfId){StockTransfer::detail($wfId);},'unrelated branch cannot view transfer details');
check(count(StockTransfer::listing())===0,'unrelated branch cannot list transfer history');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
Setting::set('stock_transfer_require_approval','0','security');
$wfAuto=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'No approval policy',bin2hex(random_bytes(16)));
check(StockTransfer::detail($wfAuto)['status']==='approved','approval policy can permit new requests without an approver');
StockTransfer::transition($wfAuto,'dispatch',bin2hex(random_bytes(16)));
$_SESSION['user']=['id'=>3,'branch_id'=>2];$wfAutoItem=StockTransfer::detail($wfAuto)['items'][0];
StockTransfer::transition($wfAuto,'receive',bin2hex(random_bytes(16)),'',[$wfAutoItem['id']=>['quantity'=>'1.5','reason'=>'Extra stock found and counted']]);
check(StockTransfer::detail($wfAuto)['items'][0]['discrepancy_quantity']==='0.5000','excess received quantity is recorded as a positive documented discrepancy');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$wfCancel=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'Cancel me',bin2hex(random_bytes(16)));
$wfBefore=RecipeInventory::balance($wfSource,1);StockTransfer::transition($wfCancel,'cancel',bin2hex(random_bytes(16)),'No longer needed');
check(StockTransfer::detail($wfCancel)['status']==='cancelled' && RecipeInventory::balance($wfSource,1)===$wfBefore,'cancelling an undispatched request preserves stock');
$wfShort=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'999']],'Short stock',bin2hex(random_bytes(16)));
reject(function()use($wfShort){StockTransfer::transition($wfShort,'dispatch',bin2hex(random_bytes(16)));},'dispatch shortage rejects entire transfer');
check(RecipeInventory::balance($wfSource,1)===$wfBefore && StockTransfer::detail($wfShort)['status']==='approved' && count(RecipeInventory::rows('SELECT a.id FROM stock_transfer_allocations a JOIN stock_transfer_items i ON i.id=a.item_id WHERE i.transfer_id=?','i',[$wfShort]))===0,'failed dispatch rolls back stock allocations and status');
Setting::set('stock_transfer_require_approval','1','security');
$wfReject=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'Reject me',bin2hex(random_bytes(16)));
$_SESSION['user']=['id'=>2,'branch_id'=>1];StockTransfer::transition($wfReject,'reject',bin2hex(random_bytes(16)),'Not approved');
check(StockTransfer::detail($wfReject)['status']==='rejected','rejection retains a reason and closes request without stock changes');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$workflowEndpoint=endpoint('inventory_action',['action'=>'stock_transfer_create','source_branch_id'=>'1','destination_branch_id'=>'2','items'=>[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'request_key'=>bin2hex(random_bytes(16))]);
check($workflowEndpoint['success'] && strpos($workflowEndpoint['redirect'],'inventory/transfers')!==false,'existing inventory AJAX endpoint creates workflow requests');
foreach(['transfer_products'=>'stock_transfer_create','approve_stock_transfers'=>'stock_transfer_approve','receive_stock_transfers'=>'stock_transfer_receive'] as $permission=>$action) {
    $pid=(int)RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$permission])[0]['id'];
    RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$pid]);
    $denied=endpoint('inventory_action',['action'=>$action,'transfer_id'=>$wfId]);
    check(empty($denied['success']) && ($denied['message'] ?? '')==='You do not have access to this action.','workflow permission toggle blocks '.$action.' directly');
    RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$pid]);
}
Setting::set('stock_transfer_require_approval','0','security');
$wfUi=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'Receiving UI fixture',bin2hex(random_bytes(16)));
StockTransfer::transition($wfUi,'dispatch',bin2hex(random_bytes(16)));
$wfPending=(int)RecipeInventory::rows("SELECT id FROM stock_transfers WHERE status='pending_approval' ORDER BY id DESC LIMIT 1")[0]['id'];
foreach(['render_transfers'=>$wfShort,'render_transfers_receiver'=>$wfUi,'render_transfers_admin'=>$wfPending] as $renderMode=>$renderId) {
    $pipes=[];$process=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,$renderMode,base64_encode(json_encode(['transfer_id'=>$renderId]))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
    fclose($pipes[0]);$renderHtml=stream_get_contents($pipes[1]);$renderErrors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($process);
    file_put_contents($root.'/scratch/'.$renderMode.'.html',$renderHtml);
    check(strpos($renderHtml,'stock-transfer-list')!==false && !preg_match('/Warning:|Notice:|Fatal error/',$renderHtml.$renderErrors),$renderMode.' renders transfer list actions and audit details without warnings');
}
check(Branch::delete(1)===false && (int)RecipeInventory::rows('SELECT branch_id FROM users WHERE id=1')[0]['branch_id']===1,'branches with transfer history cannot be deleted or detach staff');
$wfRecipeSource=product('Prepared transfer source','recipe','pc','prepared_batch');
$wfRecipeDestination=product('Prepared transfer source','recipe','pc','prepared_batch');
RecipeInventory::query('UPDATE products SET branch_id=2 WHERE id=?','i',[$wfRecipeDestination]);
Product::save_recipes($wfRecipeSource,[['ingredient_id'=>$wfSource,'quantity'=>'1','unit'=>'g']]);
$_SESSION['user']=['id'=>3,'branch_id'=>2];Product::save_recipes($wfRecipeDestination,[['ingredient_id'=>$wfDestination,'quantity'=>'1','unit'=>'g']]);
$_SESSION['user']=['id'=>1,'branch_id'=>1];RecipeInventory::prepare($wfRecipeSource,2,1,1,bin2hex(random_bytes(16)));
$wfIngredientBefore=RecipeInventory::balance($wfSource,1);$wfDestIngredientBefore=RecipeInventory::balance($wfDestination,2);
$wfPrepared=StockTransfer::create(1,2,[['source_product_id'=>$wfRecipeSource,'destination_product_id'=>$wfRecipeDestination,'quantity'=>'1']],'Prepared stock',bin2hex(random_bytes(16)));
StockTransfer::transition($wfPrepared,'dispatch',bin2hex(random_bytes(16)));
$_SESSION['user']=['id'=>3,'branch_id'=>2];$preparedLine=StockTransfer::detail($wfPrepared)['items'][0];StockTransfer::transition($wfPrepared,'receive',bin2hex(random_bytes(16)),'',[$preparedLine['id']=>['quantity'=>'1','reason'=>'']]);
check(RecipeInventory::balance($wfRecipeSource,1)===10000 && RecipeInventory::balance($wfRecipeDestination,2)===10000 && RecipeInventory::balance($wfSource,1)===$wfIngredientBefore && RecipeInventory::balance($wfDestination,2)===$wfDestIngredientBefore,'prepared recipe transfer moves finished stock without consuming any ingredients again');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$wfZero=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'Lost in transit',bin2hex(random_bytes(16)));
StockTransfer::transition($wfZero,'dispatch',bin2hex(random_bytes(16)));
$_SESSION['user']=['id'=>3,'branch_id'=>2];$zeroLine=StockTransfer::detail($wfZero)['items'][0];$beforeZero=RecipeInventory::balance($wfDestination,2);
StockTransfer::transition($wfZero,'receive',bin2hex(random_bytes(16)),'',[$zeroLine['id']=>['quantity'=>'0','reason'=>'Entire shipment lost']]);
check(RecipeInventory::balance($wfDestination,2)===$beforeZero && StockTransfer::detail($wfZero)['status']==='completed_discrepancy','zero receiving records a complete loss without inventing destination stock');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$wfFailure=StockTransfer::create(1,2,[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'1']],'Receiving rollback fixture',bin2hex(random_bytes(16)));
StockTransfer::transition($wfFailure,'dispatch',bin2hex(random_bytes(16)));
$_SESSION['user']=['id'=>3,'branch_id'=>2];$failureLine=StockTransfer::detail($wfFailure)['items'][0];$beforeFailure=RecipeInventory::balance($wfDestination,2);
$conn->query("CREATE TRIGGER fail_workflow_receipt BEFORE INSERT ON product_batches FOR EACH ROW BEGIN IF NEW.product_id=".(int)$wfDestination." THEN SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='Injected workflow receiving failure'; END IF; END");
try {StockTransfer::transition($wfFailure,'receive',bin2hex(random_bytes(16)),'',[$failureLine['id']=>['quantity'=>'1','reason'=>'']]);throw new RuntimeException('Receiving failure not injected');}catch(mysqli_sql_exception $error){}
$conn->query('DROP TRIGGER fail_workflow_receipt');
check(RecipeInventory::balance($wfDestination,2)===$beforeFailure && StockTransfer::detail($wfFailure)['status']==='dispatched' && StockTransfer::detail($wfFailure)['items'][0]['received_quantity']===null,'failed receiving rolls back destination stock quantities and completion status');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
Setting::set('stock_transfer_require_approval','1','security');
$httpRequest=endpoint('inventory_action',['action'=>'stock_transfer_create','source_branch_id'=>1,'destination_branch_id'=>2,'items'=>[['source_product_id'=>$wfSource,'destination_product_id'=>$wfDestination,'quantity'=>'0.25']],'notes'=>'HTTP lifecycle','request_key'=>bin2hex(random_bytes(16))]);
preg_match('/id=([0-9]+)/',$httpRequest['redirect'],$httpMatch);$httpId=(int)$httpMatch[1];
$httpApproved=endpoint('inventory_action_admin',['action'=>'stock_transfer_approve','transfer_id'=>$httpId,'request_key'=>bin2hex(random_bytes(16))]);
$httpDispatched=endpoint('inventory_action',['action'=>'stock_transfer_dispatch','transfer_id'=>$httpId,'request_key'=>bin2hex(random_bytes(16))]);
$httpLine=StockTransfer::detail($httpId)['items'][0];
$httpReceived=endpoint('inventory_action_receiver',['action'=>'stock_transfer_receive','transfer_id'=>$httpId,'request_key'=>bin2hex(random_bytes(16)),'received'=>[$httpLine['id']=>['quantity'=>'0.25','reason'=>'']]]);
check($httpRequest['success'] && $httpApproved['success'] && $httpDispatched['success'] && $httpReceived['success'] && StockTransfer::detail($httpId)['status']==='completed','actual AJAX request approval dispatch and destination receiving endpoints complete the full lifecycle');
