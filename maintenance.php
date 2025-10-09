<?php
require_once 'includes/database_mysql.php';

// Se la manutenzione non è attivata, reindirizza alla home
try {
    $db = new Database();
    $maintenance_enabled = $db->getSetting('maintenance_enabled');
    $maintenance_message = $db->getSetting('maintenance_message') ?? 'Sito in manutenzione. Torneremo presto!';
    
    if ($maintenance_enabled != 1) {
        header('Location: index.php');
        exit();
    }
} catch (Exception $e) {
    // In caso di errore, mostra una pagina di manutenzione generica
    $maintenance_message = 'Sito temporaneamente non disponibile. Torneremo presto!';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sito in Manutenzione - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <style>
        .maintenance-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .floating-animation {
            animation: floating 3s ease-in-out infinite;
        }
        
        @keyframes floating {
            0% { transform: translate(0, 0px); }
            50% { transform: translate(0, -10px); }
            100% { transform: translate(0, 0px); }
        }
        
        .pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="maintenance-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Logo e Titolo -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white bg-opacity-20 rounded-full mb-4 floating-animation">
                <i data-lucide="wrench" class="w-10 h-10 text-white"></i>
            </div>
            <h1 class="text-3xl font-bold text-white mb-2">Passione Calabria</h1>
            <p class="text-blue-100">La tua guida alla Calabria</p>
        </div>
        
        <!-- Card principale -->
        <div class="bg-white bg-opacity-95 backdrop-blur-sm rounded-2xl p-8 shadow-2xl">
            <div class="text-center">
                <!-- Icona manutenzione -->
                <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 rounded-full mb-6 pulse-slow">
                    <i data-lucide="settings" class="w-8 h-8 text-yellow-600"></i>
                </div>
                
                <!-- Titolo -->
                <h2 class="text-2xl font-bold text-gray-900 mb-4">Sito in Manutenzione</h2>
                
                <!-- Messaggio personalizzato -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                    <p class="text-gray-700 text-center leading-relaxed">
                        <?php echo htmlspecialchars($maintenance_message); ?>
                    </p>
                </div>
                
                <!-- Informazioni aggiuntive -->
                <div class="space-y-3 text-sm text-gray-600">
                    <div class="flex items-center justify-center">
                        <i data-lucide="clock" class="w-4 h-4 mr-2 text-gray-500"></i>
                        <span>Stimiamo di essere online a breve</span>
                    </div>
                    
                    <div class="flex items-center justify-center">
                        <i data-lucide="shield-check" class="w-4 h-4 mr-2 text-gray-500"></i>
                        <span>Stiamo migliorando l'esperienza per te</span>
                    </div>
                </div>
                
                <!-- Bottone di refresh -->
                <div class="mt-8">
                    <button onclick="window.location.reload()" 
                            class="w-full bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white py-3 px-6 rounded-lg transition-all duration-200 font-medium flex items-center justify-center space-x-2">
                        <i data-lucide="refresh-cw" class="w-4 h-4"></i>
                        <span>Riprova</span>
                    </button>
                </div>
                
                <!-- Link social o contatti -->
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <p class="text-xs text-gray-500 text-center">
                        Grazie per la pazienza • Passione Calabria Team
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Admin access (nascosto) -->
        <div class="text-center mt-6">
            <a href="admin/" class="text-white text-opacity-50 hover:text-opacity-75 text-xs transition-colors">
                Admin Area
            </a>
        </div>
    </div>

    <script>
        // Inizializza Lucide icons
        lucide.createIcons();
        
        // Auto-refresh ogni 30 secondi
        setTimeout(function() {
            window.location.reload();
        }, 30000);
        
        // Mostra il tempo trascorso
        let startTime = Date.now();
        function updateTimer() {
            let elapsed = Math.floor((Date.now() - startTime) / 1000);
            let minutes = Math.floor(elapsed / 60);
            let seconds = elapsed % 60;
            
            if (minutes > 0) {
                document.title = `Manutenzione (${minutes}m ${seconds}s) - Passione Calabria`;
            } else {
                document.title = `Manutenzione (${seconds}s) - Passione Calabria`;
            }
        }
        
        setInterval(updateTimer, 1000);
    </script>
</body>
</html>