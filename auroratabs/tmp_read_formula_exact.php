<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("SELECT expression FROM formulas WHERE id = 3");
$row = $res->fetch_assoc();
echo "EXACT_FORMULA[" . $row['expression'] . "]\n";
