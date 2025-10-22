<?php
require_once __DIR__ . '/auth_check.php';
require_once '../includes/config.php';

// Percorsi dei file di configurazione e flag
$flag_file = dirname(__DIR__) . '/maintenance.flag';
$config_file = dirname(__DIR__) . '/maintenance_config.json';

// Valori di default
$config = [
    'enabled' => false,
    'message' => 'Sito in manutenzione. Torneremo presto!',
    'end_time' => ''
];

// Carica la configurazione esistente se il file esiste
if (file_exists($config_file)) {
    $config = json_decode(file_get_contents($config_file), true);
}
// Sincronizza lo stato 'enabled' con l'esistenza del file flag
$config['enabled'] = file_exists($flag_file);


// Gestisci azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_maintenance') {
        $is_enabled = isset($_POST['maintenance_enabled']);
        $end_time_str = trim($_POST['end_date'] ?? '') . ' ' . trim($_POST['end_time'] ?? '');
        $end_timestamp = !empty(trim($end_time_str)) ? strtotime($end_time_str) : null;

        $new_config = [
            'enabled' => $is_enabled,
            'message' => trim($_POST['maintenance_message'] ?? $config['message']),
            'end_time' => $end_timestamp ? date('Y-m-d\TH:i:s', $end_timestamp) : null
        ];

        // Scrivi il file di configurazione JSON
        file_put_contents($config_file, json_encode($new_config, JSON_PRETTY_PRINT));

        // Crea o elimina il file flag
        if ($is_enabled) {
            touch($flag_file);
        } else {
            if (file_exists($flag_file)) {
                unlink($flag_file);
            }
        }
        
        header('Location: manutenzione.php?success=true');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modalità Manutenzione - Admin Panel</title>
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
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">🔧 Modalità Manutenzione</h1>
                    <p class="text-gray-600 mt-1">Gestisci la modalità manutenzione del sito</p>
                </div>
                 <div class="flex items-center space-x-2">
                    <span class="text-sm text-gray-600">Stato:</span>
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $config['enabled'] ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                        <?php echo $config['enabled'] ? 'ATTIVATA' : 'DISATTIVATA'; ?>
                    </span>
                </div>
            </div>
        </header>
        
        <main class="flex-1 overflow-auto p-6">
            <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                <p class="font-medium">Impostazioni manutenzione aggiornate con successo!</p>
            </div>
            <?php endif; ?>

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-6">
                    <form method="post" class="space-y-6">
                        <input type="hidden" name="action" value="toggle_maintenance">
                        
                        <div class="flex items-center">
                            <input type="checkbox" id="maintenance_enabled" name="maintenance_enabled" class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500" <?php echo $config['enabled'] ? 'checked' : ''; ?>>
                            <label for="maintenance_enabled" class="ml-2 font-medium text-gray-900">Attiva modalità manutenzione</label>
                        </div>

                        <div>
                            <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-2">Messaggio di Manutenzione</label>
                            <textarea id="maintenance_message" name="maintenance_message" rows="3" class="w-full p-2 border rounded-lg"><?php echo htmlspecialchars($config['message']); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fine Manutenzione (per il timer)</label>
                            <div class="flex items-center space-x-2">
                                <input type="date" name="end_date" class="p-2 border rounded-lg" value="<?php echo $config['end_time'] ? date('Y-m-d', strtotime($config['end_time'])) : ''; ?>">
                                <input type="time" name="end_time" class="p-2 border rounded-lg" value="<?php echo $config['end_time'] ? date('H:i', strtotime($config['end_time'])) : ''; ?>">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Imposta una data e ora per mostrare un conto alla rovescia sulla pagina di manutenzione. Lascia vuoto per non mostrarlo.</p>
                        </div>
                        
                        <div class="flex justify-end pt-4 border-t">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-medium">
                                Salva Impostazioni
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
