-- AuroraTabs Database Schema (Final Refined & Fixed)
-- Comprehensive schema covering all admin modules, soft deletes, and settings
-- Fixed: Reordered tables to resolve Foreign Key constraints (errno 150)

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- 1. Core Authentication & RBAC
-- --------------------------------------------------------

-- Roles Table (Must come before users)
CREATE TABLE `roles` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL UNIQUE, -- 'admin', 'organizer', 'tabulator', 'judge'
  `description` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Users Table
CREATE TABLE `users` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `mobile` VARCHAR(20) DEFAULT NULL,
  `avatar_url` VARCHAR(255) DEFAULT NULL,
  `role_id` BIGINT UNSIGNED NOT NULL,
  `status` ENUM('active', 'pending', 'suspended', 'verified') DEFAULT 'pending',
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Permissions Modules (for granular access control)
CREATE TABLE `modules` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL, -- 'Competition Manager', 'Dashboard'
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Role Permissions
CREATE TABLE `role_permissions` (
  `role_id` BIGINT UNSIGNED NOT NULL,
  `module_id` BIGINT UNSIGNED NOT NULL,
  `can_view` BOOLEAN DEFAULT FALSE,
  `can_manage` BOOLEAN DEFAULT FALSE, -- write/edit
  `can_delete` BOOLEAN DEFAULT FALSE,
  `can_export` BOOLEAN DEFAULT FALSE,
  PRIMARY KEY (`role_id`, `module_id`),
  FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`module_id`) REFERENCES `modules`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 2. System Settings & Logs
-- --------------------------------------------------------

-- General System Settings (Key-Value Store)
-- Covers: General Settings, Voting Settings, Payment Keys
CREATE TABLE `settings` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE, -- e.g., 'event_name', 'currency', 'stripe_key'
  `setting_value` TEXT,
  `group` VARCHAR(50) DEFAULT 'general', -- 'general', 'voting', 'payment', 'system'
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Activity Logs
-- Based on activity-logs.php
CREATE TABLE `activity_logs` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL, -- Null if system action
  `action` VARCHAR(100) NOT NULL, -- 'Created', 'Updated', 'Deleted', 'Login'
  `module` VARCHAR(100) NOT NULL, -- 'Competitions', 'Judges'
  `details` TEXT,
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notifications
-- Based on notifications.php
CREATE TABLE `notifications` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` BIGINT UNSIGNED DEFAULT NULL, -- Specific user
  `role_id` BIGINT UNSIGNED DEFAULT NULL, -- Or entire role group (e.g. all admins)
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `type` ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
  `link_url` VARCHAR(255) DEFAULT NULL, -- Action link
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 3. Organizations & Competitions
-- --------------------------------------------------------

CREATE TABLE `organizations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `owner_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(50) DEFAULT NULL,
  `logo_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`owner_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `competitions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `slug` VARCHAR(255) UNIQUE DEFAULT NULL,
  `type` VARCHAR(100) DEFAULT NULL, -- 'Beauty Pageant', 'Science Fair'
  `plan_type` ENUM('starter', 'pro', 'enterprise') DEFAULT 'starter',
  `description` TEXT,
  `venue` VARCHAR(255) DEFAULT NULL,
  `banner_url` VARCHAR(255) DEFAULT NULL,
  `status` ENUM('draft', 'upcoming', 'active', 'completed', 'cancelled') DEFAULT 'draft',
  `start_date` DATE DEFAULT NULL,
  `start_time` TIME DEFAULT NULL,
  `end_date` DATE DEFAULT NULL,
  `end_time` TIME DEFAULT NULL,
  `enable_audience_voting` BOOLEAN DEFAULT FALSE,
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `categories` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL, -- 'Finals', 'Preliminary'
  `type` ENUM('major', 'minor', 'special') DEFAULT 'major',
  `level` INT DEFAULT 1,
  `display_order` INT DEFAULT 0,
  `weight_percent` DECIMAL(5,2) DEFAULT 0.00,
  `description` TEXT,
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `criteria` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `category_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `weight_percent` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `display_order` INT DEFAULT 0,
  `is_required` BOOLEAN DEFAULT TRUE,
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 4. Participants (Candidates & Judges)
-- --------------------------------------------------------

CREATE TABLE `candidates` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `candidate_number` INT NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `middle_name` VARCHAR(100) DEFAULT NULL,
  `gender` ENUM('male', 'female', 'other') DEFAULT NULL,
  `age` INT DEFAULT NULL,
  `height` VARCHAR(50) DEFAULT NULL,
  `hometown` VARCHAR(255) DEFAULT NULL,
  `department` VARCHAR(255) DEFAULT NULL,
  `biography` TEXT,
  `photo_url` VARCHAR(255) DEFAULT NULL,
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `judges` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `organization_id` BIGINT UNSIGNED NOT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `title` VARCHAR(100) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `photo_url` VARCHAR(255) DEFAULT NULL,
  `access_code` VARCHAR(50) NOT NULL UNIQUE,
  `passcode` VARCHAR(255) NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  `deleted_by` BIGINT UNSIGNED DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`organization_id`) REFERENCES `organizations`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `judge_assignments` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judge_id` BIGINT UNSIGNED NOT NULL,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `role` VARCHAR(50) DEFAULT 'member', -- 'chairman', 'member'
  `assigned_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Judge Score Locking
-- Based on judge-locks.php
CREATE TABLE `judge_locks` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `judge_id` BIGINT UNSIGNED NOT NULL,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `category_id` BIGINT UNSIGNED DEFAULT NULL, -- Null means locked for whole competition
  `is_locked` BOOLEAN DEFAULT TRUE,
  `locked_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`judge_id`) REFERENCES `judges`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- 5. Scoring & Formulas
-- --------------------------------------------------------

CREATE TABLE `scores` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `assignment_id` BIGINT UNSIGNED NOT NULL, -- Judge Assignment
  `candidate_id` BIGINT UNSIGNED NOT NULL,
  `criteria_id` BIGINT UNSIGNED NOT NULL,
  `score_value` DECIMAL(5,2) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`assignment_id`) REFERENCES `judge_assignments`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`criteria_id`) REFERENCES `criteria`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `formulas` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `competition_id` BIGINT UNSIGNED NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `expression` TEXT NOT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `is_public` BOOLEAN DEFAULT FALSE,
  `is_deleted` BOOLEAN DEFAULT FALSE,
  `deleted_at` TIMESTAMP NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`competition_id`) REFERENCES `competitions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 6. Voting & Finance
-- --------------------------------------------------------

CREATE TABLE `vote_packages` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `votes_count` INT NOT NULL,
  `price` DECIMAL(10,2) NOT NULL,
  `promo_label` VARCHAR(100) DEFAULT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `transactions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `transaction_code` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `description` TEXT,
  `amount` DECIMAL(10,2) NOT NULL,
  `status` ENUM('pending', 'success', 'failed', 'refunded') DEFAULT 'pending',
  `gateway` VARCHAR(50) DEFAULT NULL, -- 'Stripe', 'PayPal', 'GCash'
  `reference_number` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `votes` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `candidate_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED DEFAULT NULL,
  `package_id` BIGINT UNSIGNED DEFAULT NULL,
  `transaction_id` BIGINT UNSIGNED DEFAULT NULL,
  `ip_address` VARCHAR(45) DEFAULT NULL, -- For rate limiting
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`candidate_id`) REFERENCES `candidates`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 8. Pricing & Subscriptions (New)
-- --------------------------------------------------------

CREATE TABLE `pricing_plans` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL, -- 'Starter', 'Pro', 'Enterprise'
  `base_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `candidate_limit` INT DEFAULT 0, -- 0 for unlimited? Or use -1? Let's use NULL for unlimited
  `judge_limit` INT DEFAULT 0,
  `description` TEXT,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `plan_features` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `feature_key` VARCHAR(100) NOT NULL UNIQUE, -- 'live_tv_export', 'branding_removal'
  `name` VARCHAR(255) NOT NULL,
  `is_visible` BOOLEAN DEFAULT TRUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `plan_feature_assignments` (
  `plan_id` BIGINT UNSIGNED NOT NULL,
  `feature_id` BIGINT UNSIGNED NOT NULL,
  PRIMARY KEY (`plan_id`, `feature_id`),
  FOREIGN KEY (`plan_id`) REFERENCES `pricing_plans`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`feature_id`) REFERENCES `plan_features`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `coupons` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(50) NOT NULL UNIQUE,
  `description` TEXT,
  `discount_type` ENUM('percent', 'fixed') DEFAULT 'percent',
  `discount_value` DECIMAL(10,2) NOT NULL,
  `max_uses` INT DEFAULT NULL,
  `used_count` INT DEFAULT 0,
  `expiry_date` TIMESTAMP NULL DEFAULT NULL,
  `is_active` BOOLEAN DEFAULT TRUE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- --------------------------------------------------------
-- 7. Seed Data
-- --------------------------------------------------------

-- Default Roles
INSERT INTO `roles` (`name`, `description`) VALUES
('admin', 'Super Administrator'),
('organizer', 'Event Organizer'),
('tabulator', 'Score Manager'),
('judge', 'Score Submitter'),
('audience', 'General User/Voter');

-- Default Settings
INSERT INTO `settings` (`setting_key`, `setting_value`, `group`) VALUES
('event_name', 'Aurora Grand Pageant', 'general'),
('currency', 'PHP', 'finance'),
('timezone', 'Asia/Manila', 'system'),
('maintenance_mode', '0', 'system'),
('vote_price', '5.00', 'voting'),
('vote_min_purchase', '10', 'voting'),
('active_gateway', 'stripe', 'payment');

COMMIT;
