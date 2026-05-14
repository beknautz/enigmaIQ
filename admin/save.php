<?php
require_once __DIR__ . '/auth.php';
require_login();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Invalid JSON']);
    exit;
}

// Sanitize recursively — strip tags from all string values
function sanitize_content($val) {
    if (is_array($val)) return array_map('sanitize_content', $val);
    if (is_string($val)) return strip_tags($val, '<b><strong><em><i><br><a>');
    return $val;
}

$data = sanitize_content($data);

$written = file_put_contents(CONTENT_FILE, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($written === false) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Could not write content file. Check file permissions.']);
    exit;
}

echo json_encode(['ok' => true]);
