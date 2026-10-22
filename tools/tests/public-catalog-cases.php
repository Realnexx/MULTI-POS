<?php
require_once $workspace.'/models/PublicCatalog.php';require_once $workspace.'/models/Cart.php';
$id=product('Public catalog fixture','simple','pc','legacy',false);
check(!PublicCatalog::visible(Product::find_by_id($id)),'New products are private by default');
RecipeInventory::query('UPDATE products SET show_on_website=1 WHERE id=?','i',[$id]);
check(PublicCatalog::visible(Product::find_by_id($id)),'Enabled active sellable product is public');
check(count(PublicCatalog::products())===1,'Public catalog reads only published database products');
check(count(PublicCatalog::categories())===1,'Only categories containing published products appear');
RecipeInventory::query("UPDATE products SET status='inactive' WHERE id=?",'i',[$id]);check(!PublicCatalog::products(),'Inactive product is excluded even when website flag enabled');
RecipeInventory::query("UPDATE products SET status='active',is_sellable=0 WHERE id=?",'i',[$id]);check(!PublicCatalog::products(),'Internal non-sellable products excluded');
RecipeInventory::query("UPDATE products SET is_sellable=1,is_ingredient=1 WHERE id=?",'i',[$id]);check(!PublicCatalog::products(),'Ingredients excluded');
RecipeInventory::query('UPDATE products SET is_ingredient=0,show_on_website=0 WHERE id=?','i',[$id]);
reject(function()use($id){Cart::add($id,1,'private-catalog',1);},'Hidden products cannot be added through a direct cart request');
check(!PublicCatalog::categories(),'Hidden-only categories excluded');
