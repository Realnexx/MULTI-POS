<?php
$t = $shift['totals'];
$money = static function($cents) { return '₱'.number_format($cents/100,2); };
$report_title = 'Cashier Shift Summary #'.(int)$shift['id'];
$store_name = Setting::get('store_name', 'Store');
$branch_name = $shift['branch_name'].' · '.$shift['register_name'];
$date_label = $shift['opened_at'].' to '.($shift['closed_at'] ?: 'Currently open');
$back_url = '?route=pos/shifts';
$cards = [];
$sections = [[
    'title'=>'Shift Details', 'columns'=>['Detail','Value'], 'rows'=>[
        ['Cashier',$shift['cashier_name']],
        ['Status',ucwords(str_replace('_',' ',$shift['status']))],
        ['Opened',$shift['opened_at']],
        ['Closed',$shift['closed_at'] ?: 'Not closed'],
    ]
], [
    'title'=>'Drawer Reconciliation', 'columns'=>['Particulars','Amount (PHP)'],
    'class'=>'monthly-business-report', 'rows'=>[
        ['Opening float',$money($t['opening_cents'])],
        ['Cash sales',$money($t['cash_sales_cents'])],
        ['Cash-in',$money($t['cash_in_cents'])],
        ['Cash-out',$money($t['cash_out_cents'])],
        ['Cash refunds',$money($t['cash_refunds_cents'])],
        ['Authorized corrections',$money($t['correction_cents'])],
        ['Expected cash',$money($shift['status']==='open' ? $t['expected_cents'] : CashShift::cents($shift['expected_cash'],true))],
    ]
]];
if ($shift['status'] !== 'open') {
    $sections[1]['rows'][] = ['Counted cash',$money(CashShift::cents($shift['counted_cash']))];
    $sections[1]['footer'] = ['Overage / shortage',$money(CashShift::cents($shift['difference'],true))];
}
$paymentRows=[];
foreach ($t['payments'] as $method=>$amount) $paymentRows[]=[$method,$money($amount)];
$sections[]=['title'=>'Net Payments by Method','class'=>'monthly-business-report','columns'=>['Payment Method','Net Amount (PHP)'],'rows'=>$paymentRows];
$report_note='Electronic payments do not add drawer cash. Cash movements and refunds retain their recorded signs. '.($shift['close_notes'] ? 'Closing notes: '.$shift['close_notes'] : '');
$print_orientation='portrait';
require __DIR__.'/../layouts/report_print.php';
