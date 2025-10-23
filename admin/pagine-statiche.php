<?php
// admin/pagine-statiche.php

// Includi l'header e il menu dell'area admin
require_once 'partials/header.php';

// Elenco delle pagine statiche modificabili
$static_pages = [
    'chi-siamo' => 'Chi Siamo',
    'contatti' => 'Contatti (solo testo, la mappa e il form sono fissi)',
    'privacy-policy' => 'Privacy Policy',
    'cookie-policy' => 'Cookie Policy',
    'termini-servizio' => 'Termini di Servizio',
];

$selected_page = $_GET['page'] ?? 'chi-siamo'; // Pagina di default
$content_file_path = '';
$current_content = '';
$success_message = '';
$error_message = '';

if (array_key_exists($selected_page, $static_pages)) {
    // Definisci il percorso del file di contenuto
    $content_file_path = dirname(__DIR__) . '/partials/static_content/' . $selected_page . '.html';

    // Gestione del salvataggio
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['content'])) {
        // Assicurati che la directory esista
        if (!is_dir(dirname($content_file_path))) {
            mkdir(dirname($content_file_path), 0755, true);
        }

        if (file_put_contents($content_file_path, $_POST['content']) !== false) {
            $success_message = 'Contenuto salvato con successo!';
        } else {
            $error_message = 'Errore durante il salvataggio del file. Controlla i permessi della cartella.';
        }
    }

    // Carica il contenuto corrente dopo un eventuale salvataggio
    if (file_exists($content_file_path)) {
        $current_content = file_get_contents($content_file_path);
    } else {
        $current_content = "<p>Inizia a scrivere il contenuto per questa pagina...</p>";
    }
} else {
    $error_message = 'Pagina selezionata non valida.';
}

// Contenuto principale della pagina
?>

<!-- Includi CSS di SunEditor -->
<link href="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/css/suneditor.min.css" rel="stylesheet">

<div class="p-6">
    <div class="bg-white shadow rounded-lg p-4">
        <h1 class="text-2xl font-bold mb-4">Gestione Pagine Statiche</h1>

        <?php if ($success_message): ?>
            <div class="p-4 mb-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($error_message): ?>
            <div class="p-4 mb-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <form method="GET" class="mb-4">
            <label for="page-select" class="block text-sm font-medium text-gray-700">Seleziona una pagina da modificare:</label>
            <select id="page-select" name="page" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md" onchange="this.form.submit()">
                <?php foreach ($static_pages as $slug => $title): ?>
                    <option value="<?php echo $slug; ?>" <?php echo ($selected_page === $slug) ? 'selected' : ''; ?>>
                        <?php echo $title; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>

        <?php if (array_key_exists($selected_page, $static_pages)): ?>
            <form method="POST" action="?page=<?php echo htmlspecialchars($selected_page); ?>">
                <textarea id="suneditor" name="content">
                    <?php echo htmlspecialchars($current_content); ?>
                </textarea>
                <button type="submit" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Salva Modifiche</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<!-- Includi JS di SunEditor e inizializzalo -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/dist/suneditor.min.js"></script>
<!-- Includi le traduzioni (opzionale) -->
<script src="https://cdn.jsdelivr.net/npm/suneditor@latest/src/lang/it.js"></script>

<script>
  const editor = SUNEDITOR.create((document.getElementById('suneditor') || 'suneditor'),{
    // Opzioni di SunEditor
    lang: SUNEDITOR_LANG['it'],
    buttonList: [
        ['undo', 'redo'],
        ['font', 'fontSize', 'formatBlock'],
        ['paragraphStyle', 'blockquote'],
        ['bold', 'underline', 'italic', 'strike', 'subscript', 'superscript'],
        ['fontColor', 'hiliteColor', 'textStyle'],
        ['removeFormat'],
        '/', // Line break
        ['outdent', 'indent'],
        ['align', 'horizontalRule', 'list', 'lineHeight'],
        ['table', 'link', 'image', 'video'],
        ['fullScreen', 'showBlocks', 'codeView'],
        ['preview', 'print'],
        ['save'] // Aggiunto pulsante Salva, anche se non strettamente necessario avendo il nostro.
    ],
    width: '100%',
    height: 'auto'
  });

  // Assicurati che il contenuto dell'editor venga passato alla textarea prima dell'invio del form
  const form = document.querySelector('form[method="POST"]');
  form.addEventListener('submit', function() {
    editor.save();
  });
</script>

<?php
// Includi il footer
require_once 'partials/footer.php';
?>
