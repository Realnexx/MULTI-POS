<?php
require_once __DIR__.'/../config/db.php'; require_once __DIR__.'/../models/CashShift.php';
require_once __DIR__.'/../middleware/access_policy.php';
header('Content-Type: application/json');
$action=$_POST['action'] ?? ''; authorize_action('pos_drawer',$action);
try {
    if($_SERVER['REQUEST_METHOD']!=='POST')throw new DomainException('Use POST for drawer operations.');
    if(!CashShift::posIntegrated())throw new DomainException('POS drawer integration is not available.');
    $branch=RecipeInventory::branch($_SESSION['pos_branch_id'] ?? null);
    if(!$branch)throw new DomainException('Select the POS branch first.');
    $user=(int)$_SESSION['user']['id']; $key=$_POST['request_key'] ?? ''; $closing=null;
    if($action==='open') {
        CashierSchedule::assertAccess($user,$branch);
        $register=(int)($_POST['register_id'] ?? 0);
        if(!RecipeInventory::rows('SELECT id FROM cash_registers WHERE id=? AND branch_id=?','ii',[$register,$branch]))throw new DomainException('Select a register in this POS branch.');
        $source=$_POST['opening_source'] ?? 'manual';
        if(!in_array($source,['manual','previous'],true))throw new DomainException('Choose an opening cash option.');
        $previous=$source==='previous'?(int)($_POST['previous_shift_id'] ?? 0):0;
        if($source==='previous' && (!$previous || ($_POST['confirm_cash'] ?? '')!=='1'))throw new DomainException('Confirm that the previous counted cash is still physically in this drawer.');
        CashShift::open($register,$user,$_POST['amount'] ?? '',$key,$previous);
    } elseif(!in_array($action,['status','transactions'],true)) {
        $state=CashShift::posState($branch); if(!$state['shift'])throw new DomainException('No active drawer found.'); $id=(int)$state['shift']['id'];
        if((int)($_POST['shift_id'] ?? 0)!==$id)throw new DomainException('The active drawer changed. Refresh its details before continuing.');
        if($action==='lock')CashShift::lockDrawer($id,$key);
        elseif($action==='unlock')CashShift::unlockDrawer($id,$_POST['secret'] ?? '');
        elseif($action==='close'){
            CashShift::close($id,$_POST['amount'] ?? '',$_POST['notes'] ?? '',$key);
            $closed=CashShift::detail($id);$closing=array_intersect_key($closed,array_flip(['id','expected_cash','counted_cash','difference','status','closed_at','close_notes','register_name','branch_name']));
        }
        elseif($action==='move')CashShift::move($id,$_POST['kind'] ?? '','in',$_POST['amount'] ?? '',$_POST['notes'] ?? '',$key);
    }
    echo json_encode(['success'=>true,'state'=>CashShift::posState($branch,$action==='transactions'),'closing'=>$closing]);
}catch(Throwable $e){error_log('POS drawer: '.get_class($e));echo json_encode(['success'=>false,'message'=>$e instanceof DomainException || $e instanceof InvalidArgumentException?$e->getMessage():'Drawer operation could not be completed. Retry safely.']);}
