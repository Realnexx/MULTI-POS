<?php
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/User.php';

/** Reusable, inventory-neutral recipe definitions use the existing product/recipe ledger. */
class RecipeLibrary
{
    public static function available()
    {
        $global = User::has_all_branch_access($_SESSION['user']['id']);
        $branch = $global ? null : RecipeInventory::branch();
        $sql = "SELECT p.*, b.name AS branch_name, (SELECT COUNT(*) FROM product_recipes r WHERE r.recipe_product_id=p.id) AS ingredient_count FROM products p LEFT JOIN branches b ON b.id=p.branch_id WHERE p.product_type='recipe' AND p.status='active'";
        return $global ? RecipeInventory::rows($sql . ' ORDER BY p.name,p.id')
            : RecipeInventory::rows($sql . ' AND (p.branch_id IS NULL OR p.branch_id=?) ORDER BY p.name,p.id', 'i', [$branch]);
    }

    public static function save($id, $name, $description, $category, $branch, array $ingredients, $key)
    {
        if (!User::has_permission($_SESSION['user']['id'], 'edit_product')) throw new DomainException('Recipe editing permission is required.');
        if (!$id && !User::has_permission($_SESSION['user']['id'], 'add_product')) throw new DomainException('Product creation permission is required.');
        $name = trim($name);
        if ($name === '') throw new DomainException('Enter a recipe name.');
        if (mb_strlen($name) > 255) throw new DomainException('Recipe name is too long.');
        return RecipeInventory::atomic(function () use ($id,$name,$description,$category,$branch,$ingredients,$key) {
            if ($id) RecipeInventory::lockProducts([$id]);
            $owner = $id ? RecipeInventory::product($id) : null;
            if ($owner && ($owner['product_type'] !== 'recipe' || $owner['status'] !== 'active')) throw new DomainException('Select an active recipe.');
            $global = User::has_all_branch_access($_SESSION['user']['id']);
            if ($owner && $owner['branch_id'] === null && !$global) throw new DomainException('Only Admin or Superadmin can change an all-branches recipe.');
            $can_change_scope = !$owner || (!$owner['is_sellable'] && !$owner['track_stock']);
            if (!$can_change_scope) {
                $branch = $owner['branch_id'] === null ? null : RecipeInventory::branch($owner['branch_id']);
            } elseif (in_array($branch, ['global','all'], true) || ($owner && $owner['branch_id'] === null && $branch === null)) {
                if (!$global) throw new DomainException('Only Admin or Superadmin can assign a recipe to all branches.');
                $branch = null;
            } else {
                if (!ctype_digit((string)$branch) || (int)$branch < 1) throw new DomainException('Choose a branch or Global (All branches).');
                $branch = RecipeInventory::branch($branch);
                if (!RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[$branch])) throw new DomainException('Choose an active branch.');
            }
            $operation = RecipeInventory::operation('recipe_definition', $key, [$id,$name,$description,$category,$branch,$ingredients], $_SESSION['user']['id'], $branch);
            if ($operation['result_id'] !== null) return (int)$operation['result_id'];
            if (!$id) {
                if (!RecipeInventory::rows('SELECT id FROM categories WHERE id=?','i',[$category])) throw new DomainException('Choose a valid category.');
                // Definitions are not sellable and have no opening stock. No stock batches are created.
                $id = Product::create($category,$name,Product::generate_unique_sku(),Product::generate_unique_barcode(),$description,0,0,'0','0','', 'active',0,0,'0',$branch,'recipe',0,0,0,'ingredient-based',null,'pc',1);
                if (!$id) throw new DomainException('Recipe could not be created.');
                RecipeInventory::setMode($id,'prepared_batch',true);
            } elseif ($can_change_scope && ($owner['branch_id'] === null ? null : (int)$owner['branch_id']) !== $branch) {
                RecipeInventory::validateIdentity($id,$owner['unit_type'],$branch,'recipe');
                RecipeInventory::query('UPDATE products SET branch_id=? WHERE id=?','ii',[$branch,$id]);
            }
            Product::save_recipes($id,$ingredients);
            RecipeInventory::query('UPDATE products SET name=?,description=? WHERE id=?','ssi',[$name,$description,$id]);
            RecipeInventory::complete($operation['id'],$id,['ingredients'=>$ingredients]);
            return (int)$id;
        });
    }
}
