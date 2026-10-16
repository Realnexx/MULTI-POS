<?php
require_once __DIR__.'/../models/Monitoring.php';
class MonitoringController {
    public function index() {
        require_permission('view_monitoring');
        enforce_csrf();
        if($_SERVER['REQUEST_METHOD']==='POST') {
            require_permission('manage_settings');
            if(!User::has_all_branch_access($_SESSION['user']['id']))access_denied();
            require_once __DIR__.'/../models/ErrorMonitor.php';require_once __DIR__.'/../models/ActivityLog.php';
            try {
                if(($_POST['monitor_action']??'')==='configure'){require_once __DIR__.'/../models/DatabaseBackup.php';DatabaseBackup::authorize((int)$_SESSION['user']['id'],$_POST['current_password']??'');ErrorMonitor::save($_POST);}
                elseif(($_POST['monitor_action']??'')==='acknowledge')ErrorMonitor::acknowledge($_POST['error_id']??0,(int)$_SESSION['user']['id']);
                else throw new DomainException('Unknown monitoring action.');
                ActivityLog::log('Monitoring Updated','Monitoring retention or error review updated.');set_flash('success','Monitoring updated.');
            }catch(DomainException $e){set_flash('danger',$e->getMessage());}
            redirect('?route=admin/monitoring');
        }

        $date=static function($value,$default){return is_string($value)&&preg_match('/^\d{4}-\d{2}-\d{2}$/D',$value)&&date('Y-m-d',strtotime($value))===$value?$value:$default;};
        $from=$date($_GET['from'] ?? null,date('Y-m-d',strtotime('-6 days')));$to=$date($_GET['to'] ?? null,date('Y-m-d'));
        if($from>$to){$swap=$from;$from=$to;$to=$swap;}
        if(strtotime($to)-strtotime($from)>366*86400)$from=date('Y-m-d',strtotime($to.' -366 days'));
        $report=Monitoring::report((int)$_SESSION['user']['id'],$from,$to);
        require __DIR__.'/../views/modules/monitoring.php';
    }
}
