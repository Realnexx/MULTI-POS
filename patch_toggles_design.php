<?php
$files = [
    'c:/xampp/htdocs/pos/views/modals/createProductModal.php',
    'c:/xampp/htdocs/pos/views/modals/editProductModal.php'
];

foreach ($files as $file) {
    if (!file_exists($file))
        continue;
    $content = file_get_contents($file);

    // Target the specific container for toggles
    $find = 'class="mt-3 d-flex flex-wrap gap-3"';
    $replace = 'class="mt-4 d-flex flex-wrap gap-4 justify-content-center"';

    // We also want to make sure the labels are clean
    $content = str_replace($find, $replace, $content);

    file_put_contents($file, $content);
    echo "Toggles centered and spaced in " . basename($file) . "\n";
}
