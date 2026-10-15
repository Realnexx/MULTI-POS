<?php
require 'app/auth/config/db_connection.php';

// Show current data
$r = $conn->query("SELECT * FROM judge_locks WHERE candidate_id IS NULL ORDER BY id");
echo "All category-level locks:\n";
while ($row = $r->fetch_assoc()) { print_r($row); }

// Fix: deduplicate - keep only the latest per group with candidate_id IS NULL
$fix = $conn->query("DELETE FROM judge_locks WHERE candidate_id IS NULL AND id NOT IN (
    SELECT max_id FROM (
        SELECT MAX(id) as max_id FROM judge_locks
        WHERE candidate_id IS NULL
        GROUP BY judge_id, competition_id, category_id
    ) t
)");
if ($fix) echo "\nDuplicate fix result: affected=" . $conn->affected_rows . "\n";
else echo "\nFix error: " . $conn->error . "\n";

// Check remaining
$r2 = $conn->query("SELECT * FROM judge_locks WHERE candidate_id IS NULL ORDER BY id");
echo "\nRemaining:\n";
while ($row = $r2->fetch_assoc()) { print_r($row); }
?>
