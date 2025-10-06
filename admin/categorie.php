<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();
$imageProcessor = new ImageProcessor();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST (AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $response = ['success' => false, 'message' => 'Azione non valida.'];

    $current_action = $_POST['action'] ?? null;
    $entity_id = $_POST['id'] ?? null;

    // Gestione Aggiunta/Modifica
    if (isset($_POST['name'])) {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $icon = $_POST['current_icon'] ?? '';

        // Se è una modifica, recupera l'icona esistente
        if ($current_action === 'edit' && $entity_id) {
            $existing_category = $db->getCategoryById($entity_id);
            $icon = $existing_category['icon'] ?? '';
        }

        // Gestione upload icona con ImageProcessor
        if (!empty($_FILES['icon']['name'])) {
            $new_icon_path = $imageProcessor->processUploadedImage($_FILES['icon'], 'categories', 128); // 128px max width
            if ($new_icon_path) {
                // Elimina il file precedente se esiste e non è un emoji
                if (!empty($icon) && strpos($icon, 'uploads/') !== false) {
                    $imageProcessor->deleteImage($icon);
                }
                $icon = $new_icon_path;
            } else {
                echo json_encode(['success' => false, 'message' => 'Errore nel caricamento dell\'icona.']);
                exit;
            }
        }

        if ($current_action === 'edit' && $entity_id) {
            $db->updateCategory($entity_id, $name, $description, $icon);
            $response = ['success' => true, 'message' => 'Categoria aggiornata con successo.'];
        } else {
            $db->createCategory($name, $description, $icon);
            $response = ['success' => true, 'message' => 'Categoria creata con successo.'];
        }
    }

    // Gestione Eliminazione
    elseif ($current_action === 'delete_category' && $entity_id) {
        $category = $db->getCategoryById($entity_id);
        if ($category && !empty($category['icon']) && strpos($category['icon'], 'uploads/') !== false) {
            $imageProcessor->deleteImage($category['icon']);
        }
        if ($db->deleteCategory($entity_id)) {
            $response = ['success' => true, 'message' => 'Categoria eliminata con successo.'];
        } else {
            $response = ['success' => false, 'message' => 'Impossibile eliminare la categoria, potrebbero esserci articoli collegati.'];
        }
    }

    echo json_encode($response);
    exit;
}

// Gestione eliminazione (GET request per fallback)
if ($action === 'delete' && $id) {
    $category = $db->getCategoryById($id);
    if ($category && !empty($category['icon']) && strpos($category['icon'], 'uploads/') !== false) {
        $imageProcessor->deleteImage($category['icon']);
    }
    $db->deleteCategory($id);
    header('Location: categorie.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Categorie - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-gray-900 text-white w-64 flex flex-col">
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-yellow-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-sm">PC</span>
                </div>
                <div>
                    <h1 class="font-bold text-lg">Admin Panel</h1>
                    <p class="text-xs text-gray-400">Passione Calabria</p>
                </div>
            </div>
        </div>
        <?php include 'partials/menu.php'; ?>
        <div class="p-4 border-t border-gray-700">
            <a href="../index.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors"><i data-lucide="log-out" class="w-5 h-5"></i><span>Torna al Sito</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Gestione Categorie</h1>
                <?php if ($action === 'list'): ?>
                <a href="categorie.php?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Nuova Categoria</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <div id="notification-placeholder" class="mb-4"></div>
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Categorie</h2>
                <table class="w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2">Icona</th>
                            <th class="text-left py-2">Nome</th>
                            <th class="text-left py-2">Descrizione</th>
                            <th class="text-left py-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $categories = $db->getCategories();
                        foreach ($categories as $category):
                        ?>
                        <tr class="border-b" id="category-row-<?php echo $category['id']; ?>">
                            <td class="py-2">
                                <?php if (strpos($category['icon'], 'uploads/') !== false): ?>
                                    <img src="../<?php echo htmlspecialchars($category['icon']); ?>" alt="Icona" class="w-8 h-8 object-cover rounded">
                                <?php else: ?>
                                    <span class="text-2xl"><?php echo htmlspecialchars($category['icon']); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="py-2"><?php echo htmlspecialchars($category['name']); ?></td>
                            <td class="py-2"><?php echo htmlspecialchars($category['description']); ?></td>
                            <td class="py-2">
                                <a href="categorie.php?action=edit&id=<?php echo $category['id']; ?>" class="text-blue-600 hover:underline">Modifica</a>
                                <button onclick="deleteCategory(<?php echo $category['id']; ?>)" class="text-red-600 hover:underline ml-4">Elimina</button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($action === 'new' || $action === 'edit'):
                $category = null;
                if ($action === 'edit' && $id) {
                    $category = $db->getCategoryById($id);
                }
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4"><?php echo $action === 'edit' ? 'Modifica Categoria' : 'Nuova Categoria'; ?></h2>
                <form id="category-form" action="categorie.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <div class="mb-4">
                        <label for="name" class="block text-gray-700 font-bold mb-2">Nome</label>
                        <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required>
                    </div>
                    <div class="mb-4">
                        <label for="description" class="block text-gray-700 font-bold mb-2">Descrizione</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="icon" class="block text-gray-700 font-bold mb-2">Icona</label>
                        <?php if (!empty($category['icon'])): ?>
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="text-sm text-gray-600">Icona attuale:</div>
                                <?php if (strpos($category['icon'], 'uploads/') !== false): ?>
                                    <img src="../<?php echo htmlspecialchars($category['icon']); ?>" alt="Icona attuale" class="w-8 h-8 object-cover rounded">
                                    <div class="text-sm text-gray-600"><?php echo basename($category['icon']); ?></div>
                                <?php else: ?>
                                    <span class="text-2xl"><?php echo htmlspecialchars($category['icon']); ?></span>
                                    <div class="text-sm text-gray-600">Emoji</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="icon" id="icon" accept="image/*" class="w-full px-3 py-2 border rounded-lg">
                        <input type="hidden" name="current_icon" value="<?php echo htmlspecialchars($category['icon'] ?? ''); ?>">
                        <div class="text-sm text-gray-500 mt-2">
                            Carica un'immagine (JPEG, PNG, GIF, SVG, WebP - max 2MB). Se non selezioni nulla, l'icona attuale verrà mantenuta.
                        </div>
                    </div>
                    <div class="text-right">
                        <a href="categorie.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva Categoria</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        lucide.createIcons();

        function deleteCategory(categoryId) {
            if (confirm('Sei sicuro di voler eliminare questa categoria?')) {
                const formData = new FormData();
                formData.append('action', 'delete_category');
                formData.append('id', categoryId);

                fetch('categorie.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const notificationPlaceholder = document.getElementById('notification-placeholder');
                    PassioneCalabria.showNotification(data.message, data.success ? 'success' : 'error', notificationPlaceholder);
                    if (data.success) {
                        document.getElementById('category-row-' + categoryId)?.remove();
                    }
                })
                .catch(error => {
                    console.error('Errore:', error);
                    const notificationPlaceholder = document.getElementById('notification-placeholder');
                    PassioneCalabria.showNotification('Errore di comunicazione con il server.', 'error', notificationPlaceholder);
                });
            }
        }
    </script>
</body>
</html>
