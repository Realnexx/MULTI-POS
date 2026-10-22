-- Add recipe permissions once; preserve grants revoked after installation.
SET @recipe_permission_new = (SELECT COUNT(*)=0 FROM permissions WHERE name='view_recipes');
INSERT INTO permissions(name,description,module) SELECT 'view_recipes','View Recipe Library and Ingredient Previews','Inventory' WHERE @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT DISTINCT rp.role_id,p.id FROM role_permissions rp JOIN permissions oldp ON oldp.id=rp.permission_id CROSS JOIN permissions p WHERE oldp.name='view_product' AND p.name='view_recipes' AND @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='view_recipes' AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin') AND @recipe_permission_new=1;
SET @recipe_permission_new = (SELECT COUNT(*)=0 FROM permissions WHERE name='create_recipes');
INSERT INTO permissions(name,description,module) SELECT 'create_recipes','Create Recipe Definitions','Inventory' WHERE @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT DISTINCT rp.role_id,p.id FROM role_permissions rp JOIN permissions oldp ON oldp.id=rp.permission_id CROSS JOIN permissions p WHERE oldp.name='add_product' AND p.name='create_recipes' AND @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='create_recipes' AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin') AND @recipe_permission_new=1;
SET @recipe_permission_new = (SELECT COUNT(*)=0 FROM permissions WHERE name='edit_recipes');
INSERT INTO permissions(name,description,module) SELECT 'edit_recipes','Edit Recipe Details and Ingredients','Inventory' WHERE @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT DISTINCT rp.role_id,p.id FROM role_permissions rp JOIN permissions oldp ON oldp.id=rp.permission_id CROSS JOIN permissions p WHERE oldp.name='edit_product' AND p.name='edit_recipes' AND @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='edit_recipes' AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin') AND @recipe_permission_new=1;
SET @recipe_permission_new = (SELECT COUNT(*)=0 FROM permissions WHERE name='manage_global_recipes');
INSERT INTO permissions(name,description,module) SELECT 'manage_global_recipes','Create and Edit Shared Recipes Across Branches','Inventory' WHERE @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT DISTINCT rp.role_id,p.id FROM role_permissions rp JOIN permissions oldp ON oldp.id=rp.permission_id CROSS JOIN permissions p WHERE oldp.name='edit_product' AND p.name='manage_global_recipes' AND @recipe_permission_new=1;
INSERT IGNORE INTO role_permissions(role_id,permission_id) SELECT r.id,p.id FROM roles r CROSS JOIN permissions p WHERE p.name='manage_global_recipes' AND LOWER(REPLACE(REPLACE(REPLACE(r.name,' ',''),'_',''),'-','')) IN ('admin','superadmin') AND @recipe_permission_new=1;
