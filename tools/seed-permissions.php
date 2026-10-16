<?php
if(PHP_SAPI!=='cli') { http_response_code(403);exit('CLI only.'); }
if(!in_array('--apply',$argv,true)) { exit("Use --apply to seed missing permissions and protect Superadmin.\n"); }
if(!is_dir(__DIR__.'/../scratch')) mkdir(__DIR__.'/../scratch',0700,true);
session_save_path(__DIR__.'/../scratch');
require __DIR__.'/../config/db.php';
require __DIR__.'/../models/PermissionSeeder.php';
echo json_encode(in_array('--inventory-actions',$argv,true) ? PermissionSeeder::syncInventoryActions($conn) : PermissionSeeder::run($conn),JSON_PRETTY_PRINT).PHP_EOL;
