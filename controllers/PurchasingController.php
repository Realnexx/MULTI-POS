<?php
require_once __DIR__.'/../middleware/rbac.php';
require_once __DIR__.'/../models/Purchasing.php';
require_once __DIR__.'/../models/Branch.php';

class PurchasingController
{
    public function page($route)
    {
        $isSuppliers=$route==='inventory/suppliers'; $isReport=$route==='inventory/purchase_reports';
        require_permissions($isSuppliers?['view_suppliers','manage_suppliers']:($isReport?['view_purchase_reports']:Purchasing::orderPermissions()));
        enforce_csrf();
        $ready=Purchasing::ready(); $error=null;
        $selectionKey=(int)$_SESSION['user']['id'];
        if (!$isSuppliers && !$isReport && $ready && isset($_GET['id'])) {
            try { $legacy=Purchasing::detail((int)$_GET['id']); }
            catch (DomainException $e) { access_denied($e->getMessage()); }
            $_SESSION['purchase_selected'][$selectionKey]=(int)$legacy['id'];
            set_flash('success','Purchase order opened.');
            redirect('?route=inventory/purchases'); return;
        }
        if ($_SERVER['REQUEST_METHOD']==='POST') {
            try {
                if (!$ready) throw new DomainException('Purchasing setup is pending.');
                $action=$_POST['action'] ?? ''; $key=$_POST['request_key'] ?? ''; $order=null;
                if (!$isSuppliers && !$isReport && in_array($action,['purchase_open','purchase_close'],true)) {
                    if ($action==='purchase_open') {
                        $view=Purchasing::detail((int)($_POST['order_id'] ?? 0));
                        $_SESSION['purchase_selected'][$selectionKey]=(int)$view['id'];
                    } else unset($_SESSION['purchase_selected'][$selectionKey]);
                    set_flash('success',$action==='purchase_open'?'Purchase order opened.':'Showing purchase orders.');
                    redirect('?route=inventory/purchases'); return;
                }
                $rules=['supplier_save'=>'manage_suppliers','purchase_create'=>'create_purchase_orders','purchase_submit'=>'create_purchase_orders','purchase_cancel'=>'create_purchase_orders','purchase_approve'=>'approve_purchase_orders','purchase_reject'=>'approve_purchase_orders','purchase_receive'=>'receive_purchases','purchase_return'=>'return_purchases','purchase_settle'=>'return_purchases'];
                if (!isset($rules[$action]) || ($isSuppliers && $action!=='supplier_save') || (!$isSuppliers && $action==='supplier_save') || $isReport) throw new DomainException('Unknown purchasing action.');
                require_permission($rules[$action]);
                if ($action==='supplier_save') Purchasing::saveSupplier((int)($_POST['supplier_id'] ?? 0),$_POST,$key);
                elseif ($action==='purchase_create') $order=Purchasing::create($_POST['branch_id'] ?? '',(int)($_POST['supplier_id'] ?? 0),is_array($_POST['items'] ?? null)?$_POST['items']:[],$_POST['expected_date'] ?? '',$_POST['notes'] ?? '',$key);
                elseif ($action==='purchase_receive') $order=Purchasing::receive((int)($_POST['order_id'] ?? 0),$_POST['reference'] ?? '',$_POST['document_date'] ?? '',is_array($_POST['items'] ?? null)?$_POST['items']:[],$_POST['notes'] ?? '',$key);
                elseif ($action==='purchase_return') $order=Purchasing::returnGoods((int)($_POST['receipt_id'] ?? 0),$_POST['reference'] ?? '',$_POST['document_date'] ?? '',is_array($_POST['quantities'] ?? null)?$_POST['quantities']:[],$_POST['notes'] ?? '',$key);
                elseif ($action==='purchase_settle') $order=Purchasing::settle((int)($_POST['return_id'] ?? 0),$_POST['settlement_status'] ?? '',$_POST['notes'] ?? '',$key);
                else $order=Purchasing::transition((int)($_POST['order_id'] ?? 0),substr($action,9),$_POST['notes'] ?? '',$key);
                set_flash('success','Purchasing record saved.');
                if ($order) $_SESSION['purchase_selected'][$selectionKey]=(int)$order;
                redirect('?route='.($isSuppliers?'inventory/suppliers':'inventory/purchases').($isSuppliers?'&branch_id='.(int)($_POST['branch_id'] ?? 0):'')); return;
            } catch (Throwable $e) {
                error_log('Purchasing action: '.$e->getMessage());
                $message=$e instanceof DomainException || $e instanceof InvalidArgumentException ? $e->getMessage():'Purchasing record could not be saved. No stock changes were committed.';
                if (($_SERVER['HTTP_X_MODAL_FORM'] ?? '')==='1') { header('Content-Type: application/json'); echo json_encode(['success'=>false,'message'=>$message]); return; }
                $error=$message;
            }
        }
        $branches=Branch::for_current_user();
        $branch=null; $suppliers=[]; $orders=[]; $selected=null; $options=[]; $products=[]; $report=[];
        $from=$_GET['from'] ?? date('Y-m-01'); $to=$_GET['to'] ?? date('Y-m-d');
        try {
            $branch=Purchasing::scope($_GET['branch_id'] ?? null);
            if ($ready) {
                if ($isSuppliers) $suppliers=Purchasing::suppliers($branch);
                elseif ($isReport) $report=Purchasing::report($branch,(int)($_GET['supplier_id'] ?? 0),$from,$to);
                else {
                    $orders=Purchasing::orders($branch,(int)($_GET['supplier_id'] ?? 0));
                    if (!empty($_SESSION['purchase_selected'][$selectionKey])) {
                        try { $selected=Purchasing::detail((int)$_SESSION['purchase_selected'][$selectionKey]); }
                        catch (DomainException $e) { unset($_SESSION['purchase_selected'][$selectionKey]); }
                        if ($selected && (($branch && (int)$selected['branch_id']!==$branch) || (!empty($_GET['supplier_id']) && (int)$selected['supplier_id']!==(int)$_GET['supplier_id']))) { $selected=null; unset($_SESSION['purchase_selected'][$selectionKey]); }
                    }
                    if ($branch) { $options=Purchasing::supplierOptions($branch); $products=Purchasing::products($branch); }
                }
            }
        } catch (DomainException $e) { access_denied($e->getMessage()); }
        catch (InvalidArgumentException $e) { $error=$e->getMessage(); }
        if ($isReport && $ready && !$error && ($_GET['export'] ?? '')==='csv') {
            header('Content-Type: text/csv; charset=utf-8'); header('Content-Disposition: attachment; filename=purchasing-report.csv');
            $out=fopen('php://output','w');
            fputcsv($out,['Branch','Supplier','Ordered amount','Received cost','Returned value','Net received cost']);
            foreach ($report as $r) {
                $safe=static function($s){ return preg_match('/^[=+@\-\t\r\n]/u',(string)$s)?"'".$s:$s; };
                fputcsv($out,[$safe($r['branch_name']),$safe($r['supplier_name']),$r['ordered_amount'],$r['received_amount'],$r['returned_amount'],$r['net_amount']]);
            }
            fclose($out); return;
        }
        require __DIR__.'/../views/modules/purchasing.php';
    }
}
