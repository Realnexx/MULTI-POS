<?php
require_once 'app/auth/config/db_connection.php';
$tables = ['votes', 'vote_packages', 'vote_purchases', 'candidates', 'competitions'];
$output = "";
foreach($tables as $table) {
    $output .= "Table: $table\n";
    $cols = $conn->query("DESCRIBE $table");
    if($cols) {
        while($col = $cols->fetch_assoc()) {
            $output .= "  - " . $col['Field'] . " (" . $col['Type'] . ")\n";
        }
    } else {
        $output .= "  - Table not found\n";
    }
    $output .= "\n";
}
file_put_contents('tmp_schema_output.txt', $output);
?>
