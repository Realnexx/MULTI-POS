<?php
require_once $workspace.'/models/PayMongo.php';
RecipeInventory::query("INSERT IGNORE INTO permissions(name,description,module) VALUES('manage_settings','Settings','Settings')");RecipeInventory::query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT 2,id FROM permissions WHERE name='manage_settings'");
$_SESSION['user']=['id'=>2,'branch_id'=>1];$_SERVER['HTTP_HOST']='promotion-test.invalid';$_SERVER['HTTPS']='on';
$resources=[];$creates=[];$hook=null;
PayMongo::$transport=function($method,$path,$secret,$payload,$key)use(&$resources,&$creates,&$hook){
 if($path==='/v1/merchants/capabilities/payment_methods')return ['data'=>['attributes'=>['payment_methods'=>['gcash','card']]]];
 if($path==='/v1/webhooks'&&$method==='GET')return ['data'=>$hook?[$hook]:[]];
 if($path==='/v1/webhooks'){$hook=['id'=>'hook_PromoFixture','attributes'=>['secret_key'=>'whsk_PromoFixtureSecret','status'=>'enabled','livemode'=>false,'url'=>'https://promotion-test.invalid/pos/ajax/paymongo_webhook.php','events'=>['checkout_session.payment.paid']]];return ['data'=>$hook];}
 if($method==='POST'&&$path==='/v2/checkout_sessions'){$id='cs_PromoFixture'.(count($creates)+1);$creates[]=$payload;$a=$payload['data']['attributes'];$a['livemode']=false;$a['status']='active';$a['payments']=[];$a['checkout_url']='https://checkout.paymongo.com/'.$id;$resources[$id]=['id'=>$id,'type'=>'checkout_session','attributes'=>$a];return ['data'=>$resources[$id]];}
 $id=basename($path);if(isset($resources[$id]))return ['data'=>$resources[$id]];
 throw new RuntimeException('Unexpected gateway request in isolated simulation: '.$path);
};
PayMongo::save(['mode'=>'test','enabled'=>'1','public_key'=>'pk_test_PromoFixturePublic','secret_key'=>'sk_test_PromoFixtureSecret','action'=>'sync'],2);
$_SESSION['user']=['id'=>3,'branch_id'=>null];Cart::add($p1,1,'promotion-gateway',3);$gatewayOrder=OnlineOrder::place(3,'promotion-gateway',1,'Customer','promo-customer@invalid.local','09123456789',bin2hex(random_bytes(32)),null,null,'CATEGORY');
$stockAfterOrder=RecipeInventory::balance($p1,1);PayMongo::checkout($gatewayOrder['tracking_number'],3);$attempt=PayMongo::latest($gatewayOrder['id']);check($creates[0]['data']['attributes']['line_items'][0]['amount']===9000,'Gateway charges discounted total rather than undiscounted product sum');
PayMongo::verify($attempt['session_id']);check(RecipeInventory::rows('SELECT status FROM promotion_redemptions WHERE order_id=?','i',[$gatewayOrder['id']])[0]['status']==='reserved','Unpaid gateway response keeps payable order reservation');
$resources[$attempt['session_id']]['attributes']['payments']=[['id'=>'pay_PromoFixture','attributes'=>['status'=>'paid','amount'=>9000,'currency'=>'PHP','source'=>['type'=>'gcash']]]];
PayMongo::verify($attempt['session_id']);PayMongo::verify($attempt['session_id']);$paid=RecipeInventory::rows('SELECT * FROM online_orders WHERE id=?','i',[$gatewayOrder['id']])[0];
check($paid['payment_status']==='paid'&&CheckoutPolicy::forSale($paid['sale_id'])['discount_cents']===1000,'Verified mocked gateway payment settles promotion with immutable receipt');
check(count(RecipeInventory::rows('SELECT id FROM promotion_redemptions WHERE order_id=?','i',[$gatewayOrder['id']]))===1&&RecipeInventory::balance($p1,1)===$stockAfterOrder,'Duplicate gateway verification cannot duplicate promo use or consume stock twice');
Cart::add($p1,1,'promotion-gateway-expiry',3);$retryOrder=OnlineOrder::place(3,'promotion-gateway-expiry',1,'Customer','promo-customer@invalid.local','09123456789',bin2hex(random_bytes(32)),null,null,'CATEGORY');PayMongo::checkout($retryOrder['tracking_number'],3);$expiredAttempt=PayMongo::latest($retryOrder['id']);$resources[$expiredAttempt['session_id']]['attributes']['status']='expired';PayMongo::verify($expiredAttempt['session_id']);
check(RecipeInventory::rows('SELECT payment_status FROM online_orders WHERE id=?','i',[$retryOrder['id']])[0]['payment_status']==='unpaid'&&RecipeInventory::rows('SELECT status FROM promotion_redemptions WHERE order_id=?','i',[$retryOrder['id']])[0]['status']==='reserved','Expired payment session releases payment lock while preserving payable order promotion');
PayMongo::checkout($retryOrder['tracking_number'],3);$retryAttempt=PayMongo::latest($retryOrder['id']);check($retryAttempt['session_id']!==$expiredAttempt['session_id'],'Customer can retry a failed payment without placing another order');
$resources[$retryAttempt['session_id']]['attributes']['payments']=[['id'=>'pay_PromoRetry','attributes'=>['status'=>'paid','amount'=>9000,'currency'=>'PHP','source'=>['type'=>'gcash']]]];PayMongo::verify($retryAttempt['session_id']);check(count(RecipeInventory::rows('SELECT id FROM promotion_redemptions WHERE order_id=?','i',[$retryOrder['id']]))===1&&RecipeInventory::rows('SELECT status FROM promotion_redemptions WHERE order_id=?','i',[$retryOrder['id']])[0]['status']==='completed','Successful payment retry completes original reservation exactly once');
PayMongo::$transport=null;
