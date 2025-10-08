<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();
$provinces = $db->getProvinces();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Province - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Province della Calabria</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($provinces as $province): ?>
                <a href="provincia.php?id=<?php echo $province['id']; ?>" class="block bg-white rounded-lg shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300 group">
                    <?php if ($province['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($province['image_path']); ?>" alt="<?php echo htmlspecialchars($province['name']); ?>" class="w-full h-48 object-cover group-hover:scale-105 transition-transform duration-300">
                    <?php endif; ?>
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2 group-hover:text-blue-600 transition-colors"><?php echo htmlspecialchars($province['name']); ?></h2>
                        <p class="text-gray-600"><?php echo htmlspecialchars($province['description']); ?></p>
                        <div class="mt-4 flex items-center text-blue-600 font-semibold opacity-0 group-hover:opacity-100 transition-opacity">
                            <span>Esplora provincia</span>
                            <i data-lucide="arrow-right" class="w-4 h-4 ml-1"></i>
                        </div>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
