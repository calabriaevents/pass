<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

$db = new Database();

// Gestione delle azioni POST per aggiunta e modifica
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? null;
    $id = $_POST['id'] ?? null;
    $nome = $_POST['nome'] ?? '';
    $provincia = $_POST['provincia'] ?? '';
    $importo_pagato = $_POST['importo_pagato'] ?? 0;
    $data_pagamento = $_POST['data_pagamento'] ?? '';

    // Calcola la data di scadenza (1 anno dopo la data di pagamento)
    $data_scadenza = date('Y-m-d', strtotime($data_pagamento . ' +1 year'));

    if ($action === 'new') {
        // Logica per aggiungere un nuovo comune
        $sql = "INSERT INTO comuni (nome, provincia, importo_pagato, data_pagamento, data_scadenza) VALUES (:nome, :provincia, :importo_pagato, :data_pagamento, :data_scadenza)";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':provincia' => $provincia,
            ':importo_pagato' => $importo_pagato,
            ':data_pagamento' => $data_pagamento,
            ':data_scadenza' => $data_scadenza
        ]);
        // TODO: Aggiungere gestione messaggi di successo/errore
    } elseif ($action === 'edit' && $id) {
        // Logica per modificare un comune esistente
        $sql = "UPDATE comuni SET nome = :nome, provincia = :provincia, importo_pagato = :importo_pagato, data_pagamento = :data_pagamento, data_scadenza = :data_scadenza WHERE id = :id";
        $stmt = $db->pdo->prepare($sql);
        $stmt->execute([
            ':nome' => $nome,
            ':provincia' => $provincia,
            ':importo_pagato' => $importo_pagato,
            ':data_pagamento' => $data_pagamento,
            ':data_scadenza' => $data_scadenza,
            ':id' => $id
        ]);
        // TODO: Aggiungere gestione messaggi di successo/errore
    }

    header('Location: comuni.php');
    exit;
}

// Gestione dell'azione GET per eliminare
$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

if ($action === 'delete' && $id) {
    // Logica per eliminare un comune
    $sql = "DELETE FROM comuni WHERE id = :id";
    $stmt = $db->pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    // TODO: Aggiungere gestione messaggi di successo/errore

    header('Location: comuni.php');
    exit;
}


// Lettura dei dati dal database
$comuni = $db->pdo->query('SELECT * FROM comuni ORDER BY nome')->fetchAll();


function getStato($data_scadenza) {
    $oggi = new DateTime();
    $scadenza = new DateTime($data_scadenza);
    $diff = $oggi->diff($scadenza);

    if ($scadenza < $oggi) {
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-800">Scaduto</span>';
    } elseif ($diff->days <= 30) {
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">In Scadenza</span>';
    } else {
        return '<span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">Attivo</span>';
    }
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Comuni - Admin Panel</title>
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
            <a href="../index.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Torna al Sito</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gestione Comuni</h1>
                    <p class="text-sm text-gray-500">Gestisci i comuni e i loro pagamenti</p>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="forms/form_comune.php?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg flex items-center space-x-2 transition-colors">
                        <i data-lucide="plus" class="w-5 h-5"></i>
                        <span>Nuovo Comune</span>
                    </a>
                </div>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-6">
            <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Elenco Comuni</h2>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Nome</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Provincia</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Importo Pagato</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Data Pagamento</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Data Scadenza</th>
                                <th class="text-left py-3 px-6 font-semibold text-gray-700">Stato</th>
                                <th class="text-right py-3 px-6 font-semibold text-gray-700">Azioni</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php foreach ($comuni as $comune): ?>
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="py-4 px-6 font-semibold text-gray-900"><?php echo htmlspecialchars($comune['nome']); ?></td>
                                <td class="py-4 px-6"><?php echo htmlspecialchars($comune['provincia']); ?></td>
                                <td class="py-4 px-6">€ <?php echo htmlspecialchars($comune['importo_pagato']); ?></td>
                                <td class="py-4 px-6"><?php echo formatDate($comune['data_pagamento']); ?></td>
                                <td class="py-4 px-6"><?php echo formatDate($comune['data_scadenza']); ?></td>
                                <td class="py-4 px-6"><?php echo getStato($comune['data_scadenza']); ?></td>
                                <td class="py-4 px-6 text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="forms/form_comune.php?action=edit&id=<?php echo $comune['id']; ?>" class="text-blue-600 hover:text-blue-700 font-medium text-sm">Modifica</a>
                                        <a href="comuni.php?action=delete&id=<?php echo $comune['id']; ?>" class="text-red-600 hover:text-red-700 font-medium text-sm" onclick="return confirm('Sei sicuro di voler eliminare questo comune?');">Elimina</a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
