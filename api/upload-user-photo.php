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

// Includi le classi necessarie
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // <-- NUOVO: Includi ImageProcessor

try {
    // Validazione input (invariata)
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : null;
    $province_id = isset($_POST['province_id']) ? intval($_POST['province_id']) : null;
    
    // Validazione campi obbligatori (invariata)
    if (empty($user_name) || empty($user_email) || empty($description)) {
        throw new Exception('Nome, email e descrizione sono obbligatori');
    }
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Indirizzo email non valido');
    }
    if (!$article_id && !$province_id) {
        throw new Exception('Specificare articolo o provincia');
    }
    
    // Validazione file (invariata)
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Errore nel caricamento della foto');
    }
    
    $file = $_FILES['photo'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    
    if ($file['size'] > $maxSize) {
        throw new Exception('La foto è troppo grande. Massimo 5MB consentiti');
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!in_array($mime_type, $allowedMimeTypes)) {
        throw new Exception('Formato file non supportato. Usa JPG, PNG, GIF o WebP');
    }

    // --- NUOVA LOGICA DI UPLOAD ---
    $imageProcessor = new ImageProcessor(); // Utilizza la classe
    $relativePath = $imageProcessor->processUploadedImage($file, 'user-experiences', 1200);

    if ($relativePath === null) {
        throw new Exception('Errore durante l\'elaborazione dell\'immagine.');
    }
    // --- FINE NUOVA LOGICA ---

    // Salva nel database
    $db = new Database();
    $stmt = $db->pdo->prepare("
        INSERT INTO user_uploads (
            article_id, province_id, user_name, user_email,
            image_path, original_filename, description, status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
    ");
    
    // Esegui la query usando il nuovo $relativePath
    if (!$stmt->execute([
        $article_id, $province_id, $user_name, $user_email,
        $relativePath, // <-- Ora contiene il percorso del file .webp
        htmlspecialchars($file['name']),
        $description
    ])) {
        // Se il DB fallisce, cancella l'immagine appena creata
        $imageProcessor->deleteImage($relativePath);
        throw new Exception('Errore nel salvataggio dei dati nel database');
    }
    
    $uploadId = $db->pdo->lastInsertId();
    
    echo json_encode([
        'success' => true,
        'message' => 'Foto caricata con successo! Sarà pubblicata dopo la moderazione.',
        'upload_id' => $uploadId,
        'file_path' => $relativePath // Restituisce il nuovo percorso
    ]);
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

// La vecchia funzione resizeImage() non è più necessaria e può essere rimossa.
?>