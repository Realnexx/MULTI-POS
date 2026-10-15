<?php
if(PHP_SAPI!=='cli')exit('CLI only');session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';require_once __DIR__.'/../models/Monitoring.php';require __DIR__.'/../models/PermissionSeeder.php';Monitoring::migrate($conn);PermissionSeeder::run($conn);echo "Monitoring tables and permissions are ready. No fabricated traffic was seeded.\n";

