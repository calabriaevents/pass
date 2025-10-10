<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termini di Servizio - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Termini di Servizio</h1>
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <p class="text-gray-600 leading-relaxed">
                L'accesso e l'utilizzo del sito Passione Calabria sono soggetti ai seguenti Termini di Servizio. L'utilizzo del sito implica l'accettazione di tutti i termini e le condizioni qui riportate.
                <br><br>
                <strong>Utilizzo del sito</strong>
                <br>
                L'utente si impegna a utilizzare il sito in conformità con la legge e i presenti Termini di Servizio. È vietato ogni utilizzo che possa danneggiare o compromettere il funzionamento del sito.
                <br><br>
                <strong>Proprietà intellettuale</strong>
                <br>
                Tutti i contenuti del sito, inclusi testi, immagini, e grafica, sono di proprietà di Passione Calabria e sono protetti dalle leggi sul diritto d'autore.
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
