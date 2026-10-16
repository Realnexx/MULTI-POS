<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!is_dir(__DIR__.'/../scratch'))mkdir(__DIR__.'/../scratch',0700,true);session_save_path(__DIR__.'/../scratch');
require_once __DIR__.'/../config/db.php';require_once __DIR__.'/../models/Setting.php';require_once __DIR__.'/../models/ErrorMonitor.php';require_once __DIR__.'/../models/DatabaseBackup.php';
try {ErrorMonitor::maintenance();$id=DatabaseBackup::runDue();echo $id?"Maintenance and scheduled backup completed.\n":"Maintenance completed; no backup due.\n";}
catch(Throwable $e){error_log('Application maintenance failed: '.get_class($e));fwrite(STDERR,"Maintenance failed. Check database access and private backup storage.\n");exit(1);}
