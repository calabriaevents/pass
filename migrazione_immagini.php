<?php
// Eseguire questo script dalla root del progetto una sola volta.
// Esempio: php migrazione_immagini.php
// Assicurarsi di aver fatto un backup del database e della cartella /uploads prima di procedere.

set_time_limit(0); // Rimuove il limite di tempo per l'esecuzione dello script
ini_set('memory_limit', '512M'); // Aumenta la memoria disponibile

require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
require_once 'includes/image_processor.php';

echo "--- Inizio Migrazione Immagini ---\n";

$db = new Database();
$imageProcessor = new ImageProcessor();
$pdo = $db->getConnection();

function migrate_image(string $old_relative_path, string $subfolder, ImageProcessor $processor): ?string {
    if (empty($old_relative_path) || str_contains($old_relative_path, 'uploads_protected')) {
        // Salta se il percorso è vuoto o già nel formato corretto
        return null;
    }

    $file_full_path = __DIR__ . '/' . $old_relative_path;

    if (!file_exists($file_full_path)) {
        echo "File non trovato, skippato: $old_relative_path\n";
        return null;
    }

    $file_info_for_processor = [
        'name' => basename($file_full_path),
        'tmp_name' => $file_full_path,
        'error' => UPLOAD_ERR_OK,
        'size' => filesize($file_full_path)
    ];

    $new_path = $processor->processUploadedImage($file_info_for_processor, $subfolder);

    if ($new_path) {
        unlink($file_full_path);
        echo "Migrato: $old_relative_path -> $new_path\n";
        return $new_path;
    } else {
        echo "Errore nella migrazione di: $old_relative_path\n";
        return null;
    }
}

// 1. Migrazione Articoli (featured_image, hero_image, logo)
echo "\n--- Migrazione immagini singole degli Articoli ---\n";
$stmt = $pdo->query("SELECT id, featured_image, hero_image, logo FROM articles");
$articles = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($articles as $article) {
    // Featured Image
    if ($new_featured_path = migrate_image($article['featured_image'], 'articles/featured', $imageProcessor)) {
        $updateStmt = $pdo->prepare("UPDATE articles SET featured_image = ? WHERE id = ?");
        $updateStmt->execute([$new_featured_path, $article['id']]);
    }
    // Hero Image
    if ($new_hero_path = migrate_image($article['hero_image'], 'articles/hero', $imageProcessor)) {
        $updateStmt = $pdo->prepare("UPDATE articles SET hero_image = ? WHERE id = ?");
        $updateStmt->execute([$new_hero_path, $article['id']]);
    }
    // Logo
    if ($new_logo_path = migrate_image($article['logo'], 'articles/logo', $imageProcessor)) {
        $updateStmt = $pdo->prepare("UPDATE articles SET logo = ? WHERE id = ?");
        $updateStmt->execute([$new_logo_path, $article['id']]);
    }
}
echo "--- Fine migrazione immagini singole degli Articoli ---\n";


// 2. Migrazione Gallerie Articoli (campo JSON)
echo "\n--- Migrazione Gallerie degli Articoli ---\n";
$stmt = $pdo->query("SELECT id, gallery_images FROM articles WHERE gallery_images IS NOT NULL AND gallery_images != '[]' AND gallery_images != ''");
$articles_with_galleries = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($articles_with_galleries as $article) {
    $gallery_json = $article['gallery_images'];
    $gallery_array = json_decode($gallery_json, true);

    if (!is_array($gallery_array)) {
        echo "Dati galleria non validi per articolo ID: {$article['id']}, skippato.\n";
        continue;
    }

    $new_gallery_array = [];
    $was_changed = false;

    foreach ($gallery_array as $old_image_path) {
        if ($new_path = migrate_image($old_image_path, 'articles/gallery', $imageProcessor)) {
            $new_gallery_array[] = $new_path;
            $was_changed = true;
        } else {
            // Se la migrazione fallisce o viene skippata, mantieni il vecchio percorso
            $new_gallery_array[] = $old_image_path;
        }
    }

    if ($was_changed) {
        $new_gallery_json = json_encode($new_gallery_array);
        $updateStmt = $pdo->prepare("UPDATE articles SET gallery_images = ? WHERE id = ?");
        $updateStmt->execute([$new_gallery_json, $article['id']]);
        echo "Galleria aggiornata per articolo ID: {$article['id']}\n";
    }
}
echo "--- Fine migrazione Gallerie degli Articoli ---\n";


// 3. Migrazione Foto Utenti
// Assumendo che la tabella sia 'user_uploads' e la colonna 'image_path'
echo "\n--- Migrazione Foto Caricate dagli Utenti ---\n";
$stmt = $pdo->query("SELECT id, image_path FROM user_uploads WHERE image_path IS NOT NULL AND image_path != ''");
$user_photos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($user_photos as $photo) {
    if ($new_path = migrate_image($photo['image_path'], 'user_photos', $imageProcessor)) {
        $updateStmt = $pdo->prepare("UPDATE user_uploads SET image_path = ? WHERE id = ?");
        $updateStmt->execute([$new_path, $photo['id']]);
    }
}
echo "--- Fine migrazione Foto Utenti ---\n";


echo "\n--- Migrazione Completata! ---\n";
echo "Ricorda di eliminare questo script ('migrazione_immagini.php') dal server.\n";
echo "Puoi anche valutare di eliminare le vecchie cartelle vuote in /uploads (es. /uploads/articles, /uploads/user-experiences).\n";

?>