<?php
if(PHP_SAPI!=='cli')exit('CLI only');
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require __DIR__.'/../models/AuthSecurity.php';
AuthSecurity::migrate($conn);echo "Authentication security tables are ready.\n";
