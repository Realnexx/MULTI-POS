<?php
require_once __DIR__.'/RecipeInventory.php';
require_once __DIR__.'/User.php';

/** Counts and loss reports share the checkout stock locks and movement ledger. */
class InventoryControl
{
    public static function ready()
    {
        return RecipeInventory::ready() && (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('inventory_reviews','inventory_reorder_levels')")[0]['n'] === 2;
    }

    private static function actor()
    {
        $id=(int)($_SESSION['user']['id'] ?? 0);
        if (!User::has_permission($id,'manage_inventory')) throw new DomainException('Inventory management permission is required.');
        if (!self::ready()) throw new DomainException('Inventory controls setup is pending.');
        return $id;
    }

    public static function item($id, $branch)
    {
        $p=RecipeInventory::product($id);
        if (!$branch || ($p['branch_id'] !== null && (int)$p['branch_id'] !== (int)$branch)) throw new DomainException('Select an item belonging to this branch.');
        if (!$p['track_stock'] || $p['status'] !== 'active' || in_array($p['inventory_type'],['non-stock','unlimited'],true) || in_array($p['product_type'],['variant','service'],true)
            || ($p['product_type']==='recipe' && $p['recipe_mode']!=='prepared_batch')) throw new DomainException('Select a physical stocked item. Made-to-order recipes are counted as ingredients.');
        return $p;
    }

    private static function quantity($value, $p)
    {
        return InventoryQuantity::convert(InventoryQuantity::parse($value,true),$p['unit_type'],$p['unit_type']);
    }

    private static function note($value)
    {
        $value=trim((string)$value);
        if ($value==='' || strlen($value)>2000) throw new DomainException('Enter a reason of up to 2,000 characters.');
        return $value;
    }

    public static function snapshot($id, $branch)
    {
        // Include batch composition and expiry, not only the total; a sale followed by a receipt invalidates a count too.
        $p=self::item($id,$branch);
        $batches=RecipeInventory::rows("SELECT id,quantity,status,expiry_date,cost_price FROM product_batches WHERE product_id=? AND branch_id=? ORDER BY id",'ii',[$id,$branch]);
        $movement=RecipeInventory::rows('SELECT COALESCE(MAX(id),0) last_id FROM inventory_movements WHERE product_id=? AND branch_id=?','ii',[$id,$branch])[0]['last_id'];
        return hash('sha256',json_encode([date('Y-m-d'),$p['unit_type'],$p['recipe_mode'],$movement,$batches]));
    }

    public static function record($id, $requestedBranch, $kind, $quantity, $notes, $key)
    {
        $user=self::actor(); $branch=RecipeInventory::branch($requestedBranch);
        $notes=self::note($notes);
        if (!in_array($kind,['count','damaged','expired','lost'],true)) throw new DomainException('Select a valid record type.');
        if (!preg_match('/^[a-f0-9]{32}$/D',(string)$key)) throw new DomainException('Reload the page and retry.');
        return RecipeInventory::atomic(function () use ($id,$branch,$kind,$quantity,$notes,$key,$user) {
            RecipeInventory::lockProducts([$id]); $p=self::item($id,$branch);
            $qty=self::quantity($quantity,$p);
            if ($kind!=='count' && !$qty) throw new DomainException('Loss quantity must be greater than zero.');
            $fingerprint=hash('sha256',json_encode([$id,$branch,$kind,$qty,$notes,$user]));
            $old=RecipeInventory::rows('SELECT * FROM inventory_reviews WHERE request_key=? FOR UPDATE','s',[$key]);
            if ($old) {
                if (!hash_equals($old[0]['request_fingerprint'],$fingerprint)) throw new DomainException('Request token already used. Reload the page.');
                return (int)$old[0]['id'];
            }
            $expected=RecipeInventory::balance($id,$branch);
            $actual=$kind==='count' ? $qty : $expected;
            $difference=$kind==='count' ? $qty-$expected : -$qty;
            $status=$kind==='count' ? ($difference===0 ? 'matched':'open') : 'recorded';
            $signed=($difference<0 ? '-':'').InventoryQuantity::format(abs($difference));
            RecipeInventory::query('INSERT INTO inventory_reviews(product_id,branch_id,product_name,unit,kind,expected_quantity,actual_quantity,difference,stock_fingerprint,notes,status,created_by,request_key,request_fingerprint) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                'iisssssssssiss',[$id,$branch,$p['name'],$p['unit_type'],$kind,InventoryQuantity::format($expected),InventoryQuantity::format($actual),$signed,self::snapshot($id,$branch),$notes,$status,$user,$key,$fingerprint]);
            global $conn; $review=(int)$conn->insert_id;
            if ($kind!=='count') {
                // Existing waste engine supports expired batches and retains their original costs.
                $op=RecipeInventory::operation('inventory_loss',$key,[$review,$kind],$user,$branch);
                if ($kind==='expired') {
                    $expired=RecipeInventory::rows("SELECT id,quantity FROM product_batches WHERE product_id=? AND branch_id=? AND quantity>0 AND (status='expired' OR (status='active' AND expiry_date<CURRENT_DATE)) ORDER BY created_at,id FOR UPDATE",'ii',[$id,$branch]);
                    $available=0;
                    foreach ($expired as $batch) $available+=InventoryQuantity::parse($batch['quantity'],true);
                    if ($available<$qty) throw new DomainException('Not enough expired stock. Expired losses can only deduct expired batches.');
                    $remaining=$qty;
                    foreach ($expired as $batch) {
                        if (!$remaining) break;
                        $take=min($remaining,InventoryQuantity::parse($batch['quantity'],true));
                        RecipeInventory::consume($id,$take,$user,$branch,$op['id'],null,'waste: expired review #'.$review,(int)$batch['id']);
                        $remaining-=$take;
                    }
                } else RecipeInventory::consume($id,$qty,$user,$branch,$op['id'],null,'waste: '.$kind.' review #'.$review);
                RecipeInventory::complete($op['id'],$review,['kind'=>$kind,'notes'=>$notes]);
                RecipeInventory::query("UPDATE inventory_reviews SET operation_id=?,resolved_by=?,resolved_at=NOW() WHERE id=?",'iii',[$op['id'],$user,$review]);
            }
            return $review;
        });
    }

    public static function resolve($id, $action, $notes)
    {
        $user=self::actor(); $notes=self::note($notes);
        if (!in_array($action,['reconcile','dismiss'],true)) throw new DomainException('Invalid resolution.');
        return RecipeInventory::atomic(function () use ($id,$action,$notes,$user) {
            $rows=RecipeInventory::rows('SELECT * FROM inventory_reviews WHERE id=?','i',[$id]);
            if (!$rows) throw new DomainException('Count not found.');
            $r=$rows[0]; $branch=RecipeInventory::branch($r['branch_id']);
            RecipeInventory::lockProducts([$r['product_id']]);
            $r=RecipeInventory::rows('SELECT * FROM inventory_reviews WHERE id=? FOR UPDATE','i',[$id])[0];
            if ($r['kind']!=='count') throw new DomainException('Only counts can be reconciled.');
            if ($r['status']!=='open') {
                if (($action==='reconcile' && $r['status']==='reconciled') || ($action==='dismiss' && $r['status']==='dismissed')) return;
                throw new DomainException('This count is already closed.');
            }
            $operation=null;
            if ($action==='reconcile') {
                self::item($r['product_id'],$branch);
                if (!hash_equals($r['stock_fingerprint'],self::snapshot($r['product_id'],$branch))) throw new DomainException('Stock changed since this count. Dismiss it and record a fresh count before reconciling.');
                $delta=InventoryQuantity::parse($r['actual_quantity'],true)-InventoryQuantity::parse($r['expected_quantity'],true);
                $op=RecipeInventory::operation('inventory_count',md5('inventory-count:'.$id),[$id],$user,$branch); $operation=$op['id'];
                if ($delta>0) RecipeInventory::receive($r['product_id'],$delta,$user,$branch,$operation,'COUNT-'.$id);
                if ($delta<0) RecipeInventory::consume($r['product_id'],-$delta,$user,$branch,$operation,null,'count reconciliation #'.$id);
                RecipeInventory::complete($operation,$id,['count'=>$id,'notes'=>$notes]);
            }
            RecipeInventory::query('UPDATE inventory_reviews SET status=?,resolution_notes=?,resolved_by=?,resolved_at=NOW(),operation_id=? WHERE id=?','ssiii',[$action==='reconcile'?'reconciled':'dismissed',$notes,$user,$operation,$id]);
        });
    }

    public static function reorder($id, $requestedBranch, $level)
    {
        $user=self::actor(); $branch=RecipeInventory::branch($requestedBranch);
        RecipeInventory::atomic(function () use ($id,$branch,$level,$user) {
            RecipeInventory::lockProducts([$id]); $p=self::item($id,$branch); $qty=self::quantity($level,$p);
            if ($qty<InventoryQuantity::parse($p['low_stock_threshold'],true)) throw new DomainException('Reorder level must be at least the minimum stock threshold.');
            RecipeInventory::query('INSERT INTO inventory_reorder_levels(product_id,branch_id,reorder_level,updated_by) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE reorder_level=VALUES(reorder_level),updated_by=VALUES(updated_by)','iisi',[$id,$branch,InventoryQuantity::format($qty),$user]);
        });
    }
}
