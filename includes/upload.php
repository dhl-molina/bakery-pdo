<?php
// includes/upload.php - one image per order: validation + saving + cleanup.
// The database stores only the path, e.g. "uploads/orders/3f9c...e1.jpg".

const IMAGE_MAX_BYTES = 2 * 1024 * 1024;   // 2 MB
const IMAGE_TYPES = [                       // allowed real image types => file extension
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
    'image/gif'  => 'gif',
    'image/webp' => 'webp',
];

// A file bigger than PHP's post_max_size makes PHP throw away the whole form ($_POST is empty).
function post_too_large(): bool
{
    return $_SERVER['REQUEST_METHOD'] === 'POST'
        && empty($_POST)
        && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0;
}

// Checks the uploaded file and moves it into uploads/orders/ under a random name.
// Returns [path, null] on success, [null, null] when no file was chosen, [null, 'message'] on a problem.
function save_order_image(?array $file): array
{
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return [null, null];                                   // the image is optional
    }
    if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        return [null, 'That image is too large. The limit is 2 MB.'];
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return [null, 'The image could not be uploaded. Please try again.'];
    }
    if ($file['size'] > IMAGE_MAX_BYTES) {
        return [null, 'That image is too large. The limit is 2 MB.'];
    }

    // getimagesize() only succeeds for a real image, so a renamed .txt or .php file is rejected
    $info = @getimagesize($file['tmp_name']);
    $mime = $info['mime'] ?? '';
    if (!isset(IMAGE_TYPES[$mime])) {
        return [null, 'Please choose a JPG, PNG, GIF or WebP image.'];
    }

    $dir = __DIR__ . '/../uploads/orders';
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) {
        return [null, 'The uploads folder could not be created.'];
    }

    // Never use the visitor's file name: make our own (random name + the extension that matches the real type)
    $name = bin2hex(random_bytes(16)) . '.' . IMAGE_TYPES[$mime];
    if (!move_uploaded_file($file['tmp_name'], "$dir/$name")) {
        return [null, 'The image could not be saved on the server.'];
    }
    return ['uploads/orders/' . $name, null];
}

// Removes an image file from disk (only paths that look like ones we created).
function delete_order_image(?string $path): void
{
    if ($path && preg_match('#^uploads/orders/[a-f0-9]{32}\.(jpg|png|gif|webp)$#', $path)) {
        $full = __DIR__ . '/../' . $path;
        if (is_file($full)) {
            unlink($full);
        }
    }
}
