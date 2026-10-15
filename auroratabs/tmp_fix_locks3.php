<?php
require 'app/auth/config/db_connection.php';

// Purge true duplicates for candidate_id IS NULL
$query = "
DELETE t1 FROM judge_locks t1
INNER JOIN judge_locks t2 
WHERE t1.id < t2.id 
AND t1.judge_id = t2.judge_id 
AND t1.competition_id = t2.competition_id 
AND t1.category_id = t2.category_id 
AND t1.candidate_id IS NULL 
AND t2.candidate_id IS NULL;
";

$conn->query($query);
echo "Purged candidate IS NULL duplicates: " . $conn->affected_rows . "\n";

// Purge true duplicates for candidate_id IS NOT NULL
$query2 = "
DELETE t1 FROM judge_locks t1
INNER JOIN judge_locks t2 
WHERE t1.id < t2.id 
AND t1.judge_id = t2.judge_id 
AND t1.competition_id = t2.competition_id 
AND t1.category_id = t2.category_id 
AND t1.candidate_id = t2.candidate_id;
";

$conn->query($query2);
echo "Purged candidate IS NOT NULL duplicates: " . $conn->affected_rows . "\n";
?>
