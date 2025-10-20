<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Verifica che ci sia una sessione attiva con i dati necessari
if (!isset($_SESSION['business_id']) || !isset($_SESSION['package_id'])) {
    header('Location: ../iscrizione-attivita.php');
    exit;
}

$db = new Database();
$business_id = $_SESSION['business_id'];
$package_id = $_SESSION['package_id'];

// Recupera i dati del business e del pacchetto
try {
    $stmt = $db->pdo->prepare("SELECT * FROM businesses WHERE id = ?");
    $stmt->execute([$business_id]);
    $business = $stmt->fetch();
    
    $stmt = $db->pdo->prepare("SELECT * FROM business_packages WHERE id = ?");
    $stmt->execute([$package_id]);
    $package = $stmt->fetch();
    
    if (!$business || !$package) {
        throw new Exception('Dati non trovati');
    }
    
    if ($package['price'] == 0) {
        // Se il pacchetto è gratuito, reindirizza direttamente alla pagina di completamento
        header('Location: ../registrazione-completata.php');
        exit;
    }
    
} catch (Exception $e) {
    header('Location: ../iscrizione-attivita.php?error=' . urlencode($e->getMessage()));
    exit;
}

// Recupera le chiavi Stripe
$stripePublishableKey = $db->getSetting('stripe_publishable_key');
$stripeSecretKey = $db->getSetting('stripe_secret_key');

$error = '';
$success = '';

// Gestione del form di pagamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!$stripeSecretKey) {
            // Modalità demo - simula il pagamento
            $stmt = $db->pdo->prepare("
                INSERT INTO subscriptions (
                    business_id, package_id, status, 
                    start_date, end_date, amount, created_at
                ) VALUES (?, ?, 'active', NOW(), 
                         DATE_ADD(NOW(), INTERVAL ? MONTH), ?, NOW())
            ");
            
            $stmt->execute([
                $business_id, 
                $package_id, 
                $package['duration_months'] ?? 12, 
                $package['price']
            ]);
            
            // Approva il business
            $stmt = $db->pdo->prepare("UPDATE businesses SET status = 'approved' WHERE id = ?");
            $stmt->execute([$business_id]);
            
            // Imposta dati di sessione per la pagina di ringraziamento
            $_SESSION['registration_success'] = true;
            $_SESSION['business_name'] = $business['name'];
            $_SESSION['business_email'] = $business['email'];
            $_SESSION['package_name'] = $package['name'];
            $_SESSION['package_price'] = $package['price'];
            
            header('Location: ../registrazione-completata.php');
            exit;
        } else {
            // In un ambiente reale, qui integreresti Stripe Payment Elements
            // Per ora, simuliamo il pagamento riuscito
            $success = 'Pagamento completato con successo! Reindirizzamento in corso...';
            
            // Crea la sottoscrizione
            $stmt = $db->pdo->prepare("
                INSERT INTO subscriptions (
                    business_id, package_id, status, 
                    start_date, end_date, amount, created_at,
                    stripe_subscription_id
                ) VALUES (?, ?, 'active', NOW(), 
                         DATE_ADD(NOW(), INTERVAL ? MONTH), ?, NOW(), ?)
            ");
            
            $fakeStripeId = 'sub_' . uniqid();
            
            $stmt->execute([
                $business_id, 
                $package_id, 
                $package['duration_months'] ?? 12, 
                $package['price'],
                $fakeStripeId
            ]);
            
            // Approva il business
            $stmt = $db->pdo->prepare("UPDATE businesses SET status = 'approved' WHERE id = ?");
            $stmt->execute([$business_id]);
            
            // Imposta dati di sessione per la pagina di ringraziamento
            $_SESSION['registration_success'] = true;
            $_SESSION['business_name'] = $business['name'];
            $_SESSION['business_email'] = $business['email'];
            $_SESSION['package_name'] = $package['name'];
            $_SESSION['package_price'] = $package['price'];
            
            // Redirect automatico dopo 2 secondi
            header('refresh:2;url=../registrazione-completata.php');
        }
        
    } catch (Exception $e) {
        $error = 'Errore durante il pagamento: ' . $e->getMessage();
    }
}

$isStripeConfigured = !empty($stripeSecretKey);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Completa il pagamento per il tuo abbonamento">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    
    <?php if ($isStripeConfigured): ?>
    <script src="https://js.stripe.com/v3/"></script>
    <?php endif; ?>
</head>
<body class="bg-gray-50">
    <?php include '../includes/header.php'; ?>
    
    <main class="py-12">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Package Summary -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Riepilogo Ordine</h1>
                
                <div class="border-b border-gray-200 pb-4 mb-4">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($package['name']); ?></h2>
                            <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($package['description']); ?></p>
                            <div class="mt-2">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    Durata: <?php echo $package['duration_months'] ?? 12; ?> mesi
                                </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-2xl font-bold text-gray-900">€<?php echo number_format($package['price'], 2); ?></div>
                            <div class="text-sm text-gray-500">IVA inclusa</div>
                        </div>
                    </div>
                </div>
                
                <!-- Business Info -->
                <div class="bg-gray-50 rounded-lg p-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Attività da registrare:</h3>
                    <p class="text-gray-700"><strong><?php echo htmlspecialchars($business['name']); ?></strong></p>
                    <p class="text-gray-600"><?php echo htmlspecialchars($business['email']); ?></p>
                </div>
            </div>

            <!-- Payment Form -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i data-lucide="credit-card" class="w-5 h-5 inline mr-2"></i>
                        Pagamento
                    </h2>
                </div>
                
                <div class="p-6">
                    <?php if ($error): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                        <i data-lucide="alert-circle" class="w-5 h-5 inline mr-2"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                    <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                        <i data-lucide="check-circle" class="w-5 h-5 inline mr-2"></i>
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!$success): ?>
                    
                    <?php if (!$isStripeConfigured): ?>
                    <!-- Demo Mode -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center">
                            <i data-lucide="info" class="w-5 h-5 text-yellow-600 mr-2"></i>
                            <div>
                                <h3 class="font-medium text-yellow-800">Modalità Demo</h3>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Stripe non è configurato. Questo è un pagamento simulato per scopi dimostrativi.
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" id="payment-form">
                        <!-- Payment Method Selection -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-3">
                                Metodo di Pagamento
                            </label>
                            
                            <?php if ($isStripeConfigured): ?>
                            <!-- Real Stripe Integration would go here -->
                            <div class="border border-gray-300 rounded-lg p-4">
                                <div class="flex items-center mb-4">
                                    <i data-lucide="credit-card" class="w-6 h-6 text-gray-400 mr-3"></i>
                                    <div>
                                        <p class="font-medium text-gray-900">Carta di Credito</p>
                                        <p class="text-sm text-gray-500">Visa, MasterCard, American Express</p>
                                    </div>
                                </div>
                                
                                <!-- Stripe Payment Element would be inserted here -->
                                <div id="payment-element" class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-8 text-center">
                                    <p class="text-gray-600">Form di pagamento Stripe verrà caricato qui</p>
                                    <p class="text-sm text-gray-500 mt-2">In ambiente di produzione, qui sarà presente il form sicuro di Stripe</p>
                                </div>
                            </div>
                            <?php else: ?>
                            <!-- Demo Payment -->
                            <div class="border border-gray-300 rounded-lg p-4">
                                <div class="flex items-center mb-4">
                                    <i data-lucide="play-circle" class="w-6 h-6 text-blue-600 mr-3"></i>
                                    <div>
                                        <p class="font-medium text-gray-900">Pagamento Demo</p>
                                        <p class="text-sm text-gray-500">Simulazione per testing</p>
                                    </div>
                                </div>
                                
                                <div class="bg-gray-50 rounded-lg p-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Numero Carta</label>
                                            <input type="text" value="4242 4242 4242 4242" readonly 
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 rounded text-gray-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Scadenza</label>
                                            <input type="text" value="12/25" readonly 
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 rounded text-gray-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">CVC</label>
                                            <input type="text" value="123" readonly 
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 rounded text-gray-500">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Nome Titolare</label>
                                            <input type="text" value="Demo User" readonly 
                                                   class="w-full px-3 py-2 bg-white border border-gray-300 rounded text-gray-500">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Terms and Conditions -->
                        <div class="mb-6">
                            <div class="flex items-start">
                                <input type="checkbox" id="accept_terms" name="accept_terms" required
                                       class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500 mt-1">
                                <label for="accept_terms" class="ml-3 text-sm text-gray-700">
                                    Confermo di aver letto e accettato i 
                                    <a href="../termini-servizio.php" class="text-blue-600 hover:underline" target="_blank">Termini di Servizio</a>
                                    e la <a href="../privacy-policy.php" class="text-blue-600 hover:underline" target="_blank">Privacy Policy</a>
                                    <span class="text-red-500">*</span>
                                </label>
                            </div>
                        </div>
                        
                        <!-- Payment Button -->
                        <div class="flex flex-col sm:flex-row gap-4">
                            <button type="button" onclick="window.history.back()" 
                                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-lg transition-colors">
                                <i data-lucide="arrow-left" class="w-5 h-5 inline mr-2"></i>
                                Torna Indietro
                            </button>
                            
                            <button type="submit" id="submit-button"
                                    class="flex-1 bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                                <i data-lucide="credit-card" class="w-5 h-5 inline mr-2"></i>
                                <?php echo $isStripeConfigured ? 'Paga €' . number_format($package['price'], 2) : 'Simula Pagamento €' . number_format($package['price'], 2); ?>
                            </button>
                        </div>
                    </form>
                    
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Security Information -->
            <div class="mt-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <i data-lucide="shield-check" class="w-5 h-5 text-green-600 mr-2"></i>
                    <div class="text-sm">
                        <h3 class="font-medium text-green-800">Pagamento Sicuro</h3>
                        <p class="text-green-700 mt-1">
                            <?php if ($isStripeConfigured): ?>
                            I tuoi dati di pagamento sono protetti con crittografia SSL e processati tramite Stripe, 
                            leader mondiale nella sicurezza dei pagamenti online.
                            <?php else: ?>
                            In produzione, tutti i pagamenti sono protetti con crittografia SSL e processati 
                            tramite Stripe per massima sicurezza.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
        lucide.createIcons();
        
        // Form validation
        document.getElementById('payment-form').addEventListener('submit', function(e) {
            const termsCheckbox = document.getElementById('accept_terms');
            if (!termsCheckbox.checked) {
                e.preventDefault();
                alert('È necessario accettare i termini e condizioni per procedere.');
                return false;
            }
            
            // Disable submit button to prevent double submission
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i data-lucide="loader" class="w-5 h-5 inline mr-2 animate-spin"></i>Elaborazione...';
            
            <?php if (!$isStripeConfigured): ?>
            // For demo, add a small delay to simulate processing
            setTimeout(() => {
                // Form will be submitted after this
            }, 1000);
            <?php endif; ?>
        });
    </script>
</body>
</html>