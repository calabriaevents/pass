<!-- Footer -->
<footer class="bg-gray-900 text-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
            <!-- About Section -->
            <div class="space-y-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-yellow-500 rounded-full flex items-center justify-center">
                        <span class="text-white font-bold text-lg">PC</span>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold">
                            Passione <span class="text-yellow-400">Calabria</span>
                        </h3>
                        <p class="text-blue-200 text-sm">La tua guida alla Calabria</p>
                    </div>
                </div>
                <p class="text-gray-300 leading-relaxed">
                    Il portale dedicato alla scoperta della Calabria autentica: luoghi, tradizioni,
                    sapori e storie che rendono unica la nostra terra.
                </p>
                <div class="flex space-x-4">
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="facebook" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="instagram" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="twitter" class="w-5 h-5"></i>
                    </a>
                    <a href="#" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="youtube" class="w-5 h-5"></i>
                    </a>
                </div>
            </div>

            <!-- Esplora -->
            <div>
                <h4 class="text-lg font-semibold mb-6 text-yellow-400">Esplora</h4>
                <ul class="space-y-3">
                    <li><a href="categorie.php" class="text-gray-300 hover:text-white transition-colors">Tutte le Categorie</a></li>
                    <li><a href="province.php" class="text-gray-300 hover:text-white transition-colors">Le Province</a></li>
                    <li><a href="mappa.php" class="text-gray-300 hover:text-white transition-colors">Mappa Interattiva</a></li>
                    <li><a href="articoli.php" class="text-gray-300 hover:text-white transition-colors">Tutti gli Articoli</a></li>
                </ul>
            </div>

            <!-- Informazioni -->
            <div>
                <h4 class="text-lg font-semibold mb-6 text-yellow-400">Informazioni</h4>
                <ul class="space-y-3">
                    <li><a href="chi-siamo.php" class="text-gray-300 hover:text-white transition-colors">Chi Siamo</a></li>
                    <li><a href="collabora.php" class="text-gray-300 hover:text-white transition-colors">Collabora con Noi</a></li>
                    <li><a href="suggerisci.php" class="text-gray-300 hover:text-white transition-colors">Suggerisci un Luogo</a></li>
                    <li><a href="contatti.php" class="text-gray-300 hover:text-white transition-colors">Contatti</a></li>
                    <li><a href="privacy-policy.php" class="text-gray-300 hover:text-white transition-colors">Privacy Policy</a></li>
                </ul>
            </div>

            <!-- Contatti -->
            <div>
                <h4 class="text-lg font-semibold mb-6 text-yellow-400">Contatti</h4>
                <div class="space-y-4">
                    <div class="flex items-center space-x-3">
                        <i data-lucide="map-pin" class="w-5 h-5 text-blue-400"></i>
                        <div>
                            <div class="font-medium">Calabria, Italia</div>
                            <div class="text-sm text-gray-400">La terra tra due mari</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i data-lucide="mail" class="w-5 h-5 text-blue-400"></i>
                        <a href="mailto:info@passionecalabria.it" class="text-gray-300 hover:text-white transition-colors">
                            info@passionecalabria.it
                        </a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <i data-lucide="phone" class="w-5 h-5 text-blue-400"></i>
                        <a href="tel:+393001234567" class="text-gray-300 hover:text-white transition-colors">
                            +39 300 123 4567
                        </a>
                    </div>
                </div>

                <!-- Newsletter -->
                <div class="mt-8">
                    <h5 class="font-semibold mb-4 text-yellow-400">Newsletter</h5>
                    <p class="text-sm text-gray-400 mb-4">
                        Ricevi aggiornamenti sui nuovi contenuti e eventi.
                    </p>
                    <form action="api/newsletter.php" method="POST" class="flex">
                        <input
                            type="email"
                            name="email"
                            placeholder="La tua email"
                            required
                            class="flex-1 px-4 py-2 bg-gray-800 border border-gray-700 rounded-l-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-white placeholder-gray-400"
                        >
                        <button
                            type="submit"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-r-lg transition-colors"
                        >
                            <i data-lucide="send" class="w-4 h-4"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bottom Footer -->
        <div class="border-t border-gray-800 mt-12 pt-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-gray-400 text-sm">
                    © 2024 Passione Calabria. Fatto con <span class="text-red-500">♥</span> in Calabria.
                </div>
                <div class="flex space-x-6 mt-4 md:mt-0">
                    <a href="termini-servizio.php" class="text-gray-400 hover:text-white text-sm transition-colors">Termini di Servizio</a>
                    <a href="privacy-policy.php" class="text-gray-400 hover:text-white text-sm transition-colors">Privacy</a>
                    <button onclick="scrollToTop()" class="text-gray-400 hover:text-white transition-colors">
                        <i data-lucide="arrow-up" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</footer>

<!-- Pulsante Torna in Alto (Floating) -->
<button id="scroll-to-top-btn" 
        onclick="scrollToTop()" 
        class="fixed bottom-6 right-6 w-12 h-12 bg-blue-600 hover:bg-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transition-all duration-300 z-50 opacity-0 invisible"
        style="transform: translateY(100px);"
        title="Torna in alto">
    <i data-lucide="chevron-up" class="w-6 h-6 mx-auto"></i>
</button>

<script>
// JavaScript per il pulsante "Torna in alto"
document.addEventListener('DOMContentLoaded', function() {
    const scrollToTopBtn = document.getElementById('scroll-to-top-btn');
    
    // Mostra/nasconde il pulsante in base allo scroll
    window.addEventListener('scroll', function() {
        if (window.scrollY > 300) {
            scrollToTopBtn.style.opacity = '1';
            scrollToTopBtn.style.visibility = 'visible';
            scrollToTopBtn.style.transform = 'translateY(0)';
        } else {
            scrollToTopBtn.style.opacity = '0';
            scrollToTopBtn.style.visibility = 'hidden';
            scrollToTopBtn.style.transform = 'translateY(100px)';
        }
    });
});

// Funzione per scrollare in alto
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}
</script>

<!-- Upload Progress JavaScript -->
<script src="<?php echo BASE_URL; ?>assets/js/upload-progress.js"></script>
