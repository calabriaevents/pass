<?php
header('Content-Type: application/json');
session_start(); // Necessario per il controllo dell'autenticazione

// Percorsi relativi per includere i file core di Passione Calabria
require_once '../includes/config.php';
require_once '../includes/database_mysql.php'; // O il tuo file di connessione DB
require_once '../includes/image_processor.php'; // Processore di immagini

// Ottieni la connessione al database
$db = new Database(); // Usa la classe Database di Passione Calabria

$response = ['status' => 'error', 'message' => 'Azione non valida.'];
$action = $_POST['action'] ?? $_GET['action'] ?? '';

// Funzione helper per verificare se l'utente è un admin loggato
function isAdmin() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

// Funzione helper per servire le immagini in modo sicuro
function getSecureImageUrl($relativePath) {
    if (empty($relativePath)) {
        return null; // O un'immagine placeholder
    }
    // L'URL punta allo script che carica l'immagine, non al file diretto
    return "/image-loader.php?path=" . urlencode($relativePath);
}

// Endpoint Pubblici (accessibili da tutti)
if (in_array($action, ['get_events', 'get_activities', 'get_config', 'track_visit'])) {

    switch ($action) {
        case 'get_events':
            $stmt = $db->pdo->prepare("SELECT * FROM events ORDER BY dataEvento DESC");
            $stmt->execute();
            $events = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($events as &$event) {
               $event['imageUrl'] = getSecureImageUrl($event['imageUrl']);
            }

            $response = ['status' => 'success', 'events' => $events, 'total' => count($events)];
            break;

        case 'get_activities':
            $stmt = $db->pdo->prepare("SELECT * FROM activities WHERE dataFineVisualizzazione >= CURDATE() ORDER BY dataCreazione DESC");
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($activities as &$activity) {
                $activity['logoUrl'] = getSecureImageUrl($activity['logoUrl']);
            }

            $response = $activities; // L'originale si aspettava un array diretto
            break;

        case 'get_config':
            $stmt = $db->pdo->prepare("SELECT key_name, value_content FROM app_config");
            $stmt->execute();
            $config_data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            $config_data['logoAppUrl'] = getSecureImageUrl($config_data['logoAppUrl']);
            $response = $config_data; // L'originale si aspettava un oggetto diretto
            break;

        case 'track_visit':
            $stmt = $db->pdo->prepare("INSERT INTO app_visits (visit_timestamp) VALUES (NOW())");
            $stmt->execute();
            $response = ['status' => 'success'];
            break;
    }

}
// Endpoint Protetti (richiedono autenticazione admin)
else if (in_array($action, ['save_event', 'delete_event', 'save_activity', 'delete_activity', 'save_settings', 'get_dashboard_stats', 'login', 'logout'])) {

    if (!isAdmin() && !in_array($action, ['login'])) {
        http_response_code(403); // Forbidden
        $response = ['status' => 'error', 'message' => 'Accesso negato.'];
    } else {
        switch ($action) {
            case 'save_event':
                $id = $_POST['id'] ?? null;
                $imageUrl = $_POST['hiddenImageUrl'] ?? null;

                if (isset($_FILES['imageFile']) && $_FILES['imageFile']['error'] == 0) {
                    $imageProcessor = new ImageProcessor('../uploads_protected/events/');
                    try {
                        $newImagePath = $imageProcessor->processImage($_FILES['imageFile']);
                        $imageUrl = 'events/' . basename($newImagePath);
                    } catch (Exception $e) {
                        $response = ['status' => 'error', 'message' => $e->getMessage()];
                        echo json_encode($response);
                        exit();
                    }
                }

                if ($id) {
                    // Update
                    $stmt = $db->pdo->prepare("UPDATE events SET titolo = ?, nomeAttivita = ?, descrizione = ?, categoria = ?, provincia = ?, citta = ?, dataEvento = ?, orarioInizio = ?, costoIngresso = ?, imageUrl = ?, linkMappaGoogle = ?, linkPreviewMappaEmbed = ?, linkContattoPrenotazioni = ? WHERE id = ?");
                    $stmt->execute([$_POST['titolo'], $_POST['nomeAttivita'], $_POST['descrizione'], $_POST['categoria'], $_POST['provincia'], $_POST['citta'], $_POST['dataEvento'], $_POST['orarioInizio'], $_POST['costoIngresso'], $imageUrl, $_POST['linkMappaGoogle'], $_POST['linkPreviewMappaEmbed'], $_POST['linkContattoPrenotazioni'], $id]);
                } else {
                    // Insert
                    $stmt = $db->pdo->prepare("INSERT INTO events (titolo, nomeAttivita, descrizione, categoria, provincia, citta, dataEvento, orarioInizio, costoIngresso, imageUrl, linkMappaGoogle, linkPreviewMappaEmbed, linkContattoPrenotazioni) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$_POST['titolo'], $_POST['nomeAttivita'], $_POST['descrizione'], $_POST['categoria'], $_POST['provincia'], $_POST['citta'], $_POST['dataEvento'], $_POST['orarioInizio'], $_POST['costoIngresso'], $imageUrl, $_POST['linkMappaGoogle'], $_POST['linkPreviewMappaEmbed'], $_POST['linkContattoPrenotazioni']]);
                }
                $response = ['status' => 'success', 'message' => 'Evento salvato.'];
                break;

            case 'delete_event':
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->pdo->prepare("DELETE FROM events WHERE id = ?");
                $stmt->execute([$data['id']]);
                $response = ['status' => 'success'];
                break;

            case 'save_activity':
                 $id = $_POST['id'] ?? null;
                $logoUrl = $_POST['hiddenLogoUrl'] ?? null;

                if (isset($_FILES['logoFile']) && $_FILES['logoFile']['error'] == 0) {
                    $imageProcessor = new ImageProcessor('../uploads_protected/activities_logos/');
                    try {
                        $newImagePath = $imageProcessor->processImage($_FILES['logoFile']);
                        $logoUrl = 'activities_logos/' . basename($newImagePath);
                    } catch (Exception $e) {
                        $response = ['status' => 'error', 'message' => $e->getMessage()];
                        echo json_encode($response);
                        exit();
                    }
                }

                if ($id) {
                    $stmt = $db->pdo->prepare("UPDATE activities SET nomeAttivita = ?, linkDestinazione = ?, logoUrl = ?, dataFineVisualizzazione = ? WHERE id = ?");
                    $stmt->execute([$_POST['nomeAttivita'], $_POST['linkDestinazione'], $logoUrl, $_POST['dataFineVisualizzazione'], $id]);
                } else {
                    $stmt = $db->pdo->prepare("INSERT INTO activities (nomeAttivita, linkDestinazione, logoUrl, dataFineVisualizzazione) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$_POST['nomeAttivita'], $_POST['linkDestinazione'], $logoUrl, $_POST['dataFineVisualizzazione']]);
                }
                $response = ['status' => 'success', 'message' => 'Attività salvata.'];
                break;

            case 'delete_activity':
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->pdo->prepare("DELETE FROM activities WHERE id = ?");
                $stmt->execute([$data['id']]);
                $response = ['status' => 'success'];
                break;

            case 'save_settings':
                 $logoUrl = $_POST['hiddenLogoAppUrl'] ?? null;
                if (isset($_FILES['logoAppFile']) && $_FILES['logoAppFile']['error'] == 0) {
                     $imageProcessor = new ImageProcessor('../uploads_protected/events/'); // Salva in una cartella generica per i loghi
                    try {
                        $newImagePath = $imageProcessor->processImage($_FILES['logoAppFile']);
                        $logoUrl = 'events/' . basename($newImagePath);
                    } catch (Exception $e) {
                        $response = ['status' => 'error', 'message' => $e->getMessage()];
                        echo json_encode($response);
                        exit();
                    }
                }

                $settings = [
                    'logoAppUrl' => $logoUrl,
                    'linkInstagram' => $_POST['linkInstagram'],
                    'linkFacebook' => $_POST['linkFacebook'],
                    'linkSitoWeb' => $_POST['linkSitoWeb'],
                    'linkIscriviAttivita' => $_POST['linkIscriviAttivita'],
                ];

                foreach ($settings as $key => $value) {
                    $stmt = $db->pdo->prepare("INSERT INTO app_config (key_name, value_content) VALUES (?, ?) ON DUPLICATE KEY UPDATE value_content = ?");
                    $stmt->execute([$key, $value, $value]);
                }
                 $response = ['status' => 'success', 'message' => 'Impostazioni salvate.'];
                break;

            case 'get_dashboard_stats':
                $stats = [];
                $stmt = $db->pdo->query("SELECT COUNT(*) FROM events WHERE dataEvento >= CURDATE()");
                $stats['active_events'] = $stmt->fetchColumn();

                $stmt = $db->pdo->query("SELECT COUNT(*) FROM activities WHERE dataFineVisualizzazione >= CURDATE()");
                $stats['active_activities'] = $stmt->fetchColumn();

                $stmt = $db->pdo->query("SELECT COUNT(*) FROM app_visits");
                $stats['total_visits'] = $stmt->fetchColumn();

                $stmt = $db->pdo->query("SELECT COUNT(*) FROM app_visits WHERE MONTH(visit_timestamp) = MONTH(CURDATE()) AND YEAR(visit_timestamp) = YEAR(CURDATE())");
                $stats['current_month_visits'] = $stmt->fetchColumn();

                $stmt = $db->pdo->query("SELECT COUNT(*) FROM app_visits WHERE MONTH(visit_timestamp) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(visit_timestamp) = YEAR(CURDATE() - INTERVAL 1 MONTH)");
                $stats['previous_month_visits'] = $stmt->fetchColumn();

                $response = $stats;
                break;

            case 'login':
                $data = json_decode(file_get_contents('php://input'), true);
                $stmt = $db->pdo->prepare("SELECT id, username, password, role FROM users WHERE username = ? AND role = 'admin'");
                $stmt->execute([$data['username']]);
                $user = $stmt->fetch();

                if ($user && password_verify($data['password'], $user['password'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['username'];
                    $_SESSION['user_role'] = $user['role'];
                    $response = ['success' => true];
                } else {
                    http_response_code(401);
                    $response = ['success' => false, 'error' => 'Credenziali non valide.'];
                }
                break;

            case 'logout':
                session_destroy();
                $response = ['success' => true];
                break;
        }
    }
}

echo json_encode($response);
$db->close();
