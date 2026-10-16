<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to install PayMongo integration (disabled by default).\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';
$conn->multi_query(file_get_contents(__DIR__.'/migrations/20261016-paymongo.sql'));
do{if($r=$conn->store_result())$r->free();}while($conn->more_results()&&$conn->next_result());
require __DIR__.'/../models/PayMongo.php';PayMongo::encryptionKey();
echo "PayMongo installed. Enter keys in System Settings → PayMongo. No payment requests were sent.\n";
