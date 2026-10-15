<?php
$file = 'c:/xampp/htdocs/pos/models/Product.php';
$content = file_get_contents($file);

$find = "        $sql .= \" AND (p.name LIKE '%$s%' OR p.description LIKE '%$s%')\";";
$replace = "        $sql .= \" AND (p.name LIKE '%$s%' OR p.description LIKE '%$s%' OR p.sku LIKE '%$s%' OR p.barcode LIKE '%$s%')\";";

$content = str_replace($find, $replace, $content);

file_put_contents($file, $content);
echo "Patch applied to count_dt_products_filtered successfully.\n";
