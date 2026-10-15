<?php
$file = 'c:/xampp/htdocs/pos/controllers/InventoryController.php';
$content = file_get_contents($file);

$find = '$barcode = sanitize($_POST[\'barcode\'] ?? \'\');';
$replace = '$barcode = sanitize($_POST[\'barcode\'] ?? \'\');

            if (empty($sku)) $sku = Product::generate_unique_sku();
            if (empty($barcode)) $barcode = Product::generate_unique_barcode();';

// Note: I need to make sure I'm in the 'create' action. 
// The first occurrence of this pattern is usually the create action.
// Let's use a more unique anchor if possible.

$create_anchor = "if (\$action === 'create') {";
if (strpos($content, $create_anchor) !== false) {
    // Find the next occurrence of barcode sanitization after create_anchor
    $pos = strpos($content, $find, strpos($content, $create_anchor));
    if ($pos !== false) {
        $content = substr_replace($content, $replace, $pos, strlen($find));
        file_put_contents($file, $content);
        echo "InventoryController.php patched for auto-generation correctly.\n";
    } else {
        echo "Barcode sanitization not found after create anchor.\n";
    }
} else {
    echo "Create action anchor not found.\n";
}
