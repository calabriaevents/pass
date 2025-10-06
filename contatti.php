<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$form_submitted = false;
$form_error = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verifica del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Errore di validazione CSRF.');
    }

    // 2. Sanitizzazione e validazione degli input
    $name = sanitize($_POST['name'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $message = sanitize($_POST['message'] ?? '');

    if (!empty($name) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL) && !empty($message)) {
        // Qui andrebbe il codice per inviare l'email
        // Per ora, mostriamo solo un messaggio di successo
        $form_submitted = true;
    } else {
        $form_error = true;
    }
}

// Genera un nuovo token CSRF per il form
$_SESSION['csrf_token'] = bin2hex(random_bytes(32));
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
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <?php if ($form_submitted): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">Grazie!</p>
                    <p>Il tuo messaggio è stato inviato con successo. Ti risponderemo al più presto.</p>
                </div>
            <?php else: ?>
                <p class="text-gray-600 mb-6">Hai domande, suggerimenti o vuoi semplicemente salutarci? Utilizza il modulo sottostante per metterti in contatto con noi.</p>
                <?php if ($form_error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p>Per favore, compila tutti i campi correttamente.</p>
                    </div>
                <?php endif; ?>
                <form action="contatti.php" method="POST">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
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
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
