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
    $name = $_POST['city_name'] ?? '';
    $province_id = $_POST['city_province_id'] ?? '';
    $description = $_POST['city_description'] ?? '';
    $latitude = !empty($_POST['city_latitude']) ? (float)$_POST['city_latitude'] : null;
    $longitude = !empty($_POST['city_longitude']) ? (float)$_POST['city_longitude'] : null;
    $google_maps_link = $_POST['city_google_maps_link'] ?? '';

    $upload_error = '';
    $hero_image_path = null;
    $gallery_images_json = null;
    
    // Se è una modifica, recupera i dati esistenti
    if ($action === 'edit' && $id) {
        $existingCity = $db->getCityById($id);
        $hero_image_path = $existingCity['hero_image'] ?? null;
        $gallery_images_json = $existingCity['gallery_images'] ?? null;
    }

    // Gestione upload immagine hero
    if (!empty($_FILES['hero_image']['name'])) {
        $upload_dir = '../uploads/cities/hero/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['hero_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($file_extension, $allowed_extensions)) {
            $upload_error = 'Formato file hero non supportato. Usa JPG, PNG, GIF o WebP.';
        } elseif ($_FILES['hero_image']['size'] > 5 * 1024 * 1024) {
            $upload_error = 'File hero troppo grande. Massimo 5MB.';
        } else {
            $filename = 'hero_' . uniqid() . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;
            
            if (move_uploaded_file($_FILES['hero_image']['tmp_name'], $upload_path)) {
                $hero_image_path = 'uploads/cities/hero/' . $filename;
                // Elimina vecchia immagine se esiste
                if ($existingCity && $existingCity['hero_image'] && file_exists('../' . $existingCity['hero_image'])) {
                    unlink('../' . $existingCity['hero_image']);
                }
            } else {
                $upload_error = 'Errore nel caricamento dell\'immagine hero.';
            }
        }
    }

    // Gestione upload galleria
    if (!empty($_FILES['gallery_images']['name'][0])) {
        $upload_dir = '../uploads/cities/gallery/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $gallery_images = [];
        if ($gallery_images_json) {
            $gallery_images = json_decode($gallery_images_json, true) ?: [];
        }
        
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_extension = strtolower(pathinfo($_FILES['gallery_images']['name'][$key], PATHINFO_EXTENSION));
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    $upload_error = 'Formato file galleria non supportato: ' . $_FILES['gallery_images']['name'][$key];
                    break;
                } elseif ($_FILES['gallery_images']['size'][$key] > 5 * 1024 * 1024) {
                    $upload_error = 'File galleria troppo grande: ' . $_FILES['gallery_images']['name'][$key];
                    break;
                } else {
                    $filename = 'gallery_' . uniqid() . '_' . time() . '.' . $file_extension;
                    $upload_path = $upload_dir . $filename;
                    
                    if (move_uploaded_file($tmp_name, $upload_path)) {
                        $gallery_images[] = 'uploads/cities/gallery/' . $filename;
                    } else {
                        $upload_error = 'Errore nel caricamento di: ' . $_FILES['gallery_images']['name'][$key];
                        break;
                    }
                }
            }
        }
        
        if (empty($upload_error)) {
            $gallery_images_json = json_encode($gallery_images);
        }
    }

    // Salvataggio nel database solo se non ci sono errori
    if (empty($upload_error)) {
        if ($action === 'edit' && $id) {
            $db->updateCityExtended($id, $name, $province_id, $description, $latitude, $longitude, $hero_image_path, $google_maps_link, $gallery_images_json);
            $success_message = "Città aggiornata con successo!";
        } else {
            $db->createCityExtended($name, $province_id, $description, $latitude, $longitude, $hero_image_path, $google_maps_link, $gallery_images_json);
            $success_message = "Città creata con successo!";
        }
        
        header('Location: citta.php?' . http_build_query($_GET));
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
        $gallery_images = array_filter($gallery_images, function($img) use ($image_to_delete) {
            return $img !== $image_to_delete;
        });
        
        // Elimina il file fisico
        if (file_exists('../' . $image_to_delete)) {
            unlink('../' . $image_to_delete);
        }
        
        // Aggiorna il database
        $gallery_images_json = json_encode(array_values($gallery_images));
        $db->updateCityExtended($id, $city['name'], $city['province_id'], $city['description'], $city['latitude'], $city['longitude'], $city['hero_image'], $city['google_maps_link'], $gallery_images_json);
        
        header('Location: citta.php?action=edit&id=' . $id);
        exit;
    }
}

if ($action === 'delete' && $id) {
    // Elimina anche le immagini associate
    $city = $db->getCityById($id);
    if ($city) {
        // Elimina hero image
        if ($city['hero_image'] && file_exists('../' . $city['hero_image'])) {
            unlink('../' . $city['hero_image']);
        }
        // Elimina galleria
        if ($city['gallery_images']) {
            $gallery_images = json_decode($city['gallery_images'], true) ?: [];
            foreach ($gallery_images as $image) {
                if (file_exists('../' . $image)) {
                    unlink('../' . $image);
                }
            }
        }
    }
    
    $result = $db->deleteCity($id);
    if (!$result) {
        $error_message = 'Impossibile eliminare la città: ci sono articoli collegati.';
    } else {
        $success_message = "Città eliminata con successo!";
    }
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
                    <h1 class="text-2xl font-bold text-gray-900">Gestione Città</h1>
                    <p class="text-sm text-gray-500">Gestisci le città della Calabria con immagini e gallerie</p>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if ($action === 'list'): ?>
                    <a href="?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        <span>Nuova Città</span>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-6">
            <?php if (isset($success_message)): ?>
            <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="check-circle" class="h-5 w-5 text-green-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-green-700"><?php echo $success_message; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo $error_message; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($upload_error) && !empty($upload_error)): ?>
            <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <i data-lucide="alert-circle" class="h-5 w-5 text-red-400"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-red-700"><?php echo $upload_error; ?></p>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <?php if ($action === 'list'): ?>
                    <!-- Lista Città -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <i data-lucide="map-pin" class="w-6 h-6 text-blue-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Elenco Città</h2>
                                    <p class="text-sm text-gray-500">Tutte le città registrate nel sistema</p>
                                </div>
                            </div>
                            
                            <?php 
                            $totalCities = $db->pdo->query('SELECT COUNT(*) FROM cities')->fetchColumn();
                            $citiesWithCoords = $db->pdo->query('SELECT COUNT(*) FROM cities WHERE latitude IS NOT NULL AND longitude IS NOT NULL')->fetchColumn();
                            ?>
                            <div class="flex space-x-4">
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-gray-900"><?php echo $totalCities; ?></div>
                                    <div class="text-xs text-gray-500">Città Totali</div>
                                </div>
                                <div class="text-center">
                                    <div class="text-2xl font-bold text-green-600"><?php echo $citiesWithCoords; ?></div>
                                    <div class="text-xs text-gray-500">Con Coordinate</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Città</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Provincia</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Hero</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Galleria</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Coordinate</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Articoli</th>
                                    <th class="text-right py-3 px-6 font-semibold text-gray-700">Azioni</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $cities = $db->getCities();
                                foreach ($cities as $city):
                                    $articleCount = $db->getArticleCountByCity($city['id']);
                                    $gallery_images = $city['gallery_images'] ? json_decode($city['gallery_images'], true) : [];
                                ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-green-500 rounded-full flex items-center justify-center text-white font-semibold text-sm mr-3">
                                                <?php echo strtoupper(substr($city['name'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($city['name']); ?></div>
                                                <div class="text-sm text-gray-500">ID: <?php echo $city['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <?php echo htmlspecialchars($city['province_name']); ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($city['hero_image']): ?>
                                        <img src="../<?php echo htmlspecialchars($city['hero_image']); ?>" alt="Hero" class="w-12 h-8 object-cover rounded">
                                        <?php else: ?>
                                        <span class="text-gray-400 text-sm">Nessuna</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center">
                                            <i data-lucide="image" class="w-4 h-4 mr-1 text-purple-600"></i>
                                            <span class="text-sm font-medium text-purple-600"><?php echo count($gallery_images); ?></span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($city['latitude'] && $city['longitude']): ?>
                                        <div class="flex items-center text-green-600 text-sm">
                                            <i data-lucide="map-pin" class="w-4 h-4 mr-1"></i>
                                            <span class="font-mono"><?php echo number_format($city['latitude'], 3); ?>, <?php echo number_format($city['longitude'], 3); ?></span>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-gray-400 text-sm flex items-center">
                                            <i data-lucide="map-pin" class="w-4 h-4 mr-1"></i>
                                            Non disponibili
                                        </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            <?php echo $articleCount; ?>
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="?action=edit&id=<?php echo $city['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center space-x-1 transition-colors">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                                <span>Modifica</span>
                                            </a>
                                            <a href="?action=delete&id=<?php echo $city['id']; ?>" 
                                               class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center space-x-1 transition-colors" 
                                               onclick="return confirm('Sei sicuro di voler eliminare questa città? Questa azione eliminerà anche tutte le immagini associate e non può essere annullata.');">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                <span>Elimina</span>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if (empty($cities)): ?>
                    <div class="text-center py-12">
                        <i data-lucide="map-pin" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Nessuna città registrata</h3>
                        <p class="text-gray-500 mb-6">Inizia aggiungendo la prima città del sistema</p>
                        <a href="?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center space-x-2 transition-colors">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                            <span>Aggiungi Prima Città</span>
                        </a>
                    </div>
                    <?php endif; ?>

                <?php elseif ($action === 'new' || $action === 'edit'): 
                    $cityData = null;
                    if ($action === 'edit' && $id) {
                        $cityData = $db->getCityById($id);
                    }
                    $gallery_images = $cityData && $cityData['gallery_images'] ? json_decode($cityData['gallery_images'], true) : [];
                ?>
                    <!-- Form Nuova/Modifica Città -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <a href="citta.php" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            </a>
                            <div class="bg-green-100 p-2 rounded-lg">
                                <i data-lucide="<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    <?php echo $action === 'edit' ? 'Modifica Città' : 'Nuova Città'; ?>
                                </h2>
                                <p class="text-sm text-gray-500">
                                    <?php echo $action === 'edit' ? 'Aggiorna le informazioni e le immagini della città' : 'Aggiungi una nuova città con immagini al sistema'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="max-w-4xl mx-auto">
                            <form action="?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data" class="space-y-8">
                                
                                <!-- Sezione Informazioni Base -->
                                <div class="bg-slate-50 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                                        Informazioni Base
                                    </h3>
                                    
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                        <div>
                                            <label for="city_name" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Nome Città *
                                            </label>
                                            <input type="text" name="city_name" id="city_name" required 
                                                   value="<?php echo htmlspecialchars($cityData['name'] ?? ''); ?>"
                                                   class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                                   placeholder="Es: Cosenza, Catanzaro, Reggio Calabria...">
                                        </div>
                                        
                                        <div>
                                            <label for="city_province_id" class="block text-sm font-semibold text-gray-700 mb-2">
                                                Provincia di Appartenenza *
                                            </label>
                                            <select name="city_province_id" id="city_province_id" required 
                                                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                                                <option value="">Seleziona una provincia</option>
                                                <?php 
                                                $provinces = $db->getProvinces();
                                                foreach ($provinces as $prov): 
                                                ?>
                                                <option value="<?php echo $prov['id']; ?>" <?php echo (isset($cityData['province_id']) && $cityData['province_id'] == $prov['id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($prov['name']); ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="mt-6">
                                        <label for="city_description" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Descrizione
                                        </label>
                                        <textarea name="city_description" id="city_description" rows="4" 
                                                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                                                  placeholder="Breve descrizione della città, punti di interesse, caratteristiche principali..."><?php echo htmlspecialchars($cityData['description'] ?? ''); ?></textarea>
                                    </div>
                                </div>

                                <!-- Sezione Immagine Hero -->
                                <div class="bg-blue-50 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i data-lucide="image" class="w-5 h-5 mr-2"></i>
                                        Immagine Hero
                                    </h3>
                                    
                                    <?php if ($cityData && $cityData['hero_image']): ?>
                                    <div class="mb-4">
                                        <p class="text-sm text-gray-600 mb-2">Immagine hero attuale:</p>
                                        <img src="../<?php echo htmlspecialchars($cityData['hero_image']); ?>" alt="Hero attuale" class="w-32 h-20 object-cover rounded-lg border">
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <label for="hero_image" class="block text-sm font-semibold text-gray-700 mb-2">
                                            <?php echo ($cityData && $cityData['hero_image']) ? 'Sostituisci Immagine Hero' : 'Carica Immagine Hero'; ?>
                                        </label>
                                        <input type="file" name="hero_image" id="hero_image" accept="image/*"
                                               class="w-full px-4 py-3 border-2 border-dashed border-blue-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                                        <p class="text-sm text-gray-500 mt-2">Formati supportati: JPG, PNG, GIF, WebP. Massimo 5MB. Verrà utilizzata come sfondo principale della pagina città.</p>
                                    </div>
                                </div>

                                <!-- Sezione Galleria -->
                                <div class="bg-purple-50 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i data-lucide="images" class="w-5 h-5 mr-2"></i>
                                        Galleria Immagini
                                    </h3>
                                    
                                    <?php if (!empty($gallery_images)): ?>
                                    <div class="mb-6">
                                        <p class="text-sm text-gray-600 mb-3">Immagini galleria attuali:</p>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                            <?php foreach ($gallery_images as $image): ?>
                                            <div class="relative group">
                                                <img src="../<?php echo htmlspecialchars($image); ?>" alt="Galleria" class="w-full h-24 object-cover rounded-lg border">
                                                <button type="button" onclick="deleteGalleryImage('<?php echo htmlspecialchars($image); ?>')" 
                                                        class="absolute top-1 right-1 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                                    <i data-lucide="x" class="w-3 h-3"></i>
                                                </button>
                                            </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div>
                                        <label for="gallery_images" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Aggiungi Immagini alla Galleria
                                        </label>
                                        <input type="file" name="gallery_images[]" id="gallery_images" accept="image/*" multiple
                                               class="w-full px-4 py-3 border-2 border-dashed border-purple-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-colors">
                                        <p class="text-sm text-gray-500 mt-2">Seleziona più immagini per la galleria. Formati supportati: JPG, PNG, GIF, WebP. Massimo 5MB per file.</p>
                                    </div>
                                </div>

                                <!-- Sezione Mappa -->
                                <div class="bg-green-50 rounded-2xl p-6">
                                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                                        <i data-lucide="map" class="w-5 h-5 mr-2"></i>
                                        Posizione e Mappa
                                    </h3>
                                    
                                    <div class="mb-6">
                                        <label for="city_google_maps_link" class="block text-sm font-semibold text-gray-700 mb-2">
                                            Link Google Maps Personalizzato
                                        </label>
                                        <input type="url" name="city_google_maps_link" id="city_google_maps_link"
                                               value="<?php echo htmlspecialchars($cityData['google_maps_link'] ?? ''); ?>"
                                               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors"
                                               placeholder="https://maps.google.com/...">
                                        <p class="text-sm text-gray-500 mt-1">Link personalizzato per Google Maps. Se vuoto, verrà generato automaticamente dalle coordinate.</p>
                                    </div>
                                    
                                    <div>
                                        <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                                            <i data-lucide="compass" class="w-4 h-4 mr-2"></i>
                                            Coordinate GPS
                                        </h4>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                            <div>
                                                <label for="city_latitude" class="block text-sm font-medium text-gray-600 mb-1">
                                                    Latitudine
                                                </label>
                                                <input type="number" name="city_latitude" id="city_latitude" step="any" 
                                                       value="<?php echo $cityData['latitude'] ?? ''; ?>"
                                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                                                       placeholder="39.0847">
                                            </div>
                                            <div>
                                                <label for="city_longitude" class="block text-sm font-medium text-gray-600 mb-1">
                                                    Longitudine
                                                </label>
                                                <input type="number" name="city_longitude" id="city_longitude" step="any" 
                                                       value="<?php echo $cityData['longitude'] ?? ''; ?>"
                                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-colors" 
                                                       placeholder="17.1252">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Info Box -->
                                <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6">
                                    <h4 class="font-semibold text-blue-900 mb-2 flex items-center">
                                        <i data-lucide="info" class="w-4 h-4 mr-2"></i>
                                        Suggerimenti per le Immagini
                                    </h4>
                                    <ul class="text-blue-800 text-sm space-y-1">
                                        <li>• <strong>Immagine Hero:</strong> Usa un'immagine panoramica e di alta qualità che rappresenti al meglio la città</li>
                                        <li>• <strong>Galleria:</strong> Aggiungi diverse foto che mostrino i punti di interesse, paesaggi e caratteristiche uniche</li>
                                        <li>• <strong>Ottimizzazione:</strong> Le immagini vengono ottimizzate automaticamente per il web</li>
                                        <li>• <strong>Coordinate GPS:</strong> Vai su Google Maps, cerca la città, clicca col tasto destro e seleziona "Cosa c'è qui?"</li>
                                    </ul>
                                </div>
                                
                                <!-- Pulsanti -->
                                <div class="flex items-center justify-end space-x-4 pt-6 border-t border-gray-200">
                                    <a href="citta.php" class="px-6 py-3 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                                        Annulla
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-semibold flex items-center space-x-2 transition-colors">
                                        <i data-lucide="<?php echo $action === 'edit' ? 'save' : 'plus'; ?>" class="w-4 h-4"></i>
                                        <span><?php echo $action === 'edit' ? 'Aggiorna' : 'Crea'; ?> Città</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Form nascosto per eliminazione immagini galleria -->
    <form id="deleteImageForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_gallery_image" id="imageToDelete">
    </form>

    <script>
        // Inizializza Lucide icons
        lucide.createIcons();

        // Auto-nascondere messaggi di successo dopo 5 secondi
        const successMessage = document.querySelector('.bg-green-50.border-l-4.border-green-400');
        if (successMessage) {
            setTimeout(() => {
                successMessage.style.transition = 'opacity 0.5s';
                successMessage.style.opacity = '0';
                setTimeout(() => {
                    successMessage.remove();
                }, 500);
            }, 5000);
        }

        // Auto-nascondere messaggi di errore dopo 8 secondi
        const errorMessage = document.querySelector('.bg-red-50');
        if (errorMessage) {
            setTimeout(() => {
                errorMessage.style.transition = 'opacity 0.5s';
                errorMessage.style.opacity = '0';
                setTimeout(() => {
                    errorMessage.remove();
                }, 500);
            }, 8000);
        }

        // Funzione per eliminare immagine dalla galleria
        function deleteGalleryImage(imagePath) {
            if (confirm('Sei sicuro di voler eliminare questa immagine dalla galleria?')) {
                document.getElementById('imageToDelete').value = imagePath;
                document.getElementById('deleteImageForm').submit();
            }
        }

        // Preview immagini prima dell'upload
        document.getElementById('hero_image')?.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                console.log('Hero image selected:', file.name, 'Size:', (file.size / 1024 / 1024).toFixed(2) + 'MB');
            }
        });

        document.getElementById('gallery_images')?.addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            if (files.length > 0) {
                console.log('Gallery images selected:', files.length, 'files');
                files.forEach(file => {
                    console.log('- ' + file.name, 'Size:', (file.size / 1024 / 1024).toFixed(2) + 'MB');
                });
            }
        });
    </script>
</body>
</html>