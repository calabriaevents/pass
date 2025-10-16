<?php
// File: db_connect.php

// Credenziali aggiornate per il nuovo database IONOS
$servername = "db5018286094.hosting-data.io"; // Il tuo NOME HOST
$username = "dbu1998090";                   // Il tuo NOME UTENTE
$password = "Bastarda692@@7c007c71@@";           // La tua PASSWORD
$dbname = "dbs14497088";                   // Il tuo NOME DATABASE

// Crea la connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    // Termina lo script e mostra un errore generico in formato JSON
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    die(json_encode(['error' => 'Connessione al database fallita.']));
}

// Imposta la codifica dei caratteri a utf8mb4 (la scelta migliore)
$conn->set_charset("utf8mb4");
