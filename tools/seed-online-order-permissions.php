<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to seed missing Online Orders permissions.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require __DIR__.'/../models/PermissionSeeder.php';
echo json_encode(PermissionSeeder::syncOnlineOrderActions($conn),JSON_PRETTY_PRINT).PHP_EOL;
