<?php
$file = 'c:/xampp/htdocs/pos/views/modals/editProductModal.php';
$content = file_get_contents($file);

$new_general_tab = '
                        <!-- General Info Tab -->
                        <div class="tab-pane fade show active" id="edit-general-tab" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="image-upload-wrapper text-center">
                                        <div class="preview-container mb-3 border rounded-4 overflow-hidden bg-light d-flex align-items-center justify-content-center"
                                            style="aspect-ratio: 1/1; cursor: pointer;"
                                            onclick="document.getElementById(\'edit_image_input\').click()">
                                            <img id="edit_img_preview" src="assets/img/default.png"
                                                class="w-100 h-100 object-fit-cover">
                                        </div>
                                        <input type="file" id="edit_image_input" name="image" class="d-none"
                                            accept="image/*">
                                        <p class="text-muted fs-10">Click image to change. Leave blank to keep current.</p>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Product Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" id="edit_prod_name"
                                            class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2" required>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Category <span
                                                    class="text-danger">*</span></label>
                                            <select name="category_id" id="edit_prod_category"
                                                class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2" required>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat[\'id\'] ?>"><?= htmlspecialchars($cat[\'name\']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Status</label>
                                            <select name="status" id="edit_status"
                                                class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">SKU / Product Code</label>
                                            <input type="text" name="sku" id="edit_sku"
                                                class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Barcode</label>
                                            <input type="text" name="barcode" id="edit_barcode"
                                                class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Product Type</label>
                                        <select name="product_type" id="edit_product_type"
                                            class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2">
                                            <option value="simple">Simple Product (Grocery/Retail)</option>
                                            <option value="recipe">Recipe / Made-to-order</option>
                                            <option value="variant">Product with Variants</option>
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
                                        <textarea name="description" id="edit_prod_desc"
                                            class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2"
                                            rows="3"></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>';

$start_tag = '<!-- General Info Tab -->';
$end_tag = '<!-- Pricing & Inventory Tab -->';

$start_pos = strpos($content, $start_tag);
$end_pos = strpos($content, $end_tag);

if ($start_pos !== false && $end_pos !== false) {
    $head = substr($content, 0, $start_pos);
    $tail = substr($content, $end_pos);
    $new_content = $head . $new_general_tab . "\n\n                        " . $tail;
    file_put_contents($file, $new_content);
    echo "editProductModal.php refactored successfully.\n";
} else {
    echo "Could not find tab tags in editProductModal.php\n";
}
