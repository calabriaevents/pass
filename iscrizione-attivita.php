<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

$db = new Database();

// Get available packages
$subscriptionPackages = [];
$consumptionPackages = [];

try {
    // Get subscription packages
    $stmt = $db->pdo->prepare('SELECT * FROM business_packages WHERE package_type = "subscription" AND is_active = 1 ORDER BY id ASC');
    $stmt->execute();
    $subscriptionPackages = $stmt->fetchAll();
    
    // Get consumption packages
    $stmt = $db->pdo->prepare('SELECT * FROM business_packages WHERE package_type = "consumption" AND is_active = 1 ORDER BY id ASC');
    $stmt->execute();
    $consumptionPackages = $stmt->fetchAll();
} catch (Exception $e) {
    $error = "Errore nel caricamento dei pacchetti: " . $e->getMessage();
}

// Get categories for the info section
$categories = $db->getCategories();
$provinces = $db->getProvinces();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iscrivi la tua Attività - Passione Calabria</title>
    <meta name="description" content="Registra la tua attività su Passione Calabria. Scegli tra i nostri pacchetti di abbonamento e servizi a consumo per dare massima visibilità alla tua attività in Calabria.">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        'calabria-blue': '#2563eb',
                        'calabria-gold': '#f59e0b',
                        'calabria-teal': '#14b8a6'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-calabria-blue via-calabria-teal to-calabria-gold text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="text-center">
                <h1 class="text-4xl md:text-6xl font-bold mb-6">
                    Porta la tua attività <span class="text-yellow-300">in Calabria</span>
                </h1>
                <p class="text-xl md:text-2xl mb-8 text-blue-100 max-w-3xl mx-auto">
                    Raggiungi migliaia di turisti e calabresi che cercano esperienze autentiche. 
                    Scegli il piano perfetto per la tua attività.
                </p>
                <div class="flex flex-wrap justify-center items-center gap-8 text-sm md:text-base">
                    <div class="flex items-center">
                        <i data-lucide="users" class="w-5 h-5 mr-2"></i>
                        <span>+50.000 visitatori/mese</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="map-pin" class="w-5 h-5 mr-2"></i>
                        <span><?php echo count($provinces); ?> province coperte</span>
                    </div>
                    <div class="flex items-center">
                        <i data-lucide="star" class="w-5 h-5 mr-2"></i>
                        <span><?php echo count($categories); ?> categorie</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Packages Section -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <?php if (isset($error)): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-8 rounded">
            <p class="font-medium"><?php echo htmlspecialchars($error); ?></p>
        </div>
        <?php endif; ?>

        <!-- Subscription Packages -->
        <?php if (!empty($subscriptionPackages)): ?>
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    📅 Piani di Abbonamento
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Soluzioni mensili e annuali per dare visibilità costante alla tua attività
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($subscriptionPackages as $package): 
                    $features = json_decode($package['features'], true) ?: [];
                    $isPopular = $package['name'] === 'Business';
                ?>
                <div class="relative bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300 <?php echo $isPopular ? 'ring-2 ring-calabria-blue transform scale-105' : ''; ?>">
                    <?php if ($isPopular): ?>
                    <div class="absolute top-0 left-1/2 transform -translate-x-1/2 -translate-y-1/2">
                        <div class="bg-calabria-blue text-white px-4 py-1 rounded-full text-sm font-semibold">
                            🔥 Più Scelto
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="px-8 py-10">
                        <!-- Package Header -->
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($package['description']); ?></p>
                            
                            <div class="mb-6">
                                <?php if ($package['price'] == 0): ?>
                                <div class="text-4xl font-bold text-gray-900">Gratuito</div>
                                <div class="text-gray-500">Per sempre</div>
                                <?php else: ?>
                                <div class="text-4xl font-bold text-gray-900">
                                    €<?php echo number_format($package['price'], 0); ?>
                                    <span class="text-xl text-gray-500 font-normal">/anno</span>
                                </div>
                                <div class="text-gray-500">Rinnovo automatico</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Features -->
                        <?php if (!empty($features)): ?>
                        <div class="mb-8">
                            <ul class="space-y-3">
                                <?php foreach ($features as $feature): ?>
                                <li class="flex items-start">
                                    <i data-lucide="check" class="w-5 h-5 text-green-500 mr-3 mt-0.5 flex-shrink-0"></i>
                                    <span class="text-gray-700"><?php echo htmlspecialchars($feature); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- CTA Button -->
                        <button onclick="selectPackage(<?php echo $package['id']; ?>, 'subscription')" 
                                class="w-full bg-gradient-to-r <?php echo $package['price'] == 0 ? 'from-gray-600 to-gray-700' : ($isPopular ? 'from-calabria-blue to-blue-700' : 'from-calabria-teal to-teal-700'); ?> text-white py-4 rounded-xl font-semibold text-lg hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                            <?php echo $package['price'] == 0 ? 'Inizia Gratis' : 'Scegli ' . htmlspecialchars($package['name']); ?>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Consumption Packages -->
        <?php if (!empty($consumptionPackages)): ?>
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    ⚡ Pacchetti a Consumo
                </h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">
                    Acquista crediti per promozioni e servizi premium senza abbonamento mensile
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($consumptionPackages as $package): 
                    $features = json_decode($package['features'], true) ?: [];
                    $creditsValue = $package['consumption_credits'] ?? 0;
                    $pricePerCredit = $creditsValue > 0 ? ($package['price'] / $creditsValue) : 0;
                ?>
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition-all duration-300">
                    <div class="px-8 py-10">
                        <!-- Package Header -->
                        <div class="text-center mb-8">
                            <h3 class="text-2xl font-bold text-gray-900 mb-2"><?php echo htmlspecialchars($package['name']); ?></h3>
                            <p class="text-gray-600 mb-6"><?php echo htmlspecialchars($package['description']); ?></p>
                            
                            <div class="mb-4">
                                <div class="text-4xl font-bold text-gray-900">
                                    €<?php echo number_format($package['price'], 0); ?>
                                </div>
                                <div class="text-calabria-gold font-semibold text-lg">
                                    <?php echo $creditsValue; ?> crediti
                                </div>
                                <div class="text-sm text-gray-500">
                                    €<?php echo number_format($pricePerCredit, 2); ?> per credito
                                </div>
                            </div>
                        </div>

                        <!-- Features -->
                        <?php if (!empty($features)): ?>
                        <div class="mb-8">
                            <ul class="space-y-3">
                                <?php foreach ($features as $feature): ?>
                                <li class="flex items-start">
                                    <i data-lucide="zap" class="w-5 h-5 text-calabria-gold mr-3 mt-0.5 flex-shrink-0"></i>
                                    <span class="text-gray-700"><?php echo htmlspecialchars($feature); ?></span>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>

                        <!-- CTA Button -->
                        <button onclick="selectPackage(<?php echo $package['id']; ?>, 'consumption')" 
                                class="w-full bg-gradient-to-r from-calabria-gold to-yellow-600 text-white py-4 rounded-xl font-semibold text-lg hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                            Acquista Crediti
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Benefits Section -->
        <div class="bg-white rounded-3xl shadow-xl p-8 md:p-12 mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Perché scegliere Passione Calabria?
                </h2>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-calabria-blue rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="eye" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Massima Visibilità</h3>
                    <p class="text-gray-600">La tua attività sarà vista da migliaia di potenziali clienti ogni mese</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-calabria-teal rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="target" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Target Locale</h3>
                    <p class="text-gray-600">Raggiungi turisti e residenti interessati alla Calabria</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-calabria-gold rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="trending-up" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Crescita Business</h3>
                    <p class="text-gray-600">Aumenta prenotazioni e vendite con la nostra piattaforma</p>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="headphones" class="w-8 h-8 text-white"></i>
                    </div>
                    <h3 class="text-xl font-semibold mb-3">Supporto Dedicato</h3>
                    <p class="text-gray-600">Il nostro team ti aiuta a ottimizzare la tua presenza</p>
                </div>
            </div>
        </div>

        <!-- FAQ Section -->
        <div class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Domande Frequenti
                </h2>
            </div>
            
            <div class="max-w-4xl mx-auto">
                <div class="space-y-6">
                    <div class="bg-white rounded-xl p-6 shadow-md">
                        <h3 class="text-lg font-semibold mb-3 text-gray-900">Come funziona l'iscrizione?</h3>
                        <p class="text-gray-600">Scegli il pacchetto che preferisci, completa i dati della tua attività e sarai subito online. Per i piani gratuiti l'attivazione è immediata.</p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-6 shadow-md">
                        <h3 class="text-lg font-semibold mb-3 text-gray-900">Posso cambiare piano in qualsiasi momento?</h3>
                        <p class="text-gray-600">Sì, puoi fare upgrade o downgrade del tuo piano quando vuoi. I pacchetti a consumo possono essere acquistati anche insieme agli abbonamenti.</p>
                    </div>
                    
                    <div class="bg-white rounded-xl p-6 shadow-md">
                        <h3 class="text-lg font-semibold mb-3 text-gray-900">Come funzionano i crediti?</h3>
                        <p class="text-gray-600">I crediti si usano per servizi premium come evidenziare la tua attività, boost nelle ricerche, posizioni privilegiate. Non scadono e li usi quando vuoi.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- CTA Section -->
    <div class="bg-gradient-to-r from-calabria-blue to-calabria-teal text-white py-16">
        <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl md:text-4xl font-bold mb-6">
                Pronto a far crescere la tua attività?
            </h2>
            <p class="text-xl mb-8 text-blue-100">
                Inizia oggi stesso e raggiungi migliaia di potenziali clienti in Calabria
            </p>
            <button onclick="document.getElementById('packages').scrollIntoView({behavior: 'smooth'})" 
                    class="bg-white text-calabria-blue px-8 py-4 rounded-full font-semibold text-lg hover:shadow-lg transition-all">
                Scegli il Tuo Piano
            </button>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script src="assets/js/main.js"></script>
    <script>
        // Initialize Lucide icons
        lucide.createIcons();

        // Package selection handler
        function selectPackage(packageId, packageType) {
            // Store selected package in sessionStorage
            sessionStorage.setItem('selectedPackageId', packageId);
            sessionStorage.setItem('selectedPackageType', packageType);
            
            // Redirect to business data form
            window.location.href = 'dati-attivita.php?package_id=' + packageId + '&type=' + packageType;
        }

        // Smooth scroll for anchor links
        document.addEventListener('DOMContentLoaded', function() {
            // Mark packages section for scroll reference
            const packagesSection = document.querySelector('.max-w-7xl');
            if (packagesSection) {
                packagesSection.id = 'packages';
            }
        });
    </script>
</body>
</html>