<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';

// Verifica se l'utente è loggato come admin
// requireLogin(); // Disabilitato per ora

$db = new Database();
$success = '';
$error = '';

// Gestione form di configurazione Stripe
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_stripe_config') {
    try {
        $publishableKey = sanitize($_POST['stripe_publishable_key'] ?? '');
        $secretKey = sanitize($_POST['stripe_secret_key'] ?? '');
        
        // Valida le chiavi
        if (empty($publishableKey) || empty($secretKey)) {
            throw new Exception('Entrambe le chiavi Stripe sono obbligatorie');
        }
        
        if (!str_starts_with($publishableKey, 'pk_')) {
            throw new Exception('La chiave pubblicabile deve iniziare con pk_');
        }
        
        if (!str_starts_with($secretKey, 'sk_')) {
            throw new Exception('La chiave segreta deve iniziare con sk_');
        }
        
        // Salva le impostazioni
        $db->setSetting('stripe_publishable_key', $publishableKey, 'text');
        $db->setSetting('stripe_secret_key', $secretKey, 'password');
        
        $success = 'Configurazione Stripe salvata con successo!';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Carica impostazioni attuali
$currentPublishableKey = $db->getSetting('stripe_publishable_key') ?? '';
$currentSecretKey = $db->getSetting('stripe_secret_key') ?? '';
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurazione Stripe - Admin Panel</title>
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
            <h1 class="text-2xl font-bold text-gray-900">Configurazione Stripe</h1>
        </header>
        <main class="flex-1 overflow-auto p-6">
            <!-- Status Messages -->
            <?php if ($success): ?>
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <i data-lucide="check-circle" class="w-5 h-5 mr-2"></i>
                    <?php echo htmlspecialchars($success); ?>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded mb-6">
                <div class="flex items-center">
                    <i data-lucide="alert-circle" class="w-5 h-5 mr-2"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Configuration Form -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">
                        <i data-lucide="credit-card" class="w-5 h-5 inline mr-2"></i>
                        Configurazione Stripe
                    </h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Configura le chiavi API di Stripe per abilitare i pagamenti sui pacchetti premium.
                    </p>
                </div>

                <form method="POST" action="stripe-config.php" class="p-6">
                    <input type="hidden" name="action" value="save_stripe_config">
                    
                    <div class="space-y-6">
                        <!-- Stripe Publishable Key -->
                        <div>
                            <label for="stripe_publishable_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Chiave Pubblicabile Stripe
                                <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="text" 
                                id="stripe_publishable_key" 
                                name="stripe_publishable_key"
                                value="<?php echo htmlspecialchars($currentPublishableKey); ?>"
                                placeholder="pk_test_..." 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required>
                            <p class="text-xs text-gray-500 mt-1">
                                La chiave pubblicabile inizia con "pk_" ed è utilizzata nel frontend
                            </p>
                        </div>

                        <!-- Stripe Secret Key -->
                        <div>
                            <label for="stripe_secret_key" class="block text-sm font-medium text-gray-700 mb-2">
                                Chiave Segreta Stripe
                                <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input 
                                    type="password" 
                                    id="stripe_secret_key" 
                                    name="stripe_secret_key"
                                    value="<?php echo htmlspecialchars($currentSecretKey); ?>"
                                    placeholder="sk_test_..." 
                                    class="w-full px-3 py-2 pr-10 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required>
                                <button 
                                    type="button" 
                                    onclick="togglePasswordVisibility()"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                    <i data-lucide="eye" id="eye-icon" class="w-4 h-4"></i>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                La chiave segreta inizia con "sk_" ed è utilizzata nel backend per processare i pagamenti
                            </p>
                        </div>

                        <!-- Warning Box -->
                        <div class="bg-amber-50 border border-amber-200 rounded-md p-4">
                            <div class="flex items-start">
                                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-600 mr-2 mt-0.5"></i>
                                <div class="text-sm">
                                    <h3 class="font-medium text-amber-800 mb-1">Importante - Sicurezza</h3>
                                    <ul class="text-amber-700 space-y-1">
                                        <li>• Non condividere mai le chiavi API di Stripe</li>
                                        <li>• Per la produzione, usa le chiavi live (pk_live_ e sk_live_)</li>
                                        <li>• Per il test, usa le chiavi test (pk_test_ e sk_test_)</li>
                                        <li>• Configura i webhook di Stripe sul tuo dominio</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Instructions -->
                        <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                            <div class="flex items-start">
                                <i data-lucide="info" class="w-5 h-5 text-blue-600 mr-2 mt-0.5"></i>
                                <div class="text-sm text-blue-800">
                                    <h3 class="font-medium mb-1">Come ottenere le chiavi Stripe:</h3>
                                    <ol class="list-decimal list-inside space-y-1">
                                        <li>Vai su <a href="https://stripe.com" target="_blank" class="underline hover:no-underline">stripe.com</a> e registra un account</li>
                                        <li>Accedi alla Dashboard di Stripe</li>
                                        <li>Vai su "Developers" → "API keys"</li>
                                        <li>Copia la "Publishable key" e la "Secret key"</li>
                                        <li>Incolla qui le chiavi e salva</li>
                                    </ol>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex justify-end pt-6 border-t border-gray-200 mt-6">
                        <button 
                            type="submit" 
                            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 flex items-center">
                            <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                            Salva Configurazione
                        </button>
                    </div>
                </form>
            </div>

            <!-- Current Status -->
            <div class="bg-white rounded-lg shadow mt-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Status Configurazione</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex items-center p-3 bg-gray-50 rounded">
                            <?php if ($currentPublishableKey): ?>
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mr-2"></i>
                                <span class="text-green-700">Chiave Pubblicabile: Configurata</span>
                            <?php else: ?>
                                <i data-lucide="x-circle" class="w-5 h-5 text-red-500 mr-2"></i>
                                <span class="text-red-700">Chiave Pubblicabile: Non configurata</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="flex items-center p-3 bg-gray-50 rounded">
                            <?php if ($currentSecretKey): ?>
                                <i data-lucide="check-circle" class="w-5 h-5 text-green-500 mr-2"></i>
                                <span class="text-green-700">Chiave Segreta: Configurata</span>
                            <?php else: ?>
                                <i data-lucide="x-circle" class="w-5 h-5 text-red-500 mr-2"></i>
                                <span class="text-red-700">Chiave Segreta: Non configurata</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($currentPublishableKey && $currentSecretKey): ?>
                    <div class="mt-4 p-3 bg-green-50 border border-green-200 rounded">
                        <p class="text-green-800">
                            <i data-lucide="check-circle" class="w-4 h-4 inline mr-1"></i>
                            Stripe è configurato e pronto per processare i pagamenti!
                        </p>
                    </div>
                    <?php else: ?>
                    <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded">
                        <p class="text-yellow-800">
                            <i data-lucide="alert-triangle" class="w-4 h-4 inline mr-1"></i>
                            Configura entrambe le chiavi per abilitare i pagamenti Stripe.
                        </p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Toggle password visibility
        function togglePasswordVisibility() {
            const passwordField = document.getElementById('stripe_secret_key');
            const eyeIcon = document.getElementById('eye-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.setAttribute('data-lucide', 'eye-off');
            } else {
                passwordField.type = 'password';
                eyeIcon.setAttribute('data-lucide', 'eye');
            }
            
            lucide.createIcons();
        }
    </script>
</body>
</html>