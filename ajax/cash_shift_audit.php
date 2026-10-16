<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../models/CashierSchedule.php';
require_once __DIR__.'/../middleware/access_policy.php';
header('Content-Type: application/json');header('Cache-Control: no-store, private');
authorize_action('cash_shift_audit','get');
try{echo json_encode(CashierSchedule::auditTable($_GET));}
catch(Throwable $e){error_log('Schedule audit table: '.get_class($e));http_response_code($e instanceof DomainException?400:500);echo json_encode(['draw'=>max(0,(int)($_GET['draw']??0)),'recordsTotal'=>0,'recordsFiltered'=>0,'data'=>[],'error'=>$e instanceof DomainException?$e->getMessage():'Audit history could not be loaded. Please retry.']);}
