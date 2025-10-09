<?php
require_once __DIR__ . '/auth_check.php';
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Aggiunto Image Processor

$db = new Database();
$imageProcessor = new ImageProcessor(); // Istanza di ImageProcessor

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['delete_gallery_image'])) {
    $name = $_POST['city_name'] ?? '';
    $province_id = $_POST['city_province_id'] ?? '';
    $description = $_POST['city_description'] ?? '';
    $latitude = !empty($_POST['city_latitude']) ? (float)$_POST['city_latitude'] : null;
    $longitude = !empty($_POST['city_longitude']) ? (float)$_POST['city_longitude'] : null;
    $google_maps_link = $_POST['city_google_maps_link'] ?? '';

    $upload_error = '';
    $hero_image_path = null;
    $gallery_images_json = null;
    
    if ($action === 'edit' && $id) {
        $existingCity = $db->getCityById($id);
        $hero_image_path = $existingCity['hero_image'] ?? null;
        $gallery_images_json = $existingCity['gallery_images'] ?? null;
    }

    // --- NUOVA GESTIONE UPLOAD CON IMAGEPROCESSOR ---
    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $new_hero_path = $imageProcessor->processUploadedImage($_FILES['hero_image'], 'cities/hero');
        if ($new_hero_path) {
            // Elimina la vecchia immagine se esiste
            if ($hero_image_path) {
                $imageProcessor->deleteImage($hero_image_path);
            }
            $hero_image_path = $new_hero_path;
        } else {
            $upload_error = 'Errore nel caricamento dell\'immagine hero.';
        }
    }

    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $gallery_images = $gallery_images_json ? json_decode($gallery_images_json, true) : [];
        $files = $_FILES['gallery_images'];

        foreach ($files['tmp_name'] as $key => $tmpName) {
            if ($files['error'][$key] === UPLOAD_ERR_OK) {
                $file_info = [
                    'name' => $files['name'][$key],
                    'type' => $files['type'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $files['error'][$key],
                    'size' => $files['size'][$key]
                ];
                $gallery_path = $imageProcessor->processUploadedImage($file_info, 'cities/gallery');
                if ($gallery_path) {
                    $gallery_images[] = $gallery_path;
                } else {
                     $upload_error = 'Errore nel caricamento di un file della galleria.';
                     break;
                }
            }
        }
        if (empty($upload_error)) {
            $gallery_images_json = json_encode(array_values($gallery_images));
        }
    }

    // Salvataggio nel database solo se non ci sono errori
    if (empty($upload_error)) {
        if ($action === 'edit' && $id) {
            $db->updateCityExtended($id, $name, $province_id, $description, $latitude, $longitude, $hero_image_path, $google_maps_link, $gallery_images_json);
        } else {
            $db->createCityExtended($name, $province_id, $description, $latitude, $longitude, $hero_image_path, $google_maps_link, $gallery_images_json);
        }
        header('Location: citta.php');
        exit;
    }
}

// Gestione eliminazione immagine galleria
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_gallery_image']) && $id) {
    $city = $db->getCityById($id);
    if ($city && $city['gallery_images']) {
        $gallery_images = json_decode($city['gallery_images'], true) ?: [];
        $image_to_delete = $_POST['delete_gallery_image'];
        
        // Rimuovi l'immagine dall'array
        $new_gallery = array_filter($gallery_images, function($img) use ($image_to_delete) {
            return $img !== $image_to_delete;
        });
        
        // Elimina il file fisico usando ImageProcessor
        $imageProcessor->deleteImage($image_to_delete);
        
        // Aggiorna il database
        $gallery_images_json = json_encode(array_values($new_gallery));
        $db->updateCityExtended($id, $city['name'], $city['province_id'], $city['description'], $city['latitude'], $city['longitude'], $city['hero_image'], $city['google_maps_link'], $gallery_images_json);
        
        header('Location: citta.php?action=edit&id=' . $id);
        exit;
    }
}

if ($action === 'delete' && $id) {
    $city = $db->getCityById($id);
    if ($city) {
        if ($city['hero_image']) $imageProcessor->deleteImage($city['hero_image']);
        if ($city['gallery_images']) {
            $gallery = json_decode($city['gallery_images'], true);
            if (is_array($gallery)) {
                foreach ($gallery as $img) {
                    $imageProcessor->deleteImage($img);
                }
            }
        }
    }
    
    $result = $db->deleteCity($id);
    header('Location: citta.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Città - Admin Panel</title>
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
                <h1 class="text-2xl font-bold text-gray-900">Gestione Città</h1>
                <?php if ($action === 'list'): ?>
                <a href="citta.php?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Nuova Città</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Città</h2>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Città</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Provincia</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Hero</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $cities = $db->getCities();
                        foreach ($cities as $city): ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2"><?php echo htmlspecialchars($city['name']); ?></td>
                            <td class="py-3 px-2"><?php echo htmlspecialchars($city['province_name']); ?></td>
                            <td class="py-3 px-2">
                                <?php if (!empty($city['hero_image'])): ?>
                                    <!-- MODIFICATO PER USARE IMAGE-LOADER.PHP -->
                                    <img src="../image-loader.php?path=<?php echo urlencode($city['hero_image']); ?>" alt="Hero" class="w-12 h-8 object-cover rounded">
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Nessuna</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2">
                                <a href="?action=edit&id=<?php echo $city['id']; ?>" class="text-blue-600 hover:underline">Modifica</a>
                                <a href="?action=delete&id=<?php echo $city['id']; ?>" onclick="return confirm('Sei sicuro?');" class="text-red-600 hover:underline ml-2">Elimina</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($action === 'new' || $action === 'edit'):
                $cityData = null;
                if ($action === 'edit' && $id) {
                    $cityData = $db->getCityById($id);
                }
                $gallery_images = $cityData && $cityData['gallery_images'] ? json_decode($cityData['gallery_images'], true) : [];
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4"><?php echo $action === 'edit' ? 'Modifica' : 'Nuova'; ?> Città</h2>
                <form action="?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
                    <!-- Campi del form -->
                    <div class="mb-4">
                        <label for="city_name" class="block text-gray-700 font-bold mb-2">Nome Città</label>
                        <input type="text" name="city_name" id="city_name" value="<?php echo htmlspecialchars($cityData['name'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg" required>
                    </div>
                    <!-- Altri campi del form (provincia, descrizione, etc.) -->
                    <div class="mb-4">
                        <label for="city_province_id" class="block text-gray-700 font-bold mb-2">Provincia</label>
                        <select name="city_province_id" id="city_province_id" class="w-full px-3 py-2 border rounded-lg" required>
                            <?php
                            $provinces = $db->getProvinces();
                            foreach ($provinces as $prov) {
                                $selected = (isset($cityData['province_id']) && $cityData['province_id'] == $prov['id']) ? 'selected' : '';
                                echo "<option value='{$prov['id']}' {$selected}>" . htmlspecialchars($prov['name']) . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-4">
                        <label for="city_description" class="block text-gray-700 font-bold mb-2">Descrizione</label>
                        <textarea name="city_description" id="city_description" rows="4" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($cityData['description'] ?? ''); ?></textarea>
                    </div>
                    <div class="mb-4">
                        <label for="city_latitude" class="block text-gray-700 font-bold mb-2">Latitudine</label>
                        <input type="text" name="city_latitude" id="city_latitude" value="<?php echo htmlspecialchars($cityData['latitude'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div class="mb-4">
                        <label for="city_longitude" class="block text-gray-700 font-bold mb-2">Longitudine</label>
                        <input type="text" name="city_longitude" id="city_longitude" value="<?php echo htmlspecialchars($cityData['longitude'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                     <div class="mb-4">
                        <label for="city_google_maps_link" class="block text-gray-700 font-bold mb-2">Link Google Maps</label>
                        <input type="url" name="city_google_maps_link" id="city_google_maps_link" value="<?php echo htmlspecialchars($cityData['google_maps_link'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                    </div>

                    <!-- Immagine Hero -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Immagine Hero</label>
                        <?php if ($cityData && $cityData['hero_image']): ?>
                        <div class="mb-2">
                            <p>Immagine attuale:</p>
                            <!-- MODIFICATO PER USARE IMAGE-LOADER.PHP -->
                            <img src="../image-loader.php?path=<?php echo urlencode($cityData['hero_image']); ?>" alt="Hero attuale" class="w-32 h-20 object-cover rounded-lg border">
                        </div>
                        <?php endif; ?>
                        <input type="file" name="hero_image" class="w-full">
                    </div>

                    <!-- Galleria Immagini -->
                    <div class="mb-4">
                        <label class="block text-gray-700 font-bold mb-2">Galleria Immagini</label>
                        <?php if (!empty($gallery_images)): ?>
                        <div class="grid grid-cols-4 gap-4 mb-4">
                            <?php foreach ($gallery_images as $image): ?>
                            <div class="relative">
                                <!-- MODIFICATO PER USARE IMAGE-LOADER.PHP -->
                                <img src="../image-loader.php?path=<?php echo urlencode($image); ?>" alt="Galleria" class="w-full h-24 object-cover rounded-lg border">
                                <button type="button" onclick="deleteGalleryImage('<?php echo htmlspecialchars($image); ?>', <?php echo $id; ?>)" class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center">&times;</button>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <input type="file" name="gallery_images[]" multiple class="w-full">
                    </div>

                    <div class="text-right">
                        <a href="citta.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <form id="deleteImageForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_gallery_image" id="imageToDelete">
    </form>

    <script>
        lucide.createIcons();
        function deleteGalleryImage(imagePath, cityId) {
            if (confirm('Sei sicuro di voler eliminare questa immagine?')) {
                const form = document.getElementById('deleteImageForm');
                document.getElementById('imageToDelete').value = imagePath;
                form.action = `citta.php?action=edit&id=${cityId}`;
                form.submit();
            }
        }
    </script>
</body>
</html>