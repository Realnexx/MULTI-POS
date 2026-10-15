CREATE TABLE IF NOT EXISTS `recalculation_history` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `triggered_by` BIGINT UNSIGNED DEFAULT NULL,
  `status` ENUM('success', 'failed') DEFAULT 'success',
  `details` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `final_scores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `candidate_id` BIGINT UNSIGNED NOT NULL,
  `score` DECIMAL(10,4) NOT NULL DEFAULT 0.0000,
  `rank` INT DEFAULT 0,
  `remarks` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `comp_cand_unique` (`competition_id`, `candidate_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
