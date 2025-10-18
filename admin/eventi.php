<?php
// Inclusione delle dipendenze per l'Admin
require_once 'auth_check.php'; // Usa l'autenticazione esistente di Passione Calabria
require_once '../includes/database_mysql.php';
require_once '../includes/eventi_manager.php';
require_once '../includes/image_processor.php'; // Per gestire l'upload delle immagini

$db = new Database();
$imageProcessor = new ImageProcessor();
$message = '';
$error = '';

// Logica di gestione del form (Aggiungi/Modifica/Elimina/Approva)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $event_id = $_POST['id'] ?? null;
    $dir_upload = 'eventi/'; // DEVI CREARE QUESTA CARTELLA

    if ($action === 'add' || $action === 'edit') {
        $data = $_POST;
        $immagine_nome_db = $_POST['immagine_corrente'] ?? null;

        // Gestione upload immagine
        if (isset($_FILES['immagine']) && $_FILES['immagine']['error'] === UPLOAD_ERR_OK) {

            $upload_result = $imageProcessor->processUploadedImage($_FILES['immagine'], $dir_upload);

            if ($upload_result) {
                // Se era un update e c'era un'immagine vecchia, la eliminiamo
                if ($action === 'edit' && $immagine_nome_db) {
                    $imageProcessor->deleteImage($immagine_nome_db, $dir_upload);
                }
                $immagine_nome_db = $upload_result;
            } else {
                $error = "Errore nell'upload dell'immagine: " . ($imageProcessor->getLastError() ?? 'Errore sconosciuto');
            }
        }

        // Finalizzazione dei dati per save_event
        $data['immagine'] = $immagine_nome_db;
        $data['approvato'] = isset($data['approvato']) ? 1 : 0;

        if (save_event($data, $event_id)) {
            $message = ($action === 'add') ? "Evento aggiunto con successo." : "Evento aggiornato con successo.";
        } else {
            $error = ($action === 'add') ? "Errore nell'aggiunta dell'evento. (Verifica campi obbligatori e connessione DB)" : "Errore nell'aggiornamento dell'evento. (Verifica campi obbligatori e connessione DB)";
        }

    } elseif ($action === 'delete') {
        // Elimina l'immagine associata prima di eliminare il record
        $event_to_delete = get_event_by_id($event_id);
        if ($event_to_delete && delete_event($event_id)) {
            if ($event_to_delete['immagine']) {
                $imageProcessor->deleteImage($event_to_delete['immagine'], $dir_upload);
            }
            $message = "Evento eliminato con successo.";
        } else {
            $error = "Errore nell'eliminazione dell'evento.";
        }
    } elseif ($action === 'approve') {
        if (approve_event($event_id)) {
            $message = "Evento approvato con successo.";
        } else {
            $error = "Errore nell'approvazione dell'evento.";
        }
    }
}

// Logica per visualizzare il form di modifica o aggiunta
$event_to_edit = null;
if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $event_to_edit = get_event_by_id($_GET['id']);
    if (!$event_to_edit) {
        $error = "Evento non trovato.";
    }
}

// Recupera tutti gli eventi per la lista
$events_list = get_all_events(false);

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
                <?php if ($event_to_edit): ?>
                    <p>Stai modificando l'evento: <strong><?= htmlspecialchars($event_to_edit['titolo']) ?></strong></p>
                <?php else: ?>
                    <a href="eventi.php?action=add" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Aggiungi Nuovo Evento</a>
                <?php endif; ?>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($message): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($event_to_edit || (isset($_GET['action']) && $_GET['action'] === 'add')): ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <?php $evento = $event_to_edit; ?>
                    <?php include 'forms/form_evento.php'; ?>
                </div>
            <?php else: ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4">Elenco Eventi (Totali)</h2>
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-2 font-semibold text-gray-700">ID</th>
                                <th class="text-left py-3 px-2 font-semibold text-gray-700">Titolo</th>
                                <th class="text-left py-3 px-2 font-semibold text-gray-700">Data</th>
                                <th class="text-left py-3 px-2 font-semibold text-gray-700">Città</th>
                                <th class="text-left py-3 px-2 font-semibold text-gray-700">Approvato</th>
                                <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($events_list)): ?>
                                <tr><td colspan="6" class="py-4 text-center">Nessun evento trovato.</td></tr>
                            <?php else: ?>
                                <?php foreach ($events_list as $event): ?>
                                    <tr class="border-b hover:bg-gray-50 <?= $event['approvato'] == 0 ? 'bg-yellow-50' : '' ?>">
                                        <td class="py-3 px-2"><?= $event['id'] ?></td>
                                        <td class="py-3 px-2"><?= htmlspecialchars($event['titolo']) ?></td>
                                        <td class="py-3 px-2"><?= date('d/m/Y', strtotime($event['data_evento'])) ?></td>
                                        <td class="py-3 px-2"><?= htmlspecialchars($event['nome_citta'] ?? 'N/D') ?></td>
                                        <td class="py-3 px-2">
                                            <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?= $event['approvato'] == 1 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                                <?= $event['approvato'] == 1 ? 'Sì' : 'No' ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-2">
                                            <div class="flex space-x-2">
                                                <?php if ($event['approvato'] == 0): ?>
                                                    <form method="post" action="eventi.php" style="display:inline-block;">
                                                        <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                                        <input type="hidden" name="action" value="approve">
                                                        <button type="submit" class="inline-flex items-center px-3 py-1 text-xs font-medium text-green-600 bg-green-100 rounded-lg hover:bg-green-200 transition-colors" onclick="return confirm('Sei sicuro di voler approvare questo evento?');">Approva</button>
                                                    </form>
                                                <?php endif; ?>
                                                <a href="eventi.php?action=edit&id=<?= $event['id'] ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">Modifica</a>
                                                <form method="post" action="eventi.php" style="display:inline-block;">
                                                    <input type="hidden" name="id" value="<?= $event['id'] ?>">
                                                    <input type="hidden" name="action" value="delete">
                                                    <button type="submit" class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors" onclick="return confirm('Sei sicuro di voler eliminare questo evento? TUTTE le informazioni verranno perse.');">Elimina</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>