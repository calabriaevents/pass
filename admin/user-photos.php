<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Initialize database
$db = new Database();

// Handle actions
if ($_POST) {
    $action = $_POST['action'] ?? '';
    $upload_id = intval($_POST['upload_id'] ?? 0);
    $admin_notes = trim($_POST['admin_notes'] ?? '');
    
    if ($upload_id > 0) {
        switch ($action) {
            case 'approve':
                $stmt = $db->connection->prepare("
                    UPDATE user_uploads 
                    SET status = 'approved', admin_notes = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param('si', $admin_notes, $upload_id);
                $stmt->execute();
                $message = "Foto approvata con successo!";
                $message_type = "success";
                break;
                
            case 'reject':
                $stmt = $db->connection->prepare("
                    UPDATE user_uploads 
                    SET status = 'rejected', admin_notes = ?, updated_at = NOW() 
                    WHERE id = ?
                ");
                $stmt->bind_param('si', $admin_notes, $upload_id);
                $stmt->execute();
                $message = "Foto rifiutata.";
                $message_type = "warning";
                break;
                
            case 'delete':
                // Get file path before deleting record
                $stmt = $db->connection->prepare("SELECT image_path FROM user_uploads WHERE id = ?");
                $stmt->bind_param('i', $upload_id);
                $stmt->execute();
                $result = $stmt->get_result();
                $upload = $result->fetch_assoc();
                
                if ($upload) {
                    // Delete file
                    $filePath = '../' . $upload['image_path'];
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                    
                    // Delete record
                    $stmt = $db->connection->prepare("DELETE FROM user_uploads WHERE id = ?");
                    $stmt->bind_param('i', $upload_id);
                    $stmt->execute();
                }
                
                $message = "Foto eliminata definitivamente.";
                $message_type = "error";
                break;
        }
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$article_filter = $_GET['article_id'] ?? '';
$province_filter = $_GET['province_id'] ?? '';

// Build query
$where_conditions = [];
$params = [];
$param_types = '';

if ($status_filter !== 'all') {
    $where_conditions[] = "u.status = ?";
    $params[] = $status_filter;
    $param_types .= 's';
}

if (!empty($article_filter)) {
    $where_conditions[] = "u.article_id = ?";
    $params[] = intval($article_filter);
    $param_types .= 'i';
}

if (!empty($province_filter)) {
    $where_conditions[] = "u.province_id = ?";
    $params[] = intval($province_filter);
    $param_types .= 'i';
}

$where_clause = !empty($where_conditions) ? 'WHERE ' . implode(' AND ', $where_conditions) : '';

// Get uploads with related data
$query = "
    SELECT u.*, 
           a.title as article_title,
           p.name as province_name,
           c.name as city_name
    FROM user_uploads u
    LEFT JOIN articles a ON u.article_id = a.id
    LEFT JOIN provinces p ON u.province_id = p.id
    LEFT JOIN cities c ON a.city_id = c.id
    {$where_clause}
    ORDER BY u.created_at DESC
    LIMIT 50
";

$stmt = $db->connection->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($param_types, ...$params);
}
$stmt->execute();
$uploads = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get counts for status badges
$counts_query = "
    SELECT status, COUNT(*) as count 
    FROM user_uploads 
    GROUP BY status
";
$counts_result = $db->connection->query($counts_query);
$status_counts = [];
while ($row = $counts_result->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Get articles and provinces for filters
$articles = $db->connection->query("SELECT id, title FROM articles ORDER BY title")->fetch_all(MYSQLI_ASSOC);
$provinces = $db->connection->query("SELECT id, name FROM provinces ORDER BY name")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moderazione Foto Utenti - Passione Calabria Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-semibold text-gray-900">📸 Moderazione Foto Utenti</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="articoli.php" class="text-blue-600 hover:text-blue-800 font-medium">← Torna agli Articoli</a>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Status Message -->
            <?php if (isset($message)): ?>
            <div class="mb-6 p-4 rounded-lg <?php echo $message_type === 'success' ? 'bg-green-100 text-green-800' : ($message_type === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                                    <i data-lucide="clock" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">In Attesa</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $status_counts['pending'] ?? 0; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                                    <i data-lucide="check" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Approvate</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $status_counts['approved'] ?? 0; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                                    <i data-lucide="x" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Rifiutate</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo $status_counts['rejected'] ?? 0; ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                                    <i data-lucide="image" class="w-5 h-5 text-white"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">Totali</dt>
                                    <dd class="text-lg font-medium text-gray-900"><?php echo array_sum($status_counts); ?></dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white shadow rounded-lg mb-8">
                <div class="px-4 py-5 sm:p-6">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Filtri</h3>
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                            <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tutti</option>
                                <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>In Attesa</option>
                                <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approvate</option>
                                <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rifiutate</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Articolo</label>
                            <select name="article_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Tutti gli articoli</option>
                                <?php foreach ($articles as $article): ?>
                                <option value="<?php echo $article['id']; ?>" <?php echo $article_filter == $article['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($article['title']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Provincia</label>
                            <select name="province_id" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Tutte le province</option>
                                <?php foreach ($provinces as $province): ?>
                                <option value="<?php echo $province['id']; ?>" <?php echo $province_filter == $province['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($province['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Filtra
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Photos Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($uploads)): ?>
                <div class="col-span-full text-center py-12">
                    <i data-lucide="image-off" class="w-12 h-12 text-gray-400 mx-auto mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Nessuna foto trovata</h3>
                    <p class="text-gray-500">Non ci sono foto che corrispondono ai filtri selezionati.</p>
                </div>
                <?php else: ?>
                <?php foreach ($uploads as $upload): ?>
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Image -->
                    <div class="aspect-w-16 aspect-h-12 bg-gray-200">
                        <img src="../<?php echo htmlspecialchars($upload['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($upload['description']); ?>"
                             class="w-full h-48 object-cover">
                    </div>
                    
                    <!-- Content -->
                    <div class="p-4">
                        <!-- Status Badge -->
                        <div class="flex items-center justify-between mb-3">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php 
                                echo $upload['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                    ($upload['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'); 
                                ?>">
                                <?php 
                                echo $upload['status'] === 'pending' ? '⏳ In Attesa' : 
                                    ($upload['status'] === 'approved' ? '✅ Approvata' : '❌ Rifiutata'); 
                                ?>
                            </span>
                            <span class="text-xs text-gray-500">
                                <?php echo date('d/m/Y H:i', strtotime($upload['created_at'])); ?>
                            </span>
                        </div>

                        <!-- User Info -->
                        <div class="mb-3">
                            <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($upload['user_name']); ?></p>
                            <p class="text-xs text-gray-500"><?php echo htmlspecialchars($upload['user_email']); ?></p>
                        </div>

                        <!-- Location -->
                        <div class="mb-3">
                            <?php if ($upload['article_title']): ?>
                            <p class="text-xs text-blue-600">📄 <?php echo htmlspecialchars($upload['article_title']); ?></p>
                            <?php endif; ?>
                            <?php if ($upload['province_name']): ?>
                            <p class="text-xs text-gray-600">📍 <?php echo htmlspecialchars($upload['province_name']); ?></p>
                            <?php endif; ?>
                        </div>

                        <!-- Description -->
                        <p class="text-sm text-gray-700 mb-4">
                            <?php echo htmlspecialchars(substr($upload['description'], 0, 100)); ?>
                            <?php if (strlen($upload['description']) > 100): ?>...<?php endif; ?>
                        </p>

                        <!-- Admin Notes -->
                        <?php if (!empty($upload['admin_notes'])): ?>
                        <div class="mb-4 p-2 bg-gray-50 rounded text-xs">
                            <strong>Note admin:</strong> <?php echo htmlspecialchars($upload['admin_notes']); ?>
                        </div>
                        <?php endif; ?>

                        <!-- Actions -->
                        <div class="flex space-x-2">
                            <button onclick="openModal(<?php echo htmlspecialchars(json_encode($upload)); ?>)" 
                                    class="flex-1 bg-blue-600 text-white px-3 py-2 rounded text-xs hover:bg-blue-700">
                                Gestisci
                            </button>
                            <a href="../<?php echo htmlspecialchars($upload['image_path']); ?>" 
                               target="_blank"
                               class="flex-1 bg-gray-600 text-white px-3 py-2 rounded text-xs hover:bg-gray-700 text-center">
                                Visualizza
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal for photo management -->
    <div id="photoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden">
        <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-medium text-gray-900" id="modalTitle">Gestione Foto</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-5 h-5"></i>
                    </button>
                </div>
                
                <div id="modalContent">
                    <!-- Content will be populated by JavaScript -->
                </div>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openModal(upload) {
            const modal = document.getElementById('photoModal');
            const modalContent = document.getElementById('modalContent');
            
            modalContent.innerHTML = `
                <div class="mb-4">
                    <img src="../${upload.image_path}" alt="${upload.description}" class="w-full h-64 object-cover rounded-lg">
                </div>
                
                <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                    <div><strong>Nome:</strong> ${upload.user_name}</div>
                    <div><strong>Email:</strong> ${upload.user_email}</div>
                    <div><strong>Data:</strong> ${new Date(upload.created_at).toLocaleString('it-IT')}</div>
                    <div><strong>Stato:</strong> 
                        <span class="px-2 py-1 rounded text-xs ${
                            upload.status === 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                            upload.status === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'
                        }">${
                            upload.status === 'pending' ? 'In Attesa' : 
                            upload.status === 'approved' ? 'Approvata' : 'Rifiutata'
                        }</span>
                    </div>
                </div>
                
                <div class="mb-4">
                    <strong>Descrizione:</strong>
                    <p class="mt-1 text-gray-700">${upload.description}</p>
                </div>
                
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="upload_id" value="${upload.id}">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Note Admin</label>
                        <textarea name="admin_notes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-md" placeholder="Aggiungi note per l'utente...">${upload.admin_notes || ''}</textarea>
                    </div>
                    
                    <div class="flex space-x-3">
                        ${upload.status !== 'approved' ? `
                        <button type="submit" name="action" value="approve" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">
                            ✅ Approva
                        </button>
                        ` : ''}
                        
                        ${upload.status !== 'rejected' ? `
                        <button type="submit" name="action" value="reject" class="bg-yellow-600 text-white px-4 py-2 rounded hover:bg-yellow-700">
                            ❌ Rifiuta
                        </button>
                        ` : ''}
                        
                        <button type="submit" name="action" value="delete" onclick="return confirm('Sei sicuro di voler eliminare definitivamente questa foto?')" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700">
                            🗑️ Elimina
                        </button>
                    </div>
                </form>
            `;
            
            modal.classList.remove('hidden');
        }

        function closeModal() {
            document.getElementById('photoModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('photoModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>