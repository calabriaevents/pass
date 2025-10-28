<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$form_submitted = false;
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        // Logica invio email (omessa per ora)
        $form_submitted = true;
    } else {
        $form_error = true;
    }
}

// Carica il contenuto testuale
$content_file = __DIR__ . '/partials/static_content/contatti.html';
$page_content = file_exists($content_file) ? file_get_contents($content_file) : '';
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
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Contatti</h1>

        <div class="max-w-6xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-12">

                <!-- Colonna Sinistra: Mappa e Info -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Dove Trovarci</h2>

                    <!-- Mappa Google Maps -->
                    <div class="aspect-w-16 aspect-h-9 mb-6">
                        <iframe
                            src="https://maps.google.com/maps?q=via%20giovanni%20falcone%20e%20paolo%20borsellino%203%20paola%20CS&t=&z=15&ie=UTF8&iwloc=&output=embed"
                            width="100%"
                            height="350"
                            style="border:0;"
                            allowfullscreen=""
                            loading="lazy">
                        </iframe>
                    </div>

                    <h3 class="text-xl font-bold text-gray-800 mb-2">I Nostri Contatti</h3>
                    <div class="text-gray-600 space-y-2">
                        <p><strong>Indirizzo:</strong> Via Giovanni Falcone e Paolo Borsellino, 3, Paola (CS)</p>
                        <p><strong>Email:</strong> info@passionecalabria.it</p>
                        <p><strong>Cell:</strong> 3888111556</p>
                    </div>
                </div>

                <!-- Colonna Destra: Form di Contatto -->
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Scrivici</h2>
                    <?php if ($form_submitted): ?>
                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                            <p class="font-bold">Grazie!</p>
                            <p>Il tuo messaggio è stato inviato. Ti risponderemo al più presto.</p>
                        </div>
                    <?php else: ?>
                        <?php echo $page_content; // Contenuto testuale modificabile ?>

                        <?php if ($form_error): ?>
                            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                                <p>Per favore, compila tutti i campi correttamente.</p>
                            </div>
                        <?php endif; ?>
                        <form action="contatti.php" method="POST">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 font-bold mb-2">Nome</label>
                                <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="mb-4">
                                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required>
                            </div>
                            <div class="mb-4">
                                <label for="message" class="block text-gray-700 font-bold mb-2">Messaggio</label>
                                <textarea name="message" id="message" rows="5" class="w-full px-3 py-2 border rounded-lg focus:ring-blue-500 focus:border-blue-500" required></textarea>
                            </div>
                            <div class="text-center">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Invia Messaggio</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>

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
