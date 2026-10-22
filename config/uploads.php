<?php
// All existing upload handlers accept images. Validate before they use client filenames.
function validate_uploaded_images(array $files)
{
    $userPhoto=($_GET['route']??'')==='admin/users' && in_array($_POST['action']??'',['create','edit'],true);
    $limitMb=$userPhoto?10:5;
    $fail=static function($message)use($userPhoto){
        if($userPhoto && !empty($_SESSION['user']['id']) && is_string($_POST['csrf_token']??null) && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'],$_POST['csrf_token'])){
            if(($_SERVER['HTTP_X_MODAL_FORM']??'')==='1'){
                http_response_code(422);header('Content-Type: application/json');
                echo json_encode(['success'=>false,'message'=>$message,'field_errors'=>['image'=>$message]]);exit;
            }
            $_SESSION['user_field_error']=['action'=>$_POST['action'],'field'=>'image','message'=>$message,'values'=>array_intersect_key($_POST,array_flip(['user_id','firstname','middlename','lastname','extname','email','roles']))];
            header('Location: '.APP_BASE_PATH.'?route=admin/users');exit;
        }
        http_response_code(400);exit($message);
    };
    foreach ($files as $file) {
        if (is_array($file['error'] ?? null)) {
            $fail('Nested uploads are not supported.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $types = ['jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG,
            'gif' => IMAGETYPE_GIF, 'webp' => IMAGETYPE_WEBP];
        $image = ($file['error'] ?? -1) === UPLOAD_ERR_OK
            ? @getimagesize($file['tmp_name'] ?? '') : false;
        if (!$image || !isset($types[$extension]) || $image[2] !== $types[$extension]
            || !is_uploaded_file($file['tmp_name']) || ($file['size'] ?? PHP_INT_MAX) > $limitMb * 1024 * 1024
            || !preg_match('/^[a-zA-Z0-9_() .-]+\.(jpg|jpeg|png|gif|webp)$/iD', basename($file['name'] ?? ''))) {
            $fail('Upload a JPG, PNG, GIF or WebP image up to '.$limitMb.' MB. Use letters, numbers, spaces, parentheses, dots, hyphens or underscores in the filename.');
        }
    }
}
