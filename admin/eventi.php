<?php
require_once __DIR__ . '/auth_check.php';
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php';

$db = new Database();
$imageProcessor = new ImageProcessor();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$error_message = ''; // Variabile per gli errori

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titolo = $_POST['titolo'] ?? '';
    $nomeAttivita = $_POST['nomeAttivita'] ?? '';
    $descrizione = $_POST['descrizione'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $provincia_id = $_POST['provincia_id'] ?? null;
    $citta_id = $_POST['citta_id'] ?? null;
    $dataEvento = $_POST['dataEvento'] ?? '';
    $orarioInizio = $_POST['orarioInizio'] ?? '';
    $costoIngresso = $_POST['costoIngresso'] ?? '';
    $linkMappaGoogle = $_POST['linkMappaGoogle'] ?? '';
    $linkPreviewMappaEmbed = $_POST['linkPreviewMappaEmbed'] ?? '';
    $linkContattoPrenotazioni = $_POST['linkContattoPrenotazioni'] ?? '';

    // --- GESTIONE UPLOAD SICURA CON CONTROLLO ERRORI ---
    $imageUrl = null;

    try {
        if (isset($_FILES['imageUrl']) && $_FILES['imageUrl']['error'] === UPLOAD_ERR_OK) {
            $imageUrl = $imageProcessor->processUploadedImage($_FILES['imageUrl'], 'events');
            if (!$imageUrl) throw new Exception("Errore nel caricamento dell'immagine dell'evento: " . $imageProcessor->getLastError());
        }

        // --- OPERAZIONI SUL DATABASE ---
        if ($action === 'edit' && $id) {
            $existingEvent = $db->getEventById($id);
            if ($imageUrl === null) $imageUrl = $existingEvent['imageUrl'] ?? null;

            $db->updateEvent($id, $titolo, $nomeAttivita, $descrizione, $categoria, $provincia_id, $citta_id, $dataEvento, $orarioInizio, $costoIngresso, $imageUrl, $linkMappaGoogle, $linkPreviewMappaEmbed, $linkContattoPrenotazioni);
        } else {
            $db->createEvent($titolo, $nomeAttivita, $descrizione, $categoria, $provincia_id, $citta_id, $dataEvento, $orarioInizio, $costoIngresso, $imageUrl, $linkMappaGoogle, $linkPreviewMappaEmbed, $linkContattoPrenotazioni);
        }

        header('Location: eventi.php');
        exit;

    } catch (Exception $e) {
        $error_message = $e->getMessage();
    }
}

if ($action === 'delete' && $id) {
    $db->deleteEvent($id);
    header('Location: eventi.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Eventi - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-gray-900 text-white w-64 flex flex-col">
        <div class="p-4 border-b border-gray-700">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-yellow-500 rounded-full flex items-center justify-center">
                    <span class="text-white font-bold text-sm">PC</span>
                </div>
                <div>
                    <h1 class="font-bold text-lg">Admin Panel</h1>
                    <p class="text-xs text-gray-400">Passione Calabria</p>
                </div>
            </div>
        </div>
        <?php include 'partials/menu.php'; ?>
        <div class="p-4 border-t border-gray-700">
            <a href="../index.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors"><i data-lucide="log-out" class="w-5 h-5"></i><span>Torna al Sito</span></a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-900">Gestione Eventi</h1>
                <?php if ($action === 'list'): ?>
                <a href="eventi.php?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Nuovo Evento</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if (!empty($error_message)): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Errore!</strong>
                <span class="block sm:inline"><?php echo htmlspecialchars($error_message); ?></span>
            </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Eventi</h2>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Immagine</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Titolo</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Data</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Città</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $events = $db->getAllEvents();
                        foreach ($events as $event):
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <div class="flex items-center space-x-3">
                                    <?php if (!empty($event['imageUrl'])): ?>
                                    <img src="../image-loader.php?path=<?php echo urlencode(str_replace('uploads_protected/', '', $event['imageUrl'] ?? '')); ?>" alt="Immagine <?php echo htmlspecialchars($event['titolo']); ?>" class="w-12 h-12 object-contain rounded-lg border p-1">
                                    <?php else: ?>
                                    <div class="w-12 h-12 bg-gray-200 rounded-lg border flex items-center justify-center">
                                        <i data-lucide="image-off" class="w-5 h-5 text-gray-400"></i>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <div>
                                    <div class="font-medium"><?php echo htmlspecialchars($event['titolo']); ?></div>
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($event['dataEvento']))); ?>
                            </td>
                            <td class="py-3 px-2">
                                <?php echo htmlspecialchars($event['citta_name'] ?? 'N/A'); ?>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex space-x-2">
                                    <a href="eventi.php?action=edit&id=<?php echo $event['id']; ?>"
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                                        <i data-lucide="edit" class="w-3 h-3 mr-1"></i>
                                        Modifica
                                    </a>
                                    <a href="eventi.php?action=delete&id=<?php echo $event['id']; ?>"
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors"
                                       onclick="return confirm('Sei sicuro di voler eliminare questo evento?');">
                                        <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>
                                        Elimina
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($action === 'new' || $action === 'edit'):
                $event = null;
                if ($action === 'edit' && $id) {
                    $event = $db->getEventById($id);
                }
                $provinces = $db->getProvinces();
                $cities = $db->getCities();
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-1">
                    <?php echo $action === 'edit' ? 'Modifica Evento' : 'Nuovo Evento'; ?>
                </h2>
                <form action="eventi.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST" enctype="multipart/form-data">
                    <?php include 'forms/form_evento.php'; ?>
                    <div class="text-right mt-6 border-t pt-4">
                        <a href="eventi.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva Evento</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>