<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Includi per la cancellazione

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();
$imageProcessor = new ImageProcessor();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings_posted = $_POST['settings'] ?? [];
    $current_settings = $db->getSettingsAsArray();

    // Controlla se un'immagine è stata cambiata e cancella la vecchia
    foreach ($settings_posted as $key => $value) {
        if (strpos($key, '_image') !== false) { // Heuristic for image paths
            $old_value = $current_settings[$key] ?? '';
            if ($value !== $old_value && !empty($old_value)) {
                $imageProcessor->deleteImage($old_value);
            }
        }
    }

    $db->updateSettings($settings_posted);
    header('Location: impostazioni.php?success=Impostazioni salvate con successo!');
    exit;
}

$settings = $db->getSettings();
$settingsArray = $db->getSettingsAsArray();

// Organize settings by category
$settingsGroups = [
    'general' => ['title' => 'Generali', 'icon' => 'settings', 'settings' => []],
    'homepage' => ['title' => 'Homepage', 'icon' => 'home', 'settings' => []],
    'social' => ['title' => 'Social Media', 'icon' => 'share-2', 'settings' => []],
    'security' => ['title' => 'API Keys & Sicurezza', 'icon' => 'lock', 'settings' => []],
];

// Categorize settings
foreach ($settings as $setting) {
    $key = $setting['key'];
    if (in_array($key, ['site_name', 'site_description', 'contact_email'])) {
        $settingsGroups['general']['settings'][] = $setting;
    } elseif (strpos($key, 'hero_') === 0 || strpos($key, 'cta_') === 0) {
        $settingsGroups['homepage']['settings'][] = $setting;
    } elseif (strpos($key, 'social_') === 0) {
        $settingsGroups['social']['settings'][] = $setting;
    } elseif (strpos($key, '_key') !== false || strpos($key, 'secret') !== false) {
        $settingsGroups['security']['settings'][] = $setting;
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impostazioni - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="../assets/js/main.js" defer></script>
</head>
<body class="min-h-screen bg-gray-100 flex">
    <!-- Sidebar -->
    <div class="bg-gray-900 text-white w-64 flex flex-col">
        <div class="p-4 border-b border-gray-700">
            <h1 class="font-bold text-lg">Admin Panel</h1>
        </div>
        <?php include 'partials/menu.php'; ?>
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
            <h1 class="text-2xl font-bold text-gray-900">⚙️ Impostazioni Sistema</h1>
        </header>
        
        <main class="flex-1 overflow-auto p-6">
            <?php if (isset($_GET['success'])): ?>
            <div class="mb-6 bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-lg success-alert">
                <p><?php echo htmlspecialchars($_GET['success']); ?></p>
            </div>
            <?php endif; ?>

            <form action="impostazioni.php" method="POST" class="space-y-8">
                <?php foreach ($settingsGroups as $group): if (!empty($group['settings'])): ?>
                <div class="bg-white rounded-xl shadow-sm border">
                    <div class="px-6 py-4 border-b">
                        <h2 class="text-lg font-bold text-gray-900"><?php echo $group['title']; ?></h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <?php foreach ($group['settings'] as $setting): ?>
                        <div>
                            <label for="<?php echo htmlspecialchars($setting['key']); ?>" class="block text-sm font-semibold text-gray-700">
                                <?php echo ucfirst(str_replace('_', ' ', $setting['key'])); ?>
                            </label>
                            <input type="<?php echo strpos($setting['key'], 'secret') !== false ? 'password' : 'text'; ?>"
                                   name="settings[<?php echo htmlspecialchars($setting['key']); ?>]"
                                   id="<?php echo htmlspecialchars($setting['key']); ?>"
                                   class="w-full mt-1 px-4 py-2 border rounded-lg"
                                   value="<?php echo htmlspecialchars($setting['value']); ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; endforeach; ?>

                <div class="flex justify-end">
                    <button type="submit" class="px-8 py-3 bg-blue-600 text-white rounded-lg font-semibold">Salva Impostazioni</button>
                </div>
            </form>
        </main>
    </div>

    <script>
        lucide.createIcons();
        // Auto-hide alerts
        setTimeout(() => {
            document.querySelectorAll('.success-alert').forEach(alert => {
                if (alert) {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(() => alert.remove(), 500);
                }
            });
        }, 5000);
    </script>
</body>
</html>