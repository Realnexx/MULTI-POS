<?php
// Public defaults; SMTP passwords belong in config.local.php or environment variables.
return [
    'idle_timeout' => 1800,
    'absolute_timeout' => 28800,
    'login_account_limit' => 5,
    'login_ip_limit' => 30,
    'login_window' => 900,
    'reset_lifetime' => 1800,
];
