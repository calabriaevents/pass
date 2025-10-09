<?php
require_once __DIR__ . '/auth_check.php';
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Aggiunto Image Processor

$db = new Database();
$imageProcessor = new ImageProcessor(); // Istanza di ImageProcessor

$entity = $_GET['entity'] ?? 'provinces';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($entity === 'provinces') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_path = $_POST['existing_image_path'] ?? null;

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $new_image_path = $imageProcessor->processUploadedImage($_FILES['image'], 'provinces');
            if ($new_image_path) {
                 if ($image_path) { // Elimina la vecchia immagine
                    $imageProcessor->deleteImage($image_path);
                }
                $image_path = $new_image_path;
            }
        }

        if ($action === 'edit' && $id) {
            $db->updateProvince($id, $name, $description, $image_path);
        } else {
            $db->createProvince($name, $description, $image_path);
        }
    }
    
    if ($entity === 'gallery' && isset($_POST['province_id'])) {
        $province_id = (int)$_POST['province_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
            $image_path = $imageProcessor->processUploadedImage($_FILES['gallery_image'], 'galleries');
            if ($image_path) {
                $db->addProvinceGalleryImage($province_id, $image_path, $title, $description);
            }
        }
    }
    
    header('Location: province.php');
    exit;
}

if ($action === 'delete' && $id) {
    if ($entity === 'provinces') {
        $province = $db->getProvinceById($id);
        if ($province && $province['image_path']) {
            $imageProcessor->deleteImage($province['image_path']);
        }
        // Also delete gallery images
        $gallery_images = $db->getProvinceGalleryImages($id);
        foreach ($gallery_images as $image) {
            $imageProcessor->deleteImage($image['image_path']);
        }
        $db->deleteProvince($id);
    } elseif ($entity === 'gallery') {
        $image = $db->getProvinceGalleryImageById($id);
        if ($image) {
            $imageProcessor->deleteImage($image['image_path']);
            $db->deleteProvinceGalleryImage($id);
        }
    }
    header('Location: province.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Province - Admin Panel</title>
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
            <a href="../index.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Torna al Sito</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestione Province</h1>
                    <p class="text-sm text-gray-500">Gestisci le province della Calabria e le loro gallerie fotografiche</p>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($entity === 'provinces' && $action === 'list'): ?>
                    <a href="?entity=provinces&action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        <span>Nuova Provincia</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-6">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <?php if ($entity === 'provinces'): ?>
                    <?php if ($action === 'list'): ?>
                    <!-- Lista Province -->
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Provincia</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Immagine</th>
                                    <th class="text-right py-3 px-6 font-semibold text-gray-700">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $provinces = $db->getProvinces();
                                foreach ($provinces as $province):
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($province['name']); ?></div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($province['image_path']): ?>
                                        <img src="../image-loader.php?path=<?php echo urlencode($province['image_path']); ?>" alt="<?php echo htmlspecialchars($province['name']); ?>" class="w-16 h-16 object-cover rounded-lg">
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <a href="?entity=provinces&action=edit&id=<?php echo $province['id']; ?>" class="text-blue-600 hover:text-blue-700 font-medium text-sm">Modifica</a>
                                        <a href="?entity=gallery&action=manage&province_id=<?php echo $province['id']; ?>" class="text-green-600 hover:text-green-700 font-medium text-sm ml-4">Galleria</a>
                                        <a href="?entity=provinces&action=delete&id=<?php echo $province['id']; ?>" class="text-red-600 hover:text-red-700 font-medium text-sm ml-4" onclick="return confirm('Sei sicuro?');">Elimina</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php elseif ($action === 'new' || $action === 'edit'):
                        $province = null;
                        if ($action === 'edit' && $id) {
                            $province = $db->getProvinceById($id);
                        }
                    ?>
                    <!-- Form Nuova/Modifica Provincia -->
                    <div class="p-6">
                        <form action="?entity=provinces&action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
                            <div class="mb-4">
                                <label for="name" class="block text-gray-700 font-bold mb-2">Nome Provincia</label>
                                <input type="text" name="name" id="name" value="<?php echo htmlspecialchars($province['name'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg" required>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block text-gray-700 font-bold mb-2">Descrizione</label>
                                <textarea name="description" id="description" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($province['description'] ?? ''); ?></textarea>
                            </div>
                            <div class="mb-4">
                                <label for="image" class="block text-gray-700 font-bold mb-2">Immagine Rappresentativa</label>
                                <input type="file" name="image" id="image" class="w-full">
                                <?php if (isset($province['image_path'])): ?>
                                <input type="hidden" name="existing_image_path" value="<?php echo htmlspecialchars($province['image_path']); ?>">
                                <img src="../image-loader.php?path=<?php echo urlencode($province['image_path']); ?>" alt="Immagine attuale" class="w-32 mt-2">
                                <?php endif; ?>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                <?php elseif ($entity === 'gallery'): ?>
                    <?php 
                    $province_id = $_GET['province_id'] ?? null;
                    if ($province_id) {
                        $province = $db->getProvinceById($province_id);
                        $gallery_images = $db->getProvinceGalleryImages($province_id);
                    }
                    ?>
                    <?php if ($action === 'manage' && $province_id): ?>
                    <!-- Gestione Galleria -->
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-4">Galleria: <?php echo htmlspecialchars($province['name']); ?></h2>
                        <a href="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg mb-4 inline-block">Aggiungi Immagine</a>
                        <div class="grid grid-cols-4 gap-4">
                            <?php foreach ($gallery_images as $image): ?>
                            <div>
                                <img src="../image-loader.php?path=<?php echo urlencode($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="w-full h-32 object-cover rounded-lg">
                                <p><?php echo htmlspecialchars($image['title']); ?></p>
                                <a href="?entity=gallery&action=delete&id=<?php echo $image['id']; ?>&province_id=<?php echo $province_id; ?>" class="text-red-500" onclick="return confirm('Sei sicuro?');">Elimina</a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php elseif ($action === 'add' && $province_id): ?>
                    <!-- Form Aggiungi Immagine -->
                    <div class="p-6">
                        <h2 class="text-2xl font-bold mb-4">Aggiungi Immagine a <?php echo htmlspecialchars($province['name']); ?></h2>
                        <form action="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="province_id" value="<?php echo $province_id; ?>">
                            <div class="mb-4">
                                <label for="gallery_image" class="block text-gray-700 font-bold mb-2">Immagine</label>
                                <input type="file" name="gallery_image" id="gallery_image" class="w-full" required>
                            </div>
                            <div class="mb-4">
                                <label for="title" class="block text-gray-700 font-bold mb-2">Titolo</label>
                                <input type="text" name="title" id="title" class="w-full px-3 py-2 border rounded-lg" required>
                            </div>
                            <div class="mb-4">
                                <label for="description" class="block text-gray-700 font-bold mb-2">Descrizione</label>
                                <textarea name="description" id="description" class="w-full px-3 py-2 border rounded-lg"></textarea>
                            </div>
                            <div class="text-right">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Aggiungi</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>