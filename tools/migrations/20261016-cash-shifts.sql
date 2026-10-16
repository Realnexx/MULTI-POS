CREATE TABLE IF NOT EXISTS cash_registers (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, branch_id INT NOT NULL, name VARCHAR(100) NOT NULL,
 status VARCHAR(16) NOT NULL DEFAULT 'active', created_by INT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY cash_register_name(branch_id,name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS cash_shift_policies (
 branch_id INT NOT NULL PRIMARY KEY, require_open TINYINT NOT NULL DEFAULT 0,
 require_approval TINYINT NOT NULL DEFAULT 1, updated_by INT NOT NULL,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS cash_shifts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, branch_id INT NOT NULL, register_id INT NOT NULL,
 cashier_id INT NOT NULL, opened_by INT NOT NULL, opened_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 opening_float DECIMAL(18,2) NOT NULL, status VARCHAR(24) NOT NULL DEFAULT 'open', require_approval TINYINT NOT NULL,
 active_cashier INT NULL, active_register INT NULL, closed_by INT NULL, closed_at DATETIME NULL,
 expected_cash DECIMAL(18,2) NULL, counted_cash DECIMAL(18,2) NULL, difference DECIMAL(18,2) NULL,
 close_notes TEXT NULL, approved_by INT NULL, approved_at DATETIME NULL,
 UNIQUE KEY cash_shift_active_cashier(active_cashier), UNIQUE KEY cash_shift_active_register(active_register),
 INDEX cash_shift_branch(branch_id,status,id), FOREIGN KEY(register_id) REFERENCES cash_registers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS cash_drawer_entries (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, shift_id BIGINT UNSIGNED NOT NULL,
 kind VARCHAR(24) NOT NULL, amount DECIMAL(18,2) NOT NULL, payment_method VARCHAR(32) NOT NULL DEFAULT 'Cash',
 sale_id INT NULL, actor_id INT NOT NULL, reason TEXT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY cash_drawer_sale_kind(sale_id,kind), INDEX cash_drawer_shift(shift_id,id),
 FOREIGN KEY(shift_id) REFERENCES cash_shifts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS cash_shift_events (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, shift_id BIGINT UNSIGNED NULL,
 branch_id INT NOT NULL, action VARCHAR(32) NOT NULL, actor_id INT NOT NULL,
 operation_id BIGINT UNSIGNED NULL, details TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY cash_shift_event_operation(operation_id), FOREIGN KEY(shift_id) REFERENCES cash_shifts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
