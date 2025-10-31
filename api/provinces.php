<?php
// api/provinces.php

require_once __DIR__ . '/config.php';

try {
    // 1. Recupera tutte le province
    $provinces_stmt = $db->pdo->query("SELECT id, name, image_path FROM provinces ORDER BY name ASC");
    $provinces = $provinces_stmt->fetchAll(PDO::FETCH_ASSOC);
    $provinces_stmt->closeCursor();

    // Normalizza i dati delle province e genera l'URL completo per l'immagine
    foreach ($provinces as &$province) {
        $province['cover_image_url'] = get_full_image_url(normalize_image_path($province['image_path']));
        // Rimuove il campo originale per pulizia
        unset($province['image_path']);
    }

    // 2. Invia la risposta JSON
    echo json_encode($provinces, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // In caso di errore del database, invia una risposta di errore generica
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Errore durante il recupero delle province.']);
    // Per debug: error_log('API Provinces Error: ' . $e->getMessage());
}
?>