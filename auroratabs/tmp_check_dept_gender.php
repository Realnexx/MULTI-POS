<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("SELECT department, gender, COUNT(*) as cnt FROM candidates WHERE is_deleted=0 GROUP BY department, gender ORDER BY department, gender");
while($row = $res->fetch_assoc()) {
    echo "Dept: " . $row['department'] . " | Gender: " . $row['gender'] . " | Count: " . $row['cnt'] . "\n";
}
