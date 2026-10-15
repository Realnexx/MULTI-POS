<?php
class Monitoring {
    private static $started;
    private static $route;
    public static function migrate($db) {
        $db->query("CREATE TABLE IF NOT EXISTS page_telemetry(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,route VARCHAR(120) NOT NULL,user_id INT NULL,branch_id INT NULL,visitor_key CHAR(64) NOT NULL,status_code SMALLINT UNSIGNED NOT NULL,duration_ms INT UNSIGNED NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(created_at,branch_id),INDEX(route,created_at)) ENGINE=InnoDB");
        $db->query("CREATE TABLE IF NOT EXISTS security_events(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,event_type VARCHAR(48) NOT NULL,route VARCHAR(120) NOT NULL,user_id INT NULL,branch_id INT NULL,status_code SMALLINT UNSIGNED NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(created_at,branch_id),INDEX(event_type,created_at)) ENGINE=InnoDB");
    }
    public static function route() {
        $r=$_GET['route'] ?? basename($_SERVER['SCRIPT_NAME'] ?? 'index.php');
        return is_string($r)&&preg_match('~^[a-zA-Z0-9_/-]{1,120}$~D',$r)?$r:'invalid-route';
    }
    private static function branch($user) {
        global $conn;if(!$user)return null;
        $stmt=$conn->prepare('SELECT branch_id FROM users WHERE id=?');$stmt->bind_param('i',$user);$stmt->execute();$row=$stmt->get_result()->fetch_assoc();return $row['branch_id'] ?? null;
    }
    public static function security($type,$status=200) {
        global $conn;
        try {$route=self::route();$user=$_SESSION['user']['id'] ?? null;$branch=self::branch($user);$type=substr($type,0,48);$stmt=$conn->prepare('INSERT INTO security_events(event_type,route,user_id,branch_id,status_code) VALUES (?,?,?,?,?)');$stmt->bind_param('ssiii',$type,$route,$user,$branch,$status);$stmt->execute();}
        catch(Throwable $e){error_log('Security monitoring write unavailable.');}
    }
    public static function start() {
        if(PHP_SAPI==='cli'||self::$started!==null)return;
        self::$started=microtime(true);self::$route=self::route();
        if(empty($_SESSION['telemetry_visitor']))$_SESSION['telemetry_visitor']=bin2hex(random_bytes(32));
        register_shutdown_function([__CLASS__,'finish']);
    }
    public static function finish() {
        global $conn;
        $status=http_response_code() ?: 200;$fatal=error_get_last();
        if($fatal&&in_array($fatal['type'],[E_ERROR,E_PARSE,E_CORE_ERROR,E_COMPILE_ERROR],true))$status=500;
        $events=[401=>'authentication_required',403=>'access_denied',419=>'csrf_rejected',429=>'rate_limited'];
        if(isset($events[$status]))self::security($events[$status],$status);elseif($status>=500)self::security('server_error',$status);
        // Count rendered pages, not polling, redirects, writes, or JSON endpoints.
        $type=implode(';',headers_list());$route=self::$route;
        if(($_SERVER['REQUEST_METHOD'] ?? 'GET')!=='GET'||($status>=300&&$status<400)||stripos($type,'application/json')!==false||strpos($route,'ajax/')===0||strpos($_SERVER['SCRIPT_NAME'] ?? '', '/ajax/')!==false||preg_match('~/(data|stats|single)$~',$route))return;
        try {$user=$_SESSION['user']['id'] ?? null;$branch=self::branch($user);$visitor=hash('sha256',$_SESSION['telemetry_visitor'] ?? bin2hex(random_bytes(32)));$ms=(int)min(4294967295,max(0,round((microtime(true)-self::$started)*1000)));$stmt=$conn->prepare('INSERT INTO page_telemetry(route,user_id,branch_id,visitor_key,status_code,duration_ms) VALUES (?,?,?,?,?,?)');$stmt->bind_param('siisii',$route,$user,$branch,$visitor,$status,$ms);$stmt->execute();}
        catch(Throwable $e){error_log('Page monitoring write unavailable.');}
    }
    public static function report($user,$from,$to) {
        require_once __DIR__.'/User.php';
        if(!User::has_permission($user,'view_monitoring'))throw new DomainException('Monitoring access denied.');
        global $conn;$where='created_at>=? AND created_at<?';$args=[$from.' 00:00:00',date('Y-m-d',strtotime($to.' +1 day')).' 00:00:00'];$types='ss';
        if(!User::has_all_branch_access($user)){$where.=' AND branch_id=?';$args[]=User::access_context($user)['branch_id'] ?? 0;$types.='i';}
        $read=static function($sql)use($conn,$where,$args,$types){$stmt=$conn->prepare(str_replace('{where}',$where,$sql));$stmt->bind_param($types,...$args);$stmt->execute();return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);};
        return ['summary'=>$read('SELECT COUNT(*) views,COUNT(DISTINCT visitor_key) visitors,ROUND(AVG(duration_ms)) average_ms,SUM(status_code>=500) errors FROM page_telemetry WHERE {where}')[0],
        'pages'=>$read('SELECT route,COUNT(*) views,ROUND(AVG(duration_ms)) average_ms,SUM(status_code>=400) errors FROM page_telemetry WHERE {where} GROUP BY route ORDER BY views DESC LIMIT 100'),
        'daily'=>$read('SELECT DATE(created_at) day,COUNT(*) views FROM page_telemetry WHERE {where} GROUP BY DATE(created_at) ORDER BY day'),
        'counts'=>$read('SELECT event_type,COUNT(*) total FROM security_events WHERE {where} GROUP BY event_type ORDER BY total DESC'),
        'events'=>$read('SELECT event_type,route,user_id,branch_id,status_code,created_at FROM security_events WHERE {where} ORDER BY id DESC LIMIT 200')];
    }
}
