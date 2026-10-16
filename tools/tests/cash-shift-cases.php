<?php
require_once $workspace.'/models/CashShift.php';
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$csKey=static function(){return bin2hex(random_bytes(16));};
PermissionSeeder::syncInventoryActions($conn);
check(User::has_permission(1,'open_cash_shift') && User::has_permission(1,'close_cash_shift') && User::has_permission(1,'view_own_shifts'),'existing POS role receives own-shift opening viewing and closing permissions');
check(!User::has_permission(1,'approve_cash_shifts') && !User::has_permission(1,'cash_drawer_movements'),'POS access alone does not grant shift approval or cash movement privileges');
$csCatalog=require $workspace.'/config/permissions.php';
foreach ($csCatalog as $name=>$info) if ($info[1]==='Cashier Shifts') {
    $pid=RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$name])[0]['id'];
    RecipeInventory::query('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(1,?),(2,?)','ii',[$pid,$pid]);
}
CashShift::register(0,1,'Drawer A','active',$csKey()); CashShift::register(0,1,'Drawer B','active',$csKey());
$csRegisters=CashShift::registers(1); $csA=(int)$csRegisters[0]['id']; $csB=(int)$csRegisters[1]['id'];
reject(function()use($csKey){CashShift::register(0,2,'Foreign register','active',$csKey());},'register administration enforces branch assignment');
CashShift::configure(1,'1','1',$csKey());
$csProduct=product('Shift tracked item','simple','pc'); RecipeInventory::query('UPDATE products SET price=10 WHERE id=?','i',[$csProduct]); RecipeInventory::adjust($csProduct,1,'stock_in','100','Shift fixture',1);
reject(function()use($csProduct,$csKey){RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','Cash','Dine-in',$csKey());},'mandatory shift policy rejects checkout with no active drawer');
check(RecipeInventory::balance($csProduct,1)===1000000,'blocked shiftless checkout does not consume stock');
$csOpenKey=$csKey(); $csShift=CashShift::open($csA,1,'1000',$csOpenKey);
check(CashShift::open($csA,1,'1000',$csOpenKey)===$csShift,'opening retry returns the same shift');
reject(function()use($csB,$csKey){CashShift::open($csB,1,'10',$csKey());},'cashier cannot open two active shifts');
$_SESSION['user']=['id'=>2,'branch_id'=>1];
reject(function()use($csA,$csKey){CashShift::open($csA,2,'10',$csKey());},'a register cannot have two active cashier shifts');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$csSaleKey=$csKey();
$csSale=RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>2]],'0','100','Cash','Dine-in',$csSaleKey);
RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>2]],'0','100','Cash','Dine-in',$csSaleKey);
$csElectronic=RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','GCash','Dine-in',$csKey());
$csT=CashShift::detail($csShift)['totals'];
check($csT['cash_sales_cents']===2000 && $csT['electronic_sales_cents']===1000 && $csT['expected_cents']===102000,'shift cash sales use net sale amount after change and exclude electronic payments');
check(count(CashShift::detail($csShift)['entries'])===2,'checkout retries cannot duplicate drawer sales');
$csMoveKey=$csKey(); CashShift::move($csShift,'cash_in','in','50','Cash replenishment',$csMoveKey); CashShift::move($csShift,'cash_in','in','50','Cash replenishment',$csMoveKey);
CashShift::move($csShift,'cash_out','out','30','Petty cash voucher',$csKey());
check(CashShift::detail($csShift)['totals']['expected_cents']===104000,'cash in and out update expected cash once');
reject(function()use($csShift,$csKey){CashShift::move($csShift,'cash_out','out','2000','Overdraw',$csKey());},'cash-out cannot overdraw the drawer');
reject(function()use($csShift,$csKey){CashShift::move($csShift,'cash_in','in','1','',$csKey());},'cash movements require an audit reason');
check(Sale::update_status($csSale,'Refunded') && Sale::update_status($csSale,'Refunded'),'cash sale refund and retry complete');
Sale::update_status($csElectronic,'Refunded');
$csT=CashShift::detail($csShift)['totals'];
check($csT['cash_refunds_cents']===-2000 && $csT['electronic_refunds_cents']===-1000 && $csT['expected_cents']===102000,'cash refunds reduce drawer cash and electronic refunds stay separate');
check(RecipeInventory::balance($csProduct,1)===970000,'financial refunds do not restore food stock');
CashShift::move($csShift,'correction','out','5','Correction for previously misstated cash-in voucher',$csKey());
$csLaterSale=RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','Cash','Dine-in',$csKey());
check(CashShift::detail($csShift)['totals']['expected_cents']===102500,'audited correction adjusts cash without overwriting prior entries');
reject(function()use($csShift,$csKey){CashShift::close($csShift,'1020','',$csKey());},'closing discrepancy requires an explanation');
check(CashShift::detail($csShift)['status']==='open','rejected closing leaves shift open');
$csCloseKey=$csKey(); CashShift::close($csShift,'1020','Five pesos short after recount',$csCloseKey); CashShift::close($csShift,'1020','Five pesos short after recount',$csCloseKey);
$csClosed=CashShift::detail($csShift);
check(RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>2]],'0','100','Cash','Dine-in',$csSaleKey)===$csSale,'retrying a previously completed checkout after closing cannot create a new drawer payment');
check($csClosed['status']==='pending_review' && $csClosed['expected_cash']==='1025.00' && $csClosed['difference']==='-5.00' && $csClosed['active_register']===null,'closing freezes expected cash records shortage and releases the register');
reject(function()use($csShift,$csKey){CashShift::move($csShift,'cash_in','in','10','Late cash entry',$csKey());},'closed drawer ledgers reject further adjustments');
reject(function()use($csShift,$csKey){CashShift::review($csShift,'approve','','Self approval',$csKey());},'cashier cannot approve their own closing');
reject(function()use($csLaterSale){Sale::update_status($csLaterSale,'Refunded');},'refund of a tracked sale requires a current active drawer');
check(RecipeInventory::rows('SELECT status FROM sales WHERE id=?','i',[$csLaterSale])[0]['status']==='Completed','failed refund preserves the original sale status');
$_SESSION['user']=['id'=>2,'branch_id'=>1];
CashShift::review($csShift,'recount','1025','Verified count from sealed cash bag',$csKey());
$csRecount=CashShift::detail($csShift); $csRecountEvents=array_values(array_filter($csRecount['events'],static function($e){return $e['action']==='recount';}));
check($csRecount['counted_cash']==='1025.00' && $csRecount['difference']==='0.00' && json_decode($csRecountEvents[0]['details'],true)['previous_count']==='1020.00','manager recount preserves previous figures in the audit trail');
$csApproveKey=$csKey(); CashShift::review($csShift,'approve','','Cash bag verified',$csApproveKey); CashShift::review($csShift,'approve','','Cash bag verified',$csApproveKey);
check(CashShift::detail($csShift)['status']==='approved','independent approval is idempotent');
reject(function()use($csShift,$csKey){CashShift::review($csShift,'recount','1000','After approval',$csKey());},'approved closing summaries are immutable');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
CashShift::configure(1,'1','0',$csKey()); $csNew=CashShift::open($csA,1,'100',$csKey());
Sale::update_status($csLaterSale,'Refunded');
check(CashShift::detail($csNew)['totals']['expected_cents']===9000 && CashShift::detail($csShift)['expected_cash']==='1025.00','late refund debits the current drawer without rewriting the original closing');
CashShift::close($csNew,'90','Balanced current drawer',$csKey());
check(CashShift::detail($csNew)['status']==='closed','branch policy can close a new shift without manager approval');
// Concurrent cash-out requests must read the latest balance after waiting on the drawer lock.
$csRace=CashShift::open($csA,1,'10',$csKey());
$csWorkers=[];
foreach ([1,2] as $n) { $pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--shift-worker',$workspace,'out',base64_encode(json_encode(['shift'=>$csRace,'key'=>$csKey()]))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$csWorkers[]=[$p,$pipes]; }
$csSuccess=0; foreach($csWorkers as [$p,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);if($out==='SUCCESS')$csSuccess++;}
check($csSuccess===1 && CashShift::detail($csRace)['totals']['expected_cents']===300,'concurrent cash-outs cannot overdraw a shared drawer');
CashShift::close($csRace,'3','Concurrent cash-out verified',$csKey());
// Checkout and close serialize through the cashier and shift locks.
$csRaceClose=CashShift::open($csA,1,'100',$csKey()); $csWorkers=[]; $csBeforeStock=RecipeInventory::balance($csProduct,1);
foreach (['sale','close'] as $action) { $pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--shift-worker',$workspace,$action,base64_encode(json_encode(['shift'=>$csRaceClose,'product'=>$csProduct,'key'=>$csKey()]))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$csWorkers[]=[$action,$p,$pipes]; }
foreach($csWorkers as [$action,$p,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);if($action==='close' && $out!=='SUCCESS')throw new RuntimeException('Concurrent closing failed: '.$out.$err);}
$csRaceClosed=CashShift::detail($csRaceClose);$csRaceSales=(int)RecipeInventory::rows("SELECT COUNT(*) n FROM cash_drawer_entries WHERE shift_id=? AND kind='cash_sale'",'i',[$csRaceClose])[0]['n'];
check($csRaceClosed['status']==='closed' && CashShift::cents($csRaceClosed['expected_cash'])===10000+1000*$csRaceSales && RecipeInventory::balance($csProduct,1)===$csBeforeStock-10000*$csRaceSales,'racing checkout is either included in closing or rejected without consuming stock');
// Opening two drawers concurrently still allows only one shift for this cashier.
$csWorkers=[]; foreach ([$csA,$csB] as $register){$pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--shift-worker',$workspace,'open',base64_encode(json_encode(['register'=>$register,'key'=>$csKey()]))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$csWorkers[]=[$p,$pipes];}
$csSuccess=0;foreach($csWorkers as [$p,$pipes]){$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);if($out==='SUCCESS')$csSuccess++;}
check($csSuccess===1,'concurrent shift opening cannot assign a cashier twice');
$csActive=RecipeInventory::rows('SELECT id FROM cash_shifts WHERE active_cashier=1')[0]['id'];
check(User::delete(1)===false && User::find_by_id(1)!==null,'active cashier accounts cannot be archived and strand an open drawer');
reject(function()use($csA,$csB,$csKey){$r=RecipeInventory::rows('SELECT register_id FROM cash_shifts WHERE active_cashier=1')[0]['register_id'];CashShift::register((int)$r,1,'Busy drawer','inactive',$csKey());},'register with an active shift cannot be archived or renamed');
// Real checkout failure rolls back its drawer sale entry too.
$csEntries=count(CashShift::detail($csActive)['entries']);
$conn->query("CREATE TRIGGER fail_shift_stock BEFORE INSERT ON inventory_movements FOR EACH ROW SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT='shift rollback fixture'");
try { RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','Cash','Dine-in',$csKey()); throw new RuntimeException('Expected stock write failure.'); } catch(mysqli_sql_exception $e) {} finally {$conn->query('DROP TRIGGER fail_shift_stock');}
check(count(CashShift::detail($csActive)['entries'])===$csEntries,'stock failure rolls back the sale and its drawer entry together');
// Branch boundaries and own-shift access.
$_SESSION['user']=['id'=>3,'branch_id'=>2];
reject(function()use($csShift){CashShift::detail($csShift);},'another branch cannot read shift details');
reject(function()use($csActive,$csKey){CashShift::move($csActive,'cash_out','out','1','Wrong branch',$csKey());},'another branch cannot operate the drawer');
check(count(CashShift::listing())===0,'branch shift monitoring excludes other branch records');
$_SESSION['user']=['id'=>1,'branch_id'=>1];
foreach (['cash_drawer_movements','manage_cash_shifts'] as $permission){$pid=RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$permission])[0]['id'];RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$pid]);}
reject(function()use($csActive,$csKey){CashShift::move($csActive,'cash_out','out','1','No permission',$csKey());},'POS cashier permissions alone cannot move drawer cash');
$csDenied=endpoint('shift_post',['action'=>'shift_move','shift_id'=>$csActive,'kind'=>'cash_in','amount'=>'1','notes'=>'Unauthorized','request_key'=>$csKey()]);
check($csDenied['success']===false,'cash movement permission is enforced on direct controller posts');
PermissionSeeder::syncInventoryActions($conn);
check(!User::has_permission(1,'cash_drawer_movements') && !User::has_permission(1,'manage_cash_shifts'),'permission synchronization does not restore revoked drawer privileges');
foreach (['cash_drawer_movements','manage_cash_shifts'] as $permission){$pid=RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$permission])[0]['id'];RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$pid]);}
$csCsrf=endpoint('shift_post',['action'=>'shift_close','shift_id'=>$csActive,'amount'=>'10','request_key'=>$csKey(),'csrf_token'=>'invalid']);check($csCsrf['success']===false,'shift operations reject invalid CSRF tokens');
$csSelect=endpoint('shift_post',['action'=>'shift_select','shift_id'=>$csShift]);check($csSelect['success']===true && $csSelect['redirect']===url('?route=pos/shifts'),'shift details use a clean URL and authenticated session selection');
foreach ([$csShift,$csActive] as $id) {
    $pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,'shift_render',base64_encode(json_encode(['_shift_id'=>$id,'_branch_id'=>'1']))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$html=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);
    check(strpos($html,'Payment & drawer ledger')!==false && !preg_match('/Warning:|Notice:|Fatal error:/',$html.$err),'shift page renders '.($id==$csShift?'approved closing':'active drawer').' without warnings');
    check(strpos($html,'aria-label="Cashier shift sections"')!==false && strpos($html,'class="nav-link active" id="shift-tab-drawer"')!==false && strpos($html,'id="shift-pane-registers"')!==false && strpos($html,'id="shift-pane-reports"')!==false,'shift tabs expose authorized sections and open the selected drawer by default');
}
CashShift::close($csActive,'10','Close race fixture',$csKey());
check(count(CashShift::report(1,date('Y-m-01'),date('Y-m-d')))>=5,'shift closing report includes all accessible closed shifts');
check(Branch::delete(1)===false,'register and shift history prevent branch deletion');
// Pickup cash/electronic payments use the same drawer, without duplicating reservation stock.
RecipeInventory::query("INSERT IGNORE INTO permissions(name,description,module) VALUES('manage_online_orders','Manage Online Orders','POS')");
$csOnlinePermission=RecipeInventory::rows("SELECT id FROM permissions WHERE name='manage_online_orders'")[0]['id']; RecipeInventory::query('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$csOnlinePermission]);
PermissionSeeder::syncOnlineOrderActions($conn);
$csOnlineShift=CashShift::open($csA,1,'100',$csKey()); $csOnlineSales=[];
foreach (['Cash'=>'15.00','GCash'=>'7.00'] as $method=>$amount) {
    $key=bin2hex(random_bytes(32));
    RecipeInventory::query("INSERT INTO online_orders(tracking_number,checkout_key,request_hash,user_id,branch_id,customer_name,email,phone,total,status,payment_status) VALUES(?,?,?,1,1,'Shift pickup customer','pickup@invalid.local','123456789',?,'pending','unpaid')",'ssss',['SHIFT-'.substr($key,0,20),$key,hash('sha256',$key),$amount]); $order=(int)$conn->insert_id;
    OnlineOrder::update_status($order,'paid',1,$method); OnlineOrder::update_status($order,'paid',1,$method);
    $csOnlineSales[]=RecipeInventory::rows('SELECT sale_id FROM online_orders WHERE id=?','i',[$order])[0]['sale_id'];
}
$csOnlineTotals=CashShift::detail($csOnlineShift)['totals'];
check($csOnlineTotals['expected_cents']===11500 && $csOnlineTotals['electronic_sales_cents']===700 && count(CashShift::detail($csOnlineShift)['entries'])===2,'pickup cash and electronic payments are tracked once in the active drawer');
foreach ($csOnlineSales as $saleId) Sale::update_status($saleId,'Refunded');
check(CashShift::detail($csOnlineShift)['totals']['expected_cents']===10000 && count(CashShift::detail($csOnlineShift)['entries'])===4,'pickup refunds are tracked without counting electronic refunds as cash');
CashShift::close($csOnlineShift,'100','Pickup refunds reconciled',$csKey());
// Approval policy is captured on opening and cannot change an existing closing requirement.
CashShift::configure(1,'1','1',$csKey()); $csSnapshot=CashShift::open($csA,1,'0',$csKey()); CashShift::configure(1,'1','0',$csKey()); CashShift::close($csSnapshot,'0','Policy snapshot',$csKey());
check(CashShift::detail($csSnapshot)['status']==='pending_review','changing approval policy cannot bypass approval for an already opened shift');
$_SESSION['user']=['id'=>2,'branch_id'=>1]; CashShift::review($csSnapshot,'approve','','Policy snapshot checked',$csKey()); $_SESSION['user']=['id'=>1,'branch_id'=>1];
// Insufficient cash blocks the refund and preserves financial and drawer records.
$csEmpty=CashShift::open($csA,1,'0',$csKey()); $csEmptySale=RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','Cash','Dine-in',$csKey());
CashShift::move($csEmpty,'cash_out','out','10','Cash moved to safe',$csKey());
reject(function()use($csEmptySale){Sale::update_status($csEmptySale,'Refunded');},'cash refunds cannot overdraw the current drawer');
check(RecipeInventory::rows('SELECT status FROM sales WHERE id=?','i',[$csEmptySale])[0]['status']==='Completed' && CashShift::detail($csEmpty)['totals']['cash_refunds_cents']===0,'rejected refund preserves sale status and drawer history');
CashShift::close($csEmpty,'0','Drawer emptied to safe',$csKey());
// A global cashier still cannot charge a payment to a shift in a different branch.
$_SESSION['user']=['id'=>2,'branch_id'=>1]; CashShift::register(0,2,'Branch two drawer','active',$csKey()); $csOtherRegister=CashShift::registers(2)[0]['id']; $csOtherShift=CashShift::open((int)$csOtherRegister,2,'100',$csKey());
reject(function()use($csProduct,$csKey){RecipeInventory::checkout(2,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','Cash','Dine-in',$csKey());},'global cashier cannot use an active shift from a different branch');
CashShift::close($csOtherShift,'100','Other branch check',$csKey()); $_SESSION['user']=['id'=>1,'branch_id'=>1];
// A minimal cashier role sees only its own drawers and cannot operate sensitive controls.
RecipeInventory::query("INSERT INTO roles(name,description) VALUES('Shift cashier only','Test role')"); $csRole=$conn->insert_id;
foreach (['access_pos','view_own_shifts','open_cash_shift','close_cash_shift'] as $permission){$pid=RecipeInventory::rows('SELECT id FROM permissions WHERE name=?','s',[$permission])[0]['id'];RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(?,?)','ii',[$csRole,$pid]);}
RecipeInventory::query("INSERT INTO users(name,email,password,branch_id) VALUES('Shift cashier','shiftcashier@invalid.local','not-a-login',1)"); $csUser=(int)$conn->insert_id; RecipeInventory::query('INSERT INTO user_roles(user_id,role_id) VALUES(?,?)','ii',[$csUser,$csRole]);
$_SESSION['user']=['id'=>$csUser,'branch_id'=>1]; $csOwn=CashShift::open($csA,$csUser,'20',$csKey());
check(count(CashShift::listing(1))===1 && CashShift::detail($csOwn)['cashier_id']==$csUser,'minimal cashier role lists and views only its own shift');
reject(function()use($csShift){CashShift::detail($csShift);},'cashier cannot inspect a coworker closing summary');
reject(function()use($csOwn,$csKey){CashShift::move($csOwn,'correction','in','1','Unauthorized correction',$csKey());},'cashier cannot record manager corrections');
reject(function()use($csKey){CashShift::configure(1,'0','0',$csKey());},'cashier cannot disable branch shift requirements');
reject(function(){CashShift::report(1,date('Y-m-01'),date('Y-m-d'));},'cashier cannot export other staff shift reports');
CashShift::close($csOwn,'20','Own drawer counted',$csKey()); $_SESSION['user']=['id'=>1,'branch_id'=>1];
// Optional setup preserves existing checkout, while legacy refunds can be paid from an active drawer.
CashShift::configure(1,'0','1',$csKey());
$csLegacy=RecipeInventory::checkout(1,1,[['product_id'=>$csProduct,'quantity'=>1]],'0','10','Cash','Dine-in',$csKey());
check(!RecipeInventory::rows('SELECT id FROM cash_drawer_entries WHERE sale_id=?','i',[$csLegacy]),'optional setup preserves existing checkout without inventing a shift assignment');
$csLegacyDrawer=CashShift::open($csA,1,'100',$csKey()); Sale::update_status($csLegacy,'Refunded');
check(CashShift::detail($csLegacyDrawer)['totals']['expected_cents']===9000,'legacy sale cash refunds are recorded in the currently paying drawer');
CashShift::close($csLegacyDrawer,'90','Legacy refund counted',$csKey());
// Report export is protected separately and contains the full closing breakdown.
$csExport=['_branch_id'=>'1','_export'=>'csv','_from'=>date('Y-m-01'),'_to'=>date('Y-m-d')];
$pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,'shift_render',base64_encode(json_encode($csExport))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$csv=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);
check(strpos($csv,'Cash refunds')!==false && strpos($csv,'Electronic sales')!==false && strpos($csv,'1025.00')!==false && !preg_match('/Warning:|Notice:|Fatal error:/',$csv.$err),'closing CSV exports payments refunds and reconciliation without rendering errors');
$csReportPermission=RecipeInventory::rows("SELECT id FROM permissions WHERE name='view_shift_reports'")[0]['id']; RecipeInventory::query('DELETE FROM role_permissions WHERE role_id=1 AND permission_id=?','i',[$csReportPermission]);
$csDeniedExport=endpoint('shift_render',$csExport); check($csDeniedExport['success']===false,'revoked report permission blocks direct shift CSV export'); RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES(1,?)','i',[$csReportPermission]);
