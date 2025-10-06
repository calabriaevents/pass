<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$form_submitted = false;
$form_error = false;
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Verifica del token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        die('Errore di validazione CSRF.');
    }

    // Funzione per il processamento sicuro delle immagini
    function processAndSaveImage($file) {
        if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) return null;
        if (!extension_loaded('gd') || !function_exists('imagecreatefromstring')) return null;
        $sourceImage = @imagecreatefromstring(file_get_contents($file['tmp_name']));
        if ($sourceImage === false) return null;

        $newFileName = 'suggestion_' . uniqid() . bin2hex(random_bytes(4)) . '.webp';
        $destinationPath = SECURE_UPLOAD_PATH . $newFileName;

        if (imagewebp($sourceImage, $destinationPath, 80)) {
            imagedestroy($sourceImage);
            return $newFileName;
        }
        imagedestroy($sourceImage);
        return null;
    }

    // 2. Sanitizzazione e validazione degli input
    $place_name = sanitize($_POST['place_name'] ?? '');
    $location = sanitize($_POST['location'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $user_name = sanitize($_POST['user_name'] ?? '');
    $user_email = sanitize($_POST['user_email'] ?? '');
    $image_paths = [];

    // Gestione dell'upload sicuro delle immagini
    if (isset($_FILES['place_images']) && !empty($_FILES['place_images']['name'][0])) {
        foreach ($_FILES['place_images']['tmp_name'] as $key => $tmp_name) {
            $file = [
                'name' => $_FILES['place_images']['name'][$key],
                'type' => $_FILES['place_images']['type'][$key],
                'tmp_name' => $tmp_name,
                'error' => $_FILES['place_images']['error'][$key],
                'size' => $_FILES['place_images']['size'][$key]
            ];
            if ($new_filename = processAndSaveImage($file)) {
                $image_paths[] = $new_filename;
            }
        }
    }

    if (!empty($place_name) && !empty($location) && !empty($description) && !empty($user_name) && !empty($user_email) && filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $db = new Database();
        $images_json = !empty($image_paths) ? json_encode($image_paths) : null;
        $db->createPlaceSuggestion($place_name, $description, $location, $user_name, $user_email, $images_json);
        $form_submitted = true;
    } else {
        $form_error = true;
        $error_message = 'Per favore, compila tutti i campi correttamente.';
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
    <title>Suggerisci un Luogo - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Suggerisci un Luogo</h1>
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-lg shadow-lg">
            <?php if ($form_submitted): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                    <p class="font-bold">Grazie per il tuo suggerimento!</p>
                    <p>Il nostro team lo esaminerà al più presto.</p>
                </div>
            <?php else: ?>
                <p class="text-gray-600 mb-6">Conosci un luogo un Monumento una spiaggia speciale in Calabria che dovremmo assolutamente includere nel nostro portale? Segnalacelo compilando il modulo qui sotto!</p>
                <?php if ($form_error): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                        <p><?php echo $error_message; ?></p>
                    </div>
                <?php endif; ?>
                <form action="suggerisci.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <div class="mb-4">
                        <label for="place_name" class="block text-gray-700 font-bold mb-2">Nome del Luogo</label>
                        <input type="text" name="place_name" id="place_name" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label for="location" class="block text-gray-700 font-bold mb-2">Località (es. Comune, Provincia)</label>
                        <input type="text" name="location" id="location" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 font-bold mb-2">Descrizione</label>
                        <textarea name="description" id="description" rows="5" class="w-full px-3 py-2 border rounded-lg" required></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="place_images" class="block text-gray-700 font-bold mb-2">Carica Immagini (opzionale)</label>
                        <input type="file" name="place_images[]" id="place_images" class="w-full px-3 py-2 border rounded-lg" multiple accept="image/jpeg, image/png, image/gif">
                        <p class="text-sm text-gray-500 mt-1">Puoi selezionare più immagini. Dimensione massima per file: 5MB.</p>
                    </div>
                    <hr class="my-6">
                    <div class="mb-4">
                        <label for="user_name" class="block text-gray-700 font-bold mb-2">Il tuo Nome</label>
                        <input type="text" name="user_name" id="user_name" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div class="mb-4">
                        <label for="user_email" class="block text-gray-700 font-bold mb-2">La tua Email</label>
                        <input type="email" name="user_email" id="user_email" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <div class="text-center">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Invia Suggerimento</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
