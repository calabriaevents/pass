<?php
// Inclusione della configurazione e connessione al database del progetto principale
require_once '../includes/database_mysql.php';
require_once '../includes/eventi_manager.php';

$db = new Database();
$events = get_all_events(true); // Solo eventi approvati e futuri
?>

<?php include '../includes/header.php'; ?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eventi in Calabria - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container mx-auto my-5">

        <h1 class="text-4xl font-bold text-center text-gray-800 mb-8">Prossimi Eventi in Calabria</h1>

        <?php if (empty($events)): ?>
            <p class="text-center text-gray-600">Non ci sono eventi approvati in programma per i prossimi giorni.</p>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($events as $event): ?>
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                        <?php if ($event['immagine']): ?>
                            <a href="dettaglio.php?id=<?= $event['id'] ?>">
                                <img src="../image-loader.php?path=<?= htmlspecialchars('eventi/' . $event['immagine']) ?>&w=400&h=250&m=crop" class="w-full h-48 object-cover" alt="<?= htmlspecialchars($event['titolo']) ?>">
                            </a>
                        <?php endif; ?>
                        <div class="p-6">
                            <h2 class="text-2xl font-bold text-gray-900 mb-2">
                                <a href="dettaglio.php?id=<?= $event['id'] ?>" class="hover:text-blue-600">
                                    <?= htmlspecialchars($event['titolo']) ?>
                                </a>
                            </h2>
                            <p class="text-gray-600 mb-4">
                                <strong>Data:</strong> <?= date('d/m/Y', strtotime($event['data_evento'])) ?><br>
                                <strong>Luogo:</strong> <?= htmlspecialchars($event['luogo']) ?> (<?= htmlspecialchars($event['nome_citta']) ?>)<br>
                                <?php if ($event['nome_categoria']): ?>
                                    <strong>Categoria:</strong> <?= htmlspecialchars($event['nome_categoria']) ?>
                                <?php endif; ?>
                            </p>
                            <a href="dettaglio.php?id=<?= $event['id'] ?>" class="text-blue-600 hover:underline">Dettagli Evento</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php include '../includes/footer.php'; ?>
</body>
</html>