<?php
require_once 'app/auth/config/db_connection.php';

function listCols($table, $conn) {
    echo "\n--- $table ---\n";
    $res = $conn->query("SHOW COLUMNS FROM $table");
    while($row = $res->fetch_assoc()) {
        echo $row['Field'] . " (" . $row['Type'] . ")\n";
    }
}

listCols('criteria', $conn);
listCols('scores', $conn);

$conn->close();
?>
