<?php
require_once __DIR__ . '/auth_check.php';
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php';

$db = new Database();
$imageProcessor = new ImageProcessor();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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

        // Prepara i percorsi delle immagini
        $featured_image = null;
        $hero_image = null;
        $logo = null;
        $gallery_images_json = null;
        $menu_pdf = null;

        // Se siamo in modifica, carica i dati esistenti per confronto
        $existingArticle = null;
        if ($action === 'edit' && $id) {
            $existingArticle = $db->getArticleById($id);
            // Inizializza con i valori esistenti
            $featured_image = $existingArticle['featured_image'] ?? null;
            $hero_image = $existingArticle['hero_image'] ?? null;
            $logo = $existingArticle['logo'] ?? null;
            $gallery_images_json = $existingArticle['gallery_images'] ?? null;
        }

        // Gestione immagine in evidenza
        if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] === UPLOAD_ERR_OK) {
            $new_featured_image = $imageProcessor->processUploadedImage($_FILES['featured_image'], 'articles/featured');
            if (!$new_featured_image) throw new Exception("Errore caricamento immagine in evidenza: " . $imageProcessor->getLastError());
            // Se c'era una vecchia immagine, cancellala
            if ($existingArticle && !empty($existingArticle['featured_image'])) {
                $imageProcessor->deleteImage($existingArticle['featured_image']);
            }
            $featured_image = $new_featured_image;
        }

        // Gestione immagine hero
        if (isset($_FILES['hero_image']) && $_FILES['hero_image']['error'] === UPLOAD_ERR_OK) {
            $new_hero_image = $imageProcessor->processUploadedImage($_FILES['hero_image'], 'articles/hero');
            if (!$new_hero_image) throw new Exception("Errore caricamento immagine hero: " . $imageProcessor->getLastError());
            if ($existingArticle && !empty($existingArticle['hero_image'])) {
                $imageProcessor->deleteImage($existingArticle['hero_image']);
            }
            $hero_image = $new_hero_image;
        }

        // Gestione logo
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $new_logo = $imageProcessor->processUploadedImage($_FILES['logo'], 'articles/logos');
            if (!$new_logo) throw new Exception("Errore caricamento logo: " . $imageProcessor->getLastError());
            if ($existingArticle && !empty($existingArticle['logo'])) {
                $imageProcessor->deleteImage($existingArticle['logo']);
            }
            $logo = $new_logo;
        }

        // Gestione galleria
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $galleryPaths = ($gallery_images_json) ? json_decode($gallery_images_json, true) : [];
            $files = $_FILES['gallery_images'];
            foreach ($files['tmp_name'] as $key => $tmpName) {
                if ($files['error'][$key] === UPLOAD_ERR_OK) {
                    $file_info = ['name' => $files['name'][$key], 'type' => $files['type'][$key], 'tmp_name' => $tmpName, 'error' => $files['error'][$key], 'size' => $files['size'][$key]];
                    $gallery_path = $imageProcessor->processUploadedImage($file_info, 'articles/gallery');
                    if ($gallery_path) {
                        $galleryPaths[] = $gallery_path;
                    } else {
                        throw new Exception("Errore in un'immagine della galleria: " . $imageProcessor->getLastError());
                    }
                }
            }
            $gallery_images_json = json_encode($galleryPaths);
        }

        // Gestione Menu PDF
        if (isset($_FILES['menu_pdf']) && $_FILES['menu_pdf']['error'] === UPLOAD_ERR_OK) {
            $uploadDirMenus = '../uploads/menus/';
            if (!is_dir($uploadDirMenus)) mkdir($uploadDirMenus, 0755, true);
            $fileName = 'menu_' . uniqid() . '.pdf';
            $targetPath = $uploadDirMenus . $fileName;
            if (move_uploaded_file($_FILES['menu_pdf']['tmp_name'], $targetPath)) {
                $menu_pdf = str_replace('../', '', $uploadDirMenus) . $fileName;
                $posted_json_data['menu_pdf_path'] = $menu_pdf;
            } else {
                 throw new Exception("Errore nel salvataggio del PDF.");
            }
        }

        $json_data = json_encode($posted_json_data);

        if ($action === 'edit' && $id) {
            // Se non è stato caricato un nuovo PDF, mantieni il vecchio valore nel JSON
             $existing_json = json_decode($existingArticle['json_data'] ?? '{}', true);
             if ($menu_pdf === null && isset($existing_json['menu_pdf_path'])) {
                  $decoded_json = json_decode($json_data, true);
                  $decoded_json['menu_pdf_path'] = $existing_json['menu_pdf_path'];
                  $json_data = json_encode($decoded_json);
             }
            $db->updateArticle($id, $title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $featured_image, $gallery_images_json, $hero_image, $logo, $json_data);
        } else {
            $db->createArticle($title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $author, $featured_image, $gallery_images_json, $hero_image, $logo, $json_data);
        }

        header('Location: articoli.php?success=1');
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

if ($action === 'delete' && $id) {
    // Aggiungere qui la logica per eliminare le immagini associate prima di eliminare l'articolo
    $db->deleteArticle($id);
    header('Location: articoli.php?success=1');
    exit;
}

if (isset($_GET['success'])) {
    $success_message = "Operazione completata con successo!";
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

            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Articoli</h2>
                <table class="w-full">
                    <tbody>
                        <?php
                        $articles = $db->getArticles(null, 0, false);
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
                                    <a href="articoli.php?action=edit&id=<?php echo $article['id']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                                        <i data-lucide="edit" class="w-3 h-3 mr-1"></i>
                                        Modifica
                                    </a>
                                    <a href="articoli.php?action=delete&id=<?php echo $article['id']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors" onclick="return confirm('Sei sicuro di voler eliminare questo articolo?');">
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
                // For new articles, category_id must be set from the previous step
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

                // Determine which form to load based on Category NAME for robustness
                $form_template = 'form_default.php'; // Default form
                $cat_name = isset($category) ? trim($category['name']) : '';

                if ($cat_name === 'Hotel e Alloggi') {
                    $form_template = 'form_hotel.php';
                } else if ($cat_name === 'Ristorazione') {
                    $form_template = 'form_ristorazione.php';
                } else if ($cat_name === 'Stabilimenti Balneari') {
                    $form_template = 'form_stabilimenti.php';
                } else if ($cat_name === 'Arte e Cultura') {
                    $form_template = 'form_arte_cultura.php';
                } else if ($cat_name === 'Musei e Gallerie') {
                    $form_template = 'form_musei_gallerie.php';
                } else if ($cat_name === 'Patrimonio Storico') {
                    $form_template = 'form_patrimonio_storico.php';
                } else if ($cat_name === 'Piazze e Vie Storiche') {
                    $form_template = 'form_piazze_vie_storiche.php';
                } else if ($cat_name === 'Siti Archeologici') {
                    $form_template = 'form_siti_archeologici.php';
                } else if ($cat_name === 'Chiese e Santuari') {
                    $form_template = 'form_chiese_santuari.php';
                } else if ($cat_name === 'Teatri e Anfiteatri') {
                    $form_template = 'form_teatri_anfiteatri.php';
                } else if ($cat_name === 'Parchi e Aree Verdi') {
                    $form_template = 'form_parchi_aree_verdi.php';
                } else if ($cat_name === 'Attività Sportive e Avventura') {
                    $form_template = 'form_attivita_sportive_avventura.php';
                } else if ($cat_name === 'Itinerari Tematici') {
                    $form_template = 'form_itinerari_tematici.php';
                } else if ($cat_name === 'Tour e Guide') {
                    $form_template = 'form_tour_guide.php';
                } else if ($cat_name === 'Shopping e Artigianato') {
                    $form_template = 'form_shopping_artigianato.php';
                } else if ($cat_name === 'Benessere e Relax') {
                    $form_template = 'form_benessere_relax.php';
                } else if ($cat_name === 'Trasporti') {
                    $form_template = 'form_trasporti.php';
                }

                $form_path = 'forms/' . $form_template;
                if (!file_exists($form_path)) {
                    $form_path = 'forms/form_default.php'; // Fallback
                    if (!file_exists($form_path)) {
                        die("Errore critico: Il form di default non è stato trovato.");
                    }
                }

                // Data needed by the form
                $categories = $db->getCategories();
                $provinces = $db->getProvinces();
                $cities = $db->getCities();
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <form action="articoli.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">
                    <?php
                    include $form_path;
                    ?>
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