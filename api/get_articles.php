<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Per ora, per test, poi si può restringere

require_once '../includes/database_mysql.php';

try {
    $db = new Database();

    // Query per prendere gli articoli. Seleziono solo i campi necessari per l'app.
    // Aggiungo anche il nome della categoria e della città per completezza.
    $stmt = $db->pdo->prepare("
        SELECT
            a.id,
            a.title,
            a.slug,
            a.subtitle,
            a.featured_image,
            a.created_at,
            c.name as category_name,
            ci.name as city_name
        FROM
            articles a
        LEFT JOIN
            categories c ON a.category_id = c.id
        LEFT JOIN
            cities ci ON a.city_id = ci.id
        WHERE
            a.status = 'published'
        ORDER BY
            a.created_at DESC
        LIMIT 20
    ");

    $stmt->execute();
    $articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $articles
    ]);

} catch (PDOException $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'success' => false,
        'error' => 'Errore durante il recupero degli articoli dal database.',
        'details' => $e->getMessage() // Rimuovere in produzione
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Si è verificato un errore generico.',
        'details' => $e->getMessage() // Rimuovere in produzione
    ]);
}
?>
