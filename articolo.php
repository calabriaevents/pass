<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
require_once 'includes/maintenance_check.php';

$db = new Database();
$article_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($article_id === 0) {
    header("Location: index.php");
    exit;
}

$article = $db->getArticleById($article_id);
if (!$article) {
    // Gestisci articolo non trovato
    http_response_code(404);
    echo "Articolo non trovato.";
    exit;
}

$json_data = json_decode($article['json_data'] ?? '{}', true);
$gallery_images = json_decode($article['gallery_images'] ?? '[]', true);

// Determina quale template di visualizzazione usare
$template_name = 'view_default.php';
$category_name = trim($article['category_name']);
// ... (logica per scegliere il template, se esiste) ...

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($article['title']); ?> - Passione Calabria</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-100">

    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <article>
            <div class="relative h-96 rounded-lg shadow-lg overflow-hidden mb-8">
                <img src="image-loader.php?path=<?php echo urlencode($article['hero_image'] ?? ''); ?>"
                     alt="<?php echo htmlspecialchars($article['title']); ?>"
                     class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black via-transparent to-transparent opacity-70"></div>
                <div class="absolute bottom-0 left-0 p-8">
                    <h1 class="text-4xl font-bold text-white"><?php echo htmlspecialchars($article['title']); ?></h1>
                    <div class="text-white text-lg mt-2">
                        <i data-lucide="map-pin" class="inline-block w-5 h-5 mr-2"></i>
                        <span><?php echo htmlspecialchars($article['city_name'] ?? ''); ?>, <?php echo htmlspecialchars($article['province_name'] ?? ''); ?></span>
                    </div>
                </div>
            </div>

            <div class="bg-white p-8 rounded-lg shadow-md">
                <div class="prose max-w-none">
                    <?php echo $article['content']; ?>
                </div>
            </div>

            <?php if (!empty($gallery_images)): ?>
            <section class="mt-12">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Galleria</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($gallery_images as $image_path): ?>
                    <div>
                        <a href="image-loader.php?path=<?php echo urlencode($image_path); ?>" data-fancybox="gallery">
                            <img src="image-loader.php?path=<?php echo urlencode($image_path); ?>"
                                 alt="Galleria immagine" class="rounded-lg shadow-md hover:opacity-90 transition-opacity">
                        </a>
                    </div>
                    <?php endforeach; ?>
                </div>
            </section>
            <?php endif; ?>
        </article>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        lucide.createIcons();
    </script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4/dist/fancybox.css"/>
    <script src="https://cdn.jsdelivr.net/npm/@fancyapps/ui@4/dist/fancybox.umd.js"></script>
</body>
</html>