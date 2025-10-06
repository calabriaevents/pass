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
    $response = ['success' => false, 'message' => 'Errore generico.'];

    $current_action = $_POST['action'] ?? null;
    $entity_id = $_POST['id'] ?? null;

    // Aggiunta o Modifica Articolo
    if (isset($_POST['title'])) {
        $title = $_POST['title'] ?? '';
        $slug = $_POST['slug'] ?? '';
        $content = $_POST['content'] ?? '';
        $excerpt = $_POST['excerpt'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        $province_id = $_POST['province_id'] ?? null;
        $city_id = $_POST['city_id'] ?? null;
        $status = $_POST['status'] ?? 'draft';
        $author = $_POST['author'] ?? 'Admin'; // Dovrebbe essere l'utente loggato
        $posted_json_data = $_POST['json_data'] ?? [];

        // Recupera dati esistenti in caso di modifica
        $existingArticle = null;
        if ($current_action === 'edit' && $entity_id) {
            $existingArticle = $db->getArticleById($entity_id);
        }

        // Inizializza i percorsi delle immagini con i valori esistenti (se presenti)
        $featured_image = $existingArticle['featured_image'] ?? null;
        $hero_image = $existingArticle['hero_image'] ?? null;
        $logo = $existingArticle['logo'] ?? null;
        $gallery_images_json = $existingArticle['gallery_images'] ?? null;

        // Processa nuova immagine in evidenza
        if (!empty($_FILES['featured_image']['name'])) {
            $new_featured_image = $imageProcessor->processUploadedImage($_FILES['featured_image'], 'articles', 1200);
            if ($new_featured_image) {
                if ($featured_image) $imageProcessor->deleteImage($featured_image);
                $featured_image = $new_featured_image;
            }
        }

        // Processa nuova immagine hero
        if (!empty($_FILES['hero_image']['name'])) {
            $new_hero_image = $imageProcessor->processUploadedImage($_FILES['hero_image'], 'articles', 1920);
            if ($new_hero_image) {
                if ($hero_image) $imageProcessor->deleteImage($hero_image);
                $hero_image = $new_hero_image;
            }
        }

        // Processa nuovo logo
        if (!empty($_FILES['logo']['name'])) {
            $new_logo = $imageProcessor->processUploadedImage($_FILES['logo'], 'articles/logos', 500);
            if ($new_logo) {
                if ($logo) $imageProcessor->deleteImage($logo);
                $logo = $new_logo;
            }
        }

        // Processa galleria immagini
        if (!empty($_FILES['gallery_images']['name'][0])) {
            $gallery_images = $gallery_images_json ? json_decode($gallery_images_json, true) : [];
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if (!empty($tmp_name)) {
                    $file_data = ['name' => $_FILES['gallery_images']['name'][$key], 'tmp_name' => $tmp_name, 'error' => $_FILES['gallery_images']['error'][$key]];
                    $new_gallery_path = $imageProcessor->processUploadedImage($file_data, 'articles/gallery', 1280);
                    if ($new_gallery_path) {
                        $gallery_images[] = $new_gallery_path;
                    }
                }
            }
            $gallery_images_json = json_encode($gallery_images);
        }

        // Gestione PDF Menu (non usa ImageProcessor)
        $existing_json = $existingArticle ? json_decode($existingArticle['json_data'], true) : [];
        if (!empty($_FILES['menu_pdf']['name'])) {
            $uploadDirMenus = '../uploads/menus/';
            if (!is_dir($uploadDirMenus)) mkdir($uploadDirMenus, 0755, true);
            $fileName = 'menu_' . uniqid() . '.pdf';
            if (move_uploaded_file($_FILES['menu_pdf']['tmp_name'], $uploadDirMenus . $fileName)) {
                if (isset($existing_json['menu_pdf_path']) && file_exists('../' . $existing_json['menu_pdf_path'])) {
                    unlink('../' . $existing_json['menu_pdf_path']);
                }
                $posted_json_data['menu_pdf_path'] = 'uploads/menus/' . $fileName;
            }
        } elseif (isset($existing_json['menu_pdf_path'])) {
            $posted_json_data['menu_pdf_path'] = $existing_json['menu_pdf_path'];
        }

        $json_data = json_encode($posted_json_data);

        // Operazione sul Database
        if ($current_action === 'edit' && $entity_id) {
            $db->updateArticle($entity_id, $title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $featured_image, $gallery_images_json, $hero_image, $logo, $json_data);
            $response = ['success' => true, 'message' => 'Articolo aggiornato con successo.'];
        } else {
            $db->createArticle($title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $author, $featured_image, $gallery_images_json, $hero_image, $logo, $json_data);
            $response = ['success' => true, 'message' => 'Articolo creato con successo.'];
        }
    }

    // Eliminazione Articolo (ricevuta via AJAX)
    elseif ($current_action === 'delete_article' && $entity_id) {
        $article = $db->getArticleById($entity_id);
        if ($article) {
            if ($article['featured_image']) $imageProcessor->deleteImage($article['featured_image']);
            if ($article['hero_image']) $imageProcessor->deleteImage($article['hero_image']);
            if ($article['logo']) $imageProcessor->deleteImage($article['logo']);
            if ($article['gallery_images']) {
                $gallery = json_decode($article['gallery_images'], true);
                foreach ($gallery as $img) $imageProcessor->deleteImage($img);
            }
        }
        if ($db->deleteArticle($entity_id)) {
            $response = ['success' => true, 'message' => 'Articolo eliminato con successo.'];
        } else {
            $response = ['success' => false, 'message' => 'Impossibile eliminare l\'articolo.'];
        }
    }

    echo json_encode($response);
    exit;
}

// Gestione eliminazione (da link, GET request) - Mantenuto per fallback o JS disabilitato
if ($action === 'delete' && $id) {
    $article = $db->getArticleById($id);
    if ($article) {
        if ($article['featured_image']) $imageProcessor->deleteImage($article['featured_image']);
        if ($article['hero_image']) $imageProcessor->deleteImage($article['hero_image']);
        if ($article['logo']) $imageProcessor->deleteImage($article['logo']);
        if ($article['gallery_images']) {
            $gallery = json_decode($article['gallery_images'], true);
            foreach ($gallery as $img) $imageProcessor->deleteImage($img);
        }
    }
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
                <h1 class="text-2xl font-bold text-gray-900">Gestione Articoli</h1>
                <?php if ($action === 'list'): ?>
                <a href="articoli.php?action=select_category" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Nuovo Articolo</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <div id="notification-placeholder" class="mb-4"></div>
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Articoli</h2>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
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
                        <tr class="border-b hover:bg-gray-50" id="article-row-<?php echo $article['id']; ?>">
                            <td class="py-3 px-2">
                                <div class="flex items-center space-x-3">
                                    <?php if (!empty($article['featured_image'])): ?>
                                    <img src="../<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-12 h-12 object-cover rounded-lg border">
                                    <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg border flex items-center justify-center">
                                        <i data-lucide="image" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium"><?php echo htmlspecialchars($article['title']); ?></div>
                                        <div class="text-sm text-gray-500">di <?php echo htmlspecialchars($article['author']); ?></div>
                                    </div>
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
                                    <button onclick="deleteArticle(<?php echo $article['id']; ?>)"
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors">
                                        <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>
                                        Elimina
                                    </button>
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
                <h2 class="text-lg font-semibold mb-1">
                    <?php echo $action === 'edit' ? 'Modifica Articolo' : 'Nuovo Articolo'; ?>
                </h2>
                <p class="text-gray-500 mb-6 border-b pb-4">
                    Categoria: <span class="font-bold text-blue-600"><?php echo htmlspecialchars($category_name); ?></span>
                </p>

                <form id="article-form" action="articoli.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <input type="hidden" name="id" value="<?php echo $id; ?>">
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

    <script src="../assets/js/main.js"></script>
    <script>
        lucide.createIcons();

        function deleteArticle(articleId) {
            if (confirm('Sei sicuro di voler eliminare questo articolo? Tutte le immagini e i file associati verranno eliminati definitivamente.')) {
                const formData = new FormData();
                formData.append('action', 'delete_article');
                formData.append('id', articleId);

                fetch('articoli.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const notificationPlaceholder = document.getElementById('notification-placeholder');
                    PassioneCalabria.showNotification(data.message, data.success ? 'success' : 'error', notificationPlaceholder);
                    if (data.success) {
                        const row = document.getElementById('article-row-' + articleId);
                        if(row) {
                            row.remove();
                        }
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
