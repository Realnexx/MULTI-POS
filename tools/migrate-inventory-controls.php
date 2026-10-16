<?php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if (!in_array('--apply', $argv, true)) exit("Use --apply to add inventory control tables. Existing stock is unchanged.\n");
session_save_path(__DIR__.'/../scratch');
require __DIR__.'/../config/db.php';
$conn->multi_query(file_get_contents(__DIR__.'/migrations/20261016-inventory-controls.sql'));
do { if ($result=$conn->store_result()) $result->free(); } while ($conn->more_results() && $conn->next_result());
echo "Inventory control tables ready. Existing stock unchanged.\n";
