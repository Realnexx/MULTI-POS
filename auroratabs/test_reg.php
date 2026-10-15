<?php
$url = 'http://localhost/auroratabs/app/auth/endpoints/auth_register.php';
$data = [
    'fname' => 'Test',
    'lname' => 'User',
    'email' => 'test' . rand(1000, 9999) . '@example.com',
    'password' => 'Password123!',
    'role_id' => 5,
    'username' => 'testuser' . rand(1000, 9999),
    'passwordHint' => 'test hint'
];

$options = [
    'http' => [
        'header'  => "Content-type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
        'ignore_errors' => true
    ]
];
$context  = stream_context_create($options);
$result = file_get_contents($url, false, $context);

echo $result;
?>
