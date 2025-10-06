<?php
$base_dir = __DIR__ . '/../uploads_protected/';

$image_path = $_GET['path'] ?? '';

// Sicurezza: Impedisce di risalire le cartelle
$safe_path = realpath($base_dir . $image_path);

if (!$safe_path || strpos($safe_path, $base_dir) !== 0 || !file_exists($safe_path)) {
    http_response_code(404);
    exit;
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($safe_path);

$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (in_array($mime_type, $allowed_mime_types)) {
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($safe_path));

    readfile($safe_path);
    exit;
} else {
    http_response_code(403);
    exit;
}