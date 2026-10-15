-- MANUAL migration. Back up and reconcile existing recipe stock first.
-- Run once against a restored test database before the intended database.
-- Existing recipes deliberately remain 'legacy' and cannot be sold until reviewed.
ALTER TABLE products
  ADD COLUMN recipe_mode VARCHAR(24) NOT NULL DEFAULT 'legacy',
  MODIFY stock DECIMAL(18,4) NOT NULL DEFAULT 0,
  MODIFY low_stock_threshold DECIMAL(18,4) NOT NULL DEFAULT 10,
  MODIFY max_stock_threshold DECIMAL(18,4) NOT NULL DEFAULT 100;
ALTER TABLE product_batches MODIFY quantity DECIMAL(18,4) NOT NULL DEFAULT 0;
ALTER TABLE inventory_logs MODIFY quantity DECIMAL(18,4) NOT NULL DEFAULT 0;
ALTER TABLE stock_notifications
  MODIFY current_stock DECIMAL(18,4) NOT NULL DEFAULT 0,
  MODIFY threshold DECIMAL(18,4) NOT NULL DEFAULT 0;
ALTER TABLE product_recipes MODIFY quantity DECIMAL(18,4) NOT NULL;
ALTER TABLE product_modifiers
  ADD COLUMN ingredient_quantity DECIMAL(18,4) NOT NULL DEFAULT 1,
  ADD COLUMN ingredient_unit VARCHAR(50) NOT NULL DEFAULT '';
ALTER TABLE sale_item_modifiers
  ADD COLUMN ingredient_quantity DECIMAL(18,4) NOT NULL DEFAULT 0,
  ADD COLUMN ingredient_unit VARCHAR(50) NOT NULL DEFAULT '';
ALTER TABLE sales ADD COLUMN request_key VARCHAR(80) NULL,
  ADD UNIQUE KEY uq_sales_request (request_key);
CREATE TABLE inventory_operations (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  kind VARCHAR(24) NOT NULL,
  request_key VARCHAR(80) NOT NULL,
  fingerprint CHAR(64) NOT NULL,
  user_id INT NOT NULL,
  branch_id INT NULL,
  result_id INT NULL,
  snapshot LONGTEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_inventory_request (kind, request_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE inventory_movements (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  operation_id BIGINT UNSIGNED NOT NULL,
  sale_item_id INT NULL,
  product_id INT NOT NULL,
  batch_id INT NOT NULL,
  branch_id INT NULL,
  quantity DECIMAL(18,4) NOT NULL,
  unit VARCHAR(50) NOT NULL,
  product_name VARCHAR(150) NOT NULL,
  unit_cost DECIMAL(10,2) NOT NULL DEFAULT 0,
  component VARCHAR(80) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_operation (operation_id),
  INDEX idx_sale_item (sale_item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
CREATE TABLE inventory_reversals (
  sale_id INT NOT NULL PRIMARY KEY,
  operation_id BIGINT UNSIGNED NOT NULL,
  policy VARCHAR(32) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
