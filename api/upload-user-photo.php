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
    
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Formato file non supportato. Usa JPG, PNG o WebP');
    }
    
    // Verifica se è realmente un'immagine
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        throw new Exception('Il file caricato non è una immagine valida');
    }
    
    // Crea directory se non esiste
    $uploadDir = '../uploads/user-experiences/';
    if (!file_exists($uploadDir)) {
        if (!mkdir($uploadDir, 0755, true)) {
            throw new Exception('Impossibile creare la directory di upload');
        }
    }
    
    // Genera nome file unico
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (empty($fileExtension)) {
        $fileExtension = 'jpg'; // default
    }
    
    $fileName = uniqid('user_exp_', true) . '.' . $fileExtension;
    $filePath = $uploadDir . $fileName;
    $relativePath = 'uploads/user-experiences/' . $fileName;
    
    // Sposta il file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        throw new Exception('Errore nel salvataggio della foto');
    }
    
    // Ridimensiona l'immagine se troppo grande
    if ($imageInfo[0] > 1200 || $imageInfo[1] > 1200) {
        resizeImage($filePath, $filePath, 1200, 1200);
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
        unlink($filePath);
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

/**
 * Ridimensiona un'immagine mantenendo le proporzioni
 */
function resizeImage($sourcePath, $destPath, $maxWidth, $maxHeight) {
    $imageInfo = getimagesize($sourcePath);
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Calcola le nuove dimensioni
    $ratio = min($maxWidth / $width, $maxHeight / $height);
    $newWidth = intval($width * $ratio);
    $newHeight = intval($height * $ratio);
    
    // Crea l'immagine source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    // Crea l'immagine destinazione
    $dest = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserva la trasparenza per PNG
    if ($type == IMAGETYPE_PNG) {
        imagealphablending($dest, false);
        imagesavealpha($dest, true);
        $transparent = imagecolorallocatealpha($dest, 255, 255, 255, 127);
        imagefilledrectangle($dest, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Ridimensiona
    imagecopyresampled($dest, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Salva
    $result = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $result = imagejpeg($dest, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            $result = imagepng($dest, $destPath, 6);
            break;
        case IMAGETYPE_WEBP:
            $result = imagewebp($dest, $destPath, 80);
            break;
    }
    
    imagedestroy($source);
    imagedestroy($dest);
    
    return $result;
}
?>