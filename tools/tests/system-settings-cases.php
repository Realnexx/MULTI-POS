<?php
require_once $workspace.'/models/TwoFactor.php';
require_once $workspace.'/models/DatabaseBackup.php';
require_once $workspace.'/models/OnlineOrder.php';
check(CheckoutPolicy::calculate(10000,10000,'10.00')['total_cents']===9000,'Legacy checkout remains unchanged until tax/charges enabled');
Setting::set('tax_enabled','1','financial');Setting::set('tax_rate','12','financial');Setting::set('tax_mode','exclusive','financial');Setting::set('service_charge_enabled','1','financial');Setting::set('service_charge','10','financial');
$f=CheckoutPolicy::calculate(10000,10000,'10');check($f['tax_cents']===1080&&$f['service_cents']===900&&$f['total_cents']===10980,'Exclusive tax and service apply after discount');
Setting::set('tax_mode','inclusive','financial');$f=CheckoutPolicy::calculate(11200,11200,'0');check($f['tax_cents']===1200&&$f['total_cents']===12320,'Inclusive tax is extracted, not charged twice');
reject(function(){CheckoutPolicy::calculate(10000,0,'1');},'Non-discountable items cannot be discounted');
Setting::set('discount_max_percent','20','financial');reject(function(){CheckoutPolicy::calculate(10000,5000,'10.01');},'Discount cap uses eligible items only');
Setting::set('discount_enabled','0','financial');reject(function(){CheckoutPolicy::calculate(10000,10000,'1');},'Disabled discounts rejected server-side');
foreach(['-1','1.001','1e3','NaN',[]] as $bad)reject(function()use($bad){CheckoutPolicy::cents($bad);},'Invalid monetary input rejected');
Setting::set('manual_payment_methods',json_encode(['Cash','Maya']),'pos');reject(function(){CheckoutPolicy::assertMethod('GCash');},'Disabled manual payment rejected');CheckoutPolicy::assertMethod('Maya');check(true,'Enabled manual payment accepted');
reject(function(){CheckoutPolicy::save('financial',['tax_rate'=>'101']);},'Invalid financial settings rejected before saving');
Setting::set('tax_enabled','0','financial');Setting::set('service_charge_enabled','0','financial');Setting::set('discount_enabled','1','financial');Setting::set('discount_max_percent','100','financial');
Setting::set('receipt_prefix','TEST-','pos');Setting::set('receipt_start_number','500','pos');Setting::set('receipt_header','<script>test</script>','pos');Setting::set('receipt_footer','Saved footer','pos');
$f=CheckoutPolicy::calculate(100,100,'0');$conn->begin_transaction();CheckoutPolicy::receipt(900001,$f);$conn->commit();
$receipt=CheckoutPolicy::forSale(900001);check($receipt['number']==='TEST-000500','Configured receipt prefix and initial number applied');
Setting::set('receipt_footer','Changed footer','pos');check(CheckoutPolicy::forSale(900001)['footer']==='Saved footer','Historical receipt snapshot is immutable');
$conn->begin_transaction();CheckoutPolicy::receipt(900002,$f);$conn->rollback();$conn->begin_transaction();CheckoutPolicy::receipt(900003,$f);$conn->commit();check(CheckoutPolicy::forSale(900003)['number']==='TEST-000501','Failed checkout rolls back receipt sequence');
check(CheckoutPolicy::forSale(123)['number']==='SHH-000123','Historical sales keep original receipt number');
Setting::set('session_timeout','15','security');check(AuthSecurity::config()['idle_timeout']===900,'Session timeout setting drives actual access policy');
Setting::set('strong_passwords','1','security');check(AuthSecurity::password_error('longpasswordlowercase')!==null,'Strong password setting enforces complexity');check(AuthSecurity::password_error('ValidLongPassword123!')===null,'Strong passphrase accepted');Setting::set('strong_passwords','0','security');check(AuthSecurity::password_error('longpasswordlowercase')===null,'Complexity switch does not remove baseline length requirement');
SystemPolicy::setPin(1,'0123');$pin=User::find_by_id(1)['pos_pin'];check($pin!=='0123'&&User::verify_pin(1,'0123')&&!User::verify_pin(1,'0000'),'Personal PIN stored as hash and verified safely');
reject(function(){SystemPolicy::setPin(1,"1' OR 1=1");},'PIN injection rejected');reject(function(){SystemPolicy::setPin(1,'123456');},'New PIN length matches four-digit terminal');
check(TwoFactor::code('GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ',1)==='287082','Authenticator matches RFC 6238 SHA-1 vector at six digits');
$secret=TwoFactor::secret();$enrolled=TwoFactor::verify(1,TwoFactor::code($secret,intdiv(time(),30)),$secret);check(count($enrolled['recovery'])===8,'Authenticator enrollment issues recovery codes');
$factor=TwoFactor::factor(1);check(strpos($factor['secret_cipher'],$secret)===false,'Authenticator secret encrypted at rest');
check(TwoFactor::verify(1,TwoFactor::code($secret,intdiv(time(),30)))===false,'Authenticator code cannot be replayed');
$recovery=$enrolled['recovery'][0];check(TwoFactor::verify(1,$recovery)!==false,'Recovery code permits sign-in');check(TwoFactor::verify(1,$recovery)===false,'Recovery code is single-use');
AuthSecurity::clear_limit('mfa-user','1');
ErrorMonitor::record('test_error','password=never-store-me','/private/source.php',42);ErrorMonitor::record('test_error','password=never-store-me','/private/source.php',42);
$errors=ErrorMonitor::rows();check(count($errors)===1&&(int)$errors[0]['occurrences']===2,'Errors grouped and counted');check(strpos(json_encode($errors),'never-store-me')===false,'Monitoring never stores raw error secrets');
ErrorMonitor::acknowledge($errors[0]['id'],1);check(ErrorMonitor::rows()[0]['acknowledged_at']!==null,'Errors can be acknowledged');ErrorMonitor::record('test_error','password=never-store-me','/private/source.php',42);check(ErrorMonitor::rows()[0]['acknowledged_at']===null,'Repeated error reopens review alert');
reject(function(){ErrorMonitor::save(['error_retention_days'=>1,'telemetry_retention_days'=>90]);},'Unsafe retention rejected');
ErrorMonitor::save(['error_retention_days'=>7,'telemetry_retention_days'=>7]);$conn->query('UPDATE application_errors SET last_seen=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 8 DAY)');ErrorMonitor::maintenance();check(count(ErrorMonitor::rows())===0,'Maintenance enforces configured error retention');check(Setting::get('maintenance_heartbeat')!==null,'Maintenance heartbeat persisted');

// Exercise the real stock-consuming POS checkout with enabled financial policy.
$sku=product('Taxed item','simple');Product::adjust_stock($sku,1,'stock_in','10','Settings fixture',null,null,1);
Setting::set('tax_enabled','1','financial');Setting::set('tax_mode','exclusive','financial');Setting::set('tax_rate','12','financial');Setting::set('service_charge_enabled','1','financial');Setting::set('service_charge','10','financial');
$sale=RecipeInventory::checkout(1,1,[['product_id'=>$sku,'quantity'=>1]],'10','200','Cash','Dine-in',bin2hex(random_bytes(16)));
$stored=RecipeInventory::rows('SELECT * FROM sales WHERE id=?','i',[$sale])[0];check($stored['final_amount']==='109.80'&&$stored['change_amount']==='90.20','Real POS checkout persists authoritative tax, charge and change');
check(CheckoutPolicy::forSale($sale)['tax_cents']===1080,'Real sale receipt retains tax snapshot');
$before=RecipeInventory::balance($sku,1);reject(function()use($sku){RecipeInventory::checkout(1,1,[['product_id'=>$sku,'quantity'=>1]],'0','100','Cash','Dine-in',bin2hex(random_bytes(16)));},'Tender short of taxed total rejected');check(RecipeInventory::balance($sku,1)===$before,'Failed taxed checkout preserves stock');
Cart::add($sku,1,'settings-order',1);$order=OnlineOrder::place(1,'settings-order',1,'Settings test','test@invalid.local','09123456789',bin2hex(random_bytes(32)),'122.00');check($order['total']==='122.00','Online checkout quotes the same tax and service rules');
Setting::set('tax_rate','50','financial');$conn->begin_transaction();$gatewaySale=OnlineOrder::record_gateway_payment($order,'PayMongo Test');$conn->commit();check(CheckoutPolicy::forSale($gatewaySale)['tax_cents']===1200,'Paying an existing order retains its original tax snapshot');
// Password validation alone must not establish an administrative session.
$conn->query("INSERT INTO users(id,name,email,username,password,branch_id) VALUES(2,'Settings admin','admin@invalid.local','settings-admin','temporary',1)");$hash=password_hash('AdminPassword123!',PASSWORD_DEFAULT);RecipeInventory::query('UPDATE users SET password=? WHERE id=2','s',[$hash]);
$conn->query("INSERT INTO roles(id,name) VALUES(2,'Admin')");$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES(2,2)');Setting::set('2fa_enabled','1','security');
function settingsAuth($payload){global $root,$workspace;$pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,'auth_factor',base64_encode(json_encode($payload+['csrf_token'=>'test-csrf']))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);$at=strrpos($out,'AUTH_STATE:');if($at===false)throw new RuntimeException('Missing authentication state: '.$err);return json_decode(substr($out,$at+11),true);}
$state=settingsAuth(['username'=>'settings-admin','password'=>'AdminPassword123!']);check(!$state['authenticated']&&!empty($state['pending']['secret']),'Administrative password sign-in creates only a pending enrollment');
$pending=$state['pending'];$enrollment=settingsAuth(['_pending'=>$pending,'code'=>TwoFactor::code($pending['secret'],intdiv(time(),30))]);check(!$enrollment['authenticated']&&!empty($enrollment['pending']['verified']),'Verified enrollment requires acknowledging recovery codes before login');
$completed=settingsAuth(['_pending'=>$enrollment['pending']]);check($completed['authenticated']&&$completed['pending']===null,'Only completed factor challenge creates authenticated session');
$again=settingsAuth(['username'=>'settings-admin','password'=>'AdminPassword123!']);check(!$again['authenticated']&&$again['pending']['secret']===null,'Enrolled account receives a verification challenge, not a new secret');
$expired=$again['pending'];$expired['expires']=time()-1;$state=settingsAuth(['_pending'=>$expired,'code'=>'000000']);check(!$state['authenticated']&&$state['pending']===null,'Expired factor challenge cannot authenticate');
RecipeInventory::query("INSERT INTO permissions(name,description,module) VALUES ('manage_settings','Settings fixture','System')");$permission=$conn->insert_id;RecipeInventory::query('INSERT INTO role_permissions(role_id,permission_id) VALUES (2,?)','i',[$permission]);
DatabaseBackup::migrate();reject(function(){DatabaseBackup::authorize(1,'anything');},'Branch staff cannot access all-branch backups');reject(function(){DatabaseBackup::authorize(2,'incorrect');},'Administrator backup action requires current password');DatabaseBackup::authorize(2,'AdminPassword123!');check(true,'Global administrator can authorize backup with current password');

foreach(['financial','pos','security','backup'] as $tab){$pipes=[];$process=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,'render_paymongo_settings',base64_encode(json_encode(['_tab'=>$tab]))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$html=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($process);check(strpos($html,'id="tab-'.$tab.'"')!==false&&!preg_match('/Warning:|Fatal error|Parse error|Notice:/',$html.$errors),'Settings '.$tab.' renders without errors');file_put_contents($root.'/scratch/settings-'.$tab.'.html',$html);if($tab==='security')check(strpos($html,htmlspecialchars(User::find_by_id(1)['pos_pin'],ENT_QUOTES))===false,'Security settings never embed personal PIN hash');}

$oldVersion=AuthSecurity::version(1);User::update_password(1,'ChangedPassword123!');check(AuthSecurity::version(1)===$oldVersion+1,'Password changes revoke existing sessions and pending factor challenges');

$version=AuthSecurity::version(1);$token=AuthSecurity::issue_reset(1);check(AuthSecurity::reset_password($token,'RecoveredPassword123!'),'Password recovery accepts a valid reset token');check(AuthSecurity::version(1)===$version+1,'Password recovery revokes existing sessions');check(!AuthSecurity::reset_password($token,'RecoveredPassword456!'),'Password recovery token cannot be reused');

$conn->query("INSERT INTO users(id,name,email,password,branch_id) VALUES(3,'Protected account','protected@invalid.local','not-a-login',1)");$conn->query("INSERT INTO roles(id,name) VALUES(3,'Super Admin')");$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES(3,3)');
reject(function(){SystemPolicy::saveSecurity(['session_timeout'=>20],[3=>'9876'],'AdminPassword123!',2);},'PIN settings respect protected Super Admin accounts');
reject(function(){SystemPolicy::saveSecurity(['session_timeout'=>0],[],'AdminPassword123!',2);},'Security settings reject invalid idle timeout');
SystemPolicy::saveSecurity(['session_timeout'=>20,'require_pos_pin'=>1,'master_pos_pin'=>'9876'],[],'AdminPassword123!',2);
check(Setting::get('2fa_enabled')==='0'&&Setting::get('strong_passwords')==='0','Security switches save disabled values explicitly');check(Setting::get('master_pos_pin')!=='9876'&&SystemPolicy::verifyPin('9876',Setting::get('master_pos_pin')),'Master PIN saved and verified as a hash');
