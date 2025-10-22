<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse([
        'success' => false,
        'message' => 'Metodo non consentito'
    ], 405);
}

try {
    $db = new Database();

    // Ottieni dati dal form
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $name = sanitize($_POST['name'] ?? '');
    $interests = $_POST['interests'] ?? [];

    // Validazione
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        jsonResponse([
            'success' => false,
            'message' => 'Indirizzo email non valido'
        ], 400);
    }

    // Controlla se l'email è già registrata
    $stmt = $db->pdo->prepare('SELECT id FROM newsletter_subscribers WHERE email = ?');
    $stmt->execute([$email]);
    $existing = $stmt->fetch();

    if ($existing) {
        jsonResponse([
            'success' => false,
            'message' => 'Questo indirizzo email è già iscritto alla newsletter'
        ], 400);
    }

    // Crea tabella newsletter se non esiste
    $db->pdo->exec("
        CREATE TABLE IF NOT EXISTS newsletter_subscribers (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            email TEXT NOT NULL UNIQUE,
            name TEXT,
            interests TEXT,
            status TEXT DEFAULT 'active',
            confirmation_token TEXT,
            confirmed_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Genera token di conferma
    $confirmationToken = bin2hex(random_bytes(32));

    // Inserisci nuovo iscritto
    $stmt = $db->pdo->prepare('
        INSERT INTO newsletter_subscribers (email, name, interests, confirmation_token)
        VALUES (?, ?, ?, ?)
    ');

    $interestsJson = json_encode($interests);
    $stmt->execute([$email, $name, $interestsJson, $confirmationToken]);

    $subscriberId = $db->pdo->lastInsertId();

    // Invia email di conferma (simulata)
    $confirmationLink = SITE_URL . "/conferma-newsletter.php?token=$confirmationToken";

    // Qui andrebbe l'invio della email reale
    // Per ora logghiamo il link di conferma
    error_log("Newsletter confirmation link for $email: $confirmationLink");

    // Log dell'iscrizione
    error_log("Nuova iscrizione newsletter: $email (ID: $subscriberId)");

    jsonResponse([
        'success' => true,
        'message' => 'Iscrizione avvenuta con successo! Controlla la tua email per confermare.',
        'subscriber_id' => $subscriberId,
        'confirmation_required' => true
    ]);

} catch (PDOException $e) {
    error_log('Errore database newsletter: ' . $e->getMessage());

    if ($e->getCode() == 23000) { // Constraint violation
        jsonResponse([
            'success' => false,
            'message' => 'Questo indirizzo email è già iscritto alla newsletter'
        ], 400);
    }

    jsonResponse([
        'success' => false,
        'message' => 'Errore durante l\'iscrizione. Riprova più tardi.'
    ], 500);

} catch (Exception $e) {
    error_log('Errore API newsletter: ' . $e->getMessage());

    jsonResponse([
        'success' => false,
        'message' => 'Errore interno del server'
    ], 500);
}

// Funzione per inviare email di conferma (placeholder)
function sendConfirmationEmail($email, $name, $confirmationLink) {
    // Qui andrebbe l'implementazione dell'invio email
    // Usando PHPMailer, SwiftMailer, o servizi come SendGrid, Mailgun, etc.

    $subject = 'Conferma la tua iscrizione a Passione Calabria';
    $message = "
        Ciao " . ($name ?: 'amico') . ",

        Grazie per esserti iscritto alla newsletter di Passione Calabria!

        Per completare l'iscrizione, clicca sul link seguente:
        $confirmationLink

        Se non hai richiesto questa iscrizione, ignora questa email.

        A presto,
        Il team di Passione Calabria
    ";

    // Simulazione invio email
    return true;
}

// Funzione per gestire la disiscrizione
function unsubscribe($email, $token = null) {
    global $db;

    try {
        if ($token) {
            // Disiscrizione tramite token
            $stmt = $db->pdo->prepare('
                UPDATE newsletter_subscribers
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE email = ? AND confirmation_token = ?
            ');
            $stmt->execute(['unsubscribed', $email, $token]);
        } else {
            // Disiscrizione diretta
            $stmt = $db->pdo->prepare('
                UPDATE newsletter_subscribers
                SET status = ?, updated_at = CURRENT_TIMESTAMP
                WHERE email = ?
            ');
            $stmt->execute(['unsubscribed', $email]);
        }

        return $stmt->rowCount() > 0;

    } catch (Exception $e) {
        error_log('Errore disiscrizione newsletter: ' . $e->getMessage());
        return false;
    }
}

// API endpoint per disiscrizione
if (isset($_GET['action']) && $_GET['action'] === 'unsubscribe') {
    $email = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL);
    $token = $_GET['token'] ?? '';

    if (!$email) {
        jsonResponse([
            'success' => false,
            'message' => 'Email non valida'
        ], 400);
    }

    if (unsubscribe($email, $token)) {
        jsonResponse([
            'success' => true,
            'message' => 'Disiscrizione avvenuta con successo'
        ]);
    } else {
        jsonResponse([
            'success' => false,
            'message' => 'Errore durante la disiscrizione'
        ], 500);
    }
}

// API endpoint per conferma iscrizione
if (isset($_GET['action']) && $_GET['action'] === 'confirm') {
    $token = $_GET['token'] ?? '';

    if (empty($token)) {
        jsonResponse([
            'success' => false,
            'message' => 'Token di conferma mancante'
        ], 400);
    }

    try {
        $stmt = $db->pdo->prepare('
            UPDATE newsletter_subscribers
            SET status = ?, confirmed_at = CURRENT_TIMESTAMP, updated_at = CURRENT_TIMESTAMP
            WHERE confirmation_token = ? AND status = ?
        ');
        $stmt->execute(['confirmed', $token, 'active']);

        if ($stmt->rowCount() > 0) {
            jsonResponse([
                'success' => true,
                'message' => 'Iscrizione confermata con successo!'
            ]);
        } else {
            jsonResponse([
                'success' => false,
                'message' => 'Token non valido o già utilizzato'
            ], 400);
        }

    } catch (Exception $e) {
        error_log('Errore conferma newsletter: ' . $e->getMessage());
        jsonResponse([
            'success' => false,
            'message' => 'Errore durante la conferma'
        ], 500);
    }
}
?>
