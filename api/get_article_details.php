<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Per ora, per test

require_once '../includes/database_mysql.php';

try {
    // 1. Validare il parametro ID
    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        http_response_code(400); // Bad Request
        echo json_encode([
            'success' => false,
            'error' => 'ID articolo mancante o non valido.'
        ]);
        exit;
    }

    $article_id = intval($_GET['id']);

    $db = new Database();

    // 2. Query per prendere tutti i dettagli dell'articolo
    // Uso un JOIN per ottenere anche i nomi di categoria, città e autore
    $stmt = $db->pdo->prepare("
        SELECT
            a.id,
            a.title,
            a.slug,
            a.subtitle,
            a.content,
            a.featured_image,
            a.logo,
            a.json_data,
            a.address,
            a.latitude,
            a.longitude,
            a.phone,
            a.email,
            a.website,
            a.created_at,
            c.name as category_name,
            ci.name as city_name,
            u.username as author_name
        FROM
            articles a
        LEFT JOIN
            categories c ON a.category_id = c.id
        LEFT JOIN
            cities ci ON a.city_id = ci.id
        LEFT JOIN
            users u ON a.user_id = u.id
        WHERE
            a.id = :id AND a.status = 'published'
    ");

    $stmt->bindParam(':id', $article_id, PDO::PARAM_INT);
    $stmt->execute();
    $article = $stmt->fetch(PDO::FETCH_ASSOC);

    // 3. Gestire il caso in cui l'articolo non viene trovato
    if (!$article) {
        http_response_code(404); // Not Found
        echo json_encode([
            'success' => false,
            'error' => 'Articolo non trovato o non pubblicato.'
        ]);
        exit;
    }

    // Decodifico i dati JSON se presenti, altrimenti imposto un array vuoto
    $article['json_data'] = $article['json_data'] ? json_decode($article['json_data'], true) : [];

    // 4. Restituire i dati
    echo json_encode([
        'success' => true,
        'data' => $article
    ]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'error' => 'Errore durante il recupero dei dettagli dell\'articolo.',
        'details' => $e->getMessage() // Da rimuovere in produzione
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Si è verificato un errore generico.',
        'details' => $e->getMessage() // Da rimuovere in produzione
    ]);
}
?>
