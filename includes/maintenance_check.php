<?php
/**
 * Controllo modalità manutenzione
 * Questo file verifica se il sito è in manutenzione e reindirizza gli utenti
 * L'area admin rimane sempre accessibile
 */

// Non eseguire il controllo se siamo già nella pagina di manutenzione o nell'area admin
$current_file = basename($_SERVER['PHP_SELF']);
$current_dir = basename(dirname($_SERVER['PHP_SELF']));

// Skip controllo per:
// - Area admin
// - Pagina manutenzione stessa
// - File di API che potrebbero essere chiamati dall'admin
if ($current_dir === 'admin' || $current_file === 'maintenance.php' || $current_dir === 'api') {
    return;
}

// Includi database se non già incluso
if (!class_exists('Database')) {
    require_once __DIR__ . '/database_mysql.php';
}

try {
    $db = new Database();
    
    // Verifica se la manutenzione è attivata
    $maintenance_enabled = $db->getSetting('maintenance_enabled');
    
    if ($maintenance_enabled == 1) {
        // Ottieni il messaggio di manutenzione
        $maintenance_message = $db->getSetting('maintenance_message') ?? 'Sito in manutenzione. Torneremo presto!';
        
        // Reindirizza alla pagina di manutenzione
        if ($current_file !== 'maintenance.php') {
            header('Location: maintenance.php');
            exit();
        }
    }
} catch (Exception $e) {
    // In caso di errore nel database, non bloccare il sito
    // Log dell'errore se necessario
    error_log("Errore controllo manutenzione: " . $e->getMessage());
}
?>