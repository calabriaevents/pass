<?php
// Percorso canonico e corretto per le immagini protette.
$base_dir = __DIR__ . '/uploads_protected/';

$image_path = $_GET['path'] ?? '';

// Sicurezza: pulizia aggressiva per prevenire attacchi "Directory Traversal".
$image_path = preg_replace('/[^a-zA-Z0-9\/._-]/', '', $image_path);
if (strpos($image_path, '..') !== false) {
    http_response_code(400); // Bad Request
    exit("Percorso non valido.");
}

$full_path = $base_dir . $image_path;

// Usa realpath() per risolvere simboli come '.' e '..' e ottenere il percorso canonico
$safe_path = realpath($full_path);
$safe_base_dir = realpath($base_dir);

// Controlla che il file esista e che il suo percorso inizi con il percorso della cartella base.
// Questa è la protezione di sicurezza più importante.
if ($safe_path === false || strpos($safe_path, $safe_base_dir) !== 0) {
    http_response_code(404); // Not Found
    exit;
}

// Determina il tipo di file in modo sicuro e servi l'immagine
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($safe_path);

$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (in_array($mime_type, $allowed_mime_types)) {
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($safe_path));
    header('Cache-Control: public, max-age=31536000'); // Cache per 1 anno
    header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');
    readfile($safe_path);
    exit;
} else {
    http_response_code(403); // Forbidden
    exit("Tipo di file non consentito.");
}