<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!in_array('--apply',$argv,true))exit("Use --apply to install checkout, access, error monitoring and backup support.\n");
if(!is_dir(__DIR__.'/../scratch'))mkdir(__DIR__.'/../scratch',0700,true);session_save_path(__DIR__.'/../scratch');
require_once __DIR__.'/../config/db.php';require_once __DIR__.'/../models/SystemPolicy.php';require_once __DIR__.'/../models/CheckoutPolicy.php';require_once __DIR__.'/../models/ErrorMonitor.php';require_once __DIR__.'/../models/DatabaseBackup.php';
SystemPolicy::migrate($conn);CheckoutPolicy::migrate($conn);ErrorMonitor::migrate($conn);DatabaseBackup::migrate();
echo "System settings support installed. New tax/charge rules, 2FA and daily backups require explicit configuration.\n";
