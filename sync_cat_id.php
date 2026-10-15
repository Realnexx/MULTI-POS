<?php
$file = 'c:/xampp/htdocs/pos/views/modals/editProductModal.php';
$content = file_get_contents($file);

$find = 'id="edit_prod_category"';
$replace = 'id="edit_prod_cat"';

if (strpos($content, $find) !== false) {
    $content = str_replace($find, $replace, $content);
    file_put_contents($file, $content);
    echo "ID edit_prod_cat synchronized successfully.\n";
} else {
    // Check if it already has the correct ID
    if (strpos($content, 'id="edit_prod_cat"') !== false) {
        echo "ID already synchronized.\n";
    } else {
        echo "Anchor for ID synchronization not found.\n";
    }
}
