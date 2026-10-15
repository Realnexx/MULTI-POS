<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("DESCRIBE candidates");
while ($r = $res->fetch_assoc()) echo $r['Field'] . " | ";
