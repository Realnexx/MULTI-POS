<?php
$file = 'c:/xampp/htdocs/pos/controllers/InventoryController.php';
$content = file_get_contents($file);

// Create block
$create_find = '$product_type = sanitize($_POST[\'product_type\'] ?? \'simple\');';
$create_replace = "\$sku = sanitize(\$_POST['sku'] ?? '');\n                \$barcode = sanitize(\$_POST['barcode'] ?? '');\n                \$status = sanitize(\$_POST['status'] ?? 'active');\n                \$product_type = sanitize(\$_POST['product_type'] ?? 'simple');";

// Edit block
$edit_find = '$product_type = sanitize($_POST[\'product_type\'] ?? \'simple\');';
// We need to distinguish between create and edit blocks as str_replace might hit both.
// Let's use a more unique anchor for edit.
$edit_anchor = '$id = intval($_POST[\'product_id\'] ?? 0);';
$edit_replacement = "\$id = intval(\$_POST['product_id'] ?? 0);\n                \$sku = sanitize(\$_POST['sku'] ?? '');\n                \$barcode = sanitize(\$_POST['barcode'] ?? '');\n                \$status = sanitize(\$_POST['status'] ?? 'active');";

// Apply replacements
$content = str_replace($create_find, $create_replace, $content);
$content = str_replace($edit_anchor, $edit_replacement, $content);

file_put_contents($file, $content);
echo "Patch applied successfully.\n";
