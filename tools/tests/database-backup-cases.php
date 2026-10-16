<?php
if(PHP_SAPI!=='cli')exit;
mysqli_report(MYSQLI_REPORT_ERROR|MYSQLI_REPORT_STRICT);
$config=require __DIR__.'/../../config/config.local.php';$c=$config['database'];
$db=new mysqli($c['host'],$c['user'],$c['password'],$c['name']);$name='pos_backup_test_'.bin2hex(random_bytes(6));
$workspace=__DIR__.'/../../scratch/'.$name;
mkdir($workspace.'/models',0700,true);mkdir($workspace.'/config',0700,true);
copy(__DIR__.'/../../models/DatabaseBackup.php',$workspace.'/models/DatabaseBackup.php');copy(__DIR__.'/../../models/Setting.php',$workspace.'/models/Setting.php');
define('DB_HOST',$c['host']);define('DB_USER',$c['user']);define('DB_PASS',$c['password']);define('DB_NAME',$name);
$checks=0;function ok($v,$message){global $checks;if(!$v)throw new RuntimeException($message);$checks++;}
function fails($work,$message){try{$work();}catch(Throwable $e){ok(true,$message);return;}throw new RuntimeException($message);}
try{
 $db->query("CREATE DATABASE `$name`");$db->select_db($name);$db->set_charset('utf8mb4');$GLOBALS['conn']=$db;
 require $workspace.'/models/Setting.php';require $workspace.'/models/DatabaseBackup.php';DatabaseBackup::migrate();
 $db->query('CREATE TABLE settings(setting_key VARCHAR(100) PRIMARY KEY,setting_value TEXT,category VARCHAR(40)) ENGINE=InnoDB');
 $db->query('CREATE TABLE sample(id INT PRIMARY KEY,payload LONGBLOB NULL,amount DECIMAL(12,2)) ENGINE=InnoDB');
 $payload="private-test\0'\\; DROP TABLE sample; — ".random_bytes(64);$stmt=$db->prepare('INSERT INTO sample VALUES(0,?,12.34),(2,NULL,-0.01)');$stmt->bind_param('s',$payload);$stmt->execute();
 ok(!DatabaseBackup::settings()['enabled'],'Schedule defaults disabled');
 fails(function(){DatabaseBackup::save(['hour'=>24,'retention'=>7]);},'Invalid schedule rejected');
 fails(function(){DatabaseBackup::save(['hour'=>2,'retention'=>0]);},'Invalid retention rejected');
 DatabaseBackup::save(['hour'=>0,'retention'=>1]);
 file_put_contents($workspace.'/config/paymongo.key',base64_encode(random_bytes(32)));
 $id=DatabaseBackup::create();$path=DatabaseBackup::download($id);$raw=file_get_contents($path);
 ok(strpos($raw,'private-test')===false && strpos($raw,'CREATE TABLE')===false,'No plaintext data or schema');
 $records=iterator_to_array(DatabaseBackup::records($path));$last=end($records);ok($last['rows']>=2 && $last['tables']['sample']===2,'Complete authenticated manifest');
 $verified=DatabaseBackup::verify($id);ok($verified['tables']===3,'Restored all tables');ok((int)$db->query('SELECT COUNT(*) n FROM sample')->fetch_assoc()['n']===2,'Source untouched');
 ok($db->query("SHOW DATABASES LIKE 'pos_restore_check_%'")->num_rows===0,'Temporary verification database cleaned');
 fails(function()use($path){iterator_to_array(DatabaseBackup::records($path,base64_encode(random_bytes(32))));},'Wrong recovery key rejected');
 $bad=$workspace.'/corrupt.posbackup';$lines=file($path);$lines[1][5]=$lines[1][5]==='A'?'B':'A';file_put_contents($bad,implode('',$lines));fails(function()use($bad){iterator_to_array(DatabaseBackup::records($bad));},'Modified ciphertext rejected');
 file_put_contents($bad,implode('',array_slice(file($path),0,-1)));fails(function()use($bad){iterator_to_array(DatabaseBackup::records($bad));},'Truncation rejected');
 fails(function(){DatabaseBackup::download('../config.local.php');},'Path traversal rejected');
 // Ensure a newer timestamp so retention has deterministic ordering.
 $db->query("UPDATE database_backups SET created_at=DATE_SUB(UTC_TIMESTAMP(),INTERVAL 1 DAY)");$second=DatabaseBackup::create();
 ok(!is_file($path),'Retention removes oldest completed encrypted file');ok(is_file(DatabaseBackup::download($second)),'Retention keeps newest completed backup');
 $copy=DatabaseBackup::recoverCopy(DatabaseBackup::download($second),DatabaseBackup::recoveryKey());$recoveryName=$copy['database'];
 ok(preg_match('/^pos_recovered_[a-f0-9]{16}$/D',$recoveryName)===1,'Recovery only creates a uniquely named new database');
 $recovered=new mysqli(DB_HOST,DB_USER,DB_PASS,$recoveryName);$actual=$recovered->query('SELECT payload FROM sample WHERE id=0')->fetch_assoc()['payload'];ok($actual===$payload,'Recovery preserves exact binary data');$money=$recovered->query('SELECT payload,amount FROM sample WHERE id=2')->fetch_assoc();ok($money['payload']===null&&$money['amount']==='-0.01','Recovery preserves nulls and exact signed decimal amounts');$recovered->close();
 ok(file_get_contents($copy['recovered_keys'].'/paymongo.key')===file_get_contents($workspace.'/config/paymongo.key'),'Recovery preserves payment encryption key privately');
 fails(function()use($second){DatabaseBackup::recoverCopy(DatabaseBackup::download($second),base64_encode(random_bytes(32)));},'Wrong recovery key cannot leave a restored database');
 ok(DatabaseBackup::runDue()===null,'Disabled scheduler does not back up');
 DatabaseBackup::save(['enabled'=>1,'hour'=>0,'retention'=>2]);ok(DatabaseBackup::runDue()!==null,'Due scheduler creates backup');ok(DatabaseBackup::runDue()===null,'Due scheduler claims only once daily');
 $db->query('CREATE TABLE unsupported(id INT) ENGINE=MyISAM');fails(function(){DatabaseBackup::create();},'Nontransactional tables rejected');
 ok(DatabaseBackup::settings()['last_error']!=='','Failure visible in settings');
 echo "Passed $checks backup checks.\n";
}finally{
 if(isset($recoveryName)&&preg_match('/^pos_recovered_[a-f0-9]{16}$/D',$recoveryName))$db->query("DROP DATABASE `$recoveryName`");
 if(preg_match('/^pos_backup_test_[a-f0-9]{12}$/D',$name))$db->query("DROP DATABASE IF EXISTS `$name`");
 // Only remove files in this test's verified, unique scratch directory.
 $root=realpath(__DIR__.'/../../scratch');$target=realpath($workspace);
 if($root&&$target&&strpos($target,$root.DIRECTORY_SEPARATOR)===0&&basename($target)===$name){
  $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($target,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){if($f->isDir())rmdir($f->getPathname());else unlink($f->getPathname());}rmdir($target);
 }
}
