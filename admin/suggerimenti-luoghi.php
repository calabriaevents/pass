<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Includi il nuovo processore di immagini

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();
$imageProcessor = new ImageProcessor(); // Istanzia il processore

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_status' && $id) {
        $status = $_POST['status'] ?? 'pending';
        $admin_notes = $_POST['admin_notes'] ?? null;
        if ($db->updatePlaceSuggestionStatus($id, $status, $admin_notes)) {
            header('Location: suggerimenti-luoghi.php?success=Status aggiornato');
        } else {
            header('Location: suggerimenti-luoghi.php?error=Errore aggiornamento');
        }
        exit;
    }
}

if ($action === 'delete' && $id) {
    // FIX: Elimina le immagini associate prima di eliminare il record
    $suggestion = $db->getPlaceSuggestionById($id);
    if ($suggestion && !empty($suggestion['images'])) {
        $image_paths = json_decode($suggestion['images'], true);
        if (is_array($image_paths)) {
            foreach ($image_paths as $image_path) {
                // L'ImageProcessor si aspetta un percorso relativo dalla cartella 'admin'
                // ma i percorsi salvati sono relativi alla root. Dobbiamo adattarlo.
                $imageProcessor->deleteImage($image_path);
            }
        }
    }

    if ($db->deletePlaceSuggestion($id)) {
        header('Location: suggerimenti-luoghi.php?success=Suggerimento eliminato con successo');
    } else {
        header('Location: suggerimenti-luoghi.php?error=Errore durante l\'eliminazione');
    }
    exit;
}

// Get messages from URL
$success_message = isset($_GET['success']) ? htmlspecialchars($_GET['success']) : null;
$error_message = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : null;
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
    <script src="../assets/js/main.js" defer></script>
</head>
<body class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-gray-900 text-white w-64 flex flex-col">
        <div class="p-4 border-b border-gray-700">
             <h1 class="font-bold text-lg">Admin Panel</h1>
        </div>
        <?php include 'partials/menu.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900">Suggerimenti Luoghi</h1>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($success_message): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 success-alert">
                <p><?php echo $success_message; ?></p>
            </div>
            <?php endif; ?>
            <?php if ($error_message): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 error-alert">
                 <p><?php echo $error_message; ?></p>
            </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <!-- ... (table listing code remains the same) ... -->
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Luogo</th>
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
                            <td class="py-3 px-2 font-medium"><?php echo htmlspecialchars($suggestion['name']); ?></td>
                            <td class="py-3 px-2 text-sm text-gray-600"><?php echo htmlspecialchars($suggestion['suggested_by_name']); ?></td>
                            <td class="py-3 px-2 text-sm text-gray-600"><?php echo date('d/m/Y', strtotime($suggestion['created_at'])); ?></td>
                            <td class="py-3 px-2">
                                <?php 
                                $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'approved' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800'];
                                ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$suggestion['status']] ?? 'bg-gray-100'; ?>">
                                    <?php echo ucfirst($suggestion['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <a href="?action=view&id=<?php echo $suggestion['id']; ?>" class="text-blue-600 hover:underline">Visualizza</a>
                                <a href="?action=delete&id=<?php echo $suggestion['id']; ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Sei sicuro? Questa azione eliminerà anche tutte le immagini associate.');">Elimina</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($action === 'view' && $id): 
                $suggestion = $db->getPlaceSuggestionById($id);
                if (!$suggestion) { echo '<p>Suggerimento non trovato.</p>'; } else {
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
                <a href="suggerimenti-luoghi.php" class="text-blue-600 mb-4 inline-block">&larr; Torna all'elenco</a>
                <h2 class="text-xl font-bold mb-4"><?php echo htmlspecialchars($suggestion['name']); ?></h2>
                
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- ... (dettagli suggerimento) ... -->
                </div>
                
                <?php if (!empty($suggestion['images'])):
                    $image_paths = json_decode($suggestion['images'], true);
                ?>
                <div class="mt-6 border-t pt-6">
                    <h4 class="font-bold text-lg mb-4">Immagini Suggerite:</h4>
                    <div class="flex flex-wrap gap-4">
                        <?php if (is_array($image_paths)): foreach ($image_paths as $image_path): ?>
                            <a href="/<?php echo ltrim($image_path, '/'); ?>" target="_blank">
                                <img src="/<?php echo ltrim($image_path, '/'); ?>" class="w-32 h-32 object-cover rounded-lg shadow-md">
                            </a>
                        <?php endforeach; endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-8 border-t pt-6">
                    <h3 class="font-semibold text-lg mb-4">Gestisci Suggerimento</h3>
                    <form action="?action=update_status&id=<?php echo $suggestion['id']; ?>" method="POST">
                        <div class="space-y-4">
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Cambia Stato</label>
                                <select name="status" id="status" class="w-full mt-1 border-gray-300 rounded-md shadow-sm">
                                    <option value="pending" <?php echo $suggestion['status'] === 'pending' ? 'selected' : ''; ?>>In Attesa</option>
                                    <option value="approved" <?php echo $suggestion['status'] === 'approved' ? 'selected' : ''; ?>>Approvato</option>
                                    <option value="rejected" <?php echo $suggestion['status'] === 'rejected' ? 'selected' : ''; ?>>Rifiutato</option>
                                </select>
                            </div>
                            <div>
                                <label for="admin_notes" class="block text-sm font-medium text-gray-700">Note Admin</label>
                                <textarea name="admin_notes" id="admin_notes" rows="3" class="w-full mt-1 border-gray-300 rounded-md shadow-sm"><?php echo htmlspecialchars($suggestion['admin_notes'] ?? ''); ?></textarea>
                            </div>
                        </div>
                        <div class="text-right mt-4">
                            <button type="submit" class="bg-blue-600 text-white font-bold py-2 px-4 rounded-lg">Aggiorna</button>
                        </div>
                    </form>
                </div>
            </div>
            <?php } endif; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.success-alert, .error-alert').forEach(alert => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
</body>
</html>