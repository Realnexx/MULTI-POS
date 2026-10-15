<?php
require 'app/auth/config/db_connection.php';
$cat_query = "SELECT c.id, c.name, c.level, c.type, 
                     (SELECT jl.is_locked FROM judge_locks jl 
                      WHERE jl.judge_id = 1 AND jl.competition_id = 1 AND jl.category_id = c.id AND jl.candidate_id IS NULL) as is_locked
              FROM categories c 
              WHERE c.competition_id = 1 AND c.is_deleted = 0 
              ORDER BY c.display_order ASC";
$res = $conn->prepare($cat_query);
if (!$res) {
    echo "PREPARE ERROR: " . $conn->error;
} else {
    echo "Query OK";
}

// Also describe judge_locks to verify state
$r2 = $conn->query("DESCRIBE judge_locks");
echo "\n\nColumns in judge_locks:\n";
while ($row = $r2->fetch_assoc()) {
    echo $row['Field'] . " | " . $row['Type'] . "\n";
}
?>
