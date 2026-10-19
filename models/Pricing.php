<?php
require_once __DIR__.'/Setting.php';
class Pricing {
 public static function defaults(){return [
 ['id'=>'basic','name'=>'Basic Multi-Branch','price'=>'80000','billing'=>'One-time development','scope'=>'24 branches','alternative'=>'Optional one-year hosting: PHP 5,000 separately. Total with hosting: PHP 85,000.','note'=>'Payment milestones: 40% initial, 30% core modules completed, 30% after UAT.','features'=>"POS checkout and barcode support\nCentralized inventory across 24 branches\nProducts, categories and suppliers\nPurchase orders and stock receiving\nStock adjustments and inventory movements\nSales, expenses and financial reports\nCashier shifts and cash drawer\nRoles, audit logs and dashboard\nDeployment, documentation and staff orientation",'enabled'=>true,'highlight'=>false],
 ['id'=>'advanced','name'=>'Advanced + Integrations','price'=>'240000','billing'=>'One-time development','scope'=>'24 branches','alternative'=>'Core system: PHP 150,000. GrabFood: PHP 45,000. foodpanda: PHP 45,000.','note'=>'Proposed integrations depend on API access, platform approval and feasibility. Provider charges are separate.','features'=>"All Basic features\nConsolidated multi-branch reporting\nCash drawer monitoring and discrepancies\nPayment method breakdown\nRefund, discount and cancellation reports\nEstimated gross profit\nProposed GrabFood and foodpanda integrations\nProduct mapping and branch order routing\nIntegration logging and reconciliation",'enabled'=>true,'highlight'=>true],
 ['id'=>'commercial','name'=>'Commercial Software','price'=>'1999','billing'=>'Per month','scope'=>'1 business · 1 branch · Up to 5 terminals','alternative'=>'Annual subscription: PHP 19,990/year. One-time license alternative: PHP 39,990.','note'=>'24 branches are not included in the base package. Support, hosting, updates and additional terminals for the license must be agreed separately.','features'=>"POS checkout and sales recording\nProduct and inventory management\nSales summaries and basic reports\nUser and cashier access\nSoftware updates according to package\nAdditional branches quoted separately",'enabled'=>true,'highlight'=>false]
 ];}
 public static function all(){ $value=Setting::get('pricing_tiers');return $value===null?self::defaults():json_decode($value,true,512,JSON_THROW_ON_ERROR); }
 public static function published(){return array_values(array_filter(self::all(),function($p){return !empty($p['enabled']);}));}
 public static function find($id){foreach(self::published() as $p)if($p['id']===$id)return $p;return null;}
 public static function save($input){
  if(!is_array($input)||count($input)<1||count($input)>50)throw new DomainException('Configure between 1 and 50 pricing tiers.');$out=[];
  foreach(array_values($input) as $i=>$row){$base=self::defaults()[$i]??[];if(!is_array($row))throw new DomainException('Invalid tier details.');$id=$row['id']??$base['id']??'';if(!is_string($id)||!preg_match('/^[a-z0-9-]{1,64}$/D',$id)||in_array($id,array_column($out,'id'),true))throw new DomainException('Invalid or duplicate tier identifier.');$tier=['id'=>$id];
   foreach(['name'=>80,'billing'=>80,'scope'=>150,'alternative'=>500,'note'=>700,'features'=>3000] as $key=>$limit){$v=$row[$key]??'';if(!is_string($v)||mb_strlen(trim($v))>$limit)throw new DomainException('Check the length of the tier details.');$tier[$key]=trim($v);}
   if($tier['name']===''||$tier['billing']===''||$tier['features']==='')throw new DomainException('Tier name, billing label and features are required.');
   $price=$row['price']??'';if(!is_string($price)||!preg_match('/^[0-9]{1,8}(?:\.[0-9]{1,2})?$/D',$price))throw new DomainException('Enter a valid nonnegative PHP price.');$tier['price']=number_format((float)$price,2,'.','');$tier['enabled']=!empty($row['enabled']);$tier['highlight']=!empty($row['highlight']);$out[]=$tier;
  }Setting::set('pricing_tiers',json_encode($out,JSON_THROW_ON_ERROR),'website');
 }
 public static function saveTier($action,$id,$data){
  if(!in_array($action,['add','edit'],true)||!is_array($data))throw new DomainException('Invalid pricing action.');
  global $conn;$conn->begin_transaction();
  try{
   $initial=json_encode(self::defaults());$stmt=$conn->prepare("INSERT IGNORE INTO settings(setting_key,setting_value,category) VALUES ('pricing_tiers',?,'website')");$stmt->bind_param('s',$initial);$stmt->execute();
   $conn->query("SELECT setting_value FROM settings WHERE setting_key='pricing_tiers' FOR UPDATE");$tiers=self::all();
   if($action==='add'){$data['id']='tier-'.bin2hex(random_bytes(8));$tiers[]=$data;}
   else{$found=false;foreach($tiers as &$tier)if($tier['id']===$id){$data['id']=$tier['id'];$tier=$data;$found=true;break;}unset($tier);if(!$found)throw new DomainException('Pricing tier not found.');}
   self::save($tiers);$conn->commit();
  }catch(Throwable $e){$conn->rollback();throw $e;}
 }

}
