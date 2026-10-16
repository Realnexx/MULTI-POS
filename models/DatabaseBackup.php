<?php
/** Encrypted, streaming logical backups. Never executes a supplied SQL file on the live database. */
class DatabaseBackup {
    private static function db() { return $GLOBALS['conn']; }
    private static function ident($name) { return '`'.str_replace('`','``',$name).'`'; }
    private static function root() {
        $root=__DIR__.'/../config/backups';
        if(!is_dir($root) && !mkdir($root,0700,true))throw new RuntimeException('Private backup storage is unavailable.');
        if(!is_file($root.'/.htaccess'))file_put_contents($root.'/.htaccess',"Require all denied\nDeny from all\n");
        return realpath($root);
    }
    public static function migrate() {
        self::db()->query("CREATE TABLE IF NOT EXISTS database_backups (id CHAR(32) PRIMARY KEY, status VARCHAR(16) NOT NULL, bytes BIGINT UNSIGNED NOT NULL DEFAULT 0, sha256 CHAR(64) NULL, table_count INT NOT NULL DEFAULT 0, row_count BIGINT NOT NULL DEFAULT 0, actor_id INT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, verified_at DATETIME NULL, error_code VARCHAR(80) NULL, INDEX(created_at)) ENGINE=InnoDB");
    }
    public static function authorize($actor,$password) {
        require_once __DIR__.'/User.php';require_once __DIR__.'/AuthSecurity.php';
        if(!$actor || !User::has_permission($actor,'manage_settings') || !User::has_all_branch_access($actor))throw new DomainException('Only a system administrator can manage database backups.');
        if(AuthSecurity::consume('backup-confirm',(string)$actor,5,900))throw new DomainException('Too many attempts. Try again in 15 minutes.');
        $user=User::find_by_id($actor);
        if(!is_string($password)||!password_verify($password,$user['password']??''))throw new DomainException('Enter your current account password to continue.');
        AuthSecurity::clear_limit('backup-confirm',(string)$actor);
    }
    public static function settings() {
        require_once __DIR__.'/Setting.php';
        return ['enabled'=>Setting::get('backup_enabled','0')==='1','hour'=>(int)Setting::get('backup_hour','2'),'retention'=>(int)Setting::get('backup_retention','7'),'last_run'=>Setting::get('backup_last_run',''),'last_success'=>Setting::get('backup_last_success',''),'last_error'=>Setting::get('backup_last_error',''),'runner_seen'=>Setting::get('backup_runner_seen','')];
    }
    public static function save(array $input) {
        $hour=filter_var($input['hour']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>0,'max_range'=>23]]);
        $keep=filter_var($input['retention']??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>1,'max_range'=>90]]);
        if($hour===false||$keep===false)throw new DomainException('Choose an hour from 0–23 and keep 1–90 backups.');
        Setting::set('backup_enabled',empty($input['enabled'])?'0':'1','backup');
        Setting::set('backup_hour',(string)$hour,'backup');Setting::set('backup_retention',(string)$keep,'backup');
    }
    public static function recent() { return self::db()->query('SELECT * FROM database_backups ORDER BY created_at DESC,id DESC LIMIT 90')->fetch_all(MYSQLI_ASSOC); }
    private static function key() {
        $path=__DIR__.'/../config/backup.key';
        if(!is_file($path)) {
            $f=@fopen($path,'x');if($f){$value=base64_encode(random_bytes(32));if(fwrite($f,$value)!==strlen($value))throw new RuntimeException('Backup key could not be saved.');fclose($f);@chmod($path,0600);}
        }
        $key=base64_decode((string)@file_get_contents($path),true);
        if($key===false||strlen($key)!==32)throw new RuntimeException('Backup encryption key is unavailable.');return $key;
    }
    public static function recoveryKey() { return base64_encode(self::key()); }
    private static function path($id) {
        if(!is_string($id)||!preg_match('/^[a-f0-9]{32}$/D',$id))throw new DomainException('Invalid backup.');
        return self::root().DIRECTORY_SEPARATOR.$id.'.posbackup';
    }
    public static function download($id) {
        $s=self::db()->prepare("SELECT * FROM database_backups WHERE id=? AND status='complete'");$s->bind_param('s',$id);$s->execute();$row=$s->get_result()->fetch_assoc();
        $path=self::path($id);
        if(!$row||!is_file($path)||!hash_equals($row['sha256'],hash_file('sha256',$path)))throw new DomainException('Backup is missing or failed its integrity check.');
        return $path;
    }
    private static function write($f,$value) { if(fwrite($f,$value)!==strlen($value))throw new RuntimeException('Backup storage is full or unavailable.'); }
    private static function record($f,$record,$key,$id,&$seq) {
        $plain=json_encode($record,JSON_THROW_ON_ERROR);$iv=random_bytes(12);$tag='';
        $cipher=openssl_encrypt($plain,'aes-256-gcm',$key,OPENSSL_RAW_DATA,$iv,$tag,$id.':'.$seq);
        if($cipher===false)throw new RuntimeException('Backup encryption failed.');
        self::write($f,base64_encode($iv.$tag.$cipher)."\n");$seq++;
    }
    private static function locked(callable $work) {
        $db=self::db();$name='pos-backup-'.substr(hash('sha256',DB_NAME),0,40);
        $s=$db->prepare('SELECT GET_LOCK(?,0) acquired');$s->bind_param('s',$name);$s->execute();
        if((int)$s->get_result()->fetch_assoc()['acquired']!==1)throw new DomainException('Another backup or verification is running.');
        try{return $work();}finally{$s=$db->prepare('SELECT RELEASE_LOCK(?)');$s->bind_param('s',$name);$s->execute();}
    }
    public static function create($actor=null) {
        return self::locked(function()use($actor){
            set_time_limit(0);$db=self::db();$id=bin2hex(random_bytes(16));$file=self::path($id);$temp=$file.'.partial';$f=null;$transaction=false;
            $s=$db->prepare("INSERT INTO database_backups(id,status,actor_id,created_at) VALUES (?,'running',?,UTC_TIMESTAMP())");$s->bind_param('si',$id,$actor);$s->execute();
            try {
                $tables=[];$result=$db->query('SHOW FULL TABLES');while($r=$result->fetch_row()){if($r[1]!=='BASE TABLE')throw new DomainException('This database contains views. Ask your administrator to configure a database-native backup.');$tables[]=$r[0];}
                // A consistent snapshot cannot cover nontransactional tables or concurrent DDL.
                $engines=$db->query("SELECT TABLE_NAME,ENGINE FROM information_schema.tables WHERE TABLE_SCHEMA=DATABASE() AND TABLE_TYPE='BASE TABLE'");
                while($r=$engines->fetch_assoc())if(strtoupper($r['ENGINE'])!=='INNODB')throw new DomainException('All tables must use InnoDB for consistent application backups.');
                foreach(['TRIGGERS','ROUTINES','EVENTS'] as $kind){$column=$kind==='TRIGGERS'?'TRIGGER_SCHEMA':($kind==='ROUTINES'?'ROUTINE_SCHEMA':'EVENT_SCHEMA');if((int)$db->query("SELECT COUNT(*) n FROM information_schema.$kind WHERE $column=DATABASE()")->fetch_assoc()['n'])throw new DomainException('Database routines, triggers or events require a database-native backup.');}
                $key=self::key();$f=fopen($temp,'x+b');if(!$f)throw new RuntimeException('Backup storage unavailable.');@chmod($temp,0600);
                self::write($f,"POSBACKUP1 $id\n");$seq=0;$rows=0;$counts=[];
                $db->query('SET TRANSACTION ISOLATION LEVEL REPEATABLE READ');$db->query('START TRANSACTION WITH CONSISTENT SNAPSHOT');$transaction=true;
                // Acquire metadata locks before dumping so schema changes cannot interleave.
                foreach($tables as $table)$db->query('SELECT 1 FROM '.self::ident($table).' LIMIT 0');
                foreach($tables as $table){
                    $schema=$db->query('SHOW CREATE TABLE '.self::ident($table))->fetch_row()[1];self::record($f,['type'=>'table','name'=>$table,'sql'=>$schema],$key,$id,$seq);
                    $result=$db->query('SELECT * FROM '.self::ident($table),MYSQLI_USE_RESULT);$count=0;
                    while($row=$result->fetch_assoc()){
                        $values=[];foreach($row as $col=>$value)$values[$col]=$value===null?null:base64_encode($value);
                        self::record($f,['type'=>'row','table'=>$table,'values'=>$values],$key,$id,$seq);$count++;$rows++;
                    }$result->free();$counts[$table]=$count;
                }
                // Include application encryption keys, never deployment DB/SMTP credentials.
                foreach(['paymongo.key','security.key'] as $keyFile){$secretPath=__DIR__.'/../config/'.$keyFile;if(is_file($secretPath))self::record($f,['type'=>'private_key','name'=>$keyFile,'value'=>base64_encode(file_get_contents($secretPath))],$key,$id,$seq);}
                self::record($f,['type'=>'end','tables'=>$counts,'rows'=>$rows,'records'=>$seq],$key,$id,$seq);
                $db->commit();$transaction=false;fflush($f);fclose($f);$f=null;
                if(!rename($temp,$file))throw new RuntimeException('Backup could not be finalized.');
                $bytes=filesize($file);$hash=hash_file('sha256',$file);$n=count($tables);
                $s=$db->prepare("UPDATE database_backups SET status='complete',bytes=?,sha256=?,table_count=?,row_count=? WHERE id=?");$s->bind_param('isiis',$bytes,$hash,$n,$rows,$id);$s->execute();
                Setting::set('backup_last_success',gmdate('Y-m-d H:i:s'),'backup');Setting::set('backup_last_error','','backup');self::prune();return $id;
            }catch(Throwable $e){
                if($transaction)$db->rollback();if(is_resource($f))fclose($f);if(is_file($temp))unlink($temp);
                $s=$db->prepare("UPDATE database_backups SET status='failed',error_code='backup_failed' WHERE id=?");$s->bind_param('s',$id);$s->execute();Setting::set('backup_last_error','Backup failed. Check storage, database permissions and supported table types.','backup');throw $e;
            }
        });
    }
    private static function prune() {
        $keep=max(1,min(90,self::settings()['retention']));$rows=self::db()->query("SELECT id FROM database_backups WHERE status='complete' ORDER BY created_at DESC,id DESC LIMIT 100000 OFFSET $keep")->fetch_all(MYSQLI_ASSOC);
        foreach($rows as $row){$p=self::path($row['id']);if(is_file($p)&&!unlink($p))continue;$s=self::db()->prepare("UPDATE database_backups SET status='pruned' WHERE id=?");$s->bind_param('s',$row['id']);$s->execute();}
    }
    /** Read authenticated records; final record detects truncation, sequence AAD detects reordering. */
    public static function records($path,$recoveryKey=null) {
        $key=$recoveryKey===null?self::key():base64_decode($recoveryKey,true);
        if($key===false||strlen($key)!==32)throw new DomainException('Invalid recovery key.');
        $f=fopen($path,'rb');if(!$f)throw new DomainException('Backup unavailable.');$seq=0;$end=false;
        try {
            $header=fgets($f);if(!preg_match('/^POSBACKUP1 ([a-f0-9]{32})\n$/D',$header??'',$m))throw new DomainException('Invalid backup format.');
            while(($line=fgets($f))!==false){
                if($end)throw new DomainException('Unexpected data after backup footer.');
                $raw=base64_decode(trim($line),true);if($raw===false||strlen($raw)<28)throw new DomainException('Damaged backup.');
                $plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',$key,OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16),$m[1].':'.$seq);
                if($plain===false)throw new DomainException('Backup authentication failed.');$record=json_decode($plain,true,512,JSON_THROW_ON_ERROR);
                if(($record['type']??'')==='end'){if($record['records']!==$seq)throw new DomainException('Invalid backup footer.');$end=true;}
                $seq++;yield $record;
            }if(!$end)throw new DomainException('Backup is incomplete.');
        }finally{fclose($f);}
    }
    public static function verify($id) {
        return self::locked(function()use($id){
            $path=self::download($id);$db=self::db();
            $s=$db->prepare('UPDATE database_backups SET verified_at=NULL,error_code=NULL WHERE id=?');$s->bind_param('s',$id);$s->execute();
            try{return self::restore($path,null,false,$id);}
            catch(Throwable $e){$s=$db->prepare("UPDATE database_backups SET error_code='verification_failed' WHERE id=?");$s->bind_param('s',$id);$s->execute();throw $e;}
        });
    }
    public static function recoverCopy($path,$recoveryKey) {
        if(PHP_SAPI!=='cli')throw new DomainException('Recovery is available only to the server administrator.');
        return self::locked(function()use($path,$recoveryKey){return self::restore($path,$recoveryKey,true,null);});
    }
    private static function restore($path,$recoveryKey,$keep,$id) {
            set_time_limit(0);$name=($keep?'pos_recovered_':'pos_restore_check_').bin2hex(random_bytes(8));$db=self::db();$test=null;$created=false;$success=false;$counts=[];$footer=null;$keys=[];
            // Verification needs CREATE/DROP DATABASE privileges; it never selects the live database.
            try {
                $db->query('CREATE DATABASE '.self::ident($name).' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');$created=true;
                $test=new mysqli(DB_HOST,DB_USER,DB_PASS,$name);$test->set_charset('utf8mb4');$test->query('SET FOREIGN_KEY_CHECKS=0');$test->query("SET SESSION sql_mode='STRICT_ALL_TABLES,NO_AUTO_VALUE_ON_ZERO'");
                foreach(self::records($path,$recoveryKey) as $r){
                    if($r['type']==='table'){$test->query($r['sql']);$counts[$r['name']]=0;}
                    elseif($r['type']==='row'){
                        $columns=[];$values=[];foreach($r['values'] as $col=>$v){$columns[]=self::ident($col);$values[]=$v===null?'NULL':"X'".bin2hex(base64_decode($v,true))."'";}
                        $test->query('INSERT INTO '.self::ident($r['table']).' ('.implode(',',$columns).') VALUES ('.implode(',',$values).')');$counts[$r['table']]++;
                    }elseif($r['type']==='private_key' && in_array($r['name']??'', ['paymongo.key','security.key'],true))$keys[$r['name']]=base64_decode($r['value'],true);
                    elseif($r['type']==='end')$footer=$r;
                }
                if(!$footer||$counts!==$footer['tables']||array_sum($counts)!==$footer['rows'])throw new RuntimeException('Restored counts differ from the backup.');
                foreach($counts as $table=>$count){if((int)$test->query('SELECT COUNT(*) n FROM '.self::ident($table))->fetch_assoc()['n']!==$count)throw new RuntimeException('Restore row count mismatch.');$check=$test->query('CHECK TABLE '.self::ident($table))->fetch_all(MYSQLI_ASSOC);foreach($check as $row)if($row['Msg_type']==='error')throw new RuntimeException('Restored table check failed.');}
                $keyDir=null;
                if($keep && $keys){$keyDir=self::root().DIRECTORY_SEPARATOR.$name;if(!mkdir($keyDir,0700))throw new RuntimeException('Recovered key storage unavailable.');foreach($keys as $file=>$value){if($value===false||file_put_contents($keyDir.DIRECTORY_SEPARATOR.$file,$value)===false)throw new RuntimeException('Recovered key could not be saved.');@chmod($keyDir.DIRECTORY_SEPARATOR.$file,0600);}}
                if($id!==null){$s=$db->prepare('UPDATE database_backups SET verified_at=UTC_TIMESTAMP() WHERE id=?');$s->bind_param('s',$id);$s->execute();}
                $success=true;return ['tables'=>count($counts),'rows'=>array_sum($counts),'database'=>$keep?$name:null,'recovered_keys'=>$keyDir];
            }finally{
                if($test)$test->close();
                if($created && (!$keep||!$success) && preg_match('/^pos_(restore_check|recovered)_[a-f0-9]{16}$/D',$name) && $name!==DB_NAME)$db->query('DROP DATABASE '.self::ident($name));
            }
    }
    public static function runDue() {
        Setting::set('backup_runner_seen',gmdate('Y-m-d H:i:s'),'backup');$c=self::settings();
        if(!$c['enabled'])return null;
        $now=new DateTimeImmutable('now',new DateTimeZone('Asia/Manila'));$due=$now->setTime($c['hour'],0);
        if($now<$due || ($c['last_run']!=='' && $c['last_run']>=$due->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s')))return null;
        // Serialize the due claim separately. create() obtains the same lock after the claim.
        $claimed=self::locked(function()use($due){$c=self::settings();$stamp=$due->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');if($c['last_run']!==''&&$c['last_run']>=$stamp)return false;Setting::set('backup_last_run',gmdate('Y-m-d H:i:s'),'backup');return true;});
        return $claimed?self::create():null;
    }
}
