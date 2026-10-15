<?php
// All existing upload handlers accept images. Validate before they use client filenames.
function validate_uploaded_images(array $files)
{
    foreach ($files as $file) {
        if (is_array($file['error'] ?? null)) {
            http_response_code(400);
            exit('Nested uploads are not supported.');
        }
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $extension = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $types = ['jpg' => IMAGETYPE_JPEG, 'jpeg' => IMAGETYPE_JPEG, 'png' => IMAGETYPE_PNG,
            'gif' => IMAGETYPE_GIF, 'webp' => IMAGETYPE_WEBP];
        $image = ($file['error'] ?? -1) === UPLOAD_ERR_OK
            ? @getimagesize($file['tmp_name'] ?? '') : false;
        if (!$image || !isset($types[$extension]) || $image[2] !== $types[$extension]
            || !is_uploaded_file($file['tmp_name']) || ($file['size'] ?? PHP_INT_MAX) > 5 * 1024 * 1024
            || !preg_match('/^[a-zA-Z0-9_() .-]+\.(jpg|jpeg|png|gif|webp)$/iD', basename($file['name'] ?? ''))) {
            http_response_code(400);
            exit('Upload a JPG, PNG, GIF or WebP image up to 5 MB. Use letters, numbers, spaces, parentheses, dots, hyphens or underscores in the filename.');
        }
    }
}
