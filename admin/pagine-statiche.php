<?php
require_once '../includes/config.php';
require_once 'auth_check.php';

// Definisci le pagine statiche che possono essere modificate
$staticPages = [
    'chi-siamo.php' => [
        'label' => 'Chi Siamo',
        'description' => 'Modifica il contenuto della pagina "Chi Siamo".',
        'content_file' => 'chi-siamo.html'
    ],
    'contatti.php' => [
        'label' => 'Contatti',
        'description' => 'Modifica il testo introduttivo della pagina contatti.',
        'content_file' => 'contatti.html'
    ],
    'privacy-policy.php' => [
        'label' => 'Privacy Policy',
        'description' => 'Aggiorna il testo della Privacy Policy.',
        'content_file' => 'privacy-policy.html'
    ],
    'termini-servizio.php' => [
        'label' => 'Termini di Servizio',
        'description' => 'Aggiorna i termini e le condizioni del servizio.',
        'content_file' => 'termini-servizio.html'
    ],
];

$success_message = '';
$error_message = '';
$selected_page_key = $_GET['page'] ?? null;
$content = '';
$content_dir = dirname(__DIR__) . '/partials/static_content/';

// Se una pagina è stata selezionata, leggi il suo contenuto dal file .html
if ($selected_page_key && isset($staticPages[$selected_page_key])) {
    $content_file_path = $content_dir . $staticPages[$selected_page_key]['content_file'];
    if (file_exists($content_file_path)) {
        $content = file_get_contents($content_file_path);
    } else {
        $error_message = "File di contenuto non trovato: " . htmlspecialchars($staticPages[$selected_page_key]['content_file']);
    }
}

// Gestione del salvataggio
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content']) && isset($_POST['page_key'])) {
    $page_key = $_POST['page_key'];
    $new_content = $_POST['content'];

    if (isset($staticPages[$page_key])) {
        $content_file_path = $content_dir . $staticPages[$page_key]['content_file'];
        if (file_put_contents($content_file_path, $new_content) !== false) {
            $success_message = "Contenuto per '{$staticPages[$page_key]['label']}' aggiornato con successo!";
            // Ricarica il contenuto per visualizzarlo aggiornato nell'editor
            $content = $new_content;
            $selected_page_key = $page_key;
        } else {
            $error_message = "Errore durante il salvataggio del file di contenuto.";
        }
    } else {
        $error_message = "Pagina non valida.";
    }
}

$page_title = 'Gestione Pagine Statiche';
include 'partials/header.php';
?>

<main class="flex-1 p-8 overflow-y-auto">
    <div class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Gestione Pagine Statiche</h1>
            <p class="text-gray-600 mt-1">Modifica il contenuto testuale delle pagine principali del sito.</p>
        </div>

        <?php if ($success_message): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-md" role="alert">
            <p><?php echo $success_message; ?></p>
        </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-md" role="alert">
            <p><?php echo $error_message; ?></p>
        </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Colonna di selezione -->
            <div class="md:col-span-1">
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-800">Seleziona una Pagina</h2>
                    </div>
                    <div class="p-4">
                        <nav class="space-y-1">
                            <?php foreach ($staticPages as $key => $details): ?>
                                <a href="?page=<?php echo $key; ?>"
                                   class="block px-4 py-3 rounded-md transition-colors <?php echo ($selected_page_key === $key) ? 'bg-blue-100 text-blue-700 font-semibold' : 'text-gray-600 hover:bg-gray-100'; ?>">
                                    <span class="block text-md"><?php echo htmlspecialchars($details['label']); ?></span>
                                    <small class="block text-sm text-gray-500"><?php echo htmlspecialchars($details['description']); ?></small>
                                </a>
                            <?php endforeach; ?>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Colonna di modifica -->
            <div class="md:col-span-2">
                <?php if ($selected_page_key && isset($staticPages[$selected_page_key])): ?>
                <div class="bg-white rounded-lg shadow-md">
                    <div class="p-6 border-b">
                        <h2 class="text-lg font-semibold text-gray-800">Modifica: <?php echo htmlspecialchars($staticPages[$selected_page_key]['label']); ?></h2>
                    </div>
                    <div class="p-6">
                        <form method="POST">
                            <input type="hidden" name="page_key" value="<?php echo $selected_page_key; ?>">
                            <div class="mb-6">
                                <label for="editor" class="block text-sm font-medium text-gray-700 mb-2">Contenuto</label>
                                <textarea name="content" id="editor" class="w-full h-96 border border-gray-300 rounded-md"><?php echo htmlspecialchars($content); ?></textarea>
                            </div>
                            <div class="flex justify-end">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">
                                    Salva Modifiche
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php else: ?>
                <div class="bg-white rounded-lg shadow-md p-12 flex flex-col items-center justify-center h-full">
                    <i data-lucide="mouse-pointer-click" class="w-16 h-16 text-gray-400 mb-4"></i>
                    <h3 class="text-xl font-semibold text-gray-700">Nessuna pagina selezionata</h3>
                    <p class="text-gray-500 mt-2">Seleziona una pagina dalla lista a sinistra per iniziare.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- CKEditor 5 -->
<script src="https://cdn.ckeditor.com/ckeditor5/35.4.0/classic/ckeditor.js"></script>
<script>
    const editorElement = document.querySelector('#editor');
    if (editorElement) {
        ClassicEditor
            .create(editorElement)
            .catch(error => {
                console.error('Errore durante l\'inizializzazione di CKEditor:', error);
            });
    }
    lucide.createIcons();
</script>

<?php include 'partials/footer.php'; ?>