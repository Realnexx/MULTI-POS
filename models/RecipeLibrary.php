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

    private static function libraryScope() {
        $where="p.product_type='recipe' AND p.status='active'";$types='';$params=[];
        if(!User::has_all_branch_access($_SESSION['user']['id'])){$where.=' AND (p.branch_id IS NULL OR p.branch_id=?)';$types='i';$params[] = RecipeInventory::branch();}
        return [$where,$types,$params];
    }
    public static function page($start=0,$length=12,$search='') {
        if(!User::has_permission($_SESSION['user']['id'],'view_recipes'))throw new DomainException('Recipe viewing permission is required.');
        [$where,$types,$params]=self::libraryScope();
        $stats=RecipeInventory::rows("SELECT COUNT(*) total,COALESCE(SUM(p.branch_id IS NULL),0) shared FROM products p WHERE $where",$types,$params)[0];
        $total=(int)$stats['total'];$search=mb_substr(trim((string)$search),0,120);
        if($search!==''){$where.=' AND p.name LIKE ?';$types.='s';$params[]='%'.$search.'%';}
        $filtered=(int)RecipeInventory::rows("SELECT COUNT(*) n FROM products p WHERE $where",$types,$params)[0]['n'];
        $length=in_array((int)$length,[6,12,24,48],true)?(int)$length:12;
        $start=max(0,(int)$start);if($start>=$filtered)$start=$filtered?intdiv($filtered-1,$length)*$length:0;
        $rows=RecipeInventory::rows("SELECT p.id,p.name,p.branch_id,p.is_sellable,b.name branch_name,(SELECT COUNT(*) FROM product_recipes r WHERE r.recipe_product_id=p.id) ingredient_count FROM products p LEFT JOIN branches b ON b.id=p.branch_id WHERE $where ORDER BY p.name,p.id LIMIT ? OFFSET ?",$types.'ii',array_merge($params,[$length,$start]));
        return ['recordsTotal'=>$total,'recordsFiltered'=>$filtered,'shared'=>(int)$stats['shared'],'start'=>$start,'length'=>$length,'data'=>$rows];
    }
    public static function productSuggestions($search,$branch='') {
        if(!User::has_permission($_SESSION['user']['id'],'view_recipes'))throw new DomainException('Recipe viewing permission is required.');
        $where="p.status='active' AND p.is_sellable=1 AND p.product_type<>'ingredient'";$types='s';$params=['%'.mb_substr(trim($search),0,120).'%'];
        if(!User::is_product_branch_admin($_SESSION['user']['id'])){
            $assigned=(int)(User::access_context($_SESSION['user']['id'])['branch_id']??0);
            if(!$assigned)throw new DomainException('Your account needs an assigned branch to see product suggestions.');
            if($branch!==''&&$branch!=='global'&&(!ctype_digit((string)$branch)||(int)$branch!==$assigned))throw new DomainException('You can only see product suggestions from your assigned branch.');
            $where.=' AND (p.branch_id IS NULL OR p.branch_id=?)';$types.='i';$params[]=$assigned;
        }
        return RecipeInventory::rows("SELECT p.id,p.name,b.name branch_name,EXISTS(SELECT 1 FROM product_recipes r WHERE r.recipe_product_id=p.id) has_recipe FROM products p LEFT JOIN branches b ON b.id=p.branch_id WHERE $where AND p.name LIKE ? ORDER BY p.name,p.id LIMIT 20",substr($types,1).'s',array_merge(array_slice($params,1),[$params[0]]));
    }
    public static function findAvailable($id) {
        [$where,$types,$params]=self::libraryScope();
        return RecipeInventory::rows("SELECT p.*,b.name branch_name FROM products p LEFT JOIN branches b ON b.id=p.branch_id WHERE $where AND p.id=?",$types.'i',array_merge($params,[(int)$id]))[0]??null;
    }

    public static function save($id, $name, $description, $category, $branch, array $ingredients, $key)
    {
        if (!User::has_permission($_SESSION['user']['id'], 'view_recipes') || !User::has_permission($_SESSION['user']['id'], $id?'edit_recipes':'create_recipes')) throw new DomainException('Recipe management permission is required.');
        $name = trim($name);
        if ($name === '') throw new DomainException('Enter a recipe name.');
        if (mb_strlen($name) > 255) throw new DomainException('Recipe name is too long.');
        return RecipeInventory::atomic(function () use ($id,$name,$description,$category,$branch,$ingredients,$key) {
            if ($id) RecipeInventory::lockProducts([$id]);
            $owner = $id ? RecipeInventory::product($id) : null;
            if ($owner && ($owner['product_type'] !== 'recipe' || $owner['status'] !== 'active')) throw new DomainException('Select an active recipe.');
            $global = User::has_all_branch_access($_SESSION['user']['id']) && User::has_permission($_SESSION['user']['id'],'manage_global_recipes');
            if ($owner && $owner['branch_id'] === null && !$global) throw new DomainException('Shared recipe management permission and access to all branches are required.');
            $can_change_scope = !$owner || (!$owner['is_sellable'] && !$owner['track_stock']);
            if (!$can_change_scope) {
                $branch = $owner['branch_id'] === null ? null : RecipeInventory::branch($owner['branch_id']);
            } elseif (in_array($branch, ['global','all'], true) || ($owner && $owner['branch_id'] === null && $branch === null)) {
                if (!$global) throw new DomainException('Shared recipe management permission and access to all branches are required.');
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
