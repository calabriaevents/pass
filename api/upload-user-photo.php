<?php
session_start();
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Includi la classe ImageProcessor

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Utente non autenticato.']);
    exit;
}

if (isset($_FILES['user_photo'])) {
    $db = new Database();
    $imageProcessor = new ImageProcessor(); // Istanzia ImageProcessor
    
    $user_id = $_SESSION['user_id'];
    // Validazione input
    $article_id = isset($_POST['article_id']) && !empty($_POST['article_id']) ? intval($_POST['article_id']) : null;
    $city_id = isset($_POST['city_id']) && !empty($_POST['city_id']) ? intval($_POST['city_id']) : null;

    if ($article_id === null && $city_id === null) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'ID articolo o città mancante.']);
        exit;
    }

    $processed_path = $imageProcessor->processUploadedImage($_FILES['user_photo'], 'user_photos');

    if ($processed_path) {
        // Nota: la tabella 'user_photos' e il metodo 'addUserPhoto' devono esistere nel DB.
        // Se la tabella si chiama 'user_uploads', il codice va adattato.
        // Assumiamo che 'addUserPhoto' gestisca l'inserimento in una tabella come 'user_photos'.
        $photo_id = $db->addUserPhoto($user_id, $article_id, $city_id, $processed_path);

        if ($photo_id) {
            echo json_encode([
                'success' => true,
                'message' => 'Foto caricata con successo! Sarà visibile dopo l\'approvazione.',
                'photo_id' => $photo_id,
                'file_path' => $processed_path
            ]);
        } else {
            $imageProcessor->deleteImage($processed_path); // Elimina l'immagine se il DB fallisce
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Errore nel salvataggio della foto nel database.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Errore durante il caricamento o l\'elaborazione dell\'immagine. Assicurati sia un formato valido (JPG, PNG).']);
    }
} else {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nessuna foto ricevuta. Assicurati di inviare il file nel campo "user_photo".']);
}