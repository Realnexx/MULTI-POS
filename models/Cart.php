<?php
require_once __DIR__.'/../config/db.php';
require_once __DIR__.'/RecipeInventory.php';
class Cart {
    public static function owner_key($session,$user=null) { return hash('sha256',$user?'user:'.(int)$user:'guest:'.$session); }
    public static function lock_owner($session,$user=null) {
        $key=self::owner_key($session,$user);
        RecipeInventory::query('INSERT IGNORE INTO online_cart_owners(owner_key) VALUES (?)','s',[$key]);
        RecipeInventory::rows('SELECT owner_key FROM online_cart_owners WHERE owner_key=? FOR UPDATE','s',[$key]);
    }
    private static function owner($session,$user) { return $user?['user_id=?','i',[(int)$user]]:['session_id=? AND user_id IS NULL','s',[$session]]; }
    public static function fingerprint(array $items,$branch) {
        $lines=[];foreach($items as $item)$lines[(int)$item['id']]=[(int)$item['id'],(int)$item['qty'],number_format((float)$item['price'],2,'.','')];ksort($lines,SORT_NUMERIC);
        return hash('sha256',json_encode([(int)$branch,array_values($lines)]));
    }
    public static function available($product,$branch=null) {
        if(!empty($product['is_ingredient'])||$product['product_type']==='ingredient')return 0;
        return RecipeInventory::catalogAvailability($product,$branch);
    }
    private static function quantity($qty,$zero=false) { if(!ctype_digit((string)$qty)||(int)$qty<($zero?0:1)||(int)$qty>100000)throw new DomainException('Enter a valid whole quantity.');return (int)$qty; }
    public static function add($id,$qty,$session,$user=null) {
        $qty=self::quantity($qty);
        return RecipeInventory::atomic(function()use($id,$qty,$session,$user){
            self::lock_owner($session,$user);[$where,$type,$params]=self::owner($session,$user);
            $row=RecipeInventory::rows('SELECT COALESCE(SUM(qty),0) quantity FROM cart WHERE '.$where.' AND product_id=?',$type.'i',array_merge($params,[$id]))[0];
            self::write_quantity($id,(int)$row['quantity']+$qty,$session,$user);
        });
    }
    private static function write_quantity($id,$qty,$session,$user) {
        $qty=self::quantity($qty,true);$product=RecipeInventory::product($id);
        require_once __DIR__.'/PublicCatalog.php';if($qty&&!PublicCatalog::visible($product))throw new DomainException('This product is not available on the website.');
        $available=self::available($product,$_SESSION['online_branch_id'] ?? null);
        if($qty>$available)throw new DomainException($product['name'].': not enough stock at the selected branch.');
        self::remove($id,$session,$user);
        if($qty)RecipeInventory::query('INSERT INTO cart(session_id,user_id,product_id,qty) VALUES (?,?,?,?)','siii',[$session,$user,$id,$qty]);
    }
    public static function update($id,$qty,$session,$user=null) {
        return RecipeInventory::atomic(function()use($id,$qty,$session,$user){self::lock_owner($session,$user);self::write_quantity($id,$qty,$session,$user);});
    }
    public static function remove($id,$session,$user=null) {
        return RecipeInventory::atomic(function()use($id,$session,$user){self::lock_owner($session,$user);[$where,$type,$params]=self::owner($session,$user);RecipeInventory::query('DELETE FROM cart WHERE '.$where.' AND product_id=?',$type.'i',array_merge($params,[$id]));});
    }
    public static function clear($session,$user=null) {
        return RecipeInventory::atomic(function()use($session,$user){self::lock_owner($session,$user);[$where,$type,$params]=self::owner($session,$user);RecipeInventory::query('DELETE FROM cart WHERE '.$where,$type,$params);});
    }
    public static function get_items($session,$user=null,$branch=null) {
        [$where,$type,$params]=self::owner($session,$user);
        $rows=RecipeInventory::rows('SELECT p.*,c.qty,cat.name category_name FROM (SELECT product_id,SUM(qty) qty FROM cart WHERE '.$where.' GROUP BY product_id) c JOIN products p ON p.id=c.product_id LEFT JOIN categories cat ON cat.id=p.category_id ORDER BY p.id',$type,$params);
        require_once __DIR__.'/PublicCatalog.php';$items=[];foreach($rows as $row){if(!PublicCatalog::visible($row))continue;$row['stock']=self::available($row,$branch ?? ($_SESSION['online_branch_id'] ?? null));$items[$row['id']]=$row;}return $items;
    }
    public static function migrate($session,$user) {
        return RecipeInventory::atomic(function()use($session,$user){
            $owners=[[null,self::owner_key($session,null)],[$user,self::owner_key($session,$user)]];usort($owners,function($a,$b){return strcmp($a[1],$b[1]);});foreach($owners as $owner)self::lock_owner($session,$owner[0]);
            foreach(self::get_items($session,null) as $item) {
                [$where,$type,$params]=self::owner($session,$user);$current=RecipeInventory::rows('SELECT COALESCE(SUM(qty),0) quantity FROM cart WHERE '.$where.' AND product_id=?',$type.'i',array_merge($params,[$item['id']]))[0];
                $qty=min(100000,(int)$item['qty']+(int)$current['quantity']);self::remove($item['id'],$session,$user);if($qty)RecipeInventory::query('INSERT INTO cart(session_id,user_id,product_id,qty) VALUES (?,?,?,?)','siii',[$session,$user,$item['id'],$qty]);
            }
            self::clear($session,null);
        });
    }
}
