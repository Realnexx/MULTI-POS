CREATE TABLE IF NOT EXISTS product_combos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 bundle_product_id INT NOT NULL,
 item_product_id INT NOT NULL,
 quantity DECIMAL(18,4) NOT NULL DEFAULT 1.0000,
 station VARCHAR(80) NOT NULL DEFAULT 'General',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_bundle_item(bundle_product_id,item_product_id),
 INDEX idx_combo_item(item_product_id),
 FOREIGN KEY(bundle_product_id) REFERENCES products(id) ON DELETE CASCADE,
 FOREIGN KEY(item_product_id) REFERENCES products(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
