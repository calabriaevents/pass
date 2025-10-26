<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../includes/database_mysql.php';

try {
    $db = new Database();

    // Query per prendere tutte le location da articoli e città.
    // Uniamo i risultati da due tabelle. Selezioniamo solo i campi essenziali.
    $stmt = $db->pdo->prepare("
        (SELECT
            id,
            title as name,
            'article' as type,
            latitude,
            longitude
        FROM articles
        WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != '' AND longitude != '')
        UNION ALL
        (SELECT
            id,
            name,
            'city' as type,
            latitude,
            longitude
        FROM cities
        WHERE latitude IS NOT NULL AND longitude IS NOT NULL AND latitude != '' AND longitude != '')
    ");

    $stmt->execute();
    $locations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'data' => $locations
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore del server interno. Impossibile recuperare le coordinate.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Si è verificato un errore generico.'
    ]);
}
?>
