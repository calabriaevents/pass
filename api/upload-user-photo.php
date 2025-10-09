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
require_once '../includes/image_processor.php'; // Aggiunto Image Processor

try {
    // Validazione input
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : null;
    $province_id = isset($_POST['province_id']) ? intval($_POST['province_id']) : null;
    
    // Validazione campi obbligatori
    if (empty($user_name) || empty($user_email) || empty($description)) {
        throw new Exception('Nome, email e descrizione sono obbligatori');
    }
    
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Indirizzo email non valido');
    }
    
    if (!$article_id && !$province_id) {
        throw new Exception('Specificare articolo o provincia');
    }
    
    // Validazione file
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Errore nel caricamento della foto');
    }
    
    $file = $_FILES['photo'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    
    if ($file['size'] > $maxSize) {
        throw new Exception('La foto è troppo grande. Massimo 5MB consentiti');
    }
    
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime_type = $finfo->file($file['tmp_name']);

    if (!in_array($mime_type, $allowedTypes)) {
        throw new Exception('Formato file non supportato. Usa JPG, PNG o WebP');
    }

    // --- NUOVA GESTIONE IMMAGINE CON IMAGEPROCESSOR ---
    $imageProcessor = new ImageProcessor();
    $relativePath = $imageProcessor->processUploadedImage($file, 'user-experiences');

    if (!$relativePath) {
        throw new Exception('Errore nel salvataggio della foto');
    }
    
    // Salva nel database
    $db = new Database();
    
    $stmt = $db->pdo->prepare("
        INSERT INTO user_uploads (
            article_id, 
            province_id, 
            user_name, 
            user_email, 
            image_path, 
            original_filename, 
            description, 
            status, 
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    if (!$stmt->execute([
        $article_id, 
        $province_id, 
        $user_name, 
        $user_email, 
        $relativePath, 
        $file['name'], 
        $description
    ])) {
        // Rimuovi il file se il database fallisce
        $imageProcessor->deleteImage($relativePath);
        throw new Exception('Errore nel salvataggio dei dati');
    }
    
    $uploadId = $db->pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Foto caricata con successo! Sarà pubblicata dopo la moderazione.',
        'upload_id' => $uploadId,
        'file_path' => $relativePath
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>