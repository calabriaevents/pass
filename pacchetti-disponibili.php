<?php
require_once 'includes/config.php';
require_once 'includes/database_mysql.php';

// Controlla se l'utente è loggato
if (!isset($_SESSION['user_logged_in']) || !$_SESSION['user_logged_in']) {
    header('Location: user-auth.php');
    exit;
}

$db = new Database();
$user_id = $_SESSION['user_id'] ?? null;
$business_id = $_SESSION['business_id'] ?? null;

if (!$user_id || !$business_id) {
    session_destroy();
    header('Location: user-auth.php?error=session_expired');
    exit;
}

// Recupera tutti i pacchetti
$current_subscription = $db->getCurrentSubscription($business_id);
$subscription_packages = $db->getAvailablePackages();
$consumption_packages = $db->getConsumptionPackages();

function parseFeatures($features_json) {
    if (empty($features_json)) return [];
    $features = json_decode($features_json, true);
    return is_array($features) ? $features : [];
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tutti i Pacchetti - Passione Calabria</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-gray-50 min-h-screen">
    <?php include 'includes/header.php'; ?>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="flex items-center mb-8">
            <a href="user-dashboard.php" class="text-gray-500 hover:text-gray-800 mr-4">
                <i data-lucide="arrow-left" class="w-6 h-6"></i>
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Piani e Pacchetti Disponibili</h1>
        </div>

        <section class="mb-16">
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">📅 Piani di Abbonamento</h2>
                <p class="text-lg text-gray-600 mt-2">Scegli il piano annuale che si adatta meglio alle tue esigenze.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($subscription_packages as $package): ?>
                    <?php
                    $is_current = $current_subscription && $current_subscription['package_id'] == $package['id'];
                    $is_upgrade = $current_subscription && $package['price'] > $current_subscription['package_price'];
                    ?>
                    <div class="bg-white rounded-lg shadow-lg p-6 border-2 <?php echo $is_current ? 'border-green-500' : 'border-transparent'; ?>">
                        <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p class="text-gray-500 text-sm mt-1 h-10"><?php echo htmlspecialchars($package['description']); ?></p>
                        <p class="text-3xl font-bold text-gray-900 my-4">
                            <?php echo $package['price'] > 0 ? '€' . number_format($package['price'], 2) : 'Gratuito'; ?>
                            <span class="text-base font-normal text-gray-500">/anno</span>
                        </p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-6">
                            <?php foreach (parseFeatures($package['features']) as $feature): ?>
                                <li class="flex items-center">
                                    <i data-lucide="check" class="w-4 h-4 text-green-500 mr-2"></i>
                                    <span><?php echo htmlspecialchars($feature); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <?php if ($is_current): ?>
                            <button class="w-full bg-gray-200 text-gray-500 font-semibold py-2 px-4 rounded-lg cursor-not-allowed">Piano Attuale</button>
                        <?php else: ?>
                            <form method="POST" action="user-dashboard.php">
                                <input type="hidden" name="action" value="<?php echo $is_upgrade ? 'upgrade' : 'downgrade'; ?>">
                                <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors">
                                    <?php echo $is_upgrade ? 'Esegui Upgrade' : 'Cambia Piano'; ?>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section>
            <div class="text-center mb-12">
                <h2 class="text-3xl font-bold text-gray-900">⚡ Pacchetti Crediti a Consumo</h2>
                <p class="text-lg text-gray-600 mt-2">Acquista crediti per servizi extra e promozioni mirate.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($consumption_packages as $package): ?>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-bold text-gray-900"><?php echo htmlspecialchars($package['name']); ?></h3>
                        <p class="text-yellow-600 font-semibold mt-1"><?php echo $package['consumption_credits']; ?> Crediti</p>
                        <p class="text-3xl font-bold text-gray-900 my-4">€<?php echo number_format($package['price'], 2); ?></p>
                        <ul class="space-y-2 text-sm text-gray-600 mb-6">
                            <?php foreach (parseFeatures($package['features']) as $feature): ?>
                                <li class="flex items-center">
                                    <i data-lucide="zap" class="w-4 h-4 text-yellow-500 mr-2"></i>
                                    <span><?php echo htmlspecialchars($feature); ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                        <form method="POST" action="user-dashboard.php">
                            <input type="hidden" name="action" value="buy_credits">
                            <input type="hidden" name="package_id" value="<?php echo $package['id']; ?>">
                            <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-lg transition-colors">Acquista Pacchetto</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <script src="assets/js/main.js"></script>
    <script>
        lucide.createIcons();
    </script>
</body>
</html>