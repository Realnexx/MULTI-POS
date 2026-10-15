<?php
require 'app/auth/config/db_connection.php';

// Step 1: Remove duplicate rows, keeping the most recent (highest id) per group
$clean = $conn->query("DELETE jl1 FROM judge_locks jl1
    INNER JOIN judge_locks jl2 
    ON jl1.judge_id = jl2.judge_id
    AND jl1.competition_id = jl2.competition_id
    AND jl1.category_id = jl2.category_id
    AND (jl1.candidate_id = jl2.candidate_id OR (jl1.candidate_id IS NULL AND jl2.candidate_id IS NULL))
    AND jl1.id < jl2.id");
if ($clean) echo "Cleaned duplicates. Affected rows: " . $conn->affected_rows . "\n";
else echo "Clean error: " . $conn->error . "\n";

// Step 2: Add unique constraint
$alter = $conn->query("ALTER TABLE judge_locks ADD UNIQUE KEY uq_judge_lock (judge_id, competition_id, category_id, candidate_id)");
if ($alter) echo "Unique key added successfully.\n";
else echo "Alter error: " . $conn->error . "\n";
?>
