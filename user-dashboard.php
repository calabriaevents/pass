<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

// Check if user is logged in
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: user-auth.php');
    exit;
}

$db = new Database();

// Carica impostazioni admin
$contact_phone = $db->getSetting('contact_phone') ?: '+39 000 000 0000';
$contact_text = $db->getSetting('contact_text') ?: 'Per qualsiasi modifica alla pagina personalizzata, contattaci:';
$contact_hours = $db->getSetting('contact_hours') ?: 'Disponibili dal Lunedì al Venerdì, 9:00-18:00';

$user_id = $_SESSION['user_id'] ?? null;
$business_id = $_SESSION['business_id'] ?? null;

// Additional safety check for session variables
if (!$user_id || !$business_id) {
    session_destroy();
    header('Location: user-auth.php?error=session_expired');
    exit;
}
$message = '';
$error = '';

// Check database connection
$database_available = $db->isConnected();
if (!$database_available) {
    $error = 'Il sistema è temporaneamente non disponibile. Alcune funzionalità potrebbero non essere disponibili.';
}

// Handle subscription actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    // Logout doesn't require database
    if ($action === 'logout') {
        session_destroy();
        header('Location: user-auth.php');
        exit;
    }

    // Other actions require database connection
    if (!$database_available) {
        $error = 'Azione non disponibile: sistema temporaneamente offline.';
    } elseif ($action === 'upgrade' || $action === 'downgrade') {
        $new_package_id = (int)$_POST['package_id'];

        if ($new_package_id > 0) {
            // Controlla se il pacchetto è a pagamento
            $stmt = $db->pdo->prepare("SELECT price FROM business_packages WHERE id = ?");
            $stmt->execute([$new_package_id]);
            $package = $stmt->fetch();
            
            if ($package && $package['price'] > 0) {
                // Pacchetto a pagamento: reindirizza a Stripe
                $_SESSION['payment_operation'] = $action;
                $_SESSION['payment_package_id'] = $new_package_id;
                header('Location: api/dashboard-stripe-checkout.php');
                exit;
            } else {
                // Pacchetto gratuito: processa direttamente
                if ($db->upgradeSubscription($business_id, $new_package_id)) {
                    $message = $action === 'upgrade' ?
                        'Abbonamento aggiornato con successo!' :
                        'Piano modificato con successo!';
                } else {
                    $error = 'Errore durante la modifica dell\'abbonamento.';
                }
            }
        }
    } elseif ($action === 'buy_credits') { // NUOVA LOGICA PER ACQUISTO CREDITI
        $package_id = (int)$_POST['package_id'];
        if ($package_id > 0) {
            // Controlla se il pacchetto crediti è a pagamento
            $stmt = $db->pdo->prepare("SELECT price FROM business_packages WHERE id = ? AND package_type = 'consumption'");
            $stmt->execute([$package_id]);
            $package = $stmt->fetch();
            
            if ($package && $package['price'] > 0) {
                // Pacchetto a pagamento: reindirizza a Stripe
                $_SESSION['payment_operation'] = 'buy_credits';
                $_SESSION['payment_package_id'] = $package_id;
                header('Location: api/dashboard-stripe-checkout.php');
                exit;
            } else {
                // Pacchetto gratuito: processa direttamente
                if ($db->purchaseCreditPackage($business_id, $package_id)) {
                    $message = "Pacchetto crediti acquistato con successo!";
                } else {
                    $error = "Errore durante l'acquisto dei crediti.";
                }
            }
        }
    }
}

// Get user and business info
$user_data = $database_available ? $db->getUserBusinessData($user_id) : false;

// **CORREZIONE CRUCIALE**: Check if user data was found at all
if (!$user_data) {
    session_destroy();
    // Show a clear error message instead of a 500 error
    die('Errore critico: impossibile recuperare i dati dell\'account. <a href="user-auth.php">Effettua nuovamente il login</a> o contatta il supporto.');
}

// Check if specific business data is missing
$business_data_missing = !isset($user_data['business_name']) || empty($user_data['business_name']);

// Get current subscription and available packages
if ($database_available && !$business_data_missing) {
    $current_subscription = $db->getCurrentSubscription($business_id);
    $packages = $db->getAvailablePackages();
    $consumption_packages = $db->getConsumptionPackages(); // NUOVO
    $credit_balance = $db->getBusinessCreditBalance($business_id); // NUOVO
    $credit_usage_history = $db->getCreditUsageHistory($business_id, 20); // NUOVO
} else {
    $current_subscription = false;
    $packages = [];
    $consumption_packages = [];
    $credit_balance = 0;
    $credit_usage_history = [];
}


// Helper function to parse features JSON
function parseFeatures($features_json) {
    if (empty($features_json)) return [];
    $features = json_decode($features_json, true);
    return is_array($features) ? $features : [];
}

// Helper function to format date
// Questa funzione è già in config.php, ma la teniamo qui per sicurezza se quel file non la include
if (!function_exists('formatDate')) {
    function formatDate($date) {
        if (!$date) return 'N/A';
        return date('d/m/Y', strtotime($date));
    }
}

// Helper function to get status badge
function getStatusBadge($status) {
    $badges = [
        'active' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">🟢 Attivo</span>',
        'expiring' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">🟡 In Scadenza</span>',
        'expired' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">🔴 Scaduto</span>',
        'cancelled' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">⭕ Cancellato</span>',
        'pending' => '<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">⏳ In Attesa</span>'
    ];
    return $badges[$status] ?? htmlspecialchars($status);
}

// Calculate expiry status
if ($current_subscription && !empty($current_subscription['end_date'])) {
    $now = time();
    $expiry = strtotime($current_subscription['end_date']);
    $days_left = ($expiry - $now) / (60 * 60 * 24);

    if ($days_left < 0) {
        $current_subscription['computed_status'] = 'expired';
    } elseif ($days_left <= 30) {
        $current_subscription['computed_status'] = 'expiring';
    } else {
        $current_subscription['computed_status'] = 'active';
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Attività - <?php echo htmlspecialchars($business_data_missing ? $user_data['email'] : $user_data['business_name']); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="index.php" class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-sm">PC</span>
                    </a>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Passione Calabria</h1>
                        <p class="text-sm text-gray-500">Dashboard Attività</p>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="text-right">
                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($business_data_missing ? 'Configurazione Incompleta' : $user_data['business_name']); ?></p>
                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($user_data['email']); ?></p>
                    </div>
                    <form method="POST" class="inline">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="bg-gray-100 hover:bg-gray-200 px-3 py-2 rounded-lg text-sm font-medium text-gray-700 transition-colors">
                            Esci
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <?php if ($message): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                ✅ <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                ❌ <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <!-- Payment Success/Error Messages from Stripe -->
        <?php if (isset($_SESSION['payment_success'])): ?>
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
                ✅ <?php echo htmlspecialchars($_SESSION['payment_success']); unset($_SESSION['payment_success']); ?>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['payment_error'])): ?>
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
                ❌ <?php echo htmlspecialchars($_SESSION['payment_error']); unset($_SESSION['payment_error']); ?>
            </div>
        <?php endif; ?>

        <?php if ($business_data_missing): ?>
            <div class="mb-6 bg-yellow-100 border-l-4 border-yellow-500 text-yellow-800 p-6 rounded-r-lg">
                <div class="flex items-center">
                    <i data-lucide="alert-triangle" class="w-6 h-6 mr-3 text-yellow-500"></i>
                    <div>
                        <h3 class="text-lg font-semibold">Configurazione Account Incompleta</h3>
                        <p class="mt-1">I dati della tua attività non sono stati trovati. Questo può accadere se la registrazione non è andata a buon fine. Ti preghiamo di contattare l'assistenza per completare la configurazione.</p>
                        <p class="mt-2 text-sm">Email di supporto: <a href="mailto:<?php echo ADMIN_EMAIL; ?>" class="font-medium underline"><?php echo ADMIN_EMAIL; ?></a></p>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold flex items-center mb-4">
                            <i data-lucide="coins" class="w-5 h-5 mr-2 text-yellow-500"></i>
                            Stato Crediti
                        </h2>
                        <div class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white rounded-lg p-6 text-center">
                            <p class="text-lg font-medium opacity-80">Crediti Attualmente Disponibili</p>
                            <p class="text-6xl font-bold my-2"><?php echo number_format($credit_balance, 0, ',', '.'); ?></p>
                            <p class="opacity-80">Utilizza i crediti per promuovere la tua attività e ottenere più visibilità.</p>
                        </div>
                    </div>

                    <?php if (count($credit_usage_history) > 0): ?>
                    <!-- Storico Utilizzi Crediti -->
                    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200">
                            <h2 class="text-xl font-semibold flex items-center">
                                <i data-lucide="history" class="w-5 h-5 mr-2 text-blue-600"></i>
                                Storico Utilizzi Crediti
                                <span class="ml-2 text-sm font-normal text-gray-500">(Ultimi <?php echo count($credit_usage_history); ?>)</span>
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">Cronologia dei servizi erogati e dei crediti utilizzati</p>
                        </div>
                        
                        <div class="divide-y divide-gray-200 max-h-96 overflow-y-auto">
                            <?php foreach ($credit_usage_history as $usage): ?>
                            <div class="p-4 hover:bg-gray-50 transition-colors">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center mb-2">
                                            <div class="flex items-center">
                                                <?php if ($usage['service_type'] === 'manual_deduction'): ?>
                                                    <i data-lucide="user-check" class="w-4 h-4 text-green-600 mr-2"></i>
                                                    <span class="text-sm font-medium text-green-700 bg-green-100 px-2 py-1 rounded-full">Servizio Completato</span>
                                                <?php else: ?>
                                                    <i data-lucide="zap" class="w-4 h-4 text-orange-600 mr-2"></i>
                                                    <span class="text-sm font-medium text-orange-700 bg-orange-100 px-2 py-1 rounded-full"><?php echo ucfirst(str_replace('_', ' ', $usage['service_type'])); ?></span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="ml-4 text-sm text-gray-500">
                                                <?php echo date('d/m/Y H:i', strtotime($usage['used_at'])); ?>
                                            </div>
                                        </div>
                                        
                                        <div class="mb-2">
                                            <p class="text-gray-800 font-medium"><?php echo htmlspecialchars($usage['service_description']); ?></p>
                                        </div>
                                        
                                        <div class="flex items-center text-sm text-gray-600">
                                            <i data-lucide="package" class="w-4 h-4 mr-1"></i>
                                            <span>Pacchetto: <?php echo htmlspecialchars($usage['package_name']); ?></span>
                                            <?php if ($usage['package_purchased_at']): ?>
                                                <span class="mx-2">•</span>
                                                <span>Acquistato: <?php echo date('d/m/Y', strtotime($usage['package_purchased_at'])); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="text-right ml-4">
                                        <div class="text-lg font-bold text-red-600">-<?php echo $usage['credits_used']; ?></div>
                                        <div class="text-xs text-gray-500">crediti utilizzati</div>
                                        <?php if ($usage['current_remaining_credits'] !== null): ?>
                                            <div class="text-xs text-gray-400 mt-1">Rimanenti: <?php echo $usage['current_remaining_credits']; ?></div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="px-6 py-3 bg-gray-50 text-center">
                            <p class="text-sm text-gray-600">
                                <i data-lucide="info" class="w-4 h-4 inline mr-1"></i>
                                Visualizzati gli ultimi 20 utilizzi. Per assistenza: <strong><?php echo htmlspecialchars($contact_phone); ?></strong>
                            </p>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold flex items-center">
                                <i data-lucide="building-2" class="w-5 h-5 mr-2 text-blue-600"></i>
                                Stato Attività
                            </h2>
                            <?php
                            $business_status_badges = [
                                'pending' => '<span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-sm font-medium">⏳ In Attesa di Approvazione</span>',
                                'approved' => '<span class="px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">✅ Approvata</span>',
                                'rejected' => '<span class="px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">❌ Rifiutata</span>'
                            ];
                            echo $business_status_badges[$user_data['business_status'] ?? 'pending'] ?? 'pending';
                            ?>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 class="font-medium text-gray-900 mb-2">Informazioni Generali</h3>
                                <div class="space-y-2 text-sm">
                                    <p><strong>Nome:</strong> <?php echo htmlspecialchars($user_data['business_name']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user_data['email']); ?></p>
                                    <?php if ($user_data['phone']): ?>
                                        <p><strong>Telefono:</strong> <?php echo htmlspecialchars($user_data['phone']); ?></p>
                                    <?php endif; ?>
                                    <?php if ($user_data['website']): ?>
                                        <p><strong>Sito:</strong> <a href="<?php echo htmlspecialchars($user_data['website']); ?>" target="_blank" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($user_data['website']); ?></a></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <h3 class="font-medium text-gray-900 mb-2">Dettagli Account</h3>
                                <div class="space-y-2 text-sm">
                                    <p><strong>Registrato:</strong> <?php echo formatDate($user_data['business_created']); ?></p>
                                    <p><strong>Ultimo Accesso:</strong> <?php echo $user_data['last_login'] ? formatDate($user_data['last_login']) : 'Primo accesso'; ?></p>
                                </div>
                            </div>
                        </div>

                        <?php if ($user_data['description']): ?>
                            <div class="mt-4">
                                <h3 class="font-medium text-gray-900 mb-2">Descrizione</h3>
                                <p class="text-sm text-gray-600"><?php echo htmlspecialchars($user_data['description']); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold flex items-center mb-6">
                            <i data-lucide="credit-card" class="w-5 h-5 mr-2 text-green-600"></i>
                            Abbonamento Attuale
                        </h2>

                        <?php if ($current_subscription && isset($current_subscription['package_name'])): ?>
                            <div class="border border-gray-200 rounded-lg p-4 mb-6">
                                <div class="flex justify-between items-start mb-4">
                                    <div>
                                        <h3 class="font-semibold text-lg text-gray-900"><?php echo htmlspecialchars($current_subscription['package_name']); ?></h3>
                                        <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($current_subscription['package_description']); ?></p>
                                    </div>
                                    <div class="text-right">
                                        <?php echo getStatusBadge($current_subscription['computed_status']); ?>
                                        <?php if ($current_subscription['package_price'] > 0): ?>
                                            <p class="text-lg font-bold text-gray-900 mt-2">€<?php echo number_format($current_subscription['package_price'], 2); ?>/anno</p>
                                        <?php else: ?>
                                            <p class="text-lg font-bold text-green-600 mt-2">Gratuito</p>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <p class="text-sm text-gray-500">Data Inizio</p>
                                        <p class="font-medium"><?php echo formatDate($current_subscription['start_date']); ?></p>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-500">Data Scadenza</p>
                                        <p class="font-medium <?php echo ($current_subscription['computed_status'] ?? '') === 'expiring' ? 'text-orange-600' : ''; ?>">
                                            <?php echo formatDate($current_subscription['end_date']); ?>
                                        </p>
                                    </div>
                                </div>

                                <?php if (!empty($current_subscription['features'])): ?>
                                    <div>
                                        <p class="text-sm text-gray-500 mb-2">Funzionalità Incluse</p>
                                        <div class="space-y-1">
                                            <?php foreach (parseFeatures($current_subscription['features']) as $feature): ?>
                                                <div class="flex items-center text-sm">
                                                    <i data-lucide="check" class="w-4 h-4 text-green-500 mr-2"></i>
                                                    <?php echo htmlspecialchars($feature); ?>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if (($current_subscription['computed_status'] ?? '') === 'expiring'): ?>
                                <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                                    <div class="flex items-center">
                                        <i data-lucide="clock" class="w-5 h-5 text-orange-600 mr-2"></i>
                                        <div>
                                            <h3 class="font-medium text-orange-900">Abbonamento in Scadenza</h3>
                                            <p class="text-sm text-orange-700 mt-1">Il tuo abbonamento scade il <?php echo formatDate($current_subscription['end_date']); ?>. Rinnova o aggiorna il tuo piano per continuare a godere di tutti i vantaggi.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php elseif (($current_subscription['computed_status'] ?? '') === 'expired'): ?>
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                                    <div class="flex items-center">
                                        <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 mr-2"></i>
                                        <div>
                                            <h3 class="font-medium text-red-900">Abbonamento Scaduto</h3>
                                            <p class="text-sm text-red-700 mt-1">Il tuo abbonamento è scaduto. Rinnova o scegli un nuovo piano per ripristinare l'accesso a tutte le funzionalità.</p>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php else: ?>
                            <div class="text-center py-8">
                                <i data-lucide="credit-card" class="w-12 h-12 text-gray-300 mx-auto mb-4"></i>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Nessun Abbonamento Attivo</h3>
                                <p class="text-gray-500">Scegli un piano per iniziare a godere di tutti i vantaggi di Passione Calabria.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="space-y-8">
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="zap" class="w-5 h-5 mr-2 text-yellow-600"></i>
                            Azioni Rapide
                        </h3>
                        <div class="space-y-3">
                            <a href="index.php" class="block w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 px-4 rounded-lg transition-colors">
                                🏠 Vai al Sito
                            </a>
                            <a href="user-profile.php" class="block w-full bg-gray-100 hover:bg-gray-200 text-gray-700 text-center py-2 px-4 rounded-lg transition-colors">
                                ⚙️ Modifica Profilo
                            </a>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="package" class="w-5 h-5 mr-2 text-purple-600"></i>
                            Piani e Pacchetti
                        </h3>
                        
                        <div class="mb-6">
                             <h4 class="text-md font-medium text-gray-800 mb-3">Piani di Abbonamento</h4>
                             <div class="space-y-3">
                                <?php 
                                $packages_shown = 0;
                                $max_packages_shown = 3;
                                foreach ($packages as $package): 
                                    if ($packages_shown >= $max_packages_shown) break;
                                    $packages_shown++;
                                ?>
                                    <?php
                                    $is_current = $current_subscription && $current_subscription['package_id'] == $package['id'];
                                    $is_upgrade = $current_subscription && $package['price'] > $current_subscription['package_price'];
                                    ?>
                                    <div class="border <?php echo $is_current ? 'border-green-500 bg-green-50' : 'border-gray-200'; ?> rounded-lg p-3">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="font-medium text-sm"><?php echo htmlspecialchars($package['name']); ?></h4>
                                            <span class="text-sm font-bold <?php echo $package['price'] > 0 ? 'text-gray-900' : 'text-green-600'; ?>">
                                                <?php echo $package['price'] > 0 ? '€' . number_format($package['price'], 2) : 'Gratuito'; ?>
                                            </span>
                                        </div>
                                        <p class="text-xs text-gray-600 mb-3"><?php echo htmlspecialchars($package['description']); ?></p>
                                        
                                        <?php if ($is_current): ?>
                                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Piano Attuale</span>
                                        <?php else: ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="<?php echo $is_upgrade ? 'upgrade' : 'downgrade'; ?>">
                                                <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                                <button type="submit"
                                                        class="text-xs bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition-colors"
                                                        onclick="return confirm('Sei sicuro di voler <?php echo $is_upgrade ? 'aggiornare' : 'cambiare'; ?> il tuo piano a <?php echo htmlspecialchars($package['name']); ?>?')">
                                                    <?php echo $is_upgrade ? 'Upgrade' : 'Cambia Piano'; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($packages) > $max_packages_shown): ?>
                                    <div class="border border-dashed border-gray-300 rounded-lg p-3 text-center">
                                        <a href="pacchetti-disponibili.php" class="text-sm text-blue-600 hover:text-blue-800 font-medium flex items-center justify-center">
                                            <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                                            Mostra Tutti i Piani (<?php echo count($packages); ?> disponibili)
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div>
                             <h4 class="text-md font-medium text-gray-800 mb-3">Pacchetti Crediti</h4>
                             <div class="space-y-3">
                                <?php if (empty($consumption_packages)): ?>
                                    <p class="text-sm text-gray-500">Nessun pacchetto crediti disponibile al momento.</p>
                                <?php else: ?>
                                    <?php 
                                    $credit_packages_shown = 0;
                                    $max_credit_packages_shown = 3;
                                    foreach ($consumption_packages as $package): 
                                        if ($credit_packages_shown >= $max_credit_packages_shown) break;
                                        $credit_packages_shown++;
                                    ?>
                                        <div class="border border-gray-200 rounded-lg p-3">
                                            <div class="flex justify-between items-start mb-2">
                                                <h5 class="font-medium text-sm"><?php echo htmlspecialchars($package['name']); ?> (+<?php echo $package['consumption_credits']; ?> crediti)</h5>
                                                <span class="text-sm font-bold text-gray-900">€<?php echo number_format($package['price'], 2); ?></span>
                                            </div>
                                            <p class="text-xs text-gray-600 mb-3"><?php echo htmlspecialchars($package['description']); ?></p>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="action" value="buy_credits">
                                                <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                                <button type="submit" class="w-full text-xs bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-2 rounded transition-colors" onclick="return confirm('Sei sicuro di voler acquistare questo pacchetto di crediti?')">
                                                    Acquista Crediti
                                                </button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (count($consumption_packages) > $max_credit_packages_shown): ?>
                                        <div class="border border-dashed border-gray-300 rounded-lg p-3 text-center">
                                            <a href="pacchetti-disponibili.php" class="text-sm text-yellow-600 hover:text-yellow-800 font-medium flex items-center justify-center">
                                                <i data-lucide="external-link" class="w-4 h-4 mr-2"></i>
                                                Mostra Tutti i Pacchetti Crediti (<?php echo count($consumption_packages); ?> disponibili)
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg p-6">
                        <h3 class="font-semibold mb-2 flex items-center">
                            <i data-lucide="phone" class="w-5 h-5 mr-2"></i>
                            Assistenza
                        </h3>
                        <p class="text-sm text-blue-100 mb-3">
                            <?php echo htmlspecialchars($contact_text); ?>
                        </p>
                        <p class="font-bold text-lg"><?php echo htmlspecialchars($contact_phone); ?></p>
                        <p class="text-xs text-blue-100 mt-2"><?php echo htmlspecialchars($contact_hours); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <script src="assets/js/main.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();
        
        // Add some interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth animations for status changes
            const statusElements = document.querySelectorAll('[class*="bg-green-100"], [class*="bg-orange-100"], [class*="bg-red-100"]');
            statusElements.forEach(el => {
                el.style.transition = 'all 0.3s ease';
            });
        });
    </script>
</body>
</html>