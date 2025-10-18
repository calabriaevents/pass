<?php
// cleanup.php - Script per la pulizia delle immagini orfane

echo "--- Inizio script di pulizia immagini ---\n";

// --- IMPOSTAZIONI ---
$days_to_keep_orphan_files = 1;
$images_directory = '../../eventi/immagini/';
$ignore_filenames = [
    'logo.png'
];

// --- CONNESSIONE AL DATABASE ---
require_once '../../includes/db_config.php';
require_once '../../includes/database_mysql.php';
$db = new Database();
$pdo = $db->pdo;
echo "Connessione al database stabilita.\n";

// --- 1. OTTIENI TUTTI I FILE SUL SERVER ---
$all_files_on_server = [];
if (is_dir($images_directory)) {
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($images_directory, RecursiveDirectoryIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $all_files_on_server[] = 'eventi/immagini/' . str_replace($images_directory, '', $file->getPathname());
        }
    }
}
echo "Trovati " . count($all_files_on_server) . " file nella cartella immagini.\n";

// --- 2. OTTIENI TUTTE LE IMMAGINI IN USO DAL DATABASE ---
$in_use_images = [];

// Immagini dagli eventi
$stmt_events = $pdo->query("SELECT imageUrl FROM plugin_eventi WHERE imageUrl IS NOT NULL AND imageUrl != ''");
if ($stmt_events) {
    while ($row = $stmt_events->fetch()) {
        $in_use_images[] = $row['imageUrl'];
    }
}

// Loghi dalle attività
$stmt_activities = $pdo->query("SELECT logoUrl FROM activities WHERE logoUrl IS NOT NULL AND logoUrl != ''");
if ($stmt_activities) {
    while ($row = $stmt_activities->fetch()) {
        $in_use_images[] = $row['logoUrl'];
    }
}

// Logo dell'app dalla configurazione
$stmt_config = $pdo->query("SELECT setting_value FROM config WHERE setting_key = 'logoAppUrl' AND setting_value IS NOT NULL AND setting_value != ''");
if ($stmt_config) {
    while ($row = $stmt_config->fetch()) {
        $in_use_images[] = $row['setting_value'];
    }
}

$in_use_images = array_unique($in_use_images);
echo "Trovati " . count($in_use_images) . " URL di immagini in uso nel database.\n";

// --- 3. CONFRONTA E TROVA I FILE ORFANI ---
$orphan_files = array_diff($all_files_on_server, $in_use_images);
echo "Trovati " . count($orphan_files) . " file orfani (non presenti nel database).\n";

// --- 4. FILTRA I FILE ORFANI USANDO LA LISTA DI IGNORATI ---
$files_to_check = [];
foreach ($orphan_files as $file_path) {
    if (!in_array(basename($file_path), $ignore_filenames)) {
        $files_to_check[] = $file_path;
    } else {
        echo "Ignorato file protetto: " . $file_path . "\n";
    }
}
echo count($files_to_check) . " file orfani verranno controllati per l'eliminazione.\n";

// --- 5. ELIMINA I FILE ORFANI E VECCHI ---
$deleted_count = 0;
$threshold_timestamp = time() - ($days_to_keep_orphan_files * 86400);

foreach ($files_to_check as $file_path) {
    $full_path = '../../' . $file_path;
    if (file_exists($full_path) && filemtime($full_path) < $threshold_timestamp) {
        if (unlink($full_path)) {
            echo "Eliminato file vecchio: " . $full_path . "\n";
            $deleted_count++;
        } else {
            echo "ERRORE: Impossibile eliminare " . $full_path . "\n";
        }
    }
}

echo "Eliminati " . $deleted_count . " file orfani più vecchi di " . $days_to_keep_orphan_files . " giorni.\n";
echo "--- Script di pulizia terminato. ---\n";

?>