<?php
require_once __DIR__ . '/auth.php';
require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['image'])) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'No file uploaded']);
    exit;
}

$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['ok' => false, 'error' => 'Upload error: ' . $file['error']]);
    exit;
}

// Validate size
$maxBytes = MAX_UPLOAD_MB * 1024 * 1024;
if ($file['size'] > $maxBytes) {
    echo json_encode(['ok' => false, 'error' => 'File too large (max ' . MAX_UPLOAD_MB . 'MB)']);
    exit;
}

// Validate mime type
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file['tmp_name']);
$allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
if (!isset($allowed[$mime])) {
    echo json_encode(['ok' => false, 'error' => 'Invalid file type. Use JPG, PNG, GIF, or WebP.']);
    exit;
}

if (!is_dir(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}

$ext = $allowed[$mime];
$filename = bin2hex(random_bytes(8)) . '.' . $ext;
$dest = UPLOADS_DIR . $filename;

if (!move_uploaded_file($file['tmp_name'], $dest)) {
    echo json_encode(['ok' => false, 'error' => 'Failed to save file']);
    exit;
}

echo json_encode(['ok' => true, 'url' => UPLOADS_URL . $filename, 'filename' => $filename]);
