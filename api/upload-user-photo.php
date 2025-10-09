<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php';

try {
    // Validazione input
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : null;
    $province_id = isset($_POST['province_id']) ? intval($_POST['province_id']) : null;
    
    // ... (validazione campi obbligatori invariata) ...
    
    // Validazione file
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Errore nel caricamento della foto, assicurati di averne scelta una.');
    }
    
    // --- GESTIONE IMMAGINE CON CONTROLLO ERRORI ---
    $imageProcessor = new ImageProcessor();
    $relativePath = $imageProcessor->processUploadedImage($_FILES['photo'], 'user-experiences');

    if (!$relativePath) {
        // Lancia un'eccezione con l'errore specifico da ImageProcessor
        throw new Exception($imageProcessor->getLastError() ?: 'Errore sconosciuto durante l\'elaborazione della foto.');
    }
    
    // Salva nel database
    $db = new Database();
    
    $stmt = $db->pdo->prepare("
        INSERT INTO user_uploads (
            article_id, province_id, user_name, user_email, image_path,
            original_filename, description, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    if (!$stmt->execute([
        $article_id, $province_id, $user_name, $user_email, $relativePath,
        $_FILES['photo']['name'], $description
    ])) {
        // Se il DB fallisce, cancella l'immagine appena caricata
        $imageProcessor->deleteImage($relativePath);
        throw new Exception('Errore nel salvataggio dei dati nel database.');
    }
    
    $uploadId = $db->pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Foto caricata con successo! Sarà pubblicata dopo la moderazione.',
        'upload_id' => $uploadId,
        'file_path' => $relativePath
    ]);
    
} catch (Exception $e) {
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>