<?php
require_once __DIR__.'/User.php';
require_once __DIR__.'/Product.php';

class DashboardData
{
    public static function load($requested=null,$range=null)
    {
        $context=User::access_context($_SESSION['user']['id'] ?? 0);
        $branch=$context['branch_id'];
        if(!$branch && !$context['global']) throw new DomainException('Your account needs an active branch before viewing the dashboard.');
        // Assigned accounts always see their own branch, including assigned admins.
        if(!$branch && $requested!==null && $requested!=='all') {
            if(!ctype_digit((string)$requested)) throw new DomainException('Choose a valid dashboard branch.');
            $branch=(int)$requested;
            if(!RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[$branch])) throw new DomainException('Choose an active dashboard branch.');
        }
        $start=null;$end=null;
        if($range) {
            $dates=explode(' to ',(string)$range);$start=$dates[0];$end=$dates[1] ?? $start;
            foreach([$start,$end] as $date) if(!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$date) || date('Y-m-d',strtotime($date))!==$date) throw new DomainException('Choose valid dashboard dates.');
            if($start>$end) throw new DomainException('The start date must precede the end date.');
        }
        $salesWhere="s.status='Completed'";$params=[];$types='';
        if($branch!==null) {$salesWhere.=' AND s.branch_id=?';$params[]=$branch;$types.='i';}
        $dateStart=$start ?: date('Y-m-d');$dateEnd=$end ?: date('Y-m-d');
        $periodWhere=$salesWhere.' AND DATE(s.created_at) BETWEEN ? AND ?';
        $periodParams=array_merge($params,[$dateStart,$dateEnd]);$periodTypes=$types.'ss';
        $totals=RecipeInventory::rows('SELECT COALESCE(SUM(s.final_amount),0) total,COUNT(*) n FROM sales s WHERE '.$periodWhere,$periodTypes,$periodParams)[0];
        $products=Product::get_all_products(null,null,$branch);$catalog=[];$low=[];
        foreach($products as $product) {
            if($product['status']!=='active' || ($product['product_type']==='recipe' && !$product['is_sellable'])) continue;
            if($branch!==null && $product['track_stock'] && !in_array($product['inventory_type'],['non-stock','unlimited'],true) && !($product['product_type']==='recipe' && $product['recipe_mode']==='made_to_order')) {
                $product['stock']=InventoryQuantity::format(RecipeInventory::balance($product['id'],$branch));
            }
            $catalog[]=$product;
            if(!$product['track_stock'] || in_array($product['inventory_type'],['non-stock','unlimited'],true) || $product['product_type']==='variant' || ($product['product_type']==='recipe' && $product['recipe_mode']!=='prepared_batch')) continue;
            if((float)$product['stock']<=(float)$product['low_stock_threshold']) $low[]=$product;
        }
        // In a consolidated view, a branch shortage must not be hidden by another branch's stock.
        if($branch===null) {
            $low=RecipeInventory::rows("SELECT p.*,b.name stock_branch_name,b.id stock_branch_id,COALESCE((SELECT SUM(pb.quantity) FROM product_batches pb WHERE pb.product_id=p.id AND pb.branch_id=b.id AND pb.status='active' AND (pb.expiry_date IS NULL OR pb.expiry_date>=".RecipeInventory::expiryDateSql().")),0) stock FROM products p JOIN branches b ON (p.branch_id IS NULL OR p.branch_id=b.id) WHERE b.status='Active' AND p.status='active' AND p.track_stock=1 AND p.inventory_type NOT IN ('non-stock','unlimited') AND p.product_type<>'variant' AND (p.product_type<>'recipe' OR (p.recipe_mode='prepared_batch' AND p.is_sellable=1)) HAVING stock<=low_stock_threshold ORDER BY stock,b.name,p.name");
            foreach($low as &$alert) $alert['name'].=' · '.$alert['stock_branch_name'];unset($alert);
        }
        usort($low,static function($a,$b){return (float)$a['stock']<=>(float)$b['stock'];});
        $users=RecipeInventory::rows('SELECT COUNT(*) n FROM users WHERE deleted_at IS NULL'.($branch!==null ? ' AND branch_id=?' : ''),$branch!==null ? 'i' : '',$branch!==null ? [$branch] : [])[0]['n'];
        $chartStart=date('Y-m-d',strtotime($dateEnd.' -6 days'));
        $chart=RecipeInventory::rows('SELECT DATE(s.created_at) day,SUM(s.final_amount) total FROM sales s WHERE '.$salesWhere.' AND DATE(s.created_at) BETWEEN ? AND ? GROUP BY DATE(s.created_at)',$types.'ss',array_merge($params,[$chartStart,$dateEnd]));
        $days=[];for($i=6;$i>=0;$i--) $days[date('Y-m-d',strtotime($dateEnd.' -'.$i.' days'))]=0;
        foreach($chart as $row) $days[$row['day']]=(float)$row['total'];
        $stats=['sales_today'=>(float)$totals['total'],'orders_today'=>(int)$totals['n'],'avg_order_value'=>$totals['n'] ? (float)$totals['total']/(int)$totals['n'] : 0,'total_products'=>count($catalog),'low_stock_count'=>count($low),'total_users'=>(int)$users,
            'sales_chart_labels'=>array_map(static function($day){return date('M d',strtotime($day));},array_keys($days)),'sales_chart_data'=>array_values($days)];
        $pendingWhere=str_replace("s.status='Completed'","s.status='Pending'",$periodWhere);
        $stats['completed_orders']=(int)$totals['n'];
        $stats['pending_orders']=(int)RecipeInventory::rows('SELECT COUNT(*) n FROM sales s WHERE '.$pendingWhere,$periodTypes,$periodParams)[0]['n'];
        $recentWhere=$branch!==null ? 's.branch_id=?' : '1=1';$recentParams=$params;$recentTypes=$types;
        if($start) {$recentWhere.=' AND DATE(s.created_at) BETWEEN ? AND ?';$recentParams=array_merge($params,[$start,$end]);$recentTypes.='ss';}
        $recent=RecipeInventory::rows('SELECT s.*,COALESCE(u.name,\'Online payment\') cashier_name,b.name branch_name FROM sales s LEFT JOIN users u ON u.id=s.cashier_id LEFT JOIN branches b ON b.id=s.branch_id WHERE '.$recentWhere.' ORDER BY s.id DESC LIMIT 10',$recentTypes,$recentParams);
        $top=RecipeInventory::rows('SELECT p.name,SUM(si.quantity) total_qty,SUM(si.subtotal) total_revenue FROM sales_items si JOIN sales s ON s.id=si.sale_id JOIN products p ON p.id=si.product_id WHERE '.$periodWhere.' GROUP BY p.id,p.name ORDER BY total_qty DESC,p.id LIMIT 3',$periodTypes,$periodParams);
        $activities=RecipeInventory::rows('SELECT l.*,u.name user_name,u.avatar_url FROM activity_logs l JOIN users u ON u.id=l.user_id'.($branch!==null ? ' WHERE u.branch_id=?' : '').' ORDER BY l.created_at DESC,l.id DESC LIMIT 5',$branch!==null ? 'i' : '',$branch!==null ? [$branch] : []);
        $name=$branch!==null ? RecipeInventory::rows('SELECT name FROM branches WHERE id=?','i',[$branch])[0]['name'] : 'All Branches';
        // Calendar summaries always describe today, this week (Monday), and this month.
        $today=date('Y-m-d');$week=date('Y-m-d',strtotime('monday this week'));$month=date('Y-m-01');
        $summaries=[];
        foreach(['today'=>$today,'week'=>$week,'month'=>$month] as $key=>$from) {
            $summaries[$key]=RecipeInventory::rows('SELECT COALESCE(SUM(s.final_amount),0) total,COUNT(*) transactions FROM sales s WHERE '.$salesWhere.' AND s.created_at>=? AND s.created_at<DATE_ADD(?,INTERVAL 1 DAY)',$types.'ss',array_merge($params,[$from,$today]))[0];
        }
        $payments=RecipeInventory::rows('SELECT s.payment_method,COUNT(*) transactions,SUM(s.final_amount) total FROM sales s WHERE '.$periodWhere.' GROUP BY s.payment_method ORDER BY total DESC',$periodTypes,$periodParams);
        $comparison=RecipeInventory::rows("SELECT b.id,b.name,COALESCE(a.total,0) total,COALESCE(a.transactions,0) transactions FROM branches b LEFT JOIN (SELECT s.branch_id,SUM(s.final_amount) total,COUNT(*) transactions FROM sales s WHERE ".$periodWhere." GROUP BY s.branch_id) a ON a.branch_id=b.id WHERE b.status='Active'".($branch!==null?' AND b.id=?':'').' ORDER BY total DESC,b.name',$periodTypes.($branch!==null?'i':''),array_merge($periodParams,$branch!==null?[$branch]:[]));
        $cost=RecipeInventory::rows('SELECT COALESCE(SUM(COALESCE((SELECT SUM(-m.quantity*m.unit_cost) FROM inventory_movements m WHERE m.sale_item_id=si.id AND m.quantity<0 AND m.unit_cost IS NOT NULL),si.quantity*COALESCE(p.cost_price,0))),0) total FROM sales_items si JOIN sales s ON s.id=si.sale_id LEFT JOIN products p ON p.id=si.product_id WHERE '.$periodWhere,$periodTypes,$periodParams)[0]['total'];
        $shiftAttention=[];$shiftsReady=false;$canReviewShifts=false;
        require_once __DIR__.'/CashShift.php';
        $actor=(int)($_SESSION['user']['id'] ?? 0);
        foreach(CashShift::permissions() as $permission) if(User::has_permission($actor,$permission)) $canReviewShifts=true;
        if($canReviewShifts && CashShift::ready()) {
            $shiftsReady=true;
            $shiftTypes=$types;$shiftParams=$params;$shiftWhere=$branch!==null?'s.branch_id=?':'1=1';
            $team=false;foreach(['view_cash_shifts','manage_cash_shifts','approve_cash_shifts','view_shift_reports'] as $permission) if(User::has_permission($actor,$permission)) $team=true;
            if(!$team){$shiftWhere.=' AND s.cashier_id=?';$shiftTypes.='i';$shiftParams[]=$actor;}
            $shiftAttention=RecipeInventory::rows("SELECT s.id,s.status,s.opened_at,s.difference,u.name cashier_name,b.name branch_name FROM cash_shifts s JOIN users u ON u.id=s.cashier_id JOIN branches b ON b.id=s.branch_id WHERE ".$shiftWhere." AND (s.status='pending_review' OR (s.status='open' AND s.opened_at<CURRENT_DATE) OR (s.status<>'approved' AND COALESCE(s.difference,0)<>0)) ORDER BY s.opened_at LIMIT 30",$shiftTypes,$shiftParams);
        }
        $operations=['summaries'=>$summaries,'payments'=>$payments,'branches'=>$comparison,'estimated_cost'=>(float)$cost,'gross_profit'=>(float)$totals['total']-(float)$cost,'shifts'=>$shiftAttention,'shifts_ready'=>$shiftsReady,'can_view_shifts'=>$canReviewShifts];
        return ['operations'=>$operations,'stats'=>$stats,'recent_sales'=>$recent,'low_stock'=>$low,'top_selling'=>$top,'recent_activities'=>$activities,'selected_branch'=>$branch===null ? 'all' : (string)$branch,'branch_name'=>$name,'can_choose_branch'=>$context['global'] && !$context['branch_id']];
    }
}
