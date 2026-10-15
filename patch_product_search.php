<?php
$file = 'c:/xampp/htdocs/pos/models/Product.php';
$content = file_get_contents($file);

// 1. Search condition
$find1 = '(p.name LIKE ? OR p.description LIKE ?)';
$replace1 = '(p.name LIKE ? OR p.description LIKE ? OR p.sku LIKE ? OR p.barcode LIKE ?)';
$content = str_replace($find1, $replace1, $content);

// 2. Bind param count
// We need to find the specific bind_param calls for dt search.
// Since there might be others, let's be careful.
$find2 = '$stmt->bind_param("ssii", $s, $s, $start, $length)';
$replace2 = '$stmt->bind_param("ssssii", $s, $s, $s, $s, $start, $length)';
$content = str_replace($find2, $replace2, $content);

file_put_contents($file, $content);
echo "Patch applied to Product.php search successfully.\n";
