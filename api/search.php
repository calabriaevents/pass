<?php
// api/search.php

require_once __DIR__ . '/config.php';

// --- Input e validazione ---
$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

// Restituisci un array vuoto se la keyword è assente o troppo corta
if (mb_strlen($keyword) < 3) {
    echo json_encode([]);
    exit;
}

// Prepara il termine di ricerca per le query LIKE
$search_term = '%' . $keyword . '%';

try {
    // --- Query unificata con UNION ALL ---
    // Cerca in articoli, città e province
    $sql = "
        (SELECT
            'article' as type,
            title,
            excerpt as description,
            slug,
            featured_image as image
        FROM articles
        WHERE (title LIKE :term1 OR excerpt LIKE :term2) AND status = 'published')

        UNION ALL

        (SELECT
            'city' as type,
            name as title,
            description,
            NULL as slug,
            hero_image as image
        FROM cities
        WHERE (name LIKE :term3))

        UNION ALL

        (SELECT
            'province' as type,
            name as title,
            NULL as description,
            NULL as slug,
            image_path as image
        FROM provinces
        WHERE (name LIKE :term4))

        LIMIT 30
    ";

    $stmt = $db->pdo->prepare($sql);
    $stmt->execute([
        ':term1' => $search_term,
        ':term2' => $search_term,
        ':term3' => $search_term,
        ':term4' => $search_term
    ]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // --- Elaborazione e formattazione dei risultati ---
    $formatted_results = [];
    foreach ($results as $result) {
        // Tronca la descrizione per uniformità
        $description = $result['description'] ?? '';
        if (mb_strlen($description) > 150) {
            $description = mb_substr($description, 0, 150) . '...';
        }

        $formatted_results[] = [
            'type' => $result['type'],
            'title' => $result['title'],
            'description' => $description,
            'slug' => $result['slug'],
            'image_url' => get_full_image_url(normalize_image_path($result['image']))
        ];
    }

    echo json_encode($formatted_results, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // In caso di errore del database, invia una risposta di errore generica
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Errore durante l\'esecuzione della ricerca.']);
    // Per debug: error_log('API Search Error: ' . $e->getMessage());
}
?>