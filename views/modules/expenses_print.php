<?php
$money = function ($value) { return '₱' . number_format((float) $value, 2); };
$report_title = 'Expense Report';
$branch_name = $selected_branch_name === 'Global Network' ? 'All Branches' : $selected_branch_name;
$date_label = ($start_date ?: 'All earlier dates') . ' to ' . ($end_date ?: 'Latest');
$back_url = '?route=admin/expenses&' . http_build_query(['branch_id'=>$branch_id,'start_date'=>$start_date,'end_date'=>$end_date]);
$cards = [];
$bottom_totals = ['Total recorded expenses'=>$money(array_sum(array_column($expenses,'amount')))];
$category_labels = ['Utilities'=>'Bills','Repair'=>'Repairs','Salary'=>'Staff Pay','Marketing'=>'Advertising','Other'=>'Other'];
$sections = [['title'=>'Expense List','columns'=>['Date','Description','Category','Branch','Amount'],'rows'=>array_map(function ($r) use ($money,$category_labels) { return [$r['expense_date'],$r['description'] ?: 'No description',$category_labels[$r['category']] ?? $r['category'],$r['branch_name'] ?: 'No Specific Branch',$money($r['amount'])]; },$expenses)]];
$report_note = 'Revenue less expenses excludes product costs; it is not net profit. Summary totals cover the selected dates and branch; search only filters the expense list.';
require __DIR__ . '/../layouts/report_print.php';
