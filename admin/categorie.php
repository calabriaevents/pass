<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Includi il nuovo processore di immagini

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();
$imageProcessor = new ImageProcessor(); // Istanzia il processore

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon_path = $_POST['current_icon'] ?? null; // Mantiene l'icona esistente di default

    $upload_error = '';

    // Gestione upload icona con ImageProcessor
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        $new_icon_path = $imageProcessor->processUploadedImage($_FILES['icon'], 'categories', 256); // Max 256px width for icons

        if ($new_icon_path) {
            // Elimina il file precedente se esiste
            if ($icon_path && strpos($icon_path, 'uploads/') !== false) {
                $imageProcessor->deleteImage($icon_path);
            }
            $icon_path = $new_icon_path;
        } else {
             $upload_error = 'Errore nel caricamento dell\'icona. Formato non supportato o file corrotto.';
        }
    }

    if (empty($upload_error)) {
        if ($action === 'edit' && $id) {
            $db->updateCategory($id, $name, $description, $icon_path);
            $success_message = "Categoria aggiornata con successo!";
        } else {
            $db->createCategory($name, $description, $icon_path);
            $success_message = "Categoria creata con successo!";
        }
        header('Location: categorie.php?success=' . urlencode($success_message));
        exit;
    } else {
        $redirect_url = $action === 'edit' ? "categorie.php?action=edit&id=$id" : "categorie.php?action=new";
        header("Location: $redirect_url&error=" . urlencode($upload_error));
        exit;
    }
}

if ($action === 'delete' && $id) {
    // Prima di eliminare, recupera la categoria per cancellare l'icona
    $category = $db->getCategoryById($id);
    if ($category && !empty($category['icon']) && strpos($category['icon'], 'uploads/') !== false) {
        $imageProcessor->deleteImage($category['icon']);
    }

    $db->deleteCategory($id);
    header('Location: categorie.php?success=' . urlencode("Categoria eliminata con successo!"));
    exit;
}

// Get messages from URL
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : null;
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
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
    <script src="../assets/js/main.js" defer></script>
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
                <a href="categorie.php?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2"><i data-lucide="plus" class="w-4 h-4"></i><span>Nuova Categoria</span></a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($success_message): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6 success-alert">
                <p class="text-sm text-green-700"><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6 error-alert">
                 <p class="text-sm text-red-700"><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Categorie</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 font-semibold">Icona</th>
                                <th class="text-left py-3 px-4 font-semibold">Nome</th>
                                <th class="text-left py-3 px-4 font-semibold">Descrizione</th>
                                <th class="text-left py-3 px-4 font-semibold">Articoli</th>
                                <th class="text-left py-3 px-4 font-semibold">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $categories = $db->getCategories();
                            foreach ($categories as $category):
                                $articleCount = $db->getArticleCountByCategory($category['id']);
                            ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <?php if (!empty($category['icon']) && strpos($category['icon'], 'uploads/') !== false): ?>
                                        <img src="../<?php echo htmlspecialchars($category['icon']); ?>" alt="Icona" class="w-8 h-8 object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-8 h-8 bg-gray-200 rounded flex items-center justify-center">
                                            <i data-lucide="image" class="w-4 h-4 text-gray-500"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 font-medium"><?php echo htmlspecialchars($category['name']); ?></td>
                                <td class="py-3 px-4 text-sm text-gray-600"><?php echo htmlspecialchars($category['description']); ?></td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                        <?php echo $articleCount; ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="categorie.php?action=edit&id=<?php echo $category['id']; ?>" class="text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Modifica"><i data-lucide="edit" class="w-4 h-4"></i></a>
                                        <a href="categorie.php?action=delete&id=<?php echo $category['id']; ?>" class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Elimina" onclick="return confirm('Sei sicuro di voler eliminare questa categoria?');"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php elseif ($action === 'new' || $action === 'edit'):
                $category = null;
                if ($action === 'edit' && $id) {
                    $category = $db->getCategoryById($id);
                }
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4"><?php echo $action === 'edit' ? 'Modifica Categoria' : 'Nuova Categoria'; ?></h2>
                <form action="categorie.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                        <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent" value="<?php echo htmlspecialchars($category['name'] ?? ''); ?>" required>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Descrizione</label>
                        <textarea name="description" id="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo htmlspecialchars($category['description'] ?? ''); ?></textarea>
                    </div>
                    <div>
                        <label for="icon" class="block text-sm font-medium text-gray-700 mb-1">Icona</label>
                        <?php if (!empty($category['icon'])): ?>
                        <div class="mb-3 p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-2">Icona attuale:</p>
                            <img src="../<?php echo htmlspecialchars($category['icon']); ?>" alt="Icona attuale" class="w-12 h-12 object-cover rounded-lg border">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="icon" id="icon" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                        <input type="hidden" name="current_icon" value="<?php echo htmlspecialchars($category['icon'] ?? ''); ?>">
                        <p class="text-xs text-gray-500 mt-2">
                            Carica un'immagine (verrà convertita in WebP e ridimensionata). Se non selezioni nulla, l'icona attuale verrà mantenuta.
                        </p>
                    </div>
                    <div class="text-right pt-4 border-t">
                        <a href="categorie.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva Categoria</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();

        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.success-alert, .error-alert').forEach(alert => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
</body>
</html>