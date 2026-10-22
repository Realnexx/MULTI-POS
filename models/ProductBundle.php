<?php
require_once __DIR__.'/Product.php';
class ProductBundle {
 public static function ready(){return (bool)RecipeInventory::rows("SHOW TABLES LIKE 'product_combos'");}
 public static function items($id){if(!self::ready())return [];return RecipeInventory::rows('SELECT c.*,p.name,p.product_type,p.branch_id,p.status,p.is_sellable,p.price,p.cost_price FROM product_combos c JOIN products p ON p.id=c.item_product_id WHERE c.bundle_product_id=? ORDER BY c.id','i',[(int)$id]);}
 public static function save($id,array $items,$creating=false){
  if(!User::has_permission($_SESSION['user']['id'],$creating?'add_product':'edit_product'))throw new DomainException('Product management permission is required to configure packages.');
  if(!self::ready())throw new DomainException('Install the product bundle migration first.');
  return RecipeInventory::atomic(function()use($id,$items){
   if(!$items||count($items)>50)throw new DomainException('Add between 1 and 50 package items.');
   $ids=[$id];foreach($items as $row)$ids[]=(int)($row['item_product_id']??0);RecipeInventory::lockProducts($ids);
   $owner=RecipeInventory::product($id);if($owner['product_type']!=='combo')throw new DomainException('Select Combo / Bundle as the product type.');
   if(RecipeInventory::rows('SELECT id FROM product_batches WHERE product_id=? AND quantity<>0 LIMIT 1','i',[$id]))throw new DomainException('Reconcile existing package stock before configuring component-based inventory. Packages use their component stock.');
   if($owner['branch_id']===null&&!User::has_all_branch_access($_SESSION['user']['id']))throw new DomainException('Access to all branches is required to edit a shared package.');
   if($owner['branch_id']!==null)RecipeInventory::branch($owner['branch_id']);
   $seen=[];$clean=[];foreach($items as $row){$child=(int)($row['item_product_id']??0);$qty=InventoryQuantity::parse((string)($row['quantity']??''));
    if($child===(int)$id||isset($seen[$child])||$qty<InventoryQuantity::SCALE||$qty>1000*InventoryQuantity::SCALE||$qty%InventoryQuantity::SCALE)throw new DomainException('Use unique package items with whole quantities from 1 to 1000.');
    $p=RecipeInventory::product($child);if($p['status']!=='active'||!$p['is_sellable']||in_array($p['product_type'],['combo','ingredient','variant'],true))throw new DomainException('Package items must be active sellable products; nested packages, variant parents and raw ingredients are not supported.');
    if($p['branch_id']!==null&&($owner['branch_id']===null||(int)$p['branch_id']!==(int)$owner['branch_id']))throw new DomainException('Each package item must belong to its branch or be shared across branches.');
    $station=trim((string)($row['station']??'General'));if($station===''||mb_strlen($station)>80)throw new DomainException('Enter a preparation station of up to 80 characters.');
    $seen[$child]=true;$clean[]=[$child,InventoryQuantity::format($qty),$station];
   }
   RecipeInventory::query('DELETE FROM product_combos WHERE bundle_product_id=?','i',[$id]);foreach($clean as $row)RecipeInventory::query('INSERT INTO product_combos(bundle_product_id,item_product_id,quantity,station) VALUES(?,?,?,?)','iiss',[$id,$row[0],$row[1],$row[2]]);
  });
 }
 public static function quote($id,$quantity,$branch){
  $items=self::items($id);if(!$items)throw new DomainException('This package has no configured items.');$needs=[];$components=[];$lockIds=[];
  foreach($items as $item){$units=InventoryQuantity::parse($item['quantity']);if($units%InventoryQuantity::SCALE||$units<=0)throw new DomainException('Package quantities must be whole numbers.');$units=intdiv($units,InventoryQuantity::SCALE);if(in_array($item['product_type'],['combo','ingredient','variant'],true))throw new DomainException('This package contains an unsupported component.');
   try{$q=RecipeInventory::quoteItem($item['item_product_id'],(string)($units*$quantity),[],$branch);}catch(DomainException | InvalidArgumentException $error){throw new DomainException('Included product "'.$item['name'].'": '.$error->getMessage(),0,$error);}$lockIds[]=(int)$item['item_product_id'];foreach($q['needs'] as $key=>$amount)$needs[$key]=($needs[$key]??0)+$amount;
   $components[]=['product_id'=>(int)$item['item_product_id'],'name'=>$q['product']['name'],'quantity'=>$units,'station'=>$item['station'],'unit_price_cents'=>$q['price_cents'],'product_type'=>$q['product']['product_type'],'recipe_mode'=>$q['product']['recipe_mode']??null];
  }
  return ['needs'=>$needs,'components'=>$components,'lock_ids'=>$lockIds];
 }
 public static function capacity($id,$branch){$quote=self::quote($id,1,$branch);$capacity=999999;foreach($quote['needs'] as $key=>$need)$capacity=min($capacity,intdiv(RecipeInventory::balance($key,$branch),$need));return $capacity;}
 public static function candidates($branch,$exclude=0){
  if(!User::has_permission($_SESSION['user']['id'],'view_product'))throw new DomainException('Product viewing permission is required.');
  $where="p.status='active' AND p.is_sellable=1 AND p.product_type NOT IN ('combo','ingredient','variant') AND p.id<>?";$types='i';$params=[(int)$exclude];
  if($branch==='global')$where.=' AND p.branch_id IS NULL';
  else{$branch=RecipeInventory::branch($branch);if(!$branch)throw new DomainException('Choose a branch for this package.');$where.=' AND (p.branch_id IS NULL OR p.branch_id=?)';$types.='i';$params[]=$branch;}
  $rows=RecipeInventory::rows("SELECT p.id,p.name,p.branch_id,p.price,p.product_type,p.recipe_mode,p.cost_price,b.name branch_name FROM products p LEFT JOIN branches b ON b.id=p.branch_id WHERE $where ORDER BY p.name,p.id",$types,$params);
  foreach($rows as &$row){$row['estimated_cost']=(float)$row['cost_price'];$row['cost_incomplete']=$branch==='global';
   if($branch!=='global')try{$q=RecipeInventory::quoteItem($row['id'],1,[],$branch);$cost=0;foreach($q['needs'] as $stockId=>$amount){$remaining=$amount;$batches=RecipeInventory::rows("SELECT quantity,cost_price FROM product_batches WHERE product_id=? AND branch_id=? AND quantity>0 AND status='active' AND (expiry_date IS NULL OR expiry_date>=".RecipeInventory::expiryDateSql().") ORDER BY created_at,id",'ii',[$stockId,$branch]);foreach($batches as $batch){$take=min($remaining,InventoryQuantity::parse($batch['quantity']));$cost+=($take/InventoryQuantity::SCALE)*(float)$batch['cost_price'];$remaining-=$take;if(!$remaining)break;}if($remaining){$cost+=($remaining/InventoryQuantity::SCALE)*(float)RecipeInventory::product($stockId)['cost_price'];$row['cost_incomplete']=true;}}$row['estimated_cost']=$cost;}catch(DomainException $e){$row['cost_incomplete']=true;}
  }unset($row);return $rows;
 }
 public static function snapshots(array $quotes){$result=[];foreach($quotes as $quote)if(!empty($quote['bundle_components']))$result[$quote['product']['id']]=$quote['bundle_components'];return $result;}
}
