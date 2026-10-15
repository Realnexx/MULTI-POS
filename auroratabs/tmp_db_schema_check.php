<?php
include 'app/auth/config/db_connection.php';
$result = $conn->query('SHOW TABLES');
while($row = $result->fetch_row()) {
    echo "Table: {$row[0]}\n";
    $cols = $conn->query("SHOW COLUMNS FROM {$row[0]}");
    while($col = $cols->fetch_assoc()) {
        echo "  - {$col['Field']} ({$col['Type']})\n";
    }
}
?>
