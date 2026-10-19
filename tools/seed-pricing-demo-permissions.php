<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to seed pricing/demo permissions.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';
$conn->multi_query(file_get_contents(__DIR__.'/migrations/20261019-pricing-demo-permissions.sql'));do{if($r=$conn->store_result())$r->free();}while($conn->more_results()&&$conn->next_result());echo "Pricing and demo request permissions installed. Existing revocations preserved.\n";
