<?php
// Mock Environment
$_SERVER['REQUEST_METHOD'] = 'POST';
$_POST['action'] = 'get_live_results';
$_POST['competition_id'] = 1; 

// Run Handler
require_once '../app/admin/endpoints/live_results_handler.php';
?>
