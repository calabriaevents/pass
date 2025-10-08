<?php
header('Content-Type: application/json');
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

$response = ['success' => false, 'message' => 'Richiesta non valida.'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get raw POST data
    $json_data = file_get_contents('php://input');
    $data = json_decode($json_data, true);

    // --- Validation ---
    if (empty($data)) {
        $response['message'] = 'Nessun dato ricevuto.';
        echo json_encode($response);
        exit;
    }

    $article_id = filter_var($data['article_id'] ?? null, FILTER_VALIDATE_INT);
    $rating = filter_var($data['rating'] ?? null, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 5]]);
    $author_name = filter_var(trim($data['author_name'] ?? ''), FILTER_SANITIZE_STRING);
    $author_email = filter_var(trim($data['author_email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $content = filter_var(trim($data['content'] ?? ''), FILTER_SANITIZE_STRING);

    if (!$article_id || !$rating || empty($author_name) || !$author_email || empty($content)) {
        $response['message'] = 'Dati non validi o mancanti. Assicurati di compilare tutti i campi.';
        if(empty($author_name)) $response['message'] = 'Il nome è obbligatorio.';
        if(!$author_email) $response['message'] = 'L\'email non è valida.';
        if(empty($content)) $response['message'] = 'Il commento non può essere vuoto.';
        if(!$rating) $response['message'] = 'La valutazione deve essere tra 1 e 5.';

        echo json_encode($response);
        exit;
    }

    // --- Database Operation ---
    try {
        $db = new Database();
        if ($db->isConnected()) {
            $success = $db->createComment($article_id, $author_name, $author_email, $content, $rating);
            if ($success) {
                $response['success'] = true;
                $response['message'] = 'Grazie! La tua recensione è stata inviata e sarà pubblicata dopo l\'approvazione.';
            } else {
                $response['message'] = 'Si è verificato un errore durante il salvataggio della recensione.';
            }
        } else {
            $response['message'] = 'Errore di connessione al database.';
        }
    } catch (Exception $e) {
        error_log('Error in submit_review.php: ' . $e->getMessage());
        $response['message'] = 'Si è verificato un errore del server.';
    }

    echo json_encode($response);
    exit;
}

echo json_encode($response);
?>
