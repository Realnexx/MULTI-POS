<?php
require_once 'app/auth/config/db_connection.php';
$tables = $conn->query("SHOW TABLES");
while($row = $tables->fetch_row()) {
    echo "Table: " . $row[0] . "\n";
    $cols = $conn->query("DESCRIBE " . $row[0]);
    while($col = $cols->fetch_assoc()) {
        echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
    }
}
?>
