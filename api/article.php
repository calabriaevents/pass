<?php
// api/article.php

require_once __DIR__ . '/config.php';

// --- Input validazione ---
// Controlla se 'slug' è stato fornito
if (!isset($_GET['slug']) || empty(trim($_GET['slug']))) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => "E' richiesto uno 'slug' valido."]);
    exit;
}
$slug = trim($_GET['slug']);

try {
    // --- Recupero dell'articolo dal database ---
    $stmt = $db->pdo->prepare(
        "SELECT a.*, c.name as category_name
         FROM articles a
         JOIN categories c ON a.category_id = c.id
         WHERE a.slug = :slug AND a.status = 'published'
         LIMIT 1"
    );
    $stmt->execute([':slug' => $slug]);
    $article = $stmt->fetch(PDO::FETCH_ASSOC);
    $stmt->closeCursor();

    // Controlla se l'articolo è stato trovato
    if (!$article) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Articolo non trovato.']);
        exit;
    }

    // --- Elaborazione dei dati ---

    // 1. Decodifica i dati JSON
    $json_data = json_decode($article['json_data'] ?? '[]', true);

    // 2. Prepara la galleria di immagini con URL completi
    $gallery_images = [];
    if (!empty($json_data['gallery_images']) && is_array($json_data['gallery_images'])) {
        foreach ($json_data['gallery_images'] as $image_path) {
            $gallery_images[] = get_full_image_url(normalize_image_path($image_path));
        }
    }

    // --- Costruzione della risposta finale ---
    $response = [
        'id' => (int)$article['id'],
        'title' => $article['title'],
        'subtitle' => $article['excerpt'], // Corretto da subtitle
        'slug' => $article['slug'],
        'content' => $article['content'],
        'publication_date' => $article['created_at'], // Corretto da publication_date
        'category' => [
            'name' => $article['category_name']
        ],
        'images' => [
            'featured' => get_full_image_url(normalize_image_path($article['featured_image'])),
            'logo' => get_full_image_url(normalize_image_path($article['logo'])),
            'gallery' => $gallery_images
        ],
        'links' => [
            'facebook' => $json_data['facebook_url'] ?? null,
            'instagram' => $json_data['instagram_url'] ?? null,
            'tiktok' => $json_data['tiktok_url'] ?? null,
            'youtube' => $json_data['youtube_url'] ?? null,
            'linkedin' => $json_data['linkedin_url'] ?? null
        ]
        // Aggiungi altri campi se necessario
    ];

    echo json_encode($response, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    // In caso di errore del database, invia una risposta di errore generica
    http_response_code(500); // Internal Server Error
    echo json_encode(['error' => "Errore durante il recupero dei dettagli dell'articolo."]);
    // Per debug: error_log('API Article Detail Error: ' . $e->getMessage());
}
?>