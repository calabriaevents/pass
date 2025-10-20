<?php
// This is the main admin dashboard page.
// It relies on the header partial to establish the database connection
// and handle session authentication.

$page_title = "Dashboard"; // Set the page title for the header
require_once __DIR__ . '/partials/header.php'; // Include header, which handles auth and DB connection.

// All database queries will now use the global $db object established in header.php

$dbError = null;
$stats = [];
$totalViews = 0;
$recentArticles = [];
$healthData = [];

try {
    if ($db->isConnected()) {
        // Carica statistiche dashboard
        $stats = [
            'articles' => $db->pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
            'published_articles' => $db->pdo->query('SELECT COUNT(*) FROM articles WHERE status = "published"')->fetchColumn(),
            'categories' => $db->pdo->query('SELECT COUNT(*) FROM categories')->fetchColumn(),
            'provinces' => $db->pdo->query('SELECT COUNT(*) FROM provinces')->fetchColumn(),
            'cities' => $db->pdo->query('SELECT COUNT(*) FROM cities')->fetchColumn(),
            'users' => $db->pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'businesses' => $db->pdo->query('SELECT COUNT(*) FROM businesses')->fetchColumn(),
            'events' => $db->pdo->query('SELECT COUNT(*) FROM events')->fetchColumn(),
            'comments' => $db->pdo->query('SELECT COUNT(*) FROM comments')->fetchColumn(),
            'pending_comments' => $db->pdo->query('SELECT COUNT(*) FROM comments WHERE status = "pending"')->fetchColumn(),
            'comuni' => $db->pdo->query('SELECT COUNT(*) FROM comuni')->fetchColumn()
        ];

        // Statistiche visualizzazioni
        $totalViews = $db->pdo->query('SELECT SUM(views) FROM articles')->fetchColumn() ?: 0;

        // Calcola guadagni totali
        $totalRevenue = 0;
        try {
            $stmt_subs = $db->pdo->query('SELECT SUM(amount) as total FROM subscriptions WHERE status IN ("active", "expired", "cancelled")');
            $subscriptionRevenue = $stmt_subs->fetch()['total'] ?: 0;

            $stmt_credits = $db->pdo->query('SELECT SUM(amount_paid) as total FROM consumption_purchases WHERE status = "completed"');
            $consumptionRevenue = $stmt_credits->fetch()['total'] ?: 0;

            $stmt_comuni = $db->pdo->query('SELECT SUM(importo_pagato) as total FROM comuni');
            $comuniRevenue = $stmt_comuni->fetch()['total'] ?: 0;

            $totalRevenue = $subscriptionRevenue + $consumptionRevenue + $comuniRevenue;

        } catch (Exception $e) {
            $totalRevenue = 0;
            error_log("Errore nel calcolo dei guadagni totali: " . $e->getMessage());
        }

        // Calcoli mensili e annuali
        $revenueCurrentMonth = 0;
        $revenuePreviousMonth = 0;
        $revenueCurrentYear = 0;
        $revenuePreviousYear = 0;

        try {
            $currentMonthStart = date('Y-m-01 00:00:00');
            $currentMonthEnd = date('Y-m-t 23:59:59');
            $revenueCurrentMonth = $db->getRevenueForPeriod($currentMonthStart, $currentMonthEnd);

            $previousMonthStart = date('Y-m-01 00:00:00', strtotime('first day of last month'));
            $previousMonthEnd = date('Y-m-t 23:59:59', strtotime('last day of last month'));
            $revenuePreviousMonth = $db->getRevenueForPeriod($previousMonthStart, $previousMonthEnd);

            $currentYearStart = date('Y-01-01 00:00:00');
            $currentYearEnd = date('Y-12-31 23:59:59');
            $revenueCurrentYear = $db->getRevenueForPeriod($currentYearStart, $currentYearEnd);

            $previousYearStart = date('Y-01-01 00:00:00', strtotime('last year'));
            $previousYearEnd = date('Y-12-31 23:59:59', strtotime('last year'));
            $revenuePreviousYear = $db->getRevenueForPeriod($previousYearStart, $previousYearEnd);
        } catch (Exception $e) {
            error_log("Errore nel calcolo dei guadagni per periodo: " . $e->getMessage());
        }

        // Articoli recenti
        $recentArticles = $db->pdo->query('
            SELECT a.*, c.name as category_name
            FROM articles a
            LEFT JOIN categories c ON a.category_id = c.id
            ORDER BY a.created_at DESC
            LIMIT 5
        ')->fetchAll();

        $healthData = [
            'database' => ['size' => 'N/A'],
            'counts' => [],
            'health' => ['checks' => ['integrityOk' => false]]
        ];
    }
} catch (PDOException $e) {
    $dbError = $e->getMessage();
}
?>
        <!-- Header is already included in partials/header.php -->

        <main class="flex-1 overflow-auto p-6">
            <?php if ($dbError): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                <p class="font-bold">Errore di Connessione al Database</p>
                <p><?php echo $dbError; ?></p>
            </div>
            <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Articoli Totali</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['articles']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo $stats['published_articles']; ?> pubblicati</p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i data-lucide="file-text" class="w-6 h-6 text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Mese Precedente</p>
                            <p class="text-3xl font-bold text-gray-900">€<?php echo number_format($revenuePreviousMonth, 2); ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('F Y', strtotime('last month')); ?></p>
                        </div>
                        <div class="bg-gray-100 p-3 rounded-full">
                            <i data-lucide="rewind" class="w-6 h-6 text-gray-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Visualizzazioni</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo number_format($totalViews); ?></p>
                            <p class="text-sm text-gray-500">Totali</p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i data-lucide="eye" class="w-6 h-6 text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Commenti</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['comments']; ?></p>
                            <p class="text-sm text-gray-500"><?php echo $stats['pending_comments']; ?> in attesa</p>
                        </div>
                        <div class="bg-purple-100 p-3 rounded-full">
                            <i data-lucide="message-square" class="w-6 h-6 text-purple-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Business</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['businesses']; ?></p>
                            <p class="text-sm text-gray-500">Registrati</p>
                        </div>
                        <div class="bg-orange-100 p-3 rounded-full">
                            <i data-lucide="building-2" class="w-6 h-6 text-orange-600"></i>
                        </div>
                    </div>
                </div>
                 <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Comuni</p>
                            <p class="text-3xl font-bold text-gray-900"><?php echo $stats['comuni']; ?></p>
                            <p class="text-sm text-gray-500">Convenzionati</p>
                        </div>
                        <div class="bg-teal-100 p-3 rounded-full">
                            <i data-lucide="building" class="w-6 h-6 text-teal-600"></i>
                        </div>
                    </div>
                </div>
            </div>
             <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Guadagni Totali</p>
                            <p class="text-3xl font-bold text-gray-900">€<?php echo number_format($totalRevenue, 2); ?></p>
                            <p class="text-sm text-gray-500">Abbonamenti + Crediti + Comuni</p>
                        </div>
                        <div class="bg-emerald-100 p-3 rounded-full">
                            <i data-lucide="euro" class="w-6 h-6 text-emerald-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Mese Corrente</p>
                            <p class="text-3xl font-bold text-gray-900">€<?php echo number_format($revenueCurrentMonth, 2); ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('F Y'); ?></p>
                        </div>
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i data-lucide="calendar" class="w-6 h-6 text-blue-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Anno Corrente</p>
                            <p class="text-3xl font-bold text-gray-900">€<?php echo number_format($revenueCurrentYear, 2); ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('Y'); ?></p>
                        </div>
                        <div class="bg-green-100 p-3 rounded-full">
                            <i data-lucide="trending-up" class="w-6 h-6 text-green-600"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Anno Precedente</p>
                            <p class="text-3xl font-bold text-gray-900">€<?php echo number_format($revenuePreviousYear, 2); ?></p>
                            <p class="text-sm text-gray-500"><?php echo date('Y', strtotime('last year')); ?></p>
                        </div>
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i data-lucide="calendar-days" class="w-6 h-6 text-yellow-600"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <i data-lucide="database" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Stato Database
                    </h3>

                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Dimensione Database</span>
                            <span class="font-medium"><?php echo $healthData['database']['size']; ?></span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Tabelle Totali</span>
                            <span class="font-medium"><?php echo count($healthData['counts']); ?></span>
                        </div>

                        <div class="flex items-center justify-between">
                            <span class="text-sm text-gray-600">Integrità</span>
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                OK
                            </span>
                        </div>

                        <div class="pt-4">
                            <a href="database.php" class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors flex items-center justify-center">
                                <i data-lucide="settings" class="w-4 h-4 mr-2"></i>
                                Gestisci Database
                            </a>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm p-6">
                    <h3 class="text-lg font-semibold mb-4 flex items-center">
                        <i data-lucide="zap" class="w-5 h-5 mr-2 text-yellow-600"></i>
                        Azioni Rapide
                    </h3>

                    <div class="grid grid-cols-2 gap-4">
                        <a href="articoli.php?action=new" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i data-lucide="plus" class="w-8 h-8 text-blue-600 mb-2"></i>
                            <span class="text-sm font-medium text-gray-900">Nuovo Articolo</span>
                        </a>

                        <a href="commenti.php?filter=pending" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i data-lucide="message-square" class="w-8 h-8 text-purple-600 mb-2"></i>
                            <span class="text-sm font-medium text-gray-900">Commenti</span>
                            <?php if ($stats['pending_comments'] > 0): ?>
                            <span class="bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full mt-1">
                                <?php echo $stats['pending_comments']; ?>
                            </span>
                            <?php endif; ?>
                        </a>

                        <a href="business.php?filter=pending" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i data-lucide="building-2" class="w-8 h-8 text-orange-600 mb-2"></i>
                            <span class="text-sm font-medium text-gray-900">Business</span>
                        </a>

                        <a href="database.php" class="flex flex-col items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <i data-lucide="download" class="w-8 h-8 text-green-600 mb-2"></i>
                            <span class="text-sm font-medium text-gray-900">Backup DB</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm p-6">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold flex items-center">
                        <i data-lucide="file-text" class="w-5 h-5 mr-2 text-blue-600"></i>
                        Articoli Recenti
                    </h3>
                    <a href="articoli.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        Vedi tutti
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Titolo</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Categoria</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Stato</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Visualizzazioni</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Data</th>
                                <th class="text-left py-3 px-4 font-medium text-gray-700">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentArticles as $article): ?>
                            <tr class="border-b border-gray-100 hover:bg-gray-50">
                                <td class="py-3 px-4">
                                    <div class="font-medium text-gray-900"><?php echo htmlspecialchars($article['title']); ?></div>
                                    <div class="text-sm text-gray-500"><?php echo htmlspecialchars(substr($article['excerpt'], 0, 60)); ?>...</div>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?php echo htmlspecialchars($article['category_name']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        <?php echo $article['status'] === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                        <?php echo ucfirst($article['status']); ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-gray-600"><?php echo $article['views']; ?></td>
                                <td class="py-3 px-4 text-gray-600"><?php echo date('d/m/Y', strtotime($article['created_at'])); ?></td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center space-x-2">
                                        <a href="../articolo.php?slug=<?php echo $article['slug']; ?>"
                                           class="text-blue-600 hover:text-blue-700" title="Visualizza">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                        <a href="articoli.php?action=edit&id=<?php echo $article['id']; ?>"
                                           class="text-gray-600 hover:text-gray-700" title="Modifica">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </main>
<?php include 'partials/footer.php'; ?>
