<?php
// api/config.php

// Imposta l'header per indicare che la risposta è in formato JSON
header('Content-Type: application/json; charset=utf-8');

// Abilita CORS per permettere all'app di accedere alle API
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Gestione della richiesta OPTIONS per il preflight CORS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Include i file di configurazione e di accesso al database
require_once dirname(__DIR__) . '/includes/db_config.php';
require_once dirname(__DIR__) . '/includes/database_mysql.php';

// Crea un'istanza della classe Database
try {
    $db = new Database();
} catch (Exception $e) {
    // In caso di errore di connessione, restituisce un errore 503
    http_response_code(503); // Service Unavailable
    echo json_encode(['error' => 'Impossibile connettersi al database. ' . $e->getMessage()]);
    exit;
}

/**
 * Genera l'URL completo per un'immagine gestita da image-loader.php.
 *
 * @param string|null $image_path Il percorso dell'immagine relativo a uploads_protected.
 * @return string|null L'URL completo dell'immagine o null se il percorso è vuoto.
 */
function get_full_image_url($image_path) {
    if (empty($image_path)) {
        return null;
    }

    // Rimuove 'uploads_protected/' se è già presente per evitare duplicazioni
    if (strpos($image_path, 'uploads_protected/') === 0) {
        $image_path = substr($image_path, strlen('uploads_protected/'));
    }

    // Determina il protocollo (http o https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    // Ottiene l'host
    $host = $_SERVER['HTTP_HOST'];

    // Costruisce l'URL di base del sito
    $base_url = $protocol . $host;

    // Aggiunge la subdirectory se il sito non è nella root
    // Esempio: /miosito/image-loader.php
    $script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

    return $base_url . $script_dir . '../image-loader.php?path=' . urlencode($image_path);
}

/**
 * Pulisce e normalizza un percorso di immagine, gestendo percorsi completi e relativi.
 *
 * @param string|null $path Il percorso dell'immagine dal database.
 * @return string|null Il percorso normalizzato pronto per get_full_image_url o null.
 */
function normalize_image_path($path) {
    if (empty($path)) {
        return null;
    }
    // Se il percorso contiene 'uploads_protected/', estrae solo la parte successiva.
    $search_string = 'uploads_protected/';
    $pos = strpos($path, $search_string);
    if ($pos !== false) {
        return substr($path, $pos + strlen($search_string));
    }
    // Altrimenti, restituisce il percorso così com'è (es. 'articles/nomefile.jpg').
    return $path;
}

?>