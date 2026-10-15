<?php
$file = 'c:/xampp/htdocs/pos/views/modules/products.php';
$content = file_get_contents($file);

$find = "                modal.find('#edit_prod_cat').val(data.category);
                modal.find('#edit_prod_desc').val(data.description);";

$replace = "                modal.find('#edit_prod_cat').val(data.category);
                modal.find('#edit_status').val(data.status);
                modal.find('#edit_sku').val(data.sku);
                modal.find('#edit_barcode').val(data.barcode);
                modal.find('#edit_prod_desc').val(data.description);";

if (strpos($content, $find) !== false) {
    $content = str_replace($find, $replace, $content);
    file_put_contents($file, $content);
    echo "products.php populated with SKU/Barcode/Status successfully.\n";
} else {
    echo "Anchor for populating edit modal not found in products.php\n";
}
