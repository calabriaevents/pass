<?php
// File: api/events.php
// MODIFIED TO USE PDO AND THE EXISTING DATABASE CLASS

// 1. PRIMA si configurano le impostazioni del cookie di sessione
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

// 2. DOPO si fa partire la sessione
session_start();

// --- GESTIONE ERRORI GLOBALE ---
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    // --- Header di Sicurezza ---
    header('Content-Type: application/json');

    // --- MODIFIED: Use the existing Database class (PDO) ---
    require_once '../includes/database_mysql.php';
    $db = new Database();

    // Check for connection failure
    if (!$db->isConnected()) {
        throw new Exception('Failed to connect to the database.');
    }

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        switch ($action) {
            case 'get_events': handleGetEvents($db); break;
            case 'get_events_by_ids': handleGetEventsByIds($db); break;
            case 'get_activities': handleGetActivities($db); break;
            case 'get_config': handleGetConfig($db); break;
            case 'get_dashboard_stats': check_admin_auth(); handleGetDashboardStats($db); break;
            case 'get_locations': handleGetLocations($db); break;
            case 'logout': handleLogout(); break;
            case 'track_visit': handleTrackVisit($db); break;
            default: echo json_encode(['error' => 'Azione GET non valida']); http_response_code(400);
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        switch ($action) {
            case 'login': handleLoginStub(); break;
            case 'save_event': check_admin_auth(); handleSaveEvent($db, $_POST); break;
            case 'delete_event': check_admin_auth(); handleDeleteEvent($db, $data); break;
            case 'save_activity': check_admin_auth(); handleSaveActivity($db, $_POST); break;
            case 'delete_activity': check_admin_auth(); handleDeleteActivity($db, $data); break;
            case 'save_settings': check_admin_auth(); handleSaveSettings($db, $_POST); break;
            default: echo json_encode(['error' => 'Azione POST non valida']); http_response_code(400);
        }
    } else {
        echo json_encode(['error' => 'Metodo non supportato']);
        http_response_code(405);
    }

    // No need to close connection, __destruct handles it.

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    error_log("Errore API: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode(['error' => 'Errore interno del server.']);
    exit;
}

// --- Funzioni di Sicurezza e Autenticazione (ADATTATE) ---

function check_admin_auth() {
    // Dipende dalla sessione del tuo pannello di amministrazione principale
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(['error' => 'Accesso non autorizzato. Effettua il login nel pannello admin.']);
        exit;
    }
}

function handleLoginStub() {
    http_response_code(405);
    echo json_encode(['error' => 'La funzionalità di login è gestita dal pannello amministratore principale.']);
}

function handleLogout() {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout effettuato con successo.']);
}

// --- Funzione Helper per l'Upload di File (MODIFICATA) ---

function handleFileUpload($fileKey, $existingUrl) {
    // PERCORSO AGGIORNATO per usare la cartella protetta
    $uploadDir = '../uploads_protected/eventi/';

    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {

        $dateDir = date('d-m-Y');
        $fullDir = $uploadDir . $dateDir . '/';

        // Crea la cartella se non esiste
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0775, true)) {
                return ['error' => "Impossibile creare la cartella: " . $fullDir];
            }
        }
        $fileName = time() . '_' . basename($_FILES[$fileKey]['name']);
        $targetFile = $fullDir . $fileName;

        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFile)) {
            // Ritorna il percorso relativo dalla root del progetto (es. 'uploads_protected/eventi/...')
            // Rimuoviamo il '../' per il percorso salvato nel DB
            $dbUrl = str_replace('../', '', $targetFile);
            return ['url' => $dbUrl];
        } else {
            return ['error' => "Errore durante il caricamento del file."];
        }
    }
    return ['url' => $existingUrl];
}

// --- Funzioni GET (Lettura Dati) ---

function handleTrackVisit($db) {
    $stmt = $db->pdo->prepare("INSERT INTO visitor_stats (stat_date, daily_visits) VALUES (CURDATE(), 1) ON DUPLICATE KEY UPDATE daily_visits = daily_visits + 1");
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

function handleGetLocations($db) {
    $sql = "SELECT DISTINCT provincia, citta FROM events WHERE dataEvento >= CURDATE() ORDER BY provincia, citta";
    $stmt = $db->pdo->query($sql);
    $locations = [];
    $provinces = ['Cosenza', 'Catanzaro', 'Reggio di Calabria', 'Crotone', 'Vibo Valentia'];

    foreach ($provinces as $province) {
        $locations[$province] = [];
    }

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        foreach($results as $row) {
            if (isset($locations[$row['provincia']])) {
                if (!in_array($row['citta'], $locations[$row['provincia']])) {
                    $locations[$row['provincia']][] = $row['citta'];
                }
            }
        }
    }
    echo json_encode($locations);
}

function handleGetEvents($db) {
    $params = [];
    $whereClauses = ["dataEvento >= CURDATE()"];
    if (!empty($_GET['category']) && $_GET['category'] !== 'Tutto') {
        $whereClauses[] = "categoria = ?"; $params[] = $_GET['category'];
    }
    if (!empty($_GET['province'])) {
        $whereClauses[] = "provincia = ?"; $params[] = $_GET['province'];
    }
    if (!empty($_GET['city'])) {
        $whereClauses[] = "citta = ?"; $params[] = $_GET['city'];
    }
    if (!empty($_GET['date'])) {
        $whereClauses[] = "DATE(dataEvento) = ?"; $params[] = $_GET['date'];
    }
    if (!empty($_GET['searchTerm'])) {
        $whereClauses[] = "(titolo LIKE ? OR descrizione LIKE ?)";
        $searchTerm = '%' . $_GET['searchTerm'] . '%';
        $params[] = $searchTerm; $params[] = $searchTerm;
    }

    $whereSql = "";
    if (!empty($whereClauses)) {
        $whereSql = "WHERE " . implode(' AND ', $whereClauses);
    }

    $totalSql = "SELECT COUNT(*) FROM events " . $whereSql;
    $stmtTotal = $db->pdo->prepare($totalSql);
    $stmtTotal->execute($params);
    $total = $stmtTotal->fetchColumn() ?: 0;

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

    $dataSql = "SELECT * FROM events " . $whereSql . " ORDER BY dataEvento ASC LIMIT ? OFFSET ?";

    $stmtData = $db->pdo->prepare($dataSql);

    $i = 1;
    foreach ($params as $param) {
        $stmtData->bindValue($i++, $param);
    }
    $stmtData->bindValue($i++, $limit, PDO::PARAM_INT);
    $stmtData->bindValue($i++, $offset, PDO::PARAM_INT);

    $stmtData->execute();
    $events = $stmtData->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['events' => $events, 'total' => $total]);
}

function handleGetEventsByIds($db) {
    if (empty($_GET['ids'])) { echo json_encode([]); return; }
    $ids = explode(',', $_GET['ids']);
    $ids = array_map('intval', $ids);
    if (empty($ids)) { echo json_encode([]); return; }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM events WHERE id IN ($placeholders) AND dataEvento >= CURDATE()";

    $stmt = $db->pdo->prepare($sql);
    $stmt->execute($ids);
    $events = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($events);
}

function handleGetActivities($db) {
    $sql = "SELECT * FROM activities WHERE dataFineVisualizzazione >= CURDATE() ORDER BY created_at DESC";
    $stmt = $db->pdo->query($sql);
    $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($activities);
}

function handleGetConfig($db) {
    $sql = "SELECT setting_key, setting_value FROM settings"; // Usiamo la tabella settings di Passione Calabria
    $stmt = $db->pdo->query($sql);
    $config = [];
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($results) {
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $config[$row['setting_key']] = $row['setting_value'];
        }
    }
    echo json_encode($config);
}

function handleGetDashboardStats($db) {
    $stats = [];

    $result_events = $db->pdo->query("SELECT COUNT(*) FROM events WHERE dataEvento >= CURDATE()");
    $stats['active_events'] = $result_events ? $result_events->fetchColumn() : 0;

    $result_activities = $db->pdo->query("SELECT COUNT(*) FROM activities WHERE dataFineVisualizzazione >= CURDATE()");
    $stats['active_activities'] = $result_activities ? $result_activities->fetchColumn() : 0;

    $result_total_visits = $db->pdo->query("SELECT SUM(daily_visits) FROM visitor_stats");
    $stats['total_visits'] = $result_total_visits ? $result_total_visits->fetchColumn() ?? 0 : 0;

    $result_current_month = $db->pdo->query("SELECT SUM(daily_visits) FROM visitor_stats WHERE YEAR(stat_date) = YEAR(CURDATE()) AND MONTH(stat_date) = MONTH(CURDATE())");
    $stats['current_month_visits'] = $result_current_month ? $result_current_month->fetchColumn() ?? 0 : 0;

    $result_prev_month = $db->pdo->query("SELECT SUM(daily_visits) FROM visitor_stats WHERE YEAR(stat_date) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(stat_date) = MONTH(CURDATE() - INTERVAL 1 MONTH)");
    $stats['previous_month_visits'] = $result_prev_month ? $result_prev_month->fetchColumn() ?? 0 : 0;

    echo json_encode($stats);
}

// --- Funzioni POST (Scrittura/Modifica Dati) - Richiedono check_admin_auth() ---

function handleSaveEvent($db, $data) {
    $uploadResult = handleFileUpload('imageFile', isset($data['hiddenImageUrl']) ? $data['hiddenImageUrl'] : '');
    if (isset($uploadResult['error'])) { http_response_code(500); echo json_encode($uploadResult); return; }
    $imageUrl = $uploadResult['url'];
    $eventId = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : 0;

    $params = [
        $data['titolo'], $data['nomeAttivita'], $data['descrizione'], $data['categoria'],
        $data['provincia'], $data['citta'], $data['dataEvento'], $data['orarioInizio'],
        $data['costoIngresso'], $imageUrl, $data['linkMappaGoogle'],
        $data['linkPreviewMappaEmbed'], $data['linkContattoPrenotazioni']
    ];

    if ($eventId > 0) {
        $stmt = $db->pdo->prepare("UPDATE events SET titolo=?, nomeAttivita=?, descrizione=?, categoria=?, provincia=?, citta=?, dataEvento=?, orarioInizio=?, costoIngresso=?, imageUrl=?, linkMappaGoogle=?, linkPreviewMappaEmbed=?, linkContattoPrenotazioni=? WHERE id=?");
        $params[] = $eventId;
    } else {
        $stmt = $db->pdo->prepare("INSERT INTO events (titolo, nomeAttivita, descrizione, categoria, provincia, citta, dataEvento, orarioInizio, costoIngresso, imageUrl, linkMappaGoogle, linkPreviewMappaEmbed, linkContattoPrenotazioni) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    }

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'id' => $eventId > 0 ? $eventId : $db->pdo->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Salvataggio evento fallito.']);
    }
}

function handleDeleteEvent($db, $data) {
    $eventId = isset($data['id']) ? intval($data['id']) : 0;
    if ($eventId > 0) {
        $stmt_select = $db->pdo->prepare("SELECT imageUrl FROM events WHERE id = ?");
        $stmt_select->execute([$eventId]);
        $row = $stmt_select->fetch(PDO::FETCH_ASSOC);
        if($row){
            $filePath = '../' . $row['imageUrl'];
            if(!empty($row['imageUrl']) && file_exists($filePath)){
                unlink($filePath);
            }
        }

        $stmt_delete = $db->pdo->prepare("DELETE FROM events WHERE id = ?");
        if ($stmt_delete->execute([$eventId])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Eliminazione evento fallita']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID evento non valido']);
    }
}

function handleSaveActivity($db, $data) {
    $uploadResult = handleFileUpload('logoFile', isset($data['hiddenLogoUrl']) ? $data['hiddenLogoUrl'] : '');
    if (isset($uploadResult['error'])) { http_response_code(500); echo json_encode($uploadResult); return; }
    $logoUrl = $uploadResult['url'];
    $activityId = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : 0;

    $params = [$data['nomeAttivita'], $data['linkDestinazione'], $logoUrl, $data['dataFineVisualizzazione']];

    if ($activityId > 0) {
        $stmt = $db->pdo->prepare("UPDATE activities SET nomeAttivita=?, linkDestinazione=?, logoUrl=?, dataFineVisualizzazione=? WHERE id=?");
        $params[] = $activityId;
    } else {
        $stmt = $db->pdo->prepare("INSERT INTO activities (nomeAttivita, linkDestinazione, logoUrl, dataFineVisualizzazione) VALUES (?, ?, ?, ?)");
    }

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'id' => $activityId > 0 ? $activityId : $db->pdo->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Salvataggio attività fallito.']);
    }
}

function handleDeleteActivity($db, $data) {
    $activityId = isset($data['id']) ? intval($data['id']) : 0;
    if ($activityId > 0) {
        $stmt_select = $db->pdo->prepare("SELECT logoUrl FROM activities WHERE id = ?");
        $stmt_select->execute([$activityId]);
        $row = $stmt_select->fetch(PDO::FETCH_ASSOC);
        if($row){
             $filePath = '../' . $row['logoUrl'];
            if(!empty($row['logoUrl']) && file_exists($filePath)){
                unlink($filePath);
            }
        }

        $stmt_delete = $db->pdo->prepare("DELETE FROM activities WHERE id = ?");
        if ($stmt_delete->execute([$activityId])) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Eliminazione attività fallita']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID attività non valido']);
    }
}

function handleSaveSettings($db, $data) {
    $logoAppUrl = isset($data['hiddenLogoAppUrl']) ? $data['hiddenLogoAppUrl'] : '';

    if (isset($_FILES['logoAppFile']) && $_FILES['logoAppFile']['error'] === UPLOAD_ERR_OK) {
        $logoDir = '../uploads_protected/logo/';
        if (!is_dir($logoDir)) {
            mkdir($logoDir, 0775, true);
        }

        $old_logos = glob($logoDir . "app-logo.*");
        if ($old_logos) {
            foreach ($old_logos as $old_logo) {
                unlink($old_logo);
            }
        }

        $file_extension = strtolower(pathinfo($_FILES['logoAppFile']['name'], PATHINFO_EXTENSION));
        $new_logo_path = $logoDir . 'app-logo.' . $file_extension;

        if (move_uploaded_file($_FILES['logoAppFile']['tmp_name'], $new_logo_path)) {
            $logoAppUrl = str_replace('../', '', $new_logo_path);
        }
    }

    $settings = [
        'logoAppUrl' => $logoAppUrl,
        'linkInstagram' => $data['linkInstagram'],
        'linkFacebook' => $data['linkFacebook'],
        'linkSitoWeb' => $data['linkSitoWeb'],
        'linkIscriviAttivita' => $data['linkIscriviAttivita']
    ];

    $stmt = $db->pdo->prepare("INSERT INTO settings (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    echo json_encode(['success' => true]);
}
?>