<?php
// File: api.php

ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');

session_start();

set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

try {
    header('Content-Type: application/json');

    // Use the main project's database connection
    require_once '../../includes/db_config.php';
    require_once '../../includes/database_mysql.php';
    $db = new Database();
    $pdo = $db->pdo;

    $action = isset($_GET['action']) ? $_GET['action'] : '';
    $method = $_SERVER['REQUEST_METHOD'];

    if ($method === 'GET') {
        switch ($action) {
            case 'get_events': handleGetEvents($pdo); break;
            case 'get_events_by_ids': handleGetEventsByIds($pdo); break;
            case 'get_activities': handleGetActivities($pdo); break;
            case 'get_config': handleGetConfig($pdo); break;
            case 'get_dashboard_stats': handleGetDashboardStats($pdo); break;
            case 'get_locations': handleGetLocations($pdo); break;
            case 'logout': handleLogout(); break;
            case 'track_visit': handleTrackVisit($pdo); break;
            default: echo json_encode(['error' => 'Azione GET non valida']); http_response_code(400);
        }
    } elseif ($method === 'POST') {
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $data = $_POST;
        }

        switch ($action) {
            case 'save_event': check_admin_auth(); handleSaveEvent($pdo, $_POST); break;
            case 'delete_event': check_admin_auth(); handleDeleteEvent($pdo, $data); break;
            case 'save_activity': check_admin_auth(); handleSaveActivity($pdo, $_POST); break;
            case 'delete_activity': check_admin_auth(); handleDeleteActivity($pdo, $data); break;
            case 'save_settings': check_admin_auth(); handleSaveSettings($pdo, $_POST); break;
            default: echo json_encode(['error' => 'Azione POST non valida']); http_response_code(400);
        }
    } else {
        echo json_encode(['error' => 'Metodo non supportato']);
        http_response_code(405);
    }

} catch (Throwable $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    error_log("Errore API: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
    echo json_encode(['error' => 'Errore interno del server.']);
    exit;
}

function check_admin_auth() {
    if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        http_response_code(403);
        echo json_encode(['error' => 'Accesso non autorizzato. Effettua il login.']);
        exit;
    }
}

function handleFileUpload($fileKey, $existingUrl) {
    if (isset($_FILES[$fileKey]) && $_FILES[$fileKey]['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../../eventi/immagini/';
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
            return ['url' => 'eventi/immagini/' . $dateDir . '/' . $fileName];
        } else {
            return ['error' => "Errore durante il caricamento del file."];
        }
    }
    return ['url' => $existingUrl];
}

function handleTrackVisit($pdo) {
    $stmt = $pdo->prepare("INSERT INTO visitor_stats (stat_date, daily_visits) VALUES (CURDATE(), 1) ON DUPLICATE KEY UPDATE daily_visits = daily_visits + 1");
    $stmt->execute();
    echo json_encode(['success' => true]);
    exit;
}

function handleGetLocations($pdo) {
    $sql = "SELECT DISTINCT provincia, citta FROM plugin_eventi WHERE dataEvento >= CURDATE() ORDER BY provincia, citta";
    $stmt = $pdo->query($sql);
    $locations = [];
    $provinces = ['Cosenza', 'Catanzaro', 'Reggio di Calabria', 'Crotone', 'Vibo Valentia'];

    foreach ($provinces as $province) {
        $locations[$province] = [];
    }

    $results = $stmt->fetchAll();
    if ($results) {
        foreach ($results as $row) {
            if (isset($locations[$row['provincia']])) {
                $locations[$row['provincia']][] = $row['citta'];
            }
        }
    }
    echo json_encode($locations);
}

function handleGetEvents($pdo) {
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

    $totalSql = "SELECT COUNT(*) as total FROM plugin_eventi " . $whereSql;
    $stmtTotal = $pdo->prepare($totalSql);
    $stmtTotal->execute($params);
    $total = $stmtTotal->fetchColumn();

    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 10;
    $offset = ($page - 1) * $limit;

    $dataSql = "SELECT * FROM plugin_eventi " . $whereSql . " ORDER BY dataEvento ASC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;

    $stmtData = $pdo->prepare($dataSql);
    $stmtData->execute($params);
    $events = $stmtData->fetchAll();

    echo json_encode(['events' => $events, 'total' => $total]);
}

function handleGetEventsByIds($pdo) {
    if (empty($_GET['ids'])) { echo json_encode([]); return; }
    $ids = explode(',', $_GET['ids']);
    $ids = array_map('intval', $ids);
    if (empty($ids)) { echo json_encode([]); return; }

    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $sql = "SELECT * FROM plugin_eventi WHERE id IN ($placeholders) AND dataEvento >= CURDATE()";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($ids);
    $events = $stmt->fetchAll();
    echo json_encode($events);
}

function handleGetActivities($pdo) {
    $sql = "SELECT * FROM activities";
    $stmt = $pdo->query($sql);
    $activities = $stmt->fetchAll();
    echo json_encode($activities);
}

function handleGetConfig($pdo) {
    $sql = "SELECT setting_key, setting_value FROM config";
    $stmt = $pdo->query($sql);
    $config = [];
    $results = $stmt->fetchAll();
    if ($results) {
        foreach ($results as $row) {
            $config[$row['setting_key']] = $row['setting_value'];
        }
    }
    echo json_encode($config);
}

function handleGetDashboardStats($pdo) {
    $stats = [];

    $stats['active_events'] = $pdo->query("SELECT COUNT(*) FROM plugin_eventi WHERE dataEvento >= CURDATE()")->fetchColumn();
    $stats['active_activities'] = $pdo->query("SELECT COUNT(*) FROM activities WHERE dataFineVisualizzazione >= CURDATE()")->fetchColumn();
    $stats['total_visits'] = $pdo->query("SELECT SUM(daily_visits) FROM visitor_stats")->fetchColumn() ?? 0;
    $stats['current_month_visits'] = $pdo->query("SELECT SUM(daily_visits) FROM visitor_stats WHERE YEAR(stat_date) = YEAR(CURDATE()) AND MONTH(stat_date) = MONTH(CURDATE())")->fetchColumn() ?? 0;
    $stats['previous_month_visits'] = $pdo->query("SELECT SUM(daily_visits) FROM visitor_stats WHERE YEAR(stat_date) = YEAR(CURDATE() - INTERVAL 1 MONTH) AND MONTH(stat_date) = MONTH(CURDATE() - INTERVAL 1 MONTH)")->fetchColumn() ?? 0;

    echo json_encode($stats);
}

function handleSaveEvent($pdo, $data) {
    $provincia = isset($data['provincia']) ? trim($data['provincia']) : '';
    $citta = isset($data['citta']) ? trim($data['citta']) : '';
    if (!empty($provincia) && !empty($citta)) {
        $stmt_loc = $pdo->prepare("INSERT IGNORE INTO locations (provincia, citta) VALUES (?, ?)");
        $stmt_loc->execute([$provincia, $citta]);
    }

    $uploadResult = handleFileUpload('imageFile', isset($data['hiddenImageUrl']) ? $data['hiddenImageUrl'] : '');
    if (isset($uploadResult['error'])) { http_response_code(500); echo json_encode($uploadResult); return; }
    $imageUrl = $uploadResult['url'];
    $eventId = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : 0;

    if ($eventId > 0) {
        $stmt = $pdo->prepare("UPDATE plugin_eventi SET titolo=?, nomeAttivita=?, descrizione=?, categoria=?, provincia=?, citta=?, dataEvento=?, orarioInizio=?, costoIngresso=?, imageUrl=?, linkMappaGoogle=?, linkPreviewMappaEmbed=?, linkContattoPrenotazioni=? WHERE id=?");
        $params = [$data['titolo'], $data['nomeAttivita'], $data['descrizione'], $data['categoria'], $data['provincia'], $data['citta'], $data['dataEvento'], $data['orarioInizio'], $data['costoIngresso'], $imageUrl, $data['linkMappaGoogle'], $data['linkPreviewMappaEmbed'], $data['linkContattoPrenotazioni'], $eventId];
    } else {
        $stmt = $pdo->prepare("INSERT INTO plugin_eventi (titolo, nomeAttivita, descrizione, categoria, provincia, citta, dataEvento, orarioInizio, costoIngresso, imageUrl, linkMappaGoogle, linkPreviewMappaEmbed, linkContattoPrenotazioni) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $params = [$data['titolo'], $data['nomeAttivita'], $data['descrizione'], $data['categoria'], $data['provincia'], $data['citta'], $data['dataEvento'], $data['orarioInizio'], $data['costoIngresso'], $imageUrl, $data['linkMappaGoogle'], $data['linkPreviewMappaEmbed'], $data['linkContattoPrenotazioni']];
    }

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'id' => $eventId > 0 ? $eventId : $pdo->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Salvataggio evento fallito']);
    }
}

function handleDeleteEvent($pdo, $data) {
    $eventId = isset($data['id']) ? intval($data['id']) : 0;
    if ($eventId > 0) {
        $stmt_select = $pdo->prepare("SELECT imageUrl FROM plugin_eventi WHERE id = ?");
        $stmt_select->execute([$eventId]);
        $row = $stmt_select->fetch();
        if($row){
            if(!empty($row['imageUrl']) && file_exists('../../' . $row['imageUrl'])){
                unlink('../../' . $row['imageUrl']);
            }
        }

        $stmt_delete = $pdo->prepare("DELETE FROM plugin_eventi WHERE id = ?");
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

function handleSaveActivity($pdo, $data) {
    $uploadResult = handleFileUpload('logoFile', isset($data['hiddenLogoUrl']) ? $data['hiddenLogoUrl'] : '');
    if (isset($uploadResult['error'])) { http_response_code(500); echo json_encode($uploadResult); return; }
    $logoUrl = $uploadResult['url'];
    $activityId = isset($data['id']) && !empty($data['id']) ? intval($data['id']) : 0;

    if ($activityId > 0) {
        $stmt = $pdo->prepare("UPDATE activities SET nomeAttivita=?, linkDestinazione=?, logoUrl=?, dataFineVisualizzazione=? WHERE id=?");
        $params = [$data['nomeAttivita'], $data['linkDestinazione'], $logoUrl, $data['dataFineVisualizzazione'], $activityId];
    } else {
        $stmt = $pdo->prepare("INSERT INTO activities (nomeAttivita, linkDestinazione, logoUrl, dataFineVisualizzazione) VALUES (?, ?, ?, ?)");
        $params = [$data['nomeAttivita'], $data['linkDestinazione'], $logoUrl, $data['dataFineVisualizzazione']];
    }

    if ($stmt->execute($params)) {
        echo json_encode(['success' => true, 'id' => $activityId > 0 ? $activityId : $pdo->lastInsertId()]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Salvataggio attività fallito']);
    }
}

function handleDeleteActivity($pdo, $data) {
    $activityId = isset($data['id']) ? intval($data['id']) : 0;
    if ($activityId > 0) {
        $stmt_select = $pdo->prepare("SELECT logoUrl FROM activities WHERE id = ?");
        $stmt_select->execute([$activityId]);
        $row = $stmt_select->fetch();
        if($row){
            if(!empty($row['logoUrl']) && file_exists('../../' . $row['logoUrl'])){
                unlink('../../' . $row['logoUrl']);
            }
        }

        $stmt_delete = $pdo->prepare("DELETE FROM activities WHERE id = ?");
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

function handleSaveSettings($pdo, $data) {
    $logoAppUrl = isset($data['hiddenLogoAppUrl']) ? $data['hiddenLogoAppUrl'] : '';

    if (isset($_FILES['logoAppFile']) && $_FILES['logoAppFile']['error'] === UPLOAD_ERR_OK) {
        $logoDir = '../../eventi/logo/';
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
            $logoAppUrl = 'eventi/logo/app-logo.' . $file_extension;
        }
    }

    $settings = [
        'logoAppUrl' => $logoAppUrl,
        'linkInstagram' => $data['linkInstagram'],
        'linkFacebook' => $data['linkFacebook'],
        'linkSitoWeb' => $data['linkSitoWeb'],
        'linkIscriviAttivita' => $data['linkIscriviAttivita']
    ];

    $stmt = $pdo->prepare("INSERT INTO config (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($settings as $key => $value) {
        $stmt->execute([$key, $value]);
    }

    echo json_encode(['success' => true]);
}
?>