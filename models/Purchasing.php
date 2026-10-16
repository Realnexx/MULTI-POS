<?php
require_once __DIR__.'/Product.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/ActivityLog.php';

class Purchasing
{
    public static function orderPermissions() { return ['view_purchase_orders','create_purchase_orders','approve_purchase_orders','receive_purchases','return_purchases']; }
    public static function protectOpenOrders($productId) {
        if (self::ready() && RecipeInventory::rows("SELECT i.id FROM purchase_order_items i JOIN purchase_orders o ON o.id=i.order_id WHERE i.product_id=? AND o.status IN ('draft','submitted','approved','partially_received') LIMIT 1 FOR UPDATE",'i',[$productId])) throw new DomainException('Complete or cancel open purchase orders before archiving or moving this product.');
    }
    public static function ready() {
        return RecipeInventory::ready() && (int)RecipeInventory::rows("SELECT COUNT(*) n FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name IN ('purchase_suppliers','purchase_orders','purchase_order_items','purchase_receipts','purchase_receipt_items','purchase_supplier_returns','purchase_supplier_return_items','purchase_events')")[0]['n']===8;
    }
    private static function actor($permissions,$branch=null) {
        $id=(int)($_SESSION['user']['id'] ?? 0); $ok=false;
        foreach ((array)$permissions as $p) if (User::has_permission($id,$p)) $ok=true;
        if (!$ok) throw new DomainException('You do not have permission for this purchasing action.');
        $ctx=User::access_context($id);
        if (!$ctx['global'] && (!$ctx['branch_id'] || ($branch!==null && (int)$branch!==$ctx['branch_id']))) throw new DomainException('This purchasing record belongs to another branch.');
        if (!self::ready()) throw new DomainException('Purchasing setup is pending.');
        return $id;
    }
    public static function scope($branch=null) {
        $ctx=User::access_context($_SESSION['user']['id'] ?? 0);
        if (!$ctx['global']) {
            if (!$ctx['branch_id'] || ($branch!==null && $branch!=='' && (int)$branch!==$ctx['branch_id'])) throw new DomainException('Choose your assigned branch.');
            return $ctx['branch_id'];
        }
        return $branch!==null && $branch!=='' ? self::activeBranch($branch) : null;
    }
    private static function activeBranch($branch) {
        if (!ctype_digit((string)$branch) || !RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[(int)$branch])) throw new DomainException('Choose an active branch.');
        return (int)$branch;
    }
    private static function text($value,$max,$required=false) {
        if (!is_scalar($value) && $value!==null) throw new DomainException('Invalid text value.');
        $v=trim((string)$value);
        if (strlen($v)>$max || ($required && $v==='')) throw new DomainException('Enter all required fields within their length limits.');
        return $v;
    }
    private static function date($value,$optional=false) {
        $v=self::text($value,10);
        if ($optional && $v==='') return null;
        $d=DateTimeImmutable::createFromFormat('!Y-m-d',$v);
        if (!$d || $d->format('Y-m-d')!==$v) throw new DomainException('Enter a valid date.');
        return $v;
    }
    private static function money($value) {
        $scaled=InventoryQuantity::parse($value,true);
        if ($scaled%100) throw new DomainException('Unit costs support up to two decimal places.');
        return intdiv($scaled,100);
    }
    private static function amount($quantity,$cents) {
        if ($quantity>intdiv(PHP_INT_MAX-5000,max(1,$cents))) throw new DomainException('Purchase line amount is too large.');
        $result=intdiv($quantity*$cents+5000,10000);
        if ($result>99999999999) throw new DomainException('Purchase line amount is too large.');
        return $result;
    }
    private static function cash($cents) { return intdiv($cents,100).'.'.str_pad((string)($cents%100),2,'0',STR_PAD_LEFT); }
    private static function qty($value,$unit,$zero=false) { return InventoryQuantity::convert(InventoryQuantity::parse($value,$zero),$unit,$unit); }
    private static function item($id,$branch) {
        $p=RecipeInventory::product($id);
        if ($p['branch_id']!==null && (int)$p['branch_id']!==$branch) throw new DomainException('Product belongs to another branch.');
        if ($p['status']!=='active' || !$p['track_stock'] || in_array($p['inventory_type'],['non-stock','unlimited'],true) || in_array($p['product_type'],['variant','service'],true) || ($p['product_type']==='recipe' && $p['recipe_mode']!=='prepared_batch')) throw new DomainException('Choose a physical stocked product or ingredient.');
        return $p;
    }
    private static function event($order,$supplier,$action,$user,$op,$details) {
        RecipeInventory::query('INSERT INTO purchase_events(order_id,supplier_id,action,actor_id,operation_id,details) VALUES(?,?,?,?,?,?)','iisiis',[$order,$supplier,$action,$user,$op,json_encode($details)]);
        ActivityLog::log('Purchasing '.str_replace('_',' ',$action),'PO #'.($order ?? '—').'; supplier #'.$supplier.'; operation #'.$op);
        RecipeInventory::complete($op,$order ?? $supplier,$details);
    }
    public static function saveSupplier($id,$data,$key) {
        $branch=self::activeBranch($data['branch_id'] ?? ''); $user=self::actor('manage_suppliers',$branch);
        $values=[self::text($data['name'] ?? '',160,true),self::text($data['contact_name'] ?? '',160),self::text($data['email'] ?? '',190),self::text($data['phone'] ?? '',80),self::text($data['address'] ?? '',2000),self::text($data['payment_terms'] ?? '',255),self::text($data['status'] ?? 'active',16)];
        if ($values[2]!=='' && !filter_var($values[2],FILTER_VALIDATE_EMAIL)) throw new DomainException('Enter a valid supplier email.');
        if (!in_array($values[6],['active','inactive'],true)) throw new DomainException('Choose an active or archived supplier status.');
        return RecipeInventory::atomic(function()use($id,$branch,$user,$values,$key){
            $op=RecipeInventory::operation('purchase_supplier',$key,[$id,$branch,$values],$user,$branch);
            if ($op['result_id']!==null) return (int)$op['result_id'];
            if ($id) {
                $rows=RecipeInventory::rows('SELECT * FROM purchase_suppliers WHERE id=? FOR UPDATE','i',[$id]);
                if (!$rows || (int)$rows[0]['branch_id']!==$branch) throw new DomainException('Supplier is not available in this branch.');
                RecipeInventory::query('UPDATE purchase_suppliers SET name=?,contact_name=?,email=?,phone=?,address=?,payment_terms=?,status=? WHERE id=?','sssssssi',array_merge($values,[$id]));
            } else {
                RecipeInventory::query('INSERT INTO purchase_suppliers(branch_id,name,contact_name,email,phone,address,payment_terms,status,created_by) VALUES(?,?,?,?,?,?,?,?,?)','isssssssi',array_merge([$branch],$values,[$user]));
                global $conn; $id=(int)$conn->insert_id;
            }
            self::event(null,$id,'supplier_saved',$user,$op['id'],['supplier_id'=>$id,'values'=>$values]);
            // Supplier operations return the supplier, not the purchase order.
            RecipeInventory::complete($op['id'],$id,['supplier_id'=>$id]);
            return $id;
        });
    }
    public static function create($branch,$supplierId,array $items,$expected,$notes,$key) {
        $branch=self::activeBranch($branch); $user=self::actor('create_purchase_orders',$branch);
        $expected=self::date($expected,true); $notes=self::text($notes,4000);
        if (!$items || count($items)>50) throw new DomainException('Add between 1 and 50 purchase items.');
        return RecipeInventory::atomic(function()use($branch,$supplierId,$items,$expected,$notes,$key,$user){
            $op=RecipeInventory::operation('purchase_create',$key,[$branch,$supplierId,$items,$expected,$notes],$user,$branch);
            if ($op['result_id']!==null) return (int)$op['result_id'];
            $s=RecipeInventory::rows("SELECT * FROM purchase_suppliers WHERE id=? AND branch_id=? AND status='active' FOR UPDATE",'ii',[$supplierId,$branch]);
            if (!$s) throw new DomainException('Choose an active supplier for this branch.');
            $ids=[]; foreach ($items as $row) { if (!is_array($row)) throw new DomainException('Invalid purchase item.'); $ids[]=(int)($row['product_id'] ?? 0); }
            RecipeInventory::lockProducts($ids); $checked=[]; $seen=[];
            foreach ($items as $row) {
                $p=self::item((int)($row['product_id'] ?? 0),$branch);
                if (isset($seen[$p['id']])) throw new DomainException('Add each purchase product once.'); $seen[$p['id']]=true;
                $qty=self::qty($row['quantity'] ?? '',$p['unit_type']); $cost=self::money($row['unit_cost'] ?? '');
                $checked[]=[$p,$qty,$cost,self::amount($qty,$cost)];
            }
            RecipeInventory::query('INSERT INTO purchase_orders(branch_id,supplier_id,supplier_snapshot,expected_date,notes,created_by) VALUES(?,?,?,?,?,?)','iisssi',[$branch,$supplierId,json_encode($s[0]),$expected,$notes,$user]);
            global $conn; $id=(int)$conn->insert_id;
            foreach ($checked as [$p,$qty,$cost,$amount]) RecipeInventory::query('INSERT INTO purchase_order_items(order_id,product_id,product_name,unit,quantity,unit_cost,amount) VALUES(?,?,?,?,?,?,?)','iisssss',[$id,$p['id'],$p['name'],$p['unit_type'],InventoryQuantity::format($qty),self::cash($cost),self::cash($amount)]);
            self::event($id,$supplierId,'created',$user,$op['id'],['notes'=>$notes]); return $id;
        });
    }
    private static function locked($id) {
        $rows=RecipeInventory::rows('SELECT * FROM purchase_orders WHERE id=? FOR UPDATE','i',[$id]);
        if (!$rows) throw new DomainException('Purchase order not found.'); return $rows[0];
    }
    public static function transition($id,$action,$notes,$key) {
        $rules=['submit'=>'create_purchase_orders','approve'=>'approve_purchase_orders','reject'=>'approve_purchase_orders','cancel'=>'create_purchase_orders'];
        if (!isset($rules[$action])) throw new DomainException('Unknown purchase order action.');
        $notes=self::text($notes,4000,in_array($action,['reject','cancel'],true));
        return RecipeInventory::atomic(function()use($id,$action,$notes,$key,$rules){
            $o=self::locked($id); $user=self::actor($rules[$action],(int)$o['branch_id']);
            $op=RecipeInventory::operation('purchase_'.$action,$key,[$id,$notes],$user,(int)$o['branch_id']);
            if ($op['result_id']!==null) return (int)$op['result_id'];
            if ($action==='submit' && $o['status']!=='draft') throw new DomainException('Only drafts can be submitted.');
            if (in_array($action,['approve','reject'],true) && $o['status']!=='submitted') throw new DomainException('Only submitted orders can be approved or rejected.');
            if ($action==='approve' && (int)$o['created_by']===$user) throw new DomainException('Another authorized approver must approve this order.');
            if ($action==='cancel' && !in_array($o['status'],['draft','submitted','approved','partially_received'],true)) throw new DomainException('Only outstanding orders can be cancelled.');
            $status=['submit'=>'submitted','approve'=>'approved','reject'=>'rejected','cancel'=>'cancelled'][$action];
            RecipeInventory::query('UPDATE purchase_orders SET status=? WHERE id=?','si',[$status,$id]);
            if ($action==='approve') RecipeInventory::query('UPDATE purchase_orders SET approved_by=?,approved_at=NOW() WHERE id=?','ii',[$user,$id]);
            self::event($id,$o['supplier_id'],$action,$user,$op['id'],['notes'=>$notes]); return $id;
        });
    }
    public static function receive($id,$reference,$date,array $items,$notes,$key) {
        $reference=self::text($reference,100,true); $date=self::date($date); $notes=self::text($notes,4000);
        if ($date>date('Y-m-d')) throw new DomainException('Delivery date cannot be in the future.');
        if (!$items || count($items)>50) throw new DomainException('Select delivery items.');
        return RecipeInventory::atomic(function()use($id,$reference,$date,$items,$notes,$key){
            $o=self::locked($id); $user=self::actor('receive_purchases',(int)$o['branch_id']);
            $op=RecipeInventory::operation('purchase_receive',$key,[$id,$reference,$date,$items,$notes],$user,(int)$o['branch_id']);
            if ($op['result_id']!==null) return (int)$op['result_id'];
            if (!in_array($o['status'],['approved','partially_received'],true)) throw new DomainException('Only approved outstanding orders can receive deliveries.');
            if (RecipeInventory::rows('SELECT id FROM purchase_receipts WHERE order_id=? AND delivery_reference=?','is',[$id,$reference])) throw new DomainException('This delivery reference was already recorded for this order.');
            $lines=RecipeInventory::rows('SELECT i.*,(SELECT COALESCE(SUM(quantity),0) FROM purchase_receipt_items WHERE order_item_id=i.id) received FROM purchase_order_items i WHERE order_id=? ORDER BY id','i',[$id]);
            $byId=array_column($lines,null,'id'); $productIds=[];
            foreach ($items as $lineId=>$row) {
                if (!isset($byId[$lineId]) || !is_array($row)) throw new DomainException('Delivery item does not belong to this order.');
                $productIds[]=$byId[$lineId]['product_id'];
            }
            RecipeInventory::lockProducts($productIds);
            RecipeInventory::query('INSERT INTO purchase_receipts(order_id,delivery_reference,received_date,received_by,notes,operation_id) VALUES(?,?,?,?,?,?)','issisi',[$id,$reference,$date,$user,$notes,$op['id']]);
            global $conn; $receipt=(int)$conn->insert_id; $any=false;
            foreach ($items as $lineId=>$row) {
                $line=$byId[$lineId]; $qty=self::qty($row['quantity'] ?? '0',$line['unit'],true); $rejected=self::qty($row['rejected_quantity'] ?? '0',$line['unit'],true);
                if (!$qty && !$rejected) continue;
                $p=self::item($line['product_id'],(int)$o['branch_id']);
                if ($p['unit_type']!==$line['unit']) throw new DomainException('Product unit changed since ordering. Cancel the outstanding order and recreate it.');
                if ($qty>InventoryQuantity::parse($line['quantity'])-InventoryQuantity::parse($line['received'],true)) throw new DomainException('Accepted delivery exceeds the outstanding order quantity.');
                $reason=self::text($row['rejection_reason'] ?? '',1000,$rejected>0);
                $cost=self::money($row['unit_cost'] ?? $line['unit_cost']); $amount=self::amount($qty,$cost);
                $expiry=self::date($row['expiry_date'] ?? '',true); $batchName=self::text($row['batch_number'] ?? '',100);
                if ($qty && $expiry && $expiry<date('Y-m-d')) throw new DomainException('Expired goods cannot be accepted. Record them as rejected delivery quantities.');
                $batch=$qty ? RecipeInventory::receive($line['product_id'],$qty,$user,(int)$o['branch_id'],$op['id'],$batchName ?: 'PO-'.$id.'-R'.$receipt.'-'.$lineId,self::cash($cost),$expiry) : null;
                RecipeInventory::query('INSERT INTO purchase_receipt_items(receipt_id,order_item_id,quantity,rejected_quantity,rejection_reason,unit_cost,amount,batch_id,batch_number,expiry_date) VALUES(?,?,?,?,?,?,?,?,?,?)','iisssssiss',[$receipt,$lineId,InventoryQuantity::format($qty),InventoryQuantity::format($rejected),$reason,self::cash($cost),self::cash($amount),$batch,$batchName,$expiry]); $any=true;
            }
            if (!$any) throw new DomainException('Enter an accepted or rejected delivery quantity.');
            $remaining=RecipeInventory::rows('SELECT COUNT(*) n FROM purchase_order_items i WHERE order_id=? AND quantity>(SELECT COALESCE(SUM(quantity),0) FROM purchase_receipt_items WHERE order_item_id=i.id)','i',[$id])[0]['n'];
            $received=RecipeInventory::rows('SELECT COALESCE(SUM(ri.quantity),0) n FROM purchase_receipt_items ri JOIN purchase_receipts r ON r.id=ri.receipt_id WHERE r.order_id=?','i',[$id])[0]['n'];
            $status=!(int)$remaining ? 'received' : ((float)$received>0 ? 'partially_received':'approved');
            RecipeInventory::query('UPDATE purchase_orders SET status=? WHERE id=?','si',[$status,$id]);
            self::event($id,$o['supplier_id'],'received',$user,$op['id'],['receipt_id'=>$receipt,'reference'=>$reference,'notes'=>$notes]); return $id;
        });
    }
    public static function returnGoods($receiptId,$reference,$date,array $items,$reason,$key) {
        $reference=self::text($reference,100,true); $date=self::date($date); $reason=self::text($reason,4000,true);
        if ($date>date('Y-m-d') || !$items || count($items)>50) throw new DomainException('Choose return items and a valid return date.');
        // Resolve the immutable parent before waiting for its transaction lock.
        $rr=RecipeInventory::rows('SELECT * FROM purchase_receipts WHERE id=?','i',[$receiptId]);
        if (!$rr) throw new DomainException('Receipt not found.'); $receipt=$rr[0];
        return RecipeInventory::atomic(function()use($receiptId,$receipt,$reference,$date,$items,$reason,$key){
            $o=self::locked($receipt['order_id']); $user=self::actor('return_purchases',(int)$o['branch_id']);
            $op=RecipeInventory::operation('purchase_return',$key,[$receiptId,$reference,$date,$items,$reason],$user,(int)$o['branch_id']);
            if ($op['result_id']!==null) return (int)$op['result_id'];
            if ($date<$receipt['received_date']) throw new DomainException('Return date cannot precede the delivery date.');
            if (RecipeInventory::rows('SELECT id FROM purchase_supplier_returns WHERE receipt_id=? AND reference=?','is',[$receiptId,$reference])) throw new DomainException('This supplier return reference was already recorded.');
            $lines=RecipeInventory::rows('SELECT ri.*,oi.product_id,oi.unit,(SELECT COALESCE(SUM(quantity),0) FROM purchase_supplier_return_items WHERE receipt_item_id=ri.id) returned FROM purchase_receipt_items ri JOIN purchase_order_items oi ON oi.id=ri.order_item_id WHERE receipt_id=?','i',[$receiptId]);
            $byId=array_column($lines,null,'id'); $ids=[];
            foreach ($items as $lineId=>$quantity) { if (!isset($byId[$lineId])) throw new DomainException('Return line does not belong to this receipt.'); $ids[]=$byId[$lineId]['product_id']; }
            RecipeInventory::lockProducts($ids);
            RecipeInventory::query('INSERT INTO purchase_supplier_returns(receipt_id,reference,reason,returned_date,returned_by,operation_id) VALUES(?,?,?,?,?,?)','isssii',[$receiptId,$reference,$reason,$date,$user,$op['id']]);
            global $conn; $return=(int)$conn->insert_id; $any=false;
            foreach ($items as $lineId=>$quantity) {
                $line=$byId[$lineId]; $qty=self::qty($quantity,$line['unit'],true); if (!$qty) continue;
                $prior=InventoryQuantity::parse($line['returned'],true);
                if ($qty>InventoryQuantity::parse($line['quantity'],true)-$prior || !$line['batch_id']) throw new DomainException('Return exceeds the received quantity remaining to return.');
                $p=RecipeInventory::product($line['product_id']);
                if (($p['branch_id']!==null && (int)$p['branch_id']!==(int)$o['branch_id']) || $p['unit_type']!==$line['unit']) throw new DomainException('Purchased item ownership or unit has changed. Reconcile it before a supplier return.');
                RecipeInventory::consume($line['product_id'],$qty,$user,(int)$o['branch_id'],$op['id'],null,'waste: supplier return #'.$return,(int)$line['batch_id']);
                $cost=self::money($line['unit_cost']); $amount=self::amount($prior+$qty,$cost)-self::amount($prior,$cost);
                RecipeInventory::query('INSERT INTO purchase_supplier_return_items(return_id,receipt_item_id,quantity,amount) VALUES(?,?,?,?)','iiss',[$return,$lineId,InventoryQuantity::format($qty),self::cash($amount)]); $any=true;
            }
            if (!$any) throw new DomainException('Enter a return quantity.');
            self::event($o['id'],$o['supplier_id'],'supplier_return',$user,$op['id'],['return_id'=>$return,'reference'=>$reference,'reason'=>$reason]); return (int)$o['id'];
        });
    }
    public static function settle($id,$status,$notes,$key) {
        if (!in_array($status,['credited','replaced'],true)) throw new DomainException('Choose credited or replaced.');
        $notes=self::text($notes,4000,true);
        $rr=RecipeInventory::rows('SELECT r.order_id FROM purchase_supplier_returns sr JOIN purchase_receipts r ON r.id=sr.receipt_id WHERE sr.id=?','i',[$id]);
        if (!$rr) throw new DomainException('Supplier return not found.'); $orderId=(int)$rr[0]['order_id'];
        return RecipeInventory::atomic(function()use($id,$orderId,$status,$notes,$key){
            $o=self::locked($orderId);
            $r=RecipeInventory::rows('SELECT * FROM purchase_supplier_returns WHERE id=? FOR UPDATE','i',[$id])[0];
            $user=self::actor('return_purchases',(int)$o['branch_id']);
            $op=RecipeInventory::operation('purchase_settle',$key,[$id,$status,$notes],$user,(int)$o['branch_id']);
            if ($op['result_id']!==null) return (int)$op['result_id'];
            if ($r['settlement_status']!=='pending') throw new DomainException('This supplier return is already settled.');
            RecipeInventory::query('UPDATE purchase_supplier_returns SET settlement_status=?,settlement_notes=? WHERE id=?','ssi',[$status,$notes,$id]);
            self::event($o['id'],$o['supplier_id'],'return_settled',$user,$op['id'],['return_id'=>$id,'status'=>$status,'notes'=>$notes]); return (int)$o['id'];
        });
    }
    public static function suppliers($branch=null) {
        self::actor(['view_suppliers','manage_suppliers']); $branch=self::scope($branch);
        return RecipeInventory::rows('SELECT s.*,b.name branch_name FROM purchase_suppliers s JOIN branches b ON b.id=s.branch_id'.($branch?' WHERE s.branch_id=?':'').' ORDER BY s.name,s.id',$branch?'i':'',$branch?[$branch]:[]);
    }
    public static function supplierOptions($branch) {
        self::actor(self::orderPermissions(),$branch); $branch=self::scope($branch);
        if (!$branch) return [];
        return RecipeInventory::rows("SELECT id,name FROM purchase_suppliers WHERE branch_id=? AND status='active' ORDER BY name,id",'i',[$branch]);
    }
    public static function products($branch) {
        self::actor(self::orderPermissions(),$branch); $branch=self::scope($branch); if (!$branch) return [];
        return RecipeInventory::rows("SELECT id,name,unit_type,cost_price FROM products WHERE (branch_id=? OR branch_id IS NULL) AND status='active' AND track_stock=1 AND inventory_type NOT IN ('non-stock','unlimited') AND product_type NOT IN ('variant','service') AND (product_type<>'recipe' OR recipe_mode='prepared_batch') ORDER BY name,id",'i',[$branch]);
    }
    public static function orders($branch=null,$supplier=null) {
        self::actor(self::orderPermissions()); $branch=self::scope($branch); $params=[]; $types='';
        $sql='SELECT o.*,b.name branch_name,(SELECT SUM(amount) FROM purchase_order_items WHERE order_id=o.id) ordered_amount FROM purchase_orders o JOIN branches b ON b.id=o.branch_id WHERE 1=1';
        if ($branch) { $sql.=' AND o.branch_id=?'; $types.='i'; $params[]=$branch; }
        if ($supplier) { $sql.=' AND o.supplier_id=?'; $types.='i'; $params[]=$supplier; }
        $rows=RecipeInventory::rows($sql.' ORDER BY o.id DESC LIMIT 200',$types,$params);
        foreach ($rows as &$row) $row['supplier_snapshot']=self::visibleSupplier($row['supplier_snapshot']); unset($row);
        return $rows;
    }
    private static function visibleSupplier($snapshot) {
        $user=(int)($_SESSION['user']['id'] ?? 0);
        if (User::has_permission($user,'view_suppliers') || User::has_permission($user,'manage_suppliers')) return $snapshot;
        $data=json_decode($snapshot,true);
        return json_encode(['name'=>$data['name'] ?? 'Supplier']);
    }
    public static function detail($id) {
        self::actor(self::orderPermissions());
        $rows=RecipeInventory::rows('SELECT o.*,b.name branch_name FROM purchase_orders o JOIN branches b ON b.id=o.branch_id WHERE o.id=?','i',[$id]);
        if (!$rows) throw new DomainException('Purchase order not found.'); $o=$rows[0]; self::actor(self::orderPermissions(),(int)$o['branch_id']);
        $o['supplier_snapshot']=self::visibleSupplier($o['supplier_snapshot']);
        $o['items']=RecipeInventory::rows('SELECT i.*,(SELECT COALESCE(SUM(quantity),0) FROM purchase_receipt_items WHERE order_item_id=i.id) received FROM purchase_order_items i WHERE order_id=? ORDER BY id','i',[$id]);
        $o['receipts']=RecipeInventory::rows('SELECT r.*,u.name receiver_name FROM purchase_receipts r LEFT JOIN users u ON u.id=r.received_by WHERE order_id=? ORDER BY id DESC','i',[$id]);
        foreach ($o['receipts'] as &$r) $r['items']=RecipeInventory::rows('SELECT ri.*,oi.product_name,oi.unit,(SELECT COALESCE(SUM(quantity),0) FROM purchase_supplier_return_items WHERE receipt_item_id=ri.id) returned FROM purchase_receipt_items ri JOIN purchase_order_items oi ON oi.id=ri.order_item_id WHERE receipt_id=? ORDER BY ri.id','i',[$r['id']]); unset($r);
        $o['returns']=RecipeInventory::rows('SELECT sr.*,(SELECT SUM(amount) FROM purchase_supplier_return_items WHERE return_id=sr.id) amount FROM purchase_supplier_returns sr JOIN purchase_receipts r ON r.id=sr.receipt_id WHERE r.order_id=? ORDER BY sr.id DESC','i',[$id]);
        foreach ($o['returns'] as &$r) $r['items']=RecipeInventory::rows('SELECT sri.*,oi.product_name,oi.unit FROM purchase_supplier_return_items sri JOIN purchase_receipt_items ri ON ri.id=sri.receipt_item_id JOIN purchase_order_items oi ON oi.id=ri.order_item_id WHERE sri.return_id=?','i',[$r['id']]); unset($r);
        $o['events']=RecipeInventory::rows('SELECT e.*,u.name actor_name FROM purchase_events e LEFT JOIN users u ON u.id=e.actor_id WHERE order_id=? ORDER BY e.id','i',[$id]);
        return $o;
    }
    public static function report($branch,$supplier,$from,$to) {
        self::actor('view_purchase_reports'); $branch=self::scope($branch); $from=self::date($from); $to=self::date($to);
        if ($from>$to) throw new DomainException('Report start date must precede the end date.');
        // Aggregate each document type independently; delivery joins must never multiply order costs.
        $sql="SELECT x.branch_id,b.name branch_name,x.supplier_id,s.name supplier_name,SUM(x.ordered) ordered_amount,SUM(x.received) received_amount,SUM(x.returned) returned_amount,SUM(x.received)-SUM(x.returned) net_amount FROM (
            SELECT o.branch_id,o.supplier_id,i.amount ordered,0 received,0 returned FROM purchase_orders o JOIN purchase_order_items i ON i.order_id=o.id WHERE DATE(o.created_at) BETWEEN ? AND ? AND o.status NOT IN ('draft','rejected','cancelled')
            UNION ALL SELECT o.branch_id,o.supplier_id,0,ri.amount,0 FROM purchase_receipts r JOIN purchase_orders o ON o.id=r.order_id JOIN purchase_receipt_items ri ON ri.receipt_id=r.id WHERE r.received_date BETWEEN ? AND ?
            UNION ALL SELECT o.branch_id,o.supplier_id,0,0,sri.amount FROM purchase_supplier_returns sr JOIN purchase_receipts r ON r.id=sr.receipt_id JOIN purchase_orders o ON o.id=r.order_id JOIN purchase_supplier_return_items sri ON sri.return_id=sr.id WHERE sr.returned_date BETWEEN ? AND ?
            ) x JOIN branches b ON b.id=x.branch_id JOIN purchase_suppliers s ON s.id=x.supplier_id WHERE 1=1";
        $types='ssssss'; $params=[$from,$to,$from,$to,$from,$to];
        if ($branch) { $sql.=' AND x.branch_id=?'; $types.='i'; $params[]=$branch; }
        if ($supplier) { $sql.=' AND x.supplier_id=?'; $types.='i'; $params[]=(int)$supplier; }
        return RecipeInventory::rows($sql.' GROUP BY x.branch_id,b.name,x.supplier_id,s.name ORDER BY b.name,s.name',$types,$params);
    }
}
