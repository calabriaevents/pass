<?php
// Inclusione della configurazione e connessione al database
require_once '../includes/database_mysql.php';
require_once '../includes/eventi_manager.php';

$db = new Database();
$event_id = $_GET['id'] ?? null;
$event = null;

if ($event_id) {
    $event = get_event_by_id($event_id);

    // Controlla che l'evento esista, sia approvato e non sia scaduto
    if (!$event || $event['approvato'] != 1 || strtotime($event['data_evento']) < strtotime(date('Y-m-d'))) {
        $event = null;
    }
}

if (!$event) {
    // Gestione 404/reindirizzamento se l'evento non è valido o trovato
    header('Location: index.php');
    exit;
}

$page_title = $event['titolo'] . ' - Eventi in Calabria';
?>

<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <div class="container mx-auto my-5">

        <nav aria-label="breadcrumb">
            <ol class="flex space-x-2 text-gray-600">
                <li><a href="../index.php" class="hover:underline">Home</a></li>
                <li>/</li>
                <li><a href="index.php" class="hover:underline">Eventi</a></li>
                <li>/</li>
                <li class="font-bold"><?= htmlspecialchars($event['titolo']) ?></li>
            </ol>
        </nav>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-6">
            <h1 class="text-4xl font-bold text-gray-900 mb-4 p-6"><?= htmlspecialchars($event['titolo']) ?></h1>

            <?php if ($event['immagine']): ?>
                <img src="../image-loader.php?path=<?= htmlspecialchars('eventi/' . $event['immagine']) ?>&w=800&h=450&m=crop" class="w-full h-96 object-cover" alt="<?= htmlspecialchars($event['titolo']) ?>">
            <?php endif; ?>

            <div class="p-6">
                <div class="text-lg text-gray-700 mb-6">
                    <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($event['data_evento'])) ?></p>
                    <?php if ($event['ora_inizio'] && $event['ora_inizio'] !== '00:00:00'): ?>
                        <p><strong>Orario:</strong> <?= date('H:i', strtotime($event['ora_inizio'])) ?>
                        <?php if ($event['ora_fine'] && $event['ora_fine'] !== '00:00:00'): ?>
                            - <?= date('H:i', strtotime($event['ora_fine'])) ?>
                        <?php endif; ?></p>
                    <?php endif; ?>
                    <p><strong>Luogo:</strong> <?= htmlspecialchars($event['luogo']) ?>
                    <?php if ($event['nome_citta']): ?>
                        (A <a href="../citta-dettaglio.php?slug=<?= urlencode($event['slug_citta']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($event['nome_citta']) ?></a>)
                    <?php endif; ?></p>
                    <?php if ($event['nome_categoria']): ?>
                        <p><strong>Categoria:</strong> <a href="../categoria.php?id=<?= urlencode($event['categoria_id']) ?>" class="text-blue-600 hover:underline"><?= htmlspecialchars($event['nome_categoria']) ?></a></p>
                    <?php endif; ?>
                </div>

                <hr class="my-6">

                <h2 class="text-3xl font-bold text-gray-800 mb-4">Descrizione Evento</h2>
                <div class="prose max-w-none">
                    <?= nl2br(htmlspecialchars($event['descrizione'])) ?>
                </div>

                <hr class="my-6">

                <a href="index.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                    Torna agli Eventi
                </a>
            </div>
        </div>
    </div>

<?php include '../includes/footer.php'; ?>
</body>
</html>