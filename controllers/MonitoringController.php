<?php
require_once __DIR__.'/../models/Monitoring.php';
class MonitoringController {
    public function index() {
        require_permission('view_monitoring');
        $date=static function($value,$default){return is_string($value)&&preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value)&&date('Y-m-d',strtotime($value))===$value?$value:$default;};
        $from=$date($_GET['from'] ?? null,date('Y-m-d',strtotime('-6 days')));$to=$date($_GET['to'] ?? null,date('Y-m-d'));
        if($from>$to){$swap=$from;$from=$to;$to=$swap;}
        if(strtotime($to)-strtotime($from)>366*86400)$from=date('Y-m-d',strtotime($to.' -366 days'));
        $report=Monitoring::report((int)$_SESSION['user']['id'],$from,$to);
        require __DIR__.'/../views/modules/monitoring.php';
    }
}
