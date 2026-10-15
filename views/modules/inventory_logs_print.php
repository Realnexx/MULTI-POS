<?php
$money = function ($value) { return '₱' . number_format((float) $value, 2); };
$quantity = function ($value) { return number_format((float) $value, 2); };
$titles = ['dashboard'=>'Business Overview', 'sales'=>'Sales Report', 'inventory'=>'Stock Movements', 'expenses'=>'Expense Report', 'revenue'=>'Revenue Report'];
$report_title = $titles[$report_tab];
$date_label = ($date_from ?: 'All earlier dates') . ' to ' . ($date_to ?: 'Latest');
if (!$date_from && !$date_to) $date_label = 'All Dates';
$back_url = '?route=inventory/logs&' . http_build_query(['tab'=>$report_tab,'branch_id'=>$branch_id,'date_from'=>$date_from,'date_to'=>$date_to]);
$revenue = (float) ($sale_stats['revenue'] ?? 0);
$discounts = (float) ($sale_stats['discounts'] ?? 0);
$cards = ['Completed Sales'=>$money($revenue), 'Expenses'=>$money($expense_total), 'Estimated Profit'=>$money($revenue - $expense_total), 'Completed Orders'=>($sale_stats['count'] ?? 0)];
$sections = [];
$report_note = 'Sales totals include completed orders. Estimated profit is sales minus recorded expenses. Stock figures show current stock, regardless of the date filter.';
if ($report_tab === 'sales') {
    $sections[] = ['title'=>count($rows).' Matching Sales', 'class'=>'report-sales-table', 'columns'=>['Order','Cashier','Products','Quantity','Total','Discount','Paid','Payment','Status','Date'], 'rows'=>array_map(function ($r) use ($money,$quantity) { return ['#'.$r['id'],$r['cashier_name'] ?? 'Staff',$r['products'] ?? '',$quantity($r['total_qty'] ?? 0),$money($r['total_amount']),$money($r['discount_amount']),$money($r['final_amount']),$r['payment_method'],$r['status'],$r['created_at']]; },$rows)];
} elseif ($report_tab === 'inventory') {
    $cards = ['Matching Movements'=>count($rows), 'Current Stock Value'=>$money($inventory_stats['total_valuation'] ?? 0), 'Low Stock Items'=>$inventory_stats['low_stock_count'] ?? 0];
    $sections[] = ['title'=>'Stock Movement History', 'columns'=>['Product','Movement','Quantity','Staff','Notes','Date'], 'rows'=>array_map(function ($r) use ($quantity) { return [$r['product_name'] ?? 'Deleted product',ucwords(str_replace('_',' ',$r['action'])),$quantity($r['quantity']),$r['user_name'] ?? 'Staff',$r['remarks'] ?? '',$r['created_at']]; },$rows)];
} elseif ($report_tab === 'expenses') {
    $cards = ['Matching Expenses'=>count($rows),'Expense Total'=>$money(array_sum(array_column($rows,'amount')))];
    $sections[] = ['title'=>'Recorded Expenses', 'columns'=>['Date','Category','Description','Branch','Amount'], 'rows'=>array_map(function ($r) use ($money) { return [$r['expense_date'],$r['category'],$r['description'],$r['branch_name'] ?: 'No Specific Branch',$money($r['amount'])]; },$rows)];
} elseif ($report_tab === 'revenue') {
    $sections[] = ['title'=>'Sales and Expenses Summary','columns'=>['Item','Amount'],'rows'=>[['Sales before discounts',$money($revenue+$discounts)],['Discounts',$money($discounts)],['Completed sales after discounts',$money($revenue)],['Recorded expenses',$money($expense_total)],['Estimated profit',$money($revenue-$expense_total)]]];
} else {
    $sections[] = ['title'=>'Top Selling Products','columns'=>['Product','Quantity Sold'],'rows'=>array_map(function ($r) use ($quantity) { return [$r['name'] ?? $r['product_name'] ?? '',$quantity($r['total_qty'] ?? 0)]; },$top_selling)];
    $sections[] = ['title'=>'Branch Sales','columns'=>['Branch','Orders','Sales'],'rows'=>array_map(function ($r) use ($money) { return [$r['name'] ?? $r['branch_name'] ?? '',$r['total_orders'] ?? $r['order_count'] ?? 0,$money($r['total_revenue'] ?? 0)]; },$top_branches)];
}
require __DIR__ . '/../layouts/report_print.php';
