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
    // Standard fields
    $title = $_POST['title'] ?? '';
    $slug = $_POST['slug'] ?? '';
    $content = $_POST['content'] ?? '';
    $excerpt = $_POST['excerpt'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $province_id = $_POST['province_id'] ?? null;
    $city_id = $_POST['city_id'] ?? null;
    $status = $_POST['status'] ?? 'draft';
    $author = $_POST['author'] ?? 'Admin'; // In un sistema reale, questo verrebbe dall'utente loggato
    $posted_json_data = $_POST['json_data'] ?? [];

    $upload_error = '';

    // Se è una modifica, recupera i dati esistenti per gestire i file
    $existingArticle = null;
    if ($action === 'edit' && $id) {
        $existingArticle = $db->getArticleById($id);
    }

    // Inizializza i percorsi dei file con i valori esistenti (se presenti)
    $featured_image_path = $existingArticle['featured_image'] ?? null;
    $hero_image_path = $existingArticle['hero_image'] ?? null;
    $logo_path = $existingArticle['logo'] ?? null;
    $gallery_images_json = $existingArticle['gallery_images'] ?? null;

    $existing_json_data = json_decode($existingArticle['json_data'] ?? '{}', true);
    $menu_pdf_path = $existing_json_data['menu_pdf_path'] ?? null;


    // --- Gestione Upload Immagini con ImageProcessor ---

    // Featured Image
    if (!empty($_FILES['featured_image']['name'])) {
        $new_path = $imageProcessor->processUploadedImage($_FILES['featured_image'], 'articles/featured', 1280);
        if ($new_path) {
            if ($featured_image_path) $imageProcessor->deleteImage($featured_image_path);
            $featured_image_path = $new_path;
        } else { $upload_error = 'Errore caricamento immagine in evidenza.'; }
    }

    // Hero Image
    if (empty($upload_error) && !empty($_FILES['hero_image']['name'])) {
        $new_path = $imageProcessor->processUploadedImage($_FILES['hero_image'], 'articles/hero', 1920);
        if ($new_path) {
            if ($hero_image_path) $imageProcessor->deleteImage($hero_image_path);
            $hero_image_path = $new_path;
        } else { $upload_error = 'Errore caricamento immagine hero.'; }
    }

    // Logo
    if (empty($upload_error) && !empty($_FILES['logo']['name'])) {
        $new_path = $imageProcessor->processUploadedImage($_FILES['logo'], 'articles/logos', 400);
        if ($new_path) {
            if ($logo_path) $imageProcessor->deleteImage($logo_path);
            $logo_path = $new_path;
        } else { $upload_error = 'Errore caricamento logo.'; }
    }

    // Gallery Images
    if (empty($upload_error) && !empty($_FILES['gallery_images']['name'][0])) {
        $gallery_images = $gallery_images_json ? json_decode($gallery_images_json, true) : [];
        foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
            if (!empty($tmp_name)) {
                $file_data = ['name' => $_FILES['gallery_images']['name'][$key], 'type' => $_FILES['gallery_images']['type'][$key], 'tmp_name' => $tmp_name, 'error' => $_FILES['gallery_images']['error'][$key], 'size' => $_FILES['gallery_images']['size'][$key]];
                $new_gallery_path = $imageProcessor->processUploadedImage($file_data, 'articles/gallery', 1280);
                if ($new_gallery_path) {
                    $gallery_images[] = $new_gallery_path;
                } else {
                    $upload_error = 'Errore caricamento immagine galleria: ' . htmlspecialchars($file_data['name']);
                    break;
                }
            }
        }
        $gallery_images_json = json_encode($gallery_images);
    }
    
    // --- Gestione Upload File non Immagine (es. PDF) ---
    if (empty($upload_error) && !empty($_FILES['menu_pdf']['name'])) {
        $upload_dir = '../uploads/menus/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);

        $file_extension = strtolower(pathinfo($_FILES['menu_pdf']['name'], PATHINFO_EXTENSION));
        if ($file_extension === 'pdf') {
            $filename = 'menu_' . uniqid() . '_' . time() . '.pdf';
            $upload_path = $upload_dir . $filename;
            if (move_uploaded_file($_FILES['menu_pdf']['tmp_name'], $upload_path)) {
                if ($menu_pdf_path && file_exists('../' . $menu_pdf_path)) unlink('../' . $menu_pdf_path);
                $menu_pdf_path = 'uploads/menus/' . $filename;
            } else { $upload_error = 'Errore nel caricamento del PDF del menu.'; }
        } else { $upload_error = 'Formato file per menu non valido. Solo PDF è accettato.'; }
    }

    // Aggiungi il percorso del menu PDF al JSON data
    if ($menu_pdf_path) {
        $posted_json_data['menu_pdf_path'] = $menu_pdf_path;
    }
    $final_json_data = json_encode($posted_json_data);

    // --- Database Operation ---
    if (empty($upload_error)) {
        if ($action === 'edit' && $id) {
            $db->updateArticle($id, $title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $featured_image_path, $gallery_images_json, $hero_image_path, $logo_path, $final_json_data);
            $success_message = "Articolo aggiornato con successo!";
        } else {
            $db->createArticle($title, $slug, $content, $excerpt, $category_id, $province_id, $city_id, $status, $author, $featured_image_path, $gallery_images_json, $hero_image_path, $logo_path, $final_json_data);
            $success_message = "Articolo creato con successo!";
        }
        header('Location: articoli.php?success=' . urlencode($success_message));
        exit;
    } else {
        // Redirect with error
        $redirect_url = $action === 'edit' ? "articoli.php?action=edit&id=$id" : "articoli.php?action=new&category_id=$category_id";
        header("Location: $redirect_url&error=" . urlencode($upload_error));
        exit;
    }
}

if ($action === 'delete' && $id) {
    $article = $db->getArticleById($id);
    if ($article) {
        // Delete all associated images
        if ($article['featured_image']) $imageProcessor->deleteImage($article['featured_image']);
        if ($article['hero_image']) $imageProcessor->deleteImage($article['hero_image']);
        if ($article['logo']) $imageProcessor->deleteImage($article['logo']);
        if ($article['gallery_images']) {
            $gallery = json_decode($article['gallery_images'], true) ?: [];
            foreach ($gallery as $img) $imageProcessor->deleteImage($img);
        }
        // Delete associated PDF from json_data
        if ($article['json_data']) {
            $json_data = json_decode($article['json_data'], true);
            if (isset($json_data['menu_pdf_path']) && file_exists('../' . $json_data['menu_pdf_path'])) {
                unlink('../' . $json_data['menu_pdf_path']);
            }
        }
        $db->deleteArticle($id);
        $success_message = "Articolo eliminato con successo!";
    } else {
        $error_message = "Articolo non trovato.";
    }
    header('Location: articoli.php?success=' . urlencode($success_message ?? '') . '&error=' . urlencode($error_message ?? ''));
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
    <title>Gestione Articoli - Admin Panel</title>
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
                <h1 class="text-2xl font-bold text-gray-900">Gestione Articoli</h1>
                <?php if ($action === 'list'): ?>
                <a href="articoli.php?action=select_category" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center space-x-2"><i data-lucide="plus" class="w-4 h-4"></i><span>Nuovo Articolo</span></a>
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
                <h2 class="text-lg font-semibold mb-4">Elenco Articoli</h2>
                <div class="overflow-x-auto">
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
                            <tr class="border-b hover:bg-gray-50">
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
                                    $statusColors = ['published' => 'bg-green-100 text-green-800', 'draft' => 'bg-yellow-100 text-yellow-800', 'archived' => 'bg-gray-100 text-gray-800'];
                                    $statusClass = $statusColors[$article['status']] ?? 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-2">
                                    <div class="flex space-x-2">
                                        <a href="articoli.php?action=edit&id=<?php echo $article['id']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors"><i data-lucide="edit" class="w-3 h-3 mr-1"></i>Modifica</a>
                                        <a href="articoli.php?action=delete&id=<?php echo $article['id']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors" onclick="return confirm('Sei sicuro di voler eliminare questo articolo? Tutte le immagini e i file associati verranno eliminati.');"><i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>Elimina</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($action === 'select_category'):
                $categories = $db->getCategories();
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6 max-w-xl mx-auto">
                <h2 class="text-lg font-semibold mb-4">Crea Nuovo Articolo</h2>
                <p class="text-gray-600 mb-6">Seleziona la categoria per il nuovo articolo. Verrà mostrato il modulo di inserimento corretto in base alla tua scelta.</p>
                <form action="articoli.php" method="GET">
                    <input type="hidden" name="action" value="new">
                    <div class="mb-4">
                        <label for="category_id" class="block text-gray-700 font-bold mb-2">Seleziona Categoria</label>
                        <select name="category_id" id="category_id" class="w-full px-3 py-2 border rounded-lg" required>
                            <option value="" disabled selected>-- Scegli una categoria --</option>
                            <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-right">
                        <a href="articoli.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Procedi <i data-lucide="arrow-right" class="inline w-4 h-4 ml-1"></i></button>
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

                // Determina il template del form
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
                    'Trasporti' => 'form_trasporti.php',
                ];
                if (array_key_exists($cat_name, $form_map)) {
                    $form_template = $form_map[$cat_name];
                }

                $form_path = 'forms/' . $form_template;
                if (!file_exists($form_path)) {
                    die("Errore: Il template del form '$form_path' non è stato trovato.");
                }

                // Data per i form
                $categories = $db->getCategories();
                $provinces = $db->getProvinces();
                $cities = $db->getCities();
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-1"><?php echo $action === 'edit' ? 'Modifica Articolo' : 'Nuovo Articolo'; ?></h2>
                <p class="text-gray-500 mb-6 border-b pb-4">Categoria: <span class="font-bold text-blue-600"><?php echo htmlspecialchars($category_name); ?></span></p>

                <form action="articoli.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="category_id" value="<?php echo htmlspecialchars($category_id); ?>">
                    <?php include $form_path; // Carica il form dinamico ?>
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