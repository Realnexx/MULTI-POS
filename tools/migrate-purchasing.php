<?php
if (PHP_SAPI!=='cli') { http_response_code(403); exit; }
if (!in_array('--apply',$argv,true)) exit("Use --apply to add purchasing tables and permissions. Existing stock is unchanged.\n");
session_save_path(__DIR__.'/../scratch');
require __DIR__.'/../config/db.php';
$conn->multi_query(file_get_contents(__DIR__.'/migrations/20261016-purchasing.sql'));
do { if ($r=$conn->store_result()) $r->free(); } while ($conn->more_results() && $conn->next_result());
require __DIR__.'/../models/PermissionSeeder.php';
PermissionSeeder::syncInventoryActions($conn);
echo "Purchasing tables and action permissions ready. Existing stock unchanged.\n";
