<?php
$file = 'c:/xampp/htdocs/pos/views/modals/createProductModal.php';

$categories = []; // This will be passed from the controller, but for rewriting we just need the placeholder logic.

// We will read the existing file to keep IDs and Category loop intact.
$content = file_get_contents($file);

// I'll reconstruct the General tab content carefully.
$new_general_tab = '
                        <!-- General Info Tab -->
                        <div class="tab-pane fade show active" id="general-tab" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="image-upload-wrapper text-center">
                                        <div class="preview-container mb-3 border rounded-4 overflow-hidden bg-light d-flex align-items-center justify-content-center border-dashed"
                                            style="aspect-ratio: 1/1; cursor: pointer;"
                                            onclick="document.getElementById(\'create_image_input\').click()">
                                            <img id="create_img_preview_new" src="assets/img/default.png"
                                                class="w-100 h-100 object-fit-cover d-none">
                                            <div id="upload_placeholder_new" class="text-muted">
                                                <span
                                                    class="material-symbols-outlined fs-1 d-block mb-2">add_photo_alternate</span>
                                                <span class="smaller fw-bold">Click to Upload</span>
                                            </div>
                                        </div>
                                        <input type="file" id="create_image_input" name="image" class="d-none"
                                            accept="image/*">
                                        <p class="text-muted fs-10">Recommended: 800x800 PNG/JPG</p>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name"
                                            class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2"
                                            placeholder="e.g. Halo-Halo Special" required>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Category <span
                                                    class="text-danger">*</span></label>
                                            <select name="category_id"
                                                class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2"
                                                required>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat[\'id\'] ?>"><?= htmlspecialchars($cat[\'name\']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Status</label>
                                            <select name="status" class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">SKU / Product Code</label>
                                            <input type="text" name="sku" class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2" placeholder="e.g. PRD-001">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Barcode</label>
                                            <input type="text" name="barcode" class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2" placeholder="Scan or enter barcode">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Product Type</label>
                                        <select name="product_type" id="create_product_type_new"
                                            class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2">
                                            <option value="simple">Simple Product (Grocery/Retail)</option>
                                            <option value="recipe">Recipe / Made-to-order</option>
                                            <option value="variant">Product with Variants (Sizes/Colors)</option>
                                            <option value="service">Service / Labor</option>
                                            <option value="weighted">Weighted (Per KG/Gram)</option>
                                            <option value="ingredient">Raw Ingredient</option>
                                            <option value="combo">Combo / Bundle</option>
                                            <option value="digital">Digital / E-Load</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="mb-0">
                                        <label class="form-label small fw-bold text-dark">Description</label>
                                        <textarea name="description"
                                            class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2" rows="3"
                                            placeholder="Deep details..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>';

// Replace the old general tab with the new one.
// We need to find the start and end of the old general tab.

$start_tag = '<!-- General Info Tab -->';
$end_tag = '<!-- Pricing & Inventory Tab -->';

$start_pos = strpos($content, $start_tag);
$end_pos = strpos($content, $end_tag);

if ($start_pos !== false && $end_pos !== false) {
    $head = substr($content, 0, $start_pos);
    $tail = substr($content, $end_pos);
    $new_content = $head . $new_general_tab . "\n\n                        " . $tail;
    file_put_contents($file, $new_content);
    echo "createProductModal.php refactored successfully.\n";
} else {
    echo "Could not find tab tags in createProductModal.php\n";
}
