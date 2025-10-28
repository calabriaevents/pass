<?php
// Percorso alla directory protetta dove risiedono le immagini caricate.
// Modifica questo percorso se la tua struttura di cartelle è diversa.
define('PROTECTED_UPLOADS_PATH', __DIR__ . '/uploads_protected/');

// Immagine segnaposto da mostrare se quella richiesta non viene trovata.
define('PLACEHOLDER_IMAGE_PATH', __DIR__ . '/assets/images/placeholder.jpg');

// Funzione per terminare in modo sicuro e mostrare un'immagine segnaposto o un errore.
function serve_placeholder_or_exit($status = 404) {
    http_response_code($status);
    if (file_exists(PLACEHOLDER_IMAGE_PATH)) {
        $placeholder_info = getimagesize(PLACEHOLDER_IMAGE_PATH);
        header('Content-Type: ' . $placeholder_info['mime']);
        readfile(PLACEHOLDER_IMAGE_PATH);
    }
    exit;
}

// 1. Validazione del parametro 'path'
if (!isset($_GET['path']) || empty(trim($_GET['path']))) {
    // Se il parametro 'path' è mancante o vuoto, termina.
    serve_placeholder_or_exit(400); // Bad Request
}

$requested_path = trim($_GET['path']);

// 2. Prevenzione di attacchi "Directory Traversal"
// Assicurati che il percorso non contenga '..' per evitare di risalire le cartelle.
if (strpos($requested_path, '..') !== false) {
    // Tentativo di accesso non autorizzato.
    serve_placeholder_or_exit(403); // Forbidden
}

// 3. Costruzione del percorso completo e sicuro del file
$file_path = PROTECTED_UPLOADS_PATH . ltrim($requested_path, '/');

// 4. Normalizzazione del percorso per risolvere eventuali ambiguità (es. / o \).
$real_file_path = realpath($file_path);

// 5. Verifica di sicurezza finale
// Controlla che il percorso reale esista, sia un file e si trovi all'interno della cartella protetta.
if (!$real_file_path || !is_file($real_file_path) || strpos($real_file_path, PROTECTED_UPLOADS_PATH) !== 0) {
    // File non trovato o tentativo di accesso a file esterni alla cartella uploads.
    serve_placeholder_or_exit(404); // Not Found
}

// 6. Servizio dell'immagine
// Ottieni il tipo MIME del file per inviare l'header corretto al browser.
$mime_type = mime_content_type($real_file_path);
if ($mime_type === false) {
    // Impossibile determinare il tipo di file.
    serve_placeholder_or_exit(500); // Internal Server Error
}

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($real_file_path));
header('Cache-Control: max-age=31536000, public'); // Aggiungi cache per migliorare le performance
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Leggi il file e invialo al browser.
readfile($real_file_path);
exit;
