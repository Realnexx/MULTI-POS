<?php
require_once $workspace.'/models/DemoAccount.php';
check(!DemoAccount::start(1)['is_demo'],'Normal accounts do not receive a demo trial');
check((int)$conn->query('SELECT COUNT(*) n FROM demo_account_access')->fetch_assoc()['n']===0,'Normal accounts create no demo-access record');
$hash=password_hash('DemoPassword123!',PASSWORD_DEFAULT);
RecipeInventory::query("INSERT INTO users(id,name,email,username,password,branch_id) VALUES (2,'Demo user','demo@invalid.local','demo-test',?,1)",'s',[$hash]);
$conn->query("INSERT INTO roles(id,name) VALUES(2,'dEmO')");$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES(2,2),(2,1)');
function demoEndpoint($mode,$data=[]){global $root,$workspace;$pipes=[];$p=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,$mode,base64_encode(json_encode($data+['csrf_token'=>'test-csrf']))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);fclose($pipes[0]);$out=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($p);if($mode==='auth_factor'){$at=strrpos($out,'AUTH_STATE:');if($at===false)throw new RuntimeException('Authentication endpoint failed: '.$err);$out=substr($out,$at+11);}return json_decode($out,true);}
$bad=demoEndpoint('auth_factor',['username'=>'demo-test','password'=>'incorrect']);
check(!$bad['authenticated'] && (int)$conn->query('SELECT COUNT(*) n FROM demo_account_access')->fetch_assoc()['n']===0,'Failed sign-in does not start demo access');
$signedIn=demoEndpoint('auth_factor',['username'=>'demo-test','password'=>'DemoPassword123!']);
check($signedIn['authenticated']&&$signedIn['demo_notice']===2,'Successful demo login queues a notice after authentication');
$first=DemoAccount::status(2);check($first['is_demo']&&!$first['expired'],'Demo role detected across case variations and multiple roles');
$seconds=strtotime($first['expires_at'].' UTC')-strtotime($first['started_at'].' UTC');check($seconds===72*3600,'Demo access lasts exactly 72 hours');
demoEndpoint('auth_factor',['username'=>'demo-test','password'=>'DemoPassword123!']);check(DemoAccount::status(2)['expires_at']===$first['expires_at'],'Signing in again never resets the three-day window');
check(demoEndpoint('demo_guard')['success'],'Unexpired demo account retains its assigned permissions');
$_SESSION['user']=['id'=>2];$_SESSION['demo_notice_pending']=2;
ob_start();require $workspace.'/views/partials/demo_notice.php';$firstNotice=ob_get_clean();
check(strpos($firstNotice,'This is a demo account. Please contact the developer.')!==false,'Global banner displays the requested demo warning');
check(strpos($firstNotice,'data-show-on-login="1"')!==false&&strpos($firstNotice,'3 days')!==false,'Post-login modal displays the three-day limit');
ob_start();require $workspace.'/views/partials/demo_notice.php';$nextNotice=ob_get_clean();check(strpos($nextNotice,'data-show-on-login="0"')!==false,'Modal appears once per successful sign-in, not on every page');
file_put_contents($root.'/scratch/demo-notice.html','<!doctype html><html><head><meta name="viewport" content="width=device-width,initial-scale=1"></head><body>'.$firstNotice.'</body></html>');
$_SESSION['user']=['id'=>1];ob_start();require $workspace.'/views/partials/demo_notice.php';check(ob_get_clean()==='','Normal accounts never see the banner or modal');
$conn->query('UPDATE demo_account_access SET started_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 3 DAY),expires_at=UTC_TIMESTAMP() WHERE user_id=2');
check(DemoAccount::status(2)['expired'],'Access expires at the exact boundary');
$expired=demoEndpoint('auth_factor',['username'=>'demo-test','password'=>'DemoPassword123!']);check(!$expired['authenticated'],'Expired demo account cannot sign in with a valid password');
$blocked=demoEndpoint('demo_guard');check(!$blocked['success']&&strpos($blocked['message'],'demo account has expired')!==false,'Existing demo sessions and protected AJAX actions expire server-side');
$conn->query('DELETE FROM user_roles WHERE user_id=2 AND role_id=2');check(!DemoAccount::status(2)['is_demo'],'Removing Demo role removes the demo restriction');
$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES (2,2)');check(DemoAccount::status(2)['expired'],'Reassigning Demo role cannot restart an expired trial');
