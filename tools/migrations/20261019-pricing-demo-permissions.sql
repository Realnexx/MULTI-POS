-- Install page/action permissions without restoring revoked grants on rerun.
START TRANSACTION;
CREATE TEMPORARY TABLE new_website_permissions(name VARCHAR(100) PRIMARY KEY,description VARCHAR(255),module VARCHAR(100));
INSERT INTO new_website_permissions SELECT 'view_pricing_tiers','View Pricing Tiers','Pricing Tiers' WHERE NOT EXISTS(SELECT 1 FROM permissions WHERE name='view_pricing_tiers');
INSERT INTO new_website_permissions SELECT 'add_pricing_tiers','Add Pricing Tiers','Pricing Tiers' WHERE NOT EXISTS(SELECT 1 FROM permissions WHERE name='add_pricing_tiers');
INSERT INTO new_website_permissions SELECT 'edit_pricing_tiers','Edit Pricing Tiers, Features and Publication Status','Pricing Tiers' WHERE NOT EXISTS(SELECT 1 FROM permissions WHERE name='edit_pricing_tiers');
INSERT INTO new_website_permissions SELECT 'view_demo_requests','View Demo Requests, Details and Contact Information','Demo Requests' WHERE NOT EXISTS(SELECT 1 FROM permissions WHERE name='view_demo_requests');
INSERT INTO new_website_permissions SELECT 'update_demo_request_status','Update Demo Request Status','Demo Requests' WHERE NOT EXISTS(SELECT 1 FROM permissions WHERE name='update_demo_request_status');
INSERT INTO permissions(name,description,module) SELECT name,description,module FROM new_website_permissions;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN new_website_permissions n JOIN permissions p ON BINARY p.name=BINARY n.name WHERE LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin');
DROP TEMPORARY TABLE new_website_permissions;
COMMIT;
