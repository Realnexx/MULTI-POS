CREATE TABLE IF NOT EXISTS inventory_reorder_levels (
 product_id INT NOT NULL, branch_id INT NOT NULL,
 reorder_level DECIMAL(18,4) NOT NULL,
 updated_by INT NOT NULL, updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY(product_id,branch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE IF NOT EXISTS inventory_reviews (
 id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 product_id INT NOT NULL, branch_id INT NOT NULL, product_name VARCHAR(255) NOT NULL, unit VARCHAR(32) NOT NULL,
 kind VARCHAR(20) NOT NULL, expected_quantity DECIMAL(18,4) NOT NULL,
 actual_quantity DECIMAL(18,4) NOT NULL, difference DECIMAL(18,4) NOT NULL,
 stock_fingerprint CHAR(64) NOT NULL, notes TEXT NOT NULL,
 status VARCHAR(20) NOT NULL, created_by INT NOT NULL, resolved_by INT NULL,
 resolution_notes TEXT NULL, operation_id BIGINT UNSIGNED NULL,
 request_key CHAR(32) NOT NULL, request_fingerprint CHAR(64) NOT NULL,
 created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, resolved_at DATETIME NULL,
 UNIQUE KEY inventory_review_request(request_key), KEY inventory_review_branch(branch_id,status,id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
