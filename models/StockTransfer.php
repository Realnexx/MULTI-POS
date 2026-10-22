<?php
require_once __DIR__.'/Product.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/Setting.php';
require_once __DIR__.'/ActivityLog.php';

/** Quantity transfers preserve product ownership and use the inventory ledger. */
class StockTransfer
{
    public static function permissions() { return ['transfer_products','approve_stock_transfers','receive_stock_transfers','view_stock_transfers']; }
    public static function ready() { return (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('stock_transfers','stock_transfer_items','stock_transfer_allocations','stock_transfer_events')")[0]['n']===4; }
    private static function actor($permission, $branch=null) {
        $actor=(int)($_SESSION['user']['id'] ?? 0);
        if (!User::has_permission($actor,$permission)) throw new DomainException('You do not have permission for this transfer action.');
        if($branch!==null) {
            $context=User::access_context($actor);
            if(!$context['global'] && (int)$context['branch_id']!==(int)$branch) throw new DomainException('This transfer action belongs to another branch.');
        }
        return $actor;
    }
    private static function activeBranch($id) {
        if(!ctype_digit((string)$id) || !RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[(int)$id])) throw new DomainException('Choose an active branch.');
        return (int)$id;
    }
    private static function productAt($id,$branch) {
        $p=RecipeInventory::product($id);
        if($p['branch_id']!==null && (int)$p['branch_id']!==(int)$branch) throw new DomainException('Select a product belonging to the selected branch.');
        if($p['status']!=='active' || !$p['track_stock'] || in_array($p['inventory_type'],['non-stock','unlimited'],true) || in_array($p['product_type'],['variant','service'],true)
            || ($p['product_type']==='recipe' && $p['recipe_mode']!=='prepared_batch')) throw new DomainException('Only tracked ingredients, ordinary products and prepared recipe stock can be transferred.');
        return $p;
    }
    private static function pair($source,$destination,$from,$to) {
        $s=self::productAt($source,$from);$d=self::productAt($destination,$to);
        $identity=static function($name){return mb_strtolower(preg_replace('/\s+/u',' ',trim($name)),'UTF-8');};
        if($identity($s['name'])!==$identity($d['name'])) throw new DomainException('The destination must be the same product as the source. Select the same product name at both branches.');
        if(InventoryQuantity::unit($s['unit_type'])!==InventoryQuantity::unit($d['unit_type']) || $s['product_type']!==$d['product_type'] || (int)$s['is_ingredient']!==(int)$d['is_ingredient']) throw new DomainException('Source and destination items must have matching product types and stock units.');
        return [$s,$d];
    }
    private static function event($id,$action,$actor,$operation,$details) {
        RecipeInventory::query('INSERT INTO stock_transfer_events(transfer_id,action,actor_id,operation_id,details) VALUES(?,?,?,?,?)','isiis',[$id,$action,$actor,$operation,json_encode($details)]);
        ActivityLog::log('Stock Transfer '.ucfirst($action),'Transfer #'.$id.'; operation #'.$operation);
        RecipeInventory::complete($operation,$id,$details);
    }
    public static function create($from,$to,array $items,$notes,$key) {
        $from=self::activeBranch($from);$to=self::activeBranch($to);$actor=self::actor('transfer_products',$from);
        if($from===$to) throw new DomainException('Choose different source and destination branches.');
        if(!$items || count($items)>50) throw new DomainException('Add between 1 and 50 stock items.');
        $notes=trim((string)$notes);if(strlen($notes)>4000) throw new DomainException('Notes are too long.');
        return RecipeInventory::atomic(function()use($from,$to,$items,$notes,$key,$actor){
            $op=RecipeInventory::operation('stock_transfer_request',$key,[$from,$to,$items,$notes],$actor,$from);
            if($op['result_id']!==null) return (int)$op['result_id'];
            $ids=[];foreach($items as $row) { $ids[]=(int)($row['source_product_id'] ?? 0);$ids[]=(int)($row['destination_product_id'] ?? 0); }
            RecipeInventory::lockProducts($ids);
            $checked=[];$seen=[];
            foreach($items as $row) {
                list($s,$d)=self::pair((int)($row['source_product_id'] ?? 0),(int)($row['destination_product_id'] ?? 0),$from,$to);
                if(isset($seen[$s['id']])) throw new DomainException('Select each source product once.');$seen[$s['id']]=true;
                $qty=InventoryQuantity::convert(InventoryQuantity::parse($row['quantity'] ?? ''),$s['unit_type'],$s['unit_type']);
                $checked[]=[$s,$d,$qty];
            }
            $required=Setting::get('stock_transfer_require_approval','1')==='1' ? 1 : 0;
            $status=$required ? 'pending_approval' : 'approved';
            RecipeInventory::query('INSERT INTO stock_transfers(source_branch_id,destination_branch_id,status,requires_approval,requested_by,notes) VALUES(?,?,?,?,?,?)','iisiis',[$from,$to,$status,$required,$actor,$notes]);
            global $conn;$id=(int)$conn->insert_id;
            foreach($checked as $row) { list($s,$d,$qty)=$row;
                RecipeInventory::query('INSERT INTO stock_transfer_items(transfer_id,source_product_id,destination_product_id,source_name,destination_name,unit,requested_quantity) VALUES(?,?,?,?,?,?,?)','iiissss',[$id,$s['id'],$d['id'],$s['name'],$d['name'],$s['unit_type'],InventoryQuantity::format($qty)]);
            }
            self::event($id,'requested',$actor,$op['id'],['notes'=>$notes,'approval_required'=>$required,'items'=>$items]);return $id;
        });
    }
    private static function locked($id) {
        $rows=RecipeInventory::rows('SELECT * FROM stock_transfers WHERE id=? FOR UPDATE','i',[(int)$id]);
        if(!$rows) throw new DomainException('Transfer not found.');return $rows[0];
    }
    public static function transition($id,$action,$key,$notes='',array $received=[]) {
        $rules=['approve'=>'approve_stock_transfers','reject'=>'approve_stock_transfers','cancel'=>'transfer_products','dispatch'=>'transfer_products','receive'=>'receive_stock_transfers'];
        if(!isset($rules[$action])) throw new DomainException('Unknown transfer action.');
        $notes=trim((string)$notes);if(strlen($notes)>4000) throw new DomainException('Notes are too long.');
        return RecipeInventory::atomic(function()use($id,$action,$key,$notes,$received,$rules){
            $t=self::locked($id);$branch=$action==='receive' ? $t['destination_branch_id'] : $t['source_branch_id'];
            $actor=self::actor($rules[$action],$branch);
            $op=RecipeInventory::operation('stock_transfer_'.$action,$key,[(int)$id,$notes,$received],$actor,(int)$branch);
            if($op['result_id']!==null) return (int)$op['result_id'];
            $items=RecipeInventory::rows('SELECT * FROM stock_transfer_items WHERE transfer_id=? ORDER BY id','i',[$id]);
            if(in_array($action,['approve','reject'],true)) {
                if($t['status']!=='pending_approval') throw new DomainException('Only requests awaiting approval can be approved or rejected.');
                if($action==='approve' && (int)$t['requested_by']===$actor) throw new DomainException('Another authorized approver must approve this request.');
                if($action==='reject' && $notes==='') throw new DomainException('Enter a rejection reason.');
                $status=$action==='approve' ? 'approved' : 'rejected';
                RecipeInventory::query('UPDATE stock_transfers SET status=?,approved_by=?,approved_at=NOW() WHERE id=?','sii',[$status,$actor,$id]);
            } elseif($action==='cancel') {
                if(!in_array($t['status'],['pending_approval','approved'],true)) throw new DomainException('Dispatched or completed transfers cannot be cancelled.');
                if($notes==='') throw new DomainException('Enter a cancellation reason.');
                RecipeInventory::query("UPDATE stock_transfers SET status='cancelled' WHERE id=?",'i',[$id]);
            } else {
                $ids=[];foreach($items as $item) {$ids[]=$item['source_product_id'];$ids[]=$item['destination_product_id'];}RecipeInventory::lockProducts($ids);
                foreach($items as $item) {
                    list($s,$d)=self::pair($item['source_product_id'],$item['destination_product_id'],$t['source_branch_id'],$t['destination_branch_id']);
                    if($s['unit_type']!==$item['unit']) throw new DomainException('Stock units changed. Cancel and recreate this request.');
                }
                if($action==='dispatch') {
                    if($t['status']!=='approved') throw new DomainException('Only approved transfers can be dispatched.');
                    foreach($items as $item) {
                        $remaining=InventoryQuantity::parse($item['requested_quantity']);
                        $batches=RecipeInventory::rows("SELECT * FROM product_batches WHERE product_id=? AND branch_id=? AND quantity>0 AND status='active' AND (expiry_date IS NULL OR expiry_date>=".RecipeInventory::expiryDateSql().") ORDER BY created_at,id FOR UPDATE",'ii',[$item['source_product_id'],$t['source_branch_id']]);
                        foreach($batches as $batch) {
                            if(!$remaining) break;$take=min($remaining,InventoryQuantity::parse($batch['quantity']));
                            RecipeInventory::consume($item['source_product_id'],$take,$actor,$t['source_branch_id'],$op['id'],null,'transfer dispatch #'.$id,(int)$batch['id']);
                            RecipeInventory::query('INSERT INTO stock_transfer_allocations(item_id,source_batch_id,quantity,cost_price,selling_price,expiry_date) VALUES(?,?,?,?,?,?)','iissss',[$item['id'],$batch['id'],InventoryQuantity::format($take),$batch['cost_price'],$batch['selling_price'],$batch['expiry_date']]);$remaining-=$take;
                        }
                        if($remaining) throw new DomainException($item['source_name'].': insufficient usable stock to dispatch.');
                        RecipeInventory::query('UPDATE stock_transfer_items SET dispatched_quantity=requested_quantity WHERE id=?','i',[$item['id']]);
                    }
                    RecipeInventory::query("UPDATE stock_transfers SET status='dispatched',dispatched_by=?,dispatched_at=NOW() WHERE id=?",'ii',[$actor,$id]);
                } else {
                    if($t['status']!=='dispatched') throw new DomainException('Only dispatched transfers can be received.');
                    if(count($received)!==count($items)) throw new DomainException('Confirm the received quantity for every item.');
                    $discrepancy=false;
                    foreach($items as $item) {
                        $entry=$received[$item['id']] ?? null;if(!is_array($entry)) throw new DomainException('Confirm every received item.');
                        $actual=InventoryQuantity::convert(InventoryQuantity::parse($entry['quantity'] ?? '',true),$item['unit'],$item['unit']);
                        $sent=InventoryQuantity::parse($item['dispatched_quantity']);$delta=$actual-$sent;
                        $reason=trim((string)($entry['reason'] ?? ''));
                        if(strlen($reason)>2000 || ($delta!==0 && $reason==='')) throw new DomainException('Enter a reason for each quantity discrepancy.');
                        $discrepancy=$discrepancy || $delta!==0;$remaining=$actual;
                        $allocations=RecipeInventory::rows('SELECT * FROM stock_transfer_allocations WHERE item_id=? ORDER BY id','i',[$item['id']]);
                        foreach($allocations as $index=>$allocation) {
                            if(!$remaining) break;
                            $take=$index===count($allocations)-1 ? $remaining : min($remaining,InventoryQuantity::parse($allocation['quantity']));
                            $batch=RecipeInventory::receive($item['destination_product_id'],$take,$actor,$t['destination_branch_id'],$op['id'],'ST-'.$id.'-'.$allocation['id'],$allocation['cost_price'],$allocation['expiry_date'],$allocation['selling_price']);
                            RecipeInventory::query("UPDATE inventory_movements SET component='transfer received' WHERE operation_id=? AND batch_id=?",'ii',[$op['id'],$batch]);
                            $remaining-=$take;
                        }
                        $difference=($delta<0 ? '-' : '').InventoryQuantity::format(abs($delta));
                        RecipeInventory::query('UPDATE stock_transfer_items SET received_quantity=?,discrepancy_quantity=?,discrepancy_reason=? WHERE id=?','sssi',[InventoryQuantity::format($actual),$difference,$reason,$item['id']]);
                    }
                    $status=$discrepancy ? 'completed_discrepancy' : 'completed';
                    RecipeInventory::query('UPDATE stock_transfers SET status=?,received_by=?,received_at=NOW() WHERE id=?','sii',[$status,$actor,$id]);
                }
            }
            self::event($id,$action,$actor,$op['id'],['notes'=>$notes,'received'=>$received]);return (int)$id;
        });
    }
    public static function listing() {
        $actor=(int)$_SESSION['user']['id'];$allowed=false;foreach(self::permissions() as $p) $allowed=$allowed || User::has_permission($actor,$p);
        if(!$allowed) throw new DomainException('Transfer viewing permission is required.');
        $context=User::access_context($actor);$scope=$context['global'] ? '' : ' WHERE (t.source_branch_id='.(int)$context['branch_id'].' OR t.destination_branch_id='.(int)$context['branch_id'].')';
        return RecipeInventory::rows('SELECT t.*,s.name source_branch,d.name destination_branch,u.name requester FROM stock_transfers t JOIN branches s ON s.id=t.source_branch_id JOIN branches d ON d.id=t.destination_branch_id JOIN users u ON u.id=t.requested_by'.$scope.' ORDER BY t.id DESC');
    }
    public static function detail($id) {
        $rows=RecipeInventory::rows('SELECT * FROM stock_transfers WHERE id=?','i',[(int)$id]);if(!$rows) throw new DomainException('Transfer not found.');$t=$rows[0];
        $actor=(int)$_SESSION['user']['id'];$allowed=false;foreach(self::permissions() as $p) $allowed=$allowed || User::has_permission($actor,$p);
        if(!$allowed) throw new DomainException('Transfer viewing permission is required.');
        $c=User::access_context($actor);if(!$c['global'] && !in_array((int)$c['branch_id'],[(int)$t['source_branch_id'],(int)$t['destination_branch_id']],true)) throw new DomainException('This transfer belongs to another branch.');
        $t['items']=RecipeInventory::rows('SELECT * FROM stock_transfer_items WHERE transfer_id=? ORDER BY id','i',[$id]);
        $t['events']=RecipeInventory::rows('SELECT e.*,u.name actor_name FROM stock_transfer_events e JOIN users u ON u.id=e.actor_id WHERE transfer_id=? ORDER BY e.id','i',[$id]);
        return $t;
    }
}
