<?php
require_once '../includes/config.php';
require_once 'auth_check.php';
require_once '../includes/database_mysql.php';

$pageTitle = 'Gestione Pagine Statiche';

$editable_pages = [
    'chi-siamo.php' => 'Chi Siamo',
    'contatti.php' => 'Contatti',
    'privacy-policy.php' => 'Privacy Policy',
    'termini-servizio.php' => 'Termini di Servizio'
];

$selected_page = '';
$page_content = '';

if (isset($_GET['page']) && array_key_exists($_GET['page'], $editable_pages)) {
    $selected_page = $_GET['page'];
    $page_path = dirname(__DIR__) . '/' . $selected_page;
    if (file_exists($page_path)) {
        $file_content = file_get_contents($page_path);

        $dom = new DOMDocument();
        // Use libxml to handle HTML5 and suppress warnings for malformed HTML
        libxml_use_internal_errors(true);
        $dom->loadHTML($file_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $main_tag = $dom->getElementsByTagName('main')->item(0);
        if ($main_tag) {
            $inner_html = '';
            foreach ($main_tag->childNodes as $child) {
                $inner_html .= $dom->saveHTML($child);
            }
            $page_content = $inner_html;
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['page']) && isset($_POST['content'])) {
    $selected_page = $_POST['page'];
    $new_content = $_POST['content'];
    $page_path = dirname(__DIR__) . '/' . $selected_page;

    if (array_key_exists($selected_page, $editable_pages) && file_exists($page_path)) {
        $template_content = file_get_contents($page_path);

        $dom = new DOMDocument();
        libxml_use_internal_errors(true);
        $dom->loadHTML($template_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();

        $main_tag = $dom->getElementsByTagName('main')->item(0);
        if ($main_tag) {
            while ($main_tag->hasChildNodes()) {
                $main_tag->removeChild($main_tag->firstChild);
            }

            $fragment = $dom->createDocumentFragment();
            // Append the new HTML content. The content from CKEditor is already HTML.
            $fragment->appendXML($new_content);
            $main_tag->appendChild($fragment);

            file_put_contents($page_path, $dom->saveHTML());
        }

        // Redirect to same page with success message
        header("Location: pagine-statiche.php?page=$selected_page&success=1");
        exit;
    }
}


include 'partials/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Gestione Pagine Statiche</h1>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Successo!</strong>
            <span class="block sm:inline">La pagina è stata aggiornata.</span>
        </div>
    <?php endif; ?>

    <div class="bg-white shadow-lg rounded-lg p-6">
        <form method="GET" action="pagine-statiche.php" class="mb-6">
            <label for="page-select" class="block text-gray-700 text-sm font-bold mb-2">Seleziona una pagina:</label>
            <div class="flex">
                <select id="page-select" name="page" class="block w-full bg-white border border-gray-300 rounded-md shadow-sm py-2 px-3 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm" onchange="this.form.submit()">
                    <option value="">-- Seleziona --</option>
                    <?php foreach ($editable_pages as $file => $title): ?>
                        <option value="<?php echo $file; ?>" <?php echo ($selected_page === $file) ? 'selected' : ''; ?>>
                            <?php echo $title; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </form>

        <?php if ($selected_page): ?>
            <form method="POST" action="pagine-statiche.php">
                <input type="hidden" name="page" value="<?php echo $selected_page; ?>">
                <textarea name="content" id="editor">
                    <?php echo htmlspecialchars($page_content); ?>
                </textarea>
                <button type="submit" class="mt-4 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">Salva Modifiche</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'))
        .catch(error => {
            console.error(error);
        });
</script>

<?php include 'partials/footer.php'; ?>