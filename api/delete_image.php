<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php';
require_once '../admin/auth_check.php'; // Usa l'auth check dell'admin

header('Content-Type: application/json');

$db = new Database();
$imageProcessor = new ImageProcessor();
$data = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($data['image_path'], $data['source'], $data['source_id'])) {
    echo json_encode(['success' => false, 'error' => 'Richiesta non valida.']);
    exit;
}

$image_path = $data['image_path'];
$source = $data['source'];
$source_id = (int)$data['source_id'];

try {
    // 1. Rimuovi il riferimento dal database
    $db_success = $db->deleteImageReference($source, $source_id, $image_path);

    if (!$db_success) {
        throw new Exception('Impossibile rimuovere il riferimento dell\'immagine dal database.');
    }

    // 2. Elimina il file fisico
    // Assicurati che il percorso sia relativo alla root del progetto
    $full_path = realpath(__DIR__ . '/../') . '/' . $image_path;

    if ($imageProcessor->deleteImage($full_path)) {
         echo json_encode(['success' => true]);
    } else {
         // Anche se l'eliminazione del file fallisce, potremmo voler considerare l'operazione un successo
         // se il riferimento DB è stato rimosso, per evitare file orfani nel DB.
         // Registra l'errore per un controllo manuale.
         error_log("Impossibile eliminare il file: " . $full_path);
         echo json_encode(['success' => true, 'warning' => 'Riferimento DB eliminato, ma il file fisico non è stato trovato o non è stato possibile eliminarlo.']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>