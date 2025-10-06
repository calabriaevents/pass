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
        $end_date = $_POST['end_date'] ?? '';
        $location = trim($_POST['location'] ?? '');
        $category_id = $_POST['category_id'] ?? null;
        $province_id = $_POST['province_id'] ?? null;
        $organizer = trim($_POST['organizer'] ?? '');
        $contact_email = trim($_POST['contact_email'] ?? '');
        $contact_phone = trim($_POST['contact_phone'] ?? '');
        $website = trim($_POST['website'] ?? '');
        $price = $_POST['price'] ?? 0;
        $status = $_POST['status'] ?? 'pending';

        $db->updateEventSuggestion($id, $title, $description, $start_date, $end_date, $location, $category_id, $province_id, $organizer, $contact_email, $contact_phone, $website, $price, $status);
        header('Location: suggerimenti-eventi.php');
        exit;
    }
}

if ($action === 'delete' && $id) {
    $db->deleteEventSuggestion($id);
    header('Location: suggerimenti-eventi.php');
    exit;
}

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
                <h1 class="text-2xl font-bold text-gray-900">Suggerimenti Eventi</h1>
            </div>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-semibold">Elenco Suggerimenti</h2>
                    <div class="flex space-x-2">
                        <a href="?status=pending" class="px-3 py-1 text-xs font-medium text-yellow-700 bg-yellow-100 rounded-lg">Pendenti</a>
                        <a href="?status=active" class="px-3 py-1 text-xs font-medium text-green-700 bg-green-100 rounded-lg">Approvati</a>
                        <a href="?status=rejected" class="px-3 py-1 text-xs font-medium text-red-700 bg-red-100 rounded-lg">Rifiutati</a>
                        <a href="?" class="px-3 py-1 text-xs font-medium text-gray-700 bg-gray-100 rounded-lg">Tutti</a>
                    </div>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Evento</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Luogo</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Date</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Suggerito da</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Stato</th>
                            <th class="text-left py-3 px-2 font-semibold text-gray-700">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $filter_status = $_GET['status'] ?? null;
                        $suggestions = $db->getEventSuggestions($filter_status);
                        foreach ($suggestions as $suggestion):
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <div class="font-medium"><?php echo htmlspecialchars($suggestion['title']); ?></div>
                                <div class="text-sm text-gray-500"><?php echo htmlspecialchars($suggestion['category_name'] ?? 'N/A'); ?></div>
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600"><?php echo htmlspecialchars($suggestion['location']); ?></td>
                            <td class="py-3 px-2 text-sm text-gray-600">
                                <?php echo date('d/m/Y H:i', strtotime($suggestion['start_date'])); ?>
                                <?php if ($suggestion['end_date']) echo ' - ' . date('d/m/Y H:i', strtotime($suggestion['end_date'])); ?>
                            </td>
                            <td class="py-3 px-2">
                                <div class="text-sm font-medium"><?php echo htmlspecialchars($suggestion['organizer']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($suggestion['contact_email']); ?></div>
                            </td>
                            <td class="py-3 px-2">
                                <?php
                                $statusColors = [
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'active' => 'bg-green-100 text-green-800',
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
                                    <a href="?action=view&id=<?php echo $suggestion['id']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 bg-blue-100 rounded-lg hover:bg-blue-200">
                                        <i data-lucide="edit" class="w-3 h-3 mr-1"></i>Modifica
                                    </a>
                                    <a href="?action=delete&id=<?php echo $suggestion['id']; ?>" class="inline-flex items-center px-3 py-1 text-xs font-medium text-red-600 bg-red-100 rounded-lg hover:bg-red-200" onclick="return confirm('Sei sicuro di voler eliminare questo suggerimento?');">
                                        <i data-lucide="trash-2" class="w-3 h-3 mr-1"></i>Elimina
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                 <?php if (empty($suggestions)): ?>
                <div class="text-center py-8">
                    <i data-lucide="calendar-off" class="w-12 h-12 text-gray-400 mx-auto mb-3"></i>
                    <p class="text-gray-500">Nessun suggerimento di evento trovato.</p>
                </div>
                <?php endif; ?>
            </div>

            <?php elseif ($action === 'view' && $id):
                $suggestion = $db->getEventSuggestionById($id);
                if (!$suggestion):
                    echo '<div class="text-center py-8"><p class="text-red-500">Suggerimento non trovato.</p></div>';
                else:
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6 max-w-4xl mx-auto">
                <form action="?action=update&id=<?php echo $suggestion['id']; ?>" method="POST" class="space-y-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-semibold">Dettagli Suggerimento Evento</h2>
                        <a href="suggerimenti-eventi.php" class="text-gray-600 hover:underline">← Torna all'elenco</a>
                    </div>

                    <!-- Event Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="title" class="block text-sm font-medium text-gray-700">Nome Evento</label>
                            <input type="text" name="title" id="title" value="<?php echo htmlspecialchars($suggestion['title']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700">Categoria</label>
                            <select name="category_id" id="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleziona categoria</option>
                                <?php foreach ($categories as $category): ?>
                                <option value="<?php echo $category['id']; ?>" <?php echo $suggestion['category_id'] == $category['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700">Descrizione</label>
                        <textarea name="description" id="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"><?php echo htmlspecialchars($suggestion['description']); ?></textarea>
                    </div>

                    <!-- Date and Location -->
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700">Data Inizio</label>
                            <input type="datetime-local" name="start_date" id="start_date" value="<?php echo date('Y-m-d\TH:i', strtotime($suggestion['start_date'])); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700">Data Fine</label>
                            <input type="datetime-local" name="end_date" id="end_date" value="<?php echo $suggestion['end_date'] ? date('Y-m-d\TH:i', strtotime($suggestion['end_date'])) : ''; ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="province_id" class="block text-sm font-medium text-gray-700">Provincia</label>
                            <select name="province_id" id="province_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Seleziona provincia</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?php echo $province['id']; ?>" <?php echo $suggestion['province_id'] == $province['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($province['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                     <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Luogo/Indirizzo</label>
                            <input type="text" name="location" id="location" value="<?php echo htmlspecialchars($suggestion['location']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700">Prezzo (€)</label>
                            <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($suggestion['price']); ?>" step="0.01" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <!-- Organizer Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="organizer" class="block text-sm font-medium text-gray-700">Organizzatore</label>
                            <input type="text" name="organizer" id="organizer" value="<?php echo htmlspecialchars($suggestion['organizer']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="contact_email" class="block text-sm font-medium text-gray-700">Email Contatto</label>
                            <input type="email" name="contact_email" id="contact_email" value="<?php echo htmlspecialchars($suggestion['contact_email']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                         <div>
                            <label for="contact_phone" class="block text-sm font-medium text-gray-700">Telefono</label>
                            <input type="tel" name="contact_phone" id="contact_phone" value="<?php echo htmlspecialchars($suggestion['contact_phone']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        <div>
                            <label for="website" class="block text-sm font-medium text-gray-700">Sito Web</label>
                            <input type="url" name="website" id="website" value="<?php echo htmlspecialchars($suggestion['website']); ?>" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700">Stato</label>
                        <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                            <option value="pending" <?php echo $suggestion['status'] === 'pending' ? 'selected' : ''; ?>>In Attesa</option>
                            <option value="active" <?php echo $suggestion['status'] === 'active' ? 'selected' : ''; ?>>Approvato</option>
                            <option value="rejected" <?php echo $suggestion['status'] === 'rejected' ? 'selected' : ''; ?>>Rifiutato</option>
                        </select>
                    </div>

                    <div class="text-right">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">
                            <i data-lucide="save" class="w-4 h-4 inline mr-1"></i>
                            Aggiorna Suggerimento
                        </button>
                    </div>
                </form>
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