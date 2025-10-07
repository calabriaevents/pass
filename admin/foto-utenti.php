<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null;
$type = $_GET['type'] ?? null; // 'article', 'city', or null for all

// Gestione delle azioni
if ($action === 'approve' && $id) {
    $db->updateUserUploadStatus($id, 'approved');
    header('Location: foto-utenti.php' . ($status ? '?status=' . $status : ''));
    exit;
}

if ($action === 'reject' && $id) {
    $admin_notes = $_POST['admin_notes'] ?? 'Foto rifiutata';
    $db->updateUserUploadStatus($id, 'rejected', $admin_notes);
    header('Location: foto-utenti.php' . ($status ? '?status=' . $status : ''));
    exit;
}

if ($action === 'delete' && $id) {
    // Recupera info foto per eliminare file
    $upload = $db->getUserUploadById($id);
    if ($upload && file_exists('../' . $upload['image_path'])) {
        unlink('../' . $upload['image_path']);
    }
    $db->deleteUserUpload($id);
    header('Location: foto-utenti.php' . ($status ? '?status=' . $status : ''));
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Foto Utenti - Admin Panel</title>
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
            <h1 class="text-2xl font-bold text-gray-900">Gestione Foto Utenti</h1>
            <p class="text-sm text-gray-500">Modera le foto caricate dagli utenti</p>
        </header>
        
        <main class="flex-1 overflow-auto p-6">
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Foto Utenti</h2>
                
                <!-- Filtri -->
                <div class="mb-4 border-b border-gray-200">
                    <nav class="flex space-x-4">
                        <a href="?status=" class="py-2 px-4 <?php if (!$status && !$type) echo 'border-b-2 border-blue-600 font-semibold'; ?>">Tutte</a>
                        <a href="?status=pending" class="py-2 px-4 <?php if ($status === 'pending') echo 'border-b-2 border-blue-600 font-semibold'; ?>">In attesa</a>
                        <a href="?status=approved" class="py-2 px-4 <?php if ($status === 'approved') echo 'border-b-2 border-blue-600 font-semibold'; ?>">Approvate</a>
                        <a href="?status=rejected" class="py-2 px-4 <?php if ($status === 'rejected') echo 'border-b-2 border-blue-600 font-semibold'; ?>">Rifiutate</a>
                    </nav>
                </div>
                
                <div class="mb-4 border-b border-gray-200">
                    <nav class="flex space-x-4">
                        <a href="?type=" class="py-2 px-4 <?php if (!$type) echo 'border-b-2 border-green-600 font-semibold text-green-600'; ?>">Tutti i Tipi</a>
                        <a href="?type=article" class="py-2 px-4 <?php if ($type === 'article') echo 'border-b-2 border-green-600 font-semibold text-green-600'; ?>">Su Articoli</a>
                        <a href="?type=city" class="py-2 px-4 <?php if ($type === 'city') echo 'border-b-2 border-green-600 font-semibold text-green-600'; ?>">Su Città</a>
                    </nav>
                </div>
                
                <!-- Griglia Foto -->
                <?php
                $uploads = $db->getUserUploads($status, $type);
                if (!empty($uploads)):
                ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    <?php foreach ($uploads as $upload): ?>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                        <!-- Immagine -->
                        <div class="aspect-square bg-gray-100 relative">
                            <img src="../<?php echo htmlspecialchars($upload['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($upload['description'] ?: 'Foto utente'); ?>"
                                 class="w-full h-full object-cover">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 right-2">
                                <?php
                                $statusColors = [
                                    'approved' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusColors[$upload['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($upload['status']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <!-- Info -->
                        <div class="p-4">
                            <div class="mb-2">
                                <?php if ($upload['city_name']): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-2">
                                        <i data-lucide="map-pin" class="w-3 h-3 mr-1"></i>
                                        <?php echo htmlspecialchars($upload['city_name']); ?>
                                    </span>
                                <?php elseif ($upload['article_title']): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-2">
                                        <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                        Articolo
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="text-sm text-gray-600 mb-2">
                                <strong><?php echo htmlspecialchars($upload['user_name']); ?></strong><br>
                                <span class="text-xs"><?php echo htmlspecialchars($upload['user_email']); ?></span>
                            </div>
                            
                            <?php if ($upload['description']): ?>
                            <p class="text-sm text-gray-700 mb-3"><?php echo htmlspecialchars($upload['description']); ?></p>
                            <?php endif; ?>
                            
                            <div class="text-xs text-gray-500 mb-3">
                                Caricata il <?php echo date('d/m/Y H:i', strtotime($upload['created_at'])); ?>
                            </div>
                            
                            <!-- Azioni -->
                            <div class="flex space-x-2">
                                <?php if ($upload['status'] === 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $upload['id']; ?>&status=<?php echo $status; ?>" 
                                   class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded-md text-xs font-medium transition-colors">
                                    <i data-lucide="check" class="w-3 h-3 inline mr-1"></i>
                                    Approva
                                </a>
                                <button onclick="openRejectModal(<?php echo $upload['id']; ?>)" 
                                        class="flex-1 bg-orange-600 hover:bg-orange-700 text-white text-center py-2 px-3 rounded-md text-xs font-medium transition-colors">
                                    <i data-lucide="x" class="w-3 h-3 inline mr-1"></i>
                                    Rifiuta
                                </button>
                                <?php endif; ?>
                                
                                <button onclick="confirmDelete(<?php echo $upload['id']; ?>)" 
                                        class="bg-red-600 hover:bg-red-700 text-white py-2 px-3 rounded-md text-xs font-medium transition-colors">
                                    <i data-lucide="trash-2" class="w-3 h-3"></i>
                                </button>
                            </div>
                            
                            <?php if ($upload['status'] === 'rejected' && $upload['admin_notes']): ?>
                            <div class="mt-3 p-2 bg-red-50 border border-red-200 rounded text-xs">
                                <strong>Note admin:</strong> <?php echo htmlspecialchars($upload['admin_notes']); ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php else: ?>
                <div class="text-center py-12">
                    <i data-lucide="image" class="w-16 h-16 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700 mb-2">Nessuna foto trovata</h3>
                    <p class="text-gray-500">Non ci sono foto utenti da moderare al momento</p>
                </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Modal Rifiuto -->
    <div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Rifiuta Foto</h3>
                    <form id="rejectForm" method="POST">
                        <input type="hidden" name="action" value="reject">
                        <input type="hidden" id="rejectId" name="id" value="">
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo del rifiuto</label>
                            <textarea name="admin_notes" rows="3" required
                                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                      placeholder="Specifica il motivo del rifiuto..."></textarea>
                        </div>
                        
                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeRejectModal()" 
                                    class="px-4 py-2 text-gray-600 hover:text-gray-800">
                                Annulla
                            </button>
                            <button type="submit" 
                                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                                Rifiuta Foto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openRejectModal(id) {
            document.getElementById('rejectId').value = id;
            document.getElementById('rejectModal').classList.remove('hidden');
        }

        function closeRejectModal() {
            document.getElementById('rejectModal').classList.add('hidden');
        }

        function confirmDelete(id) {
            if (confirm('Sei sicuro di voler eliminare questa foto? Questa azione non può essere annullata.')) {
                window.location.href = '?action=delete&id=' + id + '&status=<?php echo $status; ?>';
            }
        }

        // Chiudi modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeRejectModal();
            }
        });
    </script>
</body>
</html>