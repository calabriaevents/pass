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

// Determina l'immagine hero per la città (usa la featured image dell'articolo più recente)
$heroImage = 'assets/images/default-city-hero.jpg'; // Un'immagine di fallback
if (!empty($articles)) {
    $heroImage = $articles[0]['featured_image'] ?? $heroImage;
}

// Aggrega la galleria di immagini da tutti gli articoli della città
$galleryImages = [];
foreach ($articles as $article) {
    if (!empty($article['gallery_images'])) {
        $images = json_decode($article['gallery_images'], true);
        if (is_array($images)) {
            $galleryImages = array_merge($galleryImages, $images);
        }
    }
}
// Rimuovi eventuali duplicati
$galleryImages = array_unique($galleryImages);

// Carica impostazioni per la sezione App
$settings = $db->getSettings();
$appSettings = [];
foreach ($settings as $setting) {
    if (strpos($setting['key'], 'app_') === 0 || strpos($setting['key'], 'play_') === 0 || strpos($setting['key'], 'suggerisci_evento') === 0) {
        $appSettings[$setting['key']] = $setting['value'];
    }
}

// Costruisci il link di Google Maps
$googleMapsLink = '';
if ($city['latitude'] && $city['longitude']) {
    $googleMapsLink = 'https://www.google.com/maps/dir/?api=1&destination=' . $city['latitude'] . ',' . $city['longitude'];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($city['name']); ?> - Città della Calabria</title>
    <meta name="description" content="<?php echo htmlspecialchars($city['description'] ?: 'Scopri ' . $city['name'] . ', città della provincia di ' . $city['province_name'] . ' in Calabria.'); ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/style.css">

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'calabria-blue': {
                            50: '#eff6ff',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8'
                        },
                        'calabria-gold': {
                            50: '#fffbeb',
                            500: '#f59e0b',
                            600: '#d97706'
                        }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="breadcrumb">
                <span class="breadcrumb-item"><a href="index.php" class="text-blue-600 hover:text-blue-700">Home</a></span>
                <span class="breadcrumb-item"><a href="citta.php" class="text-blue-600 hover:text-blue-700">Città</a></span>
                <span class="breadcrumb-item"><a href="provincia.php?id=<?php echo $city['province_id']; ?>" class="text-blue-600 hover:text-blue-700"><?php echo htmlspecialchars($city['province_name']); ?></a></span>
                <span class="breadcrumb-item text-gray-900 font-medium"><?php echo htmlspecialchars($city['name']); ?></span>
            </nav>
        </div>
    </div>

    <!-- City Hero with Image -->
    <section class="relative bg-gray-800 text-white py-24 overflow-hidden">
        <div class="absolute inset-0 bg-cover bg-center bg-no-repeat" style="background-image: url('<?php echo htmlspecialchars($heroImage); ?>');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/40 to-transparent"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 text-shadow-lg">
                <?php echo htmlspecialchars($city['name']); ?>
            </h1>
            <p class="text-xl text-gray-200 mb-8 max-w-3xl mx-auto text-shadow">
                <?php echo htmlspecialchars($city['description'] ?: 'Scopri le meraviglie di ' . $city['name'] . ', nel cuore della provincia di ' . $city['province_name']); ?>
            </p>
            <div class="flex justify-center gap-4 flex-wrap">
                <span class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full border border-white/30">
                    <i data-lucide="map-pin" class="w-4 h-4 inline mr-1"></i>
                    <?php echo htmlspecialchars($city['province_name']); ?>
                </span>
                <span class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full border border-white/30">
                    <i data-lucide="file-text" class="w-4 h-4 inline mr-1"></i>
                    <?php echo $articleCount; ?> <span>Contenuti</span>
                </span>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <main class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
                <!-- Main Content Column -->
                <div class="lg:col-span-2 space-y-16">

                    <!-- Events App Section -->
                    <section>
                        <div class="bg-white rounded-2xl shadow-lg p-8">
                             <div class="flex flex-col sm:flex-row justify-center items-center gap-8">
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

                    <!-- Photo Gallery Section -->
                    <?php if (!empty($galleryImages)): ?>
                    <section>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8 flex items-center">
                            <i data-lucide="camera" class="w-8 h-8 mr-3 text-blue-600"></i>
                            Galleria Fotografica
                        </h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach (array_slice($galleryImages, 0, 6) as $image): ?>
                            <a href="<?php echo htmlspecialchars($image); ?>" class="block group relative overflow-hidden rounded-xl">
                                <img src="<?php echo htmlspecialchars($image); ?>" alt="Foto di <?php echo htmlspecialchars($city['name']); ?>" class="w-full h-full object-cover aspect-square group-hover:scale-105 transition-transform duration-300">
                                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                    <i data-lucide="zoom-in" class="w-10 h-10 text-white"></i>
                                </div>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php if (count($galleryImages) > 6): ?>
                        <div class="text-center mt-8">
                            <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full font-semibold transition-colors">
                                Mostra tutte le <?php echo count($galleryImages); ?> foto
                            </button>
                        </div>
                        <?php endif; ?>
                    </section>
                    <?php endif; ?>

                    <!-- Articles by Category Section -->
                    <section>
                        <h2 class="text-3xl font-bold text-gray-900 mb-8 flex items-center">
                            <i data-lucide="compass" class="w-8 h-8 mr-3 text-blue-600"></i>
                            Cosa fare a <?php echo htmlspecialchars($city['name']); ?>
                        </h2>
                        <div class="space-y-12">
                            <?php foreach ($allCategories as $category): ?>
                                <?php if (isset($articlesByCategory[$category['id']])): ?>
                                    <div class="category-section">
                                        <h3 class="text-2xl font-semibold text-gray-800 mb-6 border-b-2 border-blue-500 pb-2 flex items-center">
                                            <span class="text-2xl mr-3"><?php echo $category['icon']; ?></span>
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </h3>
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            <?php foreach ($articlesByCategory[$category['id']] as $article): ?>
                                            <article class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                                                <a href="articolo.php?slug=<?php echo $article['slug']; ?>" class="block">
                                                    <div class="aspect-[16/9] bg-gray-200 overflow-hidden">
                                                        <?php if ($article['featured_image']): ?>
                                                        <img src="<?php echo htmlspecialchars($article['featured_image']); ?>" alt="<?php echo htmlspecialchars($article['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="p-6">
                                                        <h4 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors line-clamp-2"><?php echo htmlspecialchars($article['title']); ?></h4>
                                                        <p class="text-gray-600 text-sm mb-4 line-clamp-3"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                                                        <div class="flex items-center text-xs text-gray-500">
                                                            <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
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
                    </section>

                </div>

                <!-- Sidebar Column -->
                <aside class="lg:col-span-1 space-y-12">
                    <!-- Map Section -->
                    <?php if ($googleMapsLink): ?>
                    <section>
                        <div class="bg-white rounded-2xl shadow-lg p-6">
                            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                <i data-lucide="map" class="w-6 h-6 mr-2 text-blue-600"></i>
                                Mappa
                            </h3>
                            <div id="sidebar-map" class="w-full h-64 bg-gray-200 rounded-lg overflow-hidden mb-4"></div>
                            <a href="<?php echo htmlspecialchars($googleMapsLink); ?>" target="_blank" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg flex items-center justify-center transition-colors">
                                <i data-lucide="navigation" class="w-5 h-5 mr-2"></i>
                                Ottieni Indicazioni
                            </a>
                        </div>
                    </section>
                    <?php endif; ?>
                </aside>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();

        // Initialize sidebar map
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
    </script>
</body>
</html>