<?php
require 'app/auth/config/db_connection.php';
$res = $conn->query("ALTER TABLE judge_locks ADD COLUMN unlock_count INT DEFAULT 0 AFTER is_locked;");
if ($res) echo 'OK'; else echo $conn->error;
?>
