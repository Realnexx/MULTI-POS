<?php
require_once 'app/auth/config/db_connection.php';
function showCols($tbl) {
    global $conn;
    echo "\n--- $tbl ---";
    $res = $conn->query("SHOW COLUMNS FROM $tbl");
    while($row = $res->fetch_assoc()) {
        echo "\n  " . $row['Field'] . " (" . $row['Type'] . ")";
    }
}
showCols('candidates');
