<?php
require_once __DIR__.'/../middleware/rbac.php';
require_once __DIR__.'/../models/CashShift.php';
require_once __DIR__.'/../models/Branch.php';
require_once __DIR__.'/../models/CashierSchedule.php';
class CashShiftController
{
    public function page() {
        require_permissions(CashShift::permissions()); enforce_csrf(); csrf_token();
        if (isset($_GET['print_shift'])) {
            try { $shift = CashShift::detail((int)$_GET['print_shift']); }
            catch (DomainException $e) { http_response_code(403); exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')); }
            require_once __DIR__.'/../models/Setting.php';
            require __DIR__.'/../views/modules/cash_shift_print.php';
            return;
        }
        $ready=CashShift::ready(); $error=null; $actor=(int)$_SESSION['user']['id'];
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            try {
                if (!$ready) throw new DomainException('Cash shift setup is pending.');
                $action=$_POST['action'] ?? ''; $key=$_POST['request_key'] ?? ''; $id=(int)($_POST['shift_id'] ?? 0);
                if ($action==='schedule_create') CashierSchedule::createPlan($_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$_POST['mode'] ?? '',$_POST['date_from'] ?? '',$_POST['date_to'] ?? '',$_POST['weekdays'] ?? [],$_POST['start_time'] ?? '',$_POST['end_time'] ?? '',$_POST['overnight'] ?? '0',$_POST['notes'] ?? '');
                elseif ($action==='schedule_save') {
                    if(!empty($_POST['schedule_id'])) CashierSchedule::editOccurrence($_POST['schedule_id'],$_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$_POST['starts_at'] ?? '',$_POST['ends_at'] ?? '',$_POST['notes'] ?? '',$_POST['status'] ?? 'scheduled',$_POST['reason'] ?? '');
                    else CashierSchedule::save(0,$_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$_POST['starts_at'] ?? '',$_POST['ends_at'] ?? '',$_POST['notes'] ?? '');
                }
                elseif ($action==='schedule_series') CashierSchedule::series($_POST['series_id'] ?? 0,$_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$_POST['date_from'] ?? '',$_POST['date_to'] ?? '',$_POST['weekdays'] ?? [],$_POST['start_time'] ?? '',$_POST['end_time'] ?? '',$_POST['overnight'] ?? '0',$_POST['notes'] ?? '',$_POST['reason'] ?? '',$_POST['status'] ?? 'active');
                elseif ($action==='schedule_attendance') CashierSchedule::attendance($_POST['schedule_id'] ?? 0,$_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$_POST['attendance_status'] ?? '',$_POST['reason'] ?? '');
                elseif ($action==='schedule_block') {
                    $start=$_POST['starts_at'] ?? ''; $end=$_POST['ends_at'] ?? '';
                    if(($_POST['block_mode'] ?? '')==='dates'){
                        $start=($_POST['block_from'] ?? '').' 00:00:00';
                        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$_POST['block_to'] ?? '',new DateTimeZone('Asia/Singapore'));
                        if(!$d || $d->format('Y-m-d')!==($_POST['block_to'] ?? ''))throw new DomainException('Choose a valid final blocked date.');
                        $end=$d->modify('+1 day')->format('Y-m-d H:i:s');
                    }
                    CashierSchedule::block($_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$start,$end,$_POST['category'] ?? '',$_POST['reason'] ?? '',$_POST['notes'] ?? '',$_POST['schedule_id'] ?? null);
                }
                elseif ($action==='schedule_restore') CashierSchedule::restoreBlock($_POST['block_id'] ?? 0,$_POST['branch_id'] ?? 0,$_POST['cashier_id'] ?? 0,$_POST['reason'] ?? '');
                elseif ($action==='schedule_policy') CashierSchedule::configure($_POST['branch_id'] ?? 0,$_POST['early_minutes'] ?? '',$_POST['late_minutes'] ?? '');
                elseif ($action==='shift_select') { CashShift::detail($id); $_SESSION['cash_shift_selected'][$actor]=$id; }
                elseif ($action==='shift_deselect') unset($_SESSION['cash_shift_selected'][$actor]);
                elseif ($action==='shift_register') CashShift::register((int)($_POST['register_id'] ?? 0),$_POST['branch_id'] ?? '',$_POST['name'] ?? '',$_POST['status'] ?? '',$key);
                elseif ($action==='shift_policy') CashShift::configure($_POST['branch_id'] ?? '',$_POST['require_open'] ?? '',$_POST['require_approval'] ?? '',$key);
                elseif ($action==='shift_open') $_SESSION['cash_shift_selected'][$actor]=CashShift::open((int)($_POST['register_id'] ?? 0),(int)($_POST['cashier_id'] ?? $actor),$_POST['amount'] ?? '',$key);
                elseif ($action==='shift_move') CashShift::move($id,$_POST['kind'] ?? '',$_POST['direction'] ?? 'in',$_POST['amount'] ?? '',$_POST['notes'] ?? '',$key);
                elseif ($action==='shift_close') CashShift::close($id,$_POST['amount'] ?? '',$_POST['notes'] ?? '',$key);
                elseif ($action==='shift_review') CashShift::review($id,$_POST['review_action'] ?? '',$_POST['amount'] ?? '',$_POST['notes'] ?? '',$key);
                else throw new DomainException('Unknown shift operation.');
                set_flash('success','Shift record updated.');
                if(strpos($action,'schedule_')===0){$_SESSION['cash_shift_tab']='schedules';redirect('?route=pos/shifts&branch_id='.(int)$_POST['branch_id']);return;}
                redirect('?route=pos/shifts'); return;
            } catch (Throwable $e) {
                error_log('Cash shift operation: '.$e->getMessage());
                $error=$e instanceof DomainException || $e instanceof InvalidArgumentException ? $e->getMessage():'The shift operation could not be completed. No drawer changes were committed.';
                if (($_SERVER['HTTP_X_MODAL_FORM'] ?? '')==='1') { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$error]); return; }
            }
        }
        try { $branch=CashShift::scope($_GET['branch_id'] ?? null); } catch (DomainException $e) { access_denied($e->getMessage()); }
        $branches=Branch::for_current_user();
        $from=$_GET['from'] ?? date('Y-m-01'); $to=$_GET['to'] ?? date('Y-m-d');
        if ($ready && ($_GET['export'] ?? '')==='csv') {
            try { $rows=CashShift::report($branch,$from,$to); } catch (DomainException $e) { access_denied($e->getMessage()); }
            header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename=cashier-shift-closings.csv');
            $out=fopen('php://output','w'); fputcsv($out,['Shift','Branch','Register','Cashier','Opened','Closed','Opening float','Cash sales','Electronic sales','Cash refunds','Electronic refunds','Cash-in','Cash-out','Corrections','Expected cash','Counted cash','Overage / shortage','Status','Explanation']);
            foreach ($rows as $r) {
                $safe=static function($v){return preg_match('/^[\s]*[=+@-]/u',(string)$v)?"'".$v:$v;};
                $t=CashShift::totals($r);
                fputcsv($out,[$r['id'],$safe($r['branch_name']),$safe($r['register_name']),$safe($r['cashier_name']),$r['opened_at'],$r['closed_at'],$r['opening_float'],CashShift::money($t['cash_sales_cents']),CashShift::money($t['electronic_sales_cents']),CashShift::money($t['cash_refunds_cents']),CashShift::money($t['electronic_refunds_cents']),CashShift::money($t['cash_in_cents']),CashShift::money($t['cash_out_cents']),CashShift::money($t['correction_cents']),$r['expected_cash'],$r['counted_cash'],$r['difference'],$r['status'],$safe($r['close_notes'])]);
            }
            fclose($out); return;
        }
        $shifts=$ready?CashShift::listing($branch):[]; $registers=$ready?CashShift::registers($branch):[]; $selected=null;
        if ($ready && !empty($_SESSION['cash_shift_selected'][$actor])) {
            try { $selected=CashShift::detail((int)$_SESSION['cash_shift_selected'][$actor]); }
            catch (DomainException $e) { unset($_SESSION['cash_shift_selected'][$actor]); }
            if ($selected && $branch && (int)$selected['branch_id']!==$branch) $selected=null;
        }
        $policy=$ready && $branch?CashShift::policy($branch):null; $cashiers=[];
        if ($ready && $branch && (User::has_permission($actor,'manage_cash_shifts') || User::has_permission($actor,'manage_cashier_schedules'))) {
            foreach (RecipeInventory::rows('SELECT id,name FROM users WHERE branch_id=? AND deleted_at IS NULL ORDER BY name','i',[$branch]) as $u) if (User::has_permission($u['id'],'access_pos')) $cashiers[]=$u;
            if (!in_array($actor,array_column($cashiers,'id')) && User::has_permission($actor,'access_pos')) $cashiers[]=['id'=>$actor,'name'=>$_SESSION['user']['name'] ?? 'Current user'];
        }
        foreach($cashiers as &$cashier)$cashier['roles']=array_column(User::get_user_roles((int)$cashier['id']),'name');unset($cashier);
        try{$scheduleFilters=CashierSchedule::planningFilters($_GET);}catch(DomainException $e){$error=$e->getMessage();$scheduleFilters=CashierSchedule::planningFilters([]);}
        if(isset($_GET['schedule_view']) || isset($_GET['schedule_from']))$_SESSION['cash_shift_tab']='schedules';
        $schedules=CashierSchedule::filteredListing($branch,$scheduleFilters);
        $scheduleBlocks=CashierSchedule::planningRecords('cashier_schedule_blocks',$branch,$scheduleFilters);
        $scheduleSeries=CashierSchedule::planningRecords('cashier_schedule_series',$branch,$scheduleFilters);
        require __DIR__.'/../views/modules/cash_shifts.php';
    }
}
