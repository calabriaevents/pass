<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();

$action = $_GET['action'] ?? 'list';
$id = $_GET['id'] ?? null;

// Handle search and filters for business list
$search_query = $_GET['search'] ?? '';
$status_filter = $_GET['status_filter'] ?? '';
$has_credits_filter = $_GET['has_credits'] ?? '';

// Gestione delle azioni POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'update_credits') {
        // Gestione aggiornamento crediti
        $purchase_id = $_POST['purchase_id'] ?? null;
        $new_credits = intval($_POST['new_credits'] ?? 0);
        $reason = $_POST['reason'] ?? '';
        $business_id = $_POST['business_id'] ?? null;
        
        if ($purchase_id && $business_id && $new_credits >= 0) {
            try {
                // Ottieni i crediti attuali
                $stmt = $db->pdo->prepare('SELECT credits_remaining, credits_purchased FROM consumption_purchases WHERE id = ? AND business_id = ?');
                $stmt->execute([$purchase_id, $business_id]);
                $purchase = $stmt->fetch();
                
                if ($purchase) {
                    $old_credits = $purchase['credits_remaining'];
                    $credits_difference = $old_credits - $new_credits;
                    
                    // Aggiorna i crediti
                    $stmt = $db->pdo->prepare('UPDATE consumption_purchases SET credits_remaining = ? WHERE id = ?');
                    $stmt->execute([$new_credits, $purchase_id]);
                    
                    // Registra l'utilizzo manuale nella tabella credit_usage se i crediti sono diminuiti
                    if ($credits_difference > 0) {
                        $stmt = $db->pdo->prepare('
                            INSERT INTO credit_usage (business_id, purchase_id, service_type, service_description, credits_used, used_at) 
                            VALUES (?, ?, ?, ?, ?, NOW())
                        ');
                        $stmt->execute([
                            $business_id, 
                            $purchase_id, 
                            'manual_deduction', 
                            'Riduzione manuale da admin: ' . htmlspecialchars($reason), 
                            $credits_difference
                        ]);
                    }
                    
                    header('Location: business.php?action=manage_credits&id=' . $business_id . '&success=1');
                    exit;
                }
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
        }
    } else {
        // Gestione normale del business
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $website = $_POST['website'] ?? '';
        $description = $_POST['description'] ?? '';
        $category_id = $_POST['category_id'] ?? null;
        $province_id = $_POST['province_id'] ?? null;
        $city_id = $_POST['city_id'] ?? null;
        $address = $_POST['address'] ?? '';
        $status = $_POST['status'] ?? 'pending';

        if ($action === 'edit' && $id) {
            $db->updateBusiness($id, $name, $email, $phone, $website, $description, $category_id, $province_id, $city_id, $address, $status);
        } else {
            $db->createBusiness($name, $email, $phone, $website, $description, $category_id, $province_id, $city_id, $address, $status);
        }
        header('Location: business.php');
        exit;
    }
}

if ($action === 'delete' && $id) {
    $db->deleteBusiness($id);
    header('Location: business.php');
    exit;
}

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Business - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gray-100 flex">
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

    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i data-lucide="building" class="w-7 h-7 text-blue-600 mr-2"></i>
                        <?php if ($action === 'manage_credits'): ?>
                            Gestione Crediti
                        <?php else: ?>
                            Gestione Business
                        <?php endif; ?>
                    </h1>
                    <?php if ($action === 'manage_credits'): ?>
                        <p class="text-sm text-gray-500">Modifica manualmente i crediti dei pacchetti a consumo</p>
                    <?php endif; ?>
                </div>
                <?php if ($action === 'list'): ?>
                <a href="business.php?action=new" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg flex items-center">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Nuovo Business
                </a>
                <?php elseif ($action === 'manage_credits'): ?>
                <a href="business.php" class="text-gray-600 hover:text-gray-800 font-medium flex items-center">
                    <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                    Torna ai Business
                </a>
                <?php endif; ?>
            </div>
        </header>

        <main class="flex-1 overflow-auto p-6">
            <?php if (isset($_GET['success'])): ?>
                <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded">
                    <p class="font-medium">✅ Crediti aggiornati con successo!</p>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="mb-6 bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded">
                    <p class="font-medium">❌ Errore: <?php echo htmlspecialchars($error); ?></p>
                </div>
            <?php endif; ?>

            <?php if ($action === 'list'): ?>
            <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="search" class="w-5 h-5 mr-2 text-blue-600"></i>
                    Ricerca e Filtri
                    <?php if ($search_query || $status_filter || $has_credits_filter): ?>
                        <span class="ml-2 text-sm font-normal text-orange-600">(Filtri attivi)</span>
                    <?php endif; ?>
                </h2>
                
                <form method="GET" class="space-y-4 md:space-y-0 md:flex md:items-end md:space-x-4">
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cerca Attività o Email</label>
                        <div class="relative">
                            <i data-lucide="search" class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 transform -translate-y-1/2"></i>
                            <input type="text" name="search" id="search" value="<?php echo htmlspecialchars($search_query); ?>"
                                   placeholder="Nome attività, email, telefono..." 
                                   class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Cerca per nome, email o numero di telefono</p>
                    </div>
                    
                    <div>
                        <label for="status_filter" class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
                        <select name="status_filter" id="status_filter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Tutti gli stati</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>In attesa</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approvato</option>
                            <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rifiutato</option>
                            <option value="suspended" <?php echo $status_filter === 'suspended' ? 'selected' : ''; ?>>Sospeso</option>
                        </select>
                    </div>
                    
                    <div>
                        <label for="has_credits" class="block text-sm font-medium text-gray-700 mb-1">Crediti</label>
                        <select name="has_credits" id="has_credits" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Tutti</option>
                            <option value="yes" <?php echo $has_credits_filter === 'yes' ? 'selected' : ''; ?>>Con crediti</option>
                            <option value="no" <?php echo $has_credits_filter === 'no' ? 'selected' : ''; ?>>Senza crediti</option>
                        </select>
                    </div>
                    
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                            <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                            Cerca
                        </button>
                        
                        <?php if ($search_query || $status_filter || $has_credits_filter): ?>
                        <a href="business.php" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
                            <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                            Reset
                        </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold mb-4 flex items-center">
                    <i data-lucide="list" class="w-5 h-5 mr-2 text-blue-600"></i>
                    Elenco Business
                    <?php 
                    $total_found = 0;
                    if ($search_query || $status_filter || $has_credits_filter): ?>
                        <span class="ml-2 text-sm font-normal text-gray-500" id="results-count"></span>
                    <?php endif; ?>
                </h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="text-left py-3 px-4 font-semibold">Nome</th>
                                <th class="text-left py-3 px-4 font-semibold">Email</th>
                                <th class="text-left py-3 px-4 font-semibold">Stato</th>
                                <th class="text-left py-3 px-4 font-semibold">Crediti</th>
                                <th class="text-left py-3 px-4 font-semibold">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            // OPTIMIZED QUERY
                            $businesses = [];
                            if ($db->isConnected()) {
                                $sql = '
                                    SELECT 
                                        b.*, 
                                        c.name as category_name, 
                                        p.name as province_name,
                                        COALESCE(cp.total_credits, 0) as total_credits,
                                        COALESCE(cp.total_purchases, 0) as total_purchases
                                    FROM 
                                        businesses b 
                                    LEFT JOIN 
                                        categories c ON b.category_id = c.id 
                                    LEFT JOIN 
                                        provinces p ON b.province_id = p.id
                                    LEFT JOIN (
                                        SELECT 
                                            business_id, 
                                            SUM(credits_remaining) as total_credits, 
                                            COUNT(*) as total_purchases 
                                        FROM 
                                            consumption_purchases 
                                        WHERE 
                                            status = "completed" 
                                        GROUP BY 
                                            business_id
                                    ) cp ON b.id = cp.business_id
                                    WHERE 1=1
                                ';
                                
                                $params = [];

                                if (!empty($search_query)) {
                                    $sql .= ' AND (b.name LIKE :search OR b.email LIKE :search OR b.phone LIKE :search)';
                                    $params[':search'] = "%$search_query%";
                                }
                                
                                if (!empty($status_filter)) {
                                    $sql .= ' AND b.status = :status';
                                    $params[':status'] = $status_filter;
                                }

                                if ($has_credits_filter === 'yes') {
                                    $sql .= ' AND COALESCE(cp.total_credits, 0) > 0';
                                } elseif ($has_credits_filter === 'no') {
                                    $sql .= ' AND COALESCE(cp.total_credits, 0) = 0';
                                }
                                
                                $sql .= ' ORDER BY b.name';
                                
                                $stmt = $db->pdo->prepare($sql);
                                $stmt->execute($params);
                                $businesses = $stmt->fetchAll();
                            }
                            
                            $total_found = count($businesses);
                            if (empty($businesses)): ?>
                                <tr>
                                    <td colspan="5" class="text-center py-12">
                                        <?php if ($search_query || $status_filter || $has_credits_filter): ?>
                                            <i data-lucide="search-x" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun risultato trovato</h3>
                                            <p class="text-gray-500 mb-4">Prova a modificare i filtri di ricerca o rimuovili per vedere tutti i business.</p>
                                            <a href="business.php" class="text-blue-600 hover:text-blue-700 font-medium">
                                                <i data-lucide="arrow-left" class="w-4 h-4 inline mr-1"></i>
                                                Visualizza tutti i business
                                            </a>
                                        <?php else: ?>
                                            <i data-lucide="building-2" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                                            <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun business presente</h3>
                                            <p class="text-gray-500">Non ci sono ancora business registrati nel sistema.</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php else:
                            foreach ($businesses as $business):
                                $total_credits = $business['total_credits'];
                                $total_purchases = $business['total_purchases'];
                            ?>
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="font-medium"><?php echo htmlspecialchars($business['name']); ?></div>
                                    <?php if (!empty($business['phone'])): ?>
                                        <div class="text-sm text-gray-500"><?php echo htmlspecialchars($business['phone']); ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4"><?php echo htmlspecialchars($business['email']); ?></td>
                                <td class="py-3 px-4">
                                    <?php
                                    $status_colors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'approved' => 'bg-green-100 text-green-800',
                                        'rejected' => 'bg-red-100 text-red-800',
                                        'suspended' => 'bg-gray-100 text-gray-800'
                                    ];
                                    $status_labels = [
                                        'pending' => 'In attesa',
                                        'approved' => 'Approvato',
                                        'rejected' => 'Rifiutato',
                                        'suspended' => 'Sospeso'
                                    ];
                                    ?>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium <?php echo $status_colors[$business['status']] ?? 'bg-gray-100 text-gray-800'; ?>">
                                        <?php echo $status_labels[$business['status']] ?? ucfirst($business['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <?php if ($total_purchases > 0): ?>
                                        <div class="flex items-center">
                                            <i data-lucide="zap" class="w-4 h-4 text-orange-500 mr-1"></i>
                                            <span class="font-semibold text-orange-600"><?php echo $total_credits; ?></span>
                                            <span class="text-sm text-gray-500 ml-1">crediti</span>
                                        </div>
                                        <div class="text-xs text-gray-400"><?php echo $total_purchases; ?> pacchetti</div>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-sm">Nessun pacchetto</span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="business.php?action=edit&id=<?php echo $business['id']; ?>" 
                                           class="text-blue-600 hover:text-blue-700 p-2 rounded-lg hover:bg-blue-50 transition-colors" title="Modifica">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                        
                                        <?php if ($total_purchases > 0): ?>
                                        <a href="business.php?action=manage_credits&id=<?php echo $business['id']; ?>" 
                                           class="text-orange-600 hover:text-orange-700 p-2 rounded-lg hover:bg-orange-50 transition-colors" title="Gestisci Crediti">
                                            <i data-lucide="zap" class="w-4 h-4"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <a href="business.php?action=delete&id=<?php echo $business['id']; ?>" 
                                           class="text-red-600 hover:text-red-700 p-2 rounded-lg hover:bg-red-50 transition-colors" title="Elimina"
                                           onclick="return confirm('Sei sicuro di voler eliminare questo business?');">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <?php elseif ($action === 'manage_credits' && $id): ?>
                <?php
                // Ottieni informazioni sul business
                $stmt = $db->pdo->prepare('SELECT * FROM businesses WHERE id = ?');
                $stmt->execute([$id]);
                $business = $stmt->fetch();
                
                if (!$business) {
                    echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded"><p>Business non trovato.</p></div>';
                } else {
                    // Ottieni tutti gli acquisti di pacchetti a consumo per questo business
                    $stmt = $db->pdo->prepare('
                        SELECT cp.*, bp.name as package_name, bp.consumption_credits as original_credits
                        FROM consumption_purchases cp
                        JOIN business_packages bp ON cp.package_id = bp.id
                        WHERE cp.business_id = ? AND cp.status = "completed"
                        ORDER BY cp.purchased_at DESC
                    ');
                    $stmt->execute([$id]);
                    $purchases = $stmt->fetchAll();
                    
                    // Ottieni lo storico utilizzi per questo business
                    $credit_usage_history = $db->getDetailedCreditUsageForAdmin($id, 50);
                ?>
                
                <div class="space-y-6">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-xl font-bold text-gray-900 flex items-center">
                                <i data-lucide="building" class="w-6 h-6 text-blue-600 mr-2"></i>
                                <?php echo htmlspecialchars($business['name']); ?>
                            </h2>
                            <span class="text-sm text-gray-500"><?php echo htmlspecialchars($business['email']); ?></span>
                        </div>
                        
                        <?php if (count($purchases) === 0): ?>
                            <div class="text-center py-12">
                                <i data-lucide="package-x" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun pacchetto a consumo</h3>
                                <p class="text-gray-500">Questo business non ha ancora acquistato pacchetti a consumo.</p>
                            </div>
                        <?php else: ?>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                                <?php
                                $total_remaining = array_sum(array_column($purchases, 'credits_remaining'));
                                $total_purchased = array_sum(array_column($purchases, 'credits_purchased'));
                                $total_used = $total_purchased - $total_remaining;
                                ?>
                                <div class="bg-blue-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-blue-900"><?php echo $total_purchased; ?></div>
                                    <div class="text-sm text-blue-600">Crediti Acquistati</div>
                                </div>
                                <div class="bg-orange-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-orange-900"><?php echo $total_remaining; ?></div>
                                    <div class="text-sm text-orange-600">Crediti Rimanenti</div>
                                </div>
                                <div class="bg-green-50 p-4 rounded-lg">
                                    <div class="text-2xl font-bold text-green-900"><?php echo $total_used; ?></div>
                                    <div class="text-sm text-green-600">Crediti Utilizzati</div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (count($purchases) > 0): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold flex items-center">
                                <i data-lucide="package" class="w-5 h-5 mr-2 text-orange-600"></i>
                                Pacchetti Acquistati
                                <span class="ml-2 text-sm font-normal text-gray-500">(<?php echo count($purchases); ?>)</span>
                            </h3>
                        </div>
                        
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($purchases as $purchase): ?>
                            <div class="p-6">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <h4 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($purchase['package_name']); ?></h4>
                                            <span class="ml-3 bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                                                ID: <?php echo $purchase['id']; ?>
                                            </span>
                                        </div>
                                        
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Acquistato:</span>
                                                <div class="font-medium"><?php echo date('d/m/Y H:i', strtotime($purchase['purchased_at'])); ?></div>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Crediti Originali:</span>
                                                <div class="font-medium"><?php echo $purchase['credits_purchased']; ?></div>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Crediti Rimanenti:</span>
                                                <div class="font-bold text-orange-600"><?php echo $purchase['credits_remaining']; ?></div>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Prezzo Pagato:</span>
                                                <div class="font-medium">€<?php echo number_format($purchase['amount_paid'], 2); ?></div>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($purchase['expires_at'])): ?>
                                        <div class="mt-2 text-sm text-gray-500">
                                            <i data-lucide="clock" class="w-4 h-4 inline mr-1"></i>
                                            Scade: <?php echo date('d/m/Y', strtotime($purchase['expires_at'])); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="ml-6">
                                        <button onclick="openEditModal(<?php echo $purchase['id']; ?>, <?php echo $purchase['credits_remaining']; ?>, '<?php echo htmlspecialchars($purchase['package_name']); ?>')" 
                                                class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center">
                                            <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                            Modifica Crediti
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <?php if (count($credit_usage_history) > 0): ?>
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold flex items-center">
                                <i data-lucide="activity" class="w-5 h-5 mr-2 text-purple-600"></i>
                                Storico Dettagliato Utilizzi
                                <span class="ml-2 text-sm font-normal text-gray-500">(Ultimi <?php echo count($credit_usage_history); ?>)</span>
                            </h3>
                            <p class="text-sm text-gray-600 mt-1">Cronologia completa di tutti i servizi erogati e crediti scalati</p>
                        </div>
                        
                        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            <?php foreach ($credit_usage_history as $usage): ?>
                            <div class="p-6 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center">
                                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                                    <?php if ($usage['service_type'] === 'manual_deduction'): ?>
                                                        <i data-lucide="user-check" class="w-4 h-4 text-purple-600"></i>
                                                    <?php else: ?>
                                                        <i data-lucide="zap" class="w-4 h-4 text-purple-600"></i>
                                                    <?php endif; ?>
                                                </div>
                                                <div>
                                                    <h4 class="font-semibold text-gray-900">
                                                        <?php if ($usage['service_type'] === 'manual_deduction'): ?>
                                                            Servizio Completato (Admin)
                                                        <?php else: ?>
                                                            <?php echo ucfirst(str_replace('_', ' ', $usage['service_type'])); ?>
                                                        <?php endif; ?>
                                                    </h4>
                                                    <p class="text-sm text-gray-500">
                                                        <?php echo date('d/m/Y H:i:s', strtotime($usage['used_at'])); ?>
                                                    </p>
                                                </div>
                                            </div>
                                            
                                            <div class="text-right">
                                                <div class="text-xl font-bold text-red-600">-<?php echo $usage['credits_used']; ?></div>
                                                <div class="text-xs text-gray-500">crediti scalati</div>
                                            </div>
                                        </div>
                                        
                                        <div class="bg-gray-50 rounded-lg p-4 mb-4">
                                            <p class="text-sm text-gray-600 mb-1"><strong>Motivo:</strong></p>
                                            <p class="text-gray-800"><?php echo htmlspecialchars($usage['service_description']); ?></p>
                                        </div>
                                        
                                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                            <div>
                                                <span class="text-gray-500">Pacchetto:</span>
                                                <div class="font-medium"><?php echo htmlspecialchars($usage['package_name']); ?></div>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">ID Acquisto:</span>
                                                <div class="font-medium">#<?php echo $usage['purchase_id']; ?></div>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Crediti Originali:</span>
                                                <div class="font-medium"><?php echo $usage['original_credits']; ?></div>
                                            </div>
                                            <div>
                                                <span class="text-gray-500">Rimanenti Dopo:</span>
                                                <div class="font-medium text-orange-600"><?php echo $usage['current_remaining_credits']; ?></div>
                                            </div>
                                        </div>
                                        
                                        <?php if ($usage['package_purchased_at']): ?>
                                        <div class="mt-3 text-xs text-gray-500">
                                            <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                                            Pacchetto acquistato: <?php echo date('d/m/Y H:i', strtotime($usage['package_purchased_at'])); ?>
                                            <?php if ($usage['amount_paid']): ?>
                                                • Importo: €<?php echo number_format($usage['amount_paid'], 2); ?>
                                            <?php endif; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="px-6 py-3 bg-gray-50 text-center">
                            <p class="text-sm text-gray-600">
                                <i data-lucide="database" class="w-4 h-4 inline mr-1"></i>
                                Visualizzati gli ultimi 50 utilizzi • Tutte le modifiche vengono registrate automaticamente
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                
                <?php } ?>

            <?php elseif ($action === 'new' || $action === 'edit'): ?>
                <?php
                $business = null;
                if ($action === 'edit' && $id) {
                    $business = $db->getBusinessById($id);
                }
                $categories = $db->getCategories();
                $provinces = $db->getProvinces();
                ?>
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h2 class="text-lg font-semibold mb-4"><?php echo $action === 'edit' ? 'Modifica Business' : 'Nuovo Business'; ?></h2>
                    <form action="business.php?action=<?php echo $action; ?><?php if ($id) echo '&id='.$id; ?>" method="POST">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="name" class="block text-gray-700 font-bold mb-2">Nome</label>
                                <input type="text" name="name" id="name" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($business['name'] ?? ''); ?>" required>
                            </div>
                            <div>
                                <label for="email" class="block text-gray-700 font-bold mb-2">Email</label>
                                <input type="email" name="email" id="email" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($business['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label for="phone" class="block text-gray-700 font-bold mb-2">Telefono</label>
                                <input type="text" name="phone" id="phone" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($business['phone'] ?? ''); ?>">
                            </div>
                            <div>
                                <label for="website" class="block text-gray-700 font-bold mb-2">Sito Web</label>
                                <input type="text" name="website" id="website" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($business['website'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-gray-700 font-bold mb-2">Descrizione</label>
                            <textarea name="description" id="description" rows="5" class="w-full px-3 py-2 border rounded-lg"><?php echo htmlspecialchars($business['description'] ?? ''); ?></textarea>
                        </div>
                        <div class="mb-4">
                            <label for="address" class="block text-gray-700 font-bold mb-2">Indirizzo</label>
                            <input type="text" name="address" id="address" class="w-full px-3 py-2 border rounded-lg" value="<?php echo htmlspecialchars($business['address'] ?? ''); ?>">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <div>
                                <label for="category_id" class="block text-gray-700 font-bold mb-2">Categoria</label>
                                <select name="category_id" id="category_id" class="w-full px-3 py-2 border rounded-lg">
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['id']; ?>" <?php if (isset($business) && $business['category_id'] == $category['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($category['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="province_id" class="block text-gray-700 font-bold mb-2">Provincia</label>
                                <select name="province_id" id="province_id" class="w-full px-3 py-2 border rounded-lg">
                                    <option value="">Nessuna</option>
                                    <?php foreach ($provinces as $province): ?>
                                    <option value="<?php echo $province['id']; ?>" <?php if (isset($business) && $business['province_id'] == $province['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($province['name']); ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div>
                                <label for="status" class="block text-gray-700 font-bold mb-2">Stato</label>
                                <select name="status" id="status" class="w-full px-3 py-2 border rounded-lg">
                                    <option value="pending" <?php if (isset($business) && $business['status'] === 'pending') echo 'selected'; ?>>In attesa</option>
                                    <option value="approved" <?php if (isset($business) && $business['status'] === 'approved') echo 'selected'; ?>>Approvato</option>
                                    <option value="rejected" <?php if (isset($business) && $business['status'] === 'rejected') echo 'selected'; ?>>Rifiutato</option>
                                    <option value="suspended" <?php if (isset($business) && $business['status'] === 'suspended') echo 'selected'; ?>>Sospeso</option>
                                </select>
                            </div>
                        </div>
                        <div class="text-right">
                            <a href="business.php" class="text-gray-600 hover:underline mr-4">Annulla</a>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg">Salva Business</button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <div id="editCreditsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen">
            <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-md mx-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Modifica Crediti</h3>
                    <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                        <i data-lucide="x" class="w-6 h-6"></i>
                    </button>
                </div>
                
                <form action="business.php?action=update_credits" method="POST">
                    <input type="hidden" name="purchase_id" id="modal_purchase_id">
                    <input type="hidden" name="business_id" value="<?php echo htmlspecialchars($id ?? ''); ?>">
                    
                    <div class="mb-4">
                        <p class="text-sm text-gray-600 mb-2">Pacchetto: <span id="modal_package_name" class="font-semibold"></span></p>
                        <label for="modal_new_credits" class="block text-gray-700 font-medium mb-2">Nuovi Crediti Rimanenti</label>
                        <input type="number" name="new_credits" id="modal_new_credits" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" required>
                        <p class="text-xs text-gray-500 mt-1">Inserisci il nuovo numero di crediti rimanenti</p>
                    </div>
                    
                    <div class="mb-6">
                        <label for="modal_reason" class="block text-gray-700 font-medium mb-2">Motivo della Modifica</label>
                        <textarea name="reason" id="modal_reason" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-transparent" placeholder="Es: Set fotografico completato, servizio erogato..." required></textarea>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-gray-600 hover:text-gray-800 font-medium">
                            Annulla
                        </button>
                        <button type="submit" class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg font-medium">
                            Aggiorna Crediti
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();

        function openEditModal(purchaseId, currentCredits, packageName) {
            document.getElementById('modal_purchase_id').value = purchaseId;
            document.getElementById('modal_new_credits').value = currentCredits;
            document.getElementById('modal_package_name').textContent = packageName;
            document.getElementById('modal_reason').value = '';
            document.getElementById('editCreditsModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editCreditsModal').classList.add('hidden');
        }

        // Chiudi modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });

        // Chiudi modal cliccando fuori
        document.getElementById('editCreditsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });
    </script>
</body>
</html>