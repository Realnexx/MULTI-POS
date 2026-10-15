<?php
if(PHP_SAPI!=='cli')exit;session_save_path(__DIR__.'/../../scratch');require __DIR__.'/../../config/db.php';require __DIR__.'/../../models/AuthSecurity.php';
$s=json_decode(file_get_contents(__DIR__.'/../../scratch/rbac-test-state.json'),true);if(!preg_match('/^pos_rbac_test_[a-f0-9]+$/',$s['database']))exit(1);$conn->select_db($s['database']);$token=AuthSecurity::issue_reset($s['users']['viewer']);file_put_contents(__DIR__.'/../../scratch/auth-design-token.json',json_encode(['token'=>$token]));
