<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../includes/database_mysql.php';
// Potremmo voler associare i token agli utenti in futuro
// require_once '../admin/auth_check.php'; // session_start() è qui dentro

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

try {
    // 1. Leggere e validare i dati JSON in input
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    $token = $data['token'] ?? null;
    $platform = $data['platform'] ?? null;

    if (empty($token) || empty($platform)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Token e piattaforma sono obbligatori.']);
        exit;
    }

    if (!in_array($platform, ['ios', 'android'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Piattaforma non valida.']);
        exit;
    }

    // 2. Connettersi al database
    $db = new Database();

    // 3. Inserire o aggiornare il token
    // Usiamo INSERT ... ON DUPLICATE KEY UPDATE per evitare token duplicati
    // e per aggiornare la data di creazione se un token viene ri-registrato.
    $stmt = $db->pdo->prepare("
        INSERT INTO push_tokens (token, platform)
        VALUES (:token, :platform)
        ON DUPLICATE KEY UPDATE
            platform = VALUES(platform),
            created_at = NOW()
    ");

    $stmt->bindParam(':token', $token, PDO::PARAM_STR);
    $stmt->bindParam(':platform', $platform, PDO::PARAM_STR);

    $stmt->execute();

    // 4. Inviare una risposta di successo
    echo json_encode([
        'success' => true,
        'message' => 'Token registrato con successo.'
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Errore del server interno. Impossibile registrare il token.'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Si è verificato un errore generico.'
    ]);
}
?>
