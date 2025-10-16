<?php
// File: db_connect.php

// Credenziali per il database locale
$servername = "localhost";
$username = "passione_user";
$password = "password";
$dbname = "passione_calabria";

// Crea la connessione
$conn = new mysqli($servername, $username, $password, $dbname);

// Controlla la connessione
if ($conn->connect_error) {
    // Termina lo script e mostra un errore generico in formato JSON
    header('Content-Type: application/json');
    http_response_code(500); // Internal Server Error
    die(json_encode(['error' => 'Connessione al database fallita: ' . $conn->connect_error]));
}

// Imposta la codifica dei caratteri a utf8mb4 (la scelta migliore)
$conn->set_charset("utf8mb4");
?>