<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

// Inizializza database se necessario
$db = new Database();

// Carica dati per la homepage
$categories = $db->getCategories();
$provinces = $db->getProvinces();
$featuredArticles = $db->getFeaturedArticles();
$homeSections = $db->getHomeSections();
$articlesWithCoordinates = $db->getAllArticlesWithCoordinates();

// Carica impostazioni homepage
$settings = $db->getSettings();
$settingsArray = [];
foreach ($settings as $setting) {
    $settingsArray[$setting['key']] = $setting['value'];
}

// Carica articoli per ogni categoria (per i slider)
foreach ($categories as &$category) {
    $category['articles'] = $db->getArticlesByCategory($category['id'], 6); // Max 6 articoli per slider
    $category['article_count'] = $db->getArticleCountByCategory($category['id']);
}
unset($category); // Unset reference

// Trova sezione hero
$heroSection = null;
foreach ($homeSections as $section) {
    if ($section['section_name'] === 'hero') {
        $heroSection = $section;
        break;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passione Calabria - La tua guida alla Calabria</title>
    <meta name="description" content="Scopri la bellezza della Calabria: mare cristallino, borghi medievali, gastronomia unica e tradizioni millenarie.">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
</head>
<body class="min-h-screen bg-gray-50 font-sans">
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-blue-900 via-blue-700 to-amber-600 text-white py-24 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('image-loader.php?path=<?php echo urlencode($heroSection['image_path'] ?? 'assets/images/default-hero.jpg'); ?>')"></div>
        <div class="absolute inset-0 bg-gradient-to-br from-blue-900/80 via-blue-700/70 to-amber-600/60"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold mb-6"><?php echo htmlspecialchars($heroSection['title'] ?? 'Esplora la Calabria'); ?></h1>
            <p class="text-xl md:text-2xl text-yellow-400 mb-8"><?php echo htmlspecialchars($heroSection['subtitle'] ?? 'Mare cristallino e storia millenaria'); ?></p>
        </div>
    </section>

    <!-- Events Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-4xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($settingsArray['events_title'] ?? 'Eventi e App'); ?></h2>
            </div>
            <div class="flex flex-col sm:flex-row justify-center items-center gap-8 mb-12">
                <?php if (!empty($settingsArray['app_store_link']) && !empty($settingsArray['app_store_image'])): ?>
                <a href="<?php echo htmlspecialchars($settingsArray['app_store_link']); ?>" target="_blank">
                    <img src="image-loader.php?path=<?php echo urlencode($settingsArray['app_store_image']); ?>" alt="Scarica su App Store" class="h-14 w-auto">
                </a>
                <?php endif; ?>
                <?php if (!empty($settingsArray['play_store_link']) && !empty($settingsArray['play_store_image'])): ?>
                <a href="<?php echo htmlspecialchars($settingsArray['play_store_link']); ?>" target="_blank">
                    <img src="image-loader.php?path=<?php echo urlencode($settingsArray['play_store_image']); ?>" alt="Scarica su Google Play" class="h-14 w-auto">
                </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($categories as $category): ?>
                <div class="bg-white rounded-2xl shadow-lg">
                    <div class="p-6">
                        <?php if (strpos($category['icon'], 'uploads/') !== false): ?>
                            <img src="image-loader.php?path=<?php echo urlencode($category['icon']); ?>" alt="<?php echo htmlspecialchars($category['name']); ?>" class="w-10 h-10 object-cover rounded-lg">
                        <?php else: ?>
                            <span class="text-4xl"><?php echo $category['icon']; ?></span>
                        <?php endif; ?>
                        <h3 class="text-2xl font-bold text-gray-900 mt-4"><?php echo htmlspecialchars($category['name']); ?></h3>
                    </div>
                    <?php if (!empty($category['articles'])): ?>
                    <div class="p-6">
                        <?php foreach ($category['articles'] as $article): ?>
                        <a href="articolo.php?slug=<?php echo $article['slug']; ?>" class="block">
                            <div class="flex items-start space-x-3 p-2 rounded-lg hover:bg-gray-50">
                                <div class="w-16 h-12 bg-gray-200 rounded">
                                    <?php if ($article['logo']): ?>
                                    <img src="image-loader.php?path=<?php echo urlencode($article['logo']); ?>" alt="Logo <?php echo htmlspecialchars($article['title']); ?>" class="w-full h-full object-contain p-1">
                                    <?php endif; ?>
                                </div>
                                <h4 class="text-sm font-semibold text-gray-900"><?php echo htmlspecialchars($article['title']); ?></h4>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Provinces Section -->
    <section class="py-20 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($provinces as $province): ?>
                <div class="bg-white rounded-2xl shadow-lg">
                    <div class="aspect-[4/3] relative">
                        <?php if (!empty($province['image_path'])): ?>
                        <img src="image-loader.php?path=<?php echo urlencode($province['image_path']); ?>" alt="<?php echo htmlspecialchars($province['name']); ?>" class="w-full h-full object-cover">
                        <?php endif; ?>
                    </div>
                    <div class="p-6">
                        <h3 class="text-2xl font-bold text-gray-900"><?php echo htmlspecialchars($province['name']); ?></h3>
                        <a href="provincia.php?id=<?php echo $province['id']; ?>" class="text-blue-600 font-semibold">Esplora</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Map Section -->
    <section class="py-20 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div id="homepage-map" class="w-full h-96 bg-gray-200 rounded-lg"></div>
        </div>
    </section>

    <?php include 'includes/footer.php'; ?>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('homepage-map') && typeof L !== 'undefined') {
                var homepageMap = L.map('homepage-map').setView([39.0, 16.5], 8);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(homepageMap);
                var articles = <?php echo json_encode($articlesWithCoordinates); ?>;
                articles.forEach(function(article) {
                    if (article.latitude && article.longitude) {
                        var marker = L.marker([parseFloat(article.latitude), parseFloat(article.longitude)]).addTo(homepageMap);
                        var imageUrl = article.featured_image ? `image-loader.php?path=${encodeURIComponent(article.featured_image)}` : '';
                        var popupContent = `<div class="p-1">` +
                            (imageUrl ? `<img src="${imageUrl}" alt="" class="w-full h-24 object-cover rounded-md mb-2">` : '') +
                            `<h4 class="font-bold">${article.title}</h4>` +
                            `<a href="articolo.php?slug=${article.slug}" class="text-blue-600 text-xs">Leggi di più</a></div>`;
                        marker.bindPopup(popupContent);
                    }
                });
            }
        });
    </script>
</body>
</html>