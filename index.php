<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';
require_once 'includes/maintenance_check.php';

$db = new Database();
$latest_articles = $db->getArticles(null, 10); // Carica gli ultimi 10 articoli pubblicati
$categories = $db->getCategories();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Passione Calabria</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-100">

    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <section class="bg-cover bg-center h-96 rounded-lg shadow-lg text-white flex items-center justify-center mb-12" style="background-image: url('https://images.unsplash.com/photo-1590827254752-a3091c12df8b?q=80&w=2070&auto=format&fit=crop');">
            <div class="bg-black bg-opacity-50 p-8 rounded-lg text-center">
                <h1 class="text-5xl font-bold mb-4">Scopri la Calabria</h1>
                <p class="text-xl">Eventi, luoghi e sapori di una terra unica.</p>
            </div>
        </section>

        <section>
            <h2 class="text-3xl font-bold text-gray-800 mb-6">In Evidenza</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($latest_articles as $article): ?>
                <a href="articolo.php?id=<?php echo $article['id']; ?>" class="bg-white rounded-lg shadow-md overflow-hidden group hover:shadow-xl transition-shadow duration-300">
                    <div class="relative">
                        <img class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300"
                             src="image-loader.php?path=<?php echo urlencode($article['featured_image']); ?>"
                             alt="<?php echo htmlspecialchars($article['title']); ?>">
                        <div class="absolute bottom-0 left-0 bg-blue-600 text-white px-3 py-1 text-sm font-semibold rounded-tr-lg">
                            <?php echo htmlspecialchars($article['category_name']); ?>
                        </div>
                    </div>
                    <div class="p-6">
                        <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($article['title']); ?></h3>
                        <p class="text-gray-600 text-sm mb-4">
                            <i data-lucide="map-pin" class="inline-block w-4 h-4 mr-1"></i>
                            <?php echo htmlspecialchars($article['city_name'] ?? 'N/D'); ?>, <?php echo htmlspecialchars($article['province_name'] ?? 'N/D'); ?>
                        </p>
                        <p class="text-gray-700 leading-relaxed"><?php echo htmlspecialchars($article['excerpt']); ?></p>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

    </main>

    <?php include 'includes/footer.php'; ?>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>