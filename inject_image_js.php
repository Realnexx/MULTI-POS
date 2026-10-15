<?php
$file = 'c:/xampp/htdocs/pos/views/modules/products.php';
$content = file_get_contents($file);

$js = "
            // Image Preview for Modals
            $(document).on('change', '#create_image_input', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#create_img_preview_new').attr('src', e.target.result).removeClass('d-none');
                        $('#upload_placeholder_new').addClass('d-none');
                    }
                    reader.readAsDataURL(file);
                }
            });

            $(document).on('change', '#edit_image_input', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $('#edit_img_preview').attr('src', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
";

$anchor = "$(document).on('click', '.edit-product-btn', function () {";
if (strpos($content, $anchor) !== false) {
    $content = str_replace($anchor, $js . "\n            " . $anchor, $content);
    file_put_contents($file, $content);
    echo "Image preview JS injected into products.php\n";
} else {
    echo "Anchor not found in products.php\n";
}
