<?php
$url = 'http://localhost/auroratabs/app/auth/endpoints/auth_register.php';
$data = [
    'fname' => 'Org',
    'lname' => 'Admin',
    'email' => 'org' . rand(1000, 9999) . '@example.com',
    'password' => 'Password123!',
    'role_id' => 2, // Organizer
    'username' => 'orguser' . rand(1000, 9999),
    'orgName' => 'Test Org',
    'eventName' => 'Test Event',
    'eventType' => 'Competition',
    'startDate' => date('Y-m-d'),
    'eventLocation' => 'Test Venue',
    'selectedPlan' => 'starter'
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
