<?php
require_once 'app/auth/config/db_connection.php';
$tables = ['votes', 'vote_packages', 'vote_purchases', 'candidates'];
foreach($tables as $table) {
    echo "Table: $table\n";
    $cols = $conn->query("DESCRIBE $table");
    if($cols) {
        while($col = $cols->fetch_assoc()) {
            echo "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        echo "  - Table not found\n";
    }
}
?>
