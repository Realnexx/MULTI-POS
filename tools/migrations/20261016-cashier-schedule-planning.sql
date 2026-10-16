CREATE TABLE IF NOT EXISTS cashier_schedule_series (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 branch_id INT NOT NULL, cashier_id INT NOT NULL,
 weekdays VARCHAR(20) NOT NULL, date_from DATE NOT NULL, date_to DATE NOT NULL,
 start_time TIME NOT NULL, end_time TIME NOT NULL, overnight TINYINT(1) NOT NULL DEFAULT 0,
 status ENUM('active','cancelled') NOT NULL DEFAULT 'active', notes VARCHAR(500) NOT NULL DEFAULT '',
 created_by INT NOT NULL, updated_by INT NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(branch_id,cashier_id,date_from,date_to)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS cashier_schedule_blocks (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 branch_id INT NOT NULL, cashier_id INT NOT NULL, schedule_id BIGINT UNSIGNED NULL,
 starts_at DATETIME NOT NULL, ends_at DATETIME NOT NULL,
 category ENUM('absence','leave','rest_day','suspension','other') NOT NULL,
 reason VARCHAR(500) NOT NULL, notes VARCHAR(500) NOT NULL DEFAULT '', active TINYINT(1) NOT NULL DEFAULT 1,
 created_by INT NOT NULL, updated_by INT NOT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX(cashier_id,branch_id,active,starts_at,ends_at), INDEX(schedule_id)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS cashier_schedule_events (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 schedule_id BIGINT UNSIGNED NULL, series_id BIGINT UNSIGNED NULL, block_id BIGINT UNSIGNED NULL,
 branch_id INT NOT NULL, cashier_id INT NOT NULL, actor_id INT NOT NULL,
 action VARCHAR(60) NOT NULL, reason VARCHAR(500) NOT NULL,
 before_data LONGTEXT NULL, after_data LONGTEXT NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 INDEX(branch_id,cashier_id,created_at), INDEX(schedule_id), INDEX(series_id), INDEX(block_id)
) ENGINE=InnoDB;
ALTER TABLE cashier_schedules
 ADD COLUMN IF NOT EXISTS series_id BIGINT UNSIGNED NULL,
 ADD COLUMN IF NOT EXISTS occurrence_date DATE NULL,
 ADD COLUMN IF NOT EXISTS is_exception TINYINT(1) NOT NULL DEFAULT 0,
 ADD COLUMN IF NOT EXISTS attendance_status ENUM('scheduled','present','absent','on_leave','completed') NOT NULL DEFAULT 'scheduled';
