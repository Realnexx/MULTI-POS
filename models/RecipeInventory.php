<?php
require_once __DIR__ . '/InventoryQuantity.php';

/** Transaction owner for recipe definitions, stock, production and checkout. */
class RecipeInventory
{
    private static $depth = 0;
    private static $locked = [];

    public static function expiryDateSql(){return "'".(new DateTimeImmutable('now',new DateTimeZone('Asia/Manila')))->format('Y-m-d')."'";}

    public static function ready()
    {
        try {
            $tables=self::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name IN ('inventory_operations','inventory_movements','inventory_reversals')")[0];
            return (bool)self::rows("SHOW COLUMNS FROM products LIKE 'recipe_mode'") && (int)$tables['n'] === 3;
        }
        catch (Throwable $error) { return false; }
    }

    private static function cents($value)
    {
        $scaled=InventoryQuantity::parse($value,true);
        if ($scaled % 100 !== 0) throw new DomainException('Money amounts support at most two decimal places.');
        return intdiv($scaled,100);
    }

    public static function query($sql, $types = '', $params = [])
    {
        global $conn;
        $stmt = $conn->prepare($sql);
        if ($types !== '') $stmt->bind_param($types, ...$params);
        $stmt->execute();
        return $stmt;
    }

    public static function rows($sql, $types = '', $params = [])
    {
        return self::query($sql, $types, $params)->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function atomic(callable $work)
    {
        global $conn;
        if (self::$depth > 0) return $work();
        $conn->begin_transaction();
        self::$locked = [];
        self::$depth++;
        try {
            $result = $work();
            $conn->commit();
            return $result;
        } catch (Throwable $error) {
            $conn->rollback();
            throw $error;
        } finally {
            self::$depth--;
            self::$locked = [];
        }
    }

    public static function branch($requested = null)
    {
        require_once __DIR__ . '/User.php';
        $context = User::access_context($_SESSION['user']['id'] ?? 0);
        $assigned = $context['branch_id'];
        if (!$context['global'] && !$assigned) throw new DomainException('Ask an administrator to assign your branch first.');
        $requested = $requested ? (int) $requested : ($assigned ? (int) $assigned : null);
        if ($requested !== ($assigned ? (int) $assigned : null)) {
            require_once __DIR__ . '/User.php';
            if (!User::has_all_branch_access($_SESSION['user']['id'] ?? 0)) {
                throw new DomainException('You cannot change stock for another branch.');
            }
        }
        if ($requested !== null && !self::rows('SELECT id FROM branches WHERE id = ?', 'i', [$requested])) {
            throw new DomainException('Select a valid branch.');
        }
        return $requested;
    }

    public static function product($id)
    {
        $rows = self::rows('SELECT * FROM products WHERE id = ?' . (isset(self::$locked[(int)$id]) ? ' FOR UPDATE' : ''), 'i', [(int) $id]);
        if (!$rows) throw new DomainException('Product no longer exists.');
        return $rows[0];
    }

    public static function lockProducts(array $ids)
    {
        $ids = array_unique(array_map('intval', $ids));
        sort($ids, SORT_NUMERIC);
        foreach ($ids as $id) {
            if (!self::rows('SELECT id FROM products WHERE id = ? FOR UPDATE', 'i', [$id])) {
                throw new DomainException('An inventory item no longer exists.');
            }
            self::$locked[$id] = true;
        }
    }

    public static function ingredient($id, $owner, $quantity, $unit, $branch)
    {
        $ingredient = self::product($id);
        if ((int) $id === (int) $owner || $ingredient['product_type'] === 'recipe'
            || !($ingredient['is_ingredient'] || $ingredient['product_type'] === 'ingredient')
            || $ingredient['status'] !== 'active' || !$ingredient['track_stock']) {
            throw new DomainException('Select an active raw ingredient; nested recipes are not supported.');
        }
        if ($ingredient['branch_id'] !== null && (int) $ingredient['branch_id'] !== (int) $branch) {
            throw new DomainException('Ingredient belongs to another branch.');
        }
        return InventoryQuantity::convert(InventoryQuantity::parse($quantity), $unit ?: $ingredient['unit_type'], $ingredient['unit_type']);
    }

    public static function saveRecipe($id, array $ingredients)
    {
        require_once __DIR__ . '/User.php';
        return self::atomic(function () use ($id, $ingredients) {
            $owner = self::product($id);
            if ($owner['branch_id'] === null && !User::has_all_branch_access($_SESSION['user']['id'])) throw new DomainException('Only Admin or Superadmin can edit an all-branches recipe.');
            $branch = $owner['branch_id'] === null ? null : self::branch($owner['branch_id']);
            self::lockProducts(array_merge([$id], array_column($ingredients, 'ingredient_id')));
            $owner = self::product($id);
            $branch = $owner['branch_id'] === null ? null : self::branch($owner['branch_id']);
            if ($owner['product_type'] !== 'recipe') throw new DomainException('Only recipe products can have ingredients.');
            if (!$ingredients) throw new DomainException('Add at least one ingredient before saving a recipe.');
            $seen = [];
            foreach ($ingredients as $row) {
                $ing = (int) ($row['ingredient_id'] ?? 0);
                if (isset($seen[$ing])) throw new DomainException('Select each ingredient only once.');
                self::ingredient($ing, $id, $row['quantity'] ?? '', $row['unit'] ?? '', $branch);
                $seen[$ing] = true;
            }
            self::query('DELETE FROM product_recipes WHERE recipe_product_id = ?', 'i', [$id]);
            foreach ($ingredients as $row) {
                $ingredient = self::product($row['ingredient_id']);
                self::query('INSERT INTO product_recipes (recipe_product_id, ingredient_product_id, quantity, unit) VALUES (?, ?, ?, ?)',
                    'iiss', [$id, (int) $row['ingredient_id'], InventoryQuantity::format(InventoryQuantity::parse($row['quantity'])),
                    InventoryQuantity::unit($row['unit'] ?: $ingredient['unit_type'])]);
            }
            return true;
        });
    }

    public static function setMode($id, $mode, $new = false, $reconciled = false)
    {
        $p = self::product($id);
        if ($p['product_type'] !== 'recipe') return;
        if (!in_array($mode,['made_to_order','prepared_batch','legacy'],true)) throw new DomainException('Select a valid recipe stock mode.');
        if ($new && $mode === 'legacy') throw new DomainException('Select made to order or prepared batch.');
        if (!$new && $mode !== $p['recipe_mode']) {
            if (!$reconciled) throw new DomainException('Review existing stock and confirm reconciliation before changing the stock mode.');
            $batches = self::rows('SELECT id FROM product_batches WHERE product_id = ? AND quantity > 0 FOR UPDATE', 'i', [$id]);
            if ($mode === 'made_to_order' && $batches) {
                throw new DomainException('Resolve existing finished batches before switching to made to order.');
            }
        }
        self::query('UPDATE products SET recipe_mode = ?, stock = CASE WHEN ? = ? THEN 0 ELSE stock END WHERE id = ?',
            'sssi',[$mode,$mode,'made_to_order',$id]);
    }

    public static function validateIdentity($id, $unit, $branch, $type)
    {
        $old=self::product($id);
        self::branch($old['branch_id']);
        if (($old['product_type'] === 'recipe' || $type === 'recipe') && $old['product_type'] !== $type) {
            throw new DomainException('Create a new product to change between recipe and ordinary stock.');
        }
        if($old['product_type']!==$type&&in_array('combo',[$old['product_type'],$type],true))throw new DomainException('Create a new product to change between a package and ordinary stock.');
        $changed=InventoryQuantity::unit($old['unit_type']) !== InventoryQuantity::unit($unit)
            || ($old['branch_id'] === null ? null : (int)$old['branch_id']) !== $branch;
        if($changed){require_once __DIR__.'/ProductBundle.php';if(ProductBundle::ready()&&self::rows('SELECT id FROM product_combos WHERE item_product_id=? LIMIT 1 FOR UPDATE','i',[$id]))throw new DomainException('Remove this product from packages before changing its branch or stock unit.');}
        if ($changed) {
            require_once __DIR__.'/Purchasing.php';
            Purchasing::protectOpenOrders($id);
        }
        if ($changed && (self::rows('SELECT id FROM product_batches WHERE product_id = ? LIMIT 1 FOR UPDATE','i',[$id])
            || self::rows('SELECT id FROM product_recipes WHERE ingredient_product_id = ? LIMIT 1 FOR UPDATE','i',[$id]))) {
            throw new DomainException('Stock units and ownership cannot be changed after batches or recipes exist. Create a new item and reconcile explicitly.');
        }
    }

    public static function saveModifiers($id, array $modifiers)
    {
        return self::atomic(function () use ($id, $modifiers) {
            $owner = self::product($id);
            $branch = self::branch($owner['branch_id']);
            self::lockProducts(array_merge([$id], array_filter(array_column($modifiers, 'ingredient_product_id'))));
            $owner = self::product($id);
            $branch = self::branch($owner['branch_id']);
            foreach ($modifiers as $mod) {
                if (trim($mod['modifier_name'] ?? '') === '' || !is_numeric($mod['additional_price'] ?? 0)
                    || (float) ($mod['additional_price'] ?? 0) < 0) throw new DomainException('Enter a modifier name and valid extra price.');
                if (!empty($mod['ingredient_product_id'])) {
                    self::ingredient($mod['ingredient_product_id'], $id, $mod['ingredient_quantity'] ?? '1', $mod['ingredient_unit'] ?? '', $branch);
                }
            }
            // Retire rather than delete: historical modifier references remain distinguishable.
            self::query('UPDATE product_modifiers SET is_active = 0 WHERE product_id = ?', 'i', [$id]);
            foreach ($modifiers as $mod) {
                $ing = !empty($mod['ingredient_product_id']) ? (int) $mod['ingredient_product_id'] : null;
                $unit = $ing ? ($mod['ingredient_unit'] ?? '') : '';
                if ($ing && $unit === '') $unit = self::product($ing)['unit_type'];
                self::query('INSERT INTO product_modifiers (product_id, modifier_name, additional_price, ingredient_product_id, ingredient_quantity, ingredient_unit) VALUES (?, ?, ?, ?, ?, ?)',
                    'ississ', [$id, trim($mod['modifier_name']), (string) ($mod['additional_price'] ?? 0), $ing,
                    $ing ? InventoryQuantity::format(InventoryQuantity::parse($mod['ingredient_quantity'] ?? '1')) : '0', $unit]);
            }
            return true;
        });
    }

    public static function balance($productId, $branch)
    {
        if (isset(self::$locked[(int)$productId])) {
            $rows=self::rows("SELECT quantity FROM product_batches WHERE product_id = ? AND branch_id <=> ? AND status = 'active' AND quantity > 0 AND (expiry_date IS NULL OR expiry_date >= ".self::expiryDateSql().") ORDER BY id FOR UPDATE",'ii',[$productId,$branch]);
            $sum=0;
            foreach ($rows as $row) $sum+=InventoryQuantity::parse($row['quantity'],true);
            return $sum;
        }
        $row = self::rows("SELECT COALESCE(SUM(quantity),0) AS qty FROM product_batches WHERE product_id = ? AND branch_id <=> ? AND status = 'active' AND quantity > 0 AND (expiry_date IS NULL OR expiry_date >= ".self::expiryDateSql().")", 'ii', [$productId, $branch])[0];
        return InventoryQuantity::parse($row['qty'], true);
    }

    public static function syncTotal($id)
    {
        self::query("UPDATE products SET stock = (SELECT COALESCE(SUM(quantity),0) FROM product_batches WHERE product_id = ? AND status = 'active' AND quantity > 0 AND (expiry_date IS NULL OR expiry_date >= ".self::expiryDateSql().")) WHERE id = ?", 'ii', [$id, $id]);
    }

    public static function recipe($id, $branch, $count)
    {
        $rows = self::rows('SELECT * FROM product_recipes WHERE recipe_product_id = ? ORDER BY ingredient_product_id' . (isset(self::$locked[(int)$id]) ? ' FOR UPDATE' : ''), 'i', [$id]);
        if (!$rows) throw new DomainException('This product has no recipe. Add ingredients first.');
        $result = [];
        foreach ($rows as $row) {
            $ingredient = (int) $row['ingredient_product_id'];
            $units = self::ingredient($ingredient, $id, $row['quantity'], $row['unit'], $branch) * $count;
            $result[$ingredient] = ($result[$ingredient] ?? 0) + $units;
        }
        return $result;
    }

    public static function availability($product, $branch)
    {
        if ($product['status'] !== 'active' || !$product['is_sellable']) return 0;
        if ($product['branch_id'] !== null && (int) $product['branch_id'] !== (int) $branch) return 0;
        if($product['product_type']==='combo'){require_once __DIR__.'/ProductBundle.php';try{return ProductBundle::capacity($product['id'],$branch);}catch(DomainException | InvalidArgumentException $e){return 0;}}
        if ($product['product_type'] === 'recipe') {
            if (($product['recipe_mode'] ?? 'legacy') === 'legacy') return 0;
            if ($product['recipe_mode'] === 'made_to_order') {
                try {
                    $capacity = PHP_INT_MAX;
                    foreach (self::recipe($product['id'], $branch, 1) as $id => $need) {
                        $capacity = min($capacity, intdiv(self::balance($id, $branch), $need));
                    }
                    return $capacity;
                } catch (DomainException | InvalidArgumentException $error) { return 0; }
            }
        }
        if (!$product['track_stock'] || in_array($product['inventory_type'], ['non-stock', 'unlimited'], true)) return 999999;
        return intdiv(self::balance($product['id'], $branch), InventoryQuantity::SCALE);
    }

    /** Read-only capacity by branch, using the same normalized recipe requirements as checkout. */
    public static function capacityReport($id,$requested=null)
    {
        require_once __DIR__.'/User.php';$p=self::product($id);if(!in_array($p['product_type'],['recipe','combo'],true))throw new DomainException('Select a recipe or package product.');
        $ctx=User::access_context($_SESSION['user']['id']??0);if(!$ctx['global']){$branch=self::branch($requested);$branches=self::rows("SELECT id,name FROM branches WHERE id=? AND status='Active'",'i',[$branch]);}
        elseif($requested){$branch=self::branch($requested);$branches=self::rows("SELECT id,name FROM branches WHERE id=? AND status='Active'",'i',[$branch]);}
        elseif($p['branch_id']!==null)$branches=self::rows("SELECT id,name FROM branches WHERE id=? AND status='Active'",'i',[$p['branch_id']]);
        else $branches=self::rows("SELECT id,name FROM branches WHERE status='Active' ORDER BY name");
        $out=[];foreach($branches as $branch){$branchId=(int)$branch['id'];if($p['branch_id']!==null&&(int)$p['branch_id']!==$branchId)throw new DomainException('Product belongs to another branch.');$capacity=PHP_INT_MAX;$ingredients=[];$problem=null;
            try{require_once __DIR__.'/ProductBundle.php';$requirements=$p['product_type']==='combo'?ProductBundle::quote($id,1,$branchId)['needs']:self::recipe($id,$branchId,1);foreach($requirements as $ingredientId=>$need){if($need<=0)throw new DomainException('Recipe ingredient quantities must be positive.');$ingredient=self::product($ingredientId);$balance=self::balance($ingredientId,$branchId);$units=intdiv($balance,$need);$capacity=min($capacity,$units);$ingredients[]=['name'=>$ingredient['name'],'unit'=>$ingredient['unit_type'],'per_unit'=>InventoryQuantity::format($need),'available'=>InventoryQuantity::format($balance),'capacity'=>$units];}}catch(DomainException | InvalidArgumentException $e){$problem=$e->getMessage();$capacity=0;}
            if($capacity===PHP_INT_MAX)$capacity=0;foreach($ingredients as &$row)$row['limiting']=$row['capacity']===$capacity;unset($row);
            $out[]=['branch'=>$branch['name'],'branch_id'=>$branchId,'capacity'=>$capacity,'ready_stock'=>InventoryQuantity::format(self::balance($id,$branchId)),'ingredients'=>$ingredients,'problem'=>$problem];
        }
        return ['name'=>$p['name'],'mode'=>$p['product_type']==='combo'?'combo':($p['recipe_mode']??'legacy'),'branches'=>$out,'note'=>'Uses active, unexpired ingredient batches in each branch. Capacities are independent estimates: recipes can share ingredients, and optional extras may reduce the quantity available.'];
    }

    public static function checkRecipe($id, $count, $branch)
    {
        $shortage = [];
        foreach (self::recipe($id, $branch, $count) as $ing => $need) {
            $available = self::balance($ing, $branch);
            $p = self::product($ing);
            if ($available < $need) $shortage[] = ['ingredient'=>$p['name'], 'required'=>InventoryQuantity::format($need),
                'available'=>InventoryQuantity::format($available), 'unit'=>$p['unit_type']];
        }
        return ['ok'=>!$shortage, 'shortage'=>$shortage];
    }

    public static function catalogAvailability($product, $branch)
    {
        if ($branch !== null) return self::availability($product,$branch);
        if ($product['branch_id'] !== null) return self::availability($product,(int)$product['branch_id']);
        // Sum capacities per location; never combine ingredients from different branches.
        $capacity=self::availability($product,null);
        foreach (self::rows("SELECT id FROM branches WHERE LOWER(status) = 'active'") as $row) {
            $capacity+=self::availability($product,(int)$row['id']);
        }
        return $capacity;
    }

    public static function operation($kind, $key, $payload, $user, $branch)
    {
        if (!preg_match('/^[a-f0-9]{32,64}$/D', $key)) throw new DomainException('Invalid request token. Reload and retry.');
        $fingerprint = hash('sha256', json_encode([$payload, (int) $user, $branch]));
        self::query('INSERT INTO inventory_operations (kind, request_key, fingerprint, user_id, branch_id, snapshot) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)',
            'sssiis', [$kind, $key, $fingerprint, $user, $branch, '{}']);
        $op = self::rows('SELECT * FROM inventory_operations WHERE kind = ? AND request_key = ? FOR UPDATE', 'ss', [$kind, $key])[0];
        if (!hash_equals($op['fingerprint'], $fingerprint)) throw new DomainException('Request token was already used for a different operation.');
        return $op;
    }

    public static function complete($op, $id, $snapshot)
    {
        self::query('UPDATE inventory_operations SET result_id = ?, snapshot = ? WHERE id = ?', 'isi', [$id, json_encode($snapshot), $op]);
    }

    /** Caller owns transaction and locks all relevant products before this method. */
    public static function consume($id, $need, $user, $branch, $op, $item, $component, $batchId = null)
    {
        if (self::$depth === 0) throw new LogicException('Consumption requires an inventory transaction.');
        $p = self::product($id);
        $sql = "SELECT * FROM product_batches WHERE product_id = ? AND branch_id <=> ? AND quantity > 0";
        $sql .= strpos($component,'waste') === 0 ? " AND status IN ('active','expired')" : " AND status = 'active' AND (expiry_date IS NULL OR expiry_date >= ".self::expiryDateSql().")";
        $params = [$id, $branch]; $types = 'ii';
        if ($batchId !== null) { $sql .= ' AND id = ?'; $params[] = $batchId; $types .= 'i'; }
        $batches = self::rows($sql . ' ORDER BY created_at, id FOR UPDATE', $types, $params);
        $sum = 0;
        foreach ($batches as $batch) $sum += InventoryQuantity::parse($batch['quantity'], true);
        if ($sum < $need) throw new DomainException($p['name'] . ': need ' . number_format($need / InventoryQuantity::SCALE, 2, '.', '') . ' ' . $p['unit_type'] . ', available ' . number_format($sum / InventoryQuantity::SCALE, 2, '.', '') . '.');
        foreach ($batches as $batch) {
            if ($need <= 0) break;
            $available = InventoryQuantity::parse($batch['quantity'], true);
            $take = min($available, $need);
            self::query('UPDATE product_batches SET quantity = ?, status = ? WHERE id = ?', 'ssi',
                [InventoryQuantity::format($available-$take), $available === $take ? 'depleted':$batch['status'], $batch['id']]);
            self::query('INSERT INTO inventory_movements (operation_id, sale_item_id, product_id, batch_id, branch_id, quantity, unit, product_name, component) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)',
                'iiiiissss', [$op, $item, $id, $batch['id'], $branch, '-' . InventoryQuantity::format($take), $p['unit_type'], $p['name'], $component]);
            self::query('UPDATE inventory_movements SET unit_cost = ? WHERE id = LAST_INSERT_ID()', 's', [$batch['cost_price']]);
            self::query('INSERT INTO inventory_logs (product_id, user_id, action, quantity, remarks, batch_id, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
                'iisssii', [$id,$user,'stock_out',InventoryQuantity::format($take),$component . ' operation #' . $op,$batch['id'],$branch]);
            $need -= $take;
        }
        self::syncTotal($id);
    }

    public static function receive($id, $quantity, $user, $branch, $op, $name, $cost = null, $expiry = null, $price = null)
    {
        $p = self::product($id);
        $label = $name ?: 'IN-' . $op;
        self::query('INSERT INTO product_batches (product_id, branch_id, batch_number, quantity, cost_price, selling_price, expiry_date) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'iisssss', [$id,$branch,$label,InventoryQuantity::format($quantity),$cost ?? $p['cost_price'],$price ?? $p['price'],$expiry ?: null]);
        global $conn;
        $batchId = $conn->insert_id;
        self::query('INSERT INTO inventory_movements (operation_id, product_id, batch_id, branch_id, quantity, unit, product_name, component) VALUES (?, ?, ?, ?, ?, ?, ?, ?)',
            'iiiissss',[$op,$id,$batchId,$branch,InventoryQuantity::format($quantity),$p['unit_type'],$p['name'],'stock_in']);
        self::query('UPDATE inventory_movements SET unit_cost = ? WHERE id = LAST_INSERT_ID()', 's', [(string)($cost ?? $p['cost_price'])]);
        self::query('INSERT INTO inventory_logs (product_id, user_id, action, quantity, remarks, batch_id, branch_id) VALUES (?, ?, ?, ?, ?, ?, ?)',
            'iisssii',[$id,$user,'stock_in',InventoryQuantity::format($quantity),'Receipt operation #' . $op,$batchId,$branch]);
        self::syncTotal($id);
        return $batchId;
    }

    public static function adjust($id, $user, $action, $quantity, $remarks, $branch, $batch = null, $cost = null, $expiry = null, $price = null, $batchId = null, $key = null)
    {
        if ($action==='stock_in' && self::product($id)['product_type']==='recipe') {
            return self::adjustRecipeStock($id,$quantity,$user,$branch,$key ?: '',$remarks,($_POST['confirm_recipe_preparation'] ?? '')==='1',$expiry);
        }
        return self::atomic(function () use ($id,$user,$action,$quantity,$remarks,$branch,$batch,$cost,$expiry,$price,$batchId,$key) {
            if (!in_array($action,['initial','stock_in','stock_out','waste'],true)) throw new DomainException('Invalid stock operation.');
            $branch = self::branch($branch);
            self::lockProducts([$id]);
            $p = self::product($id);
            if($p['product_type']==='combo')throw new DomainException('Packages have no physical stock. Adjust their component products instead.');
            if ($p['branch_id'] !== null && (int)$p['branch_id'] !== (int)$branch) throw new DomainException('Product belongs to another branch.');
            if ($p['product_type'] === 'recipe' && $action !== 'waste' && $action !== 'stock_out') {
                throw new DomainException('Use Prepare batch for finished recipe products.');
            }
            $qty = InventoryQuantity::convert(InventoryQuantity::parse($quantity, $action === 'initial'),$p['unit_type'],$p['unit_type']);
            if ($qty === 0) { self::syncTotal($id); return true; }
            $op = self::operation('adjustment',$key ?: bin2hex(random_bytes(16)),[$id,$action,(string)$quantity,$remarks,$batchId,$batch,$cost,$expiry,$price],$user,$branch);
            if ($op['result_id'] !== null) return true;
            if (in_array($action,['stock_out','waste'],true)) self::consume($id,$qty,$user,$branch,$op['id'],null,$action . ': ' . substr($remarks,0,50),$batchId);
            else self::receive($id,$qty,$user,$branch,$op['id'],$batch,$cost,$expiry,$price);
            self::complete($op['id'],$id,['remarks'=>$remarks]);
            return true;
        });
    }

    public static function adjustRecipeStock($id,$quantity,$user,$branch,$key,$remarks,$confirmed=false,$expiry=null)
    {
        require_once __DIR__.'/User.php';
        if ((int)($_SESSION['user']['id'] ?? 0)!==(int)$user || !User::has_permission($user,'manage_inventory')) throw new DomainException('Inventory management permission is required.');
        if (!preg_match('/^([1-9][0-9]{0,5})(?:\.0{1,2})?$/D',(string)$quantity) || (int)$quantity>100000) throw new DomainException('Enter a whole recipe stock quantity from 1 to 100000.');
        if ($expiry && (!preg_match('/^\d{4}-\d{2}-\d{2}$/D',$expiry) || date('Y-m-d',strtotime($expiry))!==$expiry)) throw new DomainException('Enter a valid expiry date.');
        $count=(int)$quantity;
        return self::atomic(function () use ($id,$count,$user,$branch,$key,$remarks,$confirmed,$expiry) {
            $branch=self::branch($branch);
            if (!$branch) throw new DomainException('Choose a physical branch for the prepared stock.');
            $op=self::operation('recipe_adjustment',$key,[$id,$count,$remarks,$confirmed,$expiry],$user,$branch);
            if ($op['result_id']!==null) return true;
            $needs=self::recipe($id,$branch,$count);
            self::lockProducts(array_merge([$id],array_keys($needs)));
            $product=self::product($id);
            if ($product['product_type']!=='recipe' || !$product['is_sellable']) throw new DomainException('Create a sellable product from this recipe definition before preparing stock.');
            if ($product['branch_id']!==null && (int)$product['branch_id']!==$branch) throw new DomainException('Product belongs to another branch.');
            if (($product['recipe_mode'] ?? 'legacy')==='legacy') throw new DomainException('Reconcile this existing recipe in Edit Product before preparing stock.');
            if ($product['recipe_mode']==='made_to_order') {
                if (!$confirmed) throw new DomainException('Confirm preparation and the switch to finished-stock sales before adding recipe stock.');
                if (!User::has_permission($user,'edit_product') || ($product['branch_id']===null && !User::has_all_branch_access($user))) throw new DomainException('A product manager must change this product to finished-stock mode first.');
                self::setMode($id,'prepared_batch',false,true);
            }
            $batch=self::prepare($id,$count,$user,$branch,hash('sha256','manual-preparation:'.$key));
            if ($expiry) self::query('UPDATE product_batches SET expiry_date=? WHERE id=?','si',[$expiry,$batch]);
            self::syncTotal($id);
            self::complete($op['id'],$batch,['product_id'=>$id,'quantity'=>$count,'remarks'=>$remarks,'expiry'=>$expiry]);
            return true;
        });
    }

    public static function prepare($id, $count, $user, $branch, $key)
    {
        return self::atomic(function () use ($id,$count,$user,$branch,$key) {
            $branch = self::branch($branch);
            if (!ctype_digit((string)$count) || $count < 1 || $count > 100000) throw new DomainException('Enter a whole batch size from 1 to 100000.');
            $op = self::operation('production',$key,[$id,(int)$count],$user,$branch);
            if ($op['result_id'] !== null) return (int)$op['result_id'];
            $needs = self::recipe($id,$branch,(int)$count);
            self::lockProducts(array_merge([$id],array_keys($needs)));
            $p = self::product($id);
            if ($p['status'] !== 'active' || $p['product_type'] !== 'recipe' || ($p['recipe_mode'] ?? '') !== 'prepared_batch') throw new DomainException('Select an active, reconciled prepared-batch recipe.');
            if ($p['branch_id'] !== null && (int)$p['branch_id'] !== (int)$branch) throw new DomainException('Product belongs to another branch.');
            $fresh = self::recipe($id,$branch,(int)$count);
            if (array_keys($fresh) !== array_keys($needs)) throw new DomainException('Recipe changed. Reload and retry.');
            foreach ($fresh as $ing=>$qty) self::consume($ing,$qty,$user,$branch,$op['id'],null,'production ingredient');
            $productionCost=self::rows("SELECT COALESCE(SUM(-quantity*COALESCE(unit_cost,0)),0) cost FROM inventory_movements WHERE operation_id=? AND quantity<0",'i',[$op['id']])[0]['cost'];
            $batch = self::receive($id,(int)$count*InventoryQuantity::SCALE,$user,$branch,$op['id'],'PREP-' . $op['id'],number_format((float)$productionCost/(int)$count,4,'.',''));
            self::complete($op['id'],$batch,['product'=>$p,'recipe'=>$fresh,'count'=>(int)$count]);
            return $batch;
        });
    }

    public static function quoteItem($id, $quantity, array $selected, $branch)
    {
        if (!ctype_digit((string)$quantity) || $quantity < 1 || $quantity > 100000) throw new DomainException('Enter a valid whole sale quantity.');
        $p = self::product($id);
        if ($p['status'] !== 'active' || !$p['is_sellable'] || $p['product_type'] === 'ingredient') throw new DomainException('This item is not available for sale.');
        if ($p['branch_id'] !== null && (int)$p['branch_id'] !== (int)$branch) throw new DomainException('Product belongs to another branch.');
        $needs = [];$bundleComponents=[];$lockIds=[];
        if($p['product_type']==='combo'){require_once __DIR__.'/ProductBundle.php';$bundle=ProductBundle::quote($id,(int)$quantity,$branch);$needs=$bundle['needs'];$bundleComponents=$bundle['components'];$lockIds=$bundle['lock_ids'];}
        if ($p['product_type'] === 'recipe') {
            if (($p['recipe_mode'] ?? 'legacy') === 'legacy') throw new DomainException('Reconcile this existing recipe before selling it.');
            if ($p['recipe_mode'] === 'made_to_order') $needs = self::recipe($id,$branch,(int)$quantity);
            elseif ($p['recipe_mode'] === 'prepared_batch') $needs[$id] = (int)$quantity*InventoryQuantity::SCALE;
            else throw new DomainException('Invalid recipe mode.');
        } elseif ($p['product_type']!=='combo' && $p['track_stock'] && !in_array($p['inventory_type'],['non-stock','unlimited'],true)) {
            $needs[$id] = (int)$quantity*InventoryQuantity::SCALE;
        }
        $price = self::cents($p['price']);
        $modifiers = []; $seen = [];
        foreach ($selected as $selectedMod) {
            $modId = (int)($selectedMod['id'] ?? 0);
            if (!$modId || isset($seen[$modId])) throw new DomainException('Invalid or duplicate modifier selection.');
            $rows = self::rows('SELECT * FROM product_modifiers WHERE id = ? AND product_id = ? AND is_active = 1' . (isset(self::$locked[(int)$id]) ? ' FOR UPDATE' : ''),'ii',[$modId,$id]);
            if (!$rows) throw new DomainException('A selected modifier changed. Reload the cart.');
            $mod = $rows[0]; $seen[$modId] = true;
            $price += self::cents($mod['additional_price']);
            if ($mod['ingredient_product_id']) {
                $ing = (int)$mod['ingredient_product_id'];
                $amount = self::ingredient($ing,$id,$mod['ingredient_quantity'],$mod['ingredient_unit'],$branch);
                $needs[$ing] = ($needs[$ing] ?? 0) + $amount*(int)$quantity;
            }
            $modifiers[] = $mod;
        }
        ksort($needs,SORT_NUMERIC);
        return ['product'=>$p,'quantity'=>(int)$quantity,'price_cents'=>$price,'modifiers'=>$modifiers,'needs'=>$needs,'bundle_components'=>$bundleComponents,'lock_ids'=>$lockIds];
    }

    public static function checkout($user, $branch, array $items, $discount, $tendered, $payment, $orderType, $key, $requireDrawer=false, array $promoContext=[])
    {
        return self::atomic(function () use ($user,$branch,$items,$discount,$tendered,$payment,$orderType,$key,$requireDrawer,$promoContext) {
            global $conn;
            $branch = self::branch($branch);
            if (!$items) throw new DomainException('Cart is empty.');
            if (!in_array($orderType,['Dine-in','Take-out','Online'],true)) throw new DomainException('Invalid order type.');
            require_once __DIR__.'/CheckoutPolicy.php';CheckoutPolicy::assertMethod($payment,true);
            $payload = [$items,(string)$discount,(string)$tendered,$payment,$orderType,$promoContext];
            $op = self::operation('sale',$key,$payload,$user,$branch);
            if ($op['result_id'] !== null) return (int)$op['result_id'];
            $quotes = []; $ids = [];
            require_once __DIR__.'/CashShift.php';
            $cashShift=CashShift::context($user,$branch,$requireDrawer);
            foreach ($items as $item) {
                $quote = self::quoteItem((int)$item['product_id'],$item['quantity'],$item['modifiers'] ?? [],$branch);
                $quotes[] = $quote;
                $ids[] = $quote['product']['id'];
                $ids = array_merge($ids,array_keys($quote['needs']),$quote['lock_ids']??[]);
            }
            self::lockProducts($ids);
            $quotes = []; $totalNeeds = []; $subtotal = 0;
            foreach ($items as $item) {
                $quote = self::quoteItem((int)$item['product_id'],$item['quantity'],$item['modifiers'] ?? [],$branch);
                foreach($quote['lock_ids']??[] as $lockedId)if(!in_array((int)$lockedId,array_map('intval',$ids),true))throw new DomainException('Package contents changed. Reload the cart.');
                foreach ($quote['needs'] as $id=>$need) {
                    if (!in_array($id,array_map('intval',$ids),true)) throw new DomainException('Recipe changed. Reload the cart.');
                    $totalNeeds[$id] = ($totalNeeds[$id] ?? 0)+$need;
                }
                $subtotal += $quote['price_cents']*$quote['quantity'];
                $quotes[] = $quote;
            }
            ksort($totalNeeds,SORT_NUMERIC);
            foreach ($totalNeeds as $id=>$need) {
                if (self::balance($id,$branch) < $need) {
                    $p = self::product($id);
                    throw new DomainException($p['name'] . ': insufficient stock for this order.');
                }
            }
            $eligible=0;foreach($quotes as $q)if(!empty($q['product']['is_discountable']))$eligible+=$q['price_cents']*$q['quantity'];
            require_once __DIR__.'/Promotion.php';
            $financial=Promotion::financial($quotes,$branch,'pos',$promoContext['customer_id']??null,$promoContext['code']??'',(string)$discount,true,$user,$promoContext['approval_id']??null);
            if(isset($promoContext['expected_total'])&&(int)$promoContext['expected_total']!==$financial['total_cents'])throw new DomainException('The promotion or total changed. Reload your cart before paying.');
            $discountCents=$financial['discount_cents'];
            $tenderCents = self::cents($tendered);
            if ($discountCents < 0 || $discountCents > $subtotal) throw new DomainException('Invalid discount.');
            $final=$financial['total_cents'];
            if ($tenderCents < $final) throw new DomainException('Amount tendered is less than the current total.');
            self::query("INSERT INTO sales (cashier_id, branch_id, total_amount, discount_amount, final_amount, amount_tendered, change_amount, payment_method, order_type, status, request_key) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Completed', ?)",
                'iissssssss',[$user,$branch,number_format($subtotal/100,2,'.',''),number_format($discountCents/100,2,'.',''),number_format($final/100,2,'.',''),number_format($tenderCents/100,2,'.',''),number_format(($tenderCents-$final)/100,2,'.',''),$payment,$orderType,$key]);
            $sale = $conn->insert_id;
            require_once __DIR__.'/ProductBundle.php';$financial['bundle_contents']=ProductBundle::snapshots($quotes);
            CheckoutPolicy::receipt($sale,$financial);
            Promotion::redeem($financial,$branch,$promoContext['customer_id']??null,$sale);
            if(!empty($financial['manual_discount']['approval_id']))self::query("UPDATE discount_approvals SET status='used',sale_id=? WHERE id=? AND status='approved'",'ii',[$sale,$financial['manual_discount']['approval_id']]);
            if(!empty($financial['manual_discount'])){require_once __DIR__.'/ActivityLog.php';ActivityLog::log('Manual discount applied','Sale #'.$sale.' discount PHP '.number_format($discountCents/100,2,'.','').' approval #'.($financial['manual_discount']['approval_id']??'not required'));}
            CashShift::sale($cashShift,$sale,$payment,number_format($final/100,2,'.',''),$user);
            foreach ($quotes as $quote) {
                self::query('INSERT INTO sales_items (sale_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)',
                    'iiiss',[$sale,$quote['product']['id'],$quote['quantity'],$quote['product']['price'],number_format($quote['price_cents']*$quote['quantity']/100,2,'.','')]);
                $saleItem = $conn->insert_id;
                foreach ($quote['modifiers'] as $mod) {
                    self::query('INSERT INTO sale_item_modifiers (sales_item_id, modifier_name, additional_price, ingredient_product_id, ingredient_quantity, ingredient_unit) VALUES (?, ?, ?, ?, ?, ?)',
                        'ississ',[$saleItem,$mod['modifier_name'],$mod['additional_price'],$mod['ingredient_product_id'],$mod['ingredient_quantity'],$mod['ingredient_unit']]);
                }
                foreach ($quote['needs'] as $id=>$need) self::consume($id,$need,$user,$branch,$op['id'],$saleItem,$quote['product']['product_type']==='combo'?'bundle_item:'.$quote['product']['id']:'sale consumption');
            }
            self::complete($op['id'],$sale,['lines'=>$quotes,'discount_cents'=>$discountCents]);
            return $sale;
        });
    }

    public static function reverseSale($saleId, $policy, $user, $key)
    {
        return self::atomic(function () use ($saleId,$policy,$user,$key) {
            if (!in_array($policy,['void_before_preparation','return_finished'],true)) throw new DomainException('Select a supported stock return policy.');
            $sales = self::rows('SELECT * FROM sales WHERE id = ? FOR UPDATE','i',[$saleId]);
            if (!$sales) throw new DomainException('Sale does not exist.');
            $sale=$sales[0]; $branch=self::branch($sale['branch_id']);
            require_once __DIR__.'/PayMongo.php';PayMongo::assertLocalRefundAllowed($saleId);
            $op=self::operation('reversal',$key,[$saleId,$policy],$user,$branch);
            if ($op['result_id'] !== null) return true;
            if (self::rows('SELECT sale_id FROM inventory_reversals WHERE sale_id = ?','i',[$saleId])) throw new DomainException('Stock for this sale has already been reversed.');
            $original=self::rows('SELECT * FROM inventory_operations WHERE kind = ? AND result_id = ?','si',['sale',$saleId]);
            if (!$original) $original=self::rows('SELECT io.* FROM online_orders o JOIN inventory_operations io ON io.id=o.operation_id WHERE o.sale_id=?','i',[$saleId]);
            if (!$original) throw new DomainException('This older sale has no recorded consumption. Manual reconciliation is required.');
            $snapshot=json_decode($original[0]['snapshot'],true);
            $finished=[];
            foreach ($snapshot['lines'] as $line) {
                $p=$line['product'];
                if($p['product_type']==='combo'){
                    foreach($line['bundle_components']??[] as $child){
                        if($policy==='return_finished'&&$child['product_type']==='recipe'&&$child['recipe_mode']!=='prepared_batch')throw new DomainException('This package contains made-to-order food. Use a before-preparation void only if it has not been prepared; otherwise record waste.');
                        $finished[(int)$child['product_id']]=true;
                    }
                    continue;
                }
                if ($policy === 'void_before_preparation' && ($p['product_type'] !== 'recipe' || $p['recipe_mode'] !== 'made_to_order')) {
                    throw new DomainException('A before-preparation void is only available for made-to-order items.');
                }
                if ($policy === 'return_finished' && $p['product_type'] === 'recipe' && $p['recipe_mode'] !== 'prepared_batch') {
                    throw new DomainException('Prepared-to-order food cannot be returned as raw ingredients. Record waste instead.');
                }
                $finished[(int)$p['id']]=true;
            }
            require_once __DIR__.'/CashShift.php';
            CashShift::refund($sale,$user);
            $moves=self::rows('SELECT * FROM inventory_movements WHERE operation_id = ? AND quantity < 0 ORDER BY product_id,batch_id,id','i',[$original[0]['id']]);
            self::lockProducts(array_column($moves,'product_id'));
            foreach ($moves as $move) {
                if ($policy === 'return_finished' && !isset($finished[(int)$move['product_id']])) continue;
                $currentProduct=self::product($move['product_id']);
                if ($currentProduct['branch_id'] !== null && (int)$currentProduct['branch_id'] !== (int)$branch) throw new DomainException('This product has moved to another branch. Reconcile its return with an inventory manager.');
                $batch=self::rows('SELECT * FROM product_batches WHERE id = ? FOR UPDATE','i',[$move['batch_id']]);
                if (!$batch || ($batch[0]['expiry_date'] && $batch[0]['expiry_date'] < date('Y-m-d'))) throw new DomainException('Original batch is missing or expired; record waste instead.');
                $quantity=InventoryQuantity::parse(ltrim($move['quantity'],'-'));
                self::query("UPDATE product_batches SET quantity = quantity + ?, status = 'active' WHERE id = ?",'si',[InventoryQuantity::format($quantity),$move['batch_id']]);
                self::query('INSERT INTO inventory_movements (operation_id,sale_item_id,product_id,batch_id,branch_id,quantity,unit,product_name,component) VALUES (?,?,?,?,?,?,?,?,?)',
                    'iiiiissss',[$op['id'],$move['sale_item_id'],$move['product_id'],$move['batch_id'],$branch,InventoryQuantity::format($quantity),$move['unit'],$move['product_name'],'reversal: ' . $policy]);
                self::query('UPDATE inventory_movements SET unit_cost = ? WHERE id = LAST_INSERT_ID()', 's', [$move['unit_cost']]);
                self::query('INSERT INTO inventory_logs (product_id,user_id,branch_id,batch_id,action,quantity,remarks) VALUES (?,?,?,?,?,?,?)',
                    'iiiisss',[$move['product_id'],$user,$branch,$move['batch_id'],'stock_in',InventoryQuantity::format($quantity),'Sale reversal #' . $saleId]);
                self::syncTotal($move['product_id']);
            }
            self::query('INSERT INTO inventory_reversals (sale_id,operation_id,policy) VALUES (?,?,?)','iis',[$saleId,$op['id'],$policy]);
            self::query("UPDATE sales SET status = 'Refunded' WHERE id = ?",'i',[$saleId]);
            require_once __DIR__.'/OnlineOrder.php';OnlineOrder::sync_refund($saleId,$user);require_once __DIR__.'/Promotion.php';Promotion::refund($saleId);
            self::complete($op['id'],$saleId,['original_operation'=>$original[0]['id'],'policy'=>$policy]);
            return true;
        });
    }
}
