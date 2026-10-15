<?php
require_once __DIR__.'/../models/OnlineOrder.php';
class OnlineOrderController {
    public function index() {
        require_permission('manage_online_orders');
        if($_SERVER['REQUEST_METHOD']==='POST') {
            enforce_csrf();
            try{$order=OnlineOrder::update_status((int)AuthSecurity::input('order_id'),AuthSecurity::input('status'),(int)$_SESSION['user']['id'],AuthSecurity::input('payment_method') ?: 'Cash');set_flash('success','Order '.$order['tracking_number'].' updated.');}
            catch(DomainException $e){set_flash('danger',$e->getMessage());}
            catch(Throwable $e){error_log('Online order update failed: '.$e->getMessage());set_flash('danger','The order could not be updated. Please refresh and retry.');}
            redirect('?route=admin/online_orders&tracking='.rawurlencode($order['tracking_number'] ?? AuthSecurity::input('tracking')));
        }
        $orders=OnlineOrder::list_for((int)$_SESSION['user']['id'],true);
        $tracking=isset($_GET['tracking'])&&is_string($_GET['tracking'])?$_GET['tracking']:'';
        $selected=$tracking!==''?OnlineOrder::detail($tracking,(int)$_SESSION['user']['id'],true):null;
        if($tracking!==''&&!$selected)access_denied('This order is not available in your branch.',404);
        require __DIR__.'/../views/modules/online_orders.php';
    }
}
