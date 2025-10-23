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
            <?php
            $content_file = 'partials/static_content/termini-servizio.html';
            if (file_exists($content_file)) {
                echo file_get_contents($content_file);
            } else {
                echo '<p class="text-red-500">Contenuto non trovato. Configurare la pagina nell\'area di amministrazione.</p>';
            }
            ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
