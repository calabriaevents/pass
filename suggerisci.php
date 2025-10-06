<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
require_once 'includes/image_processor.php'; // Includi il nuovo processore di immagini

// --- Gestione Richieste POST (AJAX) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $db = new Database();
    // Poiché questo script è nella root, il base_dir deve essere corretto
    $imageProcessor = new ImageProcessor('uploads/');

    try {
        $place_name = trim($_POST['place_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $user_name = trim($_POST['user_name'] ?? '');
        $user_email = trim($_POST['user_email'] ?? '');

        // Validazione
        if (empty($place_name) || empty($location) || empty($description) || empty($user_name) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Per favore, compila tutti i campi obbligatori correttamente.');
        }

        $image_paths = [];
        // Gestione upload con ImageProcessor
        if (isset($_FILES['place_images']) && !empty($_FILES['place_images']['name'][0])) {
            foreach ($_FILES['place_images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $file_data = [
                        'name' => $_FILES['place_images']['name'][$key],
                        'type' => $_FILES['place_images']['type'][$key],
                        'tmp_name' => $tmp_name,
                        'error' => $_FILES['place_images']['error'][$key],
                        'size' => $_FILES['place_images']['size'][$key]
                    ];
                    $new_path = $imageProcessor->processUploadedImage($file_data, 'suggestions', 1280);
                    if ($new_path) {
                        $image_paths[] = $new_path;
                    } else {
                        throw new Exception('Errore nel caricamento di un\'immagine: ' . htmlspecialchars($file_data['name']));
                    }
                }
            }
        }

        $images_json = !empty($image_paths) ? json_encode($image_paths) : null;
        if ($db->createPlaceSuggestion($place_name, $description, $location, $user_name, $user_email, $images_json)) {
            // Per la risposta AJAX, inviamo un URL di successo che il JS potrà usare per reindirizzare.
            echo json_encode(['success' => true, 'redirect_url' => 'suggerisci.php?success=1']);
        } else {
            throw new Exception('Si è verificato un errore nel salvataggio del suggerimento.');
        }

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit;
}

// --- Preparazione Dati per la Vista ---
$form_submitted = isset($_GET['success']);
$form_error = false; // L'errore viene gestito da JS, quindi non serve più qui
$error_message = '';
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
    <script src="assets/js/main.js" defer></script>
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
                <form action="suggerisci.php" method="POST" enctype="multipart/form-data">
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
                        <input type="file" name="place_images[]" id="place_images" class="w-full px-3 py-2 border rounded-lg" multiple accept="image/*">
                        <p class="text-sm text-gray-500 mt-1">Puoi selezionare più immagini. Verranno convertite in WebP e ottimizzate.</p>
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
    <script>
        lucide.createIcons();
    </script>
</body>
</html>