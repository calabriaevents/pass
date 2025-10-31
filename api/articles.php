<?php
// api/articles.php

require_once __DIR__ . '/config.php';

// --- Input validazione ---
// Controlla se 'category_id' è stato fornito ed è un numero intero positivo
if (!isset($_GET['category_id']) || !filter_var($_GET['category_id'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => "E' richiesto un 'category_id' valido."]);
    exit;
}
$category_id = (int)$_GET['category_id'];

// Gestione della paginazione
$page = isset($_GET['page']) && filter_var($_GET['page'], FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

try {
    // --- Conteggio totale degli articoli per la paginazione ---
    $count_stmt = $db->pdo->prepare("SELECT COUNT(id) FROM articles WHERE category_id = :category_id AND status = 'published'");
    $count_stmt->execute([':category_id' => $category_id]);
    $total_items = (int)$count_stmt->fetchColumn();
    $total_pages = ceil($total_items / $items_per_page);
    $count_stmt->closeCursor();

    // --- Recupero degli articoli paginati ---
    $articles_stmt = $db->pdo->prepare(
        "SELECT id, title, excerpt as subtitle, slug, featured_image, logo
         FROM articles
         WHERE category_id = :category_id AND status = 'published'
         ORDER BY created_at DESC
         LIMIT :limit OFFSET :offset"
    );

    // Binda i parametri
    $articles_stmt->bindValue(':category_id', $category_id, PDO::PARAM_INT);
    $articles_stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $articles_stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

    $articles_stmt->execute();
    $articles = $articles_stmt->fetchAll(PDO::FETCH_ASSOC);
    $articles_stmt->closeCursor();

    // Normalizza i dati degli articoli e genera gli URL per le immagini
    foreach ($articles as &$article) {
        $article['featured_image_url'] = get_full_image_url(normalize_image_path($article['featured_image']));
        $article['logo_url'] = get_full_image_url(normalize_image_path($article['logo']));
        // Rimuove i campi originali
        unset($article['featured_image']);
        unset($article['logo']);
    }

    // --- Costruzione della risposta finale ---
    $response = [
        'pagination' => [
            'current_page' => $page,
            'total_pages' => (int)$total_pages,
            'total_items' => $total_items
        ],
        'articles' => $articles
    ];

    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // In caso di errore del database, invia una risposta di errore generica
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => 'Errore durante il recupero degli articoli.']);
    // Per debug: error_log('API Articles Error: ' . $e->getMessage());
}
?>