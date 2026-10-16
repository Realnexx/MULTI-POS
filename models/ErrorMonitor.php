<?php
require_once __DIR__.'/Setting.php';
class ErrorMonitor {
    private static $busy=false;
    public static function migrate($db) {
        $db->query("CREATE TABLE IF NOT EXISTS application_errors(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,fingerprint CHAR(64) NOT NULL UNIQUE,error_type VARCHAR(30) NOT NULL,source_file VARCHAR(120) NOT NULL,source_line INT NOT NULL,route VARCHAR(120) NOT NULL,occurrences INT NOT NULL DEFAULT 1,first_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,last_seen DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,acknowledged_at DATETIME NULL,acknowledged_by INT NULL,INDEX(last_seen)) ENGINE=InnoDB");
    }
    public static function record($type,$message,$file,$line) {
        if(self::$busy)return;self::$busy=true;
        try {
            $db=$GLOBALS['conn'];$source=basename($file);$route=class_exists('Monitoring')?Monitoring::route():'maintenance';
            // Messages, request bodies, query strings, credentials and stack arguments are never stored.
            $fingerprint=hash('sha256',$type.'|'.$file.'|'.$line.'|'.$message);$type=substr($type,0,30);$source=substr($source,0,120);
            $s=$db->prepare('INSERT INTO application_errors(fingerprint,error_type,source_file,source_line,route,first_seen,last_seen) VALUES (?,?,?,?,?,UTC_TIMESTAMP(),UTC_TIMESTAMP()) ON DUPLICATE KEY UPDATE occurrences=occurrences+1,last_seen=UTC_TIMESTAMP(),acknowledged_at=NULL,acknowledged_by=NULL');$s->bind_param('sssis',$fingerprint,$type,$source,$line,$route);$s->execute();
        }catch(Throwable $e){/* Monitoring failure must not recursively break requests. */}finally{self::$busy=false;}
    }
    public static function start() {
        if(PHP_SAPI==='cli')return;
        ini_set('display_errors','0');ini_set('display_startup_errors','0');
        set_error_handler(function($type,$message,$file,$line){if(error_reporting()&$type)self::record('php_'.$type,$message,$file,$line);return false;});
        set_exception_handler(function($e){self::record(get_class($e),$e->getMessage(),$e->getFile(),$e->getLine());http_response_code(500);if(strpos($_SERVER['SCRIPT_NAME']??'','/ajax/')!==false){header('Content-Type: application/json');echo json_encode(['success'=>false,'message'=>'An unexpected error occurred. Please contact an administrator.']);}else echo 'An unexpected error occurred. Please contact an administrator.';});
        register_shutdown_function(function(){$e=error_get_last();if($e&&in_array($e['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true))self::record('fatal_'.$e['type'],$e['message'],$e['file'],$e['line']);});
    }
    public static function rows() {return $GLOBALS['conn']->query('SELECT * FROM application_errors ORDER BY last_seen DESC LIMIT 200')->fetch_all(MYSQLI_ASSOC);}
    public static function acknowledge($id,$actor) {$id=(int)$id;if(!$id)throw new DomainException('Choose an error record.');$s=$GLOBALS['conn']->prepare('UPDATE application_errors SET acknowledged_at=UTC_TIMESTAMP(),acknowledged_by=? WHERE id=?');$s->bind_param('ii',$actor,$id);$s->execute();}
    public static function save($data) {
        foreach(['error_retention_days','telemetry_retention_days'] as $key){$n=filter_var($data[$key]??null,FILTER_VALIDATE_INT,['options'=>['min_range'=>7,'max_range'=>365]]);if($n===false)throw new DomainException('Retention must be between 7 and 365 days.');}
        foreach(['error_retention_days','telemetry_retention_days'] as $key)Setting::set($key,(string)$data[$key],'monitoring');
    }
    public static function maintenance() {
        $db=$GLOBALS['conn'];$db->query('SELECT 1');
        foreach(['application_errors'=>['last_seen','error_retention_days'],'page_telemetry'=>['created_at','telemetry_retention_days'],'security_events'=>['created_at','telemetry_retention_days']] as $table=>$spec){$days=max(7,min(365,(int)Setting::get($spec[1],'90')));$db->query("DELETE FROM $table WHERE {$spec[0]}<DATE_SUB(UTC_TIMESTAMP(),INTERVAL $days DAY) LIMIT 10000");}
        Setting::set('maintenance_heartbeat',gmdate('Y-m-d H:i:s'),'monitoring');
    }
}
