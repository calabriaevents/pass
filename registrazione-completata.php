<?php
require_once 'includes/config.php';

// Controlla se la registrazione è stata completata con successo
if (!isset($_SESSION['registration_success']) || !$_SESSION['registration_success']) {
    header('Location: iscrizione-attivita.php');
    exit;
}

$business_name = $_SESSION['business_name'] ?? 'la tua attività';
$business_email = $_SESSION['business_email'] ?? '';
$package_name = $_SESSION['package_name'] ?? 'Gratuito';
$package_price = $_SESSION['package_price'] ?? 0;

// Pulisci la sessione per evitare accessi multipli
unset($_SESSION['registration_success']);
unset($_SESSION['business_name']);
unset($_SESSION['business_email']);
unset($_SESSION['package_name']);
unset($_SESSION['package_price']);
unset($_SESSION['selected_package_id']);
?>

<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrazione Completata - <?php echo SITE_NAME; ?></title>
    <meta name="description" content="Registrazione completata con successo">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <main class="py-12">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Success Card -->
            <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 text-white p-8 text-center">
                    <div class="w-20 h-20 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="check" class="w-10 h-10"></i>
                    </div>
                    <h1 class="text-3xl font-bold mb-2">Registrazione Completata!</h1>
                    <p class="text-green-100 text-lg">
                        Benvenuto in Passione Calabria
                    </p>
                </div>
                
                <div class="p-8">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                            Grazie per aver registrato "<?php echo htmlspecialchars($business_name); ?>"!
                        </h2>
                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4 mb-6 rounded-r-lg">
                            <div class="flex items-center">
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-blue-800">
                                        📦 Pacchetto attivato: <strong><?php echo htmlspecialchars($package_name); ?></strong>
                                        <?php if ($package_price > 0): ?>
                                        - €<?php echo number_format($package_price, 2); ?>
                                        <?php endif; ?>
                                    </p>
                                    <?php if ($business_email): ?>
                                    <p class="text-sm text-blue-700 mt-1">
                                        📧 Email di riferimento: <?php echo htmlspecialchars($business_email); ?>
                                    </p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 text-lg leading-relaxed">
                            <?php if ($package_price == 0): ?>
                            La tua attività è stata registrata con successo nel pacchetto gratuito e sarà sottoposta a revisione dal nostro team.
                            <?php else: ?>
                            Il pagamento è stato elaborato con successo e la tua attività è stata registrata nel pacchetto <?php echo htmlspecialchars($package_name); ?>.
                            <?php endif; ?>
                        </p>
                    </div>
                    
                    <!-- Timeline -->
                    <div class="bg-gray-50 rounded-xl p-6 mb-8">
                        <h3 class="font-semibold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="clock" class="w-5 h-5 mr-2 text-blue-600"></i>
                            Prossimi Passi
                        </h3>
                        
                        <div class="space-y-4">
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-green-100 text-green-600 rounded-full flex items-center justify-center mr-4 mt-0.5">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Registrazione Completata</h4>
                                    <p class="text-gray-600 text-sm">I tuoi dati sono stati salvati nel nostro sistema</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mr-4 mt-0.5">
                                    <span class="font-bold text-xs">2</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Revisione in Corso</h4>
                                    <p class="text-gray-600 text-sm">Il nostro team sta verificando le informazioni fornite</p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mr-4 mt-0.5">
                                    <span class="font-bold text-xs">3</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Approvazione e Attivazione</h4>
                                    <p class="text-gray-600 text-sm">
                                        <?php if ($package_price == 0): ?>
                                        Riceverai una email di approvazione entro 24-48 ore
                                        <?php else: ?>
                                        La tua attività è già attiva e visibile sulla piattaforma!
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="w-8 h-8 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mr-4 mt-0.5">
                                    <span class="font-bold text-xs">4</span>
                                </div>
                                <div>
                                    <h4 class="font-medium text-gray-900">Pubblicazione</h4>
                                    <p class="text-gray-600 text-sm">La tua attività sarà visibile su Passione Calabria</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Important Information -->
                    <div class="bg-blue-50 border border-blue-200 rounded-xl p-6 mb-8">
                        <h3 class="font-semibold text-blue-900 mb-3 flex items-center">
                            <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                            Conferma e Prossimi Passi
                        </h3>
                        <div class="bg-white rounded-lg p-4 mb-4 border-l-4 border-green-400">
                            <h4 class="font-medium text-gray-900 mb-2 flex items-center">
                                <i data-lucide="mail-check" class="w-5 h-5 mr-2 text-green-600"></i>
                                Conferma via Email
                            </h4>
                            <p class="text-gray-700 text-sm leading-relaxed">
                                <strong>Riceverai una conferma via email</strong> all'indirizzo <?php echo $business_email ? htmlspecialchars($business_email) : 'che hai fornito'; ?> 
                                entro pochi minuti. La email conterrà tutti i dettagli del tuo abbonamento, 
                                le credenziali di accesso e le istruzioni per iniziare.
                                <br><br>
                                <em>⚠️ Controlla anche la cartella spam se non ricevi l'email entro 10-15 minuti.</em>
                            </p>
                        </div>
                        <ul class="text-blue-800 space-y-2 text-sm">
                            <li class="flex items-start">
                                <i data-lucide="edit" class="w-4 h-4 mr-2 mt-0.5 text-blue-600"></i>
                                Potrai modificare i dati della tua attività dopo l'approvazione
                            </li>
                            <li class="flex items-start">
                                <i data-lucide="image" class="w-4 h-4 mr-2 mt-0.5 text-blue-600"></i>
                                Potrai aggiungere foto e altre informazioni al tuo profilo
                            </li>
                            <li class="flex items-start">
                                <i data-lucide="trending-up" class="w-4 h-4 mr-2 mt-0.5 text-blue-600"></i>
                                Inizierai a ricevere visibilità immediata sulla piattaforma
                            </li>
                            <li class="flex items-start">
                                <i data-lucide="help-circle" class="w-4 h-4 mr-2 mt-0.5 text-blue-600"></i>
                                Per assistenza contattaci: <strong>info@passionecalabria.it</strong> o <strong>+39 XXX XXX XXXX</strong>
                            </li>
                        </ul>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <a href="index.php" 
                            class="flex-1 bg-gradient-to-r from-blue-600 to-teal-500 hover:from-blue-700 hover:to-teal-600 text-white font-medium py-3 px-6 rounded-lg transition-colors text-center">
                            <i data-lucide="home" class="w-5 h-5 inline mr-2"></i>
                            Torna alla Homepage
                        </a>
                        
                        <a href="province.php" 
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium py-3 px-6 rounded-lg transition-colors text-center">
                            <i data-lucide="map" class="w-5 h-5 inline mr-2"></i>
                            Esplora la Calabria
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Additional Resources -->
            <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Community</h3>
                    <p class="text-gray-600 text-sm">
                        Unisciti alla community di imprenditori calabresi
                    </p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="trending-up" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Visibilità</h3>
                    <p class="text-gray-600 text-sm">
                        Aumenta la visibilità della tua attività online
                    </p>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm p-6 text-center">
                    <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="heart" class="w-6 h-6"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Passione</h3>
                    <p class="text-gray-600 text-sm">
                        Condividi la passione per la nostra terra
                    </p>
                </div>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Inizializza le icone Lucide
        lucide.createIcons();
        
        // Animazione di successo
        document.addEventListener('DOMContentLoaded', function() {
            // Aggiungi una piccola animazione alla scheda principale
            const successCard = document.querySelector('.bg-white.rounded-2xl');
            successCard.style.opacity = '0';
            successCard.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                successCard.style.transition = 'all 0.6s ease-out';
                successCard.style.opacity = '1';
                successCard.style.transform = 'translateY(0)';
            }, 100);
        });
    </script>
</body>
</html>