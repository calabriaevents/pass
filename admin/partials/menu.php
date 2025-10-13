<?php
// Determina la pagina corrente
$currentPage = basename($_SERVER['PHP_SELF']);

// Definisci le voci di menu
$menuItems = [
    ['file' => 'index.php', 'icon' => 'home', 'label' => 'Dashboard'],
    ['file' => 'gestione-home.php', 'icon' => 'layout', 'label' => 'Gestione Home'],
    ['file' => 'articoli.php', 'icon' => 'file-text', 'label' => 'Articoli'],
    ['file' => 'categorie.php', 'icon' => 'tags', 'label' => 'Categorie'],
    ['file' => 'province.php', 'icon' => 'map', 'label' => 'Province'],
    ['file' => 'citta.php', 'icon' => 'map-pin', 'label' => 'Città'],
    ['file' => 'comuni.php', 'icon' => 'building', 'label' => 'Comuni'],
    ['file' => 'commenti.php', 'icon' => 'message-square', 'label' => 'Commenti'],
    ['file' => 'foto-utenti.php', 'icon' => 'image', 'label' => 'Foto Utenti'],
    ['file' => 'suggerimenti-luoghi.php', 'icon' => 'map-pin', 'label' => 'Suggerimenti Luoghi'],
    ['file' => 'suggerimenti-eventi.php', 'icon' => 'calendar-plus', 'label' => 'Suggerimenti Eventi'],
    ['file' => 'business.php', 'icon' => 'building-2', 'label' => 'Business'],
    ['file' => 'gestione-pacchetti.php', 'icon' => 'package', 'label' => 'Pacchetti Abbonamento'],
    ['file' => 'consumo-pacchetti.php', 'icon' => 'zap', 'label' => 'Pacchetti a Consumo'],
    ['file' => 'abbonamenti.php', 'icon' => 'credit-card', 'label' => 'Abbonamenti'],
    ['file' => 'utenti.php', 'icon' => 'users', 'label' => 'Utenti'],
    ['file' => 'database.php', 'icon' => 'database', 'label' => 'Monitoraggio DB'],
    ['file' => 'manutenzione.php', 'icon' => 'wrench', 'label' => 'Modalità Manutenzione'],
    ['file' => 'impostazioni.php', 'icon' => 'settings', 'label' => 'Impostazioni'],
];

// Recupera tutti i conteggi degli elementi in attesa
$pendingCounts = [
    'events' => 0,
    'places' => 0,
    'comments' => 0,
    'businesses' => 0,
    'uploads' => 0,
];

if (isset($db) && $db->isConnected()) {
    if (method_exists($db, 'getPendingEventSuggestionsCount')) {
        $pendingCounts['events'] = $db->getPendingEventSuggestionsCount();
    }
    if (method_exists($db, 'getPendingPlaceSuggestionsCount')) {
        $pendingCounts['places'] = $db->getPendingPlaceSuggestionsCount();
    }
    if (method_exists($db, 'getPendingCommentsCount')) {
        $pendingCounts['comments'] = $db->getPendingCommentsCount();
    }
    if (method_exists($db, 'getPendingBusinessesCount')) {
        $pendingCounts['businesses'] = $db->getPendingBusinessesCount();
    }
    if (method_exists($db, 'getPendingUserUploadsCount')) {
        $pendingCounts['uploads'] = $db->getPendingUserUploadsCount();
    }
}
?>
<nav class="flex-1 p-4 overflow-y-auto">
    <ul class="space-y-2">
        <?php foreach ($menuItems as $item): ?>
            <?php
                $isActive = ($currentPage === $item['file']);
                $class = 'flex items-center space-x-3 px-3 py-2 rounded-lg transition-colors';
                if ($isActive) {
                    $class .= ' bg-gray-700 text-white';
                } else {
                    $class .= ' hover:bg-gray-700';
                }

                // Determina se mostrare un contatore e quale
                $countToShow = 0;
                if ($item['file'] === 'suggerimenti-eventi.php') {
                    $countToShow = $pendingCounts['events'];
                } elseif ($item['file'] === 'suggerimenti-luoghi.php') {
                    $countToShow = $pendingCounts['places'];
                } elseif ($item['file'] === 'commenti.php') {
                    $countToShow = $pendingCounts['comments'];
                } elseif ($item['file'] === 'business.php') {
                    $countToShow = $pendingCounts['businesses'];
                } elseif ($item['file'] === 'foto-utenti.php') {
                    $countToShow = $pendingCounts['uploads'];
                }
            ?>
            <li>
                <a href="<?php echo $item['file']; ?>" class="<?php echo $class; ?>">
                    <i data-lucide="<?php echo $item['icon']; ?>" class="w-5 h-5"></i>
                    <span class="flex-1"><?php echo $item['label']; ?></span>
                    <?php if ($countToShow > 0): ?>
                        <span class="bg-amber-500 text-white text-xs font-semibold px-2 py-1 rounded-full">
                            <?php echo $countToShow; ?>
                        </span>
                    <?php endif; ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
</nav>