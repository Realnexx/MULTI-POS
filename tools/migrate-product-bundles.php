<?php
if(PHP_SAPI!=='cli'||!in_array('--apply',$argv,true))exit("Run with --apply from the command line.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';
$conn->multi_query(preg_replace('/^\xEF\xBB\xBF/','',file_get_contents(__DIR__.'/migrations/20261022-product-bundles.sql')));do{if($result=$conn->store_result())$result->free();}while($conn->more_results()&&$conn->next_result());echo "Product bundle schema installed.\n";
