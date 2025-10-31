<?php
// api/home.php

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

    // 2. Recupera tutte le categorie
    $categories_stmt = $db->pdo->query("SELECT id, name, icon FROM categories ORDER BY name ASC");
    $categories = $categories_stmt->fetchAll(PDO::FETCH_ASSOC);
    $categories_stmt->closeCursor();

    // 3. Prepara la query per recuperare gli articoli per ogni categoria
    $articles_stmt = $db->pdo->prepare(
        "SELECT id, title, excerpt as subtitle, slug, featured_image, logo
         FROM articles
         WHERE category_id = :category_id AND status = 'published'
         ORDER BY created_at DESC
         LIMIT 10"
    );

    // Itera su ogni categoria per aggiungere i suoi articoli
    foreach ($categories as &$category) {
        $articles_stmt->execute([':category_id' => $category['id']]);
        $articles = $articles_stmt->fetchAll(PDO::FETCH_ASSOC);

        // Normalizza i dati degli articoli e genera gli URL per le immagini
        foreach ($articles as &$article) {
            $article['featured_image_url'] = get_full_image_url(normalize_image_path($article['featured_image']));
            $article['logo_url'] = get_full_image_url(normalize_image_path($article['logo']));
            // Rimuove i campi originali
            unset($article['featured_image']);
            unset($article['logo']);
        }

        $category['articles'] = $articles;
    }

    // 4. Costruisce la risposta finale
    $response = [
        'provinces' => $provinces,
        'categories' => $categories
    ];

    // Invia la risposta JSON
    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // In caso di errore del database, invia una risposta di errore generica
    http_response_code(500); // Internal Server Error
    // Per sicurezza, non esporre i dettagli dell'errore al client in produzione
    echo json_encode(['error' => 'Errore durante il recupero dei dati per la homepage.']);
    // Per debug: error_log('API Home Error: ' . $e->getMessage());
}
?>