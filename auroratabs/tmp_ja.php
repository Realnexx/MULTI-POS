<?php
require_once 'app/auth/config/db_connection.php';
$id = 3;
$res = $conn->query("SELECT * FROM judge_assignments WHERE competition_id = $id");
while($row = $res->fetch_assoc()) {
    echo "Assignment ID: " . $row['id'] . " | Judge ID: " . $row['judge_id'] . " | Competition ID: " . $row['competition_id'] . "\n";
}
