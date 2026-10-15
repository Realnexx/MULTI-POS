<?php
if(PHP_SAPI!=='cli')exit;
session_save_path(__DIR__.'/../../scratch');require __DIR__.'/../../config/db.php';
$state=json_decode(file_get_contents(__DIR__.'/../../scratch/rbac-test-state.json'),true);
if(!preg_match('/^pos_rbac_test_[a-f0-9]+$/',$state['database']))throw new Exception('Use an isolated database');
$conn->select_db($state['database']);
$conn->query("INSERT INTO categories(id,name) VALUES(701,'Test Burgers')");
$conn->query("INSERT INTO products(id,name,category_id,price,cost_price,stock,status,product_type,is_sellable,description,image_url) VALUES(801,'Test Cheeseburger',701,129,50,20,'active','simple',1,'A burger for isolated design tests.','https://images.unsplash.com/photo-1568901346375-23c9450c58cd?auto=format&fit=crop&w=600&q=80')");
echo "Public shop fixtures created in isolated database.\n";
