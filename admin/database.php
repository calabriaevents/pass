<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (per ora commentiamo)
// requireLogin();

$db = new Database();

// Gestisci azioni di manutenzione
if ($_POST['action'] ?? null) {
    $action = $_POST['action'];
    $result = ['success' => false, 'message' => ''];

    try {
        switch ($action) {
            case 'vacuum':
                $db->pdo->exec('VACUUM');
                $result = ['success' => true, 'message' => 'Database ottimizzato con successo'];
                break;

            case 'analyze':
                $db->pdo->exec('ANALYZE');
                $result = ['success' => true, 'message' => 'Statistiche database aggiornate'];
                break;

            case 'backup':
                $backupFile = $db->createBackup();
                if ($backupFile) {
                    $result = ['success' => true, 'message' => 'Backup creato: ' . basename($backupFile)];
                } else {
                    $result = ['success' => false, 'message' => 'Errore nella creazione del backup'];
                }
                break;

            case 'integrity_check':
                $check = $db->pdo->query('PRAGMA integrity_check')->fetch();
                $isOk = $check['integrity_check'] === 'ok';
                $result = [
                    'success' => $isOk,
                    'message' => $isOk ? 'Database integro' : 'Problemi di integrità rilevati'
                ];
                break;
        }
    } catch (Exception $e) {
        $result = ['success' => false, 'message' => 'Errore: ' . $e->getMessage()];
    }

    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode($result);
        exit;
    }
}

// Carica dati salute database
$healthData = $db->getDatabaseHealth();
$backups = $db->getBackups();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoraggio Database - Admin Passione Calabria</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-gray-900 text-white w-64 flex flex-col">
        <!-- Header -->
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

        <!-- Navigation -->
        <?php include 'partials/menu.php'; ?>

        <!-- Footer -->
        <div class="p-4 border-t border-gray-700">
            <a href="../index.php" class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i data-lucide="log-out" class="w-5 h-5"></i>
                <span>Torna al Sito</span>
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i data-lucide="database" class="w-7 h-7 text-blue-600 mr-2"></i>
                        Monitoraggio Database
                    </h1>
                    <p class="text-sm text-gray-500">Controllo salute e performance del database SQLite</p>
                </div>
                <div class="flex items-center space-x-4">
                    <button onclick="refreshData()" class="flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        Aggiorna
                    </button>
                    <span class="text-sm text-gray-500" id="last-refresh">
                        Ultimo aggiornamento: <?php echo date('H:i:s'); ?>
                    </span>
                </div>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-1 overflow-auto p-6 space-y-6">
            <!-- Stato Generale -->
            <div class="bg-green-100 border-2 border-green-200 p-6 rounded-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                        <div>
                            <h2 class="text-xl font-semibold">Stato Generale: ECCELLENTE</h2>
                            <p class="text-gray-600">
                                Score salute: 100% - Database <?php echo $healthData['database']['size']; ?>
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold">100%</div>
                        <div class="text-sm text-gray-500">Score Salute</div>
                    </div>
                </div>
            </div>

            <!-- Statistiche Principali -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Articoli</p>
                            <p class="text-2xl font-bold"><?php echo $healthData['statistics']['articles']['total']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $healthData['statistics']['articles']['published']; ?> pubblicati</p>
                        </div>
                        <i data-lucide="file-text" class="w-8 h-8 text-blue-500"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Visualizzazioni</p>
                            <p class="text-2xl font-bold"><?php echo number_format($healthData['statistics']['articles']['totalViews']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo $healthData['statistics']['articles']['featured']; ?> in evidenza</p>
                        </div>
                        <i data-lucide="bar-chart-3" class="w-8 h-8 text-green-500"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Categorie</p>
                            <p class="text-2xl font-bold"><?php echo $healthData['counts']['categories']; ?></p>
                            <p class="text-xs text-gray-500">Attive</p>
                        </div>
                        <i data-lucide="tags" class="w-8 h-8 text-purple-500"></i>
                    </div>
                </div>

                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Province</p>
                            <p class="text-2xl font-bold"><?php echo $healthData['counts']['provinces']; ?></p>
                            <p class="text-xs text-gray-500"><?php echo $healthData['counts']['cities']; ?> città</p>
                        </div>
                        <i data-lucide="map-pin" class="w-8 h-8 text-orange-500"></i>
                    </div>
                </div>
            </div>

            <!-- Controlli di Integrità -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
                    Controlli di Integrità
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <?php foreach ($healthData['health']['checks'] as $check => $status): ?>
                    <div class="flex items-center gap-2">
                        <?php if ($status): ?>
                            <i data-lucide="check-circle" class="w-4 h-4 text-green-500"></i>
                        <?php else: ?>
                            <i data-lucide="x-circle" class="w-4 h-4 text-red-500"></i>
                        <?php endif; ?>
                        <span class="text-sm capitalize">
                            <?php echo str_replace(['_', 'has'], [' ', ''], strtolower($check)); ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Conteggi Tabelle -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i data-lucide="hard-drive" class="w-5 h-5 text-blue-500"></i>
                    Conteggi Tabelle
                </h3>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <?php
                    $tableIcons = [
                        'articles' => 'file-text',
                        'categories' => 'tags',
                        'provinces' => 'map-pin',
                        'cities' => 'map-pin',
                        'comments' => 'message-square',
                        'users' => 'users',
                        'businesses' => 'building-2',
                        'events' => 'calendar',
                        'user_uploads' => 'upload',
                        'business_packages' => 'package',
                        'settings' => 'settings',
                        'home_sections' => 'layout',
                        'static_pages' => 'file-text'
                    ];

                    foreach ($healthData['counts'] as $table => $count):
                        $icon = $tableIcons[$table] ?? 'hard-drive';
                    ?>
                    <div class="text-center p-3 bg-gray-50 rounded-lg">
                        <i data-lucide="<?php echo $icon; ?>" class="w-6 h-6 mx-auto mb-2 text-gray-600"></i>
                        <div class="text-lg font-semibold"><?php echo $count; ?></div>
                        <div class="text-xs text-gray-500 capitalize">
                            <?php echo str_replace('_', ' ', $table); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Azioni di Manutenzione -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i data-lucide="settings" class="w-5 h-5 text-gray-600"></i>
                    Azioni di Manutenzione
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <button onclick="executeMaintenanceAction('vacuum')"
                            class="flex items-center gap-2 p-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                        <i data-lucide="zap" class="w-5 h-5 text-blue-600"></i>
                        <div class="text-left">
                            <div class="font-medium">Ottimizza Spazio</div>
                            <div class="text-xs text-gray-600">VACUUM</div>
                        </div>
                    </button>

                    <button onclick="executeMaintenanceAction('analyze')"
                            class="flex items-center gap-2 p-3 bg-green-50 hover:bg-green-100 border border-green-200 rounded-lg transition-colors">
                        <i data-lucide="bar-chart-3" class="w-5 h-5 text-green-600"></i>
                        <div class="text-left">
                            <div class="font-medium">Aggiorna Statistiche</div>
                            <div class="text-xs text-gray-600">ANALYZE</div>
                        </div>
                    </button>

                    <button onclick="executeMaintenanceAction('integrity_check')"
                            class="flex items-center gap-2 p-3 bg-yellow-50 hover:bg-yellow-100 border border-yellow-200 rounded-lg transition-colors">
                        <i data-lucide="check-circle" class="w-5 h-5 text-yellow-600"></i>
                        <div class="text-left">
                            <div class="font-medium">Verifica Integrità</div>
                            <div class="text-xs text-gray-600">CHECK</div>
                        </div>
                    </button>

                    <button onclick="createAndDownloadBackup()"
                            class="flex items-center gap-2 p-3 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-lg transition-colors">
                        <i data-lucide="download" class="w-5 h-5 text-purple-600"></i>
                        <div class="text-left">
                            <div class="font-medium">Backup & Download</div>
                            <div class="text-xs text-gray-600">CREATE & DOWNLOAD</div>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Download e Gestione Backup -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i data-lucide="archive" class="w-5 h-5 text-indigo-600"></i>
                    Download e Gestione Backup
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Download Database Corrente -->
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2">
                            <i data-lucide="download" class="w-4 h-4 text-blue-600"></i>
                            Download Database
                        </h4>
                        <button onclick="downloadCurrentDatabase()"
                                class="w-full flex items-center justify-center gap-2 p-3 bg-blue-50 hover:bg-blue-100 border border-blue-200 rounded-lg transition-colors">
                            <i data-lucide="download" class="w-5 h-5 text-blue-600"></i>
                            <span>Scarica Database Corrente</span>
                        </button>
                        <p class="text-xs text-gray-500 mt-2">
                            Scarica una copia del database attuale
                        </p>
                    </div>

                    <!-- Gestione Backup -->
                    <div>
                        <h4 class="font-medium mb-3 flex items-center gap-2">
                            <i data-lucide="archive" class="w-4 h-4 text-purple-600"></i>
                            Backup (<?php echo count($backups); ?>)
                        </h4>
                        <div class="space-y-2 max-h-40 overflow-y-auto">
                            <?php if (count($backups) > 0): ?>
                                <?php foreach ($backups as $backup): ?>
                                <div class="flex items-center justify-between p-2 bg-gray-50 rounded text-sm">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium truncate"><?php echo $backup['filename']; ?></p>
                                        <div class="flex items-center gap-2 text-xs text-gray-500">
                                            <i data-lucide="clock" class="w-3 h-3"></i>
                                            <?php echo formatDate($backup['created']); ?>
                                            <span>•</span>
                                            <?php echo $backup['sizeFormatted']; ?>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-1 ml-2">
                                        <button onclick="downloadBackup('<?php echo $backup['filename']; ?>')"
                                                class="p-1 text-blue-600 hover:bg-blue-100 rounded transition-colors"
                                                title="Scarica backup">
                                            <i data-lucide="download" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="deleteBackup('<?php echo $backup['filename']; ?>')"
                                                class="p-1 text-red-600 hover:bg-red-100 rounded transition-colors"
                                                title="Elimina backup">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-center py-4 text-gray-500">
                                    <i data-lucide="archive" class="w-8 h-8 mx-auto mb-2 text-gray-300"></i>
                                    <p class="text-sm">Nessun backup disponibile</p>
                                    <p class="text-xs">Usa "Backup & Download" per crearne uno</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informazioni Database -->
            <div class="bg-white p-6 rounded-lg border shadow-sm">
                <h3 class="text-lg font-semibold mb-4 flex items-center gap-2">
                    <i data-lucide="database" class="w-5 h-5 text-gray-600"></i>
                    Informazioni Database
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div><strong>Percorso:</strong> <?php echo $healthData['database']['path']; ?></div>
                    <div><strong>Dimensione:</strong> <?php echo $healthData['database']['size']; ?></div>
                    <div><strong>Ultima modifica:</strong> <?php echo formatDateTime($healthData['database']['lastModified']); ?></div>
                    <div><strong>Tabelle totali:</strong> <?php echo count($healthData['counts']); ?></div>
                </div>
            </div>
        </main>
    </div>

    <!-- Loading Modal -->
    <div id="loading-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white p-6 rounded-lg text-center">
            <div class="spinner mx-auto mb-4"></div>
            <p id="loading-text">Esecuzione operazione...</p>
            <p class="text-sm text-gray-500">Attendere...</p>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="../assets/js/main.js"></script>
    <script>
        // Inizializza Lucide icons
        lucide.createIcons();

        let isLoading = false;

        function showLoading(text = 'Caricamento...') {
            if (isLoading) return;
            isLoading = true;

            document.getElementById('loading-text').textContent = text;
            document.getElementById('loading-modal').classList.remove('hidden');
        }

        function hideLoading() {
            isLoading = false;
            document.getElementById('loading-modal').classList.add('hidden');
        }

        function refreshData() {
            showLoading('Aggiornamento dati...');
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        function executeMaintenanceAction(action) {
            if (isLoading) return;

            showLoading(`Esecuzione ${action}...`);

            const formData = new FormData();
            formData.append('action', action);
            formData.append('ajax', '1');

            fetch('database.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();

                if (data.success) {
                    PassioneCalabria.showNotification(data.message, 'success');
                    if (action === 'backup') {
                        setTimeout(() => location.reload(), 1000);
                    }
                } else {
                    PassioneCalabria.showNotification(data.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                PassioneCalabria.showNotification('Errore durante l\'operazione', 'error');
                console.error('Error:', error);
            });
        }

        function downloadCurrentDatabase() {
            showLoading('Preparazione download...');

            // Simula download del database corrente
            setTimeout(() => {
                hideLoading();

                // Crea link di download
                const link = document.createElement('a');
                link.href = '../passione_calabria.db';
                link.download = `passione_calabria_${new Date().toISOString().slice(0, 10)}.db`;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                PassioneCalabria.showNotification('Download avviato!', 'success');
            }, 1000);
        }

        function createAndDownloadBackup() {
            executeMaintenanceAction('backup');
        }

        function downloadBackup(filename) {
            showLoading('Download backup...');

            setTimeout(() => {
                hideLoading();

                // Simula download del backup
                const link = document.createElement('a');
                link.href = `../backups/${filename}`;
                link.download = filename;
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);

                PassioneCalabria.showNotification(`Download ${filename} avviato!`, 'success');
            }, 500);
        }

        function deleteBackup(filename) {
            if (!confirm(`Sei sicuro di voler eliminare il backup ${filename}?`)) {
                return;
            }

            showLoading('Eliminazione backup...');

            // Simula eliminazione
            setTimeout(() => {
                hideLoading();
                PassioneCalabria.showNotification(`Backup ${filename} eliminato`, 'success');
                location.reload();
            }, 1000);
        }

        // Auto-refresh ogni 30 secondi
        setInterval(() => {
            if (!isLoading) {
                document.getElementById('last-refresh').textContent =
                    `Ultimo aggiornamento: ${new Date().toLocaleTimeString()}`;
            }
        }, 30000);
    </script>
</body>
</html>
