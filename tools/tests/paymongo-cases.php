<?php
require_once $workspace.'/models/PayMongo.php';
RecipeInventory::query("INSERT IGNORE INTO permissions(name,description,module) VALUES('manage_settings','Settings','Settings')");
RecipeInventory::query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT 2,id FROM permissions WHERE name='manage_settings'");
$_SESSION['user']=['id'=>2,'branch_id'=>1];
$_SERVER['HTTP_HOST']='store.example';$_SERVER['HTTPS']='on';
$pmInput=['mode'=>'test','enabled'=>'1','public_key'=>'pk_test_examplePublic1234','secret_key'=>'sk_test_exampleSecret1234','action'=>'sync'];
$pmHook=null;$pmMethods=['card','gcash','qrph'];$pmResources=[];$pmCalls=[];$pmCreates=[];$pmTimeout=false;$pmNetworkDown=false;$pmSequence=0;
PayMongo::$transport=function($method,$path,$secret,$payload,$key)use(&$pmHook,&$pmMethods,&$pmResources,&$pmCalls,&$pmCreates,&$pmTimeout,&$pmNetworkDown,&$pmSequence){
    $pmCalls[]=[$method,$path];
    if($pmNetworkDown)throw new PayMongoApiException('Simulated outage');
    if($path==='/v1/merchants/capabilities/payment_methods')return ['data'=>['attributes'=>['payment_methods'=>$pmMethods]]];
    if($path==='/v1/webhooks' && $method==='GET')return ['data'=>$pmHook?[$pmHook]:[]];
    if($path==='/v1/webhooks'){$pmHook=['id'=>'hook_TestWebhook','attributes'=>['secret_key'=>'whsk_TestSigning1234','status'=>'enabled','livemode'=>false,'url'=>'https://store.example/pos/ajax/paymongo_webhook.php','events'=>['checkout_session.payment.paid']]];return ['data'=>$pmHook];}
    if($method==='POST' && $path==='/v2/checkout_sessions'){
        $pmCreates[]=[$key,$payload,$secret];if($pmTimeout){$pmTimeout=false;throw new PayMongoApiException('Simulated timeout');}
        $id='cs_TestSession'.(++$pmSequence);$a=$payload['data']['attributes'];$a['livemode']=false;$a['status']='active';$a['payments']=[];$a['checkout_url']='https://checkout.paymongo.com/'.$id;
        $pmResources[$id]=['id'=>$id,'type'=>'checkout_session','attributes'=>$a];return ['data'=>$pmResources[$id]];
    }
    if($method==='POST' && preg_match('~/checkout_sessions/(cs_[A-Za-z0-9]+)/expire$~',$path,$match)){$pmResources[$match[1]]['attributes']['status']='expired';return ['data'=>$pmResources[$match[1]]];}
    $id=basename($path);if(isset($pmResources[$id]))return ['data'=>$pmResources[$id]];
    throw new RuntimeException('Unexpected mocked PayMongo call '.$path);
};
PayMongo::save($pmInput,2);
$pmSummary=PayMongo::summary();$pmStored=RecipeInventory::rows('SELECT * FROM paymongo_config WHERE id=1')[0];
check($pmSummary['enabled'] && count($pmSummary['methods'])===3,'PayMongo enable checks credentials, connects webhook and inserts discovered methods');
check(strpos($pmStored['secret_cipher'],$pmInput['secret_key'])===false && strpos(json_encode($pmSummary),$pmInput['secret_key'])===false && $pmSummary['has_webhook'],'gateway secrets are encrypted and never returned in settings summary');
reject(function()use($pmInput){$bad=$pmInput;$bad['public_key']='pk_live_examplePublic1234';PayMongo::save($bad,2);},'PayMongo rejects mismatched test and live keys');
$_SERVER['HTTPS']='off';$callsBefore=count($pmCalls);try{$localNotice=PayMongo::save($pmInput,2);check(!PayMongo::summary()['enabled']&&count(PayMongo::summary()['methods'])===3&&strpos($localNotice,'remain disabled')!==false,'HTTP setup saves keys and synchronizes methods while deferring checkout activation');check(count(array_filter(array_slice($pmCalls,$callsBefore),function($call){return strpos($call[1],'/webhooks')!==false;}))===0,'HTTP configuration does not register an unreachable webhook');}finally{$_SERVER['HTTPS']='on';}
$pmMethods=['card'];PayMongo::save($pmInput,2);
check(count(array_filter($pmCalls,static function($call){return $call[0]==='POST' && $call[1]==='/v1/webhooks';}))===1,'saving enabled settings reuses an existing matching webhook');
check((int)RecipeInventory::rows("SELECT available FROM paymongo_methods WHERE mode='test' AND code='gcash'")[0]['available']===0,'sync disables removed capabilities without deleting payment-method history');
$pmNetworkDown=true;reject(function()use($pmInput){PayMongo::save($pmInput,2);},'failed credential check rejects settings save');
check(PayMongo::summary()['enabled'],'failed sync preserves the last working configuration');
PayMongo::save(['action'=>'disable'],2);check(!PayMongo::available(),'disabling succeeds even when PayMongo is offline');$pmNetworkDown=false;PayMongo::save($pmInput,2);
check(PayMongo::parseMethods(['data'=>['card','gcash']])===['card','gcash'],'capability parser accepts documented identifier arrays');
reject(function(){PayMongo::parseMethods(['data'=>['attributes'=>['unknown'=>true]]]);},'unrecognized capability response fails closed');
check(PayMongo::applicationUrl()==='https://store.example'.APP_BASE_PATH,'callback URLs are detected automatically from the deployment address');
check(PayMongo::safeCheckoutUrl('https://checkout.paymongo.com/test') && !PayMongo::safeCheckoutUrl('https://checkout.paymongo.com.evil.example/test'),'hosted checkout redirects are restricted to the PayMongo checkout host');
$pmNewOrder=function()use($csProduct,$conn){
    $tracking='PM-'.bin2hex(random_bytes(8));$key=bin2hex(random_bytes(32));
    RecipeInventory::query("INSERT INTO online_orders(tracking_number,checkout_key,request_hash,user_id,branch_id,customer_name,email,phone,total) VALUES(?,?,?,1,1,'Gateway customer','customer@invalid.local','09123456789',125)",'sss',[$tracking,$key,hash('sha256',$tracking)]);
    $id=$conn->insert_id;RecipeInventory::query("INSERT INTO online_order_items(order_id,product_id,product_name,quantity,unit_price,line_total,needs_snapshot) VALUES(?,?,'Gateway fixture',1,125,125,'{}')",'ii',[$id,$csProduct]);return RecipeInventory::rows('SELECT * FROM online_orders WHERE id=?','i',[$id])[0];
};
$_SESSION['user']=['id'=>1,'branch_id'=>1];
$pmOrder=$pmNewOrder();$pmStockBefore=(int)RecipeInventory::rows('SELECT COUNT(*) n FROM inventory_movements')[0]['n'];
reject(function()use($pmOrder){PayMongo::checkout($pmOrder['tracking_number'],3);},'another customer cannot initiate payment for an order');
$pmUrl=PayMongo::checkout($pmOrder['tracking_number'],1);$pmAttempt=PayMongo::latest($pmOrder['id']);
check($pmUrl==='https://checkout.paymongo.com/'.$pmAttempt['session_id'] && RecipeInventory::rows('SELECT payment_status FROM online_orders WHERE id=?','i',[$pmOrder['id']])[0]['payment_status']==='processing','starting checkout reserves the order payment and returns hosted URL');
PayMongo::checkout($pmOrder['tracking_number'],1);check(count($pmCreates)===1,'repeated checkout reuses the active session without creating another payment');
RecipeInventory::query("INSERT IGNORE INTO permissions(name,description,module) VALUES('manage_online_orders','Orders','Online Orders'),('record_online_order_payments','Payments','Online Orders')");
RecipeInventory::query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT 1,id FROM permissions WHERE name IN ('manage_online_orders','record_online_order_payments')");
reject(function()use($pmOrder){OnlineOrder::update_status($pmOrder['id'],'paid',1,'Cash');},'counter payment is blocked while a PayMongo checkout remains active');
PayMongo::verify($pmAttempt['session_id']);check(!RecipeInventory::rows('SELECT sale_id FROM online_orders WHERE id=?','i',[$pmOrder['id']])[0]['sale_id'],'unpaid session and success-page visits cannot mark an order paid');
$pmSession=$pmAttempt['session_id'];$pmPaid=['id'=>'pay_TestPayment1','attributes'=>['status'=>'paid','amount'=>12499,'currency'=>'PHP','livemode'=>false,'source'=>['type'=>'card']]];
$pmResources[$pmSession]['attributes']['payments']=[$pmPaid];
reject(function()use($pmSession){PayMongo::verify($pmSession);},'gateway verification rejects a mismatched amount');
$pmResources[$pmSession]['attributes']['payments'][0]['attributes']['amount']=12500;$pmResources[$pmSession]['attributes']['livemode']=true;
reject(function()use($pmSession){PayMongo::verify($pmSession);},'gateway verification rejects a mismatched environment');$pmResources[$pmSession]['attributes']['livemode']=false;
$pmBody=json_encode(['data'=>['id'=>'evt_TestEvent','attributes'=>['type'=>'checkout_session.payment.paid','data'=>['id'=>$pmSession]]]]);
reject(function()use($pmBody){PayMongo::webhook($pmBody,'t='.time().',te=invalid');},'forged webhook signatures cannot mark orders paid');
$oldTime=(string)(time()-3600);reject(function()use($pmBody,$oldTime){PayMongo::webhook($pmBody,'t='.$oldTime.',te='.hash_hmac('sha256',$oldTime.'.'.$pmBody,'whsk_TestSigning1234'));},'old signed webhook deliveries are rejected');
$pmTimestamp=(string)time();$pmSignature='t='.$pmTimestamp.',te='.hash_hmac('sha256',$pmTimestamp.'.'.$pmBody,'whsk_TestSigning1234');
PayMongo::webhook($pmBody,$pmSignature);PayMongo::webhook($pmBody,$pmSignature);PayMongo::verify($pmSession);
$pmV2=json_encode(['event_type'=>'send.webhook','data'=>['type'=>'checkout_session.payment.paid','data'=>['id'=>$pmSession]]]);PayMongo::webhook($pmV2,'t='.$pmTimestamp.',te='.hash_hmac('sha256',$pmTimestamp.'.'.$pmV2,'whsk_TestSigning1234'));
check(true,'both documented webhook envelopes are accepted after signature verification');
$pmPaidOrder=RecipeInventory::rows('SELECT * FROM online_orders WHERE id=?','i',[$pmOrder['id']])[0];$pmSale=RecipeInventory::rows('SELECT * FROM sales WHERE id=?','i',[$pmPaidOrder['sale_id']])[0];
check($pmPaidOrder['payment_status']==='paid' && $pmSale['cashier_id']===null && (float)$pmSale['final_amount']===125.0,'verified gateway payment creates an online sale without impersonating a cashier');
check((int)RecipeInventory::rows('SELECT COUNT(*) n FROM sales WHERE request_key=?','s',['online-'.$pmOrder['checkout_key']])[0]['n']===1,'duplicate webhook deliveries and status checks create exactly one sale');
check((int)RecipeInventory::rows('SELECT COUNT(*) n FROM inventory_movements')[0]['n']===$pmStockBefore && !RecipeInventory::rows('SELECT id FROM cash_drawer_entries WHERE sale_id=?','i',[$pmSale['id']]),'external payments neither consume stock again nor alter a cashier drawer');
reject(function()use($pmSale){Sale::update_status($pmSale['id'],'Refunded');},'local refund actions cannot falsely refund PayMongo funds');
$pmRetryOrder=$pmNewOrder();$pmTimeout=true;reject(function()use($pmRetryOrder){PayMongo::checkout($pmRetryOrder['tracking_number'],1);},'ambiguous API timeout keeps order reserved for safe retry');
PayMongo::checkout($pmRetryOrder['tracking_number'],1);$pmLast=count($pmCreates)-1;
check($pmCreates[$pmLast][0]===$pmCreates[$pmLast-1][0] && $pmCreates[$pmLast][1]===$pmCreates[$pmLast-1][1],'timeout retries preserve both idempotency key and original request body');
$pmRetry=PayMongo::latest($pmRetryOrder['id']);$pmResources[$pmRetry['session_id']]['attributes']['status']='expired';PayMongo::verify($pmRetry['session_id']);
check(RecipeInventory::rows('SELECT payment_status FROM online_orders WHERE id=?','i',[$pmRetryOrder['id']])[0]['payment_status']==='unpaid','only provider-confirmed expiration releases the payment reservation');
PayMongo::checkout($pmRetryOrder['tracking_number'],1);check(PayMongo::latest($pmRetryOrder['id'])['session_id']!==$pmRetry['session_id'],'a verified expired checkout can be replaced by a fresh payment attempt');
$pmFresh=PayMongo::latest($pmRetryOrder['id']);
reject(function()use($pmFresh){PayMongo::expire($pmFresh['id'],3);},'another customer cannot cancel a payment session');
PayMongo::expire($pmFresh['id'],1);
check(PayMongo::latest($pmRetryOrder['id'])['state']==='expired','customer can safely cancel an unpaid hosted checkout after provider confirmation');
$pmStaleOrder=$pmNewOrder();$pmTimeout=true;reject(function()use($pmStaleOrder){PayMongo::checkout($pmStaleOrder['tracking_number'],1);},'timeout fixture preserves uncertain creation');
RecipeInventory::query("UPDATE paymongo_payments SET created_at=DATE_SUB(NOW(),INTERVAL 2 DAY) WHERE order_id=?",'i',[$pmStaleOrder['id']]);
reject(function()use($pmStaleOrder){PayMongo::checkout($pmStaleOrder['tracking_number'],1);},'uncertain creation is not retried after provider idempotency window');
$_SESSION['user']=['id'=>2,'branch_id'=>1];
reject(function()use($pmInput){$changed=$pmInput;$changed['secret_key']='sk_test_rotatedSecret1234';PayMongo::save($changed,2);},'payment keys cannot be switched while an uncertain checkout is outstanding');
PayMongo::save(['action'=>'disable'],2);$_SESSION['user']=['id'=>1,'branch_id'=>1];reject(function()use($pmNewOrder){$o=$pmNewOrder();PayMongo::checkout($o['tracking_number'],1);},'disabled gateway rejects new online checkouts');
PayMongo::$transport=null;$_SESSION['user']=['id'=>1,'branch_id'=>1];

foreach(['render_paymongo_settings'=>[],'render_paymongo_tracking'=>['_tracking'=>$pmRetryOrder['tracking_number']]] as $renderMode=>$payload){
 $pipes=[];$proc=proc_open([PHP_BINARY,$root.'/tools/tests/burger-integration.php','--endpoint',$workspace,$renderMode,base64_encode(json_encode($payload))],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes);
 fclose($pipes[0]);$html=stream_get_contents($pipes[1]);$errors=stream_get_contents($pipes[2]);fclose($pipes[1]);fclose($pipes[2]);proc_close($proc);
 file_put_contents($root.'/scratch/'.$renderMode.'-diagnostics.html',$html.$errors);
 check(!preg_match('/Warning:|Notice:|Fatal error|Parse error/',$html.$errors) && strpos($html,'<!')!==false,$renderMode.' renders without PHP warnings');
 if($renderMode==='render_paymongo_settings')check(strpos($html,'id="pm-site"')===false && strpos($html,'name="paymongo[site_url]"')===false,'PayMongo settings contain no editable public application URL field');
 check(strpos($html,'sk_test_exampleSecret1234')===false && strpos($html,'whsk_TestSigning1234')===false,$renderMode.' never exposes stored secrets');
 file_put_contents($root.'/scratch/'.$renderMode.'.html',$html);
}
