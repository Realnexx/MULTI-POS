<?php
if(PHP_SAPI!=='cli')exit;
if(!in_array('--apply',$argv,true))exit("Use --apply to add explicit website product visibility.\n");
session_save_path(__DIR__.'/../scratch');require __DIR__.'/../config/db.php';
if(!$conn->query("SHOW COLUMNS FROM products LIKE 'show_on_website'")->num_rows)$conn->query('ALTER TABLE products ADD show_on_website TINYINT(1) NOT NULL DEFAULT 0');
echo "Website visibility installed. Products remain private until Show on website is enabled.\n";
