<?php
$file = 'c:/xampp/htdocs/pos/views/modules/products.php';
$content = file_get_contents($file);

$find = '        });
    </script>';
$replace = '
            // Image Preview for Create Modal
            $(document).on(\'change\', \'#create_image_input\', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(\'#create_img_preview_new\').attr(\'src\', e.target.result).removeClass(\'d-none\');
                        $(\'#upload_placeholder_new\').addClass(\'d-none\');
                    }
                    reader.readAsDataURL(file);
                }
            });

            // Image Preview for Edit Modal
            $(document).on(\'change\', \'#edit_image_input\', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        $(\'#edit_img_preview\').attr(\'src\', e.target.result);
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>';

// Use str_replace if it's unique enough (it should be since it's the end of the file)
$content = str_replace($find, $replace, $content);

file_put_contents($file, $content);
echo "Image preview JS added to products.php\n";
