<?php
require_once '../includes/config.php';
require_once '../includes/database_mysql.php';
require_once '../includes/image_processor.php'; // Includi il nuovo processore di immagini

// Controlla autenticazione (da implementare)
// requireLogin();

$db = new Database();
$imageProcessor = new ImageProcessor(); // Istanzia il processore

// Gestione upload immagini AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
    $file = $_FILES['image'];
    $new_path = $imageProcessor->processUploadedImage($file, 'home', 1920); // Salva in 'uploads/home/'

    if ($new_path) {
        // Rimuovi '../' per il percorso web
        $web_path = str_replace('../', '', $new_path);
        echo json_encode(['success' => true, 'path' => $web_path]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Errore durante l\'elaborazione dell\'immagine.']);
    }
    exit;
}

// Helper function to delete old image if it has changed
function deleteOldImageOnChange(string $new_path, string $old_path, ImageProcessor $processor) {
    if ($new_path !== $old_path && !empty($old_path)) {
        $processor->deleteImage($old_path);
    }
}

// Gestione aggiornamenti sezioni
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $settings = $db->getSettingsAsArray();

    switch ($_POST['action']) {
        case 'update_hero':
            $heroSection = $db->getHomeSectionByName('hero');
            $old_image_path = $heroSection['image_path'] ?? '';
            $new_image_path = $_POST['hero_image'];
            deleteOldImageOnChange($new_image_path, $old_image_path, $imageProcessor);

            $db->updateHomeSection('hero', [
                'title' => $_POST['hero_title'],
                'subtitle' => $_POST['hero_subtitle'], 
                'description' => $_POST['hero_description'],
                'image_path' => $new_image_path,
                'custom_data' => json_encode([
                    'button1_text' => $_POST['button1_text'],
                    'button1_link' => $_POST['button1_link'],
                    'button2_text' => $_POST['button2_text'],
                    'button2_link' => $_POST['button2_link']
                ])
            ]);
            break;
            
        case 'update_events':
            deleteOldImageOnChange($_POST['app_store_image'], $settings['app_store_image'] ?? '', $imageProcessor);
            deleteOldImageOnChange($_POST['play_store_image'], $settings['play_store_image'] ?? '', $imageProcessor);

            $new_settings = [
                'app_store_link' => $_POST['app_store_link'] ?? '',
                'play_store_link' => $_POST['play_store_link'] ?? '',
                'app_store_image' => $_POST['app_store_image'] ?? '',
                'play_store_image' => $_POST['play_store_image'] ?? '',
                'vai_app_link' => $_POST['vai_app_link'] ?? '',
                'suggerisci_evento_link' => $_POST['suggerisci_evento_link'] ?? ''
            ];
            $db->updateSettings($new_settings);
            break;
            
        case 'update_categories':
            deleteOldImageOnChange($_POST['categories_bg_image'], $settings['categories_bg_image'] ?? '', $imageProcessor);
            $new_settings = [
                'categories_title' => $_POST['categories_title'] ?? 'Esplora per Categoria',
                'categories_description' => $_POST['categories_description'] ?? 'Scopri la Calabria...',
                'categories_button_text' => $_POST['categories_button_text'] ?? 'Vedi Tutte le Categorie',
                'categories_bg_image' => $_POST['categories_bg_image'] ?? ''
            ];
            $db->updateSettings($new_settings);
            break;
            
        case 'update_provinces':
            deleteOldImageOnChange($_POST['provinces_bg_image'], $settings['provinces_bg_image'] ?? '', $imageProcessor);
            $new_settings = [
                'provinces_title' => $_POST['provinces_title'] ?? 'Esplora le Province',
                'provinces_description' => $_POST['provinces_description'] ?? 'Ogni provincia custodisce tesori unici',
                'provinces_bg_image' => $_POST['provinces_bg_image'] ?? ''
            ];
            $db->updateSettings($new_settings);
            break;
            
        case 'update_map':
            $new_settings = [
                'map_title' => $_POST['map_title'] ?? 'Esplora la Mappa Interattiva',
                'map_description' => $_POST['map_description'] ?? 'Naviga con la nostra mappa interattiva',
                'map_full_link_text' => $_POST['map_full_link_text'] ?? 'Visualizza mappa completa'
            ];
            $db->updateSettings($new_settings);
            break;
            
        case 'update_cta':
            deleteOldImageOnChange($_POST['cta_bg_image'], $settings['cta_bg_image'] ?? '', $imageProcessor);
            $new_settings = [
                'cta_title' => $_POST['cta_title'] ?? 'Vuoi far Conoscere la Tua Calabria?',
                'cta_description' => $_POST['cta_description'] ?? 'Unisciti alla nostra community!',
                'cta_button1_text' => $_POST['cta_button1_text'] ?? 'Collabora con Noi',
                'cta_button1_link' => $_POST['cta_button1_link'] ?? 'collabora.php',
                'cta_button2_text' => $_POST['cta_button2_text'] ?? 'Suggerisci un Luogo',
                'cta_button2_link' => $_POST['cta_button2_link'] ?? 'suggerisci.php',
                'cta_bg_image' => $_POST['cta_bg_image'] ?? ''
            ];
            $db->updateSettings($new_settings);
            break;
            
        case 'update_newsletter':
            deleteOldImageOnChange($_POST['newsletter_bg_image'], $settings['newsletter_bg_image'] ?? '', $imageProcessor);
            $new_settings = [
                'newsletter_title' => $_POST['newsletter_title'] ?? 'Resta Connesso con la Calabria',
                'newsletter_description' => $_POST['newsletter_description'] ?? 'Iscriviti alla nostra newsletter...',
                'newsletter_placeholder' => $_POST['newsletter_placeholder'] ?? 'Inserisci la tua email',
                'newsletter_button' => $_POST['newsletter_button'] ?? 'Iscriviti Gratis',
                'newsletter_privacy' => $_POST['newsletter_privacy'] ?? 'Rispettiamo la tua privacy.',
                'newsletter_form_action' => $_POST['newsletter_form_action'] ?? 'api/newsletter.php',
                'newsletter_bg_image' => $_POST['newsletter_bg_image'] ?? ''
            ];
            $db->updateSettings($new_settings);
            break;
            
        case 'update_social':
            $new_settings = [
                'social_facebook' => $_POST['social_facebook'] ?? '',
                'social_instagram' => $_POST['social_instagram'] ?? '',
                'social_twitter' => $_POST['social_twitter'] ?? '',
                'social_youtube' => $_POST['social_youtube'] ?? '',
                'social_follow_text' => $_POST['social_follow_text'] ?? 'Seguici sui social media'
            ];
            $db->updateSettings($new_settings);
            break;
    }
    
    header('Location: gestione-home.php?success=true');
    exit;
}

// Carica dati attuali per la visualizzazione
$heroSection = $db->getHomeSectionByName('hero');
$heroData = $heroSection ? json_decode($heroSection['custom_data'], true) : [];
$settingsArray = $db->getSettingsAsArray();

?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestione Home - Admin Panel</title>
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
            <h1 class="text-2xl font-bold text-gray-900">Gestione Homepage</h1>
        </header>
        
        <main class="flex-1 overflow-auto p-6">
            <?php if (isset($_GET['success'])): ?>
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 success-alert" role="alert">
                <p>✅ Modifiche salvate con successo!</p>
            </div>
            <?php endif; ?>

            <!-- Form Sezioni -->
            <div class="space-y-6">
                <!-- Sezione Hero -->
                <form method="POST" class="bg-white rounded-lg shadow-sm p-6">
                    <input type="hidden" name="action" value="update_hero">
                    <h2 class="text-xl font-bold text-gray-900 mb-4">🎯 Sezione Hero</h2>
                    <!-- ... campi ... -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Titolo</label>
                                <input type="text" name="hero_title" value="<?php echo htmlspecialchars($heroSection['title'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Sottotitolo</label>
                                <input type="text" name="hero_subtitle" value="<?php echo htmlspecialchars($heroSection['subtitle'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Immagine Sfondo</label>
                                <div class="flex items-center space-x-2">
                                    <input type="text" name="hero_image" id="hero_image_path" value="<?php echo htmlspecialchars($heroSection['image_path'] ?? ''); ?>" class="flex-1 px-3 py-2 border rounded-lg" readonly>
                                    <button type="button" onclick="document.getElementById('hero_image_upload').click()" class="bg-blue-600 text-white px-4 py-2 rounded-lg">Carica</button>
                                </div>
                                <input type="file" id="hero_image_upload" class="hidden" onchange="handleImageUpload(this, 'hero_image_path')">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <!-- Pulsanti -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Testo Pulsante 1</label>
                                <input type="text" name="button1_text" value="<?php echo htmlspecialchars($heroData['button1_text'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Link Pulsante 1</label>
                                <input type="text" name="button1_link" value="<?php echo htmlspecialchars($heroData['button1_link'] ?? ''); ?>" class="w-full px-3 py-2 border rounded-lg">
                            </div>
                        </div>
                    </div>
                    <div class="mt-6 flex justify-end">
                        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded-lg font-semibold">Salva Sezione Hero</button>
                    </div>
                </form>

                <!-- Altre sezioni (form simili) -->
                <!-- ... -->
            </div>
        </main>
    </div>

    <script>
        lucide.createIcons();

        function handleImageUpload(fileInput, targetInputId) {
            if (fileInput.files.length === 0) return;
            const file = fileInput.files[0];
            const formData = new FormData();
            formData.append('image', file);

            // Aggiungi un feedback di caricamento
            const targetInput = document.getElementById(targetInputId);
            const originalValue = targetInput.value;
            targetInput.value = 'Caricamento in corso...';
            targetInput.disabled = true;

            fetch('gestione-home.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    targetInput.value = data.path;
                } else {
                    alert('Errore: ' + data.error);
                    targetInput.value = originalValue; // Ripristina il valore precedente in caso di errore
                }
            })
            .catch(error => {
                console.error('Upload error:', error);
                alert('Si è verificato un errore di rete.');
                targetInput.value = originalValue;
            })
            .finally(() => {
                targetInput.disabled = false;
                fileInput.value = ''; // Resetta l'input file
            });
        }
        
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