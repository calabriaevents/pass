<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Errore sconosciuto.'];

    $place_name = trim($_POST['place_name'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $image_paths = [];

    // Validazione dei campi di testo
    if (empty($place_name) || empty($location) || empty($description) || empty($user_name) || !filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Per favore, compila tutti i campi obbligatori correttamente.';
        echo json_encode($response);
        exit;
    }

    // Gestione dell'upload delle immagini
    if (isset($_FILES['place_images']) && !empty($_FILES['place_images']['name'][0])) {
        $upload_dir = 'uploads/suggestions/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_file_size = 5 * 1024 * 1024; // 5 MB

        foreach ($_FILES['place_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['place_images']['error'][$key] !== UPLOAD_ERR_OK) continue;

            $file_name = $_FILES['place_images']['name'][$key];
            $file_size = $_FILES['place_images']['size'][$key];
            $file_type = $_FILES['place_images']['type'][$key];

            if (!in_array($file_type, $allowed_types)) {
                $response['message'] = "Tipo di file non consentito: " . htmlspecialchars($file_name);
                echo json_encode($response);
                exit;
            }
            if ($file_size > $max_file_size) {
                $response['message'] = "File troppo grande: " . htmlspecialchars($file_name);
                echo json_encode($response);
                exit;
            }

            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_file_name = uniqid('suggestion_', true) . '.' . $file_extension;
            $destination = $upload_dir . $new_file_name;

            if (move_uploaded_file($tmp_name, $destination)) {
                $image_paths[] = $destination;
            } else {
                $response['message'] = 'Errore durante il salvataggio del file: ' . htmlspecialchars($file_name);
                echo json_encode($response);
                exit;
            }
        }
    }

    // Salvataggio nel database
    $db = new Database();
    $images_json = !empty($image_paths) ? json_encode($image_paths) : null;
    $success = $db->createPlaceSuggestion($place_name, $description, $location, $user_name, $user_email, $images_json);

    if ($success) {
        $response = ['success' => true, 'message' => 'Grazie per il tuo suggerimento! Il nostro team lo esaminerà al più presto.'];
    } else {
        $response['message'] = 'Si è verificato un errore durante il salvataggio del suggerimento. Riprova.';
    }

    echo json_encode($response);
    exit;
}

$form_submitted = false;
$form_error = false;
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
                <form action="suggerisci.php" method="POST" enctype="multipart/form-data" class="ajax-form">
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
