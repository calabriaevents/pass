<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();

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
            $upload_dir = '../uploads/provinces/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($_FILES['image']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                $image_path = 'uploads/provinces/' . $filename;
            }
        }

        if ($action === 'edit' && $id) {
            $db->updateProvince($id, $name, $description, $image_path);
            $success_message = "Provincia aggiornata con successo!";
        } else {
            $db->createProvince($name, $description, $image_path);
            $success_message = "Provincia creata con successo!";
        }
    }
    
    // Gestione upload immagini galleria
    if ($entity === 'gallery' && isset($_POST['province_id'])) {
        $province_id = (int)$_POST['province_id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        if (isset($_FILES['gallery_image']) && $_FILES['gallery_image']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = '../uploads/galleries/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $filename = uniqid() . '-' . basename($_FILES['gallery_image']['name']);
            $target_file = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['gallery_image']['tmp_name'], $target_file)) {
                $image_path = 'uploads/galleries/' . $filename;
                $db->addProvinceGalleryImage($province_id, $image_path, $title, $description);
                $success_message = "Immagine aggiunta alla galleria con successo!";
            }
        }
    }
    
    if (empty($upload_error)) {
        header('Location: province.php?' . http_build_query($_GET));
        exit;
    }
}

if ($action === 'delete' && $id) {
    if ($entity === 'provinces') {
        $db->deleteProvince($id);
        $success_message = "Provincia eliminata con successo!";
    } elseif ($entity === 'gallery') {
        $db->deleteProvinceGalleryImage($id);
        $success_message = "Immagine eliminata dalla galleria con successo!";
    }
    header('Location: province.php?' . http_build_query(array_filter(['entity' => $_GET['entity'] ?? 'provinces', 'action' => $_GET['back_action'] ?? 'list', 'id' => $_GET['province_id'] ?? null])));
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

            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <?php if ($entity === 'provinces'): ?>
                    <?php if ($action === 'list'): ?>
                    <!-- Lista Province -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="bg-blue-100 p-2 rounded-lg">
                                    <i data-lucide="map" class="w-6 h-6 text-blue-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Elenco Province</h2>
                                    <p class="text-sm text-gray-500">Gestisci le province calabresi e le loro gallerie</p>
                                </div>
                            </div>

                            <?php 
                            $totalProvinces = $db->pdo->query('SELECT COUNT(*) FROM provinces')->fetchColumn();
                            ?>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900"><?php echo $totalProvinces; ?></div>
                                <div class="text-xs text-gray-500">Province Totali</div>
                            </div>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gray-50 border-b border-gray-200">
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Provincia</th>
                                    <th class="text-left py-3 px-6 font-semibold text-gray-700">Descrizione</th>
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
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-full flex items-center justify-center text-white font-semibold text-sm mr-3">
                                                <?php echo strtoupper(substr($province['name'], 0, 2)); ?>
                                            </div>
                                            <div>
                                                <div class="font-semibold text-gray-900"><?php echo htmlspecialchars($province['name']); ?></div>
                                                <div class="text-sm text-gray-500">ID: <?php echo $province['id']; ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="text-sm text-gray-600 max-w-md">
                                            <?php echo htmlspecialchars(substr($province['description'] ?: 'Nessuna descrizione disponibile', 0, 100)); ?>
                                            <?php if (strlen($province['description'] ?: '') > 100): ?>...<?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <?php if ($province['image_path']): ?>
                                        <div class="w-16 h-16 rounded-lg overflow-hidden">
                                            <img src="../<?php echo htmlspecialchars($province['image_path']); ?>" 
                                                 alt="<?php echo htmlspecialchars($province['name']); ?>" 
                                                 class="w-full h-full object-cover">
                                        </div>
                                        <?php else: ?>
                                        <div class="w-16 h-16 bg-gray-100 rounded-lg flex items-center justify-center">
                                            <i data-lucide="image" class="w-6 h-6 text-gray-400"></i>
                                        </div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-4 px-6 text-right">
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="?entity=provinces&action=edit&id=<?php echo $province['id']; ?>" 
                                               class="text-blue-600 hover:text-blue-700 font-medium text-sm flex items-center space-x-1 transition-colors">
                                                <i data-lucide="edit" class="w-4 h-4"></i>
                                                <span>Modifica</span>
                                            </a>
                                            <a href="?entity=gallery&action=manage&province_id=<?php echo $province['id']; ?>" 
                                               class="text-green-600 hover:text-green-700 font-medium text-sm flex items-center space-x-1 transition-colors">
                                                <i data-lucide="images" class="w-4 h-4"></i>
                                                <span>Galleria</span>
                                            </a>
                                            <a href="?entity=provinces&action=delete&id=<?php echo $province['id']; ?>" 
                                               class="text-red-600 hover:text-red-700 font-medium text-sm flex items-center space-x-1 transition-colors" 
                                               onclick="return confirm('Sei sicuro di voler eliminare questa provincia?');">
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

                    <?php if (empty($provinces)): ?>
                    <div class="text-center py-12">
                        <i data-lucide="map" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Nessuna provincia registrata</h3>
                        <p class="text-gray-500 mb-6">Inizia aggiungendo la prima provincia del sistema</p>
                        <a href="?entity=provinces&action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center space-x-2 transition-colors">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                            <span>Aggiungi Prima Provincia</span>
                        </a>
                    </div>
                    <?php endif; ?>

                    <?php elseif ($action === 'new' || $action === 'edit'):
                        $province = null;
                        if ($action === 'edit' && $id) {
                            $province = $db->getProvinceById($id);
                        }
                    ?>
                    <!-- Form Nuova/Modifica Provincia -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <a href="?entity=provinces" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            </a>
                            <div class="bg-blue-100 p-2 rounded-lg">
                                <i data-lucide="<?php echo $action === 'edit' ? 'edit' : 'plus'; ?>" class="w-6 h-6 text-blue-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">
                                    <?php echo $action === 'edit' ? 'Modifica Provincia' : 'Nuova Provincia'; ?>
                                </h2>
                                <p class="text-sm text-gray-500">
                                    <?php echo $action === 'edit' ? 'Aggiorna le informazioni della provincia' : 'Aggiungi una nuova provincia al sistema'; ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="max-w-2xl mx-auto">
                            <form action="?entity=provinces&action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                                <!-- Nome -->
                                <div>
                                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Nome Provincia *
                                    </label>
                                    <input type="text" name="name" id="name" required 
                                           value="<?php echo htmlspecialchars($province['name'] ?? ''); ?>"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                                           placeholder="Es: Cosenza, Catanzaro, Reggio Calabria...">
                                </div>
                                
                                <!-- Descrizione -->
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Descrizione
                                    </label>
                                    <textarea name="description" id="description" rows="4" 
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                                              placeholder="Descrizione della provincia, caratteristiche principali, attrazioni turistiche..."><?php echo htmlspecialchars($province['description'] ?? ''); ?></textarea>
                                </div>
                                
                                <!-- Immagine -->
                                <div>
                                    <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Immagine Rappresentativa
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors" id="upload-area">
                                        <input type="file" name="image" id="image" accept="image/*" class="hidden" onchange="previewImage(this)">
                                        
                                        <?php if (isset($province['image_path']) && $province['image_path']): ?>
                                        <div id="existing-image">
                                            <img src="../<?php echo htmlspecialchars($province['image_path']); ?>" 
                                                 alt="Immagine attuale" 
                                                 class="max-w-full max-h-64 mx-auto rounded-lg shadow-sm mb-4">
                                            <p class="text-sm text-gray-600 mb-2">Immagine attuale</p>
                                            <button type="button" onclick="document.getElementById('image').click()" 
                                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                                Cambia Immagine
                                            </button>
                                            <input type="hidden" name="existing_image_path" value="<?php echo htmlspecialchars($province['image_path']); ?>">
                                        </div>
                                        <?php else: ?>
                                        <div id="upload-prompt">
                                            <i data-lucide="upload" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                                            <p class="text-gray-600 mb-2">Clicca per selezionare un'immagine o trascinala qui</p>
                                            <p class="text-sm text-gray-500">PNG, JPG, WebP fino a 5MB</p>
                                            <button type="button" onclick="document.getElementById('image').click()" 
                                                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                                Seleziona Immagine
                                            </button>
                                        </div>
                                        <?php endif; ?>
                                        
                                        <div id="image-preview" class="hidden">
                                            <img id="preview-img" src="" alt="Anteprima" class="max-w-full max-h-64 mx-auto rounded-lg shadow-sm">
                                            <div class="mt-4">
                                                <button type="button" onclick="removePreview()" class="text-red-600 hover:text-red-700 font-semibold">
                                                    Rimuovi Immagine
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Pulsanti -->
                                <div class="flex items-center justify-end space-x-4 pt-6">
                                    <a href="?entity=provinces" class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                                        Annulla
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center space-x-2 transition-colors">
                                        <i data-lucide="<?php echo $action === 'edit' ? 'save' : 'plus'; ?>" class="w-4 h-4"></i>
                                        <span><?php echo $action === 'edit' ? 'Aggiorna' : 'Crea'; ?> Provincia</span>
                                    </button>
                                </div>
                            </form>
                        </div>
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
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <a href="?entity=provinces" class="text-gray-400 hover:text-gray-600 transition-colors">
                                    <i data-lucide="arrow-left" class="w-5 h-5"></i>
                                </a>
                                <div class="bg-green-100 p-2 rounded-lg">
                                    <i data-lucide="images" class="w-6 h-6 text-green-600"></i>
                                </div>
                                <div>
                                    <h2 class="text-lg font-semibold text-gray-900">Galleria: <?php echo htmlspecialchars($province['name']); ?></h2>
                                    <p class="text-sm text-gray-500">Gestisci le immagini della galleria per questa provincia</p>
                                </div>
                            </div>
                            <a href="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" 
                               class="bg-green-600 hover:bg-green-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors">
                                <i data-lucide="plus" class="w-5 h-5"></i>
                                <span>Aggiungi Immagine</span>
                            </a>
                        </div>
                    </div>
                    
                    <?php if (empty($gallery_images)): ?>
                    <div class="text-center py-12">
                        <i data-lucide="image-off" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">Nessuna immagine in galleria</h3>
                        <p class="text-gray-500 mb-6">Aggiungi la prima immagine per iniziare a costruire la galleria di <?php echo htmlspecialchars($province['name']); ?></p>
                        <a href="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" 
                           class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg inline-flex items-center space-x-2 transition-colors">
                            <i data-lucide="plus" class="w-5 h-5"></i>
                            <span>Aggiungi Prima Immagine</span>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                            <?php foreach ($gallery_images as $image): ?>
                            <div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200 hover:shadow-md transition-shadow">
                                <div class="aspect-[4/3] bg-gray-100">
                                    <img src="../<?php echo htmlspecialchars($image['image_path']); ?>" 
                                         alt="<?php echo htmlspecialchars($image['title']); ?>" 
                                         class="w-full h-full object-cover">
                                </div>
                                <div class="p-4">
                                    <h3 class="font-semibold text-gray-900 mb-1"><?php echo htmlspecialchars($image['title']); ?></h3>
                                    <p class="text-sm text-gray-600 mb-3"><?php echo htmlspecialchars($image['description']); ?></p>
                                    <div class="flex justify-between items-center">
                                        <span class="text-xs text-gray-500">
                                            <?php echo date('d/m/Y', strtotime($image['created_at'])); ?>
                                        </span>
                                        <a href="?entity=gallery&action=delete&id=<?php echo $image['id']; ?>&province_id=<?php echo $province_id; ?>&back_action=manage" 
                                           class="text-red-600 hover:text-red-700 font-semibold text-sm flex items-center space-x-1 transition-colors" 
                                           onclick="return confirm('Sei sicuro di voler eliminare questa immagine?');">
                                            <i data-lucide="trash-2" class="w-3 h-3"></i>
                                            <span>Elimina</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php elseif ($action === 'add' && $province_id): ?>
                    <!-- Form Aggiungi Immagine -->
                    <div class="px-6 py-4 border-b border-gray-200">
                        <div class="flex items-center space-x-3">
                            <a href="?entity=gallery&action=manage&province_id=<?php echo $province_id; ?>" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5"></i>
                            </a>
                            <div class="bg-green-100 p-2 rounded-lg">
                                <i data-lucide="plus" class="w-6 h-6 text-green-600"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-semibold text-gray-900">Aggiungi Immagine alla Galleria</h2>
                                <p class="text-sm text-gray-500"><?php echo htmlspecialchars($province['name']); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <div class="max-w-2xl mx-auto">
                            <form action="?entity=gallery&action=add&province_id=<?php echo $province_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-6">
                                <input type="hidden" name="province_id" value="<?php echo $province_id; ?>">
                                
                                <!-- Immagine -->
                                <div>
                                    <label for="gallery_image" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Immagine *
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-gray-400 transition-colors" id="upload-area">
                                        <input type="file" name="gallery_image" id="gallery_image" accept="image/*" class="hidden" onchange="previewGalleryImage(this)" required>
                                        <div id="upload-prompt">
                                            <i data-lucide="upload" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                                            <p class="text-gray-600 mb-2">Clicca per selezionare un'immagine o trascinala qui</p>
                                            <p class="text-sm text-gray-500">PNG, JPG, WebP fino a 5MB</p>
                                            <button type="button" onclick="document.getElementById('gallery_image').click()" 
                                                    class="mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                                                Seleziona Immagine
                                            </button>
                                        </div>
                                        <div id="image-preview" class="hidden">
                                            <img id="preview-img" src="" alt="Anteprima" class="max-w-full max-h-64 mx-auto rounded-lg shadow-sm">
                                            <div class="mt-4">
                                                <button type="button" onclick="removeGalleryPreview()" class="text-red-600 hover:text-red-700 font-semibold">
                                                    Rimuovi Immagine
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Titolo -->
                                <div>
                                    <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Titolo *
                                    </label>
                                    <input type="text" name="title" id="title" required 
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                                           placeholder="Es: Panorama di <?php echo htmlspecialchars($province['name']); ?>">
                                </div>
                                
                                <!-- Descrizione -->
                                <div>
                                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
                                        Descrizione
                                    </label>
                                    <textarea name="description" id="description" rows="3" 
                                              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors" 
                                              placeholder="Descrizione opzionale dell'immagine..."></textarea>
                                </div>
                                
                                <!-- Pulsanti -->
                                <div class="flex items-center justify-end space-x-4 pt-6">
                                    <a href="?entity=gallery&action=manage&province_id=<?php echo $province_id; ?>" 
                                       class="px-6 py-2 text-gray-600 hover:text-gray-800 font-medium transition-colors">
                                        Annulla
                                    </a>
                                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold flex items-center space-x-2 transition-colors">
                                        <i data-lucide="plus" class="w-4 h-4"></i>
                                        <span>Aggiungi Immagine</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Inizializza Lucide icons
        lucide.createIcons();
        
        // Funzioni per l'upload delle immagini
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validazione lato client
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type.toLowerCase())) {
                    alert('Tipo di file non supportato. Utilizzare JPEG, PNG o WebP.');
                    input.value = '';
                    return;
                }
                
                if (file.size > maxSize) {
                    alert('Il file è troppo grande. Dimensione massima: 5MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Nascondi il prompt di upload
                    document.getElementById('upload-prompt').classList.add('hidden');
                    
                    // Nascondi l'immagine esistente se presente
                    const existingImage = document.getElementById('existing-image');
                    if (existingImage) {
                        existingImage.classList.add('hidden');
                    }
                    
                    // Mostra la preview
                    const preview = document.getElementById('image-preview');
                    const previewImg = document.getElementById('preview-img');
                    
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
        
        function removePreview() {
            // Reset input file
            document.getElementById('image').value = '';
            
            // Nascondi preview
            document.getElementById('image-preview').classList.add('hidden');
            
            // Mostra di nuovo il prompt
            document.getElementById('upload-prompt').classList.remove('hidden');
            
            // Mostra di nuovo l'immagine esistente se presente
            const existingImage = document.getElementById('existing-image');
            if (existingImage) {
                existingImage.classList.remove('hidden');
            }
        }
        
        // Funzioni per la gestione delle immagini galleria
        function previewGalleryImage(input) {
            if (input.files && input.files[0]) {
                const file = input.files[0];
                
                // Validazione lato client
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
                const maxSize = 5 * 1024 * 1024; // 5MB
                
                if (!allowedTypes.includes(file.type.toLowerCase())) {
                    alert('Tipo di file non supportato. Utilizzare JPEG, PNG o WebP.');
                    input.value = '';
                    return;
                }
                
                if (file.size > maxSize) {
                    alert('Il file è troppo grande. Dimensione massima: 5MB.');
                    input.value = '';
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    // Nascondi il prompt di upload
                    document.getElementById('upload-prompt').classList.add('hidden');
                    
                    // Mostra la preview
                    const preview = document.getElementById('image-preview');
                    const previewImg = document.getElementById('preview-img');
                    
                    previewImg.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        }
        
        function removeGalleryPreview() {
            // Reset input file
            const fileInput = document.getElementById('gallery_image');
            if (fileInput) {
                fileInput.value = '';
            }
            
            // Nascondi preview
            const imagePreview = document.getElementById('image-preview');
            if (imagePreview) {
                imagePreview.classList.add('hidden');
            }
            
            // Mostra di nuovo il prompt
            const uploadPrompt = document.getElementById('upload-prompt');
            if (uploadPrompt) {
                uploadPrompt.classList.remove('hidden');
            }
        }
        
        // Drag & Drop functionality
        const uploadArea = document.getElementById('upload-area');
        if (uploadArea) {
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                uploadArea.classList.add('border-blue-500', 'bg-blue-50');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                uploadArea.classList.remove('border-blue-500', 'bg-blue-50');
                
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    const imageInput = document.getElementById('image') || document.getElementById('gallery_image');
                    if (imageInput) {
                        imageInput.files = files;
                        if (imageInput.id === 'image') {
                            previewImage(imageInput);
                        } else {
                            previewGalleryImage(imageInput);
                        }
                    }
                }
            });
        }
        
        // Auto-nascondere messaggi di successo dopo 5 secondi
        const successMessage = document.querySelector('.bg-green-50');
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
    </script>
</body>
</html>