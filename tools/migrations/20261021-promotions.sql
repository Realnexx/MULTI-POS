-- Promotions installation. Select your application database first.
CREATE TABLE IF NOT EXISTS promotions(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,description VARCHAR(500) NOT NULL,code VARCHAR(40) NULL UNIQUE,discount_type VARCHAR(12) NOT NULL,value_cents BIGINT NOT NULL,min_cents BIGINT NOT NULL DEFAULT 0,max_cents BIGINT NULL,starts_at DATETIME NOT NULL,ends_at DATETIME NOT NULL,channel VARCHAR(10) NOT NULL,status VARCHAR(12) NOT NULL DEFAULT 'draft',usage_limit INT NULL,customer_limit INT NULL,created_by INT NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,INDEX(status,starts_at,ends_at)) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS promotion_targets(promotion_id INT NOT NULL,target_type VARCHAR(12) NOT NULL,target_id INT NOT NULL,PRIMARY KEY(promotion_id,target_type,target_id)) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS promotion_redemptions(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,promotion_id INT NOT NULL,branch_id INT NOT NULL,customer_id INT NULL,sale_id INT NULL UNIQUE,order_id INT NULL UNIQUE,status VARCHAR(12) NOT NULL,discount_cents BIGINT NOT NULL,subtotal_cents BIGINT NOT NULL,net_cents BIGINT NOT NULL,snapshot LONGTEXT NOT NULL,created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,INDEX(promotion_id,status,customer_id),INDEX(branch_id,created_at)) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS discount_approvals(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,cashier_id INT NOT NULL,branch_id INT NOT NULL,request_hash CHAR(64) NOT NULL,amount_cents BIGINT NOT NULL,reason VARCHAR(255) NOT NULL,status VARCHAR(12) NOT NULL DEFAULT 'pending',approved_by INT NULL,sale_id INT NULL UNIQUE,created_at DATETIME NOT NULL,expires_at DATETIME NOT NULL,INDEX(cashier_id,status)) ENGINE=InnoDB;

SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='view_promotions' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'view_promotions','View Promotions','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='view_promotions' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='create_promotions' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'create_promotions','Create Promotions','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='create_promotions' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='edit_promotions' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'edit_promotions','Edit Promotion Rules','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='edit_promotions' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='activate_promotions' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'activate_promotions','Activate Pause and Archive Promotions','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='activate_promotions' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='apply_manual_discounts' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'apply_manual_discounts','Apply Manual Discounts Within Limit','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='apply_manual_discounts' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT rp.role_id,newp.id FROM role_permissions rp JOIN permissions oldp ON oldp.id=rp.permission_id CROSS JOIN permissions newp WHERE oldp.name='access_pos' AND newp.name='apply_manual_discounts' AND @promo_permission_exists IS NULL;
SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='approve_discount_overrides' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'approve_discount_overrides','Approve Above-Limit Manual Discounts','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='approve_discount_overrides' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
SET @promo_permission_exists = (SELECT id FROM permissions WHERE name='view_promotion_reports' LIMIT 1);
INSERT INTO permissions(name,description,module) SELECT 'view_promotion_reports','View Promotion Reports','Promotions' WHERE @promo_permission_exists IS NULL;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='view_promotion_reports' AND @promo_permission_exists IS NULL AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
INSERT IGNORE INTO settings(setting_key,setting_value,category) VALUES('cashier_discount_max_percent','10','financial');
