<?php

function store_product_image($file)
{
    $maxSize = 5 * 1024 * 1024;
    $mimeExtensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    if (!is_array($file) || !isset($file['error'])) {
        return ['success' => false, 'error' => 'The image upload was not received.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $message = $file['error'] === UPLOAD_ERR_NO_FILE
            ? 'Choose a product image.'
            : 'The image could not be uploaded. Please try again.';
        return ['success' => false, 'error' => $message];
    }

    if (!isset($file['size'], $file['tmp_name']) || $file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'Product images must be 5 MB or smaller.'];
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        return ['success' => false, 'error' => 'The uploaded image could not be verified.'];
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo === false) {
        error_log('Unable to initialize fileinfo for product image validation.');
        return ['success' => false, 'error' => 'Image validation is unavailable. Please contact support.'];
    }

    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!isset($mimeExtensions[$mimeType]) || @getimagesize($file['tmp_name']) === false) {
        return ['success' => false, 'error' => 'Use a valid JPEG, PNG, GIF, or WebP image.'];
    }

    $uploadDirectory = __DIR__ . '/../images/products';
    if (!is_dir($uploadDirectory) || !is_writable($uploadDirectory)) {
        error_log('Product image directory is missing or not writable: ' . $uploadDirectory);
        return ['success' => false, 'error' => 'Product image storage is unavailable. Please contact support.'];
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $mimeExtensions[$mimeType];
    if (!move_uploaded_file($file['tmp_name'], $uploadDirectory . '/' . $filename)) {
        error_log('Failed to move uploaded product image into storage.');
        return ['success' => false, 'error' => 'The product image could not be saved. Please try again.'];
    }

    return ['success' => true, 'filename' => $filename];
}

function remove_product_image($filename)
{
    if (!is_string($filename) || $filename === '' || basename($filename) !== $filename) {
        return;
    }

    $imagePath = __DIR__ . '/../images/products/' . $filename;
    if (is_file($imagePath) && !unlink($imagePath)) {
        error_log('Failed to remove product image: ' . $imagePath);
    }
}
