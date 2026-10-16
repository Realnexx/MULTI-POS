CREATE TABLE IF NOT EXISTS purchase_suppliers (
 id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, branch_id INT NOT NULL,
 name VARCHAR(160) NOT NULL, contact_name VARCHAR(160) NOT NULL DEFAULT '',
 email VARCHAR(190) NOT NULL DEFAULT '', phone VARCHAR(80) NOT NULL DEFAULT '',
 address TEXT NOT NULL, payment_terms VARCHAR(255) NOT NULL DEFAULT '', status VARCHAR(16) NOT NULL DEFAULT 'active',
 created_by INT NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX supplier_branch(branch_id,status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_orders (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, branch_id INT NOT NULL, supplier_id INT NOT NULL,
 supplier_snapshot TEXT NOT NULL, status VARCHAR(24) NOT NULL DEFAULT 'draft', expected_date DATE NULL,
 notes TEXT NOT NULL, created_by INT NOT NULL, approved_by INT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, approved_at DATETIME NULL,
 INDEX purchase_branch_status(branch_id,status),
 FOREIGN KEY(supplier_id) REFERENCES purchase_suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_order_items (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 product_id INT NOT NULL, product_name VARCHAR(255) NOT NULL, unit VARCHAR(32) NOT NULL,
 quantity DECIMAL(18,4) NOT NULL, unit_cost DECIMAL(18,2) NOT NULL, amount DECIMAL(18,2) NOT NULL,
 UNIQUE KEY purchase_order_product(order_id,product_id), FOREIGN KEY(order_id) REFERENCES purchase_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_receipts (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NOT NULL,
 delivery_reference VARCHAR(100) NOT NULL, received_date DATE NOT NULL, received_by INT NOT NULL,
 notes TEXT NOT NULL, operation_id BIGINT UNSIGNED NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY purchase_delivery(order_id,delivery_reference), UNIQUE KEY purchase_receipt_operation(operation_id),
 FOREIGN KEY(order_id) REFERENCES purchase_orders(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_receipt_items (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, receipt_id BIGINT UNSIGNED NOT NULL,
 order_item_id BIGINT UNSIGNED NOT NULL, quantity DECIMAL(18,4) NOT NULL, rejected_quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
 rejection_reason VARCHAR(1000) NOT NULL DEFAULT '', unit_cost DECIMAL(18,2) NOT NULL, amount DECIMAL(18,2) NOT NULL,
 batch_id INT NULL, batch_number VARCHAR(100) NOT NULL DEFAULT '', expiry_date DATE NULL,
 UNIQUE KEY purchase_receipt_line(receipt_id,order_item_id),
 FOREIGN KEY(receipt_id) REFERENCES purchase_receipts(id), FOREIGN KEY(order_item_id) REFERENCES purchase_order_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_supplier_returns (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, receipt_id BIGINT UNSIGNED NOT NULL,
 reference VARCHAR(100) NOT NULL, reason TEXT NOT NULL, returned_date DATE NOT NULL, returned_by INT NOT NULL,
 settlement_status VARCHAR(20) NOT NULL DEFAULT 'pending', settlement_notes TEXT NULL,
 operation_id BIGINT UNSIGNED NOT NULL, created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY supplier_return_reference(receipt_id,reference), UNIQUE KEY supplier_return_operation(operation_id),
 FOREIGN KEY(receipt_id) REFERENCES purchase_receipts(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_supplier_return_items (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, return_id BIGINT UNSIGNED NOT NULL,
 receipt_item_id BIGINT UNSIGNED NOT NULL, quantity DECIMAL(18,4) NOT NULL, amount DECIMAL(18,2) NOT NULL,
 UNIQUE KEY supplier_return_line(return_id,receipt_item_id),
 FOREIGN KEY(return_id) REFERENCES purchase_supplier_returns(id), FOREIGN KEY(receipt_item_id) REFERENCES purchase_receipt_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS purchase_events (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY, order_id BIGINT UNSIGNED NULL, supplier_id INT NOT NULL,
 action VARCHAR(40) NOT NULL, actor_id INT NOT NULL, operation_id BIGINT UNSIGNED NOT NULL, details TEXT NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY purchase_event_operation(operation_id),
 FOREIGN KEY(order_id) REFERENCES purchase_orders(id), FOREIGN KEY(supplier_id) REFERENCES purchase_suppliers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
