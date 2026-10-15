<?php
require 'app/auth/config/db_connection.php';
$_POST['action'] = 'get_live_results';
$_POST['competition_id'] = 3; // Hardcoding ID for testing (assume 3 is "matahum sa union")
$_SERVER['REQUEST_METHOD'] = 'POST';

// We can just include the handler and catch the JSON output
ob_start();
require 'app/admin/endpoints/live_results_handler.php';
$out = ob_get_clean();

echo "Raw Output:\n";
echo $out;
?>
