<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/../models/PayMongo.php';
header('Content-Type: application/json');header('Cache-Control: no-store');
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo '{"success":false}';exit;}
$body=file_get_contents('php://input',false,null,0,1048577);
if(strlen($body)>1048576){http_response_code(413);echo '{"success":false}';exit;}
try {
    if(!PayMongo::ready())throw new RuntimeException('Not installed');
    PayMongo::webhook($body,$_SERVER['HTTP_PAYMONGO_SIGNATURE']??'');
    echo '{"success":true}';
} catch(PayMongoApiException $error){http_response_code(503);echo '{"success":false}';}
catch(DomainException $error){http_response_code(400);echo '{"success":false}';}
catch(Throwable $error){error_log('PayMongo notification could not be processed.');http_response_code(503);echo '{"success":false}';}
