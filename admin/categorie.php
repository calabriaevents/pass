<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $description = $_POST['description'] ?? '';
    $icon = $_POST['current_icon'] ?? ''; // Mantieni l'icona esistente di default

    // Gestione upload icona
    if (isset($_FILES['icon']) && $_FILES['icon']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/categories/';
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml', 'image/webp'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (in_array($_FILES['icon']['type'], $allowedTypes) && $_FILES['icon']['size'] <= $maxSize) {
            $extension = pathinfo($_FILES['icon']['name'], PATHINFO_EXTENSION);
            $filename = 'category_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
            $uploadPath = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['icon']['tmp_name'], $uploadPath)) {
                // Elimina il file precedente se esiste e non è un emoji
                if (!empty($icon) && strpos($icon, 'uploads/') !== false && file_exists('../' . $icon)) {
                    unlink('../' . $icon);
                }
                $icon = 'uploads/categories/' . $filename;
            }
        }
    }

    if ($action === 'edit' && $id) {
        $db->updateCategory($id, $name, $description, $icon);
    } else {
        $db->createCategory($name, $description, $icon);
    }
    header('Location: categorie.php');
    exit;
}

if ($action === 'delete' && $id) {
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
                        <tr class="border-b">
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
                                <a href="categorie.php?action=delete&id=<?php echo $category['id']; ?>" class="text-red-600 hover:underline ml-4" onclick="return confirm('Sei sicuro di voler eliminare questa categoria?');">Elimina</a>
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
                <form action="categorie.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
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

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
