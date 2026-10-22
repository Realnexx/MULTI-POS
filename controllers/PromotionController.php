<?php
require_once __DIR__.'/../models/Promotion.php';
class PromotionController {
 public function index(){
  require_login();$actor=(int)$_SESSION['user']['id'];$ctx=User::access_context($actor);enforce_csrf();$error='';$old=[];
  if(!Promotion::ready()){http_response_code(503);exit('Install the promotions migration before using this page.');}
  if($_SERVER['REQUEST_METHOD']==='POST')try{
   $action=$_POST['action']??'';
   if($action==='save'){Promotion::save($_POST,$actor);}
   elseif($action==='status')Promotion::status((int)($_POST['id']??0),$_POST['status']??'',$actor);
   elseif($action==='approve')Promotion::approve((int)($_POST['id']??0),$actor,$_POST['decision']??'');
   elseif($action==='limit'){if(!Promotion::can('approve_discount_overrides')||!$ctx['global'])throw new DomainException('Global approval permission is required.');$cap=CheckoutPolicy::cents($_POST['cashier_limit']??'');if($cap>10000)throw new DomainException('The cashier limit must be between 0 and 100 percent.');Setting::set('cashier_discount_max_percent',number_format($cap/100,2,'.',''),'financial');ActivityLog::log('Discount limit updated','Cashier discount limit updated.');}
   else throw new DomainException('Unknown promotion action.');
   if($action==='status'&&($_POST['status']??'')==='paused'&&($_POST['edit_after_pause']??'')==='1'&&Promotion::can('edit_promotions')){set_flash('success','Promotion paused. Update the details, then activate it again when ready.');redirect('?route=admin/promotions&tab=list&edit='.(int)$_POST['id']);}
   set_flash('success',$action==='save'&&!empty($_POST['id'])?'Promotion updated. Activate it from the list when ready.':'Promotion settings updated.');redirect('?route=admin/promotions&tab='.(in_array($action,['approve','limit'],true)?'approvals':'list'));
  }catch(DomainException $e){$error=$e->getMessage();$old=$_POST;}catch(mysqli_sql_exception $e){if((int)$e->getCode()===1062){$error='That promo code is already used. Choose a unique code.';$old=$_POST;}else throw $e;}
  $promotions=Promotion::can('view_promotions')?RecipeInventory::rows('SELECT * FROM promotions ORDER BY id DESC'):[];
  if(!$ctx['global'])$promotions=array_values(array_filter($promotions,function($p)use($ctx){$targets=Promotion::targets($p['id']);$branches=[];foreach($targets as $t)if($t['target_type']==='branch')$branches[]=(int)$t['target_id'];return !$branches||in_array((int)$ctx['branch_id'],$branches,true);}));
  $edit=null;if(isset($_GET['edit'])&&Promotion::can('edit_promotions')&&$ctx['global']){$r=RecipeInventory::rows('SELECT * FROM promotions WHERE id=?','i',[(int)$_GET['edit']]);$edit=$r[0]??null;}
  $branches=$ctx['global']?RecipeInventory::rows('SELECT id,name FROM branches ORDER BY name'):[];
  $products=$ctx['global']?RecipeInventory::rows("SELECT id,name FROM products WHERE status='active' ORDER BY name"):[];
  $categories=$ctx['global']?RecipeInventory::rows('SELECT id,name FROM categories ORDER BY name'):[];
  $where=$ctx['global']?'1=1':'a.branch_id='.(int)$ctx['branch_id'];
  $approvals=Promotion::can('approve_discount_overrides')?RecipeInventory::rows("SELECT a.*,u.name cashier,b.name branch FROM discount_approvals a JOIN users u ON u.id=a.cashier_id JOIN branches b ON b.id=a.branch_id WHERE $where AND a.status='pending' AND a.expires_at>'".date('Y-m-d H:i:s')."' ORDER BY a.id DESC LIMIT 100"):[];
  $from=$_GET['from']??date('Y-m-01');$to=$_GET['to']??date('Y-m-d');foreach([$from,$to] as $date)if(!is_string($date)||!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$date)||date('Y-m-d',strtotime($date))!==$date)throw new DomainException('Choose valid report dates.');if($from>$to||strtotime($to)-strtotime($from)>366*86400){$from=date('Y-m-01');$to=date('Y-m-d');}
  $report=[];if(Promotion::can('view_promotion_reports')){
   $filter=$ctx['global']?'':' AND r.branch_id='.(int)$ctx['branch_id'];
   $report=RecipeInventory::rows("SELECT p.name,b.name branch,r.status,COUNT(*) uses,SUM(r.discount_cents) discounts,SUM(r.subtotal_cents-r.discount_cents) net,COALESCE(SUM(c.cost),0) costs FROM promotion_redemptions r JOIN promotions p ON p.id=r.promotion_id JOIN branches b ON b.id=r.branch_id LEFT JOIN (SELECT si.sale_id,SUM(-m.quantity*COALESCE(m.unit_cost,0))*100 cost FROM sales_items si JOIN inventory_movements m ON m.sale_item_id=si.id WHERE m.quantity<0 GROUP BY si.sale_id) c ON c.sale_id=r.sale_id WHERE r.created_at>=? AND r.created_at<DATE_ADD(?,INTERVAL 1 DAY) $filter GROUP BY p.id,p.name,b.id,b.name,r.status ORDER BY net DESC",'ss',[$from,$to]);
  }
  require __DIR__.'/../views/modules/promotions.php';
 }
}
