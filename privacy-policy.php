<?php
// privacy-policy.php
session_start();
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

// Percorso al file di contenuto
$content_file = __DIR__ . '/partials/static_content/privacy-policy.html';
$page_content = '';

// Carica il contenuto dal file HTML se esiste
if (file_exists($content_file)) {
    $page_content = file_get_contents($content_file);
} else {
    // Contenuto di fallback se il file non viene trovato
    $page_content = '<p>Contenuto non disponibile. Torna a visitare la pagina più tardi.</p>';
}
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
            <?php echo $page_content; // Stampa il contenuto caricato dinamicamente ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
