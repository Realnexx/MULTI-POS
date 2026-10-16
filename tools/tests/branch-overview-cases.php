<?php
require_once $workspace.'/models/BranchOverview.php';
$conn->begin_transaction();
try {
 $conn->query("INSERT IGNORE INTO permissions(name,description,module) VALUES('manage_branches','Branches','Branches'),('view_reports','Reports','Reports'),('manage_expenses','Expenses','Expenses')");
 $conn->query("INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT ur.role_id,p.id FROM user_roles ur CROSS JOIN permissions p WHERE ur.user_id=2 AND p.name IN ('manage_branches','view_dashboard','view_reports','manage_expenses','view_product','view_purchase_orders','view_cash_shifts')");
 $_SESSION['user']=['id'=>2];
 $branchOverview=BranchOverview::load(1,'1900-01-01','1900-12-31');
 check($branchOverview['sales']['revenue']==0 && $branchOverview['expenses']['total']==0,'empty branch overview period returns zero financial totals');
 $conn->query("INSERT INTO expenses(branch_id,category,amount,expense_date,description) VALUES(1,'Test expense',12,'1999-02-03','Own branch'),(2,'Test expense',999,'1999-02-03','Other branch')");
 $conn->query("INSERT INTO sales(cashier_id,branch_id,total_amount,discount_amount,final_amount,amount_tendered,change_amount,payment_method,order_type,status,created_at) VALUES(1,1,100,10,90,100,10,'Cash','Dine-in','Completed','1999-02-03 10:00:00'),(1,2,500,0,500,500,0,'Cash','Dine-in','Completed','1999-02-03 10:00:00')");
 $branchOverview=BranchOverview::load(1,'1999-02-03','1999-02-03');
 check((float)$branchOverview['sales']['revenue']===90.0 && (float)$branchOverview['expenses']['total']===12.0,'branch overview isolates sales and expenses from other branches and dates');
 check($branchOverview['sales']['operating_profit_estimate']===78.0,'branch profit subtracts recorded expenses once and does not subtract purchase-order commitments');
 check(array_sum(array_column($branchOverview['sales']['payments'],'total'))==90,'branch overview payments reconcile to discounted revenue');
 reject(function(){BranchOverview::load(1,'2026-02-30','2026-03-01');},'branch overview rejects impossible dates');
 reject(function(){BranchOverview::load(1,'2020-01-01','2026-01-01');},'branch overview limits reporting range to one year');
 reject(function(){BranchOverview::load(999999);},'branch overview rejects missing branch');
 $conn->query("DELETE rp FROM role_permissions rp JOIN user_roles ur ON ur.role_id=rp.role_id JOIN permissions p ON p.id=rp.permission_id WHERE ur.user_id=2 AND p.name IN ('view_dashboard','view_reports','manage_expenses','view_purchase_orders','create_purchase_orders','approve_purchase_orders','receive_purchases','return_purchases','view_product','view_cash_shifts','manage_cash_shifts','approve_cash_shifts','view_shift_reports')");
 $restrictedOverview=BranchOverview::load(1);
 check($restrictedOverview['sales']===null && $restrictedOverview['expenses']===null && $restrictedOverview['purchases']===null && $restrictedOverview['stock']===null && $restrictedOverview['shifts']===null,'branch management alone does not reveal financial purchasing stock or shift data');
 $_SESSION['user']=['id'=>1];
 reject(function(){BranchOverview::load(2);},'cashiers cannot read another branch overview directly');
} finally {$conn->rollback();$_SESSION['user']=['id'=>1,'branch_id'=>1];}
