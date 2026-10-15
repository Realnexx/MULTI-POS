<?php
$file = 'c:/xampp/htdocs/pos/views/modules/products.php';
$content = file_get_contents($file);

// 1. Data attributes update
$data_find = 'data-unit-type="${row.unit_type || \'pc\'}"';
$data_replace = 'data-unit-type="${row.unit_type || \'pc\'}" 
                                          data-sku="${row.sku || \'\'}" 
                                          data-barcode="${row.barcode || \'\'}" 
                                          data-status="${row.status || \'active\'}"';

$content = str_replace($data_find, $data_replace, $content);

// 2. JS Populating logic update
$js_find = "$('#edit_name').val($(this).data('name'));\n            $('#edit_description').val($(this).data('description'));";
// Since whitespace might vary, let's use a regex-like approach or just find by lines.
// Actually, let's just find edit_name and add below it.

$js_anchor = "$('#edit_name').val($(this).data('name'));";
$js_replacement = "$('#edit_name').val($(this).data('name'));
            $('#edit_sku').val($(this).data('sku'));
            $('#edit_barcode').val($(this).data('barcode'));
            $('#edit_status').val($(this).data('status'));";

$content = str_replace($js_anchor, $js_replacement, $content);

file_put_contents($file, $content);
echo "Patch applied to products.php successfully.\n";
