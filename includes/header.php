<?php
// Header comune per tutte le pagine
require_once __DIR__ . '/config.php';

// Controllo modalità manutenzione (solo per pagine pubbliche)
require_once __DIR__ . '/maintenance_check.php';
?>
<!-- Upload Progress CSS -->
<link rel="stylesheet" href="<?php echo SITE_URL; ?>/assets/css/upload-progress.css">
<!-- Header -->
<header class="bg-gradient-to-r from-blue-600 via-teal-500 to-yellow-500 text-white">
    <!-- Top Bar -->
    <div class="bg-black/20 py-2">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center text-sm">
                <div class="flex items-center space-x-2">
                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                    <span>Scopri la Calabria</span>
                </div>
                
                <div class="hidden sm:block">
                    <span>Benvenuto in Passione Calabria</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="relative">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <!-- Logo -->
                <div class="flex items-center space-x-3">
                    <a href="index.php" class="flex items-center space-x-3">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-yellow-500 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-lg">PC</span>
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold">
                                Passione <span class="text-yellow-300">Calabria</span>
                            </h1>
                            <p class="text-blue-100 text-sm">La tua guida alla Calabria</p>
                        </div>
                    </a>
                </div>

                <!-- Navigation Links - Centered -->
                <div class="hidden lg:flex items-center justify-center flex-1">
                    <div class="flex items-center space-x-8">
                        <a href="index.php" class="hover:text-yellow-300 transition-colors font-medium">Home</a>
                        <a href="categorie.php" class="hover:text-yellow-300 transition-colors font-medium">Categorie</a>
                        <a href="province.php" class="hover:text-yellow-300 transition-colors font-medium">Province</a>
                        <a href="citta.php" class="hover:text-yellow-300 transition-colors font-medium">Città</a>
                        <a href="mappa.php" class="hover:text-yellow-300 transition-colors font-medium">Mappa</a>
                        <a href="iscrizione-attivita.php" class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-full transition-colors font-medium">Iscrivi la tua attività</a>
                        <a href="admin/" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-full transition-colors font-medium">Admin</a>
                    </div>
                </div>
                
                <!-- User Actions -->
                <div class="hidden lg:flex items-center space-x-4">
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <!-- Logged in user menu -->
                        <div class="relative group">
                            <button class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full transition-colors">
                                <i data-lucide="user" class="w-4 h-4"></i>
                                <span class="text-sm font-medium"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Attività'); ?></span>
                                <i data-lucide="chevron-down" class="w-4 h-4"></i>
                            </button>
                            
                            <!-- Dropdown menu -->
                            <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                                <div class="py-2">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="text-sm font-medium text-gray-900"><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Attività'); ?></p>
                                        <p class="text-xs text-gray-500"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                                        <p class="text-xs text-orange-600 font-medium">
                                            Stato: <?php echo ucfirst($_SESSION['business_status'] ?? 'pending'); ?>
                                        </p>
                                    </div>
                                    <a href="user-dashboard.php" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2"></i>
                                        Dashboard
                                    </a>
                                    <a href="user-auth.php?action=logout" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                                        Logout
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Not logged in -->
                        <a href="user-auth.php?action=login" class="flex items-center space-x-2 bg-white/10 hover:bg-white/20 px-4 py-2 rounded-full transition-colors">
                            <i data-lucide="user" class="w-4 h-4"></i>
                            <span class="text-sm font-medium">Area Attività</span>
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile User & Menu Button -->
                <div class="lg:hidden flex items-center space-x-2">
                    <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                        <a href="user-dashboard.php" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-colors" title="Dashboard">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </a>
                    <?php else: ?>
                        <a href="user-auth.php?action=login" class="bg-white/10 hover:bg-white/20 p-2 rounded-full transition-colors" title="Area Attività">
                            <i data-lucide="user" class="w-5 h-5"></i>
                        </a>
                    <?php endif; ?>
                    
                    <button id="mobile-menu-btn" class="p-2 focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 rounded-md">
                        <i data-lucide="menu" class="w-6 h-6"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="lg:hidden hidden bg-black/20 backdrop-blur-sm border-t border-white/10">
            <div class="px-4 py-4 space-y-3">
                <a href="index.php" class="block py-2 hover:text-yellow-300 transition-colors">Home</a>
                <a href="categorie.php" class="block py-2 hover:text-yellow-300 transition-colors">Categorie</a>
                <a href="province.php" class="block py-2 hover:text-yellow-300 transition-colors">Province</a>
                <a href="citta.php" class="block py-2 hover:text-yellow-300 transition-colors">Città</a>
                <a href="mappa.php" class="block py-2 hover:text-yellow-300 transition-colors">Mappa</a>
                <a href="iscrizione-attivita.php" class="block py-2 hover:text-yellow-300 transition-colors">Iscrivi la tua attività</a>
                
                <!-- User Menu Section -->
                <?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in']): ?>
                    <div class="border-t border-white/20 pt-3 mt-3">
                        <div class="text-white/80 text-sm mb-2 font-medium">
                            <?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Attività'); ?>
                        </div>
                        <div class="text-white/60 text-xs mb-2">
                            <?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>
                        </div>
                        <div class="text-orange-300 text-xs mb-3">
                            Stato: <?php echo ucfirst($_SESSION['business_status'] ?? 'pending'); ?>
                        </div>
                        <a href="user-dashboard.php" class="block py-2 hover:text-yellow-300 transition-colors">
                            <i data-lucide="layout-dashboard" class="w-4 h-4 mr-2 inline"></i>
                            Dashboard
                        </a>
                        <a href="user-auth.php?action=logout" class="block py-2 hover:text-yellow-300 transition-colors">
                            <i data-lucide="log-out" class="w-4 h-4 mr-2 inline"></i>
                            Logout
                        </a>
                    </div>
                <?php else: ?>
                    <div class="border-t border-white/20 pt-3 mt-3">
                        <a href="user-auth.php?action=login" class="block py-2 hover:text-yellow-300 transition-colors">
                            <i data-lucide="user" class="w-4 h-4 mr-2 inline"></i>
                            Area Attività
                        </a>
                    </div>
                <?php endif; ?>
                
                <a href="admin/" class="block py-2 hover:text-yellow-300 transition-colors">Admin</a>
            </div>
        </div>
    </nav>
</header>

<!-- Menu mobile gestito da assets/js/main.js -->

<style>
/* Animazione per il menu mobile */
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

#mobile-menu {
    transition: all 0.3s ease-in-out;
}

/* Miglioramenti per l'accessibilità */
#mobile-menu-btn:focus {
    outline: 2px solid rgba(255, 255, 255, 0.5);
    outline-offset: 2px;
}

/* Responsive migliorato */
@media (max-width: 1023px) {
    #mobile-menu {
        animation: slideDown 0.3s ease-out;
    }
}

/* Stili per il dropdown menu hover */
.group:hover .group-hover\:opacity-100 {
    opacity: 1;
}

.group:hover .group-hover\:visible {
    visibility: visible;
}
</style>