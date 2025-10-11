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
    <title>Chi Siamo - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Chi Siamo</h1>
        <div class="max-w-3xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <p class="text-gray-600 leading-relaxed">
                <strong>Passione Calabria</strong> nasce dall'amore per la nostra terra, una regione ricca di storia, cultura, e bellezze naturali. Il nostro obiettivo è quello di promuovere la Calabria autentica, quella lontana dai soliti cliché, per farla conoscere e apprezzare in tutto il mondo.
                <br><br>
                Siamo un team di giovani calabresi che hanno deciso di mettere in gioco le proprie competenze e la propria passione per creare un portale che sia un punto di riferimento per chiunque voglia scoprire la Calabria.
            </p>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
