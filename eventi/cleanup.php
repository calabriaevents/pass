<?php
// cleanup.php - Script per la pulizia delle immagini orfane

echo "--- Inizio script di pulizia immagini ---\n";

// --- IMPOSTAZIONI ---
// Il numero di giorni dopo i quali un'immagine non utilizzata può essere eliminata.
$days_to_keep_orphan_files = 1;
// La cartella principale delle immagini
$images_directory = 'immagini/';
// NUOVO: Lista dei nomi di file da non eliminare mai, indipendentemente da dove si trovino.
$ignore_filenames = [
    'logo.png'
];


// --- CONNESSIONE AL DATABASE ---
require 'db_connect.php';
echo "Connessione al database stabilita.\n";

// --- 1. OTTIENI TUTTI I FILE SUL SERVER ---
$all_files_on_server = [];
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($images_directory, RecursiveDirectoryIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if ($file->isFile()) {
        $all_files_on_server[] = $file->getPathname();
    }
}
echo "Trovati " . count($all_files_on_server) . " file nella cartella immagini.\n";

// --- 2. OTTIENI TUTTE LE IMMAGINI IN USO DAL DATABASE ---
$in_use_images = [];

// Immagini dagli eventi
$result_events = $conn->query("SELECT imageUrl FROM events WHERE imageUrl IS NOT NULL AND imageUrl != ''");
if ($result_events) {
    while ($row = $result_events->fetch_assoc()) {
        $in_use_images[] = $row['imageUrl'];
    }
}

// Loghi dalle attività
$result_activities = $conn->query("SELECT logoUrl FROM activities WHERE logoUrl IS NOT NULL AND logoUrl != ''");
if ($result_activities) {
    while ($row = $result_activities->fetch_assoc()) {
        $in_use_images[] = $row['logoUrl'];
    }
}

// Logo dell'app dalla configurazione
$result_config = $conn->query("SELECT setting_value FROM config WHERE setting_key = 'logoAppUrl' AND setting_value IS NOT NULL AND setting_value != ''");
if ($result_config) {
    while ($row = $result_config->fetch_assoc()) {
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
    // Controlla se il nome del file (es. 'logo.png') è nella lista da ignorare
    if (!in_array(basename($file_path), $ignore_filenames)) {
        $files_to_check[] = $file_path;
    } else {
        echo "Ignorato file protetto: " . $file_path . "\n";
    }
}
echo count($files_to_check) . " file orfani verranno controllati per l'eliminazione.\n";

// --- 5. ELIMINA I FILE ORFANI E VECCHI ---
$deleted_count = 0;
$threshold_timestamp = time() - ($days_to_keep_orphan_files * 86400); // 86400 secondi in un giorno

foreach ($files_to_check as $file_path) {
    if (file_exists($file_path) && filemtime($file_path) < $threshold_timestamp) {
        if (unlink($file_path)) {
            echo "Eliminato file vecchio: " . $file_path . "\n";
            $deleted_count++;
        } else {
            echo "ERRORE: Impossibile eliminare " . $file_path . "\n";
        }
    }
}

echo "Eliminati " . $deleted_count . " file orfani più vecchi di " . $days_to_keep_orphan_files . " giorni.\n";
echo "--- Script di pulizia terminato. ---\n";

$conn->close();
?>