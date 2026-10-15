<?php
require_once __DIR__.'/../models/AuthSecurity.php';
function enforce_session_timeout() {
    static $checked=false;if($checked)return;$checked=true;
    if(empty($_SESSION['user']['id']))return;
    header('Cache-Control: no-store, private');
    require_once __DIR__.'/../models/User.php';
    $id=(int)$_SESSION['user']['id'];$policy=AuthSecurity::config();$now=time();$auth=$_SESSION['auth'] ?? null;
    $expired=!User::find_by_id($id) || !is_array($auth) || $now-(int)($auth['last_activity'] ?? 0) >= (int)$policy['idle_timeout'] || $now-(int)($auth['started_at'] ?? 0) >= (int)$policy['absolute_timeout'] || (int)($auth['version'] ?? 0)!==AuthSecurity::version($id);
    if($expired) {
        Monitoring::security('session_expired',401);
        $_SESSION=[];session_regenerate_id(true);
        $message='Your session has ended. Please sign in again.';
        if(strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '')==='xmlhttprequest' || strpos($_SERVER['SCRIPT_NAME'] ?? '', '/ajax/')!==false || preg_match('~/(data|single|stats)$~',$_GET['route'] ?? '')) {
            http_response_code(401);header('Content-Type: application/json');echo json_encode(['success'=>false,'message'=>$message,'redirect'=>url('?route=login')]);exit;
        }
        set_flash('danger',$message);redirect('?route=login');
    }
    // Background polling must not keep an unattended terminal signed in.
    $background=strpos($_SERVER['SCRIPT_NAME'] ?? '', '/ajax/')!==false || strpos($_GET['route'] ?? '', 'ajax/')===0 || preg_match('~/(data|single|stats)$~',$_GET['route'] ?? '');
    if(!$background || ($_SERVER['REQUEST_METHOD'] ?? 'GET')==='POST') $_SESSION['auth']['last_activity']=$now;
}
