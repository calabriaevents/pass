<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();

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

// La logica di upload è gestita da api/upload-user-photo.php
// La logica dei commenti rimane per ora, ma andrebbe spostata in un API endpoint
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

// Raggruppa articoli per categoria
$articlesByCategory = [];
foreach ($articles as $article) {
    $articlesByCategory[$article['category_id']][] = $article;
}

// Determina l'immagine hero per la città
$heroImage = $city['hero_image'] ?? 'assets/images/default-city-hero.jpg';
if (empty($city['hero_image']) && !empty($articles)) {
    $heroImage = $articles[0]['featured_image'] ?? $heroImage;
}

// Carica foto utenti approvate e commenti
$userPhotos = $db->getApprovedUserUploads(null, $cityId);
$cityComments = $db->getApprovedCommentsByCityId($cityId);

// Link Google Maps
$googleMapsLink = $city['google_maps_link'] ?: '';
if (empty($googleMapsLink) && $city['latitude'] && $city['longitude']) {
    $googleMapsLink = 'https://www.google.com/maps/dir/?api=1&destination=' . $city['latitude'] . ',' . $city['longitude'];
}

// Impostazioni App
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
</head>
<body class="bg-slate-50">
    <?php include 'includes/header.php'; ?>

    <!-- Hero -->
    <section class="relative h-[70vh] overflow-hidden">
        <div class="absolute inset-0">
            <img src="image-loader.php?path=<?php echo urlencode($heroImage); ?>" alt="<?php echo htmlspecialchars($city['name']); ?>" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent"></div>
        </div>
        <div class="relative h-full flex items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
                <h1 class="text-5xl lg:text-7xl font-bold text-white mb-6 leading-tight"><?php echo htmlspecialchars($city['name']); ?></h1>
            </div>
        </div>
    </section>

    <main class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Galleria Foto Utenti -->
            <section>
                <button onclick="openUploadModal(null, <?php echo $cityId; ?>)" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors">
                    Carica la tua foto
                </button>
                <?php if (!empty($userPhotos)): ?>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mt-8">
                    <?php foreach ($userPhotos as $photo): ?>
                    <div class="group relative overflow-hidden rounded-2xl">
                        <img src="image-loader.php?path=<?php echo urlencode($photo['image_path']); ?>" alt="<?php echo htmlspecialchars($photo['description'] ?: $city['name']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </section>

            <!-- Articoli per Categoria -->
            <section class="mt-16">
                <?php foreach ($allCategories as $category): ?>
                    <?php if (isset($articlesByCategory[$category['id']]) && !empty($articlesByCategory[$category['id']])): ?>
                    <div class="mb-12">
                        <h3 class="text-3xl font-bold text-slate-900"><?php echo htmlspecialchars($category['name']); ?></h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mt-6">
                            <?php foreach ($articlesByCategory[$category['id']] as $article): ?>
                            <article class="group bg-white rounded-2xl shadow-lg overflow-hidden">
                                <a href="articolo.php?slug=<?php echo $article['slug']; ?>" class="block">
                                    <div class="aspect-[16/9] bg-slate-200 overflow-hidden">
                                        <?php if ($article['featured_image']): ?>
                                        <img src="image-loader.php?path=<?php echo urlencode($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <?php endif; ?>
                                    </div>
                                    <div class="p-6">
                                        <h4 class="text-xl font-bold text-slate-900 group-hover:text-blue-600"><?php echo htmlspecialchars($article['title']); ?></h4>
                                    </div>
                                </a>
                            </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </section>

             <!-- Sezione App Eventi -->
            <?php if (!empty($appSettings['app_store_link']) || !empty($appSettings['play_store_link'])): ?>
            <section class="bg-white rounded-3xl shadow-lg p-8 lg:p-12">
                <div class="text-center">
                    <h2 class="text-3xl font-bold text-slate-900 mb-6">Scopri tutti gli eventi</h2>
                    <div class="flex flex-col sm:flex-row justify-center items-center gap-4">
                        <?php if (!empty($appSettings['app_store_link']) && !empty($appSettings['app_store_image'])): ?>
                        <a href="<?php echo htmlspecialchars($appSettings['app_store_link']); ?>" target="_blank">
                            <img src="image-loader.php?path=<?php echo urlencode($appSettings['app_store_image']); ?>" alt="Scarica su App Store" class="h-14 w-auto">
                        </a>
                        <?php endif; ?>
                        <?php if (!empty($appSettings['play_store_link']) && !empty($appSettings['play_store_image'])): ?>
                        <a href="<?php echo htmlspecialchars($appSettings['play_store_link']); ?>" target="_blank">
                            <img src="image-loader.php?path=<?php echo urlencode($appSettings['play_store_image']); ?>" alt="Scarica su Google Play" class="h-14 w-auto">
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <?php include 'partials/user-upload-modal.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
        UserUploadModal.init();
    </script>
</body>
</html>