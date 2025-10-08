<?php
// Imposta gli header per la risposta JSON e per il CORS
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Accetta solo richieste di tipo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'error' => 'Metodo non consentito.']);
    exit;
}

// Includi le classi necessarie
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // <-- Includiamo il nostro processore di immagini

try {
    // --- 1. Validazione degli Input ---
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $article_id = isset($_POST['article_id']) ? intval($_POST['article_id']) : null;
    $province_id = isset($_POST['province_id']) ? intval($_POST['province_id']) : null;
    
    // Controlla che i campi di testo obbligatori non siano vuoti
    if (empty($user_name) || empty($user_email) || empty($description)) {
        throw new Exception('Nome, email e descrizione sono obbligatori.');
    }
    
    // Valida il formato dell'email
    if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('L\'indirizzo email inserito non è valido.');
    }
    
    // Assicurati che sia stato fornito un ID per l'articolo o per la provincia
    if (!$article_id && !$province_id) {
        throw new Exception('È necessario specificare un articolo o una provincia di riferimento.');
    }
    
    // --- 2. Validazione del File Caricato ---
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Errore durante il caricamento della foto. Assicurati di aver selezionato un file.');
    }
    
    $file = $_FILES['photo'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    
    // Controlla la dimensione del file
    if ($file['size'] > $maxSize) {
        throw new Exception('La foto è troppo grande. La dimensione massima consentita è 5MB.');
    }
    
    // Controlla che il tipo MIME sia tra quelli permessi
    $fileMimeType = mime_content_type($file['tmp_name']);
    if (!in_array($fileMimeType, $allowedMimeTypes)) {
        throw new Exception('Formato file non supportato. Sono consentiti solo JPG, PNG, GIF e WebP.');
    }
    
    // --- 3. Elaborazione dell'Immagine ---
    // Crea un'istanza del nostro processore di immagini
    $imageProcessor = new ImageProcessor();
    
    // Processa l'immagine. La classe si occuperà di ridimensionare, convertire e salvare il file.
    // La cartella di destinazione sarà 'uploads_protected/user-experiences/'
    $relativePath = $imageProcessor->processUploadedImage($file, 'user-experiences', 1200);
    
    if (!$relativePath) {
        throw new Exception('Si è verificato un errore tecnico durante l\'elaborazione dell\'immagine.');
    }
    
    // --- 4. Salvataggio nel Database ---
    $db = new Database();
    $pdo = $db->pdo; // Usa la proprietà pubblica pdo

    $stmt = $pdo->prepare("
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
    
    // Esegui la query per inserire i dati nel database
    $success = $stmt->execute([
        $article_id, 
        $province_id, 
        $user_name, 
        $user_email, 
        $relativePath, // <-- Usiamo il nuovo percorso relativo sicuro
        $file['name'], // Salviamo comunque il nome originale per riferimento
        $description
    ]);

    if (!$success) {
        // Se l'inserimento nel database fallisce, dobbiamo eliminare l'immagine appena caricata
        $imageProcessor->deleteImage($relativePath);
        throw new Exception('Errore durante il salvataggio dei dati nel database.');
    }
    
    $uploadId = $pdo->lastInsertId();
    
    // --- 5. Risposta di Successo ---
    echo json_encode([
        'success' => true,
        'message' => 'Foto caricata con successo! Sarà pubblicata dopo l\'approvazione del nostro staff.',
        'upload_id' => $uploadId,
        'file_path' => $relativePath
    ]);
    
} catch (Exception $e) {
    // In caso di qualsiasi errore, invia una risposta JSON con il messaggio di errore
    http_response_code(400); // Bad Request
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>