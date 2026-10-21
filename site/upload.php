<?php
// Product image uploads, shared by ajax/itemInsert.php and ajax/updateProduct.php.

/**
 * Validate and store an uploaded product image under images/<random>/.
 * Returns [relativePath, null] on success, [null, null] when no file was sent,
 * or [null, errorMessage] when the file is rejected.
 */
function save_product_image(?array $file): array
{
    if (!$file || empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_OK) === UPLOAD_ERR_NO_FILE) {
        return [null, null];
    }
    if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
        return [null, 'The image did not upload. Try again with a smaller file.'];
    }

    $allowedMime = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    $allowedExt  = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
    $ext  =strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (!in_array($mime, $allowedMime, true) || !in_array($ext, $allowedExt, true)) {
        return [null, 'Only JPG, PNG, WebP or GIF images can be uploaded.'];
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        return [null, 'The image must be under 5 MB.'];
    }

    // Never trust the client's file name: keep letters, digits, dot, dash and underscore only.
    $name = preg_replace('/[^A-Za-z0-9._-]/', '_', basename($file['name']));
    $name = ltrim($name, '.') ?: 'image.' . $ext;

    $folder = bin2hex(random_bytes(4));
    $dir = __DIR__ . '/../images/' . $folder;
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return [null, 'The image could not be saved. Check that the images folder is writable.'];
    }
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $name)) {
        return [null, 'The image could not be saved. Check that the images folder is writable.'];
    }
    return ['images/' . $folder . '/' . $name, null];
}

/** Delete a previously uploaded product image, given its path relative to the app root. */
function delete_product_image(?string $relativePath): void
{
    if (!$relativePath || strpos($relativePath, 'images/') !== 0 || strpos($relativePath, '..') !== false) {
        return;
    }
    $full = __DIR__ . '/../' . $relativePath;
    if (is_file($full)) {
        unlink($full);
        @rmdir(dirname($full)); // remove the per-upload folder if it is now empty
    }
}

/** One-shot message shown as a toast on the next page load. */
function flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}
