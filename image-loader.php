<?php
// Percorso corretto alla cartella delle immagini protette.
// Si assume che 'uploads_protected' si trovi nella stessa cartella di questo script.
$base_dir = __DIR__ . '/uploads_protected/';

$image_path = $_GET['path'] ?? '';

// Sicurezza: Impedisce di risalire le cartelle (es. ../../) e pulisce il percorso.
// Rimuove caratteri potenzialmente dannosi per la sicurezza.
$image_path = preg_replace('/[^a-zA-Z0-9\/._-]/', '', $image_path);
if (strpos($image_path, '..') !== false) {
    http_response_code(400); // Bad Request
    echo "Accesso non valido.";
    exit;
}

// Costruisce il percorso completo e sicuro del file
$safe_path = realpath($base_dir . $image_path);

// Controlla che il file esista e si trovi DENTRO la cartella base_dir
if (!$safe_path || strpos($safe_path, realpath($base_dir)) !== 0 || !file_exists($safe_path)) {
    http_response_code(404); // Not Found
    // Puoi decommentare la riga sotto per il debug, ma non lasciarla in produzione
    // echo "File non trovato o percorso non valido: " . htmlspecialchars($base_dir . $image_path);
    exit;
}

// Determina il tipo di file in modo sicuro
$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime_type = $finfo->file($safe_path);

$allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
if (in_array($mime_type, $allowed_mime_types)) {
    // Imposta gli header e invia il contenuto del file
    header('Content-Type: ' . $mime_type);
    header('Content-Length: ' . filesize($safe_path));
    header('Cache-Control: max-age=31536000'); // Imposta una cache di 1 anno

    readfile($safe_path);
    exit;
} else {
    http_response_code(403); // Forbidden
    echo "Tipo di file non consentito.";
    exit;
}