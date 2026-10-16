<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to add recurring schedules, blocks and audit events.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';
foreach(['20261016-cashier-schedules.sql','20261016-cashier-schedule-planning.sql'] as $file){
 $conn->multi_query(file_get_contents(__DIR__.'/migrations/'.$file));
 do{if($r=$conn->store_result())$r->free();}while($conn->more_results()&&$conn->next_result());
}
echo "Recurring schedules, blocks, attendance and audit tables ready. Existing schedules preserved.\n";
