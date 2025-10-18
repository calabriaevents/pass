<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

if (!isset($_GET['id'])) {
    header('Location: eventi.php');
    exit;
}

$id = $_GET['id'];
$db = new Database();
$event = $db->getEventById($id);

if (!$event) {
    header('HTTP/1.0 404 Not Found');
    echo 'Evento non trovato';
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title><?php echo htmlspecialchars($event['titolo']); ?> - Passione Calabria</title>
    <meta name="description" content="<?php echo htmlspecialchars(substr(strip_tags($event['descrizione']), 0, 160)); ?>">
    <link rel="canonical" href="<?php echo SITE_URL . '/evento-dettaglio.php?id=' . urlencode($id); ?>" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-100">
    <?php include 'includes/header.php'; ?>

    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <?php if ($event['imageUrl']): ?>
                <img src="image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $event['imageUrl'])); ?>" alt="<?php echo htmlspecialchars($event['titolo']); ?>" class="w-full h-96 object-cover">
            <?php endif; ?>
            <div class="p-6">
                <h1 class="text-4xl font-bold text-gray-900 mb-4"><?php echo htmlspecialchars($event['titolo']); ?></h1>
                <div class="flex flex-wrap items-center text-gray-600 mb-6">
                    <div class="mr-6 mb-2">
                        <i data-lucide="calendar" class="inline-block w-5 h-5 mr-2"></i>
                        <span><?php echo htmlspecialchars(date('d/m/Y', strtotime($event['dataEvento']))); ?></span>
                    </div>
                    <div class="mr-6 mb-2">
                        <i data-lucide="clock" class="inline-block w-5 h-5 mr-2"></i>
                        <span><?php echo htmlspecialchars(date('H:i', strtotime($event['orarioInizio']))); ?></span>
                    </div>
                    <div class="mr-6 mb-2">
                        <i data-lucide="map-pin" class="inline-block w-5 h-5 mr-2"></i>
                        <span><?php echo htmlspecialchars($event['citta_name'] ?? 'N/A'); ?>, <?php echo htmlspecialchars($event['province_name'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="mr-6 mb-2">
                        <i data-lucide="tag" class="inline-block w-5 h-5 mr-2"></i>
                        <span><?php echo htmlspecialchars($event['categoria']); ?></span>
                    </div>
                    <div class="mr-6 mb-2">
                        <i data-lucide="dollar-sign" class="inline-block w-5 h-5 mr-2"></i>
                        <span><?php echo htmlspecialchars($event['costoIngresso']); ?></span>
                    </div>
                </div>

                <div class="prose max-w-none">
                    <?php echo nl2br(htmlspecialchars($event['descrizione'])); ?>
                </div>

                <?php if ($event['linkMappaGoogle']): ?>
                <div class="mt-8">
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Mappa</h3>
                    <div class="aspect-w-16 aspect-h-9">
                        <?php echo $event['linkPreviewMappaEmbed']; ?>
                    </div>
                    <a href="<?php echo htmlspecialchars($event['linkMappaGoogle']); ?>" target="_blank" rel="noopener noreferrer" class="inline-block mt-4 bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                        Apri in Google Maps
                    </a>
                </div>
                <?php endif; ?>

                <?php if ($event['linkContattoPrenotazioni']): ?>
                <div class="mt-8 text-center">
                    <a href="<?php echo htmlspecialchars($event['linkContattoPrenotazioni']); ?>" target="_blank" rel="noopener noreferrer" class="inline-block bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg text-lg">
                        Contatta per Prenotazioni
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>