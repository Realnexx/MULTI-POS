<?php
if(PHP_SAPI!=='cli')exit;
session_save_path(__DIR__.'/../../scratch');require __DIR__.'/../../config/db.php';require __DIR__.'/../../models/User.php';require __DIR__.'/../../models/RecipeInventory.php';require __DIR__.'/../../models/Sale.php';
$state=json_decode(file_get_contents(__DIR__.'/../../scratch/rbac-test-state.json'),true);
if(!preg_match('/^pos_rbac_test_[a-f0-9]+$/',$state['database']))throw new Exception('Isolated database required');
$conn->select_db($state['database']);$count=0;
function branch_check($ok,$message){global $count;if(!$ok)throw new Exception($message);$count++;}
$staff=$state['users']['stock'];$_SESSION['user']=['id'=>$staff,'role'=>'Superadmin','branch_id'=>502];
branch_check(!User::has_all_branch_access($staff),'Forged session role must not grant global access');
branch_check(RecipeInventory::branch()===501,'Stock branch must come from database');
try{RecipeInventory::branch(502);throw new Exception('Cross-branch write accepted');}catch(DomainException $e){$count++;}
$rid=$state['roles']['stock'];$conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT $rid,id FROM permissions WHERE name='manage_branches'");
branch_check(!User::has_all_branch_access($staff),'Branch permission must not grant global scope');
$conn->query("UPDATE users SET branch_id=NULL WHERE id=$staff");
try{RecipeInventory::branch();throw new Exception('Unassigned stock access accepted');}catch(DomainException $e){$count++;}
branch_check(User::landing_route($staff)==='admin/start','Unassigned staff need setup landing');
$conn->query("UPDATE users SET branch_id=502 WHERE id=$staff");
branch_check(RecipeInventory::branch()===502,'Assignment changes must take effect without login');
$conn->query("UPDATE users SET branch_id=501 WHERE id=$staff");
foreach(['admin','super'] as $role){$_SESSION['user']=['id'=>$state['users'][$role]];branch_check(User::has_all_branch_access($_SESSION['user']['id']),$role.' needs all branches');branch_check(RecipeInventory::branch(502)===502,$role.' can choose branch');}
branch_check(User::landing_route($state['users']['customer'])==='home','Customer stays on website');
echo "PASS $count database-derived branch scope, assignment refresh and unassigned staff checks\n";
