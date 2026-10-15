<?php
$files = [
    'c:/xampp/htdocs/pos/views/modals/createProductModal.php',
    'c:/xampp/htdocs/pos/views/modals/editProductModal.php'
];

foreach ($files as $file) {
    if (!file_exists($file))
        continue;
    $content = file_get_contents($file);

    // Improved Toggle Structure
    $old_toggle_start = '<div class="form-check form-switch p-0 h-stack gap-2 border bg-white rounded-pill px-3 py-1">';
    $new_toggle_start = '<div class="d-flex align-items-center gap-2 border bg-white rounded-pill px-3 py-2 shadow-sm" style="min-width: 140px; justify-content: center;">
                                            <div class="form-check form-switch p-0 m-0 d-flex align-items-center">';

    // We need to be careful with the close tag.
    // The previous one had one </div> for the parent.
    // The new one has two: one for the form-check div and one for the parent.

    // Let's use a more surgical replacement for the entire toggle blocks.

    // Pattern for Track Stock
    $pattern1 = '/<div\s+class="form-check form-switch p-0 h-stack gap-2 border bg-white rounded-pill px-3 py-1">\s*<input class="form-check-input m-0 shadow-none border-secondary-subtle"\s+type="checkbox" name="track_stock"([^>]*)id="([^"]*)">\s*<label class="form-check-label fs-9 fw-bold cursor-pointer"\s+for="[^"]*">Track Stock<\/label>\s*<\/div>/i';

    $replacement1 = '<div class="d-flex align-items-center gap-2 border bg-white rounded-pill px-3 py-2 shadow-sm" style="min-width: 140px; justify-content: center;">
                                                <div class="form-check form-switch p-0 m-0 d-flex align-items-center">
                                                    <input class="form-check-input m-0 shadow-none border-secondary-subtle cursor-pointer" type="checkbox" name="track_stock" $1 id="$2">
                                                </div>
                                                <label class="form-check-label fs-9 fw-bold cursor-pointer m-0" for="$2">Track Stock</label>
                                            </div>';

    // Pattern for Sellable
    $pattern2 = '/<div\s+class="form-check form-switch p-0 h-stack gap-2 border bg-white rounded-pill px-3 py-1">\s*<input class="form-check-input m-0 shadow-none border-secondary-subtle"\s+type="checkbox" name="is_sellable"([^>]*)id="([^"]*)">\s*<label class="form-check-label fs-9 fw-bold cursor-pointer"\s+for="[^"]*">Sellable<\/label>\s*<\/div>/i';

    $replacement2 = '<div class="d-flex align-items-center gap-2 border bg-white rounded-pill px-3 py-2 shadow-sm" style="min-width: 140px; justify-content: center;">
                                                <div class="form-check form-switch p-0 m-0 d-flex align-items-center">
                                                    <input class="form-check-input m-0 shadow-none border-secondary-subtle cursor-pointer" type="checkbox" name="is_sellable" $1 id="$2">
                                                </div>
                                                <label class="form-check-label fs-9 fw-bold cursor-pointer m-0" for="$2">Sellable</label>
                                            </div>';

    $content = preg_replace($pattern1, $replacement1, $content);
    $content = preg_replace($pattern2, $replacement2, $content);

    file_put_contents($file, $content);
    echo "Toggles refactored for alignment in " . basename($file) . "\n";
}
