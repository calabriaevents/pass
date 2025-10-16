<?php
// File: api.php

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
    // NOTA: Spostati qui perché header() deve essere chiamato prima di qualsiasi output.
    header('Content-Type: application/json');
    require 'db_connect.php';

    // --- CREDENZIALI ADMIN SICURE ---
   define('ADMIN_USERNAME', 'Banana33');
    define('ADMIN_PASSWORD_HASH', '$2y$10$vgvMvBiVtf/MjDbJR5XYXen3wpiroye4PlfMLtgM4pU/169HytTGG'); // <-- IMPORTANTE: INSERISCI QUI IL TUO HASH CORRETTO

    // --- COSTANTI PER IL RATE LIMITING ---
    define('MAX_LOGIN_ATTEMPTS', 5);
    define('LOGIN_ATTEMPT_WINDOW', 15 * 60);
    define('LOCKOUT_DURATION', 30 * 60);

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        switch ($action) {
            case 'get_events': handleGetEvents($conn); break;
            case 'get_events_by_ids': handleGetEventsByIds($conn); break;
            case 'get_activities': handleGetActivities($conn); break;
            case 'get_config': handleGetConfig($conn); break;
            case 'get_dashboard_stats': handleGetDashboardStats($conn); break;
            case 'get_locations': handleGetLocations($conn); break;
            case 'logout': handleLogout(); break;
            case 'track_visit': handleTrackVisit($conn); break;
            default: echo json_encode(['error' => 'Azione GET non valida']); http_response_code(400);
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }
        
        switch ($action) {
            case 'login': handleLogin($conn, $data); break;
            case 'save_event': check_admin_auth(); handleSaveEvent($conn, $_POST); break;
            case 'delete_event': check_admin_auth(); handleDeleteEvent($conn, $data); break;
            case 'save_activity': check_admin_auth(); handleSaveActivity($conn, $_POST); break;
            case 'delete_activity': check_admin_auth(); handleDeleteActivity($conn, $data); break;
            case 'save_settings': check_admin_auth(); handleSaveSettings($conn, $_POST); break;
            default: echo json_encode(['error' => 'Azione POST non valida']); http_response_code(400);
        }
    } else {
        echo json_encode(['error' => 'Metodo non supportato']);
        http_response_code(405);
    }

    $conn->close();

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    error_log("Errore API: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode(['error' => 'Errore interno del server.']);
    exit;
}

// --- Funzioni di Sicurezza e Autenticazione ---

function check_admin_auth() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(['error' => 'Accesso non autorizzato. Effettua il login.']);
        exit;
    }
}

function handleLogin($conn, $data) {
    $ip_address = $_SERVER['REMOTE_ADDR'];
    $conn->query("DELETE FROM login_attempts WHERE attempt_time < NOW() - INTERVAL " . LOGIN_ATTEMPT_WINDOW . " SECOND");
    $stmt = $conn->prepare("SELECT COUNT(*) as attempts FROM login_attempts WHERE ip_address = ?");
    $stmt->bind_param('s', $ip_address);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    if ($result && $result['attempts'] >= MAX_LOGIN_ATTEMPTS) {
        http_response_code(429);
        echo json_encode(['error' => 'Troppi tentativi falliti. Riprova più tardi.']);
        return;
    }

    if (isset($data['username']) && isset($data['password'])) {
        if ($data['username'] === ADMIN_USERNAME && password_verify($data['password'], ADMIN_PASSWORD_HASH)) {
            $stmt_del = $conn->prepare("DELETE FROM login_attempts WHERE ip_address = ?");
            $stmt_del->bind_param('s', $ip_address);
            $stmt_del->execute();
            $_SESSION['is_admin'] = true;
            echo json_encode(['success' => true]);
        } else {
            $stmt_ins = $conn->prepare("INSERT INTO login_attempts (ip_address) VALUES (?)");
            $stmt_ins->bind_param('s', $ip_address);
            $stmt_ins->execute();
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Credenziali non valide']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'Dati di login mancanti']);
    }
}

function handleLogout() {
    session_unset();
    session_destroy();
    echo json_encode(['success' => true, 'message' => 'Logout effettuato con successo.']);
}

// --- Funzione Helper per l'Upload di File ---

function handleFileUpload($fileKey, $existingUrl) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'immagini/';
        $dateDir = date('d-m-Y');
        $fullDir = $uploadDir . $dateDir . '/';
        if (!is_dir($fullDir)) {
            if (!mkdir($fullDir, 0775, true)) {
                return ['error' => "Impossibile creare la cartella: " . $fullDir];
            }
        }
        $fileName = time() . '_' . basename($_FILES[$fileKey]['name']);
        $targetFile = $fullDir . $fileName;
        if (move_uploaded_file($_FILES[$fileKey]['tmp_name'], $targetFile)) {
            return ['url' => $targetFile];
        } else {
            return ['error' => "Errore durante il caricamento del file."];
        }
    }
    return ['url' => $existingUrl];
}

// --- Funzioni GET (Lettura Dati) ---

function handleTrackVisit($conn) {
    $stmt = $conn->prepare("INSERT INTO visitor_stats (stat_date, daily_visits) VALUES (CURDATE(), 1) ON DUPLICATE KEY UPDATE daily_visits = daily_visits + 1");
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

// MODIFICATA: Mostra solo città con eventi attivi
function handleGetLocations($conn) {
    $sql = "SELECT DISTINCT provincia, citta FROM events WHERE dataEvento >= CURDATE() ORDER BY provincia, citta";
    $result = $conn->query($sql);
    $locations = [];
    $provinces = ['Cosenza', 'Catanzaro', 'Reggio di Calabria', 'Crotone', 'Vibo Valentia'];

    // Inizializza tutte le province per assicurarsi che esistano nel JSON anche se vuote
    foreach ($provinces as $province) {
        $locations[$province] = [];
    }

    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Controlla se la provincia esiste prima di aggiungere la città
            if (isset($locations[$row['provincia']])) {
                $locations[$row['provincia']][] = $row['citta'];
            }
        }
    }
    echo json_encode($locations);
}

function handleGetEvents($conn) {
    $params = [];
    $types = '';
    $whereClauses = ["dataEvento >= CURDATE()"];
    if (!empty($_GET['category']) && $_GET['category'] !== 'Tutto') {
        $whereClauses[] = "categoria = ?"; $params[] = $_GET['category']; $types .= 's';
    }
    if (!empty($_GET['province'])) {
        $whereClauses[] = "provincia = ?"; $params[] = $_GET['province']; $types .= 's';
    }
    if (!empty($_GET['city'])) {
        $whereClauses[] = "citta = ?"; $params[] = $_GET['city']; $types .= 's';
    }
    if (!empty($_GET['date'])) {
        $whereClauses[] = "DATE(dataEvento) = ?"; $params[] = $_GET['date']; $types .= 's';
    }
    if (!empty($_GET['searchTerm'])) {
        $whereClauses[] = "(titolo LIKE ? OR descrizione LIKE ?)";
        $searchTerm = '%' . $_GET['searchTerm'] . '%';
        $params[] = $searchTerm; $params[] = $searchTerm; $types .= 'ss';
    }
    
    $whereSql = "";
    if (!empty($whereClauses)) {
        $whereSql = "WHERE " . implode(' AND ', $whereClauses);
    }
    
    $totalSql = "SELECT COUNT(*) as total FROM events " . $whereSql;
    $stmtTotal = $conn->prepare($totalSql);
    if (!empty($params)) { $stmtTotal->bind_param($types, ...$params); }
    $stmtTotal->execute();
    $totalResult = $stmtTotal->get_result();
    $total = $totalResult ? $totalResult->fetch_assoc()['total'] : 0;
    $stmtTotal->close();

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;
    
    $dataSql = "SELECT * FROM events " . $whereSql . " ORDER BY dataEvento ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmtData = $conn->prepare($dataSql);
    $stmtData->bind_param($types, ...$params);
    $stmtData->execute();
    $result = $stmtData->get_result();
    $events = [];
    if ($result) {
        while($row = $result->fetch_assoc()) { $events[] = $row; }
    }
    $stmtData->close();

    echo json_encode(['events' => $events, 'total' => $total]);
}

function handleGetEventsByIds($conn) {
    if (empty($_GET['ids'])) { echo json_encode([]); return; }
    $ids = explode(',', $_GET['ids']);
    $ids = array_map('intval', $ids);
    if (empty($ids)) { echo json_encode([]); return; }
    
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM events WHERE id IN ($placeholders) AND dataEvento >= CURDATE()";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(str_repeat('i', count($ids)), ...$ids);
    $stmt->execute();
    $result = $stmt->get_result();
    $events = [];
    if ($result) {
        while($row = $result->fetch_assoc()) { $events[] = $row; }
    }
    $stmt->close();
    echo json_encode($events);
}

function handleGetActivities($conn) {
    $sql = "SELECT * FROM activities";
    $result = $conn->query($sql);
    $activities = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { $activities[] = $row; }
    }
    echo json_encode($activities);
}

function handleGetConfig($conn) {
    $sql = "SELECT setting_key, setting_value FROM config";
    $result = $conn->query($sql);
    $config = [];
    if ($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) { $config[$row['setting_key']] = $row['setting_value']; }
    }
    echo json_encode($config);
}

function handleGetDashboardStats($conn) {
    $stats = [];
    
    $result_events = $conn->query("SELECT COUNT(*) as count FROM events WHERE dataEvento >= CURDATE()");
    $stats['active_events'] = $result_events ? $result_events->fetch_assoc()['count'] : 0;
    
    $result_activities = $conn->query("SELECT COUNT(*) as count FROM activities WHERE dataFineVisualizzazione >= CURDATE()");
    $stats['active_activities'] = $result_activities ? $result_activities->fetch_assoc()['count'] : 0;
    
    $result_total_visits = $conn->query("SELECT SUM(daily_visits) as count FROM visitor_stats");
    $stats['total_visits'] = $result_total_visits ? $result_total_visits->fetch_assoc()['count'] ?? 0 : 0;

    $result_current_month = $conn->query("SELECT SUM(daily_visits) as count FROM visitor_stats WHERE YEAR(stat_date) = YEAR(CURDATE()) AND MONTH(stat_date) = MONTH(CURDATE())");
    $stats['current_month_visits'] = $result_current_month ? $result_current_month->fetch_assoc()['count'] ?? 0 : 0;

    $result_prev_month = $conn->query("SELECT SUM(daily_visits) as count FROM visitor_stats WHERE YEAR(stat_date) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(stat_date) = MONTH(CURDATE() - INTERVAL 1 MONTH)");
    $stats['previous_month_visits'] = $result_prev_month ? $result_prev_month->fetch_assoc()['count'] ?? 0 : 0;

    echo json_encode($stats);
}

// --- Funzioni POST (Scrittura/Modifica Dati) ---

function handleSaveEvent($conn, $data) {
    $provincia = isset($data['provincia']) ? trim($data['provincia']) : '';
    $citta = isset($data['citta']) ? trim($data['citta']) : '';
    if (!empty($provincia) && !empty($citta)) {
        $stmt_loc = $conn->prepare("INSERT IGNORE INTO locations (provincia, citta) VALUES (?, ?)");
        $stmt_loc->bind_param("ss", $provincia, $citta);
        $stmt_loc->execute();
        $stmt_loc->close();
    }

    $uploadResult = handleFileUpload('imageFile', isset($data['hiddenImageUrl']) ? $data['hiddenImageUrl'] : '');
    if (isset($uploadResult['error'])) { http_response_code(500); echo json_encode($uploadResult); return; }
    $imageUrl = $uploadResult['url'];
    $eventId = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : 0;

    if ($eventId > 0) {
        $stmt = $conn->prepare("UPDATE events SET titolo=?, nomeAttivita=?, descrizione=?, categoria=?, provincia=?, citta=?, dataEvento=?, orarioInizio=?, costoIngresso=?, imageUrl=?, linkMappaGoogle=?, linkPreviewMappaEmbed=?, linkContattoPrenotazioni=? WHERE id=?");
        $stmt->bind_param("sssssssssssssi", $data['titolo'], $data['nomeAttivita'], $data['descrizione'], $data['categoria'], $data['provincia'], $data['citta'], $data['dataEvento'], $data['orarioInizio'], $data['costoIngresso'], $imageUrl, $data['linkMappaGoogle'], $data['linkPreviewMappaEmbed'], $data['linkContattoPrenotazioni'], $eventId);
    } else {
        $stmt = $conn->prepare("INSERT INTO events (titolo, nomeAttivita, descrizione, categoria, provincia, citta, dataEvento, orarioInizio, costoIngresso, imageUrl, linkMappaGoogle, linkPreviewMappaEmbed, linkContattoPrenotazioni) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssssssss", $data['titolo'], $data['nomeAttivita'], $data['descrizione'], $data['categoria'], $data['provincia'], $data['citta'], $data['dataEvento'], $data['orarioInizio'], $data['costoIngresso'], $imageUrl, $data['linkMappaGoogle'], $data['linkPreviewMappaEmbed'], $data['linkContattoPrenotazioni']);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $eventId > 0 ? $eventId : $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Salvataggio evento fallito: ' . $stmt->error]);
    }
    $stmt->close();
}

function handleDeleteEvent($conn, $data) {
    $eventId = isset($data['id']) ? intval($data['id']) : 0;
    if ($eventId > 0) {
        $stmt_select = $conn->prepare("SELECT imageUrl FROM events WHERE id = ?");
        $stmt_select->bind_param("i", $eventId);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        if($row = $result->fetch_assoc()){
            if(!empty($row['imageUrl']) && file_exists($row['imageUrl'])){
                unlink($row['imageUrl']);
            }
        }
        $stmt_select->close();

        $stmt_delete = $conn->prepare("DELETE FROM events WHERE id = ?");
        $stmt_delete->bind_param("i", $eventId);
        if ($stmt_delete->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Eliminazione evento fallita']);
        }
        $stmt_delete->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID evento non valido']);
    }
}

function handleSaveActivity($conn, $data) {
    $uploadResult = handleFileUpload('logoFile', isset($data['hiddenLogoUrl']) ? $data['hiddenLogoUrl'] : '');
    if (isset($uploadResult['error'])) { http_response_code(500); echo json_encode($uploadResult); return; }
    $logoUrl = $uploadResult['url'];
    $activityId = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : 0;

    if ($activityId > 0) {
        $stmt = $conn->prepare("UPDATE activities SET nomeAttivita=?, linkDestinazione=?, logoUrl=?, dataFineVisualizzazione=? WHERE id=?");
        $stmt->bind_param("ssssi", $data['nomeAttivita'], $data['linkDestinazione'], $logoUrl, $data['dataFineVisualizzazione'], $activityId);
    } else {
        $stmt = $conn->prepare("INSERT INTO activities (nomeAttivita, linkDestinazione, logoUrl, dataFineVisualizzazione) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $data['nomeAttivita'], $data['linkDestinazione'], $logoUrl, $data['dataFineVisualizzazione']);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'id' => $activityId > 0 ? $activityId : $conn->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Salvataggio attività fallito: ' . $stmt->error]);
    }
    $stmt->close();
}

function handleDeleteActivity($conn, $data) {
    $activityId = isset($data['id']) ? intval($data['id']) : 0;
    if ($activityId > 0) {
        $stmt_select = $conn->prepare("SELECT logoUrl FROM activities WHERE id = ?");
        $stmt_select->bind_param("i", $activityId);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        if($row = $result->fetch_assoc()){
            if(!empty($row['logoUrl']) && file_exists($row['logoUrl'])){
                unlink($row['logoUrl']);
            }
        }
        $stmt_select->close();

        $stmt_delete = $conn->prepare("DELETE FROM activities WHERE id = ?");
        $stmt_delete->bind_param("i", $activityId);
        if ($stmt_delete->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Eliminazione attività fallita']);
        }
        $stmt_delete->close();
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'ID attività non valido']);
    }
}

function handleSaveSettings($conn, $data) {
    $logoAppUrl = isset($data['hiddenLogoAppUrl']) ? $data['hiddenLogoAppUrl'] : '';

    if (isset($_FILES['logoAppFile']) && $_FILES['logoAppFile']['error'] === UPLOAD_ERR_OK) {
        $logoDir = 'logo/';
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
            $logoAppUrl = $new_logo_path;
        }
    }
    
    $settings = [
        'logoAppUrl' => $logoAppUrl,
        'linkInstagram' => $data['linkInstagram'],
        'linkFacebook' => $data['linkFacebook'],
        'linkSitoWeb' => $data['linkSitoWeb'],
        'linkIscriviAttivita' => $data['linkIscriviAttivita']
    ];

    $stmt = $conn->prepare("INSERT INTO config (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $key => $value) {
        $stmt->bind_param("ss", $key, $value);
        $stmt->execute();
    }
    $stmt->close();
    
    echo json_encode(['success' => true]);
}
?>