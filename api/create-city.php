<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

require_once '../includes/database_mysql.php';

try {
    $input = json_decode(file_get_contents('php://input'), true);
    $cityName = trim($input['name'] ?? '');
    $provinceId = intval($input['province_id'] ?? 0);
    
    if (empty($cityName) || $provinceId <= 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome città e provincia richiesti']);
        exit;
    }
    
    $db = new Database();
    
    // Check if city already exists in this province
    $stmt = $db->connection->prepare("
        SELECT id FROM cities 
        WHERE name = ? AND province_id = ?
    ");
    $stmt->bind_param('si', $cityName, $provinceId);
    $stmt->execute();
    $existing = $stmt->get_result()->fetch_assoc();
    
    if ($existing) {
        echo json_encode([
            'success' => true,
            'city_id' => $existing['id'],
            'message' => 'Città già esistente'
        ]);
        exit;
    }
    
    // Create new city
    $stmt = $db->connection->prepare("
        INSERT INTO cities (name, province_id, created_at) 
        VALUES (?, ?, NOW())
    ");
    $stmt->bind_param('si', $cityName, $provinceId);
    $stmt->execute();
    
    $newCityId = $db->connection->insert_id;
    
    // Get province name for response
    $stmt = $db->connection->prepare("
        SELECT name FROM provinces WHERE id = ?
    ");
    $stmt->bind_param('i', $provinceId);
    $stmt->execute();
    $province = $stmt->get_result()->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'city_id' => $newCityId,
        'city_name' => $cityName,
        'province_name' => $province['name'],
        'message' => 'Città creata con successo'
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Errore nella creazione della città']);
}
?>