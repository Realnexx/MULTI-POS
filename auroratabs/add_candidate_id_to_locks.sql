-- Add candidate_id to judge_locks for granular unlocking
ALTER TABLE `judge_locks` 
ADD COLUMN `candidate_id` BIGINT UNSIGNED DEFAULT NULL AFTER `category_id`;

-- Add foreign key constraint
ALTER TABLE `judge_locks`
ADD CONSTRAINT `fk_judge_locks_candidate` 
FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE;

-- Optional: Add index for performance
CREATE INDEX `idx_judge_locks_complex` ON `judge_locks` (`judge_id`, `competition_id`, `category_id`, `candidate_id`);
