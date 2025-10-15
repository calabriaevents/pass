<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();
$articles = $db->getAllArticlesWithCoordinates();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mappa - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-4">Mappa della Calabria</h1>
        <p class="text-center text-gray-600 mb-8">Esplora gli articoli e i luoghi d'interesse attraverso la mappa interattiva</p>
        <div class="bg-white rounded-lg shadow-lg p-4 mb-4">
            <div class="flex justify-between items-center text-sm text-gray-600">
                <div class="flex items-center">
                    <i data-lucide="map-pin" class="w-4 h-4 mr-2 text-blue-600"></i>
                    <span id="map-info">Caricamento...</span>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center">
                        <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                        <span>Articoli</span>
                    </div>
                </div>
            </div>
        </div>
        <div id="map" style="height: 600px;" class="rounded-lg shadow-lg"></div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
        var map = L.map('map').setView([39.0, 16.5], 9);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var articles = <?php echo json_encode($articles); ?>;

        articles.forEach(function(article) {
            if (article.latitude && article.longitude) {
                var marker = L.marker([parseFloat(article.latitude), parseFloat(article.longitude)]).addTo(map);
                var popupContent = '<div class="p-3 min-w-64">' +
                    '<div class="flex items-start space-x-3">' +
                        (article.logo ?
                            '<img src="image-loader.php?path=' + article.logo.replace('uploads_protected/', '') + '" alt="' + article.title + '" class="w-16 h-12 object-contain rounded">' :
                            '<div class="w-16 h-12 bg-gray-200 rounded flex items-center justify-center"><i data-lucide="image" class="w-4 h-4 text-gray-500"></i></div>'
                        ) +
                        '<div class="flex-1">' +
                            '<h4 class="font-bold text-gray-900 text-sm mb-1">' + article.title + '</h4>' +
                            (article.excerpt ? '<p class="text-gray-600 text-xs mb-2 line-clamp-2">' + article.excerpt.substring(0, 80) + '...</p>' : '') +
                            '<div class="flex items-center justify-between">' +
                                '<div class="flex items-center text-xs text-gray-500">' +
                                    (article.category_icon ? '<span class="mr-1">' + article.category_icon + '</span>' : '') +
                                    '<span>' + (article.category_name || 'Articolo') + '</span>' +
                                '</div>' +
                                '<a href="articolo.php?slug=' + article.slug + '" class="text-blue-600 hover:text-blue-800 text-xs font-medium">Leggi</a>' +
                            '</div>' +
                        '</div>' +
                    '</div>';

                if (article.google_maps_iframe) {
                    popupContent += '<div class="mt-2">' + article.google_maps_iframe + '</div>';
                }

                popupContent += '</div>';
                marker.bindPopup(popupContent, {maxWidth: 300});
            }
        });
        
        // Update map info
        document.addEventListener('DOMContentLoaded', function() {
            var mapInfo = document.getElementById('map-info');
            if (mapInfo) {
                mapInfo.textContent = articles.length + ' articoli visualizzati';
            }
        });
    </script>
</body>
</html>
