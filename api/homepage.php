<?php
// Imposta l'header per la risposta JSON
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Per test in locale

// Include i file necessari
require_once dirname(__DIR__) . '/includes/config.php';
require_once dirname(__DIR__) . '/includes/database_mysql.php';

// Crea una nuova istanza del database
$db = new Database();

// Array per contenere i dati della risposta
$response = [
    'hero' => [],
    'categories' => [],
    'articles' => []
];

try {
    // 1. Recupera i dati per la sezione Hero
    $heroSettings = $db->getSettings('hero_');
    $response['hero'] = [
        'title' => $heroSettings['hero_title'] ?? 'Benvenuti',
        'subtitle' => $heroSettings['hero_subtitle'] ?? 'Scopri la Calabria',
        'image' => $heroSettings['hero_image'] ?? ''
    ];

    // 2. Recupera le categorie
    // Prendo solo le prime 8 categorie per non affollare la homepage
    $queryCategories = "SELECT name, icon FROM categories ORDER BY name ASC LIMIT 8";
    $stmtCategories = $db->pdo->prepare($queryCategories);
    $stmtCategories->execute();
    $response['categories'] = $stmtCategories->fetchAll(PDO::FETCH_ASSOC);

    // 3. Recupera gli ultimi articoli
    $queryArticles = "
        SELECT a.title, a.slug, a.featured_image, c.name AS nome_categoria
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        WHERE a.status = 'published'
        ORDER BY a.created_at DESC
        LIMIT 6
    ";
    $stmtArticles = $db->pdo->prepare($queryArticles);
    $stmtArticles->execute();
    $response['articles'] = $stmtArticles->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // In caso di errore, restituisci un errore 500
    http_response_code(500);
    $response = ['error' => 'Errore durante il recupero dei dati dal database.'];
    // In un ambiente di produzione, si dovrebbe loggare $e->getMessage() invece di mostrarlo
}

// Chiudi la connessione
$db = null;

// Restituisci la risposta in formato JSON
echo json_encode($response);
