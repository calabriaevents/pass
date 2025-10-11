<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Privacy Policy</h1>
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <p class="text-gray-600 leading-relaxed">
                In questa pagina si descrivono le modalità di gestione del sito in riferimento al trattamento dei dati personali degli utenti che lo consultano.
                <br><br>
                L'informativa è resa solo per il sito di Passione Calabria e non anche per altri siti web eventualmente consultati dall'utente tramite link.
                <br><br>
                <strong>Titolare del trattamento</strong>
                <br>
                Il Titolare del trattamento è Passione Calabria, con sede in Calabria, Italia.
                <br><br>
                <strong>Tipi di dati trattati</strong>
                <br>
                I dati personali raccolti da questo sito, in modo autonomo o tramite terze parti, sono: Cookie, Dati di utilizzo, Email e Nome.
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
