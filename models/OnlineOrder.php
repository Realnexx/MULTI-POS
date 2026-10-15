<?php
require_once __DIR__.'/Cart.php';
require_once __DIR__.'/User.php';
require_once __DIR__.'/Setting.php';
class OnlineOrder {
    public static function migrate($db) {
        $db->query("CREATE TABLE IF NOT EXISTS online_cart_owners(owner_key CHAR(64) PRIMARY KEY) ENGINE=InnoDB");
        $db->query("CREATE TABLE IF NOT EXISTS online_orders(id INT AUTO_INCREMENT PRIMARY KEY,tracking_number VARCHAR(40) NOT NULL UNIQUE,checkout_key CHAR(64) NOT NULL UNIQUE,request_hash CHAR(64) NOT NULL,user_id INT NOT NULL,branch_id INT NOT NULL,customer_name VARCHAR(150) NOT NULL,email VARCHAR(254) NOT NULL,phone VARCHAR(40) NOT NULL,status VARCHAR(24) NOT NULL DEFAULT 'pending',payment_status VARCHAR(24) NOT NULL DEFAULT 'unpaid',total DECIMAL(10,2) NOT NULL,operation_id BIGINT UNSIGNED NULL,sale_id INT NULL UNIQUE,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,INDEX(user_id,created_at),INDEX(branch_id,status)) ENGINE=InnoDB");
        $db->query("CREATE TABLE IF NOT EXISTS online_order_items(id INT AUTO_INCREMENT PRIMARY KEY,order_id INT NOT NULL,product_id INT NOT NULL,product_name VARCHAR(150) NOT NULL,quantity INT NOT NULL,unit_price DECIMAL(10,2) NOT NULL,line_total DECIMAL(10,2) NOT NULL,recipe_mode VARCHAR(24) NULL,needs_snapshot LONGTEXT NOT NULL,INDEX(order_id)) ENGINE=InnoDB");
        $db->query("CREATE TABLE IF NOT EXISTS online_order_events(id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,order_id INT NOT NULL,status VARCHAR(24) NOT NULL,user_id INT NOT NULL,note VARCHAR(255) NOT NULL,created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,INDEX(order_id,id)) ENGINE=InnoDB");
    }
    public static function enabled() { return Setting::get('online_ordering_enabled','1') === '1'; }
    private static function event($order,$status,$user,$note) { RecipeInventory::query('INSERT INTO online_order_events(order_id,status,user_id,note) VALUES (?,?,?,?)','isis',[$order,$status,$user,$note]); }
    public static function place($user,$session,$branch,$name,$email,$phone,$key,$expectedTotal=null,$expectedQuote=null) {
        if(!$user||!User::find_by_id($user)||!preg_match('/^[a-f0-9]{64}$/D',$key))throw new DomainException('Please sign in and reload your cart.');
        if(!is_string($name)||trim($name)===''||mb_strlen($name)>150||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($email)>254||!preg_match('/^[+0-9 ()-]{7,40}$/D',$phone))throw new DomainException('Enter your name, a valid email, and a contact number.');
        $fingerprint=hash('sha256',json_encode([(int)$user,(int)$branch,trim($name),strtolower($email),$phone]));
        return RecipeInventory::atomic(function()use($user,$session,$branch,$name,$email,$phone,$key,$fingerprint,$expectedTotal,$expectedQuote){
            global $conn;Cart::lock_owner($session,$user);
            $old=RecipeInventory::rows('SELECT * FROM online_orders WHERE checkout_key=? FOR UPDATE','s',[$key]);
            if($old){if((int)$old[0]['user_id']!==$user||!hash_equals($old[0]['request_hash'],$fingerprint))throw new DomainException('This checkout was already submitted with different details. Reload your cart.');return $old[0];}
            if(!self::enabled())throw new DomainException('Online ordering is currently closed. You can still track existing orders.');
            if(!RecipeInventory::rows("SELECT id FROM branches WHERE id=? AND status='Active'",'i',[$branch]))throw new DomainException('Select an active pickup branch.');
            $cart=Cart::get_items($session,$user,$branch);if(!$cart)throw new DomainException('Your cart is empty.');
            if($expectedQuote!==null&&!hash_equals(Cart::fingerprint($cart,$branch),$expectedQuote))throw new DomainException('Your cart changed. Review it before placing your order.');
            $ids=[];foreach($cart as $item){$quote=RecipeInventory::quoteItem($item['id'],(string)$item['qty'],[],$branch);$ids[]=$item['id'];$ids=array_merge($ids,array_keys($quote['needs']));}RecipeInventory::lockProducts($ids);
            $quotes=[];$needs=[];$cents=0;
            foreach($cart as $item){$quote=RecipeInventory::quoteItem($item['id'],(string)$item['qty'],[],$branch);if(!empty($quote['product']['is_ingredient']))throw new DomainException('Ingredients are not available to order.');foreach($quote['needs'] as $id=>$qty){if(!in_array((int)$id,array_map('intval',$ids),true))throw new DomainException('The recipe changed. Reload your cart.');$needs[$id]=($needs[$id] ?? 0)+$qty;}$cents+=$quote['price_cents']*$quote['quantity'];$quotes[]=$quote;}
            if($expectedTotal!==null && (!preg_match('/^[0-9]{1,8}\.[0-9]{2}$/D',$expectedTotal) || intdiv(InventoryQuantity::parse($expectedTotal,true),100)!==$cents))throw new DomainException('Prices changed. Review your cart total and try again.');
            foreach($needs as $id=>$qty)if(RecipeInventory::balance($id,$branch)<$qty)throw new DomainException(RecipeInventory::product($id)['name'].': insufficient stock for this order.');
            $tracking='WEB-'.date('ymd').'-'.strtoupper(bin2hex(random_bytes(8)));$total=number_format($cents/100,2,'.','');
            RecipeInventory::query('INSERT INTO online_orders(tracking_number,checkout_key,request_hash,user_id,branch_id,customer_name,email,phone,total) VALUES (?,?,?,?,?,?,?,?,?)','sssiissss',[$tracking,$key,$fingerprint,$user,$branch,trim($name),strtolower($email),$phone,$total]);$order=$conn->insert_id;
            $op=RecipeInventory::operation('online_order',$key,[$order,$quotes],$user,$branch);
            foreach($quotes as $quote){$p=$quote['product'];RecipeInventory::query('INSERT INTO online_order_items(order_id,product_id,product_name,quantity,unit_price,line_total,recipe_mode,needs_snapshot) VALUES (?,?,?,?,?,?,?,?)','iisissss',[$order,$p['id'],$p['name'],$quote['quantity'],number_format($quote['price_cents']/100,2,'.',''),number_format($quote['price_cents']*$quote['quantity']/100,2,'.',''),$p['recipe_mode'],json_encode($quote['needs'])]);$line=$conn->insert_id;foreach($quote['needs'] as $id=>$qty)RecipeInventory::consume($id,$qty,$user,$branch,$op['id'],null,'online:'.$tracking.':'.$line);}
            RecipeInventory::complete($op['id'],$order,['tracking'=>$tracking,'lines'=>$quotes]);RecipeInventory::query('UPDATE online_orders SET operation_id=? WHERE id=?','ii',[$op['id'],$order]);self::event($order,'pending',$user,'Order placed; stock reserved once.');Cart::clear($session,$user);
            return RecipeInventory::rows('SELECT * FROM online_orders WHERE id=?','i',[$order])[0];
        });
    }
    public static function detail($tracking,$user,$staff=false) {
        $sql='SELECT o.*,b.name branch_name,b.address branch_address FROM online_orders o LEFT JOIN branches b ON b.id=o.branch_id WHERE o.tracking_number=?';$types='s';$params=[$tracking];
        if(!$staff){$sql.=' AND o.user_id=?';$types.='i';$params[]=$user;}elseif(!User::has_all_branch_access($user)){$sql.=' AND o.branch_id=?';$types.='i';$params[]=User::access_context($user)['branch_id'] ?? 0;}
        $rows=RecipeInventory::rows($sql,$types,$params);if(!$rows)return null;$order=$rows[0];$order['items']=RecipeInventory::rows('SELECT * FROM online_order_items WHERE order_id=? ORDER BY id','i',[$order['id']]);$order['events']=RecipeInventory::rows('SELECT status,note,created_at FROM online_order_events WHERE order_id=? ORDER BY id','i',[$order['id']]);return $order;
    }
    public static function list_for($user,$staff=false) {
        $where=$staff?'1=1':'o.user_id='.(int)$user;if($staff&&!User::has_all_branch_access($user))$where='o.branch_id='.(int)(User::access_context($user)['branch_id'] ?? 0);
        $order=$staff?"CASE WHEN o.status IN ('pending','preparing','ready') THEN 0 ELSE 1 END,o.id DESC":'o.id DESC';
        return RecipeInventory::rows('SELECT o.*,b.name branch_name FROM online_orders o LEFT JOIN branches b ON b.id=o.branch_id WHERE '.$where.' ORDER BY '.$order.' LIMIT 200');
    }
    public static function update_status($id,$status,$actor,$payment='Cash') {
        if(!User::has_permission($actor,'manage_online_orders'))throw new DomainException('You cannot manage online orders.');
        return RecipeInventory::atomic(function()use($id,$status,$actor,$payment){
            global $conn;$rows=RecipeInventory::rows('SELECT * FROM online_orders WHERE id=? FOR UPDATE','i',[$id]);if(!$rows)throw new DomainException('Order not found.');$order=$rows[0];RecipeInventory::branch($order['branch_id']);
            if($status==='paid') {
                if(!User::has_permission($actor,'access_pos'))throw new DomainException('You cannot record payments.');
                if($order['payment_status']==='paid')return $order;
                if(!in_array($order['status'],['pending','preparing','ready'],true))throw new DomainException('This order cannot accept payment.');
                self::record_payment($order,$actor,$payment);
                self::event($id,'paid',$actor,'Payment received at counter; sale recorded.');
                return RecipeInventory::rows('SELECT * FROM online_orders WHERE id=?','i',[$id])[0];
            }
            if($order['status']===$status)return $order;
            $next=['pending'=>['preparing','cancelled'],'preparing'=>['ready'],'ready'=>['completed']];if(!in_array($status,$next[$order['status']] ?? [],true))throw new DomainException('That order status change is not allowed.');
            if($status==='cancelled') {
                if($order['payment_status']!=='unpaid')throw new DomainException('Paid orders must be refunded through Order History.');
                if(!User::has_permission($actor,'refund_sales')&&!User::has_permission($actor,'manage_inventory'))throw new DomainException('Ask a manager to cancel this order.');
                $moves=RecipeInventory::rows('SELECT * FROM inventory_movements WHERE operation_id=? AND quantity<0 ORDER BY product_id,batch_id,id','i',[$order['operation_id']]);RecipeInventory::lockProducts(array_column($moves,'product_id'));
                $op=RecipeInventory::operation('online_cancel',hash('sha256','cancel:'.$order['checkout_key']),[$id],$actor,$order['branch_id']);
                foreach($moves as $move){$qty=InventoryQuantity::parse(ltrim($move['quantity'],'-'));$batch=RecipeInventory::rows('SELECT * FROM product_batches WHERE id=? FOR UPDATE','i',[$move['batch_id']]);if(!$batch)throw new DomainException('An original stock batch is missing. Contact your administrator.');$batchStatus=($batch[0]['expiry_date']&&$batch[0]['expiry_date']<date('Y-m-d'))||$batch[0]['status']==='expired'?'expired':($batch[0]['status']==='depleted'?'active':$batch[0]['status']);RecipeInventory::query('UPDATE product_batches SET quantity=quantity+?,status=? WHERE id=?','ssi',[InventoryQuantity::format($qty),$batchStatus,$move['batch_id']]);RecipeInventory::query('INSERT INTO inventory_movements(operation_id,product_id,batch_id,branch_id,quantity,unit,product_name,unit_cost,component) VALUES (?,?,?,?,?,?,?,?,?)','iiiisssss',[$op['id'],$move['product_id'],$move['batch_id'],$order['branch_id'],InventoryQuantity::format($qty),$move['unit'],$move['product_name'],$move['unit_cost'],'cancel:'.$order['tracking_number']]);RecipeInventory::query('INSERT INTO inventory_logs(product_id,user_id,branch_id,batch_id,action,quantity,remarks) VALUES (?,?,?,?,?,?,?)','iiiisss',[$move['product_id'],$actor,$order['branch_id'],$move['batch_id'],'stock_in',InventoryQuantity::format($qty),'Cancelled '.$order['tracking_number']]);RecipeInventory::syncTotal($move['product_id']);}RecipeInventory::complete($op['id'],$id,['tracking'=>$order['tracking_number']]);
            }
            if($status==='completed' && $order['payment_status']!=='paid')throw new DomainException('Record payment before completing pickup.');
            RecipeInventory::query('UPDATE online_orders SET status=? WHERE id=?','si',[$status,$id]);self::event($id,$status,$actor,$status==='completed'?'Order collected.':'Order '.str_replace('_',' ',$status).'.');return RecipeInventory::rows('SELECT * FROM online_orders WHERE id=?','i',[$id])[0];
        });
    }
    private static function record_payment($order,$actor,$payment) {
        global $conn;$id=(int)$order['id'];
                if(!in_array($payment,['Cash','GCash','Maya','Card','Bank Transfer'],true))throw new DomainException('Select a supported payment method.');
                $key='online-'.$order['checkout_key'];RecipeInventory::query("INSERT INTO sales(cashier_id,branch_id,total_amount,discount_amount,final_amount,amount_tendered,change_amount,payment_method,order_type,status,request_key) VALUES (?,?,?,0,?,?,0,?,'Online','Completed',?)",'iisssss',[$actor,$order['branch_id'],$order['total'],$order['total'],$order['total'],$payment,$key]);$sale=$conn->insert_id;
                foreach(RecipeInventory::rows('SELECT * FROM online_order_items WHERE order_id=? ORDER BY id','i',[$id]) as $item){RecipeInventory::query('INSERT INTO sales_items(sale_id,product_id,quantity,price,subtotal) VALUES (?,?,?,?,?)','iiiss',[$sale,$item['product_id'],$item['quantity'],$item['unit_price'],$item['line_total']]);$saleItem=$conn->insert_id;RecipeInventory::query('UPDATE inventory_movements SET sale_item_id=? WHERE operation_id=? AND component=?','iis',[$saleItem,$order['operation_id'],'online:'.$order['tracking_number'].':'.$item['id']]);}
                RecipeInventory::query("UPDATE online_orders SET sale_id=?,payment_status='paid' WHERE id=?",'ii',[$sale,$id]);
    }
    public static function sync_refund($sale,$actor) { RecipeInventory::query("UPDATE online_orders SET status='refunded',payment_status='refunded' WHERE sale_id=? AND payment_status<>'refunded'",'i',[$sale]);RecipeInventory::query("INSERT INTO online_order_events(order_id,status,user_id,note) SELECT id,'refunded',?,'Payment refunded.' FROM online_orders WHERE sale_id=? AND NOT EXISTS (SELECT 1 FROM online_order_events e WHERE e.order_id=online_orders.id AND e.status='refunded')",'ii',[$actor,$sale]); }
}
