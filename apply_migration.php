<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔧 Applicazione Migrazione City Improvements 2025\n\n";

// Carica configurazione
require_once 'config.php';
require_once 'includes/database_mysql.php';

try {
    $db = new Database($host, $dbname, $username, $password);
    
    if (!$db->isConnected()) {
        throw new Exception("Impossibile connettersi al database");
    }
    
    echo "✅ Connesso al database\n\n";
    
    // Leggi il file di migrazione
    $migrationFile = 'migrations/city_improvements_2025.sql';
    if (!file_exists($migrationFile)) {
        throw new Exception("File migrazione non trovato: $migrationFile");
    }
    
    $migrationSQL = file_get_contents($migrationFile);
    echo "✅ File migrazione caricato\n\n";
    
    // Dividi le query (rimuovi commenti e linee vuote)
    $queries = [];
    $lines = explode("\n", $migrationSQL);
    $currentQuery = '';
    
    foreach ($lines as $line) {
        $line = trim($line);
        
        // Salta commenti e linee vuote
        if (empty($line) || strpos($line, '--') === 0) {
            continue;
        }
        
        $currentQuery .= $line . ' ';
        
        // Se la linea finisce con ; abbiamo una query completa
        if (substr($line, -1) === ';') {
            $queries[] = trim($currentQuery);
            $currentQuery = '';
        }
    }
    
    echo "📝 Trovate " . count($queries) . " query da eseguire\n\n";
    
    // Verifica se le colonne esistono già
    $checkColumns = [
        "SHOW COLUMNS FROM user_uploads LIKE 'city_id'",
        "SHOW COLUMNS FROM comments LIKE 'city_id'"
    ];
    
    $pdo = $db->getPDO();
    
    echo "🔍 Verifica colonne esistenti:\n";
    foreach ($checkColumns as $i => $check) {
        $stmt = $pdo->query($check);
        $exists = $stmt->fetch();
        $table = $i === 0 ? 'user_uploads' : 'comments';
        
        if ($exists) {
            echo "⚠️  Colonna city_id già esistente in $table\n";
        } else {
            echo "✅ Colonna city_id non presente in $table (normale)\n";
        }
    }
    
    echo "\n🚀 Inizio applicazione migrazione...\n\n";
    
    // Esegui ogni query
    $success = 0;
    $errors = 0;
    
    foreach ($queries as $i => $query) {
        try {
            echo "Query " . ($i + 1) . ": ";
            
            // Mostra anteprima query (primi 60 caratteri)
            $preview = substr(str_replace(['  ', "\n", "\r"], ' ', $query), 0, 60);
            echo "$preview...\n";
            
            $stmt = $pdo->exec($query);
            echo "✅ Eseguita con successo\n\n";
            $success++;
            
        } catch (PDOException $e) {
            $error = $e->getMessage();
            
            // Errori che possiamo ignorare (colonne/vincoli già esistenti)
            if (strpos($error, 'Duplicate column name') !== false ||
                strpos($error, 'Duplicate key name') !== false ||
                strpos($error, 'Duplicate foreign key constraint name') !== false) {
                echo "⚠️  Query saltata (già applicata): $error\n\n";
            } else {
                echo "❌ Errore: $error\n\n";
                $errors++;
            }
        }
    }
    
    echo "📊 Risultati:\n";
    echo "✅ Query eseguite con successo: $success\n";
    echo "❌ Errori: $errors\n\n";
    
    // Verifica finale
    echo "🔍 Verifica finale colonne aggiunte:\n";
    foreach ($checkColumns as $i => $check) {
        $stmt = $pdo->query($check);
        $exists = $stmt->fetch();
        $table = $i === 0 ? 'user_uploads' : 'comments';
        
        if ($exists) {
            echo "✅ Colonna city_id presente in $table\n";
        } else {
            echo "❌ Colonna city_id MANCANTE in $table\n";
        }
    }
    
    if ($errors === 0) {
        echo "\n🎉 MIGRAZIONE COMPLETATA CON SUCCESSO!\n";
        echo "🔗 Ora le foto e i commenti possono essere collegati alle città!\n";
    } else {
        echo "\n⚠️  Migrazione completata con alcuni errori\n";
    }
    
} catch (Exception $e) {
    echo "❌ Errore durante migrazione: " . $e->getMessage() . "\n";
    exit(1);
}
?>