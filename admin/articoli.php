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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $province_id = $_POST['province_id'] ?? null;
    $city_id = $_POST['city_id'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $author = $_POST['author'] ?? 'Admin';
    $posted_json_data = isset($_POST['json_data']) && is_array($_POST['json_data']) ? $_POST['json_data'] : [];

    // --- GESTIONE UPLOAD SICURA ---

    // Inizializza tutte le variabili dei file a null
    $featured_image = null;
    $hero_image = null;
    $logo = null;
    $menu_pdf = null; // Variabile per il PDF del menu

    // Gestione immagini con ImageProcessor
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
        $featured_image = $imageProcessor->processUploadedImage($_FILES['featured_image'], 'articles/featured');
    }

    if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
        $hero_image = $imageProcessor->processUploadedImage($_FILES['hero_image'], 'articles/hero');
    }

    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $logo = $imageProcessor->processUploadedImage($_FILES['logo'], 'articles/logos');
    }

    // Gestione Menu PDF (salvato ancora in 'uploads' pubblici per ora)
    if (isset($_FILES['menu_pdf']) && $_FILES['menu_pdf']['error'] === UPLOAD_ERR_OK) {
        $uploadDirMenus = '../uploads/menus/';
        if (!is_dir($uploadDirMenus)) {
            mkdir($uploadDirMenus, 0755, true);
        }
        $fileName = 'menu_' . uniqid() . '.pdf';
        $targetPath = $uploadDirMenus . $fileName;
        if (move_uploaded_file($_FILES['menu_pdf']['tmp_name'], $targetPath)) {
            $menu_pdf = str_replace('../', '', $uploadDirMenus) . $fileName;
            $posted_json_data['menu_pdf_path'] = $menu_pdf;
        }
    }

    // Gestione galleria immagini con ImageProcessor
    $gallery_images = null;
    if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
        $galleryPaths = [];
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
                $gallery_path = $imageProcessor->processUploadedImage($file_info, 'articles/gallery');
                if ($gallery_path) {
                    $galleryPaths[] = $gallery_path;
                }
            }
        }
        if (!empty($galleryPaths)) {
            $gallery_images = json_encode($galleryPaths);
        }
    }
    
    // Codifica finale dei dati JSON
    $json_data = json_encode($posted_json_data);

    // --- OPERAZIONI SUL DATABASE ---
    if ($action === 'edit' && $id) {
        $existingArticle = $db->getArticleById($id);

        // Se non è stata caricata una nuova immagine, mantiene la vecchia
        if ($featured_image === null) $featured_image = $existingArticle['featured_image'] ?? null;
        if ($hero_image === null) $hero_image = $existingArticle['hero_image'] ?? null;
        if ($logo === null) $logo = $existingArticle['logo'] ?? null;
        if ($gallery_images === null) $gallery_images = $existingArticle['gallery_images'] ?? null;

        // Gestione speciale per il percorso del menu_pdf nel JSON
        $existing_json = json_decode($existingArticle['json_data'] ?? '{}', true);
        if ($menu_pdf === null && isset($existing_json['menu_pdf_path'])) {
             $decoded_json = json_decode($json_data, true);
             $decoded_json['menu_pdf_path'] = $existing_json['menu_pdf_path'];
             $json_data = json_encode($decoded_json);
        }

        $db->updateArticle($id, $title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $featured_image, $gallery_images, $hero_image, $logo, $json_data);
    } else {
        $db->createArticle($title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $author, $featured_image, $gallery_images, $hero_image, $logo, $json_data);
    }

    header('Location: articoli.php');
    exit;
}

if ($action === 'delete' && $id) {
    // Aggiungere qui la logica per eliminare le immagini associate prima di eliminare l'articolo
    $db->deleteArticle($id);
    header('Location: articoli.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Articoli - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-100 flex">
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

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Gestione Articoli</h1>
                <?php if ($action === 'list'): ?>
                <a href="articoli.php?action=select_category" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Nuovo Articolo</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Articoli</h2>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Logo</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Articolo</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Categoria</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Stato</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $articles = $db->getArticles(null, 0, false); // Get all articles
                        foreach ($articles as $article):
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <div class="flex items-center space-x-3">
                                    <?php if (!empty($article['logo'])): ?>
                                    <img src="../image-loader.php?path=<?php echo urlencode($article['logo']); ?>" alt="Logo <?php echo htmlspecialchars($article['title']); ?>" class="w-12 h-12 object-contain rounded-lg border p-1">
                                    <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg border flex items-center justify-center">
                                        <i data-lucide="image-off" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <div>
                                    <div class="font-medium"><?php echo htmlspecialchars($article['title']); ?></div>
                                    <div class="text-sm text-gray-500">di <?php echo htmlspecialchars($article['author']); ?></div>
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo htmlspecialchars($article['category_name']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <?php 
                                $statusColors = [
                                    'published' => 'bg-green-100 text-green-800',
                                    'draft' => 'bg-yellow-100 text-yellow-800',
                                    'archived' => 'bg-gray-100 text-gray-800'
                                ];
                                $statusClass = $statusColors[$article['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($article['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex space-x-2">
                                    <a href="articoli.php?action=edit&id=<?php echo $article['id']; ?>" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                                        <i data-lucide="edit" class="w-3 h-3 mr-1"></i>
                                        Modifica
                                    </a>
                                    <a href="articoli.php?action=delete&id=<?php echo $article['id']; ?>" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors"
                                       onclick="return confirm('Sei sicuro di voler eliminare questo articolo? Tutte le immagini associate verranno eliminate.');">    
                                        <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>
                                        Elimina
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($action === 'select_category'):
                $categories = $db->getCategories();
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6 max-w-xl mx-auto">
                <h2 class="text-lg font-semibold mb-4">Crea Nuovo Articolo</h2>
                <p class="text-gray-600 mb-6">Come primo passo, seleziona la categoria per la quale vuoi creare un nuovo articolo. A seconda della categoria, ti verrà mostrato un modulo di inserimento differente.</p>
                <form action="articoli.php" method="GET">
                    <input type="hidden" name="action" value="new">
                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-700 font-bold mb-2">Seleziona Categoria</label>
                        <select name="category_id" id="category_id" class="w-full px-3 py-2 border rounded-lg" required>
                            <option value="" disabled selected>-- Scegli una categoria --</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-right">
                        <a href="articoli.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            Procedi
                            <i data-lucide="arrow-right" class="inline w-4 h-4 ml-1"></i>
                        </button>
                    </div>
                </form>
            </div>

            <?php elseif ($action === 'new' || $action === 'edit'):
                if ($action === 'new' && !isset($_GET['category_id'])) {
                    header('Location: articoli.php?action=select_category');
                    exit;
                }

                $article = null;
                $category_id = $_GET['category_id'] ?? null;
                $category_name = 'Default';

                if ($action === 'edit' && $id) {
                    $article = $db->getArticleById($id);
                    $category_id = $article['category_id'];
                }

                if ($category_id) {
                    $category = $db->getCategoryById($category_id);
                    $category_name = $category['name'] ?? 'Sconosciuta';
                }

                $form_template = 'form_default.php';
                $cat_name = isset($category) ? trim($category['name']) : '';

                $form_map = [
                    'Hotel e Alloggi' => 'form_hotel.php',
                    'Ristorazione' => 'form_ristorazione.php',
                    'Stabilimenti Balneari' => 'form_stabilimenti.php',
                    'Arte e Cultura' => 'form_arte_cultura.php',
                    'Musei e Gallerie' => 'form_musei_gallerie.php',
                    'Patrimonio Storico' => 'form_patrimonio_storico.php',
                    'Piazze e Vie Storiche' => 'form_piazze_vie_storiche.php',
                    'Siti Archeologici' => 'form_siti_archeologici.php',
                    'Chiese e Santuari' => 'form_chiese_santuari.php',
                    'Teatri e Anfiteatri' => 'form_teatri_anfiteatri.php',
                    'Parchi e Aree Verdi' => 'form_parchi_aree_verdi.php',
                    'Attività Sportive e Avventura' => 'form_attivita_sportive_avventura.php',
                    'Itinerari Tematici' => 'form_itinerari_tematici.php',
                    'Tour e Guide' => 'form_tour_guide.php',
                    'Shopping e Artigianato' => 'form_shopping_artigianato.php',
                    'Benessere e Relax' => 'form_benessere_relax.php',
                    'Trasporti' => 'form_trasporti.php'
                ];

                if (array_key_exists($cat_name, $form_map)) {
                    $form_template = $form_map[$cat_name];
                }

                $form_path = 'forms/' . $form_template;
                if (!file_exists($form_path)) {
                    $form_path = 'forms/form_default.php';
                }

                $categories = $db->getCategories();
                $provinces = $db->getProvinces();
                $cities = $db->getCities();
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-1">
                    <?php echo $action === 'edit' ? 'Modifica Articolo' : 'Nuovo Articolo'; ?>
                </h2>
                <p class="text-gray-500 mb-6 border-b pb-4">
                    Categoria: <span class="font-bold text-blue-600"><?php echo htmlspecialchars($category_name); ?></span>
                </p>

                <form action="articoli.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">
                    <?php include $form_path; ?>
                    <div class="text-right mt-6 border-t pt-4">
                        <a href="articoli.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva Articolo</button>
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