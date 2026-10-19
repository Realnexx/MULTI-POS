<?php
require_once $workspace.'/models/DemoRequest.php';DemoRequest::migrate($conn);
$input=['full_name'=>'Demo requester','email'=>'requester@example.com','phone'=>'+639123456789','business_name'=>'Example business','business_type'=>'Retail','branch_count'=>'3','message'=>"<script>alert('test')</script>",'consent'=>'1'];
$key=bin2hex(random_bytes(32));$id=DemoRequest::submit($input,$key);check($id>0,'Valid demo request saved to database');
check(DemoRequest::submit($input,$key)===$id&&count(DemoRequest::recent())===1,'Repeated submission does not duplicate a request');
foreach(['email'=>'invalid','phone'=>'abc','branch_count'=>'0','full_name'=>'','consent'=>'0','message'=>str_repeat('a',3001)] as $field=>$value){$bad=$input;$bad[$field]=$value;reject(function()use($bad){DemoRequest::submit($bad,bin2hex(random_bytes(32)));},'Invalid '.$field.' rejected');}
reject(function()use($input){DemoRequest::submit($input,'bad');},'Invalid submission key rejected');
DemoRequest::updateStatus($id,'contacted');check(DemoRequest::recent()[0]['status']==='contacted','Administrator status change persists');reject(function()use($id){DemoRequest::updateStatus($id,'unexpected');},'Invalid status rejected');
$stored=DemoRequest::recent()[0];check($stored['message']===$input['message'],'Requirements saved without executing HTML');
$_SESSION['user']['name']='Test reviewer';$requests=DemoRequest::recent();$title='';ob_start();require $workspace.'/views/modules/demo_requests.php';$html=ob_get_clean();file_put_contents($root.'/scratch/demo-requests-admin.html',$html);check(strpos($html,"<script>alert('test')</script>")===false&&strpos($html,'demo-requests-table')!==false,'Admin table renders without embedding submitted HTML');

$page=DemoRequest::dataTable(['draw'=>7,'start'=>0,'length'=>10]);check($page['draw']===7&&$page['recordsTotal']===1&&count($page['data'])===1,'Server-side DataTable returns protocol counts and rows');
check(DemoRequest::dataTable(['status'=>'new'])['recordsFiltered']===0,'Server-side status filter applied');check(DemoRequest::dataTable(['status'=>'contacted'])['recordsFiltered']===1,'Matching status returns request');
check(DemoRequest::dataTable(['search'=>['value'=>'Example business']])['recordsFiltered']===1,'Server-side search matches business');check(DemoRequest::dataTable(['search'=>['value'=>"' OR 1=1 --"]])['recordsFiltered']===0,'Search input is bound safely');
check(count(DemoRequest::dataTable(['start'=>1,'length'=>1])['data'])===0,'Pagination offset applied in database');
check(DemoRequest::dataTable(['order'=>[['column'=>999,'dir'=>'DROP TABLE']]])['recordsTotal']===1,'Invalid sorting cannot inject SQL');
