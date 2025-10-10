<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
require_once 'includes/image_processor.php';

$db = new Database();
$imageProcessor = new ImageProcessor();

// Verifica se l'ID città è fornito
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: citta.php");
    exit;
}

$cityId = (int)$_GET['id'];

// Carica dati città
$city = $db->getCityById($cityId);
if (!$city) {
    header("Location: citta.php");
    exit;
}

// Gestione upload foto utenti
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'upload_photo') {
    $user_name = trim($_POST['user_name'] ?? '');
    $user_email = trim($_POST['user_email'] ?? '');
    $description = trim($_POST['description'] ?? '');

    $upload_error = '';
    $success_message = '';

    try {
        if (empty($user_name) || empty($user_email)) {
            throw new Exception('Nome e email sono obbligatori.');
        }
        if (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Email non valida.');
        }
        if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Seleziona una foto valida da caricare.');
        }

        // Usa ImageProcessor per gestire l'upload
        $upload_path = $imageProcessor->processUploadedImage($_FILES['photo'], 'user_photos');

        if ($upload_path) {
            // Salva nel database
            if ($db->createCityPhotoUpload($cityId, $user_name, $user_email, $upload_path, $_FILES['photo']['name'], $description)) {
                $success_message = 'Foto caricata con successo! Verrà pubblicata dopo la moderazione.';
            } else {
                // Se il DB fallisce, cancella l'immagine appena caricata
                $imageProcessor->deleteImage($upload_path);
                throw new Exception('Errore nel salvare le informazioni della foto nel database.');
            }
        } else {
            throw new Exception('Errore nel caricamento del file: ' . $imageProcessor->getLastError());
        }
    } catch (Exception $e) {
        $upload_error = $e->getMessage();
    }
}

// Gestione commenti città
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_comment') {
    $user_name = trim($_POST['comment_name'] ?? '');
    $user_email = trim($_POST['comment_email'] ?? '');
    $comment_content = trim($_POST['comment_content'] ?? '');
    $rating = isset($_POST['rating']) ? (int)$_POST['rating'] : null;
    
    $comment_error = '';
    $comment_success = '';
    
    if (empty($user_name) || empty($user_email) || empty($comment_content)) {
        $comment_error = 'Nome, email e commento sono obbligatori.';
    } elseif (!filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        $comment_error = 'Email non valida.';
    } elseif (strlen($comment_content) < 10) {
        $comment_error = 'Il commento deve essere di almeno 10 caratteri.';
    } else {
        if ($db->createCityComment($cityId, $user_name, $user_email, $comment_content, $rating)) {
            $comment_success = 'Commento aggiunto! Verrà pubblicato dopo la moderazione.';
        } else {
            $comment_error = 'Errore nel salvare il commento.';
        }
    }
}

// Carica tutti gli articoli per la città
$articles = $db->getArticlesByCity($cityId);
$articleCount = count($articles);

// Carica tutte le categorie
$allCategories = $db->getCategories();

// Raggruppa articoli per categoria - SOLO per questa città
$articlesByCategory = [];
foreach ($articles as $article) {
    $articlesByCategory[$article['category_id']][] = $article;
}

// Determina l'immagine hero per la città
$rawHeroImage = $city['hero_image'] ?: 'assets/images/default-city-hero.jpg';
if (empty($city['hero_image']) && !empty($articles)) {
    $rawHeroImage = $articles[0]['featured_image'] ?? $rawHeroImage;
}

// Pulisci il percorso e usa image-loader.php se non è un'immagine di default
if (strpos($rawHeroImage, 'assets/') === 0) {
    $heroImage = htmlspecialchars($rawHeroImage);
} else {
    $clean_path = str_replace(['uploads_protected/', 'uploads/'], '', $rawHeroImage ?? '');
    $heroImage = 'image-loader.php?path=' . urlencode($clean_path);
}

// Carica foto utenti approvate per la città
$userPhotos = $db->getApprovedCityPhotos($cityId);

// --- INIZIO UNIFICA GALLERIE ---
$adminGalleryImages = !empty($city['gallery_images']) ? json_decode($city['gallery_images'], true) : [];
$allGalleryImages = [];

// Aggiungi le immagini dell'admin
foreach ($adminGalleryImages as $imagePath) {
    $allGalleryImages[] = [
        'image_path' => $imagePath,
        'user_name' => 'Passione Calabria', // Etichetta per le foto caricate dallo staff
        'description' => ''
    ];
}

// Aggiungi le foto degli utenti
foreach ($userPhotos as $photo) {
    $allGalleryImages[] = [
        'image_path' => $photo['image_path'],
        'user_name' => $photo['user_name'],
        'description' => $photo['description']
    ];
}
// --- FINE UNIFICA GALLERIE ---

// Carica commenti approvati per la città
$cityComments = $db->getApprovedCommentsByCityId($cityId);

// Costruisci il link di Google Maps
$googleMapsLink = $city['google_maps_link'] ?: '';
if (empty($googleMapsLink) && $city['latitude'] && $city['longitude']) {
    $googleMapsLink = 'https://www.google.com/maps/dir/?api=1&destination=' . $city['latitude'] . ',' . $city['longitude'];
}

// Carica impostazioni per la sezione App
$settings = $db->getSettings();
$appSettings = [];
foreach ($settings as $setting) {
    if (strpos($setting['key'], 'app_') === 0 || strpos($setting['key'], 'play_') === 0) {
        $appSettings[$setting['key']] = $setting['value'];
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($city['name']); ?> - Scopri la Calabria</title>
    <meta name="description" content="<?php echo htmlspecialchars($city['description'] ?: 'Esplora ' . $city['name'] . ' in Calabria: attrazioni, tradizioni e bellezze naturali.'); ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        slate: {
                            50: '#f8fafc',
                            900: '#0f172a'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-50">
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="flex items-center text-sm text-slate-600">
                <a href="index.php" class="hover:text-slate-900 transition-colors">Home</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                <a href="citta.php" class="hover:text-slate-900 transition-colors">Città</a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                <a href="provincia.php?id=<?php echo $city['province_id']; ?>" class="hover:text-slate-900 transition-colors"><?php echo htmlspecialchars($city['province_name']); ?></a>
                <i data-lucide="chevron-right" class="w-4 h-4 mx-2"></i>
                <span class="text-slate-900 font-medium"><?php echo htmlspecialchars($city['name']); ?></span>
            </nav>
        </div>
    </div>

    <!-- Hero Cinematografico -->
    <section class="relative h-[70vh] overflow-hidden">
        <!-- Immagine Background -->
        <div class="absolute inset-0">
            <img src="<?php echo $heroImage; ?>" alt="<?php echo htmlspecialchars($city['name']); ?>"
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
        </div>
        
        <!-- Contenuto Hero -->
        <div class="relative h-full flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <div class="max-w-4xl">
                    <!-- Badge Provincia -->
                    <div class="inline-flex items-center px-4 py-2 bg-white/20 backdrop-blur-sm rounded-full text-white text-sm font-medium mb-6">
                        <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
                        <?php echo htmlspecialchars($city['province_name']); ?>
                    </div>
                    
                    <!-- Titolo -->
                    <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6 leading-tight">
                        <?php echo htmlspecialchars($city['name']); ?>
                    </h1>
                    
                    <!-- Descrizione -->
                    <p class="text-xl lg:text-2xl text-white/90 mb-8 max-w-3xl leading-relaxed">
                        <?php echo htmlspecialchars($city['description'] ?: 'Scopri le meraviglie di ' . $city['name'] . ', perla della provincia di ' . $city['province_name']); ?>
                    </p>
                    
                    <!-- Statistiche -->
                    <div class="flex flex-wrap gap-6 text-white/80">
                        <div class="flex items-center">
                            <i data-lucide="file-text" class="w-5 h-5 mr-2"></i>
                            <span class="text-lg font-semibold"><?php echo $articleCount; ?></span>
                            <span class="ml-1">contenuti</span>
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="camera" class="w-5 h-5 mr-2"></i>
                            <span class="text-lg font-semibold"><?php echo count($userPhotos); ?></span>
                            <span class="ml-1">foto</span>
                        </div>
                        <div class="flex items-center">
                            <i data-lucide="message-circle" class="w-5 h-5 mr-2"></i>
                            <span class="text-lg font-semibold"><?php echo count($cityComments); ?></span>
                            <span class="ml-1">recensioni</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-16">
                
                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-20">

                    <!-- Sezione App Eventi -->
                    <?php if (!empty($appSettings['app_store_link']) || !empty($appSettings['play_store_link'])): ?>
                    <section class="bg-white rounded-3xl shadow-lg p-8 lg:p-12">
                        <div class="text-center">
                            <h2 class="text-3xl font-bold text-slate-900 mb-6">
                                Scopri tutti gli eventi di <?php echo htmlspecialchars($city['name']); ?>
                            </h2>
                            <p class="text-slate-600 mb-8 text-lg">
                                Scarica l'app ufficiale di Passione Calabria per non perdere mai un evento!
                            </p>
                            <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                                <?php if (!empty($appSettings['app_store_link']) && !empty($appSettings['app_store_image'])): ?>
                                <a href="<?php echo htmlspecialchars($appSettings['app_store_link']); ?>" target="_blank" class="transition-transform hover:scale-105">
                                    <img src="<?php echo htmlspecialchars($appSettings['app_store_image']); ?>" alt="Scarica su App Store" class="h-14 w-auto">
                                </a>
                                <?php endif; ?>
                                <?php if (!empty($appSettings['play_store_link']) && !empty($appSettings['play_store_image'])): ?>
                                <a href="<?php echo htmlspecialchars($appSettings['play_store_link']); ?>" target="_blank" class="transition-transform hover:scale-105">
                                    <img src="<?php echo htmlspecialchars($appSettings['play_store_image']); ?>" alt="Scarica su Google Play" class="h-14 w-auto">
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- Galleria Unificata -->
<section>
    <div class="flex items-center justify-between mb-12">
        <div>
            <h2 class="text-4xl font-bold text-slate-900 mb-2">
                Galleria di <?php echo htmlspecialchars($city['name']); ?>
            </h2>
            <p class="text-xl text-slate-600">
                Scopri <?php echo htmlspecialchars($city['name']); ?> attraverso gli occhi dello staff e della community
            </p>
        </div>
        <button onclick="openPhotoUploadModal()" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-full font-semibold transition-colors flex items-center">
            <i data-lucide="camera" class="w-5 h-5 mr-2"></i>
            Carica la tua foto
        </button>
    </div>

    <?php if (!empty($allGalleryImages)): ?>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
        <?php foreach ($allGalleryImages as $index => $photo):
            $clean_path = $photo['image_path'] ?? '';
            // GESTIONE BACKWARD-COMPATIBILITY PER VECCHI PERCORSI
            if (strpos($clean_path, 'uploads/') === 0) {
                $clean_path = substr($clean_path, strlen('uploads/'));
            }
        ?>
        <div class="<?php echo ($index === 0) ? 'col-span-2 row-span-2' : ''; ?> group relative overflow-hidden rounded-2xl aspect-square">
            <img src="image-loader.php?path=<?php echo urlencode($clean_path); ?>"
                 alt="<?php echo htmlspecialchars($photo['description'] ?: $city['name']); ?>"
                 class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors duration-300"></div>
            <div class="absolute bottom-4 left-4 text-white opacity-0 group-hover:opacity-100 transition-opacity">
                <p class="text-sm font-medium"><?php echo htmlspecialchars($photo['user_name']); ?></p>
                <?php if ($photo['description']): ?>
                <p class="text-xs opacity-80"><?php echo htmlspecialchars($photo['description']); ?></p>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="bg-slate-100 rounded-3xl p-12 text-center">
        <i data-lucide="camera" class="w-16 h-16 text-slate-400 mx-auto mb-4"></i>
        <h3 class="text-2xl font-semibold text-slate-700 mb-2">Nessuna foto ancora</h3>
        <p class="text-slate-600 mb-6">Sii il primo a condividere una foto di <?php echo htmlspecialchars($city['name']); ?>!</p>
        <button onclick="openPhotoUploadModal()" class="bg-slate-900 hover:bg-slate-800 text-white px-6 py-3 rounded-full font-semibold">
            Carica la prima foto
        </button>
    </div>
    <?php endif; ?>
</section>

                    <!-- Cosa Fare - Categorie per Città -->
                    <section>
                        <div class="mb-12">
                            <h2 class="text-4xl font-bold text-slate-900 mb-2">
                                Cosa fare a <?php echo htmlspecialchars($city['name']); ?>
                            </h2>
                            <p class="text-xl text-slate-600">
                                Esplora tutte le categorie di attività e luoghi d'interesse
                            </p>
                        </div>
                        
                        <div class="space-y-16">
                            <?php foreach ($allCategories as $category): ?>
                                <?php if (isset($articlesByCategory[$category['id']]) && !empty($articlesByCategory[$category['id']])): ?>
                                <div class="category-section">
                                    <!-- Header Categoria -->
                                    <div class="flex items-center mb-8 pb-4 border-b-2 border-slate-200">
                                        <span class="text-4xl mr-4"><?php echo $category['icon']; ?></span>
                                        <div>
                                            <h3 class="text-3xl font-bold text-slate-900"><?php echo htmlspecialchars($category['name']); ?></h3>
                                            <p class="text-slate-600"><?php echo count($articlesByCategory[$category['id']]); ?> contenuti disponibili</p>
                                        </div>
                                    </div>
                                    
                                    <!-- Grid Articoli -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                        <?php foreach ($articlesByCategory[$category['id']] as $article): ?>
                                        <article class="group bg-white rounded-2xl shadow-lg overflow-hidden hover:shadow-xl transition-all duration-300">
                                            <a href="articolo.php?slug=<?php echo $article['slug']; ?>" class="block">
                                                <div class="aspect-[16/9] bg-slate-200 overflow-hidden">
                                                    <?php if ($article['featured_image']): ?>
                                                    <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $article['featured_image'] ?? '')); ?>"
                                                         alt="<?php echo htmlspecialchars($article['title']); ?>" 
                                                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                                    <?php else: ?>
                                                    <div class="w-full h-full bg-gradient-to-br from-slate-300 to-slate-400 flex items-center justify-center">
                                                        <span class="text-4xl"><?php echo $category['icon']; ?></span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="p-6">
                                                    <h4 class="text-xl font-bold text-slate-900 mb-3 group-hover:text-blue-600 transition-colors line-clamp-2">
                                                        <?php echo htmlspecialchars($article['title']); ?>
                                                    </h4>
                                                    <p class="text-slate-600 mb-4 line-clamp-3 leading-relaxed">
                                                        <?php echo htmlspecialchars($article['excerpt']); ?>
                                                    </p>
                                                    <div class="flex items-center text-sm text-slate-500">
                                                        <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>
                                                        <span><?php echo formatDate($article['created_at']); ?></span>
                                                    </div>
                                                </div>
                                            </a>
                                        </article>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (empty($articles)): ?>
                        <div class="bg-slate-100 rounded-3xl p-12 text-center">
                            <i data-lucide="compass" class="w-16 h-16 text-slate-400 mx-auto mb-4"></i>
                            <h3 class="text-2xl font-semibold text-slate-700 mb-2">Contenuti in arrivo</h3>
                            <p class="text-slate-600">Stiamo preparando fantastici contenuti su <?php echo htmlspecialchars($city['name']); ?>!</p>
                        </div>
                        <?php endif; ?>
                    </section>

                    <!-- Sezione Commenti e Recensioni -->
                    <section>
                        <div class="mb-12">
                            <h2 class="text-4xl font-bold text-slate-900 mb-2">
                                Recensioni e Commenti
                            </h2>
                            <p class="text-xl text-slate-600">
                                Cosa pensano i visitatori di <?php echo htmlspecialchars($city['name']); ?>
                            </p>
                        </div>

                        <!-- Form Aggiungi Commento -->
                        <div class="bg-white rounded-3xl shadow-lg p-8 mb-12">
                            <h3 class="text-2xl font-semibold text-slate-900 mb-6">Condividi la tua esperienza</h3>
                            
                            <?php if (!empty($comment_error)): ?>
                            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                                <?php echo htmlspecialchars($comment_error); ?>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($comment_success)): ?>
                            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                                <?php echo htmlspecialchars($comment_success); ?>
                            </div>
                            <?php endif; ?>
                            
                            <form method="POST" class="space-y-6">
                                <input type="hidden" name="action" value="add_comment">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Nome *</label>
                                        <input type="text" name="comment_name" required 
                                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="Il tuo nome">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-semibold text-slate-700 mb-2">Email *</label>
                                        <input type="email" name="comment_email" required 
                                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                               placeholder="la-tua-email@esempio.com">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Valutazione</label>
                                    <div class="flex items-center space-x-2">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="<?php echo $i; ?>" class="sr-only rating-input">
                                            <i data-lucide="star" class="w-8 h-8 text-slate-300 rating-star hover:text-yellow-400 transition-colors"></i>
                                        </label>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-sm font-semibold text-slate-700 mb-2">Il tuo commento *</label>
                                    <textarea name="comment_content" required rows="4" 
                                              class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                              placeholder="Racconta la tua esperienza a <?php echo htmlspecialchars($city['name']); ?>..."></textarea>
                                </div>
                                
                                <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-full font-semibold transition-colors">
                                    Invia Recensione
                                </button>
                            </form>
                        </div>

                        <!-- Lista Commenti -->
                        <?php if (!empty($cityComments)): ?>
                        <div class="space-y-8">
                            <?php foreach ($cityComments as $comment): ?>
                            <div class="bg-white rounded-2xl shadow-lg p-8">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <h4 class="font-semibold text-slate-900"><?php echo htmlspecialchars($comment['author_name']); ?></h4>
                                        <div class="flex items-center mt-2">
                                            <?php if ($comment['rating']): ?>
                                            <div class="flex items-center mr-4">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <i data-lucide="star" class="w-4 h-4 <?php echo ($i <= $comment['rating']) ? 'text-yellow-400 fill-current' : 'text-slate-300'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <?php endif; ?>
                                            <span class="text-sm text-slate-500"><?php echo formatDate($comment['created_at']); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-slate-700 leading-relaxed"><?php echo nl2br(htmlspecialchars($comment['content'])); ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="bg-slate-100 rounded-3xl p-12 text-center">
                            <i data-lucide="message-circle" class="w-16 h-16 text-slate-400 mx-auto mb-4"></i>
                            <h3 class="text-2xl font-semibold text-slate-700 mb-2">Nessuna recensione ancora</h3>
                            <p class="text-slate-600">Sii il primo a condividere la tua esperienza!</p>
                        </div>
                        <?php endif; ?>
                    </section>

                </div>

                <!-- Sidebar -->
                <aside class="lg:col-span-1 space-y-8">
                    <!-- Mappa -->
                    <?php if ($googleMapsLink): ?>
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-2xl font-semibold text-slate-900 mb-6 flex items-center">
                            <i data-lucide="map" class="w-6 h-6 mr-3"></i>
                            Come arrivare
                        </h3>
                        
                        <div id="sidebar-map" class="w-full h-64 bg-slate-200 rounded-2xl overflow-hidden mb-6"></div>
                        
                        <a href="<?php echo htmlspecialchars($googleMapsLink); ?>" target="_blank" 
                           class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-4 px-6 rounded-2xl flex items-center justify-center transition-colors">
                            <i data-lucide="navigation" class="w-5 h-5 mr-2"></i>
                            Ottieni Indicazioni
                        </a>
                    </div>
                    <?php endif; ?>

                    <!-- Info Utili -->
                    <div class="bg-white rounded-3xl shadow-lg p-8">
                        <h3 class="text-2xl font-semibold text-slate-900 mb-6">Info Utili</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <i data-lucide="map-pin" class="w-5 h-5 text-slate-400 mr-3"></i>
                                <span class="text-slate-600">Provincia di <?php echo htmlspecialchars($city['province_name']); ?></span>
                            </div>
                            <div class="flex items-center">
                                <i data-lucide="file-text" class="w-5 h-5 text-slate-400 mr-3"></i>
                                <span class="text-slate-600"><?php echo $articleCount; ?> contenuti disponibili</span>
                            </div>
                            <div class="flex items-center">
                                <i data-lucide="camera" class="w-5 h-5 text-slate-400 mr-3"></i>
                                <span class="text-slate-600"><?php echo count($userPhotos); ?> foto della community</span>
                            </div>
                            <?php if ($city['latitude'] && $city['longitude']): ?>
                            <div class="flex items-center">
                                <i data-lucide="compass" class="w-5 h-5 text-slate-400 mr-3"></i>
                                <span class="text-slate-600 text-xs font-mono"><?php echo number_format($city['latitude'], 4); ?>, <?php echo number_format($city['longitude'], 4); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Suggerimenti -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-3xl p-8">
                        <h3 class="text-2xl font-semibold text-slate-900 mb-4">Suggerisci un Luogo</h3>
                        <p class="text-slate-600 mb-6">Conosci un posto speciale a <?php echo htmlspecialchars($city['name']); ?>? Condividilo con la community!</p>
                        <a href="suggerisci.php" class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold">
                            <span>Suggerisci ora</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-2"></i>
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </main>

    <!-- Modal Upload Foto -->
    <div id="photoUploadModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-3xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-8">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-3xl font-bold text-slate-900">Carica una foto</h3>
                        <button onclick="closePhotoUploadModal()" class="text-slate-400 hover:text-slate-600">
                            <i data-lucide="x" class="w-6 h-6"></i>
                        </button>
                    </div>
                    
                    <?php if (!empty($upload_error)): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6">
                        <?php echo htmlspecialchars($upload_error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success_message)): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl mb-6">
                        <?php echo htmlspecialchars($success_message); ?>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="upload_photo">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Nome *</label>
                                <input type="text" name="user_name" required 
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="Il tuo nome">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-2">Email *</label>
                                <input type="email" name="user_email" required 
                                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                       placeholder="la-tua-email@esempio.com">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Foto *</label>
                            <input type="file" name="photo" accept="image/*" required 
                                   class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <p class="text-sm text-slate-500 mt-2">Formati supportati: JPG, PNG, GIF, WebP. Massimo 5MB.</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-2">Descrizione (opzionale)</label>
                            <textarea name="description" rows="3" 
                                      class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                      placeholder="Descrivi questa foto di <?php echo htmlspecialchars($city['name']); ?>..."></textarea>
                        </div>
                        
                        <div class="flex items-center justify-between pt-6">
                            <button type="button" onclick="closePhotoUploadModal()" class="px-6 py-3 text-slate-600 hover:text-slate-800 font-medium">
                                Annulla
                            </button>
                            <button type="submit" class="bg-slate-900 hover:bg-slate-800 text-white px-8 py-3 rounded-full font-semibold transition-colors">
                                Carica Foto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();

        // Inizializza mappa sidebar
        <?php if ($city['latitude'] && $city['longitude']): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const map = L.map('sidebar-map').setView([<?php echo $city['latitude']; ?>, <?php echo $city['longitude']; ?>], 13);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            L.marker([<?php echo $city['latitude']; ?>, <?php echo $city['longitude']; ?>]).addTo(map)
                .bindPopup('<?php echo htmlspecialchars($city['name']); ?>')
                .openPopup();
        });
        <?php endif; ?>

        // Gestione rating stelle
        document.addEventListener('DOMContentLoaded', function() {
            const ratingInputs = document.querySelectorAll('.rating-input');
            const ratingStars = document.querySelectorAll('.rating-star');
            
            ratingInputs.forEach((input, index) => {
                input.addEventListener('change', function() {
                    ratingStars.forEach((star, starIndex) => {
                        if (starIndex <= index) {
                            star.classList.remove('text-slate-300');
                            star.classList.add('text-yellow-400', 'fill-current');
                        } else {
                            star.classList.add('text-slate-300');
                            star.classList.remove('text-yellow-400', 'fill-current');
                        }
                    });
                });
            });
        });

        // Gestione Modal
        function openPhotoUploadModal() {
            document.getElementById('photoUploadModal').classList.remove('hidden');
        }
        
        function closePhotoUploadModal() {
            document.getElementById('photoUploadModal').classList.add('hidden');
        }
        
        // Chiudi modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closePhotoUploadModal();
            }
        });
        
        // Chiudi modal cliccando fuori
        document.getElementById('photoUploadModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closePhotoUploadModal();
            }
        });

        <?php if (!empty($upload_error) || !empty($success_message)): ?>
        // Apri automaticamente modal se ci sono messaggi di upload
        openPhotoUploadModal();
        <?php endif; ?>
    </script>
</body>
</html>