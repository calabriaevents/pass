<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();
$events = $db->getAllEvents();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventi - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Tutti gli Eventi</h1>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach ($events as $event): ?>
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <?php if ($event['imageUrl']): ?>
                        <a href="evento-dettaglio.php?id=<?php echo $event['id']; ?>">
                            <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $event['imageUrl'])); ?>" alt="<?php echo htmlspecialchars($event['titolo']); ?>" class="w-full h-48 object-cover">
                        </a>
                    <?php endif; ?>
                    <div class="p-6">
                        <h2 class="text-2xl font-bold text-gray-900 mb-2">
                            <a href="evento-dettaglio.php?id=<?php echo $event['id']; ?>" class="hover:text-blue-600">
                                <?php echo htmlspecialchars($event['titolo']); ?>
                            </a>
                        </h2>
                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars(substr($event['descrizione'], 0, 100)); ?>...</p>
                        <a href="evento-dettaglio.php?id=<?php echo $event['id']; ?>" class="text-blue-600 hover:underline">Vedi Dettagli</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>