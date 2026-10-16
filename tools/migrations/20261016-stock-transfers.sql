CREATE TABLE IF NOT EXISTS stock_transfers (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 source_branch_id INT NOT NULL, destination_branch_id INT NOT NULL,
 status VARCHAR(32) NOT NULL, requires_approval TINYINT NOT NULL DEFAULT 1,
 requested_by INT NOT NULL, approved_by INT NULL, dispatched_by INT NULL, received_by INT NULL,
 notes TEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 approved_at DATETIME NULL, dispatched_at DATETIME NULL, received_at DATETIME NULL,
 INDEX transfer_source_status(source_branch_id,status), INDEX transfer_destination_status(destination_branch_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS stock_transfer_items (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, transfer_id BIGINT UNSIGNED NOT NULL,
 source_product_id INT NOT NULL, destination_product_id INT NOT NULL,
 source_name VARCHAR(255) NOT NULL, destination_name VARCHAR(255) NOT NULL, unit VARCHAR(32) NOT NULL,
 requested_quantity DECIMAL(18,4) NOT NULL, dispatched_quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
 received_quantity DECIMAL(18,4) NULL, discrepancy_quantity DECIMAL(18,4) NULL, discrepancy_reason TEXT NULL,
 UNIQUE KEY transfer_source_product(transfer_id,source_product_id),
 CONSTRAINT stock_transfer_items_header FOREIGN KEY(transfer_id) REFERENCES stock_transfers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS stock_transfer_allocations (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, item_id BIGINT UNSIGNED NOT NULL,
 source_batch_id INT NOT NULL, quantity DECIMAL(18,4) NOT NULL,
 cost_price DECIMAL(18,4) NOT NULL, selling_price DECIMAL(18,4) NOT NULL, expiry_date DATE NULL,
 CONSTRAINT stock_transfer_allocations_item FOREIGN KEY(item_id) REFERENCES stock_transfer_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS stock_transfer_events (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, transfer_id BIGINT UNSIGNED NOT NULL,
 action VARCHAR(32) NOT NULL, actor_id INT NOT NULL, operation_id BIGINT UNSIGNED NOT NULL,
 details LONGTEXT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY stock_transfer_event_operation(operation_id),
 CONSTRAINT stock_transfer_events_header FOREIGN KEY(transfer_id) REFERENCES stock_transfers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
