<?php
if(PHP_SAPI!=='cli'){http_response_code(403);exit;}
if(count($argv)!==3)exit("Usage: php tools/recover-database-copy.php ARCHIVE RECOVERY_KEY_FILE\nCreates a new database; never overwrites the live database.\n");
if(!is_dir(__DIR__.'/../scratch'))mkdir(__DIR__.'/../scratch',0700,true);session_save_path(__DIR__.'/../scratch');
require_once __DIR__.'/../config/db.php';require_once __DIR__.'/../models/DatabaseBackup.php';
try {
    $key=trim((string)file_get_contents($argv[2]));
    $result=DatabaseBackup::recoverCopy($argv[1],$key);
    echo json_encode($result,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\nValidate the recovered database and install recovered private keys before switching your deployment configuration. No live database was replaced.\n";
}catch(Throwable $e){fwrite(STDERR,'Recovery failed: '.$e->getMessage()."\n");exit(1);}
