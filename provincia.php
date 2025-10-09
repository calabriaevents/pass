<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();

// Verifica se l'ID provincia è fornito
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: province.php");
    exit;
}

$provinceId = (int)$_GET['id'];

// Carica dati provincia
$province = $db->getProvinceById($provinceId);
if (!$province) {
    header("Location: province.php");
    exit;
}

// Carica articoli della provincia
$articles = $db->getArticlesByProvince($provinceId);
$articleCount = $db->getArticleCountByProvince($provinceId);

// Carica articoli con coordinate della provincia per la mappa
$articlesWithCoordinates = $db->getArticlesWithCoordinatesByProvince($provinceId);

// Carica città della provincia
$cities = $db->getCitiesByProvince($provinceId);

// Carica la galleria di immagini per la provincia
$galleryImages = $db->getProvinceGalleryImages($provinceId);

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($province['name']); ?> - Province della Calabria</title>
    <meta name="description" content="<?php echo htmlspecialchars($province['description']); ?>">

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <!-- Breadcrumb -->
    <div class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <nav class="breadcrumb">
                <span class="breadcrumb-item"><a href="index.php" class="text-blue-600 hover:text-blue-700">Home</a></span>
                <span class="breadcrumb-item"><a href="province.php" class="text-blue-600 hover:text-blue-700">Province</a></span>
                <span class="breadcrumb-item text-gray-900 font-medium"><?php echo htmlspecialchars($province['name']); ?></span>
            </nav>
        </div>
    </div>

    <!-- Province Hero -->
    <div class="bg-gradient-to-r from-blue-600 via-teal-500 to-yellow-500 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="text-6xl mb-6">🏛️</div>
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <span>Provincia di</span> <?php echo htmlspecialchars($province['name']); ?>
            </h1>
            <p class="text-xl text-blue-100 max-w-3xl mx-auto">
                <?php echo htmlspecialchars($province['description']); ?>
            </p>
            <div class="mt-8 flex justify-center gap-4 flex-wrap">
                <span class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full">
                    <?php echo $articleCount; ?> <span><?php echo $articleCount == 1 ? 'articolo' : 'articoli'; ?></span>
                </span>
                <span class="bg-white/20 backdrop-blur-sm text-white px-4 py-2 rounded-full">
                    <?php echo count($articlesWithCoordinates); ?> <span>articoli sulla mappa</span>
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Articles Section -->
            <?php if (!empty($articles)): ?>
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                    <span>Articoli di</span> <?php echo htmlspecialchars($province['name']); ?>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <?php foreach ($articles as $article): ?>
                    <article class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-2xl transition-all duration-300 group">
                        <div class="aspect-[4/3] bg-gradient-to-br from-blue-500 to-teal-600 relative overflow-hidden">
                            <?php if ($article['featured_image']): ?>
                            <img src="image-loader.php?path=<?php echo urlencode($article['featured_image']); ?>"
                                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-teal-600"></div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-black/40"></div>
                            <div class="absolute top-4 left-4 right-4">
                                <div class="flex justify-between items-start">
                                    <span class="bg-white/20 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                                        <?php echo htmlspecialchars($article['category_name'] ?? 'Articolo'); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="absolute bottom-4 left-4 right-4 text-white">
                                <h3 class="text-xl font-bold mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </h3>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($article['excerpt']); ?>
                            </p>
                            <a href="articolo.php?slug=<?php echo $article['slug']; ?>" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                <span>Leggi di più</span> <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Province Gallery Section -->
            <?php if (!empty($galleryImages)): ?>
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                    <span>Galleria di</span> <?php echo htmlspecialchars($province['name']); ?>
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($galleryImages as $image): ?>
                    <div class="group cursor-pointer" onclick="openGalleryModal('image-loader.php?path=<?php echo urlencode($image['image_path']); ?>', '<?php echo htmlspecialchars($image['title']); ?>')">
                        <div class="aspect-[4/3] bg-gray-200 rounded-lg overflow-hidden shadow-lg">
                            <img src="image-loader.php?path=<?php echo urlencode($image['image_path']); ?>"
                                 alt="<?php echo htmlspecialchars($image['title']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        </div>
                        <div class="mt-3">
                            <h3 class="font-semibold text-gray-900"><?php echo htmlspecialchars($image['title']); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($image['description']); ?></p>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Province Map Section -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                    <span>Mappa Interattiva</span> di <?php echo htmlspecialchars($province['name']); ?>
                </h2>
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div id="province-map" class="w-full h-96 bg-gray-100 rounded-lg overflow-hidden"></div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script>
        lucide.createIcons();
        
        function openGalleryModal(imageUrl, title) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="max-w-4xl max-h-full relative">
                    <button onclick="this.parentElement.parentElement.remove()" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                        <i data-lucide="x" class="w-8 h-8"></i>
                    </button>
                    <img src="${imageUrl}" alt="${title}" class="max-w-full max-h-[90vh] rounded-lg">
                </div>
            `;
            document.body.appendChild(modal);
            lucide.createIcons();
        }

        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('province-map')) {
                const provinceArticles = <?php echo json_encode($articlesWithCoordinates); ?>;
                let centerLat = 39.0;
                let centerLng = 16.5;
                if (provinceArticles.length > 0) {
                    centerLat = parseFloat(provinceArticles[0].latitude) || centerLat;
                    centerLng = parseFloat(provinceArticles[0].longitude) || centerLng;
                }

                const map = L.map('province-map').setView([centerLat, centerLng], 9);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors'
                }).addTo(map);

                provinceArticles.forEach(article => {
                    if (article.latitude && article.longitude) {
                        const marker = L.marker([parseFloat(article.latitude), parseFloat(article.longitude)]).addTo(map);
                        const imageUrl = article.featured_image ? `image-loader.php?path=${encodeURIComponent(article.featured_image)}` : '';
                        const popupContent = `
                            <div class="flex items-start space-x-3 p-1">
                                ${imageUrl ? `<img src="${imageUrl}" alt="" class="w-16 h-12 object-cover rounded">` : ''}
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-sm">${article.title}</h4>
                                    <a href="articolo.php?slug=${article.slug}" class="text-blue-600 text-xs font-medium">Leggi di più</a>
                                </div>
                            </div>
                        `;
                        marker.bindPopup(popupContent, { minWidth: 200 });
                    }
                });
            }
        });
    </script>
</body>
</html>