<?php
$file = 'c:/xampp/htdocs/pos/views/modals/editProductModal.php';
$content = file_get_contents($file);

// Correct the anchor and add fields
$find = '<select name="product_type" id="edit_product_type"';
$replace = '
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Status</label>
                                            <select name="status" id="edit_status" class="form-select rounded-3 border-0 bg-light-gray shadow-none py-2">
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">SKU / Product Code</label>
                                            <input type="text" name="sku" id="edit_sku" class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2" placeholder="e.g. PRD-001">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark">Barcode</label>
                                            <input type="text" name="barcode" id="edit_barcode" class="form-control rounded-3 border-0 bg-light-gray shadow-none py-2" placeholder="Scan or enter barcode">
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark">Product Type</label>
                                        <select name="product_type" id="edit_product_type"';

if (strpos($content, $find) !== false) {
    $content = str_replace($find, $replace, $content);
    file_put_contents($file, $content);
    echo "Corrected patch applied to editProductModal.php successfully.\n";
} else {
    echo "Anchor not found in editProductModal.php\n";
}
