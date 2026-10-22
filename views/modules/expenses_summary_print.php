<?php
$money = function ($value) { return '₱' . number_format((float) $value, 2); };
$print_orientation = !empty($monthly_report) ? 'portrait' : 'landscape';
$report_title = !empty($monthly_report) ? 'Monthly Sales and Expenses — '.date('F Y',strtotime($start_date)) : 'Daily Sales and Expenses';
$branch_name = $selected_branch_name === 'Global Network' ? 'All Branches' : $selected_branch_name;
$date_label = $start_date . ' to ' . $end_date;
$back_url = '?route=admin/expenses&' . http_build_query(['branch_id'=>$branch_id,'start_date'=>$start_date,'end_date'=>$end_date]);
$cards = [];
$bottom_totals = ['Total sales'=>$money($totals['sales']), 'Total expenses'=>$money($totals['expenses']+$totals['others']), 'Revenue less expenses'=>$money($totals['net'])];
$sections = [['title'=>'Daily Breakdown','class'=>'monthly-business-report','columns'=>['Day','Sales','Expenses','Revenue Less Expenses'],'rows'=>array_map(function ($r) use ($money) { return [date('d · D',strtotime($r['date'])),$money($r['sales']),$money($r['expenses']+$r['others']),$money($r['net'])]; },array_values($summary))]];
$sections[0]['footer'] = ['MONTH TOTAL', $money($totals['sales']), $money($totals['expenses']+$totals['others']), $money($totals['net'])];
$bottom_totals = [];
$report_note = 'Expenses include all categories. Revenue less expenses excludes product costs; it is not net profit.';
require __DIR__ . '/../layouts/report_print.php';
