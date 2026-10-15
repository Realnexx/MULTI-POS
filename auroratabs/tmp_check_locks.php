<?php
require 'app/auth/config/db_connection.php';

// Show indexes on judge_locks
$r = $conn->query("SHOW INDEX FROM judge_locks");
echo "Indexes:\n";
while ($row = $r->fetch_assoc()) {
    echo "{$row['Key_name']} | {$row['Column_name']} | Unique: " . ($row['Non_unique'] == 0 ? 'YES' : 'NO') . "\n";
}

// Show duplicates
$r2 = $conn->query("SELECT judge_id, competition_id, category_id, candidate_id, COUNT(*) as cnt 
                    FROM judge_locks GROUP BY judge_id, competition_id, category_id, candidate_id HAVING cnt > 1");
echo "\nDuplicates:\n";
if ($r2->num_rows === 0) {
    echo "None found.\n";
} else {
    while ($row = $r2->fetch_assoc()) {
        print_r($row);
    }
}
?>
