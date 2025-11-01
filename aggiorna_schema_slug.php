<?php
header('Content-Type: text/plain; charset=utf-8');

require_once __DIR__ . '/includes/database_mysql.php';

echo "===================================================\n";
echo "🚀 ESECUZIONE AGGIORNAMENTO SCHEMA DATABASE 🚀\n";
echo "===================================================\n\n";

try {
    $db = new Database();
    if (!$db->isConnected()) {
        throw new Exception("Connessione al database fallita: " . $db->getConnectionError());
    }

    $pdo = $db->pdo;
    $tabelle = ['provinces', 'cities'];
    $colonna = 'slug';

    foreach ($tabelle as $tabella) {
        echo "--- Controllo tabella: {$tabella} ---\n";

        $stmt = $pdo->query("SHOW COLUMNS FROM `{$tabella}` LIKE '{$colonna}'");
        $esiste = $stmt->fetch();

        if ($esiste) {
            echo "✅ La colonna '{$colonna}' esiste già nella tabella '{$tabella}'. Nessuna modifica necessaria.\n\n";
        } else {
            echo "🔧 La colonna '{$colonna}' non esiste. Aggiungo colonna...\n";

            // Aggiunge la colonna slug dopo la colonna 'name'
            $sql = "ALTER TABLE `{$tabella}` ADD COLUMN `{$colonna}` VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `name`";

            if ($pdo->exec($sql) !== false) {
                echo "✅ Colonna '{$colonna}' aggiunta con successo alla tabella '{$tabella}'.\n\n";
            } else {
                echo "❌ ERRORE: Impossibile aggiungere la colonna '{$colonna}' alla tabella '{$tabella}'.\n\n";
            }
        }
    }

    echo "===================================================\n";
    echo "🎉 Aggiornamento dello schema completato! 🎉\n";
    echo "===================================================\n\n";
    echo "IMPORTANTE: Ora puoi eliminare questo file (aggiorna_schema_slug.php) dal tuo server.\n";

} catch (Exception $e) {
    http_response_code(500);
    echo "\n\n❌ ERRORE CRITICO: " . $e->getMessage() . "\n";
    echo "L'aggiornamento non è andato a buon fine. Controlla le credenziali del database e i permessi.\n";
}
