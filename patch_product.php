<?php
$file = 'c:\xampp\htdocs\pos\models\Product.php';
$content = file_get_contents($file);

// Exact 23-character string for create
$correct_create = 'issssddiiissiisiisiiisss';
// Exact 24-character string for update
$correct_update = 'issssddiiissiisiisiiisssi';

// Match lines and replace
$content = preg_replace('/bind_param\("[^"]+", \$category_id, \$name, \$sku, \$barcode, \$description, \$price, \$cost_price, \$stock, \$low_stock_threshold, \$max_stock_threshold, \$image_url, \$status, \$is_featured, \$is_best_seller, \$branch_id, \$product_type, \$track_stock, \$is_sellable, \$is_ingredient, \$is_discountable, \$inventory_type, \$json_attributes, \$unit_type\)/', 'bind_param("' . $correct_create . '", $category_id, $name, $sku, $barcode, $description, $price, $cost_price, $stock, $low_stock_threshold, $max_stock_threshold, $image_url, $status, $is_featured, $is_best_seller, $branch_id, $product_type, $track_stock, $is_sellable, $is_ingredient, $is_discountable, $inventory_type, $json_attributes, $unit_type)', $content);

$content = preg_replace('/bind_param\("[^"]+", \$category_id, \$name, \$sku, \$barcode, \$description, \$price, \$cost_price, \$stock, \$low_stock_threshold, \$max_stock_threshold, \$image_url, \$status, \$is_featured, \$is_best_seller, \$branch_id, \$product_type, \$track_stock, \$is_sellable, \$is_ingredient, \$is_discountable, \$inventory_type, \$json_attributes, \$unit_type, \$id\)/', 'bind_param("' . $correct_update . '", $category_id, $name, $sku, $barcode, $description, $price, $cost_price, $stock, $low_stock_threshold, $max_stock_threshold, $image_url, $status, $is_featured, $is_best_seller, $branch_id, $product_type, $track_stock, $is_sellable, $is_ingredient, $is_discountable, $inventory_type, $json_attributes, $unit_type, $id)', $content);

file_put_contents($file, $content);
echo "PATCH APPLIED";
?>