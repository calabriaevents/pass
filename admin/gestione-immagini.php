<?php
require_once __DIR__ . '/auth_check.php';
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

$db = new Database();
$error_message = '';
$success_message = '';

// Gestione eliminazione
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_image') {
    $path = $_POST['path'] ?? '';
    $sourceType = $_POST['source_type'] ?? '';
    $sourceId = $_POST['source_id'] ?? '';

    if ($path && $sourceType && $sourceId) {
        // 1. Elimina il riferimento dal database
        $dbSuccess = $db->deleteImageReference($path, $sourceType, $sourceId);

        if ($dbSuccess) {
            // 2. Elimina il file fisico
            $fullPath = __DIR__ . '/../' . $path;
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
            $success_message = "Immagine eliminata con successo!";
        } else {
            $error_message = "Errore: impossibile eliminare il riferimento dell'immagine dal database.";
        }
    } else {
        $error_message = "Dati mancanti per l'eliminazione.";
    }
}

// Filtri
$searchTerm = $_GET['q'] ?? '';
$sourceTypeFilter = $_GET['source_type'] ?? '';

$allImages = $db->getAllImages($searchTerm, $sourceTypeFilter);
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Immagini - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-100 flex">
    <div class="bg-gray-900 text-white w-64 flex flex-col">
        <div class="p-4 border-b border-gray-700">
             <h1 class="font-bold text-lg">Admin Panel</h1>
        </div>
        <?php include 'partials/menu.php'; ?>
    </div>

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900">Gestione Immagini</h1>
        </header>

        <main class="flex-1 overflow-auto p-6">
            <?php if (!empty($success_message)): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline"><?php echo htmlspecialchars($success_message); ?></span>
            </div>
            <?php endif; ?>
            <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Errore!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-sm">
                <div class="p-4 border-b">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Cerca per nome file o sorgente</label>
                            <input type="text" name="q" value="<?php echo htmlspecialchars($searchTerm); ?>" placeholder="Es: Cosenza, nome-file.jpg..." class="w-full px-3 py-2 border border-gray-300 rounded-md">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo di sorgente</label>
                            <select name="source_type" class="w-full px-3 py-2 border border-gray-300 rounded-md">
                                <option value="">Tutti i tipi</option>
                                <option value="Articolo" <?php echo ($sourceTypeFilter === 'Articolo') ? 'selected' : ''; ?>>Articolo</option>
                                <option value="Città" <?php echo ($sourceTypeFilter === 'Città') ? 'selected' : ''; ?>>Città</option>
                                <option value="Utente" <?php echo ($sourceTypeFilter === 'Utente') ? 'selected' : ''; ?>>Utente</option>
                            </select>
                        </div>
                        <div class="md:col-span-3 flex justify-end gap-2">
                            <a href="gestione-immagini.php" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">Annulla filtri</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg">Filtra</button>
                        </div>
                    </form>
                </div>

                <div class="p-4">
                    <?php if (empty($allImages)): ?>
                        <p class="text-center text-gray-500 py-8">Nessuna immagine trovata con i filtri attuali.</p>
                    <?php else: ?>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                            <?php foreach ($allImages as $image): ?>
                                <div class="group relative border rounded-lg overflow-hidden">
                                    <img src="../image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $image['path'])); ?>" alt="" class="w-full h-32 object-cover">
                                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity p-2 flex flex-col justify-between text-white text-xs">
                                        <div>
                                            <p class="font-bold"><?php echo htmlspecialchars($image['source_type']); ?></p>
                                            <p class="break-words"><?php echo htmlspecialchars($image['source_name']); ?></p>
                                        </div>
                                        <form method="POST" onsubmit="return confirm('Sei sicuro di voler eliminare questa immagine? L\'azione non è reversibile.');">
                                            <input type="hidden" name="action" value="delete_image">
                                            <input type="hidden" name="path" value="<?php echo htmlspecialchars($image['path']); ?>">
                                            <input type="hidden" name="source_type" value="<?php echo htmlspecialchars($image['source_type']); ?>">
                                            <input type="hidden" name="source_id" value="<?php echo htmlspecialchars($image['source_id']); ?>">
                                            <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white rounded-md py-1 mt-1 text-xs">Elimina</button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>