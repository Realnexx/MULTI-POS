<?php
require 'app/auth/config/db_connection.php';

$r2 = $conn->query("SELECT * FROM judge_locks WHERE judge_id = 17 AND competition_id = 3 AND category_id = 4 AND candidate_id IS NULL ORDER BY id");
echo "Rows for Judge 17, Comp 3, Cat 4:\n";
while ($row = $r2->fetch_assoc()) { print_r($row); }
?>
