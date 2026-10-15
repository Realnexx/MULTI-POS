<?php
require_once 'app/auth/config/db_connection.php';
$id = 3;
$res = $conn->query("SELECT title, formula_id FROM competitions WHERE id = $id");
$row = $res->fetch_assoc();
echo "Competition: " . $row['title'] . " (ID: $id) -> Formula ID: " . $row['formula_id'] . "\n";

$fid = $row['formula_id'];
$res2 = $conn->query("SELECT name, expression FROM formulas WHERE id = $fid");
$row2 = $res2->fetch_assoc();
echo "Linked Formula: " . $row2['name'] . " -> " . $row2['expression'] . "\n";
