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

// Carica galleria della provincia
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

    <!-- Suggest Section -->
    <div class="bg-gradient-to-r from-green-50 to-blue-50 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="text-center">
                <div class="text-4xl mb-4">💡</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Suggerisci un Luogo</h2>
                <p class="text-gray-600 mb-6 max-w-2xl mx-auto">
                    Conosci un luogo interessante in <?php echo htmlspecialchars($province['name']); ?> che dovrebbe essere incluso nel nostro sito? Suggeriscilo alla nostra community!
                </p>
                <a href="suggerisci.php" class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-3 rounded-full transition-colors">
                    <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                    <span>Suggerisci un Luogo</span>
                </a>
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
                        <!-- Article Image -->
                        <div class="aspect-[4/3] bg-gradient-to-br from-blue-500 to-teal-600 relative overflow-hidden">
                            <?php if ($article['featured_image']): ?>
                            <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $article['featured_image'] ?? '')); ?>"
                                 alt="<?php echo htmlspecialchars($article['title']); ?>"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            <?php else: ?>
                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-teal-600"></div>
                            <?php endif; ?>
                            
                            <div class="absolute inset-0 bg-black/40"></div>
                            
                            <!-- Article Meta -->
                            <div class="absolute top-4 left-4 right-4">
                                <div class="flex justify-between items-start">
                                    <span class="bg-white/20 backdrop-blur-sm text-white px-3 py-1 rounded-full text-sm">
                                        <?php echo htmlspecialchars($article['category_name'] ?? 'Articolo'); ?>
                                    </span>
                                    <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-medium">
                                        <?php echo $article['views']; ?> <span>visite</span>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="absolute bottom-4 left-4 right-4 text-white">
                                <h3 class="text-xl font-bold mb-2 line-clamp-2">
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </h3>
                            </div>
                        </div>

                        <!-- Article Content -->
                        <div class="p-6">
                            <p class="text-gray-600 mb-4 line-clamp-3">
                                <?php echo htmlspecialchars($article['excerpt']); ?>
                            </p>
                            
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center space-x-4 text-sm text-gray-500">
                                    <span class="flex items-center">
                                        <i data-lucide="calendar" class="w-4 h-4 mr-1"></i>
                                        <?php echo formatDate($article['created_at']); ?>
                                    </span>
                                    <span class="flex items-center">
                                        <i data-lucide="user" class="w-4 h-4 mr-1"></i>
                                        <?php echo htmlspecialchars($article['author']); ?>
                                    </span>
                                </div>
                            </div>
                            
                            <a href="articolo.php?slug=<?php echo $article['slug']; ?>" 
                               class="inline-flex items-center text-blue-600 hover:text-blue-700 font-semibold transition-colors">
                                <span>Leggi di più</span> <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                            </a>
                        </div>
                    </article>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php else: ?>
            <!-- Empty Articles State -->
            <div class="text-center py-20">
                <div class="text-6xl mb-6">📝</div>
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    Nessun articolo disponibile
                </h2>
                <p class="text-gray-600 mb-8 max-w-md mx-auto">
                    Non ci sono ancora articoli per questa provincia, ma ne stiamo preparando di fantastici!
                </p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="province.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-full font-semibold transition-colors">
                        Esplora Altre Province
                    </a>
                    <a href="suggerisci.php" class="border-2 border-blue-600 text-blue-600 hover:bg-blue-600 hover:text-white px-6 py-3 rounded-full font-semibold transition-colors">
                        Suggerisci Contenuti
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Include User Experiences Section -->
            <?php
            $article_id = null;
            $province_id = $provinceId;
            include __DIR__ . '/partials/user-experiences.php';
            ?>
            
            <!-- Province Map Section -->
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                    <span>Mappa Interattiva</span> di <?php echo htmlspecialchars($province['name']); ?>
                </h2>
                <div class="bg-white rounded-2xl shadow-lg p-8">
                    <div class="mb-6">
                        <p class="text-gray-600 text-center mb-4">
                            Esplora <?php echo htmlspecialchars($province['name']); ?> con la mappa interattiva. Scopri città, luoghi d'interesse e punti di riferimento.
                        </p>
                    </div>
                    <div id="province-map" class="w-full h-96 bg-gray-100 rounded-lg overflow-hidden">
                        <!-- Mappa Leaflet provincia specifica -->
                    </div>
                    <div class="mt-4 text-center">
                        <p class="text-sm text-gray-500">
                            <span><?php echo count($articlesWithCoordinates); ?> articoli visualizzati</span>
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Province Gallery Section -->
            <?php if (!empty($galleryImages)): ?>
            <div class="mb-16">
                <h2 class="text-3xl font-bold text-gray-900 mb-8 text-center">
                    <span>Galleria di</span> <?php echo htmlspecialchars($province['name']); ?>
                </h2>
                <p class="text-gray-600 text-center mb-8">
                    Le foto più belle condivise dalla nostra community
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($galleryImages as $image): ?>
                    <div class="group cursor-pointer" onclick="openGalleryModal('image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $image['image_path'])); ?>', '<?php echo htmlspecialchars($image['title']); ?>')">
                        <div class="aspect-[4/3] bg-gray-200 rounded-lg overflow-hidden shadow-lg">
                            <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $image['image_path'])); ?>"
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
                
                <!-- Call to Action per condividere foto -->
                <div class="bg-gradient-to-r from-blue-50 to-green-50 rounded-2xl p-8 mt-12 text-center">
                    <div class="text-4xl mb-4">📸</div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Condividi le tue foto!</h3>
                    <p class="text-gray-600 mb-6">
                        <span>Hai delle belle foto di</span> <?php echo htmlspecialchars($province['name']); ?>? <span>Condividile con la community!</span>
                    </p>
                    <button onclick="openUploadModal(null, <?php echo $provinceId; ?>)" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-full transition-colors">
                        <i data-lucide="camera" class="w-5 h-5 mr-2 inline"></i>
                        Carica Foto
                    </button>
                </div>
            </div>
            
            <!-- Related Provinces -->
            <div class="mt-16">
                <h3 class="text-2xl font-bold text-gray-900 mb-8 text-center">
                    Esplora Altre Province
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <?php 
                    $otherProvinces = array_filter($db->getProvinces(), function($prov) use ($provinceId) {
                        return $prov['id'] != $provinceId;
                    });
                    ?>
                    <?php foreach ($otherProvinces as $relatedProvince): ?>
                    <a href="provincia.php?id=<?php echo $relatedProvince['id']; ?>" 
                       class="block bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-all group">
                        <div class="text-4xl mb-3">🏛️</div>
                        <h4 class="text-lg font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors">
                            <?php echo htmlspecialchars($relatedProvince['name']); ?>
                        </h4>
                        <p class="text-gray-600 text-sm mb-4">
                            <?php echo htmlspecialchars(substr($relatedProvince['description'], 0, 80)); ?>...
                        </p>
                        <div class="flex items-center text-blue-600 font-semibold">
                            <span>Esplora</span> <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Include User Upload Modal -->
    <?php include 'partials/user-upload-modal.php'; ?>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Inizializza Lucide icons
        lucide.createIcons();

        // Animazioni scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-fade-in-up');
                }
            });
        }, observerOptions);

        document.querySelectorAll('.bg-white').forEach(card => {
            observer.observe(card);
        });
        
        // Funzioni per la galleria
        function openGalleryModal(imageUrl, title) {
            // Crea modal per visualizzare immagine ingrandita
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 bg-black/80 flex items-center justify-center z-50 p-4';
            modal.innerHTML = `
                <div class="max-w-4xl max-h-full relative">
                    <button onclick="this.parentElement.parentElement.remove()" class="absolute -top-10 right-0 text-white hover:text-gray-300">
                        <i data-lucide="x" class="w-8 h-8"></i>
                    </button>
                    <img src="${imageUrl}" alt="${title}" class="max-w-full max-h-full rounded-lg">
                    <div class="absolute bottom-0 left-0 right-0 bg-black/60 text-white p-4 rounded-b-lg">
                        <h3 class="text-lg font-semibold">${title}</h3>
                    </div>
                </div>
            `;
            
            document.body.appendChild(modal);
            lucide.createIcons();
            
            // Chiudi modal con ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modal.remove();
                }
            });
            
            // Chiudi modal cliccando fuori
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    modal.remove();
                }
            });
        }
        
        // Inizializza mappa specifica provincia
        document.addEventListener('DOMContentLoaded', function() {
            if (document.getElementById('province-map')) {
                initProvinceMap();
            }
        });
        
        function initProvinceMap() {
            const provinceArticles = <?php echo json_encode($articlesWithCoordinates); ?>;
            const provinceName = <?php echo json_encode($province['name']); ?>;
            
            if (provinceArticles.length === 0) {
                document.getElementById('province-map').innerHTML = `
                    <div class="w-full h-full flex items-center justify-center text-gray-500">
                        <div class="text-center">
                            <i data-lucide="map-off" class="w-16 h-16 mx-auto mb-4"></i>
                            <p>Nessun articolo con coordinate per questa provincia</p>
                        </div>
                    </div>
                `;
                lucide.createIcons();
                return;
            }
            
            // Calcola centro mappa dal primo articolo
            const firstArticle = provinceArticles[0];
            let centerLat = parseFloat(firstArticle.latitude) || 39.0;
            let centerLng = parseFloat(firstArticle.longitude) || 16.5;
            
            const map = L.map('province-map').setView([centerLat, centerLng], 10);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);
            
            // Aggiungi marker per ogni articolo
            provinceArticles.forEach(article => {
                if (article.latitude && article.longitude) {
                    const marker = L.marker([parseFloat(article.latitude), parseFloat(article.longitude)]).addTo(map);
                    const popupContent = `
                        <div class="p-3 min-w-64">
                            <div class="flex items-start space-x-3">
                                ${article.featured_image ?
                                    `<img src="image-loader.php?path=${encodeURIComponent(article.featured_image.replace('uploads_protected/', ''))}" alt="${article.title}" class="w-16 h-12 object-cover rounded">` :
                                    `<div class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center"><i data-lucide="image" class="w-4 h-4 text-gray-500"></i></div>`
                                }
                                <div class="flex-1">
                                    <h4 class="font-bold text-gray-900 text-sm mb-1">${article.title}</h4>
                                    ${article.excerpt ? `<p class="text-gray-600 text-xs mb-2">${article.excerpt.substring(0, 80)}...</p>` : ''}
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center text-xs text-gray-500">
                                            ${article.category_icon ? `<span class="mr-1">${article.category_icon}</span>` : ''}
                                            <span>${article.category_name || 'Articolo'}</span>
                                        </div>
                                        <a href="articolo.php?slug=${article.slug}" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Leggi</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                    marker.bindPopup(popupContent, {maxWidth: 300});
                }
            });
            
            // Adatta la vista per includere tutti i marker
            if (provinceArticles.length > 1) {
                const markers = provinceArticles
                    .filter(article => article.latitude && article.longitude)
                    .map(article => L.marker([parseFloat(article.latitude), parseFloat(article.longitude)]));
                if (markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        }
    </script>
    
    <!-- Gallery Modal Styles -->
    <style>
        .gallery-modal {
            animation: fadeIn 0.3s ease-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</body>
</html>