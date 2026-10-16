<?php
class PermissionSeeder {
 // Install new action controls once, preserving each role's existing access.
 // Subsequent runs never restore permissions an administrator has revoked.
 public static function syncInventoryActions($conn) {
  $catalog=require __DIR__.'/../config/permissions.php';
  $legacy=['lock_cash_drawer'=>'access_pos','view_own_shifts'=>'access_pos','open_cash_shift'=>'access_pos','close_cash_shift'=>'access_pos','view_stock_history'=>'view_product','edit_stock_batches'=>'manage_inventory','prepare_recipe_stock'=>'manage_inventory','transfer_products'=>'manage_inventory','view_stock_transfers'=>'transfer_products','receive_stock_transfers'=>'transfer_products'];
  $conn->begin_transaction();
  try {
   foreach($catalog as $name=>$info) {
    if(!in_array($info[1],['Inventory','Purchasing','Cashier Shifts'],true)) continue;
    $s=$conn->prepare('SELECT id FROM permissions WHERE name=?');$s->bind_param('s',$name);$s->execute();$found=$s->get_result()->fetch_assoc();$s->close();
    if($found) {
     $s=$conn->prepare('UPDATE permissions SET description=?,module=? WHERE id=?');$s->bind_param('ssi',$info[0],$info[1],$found['id']);$s->execute();$s->close();continue;
    }
    $s=$conn->prepare('INSERT INTO permissions(name,description,module) VALUES (?,?,?)');$s->bind_param('sss',$name,$info[0],$info[1]);$s->execute();$id=$conn->insert_id;$s->close();
    if(isset($legacy[$name])) {
     $s=$conn->prepare('INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT rp.role_id,? FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE p.name=?');$s->bind_param('is',$id,$legacy[$name]);$s->execute();$s->close();
    }
    $conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT id,".(int)$id." FROM roles WHERE LOWER(REPLACE(REPLACE(REPLACE(name,' ',''),'_',''),'-','')) IN ('superadmin','admin')");
   }
   $conn->commit();return ['inventory_actions'=>'synchronized'];
  } catch(Throwable $error) { $conn->rollback();throw $error; }
 }
 // Only seed each missing online-order permission once. Existing role choices are authoritative.
 public static function syncOnlineOrderActions($conn) {
  $catalog=require __DIR__.'/../config/permissions.php';$created=[];$conn->begin_transaction();
  try {
   foreach($catalog as $name=>$info){
    if($info[1]!=='Online Orders')continue;
    $s=$conn->prepare('SELECT id FROM permissions WHERE name=?');$s->bind_param('s',$name);$s->execute();$found=$s->get_result()->fetch_assoc();$s->close();
    if($found){$s=$conn->prepare('UPDATE permissions SET description=?,module=? WHERE id=?');$s->bind_param('ssi',$info[0],$info[1],$found['id']);$s->execute();$s->close();continue;}
    $s=$conn->prepare('INSERT INTO permissions(name,description,module) VALUES(?,?,?)');$s->bind_param('sss',$name,$info[0],$info[1]);$s->execute();$pid=(int)$conn->insert_id;$s->close();$created[]=$name;
    if($name==='manage_online_orders'){
     $conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT id,$pid FROM roles WHERE LOWER(REPLACE(REPLACE(REPLACE(name,' ',''),'_',''),'-','')) IN ('superadmin','admin','manager','cashier')");
    }else{
     // Copy the old page grant; runtime checks retain payment/cancellation prerequisites
     // across all of a user's roles, including permissions split across multiple roles.
     $s=$conn->prepare("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT DISTINCT rp.role_id,? FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE p.name='manage_online_orders'");$s->bind_param('i',$pid);$s->execute();$s->close();
     $conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT id,$pid FROM roles WHERE LOWER(REPLACE(REPLACE(REPLACE(name,' ',''),'_',''),'-','')) IN ('superadmin','admin')");
    }
   }
   $conn->commit();return ['online_order_permissions_created'=>$created];
  }catch(Throwable $e){$conn->rollback();throw $e;}
 }
 // Explicit migration: add dashboard access to existing staff roles without touching other grants.
 public static function syncDashboardAccess($conn) {
  $conn->begin_transaction();
  try {
   $conn->query("INSERT IGNORE INTO permissions(name,description,module) VALUES('view_dashboard','View Dashboard','Dashboard')");
   $conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='view_dashboard' AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) NOT IN ('customer','guest')");
   $added=$conn->affected_rows;$conn->commit();return ['dashboard_role_grants_added'=>$added];
  }catch(Throwable $e){$conn->rollback();throw $e;}
 }
 public static function run($conn) {
  self::syncInventoryActions($conn);
  $catalog=require __DIR__.'/../config/permissions.php';
  $conn->begin_transaction();
  try {
   foreach($catalog as $name=>$info) {
    if($info[1]==='Online Orders')continue;
    $stmt=$conn->prepare('SELECT id FROM permissions WHERE name=?');$stmt->bind_param('s',$name);$stmt->execute();$found=$stmt->get_result()->fetch_assoc();$stmt->close();
    if(!$found) { $stmt=$conn->prepare('INSERT INTO permissions(name,description,module) VALUES (?,?,?)');$stmt->bind_param('sss',$name,$info[0],$info[1]);$stmt->execute();$stmt->close(); }
   }
   $all=$conn->query('SELECT id,name FROM permissions')->fetch_all(MYSQLI_ASSOC);$ids=array_column($all,'id','name');
   $roles=$conn->query('SELECT id,name FROM roles')->fetch_all(MYSQLI_ASSOC);
   $super=array_filter($roles,function($r){return preg_replace('/[ _-]/','',strtolower($r['name']))==='superadmin';});
   if(!$super) { $conn->query("INSERT INTO roles(name,description,is_system) VALUES ('Superadmin','Full access to all pages and actions',1)");$super=[['id'=>$conn->insert_id,'name'=>'Superadmin']]; }
   $grant=$conn->prepare('INSERT IGNORE INTO role_permissions(role_id,permission_id) VALUES (?,?)');
   foreach($super as $role) {
    $rid=(int)$role['id'];$conn->query('UPDATE roles SET is_system=1 WHERE id='.$rid);
    foreach($ids as $pid) { $grant->bind_param('ii',$rid,$pid);$grant->execute(); }
   }
   foreach($roles as $role) {
    $rid=(int)$role['id'];$name=strtolower(trim($role['name']));$wanted=[];
    if($name==='admin') $wanted=array_keys(array_filter($catalog,static function($entry){return $entry[1]!=='Online Orders';}));
    elseif($name==='cashier') $wanted=['access_pos','view_dashboard'];
    elseif($name==='manager') {
     $has=$conn->query('SELECT COUNT(*) n FROM role_permissions WHERE role_id='.$rid)->fetch_assoc()['n'];
     $wanted=[];
     if(!$has) $wanted=['view_dashboard','view_product','add_product','edit_product','manage_inventory','access_pos','view_reports','manage_expenses','refund_sales'];
    }
    if(!in_array(preg_replace('/[ _-]/','',$name),['customer','guest'],true)) $wanted[]='view_dashboard';
    foreach($wanted as $permission) { $pid=(int)$ids[$permission];$grant->bind_param('ii',$rid,$pid);$grant->execute(); }
   }
   // Preserve website administrators that were assigned the older CMS name.
   if(isset($ids['manage_cms'])) {
    $old=(int)$ids['manage_cms'];$canonical=(int)$ids['access_website_admin'];
    $conn->query('INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT role_id,'.$canonical.' FROM role_permissions WHERE permission_id='.$old);
   }
   $grant->close();$conn->commit();
   self::syncOnlineOrderActions($conn);
   return ['permissions'=>(int)$conn->query('SELECT COUNT(*) n FROM permissions')->fetch_assoc()['n'],'superadmin_roles'=>count($super)];
  } catch(Throwable $error) { $conn->rollback();throw $error; }
 }
}
