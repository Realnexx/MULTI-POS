<?php
require_once 'app/auth/config/db_connection.php';
$res = $conn->query("SELECT f.id, f.name, f.expression, c.title as comp_title 
                     FROM formulas f 
                     LEFT JOIN competitions c ON c.formula_id = f.id");
while($row = $res->fetch_assoc()) {
    echo $row['id'] . ": " . $row['name'] . " -> " . $row['expression'] . " (Linked Comp: " . ($row['comp_title'] ?? 'None') . ")\n";
}
echo "\n--- All Active Formulas ---\n";
$res2 = $conn->query("SELECT id, name, expression, is_active FROM formulas WHERE is_active = 1");
while($row = $res2->fetch_assoc()) {
    echo $row['id'] . ": " . $row['name'] . " (" . $row['expression'] . ")\n";
}
