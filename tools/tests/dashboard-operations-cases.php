<?php
require_once $workspace.'/models/DashboardData.php';
$_SESSION['user']=['id'=>1,'branch_id'=>999];
$overview=DashboardData::load('all','1900-01-01 to 2100-01-01');
foreach(['today'=>date('Y-m-d'),'week'=>date('Y-m-d',strtotime('monday this week')),'month'=>date('Y-m-01')] as $key=>$from){
 $expected=RecipeInventory::rows("SELECT COALESCE(SUM(final_amount),0) total,COUNT(*) n FROM sales WHERE branch_id=1 AND status='Completed' AND created_at>=? AND created_at<DATE_ADD(?,INTERVAL 1 DAY)",'ss',[$from,date('Y-m-d')])[0];
 check((float)$overview['operations']['summaries'][$key]['total']===(float)$expected['total'] && (int)$overview['operations']['summaries'][$key]['transactions']===(int)$expected['n'],'dashboard '.$key.' calendar summary respects dates, completed status and database branch');
}
check(abs(array_sum(array_column($overview['operations']['payments'],'total'))-$overview['stats']['sales_today'])<.001,'payment methods reconcile to selected-period completed sales');
check(count($overview['operations']['branches'])===1 && (int)$overview['operations']['branches'][0]['id']===1,'branch comparison cannot expose other branches to assigned staff');
check(abs($overview['operations']['gross_profit']+$overview['operations']['estimated_cost']-$overview['stats']['sales_today'])<.001,'estimated gross profit subtracts item cost once from net completed sales');
$team=false;foreach(['view_cash_shifts','manage_cash_shifts','approve_cash_shifts','view_shift_reports'] as $p)if(User::has_permission(1,$p))$team=true;
foreach($overview['operations']['shifts'] as $shift)check($shift['branch_name']==='Test branch','shift attention respects assigned branch');
RecipeInventory::query('UPDATE users SET branch_id=NULL WHERE id=2');$_SESSION['user']=['id'=>2];
$globalOverview=DashboardData::load('all','1900-01-01 to 2100-01-01');
check(count($globalOverview['operations']['branches'])===(int)RecipeInventory::rows("SELECT COUNT(*) n FROM branches WHERE status='Active'")[0]['n'],'consolidated comparison includes every active branch including zero-sale branches');
check(abs(array_sum(array_column($globalOverview['operations']['payments'],'total'))-$globalOverview['stats']['sales_today'])<.001,'consolidated payment summaries reconcile across branches');
RecipeInventory::query('UPDATE users SET branch_id=1 WHERE id=2');
$before=(int)RecipeInventory::rows('SELECT COUNT(*) n FROM role_permissions')[0]['n'];
PermissionSeeder::syncDashboardAccess($conn);$after=(int)RecipeInventory::rows('SELECT COUNT(*) n FROM role_permissions')[0]['n'];
PermissionSeeder::syncDashboardAccess($conn);
check((int)RecipeInventory::rows('SELECT COUNT(*) n FROM role_permissions')[0]['n']===$after && $after>=$before,'dashboard access migration adds missing grants without duplicates or revoked unrelated grants');
check((int)RecipeInventory::rows("SELECT COUNT(*) n FROM roles r WHERE LOWER(r.name) NOT IN ('customer','guest') AND NOT EXISTS(SELECT 1 FROM role_permissions rp JOIN permissions p ON p.id=rp.permission_id WHERE rp.role_id=r.id AND p.name='view_dashboard')")[0]['n']===0,'all staff roles receive dashboard access');
$_SESSION['user']=['id'=>1,'branch_id'=>1];

$conn->begin_transaction();
try {
 RecipeInventory::query('UPDATE products SET cost_price=4 WHERE id=?','i',[$csProduct]);
 RecipeInventory::query("INSERT INTO sales(cashier_id,branch_id,total_amount,discount_amount,final_amount,amount_tendered,change_amount,payment_method,order_type,status,created_at) VALUES(1,1,20,2,18,20,2,'Cash','Dine-in','Completed','1999-01-02 12:00:00')");
 $profitSale=$conn->insert_id;
 RecipeInventory::query('INSERT INTO sales_items(sale_id,product_id,quantity,price,subtotal) VALUES(?,?,2,10,20)','ii',[$profitSale,$csProduct]);
 RecipeInventory::query("INSERT INTO sales(cashier_id,branch_id,total_amount,discount_amount,final_amount,amount_tendered,change_amount,payment_method,order_type,status,created_at) VALUES(1,1,99,0,99,99,0,'GCash','Dine-in','Refunded','1999-01-02 13:00:00')");
 $profitDashboard=DashboardData::load('all','1999-01-02');
 check($profitDashboard['operations']['estimated_cost']===8.0 && $profitDashboard['operations']['gross_profit']===10.0,'profit uses quantity times fallback cost and discounted completed revenue, excluding refunds');
 check(count($profitDashboard['operations']['payments'])===1 && (float)$profitDashboard['operations']['payments'][0]['total']===18.0,'payment summary excludes refunded sales and uses final amount after discounts');
 check($profitDashboard['stats']['pending_orders']===0 && $profitDashboard['stats']['completed_orders']===1,'transaction status counts use the full selected period and never classify refunds as pending');
} finally { $conn->rollback(); }
