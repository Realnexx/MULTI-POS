<?php
require_once __DIR__.'/Setting.php';
class CheckoutPolicy {
    public static function migrate($db) {
        $db->query('CREATE TABLE IF NOT EXISTS sale_receipts(sale_id INT PRIMARY KEY,receipt_number VARCHAR(80) NOT NULL UNIQUE,snapshot LONGTEXT NOT NULL) ENGINE=InnoDB');
        $db->query('CREATE TABLE IF NOT EXISTS order_finance(order_id INT PRIMARY KEY,snapshot LONGTEXT NOT NULL) ENGINE=InnoDB');
        $next=(int)$db->query('SELECT COALESCE(MAX(id),0)+1 n FROM sales')->fetch_assoc()['n'];$s=$db->prepare("INSERT IGNORE INTO settings(setting_key,setting_value,category) VALUES ('receipt_next_number',?,'pos')");$s->bind_param('s',$next);$s->execute();
    }
    public static function cents($v) {
        if(!is_scalar($v)||!preg_match('/^[0-9]{1,9}(?:\.[0-9]{1,2})?$/D',(string)$v))throw new DomainException('Enter a nonnegative amount with at most two decimal places.');
        $parts=explode('.',(string)$v);return (int)$parts[0]*100+(int)str_pad($parts[1]??'',2,'0');
    }
    private static function rate($v) {$n=self::cents($v);if($n>10000)throw new DomainException('Rates must be between 0 and 100 percent.');return $n;}
    public static function methods() {
        $raw=Setting::get('manual_payment_methods');
        $all=['Cash','GCash','Card','Maya','Bank Transfer','Credit Card','Debit Card'];
        if($raw===null)return $all;$methods=json_decode($raw,true);
        if(!is_array($methods)||!$methods||array_diff($methods,$all))throw new DomainException('Manual payment configuration is invalid. Ask your administrator.');return $methods;
    }
    public static function posMethods() {
        $methods=['Cash'];
        require_once __DIR__.'/PayMongo.php';
        if(PayMongo::ready())foreach(PayMongo::summary()['methods'] as $method){
            if(!empty($method['available']))$methods[]=$method['label'];
        }
        return array_values(array_unique($methods));
    }
    public static function assertMethod($method,$pos=false) {if(!is_string($method)||!in_array($method,$pos?array_unique(array_merge(self::methods(),self::posMethods())):self::methods(),true))throw new DomainException('This payment method is disabled. Select an available method.');}
    public static function calculate($subtotal,$eligible,$discount) {
        if($subtotal<0||$eligible<0||$eligible>$subtotal)throw new DomainException('Invalid checkout subtotal.');
        $discount=self::cents($discount);$cap=self::rate(Setting::get('discount_max_percent','100'));
        if(Setting::get('discount_enabled','1')!=='1'&&$discount>0)throw new DomainException('Discounts are disabled.');
        if($discount>$eligible||$discount>intdiv($eligible*$cap,10000))throw new DomainException('Discount exceeds the eligible products or configured maximum.');
        $base=$subtotal-$discount;$taxRate=Setting::get('tax_enabled','0')==='1'?self::rate(Setting::get('tax_rate','12')):0;
        $chargeRate=Setting::get('service_charge_enabled','0')==='1'?self::rate(Setting::get('service_charge','0')):0;
        $inclusive=Setting::get('tax_mode','exclusive')==='inclusive';
        $tax=$inclusive?(int)round($base*$taxRate/(10000+$taxRate)):(int)round($base*$taxRate/10000);
        $charge=(int)round($base*$chargeRate/10000);$total=$base+($inclusive?0:$tax)+$charge;
        if($total>9999999999)throw new DomainException('Order exceeds the supported total.');
        return ['subtotal_cents'=>$subtotal,'discount_cents'=>$discount,'tax_cents'=>$tax,'tax_rate'=>$taxRate,'tax_inclusive'=>$inclusive,'service_cents'=>$charge,'service_rate'=>$chargeRate,'total_cents'=>$total];
    }
    public static function cart(array $cart,$discount) {
        require_once __DIR__.'/Product.php';$subtotal=0;$eligible=0;
        foreach($cart as $item){$line=self::cents(number_format((float)($item['subtotal']??($item['price']*($item['quantity']??$item['qty']))),2,'.',''));$subtotal+=$line;$p=Product::find_by_id($item['product_id']??$item['id']);if(!empty($p['is_discountable']))$eligible+=$line;}
        return self::calculate($subtotal,$eligible,$discount);
    }
    public static function save($category,array $data) {
        if($category==='financial'){
            $out=['currency'=>'PHP','tax_rate'=>number_format(self::rate($data['tax_rate']??'0')/100,2,'.',''),'service_charge'=>number_format(self::rate($data['service_charge']??'0')/100,2,'.',''),'discount_max_percent'=>number_format(self::rate($data['discount_max_percent']??'100')/100,2,'.','')];
            foreach(['tax_enabled','service_charge_enabled','discount_enabled'] as $k)$out[$k]=empty($data[$k])?'0':'1';
            if(!in_array($data['tax_mode']??'', ['inclusive','exclusive'],true))throw new DomainException('Choose inclusive or exclusive tax.');$out['tax_mode']=$data['tax_mode'];$out['currency_symbol']='₱';$out['currency_name']='Philippine Peso';
        }else{
            $out=array_intersect_key($data,array_flip(['receipt_header','receipt_footer','receipt_prefix','receipt_start_number','manual_payment_methods','stock_threshold','default_order_type']));foreach(['receipt_header','receipt_footer'] as $k){if(!is_string($data[$k]??'')||mb_strlen($data[$k]??'')>300)throw new DomainException('Receipt text must be at most 300 characters.');}
            if(!preg_match('/^[A-Za-z0-9-]{1,20}$/D',$data['receipt_prefix']??''))throw new DomainException('Receipt prefix must contain 1–20 letters, digits or dashes.');
            if(!ctype_digit((string)($data['receipt_start_number']??''))||(int)$data['receipt_start_number']<1||(int)$data['receipt_start_number']>999999999)throw new DomainException('Choose a receipt starting number from 1–999999999.');
            $methods=$data['manual_payment_methods']??[];$all=['Cash','GCash','Card','Maya','Bank Transfer','Credit Card','Debit Card'];
            if(!is_array($methods)||!$methods||array_diff($methods,$all))throw new DomainException('Enable at least one supported manual payment method.');
            $out['manual_payment_methods']=json_encode(array_values(array_unique($methods)));unset($out['receipt_next_number']);
        }
        $db=$GLOBALS['conn'];$db->begin_transaction();try{Setting::update_all($out,$category);$db->commit();}catch(Throwable $e){$db->rollback();throw $e;}
    }
    /** Caller holds the checkout transaction. The counter update rolls back with the sale. */
    public static function receipt($sale,array $snapshot) {
        $db=$GLOBALS['conn'];$db->query("INSERT IGNORE INTO settings(setting_key,setting_value,category) VALUES ('receipt_next_number','1','pos')");
        $row=$db->query("SELECT setting_value FROM settings WHERE setting_key='receipt_next_number' FOR UPDATE")->fetch_assoc();
        $number=max((int)$row['setting_value'],(int)Setting::get('receipt_start_number','1'));$prefix=Setting::get('receipt_prefix','SHH-');
        if(!preg_match('/^[A-Za-z0-9-]{1,20}$/D',$prefix))$prefix='SHH-';
        $receipt=$prefix.str_pad((string)$number,6,'0',STR_PAD_LEFT);$snapshot['header']=Setting::get('receipt_header','');$snapshot['footer']=Setting::get('receipt_footer','Thank you for your visit!');
        $s=$db->prepare('INSERT INTO sale_receipts(sale_id,receipt_number,snapshot) VALUES (?,?,?)');$json=json_encode($snapshot,JSON_THROW_ON_ERROR);$s->bind_param('iss',$sale,$receipt,$json);$s->execute();Setting::set('receipt_next_number',(string)($number+1),'pos');
    }
    public static function forSale($sale) {
        $s=$GLOBALS['conn']->prepare('SELECT receipt_number,snapshot FROM sale_receipts WHERE sale_id=?');$s->bind_param('i',$sale);$s->execute();$r=$s->get_result()->fetch_assoc();
        return $r?array_merge(json_decode($r['snapshot'],true),['number'=>$r['receipt_number']]):['number'=>'SHH-'.str_pad((string)$sale,6,'0',STR_PAD_LEFT),'header'=>'','footer'=>'Thank you for your visit!','tax_cents'=>0,'service_cents'=>0];
    }
    public static function orderSnapshot($order,array $snapshot) {$json=json_encode($snapshot,JSON_THROW_ON_ERROR);$s=$GLOBALS['conn']->prepare('INSERT INTO order_finance(order_id,snapshot) VALUES (?,?)');$s->bind_param('is',$order,$json);$s->execute();}
    public static function orderReceipt($order,$sale,$total) {
        $s=$GLOBALS['conn']->prepare('SELECT snapshot FROM order_finance WHERE order_id=?');$s->bind_param('i',$order);$s->execute();$r=$s->get_result()->fetch_assoc();
        $snapshot=$r?json_decode($r['snapshot'],true):[];
        // Legacy orders retain their original promised total, even after policy changes.
        if(!$r)$snapshot=['subtotal_cents'=>self::cents($total),'discount_cents'=>0,'tax_cents'=>0,'service_cents'=>0,'total_cents'=>self::cents($total)];
        self::receipt($sale,$snapshot);
        $s=$GLOBALS['conn']->prepare('UPDATE sales SET total_amount=?,discount_amount=? WHERE id=?');$sub=number_format($snapshot['subtotal_cents']/100,2,'.','');$discount=number_format($snapshot['discount_cents']/100,2,'.','');$s->bind_param('ssi',$sub,$discount,$sale);$s->execute();
    }
}
