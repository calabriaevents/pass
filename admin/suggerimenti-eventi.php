<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Authentication check (to be implemented)
// requireLogin();

$db = new Database();
$provinces = $db->getProvinces();
$categories = $db->getCategories();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update' && $id) {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $start_date = $_POST['start_date'] ?? '';
        $end_date = !empty($_POST['end_date']) ? $_POST['end_date'] : null;
        $location = trim($_POST['location'] ?? '');
        $category_id = $_POST['category_id'] ?? null;
        $province_id = $_POST['province_id'] ?? null;
        $organizer = trim($_POST['organizer'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $price = $_POST['price'] ?? 0;
        $status = $_POST['status'] ?? 'pending';

        if ($db->updateEventSuggestion($id, $title, $description, $start_date, $end_date, $location, $category_id, $province_id, $organizer, $contact_email, $contact_phone, $website, $price, $status)) {
             header('Location: suggerimenti-eventi.php?success=Suggerimento aggiornato con successo!');
        } else {
             header('Location: suggerimenti-eventi.php?action=view&id=' . $id . '&error=Errore durante l\'aggiornamento.');
        }
        exit;
    }
}

if ($action === 'delete' && $id) {
    // Note: No files to delete for event suggestions in this implementation
    if ($db->deleteEventSuggestion($id)) {
        header('Location: suggerimenti-eventi.php?success=Suggerimento eliminato con successo.');
    } else {
        header('Location: suggerimenti-eventi.php?error=Errore durante l\'eliminazione.');
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
    <title>Suggerimenti Eventi - Admin Panel</title>
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
            <h1 class="text-2xl font-bold text-gray-900">Suggerimenti Eventi</h1>
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
                <!-- ... table listing code ... -->
                 <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Evento</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Stato</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $suggestions = $db->getEventSuggestions(null);
                        foreach ($suggestions as $suggestion):
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2"><?php echo htmlspecialchars($suggestion['title']); ?></td>
                            <td class="py-3 px-2">
                                <?php $statusColors = ['pending' => 'bg-yellow-100 text-yellow-800', 'active' => 'bg-green-100 text-green-800', 'rejected' => 'bg-red-100 text-red-800']; ?>
                                <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusColors[$suggestion['status']] ?? 'bg-gray-100'; ?>">
                                    <?php echo ucfirst($suggestion['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <a href="?action=view&id=<?php echo $suggestion['id']; ?>" class="text-blue-600 hover:underline">Modifica</a>
                                <a href="?action=delete&id=<?php echo $suggestion['id']; ?>" class="text-red-600 hover:underline ml-2" onclick="return confirm('Sei sicuro?');">Elimina</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php elseif ($action === 'view' && $id):
                $suggestion = $db->getEventSuggestionById($id);
                if (!$suggestion) { echo '<p>Suggerimento non trovato.</p>'; } else {
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
                <form action="?action=update&id=<?php echo $suggestion['id']; ?>" method="POST" class="space-y-6">
                    <h2 class="text-xl font-bold">Modifica Suggerimento Evento</h2>
                    <!-- Event Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Nome Evento</label>
                            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($suggestion['title']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                         <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Stato</label>
                            <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="pending" <?php echo $suggestion['status'] === 'pending' ? 'selected' : ''; ?>>In Attesa</option>
                                <option value="active" <?php echo $suggestion['status'] === 'active' ? 'selected' : ''; ?>>Approvato</option>
                                <option value="rejected" <?php echo $suggestion['status'] === 'rejected' ? 'selected' : ''; ?>>Rifiutato</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descrizione</label>
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo htmlspecialchars($suggestion['description']); ?></textarea>
                    </div>

                    <div class="text-right">
                        <a href="suggerimenti-eventi.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Aggiorna</button>
                    </div>
                </form>
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