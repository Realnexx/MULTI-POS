<?php
require_once 'config/db.php';
require_once 'models/Product.php';

// Dummy data
$category_id = 1;
$name = "Test Product " . time();
$sku = "TEST-" . time();
$barcode = "BAR-" . time();
$description = "Test description";
$price = 10.00;
$cost_price = 5.00;
$stock = 100;
$low_stock_threshold = 10;
$image_url = "assets/img/default.png";
$status = "active";
$is_featured = 0;
$is_best_seller = 0;
$max_stock_threshold = 200;
$branch_id = 1;
$product_type = 'simple';
$track_stock = 1;
$is_sellable = 1;
$is_ingredient = 0;
$inventory_type = 'stocked';
$json_attributes = null;
$unit_type = 'pc';
$is_discountable = 1;

try {
    $res = Product::create($category_id, $name, $sku, $barcode, $description, $price, $cost_price, $stock, $low_stock_threshold, $image_url, $status, $is_featured, $is_best_seller, $max_stock_threshold, $branch_id, $product_type, $track_stock, $is_sellable, $is_ingredient, $inventory_type, $json_attributes, $unit_type, $is_discountable);
    if ($res) {
        echo "CREATE SUCCESS: ID " . $res;
    } else {
        echo "CREATE FAILED. Check logs.";
        if (isset($_SESSION['sql_error'])) {
            echo " SQL ERROR: " . $_SESSION['sql_error'];
        }
    }
} catch (Exception $e) {
    echo "EXCEPTION: " . $e->getMessage();
}
?>