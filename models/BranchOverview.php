<?php
require_once __DIR__.'/Branch.php';
require_once __DIR__.'/Product.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/Purchasing.php';
require_once __DIR__.'/CashShift.php';

class BranchOverview
{
    public static function load($id, $from=null, $to=null)
    {
        $actor=(int)($_SESSION['user']['id'] ?? 0);
        if(!User::has_permission($actor,'manage_branches') || !User::has_all_branch_access($actor)) throw new DomainException('You cannot view this branch overview.');
        if(!ctype_digit((string)$id) || !($branch=Branch::find_by_id((int)$id))) throw new DomainException('Branch not found.');
        $id=(int)$id;$from=$from ?: date('Y-m-01');$to=$to ?: date('Y-m-d');
        foreach([$from,$to] as $date) if(!is_string($date) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D',$date) || date('Y-m-d',strtotime($date))!==$date) throw new DomainException('Choose valid dates.');
        if($from>$to || (strtotime($to)-strtotime($from))/86400>365) throw new DomainException('Choose a date range of up to one year.');
        $can=function($permissions)use($actor){foreach((array)$permissions as $p)if(User::has_permission($actor,$p))return true;return false;};
        $data=['branch'=>$branch,'from'=>$from,'to'=>$to,'team'=>RecipeInventory::rows("SELECT u.id,u.name,GROUP_CONCAT(DISTINCT r.name ORDER BY r.name SEPARATOR ', ') roles FROM users u LEFT JOIN user_roles ur ON ur.user_id=u.id LEFT JOIN roles r ON r.id=ur.role_id WHERE u.branch_id=? AND u.deleted_at IS NULL GROUP BY u.id ORDER BY u.name",'i',[$id]),'sales'=>null,'expenses'=>null,'purchases'=>null,'stock'=>null,'shifts'=>null];
        $branch['full_image_url']=get_branch_image($branch['image_url'],$id);$data['branch']=$branch;
        $where="s.branch_id=? AND s.status='Completed' AND s.created_at>=? AND s.created_at<DATE_ADD(?,INTERVAL 1 DAY)";$args=[$id,$from,$to];
        if($can(['view_dashboard','view_reports'])) {
            $sales=RecipeInventory::rows('SELECT COALESCE(SUM(s.final_amount),0) revenue,COUNT(*) transactions,COALESCE(AVG(s.final_amount),0) average_order FROM sales s WHERE '.$where,'iss',$args)[0];
            $sales['cost']=(float)RecipeInventory::rows('SELECT COALESCE(SUM(COALESCE((SELECT SUM(-m.quantity*m.unit_cost) FROM inventory_movements m WHERE m.sale_item_id=si.id AND m.quantity<0 AND m.unit_cost IS NOT NULL),si.quantity*COALESCE(p.cost_price,0))),0) total FROM sales_items si JOIN sales s ON s.id=si.sale_id LEFT JOIN products p ON p.id=si.product_id WHERE '.$where,'iss',$args)[0]['total'];
            $sales['gross_profit']=(float)$sales['revenue']-$sales['cost'];
            $sales['trend']=RecipeInventory::rows('SELECT DATE(s.created_at) day,SUM(s.final_amount) total,COUNT(*) transactions FROM sales s WHERE '.$where.' GROUP BY DATE(s.created_at) ORDER BY day','iss',$args);
            $sales['payments']=RecipeInventory::rows('SELECT s.payment_method method,SUM(s.final_amount) total,COUNT(*) transactions FROM sales s WHERE '.$where.' GROUP BY s.payment_method ORDER BY total DESC','iss',$args);
            $sales['best_sellers']=RecipeInventory::rows('SELECT p.name,SUM(si.quantity) quantity,SUM(si.subtotal) total FROM sales_items si JOIN sales s ON s.id=si.sale_id LEFT JOIN products p ON p.id=si.product_id WHERE '.$where.' GROUP BY si.product_id,p.name ORDER BY quantity DESC LIMIT 10','iss',$args);
            $sales['calendar']=[];foreach(['Today'=>date('Y-m-d'),'This week'=>date('Y-m-d',strtotime('monday this week')),'This month'=>date('Y-m-01')] as $label=>$start)$sales['calendar'][]=['label'=>$label]+RecipeInventory::rows("SELECT COALESCE(SUM(final_amount),0) total,COUNT(*) transactions FROM sales WHERE branch_id=? AND status='Completed' AND created_at>=? AND created_at<DATE_ADD(?,INTERVAL 1 DAY)",'iss',[$id,$start,date('Y-m-d')])[0];
            $data['sales']=$sales;
        }
        if($can(['manage_expenses','view_reports'])) {
            $expenses=RecipeInventory::rows('SELECT COALESCE(SUM(amount),0) total,COUNT(*) count FROM expenses WHERE branch_id=? AND expense_date BETWEEN ? AND ?','iss',$args)[0];
            $expenses['categories']=RecipeInventory::rows('SELECT category,SUM(amount) total,COUNT(*) count FROM expenses WHERE branch_id=? AND expense_date BETWEEN ? AND ? GROUP BY category ORDER BY total DESC','iss',$args);
            $expenses['recent']=RecipeInventory::rows('SELECT category,amount,expense_date,description FROM expenses WHERE branch_id=? AND expense_date BETWEEN ? AND ? ORDER BY expense_date DESC,id DESC LIMIT 20','iss',$args);
            $data['expenses']=$expenses;
            if($data['sales']!==null)$data['sales']['operating_profit_estimate']=$data['sales']['gross_profit']-(float)$expenses['total'];
        }
        if($can(Purchasing::orderPermissions())) {
            $data['purchases']=['ready'=>Purchasing::ready(),'statuses'=>[],'recent'=>[]];
            if($data['purchases']['ready']) {
                $po="o.branch_id=? AND o.created_at>=? AND o.created_at<DATE_ADD(?,INTERVAL 1 DAY)";
                $data['purchases']['statuses']=RecipeInventory::rows('SELECT o.status,COUNT(*) count,COALESCE(SUM((SELECT SUM(i.amount) FROM purchase_order_items i WHERE i.order_id=o.id)),0) total FROM purchase_orders o WHERE '.$po.' GROUP BY o.status','iss',$args);
                $data['purchases']['recent']=RecipeInventory::rows('SELECT o.id,o.status,o.created_at,o.expected_date,ps.name supplier,COALESCE((SELECT SUM(i.amount) FROM purchase_order_items i WHERE i.order_id=o.id),0) total FROM purchase_orders o LEFT JOIN purchase_suppliers ps ON ps.id=o.supplier_id WHERE '.$po.' ORDER BY o.id DESC LIMIT 20','iss',$args);
            }
        }
        if($can(['view_dashboard','view_product'])) {
            $stock=[];foreach(Product::get_all_products(null,null,$id) as $p){
                if($p['status']!=='active' || !$p['track_stock'] || in_array($p['inventory_type'],['non-stock','unlimited'],true) || $p['product_type']==='variant' || ($p['product_type']==='recipe' && $p['recipe_mode']!=='prepared_batch'))continue;
                $balance=(float)InventoryQuantity::format(RecipeInventory::balance($p['id'],$id));if($balance<=(float)$p['low_stock_threshold'])$stock[]=['name'=>$p['name'],'stock'=>$balance,'threshold'=>$p['low_stock_threshold'],'unit'=>$p['unit_type']];
            }usort($stock,fn($a,$b)=>$a['stock']<=>$b['stock']);$data['stock']=['count'=>count($stock),'items'=>array_slice($stock,0,20)];
        }
        if($can(['view_cash_shifts','manage_cash_shifts','approve_cash_shifts','view_shift_reports'])) {
            $data['shifts']=['ready'=>CashShift::ready(),'items'=>[]];
            if($data['shifts']['ready'])$data['shifts']['items']=RecipeInventory::rows("SELECT s.id,s.status,s.opened_at,s.closed_at,s.expected_cash,s.counted_cash,s.difference,u.name cashier FROM cash_shifts s LEFT JOIN users u ON u.id=s.cashier_id WHERE s.branch_id=? AND (s.status IN ('open','pending_review') OR (s.closed_at>=? AND s.closed_at<DATE_ADD(?,INTERVAL 1 DAY))) ORDER BY s.id DESC LIMIT 20",'iss',$args);
        }
        return $data;
    }
}
