<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$form_submitted = false;
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        // Qui andrebbe il codice per inviare l'email
        // Per ora, mostriamo solo un messaggio di successo
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
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Contatti</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <?php if ($form_submitted): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p class="font-bold">Grazie!</p>
                        <p>Il tuo messaggio è stato inviato con successo. Ti risponderemo al più presto.</p>
                    </div>
                <?php else: ?>
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Inviaci un messaggio</h2>
                    <p class="text-gray-600 mb-6">Hai domande, suggerimenti o vuoi semplicemente salutarci? Utilizza il modulo sottostante.</p>
                    <?php if ($form_error): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                            <p>Per favore, compila tutti i campi correttamente.</p>
                        </div>
                    <?php endif; ?>
                    <form action="contatti.php" method="POST">
                        <div class="mb-4">
                            <label for="name" class="block text-gray-700 font-bold mb-2">Nome</label>
                            <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                            <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg" required>
                        </div>
                        <div class="mb-4">
                            <label for="message" class="block text-gray-700 font-bold mb-2">Messaggio</label>
                            <textarea name="message" id="message" rows="5" class="w-full px-3 py-2 border rounded-lg" required></textarea>
                        </div>
                        <div class="text-center">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Invia Messaggio</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
            <div class="bg-white p-8 rounded-lg shadow-lg">
                <h2 class="text-2xl font-bold text-gray-800 mb-6">I nostri recapiti</h2>
                <div class="space-y-6">
                    <div class="flex items-start">
                        <i data-lucide="map-pin" class="w-6 h-6 mr-4 text-blue-600 flex-shrink-0 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Indirizzo</h3>
                            <p class="text-gray-600">Via Giovanni Falcone e Paolo Borsellino, 3, 87027 Paola CS</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i data-lucide="phone" class="w-6 h-6 mr-4 text-blue-600 flex-shrink-0 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Telefono</h3>
                            <p class="text-gray-600">334 507 5668</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <i data-lucide="mail" class="w-6 h-6 mr-4 text-blue-600 flex-shrink-0 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-lg text-gray-800">Email</h3>
                            <p class="text-gray-600">info@passionecalabria.it</p>
                        </div>
                    </div>
                </div>
                <div class="mt-8">
                    <iframe class="rounded-lg" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.936184820103!2d16.036974076362178!3d39.357681719413705!2m3!1f0!2f0!3f0!3m2!i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4c9f6f9b12b%3A0xf2d6f1fb3382c373!2sVia%20Giovanni%20Falcone%20e%20Paolo%20Borsellino%2C%203%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1760446048739!5m2!1sit!2sit" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
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
