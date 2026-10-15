<?php
require_once '../auth/config/db_connection.php';

echo "--- TABLE CONTENTS ---\n";
$res = $conn->query("SELECT * FROM competitions");
if ($res) {
    while ($row = $res->fetch_assoc()) {
        print_r($row);
    }
} else {
    echo "Query Error: " . $conn->error . "\n";
}

echo "\n--- JOIN TEST ---\n";
$query = "SELECT c.*, u.username as lead_admin_name, f.name as formula_name 
          FROM competitions c
          LEFT JOIN users u ON c.lead_admin_id = u.id
          LEFT JOIN formulas f ON c.formula_id = f.id
          WHERE c.is_deleted = 0";
$res = $conn->query($query);
if ($res) {
    echo "Found " . $res->num_rows . " results.\n";
    while ($row = $res->fetch_assoc()) {
        echo "Competition: " . $row['title'] . " | Admin: " . $row['lead_admin_name'] . " | Formula: " . $row['formula_name'] . "\n";
    }
} else {
    echo "Join Query Error: " . $conn->error . "\n";
}
?>
