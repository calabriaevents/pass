<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_status' && $id) {
        $status = $_POST['status'] ?? 'pending';
        $admin_notes = $_POST['admin_notes'] ?? null;
        $db->updatePlaceSuggestionStatus($id, $status, $admin_notes);
        header('Location: suggerimenti-luoghi.php');
        exit;
    }
}

if ($action === 'delete' && $id) {
    $db->deletePlaceSuggestion($id);
    header('Location: suggerimenti-luoghi.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suggerimenti Luoghi - Admin Panel</title>
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
                <h1 class="text-2xl font-bold text-gray-900">Suggerimenti Luoghi</h1>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Elenco Suggerimenti</h2>
                    <div class="flex space-x-2">
                        <a href="?status=pending" class="px-3 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-lg">Pendenti</a>
                        <a href="?status=approved" class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-lg">Approvati</a>
                        <a href="?status=rejected" class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-lg">Rifiutati</a>
                        <a href="?" class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg">Tutti</a>
                    </div>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Luogo</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Località</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Suggerito da</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Data</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Stato</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filter_status = $_GET['status'] ?? null;
                        $suggestions = $db->getPlaceSuggestions($filter_status);
                        foreach ($suggestions as $suggestion):
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <div>
                                    <div class="font-medium"><?php echo htmlspecialchars($suggestion['name']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo substr(htmlspecialchars($suggestion['description']), 0, 100); ?>...</div>
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <span class="text-sm text-gray-600"><?php echo htmlspecialchars($suggestion['address']); ?></span>
                            </td>
                            <td class="py-3 px-2">
                                <div>
                                    <div class="text-sm font-medium"><?php echo htmlspecialchars($suggestion['suggested_by_name']); ?></div>
                                    <div class="text-xs text-gray-500"><?php echo htmlspecialchars($suggestion['suggested_by_email']); ?></div>
                                </div>
                            </td>
                            <td class="py-3 px-2">
                                <span class="text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($suggestion['created_at'])); ?></span>
                            </td>
                            <td class="py-3 px-2">
                                <?php 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusColors[$suggestion['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($suggestion['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex space-x-2">
                                    <a href="suggerimenti-luoghi.php?action=view&id=<?php echo $suggestion['id']; ?>" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200 transition-colors">
                                        <i data-lucide="eye" class="w-3 h-3 mr-1"></i>
                                        Visualizza
                                    </a>
                                    <a href="suggerimenti-luoghi.php?action=delete&id=<?php echo $suggestion['id']; ?>" 
                                       class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200 transition-colors"
                                       onclick="return confirm('Sei sicuro di voler eliminare questo suggerimento?');">    
                                        <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>
                                        Elimina
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if (empty($suggestions)): ?>
                <div class="text-center py-8">
                    <i data-lucide="map-pin" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-500">Nessun suggerimento trovato.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($action === 'view' && $id): 
                $suggestion = $db->getPlaceSuggestionById($id);
                if (!$suggestion):
                    echo '<div class="text-center py-8"><p class="text-red-500">Suggerimento non trovato.</p></div>';
                else:
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-lg font-semibold">Dettagli Suggerimento</h2>
                    <a href="suggerimenti-luoghi.php" class="text-gray-600 hover:underline">← Torna all'elenco</a>
                </div>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Informazioni Luogo</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome Luogo</label>
                                <p class="text-gray-900 font-medium"><?php echo htmlspecialchars($suggestion['name']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Località</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($suggestion['address']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Descrizione</label>
                                <p class="text-gray-900"><?php echo nl2br(htmlspecialchars($suggestion['description'])); ?></p>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="font-semibold text-gray-800 mb-2">Informazioni Utente</h3>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nome</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($suggestion['suggested_by_name']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Email</label>
                                <p class="text-gray-900"><?php echo htmlspecialchars($suggestion['suggested_by_email']); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Data Suggerimento</label>
                                <p class="text-gray-900"><?php echo date('d/m/Y H:i', strtotime($suggestion['created_at'])); ?></p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Stato Attuale</label>
                                <?php 
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'approved' => 'bg-green-100 text-green-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusColors[$suggestion['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block px-3 py-1 text-sm font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($suggestion['status']); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($suggestion['images'])): ?>
                <div class="mt-6 border-t pt-6">
                    <h4 class="font-bold text-lg mb-4 text-gray-800">Immagini Suggerite:</h4>
                    <div class="flex flex-wrap gap-4">
                        <?php
                        // Decodifica la stringa JSON contenente i percorsi delle immagini
                        $image_paths = json_decode($suggestion['images'], true);

                        if (is_array($image_paths)):
                            foreach ($image_paths as $image_path):
                                // Crea un percorso root-relative per l'immagine
                                $correct_path = '/' . ltrim($image_path, '/');
                        ?>
                                <div class="w-1/3 p-1">
                                    <a href="<?php echo htmlspecialchars($correct_path); ?>" target="_blank" class="block border rounded-lg overflow-hidden shadow-sm hover:shadow-md transition-shadow">
                                        <img src="<?php echo htmlspecialchars($correct_path); ?>" alt="Immagine suggerita" class="w-full h-auto object-cover">
                                    </a>
                                </div>
                        <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($suggestion['admin_notes']): ?>
                <div class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <h4 class="font-medium text-blue-900 mb-2">Note Admin</h4>
                    <p class="text-blue-800"><?php echo nl2br(htmlspecialchars($suggestion['admin_notes'])); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mt-8 border-t pt-6">
                    <h3 class="font-semibold text-gray-800 mb-4">Gestisci Suggerimento</h3>
                    <form action="suggerimenti-luoghi.php?action=update_status&id=<?php echo $suggestion['id']; ?>" method="POST">
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Cambia Stato</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border rounded-lg">
                                    <option value="pending" <?php echo $suggestion['status'] === 'pending' ? 'selected' : ''; ?>>In Attesa</option>
                                    <option value="approved" <?php echo $suggestion['status'] === 'approved' ? 'selected' : ''; ?>>Approvato</option>
                                    <option value="rejected" <?php echo $suggestion['status'] === 'rejected' ? 'selected' : ''; ?>>Rifiutato</option>
                                </select>
                            </div>
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700 mb-2">Note Admin (opzionale)</label>
                                <textarea name="admin_notes" id="admin_notes" rows="3" class="w-full px-3 py-2 border rounded-lg" placeholder="Aggiungi note per il team..."><?php echo htmlspecialchars($suggestion['admin_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="text-right mt-4">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                                <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                                Aggiorna Suggerimento
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>