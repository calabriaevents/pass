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
    $db->updateCommentStatus($id, 'approved');
    header('Location: commenti.php');
    exit;
}

if ($action === 'reject' && $id) {
    $db->updateCommentStatus($id, 'rejected');
    header('Location: commenti.php');
    exit;
}

if ($action === 'delete' && $id) {
    $db->deleteComment($id);
    header('Location: commenti.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'edit' && $id) {
    $content = $_POST['content'] ?? '';
    $db->updateCommentContent($id, $content);
    header('Location: commenti.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Commenti - Admin Panel</title>
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
            <h1 class="text-2xl font-bold text-gray-900">Gestione Commenti</h1>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Elenco Commenti</h2>
                <div class="mb-4 border-b border-gray-200">
                    <nav class="flex space-x-4">
                        <a href="?status=" class="py-2 px-4 <?php if (!$status && !$type) echo 'border-b-2 border-blue-600 font-semibold'; ?>">Tutti</a>
                        <a href="?status=pending" class="py-2 px-4 <?php if ($status === 'pending' && !$type) echo 'border-b-2 border-blue-600 font-semibold'; ?>">In attesa</a>
                        <a href="?status=approved" class="py-2 px-4 <?php if ($status === 'approved' && !$type) echo 'border-b-2 border-blue-600 font-semibold'; ?>">Approvati</a>
                        <a href="?status=rejected" class="py-2 px-4 <?php if ($status === 'rejected' && !$type) echo 'border-b-2 border-blue-600 font-semibold'; ?>">Rifiutati</a>
                    </nav>
                </div>
                <div class="mb-4 border-b border-gray-200">
                    <nav class="flex space-x-4">
                        <a href="?type=" class="py-2 px-4 <?php if (!$type) echo 'border-b-2 border-green-600 font-semibold text-green-600'; ?>">Tutti i Tipi</a>
                        <a href="?type=article" class="py-2 px-4 <?php if ($type === 'article') echo 'border-b-2 border-green-600 font-semibold text-green-600'; ?>">Su Articoli</a>
                        <a href="?type=city" class="py-2 px-4 <?php if ($type === 'city') echo 'border-b-2 border-green-600 font-semibold text-green-600'; ?>">Su Città</a>
                    </nav>
                </div>
                <table class="w-full">
                    <thead>
                        <tr class="border-b bg-gray-50">
                            <th class="text-left py-3 px-2">Autore</th>
                            <th class="text-left py-3 px-2">Valutazione</th>
                            <th class="text-left py-3 px-2">Commento</th>
                            <th class="text-left py-3 px-2">Oggetto</th>
                            <th class="text-left py-3 px-2">Stato</th>
                            <th class="text-left py-3 px-2">Azioni</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Determina quale metodo usare in base al tipo
                        if ($type === 'city') {
                            $comments = $db->getCityComments(null, $status);
                        } elseif ($type === 'article') {
                            $comments = $db->getComments($status);
                            // Filtra solo commenti su articoli
                            $comments = array_filter($comments, function($comment) {
                                return !empty($comment['article_id']);
                            });
                        } else {
                            // Tutti i commenti - combina articoli e città
                            $articleComments = $db->getComments($status);
                            $cityComments = $db->getCityComments(null, $status);
                            $comments = array_merge($articleComments, $cityComments);
                            // Ordina per data di creazione
                            usort($comments, function($a, $b) {
                                return strtotime($b['created_at']) - strtotime($a['created_at']);
                            });
                        }
                        foreach ($comments as $comment):
                        ?>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-2">
                                <div class="font-medium"><?php echo htmlspecialchars($comment['author_name']); ?></div>
                                <div class="text-xs text-gray-500"><?php echo htmlspecialchars($comment['author_email']); ?></div>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex items-center">
                                    <?php for ($i = 0; $i < 5; $i++): ?>
                                        <i data-lucide="star" class="w-4 h-4 <?php echo ($i < $comment['rating']) ? 'text-yellow-400 fill-current' : 'text-gray-300'; ?>"></i>
                                    <?php endfor; ?>
                                    <span class="ml-2 text-sm font-bold">(<?php echo $comment['rating']; ?>)</span>
                                </div>
                            </td>
                            <td class="py-3 px-2 text-sm text-gray-600"><?php echo htmlspecialchars(substr($comment['content'], 0, 50)); ?>...</td>
                            <td class="py-3 px-2 text-sm">
                                <?php if (!empty($comment['article_title'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800 mb-1">
                                        <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                        Articolo
                                    </span><br>
                                    <a href="../articolo.php?slug=<?php echo $comment['article_slug'] ?? ''; ?>" target="_blank" class="text-blue-600 hover:underline">
                                        <?php echo htmlspecialchars($comment['article_title']); ?>
                                    </a>
                                <?php elseif (!empty($comment['city_name'])): ?>
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 mb-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 mr-1"></i>
                                        Città
                                    </span><br>
                                    <a href="../citta-dettaglio.php?id=<?php echo $comment['city_id'] ?? ''; ?>" target="_blank" class="text-green-600 hover:underline">
                                        <?php echo htmlspecialchars($comment['city_name']); ?>
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-xs">Oggetto sconosciuto</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-2">
                                <?php
                                $statusColors = [
                                    'approved' => 'bg-green-100 text-green-800',
                                    'pending' => 'bg-yellow-100 text-yellow-800',
                                    'rejected' => 'bg-red-100 text-red-800'
                                ];
                                $statusClass = $statusColors[$comment['status']] ?? 'bg-gray-100 text-gray-800';
                                ?>
                                <span class="inline-block px-2 py-1 text-xs font-semibold rounded-full <?php echo $statusClass; ?>">
                                    <?php echo ucfirst($comment['status']); ?>
                                </span>
                            </td>
                            <td class="py-3 px-2">
                                <div class="flex space-x-1">
                                <?php if ($comment['status'] === 'pending'): ?>
                                <a href="?action=approve&id=<?php echo $comment['id']; ?>" class="p-1 text-green-600 rounded-md hover:bg-green-100"><i data-lucide="check" class="w-4 h-4"></i></a>
                                <a href="?action=reject&id=<?php echo $comment['id']; ?>" class="p-1 text-orange-600 rounded-md hover:bg-orange-100"><i data-lucide="x" class="w-4 h-4"></i></a>
                                <?php endif; ?>
                                <a href="?action=edit&id=<?php echo $comment['id']; ?>" class="p-1 text-blue-600 rounded-md hover:bg-blue-100"><i data-lucide="edit" class="w-4 h-4"></i></a>
                                <a href="?action=delete&id=<?php echo $comment['id']; ?>" class="p-1 text-red-600 rounded-md hover:bg-red-100" onclick="return confirm('Sei sicuro di voler eliminare questo commento?');"><i data-lucide="trash-2" class="w-4 h-4"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php elseif ($action === 'edit'):
                $comment = null;
                if ($id) {
                    $comment = $db->getCommentById($id);
                }
            ?>
            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4">Modifica Commento</h2>
                <form action="?action=edit&id=<?php echo $id; ?>" method="POST">
                    <div class="mb-4">
                        <label for="content" class="block text-gray-700 font-bold mb-2">Contenuto</label>
                        <textarea name="content" id="content" rows="5" class="w-full px-3 py-2 border rounded-lg" required><?php echo htmlspecialchars($comment['content'] ?? ''); ?></textarea>
                    </div>
                    <div class="text-right">
                        <a href="commenti.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva Modifiche</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
        </main>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
