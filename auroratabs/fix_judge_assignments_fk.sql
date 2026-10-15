-- aurora.judge_assignments FK Fix
-- Repoint judge_id to users(id) instead of judges(id)

-- 1. Drop the incorrect foreign key
ALTER TABLE `judge_assignments` DROP FOREIGN KEY `judge_assignments_ibfk_1`;

-- 2. Add the correct foreign key pointing to users table
ALTER TABLE `judge_assignments` 
ADD CONSTRAINT `fk_judge_assignments_user` 
FOREIGN KEY (`judge_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE;

-- 3. Update scores table as well (it also references candidates and criteria, check if it references anything else)
-- Current scores table:
-- FOREIGN KEY (`assignment_id`) REFERENCES `judge_assignments`(`id`) ON DELETE CASCADE,
-- FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE,
-- FOREIGN KEY (`criteria_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE
-- No change needed for scores as it points to assignment_id.

-- 4. Check judge_locks table
-- Current judge_locks table:
-- FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE
ALTER TABLE `judge_locks` DROP FOREIGN KEY `judge_locks_ibfk_1`;
ALTER TABLE `judge_locks` 
ADD CONSTRAINT `fk_judge_locks_user` 
FOREIGN KEY (`judge_id`) REFERENCES `users`(`id`) 
ON DELETE CASCADE;

-- Optional: Drop judges table if it's no longer needed
-- DROP TABLE `judges`; 
