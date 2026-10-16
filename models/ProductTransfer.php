<?php
require_once __DIR__ . '/Product.php';
require_once __DIR__ . '/User.php';

class ProductTransfer
{
    public static function canTransfer($actor, $source)
    {
        if (!User::has_permission($actor,'reassign_products')) return false;
        $context=User::access_context($actor);
        return $context['global'] || ($source !== null && $source !== 'global' && $context['branch_id'] && (int)$source===(int)$context['branch_id']);
    }
    /** Move the complete product assignment and physical batches; never rewrite old ledger rows. */
    public static function run($id, $destination, $actor, $key, $expectedSource)
    {
        if ((int)($_SESSION['user']['id'] ?? 0) !== (int)$actor || !self::canTransfer($actor,$expectedSource)) {
            throw new DomainException('Transfer permission and access to the source branch are required.');
        }
        if (!ctype_digit((string)$destination) || (int)$destination < 1) throw new DomainException('Choose a destination branch.');
        if ($expectedSource !== 'global' && (!ctype_digit((string)$expectedSource) || (int)$expectedSource < 1)) throw new DomainException('Reload the product before transferring it.');
        $destination=(int)$destination; $id=(int)$id;
        return RecipeInventory::atomic(function () use ($id,$destination,$actor,$key,$expectedSource) {
            $target=RecipeInventory::rows("SELECT id,name FROM branches WHERE id=? AND status='Active' FOR UPDATE",'i',[$destination]);
            if (!$target) throw new DomainException('Choose an active destination branch.');
            $op=RecipeInventory::operation('branch_transfer',$key,[$id,$destination,(string)$expectedSource],$actor,$destination);
            if ($op['result_id'] !== null) return json_decode($op['snapshot'],true);
            RecipeInventory::lockProducts([$id]);
            require_once __DIR__.'/Purchasing.php';
            Purchasing::protectOpenOrders($id);
            $product=RecipeInventory::product($id);
            $source=$product['branch_id'] === null ? null : (int)$product['branch_id'];
            require_once __DIR__.'/StockTransfer.php';
            if(StockTransfer::ready() && RecipeInventory::rows("SELECT i.id FROM stock_transfer_items i JOIN stock_transfers t ON t.id=i.transfer_id WHERE (i.source_product_id=? OR i.destination_product_id=?) AND t.status IN ('pending_approval','approved','dispatched') LIMIT 1",'ii',[$id,$id])) throw new DomainException('Complete or cancel open stock transfer requests before moving product ownership.');
            if (($source === null ? 'global' : (string)$source) !== (string)$expectedSource) throw new DomainException('This product’s branch changed. Reload before transferring it.');
            if ($source === $destination) throw new DomainException('Choose a different destination branch.');
            $dependencies=RecipeInventory::rows('SELECT p.id FROM product_recipes r JOIN products p ON p.id=r.recipe_product_id WHERE r.ingredient_product_id=? AND (p.branch_id IS NULL OR p.branch_id<>?) LIMIT 1','ii',[$id,$destination]);
            $extras=RecipeInventory::rows('SELECT p.id FROM product_modifiers m JOIN products p ON p.id=m.product_id WHERE m.ingredient_product_id=? AND (p.branch_id IS NULL OR p.branch_id<>?) LIMIT 1','ii',[$id,$destination]);
            if ($dependencies || $extras) throw new DomainException('This ingredient is used by recipes or add-ons in another branch. Update those references before transferring it.');
            foreach (Product::get_recipes($id) as $row) RecipeInventory::ingredient($row['ingredient_product_id'],$id,$row['quantity'],$row['unit'],$destination);
            foreach (Product::get_modifiers($id) as $row) if ($row['ingredient_product_id']) RecipeInventory::ingredient($row['ingredient_product_id'],$id,$row['ingredient_quantity'],$row['ingredient_unit'],$destination);
            $pending=RecipeInventory::rows("SELECT o.id FROM online_orders o WHERE o.status IN ('pending','preparing','ready') AND (EXISTS (SELECT 1 FROM online_order_items i WHERE i.order_id=o.id AND i.product_id=?) OR EXISTS (SELECT 1 FROM inventory_movements m WHERE m.operation_id=o.operation_id AND m.product_id=? AND m.quantity<0)) LIMIT 1",'ii',[$id,$id]);
            if ($pending) throw new DomainException('Finish or cancel open online orders using this product before transferring it.');
            $batches=RecipeInventory::rows('SELECT * FROM product_batches WHERE product_id=? AND quantity>0 ORDER BY id FOR UPDATE','i',[$id]);
            if (!$batches && InventoryQuantity::parse($product['stock'],true)>0) throw new DomainException('Stock has no matching batches. Reconcile inventory before transferring this product.');
            foreach ($batches as $batch) {
                if (($batch['branch_id'] === null ? null : (int)$batch['branch_id']) !== $source) throw new DomainException('This shared product has stock assigned to other branches. Reconcile those balances before transferring its ownership.');
                if (!in_array($batch['status'],['active','expired'],true)) throw new DomainException('Reconcile inconsistent batch balances before transferring this product.');
            }
            $total=0; $links=[];
            foreach ($batches as $batch) {
                $quantity=InventoryQuantity::parse($batch['quantity']); $total+=$quantity;
                RecipeInventory::query("UPDATE product_batches SET quantity=0,status='depleted' WHERE id=?",'i',[$batch['id']]);
                RecipeInventory::query('INSERT INTO inventory_movements(operation_id,product_id,batch_id,branch_id,quantity,unit,product_name,unit_cost,component) VALUES (?,?,?,?,?,?,?,?,?)','iiiisssss',[$op['id'],$id,$batch['id'],$source,'-'.InventoryQuantity::format($quantity),$product['unit_type'],$product['name'],$batch['cost_price'],'branch transfer out']);
                RecipeInventory::query('INSERT INTO inventory_logs(product_id,user_id,branch_id,batch_id,action,quantity,remarks) VALUES (?,?,?,?,?,?,?)','iiiisss',[$id,$actor,$source,$batch['id'],'stock_out',InventoryQuantity::format($quantity),'Branch transfer to '.$target[0]['name'].' operation #'.$op['id']]);
                $received=RecipeInventory::receive($id,$quantity,$actor,$destination,$op['id'],'TRANSFER-'.$op['id'].'-'.$batch['id'],$batch['cost_price'],$batch['expiry_date'],$batch['selling_price']);
                if ($batch['status']==='expired') RecipeInventory::query("UPDATE product_batches SET status='expired' WHERE id=?",'i',[$received]);
                RecipeInventory::query("UPDATE inventory_movements SET component='branch transfer in' WHERE operation_id=? AND batch_id=?",'ii',[$op['id'],$received]);
                $links[]=['source_batch'=>(int)$batch['id'],'destination_batch'=>(int)$received,'quantity'=>InventoryQuantity::format($quantity)];
            }
            RecipeInventory::query('UPDATE products SET branch_id=? WHERE id=?','ii',[$destination,$id]);
            RecipeInventory::syncTotal($id);
            RecipeInventory::query("UPDATE stock_notifications SET status='resolved' WHERE product_id=? AND status='unread'",'i',[$id]);
            $result=['product_id'=>$id,'source_branch'=>$source,'destination_branch'=>$destination,'destination_name'=>$target[0]['name'],'quantity'=>InventoryQuantity::format($total),'batches'=>$links];
            RecipeInventory::complete($op['id'],$id,$result);
            return $result;
        });
    }
}
