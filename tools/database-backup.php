<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(!is_dir(__DIR__.'/../scratch'))mkdir(__DIR__.'/../scratch',0700,true);session_save_path(__DIR__.'/../scratch');
require_once __DIR__.'/../config/db.php';require_once __DIR__.'/../models/Setting.php';require_once __DIR__.'/../models/DatabaseBackup.php';
try {
    $action=$argv[1]??'';
    if($action==='install'){DatabaseBackup::migrate();echo "Backup storage installed. Configure System Settings → Database backups.\n";}
    elseif($action==='configure' && count($argv)===5){DatabaseBackup::save(['enabled'=>$argv[2]==='1','hour'=>$argv[3],'retention'=>$argv[4]]);echo "Backup schedule saved.\n";}
    elseif($action==='run-due'){echo DatabaseBackup::runDue()?"Scheduled backup completed.\n":"No backup due.\n";}
    elseif($action==='create'){echo 'Backup completed: '.DatabaseBackup::create()."\n";}
    elseif($action==='verify-latest'){$rows=DatabaseBackup::recent();$id=null;foreach($rows as $row)if($row['status']==='complete'){$id=$row['id'];break;}if(!$id)throw new DomainException('No completed backup.');echo json_encode(DatabaseBackup::verify($id))."\n";}
    elseif($action==='verify' && isset($argv[2])){echo json_encode(DatabaseBackup::verify($argv[2]))."\n";}
    else {fwrite(STDERR,"Usage: php tools/database-backup.php install|configure ENABLED HOUR KEEP|run-due|create|verify ID\n");exit(2);}
}catch(Throwable $e){fwrite(STDERR,"Backup operation failed: ".$e->getMessage()."\n");exit(1);}
