<?php
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database_mysql.php';
require_once __DIR__ . '/../auth_check.php';

$db = new Database();

$action = $_GET['action'] ?? 'new';
$id = $_GET['id'] ?? null;
$comune = null;

if ($id) {
    $stmt = $db->pdo->prepare('SELECT * FROM comuni WHERE id = ?');
    $stmt->execute([$id]);
    $comune = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $action === 'edit' ? 'Modifica' : 'Nuovo'; ?> Comune - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../assets/css/style.css">
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
        <?php include __DIR__ . '/../partials/menu.php'; ?>
        <div class="p-4 border-t border-gray-700">
            <a href="../../index.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Torna al Sito</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900"><?php echo $action === 'edit' ? 'Modifica' : 'Nuovo'; ?> Comune</h1>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <div class="bg-white p-6 rounded-lg shadow-sm">
                <form action="../comuni.php" method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    <?php if ($id): ?>
                        <input type="hidden" name="id" value="<?php echo $id; ?>">
                    <?php endif; ?>

                    <div class="mb-4">
                        <label for="nome" class="block text-sm font-semibold text-gray-700">Nome Comune</label>
                        <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($comune['nome'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label for="provincia" class="block text-sm font-semibold text-gray-700">Provincia</label>
                        <input type="text" name="provincia" id="provincia" value="<?php echo htmlspecialchars($comune['provincia'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label for="importo_pagato" class="block text-sm font-semibold text-gray-700">Importo Pagato</label>
                        <input type="number" step="0.01" name="importo_pagato" id="importo_pagato" value="<?php echo htmlspecialchars($comune['importo_pagato'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="mb-4">
                        <label for="data_pagamento" class="block text-sm font-semibold text-gray-700">Data Pagamento</label>
                        <input type="date" name="data_pagamento" id="data_pagamento" value="<?php echo htmlspecialchars($comune['data_pagamento'] ?? ''); ?>" class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500" required>
                    </div>

                    <div class="flex justify-end">
                        <a href="../comuni.php" class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-lg mr-2">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg"><?php echo $action === 'edit' ? 'Aggiorna' : 'Salva'; ?></button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>
