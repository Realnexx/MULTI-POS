<?php
// Isolated response test: no database, real uploads or user records.
define('APP_BASE_PATH','/pos/');
$_SESSION=['user'=>['id'=>1],'csrf_token'=>'fixture-token'];
$_GET=['route'=>'admin/users'];
$_POST=['action'=>'edit','csrf_token'=>'fixture-token','user_id'=>2];
$_SERVER['HTTP_X_MODAL_FORM']='1';
require __DIR__.'/../../config/uploads.php';
validate_uploaded_images(['image'=>['name'=>'test.png','error'=>UPLOAD_ERR_INI_SIZE,'size'=>11*1024*1024,'tmp_name'=>'']]);
