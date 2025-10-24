<?php
require_once __DIR__ . '/includes/config.php';

// Ottieni il nome del file dalla query string e sanitizzalo
$file = $_GET['file'] ?? '';

// Rimuovi caratteri potenzialmente dannosi per prevenire attacchi di directory traversal
$file = basename($file);

// Costruisci il percorso completo e sicuro del file immagine
$filePath = SECURE_UPLOAD_PATH . $file;

// Verifica che il file esista e non sia una directory
if (empty($file) || !file_exists($filePath) || is_dir($filePath)) {
    // Se il file non è valido, restituisci un errore 404
    http_response_code(404);
    echo "Immagine non trovata.";
    exit;
}

// Determina il MIME type del file per inviare l'header corretto
// In questo caso, sappiamo che salveremo solo file WebP
$mime_type = 'image/webp';

// Imposta l'header del Content-Type
header('Content-Type: ' . $mime_type);

// Leggi il file e invialo al browser
readfile($filePath);
exit;
?>