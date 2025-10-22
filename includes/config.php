<?php
// Configurazione generale
define('SITE_NAME', 'Passione Calabria');
define('SITE_DESCRIPTION', 'La tua guida alla Calabria');

// --- CONTROLLO MODALITÀ MANUTENZIONE ---
// Questo blocco viene eseguito su ogni pagina che include questo file di configurazione.
$maintenance_flag_file = dirname(__DIR__) . '/maintenance.flag';
// Controlla se il file flag esiste e se non ci troviamo in una pagina dell'area admin.
if (file_exists($maintenance_flag_file) && strpos($_SERVER['REQUEST_URI'], '/admin/') === false) {
    // Se la pagina richiesta non è già maintenance.php, reindirizza.
    if (basename($_SERVER['PHP_SELF']) !== 'maintenance.php') {
        // Usa un percorso relativo o assoluto a seconda della configurazione del server.
        // Per robustezza, calcoliamo un percorso di base.
        $base_url = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
        // Se siamo nella root, il base_url potrebbe essere vuoto.
        if ($base_url === '' || $base_url === DIRECTORY_SEPARATOR) {
            header('Location: /maintenance.php');
        } else {
             // Gestisce il caso in cui il sito sia in una sottocartella
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname(dirname($_SERVER'['PHP_SELF'])), '/\\');
            header('Location: ' . $protocol . $host . $path . '/maintenance.php');
        }
        exit();
    }
}
if (!defined('SITE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script_dir = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
    // Rimuovi 'includes/' dal path se presente, dato che config.php è in 'includes'
    $base_path = rtrim(preg_replace('/\/includes\/?$/', '/', $script_dir), '/');
    define('SITE_URL', $protocol . $host . $base_path);
}
define('ADMIN_EMAIL', 'admin@passionecalabria.it');

// Configurazione database
define('DB_PATH', __DIR__ . '/../passione_calabria.db');

// Configurazione upload
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configurazione sicurezza
define('ADMIN_PASSWORD_HASH', '$2y$10$example'); // Cambiare con hash reale
define('SESSION_LIFETIME', 3600); // 1 ora

// Timezone
date_default_timezone_set('Europe/Rome');

// Encoding
mb_internal_encoding('UTF-8');

// Gestione errori
if ($_ENV['ENVIRONMENT'] ?? 'production' === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Avvia sessione se non già avviata
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Funzioni di utility
function sanitize($input) {
    if (is_array($input)) {
        return array_map('sanitize', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function createSlug($text) {
    $text = strtolower($text);
    $text = preg_replace('/[àáâãäå]/', 'a', $text);
    $text = preg_replace('/[èéêë]/', 'e', $text);
    $text = preg_replace('/[ìíîï]/', 'i', $text);
    $text = preg_replace('/[òóôõö]/', 'o', $text);
    $text = preg_replace('/[ùúûü]/', 'u', $text);
    $text = preg_replace('/[ç]/', 'c', $text);
    $text = preg_replace('/[ñ]/', 'n', $text);
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s_-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

function formatDate($date) {
    return date('d/m/Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('d/m/Y H:i', strtotime($datetime));
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}

function isLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /admin/login.php');
        exit;
    }
}

function redirectTo($url) {
    header("Location: $url");
    exit;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>
