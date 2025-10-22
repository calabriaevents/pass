<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

session_start();

$form_submitted = false;
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        // This is where the email sending logic would go.
        // For now, we just show a success message.
        $form_submitted = true;
    } else {
        $form_error = true;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contatti - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-12">Contatti</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Colonna sinistra: Mappa e Info -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Dove Trovarci</h2>
                <div class="aspect-w-16 aspect-h-9 mb-6">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3107.1234567890123!2d16.0825!3d39.365!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133f8d3c55555555%3A0x1234567890abcdef!2sVia%20Giovanni%20Falcone%20e%20Paolo%20Borsellino%2C%203%2C%20Paola%20CS!5e0!3m2!1sit!2sit!4v1620000000000!5m2!1sit!2sit"
                        width="100%"
                        height="300"
                        style="border:0;"
                        allowfullscreen=""
                        loading="lazy">
                    </iframe>
                </div>
                <h3 class="text-xl font-bold text-gray-800 mb-4">I Nostri Contatti</h3>
                <?php
                $content_file = 'partials/static_content/contatti.html';
                if (file_exists($content_file)) {
                    readfile($content_file);
                } else {
                    echo '<p class="text-red-500">Informazioni di contatto non disponibili.</p>';
                }
                ?>
            </div>

            <!-- Colonna destra: Form -->
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Invia un Messaggio</h2>
                <?php if ($form_submitted): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p class="font-bold">Grazie!</p>
                        <p>Il tuo messaggio è stato inviato con successo. Ti risponderemo al più presto.</p>
                    </div>
                <?php else: ?>
                    <p class="text-gray-600 mb-6">Hai domande o suggerimenti? Compila il modulo.</p>
                    <?php if ($form_error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p>Per favore, compila tutti i campi correttamente.</p>
                        </div>
                    <?php endif; ?>
                    <form action="contatti.php" method="POST">
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-bold mb-2">Nome</label>
                            <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                            <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="block text-gray-700 font-bold mb-2">Messaggio</label>
                            <textarea name="message" id="message" rows="5" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-transform transform hover:scale-105">Invia Messaggio</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
