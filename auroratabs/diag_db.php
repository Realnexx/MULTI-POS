<?php
require_once 'app/auth/config/db_connection.php';

echo "Checking judge_assignments table structure:\n";
$res = $conn->query("DESCRIBE judge_assignments");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\nChecking judges table structure:\n";
$res = $conn->query("DESCRIBE judges");
while($row = $res->fetch_assoc()) {
    print_r($row);
}

echo "\nChecking ForeignKey constraints for judge_assignments:\n";
$res = $conn->query("SELECT TABLE_NAME, COLUMN_NAME, CONSTRAINT_NAME, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME 
                    FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
                    WHERE TABLE_NAME = 'judge_assignments' AND TABLE_SCHEMA = 'auroratabs'");
while($row = $res->fetch_assoc()) {
    print_r($row);
}
?>
