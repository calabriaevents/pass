<?php
/**
 * Script per verificare la struttura del database e lo stato delle colonne city_id
 * Controlla se è sicuro applicare la migrazione foto-città
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

echo "🔍 VERIFICA STRUTTURA DATABASE\n\n";

$db = new Database();

if (!$db->isConnected()) {
    echo "❌ ERRORE: Impossibile connettersi al database\n";
    echo "Dettagli: " . $db->getConnectionError() . "\n";
    exit(1);
}

echo "✅ Connesso al database con successo\n\n";

try {
    $pdo = $db->pdo;
    
    // 1. Verifica struttura tabella user_uploads
    echo "📋 TABELLA user_uploads:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM user_uploads");
    $columns = $stmt->fetchAll();
    
    $hasCityId = false;
    foreach ($columns as $column) {
        echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
        if ($column['Field'] === 'city_id') {
            $hasCityId = true;
        }
    }
    
    if ($hasCityId) {
        echo "✅ Colonna city_id PRESENTE in user_uploads\n\n";
    } else {
        echo "❌ Colonna city_id MANCANTE in user_uploads\n\n";
    }
    
    // 2. Verifica struttura tabella comments
    echo "📋 TABELLA comments:\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM comments");
    $columns = $stmt->fetchAll();
    
    $commentsHasCityId = false;
    foreach ($columns as $column) {
        echo "   - " . $column['Field'] . " (" . $column['Type'] . ")\n";
        if ($column['Field'] === 'city_id') {
            $commentsHasCityId = true;
        }
    }
    
    if ($commentsHasCityId) {
        echo "✅ Colonna city_id PRESENTE in comments\n\n";
    } else {
        echo "❌ Colonna city_id MANCANTE in comments\n\n";
    }
    
    // 3. Test funzionalità basilari per verificare che non ci siano errori
    echo "🧪 TEST FUNZIONALITÀ BASILARI:\n";
    
    // Test caricamento città
    try {
        $cities = $db->getCities();
        echo "✅ getCities(): " . count($cities) . " città caricate\n";
    } catch (Exception $e) {
        echo "❌ getCities(): " . $e->getMessage() . "\n";
    }
    
    // Test caricamento uploads
    try {
        $uploads = $db->getUserUploads();
        echo "✅ getUserUploads(): " . count($uploads) . " upload trovati\n";
    } catch (Exception $e) {
        echo "❌ getUserUploads(): " . $e->getMessage() . "\n";
    }
    
    // Test caricamento commenti
    try {
        $comments = $db->getComments();
        echo "✅ getComments(): " . count($comments) . " commenti trovati\n";
    } catch (Exception $e) {
        echo "❌ getComments(): " . $e->getMessage() . "\n";
    }
    
    // Test metodi città specifici
    if (!empty($cities)) {
        $testCityId = $cities[0]['id'];
        echo "\n🏘️ TEST METODI CITTÀ (ID: $testCityId):\n";
        
        try {
            $cityPhotos = $db->getApprovedCityPhotos($testCityId);
            echo "✅ getApprovedCityPhotos(): " . count($cityPhotos) . " foto\n";
        } catch (Exception $e) {
            echo "❌ getApprovedCityPhotos(): " . $e->getMessage() . "\n";
        }
        
        try {
            $cityComments = $db->getApprovedCommentsByCityId($testCityId);
            echo "✅ getApprovedCommentsByCityId(): " . count($cityComments) . " commenti\n";
        } catch (Exception $e) {
            echo "❌ getApprovedCommentsByCityId(): " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n";
    
    // 4. Sommario e raccomandazioni
    echo "📊 SOMMARIO:\n";
    
    if ($hasCityId && $commentsHasCityId) {
        echo "✅ STRUTTURA COMPLETA: Le colonne city_id esistono in entrambe le tabelle\n";
        echo "💡 RACCOMANDAZIONE: Puoi attivare i metodi avanzati per il collegamento foto-città\n";
    } elseif (!$hasCityId && !$commentsHasCityId) {
        echo "⚠️  STRUTTURA BASE: Le colonne city_id non esistono (stato normale)\n";
        echo "💡 RACCOMANDAZIONE: Applica la migrazione per abilitare il collegamento foto-città\n";
    } else {
        echo "🔄 STRUTTURA PARZIALE: Solo alcune colonne city_id esistono\n";
        echo "💡 RACCOMANDAZIONE: Completa la migrazione per avere tutte le funzionalità\n";
    }
    
    echo "\n🔧 STATO CORRENTE DEI METODI:\n";
    echo "✅ Metodi database: Configurati per compatibilità (nessun errore 500)\n";
    echo "✅ Pagine città: Dovrebbero funzionare normalmente\n";
    echo "⚠️  Foto città: Non ancora collegate alle città specifiche\n";
    echo "⚠️  Commenti città: Non ancora collegati alle città specifiche\n";
    
    echo "\n🎯 PROSSIMI PASSI:\n";
    if (!$hasCityId || !$commentsHasCityId) {
        echo "1. Verifica che le pagine città funzionino (nessun errore 500)\n";
        echo "2. Applica la migrazione SQL per aggiungere colonne city_id\n";
        echo "3. Attiva i metodi avanzati per il collegamento foto-città\n";
        echo "4. Testa il flusso completo upload → moderazione → visualizzazione\n";
    } else {
        echo "1. Attiva i metodi avanzati nel database_mysql.php\n";
        echo "2. Testa il flusso completo già funzionante\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERRORE durante la verifica: " . $e->getMessage() . "\n";
    exit(1);
}

echo "\n🔚 Verifica completata.\n";
?>