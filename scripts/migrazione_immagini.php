<?php
// Eseguire questo script dalla root del progetto: php scripts/migrazione_immagini.php

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database_mysql.php';
require_once __DIR__ . '/../includes/image_processor.php';

echo "-------------------------------------------\n";
echo "🚀 Inizio Migrazione Immagini Pubbliche 🚀\n";
echo "-------------------------------------------\n\n";

$db = new Database();
$imageProcessor = new ImageProcessor();
$total_migrated = 0;
$total_errors = 0;

function migrate_image($path, $public_path, $type) {
    global $imageProcessor, $total_migrated, $total_errors;
    if (empty($path)) return;

    if ($imageProcessor->publishImage($path, $public_path)) {
        echo "✅ [{$type}] Migrata: {$path} -> {$public_path}\n";
        $total_migrated++;
    } else {
        echo "❌ [{$type}] Errore: Impossibile migrare {$path}. Dettagli: " . $imageProcessor->getLastError() . "\n";
        $total_errors++;
    }
}

// 1. Migrazione Articoli
echo "\n--- Inizio migrazione Articoli ---\n";
$articles = $db->getArticles(null, 0, false);
foreach ($articles as $article) {
    if (empty($article['slug'])) continue;

    migrate_image($article['featured_image'], "articoli/{$article['slug']}/featureds/" . basename($article['featured_image']), 'Articolo');
    migrate_image($article['hero_image'], "articoli/{$article['slug']}/heros/" . basename($article['hero_image']), 'Articolo');
    migrate_image($article['logo'], "articoli/{$article['slug']}/logoss/" . basename($article['logo']), 'Articolo');

    $gallery = json_decode($article['gallery_images'] ?? '[]', true);
    foreach ($gallery as $img) {
        migrate_image($img, "articoli/{$article['slug']}/gallery/" . basename($img), 'Articolo');
    }
}
echo "--- Fine migrazione Articoli ---\n";

// 2. Migrazione Province
echo "\n--- Inizio migrazione Province ---\n";
$provinces = $db->getProvinces();
foreach ($provinces as $province) {
    if (empty($province['slug'])) continue;
    migrate_image($province['image_path'], "provinces/{$province['slug']}/" . basename($province['image_path']), 'Provincia');
}
echo "--- Fine migrazione Province ---\n";

// 3. Migrazione Città
echo "\n--- Inizio migrazione Città ---\n";
$cities = $db->getCities();
foreach ($cities as $city) {
    if (empty($city['slug'])) continue;
    migrate_image($city['hero_image'], "cities/{$city['slug']}/hero/" . basename($city['hero_image']), 'Città');

    $gallery = json_decode($city['gallery_images'] ?? '[]', true);
    foreach ($gallery as $img) {
        migrate_image($img, "cities/{$city['slug']}/gallery/" . basename($img), 'Città');
    }
}
echo "--- Fine migrazione Città ---\n";

// 4. Migrazione Impostazioni (Hero Image)
echo "\n--- Inizio migrazione Impostazioni ---\n";
$hero_image = $db->getSetting('hero_image');
migrate_image($hero_image, 'settings/hero/' . basename($hero_image), 'Impostazioni');
echo "--- Fine migrazione Impostazioni ---\n";

// 5. Migrazione User Uploads (solo approvati)
echo "\n--- Inizio migrazione User Uploads ---\n";
$user_uploads = $db->getUserUploads('approved');
foreach ($user_uploads as $upload) {
    $slug = '';
    $type = '';
    if ($upload['article_id']) {
        $article = $db->getArticleById($upload['article_id']);
        $slug = $article['slug'] ?? '';
        $type = 'articoli';
    } elseif ($upload['city_id']) {
        $city = $db->getCityById($upload['city_id']);
        $slug = $city['slug'] ?? '';
        $type = 'cities';
    }

    if ($type && $slug) {
        migrate_image($upload['image_path'], "{$type}/{$slug}/user-uploads/" . basename($upload['image_path']), 'User Upload');
    }
}
echo "--- Fine migrazione User Uploads ---\n";


echo "\n-------------------------------------------\n";
echo "🎉 Migrazione Completata! 🎉\n";
echo "-------------------------------------------\n";
echo "Totale immagini migrate: {$total_migrated}\n";
echo "Totale errori: {$total_errors}\n\n";

?>
