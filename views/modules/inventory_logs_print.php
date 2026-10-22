<?php
$money = function ($value) { return '₱' . number_format((float) $value, 2); };
$quantity = function ($value) { return number_format((float) $value, 0); };
$titles = ['dashboard'=>'Business Overview', 'sales'=>'Sales Report', 'inventory'=>'Stock Movements', 'expenses'=>'Expense Report', 'revenue'=>'Revenue Report'];
$report_title = $titles[$report_tab];
$date_label = ($date_from ?: 'All earlier dates') . ' to ' . ($date_to ?: 'Latest');
if (!$date_from && !$date_to) $date_label = 'All Dates';
$back_url = '?route=inventory/logs&' . http_build_query(['tab'=>$report_tab,'branch_id'=>$branch_id,'date_from'=>$date_from,'date_to'=>$date_to]);
$revenue = (float) ($sale_stats['revenue'] ?? 0);
$discounts = (float) ($sale_stats['discounts'] ?? 0);
$cards = ['Completed Sales'=>$money($revenue), 'Expenses'=>$money($expense_total), 'Revenue Less Expenses'=>$money($revenue - $expense_total), 'Completed Orders'=>($sale_stats['count'] ?? 0)];
$sections = [];
$report_note = 'Sales totals include completed orders. Revenue less expenses excludes cost of goods sold and unrecorded costs; it is not net profit. Stock figures show current stock, regardless of the date filter.';
if ($report_tab === 'sales') {
    $sections[] = ['title'=>count($rows).' Matching Sales', 'class'=>'report-sales-table', 'columns'=>['Order','Cashier','Products','Quantity','Total','Discount','Paid','Payment','Status','Date'], 'rows'=>array_map(function ($r) use ($money,$quantity) { return ['#'.$r['id'],$r['cashier_name'] ?? 'Staff',$r['products'] ?? '',$quantity($r['total_qty'] ?? 0),$money($r['total_amount']),$money($r['discount_amount']),$money($r['final_amount']),$r['payment_method'],$r['status'],$r['created_at']]; },$rows)];
} elseif ($report_tab === 'inventory') {
    $cards = ['Matching Movements'=>count($rows), 'Current Stock Value'=>$money($inventory_stats['total_valuation'] ?? 0), 'Low Stock Items'=>$inventory_stats['low_stock_count'] ?? 0];
    $sections[] = ['title'=>'Stock Movement History', 'columns'=>['Product','Movement','Quantity','Staff','Notes','Date'], 'rows'=>array_map(function ($r) use ($quantity) { return [$r['product_name'] ?? 'Deleted product',ucwords(str_replace('_',' ',$r['action'])),$quantity($r['quantity']),$r['user_name'] ?? 'Staff',$r['remarks'] ?? '',$r['created_at']]; },$rows)];
} elseif ($report_tab === 'expenses') {
    $cards = ['Matching Expenses'=>count($rows),'Expense Total'=>$money(array_sum(array_column($rows,'amount')))];
    $sections[] = ['title'=>'Recorded Expenses', 'columns'=>['Date','Category','Description','Branch','Amount'], 'rows'=>array_map(function ($r) use ($money) { return [$r['expense_date'],$r['category'],$r['description'],$r['branch_name'] ?: 'No Specific Branch',$money($r['amount'])]; },$rows)];
} elseif ($report_tab === 'revenue') {
    $cards = [];
    $balance = $revenue - $expense_total;
    $sections[] = ['title'=>'Revenue Statement', 'class'=>'print-revenue-statement', 'row_classes'=>['statement-section','','statement-deduction','statement-subtotal','statement-deduction','statement-balance','statement-memo','statement-memo'], 'columns'=>['Particulars','Amount (PHP)'],'rows'=>[
        ['Sales revenue',''],
        ['Gross sales',$money($revenue+$discounts)],
        ['Less: Sales discounts','('.$money($discounts).')'],
        ['Net sales revenue',$money($revenue)],
        ['Less: Recorded expenses','('.$money($expense_total).')'],
        ['Revenue less expenses',$balance < 0 ? '('.$money(abs($balance)).')' : $money($balance)],
        ['Completed transactions',number_format($sale_stats['count'] ?? 0)],
        ['Average order value',$money(($sale_stats['count'] ?? 0) > 0 ? $revenue/$sale_stats['count'] : 0)]
    ]];
} else {
    $sections[] = ['title'=>'Top Selling Products','columns'=>['Product','Quantity Sold'],'rows'=>array_map(function ($r) use ($quantity) { return [$r['name'] ?? $r['product_name'] ?? '',$quantity($r['total_qty'] ?? 0)]; },$top_selling)];
    $sections[] = ['title'=>'Branch Sales','columns'=>['Branch','Orders','Sales'],'rows'=>array_map(function ($r) use ($money) { return [$r['name'] ?? $r['branch_name'] ?? '',$r['total_orders'] ?? $r['order_count'] ?? 0,$money($r['total_revenue'] ?? 0)]; },$top_branches)];
}
// Print totals follow the displayed records and their filters.
$cards = [];
if ($report_tab === 'sales') {
    $completed = array_filter($rows, function($row) { return $row['status'] === 'Completed'; });
    $bottom_totals = [];
    $sections[0]['footer'] = ['Completed sales total', '', '', '', '', '', $money(array_sum(array_column($completed, 'final_amount'))), '', '', ''];
} elseif ($report_tab === 'expenses') {
    $bottom_totals = ['Total recorded expenses' => $money(array_sum(array_column($rows, 'amount')))];
} elseif ($report_tab === 'inventory') {
    $bottom_totals = ['Current inventory value' => $money($inventory_stats['total_valuation'] ?? 0)];
} else {
    $bottom_totals = ['Total completed sales' => $money($revenue), 'Less: Recorded expenses' => '('.$money($expense_total).')', 'Revenue less expenses' => $money($revenue-$expense_total)];
}
require __DIR__ . '/../layouts/report_print.php';
