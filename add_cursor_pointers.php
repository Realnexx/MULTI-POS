<?php
$files = [
    'c:/xampp/htdocs/pos/views/modals/createProductModal.php',
    'c:/xampp/htdocs/pos/views/modals/editProductModal.php'
];

foreach ($files as $file) {
    if (!file_exists($file))
        continue;
    $content = file_get_contents($file);

    // Add cursor-pointer to the parent pill and ensure labels/inputs have it
    $find = 'class="d-flex align-items-center gap-2 border bg-white rounded-pill px-3 py-2 shadow-sm"';
    $replace = 'class="d-flex align-items-center gap-2 border bg-white rounded-pill px-3 py-2 shadow-sm cursor-pointer"';

    $content = str_replace($find, $replace, $content);

    // Also check for other toggles like track_stock_sw etc to ensure they have the class
    $content = str_replace('class="form-check-input m-0 shadow-none border-secondary-subtle"', 'class="form-check-input m-0 shadow-none border-secondary-subtle cursor-pointer"', $content);

    file_put_contents($file, $content);
    echo "Cursor pointers added to " . basename($file) . "\n";
}
