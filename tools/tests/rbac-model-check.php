<?php
if(PHP_SAPI!=='cli')exit('CLI only');
session_save_path(__DIR__.'/../../scratch');require __DIR__.'/../../config/db.php';
require __DIR__.'/../../models/User.php';require __DIR__.'/../../models/Role.php';require __DIR__.'/../../models/PermissionSeeder.php';
$state=json_decode(file_get_contents(__DIR__.'/../../scratch/rbac-test-state.json'),true);
if(!preg_match('/^pos_rbac_test_[a-f0-9]+$/',$state['database']))throw new Exception('Not an isolated database');
$conn->select_db($state['database']);$checks=0;
function check_rbac($condition,$message){global $checks;if(!$condition)throw new Exception($message);$checks++;}
$count=function()use($conn){$counts=[];foreach(['roles','permissions','role_permissions','user_roles'] as $table)$counts[]=$conn->query('SELECT COUNT(*) n FROM '.$table)->fetch_assoc()['n'];return $counts;};
PermissionSeeder::run($conn);$before=$count();PermissionSeeder::run($conn);check_rbac($before===$count(),'Seeder must be idempotent');
$super=$state['users']['super'];$customer=$state['users']['customer'];$admin=$state['users']['admin'];
$catalog=require __DIR__.'/../../config/permissions.php';
foreach($catalog as $name=>$info){check_rbac(User::has_permission($super,$name),'Superadmin missing '.$name);check_rbac(!User::has_permission($customer,$name),'Customer gained '.$name);}
Role::add_permission('test_future_permission','Test only','Test');
check_rbac(User::has_permission($super,'test_future_permission'),'Superadmin future access');
$rid=(int)$state['roles']['Superadmin'];$total=(int)$conn->query('SELECT COUNT(*) n FROM permissions')->fetch_assoc()['n'];
check_rbac((int)$conn->query('SELECT COUNT(*) n FROM role_permissions WHERE role_id='.$rid)->fetch_assoc()['n']===$total,'New permissions must be assigned to Superadmin');
check_rbac(Role::delete($rid)===false,'Superadmin deletion blocked');
Role::update($rid,'Renamed','Test protected role',[]);
$row=$conn->query('SELECT name,is_system FROM roles WHERE id='.$rid)->fetch_assoc();check_rbac($row['name']==='Superadmin'&&(int)$row['is_system']===1,'Superadmin identity protected');
check_rbac((int)$conn->query('SELECT COUNT(*) n FROM role_permissions WHERE role_id='.$rid)->fetch_assoc()['n']===$total,'Superadmin full grants preserved');
$pid=(int)$conn->query("SELECT id FROM permissions WHERE name='view_dashboard'")->fetch_assoc()['id'];
check_rbac(Role::delete_permission($pid)===false,'Canonical permission deletion blocked');
check_rbac(Role::update_permission($pid,'renamed_key','Test','Dashboard')===false,'Canonical key rename blocked');
check_rbac(!User::can_manage_user($state['users']['users'],$admin),'Lower role cannot manage Admin');
check_rbac(User::can_manage_user($super,$admin),'Superadmin can manage Admin');
$conn->query('UPDATE users SET deleted_at=NOW() WHERE id='.(int)$super);check_rbac(!User::has_permission($super,'view_dashboard'),'Deleted account loses authority');$conn->query('UPDATE users SET deleted_at=NULL WHERE id='.(int)$super);
echo 'PASS '.$checks.' RBAC model and seed checks in isolated database'.PHP_EOL;
