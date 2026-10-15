<?php
class PermissionSeeder {
 public static function run($conn) {
  $catalog=require __DIR__.'/../config/permissions.php';
  $conn->begin_transaction();
  try {
   foreach($catalog as $name=>$info) {
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
    if($name==='admin') $wanted=array_keys($catalog);
    elseif($name==='cashier') $wanted=['access_pos','manage_online_orders'];
    elseif($name==='manager') {
     $has=$conn->query('SELECT COUNT(*) n FROM role_permissions WHERE role_id='.$rid)->fetch_assoc()['n'];
     $wanted=['manage_online_orders'];
     if(!$has) $wanted=['view_dashboard','view_product','add_product','edit_product','manage_inventory','access_pos','manage_online_orders','view_reports','manage_expenses','refund_sales'];
    }
    foreach($wanted as $permission) { $pid=(int)$ids[$permission];$grant->bind_param('ii',$rid,$pid);$grant->execute(); }
   }
   // Preserve website administrators that were assigned the older CMS name.
   if(isset($ids['manage_cms'])) {
    $old=(int)$ids['manage_cms'];$canonical=(int)$ids['access_website_admin'];
    $conn->query('INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT role_id,'.$canonical.' FROM role_permissions WHERE permission_id='.$old);
   }
   $grant->close();$conn->commit();
   return ['permissions'=>count($ids),'superadmin_roles'=>count($super)];
  } catch(Throwable $error) { $conn->rollback();throw $error; }
 }
}
