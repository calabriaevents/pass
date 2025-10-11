<?php
// admin/pagine-statiche.php
session_start();
include '../includes/db_config.php';
include '../includes/database_mysql.php';
include 'auth_check.php';

$db = new Database();

$tinymce_api_key = $db->getSetting('tinymce_api_key');
if(empty($tinymce_api_key)) {
    $tinymce_api_key = 'no-api-key';
}

// List of editable static pages
$editable_pages = [
    'chi-siamo.php' => 'Chi Siamo',
    'contatti.php' => 'Contatti',
    'privacy-policy.php' => 'Privacy Policy',
    'termini-servizio.php' => 'Termini di Servizio'
];

$selected_page = '';
$page_content = '';
$feedback = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['page_to_edit']) && isset($editable_pages[$_POST['page_to_edit']])) {
        $selected_page = $_POST['page_to_edit'];
        if (isset($_POST['page_content'])) {
            // Save the content
            $content_to_save = $_POST['page_content'];
            if (file_put_contents('../' . $selected_page, $content_to_save) !== false) {
                $feedback = "Pagina '{$editable_pages[$selected_page]}' aggiornata con successo!";
            } else {
                $feedback = "Errore durante il salvataggio della pagina.";
            }
        }
    }
}

if (isset($_GET['page']) && isset($editable_pages[$_GET['page']])) {
    $selected_page = $_GET['page'];
    $page_content = file_get_contents('../' . $selected_page);
}

include 'partials/header.php';
?>
<script src="https://cdn.tiny.cloud/1/<?php echo $tinymce_api_key; ?>/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Gestione Pagine Statiche</h1>

    <?php if ($feedback): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline"><?php echo $feedback; ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white p-4 rounded-lg shadow">
        <form method="GET" action="pagine-statiche.php" class="mb-4">
            <label for="page-select" class="block text-sm font-medium text-gray-700">Seleziona una pagina da modificare:</label>
            <div class="flex">
                <select id="page-select" name="page" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                    <option value="">-- Seleziona --</option>
                    <?php foreach ($editable_pages as $file => $title): ?>
                        <option value="<?php echo $file; ?>" <?php echo ($selected_page === $file) ? 'selected' : ''; ?>>
                            <?php echo $title; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="ml-2 px-4 py-2 bg-blue-500 text-white rounded-md">Modifica</button>
            </div>
        </form>

        <?php if ($selected_page): ?>
            <hr class="my-4">
            <h2 class="text-xl font-bold mb-2">Modifica: <?php echo $editable_pages[$selected_page]; ?></h2>
            <form method="POST" action="pagine-statiche.php">
                <input type="hidden" name="page_to_edit" value="<?php echo $selected_page; ?>">
                <div class="mb-4">
                    <label for="page-content" class="block text-sm font-medium text-gray-700">Contenuto della pagina</label>
                    <textarea id="page-content" name="page_content" rows="20" class="mt-1 block w-full p-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"><?php echo $page_content; ?></textarea>
                </div>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md">Salva Modifiche</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php include 'partials/footer.php'; ?>
<script>
    tinymce.init({
        selector: 'textarea#page-content',
        plugins: 'code table lists image link',
        toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | indent outdent | bullist numlist | code | table | image | link'
    });
</script>