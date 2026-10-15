<?php
if(PHP_SAPI!=='cli')exit;
function url($path=''){return '/pos/'.ltrim($path,'/');}
function csrf_token(){return 'preview-token';}
function get_flash(){return null;}
function get_product_image($path,$id=0){return $path;}
function get_branch_image($path,$id=0){return 'https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=800&q=80';}
class Setting { public static function get($key,$default=null) {global $store_settings;return $store_settings[$key] ?? $default;} }
$_SESSION=[];
$store_settings=['store_name'=>'Kairo Burger Co.','currency_symbol'=>'₱'];$content=[];$featured=[];$best_sellers=[];
$photos=['https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1550547660-d9450f859349?auto=format&fit=crop&w=600&q=80','https://images.unsplash.com/photo-1586190848861-99aa4a171e90?auto=format&fit=crop&w=600&q=80'];
$burgers=[];foreach(['Classic Cheeseburger','Double Smash Burger','The House Special'] as $i=>$name)$burgers[]=['id'=>$i+1,'name'=>$name,'price'=>129+$i*40,'image_url'=>$photos[$i],'description'=>'A burger worth making plans for. Pick your favorite and dig in.','category_name'=>'Signature burgers','status'=>'active','is_sellable'=>1,'is_featured'=>$i===0];
$burgers[]=['id'=>99,'name'=>'Hidden ingredient','is_ingredient'=>1];
$categories=[['id'=>1,'name'=>'Burgers'],['id'=>2,'name'=>'Sides'],['id'=>3,'name'=>'Drinks'],['id'=>4,'name'=>'Combos']];
$branches=[['name'=>'Downtown','address'=>'123 Main Street','phone'=>'0912 345 6789'],['name'=>'Northside','address'=>'45 Market Avenue','phone'=>'0912 345 6789'],['name'=>'Town Center','address'=>'8 Central Road','phone'=>'0912 345 6789']];
$view=$argv[1] ?? 'home';
if(!in_array($view,['home','products','product_detail','about','branches','contact','cart','cart_empty'],true))exit('Unknown preview');
foreach($burgers as &$item){$item['stock']=20;}unset($item);
foreach($branches as $i=>&$branch){$branch['id']=$i+1;}unset($branch);
$products=$burgers;$product=$burgers[0];$related_products=array_slice($burgers,1,2);$category_id=null;
$cart=$view==='cart_empty' ? [] : [array_merge($product,['qty'=>2])];
if($view==='cart_empty')$view='cart';
$_GET['route']=$view;
require __DIR__.'/../../views/website/'.$view.'.php';
