<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Includi il nuovo processore di immagini

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();
$imageProcessor = new ImageProcessor(); // Istanzia il processore

$entity = $_GET['entity'] ?? 'provinces';
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$province_id = $_GET['province_id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $upload_error = '';

    if ($entity === 'provinces') {
        $name = $_POST['name'] ?? '';
        $description = $_POST['description'] ?? '';
        $image_path = $_POST['existing_image_path'] ?? null;

        // Gestione upload con ImageProcessor
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $new_image_path = $imageProcessor->processUploadedImage($_FILES['image'], 'provinces', 1920);
            if ($new_image_path) {
                // Elimina immagine esistente se presente
                if ($image_path) {
                    $imageProcessor->deleteImage($image_path);
                }
                $image_path = $new_image_path;
            } else {
                $upload_error = 'Errore nel caricamento dell\'immagine rappresentativa.';
            }
        }

        if (empty($upload_error)) {
            if ($action === 'edit' && $id) {
                $db->updateProvince($id, $name, $description, $image_path);
                $success_message = "Provincia aggiornata con successo!";
            } else {
                $db->createProvince($name, $description, $image_path);
                $success_message = "Provincia creata con successo!";
            }
            header('Location: province.php?success=' . urlencode($success_message));
            exit;
        }
    }
    
    // Gestione upload immagini galleria
    if ($entity === 'gallery' && isset($_POST['province_id'])) {
        $province_id_post = (int)$_POST['province_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
            $gallery_image_path = $imageProcessor->processUploadedImage($_FILES['gallery_image'], 'galleries', 1280);
            if ($gallery_image_path) {
                $db->addProvinceGalleryImage($province_id_post, $gallery_image_path, $title, $description);
                $success_message = "Immagine aggiunta alla galleria con successo!";
            } else {
                $upload_error = 'Errore nel caricamento dell\'immagine della galleria.';
            }
        } else {
            $upload_error = 'Nessuna immagine selezionata per la galleria.';
        }

        $redirect_url = 'province.php?entity=gallery&action=manage&province_id=' . $province_id_post;
        if (!empty($success_message)) {
             $redirect_url .= '&success=' . urlencode($success_message);
        }
        if (!empty($upload_error)) {
             $redirect_url .= '&error=' . urlencode($upload_error);
        }
        header('Location: ' . $redirect_url);
        exit;
    }
    
    if (!empty($upload_error)) {
        header('Location: province.php?action=' . $action . ($id ? '&id='.$id : '') . '&error=' . urlencode($upload_error));
        exit;
    }
}

if ($action === 'delete' && $id) {
    if ($entity === 'provinces') {
        // Elimina la provincia e tutte le immagini associate
        $province = $db->getProvinceById($id);
        if ($province) {
            // Elimina immagine rappresentativa
            if ($province['image_path']) {
                $imageProcessor->deleteImage($province['image_path']);
            }
            // Elimina immagini della galleria
            $gallery_images = $db->getProvinceGalleryImages($id);
            foreach ($gallery_images as $image) {
                $imageProcessor->deleteImage($image['image_path']);
            }
        }
        $db->deleteProvince($id);
        $success_message = "Provincia e tutte le immagini associate eliminate con successo!";
        header('Location: province.php?success=' . urlencode($success_message));
        exit;

    } elseif ($entity === 'gallery') {
        // Elimina una singola immagine dalla galleria
        $image = $db->getProvinceGalleryImageById($id);
        if ($image) {
            $imageProcessor->deleteImage($image['image_path']);
            $db->deleteProvinceGalleryImage($id);
            $success_message = "Immagine eliminata dalla galleria con successo!";
        } else {
            $error_message = "Immagine non trovata.";
        }

        $redirect_url = 'province.php?entity=gallery&action=manage&province_id=' . ($_GET['province_id'] ?? $image['province_id']);
         if (!empty($success_message)) {
             $redirect_url .= '&success=' . urlencode($success_message);
        }
        if (!empty($error_message)) {
             $redirect_url .= '&error=' . urlencode($error_message);
        }
        header('Location: ' . $redirect_url);
        exit;
    }
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
    <title>Gestione Province - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
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

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <?php if ($entity === 'provinces'): ?>
                    <?php if ($action === 'list'): ?>
                    <!-- Lista Province -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Elenco Province</h2>
                    </div>

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
                                    <td class="py-4 px-6 font-semibold text-gray-900"><?php echo htmlspecialchars($province['name']); ?></td>
                                    <td class="py-4 px-6">
                                        <?php if ($province['image_path']): ?>
                                            <img src="../<?php echo htmlspecialchars($province['image_path']); ?>" alt="<?php echo htmlspecialchars($province['name']); ?>" class="w-24 h-16 object-cover rounded-lg">
                                        <?php else: ?>
                                            <span class="text-gray-400 text-sm">Nessuna</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="?entity=provinces&action=edit&id=<?php echo $province['id']; ?>" class="text-blue-600 hover:text-blue-700 font-medium text-sm p-2 rounded-lg hover:bg-blue-50">Modifica</a>
                                            <a href="?entity=gallery&action=manage&province_id=<?php echo $province['id']; ?>" class="text-green-600 hover:text-green-700 font-medium text-sm p-2 rounded-lg hover:bg-green-50">Galleria</a>
                                            <a href="?entity=provinces&action=delete&id=<?php echo $province['id']; ?>" class="text-red-600 hover:text-red-700 font-medium text-sm p-2 rounded-lg hover:bg-red-50" onclick="return confirm('Sei sicuro di voler eliminare questa provincia e tutte le sue immagini?');">Elimina</a>
                                        </div>
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
                        <h2 class="text-lg font-semibold mb-4"><?php echo $action === 'edit' ? 'Modifica Provincia' : 'Nuova Provincia'; ?></h2>
                        <form action="?entity=provinces&action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Nome Provincia *</label>
                                <input type="text" name="name" id="name" required value="<?php echo htmlspecialchars($province['name'] ?? ''); ?>" class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Descrizione</label>
                                <textarea name="description" id="description" rows="4" class="w-full px-4 py-2 border rounded-lg"><?php echo htmlspecialchars($province['description'] ?? ''); ?></textarea>
                            </div>
                            <div>
                                <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">Immagine Rappresentativa</label>
                                <?php if (isset($province['image_path']) && $province['image_path']): ?>
                                    <img src="../<?php echo htmlspecialchars($province['image_path']); ?>" alt="Immagine attuale" class="w-32 h-auto rounded-lg mb-2">
                                <?php endif; ?>
                                <input type="file" name="image" id="image" accept="image/*">
                                <input type="hidden" name="existing_image_path" value="<?php echo htmlspecialchars($province['image_path'] ?? ''); ?>">
                            </div>
                            <div class="flex items-center justify-end space-x-4">
                                <a href="?entity=provinces" class="px-6 py-2 text-gray-600">Annulla</a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Salva</button>
                            </div>
                        </form>
                    </div>
                    <?php endif; ?>

                <?php elseif ($entity === 'gallery'): ?>
                    <?php 
                    if ($province_id) {
                        $province = $db->getProvinceById($province_id);
                        $gallery_images = $db->getProvinceGalleryImages($province_id);
                    }
                    ?>
                    <?php if ($action === 'manage' && $province_id): ?>
                    <!-- Gestione Galleria -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                             <a href="?entity=provinces" class="text-gray-600 hover:text-gray-800 font-medium flex items-center space-x-2">
                                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                                <span>Torna alle Province</span>
                            </a>
                            <h2 class="text-lg font-semibold text-gray-900">Galleria: <?php echo htmlspecialchars($province['name']); ?></h2>
                            <a href="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg">Aggiungi Immagine</a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                            <?php foreach ($gallery_images as $image): ?>
                            <div class="rounded-lg overflow-hidden shadow-md">
                                <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" alt="<?php echo htmlspecialchars($image['title']); ?>" class="w-full h-48 object-cover">
                                <div class="p-4">
                                    <h3 class="font-semibold"><?php echo htmlspecialchars($image['title']); ?></h3>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($image['description']); ?></p>
                                    <a href="?entity=gallery&action=delete&id=<?php echo $image['id']; ?>&province_id=<?php echo $province_id; ?>" class="text-red-600 hover:underline mt-2 inline-block" onclick="return confirm('Sei sicuro di voler eliminare questa immagine?');">Elimina</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <?php elseif ($action === 'add' && $province_id): ?>
                    <!-- Form Aggiungi Immagine -->
                    <div class="p-6">
                        <h2 class="text-lg font-semibold mb-4">Aggiungi Immagine a: <?php echo htmlspecialchars($province['name']); ?></h2>
                        <form action="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                            <input type="hidden" name="province_id" value="<?php echo $province_id; ?>">
                            <div>
                                <label for="gallery_image" class="block text-sm font-semibold text-gray-700 mb-2">Immagine *</label>
                                <input type="file" name="gallery_image" id="gallery_image" accept="image/*" required>
                            </div>
                            <div>
                                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">Titolo *</label>
                                <input type="text" name="title" id="title" required class="w-full px-4 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Descrizione</label>
                                <textarea name="description" id="description" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>
                            <div class="flex items-center justify-end space-x-4">
                                <a href="?entity=gallery&action=manage&province_id=<?php echo $province_id; ?>" class="px-6 py-2 text-gray-600">Annulla</a>
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold">Aggiungi Immagine</button>
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