<?php
$file = 'c:/xampp/htdocs/pos/views/modals/createProductModal.php';
$content = file_get_contents($file);

$find1 = 'placeholder="e.g. PRD-001">';
$replace1 = 'placeholder="Auto-generated" readonly>';

$find2 = 'placeholder="Scan or enter barcode">';
$replace2 = 'placeholder="Auto-generated" readonly>';

$content = str_replace($find1, $replace1, $content);
$content = str_replace($find2, $replace2, $content);

file_put_contents($file, $content);
echo "createProductModal.php UI updated to readonly for SKU/Barcode.\n";
