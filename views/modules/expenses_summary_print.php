<?php
$money = function ($value) { return '₱' . number_format((float) $value, 2); };
$report_title = 'Daily Sales and Expenses';
$branch_name = $selected_branch_name === 'Global Network' ? 'All Branches' : $selected_branch_name;
$date_label = $start_date . ' to ' . $end_date;
$back_url = '?route=admin/expenses&' . http_build_query(['branch_id'=>$branch_id,'start_date'=>$start_date,'end_date'=>$end_date]);
$cards = ['Sales'=>$money($totals['sales']), 'Expenses'=>$money($totals['expenses'] + $totals['others']), 'Estimated Profit'=>$money($totals['net'])];
$sections = [['title'=>'Daily Totals','columns'=>['Date','Sales','Expenses','Estimated Profit'],'rows'=>array_map(function ($r) use ($money) { return [$r['date'],$money($r['sales']),$money($r['expenses']+$r['others']),$money($r['net'])]; },array_values($summary))]];
$report_note = 'Expenses include all categories. Estimated profit is sales minus recorded expenses.';
require __DIR__ . '/../layouts/report_print.php';
