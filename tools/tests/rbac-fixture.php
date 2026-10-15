<?php
if(PHP_SAPI!=='cli')exit('CLI only');
session_save_path(__DIR__.'/../../scratch');
require __DIR__.'/../../config/db.php';
require __DIR__.'/../../models/PermissionSeeder.php';
$statePath=__DIR__.'/../../scratch/rbac-test-state.json';
if(($argv[1] ?? '')==='cleanup') {
 $state=json_decode(file_get_contents($statePath),true);$name=$state['database'];
 if(!preg_match('/^pos_rbac_test_[a-f0-9]+$/',$name))throw new Exception('Unsafe test database');
 $conn->query('DROP DATABASE `'.$name.'`');echo "Temporary RBAC database removed.\n";exit;
}
$name='pos_rbac_test_'.bin2hex(random_bytes(4));$source=DB_NAME;
$conn->query('CREATE DATABASE `'.$name.'` CHARACTER SET utf8mb4');
$tables=$conn->query('SHOW TABLES')->fetch_all(MYSQLI_NUM);
foreach($tables as $table) { $t=str_replace('`','``',$table[0]);$db=str_replace('`','``',$source);$conn->query('CREATE TABLE `'.$name.'`.`'.$t.'` LIKE `'.$db.'`.`'.$t.'`'); }
foreach(['roles','permissions','role_permissions'] as $t)$conn->query('INSERT INTO `'.$name.'`.`'.$t.'` SELECT * FROM `'.$source.'`.`'.$t.'`');
$conn->select_db($name);PermissionSeeder::run($conn);
$roleIds=array_column($conn->query('SELECT id,name FROM roles')->fetch_all(MYSQLI_ASSOC),'id','name');
$custom=['viewer'=>['view_product'],'editor'=>['view_product','edit_product'],'stock'=>['view_product','manage_inventory'],'expense'=>['manage_expenses'],'roles'=>['manage_roles'],'users'=>['manage_users']];
foreach($custom as $role=>$permissions) {
 $conn->query("INSERT INTO roles(name,description,is_system) VALUES ('Test_".$role."','Temporary test role',0)");$rid=$conn->insert_id;$roleIds[$role]=$rid;
 foreach($permissions as $p)$conn->query("INSERT INTO role_permissions(role_id,permission_id) SELECT ".$rid.",id FROM permissions WHERE name='".$p."'");
}
$cases=['super'=>'Superadmin','admin'=>'Admin','cashier'=>'Cashier','customer'=>'Customer','viewer'=>'viewer','editor'=>'editor','stock'=>'stock','expense'=>'expense','roles'=>'roles','users'=>'users'];$users=[];$i=100;
foreach($cases as $case=>$role) {
 $id=$i++;$users[$case]=$id;$conn->query("INSERT INTO users(id,name,username,email,password) VALUES (".$id.",'RBAC Test','rbac_".$case."','".$case."@rbac.invalid','unused')");$rid=(int)$roleIds[$role];$conn->query('INSERT INTO user_roles(user_id,role_id) VALUES ('.$id.','.$rid.')');
}
$conn->query("INSERT INTO branches(id,name,address,phone,status) VALUES (501,'Test Branch A','Test','000','Active'),(502,'Test Branch B','Test','000','Active')");
$conn->query("UPDATE users SET branch_id=501,branch='Test Branch A'");
$testPassword=password_hash('FixturePassword!42',PASSWORD_BCRYPT);
$stmt=$conn->prepare('UPDATE users SET password=?');$stmt->bind_param('s',$testPassword);$stmt->execute();$stmt->close();
$state=['database' =>$name,'users'=>$users,'roles'=>$roleIds];file_put_contents($statePath,json_encode($state));
session_write_close();ini_set('session.use_strict_mode','0');foreach($users as $case=>$id) {
 session_id('rbactest'.$case);session_start();$_SESSION=['user'=>['id'=>$id,'name'=>'RBAC Test','role'=>'forged-display-label','branch_id'=>null],'auth'=>['started_at'=>time(),'last_activity'=>time(),'version'=>1],'csrf_token'=>'rbac-fixture-token'];session_write_close();
}
$prepend="<?php if(PHP_SAPI!=='cli-server')exit;require '".str_replace('\\','/',realpath(__DIR__.'/../../config/db.php'))."';\n\$conn->select_db('".$name."');";
file_put_contents(__DIR__.'/../../scratch/rbac-http-prepend.php',$prepend);
echo json_encode(['database'=>$name,'fixtures'=>count($users)]).PHP_EOL;
