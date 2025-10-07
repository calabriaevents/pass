<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();

// Gestisci azioni
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_maintenance') {
        $maintenance_enabled = isset($_POST['maintenance_enabled']) ? '1' : '0';
        $maintenance_message = trim($_POST['maintenance_message'] ?? '');
        
        // Aggiorna impostazioni manutenzione
        $db->setSetting('maintenance_enabled', $maintenance_enabled);
        $db->setSetting('maintenance_message', $maintenance_message);
        
        header('Location: manutenzione.php?success=true');
        exit;
    }
}

// Ottieni impostazioni attuali
$maintenance_enabled = $db->getSetting('maintenance_enabled') == '1';
$maintenance_message = $db->getSetting('maintenance_message') ?: 'Sito in manutenzione. Torneremo presto!';

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
                    <span class="px-3 py-1 rounded-full text-sm font-medium <?php echo $maintenance_enabled ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?>">
                        <?php echo $maintenance_enabled ? 'ATTIVATA' : 'DISATTIVATA'; ?>
                    </span>
                </div>
            </div>
        </header>
        
        <main class="flex-1 overflow-auto p-6">
            <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 text-green-700 p-4 rounded-lg shadow-sm" role="alert">
                <div class="flex items-center">
                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                    <p class="font-medium">Impostazioni manutenzione aggiornate con successo!</p>
                </div>
            </div>
            <?php endif; ?>

            <!-- Stato Attuale -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                            <i data-lucide="activity" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Stato Attuale</h2>
                            <p class="text-sm text-gray-600 mt-1">Situazione corrente del sistema</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-3">
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Modalità Manutenzione</span>
                                <span class="px-2 py-1 rounded text-xs font-medium <?php echo $maintenance_enabled ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                    <?php echo $maintenance_enabled ? 'ATTIVA' : 'INATTIVA'; ?>
                                </span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Area Admin</span>
                                <span class="px-2 py-1 rounded text-xs font-medium bg-green-100 text-green-700">SEMPRE ATTIVA</span>
                            </div>
                            
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Sito Utenti</span>
                                <span class="px-2 py-1 rounded text-xs font-medium <?php echo $maintenance_enabled ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'; ?>">
                                    <?php echo $maintenance_enabled ? 'IN MANUTENZIONE' : 'ATTIVO'; ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="space-y-3">
                            <h3 class="text-sm font-medium text-gray-700">Anteprima Messaggio</h3>
                            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                                <div class="flex items-center">
                                    <i data-lucide="wrench" class="w-5 h-5 text-yellow-600 mr-2"></i>
                                    <span class="text-yellow-800 text-sm"><?php echo htmlspecialchars($maintenance_message); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Gestione Manutenzione -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center shadow-sm">
                            <i data-lucide="settings" class="w-5 h-5 text-blue-600"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Gestione Manutenzione</h2>
                            <p class="text-sm text-gray-600 mt-1">Attiva o disattiva la modalità manutenzione</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <form method="post" class="space-y-6">
                        <input type="hidden" name="action" value="toggle_maintenance">
                        
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <input type="checkbox" id="maintenance_enabled" name="maintenance_enabled" 
                                       class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2"
                                       <?php echo $maintenance_enabled ? 'checked' : ''; ?>>
                                <label for="maintenance_enabled" class="ml-2 text-sm font-medium text-gray-900">
                                    Attiva modalità manutenzione per il sito utenti
                                </label>
                            </div>
                            
                            <div>
                                <label for="maintenance_message" class="block text-sm font-medium text-gray-700 mb-2">
                                    Messaggio di Manutenzione
                                </label>
                                <textarea id="maintenance_message" name="maintenance_message" rows="3" 
                                          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                                          placeholder="Inserisci il messaggio da mostrare agli utenti durante la manutenzione"><?php echo htmlspecialchars($maintenance_message); ?></textarea>
                                <p class="mt-1 text-xs text-gray-500">
                                    Questo messaggio verrà mostrato agli utenti quando il sito è in manutenzione. L'area admin rimane sempre accessibile.
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                            <div class="text-sm text-gray-600">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                L'area admin rimane sempre accessibile anche durante la manutenzione
                            </div>
                            
                            <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white px-6 py-2 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl font-medium">
                                <i data-lucide="save" class="w-4 h-4 inline mr-2"></i>
                                Aggiorna Impostazioni
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Istruzioni -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-blue-900 mb-3">
                    <i data-lucide="help-circle" class="w-5 h-5 inline mr-2"></i>
                    Come Funziona
                </h3>
                <ul class="space-y-2 text-sm text-blue-800">
                    <li class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <span>Quando attivata, la modalità manutenzione mostrerà il messaggio personalizzato a tutti i visitatori del sito</span>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <span>L'area admin rimane sempre accessibile per continuare a gestire il sito</span>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <span>Gli utenti business non potranno accedere alle loro dashboard durante la manutenzione</span>
                    </li>
                    <li class="flex items-start">
                        <i data-lucide="check" class="w-4 h-4 mr-2 mt-0.5 text-blue-600 flex-shrink-0"></i>
                        <span>Disattiva la manutenzione per ripristinare il normale funzionamento del sito</span>
                    </li>
                </ul>
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>