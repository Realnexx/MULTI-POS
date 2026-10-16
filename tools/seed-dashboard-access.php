<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to grant dashboard access to staff roles.\n");
session_save_path(__DIR__.'/../scratch');
require __DIR__.'/../config/db.php';
require __DIR__.'/../models/PermissionSeeder.php';
echo json_encode(PermissionSeeder::syncDashboardAccess($conn),JSON_PRETTY_PRINT).PHP_EOL;
